@extends('reports.layout')

@php
    use App\Support\Reports\ReportTheme;

    $fmtRs = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');

    // Regularidade → cor do design system (mesmo mapa da grade/relatórios).
    $regHex = [
        'regular' => ReportTheme::OK,
        'irregular' => ReportTheme::IRREGULAR,
        'indeterminada' => ReportTheme::ALERTA,
        'nao_consultado' => ReportTheme::NEUTRO,
    ];
@endphp

@section('titulo', 'Carteira de Clientes')

@section('meta')
    <div>{{ $total }} {{ $total === 1 ? 'cliente' : 'clientes' }}</div>
    <div>{{ $fmtRs($total_movimentado) }} movimentado</div>
@endsection

@section('rodape_hash', \App\Support\PdfReport::hashDocumento(auth()->id(), 'clientes-listagem', $total))

@section('conteudo')

    <div class="secao">
        <div class="secao-header">
            Carteira de Clientes
            <span class="meta">Emitido em {{ $gerado_em }}</span>
        </div>
        <div class="secao-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th style="width:120px;">Documento</th>
                        <th class="center" style="width:52px;">Tipo</th>
                        <th class="center" style="width:36px;">UF</th>
                        <th style="width:110px;">Situação</th>
                        <th style="width:110px;">Regime</th>
                        <th class="right" style="width:110px;">Movimentado</th>
                        <th class="center" style="width:90px;">Regularidade</th>
                        <th class="center" style="width:74px;">Últ. consulta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $c)
                        <tr>
                            <td style="font-weight:600; color:#111827;">{{ $c['nome'] }}</td>
                            <td class="mono">{{ $c['documento'] }}</td>
                            <td class="center">{{ $c['tipo'] }}</td>
                            <td class="center">{{ $c['uf'] }}</td>
                            <td>{{ $c['situacao'] }}</td>
                            <td>{{ $c['regime'] }}</td>
                            <td class="right mono">{{ $fmtRs($c['movimentado']) }}</td>
                            <td class="center">
                                <span class="badge" style="background-color:{{ $regHex[$c['regularidade_classe']] ?? ReportTheme::NEUTRO }};">{{ $c['regularidade'] }}</span>
                            </td>
                            <td class="center">{{ $c['ultima_consulta'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="right" style="border-top:1.5px solid #1f2937; font-weight:bold;">Total movimentado</td>
                        <td class="right mono" style="border-top:1.5px solid #1f2937; font-weight:bold;">{{ $fmtRs($total_movimentado) }}</td>
                        <td colspan="2" style="border-top:1.5px solid #1f2937;"></td>
                    </tr>
                </tfoot>
            </table>
            <p class="muted small" style="margin-top:6px;">
                Movimentado = soma das notas (EFD fiscal + XML, deduplicadas por chave). Regularidade e última
                consulta refletem a consulta de CNPJ mais recente do documento. Panorama complementar ao dossiê individual.
            </p>
        </div>
    </div>

@endsection
