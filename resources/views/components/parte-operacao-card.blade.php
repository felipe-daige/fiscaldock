{{-- Card canônico de Cliente/Participante no detalhe de documento fiscal.
     O corpo usa <x-dados-tabela>; todos os chamadores devem enviar o mesmo contrato de campos
     quando as entidades forem comparadas lado a lado. --}}
@props([
    'titulo',
    'nome',
    'href' => null,
    'descricao' => null,
    'campos' => [],
    'situacao' => null,
    'situacaoHex' => '#9ca3af',
    'papel' => null,
    'papelHex' => '#1d4ed8',
])
@php
    $situacaoLabel = trim((string) $situacao) ?: 'Situação não informada';
@endphp

<article {{ $attributes->merge(['class' => 'h-full bg-white rounded border border-gray-300 overflow-hidden flex flex-col']) }} data-parte-operacao-card>
    <div class="h-10 bg-gray-50 px-4 border-b border-gray-200 flex items-center justify-between gap-2">
        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">{{ $titulo }}</span>
        @if($papel)
            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white"
                  style="background-color: {{ $papelHex }}">{{ $papel }}</span>
        @endif
    </div>

    <div class="p-4 flex-1">
        @if($href)
            <a href="{{ $href }}" data-link
               class="h-10 line-clamp-2 text-sm font-semibold leading-5 text-gray-900 hover:text-gray-600 hover:underline">
                {{ $nome ?: '—' }}
            </a>
        @else
            <p class="h-10 line-clamp-2 text-sm font-semibold leading-5 text-gray-900">
                {{ $nome ?: '—' }}
            </p>
        @endif

        <p class="h-8 mt-1 line-clamp-2 text-[11px] leading-4 text-gray-500">
            {{ $descricao ?: 'Nome fantasia não informado' }}
        </p>

        <div class="h-6 mt-2 flex items-center">
            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white"
                  style="background-color: {{ $situacaoHex }}">{{ $situacaoLabel }}</span>
        </div>

        <x-dados-tabela :campos="$campos" class="mt-4" />
    </div>

    <div class="h-9 border-t border-gray-200 bg-gray-50 px-4 flex items-center justify-end">
        @if($href)
            <a href="{{ $href }}" data-link class="text-[11px] font-semibold text-gray-600 hover:text-gray-900 hover:underline">
                Abrir cadastro completo →
            </a>
        @else
            <span class="text-[11px] font-medium text-gray-400">Perfil cadastral não disponível</span>
        @endif
    </div>
</article>
