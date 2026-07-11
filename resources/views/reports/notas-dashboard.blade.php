@extends('reports.layout')

@php
    use App\Services\Notas\Export\DashboardNotasReportBuilder as Builder;
    use App\Support\Reports\XlsxReport;

    $p = $relatorio['periodo'];
    $k = $relatorio['kpis'];
    $s = $relatorio['saldos'];

    $brl = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $int = fn ($v) => number_format((float) $v, 0, ',', '.');

    // Formata a célula conforme o formato declarado pela seção (mesma fonte do XLSX):
    // sem isso o PDF reimplementaria as regras de moeda/percentual coluna a coluna.
    $celula = function ($valor, ?string $formato) use ($brl, $int) {
        if ($formato === XlsxReport::FMT_BRL) {
            return $brl($valor);
        }
        if ($formato === XlsxReport::FMT_PCT) {
            return number_format((float) $valor, 1, ',', '.').'%';
        }
        if ($formato === XlsxReport::FMT_INT) {
            return $int($valor);
        }

        return $valor === '' || $valor === null ? '—' : $valor;
    };

    $numerica = fn (?string $formato) => in_array($formato, [XlsxReport::FMT_BRL, XlsxReport::FMT_PCT, XlsxReport::FMT_INT], true);
@endphp

@section('titulo', $relatorio['titulo'])
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('notas-dashboard', $p['inicio'], $p['fim'], json_encode($relatorio['filtros'])))

@section('meta')
    @foreach ($relatorio['filtros'] as $rotulo => $valor)
        <div>{{ $rotulo }}: {{ $valor }}</div>
    @endforeach
@endsection

@section('conteudo')
    {{-- Indicadores do acervo --}}
    <div class="secao">
        <div class="secao-header">Indicadores do acervo</div>
        <div class="secao-body">
            @include('reports.partials._kpi-strip', ['itens' => [
                ['label' => 'Notas no recorte', 'valor' => $int($k['total_notas'])],
                ['label' => 'Aquisições', 'valor' => $brl($k['valor_entradas'])],
                ['label' => 'Faturamento', 'valor' => $brl($k['valor_saidas'])],
                ['label' => 'Saldo líquido', 'valor' => $brl($k['saldo'])],
                ['label' => 'Contrapartes', 'valor' => $int($k['participantes_unicos'])],
                ['label' => 'Saldo ICMS', 'valor' => $brl($s['icms']['saldo'])],
                ['label' => 'Saldo PIS', 'valor' => $brl($s['pis']['saldo'])],
                ['label' => 'Saldo COFINS', 'valor' => $brl($s['cofins']['saldo'])],
            ], 'compacto' => true])
        </div>
    </div>

    @php
        $cKpis = $relatorio['compliance_kpis'];
        $rAlertas = $relatorio['resumo_alertas'];
    @endphp

    {{-- Ressalvas de leitura: sem isso o contador toma número incompleto por número certo. --}}
    @if ($relatorio['alerta_pis_cofins'] || ($cKpis['nao_consultados'] ?? 0) > 0 || ($cKpis['exposicao'] ?? 0) > 0)
        <div style="background-color:#fffbeb;border:1px solid #fde68a;padding:8px;margin-bottom:12px;">
            @if ($relatorio['alerta_pis_cofins'])
                <span style="color:#92400e;font-size:9px;display:block;">
                    &#9888; Mais de 70% dos itens da EFD Contribuições estão sem PIS/COFINS — os saldos desses tributos estão subestimados.
                </span>
            @endif
            @if (($cKpis['exposicao'] ?? 0) > 0)
                <span style="color:#92400e;font-size:9px;display:block;">
                    &#9888; {{ $cKpis['irregulares'] }} contraparte(s) com situação cadastral irregular — {{ $brl($cKpis['exposicao']) }} movimentados.
                </span>
            @endif
            @if (($cKpis['nao_consultados'] ?? 0) > 0)
                <span style="color:#92400e;font-size:9px;display:block;">
                    &#9888; {{ $cKpis['nao_consultados'] }} de {{ $cKpis['total'] }} contrapartes nunca consultadas — risco cadastral não avaliado.
                </span>
            @endif
        </div>
    @endif

    @if ($rAlertas['total'] > 0)
        <div class="secao">
            <div class="secao-header">Alertas por severidade</div>
            <div class="secao-body">
                @include('reports.partials._kpi-strip', ['itens' => [
                    ['label' => 'Alta', 'valor' => $rAlertas['alta']],
                    ['label' => 'Média', 'valor' => $rAlertas['media']],
                    ['label' => 'Baixa', 'valor' => $rAlertas['baixa']],
                    ['label' => 'Total', 'valor' => $rAlertas['total']],
                ], 'compacto' => true])
            </div>
        </div>
    @endif

    {{-- Seções --}}
    @foreach ($relatorio['ordem_secoes'] as $chave)
        @php
            $sec = $relatorio['secoes'][$chave];
            $formatos = $sec['formatos'] ?? [];
            $linhas = $sec['linhas'];
            $truncada = count($linhas) > Builder::LIMITE_PDF;
            $visiveis = $truncada ? array_slice($linhas, 0, Builder::LIMITE_PDF) : $linhas;
            $cores = $sec['cores'] ?? [];
        @endphp

        {{-- Sem page-break-inside:avoid: seções com dezenas de linhas precisam quebrar
             entre páginas; o dompdf já quebra por linha de tabela. --}}
        <div class="secao">
            <div class="secao-header">
                {{ $sec['titulo'] }}
                @if ($truncada)
                    <span class="meta">{{ Builder::LIMITE_PDF }} de {{ count($linhas) }} linhas — planilha traz tudo</span>
                @endif
            </div>
            <div class="secao-body">
                @if (! empty($sec['nota']))
                    <div class="small muted" style="margin-bottom:6px;">{{ $sec['nota'] }}</div>
                @endif

                @if (empty($linhas))
                    <span style="font-size:8px;color:#9ca3af;">Sem dados no recorte.</span>
                @else
                    @if (! empty($sec['grafico']))
                        <div style="margin-bottom:10px;">
                            @if ($sec['grafico']['tipo'] === 'colunas')
                                @include('reports.partials._column-chart', [
                                    'colunas' => $sec['grafico']['colunas'],
                                    'legenda' => $sec['grafico']['legenda'] ?? [],
                                ])
                            @elseif ($sec['grafico']['tipo'] === 'stacked')
                                @include('reports.partials._stacked-bar', ['itens' => $sec['grafico']['itens']])
                            @else
                                @include('reports.partials._bar-chart', ['itens' => $sec['grafico']['itens']])
                            @endif
                        </div>
                    @endif

                    <table class="table">
                        <thead>
                            <tr>
                                @foreach ($sec['colunas'] as $i => $col)
                                    <th class="{{ $numerica($formatos[$i] ?? null) ? 'right' : '' }}">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($visiveis as $idx => $linha)
                                <tr>
                                    @foreach ($linha as $i => $valor)
                                        @php $hex = $cores[$idx][$i] ?? null; @endphp
                                        <td class="{{ $numerica($formatos[$i] ?? null) ? 'right' : '' }}">
                                            @if ($hex)
                                                <span class="badge" style="background-color:{{ $hex }}">{{ $valor }}</span>
                                            @else
                                                {{ $celula($valor, $formatos[$i] ?? null) }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            @if (! empty($sec['total']) && ! $truncada)
                                <tr>
                                    @foreach ($sec['total'] as $i => $valor)
                                        <td class="{{ $numerica($formatos[$i] ?? null) ? 'right' : '' }}" style="font-weight:bold;background:#f3f4f6;border-top:1.5px solid #1f2937;">
                                            {{ $valor === '' ? '' : $celula($valor, $formatos[$i] ?? null) }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endforeach
@endsection
