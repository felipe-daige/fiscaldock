@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $post['title'],
    'description' => $post['meta_description'],
    'datePublished' => $post['data'],
    'dateModified' => $post['data'],
    'url' => 'https://fiscaldock.com.br/conteudos/' . $post['slug'],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => 'https://fiscaldock.com.br/conteudos/' . $post['slug'],
    ],
    'image' => asset('binary_files/logo/Logo FiscalDock.png'),
    'articleSection' => $post['categoria'],
    'author' => ['@type' => 'Organization', 'name' => 'FiscalDock'],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'FiscalDock',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => asset('binary_files/logo/Logo FiscalDock.png'),
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => 'https://fiscaldock.com.br/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Central de Conteúdo Fiscal', 'item' => 'https://fiscaldock.com.br/conteudos'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => 'https://fiscaldock.com.br/conteudos/' . $post['slug']],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

<style>
    .content-article-page { overflow: clip; }
    .content-article-page *, .content-article-page *::before, .content-article-page *::after { box-sizing: border-box; }
    .content-article-page article, .content-article-page aside, .content-article-page .grid > * { min-width: 0; }
    .content-article-page h1, .content-article-page h2, .content-article-page h3, .content-article-page h4, .content-article-page p, .content-article-page li, .content-article-page a { overflow-wrap: anywhere; }
    .content-article-page img, .content-article-page svg { max-width: 100%; }
    .content-article-page code { white-space: normal; overflow-wrap: anywhere; }

    .blog-meta-panel { margin-bottom: 1.25rem; border: 1px solid #dfe5ec; border-radius: .85rem; padding: .9rem; background: #f8fafc; }
    .blog-meta-panel__top, .blog-meta-panel__facts { display: flex; flex-wrap: wrap; align-items: center; gap: .45rem; }
    .blog-meta-panel__facts { margin-top: .7rem; border-top: 1px solid #e5e9ef; padding-top: .7rem; }
    .blog-badge { display: inline-flex; align-items: center; min-height: 1.75rem; border-radius: 999px; padding: .3rem .55rem; font-size: .65rem; font-weight: 700; line-height: 1.2; }
    .blog-badge--primary { color: #fff; background: #10233f; }
    .blog-badge--series { color: #1e4fa0; background: #dcecff; }
    .blog-badge--muted { color: #5f6b7a; background: #edf1f5; }
    .blog-meta-link { display: inline-flex; align-items: center; min-height: 1.75rem; border-bottom: 1px solid #1e4fa0; font-size: .68rem; font-weight: 700; color: #1e4fa0; }
    .blog-meta-chip { display: inline-flex; align-items: center; gap: .4rem; font-family: ui-monospace,monospace; font-size: .61rem; color: #697486; }
    .blog-meta-chip__dot { width: .35rem; height: .35rem; border-radius: 50%; background: #1e4fa0; }

    .content-article-body { font-size: 1rem; line-height: 1.78; }
    .content-article-body > p { margin-block: 1rem; }
    .content-article-body > h2 { margin-top: 2.5rem; }
    .content-article-body > h3 { margin-top: 2rem; }
    .content-article-body ul, .content-article-body ol { padding-left: 1.35rem; }
    .content-article-body a { color: #1e4fa0; text-decoration: underline; text-underline-offset: 2px; }

    .blog-table-wrap { width: 100%; margin: 1.5rem 0; border: 1px solid #dfe5ec; border-radius: .75rem; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: thin; }
    .blog-table { width: 100%; min-width: 42rem; border-collapse: collapse; background: #fff; font-size: .78rem; line-height: 1.45; }
    .blog-table th { padding: .75rem; text-align: left; color: #fff; background: #10233f; }
    .blog-table td { border-top: 1px solid #e5e9ef; padding: .75rem; vertical-align: top; color: #4b5565; }
    .blog-table tbody tr:nth-child(even) { background: #f8fafc; }

    .blog-data-card { margin: 1.5rem 0; border: 1px solid #dfe5ec; border-radius: .85rem; padding: 1rem; background: #f8fafc; }
    .blog-data-card__title { margin-bottom: 1rem; font-family: 'Fraunces',Georgia,serif; font-size: 1.15rem; font-weight: 650; color: #0b1424; }
    .blog-step-flow { display: grid; grid-template-columns: repeat(auto-fit,minmax(8.5rem,1fr)); gap: .55rem; }
    .blog-step-flow__item { min-width: 0; border: 1px solid #dfe5ec; border-radius: .65rem; padding: .75rem; background: #fff; }
    .blog-step-flow__step { display: grid; place-items: center; width: 1.8rem; height: 1.8rem; border-radius: 50%; font-size: .68rem; font-weight: 800; color: #fff; background: #1e4fa0; }
    .blog-step-flow__title { margin-top: .65rem; font-size: .76rem; font-weight: 750; color: #172033; }
    .blog-step-flow__text { margin-top: .3rem; font-size: .67rem; line-height: 1.5; color: #697486; }
    .blog-bar-chart { display: grid; gap: .75rem; }
    .blog-bar-chart__row { display: grid; grid-template-columns: minmax(7rem,.7fr) minmax(8rem,1.3fr) minmax(5rem,.5fr); gap: .6rem; align-items: center; }
    .blog-bar-chart__label, .blog-bar-chart__value { font-size: .68rem; color: #5f6b7a; }
    .blog-bar-chart__value { text-align: right; font-weight: 700; }
    .blog-bar-chart__track { height: .5rem; border-radius: 999px; background: #e1e7ee; overflow: hidden; }
    .blog-bar-chart__fill { width: calc(var(--bar-value,0) * 1%); height: 100%; border-radius: inherit; background: #1e4fa0; }
    .blog-bar-chart__fill--amber { background: #d97706; }
    .blog-bar-chart__fill--emerald { background: #059669; }
    .blog-bar-chart__fill--slate { background: #64748b; }
    .blog-note { margin-top: .85rem; font-size: .67rem; line-height: 1.55; color: #778294; }
    .content-article-cta, .content-article-secondary-cta { min-height: 48px; }

    @media (max-width: 680px) {
        .content-article-page { padding-block: 2.75rem; }
        .content-article-page h1 { font-family: 'Fraunces',Georgia,serif; font-size: 2rem; line-height: 1.08; }
        .content-article-body { font-size: .96rem; line-height: 1.72; }
        .content-article-body > h2 { font-size: 1.45rem; line-height: 1.2; }
        .blog-meta-panel { padding: .75rem; }
        .blog-table { min-width: 36rem; }
        .blog-data-card { padding: .8rem; }
        .blog-step-flow { grid-template-columns: 1fr; }
        .blog-step-flow__item { display: grid; grid-template-columns: 2rem 1fr; gap: .1rem .65rem; align-items: center; }
        .blog-step-flow__step { grid-row: span 2; }
        .blog-step-flow__title, .blog-step-flow__text { margin-top: 0; }
        .blog-bar-chart__row { grid-template-columns: minmax(0,1fr) auto; gap: .35rem .65rem; }
        .blog-bar-chart__track { grid-column: 1 / -1; grid-row: 2; }
        .blog-bar-chart__value { grid-column: 2; grid-row: 1; }
        .content-article-cta, .content-article-secondary-cta { width: 100%; justify-content: center; }
    }
</style>

<section class="content-article-page py-12 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            {{-- Artigo --}}
            <article class="lg:col-span-8">
                <div class="mb-8">
                    <a href="/conteudos" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium text-sm mb-6">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar aos conteúdos
                    </a>
                    <div class="blog-meta-panel">
                        <div class="blog-meta-panel__top">
                            <span class="blog-badge blog-badge--primary">{{ $post['categoria'] }}</span>
                            @if(!empty($post['serie']))
                            <span class="blog-badge blog-badge--series">Série</span>
                            <span class="blog-badge blog-badge--muted">{{ $post['serie'] }}</span>
                            @endif
                            @if(($post['tema'] ?? null) === 'efd')
                            <a href="/conteudos/efd" class="blog-meta-link">Conteúdos sobre EFD</a>
                            @endif
                        </div>
                        <div class="blog-meta-panel__facts">
                            <span class="blog-meta-chip">
                                <span class="blog-meta-chip__dot"></span>
                                {{ \Carbon\Carbon::parse($post['data'])->format('d/m/Y') }}
                            </span>
                            <span class="blog-meta-chip">
                                <span class="blog-meta-chip__dot"></span>
                                {{ $post['tempo_leitura'] }} de leitura
                            </span>
                            @if(!empty($seriePos) && !empty($serieTotal))
                            <span class="blog-meta-chip">
                                <span class="blog-meta-chip__dot"></span>
                                Parte {{ $seriePos }} de {{ $serieTotal }}
                            </span>
                            @endif
                        </div>
                        @if(!empty($post['tags']))
                        <div class="blog-meta-panel__top" style="margin-top:0.75rem;">
                            @foreach($post['tags'] as $tag)
                            <span class="blog-badge blog-badge--muted">#{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 leading-tight">{{ $post['title'] }}</h1>
                </div>

                <div class="content-article-body prose prose-lg max-w-none text-gray-700 leading-relaxed">
                    @include($post['view'])
                </div>

                @if(!empty($seriesPosts))
                <div class="mt-10 rounded-2xl border border-gray-200 bg-gray-50 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Continue na série</h3>
                    <div class="space-y-3">
                        @foreach($seriesPosts as $seriesPost)
                        <a href="/conteudos/{{ $seriesPost['slug'] }}" class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white px-4 py-3 hover:border-blue-300 hover:shadow-sm transition-all">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $seriesPost['title'] }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $seriesPost['tempo_leitura'] }} de leitura</div>
                            </div>
                            <span class="text-blue-600 font-medium text-sm">Ler</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($seriePrev) || !empty($serieNext))
                <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if(!empty($seriePrev))
                    <a href="/conteudos/{{ $seriePrev['slug'] }}" class="rounded-xl border border-gray-200 bg-white p-4 hover:border-blue-300 hover:shadow-sm transition-all">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 mb-1">← Anterior na série</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $seriePrev['title'] }}</div>
                    </a>
                    @else
                    <div></div>
                    @endif
                    @if(!empty($serieNext))
                    <a href="/conteudos/{{ $serieNext['slug'] }}" class="rounded-xl border border-gray-200 bg-white p-4 hover:border-blue-300 hover:shadow-sm transition-all sm:text-right">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 mb-1">Próximo na série →</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $serieNext['title'] }}</div>
                    </a>
                    @endif
                </div>
                @endif

                <div class="mt-12 rounded-xl p-5 sm:p-8 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e5a9a 50%, #0f172a 100%);">
                    <h3 class="text-2xl font-bold mb-3">Proteja seus clientes contra riscos fiscais</h3>
                    <p class="text-white/80 mb-6">O FiscalDock automatiza o monitoramento de participantes, a importação de SPED e a detecção de riscos. Teste gratuitamente.</p>
                    <a href="/criar-conta" class="content-article-cta btn-cta inline-flex items-center">
                        Criar conta grátis
                        <svg class="h-5 w-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>
            </article>

            {{-- Sidebar --}}
            <aside class="lg:col-span-4">
                <div class="lg:sticky lg:top-24">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Artigos relacionados</h3>
                    <div class="space-y-6">
                        @foreach($otherPosts as $otherPost)
                        <a href="/conteudos/{{ $otherPost['slug'] }}" class="group block">
                            <div class="bg-white rounded-lg border border-gray-200 p-4 hover:border-blue-300 hover:shadow-sm transition-all">
                                <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-semibold rounded mb-2">{{ $otherPost['categoria'] }}</span>
                                <h4 class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors leading-snug">{{ $otherPost['title'] }}</h4>
                                <p class="text-xs text-gray-500 mt-1">{{ $otherPost['tempo_leitura'] }} de leitura</p>
                            </div>
                        </a>
                        @endforeach
                    </div>

                    <div class="mt-8 bg-gray-50 rounded-xl border border-gray-200 p-6">
                        <h4 class="text-base font-bold text-gray-900 mb-2">Quer ver na prática?</h4>
                        <p class="text-sm text-gray-600 mb-4">Fale com a FiscalDock para tirar dúvidas comerciais e entender o melhor caminho para sua operação.</p>
                        <a href="/agendar" class="content-article-secondary-cta inline-flex items-center justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Falar com um especialista
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
