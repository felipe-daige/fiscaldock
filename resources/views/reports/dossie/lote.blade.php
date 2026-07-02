{{-- Dossiê em LOTE: capa com índice do lote + um dossiê completo por cliente,
     cada um seguido dos dossiês dos seus participantes. Reusa o _bloco (fonte
     única dos dossiês standalone e do anexo do BI). Payload: DossieLoteBuilder. --}}
@extends('reports.layout')

@php
    $idsClientes = collect($grupos)->map(fn ($g) => $g['dossie']['cliente']->id)->all();
    $totalParticipantes = collect($grupos)->sum(fn ($g) => count($g['participantes']));
@endphp

@php $c1 = $grupos[0]['dossie']['cliente']; @endphp
@section('titulo', count($grupos) === 1
    ? 'Dossiê — '.($c1->razao_social ?: $c1->documento)
    : 'Dossiê em Lote — '.count($grupos).' clientes')
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('dossie-lote', ...$idsClientes))
@section('meta')
    <div class="mono">{{ count($grupos) }} {{ count($grupos) === 1 ? 'cliente' : 'clientes' }} · {{ $totalParticipantes }} {{ $totalParticipantes === 1 ? 'participante' : 'participantes' }}</div>
@endsection

@push('estilos')
    @include('reports.dossie._estilos')
@endpush

@section('conteudo')
    {{-- ── Capa: índice do lote ── --}}
    <div class="secao">
        <div class="secao-header">Conteúdo do Lote <span class="meta">top {{ $top }} participantes por volume EFD · gerado em {{ $gerado_em }}</span></div>
        <div class="secao-body">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:24px;">#</th>
                        <th>Cliente</th>
                        <th style="width:110px;">Documento</th>
                        <th class="center" style="width:28px;">UF</th>
                        <th class="center" style="width:70px;">Score</th>
                        <th class="right" style="width:90px;">Participantes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grupos as $g)
                        @php
                            $c = $g['dossie']['cliente'];
                            $cls = $g['dossie']['score']['classificacao'] ?? 'medio';
                            $hex = \App\Support\Reports\ReportTheme::riscoHex($cls);
                        @endphp
                        <tr>
                            <td class="mono">{{ $loop->iteration }}</td>
                            <td>{{ $c->razao_social ?: ($c->nome_fantasia ?: '—') }}</td>
                            <td class="mono">{{ $c->documento }}</td>
                            <td class="center">{{ $c->uf ?: '—' }}</td>
                            <td class="center"><span class="badge" style="background-color: {{ $hex }};">{{ ucfirst($cls) }}</span></td>
                            <td class="right">
                                {{ count($g['participantes']) }}@if ($g['participantes_total'] > count($g['participantes']))<span class="muted"> de {{ $g['participantes_total'] }}</span>@endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="small muted" style="margin-top:6px;">
                Participantes limitados ao top {{ $top }} por volume EFD de cada cliente (escolha na geração).
                @if (! empty($truncado))
                    Documento também limitado a {{ \App\Services\Clientes\DossieLoteBuilder::TETO_ITENS }} dossiês por geração — gere lotes menores para o conteúdo completo.
                @endif
            </div>
        </div>
    </div>

    {{-- ── Um dossiê completo por cliente, seguido dos seus participantes ── --}}
    @foreach ($grupos as $g)
        @php $c = $g['dossie']['cliente']; @endphp
        <div style="page-break-before:always;">
            <div class="secao-header" style="background:#374151;letter-spacing:.06em;">
                Cliente {{ $loop->iteration }}/{{ count($grupos) }} — {{ $c->razao_social ?: $c->documento }}
                <span class="meta mono">{{ $c->documento }}</span>
            </div>
            <div style="height:8px;"></div>
            @include('reports.dossie._bloco', array_merge($g['dossie'], ['participante' => $c]))
        </div>

        @foreach ($g['participantes'] as $d)
            <div style="page-break-before:always;">
                <div class="secao-header" style="background:#6b7280;letter-spacing:.06em;">
                    Participante — {{ $d['participante']->razao_social ?: $d['participante']->documento }}
                    <span class="meta">vinculado a {{ $c->razao_social ?: $c->documento }}</span>
                </div>
                <div style="height:8px;"></div>
                @include('reports.dossie._bloco', $d)
            </div>
        @endforeach
    @endforeach
@endsection
