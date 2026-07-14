{{-- Dossiê em LOTE de PARTICIPANTES: capa com índice + um dossiê completo por
     participante. Reusa o _bloco (fonte única dos dossiês standalone e do lote de
     clientes). Payload: ParticipanteController::dossieLote (lista de
     DossieParticipanteBuilder::montar). --}}
@extends('reports.layout')

@php
    $ids = collect($dossies)->map(fn ($d) => $d['participante']->id)->all();
    $p1 = $dossies[0]['participante'];
@endphp

@section('titulo', count($dossies) === 1
    ? 'Dossiê — '.($p1->razao_social ?: $p1->documento)
    : 'Dossiê em Lote — '.count($dossies).' participantes')
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('dossie-part-lote', ...$ids))
@section('meta')
    <div class="mono">{{ count($dossies) }} {{ count($dossies) === 1 ? 'participante' : 'participantes' }}</div>
@endsection

@push('estilos')
    @include('reports.dossie._estilos')
@endpush

@section('conteudo')
    {{-- ── Capa: índice do lote ── --}}
    <div class="secao">
        <div class="secao-header">Conteúdo do Lote <span class="meta">ordenado por volume EFD · gerado em {{ $gerado_em }}</span></div>
        <div class="secao-body">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:24px;">#</th>
                        <th>Participante</th>
                        <th style="width:110px;">Documento</th>
                        <th class="center" style="width:28px;">UF</th>
                        <th class="center" style="width:70px;">Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dossies as $d)
                        @php
                            $p = $d['participante'];
                            $cls = $d['score']['classificacao'] ?? 'nao_avaliado';
                            $hex = \App\Support\Reports\ReportTheme::riscoHex($cls);
                            $clsLabel = app(\App\Services\RiskScoreService::class)->getLabelClassificacao($cls);
                        @endphp
                        <tr>
                            <td class="mono">{{ $loop->iteration }}</td>
                            <td>{{ $p->razao_social ?: ($p->nome_fantasia ?: '—') }}</td>
                            <td class="mono">{{ $p->documento }}</td>
                            <td class="center">{{ $p->uf ?: '—' }}</td>
                            <td class="center"><span class="badge" style="background-color: {{ $hex }};">{{ $clsLabel }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if (! empty($truncado))
                <div class="small muted" style="margin-top:6px;">
                    Documento limitado a {{ \App\Services\Clientes\DossieLoteBuilder::TETO_ITENS }} dossiês por geração
                    (os de maior volume EFD entram primeiro) — gere lotes menores para o conteúdo completo.
                </div>
            @endif
        </div>
    </div>

    {{-- ── Um dossiê completo por participante ── --}}
    @foreach ($dossies as $d)
        @php $p = $d['participante']; @endphp
        <div style="page-break-before:always;">
            <div class="secao-header" style="background:#374151;letter-spacing:.06em;">
                Participante {{ $loop->iteration }}/{{ count($dossies) }} — {{ $p->razao_social ?: $p->documento }}
                <span class="meta mono">{{ $p->documento }}</span>
            </div>
            <div style="height:8px;"></div>
            @include('reports.dossie._bloco', $d)
        </div>
    @endforeach
@endsection
