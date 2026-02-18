<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatorio de Consulta - Lote #{{ $lote->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #1a1a1a;
        }

        .container {
            padding: 15px;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }

        .logo-text {
            font-size: 22px;
            font-weight: bold;
            color: #2563eb;
        }

        .logo-subtitle {
            font-size: 10px;
            color: #64748b;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }

        .report-meta {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        /* Summary section */
        .summary {
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 12px;
            border-radius: 4px;
        }

        .summary-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            padding: 5px 10px;
            vertical-align: top;
        }

        .summary-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }

        /* Risk indicators */
        .risk-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .risk-item {
            display: table-cell;
            text-align: center;
            padding: 5px;
        }

        .risk-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }

        .risk-baixo {
            background: #dcfce7;
            color: #166534;
        }

        .risk-medio {
            background: #fef9c3;
            color: #854d0e;
        }

        .risk-alto {
            background: #fed7aa;
            color: #9a3412;
        }

        .risk-critico {
            background: #fecaca;
            color: #991b1b;
        }

        /* Table */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 8px;
        }

        .results-table th {
            background: #1e293b;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
        }

        .results-table td {
            padding: 5px 4px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .results-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .results-table tr:hover {
            background: #f1f5f9;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .status-ativa {
            background: #dcfce7;
            color: #166534;
        }

        .status-suspensa {
            background: #fef9c3;
            color: #854d0e;
        }

        .status-inapta, .status-baixada {
            background: #fecaca;
            color: #991b1b;
        }

        .status-negativa, .status-regular {
            background: #dcfce7;
            color: #166534;
        }

        .status-positiva, .status-irregular {
            background: #fecaca;
            color: #991b1b;
        }

        .status-erro {
            background: #fecaca;
            color: #991b1b;
        }

        .status-sucesso {
            background: #dcfce7;
            color: #166534;
        }

        /* Score cell */
        .score-cell {
            text-align: center;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 8px;
        }

        .footer-link {
            color: #2563eb;
            text-decoration: none;
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }

        /* Legend */
        .legend {
            margin-top: 15px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 4px;
        }

        .legend-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .legend-grid {
            display: table;
            width: 100%;
        }

        .legend-item {
            display: table-cell;
            padding: 3px 8px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="logo-text">FiscalDock</div>
                <div class="logo-subtitle">Compliance Fiscal Inteligente</div>
            </div>
            <div class="header-right">
                <div class="report-title">Relatorio de Analise Fiscal</div>
                <div class="report-meta">
                    Lote #{{ $lote->id }} | Plano: {{ $plano->nome ?? 'N/A' }} | Gerado em: {{ $gerado_em }}
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-title">Resumo Executivo</div>
            <div class="summary-grid">
                <div class="summary-row">
                    <div class="summary-cell">
                        <div class="summary-label">Total Consultado</div>
                        <div class="summary-value">{{ $resumo['total'] }}</div>
                    </div>
                    <div class="summary-cell">
                        <div class="summary-label">Sucesso</div>
                        <div class="summary-value" style="color: #166534;">{{ $resumo['sucesso'] }}</div>
                    </div>
                    <div class="summary-cell">
                        <div class="summary-label">Erros</div>
                        <div class="summary-value" style="color: #991b1b;">{{ $resumo['erro'] }}</div>
                    </div>
                    <div class="summary-cell">
                        <div class="summary-label">Score Medio</div>
                        <div class="summary-value">{{ $resumo['score_medio'] }}</div>
                    </div>
                </div>
            </div>

            <div class="risk-grid">
                <div class="risk-item">
                    <span class="risk-badge risk-baixo">Baixo: {{ $resumo['por_classificacao']['baixo'] }}</span>
                </div>
                <div class="risk-item">
                    <span class="risk-badge risk-medio">Medio: {{ $resumo['por_classificacao']['medio'] }}</span>
                </div>
                <div class="risk-item">
                    <span class="risk-badge risk-alto">Alto: {{ $resumo['por_classificacao']['alto'] }}</span>
                </div>
                <div class="risk-item">
                    <span class="risk-badge risk-critico">Critico: {{ $resumo['por_classificacao']['critico'] }}</span>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <table class="results-table">
            <thead>
                <tr>
                    <th style="width: 12%;">CNPJ</th>
                    <th style="width: 18%;">Razao Social</th>
                    <th style="width: 4%;">UF</th>
                    <th style="width: 8%;">Situacao</th>
                    <th style="width: 6%;">Simples</th>
                    @if(in_array('sintegra', $plano->consultas_incluidas ?? []))
                    <th style="width: 8%;">SINTEGRA</th>
                    @endif
                    @if(in_array('cnd_federal', $plano->consultas_incluidas ?? []))
                    <th style="width: 10%;">CND Federal</th>
                    @endif
                    @if(in_array('crf_fgts', $plano->consultas_incluidas ?? []))
                    <th style="width: 8%;">CRF FGTS</th>
                    @endif
                    @if(in_array('cndt', $plano->consultas_incluidas ?? []))
                    <th style="width: 8%;">CNDT</th>
                    @endif
                    @if(in_array('tcu_consolidada', $plano->consultas_incluidas ?? []))
                    <th style="width: 8%;">Compliance</th>
                    @endif
                    <th style="width: 6%;">Score</th>
                    <th style="width: 8%;">Risco</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultados as $r)
                <tr>
                    <td>{{ $r['cnpj'] }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($r['razao_social'], 30) }}</td>
                    <td>{{ $r['uf'] }}</td>
                    <td>
                        @if($r['status_consulta'] === 'sucesso')
                            <span class="status-badge status-{{ strtolower($r['situacao_cadastral'] ?? 'ativa') }}">
                                {{ $r['situacao_cadastral'] ?? '-' }}
                            </span>
                        @else
                            <span class="status-badge status-erro">ERRO</span>
                        @endif
                    </td>
                    <td>{{ $r['simples_nacional'] }}</td>
                    @if(in_array('sintegra', $plano->consultas_incluidas ?? []))
                    <td>
                        @if($r['sintegra_situacao'])
                            <span class="status-badge status-{{ strtolower($r['sintegra_situacao']) === 'habilitado' ? 'ativa' : 'suspensa' }}">
                                {{ $r['sintegra_situacao'] }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    @endif
                    @if(in_array('cnd_federal', $plano->consultas_incluidas ?? []))
                    <td>
                        @if($r['cnd_federal_status'])
                            <span class="status-badge status-{{ in_array(strtoupper($r['cnd_federal_status']), ['NEGATIVA', 'REGULAR']) ? 'negativa' : 'positiva' }}">
                                {{ $r['cnd_federal_status'] }}
                            </span>
                            @if($r['cnd_federal_validade'])
                                <br><small>Val: {{ $r['cnd_federal_validade'] }}</small>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    @endif
                    @if(in_array('crf_fgts', $plano->consultas_incluidas ?? []))
                    <td>
                        @if($r['crf_fgts_status'])
                            <span class="status-badge status-{{ strtoupper($r['crf_fgts_status']) === 'REGULAR' ? 'regular' : 'irregular' }}">
                                {{ $r['crf_fgts_status'] }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    @endif
                    @if(in_array('cndt', $plano->consultas_incluidas ?? []))
                    <td>
                        @if($r['cndt_status'])
                            <span class="status-badge status-{{ in_array(strtoupper($r['cndt_status']), ['NEGATIVA', 'REGULAR']) ? 'negativa' : 'positiva' }}">
                                {{ $r['cndt_status'] }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    @endif
                    @if(in_array('tcu_consolidada', $plano->consultas_incluidas ?? []))
                    <td>
                        @if($r['ceis'] === 'Sim' || $r['cnep'] === 'Sim')
                            <span class="status-badge status-positiva">RESTRITO</span>
                        @elseif($r['tcu_situacao'] || $r['ceis'] === 'Nao')
                            <span class="status-badge status-negativa">OK</span>
                        @else
                            -
                        @endif
                    </td>
                    @endif
                    <td class="score-cell">
                        <strong>{{ $r['score_total'] }}</strong>
                    </td>
                    <td>
                        <span class="risk-badge risk-{{ $r['classificacao'] }}">
                            {{ ucfirst($r['classificacao']) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Legend -->
        <div class="legend">
            <div class="legend-title">Legenda de Classificacao de Risco</div>
            <div class="legend-grid">
                <div class="legend-item">
                    <span class="risk-badge risk-baixo">Baixo (0-20)</span> Empresa em situacao regular
                </div>
                <div class="legend-item">
                    <span class="risk-badge risk-medio">Medio (21-50)</span> Atencao necessaria
                </div>
                <div class="legend-item">
                    <span class="risk-badge risk-alto">Alto (51-80)</span> Irregularidades detectadas
                </div>
                <div class="legend-item">
                    <span class="risk-badge risk-critico">Critico (81-100)</span> Risco elevado
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Relatorio gerado automaticamente pelo sistema FiscalDock.
                Os dados apresentados sao baseados em consultas realizadas junto a orgaos oficiais.
            </p>
            <p style="margin-top: 5px;">
                <span class="footer-link">https://fiscaldock.com.br</span> |
                Lote #{{ $lote->id }} |
                Usuario ID: {{ $lote->user_id }} |
                {{ $gerado_em }}
            </p>
        </div>
    </div>
</body>
</html>
