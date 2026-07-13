@php
    $postCollection = collect($posts ?? [])->sortByDesc('data')->values();
    $topicCollection = collect($topics ?? []);
    $heroPost = $featuredPost ?? $postCollection->first();
    $trailPosts = array_slice($seriesPosts ?? [], 0, 6);
    $totalArticles = $postCollection->count();
    $totalTopics = $topicCollection->count();
    $latestDate = $postCollection->max('data');
    $topicCodes = [
        'efd' => 'EFD',
        'clearance' => 'DF-e',
        'consultas' => 'CNPJ',
        'compliance' => 'Risco',
        'sped' => 'SPED',
    ];
@endphp

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'Central de Conteúdo Fiscal',
    'description' => 'Guias e análises para contadores sobre SPED, EFD, consultas CNPJ, regularidade fiscal, compliance e validação de documentos.',
    'url' => 'https://fiscaldock.com.br/conteudos',
    'isPartOf' => ['@type' => 'WebSite', 'name' => 'FiscalDock', 'url' => 'https://fiscaldock.com.br'],
    'mainEntity' => [
        '@type' => 'ItemList',
        'numberOfItems' => $totalArticles,
        'itemListElement' => $postCollection->map(fn ($post, $index) => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'url' => 'https://fiscaldock.com.br/conteudos/'.$post['slug'],
            'name' => $post['title'],
        ])->all(),
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
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

<style>
    @property --ct-cover-angle {
        syntax: '<angle>';
        inherits: false;
        initial-value: 0deg;
    }
    .content-hub {
        --ct-ink: #0b1424;
        --ct-navy: #10233f;
        --ct-blue: #1e4fa0;
        --ct-paper: #f7f4ed;
        --ct-soft: #f4f7fa;
        --ct-line: #dfe5ec;
        --ct-muted: #647084;
        --ct-yellow: #facc15;
        color: #111827;
        background: #fff;
        overflow: clip;
    }
    .content-hub *, .content-hub *::before, .content-hub *::after { box-sizing: border-box; }
    .ct-shell { width: min(100% - 2rem, 80rem); margin-inline: auto; }
    .ct-kicker { display: inline-flex; align-items: center; gap: .65rem; font-family: ui-monospace,monospace; font-size: .66rem; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; color: #758196; }
    .ct-kicker::before { content: ''; width: 1.6rem; height: 1px; background: currentColor; opacity: .55; }
    .ct-kicker--light { color: rgba(255,255,255,.58); }
    .ct-heading { margin-top: .85rem; font-family: 'Fraunces',Georgia,serif; font-size: clamp(2.25rem,4.8vw,4.15rem); font-weight: 600; line-height: 1.02; letter-spacing: -.04em; color: var(--ct-ink); }
    .ct-lead { margin-top: 1rem; max-width: 43rem; font-size: 1rem; line-height: 1.72; color: var(--ct-muted); }

    .ct-hero { position: relative; isolation: isolate; padding: clamp(4.5rem,8vw,7.2rem) 0 clamp(4rem,7vw,6.4rem); color: #fff; background: linear-gradient(145deg,#071322,#102746 72%,#14345d); }
    .ct-hero::before { content: ''; position: absolute; z-index: -1; inset: 0; background-image: linear-gradient(rgba(148,197,255,.065) 1px,transparent 1px),linear-gradient(90deg,rgba(148,197,255,.065) 1px,transparent 1px); background-size: 48px 48px; -webkit-mask-image: linear-gradient(to right,#000,transparent 85%); mask-image: linear-gradient(to right,#000,transparent 85%); }
    .ct-hero-grid { display: grid; grid-template-columns: minmax(0,1.05fr) minmax(24rem,.95fr); gap: clamp(2.5rem,7vw,6.5rem); align-items: center; }
    .ct-hero-grid > *, .ct-section-head > *, .ct-series-grid > *, .ct-final-card > * { min-width: 0; }
    .ct-masthead { display: flex; align-items: center; gap: .8rem; margin-bottom: 1.15rem; font-family: ui-monospace,monospace; font-size: .61rem; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: rgba(255,255,255,.48); }
    .ct-masthead span { height: 1px; flex: 1; background: rgba(255,255,255,.17); }
    .ct-hero h1 { max-width: 48rem; font-family: 'Fraunces',Georgia,serif; font-size: clamp(3.1rem,6.4vw,5.8rem); font-weight: 600; line-height: .96; letter-spacing: -.055em; }
    .ct-hero h1 em { display: block; color: #fde68a; font-style: normal; }
    .ct-hero-copy { margin-top: 1.35rem; max-width: 42rem; font-size: 1rem; line-height: 1.72; color: rgba(255,255,255,.7); }
    .ct-hero-meta { display: flex; flex-wrap: wrap; gap: .55rem 1.2rem; margin-top: 1.5rem; font-family: ui-monospace,monospace; font-size: .61rem; letter-spacing: .09em; text-transform: uppercase; color: rgba(255,255,255,.46); }
    .ct-hero-meta span { display: inline-flex; align-items: center; gap: .45rem; }
    .ct-hero-meta span::before { content: ''; width: .35rem; height: .35rem; border-radius: 50%; background: #facc15; }

    .ct-cover { position: relative; isolation: isolate; border: 1px solid rgba(255,255,255,.18); border-radius: 1.1rem; padding: .8rem; background: rgba(5,15,28,.55); box-shadow: 0 38px 90px -48px rgba(0,0,0,.85); backdrop-filter: blur(12px); }
    .ct-cover-inner { position: relative; isolation: isolate; min-height: 28rem; display: flex; flex-direction: column; border-radius: .8rem; padding: 1.4rem; color: var(--ct-ink); background: linear-gradient(150deg,#fffdf7,#eee7d8); overflow: hidden; }
    .ct-cover-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; border-bottom: 1px solid rgba(11,20,36,.15); padding-bottom: .85rem; font-family: ui-monospace,monospace; font-size: .55rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #697386; }
    .ct-cover-number { font-family: 'Fraunces',Georgia,serif; font-size: 4.8rem; font-weight: 600; line-height: .8; letter-spacing: -.08em; color: #d8d1c2; }
    .ct-cover-topic { margin-top: 2.7rem; font-family: ui-monospace,monospace; font-size: .61rem; font-weight: 700; letter-spacing: .15em; text-transform: uppercase; color: var(--ct-blue); }
    .ct-cover h2 { max-width: 27rem; margin-top: .75rem; font-family: 'Fraunces',Georgia,serif; font-size: clamp(1.75rem,3vw,2.45rem); font-weight: 650; line-height: 1.04; letter-spacing: -.035em; }
    .ct-cover p { margin-top: .9rem; max-width: 27rem; font-size: .78rem; line-height: 1.62; color: #5f6877; }
    .ct-cover-link { display: inline-flex; align-items: center; gap: .6rem; width: fit-content; margin-top: auto; border-bottom: 1px solid var(--ct-ink); padding: .7rem 0 .35rem; font-size: .76rem; font-weight: 750; color: var(--ct-ink); }
    .ct-cover-link span { transition: transform .18s ease; }
    .ct-cover-link:hover span { transform: translateX(.25rem); }

    .ct-index { position: sticky; z-index: 20; top: 65px; border-bottom: 1px solid var(--ct-line); background: rgba(255,255,255,.94); backdrop-filter: blur(12px); }
    .ct-index-list { display: flex; gap: .35rem; overflow-x: auto; padding: .65rem 0; scrollbar-width: none; }
    .ct-index-list::-webkit-scrollbar { display: none; }
    .ct-index-link { flex: 0 0 auto; border-radius: .45rem; padding: .62rem .8rem; font-size: .68rem; font-weight: 650; color: #5b6677; }
    .ct-index-link:hover, .ct-index-link--active { color: #fff; background: var(--ct-ink); }

    .ct-section { padding: clamp(4.5rem,8vw,7rem) 0; }
    .ct-section--soft { background: var(--ct-soft); }
    .ct-section-head { display: grid; grid-template-columns: minmax(0,1fr) minmax(17rem,.55fr); gap: 2rem; align-items: end; margin-bottom: 2.2rem; }
    .ct-section-head > p { font-size: .83rem; line-height: 1.65; color: var(--ct-muted); }

    .ct-topic-grid { display: grid; grid-template-columns: repeat(6,minmax(0,1fr)); gap: .7rem; }
    .ct-topic-card { position: relative; min-height: 15rem; display: flex; flex-direction: column; grid-column: span 2; border: 1px solid var(--ct-line); border-radius: .9rem; padding: 1.15rem; background: #fff; overflow: hidden; transition: transform .18s ease,border-color .18s ease,box-shadow .18s ease; }
    .ct-topic-card:nth-child(4), .ct-topic-card:nth-child(5) { grid-column: span 3; }
    .ct-topic-card::after { content: attr(data-code); position: absolute; right: -.25rem; bottom: -.85rem; font-family: 'Fraunces',Georgia,serif; font-size: 5.2rem; font-weight: 650; line-height: 1; color: #f0f3f7; pointer-events: none; }
    .ct-topic-card:hover { transform: translateY(-3px); border-color: #aebed1; box-shadow: 0 24px 55px -40px rgba(15,35,65,.6); }
    .ct-topic-top { position: relative; z-index: 1; display: flex; justify-content: space-between; gap: 1rem; font-family: ui-monospace,monospace; font-size: .55rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #8792a2; }
    .ct-topic-card h3 { position: relative; z-index: 1; margin-top: 2rem; max-width: 20rem; font-family: 'Fraunces',Georgia,serif; font-size: 1.45rem; font-weight: 650; line-height: 1.08; color: var(--ct-ink); }
    .ct-topic-card p { position: relative; z-index: 1; margin-top: .65rem; max-width: 23rem; font-size: .7rem; line-height: 1.55; color: #697486; }
    .ct-topic-card strong { position: relative; z-index: 1; margin-top: auto; padding-top: 1rem; font-size: .69rem; color: var(--ct-blue); }

    .ct-series { position: relative; isolation: isolate; color: #fff; background: var(--ct-ink); }
    .ct-series::before { content: ''; position: absolute; z-index: -1; inset: 0; background-image: linear-gradient(rgba(148,197,255,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(148,197,255,.045) 1px,transparent 1px); background-size: 46px 46px; }
    .ct-series-grid { display: grid; grid-template-columns: minmax(18rem,.7fr) minmax(0,1.3fr); gap: clamp(2.5rem,7vw,6rem); align-items: start; }
    .ct-series .ct-heading { color: #fff; }
    .ct-series-copy { margin-top: 1rem; font-size: .86rem; line-height: 1.7; color: rgba(255,255,255,.62); }
    .ct-series-cta { display: inline-flex; align-items: center; gap: .55rem; margin-top: 1.4rem; border-radius: .45rem; padding: .8rem 1rem; font-size: .73rem; font-weight: 750; color: var(--ct-ink); background: #fde68a; }
    .ct-series-list { counter-reset: series; border-top: 1px solid rgba(255,255,255,.16); }
    .ct-series-item { counter-increment: series; display: grid; grid-template-columns: 2.2rem minmax(0,1fr) auto; gap: .85rem; align-items: center; border-bottom: 1px solid rgba(255,255,255,.12); padding: 1rem .25rem; }
    .ct-series-item::before { content: counter(series,decimal-leading-zero); font-family: ui-monospace,monospace; font-size: .59rem; color: rgba(255,255,255,.38); }
    .ct-series-item strong { display: block; font-family: 'Fraunces',Georgia,serif; font-size: 1rem; font-weight: 600; line-height: 1.25; color: #fff; }
    .ct-series-item span { font-family: ui-monospace,monospace; font-size: .55rem; color: rgba(255,255,255,.38); }
    .ct-series-item:hover strong { color: #fde68a; }

    .ct-library-tools { display: grid; grid-template-columns: minmax(16rem,.7fr) minmax(0,1.3fr); gap: .8rem; align-items: center; border: 1px solid var(--ct-line); border-radius: .9rem; padding: .75rem; background: #fff; }
    .ct-search { position: relative; }
    .ct-search svg { position: absolute; left: .9rem; top: 50%; width: 1rem; height: 1rem; color: #8792a2; transform: translateY(-50%); }
    .ct-search input { width: 100%; height: 2.8rem; border: 1px solid #d6dde7; border-radius: .55rem; padding: 0 1rem 0 2.65rem; font-size: .75rem; color: var(--ct-ink); background: #f8fafc; outline: none; }
    .ct-search input:focus { border-color: #7ca6d8; box-shadow: 0 0 0 3px rgba(30,79,160,.1); }
    .ct-filters { display: flex; justify-content: flex-end; gap: .35rem; overflow-x: auto; scrollbar-width: none; }
    .ct-filter { flex: 0 0 auto; border: 1px solid #d9e0e8; border-radius: 999px; padding: .62rem .75rem; font-size: .63rem; font-weight: 700; color: #5d6979; background: #fff; }
    .ct-filter[aria-pressed="true"] { border-color: var(--ct-ink); color: #fff; background: var(--ct-ink); }
    .ct-library-summary { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin: 1.2rem 0 .85rem; font-family: ui-monospace,monospace; font-size: .58rem; letter-spacing: .1em; text-transform: uppercase; color: #818c9c; }
    .ct-article-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: .75rem; }
    .ct-article-card { min-width: 0; display: flex; flex-direction: column; min-height: 20rem; border: 1px solid var(--ct-line); border-radius: .9rem; padding: 1.1rem; background: #fff; transition: transform .18s ease,border-color .18s ease,box-shadow .18s ease; }
    .ct-article-card:hover { transform: translateY(-3px); border-color: #afbed0; box-shadow: 0 26px 58px -42px rgba(15,35,65,.62); }
    .ct-article-card[hidden] { display: none; }
    .ct-article-meta { display: flex; align-items: center; justify-content: space-between; gap: .8rem; font-family: ui-monospace,monospace; font-size: .54rem; letter-spacing: .09em; text-transform: uppercase; color: #8994a4; }
    .ct-article-category { color: var(--ct-blue); font-weight: 750; }
    .ct-article-rule { position: relative; height: 5.2rem; margin: 1rem 0; border-radius: .55rem; background: linear-gradient(135deg,#edf3fa,#f8f5ed); overflow: hidden; }
    .ct-article-rule::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(30,79,160,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(30,79,160,.08) 1px,transparent 1px); background-size: 22px 22px; }
    .ct-article-rule::after { content: attr(data-index); position: absolute; right: .7rem; bottom: -.55rem; font-family: 'Fraunces',Georgia,serif; font-size: 4rem; font-weight: 650; color: rgba(16,35,63,.13); }
    .ct-article-card h3 { font-family: 'Fraunces',Georgia,serif; font-size: 1.23rem; font-weight: 650; line-height: 1.16; letter-spacing: -.022em; color: var(--ct-ink); }
    .ct-article-card p { margin-top: .65rem; font-size: .69rem; line-height: 1.58; color: #697486; }
    .ct-article-link { display: inline-flex; align-items: center; gap: .45rem; margin-top: auto; padding-top: 1rem; font-size: .68rem; font-weight: 750; color: var(--ct-blue); }
    .ct-empty { margin-top: 1rem; border: 1px dashed #cbd5df; border-radius: .8rem; padding: 2rem; text-align: center; color: var(--ct-muted); background: #fff; }

    .ct-final { padding: clamp(4.5rem,8vw,7rem) 0; background: var(--ct-paper); }
    .ct-final-card { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 2rem; align-items: center; border: 1px solid #ddd6c8; border-radius: 1rem; padding: clamp(1.5rem,4vw,3rem); background: #fffdf8; }
    .ct-final h2 { max-width: 43rem; font-family: 'Fraunces',Georgia,serif; font-size: clamp(2rem,4vw,3.4rem); font-weight: 600; line-height: 1.04; letter-spacing: -.04em; color: var(--ct-ink); }
    .ct-final p { max-width: 42rem; margin-top: .75rem; font-size: .82rem; line-height: 1.65; color: var(--ct-muted); }
    .ct-final-actions { display: flex; flex-wrap: wrap; gap: .6rem; }
    .ct-btn-secondary { display: inline-flex; align-items: center; justify-content: center; min-height: 48px; border: 1px solid #cfd6df; border-radius: .5rem; padding: .8rem 1rem; font-size: .73rem; font-weight: 750; color: var(--ct-ink); background: #fff; }

    [data-content-reveal] { opacity: 1; transform: none; }
    .js .content-hub [data-content-reveal] { opacity: 0; transform: translateY(16px); transition: opacity .5s ease,transform .5s ease; }
    .js .content-hub [data-content-reveal].is-visible { opacity: 1; transform: none; }

    @media (max-width: 980px) {
        .ct-hero-grid, .ct-series-grid { grid-template-columns: 1fr; }
        .ct-cover { max-width: 39rem; }
        .ct-topic-grid { grid-template-columns: repeat(2,minmax(0,1fr)); }
        .ct-topic-card, .ct-topic-card:nth-child(4), .ct-topic-card:nth-child(5) { grid-column: auto; }
        .ct-article-grid { grid-template-columns: repeat(2,minmax(0,1fr)); }
        .ct-library-tools { grid-template-columns: 1fr; }
        .ct-filters { justify-content: flex-start; }
        .ct-final-card { grid-template-columns: 1fr; }
    }
    @media (max-width: 680px) {
        .ct-shell { width: min(100% - 1.25rem,80rem); }
        .ct-hero { padding-top: 3.6rem; }
        .ct-hero h1 { font-size: clamp(2.8rem,14vw,4rem); }
        .ct-hero-copy { font-size: .93rem; }
        .ct-cover {
            transform-origin: 50% 12%;
            box-shadow: 0 36px 80px -30px rgba(0,0,0,.88), 0 0 55px -34px rgba(253,230,138,.8);
        }
        .content-motion-ready .ct-cover:not(.ct-cover--in-view) { opacity: 0; transform: perspective(900px) translateY(42px) rotateX(10deg) scale(.94); filter: blur(6px); }
        .ct-cover.ct-cover--in-view { animation: ct-cover-arrive .9s cubic-bezier(.2,.8,.2,1) both; }
        .ct-cover::before {
            content: '';
            position: absolute;
            z-index: -1;
            inset: -2px;
            border-radius: 1.2rem;
            background: conic-gradient(from var(--ct-cover-angle),transparent 0 22%,rgba(253,230,138,.95) 30%,rgba(96,165,250,.72) 38%,transparent 48% 76%,rgba(52,211,153,.6) 84%,transparent 92%);
            filter: blur(.15px);
        }
        .ct-cover::after {
            content: '';
            position: absolute;
            z-index: -2;
            inset: .75rem -.4rem -.55rem .7rem;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 1rem;
            background: rgba(102,134,173,.22);
            transform: rotate(1.7deg);
            box-shadow: 0 18px 40px -24px rgba(0,0,0,.9);
        }
        .ct-cover-inner {
            min-height: 25rem;
            padding: 1.1rem;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.72);
        }
        .ct-cover-inner::before {
            content: '';
            position: absolute;
            z-index: -1;
            inset: -45% 25% 55% -25%;
            border-radius: 50%;
            background: radial-gradient(circle,rgba(255,255,255,.95),transparent 68%);
            filter: blur(12px);
        }
        .ct-cover-inner::after {
            content: '';
            position: absolute;
            z-index: 5;
            top: -25%;
            bottom: -25%;
            left: -65%;
            width: 38%;
            pointer-events: none;
            background: linear-gradient(90deg,transparent,rgba(255,255,255,.5),transparent);
            transform: rotate(14deg);
        }
        .ct-cover-number { text-shadow: 0 12px 28px rgba(92,72,36,.1); }
        .ct-cover-topic::before { content: ''; display: inline-block; width: .42rem; height: .42rem; margin-right: .45rem; border-radius: 50%; background: #1e4fa0; box-shadow: 0 0 0 0 rgba(30,79,160,.3); }
        .ct-cover--in-view::before { animation: ct-cover-orbit 7s linear infinite; }
        .ct-cover--in-view .ct-cover-inner { animation: ct-cover-float 5.8s ease-in-out 1s infinite; }
        .ct-cover--in-view .ct-cover-inner::after { animation: ct-cover-shine 5.8s ease-in-out 1.35s infinite; }
        .ct-cover--in-view .ct-cover-number { animation: ct-cover-number 5.8s ease-in-out 1s infinite; }
        .ct-cover--in-view .ct-cover-topic::before { animation: ct-cover-pulse 2.4s ease-out infinite; }
        .ct-cover h2, .ct-topic-card h3, .ct-article-card h3 { overflow-wrap: anywhere; }
        .ct-cover-number { font-size: 4rem; }
        .ct-index { top: calc(var(--lp-header-mobile-height,72px) + env(safe-area-inset-top)); }
        .ct-section { padding: 4.5rem 0; }
        .ct-section-head { grid-template-columns: 1fr; gap: 1rem; }
        .ct-heading { font-size: 2.4rem; }
        .ct-topic-grid, .ct-article-grid { grid-template-columns: 1fr; }
        .ct-topic-card { min-height: 13rem; }
        .ct-series-item { grid-template-columns: 2rem minmax(0,1fr); }
        .ct-series-item > span { display: none; }
        .ct-library-tools { padding: .6rem; }
        .ct-search input { height: 3rem; font-size: 1rem; }
        .ct-filters { margin-inline: -.6rem; padding-inline: .6rem; }
        .ct-filter { min-height: 44px; padding-inline: .9rem; }
        .ct-article-meta { flex-wrap: wrap; line-height: 1.45; }
        .ct-article-card { min-height: 18rem; }
        .ct-final-actions { display: grid; grid-template-columns: 1fr; }
        .ct-final-actions > * { width: 100%; }
    }
    @media (max-width: 360px) {
        .ct-shell { width: min(100% - 1rem,80rem); }
        .ct-hero h1 { font-size: 2.65rem; }
        .ct-masthead { gap: .45rem; letter-spacing: .13em; }
        .ct-hero-meta { display: grid; gap: .55rem; }
        .ct-cover { padding: .55rem; }
        .ct-cover-inner { padding: 1rem; }
        .ct-cover h2 { font-size: 1.65rem; }
        .ct-library-summary { align-items: flex-start; flex-direction: column; }
    }
    @media (prefers-reduced-motion: reduce) {
        .content-hub *, .content-hub *::before, .content-hub *::after { transition-duration: .01ms !important; scroll-behavior: auto !important; }
        .ct-cover, .ct-cover::before, .ct-cover-inner, .ct-cover-inner::after, .ct-cover-number, .ct-cover-topic::before { animation: none !important; }
        .content-motion-ready .ct-cover:not(.ct-cover--in-view) { opacity: 1; transform: none; filter: none; }
    }
    @keyframes ct-cover-arrive {
        0% { opacity: 0; transform: perspective(900px) translateY(42px) rotateX(10deg) scale(.94); filter: blur(6px); }
        65% { opacity: 1; transform: perspective(900px) translateY(-4px) rotateX(-1.5deg) scale(1.01); filter: blur(0); }
        100% { opacity: 1; transform: perspective(900px) translateY(0) rotateX(0) scale(1); filter: blur(0); }
    }
    @keyframes ct-cover-orbit { to { --ct-cover-angle: 360deg; } }
    @keyframes ct-cover-float { 0%,100% { transform: translateY(0) rotate(-.18deg); } 50% { transform: translateY(-7px) rotate(.18deg); } }
    @keyframes ct-cover-shine { 0%,20% { left: -65%; opacity: 0; } 35% { opacity: .75; } 58%,100% { left: 130%; opacity: 0; } }
    @keyframes ct-cover-number { 0%,100% { transform: translateY(0); color: #d8d1c2; } 50% { transform: translateY(-3px); color: #cfc3ad; } }
    @keyframes ct-cover-pulse { 0% { box-shadow: 0 0 0 0 rgba(30,79,160,.32); } 70%,100% { box-shadow: 0 0 0 8px rgba(30,79,160,0); } }
</style>

<div class="content-hub">
    <section class="ct-hero" aria-labelledby="content-title">
        <div class="ct-shell ct-hero-grid">
            <div>
                <div class="ct-masthead">FiscalDock <span></span> Edição fiscal contínua</div>
                <h1 id="content-title">Central de <em>Conteúdo Fiscal.</em></h1>
                <p class="ct-hero-copy">Informação técnica para transformar obrigações, documentos e riscos em decisões mais seguras. Conteúdo escrito para a rotina de contadores, escritórios e áreas fiscais.</p>
                <div class="ct-hero-meta">
                    <span>{{ $totalArticles }} análises publicadas</span>
                    <span>{{ $totalTopics }} áreas de estudo</span>
                    @if($latestDate)<span>Acervo atualizado em {{ \Carbon\Carbon::parse($latestDate)->format('m/Y') }}</span>@endif
                </div>
            </div>

            @if($heroPost)
            <article class="ct-cover" data-mobile-activation-ratio="0.45" data-mobile-activation-line="0.72">
                <div class="ct-cover-inner">
                    <div class="ct-cover-head"><span>Leitura recomendada</span><span>{{ $heroPost['tempo_leitura'] }}</span></div>
                    <div class="ct-cover-number">01</div>
                    <span class="ct-cover-topic">{{ $heroPost['categoria'] }} @if(!empty($heroPost['serie'])) · série especial @endif</span>
                    <h2>{{ $heroPost['title'] }}</h2>
                    <p>{{ $heroPost['excerpt'] }}</p>
                    <a href="/conteudos/{{ $heroPost['slug'] }}" class="ct-cover-link">Abrir análise <span aria-hidden="true">→</span></a>
                </div>
            </article>
            @endif
        </div>
    </section>

    <nav class="ct-index" aria-label="Navegação da Central de Conteúdo">
        <div class="ct-shell ct-index-list">
            <a href="#temas" class="ct-index-link ct-index-link--active">Áreas de estudo</a>
            <a href="#trilha-efd" class="ct-index-link">Trilha de EFD</a>
            <a href="#acervo" class="ct-index-link">Acervo completo</a>
            @foreach($topicCollection as $topic)
                <a href="{{ $topic['url'] }}" class="ct-index-link">{{ $topic['short_title'] }}</a>
            @endforeach
        </div>
    </nav>

    <section id="temas" class="ct-section">
        <div class="ct-shell">
            <header class="ct-section-head" data-content-reveal>
                <div><span class="ct-kicker">Índice temático</span><h2 class="ct-heading">Comece pelo assunto que está na sua mesa.</h2></div>
                <p>Cada área reúne leituras conectadas, do conceito à aplicação prática. Assim você aprofunda um tema sem depender de uma sequência aleatória de publicações.</p>
            </header>
            <div class="ct-topic-grid">
                @foreach($topicCollection as $topic)
                <a href="{{ $topic['url'] }}" class="ct-topic-card" data-code="{{ $topicCodes[$topic['slug']] ?? strtoupper($topic['slug']) }}" data-content-reveal>
                    <div class="ct-topic-top"><span>Área {{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}</span><span>{{ $topic['count'] }} {{ $topic['count'] === 1 ? 'artigo' : 'artigos' }}</span></div>
                    <h3>{{ $topic['title'] }}</h3>
                    <p>{{ $topic['description'] }}</p>
                    <strong>Acessar área de estudo →</strong>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    @if(!empty($trailPosts))
    <section id="trilha-efd" class="ct-section ct-series">
        <div class="ct-shell ct-series-grid">
            <div data-content-reveal>
                <span class="ct-kicker ct-kicker--light">Trilha orientada · {{ count($seriesPosts) }} leituras</span>
                <h2 class="ct-heading">Guia de EFD para Contadores.</h2>
                <p class="ct-series-copy">Uma sequência para entender as duas escriturações, estruturar a revisão mensal, cruzar EFD e XML e reduzir retrabalho na equipe.</p>
                <a href="/conteudos/efd" class="ct-series-cta">Ver a trilha completa <span aria-hidden="true">→</span></a>
            </div>
            <div class="ct-series-list" data-content-reveal>
                @foreach($trailPosts as $seriesPost)
                <a href="/conteudos/{{ $seriesPost['slug'] }}" class="ct-series-item">
                    <strong>{{ $seriesPost['title'] }}</strong>
                    <span>{{ $seriesPost['tempo_leitura'] }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section id="acervo" class="ct-section ct-section--soft">
        <div class="ct-shell">
            <header class="ct-section-head" data-content-reveal>
                <div><span class="ct-kicker">Biblioteca técnica</span><h2 class="ct-heading">Encontre a leitura certa para a próxima decisão.</h2></div>
                <p>Pesquise pelo problema, obrigação ou documento. Os filtros trabalham no acervo completo sem esconder a origem temática de cada análise.</p>
            </header>

            <div class="ct-library-tools" data-content-reveal>
                <label class="ct-search">
                    <span class="sr-only">Pesquisar no acervo</span>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke-width="1.8"/><path d="m20 20-4-4" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <input type="search" id="content-search" placeholder="Buscar por EFD, CNPJ, certidão, XML…" autocomplete="off">
                </label>
                <div class="ct-filters" aria-label="Filtrar conteúdos por tema">
                    <button type="button" class="ct-filter" data-content-filter="all" aria-pressed="true">Todos</button>
                    @foreach($topicCollection as $topic)
                        <button type="button" class="ct-filter" data-content-filter="{{ $topic['slug'] }}" aria-pressed="false">{{ $topic['short_title'] }}</button>
                    @endforeach
                </div>
            </div>

            <div class="ct-library-summary" data-content-reveal>
                <span id="content-result-count">{{ $totalArticles }} conteúdos encontrados</span>
                <span>Mais recentes primeiro</span>
            </div>

            <div class="ct-article-grid" id="content-grid">
                @foreach($postCollection as $post)
                <a href="/conteudos/{{ $post['slug'] }}" class="ct-article-card" data-content-card data-topic="{{ $post['tema'] ?? '' }}" data-search="{{ \Illuminate\Support\Str::lower($post['title'].' '.$post['excerpt'].' '.$post['categoria'].' '.implode(' ', $post['tags'] ?? [])) }}" data-content-reveal>
                    <div class="ct-article-meta"><span class="ct-article-category">{{ $post['categoria'] }}</span><span>{{ \Carbon\Carbon::parse($post['data'])->format('d.m.Y') }} · {{ $post['tempo_leitura'] }}</span></div>
                    <div class="ct-article-rule" data-index="{{ str_pad((string) ($loop->index + 1), 2, '0', STR_PAD_LEFT) }}"></div>
                    <h3>{{ $post['title'] }}</h3>
                    <p>{{ $post['excerpt'] }}</p>
                    <span class="ct-article-link">Ler conteúdo <span aria-hidden="true">→</span></span>
                </a>
                @endforeach
            </div>
            <div id="content-empty" class="ct-empty" hidden>Nenhum conteúdo corresponde a essa busca. Tente outro termo ou selecione todos os temas.</div>
        </div>
    </section>

    <section class="ct-final">
        <div class="ct-shell">
            <div class="ct-final-card" data-content-reveal>
                <div><h2>Da leitura para uma rotina fiscal acompanhável.</h2><p>Conheça como a FiscalDock organiza documentos, consultas CNPJ, riscos e evidências em uma única operação.</p></div>
                <div class="ct-final-actions"><a href="/solucoes" class="ct-btn-secondary">Conhecer a plataforma</a><a href="/criar-conta" class="btn-cta">Criar conta grátis</a></div>
            </div>
        </div>
    </section>
</div>
