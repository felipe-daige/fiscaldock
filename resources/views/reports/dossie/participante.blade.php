@extends('reports.layout')

@section('titulo', 'Dossiê — '.($participante->razao_social ?: $participante->documento))
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('part', $participante->id, $participante->documento))
@section('meta')
    <div class="mono">{{ $participante->documento }}</div>
@endsection

@push('estilos')
    @include('reports.dossie._estilos')
@endpush

@section('conteudo')
    @include('reports.dossie._bloco')
@endsection
