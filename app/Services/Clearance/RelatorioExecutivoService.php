<?php

namespace App\Services\Clearance;

use App\Models\Cliente;
use App\Models\ConsultaLote;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Monta o view-model do PDF executivo de clearance a partir do resultado já calculado
 * (NÃO recalcula divergências — reusa a saída de DivergenciaService::analisar).
 *
 * Acrescenta ao resultado da tela: exposição monetizada (multa 75% + decadência),
 * concentração de risco top-5, metadados de capa e hash de integridade.
 *
 * Escopo MVP (2026-06-16): sem coluna "tratamento aplicado", sem Selic, sem recorrência
 * cross-lote. Ver docs/clearance/pdf-executivo.md.
 */
class RelatorioExecutivoService
{
    public function __construct(private ?ExposicaoFiscalService $exposicao = null)
    {
        $this->exposicao ??= new ExposicaoFiscalService;
    }

    /**
     * @param  Collection  $resultados  snapshots brutos (ClearanceController::listarConsultasDfePorLote)
     * @param  array  $divergencia  saída de DivergenciaService::analisar
     */
    public function montar(ConsultaLote $lote, Collection $resultados, array $divergencia): array
    {
        $divergencias = collect($divergencia['divergencias'] ?? []);
        $documentos = $this->enriquecerDocumentos($divergencias);
        $veredito = $divergencia['veredito'] ?? [];
        $base = (float) ($veredito['valor_divergente'] ?? 0.0);

        return [
            'capa' => $this->montarCapa($lote, $resultados),
            'resumo' => [
                'veredito' => $veredito,
                'total_documentos' => $resultados->count(),
                'total_divergencias' => $documentos->count(),
                'total_criticas' => (int) ($veredito['total_criticas'] ?? 0),
                'total_revisar' => (int) ($veredito['total_revisar'] ?? 0),
                'sem_divergencia' => collect($divergencia['sem_divergencia'] ?? [])->count(),
                'ruido' => collect($divergencia['ruido'] ?? [])->count(),
            ],
            'exposicao' => $this->montarExposicaoAgregada($base),
            'breakdown' => $divergencia['breakdown'] ?? [],
            'kpis' => $divergencia['kpis'] ?? [],
            'concentracao' => $this->montarConcentracao($documentos),
            'documentos' => $documentos,
            'sem_divergencia' => collect($divergencia['sem_divergencia'] ?? []),
            'metodologia' => [
                'tolerancia_absoluta' => DivergenciaService::TOLERANCIA_ABSOLUTA_RUIDO,
                'tolerancia_percentual' => DivergenciaService::TOLERANCIA_PERCENTUAL_RUIDO,
                'aliquota_multa' => ExposicaoFiscalService::ALIQUOTA_MULTA_OFICIO,
                'anos_decadencia' => ExposicaoFiscalService::ANOS_DECADENCIA,
            ],
            'hash' => $this->hashIntegridade($lote, $resultados, $veredito),
        ];
    }

    private function montarCapa(ConsultaLote $lote, Collection $resultados): array
    {
        $escritorio = Cliente::query()
            ->where('user_id', $lote->user_id)
            ->where('is_empresa_propria', true)
            ->first();

        $datas = $resultados
            ->map(fn ($r) => $r->data_emissao ?? null)
            ->filter()
            ->map(fn ($d) => substr((string) $d, 0, 10))
            ->filter(fn ($d) => $d !== '' && $d !== '0000-00-00')
            ->sort()
            ->values();

        $inicio = $datas->first();
        $fim = $datas->last();

        $clientesAuditados = $resultados
            ->map(fn ($r) => $r->cliente_nome ?? null)
            ->filter()
            ->unique()
            ->values();

        $clienteAuditado = $clientesAuditados->count() === 1
            ? $clientesAuditados->first()
            : ($escritorio?->razao_social ?? 'Acervo do escritório');

        return [
            'escritorio' => [
                'razao_social' => $escritorio?->razao_social ?? '—',
                'cnpj' => $this->formatarCnpj($escritorio?->documento),
            ],
            'cliente_auditado' => [
                'razao_social' => $clienteAuditado,
            ],
            'periodo' => [
                'inicio' => $inicio,
                'fim' => $fim,
                'label' => $this->labelPeriodo($inicio, $fim),
            ],
            'lote_id' => $lote->id,
            'emitido_em' => Carbon::now(),
            'emitido_em_label' => Carbon::now()->format('d/m/Y H:i'),
        ];
    }

    /**
     * Acrescenta exposição por documento às linhas divergentes (críticas + a revisar).
     */
    private function enriquecerDocumentos(Collection $divergencias): Collection
    {
        return $divergencias->map(function ($linha) {
            $base = $this->baseExpostaLinha($linha);
            $pacote = $this->exposicao->montar($base, $linha->data_emissao ?? null);

            $linha->exposicao_base = $pacote['base'];
            $linha->exposicao_multa = $pacote['multa'];
            $linha->exposicao_total = $pacote['total'];
            $linha->decadencia_label = $pacote['decadencia_label'];

            return $linha;
        })->values();
    }

    /**
     * Base exposta por linha — espelha a lógica de valorCriticoTotal de DivergenciaService:
     * nota fria (NAO_ENCONTRADA com declarado) expõe o valor declarado inteiro; demais, o |Δ|.
     */
    private function baseExpostaLinha(object $linha): float
    {
        $status = strtoupper((string) ($linha->status_label ?? $linha->status ?? ''));
        $declarado = $linha->declarado_valor ?? null;
        $sefaz = $linha->valor_total ?? null;

        if ($status === 'NAO_ENCONTRADA' && $declarado !== null && $sefaz === null) {
            return round((float) $declarado, 2);
        }

        return round(abs((float) ($linha->delta_valor ?? 0.0)), 2);
    }

    private function montarExposicaoAgregada(float $base): array
    {
        $pacote = $this->exposicao->montar($base, null);

        return [
            'base' => $pacote['base'],
            'multa' => $pacote['multa'],
            'total' => $pacote['total'],
            'base_label' => $this->reais($pacote['base']),
            'multa_label' => $this->reais($pacote['multa']),
            'total_label' => $this->reais($pacote['total']),
        ];
    }

    /**
     * Top-5 emitentes por valor exposto. Recorrência cross-lote = fase 2.
     */
    private function montarConcentracao(Collection $documentos): Collection
    {
        return $documentos
            ->groupBy(fn ($linha) => (string) ($linha->emit_cnpj ?? $linha->emit_nome ?? '—'))
            ->map(function (Collection $grupo) {
                $primeiro = $grupo->first();
                $valor = round((float) $grupo->sum('exposicao_base'), 2);

                return [
                    'emit_nome' => $primeiro->emit_nome ?? '—',
                    'emit_cnpj' => $primeiro->emit_cnpj ?? '—',
                    'qtd' => $grupo->count(),
                    'valor_exposto' => $valor,
                    'valor_exposto_label' => $this->reais($valor),
                ];
            })
            ->sortByDesc('valor_exposto')
            ->take(5)
            ->values();
    }

    /**
     * Hash sobre o payload de DADOS (não os bytes do PDF nem o timestamp) → mesmo lote, mesmo hash.
     */
    private function hashIntegridade(ConsultaLote $lote, Collection $resultados, array $veredito): string
    {
        $payload = [
            'lote' => $lote->id,
            'severidade' => $veredito['severidade'] ?? null,
            'valor_divergente' => $veredito['valor_divergente'] ?? null,
            'documentos' => $resultados
                ->map(fn ($r) => [
                    'chave' => $r->chave_acesso ?? null,
                    'status' => strtoupper((string) ($r->status_label ?? $r->status ?? '')),
                    'valor' => $r->valor_total ?? null,
                ])
                ->sortBy('chave')
                ->values()
                ->all(),
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    private function labelPeriodo(?string $inicio, ?string $fim): string
    {
        if ($inicio === null && $fim === null) {
            return '—';
        }

        $fmt = fn (?string $d) => $d ? Carbon::parse($d)->format('d/m/Y') : '—';

        return $inicio === $fim ? $fmt($inicio) : $fmt($inicio).' a '.$fmt($fim);
    }

    private function formatarCnpj(?string $documento): string
    {
        $digits = preg_replace('/\D/', '', (string) $documento);

        if (strlen($digits) !== 14) {
            return $documento ?: '—';
        }

        return preg_replace(
            '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/',
            '$1.$2.$3/$4-$5',
            $digits
        );
    }

    private function reais(float $valor): string
    {
        return 'R$ '.number_format($valor, 2, ',', '.');
    }
}
