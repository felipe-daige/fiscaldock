<aside {{ $attributes->merge(['class' => 'sidebar', 'id' => 'sidebar', 'aria-label' => 'Menu lateral']) }}>
    <div class="sidebar__brand">
        <a href="{{ url('/app/dashboard') }}" class="sidebar__brand-link" data-link data-no-active>
            <img src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}" alt="FiscalDock" class="sidebar__brand-logo">
            <span class="sidebar__brand-copy">
                <span class="sidebar__brand-text">FiscalDock</span>
                <span class="sidebar__brand-subtitle">Painel Fiscal</span>
            </span>
        </a>

        <button id="sidebar-collapse-btn" type="button" class="sidebar__collapse-btn" aria-label="Recolher menu" title="Recolher menu">
            <svg class="w-4 h-4 sidebar__collapse-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </button>

        <button id="sidebar-close-btn" type="button" class="sidebar__close-btn" aria-label="Fechar menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div class="sidebar__nav-wrap">
        <div class="sidebar__nav-fade sidebar__nav-fade--top" aria-hidden="true"></div>
        <nav class="sidebar__nav">
            {{ $slot }}
        </nav>
        <div class="sidebar__nav-fade sidebar__nav-fade--bottom" aria-hidden="true"></div>
    </div>

    @if(isset($footer))
        {{ $footer }}
    @endif
</aside>
