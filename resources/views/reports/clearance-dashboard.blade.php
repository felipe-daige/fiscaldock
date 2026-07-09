@extends('reports.layout')

@php
    use App\Services\Clearance\Export\ClearanceDashboardReportBuilder as Builder;
    use App\Support\Reports\XlsxReport;

    $r = $relatorio['resumo'];
    $b = $relatorio['backlog'];

    $brl = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $int = fn ($v) => number_format((float) $v, 0, ',', '.');

    // Formata a célula conforme o formato declarado pela seção (mesma fonte do XLSX).
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
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('clearance-dashboard', $r['total_notas'], $r['notas_bloqueantes']))

@section('meta')
    @foreach ($relatorio['filtros'] as $rotulo => $valor)
        <div>{{ $rotulo }}: {{ $valor }}</div>
    @endforeach
@endsection

@section('conteudo')
    {{-- Posição na Receita --}}
    <div class="secao">
        <div class="secao-header">Posição na Receita Federal</div>
        <div class="secao-body">
            @include('reports.partials._kpi-strip', ['itens' => [
                ['label' => 'Notas no acervo', 'valor' => $int($r['total_notas'])],
                ['label' => 'Verificadas', 'valor' => $int($r['verificadas'])],
                ['label' => 'Pendentes', 'valor' => $int($r['pendentes'])],
                ['label' => 'Cobertura', 'valor' => number_format((float) $r['cobertura_pct'], 1, ',', '.').'%'],
                ['label' => 'Valor movimentado', 'valor' => $brl($r['valor_total'])],
                ['label' => 'Bloqueantes', 'valor' => $int($r['notas_bloqueantes'])],
                ['label' => 'Exposição bloqueante', 'valor' => $brl($r['valor_bloqueante'])],
            ], 'compacto' => true])
        </div>
    </div>

    {{-- Ressalvas acionáveis: risco escriturado + custo pra fechar o gap de verificação. --}}
    @if (($r['notas_bloqueantes'] ?? 0) > 0 || ($b['notas'] ?? 0) > 0)
        <div style="background-color:#fffbeb;border:1px solid #fde68a;padding:8px;margin-bottom:12px;">
            @if (($r['notas_bloqueantes'] ?? 0) > 0)
                <span style="color:#92400e;font-size:9px;display:block;">
                    &#9888; {{ $int($r['notas_bloqueantes']) }} nota(s) cancelada/denegada/inutilizada ainda escriturada(s) — {{ $brl($r['valor_bloqueante']) }} em risco fiscal.
                </span>
            @endif
            @if (($b['notas'] ?? 0) > 0)
                <span style="color:#92400e;font-size:9px;display:block;">
                    &#9888; {{ $int($b['notas']) }} nota(s) sem verificação na Receita — verificar todas custa ~{{ $int($b['custo_creditos']) }} créditos ({{ $brl($b['custo_reais']) }}).
                </span>
            @endif
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
                    <span style="font-size:8px;color:#9ca3af;">Sem dados no acervo.</span>
                @else
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
                                        <td class="{{ $numerica($formatos[$i] ?? null) ? 'right' : '' }} {{ $sec['colunas'][$i] === 'Chave de acesso' ? 'mono' : '' }}">
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
