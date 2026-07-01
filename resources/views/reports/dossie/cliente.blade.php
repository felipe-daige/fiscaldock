{{-- Dossiê do CLIENTE. Reusa o bloco do dossiê: o cliente é passado no slot `participante`
     (Cliente expõe razao_social/situacao_cadastral/documento/uf, lidos pelos partials).
     $score/$movimentacao/$consulta vêm do payload e ficam no escopo, herdados pelo bloco. --}}
@extends('reports.layout')

@section('titulo', 'Dossiê — '.($cliente->razao_social ?: $cliente->documento))
@section('rodape_hash', \App\Support\PdfReport::hashDocumento('cli', $cliente->id, $cliente->documento))
@section('meta')
    <div class="mono">{{ $cliente->documento }}</div>
@endsection

@push('estilos')
    @include('reports.dossie._estilos')
@endpush

@section('conteudo')
    @include('reports.dossie._bloco', ['participante' => $cliente])
@endsection
