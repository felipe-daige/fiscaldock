<?php

namespace App\Services\Consultas;

use App\Models\EfdNota;
use App\Services\BiService;

/**
 * Estima o regime tributário quando a RFB não publica (regime "Não informado" após o
 * cadastro e o fallback pela matriz). O valor estimado NUNCA se passa por dado oficial:
 * vai com `regime_tributario_origem = 'estimado'` e nota "estimado — <base>" — e a
 * ficha (AtualizarFichaCadastralService) nunca deixa estimativa sobrescrever regime real.
 *
 * Regras, em ordem:
 *  1. Atividade obrigada ao Lucro Real (art. 14 Lei 9.718): bancos/financeiras/seguradoras
 *     (CNAE divisões 64/65) ou Sociedade Anônima Aberta.
 *  2. Vendas do participante ao usuário (EFD, 12 meses) acima do teto do Presumido
 *     (R$ 78 mi/ano) — piso comprovado de faturamento já obriga o Lucro Real.
 *  3. Default: Lucro Presumido — destino mais comum fora do Simples (base enriquecida
 *     com a data de exclusão do Simples quando existir).
 */
class RegimeEstimadoResolver
{
    public const ORIGEM = 'estimado';

    /** Teto anual de receita bruta do Lucro Presumido (art. 13 Lei 9.718). */
    public const TETO_PRESUMIDO = 78_000_000.00;

    /** Divisões CNAE obrigadas ao Lucro Real (instituições financeiras, seguros, previdência). */
    private const CNAE_DIVISOES_LUCRO_REAL = ['64', '65'];

    /**
     * Aplica a estimativa sobre o resultado normalizado do CadastroFonte.
     * Só age quando o regime ficou "Não informado"; caso contrário devolve intacto.
     *
     * @param  array<string,mixed>  $dados  saída de CadastroFonte::normalizar (regime já resolvido)
     */
    public function aplicar(array $dados, int $userId, string $alvoTipo, int $alvoId): array
    {
        if (($dados['regime_tributario'] ?? null) !== 'Não informado') {
            return $dados;
        }

        $vendas12m = $alvoTipo === 'participante'
            ? $this->vendas12mParaUsuario($userId, $alvoId)
            : null;

        [$regime, $base] = $this->estimar(
            naturezaJuridica: (string) ($dados['natureza_juridica'] ?? ''),
            cnaePrincipal: $this->cnaePrincipal($dados['cnaes'] ?? null),
            dataExclusaoSimples: $dados['data_exclusao_simples'] ?? null,
            vendas12m: $vendas12m,
        );

        $dados['regime_tributario'] = $regime;
        $dados['regime_tributario_origem'] = self::ORIGEM;
        $dados['regime_tributario_nota'] = 'estimado — '.$base;

        return $dados;
    }

    /**
     * Núcleo da heurística (puro, sem I/O) — reutilizado pelo backfill de fichas.
     *
     * @return array{0: string, 1: string} [regime, base da estimativa]
     */
    public function estimar(
        string $naturezaJuridica,
        ?string $cnaePrincipal,
        ?string $dataExclusaoSimples,
        ?float $vendas12m,
    ): array {
        if ($this->atividadeObrigadaLucroReal($naturezaJuridica, $cnaePrincipal)) {
            return ['Lucro Real', 'a natureza jurídica/atividade da empresa obriga a apuração pelo Lucro Real'];
        }

        if ($vendas12m !== null && $vendas12m > self::TETO_PRESUMIDO) {
            return ['Lucro Real', 'as vendas identificadas nas suas EFD (últimos 12 meses) superam o teto do Lucro Presumido (R$ 78 mi/ano)'];
        }

        $base = 'a RFB não publica o regime deste CNPJ; Lucro Presumido é o regime mais comum fora do Simples';
        if ($dataExclusaoSimples) {
            try {
                $data = \Carbon\Carbon::parse($dataExclusaoSimples)->format('d/m/Y');
                $base = "deixou o Simples Nacional em {$data}; Lucro Presumido é o destino mais comum após a exclusão";
            } catch (\Throwable) {
                // data ilegível → base genérica
            }
        }

        return ['Lucro Presumido', $base];
    }

    private function atividadeObrigadaLucroReal(string $naturezaJuridica, ?string $cnaePrincipal): bool
    {
        if (preg_match('/an[oô]nima aberta/iu', $naturezaJuridica)) {
            return true;
        }

        $divisao = substr(preg_replace('/\D/', '', (string) $cnaePrincipal), 0, 2);

        return in_array($divisao, self::CNAE_DIVISOES_LUCRO_REAL, true);
    }

    /** @param mixed $cnaes estrutura do CadastroFonte: [{codigo, descricao, principal}] */
    private function cnaePrincipal(mixed $cnaes): ?string
    {
        if (! is_array($cnaes)) {
            return null;
        }
        $principal = collect($cnaes)->firstWhere('principal', true) ?? ($cnaes[0] ?? null);

        return isset($principal['codigo']) ? (string) $principal['codigo'] : null;
    }

    /**
     * Piso de faturamento do participante: o que ele VENDEU pro usuário (entradas do usuário)
     * nos últimos 12 meses, na regra canônica de dedup do BI. Saídas ficam de fora (são
     * compras do participante, não receita dele).
     */
    public function vendas12mParaUsuario(int $userId, int $participanteId): ?float
    {
        try {
            $valor = EfdNota::query()
                ->where('user_id', $userId)
                ->where('participante_id', $participanteId)
                ->where('tipo_operacao', 'entrada')
                ->where('cancelada', false)
                ->whereRaw(BiService::dedupParticipanteSql('efd_notas'))
                ->where('data_emissao', '>=', now()->subMonths(12))
                ->sum('valor_total');

            return (float) $valor;
        } catch (\Throwable $e) {
            report($e);

            return null; // sem EFD não derruba a estimativa — cai no default
        }
    }
}
