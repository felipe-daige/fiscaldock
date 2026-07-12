<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Participante;
use App\Services\Consultas\RegimeEstimadoResolver;
use Illuminate\Console\Command;

/**
 * Backfill do regime ESTIMADO para fichas que ficaram "Não informado" mesmo após o
 * fallback pela matriz (RFB não publica a forma de tributação do CNPJ). Roda offline:
 * a ficha já tem natureza jurídica, CNAE principal e a nota histórica do Simples — a
 * mesma heurística do RegimeEstimadoResolver usada nas consultas novas. Grava com
 * origem 'estimado'; a próxima consulta com regime real da RFB sobrescreve.
 *
 * Só age em regime = 'Não informado' (consulta cadastral já rodou). Ficha nunca
 * consultada (regime null) fica de fora — sem cadastro, não dá nem pra saber se é Simples.
 */
class EstimarRegimeIndefinido extends Command
{
    protected $signature = 'regime:estimar-indefinidos {--dry-run : Só mostra o que mudaria, sem gravar}';

    protected $description = 'Estima regime tributário (origem estimado) para fichas com regime "Não informado"';

    public function handle(RegimeEstimadoResolver $resolver): int
    {
        $dry = (bool) $this->option('dry-run');

        $alvos = collect()
            ->concat(Participante::where('regime_tributario', 'Não informado')->get())
            ->concat(Cliente::where('regime_tributario', 'Não informado')->get());

        $this->info($alvos->count().' ficha(s) com regime "Não informado".'.($dry ? ' [dry-run]' : ''));

        $atualizados = 0;
        foreach ($alvos as $alvo) {
            $tipo = $alvo instanceof Participante ? 'participante' : 'cliente';

            $vendas12m = $alvo instanceof Participante
                ? $resolver->vendas12mParaUsuario($alvo->user_id, $alvo->id)
                : null;

            [$regime, $base] = $resolver->estimar(
                naturezaJuridica: (string) $alvo->natureza_juridica,
                cnaePrincipal: $alvo->cnae_principal,
                dataExclusaoSimples: $this->dataExclusaoDaNota((string) $alvo->regime_tributario_nota),
                vendas12m: $vendas12m,
            );

            $nota = 'estimado — '.$base;
            $this->info("  {$tipo} #{$alvo->id}: {$regime} — {$base}");

            if (! $dry) {
                $alvo->forceFill([
                    'regime_tributario' => $regime,
                    'regime_tributario_origem' => RegimeEstimadoResolver::ORIGEM,
                    'regime_tributario_nota' => $nota,
                ])->save();
            }

            $atualizados++;
        }

        $this->info(($dry ? '[dry-run] ' : '')."{$atualizados} ficha(s) atualizada(s).");

        return self::SUCCESS;
    }

    /** Nota legada "foi optante do Simples Nacional até 16/04/2025" → data em ISO. */
    private function dataExclusaoDaNota(string $nota): ?string
    {
        if (preg_match('/at[ée] (\d{2}\/\d{2}\/\d{4})/u', $nota, $m)) {
            try {
                return \Carbon\Carbon::createFromFormat('d/m/Y', $m[1])->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
