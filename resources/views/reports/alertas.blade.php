@extends('reports.layout')

@section('titulo', 'Central de Alertas')

@section('meta')
    <div>{{ $total }} {{ $total === 1 ? 'alerta' : 'alertas' }}</div>
    @if(($valorRiscoTotal ?? 0) > 0)
        <div>R$ {{ number_format($valorRiscoTotal, 2, ',', '.') }} em risco</div>
    @endif
@endsection

@section('rodape_hash', $hashDoc)

@php
    use App\Support\Reports\ReportTheme;

    $sevMeta = [
        'alta' => ['label' => 'Alta', 'hex' => ReportTheme::IRREGULAR],
        'media' => ['label' => 'Média', 'hex' => ReportTheme::ALERTA],
        'baixa' => ['label' => 'Baixa', 'hex' => ReportTheme::NEUTRO],
    ];
    $fmtRs = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $valorRiscoTotal = $valorRiscoTotal ?? 0;
@endphp

@section('conteudo')

    {{-- Materialidade: valor fiscal em risco (headline pro contador) --}}
    @if($valorRiscoTotal > 0)
        <div class="card-slate" style="border-top-color:{{ ReportTheme::IRREGULAR }}; margin-bottom:14px;">
            <table style="width:100%;"><tr>
                <td style="vertical-align:middle;">
                    <div class="ident-k" style="color:{{ ReportTheme::IRREGULAR }};">Valor fiscal em risco</div>
                    <div style="font-size:18px; font-weight:bold; color:{{ ReportTheme::IRREGULAR }};">{{ $fmtRs($valorRiscoTotal) }}</div>
                </td>
                <td style="vertical-align:middle; text-align:right; width:55%;">
                    <div class="muted small">Exposição a glosa de crédito (fornecedores irregulares + certidões positivas), na janela de decadência. Soma dos {{ $total }} {{ $total === 1 ? 'alerta' : 'alertas' }} deste relatório.</div>
                </td>
            </tr></table>
        </div>
    @endif

    {{-- Resumo por classe --}}
    <div class="secao">
        <div class="secao-header">
            Resumo
            <span class="meta">{{ $total }} {{ $total === 1 ? 'alerta ativo' : 'alertas ativos' }}</span>
        </div>
        <div class="secao-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Classe</th>
                        <th class="right">Alta</th>
                        <th class="right">Média</th>
                        <th class="right">Baixa</th>
                        <th class="right">Total</th>
                        <th class="right" style="width:90px;">Em risco</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grupos as $grupo)
                        @php
                            $porSev = $grupo['alertas']->countBy('severidade');
                            $riscoGrupo = (float) $grupo['alertas']->sum('valor_risco');
                        @endphp
                        <tr>
                            <td>
                                <span class="badge" style="background-color:{{ $grupo['cor'] }}">{{ $grupo['label'] }}</span>
                            </td>
                            <td class="right">{{ $porSev['alta'] ?? 0 }}</td>
                            <td class="right">{{ $porSev['media'] ?? 0 }}</td>
                            <td class="right">{{ $porSev['baixa'] ?? 0 }}</td>
                            <td class="right"><strong>{{ $grupo['alertas']->count() }}</strong></td>
                            <td class="right">{{ $riscoGrupo > 0 ? $fmtRs($riscoGrupo) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @if($valorRiscoTotal > 0)
                    <tfoot>
                        <tr>
                            <td colspan="5" class="right" style="border-top:1.5px solid #1f2937; font-weight:bold;">Total em risco</td>
                            <td class="right" style="border-top:1.5px solid #1f2937; font-weight:bold; color:{{ ReportTheme::IRREGULAR }};">{{ $fmtRs($valorRiscoTotal) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Uma seção por classe --}}
    @foreach($grupos as $grupo)
        @php $riscoGrupo = (float) $grupo['alertas']->sum('valor_risco'); @endphp
        <div class="secao">
            <div class="secao-header" style="background:{{ $grupo['cor'] }}">
                {{ $grupo['label'] }}
                <span class="meta">{{ $grupo['alertas']->count() }} {{ $grupo['alertas']->count() === 1 ? 'alerta' : 'alertas' }}@if($riscoGrupo > 0) · {{ $fmtRs($riscoGrupo) }} em risco @endif</span>
            </div>
            <div class="secao-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:70px;">Severidade</th>
                            <th>Alerta</th>
                            <th style="width:135px;">Cliente / Participante</th>
                            <th class="right" style="width:36px;">Qtd.</th>
                            <th class="right" style="width:80px;">Em risco</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grupo['alertas'] as $alerta)
                            @php
                                $sev = $sevMeta[$alerta->severidade] ?? ['label' => ucfirst((string) $alerta->severidade), 'hex' => ReportTheme::NEUTRO];
                                $alvo = $alerta->cliente?->razao_social ?? $alerta->participante?->razao_social;
                                $riscoAlerta = (float) $alerta->valor_risco;
                            @endphp
                            <tr>
                                <td>
                                    @include('reports.partials._badge', ['label' => $sev['label'], 'hex' => $sev['hex']])
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#111827;">{{ $alerta->titulo }}</div>
                                    @if($alerta->descricao)
                                        <div class="muted small" style="margin-top:2px;">{{ $alerta->descricao }}</div>
                                    @endif
                                </td>
                                <td>{{ $alvo ?? '—' }}</td>
                                <td class="right">{{ $alerta->total_afetados ?: '—' }}</td>
                                <td class="right" style="white-space:nowrap;">{{ $riscoAlerta > 0 ? $fmtRs($riscoAlerta) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

@endsection
