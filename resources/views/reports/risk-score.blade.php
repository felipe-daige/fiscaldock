@extends('reports.layout')

@php
    use App\Services\Risk\Export\RiskScoreReportBuilder as Builder;

    $k = $relatorio['kpis'];
    $registros = $relatorio['registros'];
    $truncado = count($registros) > Builder::LIMITE_PDF;
    $visiveis = $truncado ? array_slice($registros, 0, Builder::LIMITE_PDF) : $registros;
    $inteiro = fn ($valor) => $valor === null ? '—' : (string) $valor;
@endphp

@section('titulo', $relatorio['titulo'])
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('score-fiscal', auth()->id(), json_encode($relatorio['filtros'])))

@section('meta')
    @foreach ($relatorio['filtros'] as $rotulo => $valor)
        <div>{{ $rotulo }}: {{ $valor }}</div>
    @endforeach
@endsection

@push('estilos')
<style>
    .risk-table th, .risk-table td { font-size:6.8px; padding:3px; }
    .risk-table .alvo { width:29%; }
    .risk-table .tipo { width:12%; }
    .risk-table .fontes { width:24%; }
    .risk-table .consulta { width:12%; }
</style>
@endpush

@section('conteudo')
    <div class="secao">
        <div class="secao-header">Resumo do recorte</div>
        <div class="secao-body">
            @include('reports.partials._kpi-strip', ['itens' => [
                ['label' => 'Avaliados', 'valor' => $k['avaliados']],
                ['label' => 'Baixo', 'valor' => $k['baixo']],
                ['label' => 'Médio', 'valor' => $k['medio']],
                ['label' => 'Alto', 'valor' => $k['alto']],
                ['label' => 'Crítico', 'valor' => $k['critico']],
                ['label' => 'Inconclusivo', 'valor' => $k['inconclusivo']],
                ['label' => 'Não consultados', 'valor' => $k['nao_consultados']],
            ], 'compacto' => true])
            <div class="small muted" style="margin-top:6px;">
                O score vai de 0 (melhor regularidade) a 100 (pior). “—” indica fonte não avaliada.
                A classificação pode ter piso por certidão positiva, mesmo quando a nota numérica é baixa.
            </div>
        </div>
    </div>

    <div class="secao">
        <div class="secao-header">
            CNPJs por risco
            @if ($truncado)
                <span class="meta">{{ Builder::LIMITE_PDF }} de {{ count($registros) }} linhas — planilhas trazem tudo</span>
            @endif
        </div>
        <div class="secao-body">
            @if (empty($registros))
                <span class="small muted">Nenhum CNPJ no recorte.</span>
            @else
                <table class="table risk-table">
                    <thead>
                        <tr>
                            <th class="alvo">CNPJ / razão social</th>
                            <th class="tipo">Tipo / papel</th>
                            <th class="right">Score</th>
                            <th>Classificação</th>
                            <th class="fontes">Subscores (0–100)</th>
                            <th>Crédito IBS/CBS</th>
                            <th class="consulta">Última consulta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visiveis as $registro)
                            <tr>
                                <td>
                                    <span class="mono">{{ $registro['cnpj'] }}</span><br>
                                    {{ $registro['razao_social'] }}{{ $registro['uf'] !== '—' ? ' · '.$registro['uf'] : '' }}
                                </td>
                                <td>{{ $registro['tipo'] }}{{ $registro['papel'] !== '—' ? ' · '.$registro['papel'] : '' }}</td>
                                <td class="right">{{ $inteiro($registro['score_total']) }}</td>
                                <td>
                                    <span class="badge" style="background-color:{{ Builder::corClassificacao($registro['classificacao_codigo']) }}">
                                        {{ $registro['classificacao'] }}
                                    </span>
                                </td>
                                <td class="mono">
                                    Cad {{ $inteiro($registro['score_cadastral']) }} ·
                                    Fed {{ $inteiro($registro['score_cnd_federal']) }} ·
                                    Est {{ $inteiro($registro['score_cnd_estadual']) }}<br>
                                    FGTS {{ $inteiro($registro['score_fgts']) }} ·
                                    CNDT {{ $inteiro($registro['score_trabalhista']) }}
                                </td>
                                <td>{{ $registro['credito_reforma'] }}</td>
                                <td>{{ $registro['ultima_consulta'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
