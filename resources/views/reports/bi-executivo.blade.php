@extends('reports.layout')

@section('titulo', 'BI Fiscal — Relatório Executivo')
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('bi', json_encode($relatorio['periodo'] ?? [])))

@section('conteudo')
    @php
        $p = $relatorio['periodo'];
        $k = $relatorio['kpis'];
        $cob = $relatorio['cobertura'] ?? ['parcial' => false];
        $svc = app(\App\Services\BiExportService::class);
        $modo = $relatorio['modo'] ?? 'portfolio';
        // Seções que ganham barras CSS (idxLabel, idxValorBrl, hex)
        $barras = [
            'faturamento' => [0, 1, '#2563eb'],
            'tributos' => [0, 5, '#b45309'],
        ];
    @endphp

    <h1 style="font-size:16px;font-weight:bold;color:#111827;margin:0 0 2px;">BI Fiscal — Relatório Executivo</h1>
    <p style="font-size:9px;color:#6b7280;margin:0 0 12px;">
        {{ $modo === 'cliente' ? 'Cliente #'.$p['cliente_id'] : 'Carteira (todos os clientes)' }}
        · Período: {{ $p['inicio'] ?? 'Todos' }} a {{ $p['fim'] ?? 'Todos' }}
        · Gerado em {{ now()->format('d/m/Y H:i') }}
    </p>

    {{-- KPIs --}}
    <table style="width:100%;border-collapse:collapse;margin-bottom:12px;">
        <tr>
            @foreach ([
                ['Faturamento', $k['faturamento']], ['Aquisições', $k['aquisicoes']],
                ['Tributos', $k['tributos']], ['Saldo líquido', $k['saldo_liquido']],
            ] as $kpi)
                <td style="width:25%;border:1px solid #e5e7eb;padding:8px;vertical-align:top;">
                    <div style="font-size:8px;color:#9ca3af;text-transform:uppercase;">{{ $kpi[0] }}</div>
                    <div style="font-size:12px;font-weight:bold;color:#111827;">R$ {{ $kpi[1] }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    {{-- Cobertura --}}
    @if (! empty($cob['parcial']))
        @php
            $semFiscal = collect($cob['meses_sem_fiscal'] ?? []);
            $semContrib = collect($cob['meses_sem_contrib'] ?? []);
        @endphp
        <div style="background-color:#fffbeb;border:1px solid #fde68a;padding:8px;margin-bottom:12px;">
            @if ($semFiscal->isNotEmpty())
                <span style="color:#92400e;font-size:9px;display:block;">
                    &#9888; {{ $semFiscal->count() }} {{ $semFiscal->count() === 1 ? 'mês' : 'meses' }} sem EFD ICMS/IPI — entradas incompletas: {{ $semFiscal->pluck('label')->implode(', ') }}
                </span>
            @endif
            @if ($semContrib->isNotEmpty())
                <span style="color:#92400e;font-size:9px;display:block;">
                    &#9888; {{ $semContrib->count() }} {{ $semContrib->count() === 1 ? 'mês' : 'meses' }} sem EFD PIS/COFINS — receita/tributos incompletos: {{ $semContrib->pluck('label')->implode(', ') }}
                </span>
            @endif
        </div>
    @endif

    {{-- Seções na ordem definida pelo service --}}
    @foreach ($relatorio['ordem_secoes'] as $chave)
        @if ($chave === 'score-carteira')
            @php $sc = $relatorio['score_carteira'] ?? null; @endphp
            @if ($sc)
                <h2 style="font-size:11px;font-weight:bold;color:#374151;margin:14px 0 4px;">Score da carteira</h2>
                <table style="width:100%;border-collapse:collapse;margin-bottom:6px;">
                    <tr>
                        @foreach ([
                            ['% Regular', $sc['percentual_regular'].'%'],
                            ['Irregulares', $sc['irregulares'].' / '.$sc['participantes_ativos']],
                            ['% Em risco', $sc['percentual_em_risco'].'%'],
                            ['Valor em risco', 'R$ '.$sc['valor_total_em_risco_brl']],
                        ] as $kpi)
                            <td style="width:25%;border:1px solid #e5e7eb;padding:8px;vertical-align:top;">
                                <div style="font-size:8px;color:#9ca3af;text-transform:uppercase;">{{ $kpi[0] }}</div>
                                <div style="font-size:12px;font-weight:bold;color:#111827;">{{ $kpi[1] }}</div>
                            </td>
                        @endforeach
                    </tr>
                </table>
            @endif
        @else
            @php $sec = $relatorio['secoes'][$chave] ?? null; @endphp
            @if ($sec)
                <h2 style="font-size:11px;font-weight:bold;color:#374151;margin:14px 0 4px;">{{ $sec['titulo'] }}</h2>
                @if (isset($barras[$chave]) && ! empty($sec['linhas']))
                    @include('reports.partials._bar-chart', ['itens' => $svc->barChartItens($sec['linhas'], $barras[$chave][0], $barras[$chave][1], $barras[$chave][2])])
                @endif
                @include('reports.bi-executivo-tabela', ['sec' => $sec])
            @endif
        @endif
    @endforeach
@endsection
