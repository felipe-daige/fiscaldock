@extends('reports.layout')

@php
    use App\Support\Reports\ReportTheme;

    $fmtRs = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');

    // Regularidade → cor do design system (mesmo mapa de clientes-listagem).
    $regHex = [
        'regular' => ReportTheme::OK,
        'irregular' => ReportTheme::IRREGULAR,
        'indeterminada' => ReportTheme::ALERTA,
        'nao_consultado' => ReportTheme::NEUTRO,
        'cpf' => ReportTheme::OUTRO, // CPF: neutro-escuro, não "sem dado"
    ];
    // Papel fiscal — tinta neutra (informativo, não é juízo de risco).
    $papelHex = [
        'fornecedor' => '#1d4ed8',
        'cliente' => '#047857',
        'ambos' => '#7c3aed',
        'sem_movimentacao' => ReportTheme::NEUTRO,
    ];
@endphp

@section('titulo', 'Participantes')

@section('meta')
    <div>{{ $total }} {{ $total === 1 ? 'participante' : 'participantes' }}</div>
    <div>{{ $fmtRs($total_movimentado) }} movimentado</div>
@endsection

@section('rodape_hash', \App\Support\PdfReport::hashDocumento(auth()->id(), 'participantes-listagem', $total))

@section('conteudo')

    <div class="secao">
        <div class="secao-header">
            Participantes
            <span class="meta">Emitido em {{ $gerado_em }}</span>
        </div>
        <div class="secao-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Participante</th>
                        <th style="width:120px;">Documento</th>
                        <th class="center" style="width:36px;">UF</th>
                        <th style="width:100px;">Situação</th>
                        <th style="width:100px;">Regime</th>
                        <th class="center" style="width:74px;">Papel</th>
                        <th class="right" style="width:44px;">Notas</th>
                        <th class="right" style="width:105px;">Movimentado</th>
                        <th class="center" style="width:88px;">Regularidade</th>
                        <th class="center" style="width:70px;">Últ. consulta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($participantes as $p)
                        <tr>
                            <td style="font-weight:600; color:#111827;">{{ $p['nome'] }}</td>
                            <td class="mono">{{ $p['documento'] }}</td>
                            <td class="center">{{ $p['uf'] }}</td>
                            <td>{{ $p['situacao'] }}</td>
                            <td>{{ $p['regime'] }}</td>
                            <td class="center">
                                <span class="badge" style="background-color:{{ $papelHex[$p['papel_classe']] ?? ReportTheme::NEUTRO }};">{{ $p['papel'] }}</span>
                            </td>
                            <td class="right mono">{{ $p['notas'] ?: '—' }}</td>
                            <td class="right mono">{{ $fmtRs($p['movimentado']) }}</td>
                            <td class="center">
                                <span class="badge" style="background-color:{{ $regHex[$p['regularidade_classe']] ?? ReportTheme::NEUTRO }};">{{ $p['regularidade'] }}</span>
                            </td>
                            <td class="center">{{ $p['ultima_consulta'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="right" style="border-top:1.5px solid #1f2937; font-weight:bold;">Total movimentado</td>
                        <td class="right mono" style="border-top:1.5px solid #1f2937; font-weight:bold;">{{ $fmtRs($total_movimentado) }}</td>
                        <td colspan="2" style="border-top:1.5px solid #1f2937;"></td>
                    </tr>
                </tfoot>
            </table>
            <p class="muted small" style="margin-top:6px;">
                Movimentado = volume EFD do participante (notas não canceladas, deduplicadas por chave no escopo do
                participante) — mesmo número da ficha, do dossiê e do Score Fiscal. Papel é derivado das operações
                (entrada → fornecedor, saída → cliente). Panorama complementar ao dossiê individual.
            </p>
        </div>
    </div>

@endsection
