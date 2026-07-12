<?php

namespace App\Services\Bi\Export;

use App\Models\Cliente;
use App\Services\Bi\CruzamentosConsultasClearanceService;
use Illuminate\Support\Collection;

/**
 * Fonte única do PDF de Cruzamentos Fiscais (/app/bi/cruzamentos).
 *
 * Reusa os MESMOS services da tela — os números do relatório batem com a tela por
 * construção. Além do recorte, deriva o parecer executivo, a lista de providências
 * e mantém o carimbo da última consulta por fornecedor (prova de diligência: o
 * documento registra QUANDO cada CNPJ foi verificado e qual era a situação).
 */
class CruzamentosReportBuilder
{
    public const LIMITE_PDF = 80;

    public function __construct(private CruzamentosConsultasClearanceService $service) {}

    /**
     * @param  array{cliente_id?:int|null, data_inicio?:string|null, data_fim?:string|null}  $filtros
     */
    public function montar(int $userId, array $filtros): array
    {
        $irregulares = $this->service->fornecedoresIrregularesComCompras($userId, $filtros);
        $canceladas = $this->service->notasCanceladasComEmitente($userId, $filtros);
        $diagnostico = $this->service->diagnostico($userId);

        $kpis = [
            'irregulares_qtd' => $irregulares->count(),
            'irregulares_valor' => round((float) $irregulares->sum('valor_comprado'), 2),
            'canceladas_qtd' => $canceladas->count(),
        ];

        return [
            'titulo' => 'Cruzamentos Fiscais — Consultas × Notas',
            'gerado_em' => now(),
            'filtros' => $this->rotulosFiltros($userId, $filtros),
            'kpis' => $kpis,
            'diagnostico' => $diagnostico,
            'parecer' => $this->parecer($kpis, $diagnostico),
            'irregulares' => $irregulares->map(fn (array $l) => $this->linhaIrregular($l))->values()->all(),
            'canceladas' => $canceladas->map(fn (array $l) => $this->linhaCancelada($l))->values()->all(),
            'providencias' => $this->providencias($irregulares, $canceladas, $diagnostico),
        ];
    }

    /** @return array<string, string> */
    private function rotulosFiltros(int $userId, array $filtros): array
    {
        $clienteId = $filtros['cliente_id'] ?? null;
        $cliente = $clienteId
            ? Cliente::where('user_id', $userId)->find((int) $clienteId)
            : null;

        $de = $filtros['data_inicio'] ?? null;
        $ate = $filtros['data_fim'] ?? null;
        $fmt = fn (string $d) => \Illuminate\Support\Carbon::parse($d)->format('d/m/Y');
        $periodo = match (true) {
            $de && $ate => $fmt($de).' a '.$fmt($ate),
            (bool) $de => 'a partir de '.$fmt($de),
            (bool) $ate => 'até '.$fmt($ate),
            default => 'Todo o acervo',
        };

        return [
            'Cliente' => $cliente?->razao_social ?? 'Todos os CNPJs',
            'Período' => $periodo,
        ];
    }

    /**
     * Parecer executivo derivado por regra (sem juízo além dos dados da tela):
     * vermelho quando há compras de fornecedor irregular, âmbar quando só há notas
     * canceladas, verde quando os cruzamentos não acham nada — e cinza quando não
     * há sobreposição de dado (consultas × fornecedores) para cruzar.
     *
     * @return array{nivel: string, hex: string, label: string, texto: string}
     */
    private function parecer(array $kpis, array $diagnostico): array
    {
        if ($kpis['irregulares_qtd'] > 0) {
            return [
                'nivel' => 'critico',
                'hex' => '#dc2626',
                'label' => 'Exposição identificada',
                'texto' => sprintf(
                    'Há %d fornecedor(es) com certidão positiva ou situação cadastral irregular somando R$ %s em compras no recorte. Créditos apropriados dessas operações podem ser questionados; recomenda-se as providências listadas ao final antes de novos pagamentos.',
                    $kpis['irregulares_qtd'],
                    number_format($kpis['irregulares_valor'], 2, ',', '.')
                ),
            ];
        }

        if ($kpis['canceladas_qtd'] > 0) {
            return [
                'nivel' => 'atencao',
                'hex' => '#b45309',
                'label' => 'Pontos de atenção',
                'texto' => sprintf(
                    'Nenhum fornecedor irregular com compras no recorte, mas %d nota(s) do acervo constam CANCELADAS na SEFAZ. Conferir a escrituração e o aproveitamento de crédito dessas notas.',
                    $kpis['canceladas_qtd']
                ),
            ];
        }

        if ($diagnostico['fornecedores_consultados_qtd'] === 0) {
            return [
                'nivel' => 'sem_dado',
                'hex' => '#6b7280',
                'label' => 'Sem cobertura para cruzar',
                'texto' => 'Nenhum CNPJ consultado é fornecedor nas notas de entrada — os cruzamentos não têm sobreposição de dado. Consultar os CNPJs dos fornecedores habilita este relatório como prova de diligência.',
            ];
        }

        return [
            'nivel' => 'ok',
            'hex' => '#16a34a',
            'label' => 'Nada a tratar',
            'texto' => sprintf(
                'Entre os %d fornecedor(es) consultados que aparecem nas notas de entrada, nenhum apresenta certidão positiva ou situação cadastral irregular, e não há nota do acervo cancelada na SEFAZ no recorte.',
                $diagnostico['fornecedores_consultados_qtd']
            ),
        ];
    }

    private function linhaIrregular(array $l): array
    {
        $consulta = $l['ultima_consulta_em'] ?? null;

        return [
            'razao_social' => $l['razao_social'],
            'cnpj' => $this->formatarCnpj($l['documento']),
            'motivos' => $l['motivos'],
            'valor_comprado' => (float) $l['valor_comprado'],
            'qtd_notas' => (int) $l['qtd_notas'],
            'ultima_consulta' => $consulta ? $consulta->format('d/m/Y H:i') : '—',
        ];
    }

    private function linhaCancelada(array $l): array
    {
        $consulta = $l['consultado_em'] ?? null;

        return [
            'chave_acesso' => $l['chave_acesso'],
            'numero' => $l['numero'] !== null ? (string) $l['numero'] : '—',
            'emit_nome' => $l['emit_nome'],
            'emit_cnpj' => $this->formatarCnpj($l['emit_cnpj']),
            'valor' => $l['valor'],
            'situacao_emitente' => $l['situacao_emitente'] ?? 'Não consultado',
            'verificado_em' => $consulta ? \Illuminate\Support\Carbon::parse($consulta)->format('d/m/Y H:i') : '—',
        ];
    }

    /**
     * Checklist de providências derivado dos achados — cada item nasce de um dado
     * concreto do recorte, para o contador imprimir e riscar.
     *
     * @return array<int, string>
     */
    private function providencias(Collection $irregulares, Collection $canceladas, array $diagnostico): array
    {
        $itens = [];

        if ($irregulares->isNotEmpty()) {
            $itens[] = sprintf(
                'Exigir certidões negativas atualizadas dos %d fornecedor(es) irregular(es) antes de autorizar novos pagamentos.',
                $irregulares->count()
            );
            $itens[] = sprintf(
                'Avaliar com o responsável tributário o risco de glosa dos créditos das compras de fornecedores irregulares (R$ %s no recorte).',
                number_format((float) $irregulares->sum('valor_comprado'), 2, ',', '.')
            );

            $baixadas = $irregulares->filter(
                fn (array $l) => (bool) preg_grep('/Situação cadastral/', $l['motivos'])
            )->count();
            if ($baixadas > 0) {
                $itens[] = sprintf(
                    'Verificar se as operações com os %d fornecedor(es) de situação cadastral não ativa ocorreram antes ou depois da baixa/suspensão.',
                    $baixadas
                );
            }
        }

        if ($canceladas->isNotEmpty()) {
            $itens[] = sprintf(
                'Conferir a escrituração das %d nota(s) canceladas na SEFAZ e estornar créditos eventualmente aproveitados.',
                $canceladas->count()
            );
        }

        $naoConsultados = $diagnostico['fornecedores_entrada_qtd'] - $diagnostico['fornecedores_consultados_qtd'];
        if ($naoConsultados > 0) {
            $itens[] = sprintf(
                'Consultar os %d fornecedor(es) das notas de entrada ainda sem consulta de CNPJ — amplia a cobertura deste relatório.',
                $naoConsultados
            );
        }

        return $itens;
    }

    private function formatarCnpj(mixed $documento): string
    {
        $cnpj = preg_replace('/\D/', '', (string) $documento);

        return strlen($cnpj) === 14
            ? preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $cnpj)
            : (string) $documento;
    }
}
