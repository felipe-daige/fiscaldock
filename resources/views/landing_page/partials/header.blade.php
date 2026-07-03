{{-- Header público (identidade editorial escura — par do footer; autocontido, o CSS da inicio não carrega nas outras páginas) --}}
<style>
    .lp-header {
        position: sticky;
        top: 0;
        z-index: 50;
        background-color: rgba(11, 20, 36, 0.92);
        -webkit-backdrop-filter: blur(10px);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(148, 197, 255, 0.14);
    }
    .lp-header-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 0.55rem;
        background-color: #f8fafc;
        box-shadow: 0 0 0 1px rgba(148, 197, 255, 0.25);
        flex-shrink: 0;
    }
    .lp-header-brand {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 600;
        font-size: 1.3rem;
        line-height: 1.1;
        color: #ffffff;
    }
    .lp-header-link {
        position: relative;
        display: inline-flex;
        align-items: center;
        min-height: 40px;
        line-height: 1.2;
        font-size: 0.9rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.75);
        transition: color 0.15s ease;
    }
    .lp-header-link:hover { color: #ffffff; }
    .lp-header-link--active { color: #fde68a; }
    .lp-header-link--active::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 2px;
        height: 2px;
        border-radius: 1px;
        background-color: #facc15;
    }
    .lp-header-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        border-radius: 0.375rem;
        border: 1px solid rgba(255, 255, 255, 0.28);
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #ffffff;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .lp-header-btn:hover {
        background-color: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.45);
    }
    .lp-header-burger {
        color: rgba(255, 255, 255, 0.8);
        transition: color 0.15s ease;
    }
    .lp-header-burger:hover { color: #ffffff; }
    .lp-header-mobile-link {
        display: block;
        padding: 0.5rem 0;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.8);
        transition: color 0.15s ease;
    }
    .lp-header-mobile-link:hover { color: #ffffff; }
    .lp-header-mobile-link--active { color: #fde68a; }
    @media (prefers-reduced-motion: reduce) {
        .lp-header-link, .lp-header-btn, .lp-header-burger, .lp-header-mobile-link { transition: none; }
    }
</style>
<header class="lp-header">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex justify-between items-center py-3.5">
            <a href="/inicio" class="flex items-center gap-3">
                <span class="lp-header-logo">
                    <img src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}" alt="FiscalDock" class="h-6 object-contain">
                </span>
                <span class="lp-header-brand">FiscalDock</span>
            </a>

            <ul class="hidden lg:flex items-center gap-6">
                <li class="flex items-center"><a href="/solucoes" class="lp-header-link {{ request()->is('solucoes') ? 'lp-header-link--active' : '' }}">Soluções</a></li>
                <li class="flex items-center"><a href="/precos" class="lp-header-link {{ request()->is('precos') ? 'lp-header-link--active' : '' }}">Preços</a></li>
                <li class="flex items-center"><a href="/duvidas" class="lp-header-link {{ request()->is('duvidas') ? 'lp-header-link--active' : '' }}">Dúvidas</a></li>
                <li class="flex items-center"><a href="/blog" class="lp-header-link {{ request()->is('blog*') ? 'lp-header-link--active' : '' }}">Blog</a></li>
                <li class="flex items-center" aria-hidden="true"><span class="select-none" style="color: rgba(255, 255, 255, 0.2);">|</span></li>
                <li class="flex items-center"><a href="/login" class="lp-header-link {{ request()->is('login') ? 'lp-header-link--active' : '' }}">Login</a></li>
                <li class="flex items-center">
                    <a href="/criar-conta" class="btn-cta btn-cta--nav">
                        Criar conta grátis
                    </a>
                </li>
                <li class="flex items-center">
                    <a href="/agendar" class="lp-header-btn">
                        Falar com especialista
                    </a>
                </li>
            </ul>

            <button id="mobile-menu-btn" class="lg:hidden p-2 lp-header-burger" aria-label="Abrir menu">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </nav>

        <div id="mobile-menu" class="hidden lg:hidden flex-col gap-4 py-4" style="border-top: 1px solid rgba(255, 255, 255, 0.12);">
            <a href="/solucoes" data-link class="lp-header-mobile-link {{ request()->is('solucoes') ? 'lp-header-mobile-link--active' : '' }}">Soluções</a>
            <a href="/precos" data-link class="lp-header-mobile-link {{ request()->is('precos') ? 'lp-header-mobile-link--active' : '' }}">Preços</a>
            <a href="/duvidas" data-link class="lp-header-mobile-link {{ request()->is('duvidas') ? 'lp-header-mobile-link--active' : '' }}">Dúvidas</a>
            <a href="/blog" data-link class="lp-header-mobile-link {{ request()->is('blog*') ? 'lp-header-mobile-link--active' : '' }}">Blog</a>
            <div class="pt-4 flex flex-col gap-4" style="border-top: 1px solid rgba(255, 255, 255, 0.12);">
                <a href="/login" data-link class="lp-header-mobile-link {{ request()->is('login') ? 'lp-header-mobile-link--active' : '' }}">Login</a>
                <a href="/criar-conta" data-link class="btn-cta btn-cta--block">
                    Criar conta grátis
                </a>
                <a href="/agendar" data-link class="lp-header-btn w-full">
                    Falar com especialista
                </a>
            </div>
        </div>
    </div>
</header>
