<aside {{ $attributes->merge(['class' => 'sidebar', 'id' => 'sidebar', 'aria-label' => 'Menu lateral']) }}>
    <!-- Brand -->
    <div class="sidebar__brand">
        <a href="{{ url('/app/dashboard') }}" class="flex items-center gap-3 min-w-0" data-link data-no-active>
            <img src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}" alt="FiscalDock" class="sidebar__brand-logo">
            <span class="sidebar__brand-text text-brand">FiscalDock</span>
        </a>

        <!-- Close (mobile) -->
        <button id="sidebar-close-btn" type="button" class="sidebar__close-btn" aria-label="Fechar menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Nav -->
    <nav class="sidebar__nav">
        {{ $slot }}
    </nav>

    <!-- Footer -->
    @if(isset($footer))
        {{ $footer }}
    @endif
</aside>
