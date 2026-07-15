@props(['containerId', 'titulo', 'subtitulo'])

<x-cockpit.layout
    :container-id="$containerId"
    :titulo="$titulo"
    :subtitulo="$subtitulo"
    eyebrow="Cadastros"
    resumo-titulo="Resumo Operacional"
    data-cadastro-lista-layout
>
    @isset($principal)
        <x-slot:principal>{{ $principal }}</x-slot:principal>
    @endisset
    @isset($acoes)
        <x-slot:acoes>{{ $acoes }}</x-slot:acoes>
    @endisset
    @isset($resumo)
        <x-slot:resumo>{{ $resumo }}</x-slot:resumo>
    @endisset

    {{ $slot }}
</x-cockpit.layout>
