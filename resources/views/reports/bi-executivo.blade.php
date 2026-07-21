@extends('reports.layout')

@section('titulo', 'BI Fiscal — Relatório Executivo')
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('bi', json_encode($relatorio['periodo'] ?? [])))

@php
    $p = $relatorio['periodo'];
    $modo = $relatorio['modo'] ?? 'portfolio';
@endphp

@section('meta')
    <div>{{ $modo === 'cliente' ? 'Cliente #'.$p['cliente_id'] : 'Carteira (todos os clientes)' }}</div>
    <div>Período: {{ $p['inicio'] ?? 'Todos' }} a {{ $p['fim'] ?? 'Todos' }}</div>
@endsection

@push('estilos')
    @include('reports.dossie._estilos')
@endpush

@section('conteudo')
    @php
        $k = $relatorio['kpis'];
        $cob = $relatorio['cobertura'] ?? ['parcial' => false];
        $svc = app(\App\Services\BiExportService::class);
        // Seções que ganham barras CSS (idxLabel, idxValorBrl, hex) — casados às colunas atuais
        $barras = [
            'cfop' => [0, 2, '#7c3aed'],
        ];
        // Parse de BRL "1.234,56" → float, reusado pelos gráficos das seções.
        $parseBrl = fn ($brl) => (float) str_replace(',', '.', str_replace('.', '', (string) $brl));
        // UF e Devoluções têm layout próprio (tabela única com barra inline) — ver branch abaixo.
        $barrasInline = [
            'uf' => [0, 1, '#0891b2'],
            'devolucoes' => [0, 1, '#be185d'],
        ];
    @endphp

    {{-- Indicadores do período (KPIs) --}}
    <div class="secao">
        <div class="secao-header">Indicadores do período</div>
        <div class="secao-body">
            @include('reports.partials._kpi-strip', ['itens' => [
                ['label' => 'Faturamento', 'valor' => 'R$ '.$k['faturamento']],
                ['label' => 'Aquisições', 'valor' => 'R$ '.$k['aquisicoes']],
                ['label' => 'Tributos (débito s/ saída)', 'valor' => 'R$ '.$k['tributos']],
                ['label' => 'A recolher (apurado)', 'valor' => 'R$ '.($relatorio['a_recolher_brl'] ?? '0,00')],
                ['label' => 'Saldo líquido', 'valor' => 'R$ '.$k['saldo_liquido']],
            ]])
        </div>
    </div>

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
                <div class="secao">
                    <div class="secao-header">Score da carteira</div>
                    <div class="secao-body">
                        @include('reports.partials._kpi-strip', ['itens' => [
                            ['label' => '% Regular', 'valor' => $sc['percentual_regular'].'%'],
                            ['label' => 'Irregulares', 'valor' => $sc['irregulares'].' / '.$sc['participantes_ativos']],
                            ['label' => '% Em risco', 'valor' => $sc['percentual_em_risco'].'%'],
                            ['label' => 'Valor em risco', 'valor' => 'R$ '.$sc['valor_total_em_risco_brl']],
                        ]])
                    </div>
                </div>
            @endif
        @elseif ($chave === 'contrapartes')
            @php $sec = $relatorio['secoes']['contrapartes'] ?? null; @endphp
            @if ($sec && ! empty($sec['itens']))
                @php
                    $itens = $sec['itens'];
                    $maxVol = collect($itens)->max('volume') ?: 0;
                    $temPapel = ($sec['modo'] ?? '') === 'cliente';
                @endphp
                <div class="secao">
                    <div class="secao-header">{{ $sec['titulo'] }}</div>
                    <div class="secao-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    @if ($temPapel)<th>Papel</th>@endif
                                    <th>CNPJ</th>
                                    <th>Razão social</th>
                                    <th class="center">Score</th>
                                    <th class="right">Volume</th>
                                    <th class="right">Notas</th>
                                    <th>Principais CFOPs</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($itens as $it)
                                    @php $hex = \App\Support\Reports\ReportTheme::riscoHex($it['classificacao']); @endphp
                                    <tr>
                                        @if ($temPapel)<td>{{ $it['papel'] }}</td>@endif
                                        <td class="mono">{{ $it['cnpj'] }}</td>
                                        <td>{{ $it['razao'] }}</td>
                                        <td class="center">
                                            @if ($it['classificacao'])
                                                <span class="badge" style="background-color:{{ $hex }}">{{ $it['classificacao'] === 'inconclusivo' ? 'não conclusivo' : $it['classificacao'].($it['score_total'] !== null ? ' '.$it['score_total'] : '') }}</span>
                                            @elseif (\App\Support\Documento::ehCpf($it['cnpj']))
                                                {{-- CPF não é consultável como PJ → marcar pessoa física, não "nunca consultado". --}}
                                                <span class="badge" style="background-color:{{ \App\Support\Reports\ReportTheme::OUTRO }}">CPF</span>
                                            @else
                                                <span class="badge" style="background-color:#9ca3af">nunca consultado</span>
                                            @endif
                                        </td>
                                        <td class="right">
                                            <div style="font-weight:bold;">R$&nbsp;{{ $it['volume_brl'] }}</div>
                                            <div style="background:#f3f4f6;height:5px;width:100%;">
                                                <div style="background-color:#2563eb;height:5px;width:{{ $maxVol > 0 ? (int) round($it['volume'] / $maxVol * 100) : 0 }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="right">{{ $it['notas'] }}</td>
                                        <td class="small">{{ count($it['cfops']) ? implode(' · ', $it['cfops']) : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @elseif ($chave === 'faturamento')
            @php $sec = $relatorio['secoes']['faturamento'] ?? null; @endphp
            @if ($sec && ! empty($sec['linhas']))
                @php
                    // colunas: 0 Mês, 1 Faturamento, 2 Qtd Notas
                    $colsChart = array_map(fn ($l) => [
                        'label' => $l[0],
                        'series' => [['valor' => $parseBrl($l[1]), 'hex' => '#2563eb']],
                    ], $sec['linhas']);
                @endphp
                <div class="secao">
                    <div class="secao-header">{{ $sec['titulo'] }} <span class="meta">evolução mensal</span></div>
                    <div class="secao-body">
                        @include('reports.partials._column-chart', ['colunas' => $colsChart, 'legenda' => [['label' => 'Faturamento', 'hex' => '#2563eb']], 'altura' => 80])
                        <div style="height:8px;"></div>
                        @include('reports.bi-executivo-tabela', ['sec' => $sec])
                    </div>
                </div>
            @endif
        @elseif ($chave === 'tributos')
            @php $sec = $relatorio['secoes']['tributos'] ?? null; @endphp
            @if ($sec && ! empty($sec['linhas']))
                @php
                    // colunas: 0 Mês, 1 Faturamento, 2 ICMS, 3 PIS, 4 COFINS, 5 Total Tributos, 6 Alíq%
                    $icms = array_sum(array_map(fn ($l) => $parseBrl($l[2]), $sec['linhas']));
                    $pis = array_sum(array_map(fn ($l) => $parseBrl($l[3]), $sec['linhas']));
                    $cofins = array_sum(array_map(fn ($l) => $parseBrl($l[4]), $sec['linhas']));
                    $tot = max(0.0001, $icms + $pis + $cofins);
                    $fmt = fn ($v) => 'R$ '.number_format($v, 2, ',', '.');
                    $mix = [
                        ['label' => 'ICMS', 'pct' => round($icms / $tot * 100, 1), 'valor' => $fmt($icms), 'hex' => '#b45309'],
                        ['label' => 'PIS', 'pct' => round($pis / $tot * 100, 1), 'valor' => $fmt($pis), 'hex' => '#0891b2'],
                        ['label' => 'COFINS', 'pct' => round($cofins / $tot * 100, 1), 'valor' => $fmt($cofins), 'hex' => '#7c3aed'],
                    ];
                    $colsChart = array_map(fn ($l) => [
                        'label' => $l[0],
                        'series' => [['valor' => $parseBrl($l[5]), 'hex' => '#b45309']],
                    ], $sec['linhas']);
                @endphp
                <div class="secao">
                    <div class="secao-header">{{ $sec['titulo'] }} <span class="meta">evolução + composição</span></div>
                    <div class="secao-body">
                        @include('reports.partials._column-chart', ['colunas' => $colsChart, 'legenda' => [['label' => 'Total tributos', 'hex' => '#b45309']], 'altura' => 70])
                        <div style="font-weight:bold;font-size:8px;color:#374151;margin:10px 0 4px;">Composição dos tributos (acumulado)</div>
                        @include('reports.partials._stacked-bar', ['itens' => $mix])
                        <div style="height:8px;"></div>
                        @include('reports.bi-executivo-tabela', ['sec' => $sec])
                    </div>
                </div>
            @endif
        @elseif (isset($barrasInline[$chave]))
            @php
                $sec = $relatorio['secoes'][$chave] ?? null;
                [$iLabel, $iVal, $hex] = $barrasInline[$chave];
                $cc = $relatorio['cobertura_consulta'] ?? ['sem_uf' => 0];
                // Largura da barra: parse do BRL "1.234,56" → float, relativo ao máximo da série.
                $parse = fn ($brl) => (float) str_replace(',', '.', str_replace('.', '', (string) $brl));
                $maxVal = ! empty($sec['linhas']) ? collect($sec['linhas'])->max(fn ($l) => $parse($l[$iVal])) : 0;
            @endphp
            @if ($sec && ! empty($sec['linhas']))
                <div class="secao">
                    <div class="secao-header">{{ $sec['titulo'] }}</div>
                    <div class="secao-body">
                        @if ($chave === 'uf' && $modo === 'portfolio' && ($cc['sem_uf'] ?? 0) > 0)
                            <div style="background-color:#fffbeb;border:1px solid #fde68a;padding:6px;font-size:9px;color:#92400e;margin-bottom:6px;">
                                &#9888; {{ $cc['sem_uf'] }} participantes sem UF ({{ $cc['sem_uf_cnpj'] ?? 0 }} CNPJ, {{ $cc['sem_uf_cpf'] ?? 0 }} CPF) — distribuição geográfica incompleta. CPF não tem UF de estabelecimento (esperado); consulte os {{ $cc['sem_uf_cnpj'] ?? 0 }} CNPJ para enriquecer.
                            </div>
                        @endif
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ $sec['colunas'][$iLabel] }}</th>
                                    <th class="right" style="width:50%;">{{ $sec['colunas'][$iVal] }}</th>
                                    <th class="right" style="width:14%;">{{ $sec['colunas'][2] ?? 'Qtd' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sec['linhas'] as $l)
                                    @php $w = $maxVal > 0 ? (int) round($parse($l[$iVal]) / $maxVal * 100) : 0; @endphp
                                    <tr>
                                        <td>{{ $l[$iLabel] }}</td>
                                        <td class="right">
                                            <div style="font-weight:bold;">R$&nbsp;{{ $l[$iVal] }}</div>
                                            <div style="background:#f3f4f6;height:5px;width:100%;">
                                                <div style="background-color:{{ $hex }};height:5px;width:{{ $w }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="right">{{ $l[2] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @elseif ($chave === 'catalogo')
            @php
                $sec = $relatorio['secoes']['catalogo'] ?? null;
                // O catálogo completo pode ter milhares de itens (planilhas levam tudo);
                // no PDF executivo cortamos no topo por valor pra não estourar páginas/memória.
                $capPdf = 100;
                $total = $sec ? count($sec['linhas']) : 0;
                $visiveis = $total > $capPdf ? array_slice($sec['linhas'], 0, $capPdf) : ($sec['linhas'] ?? []);
            @endphp
            @if ($sec)
                <div class="secao">
                    <div class="secao-header">{{ $sec['titulo'] }}
                        @if ($total > $capPdf)<span class="meta">{{ $capPdf }} de {{ $total }} — planilhas trazem tudo</span>@endif
                    </div>
                    <div class="secao-body">
                        @include('reports.bi-executivo-tabela', ['sec' => ['colunas' => $sec['colunas'], 'linhas' => $visiveis]])
                    </div>
                </div>
            @endif
        @elseif ($chave === 'dossie-participantes')
            @php $sec = $relatorio['secoes']['dossie-participantes'] ?? null; @endphp
            @if ($sec && ! empty($sec['linhas']))
                <div class="secao">
                    <div class="secao-header">{{ $sec['titulo'] }} <span class="meta">{{ count($sec['linhas']) }} participantes</span></div>
                    <div class="secao-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="white-space:nowrap;">CNPJ/CPF</th>
                                    <th>Razão social</th>
                                    <th class="center">UF</th>
                                    <th class="right">Notas</th>
                                    <th class="right" style="white-space:nowrap;">Entradas (qtd · R$)</th>
                                    <th class="right" style="white-space:nowrap;">Saídas (qtd · R$)</th>
                                    <th class="right">Movimentado</th>
                                    <th class="center">Risco</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sec['linhas'] as $i => $l)
                                    @php
                                        $cls = $sec['classificacoes'][$i] ?? null;
                                        $hex = \App\Support\Reports\ReportTheme::riscoHex($cls);
                                        // Score só faz sentido em risco real; inconclusivo/nunca consultado ficam sem número.
                                        $comScore = $cls && $cls !== 'inconclusivo' && $l[10] !== '—';
                                    @endphp
                                    <tr>
                                        <td class="mono">{{ $l[1] }}</td>
                                        <td>{{ $l[0] }}</td>
                                        <td class="center">{{ $l[2] }}</td>
                                        <td class="right">{{ $l[3] }}</td>
                                        <td class="right">{{ $l[5] }} · {{ $l[6] }}</td>
                                        <td class="right">{{ $l[7] }} · {{ $l[8] }}</td>
                                        <td class="right">R$&nbsp;{{ $l[4] }}</td>
                                        <td class="center">
                                            <span class="badge" style="background-color:{{ $hex }}">{{ $l[11] }}{{ $comScore ? ' '.$l[10] : '' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @else
            @php
                $sec = $relatorio['secoes'][$chave] ?? null;
                $cc = $relatorio['cobertura_consulta'] ?? ['total' => 0, 'sem_consulta' => 0, 'sem_uf' => 0];
            @endphp
            @if ($sec)
                <div class="secao">
                    <div class="secao-header">{{ $sec['titulo'] }}</div>
                    <div class="secao-body">
                        @if (in_array($chave, ['riscos-notas', 'riscos-fornecedores'], true) && empty($sec['linhas']) && ($cc['sem_consulta'] ?? 0) > 0)
                            <div style="background-color:#fffbeb;border:1px solid #fde68a;padding:6px;font-size:9px;color:#92400e;">
                                &#9888; {{ $cc['sem_consulta'] }} de {{ $cc['total'] }} participantes nunca consultados ({{ $cc['sem_consulta_cnpj'] ?? 0 }} CNPJ, {{ $cc['sem_consulta_cpf'] ?? 0 }} CPF) — risco não avaliado (sem dado de certidão/cadastro).
                            </div>
                        @endif

                        @if ($chave === 'uf' && $modo === 'portfolio' && ($cc['sem_uf'] ?? 0) > 0)
                            <div style="background-color:#fffbeb;border:1px solid #fde68a;padding:6px;font-size:9px;color:#92400e;margin-bottom:6px;">
                                &#9888; {{ $cc['sem_uf'] }} participantes sem UF ({{ $cc['sem_uf_cnpj'] ?? 0 }} CNPJ, {{ $cc['sem_uf_cpf'] ?? 0 }} CPF) — distribuição geográfica incompleta. CPF não tem UF de estabelecimento (esperado); consulte os {{ $cc['sem_uf_cnpj'] ?? 0 }} CNPJ para enriquecer.
                            </div>
                        @endif

                        @if (isset($barras[$chave]) && ! empty($sec['linhas']))
                            @include('reports.partials._bar-chart', ['itens' => $svc->barChartItens($sec['linhas'], $barras[$chave][0], $barras[$chave][1], $barras[$chave][2])])
                        @endif
                        @include('reports.bi-executivo-tabela', ['sec' => $sec])
                    </div>
                </div>
            @endif
        @endif
    @endforeach

    @if (! empty($dossies))
        <div class="secao" style="page-break-before:always;">
            <div class="secao-header">Dossiês</div>
        </div>

        @if (! empty($dossies['clientes']))
            <div class="secao-header" style="background:#374151;letter-spacing:.06em;">Clientes</div>
            @foreach ($dossies['clientes'] as $d)
                <div style="{{ $loop->first ? '' : 'page-break-before:always;' }}">
                    @include('reports.dossie._bloco', array_merge($d, ['participante' => $d['cliente']]))
                </div>
            @endforeach
        @endif

        @if (! empty($dossies['participantes']))
            <div class="secao-header" style="background:#374151;letter-spacing:.06em;page-break-before:always;">Participantes</div>
            @foreach ($dossies['participantes'] as $d)
                <div style="{{ $loop->first ? '' : 'page-break-before:always;' }}">
                    @include('reports.dossie._bloco', $d)
                </div>
            @endforeach
        @endif
    @endif
@endsection
