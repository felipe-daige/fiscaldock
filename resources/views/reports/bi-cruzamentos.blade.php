@extends('reports.layout')

@php
    use App\Services\Bi\Export\CruzamentosReportBuilder as Builder;

    $k = $relatorio['kpis'];
    $parecer = $relatorio['parecer'];
    $irregulares = $relatorio['irregulares'];
    $canceladas = $relatorio['canceladas'];
    $providencias = $relatorio['providencias'];

    $irregularesTrunc = count($irregulares) > Builder::LIMITE_PDF;
    $canceladasTrunc = count($canceladas) > Builder::LIMITE_PDF;
    $irregularesVis = $irregularesTrunc ? array_slice($irregulares, 0, Builder::LIMITE_PDF) : $irregulares;
    $canceladasVis = $canceladasTrunc ? array_slice($canceladas, 0, Builder::LIMITE_PDF) : $canceladas;

    $moeda = fn ($v) => $v === null ? '—' : 'R$ '.number_format((float) $v, 2, ',', '.');
@endphp

@section('titulo', 'Cruzamentos Fiscais')
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('bi-cruzamentos', auth()->id(), json_encode($relatorio['filtros'])))

@section('meta')
    @foreach ($relatorio['filtros'] as $rotulo => $valor)
        <div>{{ $rotulo }}: {{ $valor }}</div>
    @endforeach
@endsection

@push('estilos')
<style>
    .cx-table th, .cx-table td { font-size:7px; padding:3px 4px; }
    .cx-motivo { color:#b91c1c; }
    .parecer { border:1px solid #e5e7eb; border-left-width:4px; padding:8px 10px; margin-bottom:12px; }
    .parecer-label { font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:.08em; margin-bottom:3px; }
    .parecer-texto { font-size:8.5px; color:#374151; line-height:1.5; }
    .prov-lista { list-style:none; }
    .prov-lista li { font-size:8.5px; color:#374151; padding:4px 0 4px 16px; border-bottom:1px solid #f3f4f6; position:relative; line-height:1.4; }
    .prov-check { position:absolute; left:0; top:3px; width:9px; height:9px; border:1px solid #9ca3af; border-radius:2px; }
</style>
@endpush

@section('conteudo')
    {{-- Parecer executivo — derivado por regra dos mesmos números da tela --}}
    <div class="parecer" style="border-left-color:{{ $parecer['hex'] }};">
        <div class="parecer-label" style="color:{{ $parecer['hex'] }};">Parecer: {{ $parecer['label'] }}</div>
        <div class="parecer-texto">{{ $parecer['texto'] }}</div>
    </div>

    {{-- Resumo do recorte --}}
    <div class="secao">
        <div class="secao-header">Resumo do recorte</div>
        <div class="secao-body">
            @include('reports.partials._kpi-strip', ['itens' => [
                ['label' => 'Fornecedores irregulares', 'valor' => number_format($k['irregulares_qtd'], 0, ',', '.')],
                ['label' => 'Compras de irregulares', 'valor' => $moeda($k['irregulares_valor'])],
                ['label' => 'Notas canceladas (SEFAZ)', 'valor' => number_format($k['canceladas_qtd'], 0, ',', '.')],
                ['label' => 'CNPJs consultados', 'valor' => number_format($relatorio['diagnostico']['consultados_qtd'], 0, ',', '.')],
                ['label' => 'Fornec. consultados', 'valor' => number_format($relatorio['diagnostico']['fornecedores_consultados_qtd'], 0, ',', '.')],
            ], 'compacto' => true])
            <div class="small muted" style="margin-top:6px;">
                <b>Irregular</b> = a última consulta do CNPJ apontou certidão positiva (CND Federal ou Estadual com débito)
                ou situação cadastral não ativa. Mesma classificação do Score de Risco e da Central de Alertas.
                <b>Compras</b> = entradas do EFD (sem dupla contagem ICMS/IPI × PIS/COFINS) + entradas de XML importado
                (exceto devoluções; nota já no EFD não soma de novo). A data de cada consulta é registrada como prova de diligência.
            </div>
        </div>
    </div>

    {{-- 1. Fornecedor irregular × compras --}}
    <div class="secao">
        <div class="secao-header">
            Fornecedor irregular × compras
            @if ($irregularesTrunc)
                <span class="meta">{{ Builder::LIMITE_PDF }} de {{ count($irregulares) }} linhas — planilhas trazem tudo</span>
            @endif
        </div>
        <div class="secao-body">
            @if (empty($irregulares))
                <span class="small muted">Nenhum fornecedor com certidão ou situação irregular entre os que você comprou.</span>
            @else
                <table class="table cx-table">
                    <thead>
                        <tr>
                            <th style="width:32%;">Fornecedor</th>
                            <th style="width:30%;">Motivo (última consulta)</th>
                            <th class="right" style="width:15%;">Comprado</th>
                            <th class="right" style="width:8%;">Notas</th>
                            <th style="width:15%;">Consultado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($irregularesVis as $linha)
                            <tr>
                                <td>
                                    {{ $linha['razao_social'] }}<br>
                                    <span class="mono muted">{{ $linha['cnpj'] }}</span>
                                </td>
                                <td class="cx-motivo">{{ implode(' · ', $linha['motivos']) }}</td>
                                <td class="right">{{ $moeda($linha['valor_comprado']) }}</td>
                                <td class="right">{{ $linha['qtd_notas'] }}</td>
                                <td>{{ $linha['ultima_consulta'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- 2. Notas canceladas na SEFAZ × emitente --}}
    <div class="secao">
        <div class="secao-header">
            Notas canceladas na SEFAZ × emitente
            @if ($canceladasTrunc)
                <span class="meta">{{ Builder::LIMITE_PDF }} de {{ count($canceladas) }} linhas</span>
            @endif
        </div>
        <div class="secao-body">
            @if (empty($canceladas))
                <span class="small muted">Nenhuma nota do acervo consta cancelada na SEFAZ no recorte.</span>
            @else
                <table class="table cx-table">
                    <thead>
                        <tr>
                            <th style="width:10%;">Nº</th>
                            <th style="width:34%;">Emitente</th>
                            <th class="right" style="width:14%;">Valor</th>
                            <th style="width:27%;">Situação do emitente</th>
                            <th style="width:15%;">Verificado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($canceladasVis as $linha)
                            <tr>
                                <td>{{ $linha['numero'] }}</td>
                                <td>
                                    {{ $linha['emit_nome'] }}<br>
                                    <span class="mono muted">{{ $linha['emit_cnpj'] }}</span>
                                </td>
                                <td class="right">{{ $moeda($linha['valor']) }}</td>
                                <td>{{ $linha['situacao_emitente'] }}</td>
                                <td>{{ $linha['verificado_em'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- 3. Providências --}}
    @if (! empty($providencias))
        <div class="secao">
            <div class="secao-header">Providências sugeridas <span class="meta">derivadas dos achados</span></div>
            <div class="secao-body">
                <ul class="prov-lista">
                    @foreach ($providencias as $item)
                        <li><span class="prov-check"></span>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- 4. Metodologia / auditabilidade --}}
    <div class="secao">
        <div class="secao-header">Metodologia e fontes <span class="meta">auditabilidade</span></div>
        <div class="secao-body small muted" style="line-height:1.6;">
            <b>Regularidade dos fornecedores:</b> lida de <span class="mono">participante_scores</span>, a projeção da última
            consulta de CNPJ — a mesma fonte do Score de Risco e da Central de Alertas, por isso os números nunca divergem entre as telas.
            Um fornecedor é irregular quando a última consulta apontou certidão positiva (CND Federal ou Estadual) ou situação
            cadastral BAIXADA, INAPTA, SUSPENSA ou NULA.<br>
            <b>Volume de compras:</b> entradas do SPED (EFD), deduplicadas pela regra canônica de movimento por participante
            (a mesma nota escriturada no ICMS/IPI e no PIS/COFINS conta uma vez), somadas às entradas de XML importado cujo emitente
            é o fornecedor (exceto devoluções); quando a chave já existe no EFD, o XML não é contado de novo.<br>
            <b>Notas canceladas:</b> snapshot da SEFAZ em <span class="mono">nfe_consultas</span> (via Clearance), cruzado com a
            situação do emitente na última consulta. Cobertura esparsa — só aparece o que já passou pelo Clearance.<br>
            <b>Datas de consulta</b> são o carimbo da última verificação de cada CNPJ/documento — este relatório serve como registro
            de diligência do adquirente no recorte informado. Gerado em {{ $relatorio['gerado_em']->format('d/m/Y H:i') }}.
        </div>
    </div>
@endsection
