<?php

namespace App\Console\Commands;

use App\Models\ConsultaResultado;
use App\Models\CteConsulta;
use App\Models\NfeConsulta;
use App\Services\Consultas\ComprovanteArquivador;
use Illuminate\Console\Command;

class ArquivarComprovantesCommand extends Command
{
    /** @var string */
    protected $signature = 'consultas:arquivar-comprovantes
        {--dry-run : Apenas contabiliza comprovantes ainda recuperáveis}
        {--limite=500 : Quantidade máxima de arquivos a examinar}';

    /** @var string */
    protected $description = 'Arquiva comprovantes temporários ainda disponíveis no storage local';

    private int $examinados = 0;

    private int $arquivados = 0;

    private int $expirados = 0;

    private int $falhas = 0;

    private int $pendentesDryRun = 0;

    private int $limite = 500;

    private bool $dryRun = false;

    public function handle(ComprovanteArquivador $arquivador): int
    {
        $this->limite = max(1, (int) $this->option('limite'));
        $this->dryRun = (bool) $this->option('dry-run');

        $this->processarResultadosCnpj($arquivador);
        $this->processarSnapshots(NfeConsulta::class, $arquivador);
        $this->processarSnapshots(CteConsulta::class, $arquivador);

        $this->line('Arquivados: '.$this->arquivados);
        $this->line('Pulados-expirados: '.$this->expirados);
        $this->line('Falhas: '.$this->falhas);
        if ($this->dryRun) {
            $this->line('Vivos pendentes (dry-run): '.$this->pendentesDryRun);
        }

        return self::SUCCESS;
    }

    private function processarResultadosCnpj(ComprovanteArquivador $arquivador): void
    {
        $fontes = [
            'cnd_federal',
            'cnd_estadual',
            'cnd_municipal',
            'crf_fgts',
            'cndt',
            'sintegra',
        ];

        foreach (ConsultaResultado::query()->with(['lote:id,user_id', 'participante:id,documento', 'cliente:id,documento'])->whereNotNull('resultado_dados')->lazyById() as $resultado) {
            if ($this->atingiuLimite()) {
                return;
            }

            $dados = is_array($resultado->resultado_dados) ? $resultado->resultado_dados : [];
            $alterado = false;

            foreach ($fontes as $fonte) {
                if ($this->atingiuLimite()) {
                    break;
                }

                $bloco = $dados[$fonte] ?? null;
                if (! is_array($bloco)
                    || ! empty($bloco['comprovante_arquivo'])
                    || empty($bloco['comprovante'])) {
                    continue;
                }

                $arquivo = $this->arquivarSeVivo(
                    (string) $bloco['comprovante'],
                    (int) $resultado->lote?->user_id,
                    $arquivador,
                    ComprovanteArquivador::rotuloFonte(
                        $fonte,
                        $resultado->participante?->documento ?? $resultado->cliente?->documento,
                    ),
                );
                if ($arquivo !== null) {
                    $dados[$fonte]['comprovante_arquivo'] = $arquivo['path'];
                    $dados[$fonte]['comprovante_arquivado_em'] = $arquivo['arquivado_em'];
                    $alterado = true;
                }
            }

            if ($alterado && ! $this->dryRun) {
                $resultado->resultado_dados = $dados;
                $resultado->save();
            }
        }
    }

    /** @param class-string<NfeConsulta|CteConsulta> $model */
    private function processarSnapshots(string $model, ComprovanteArquivador $arquivador): void
    {
        if ($this->atingiuLimite()) {
            return;
        }

        foreach ($model::query()->lazyById() as $snapshot) {
            if ($this->atingiuLimite()) {
                return;
            }

            $payload = is_array($snapshot->payload) ? $snapshot->payload : [];
            $arquivos = (array) ($payload['comprovantes_arquivos'] ?? []);
            $alterado = false;

            foreach ([
                'html' => $snapshot->url_html,
                'xml' => $snapshot->url_xml,
                'site_receipt' => $snapshot->url_site_receipt,
            ] as $tipo => $url) {
                if ($this->atingiuLimite()) {
                    break;
                }
                if (! empty($arquivos[$tipo]) || ! is_string($url) || trim($url) === '') {
                    continue;
                }

                $arquivo = $this->arquivarSeVivo(
                    $url,
                    (int) $snapshot->user_id,
                    $arquivador,
                    ComprovanteArquivador::rotuloDocumento(
                        $model === CteConsulta::class ? 'CTE' : 'NFE',
                        (string) $snapshot->chave_acesso,
                        $tipo,
                    ),
                );
                if ($arquivo !== null) {
                    $arquivos[$tipo] = $arquivo['path'];
                    $alterado = true;
                }
            }

            if ($alterado && ! $this->dryRun) {
                $payload['comprovantes_arquivos'] = $arquivos;
                $snapshot->payload = $payload;
                $snapshot->save();
            }
        }
    }

    /** @return array{path: string, arquivado_em: string}|null */
    private function arquivarSeVivo(
        string $url,
        int $userId,
        ComprovanteArquivador $arquivador,
        ?string $rotulo = null,
    ): ?array {
        $this->examinados++;
        $expiraEm = $arquivador->expiraEm($url);
        if ($expiraEm !== null && $expiraEm <= now()->timestamp) {
            $this->expirados++;

            return null;
        }

        if ($this->dryRun) {
            $this->pendentesDryRun++;

            return null;
        }

        if ($userId <= 0) {
            $this->falhas++;

            return null;
        }

        $arquivo = $arquivador->arquivar($url, $userId, $rotulo);
        if ($arquivo === null) {
            $this->falhas++;

            return null;
        }

        $this->arquivados++;

        return $arquivo;
    }

    private function atingiuLimite(): bool
    {
        return $this->examinados >= $this->limite;
    }
}
