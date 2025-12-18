@props([
    'href' => null,
    'as' => null,
    'size' => 'md', // sm | md | lg
])

@php
    $tag = $as ?? ($href ? 'a' : 'button');
    $sizes = [
        'sm' => 'px-5 py-2 text-sm',
        'md' => 'px-6 py-3 text-sm',
        'lg' => 'px-7 py-3.5 text-base',
    ];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
    $baseClasses = "btn-cta inline-flex items-center justify-center flex-nowrap gap-2 font-semibold leading-tight {$sizeClasses}";
@endphp

@if ($tag === 'a')
    <a href="{{ $href ?? '#' }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </button>
@endif
