@props(['href', 'icon' => null, 'badge' => null, 'badgeLabel' => null])

<a href="{{ $href }}" data-link data-sidebar-link {{ $attributes->merge(['class' => 'sidebar__item']) }}>
    @if($icon)
        {{ $icon }}
    @endif
    <span class="sidebar__item-label">{{ $slot }}</span>

    @if($badge)
        <span class="sidebar__item-badge" aria-label="{{ $badgeLabel ?? $badge }}"></span>
    @endif
</a>
