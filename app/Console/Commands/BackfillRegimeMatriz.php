<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Participante;
use App\Services\Consultas\Fontes\CadastroFonte;
use App\Services\Consultas\Providers\MinhaReceitaProvider;
use App\Support\Cnpj;
use Illuminate\Console\Command;

/**
 * Backfill do regime tributário de fichas que ficaram "Não informado" ANTES do fallback
 * pela matriz (2026-07-03): a RFB só publica a forma de tributação no CNPJ da matriz,
 * então filiais consultadas ficavam sem regime. Refaz só a fonte cadastral (minhareceita,
 * grátis, throttle 1 req/s) e grava regime + nota. NÃO toca ultima_consulta_em — isto não
 * é uma consulta nova, é correção de exibição.
 */
class BackfillRegimeMatriz extends Command
{
    protected $signature = 'regime:backfill-matriz {--dry-run : Só mostra o que mudaria, sem gravar}';

    protected $description = 'Preenche regime tributário "Não informado" via cadastro da matriz (minhareceita, grátis)';

    public function handle(CadastroFonte $fonte, MinhaReceitaProvider $provider): int
    {
        $dry = (bool) $this->option('dry-run');

        $alvos = collect()
            ->concat(
                Participante::whereNotNull('ultima_consulta_em')
                    ->where(fn ($q) => $q->whereNull('regime_tributario')->orWhere('regime_tributario', 'Não informado'))
                    ->get()
            )
            ->concat(
                Cliente::where(fn ($q) => $q->whereNull('regime_tributario')->orWhere('regime_tributario', 'Não informado'))
                    ->whereNotNull('documento')
                    ->get()
            )
            ->filter(fn ($a) => strlen(Cnpj::digitos((string) $a->documento)) === 14);

        $this->info($alvos->count().' ficha(s) com regime indefinido.'.($dry ? ' [dry-run]' : ''));

        $atualizados = 0;
        // Cache por CNPJ consultado na execução: filiais da mesma base reusam a matriz.
        $rawPorCnpj = [];
        $buscar = function (string $cnpj) use ($provider, &$rawPorCnpj): array {
            if (! array_key_exists($cnpj, $rawPorCnpj)) {
                sleep(1); // gentileza com a API pública
                $resp = $provider->consultar('', ['cnpj' => $cnpj]);
                $rawPorCnpj[$cnpj] = $resp->status === 'sucesso' ? $resp->raw : [];
            }

            return $rawPorCnpj[$cnpj];
        };

        foreach ($alvos as $alvo) {
            $cnpj = Cnpj::digitos((string) $alvo->documento);
            $tipo = $alvo instanceof Participante ? 'participante' : 'cliente';

            $raw = $buscar($cnpj);
            if ($raw === []) {
                $this->warn("  {$tipo} #{$alvo->id} {$cnpj}: cadastro indisponível, pulado");

                continue;
            }

            $dados = $fonte->normalizar($raw);

            if ($fonte->regimeIndefinido($dados) && Cnpj::ehFilial($cnpj)) {
                $rawMatriz = $buscar(Cnpj::matriz($cnpj));
                if ($rawMatriz !== []) {
                    $dados = $fonte->aplicarRegimeDaMatriz($dados, $rawMatriz);
                }
            }

            $regime = $dados['regime_tributario'] ?? null;
            $nota = $dados['regime_tributario_nota'] ?? null;

            if ($regime === null || ($regime === 'Não informado' && $nota === null)) {
                $this->line("  {$tipo} #{$alvo->id} {$cnpj}: segue sem regime publicado (matriz idem)");

                continue;
            }

            $this->info("  {$tipo} #{$alvo->id} {$cnpj}: {$regime}".($nota ? " — {$nota}" : ''));

            if (! $dry) {
                $alvo->forceFill([
                    'regime_tributario' => $regime,
                    'regime_tributario_nota' => $nota,
                ])->save();
            }

            $atualizados++;
        }

        $this->info(($dry ? '[dry-run] ' : '')."{$atualizados} ficha(s) atualizada(s).");

        return self::SUCCESS;
    }
}
