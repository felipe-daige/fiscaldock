@props(['href', 'icon' => null, 'badge' => null, 'badgeLabel' => null, 'pill' => null, 'pillUntil' => null, 'lock' => false])

@php
    $pillVisivel = ! $lock && $pill && (! $pillUntil || now()->lte(\Illuminate\Support\Carbon::parse($pillUntil)->endOfDay()));
    $rotulo = trim(strip_tags($slot));
@endphp

<a href="{{ $href }}" data-link data-sidebar-link title="{{ $lock ? $rotulo.' — recurso de plano pago' : $rotulo }}" {{ $attributes->merge(['class' => 'sidebar__item']) }}>
    @if($icon)
        {{ $icon }}
    @endif
    <span class="sidebar__item-label">{{ $slot }}</span>

    @if($badge)
        <span class="sidebar__item-badge-count" aria-label="{{ $badgeLabel ?? $badge }}" style="background-color: #d97706;">{{ $badge }}</span>
    @elseif($lock)
        <span style="margin-left:auto; display:inline-flex; align-items:center; justify-content:center; width:16px; height:16px; border-radius:9999px; background-color:#e5e7eb;" aria-label="Recurso de plano pago" data-lock-pill>
            <svg style="width:9px; height:9px; color:#6b7280" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </span>
    @elseif($pillVisivel)
        <span style="margin-left:auto; white-space:nowrap; font-size:9px; font-weight:700; line-height:1; padding:2px 6px; border-radius:9999px; background-color:#dcfce7; color:#166534; text-transform:uppercase; letter-spacing:0.04em;">{{ $pill }}</span>
    @endif
</a>
