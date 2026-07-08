@extends('reports.layout')

@php
    use App\Services\Catalogo\Export\CatalogoReportBuilder as Builder;
    use App\Support\Reports\XlsxReport;

    $k = $relatorio['kpis'];

    $brl = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $int = fn ($v) => number_format((float) $v, 0, ',', '.');

    // Formata a célula conforme o formato declarado pela seção (mesma fonte do XLSX).
    $celula = function ($valor, ?string $formato) use ($brl, $int) {
        if ($valor === null) {
            return '—';
        }
        if ($formato === XlsxReport::FMT_BRL) {
            return $brl($valor);
        }
        if ($formato === XlsxReport::FMT_PCT) {
            return number_format((float) $valor, 2, ',', '.').'%';
        }
        if ($formato === XlsxReport::FMT_INT) {
            return $int($valor);
        }

        return $valor === '' ? '—' : $valor;
    };

    $numerica = fn (?string $formato) => in_array($formato, [XlsxReport::FMT_BRL, XlsxReport::FMT_PCT, XlsxReport::FMT_INT], true);
@endphp

@section('titulo', $relatorio['titulo'])
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('catalogo', json_encode($relatorio['filtros'])))

@section('meta')
    @foreach ($relatorio['filtros'] as $rotulo => $valor)
        <div>{{ $rotulo }}: {{ $valor }}</div>
    @endforeach
@endsection

@section('conteudo')
    {{-- Resumo fiscal do catálogo --}}
    <div class="secao">
        <div class="secao-header">Resumo fiscal do catálogo</div>
        <div class="secao-body">
            @include('reports.partials._kpi-strip', ['itens' => [
                ['label' => 'Total de produtos', 'valor' => $int($k['total_produtos'])],
                ['label' => 'Com movimentação', 'valor' => $int($k['com_movimentacao'])],
                ['label' => 'Sem movimentação', 'valor' => $int($k['sem_movimentacao'])],
                ['label' => 'Valor movimentado', 'valor' => $brl($k['valor_movimentado'])],
                ['label' => 'Alíq. divergente', 'valor' => $int($k['aliq_divergente'])],
                ['label' => 'NCM faltando', 'valor' => $int($k['ncm_faltando'])],
            ], 'compacto' => true])
        </div>
    </div>

    @if (($k['ncm_faltando'] ?? 0) > 0 || ($k['aliq_divergente'] ?? 0) > 0)
        <div style="background-color:#fffbeb;border:1px solid #fde68a;padding:8px;margin-bottom:12px;">
            @if (($k['ncm_faltando'] ?? 0) > 0)
                <span style="color:#92400e;font-size:9px;display:block;">
                    &#9888; {{ $int($k['ncm_faltando']) }} mercadoria(s)/produto(s) (tipo 00–06) sem NCM no cadastro 0200 — gap fiscal.
                </span>
            @endif
            @if (($k['aliq_divergente'] ?? 0) > 0)
                <span style="color:#92400e;font-size:9px;display:block;">
                    &#9888; {{ $int($k['aliq_divergente']) }} item(ns) com alíquota de ICMS do cadastro divergente da praticada nas notas.
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
                    <span style="font-size:8px;color:#9ca3af;">Sem dados no recorte.</span>
                @else
                    @if (! empty($sec['grafico']))
                        <div style="margin-bottom:10px;">
                            @if ($sec['grafico']['tipo'] === 'stacked')
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
