@extends('reports.layout')

@section('titulo', 'Central de Alertas')

@section('meta')
    <div>{{ $total }} {{ $total === 1 ? 'alerta' : 'alertas' }}</div>
@endsection

@section('rodape_hash', $hashDoc)

@php
    use App\Support\Reports\ReportTheme;

    $sevMeta = [
        'alta' => ['label' => 'Alta', 'hex' => ReportTheme::IRREGULAR],
        'media' => ['label' => 'Média', 'hex' => ReportTheme::ALERTA],
        'baixa' => ['label' => 'Baixa', 'hex' => ReportTheme::NEUTRO],
    ];
@endphp

@section('conteudo')

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
                    </tr>
                </thead>
                <tbody>
                    @foreach($grupos as $grupo)
                        @php
                            $porSev = $grupo['alertas']->countBy('severidade');
                        @endphp
                        <tr>
                            <td>
                                <span class="badge" style="background-color:{{ $grupo['cor'] }}">{{ $grupo['label'] }}</span>
                            </td>
                            <td class="right">{{ $porSev['alta'] ?? 0 }}</td>
                            <td class="right">{{ $porSev['media'] ?? 0 }}</td>
                            <td class="right">{{ $porSev['baixa'] ?? 0 }}</td>
                            <td class="right"><strong>{{ $grupo['alertas']->count() }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Uma seção por classe --}}
    @foreach($grupos as $grupo)
        <div class="secao">
            <div class="secao-header" style="background:{{ $grupo['cor'] }}">
                {{ $grupo['label'] }}
                <span class="meta">{{ $grupo['alertas']->count() }} {{ $grupo['alertas']->count() === 1 ? 'alerta' : 'alertas' }}</span>
            </div>
            <div class="secao-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:70px;">Severidade</th>
                            <th>Alerta</th>
                            <th style="width:150px;">Cliente / Participante</th>
                            <th class="right" style="width:40px;">Qtd.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grupo['alertas'] as $alerta)
                            @php
                                $sev = $sevMeta[$alerta->severidade] ?? ['label' => ucfirst((string) $alerta->severidade), 'hex' => ReportTheme::NEUTRO];
                                $alvo = $alerta->cliente?->razao_social ?? $alerta->participante?->razao_social;
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

@endsection
