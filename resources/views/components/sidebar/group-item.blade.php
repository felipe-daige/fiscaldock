@props(['href'])

<a href="{{ $href }}" data-link {{ $attributes->merge(['class' => 'sidebar__group-menu-item']) }}>
    <span class="sidebar__group-menu-item-label">{{ $slot }}</span>
</a>
