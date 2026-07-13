@php
    $pricingData = $pricingData ?? [];
    $subscriptionPlans = collect($subscriptionPlans ?? []);
    $clearancePricing = $clearancePricing ?? ['batch_basic' => 1.00, 'search' => 1.00, 'search_enabled' => false, 'full_enabled' => false];
    $minimumDeposit = (float) ($pricingData['minimum_deposit'] ?? 100);
    $featuredOffers = $pricingData['featured_offers'] ?? [];
    $products = collect($pricingData['products'] ?? []);
    $complianceSources = collect($pricingData['compliance_sources'] ?? [])->where('status', 'ativo')->values();
    $trialBalance = (float) config('trial.saldo_reais');
    $trialDays = (int) config('trial.validade_dias');
    $freeConsultationLimit = (int) config('trial.limite_consultas_gratuito', 3);
    $firstPaidConsultation = $products->where('price', '>', 0)->sortBy('price')->first();
    $firstPaidPlan = $subscriptionPlans->first(fn ($plan) => $plan->codigo !== 'free' && $plan->codigo !== 'enterprise');

    $planDescriptions = [
        'free' => 'Para conhecer o radar fiscal com uma empresa e começar a organizar a operação.',
        'essencial' => 'Para profissionais e pequenas carteiras que precisam sair do acompanhamento manual.',
        'profissional' => 'Para equipes que monitoram uma carteira ativa e precisam de análise e relatórios.',
        'escritorio' => 'Para escritórios estruturados, com mais clientes, usuários e frequência de controle.',
        'enterprise' => 'Limites, integrações e rotina de monitoramento desenhados para a sua operação.',
    ];
    $depthLabels = [
        'cadastral' => 'Cadastral',
        'licitacao' => 'Licitação',
        'compliance' => 'Compliance',
        'due_diligence' => 'Due Diligence',
    ];
    $frequencyLabels = [1 => 'diário', 7 => 'semanal', 15 => 'quinzenal', 30 => 'mensal'];
    $productHighlights = [
        'gratuito' => ['Situação cadastral', 'Endereço e dados básicos', "Até {$freeConsultationLimit} CNPJs antes da 1ª recarga"],
        'validacao' => ['Regime tributário e Simples', 'QSA, CNAEs e capital social', 'Parecer fiscal automático'],
        'licitacao' => ['Tudo da Validação', 'CND Federal', 'CNDT e regularidade do FGTS'],
        'compliance' => ['Tudo da Licitação', 'CND Estadual e Municipal', 'SINTEGRA e visão consolidada'],
    ];
@endphp

@push('structured-data')
@include('landing_page.partials.breadcrumb-schema', [
    'trail' => [
        ['name' => 'Início', 'url' => url('/')],
        ['name' => 'Preços', 'url' => url('/precos')],
    ],
])
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => 'FiscalDock — Radar de riscos fiscais',
    'description' => 'Planos de assinatura e saldo pré-pago para monitoramento fiscal, consultas CNPJ e análise de SPED.',
    'brand' => ['@type' => 'Brand', 'name' => 'FiscalDock'],
    'offers' => $subscriptionPlans
        ->filter(fn ($plan) => $plan->codigo !== 'enterprise')
        ->map(fn ($plan) => [
            '@type' => 'Offer',
            'name' => 'Plano '.$plan->nome,
            'price' => number_format($plan->preco_mensal_centavos / 100, 2, '.', ''),
            'priceCurrency' => 'BRL',
            'availability' => 'https://schema.org/InStock',
            'url' => route('signup'),
        ])->values()->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

<style>
    .pricing-page {
        --pricing-ink: #0b1424;
        --pricing-navy: #10233f;
        --pricing-blue: #1e4fa0;
        --pricing-sky: #dcecff;
        --pricing-paper: #f7f4ed;
        --pricing-line: #dfe5ec;
        --pricing-muted: #5f6b7a;
        --pricing-yellow: #facc15;
        background: #ffffff;
        color: #111827;
        overflow: hidden;
    }
    .pricing-page *, .pricing-page *::before, .pricing-page *::after { box-sizing: border-box; }
    .pricing-shell { width: min(100% - 2rem, 80rem); margin-inline: auto; }
    .pricing-serif {
        font-family: 'Fraunces', Georgia, 'Times New Roman', serif;
        font-optical-sizing: auto;
        letter-spacing: -0.025em;
    }
    .pricing-kicker {
        display: inline-flex;
        align-items: center;
        gap: .65rem;
        font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .2em;
        line-height: 1.2;
        text-transform: uppercase;
        color: #728095;
    }
    .pricing-kicker::before { content: ''; width: 1.7rem; height: 1px; background: currentColor; opacity: .5; }
    .pricing-kicker--light { color: rgba(255,255,255,.64); }
    .pricing-heading {
        margin-top: .9rem;
        font-family: 'Fraunces', Georgia, serif;
        font-size: clamp(2rem, 4vw, 3.55rem);
        font-weight: 600;
        line-height: 1.04;
        letter-spacing: -.035em;
        color: var(--pricing-ink);
    }
    .pricing-lead { margin-top: 1rem; max-width: 44rem; font-size: 1rem; line-height: 1.75; color: var(--pricing-muted); }

    .pricing-hero {
        position: relative;
        isolation: isolate;
        /* Altura e respiro do topo são compartilhados com .sol-hero (/solucoes) — manter em sincronia. */
        display: flex;
        align-items: center;
        min-height: clamp(32rem, 70vw, 48rem);
        padding: clamp(4.5rem, 9vw, 7.5rem) 0 clamp(4rem, 8vw, 7rem);
        background:
            radial-gradient(circle at 84% 16%, rgba(57, 118, 198, .24), transparent 29rem),
            linear-gradient(145deg, #081322 0%, #10233f 60%, #0d1c32 100%);
    }
    .pricing-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: -1;
        background-image:
            linear-gradient(to right, rgba(148,197,255,.055) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(148,197,255,.055) 1px, transparent 1px);
        background-size: 46px 46px;
        -webkit-mask-image: radial-gradient(100% 100% at 70% 20%, #000 10%, transparent 75%);
        mask-image: radial-gradient(100% 100% at 70% 20%, #000 10%, transparent 75%);
    }
    .pricing-hero-grid { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(23rem, .8fr); gap: clamp(2.5rem, 7vw, 6rem); align-items: center; }
    .pricing-hero h1 { margin-top: 1.15rem; max-width: 48rem; font-size: clamp(2.9rem, 6vw, 5.45rem); font-weight: 600; line-height: .97; color: #fff; }
    .pricing-hero h1 em { color: #fde68a; font-style: normal; }
    .pricing-hero-copy { margin-top: 1.35rem; max-width: 42rem; font-size: clamp(1rem, 1.45vw, 1.16rem); line-height: 1.75; color: rgba(255,255,255,.72); }
    .pricing-hero-actions { display: flex; flex-wrap: wrap; gap: .8rem; margin-top: 1.8rem; }
    .pricing-secondary-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 48px;
        padding: .875rem 1.3rem; border: 1px solid rgba(255,255,255,.26); border-radius: 8px;
        color: #fff; font-size: .94rem; font-weight: 650; text-decoration: none; transition: .18s ease;
    }
    .pricing-secondary-btn:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.5); }
    .pricing-hero-facts { display: flex; flex-wrap: wrap; gap: .7rem 1.3rem; margin-top: 1.65rem; }
    .pricing-hero-fact { display: inline-flex; align-items: center; gap: .45rem; font-size: .77rem; color: rgba(255,255,255,.68); }
    .pricing-hero-fact svg { width: 1rem; height: 1rem; color: #fde68a; flex: 0 0 auto; }

    .pricing-ledger {
        position: relative;
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 1.35rem;
        padding: 1.15rem;
        background: rgba(7,17,31,.62);
        box-shadow: 0 35px 80px -36px rgba(0,0,0,.7);
        backdrop-filter: blur(14px);
    }
    .pricing-ledger::after { content: ''; position: absolute; right: -1.5rem; bottom: -1.5rem; width: 6rem; height: 6rem; border: 1px solid rgba(253,230,138,.18); border-radius: 50%; pointer-events: none; }
    .pricing-ledger-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .25rem .25rem 1rem; }
    .pricing-ledger-label { font-family: ui-monospace, monospace; font-size: .62rem; letter-spacing: .16em; text-transform: uppercase; color: rgba(255,255,255,.48); }
    .pricing-live { display: inline-flex; align-items: center; gap: .4rem; font-family: ui-monospace, monospace; font-size: .6rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #bbf7d0; }
    .pricing-live::before { content: ''; width: .45rem; height: .45rem; border-radius: 50%; background: #34d399; box-shadow: 0 0 0 4px rgba(52,211,153,.12); animation: pricing-live-pulse 2.6s ease-in-out infinite; }
    @keyframes pricing-live-pulse { 0%, 100% { box-shadow: 0 0 0 4px rgba(52,211,153,.12); } 50% { box-shadow: 0 0 0 7px rgba(52,211,153,.04); } }
    .pricing-balance-card { border-radius: 1rem; padding: 1.15rem 1.25rem; color: var(--pricing-ink); background: linear-gradient(145deg, #fffef8, #f7edcf); }
    .pricing-balance-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
    .pricing-balance-card small { display: block; font-family: ui-monospace, monospace; font-size: .58rem; font-weight: 750; letter-spacing: .12em; text-transform: uppercase; color: #7b7361; }
    .pricing-balance-chip { flex: 0 0 auto; border: 1px solid rgba(11,20,36,.16); border-radius: 999px; padding: .22rem .5rem; font-family: ui-monospace, monospace; font-size: .55rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #6b6252; background: rgba(255,255,255,.55); }
    .pricing-balance-value { margin-top: .5rem; font-family: 'Fraunces', Georgia, serif; font-size: clamp(2.35rem, 4.4vw, 3.25rem); font-weight: 650; line-height: 1; letter-spacing: -.04em; }
    .pricing-balance-note { margin-top: .55rem; font-size: .72rem; line-height: 1.45; color: #6b6252; }

    .pricing-runs { margin-top: .6rem; border: 1px solid rgba(255,255,255,.09); border-radius: .85rem; padding: .8rem .9rem; background: rgba(255,255,255,.045); }
    .pricing-runs-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding-bottom: .35rem; border-bottom: 1px solid rgba(255,255,255,.08); }
    .pricing-runs-head span { font-family: ui-monospace, monospace; font-size: .56rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: rgba(255,255,255,.5); }
    .pricing-runs-head em { font-family: ui-monospace, monospace; font-size: .52rem; font-style: normal; letter-spacing: .06em; text-transform: uppercase; color: rgba(255,255,255,.32); }
    .pricing-run { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: .3rem .7rem; align-items: center; padding: .5rem 0; }
    .pricing-run + .pricing-run { border-top: 1px solid rgba(255,255,255,.06); }
    .pricing-run-name { font-size: .72rem; font-weight: 650; color: #fff; transition: color .18s ease; }
    .pricing-run:hover .pricing-run-name { color: #fde68a; }
    .pricing-run-price { font-family: ui-monospace, monospace; font-size: .62rem; text-align: right; color: rgba(255,255,255,.45); }
    .pricing-run-bar { position: relative; grid-column: 1; height: 3px; border-radius: 999px; background: rgba(255,255,255,.09); overflow: hidden; }
    .pricing-run-bar i {
        position: relative; display: block; width: var(--fill, 100%); height: 100%; border-radius: inherit; overflow: hidden;
        background: linear-gradient(90deg,#fde68a,#34d399);
        transform-origin: left center;
        animation: pricing-run-fill .95s cubic-bezier(.22,.9,.24,1) both;
        animation-delay: var(--delay, 0ms);
    }
    .pricing-run-bar i::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.75), transparent);
        transform: translateX(-100%);
        animation: pricing-run-scan 3.6s ease-in-out infinite;
        animation-delay: var(--delay, 0ms);
    }
    @keyframes pricing-run-fill { from { transform: scaleX(0); } to { transform: scaleX(1); } }
    @keyframes pricing-run-scan { 0%, 35% { transform: translateX(-100%); } 70%, 100% { transform: translateX(100%); } }
    .pricing-run-count { grid-column: 2; font-family: ui-monospace, monospace; font-size: .64rem; font-weight: 700; white-space: nowrap; text-align: right; color: #a7f3d0; }
    @media (prefers-reduced-motion: reduce) {
        .pricing-run-bar i { animation: none; }
        .pricing-run-bar i::after { animation: none; opacity: 0; }
    }

    .pricing-ledger-rows { display: grid; gap: .45rem; margin-top: .6rem; }
    .pricing-ledger-row { display: grid; grid-template-columns: 1fr auto; gap: .8rem; align-items: center; border: 1px solid rgba(255,255,255,.09); border-radius: .75rem; padding: .7rem .9rem; background: rgba(255,255,255,.045); }
    .pricing-ledger-row span { font-size: .72rem; color: rgba(255,255,255,.55); }
    .pricing-ledger-row strong { font-family: ui-monospace, monospace; font-size: .74rem; font-weight: 700; color: #fff; }
    .pricing-ledger-foot { margin-top: .7rem; padding-top: .65rem; border-top: 1px dashed rgba(255,255,255,.12); font-family: ui-monospace, monospace; font-size: .53rem; line-height: 1.5; letter-spacing: .04em; text-transform: uppercase; color: rgba(255,255,255,.38); }
    @media (prefers-reduced-motion: reduce) { .pricing-live::before { animation: none; } }

    .pricing-paths { position: relative; z-index: 2; margin-top: -1.9rem; }
    .pricing-paths-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); border: 1px solid var(--pricing-line); border-radius: 1.1rem; background: #fff; box-shadow: 0 22px 60px -38px rgba(15,35,65,.45); overflow: hidden; }
    .pricing-path { display: grid; grid-template-columns: auto 1fr; gap: 1rem; padding: 1.35rem 1.5rem; }
    .pricing-path + .pricing-path { border-left: 1px solid var(--pricing-line); }
    .pricing-path-icon { display: grid; place-items: center; width: 2.65rem; height: 2.65rem; border-radius: .75rem; background: #edf4fd; color: var(--pricing-blue); }
    .pricing-path-icon svg { width: 1.25rem; height: 1.25rem; }
    .pricing-path h2 { font-size: .93rem; font-weight: 750; color: var(--pricing-ink); }
    .pricing-path p { margin-top: .25rem; font-size: .78rem; line-height: 1.55; color: #667085; }

    .pricing-section { padding: clamp(4.5rem, 8vw, 7.5rem) 0; }
    .pricing-section--soft { background: #f6f8fb; }
    .pricing-section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 2rem; margin-bottom: 2.1rem; }
    .pricing-section-head .pricing-lead { margin-bottom: .2rem; }
    .pricing-cycle {
        display: inline-flex; flex: 0 0 auto; gap: .25rem; padding: .3rem; border: 1px solid #d9e0e9;
        border-radius: .75rem; background: #fff; box-shadow: 0 10px 30px -22px rgba(15,35,65,.4);
    }
    .pricing-cycle button { min-height: 42px; border: 0; border-radius: .55rem; padding: .6rem .9rem; background: transparent; color: #667085; font-size: .78rem; font-weight: 750; cursor: pointer; }
    .pricing-cycle button[aria-pressed="true"] { background: var(--pricing-ink); color: #fff; box-shadow: 0 8px 18px -12px rgba(11,20,36,.8); }
    .pricing-cycle em { margin-left: .25rem; color: #047857; font-size: .65rem; font-style: normal; }
    .pricing-cycle button[aria-pressed="true"] em { color: #a7f3d0; }

    .pricing-plans { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .8rem; align-items: stretch; }
    .pricing-plan { position: relative; display: flex; min-width: 0; flex-direction: column; border: 1px solid #dce2ea; border-radius: 1rem; background: #fff; overflow: hidden; transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
    .pricing-plan:hover { transform: translateY(-3px); border-color: #b8c6d8; box-shadow: 0 22px 45px -33px rgba(15,35,65,.55); }
    .pricing-plan--featured { border-color: var(--pricing-ink); box-shadow: 0 24px 55px -34px rgba(15,35,65,.52); }
    .pricing-plan-band { min-height: 1.6rem; padding: .42rem .8rem; background: #eef3f8; font-family: ui-monospace, monospace; font-size: .57rem; font-weight: 750; letter-spacing: .13em; text-align: center; text-transform: uppercase; color: #728095; }
    .pricing-plan--featured .pricing-plan-band { background: var(--pricing-ink); color: #fde68a; }
    .pricing-plan-body { display: flex; flex: 1; flex-direction: column; padding: 1.2rem 1.05rem 1.05rem; }
    .pricing-plan-name { font-family: ui-monospace, monospace; font-size: .66rem; font-weight: 750; letter-spacing: .15em; text-transform: uppercase; color: #6b7280; }
    .pricing-plan-price { min-height: 4.7rem; margin-top: .7rem; }
    .pricing-plan-price strong { display: block; font-family: 'Fraunces', Georgia, serif; font-size: clamp(1.75rem, 2.5vw, 2.25rem); font-weight: 650; line-height: 1; letter-spacing: -.035em; color: var(--pricing-ink); }
    .pricing-plan-price strong span { font-family: 'Instrument Sans', system-ui, sans-serif; font-size: .72rem; font-weight: 650; letter-spacing: 0; color: #7b8491; }
    .pricing-plan-price small { display: block; margin-top: .45rem; font-size: .66rem; line-height: 1.35; color: #7b8491; }
    .pricing-plan-description { min-height: 5.2rem; margin-top: .75rem; font-size: .76rem; line-height: 1.55; color: #5f6b7a; }
    .pricing-plan-divider { height: 1px; margin: 1rem 0; background: #e7ebf0; }
    .pricing-plan-features { display: grid; gap: .65rem; margin: 0; padding: 0; list-style: none; }
    .pricing-plan-features li { display: grid; grid-template-columns: auto 1fr; gap: .5rem; align-items: start; font-size: .71rem; line-height: 1.4; color: #3f4a59; }
    .pricing-plan-features svg { width: .9rem; height: .9rem; margin-top: .05rem; color: #047857; }
    .pricing-plan-action { margin-top: auto; padding-top: 1.2rem; }
    .pricing-plan-btn { display: inline-flex; width: 100%; min-height: 42px; align-items: center; justify-content: center; border: 1px solid #cfd7e2; border-radius: .55rem; padding: .65rem .75rem; color: #1f2937; font-size: .72rem; font-weight: 750; text-decoration: none; transition: .16s ease; }
    .pricing-plan-btn:hover { border-color: var(--pricing-blue); color: var(--pricing-blue); background: #f4f8fd; }
    .pricing-plan--featured .pricing-plan-btn { border-color: var(--pricing-yellow); background: var(--pricing-yellow); color: var(--pricing-ink); }
    .pricing-plan--featured .pricing-plan-btn:hover { border-color: #eab308; background: #eab308; }
    .pricing-plans-note { display: flex; align-items: flex-start; gap: .55rem; margin-top: 1rem; font-size: .73rem; line-height: 1.55; color: #6b7280; }
    .pricing-plans-note svg { width: 1rem; height: 1rem; margin-top: .1rem; color: var(--pricing-blue); flex: 0 0 auto; }

    .pricing-products { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
    .pricing-product { position: relative; display: flex; flex-direction: column; min-height: 25rem; border: 1px solid var(--pricing-line); border-radius: 1rem; padding: 1.35rem; background: #fff; overflow: hidden; }
    .pricing-product::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: #ccd6e3; }
    .pricing-product--validacao::before { background: #3b82f6; }
    .pricing-product--licitacao::before { background: #d97706; }
    .pricing-product--compliance::before { background: #047857; }
    .pricing-product-index { display: flex; align-items: center; justify-content: space-between; gap: 1rem; font-family: ui-monospace, monospace; font-size: .61rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #8993a1; }
    .pricing-product-status { display: inline-flex; align-items: center; gap: .35rem; letter-spacing: .08em; color: #047857; }
    .pricing-product-status::before { content: ''; width: .38rem; height: .38rem; border-radius: 50%; background: #10b981; }
    .pricing-product h3 { margin-top: 1.1rem; font-family: 'Fraunces', Georgia, serif; font-size: 1.55rem; font-weight: 650; letter-spacing: -.02em; color: var(--pricing-ink); }
    .pricing-product-price { margin-top: .55rem; font-family: 'Fraunces', Georgia, serif; font-size: 2rem; font-weight: 650; line-height: 1; letter-spacing: -.03em; color: var(--pricing-ink); }
    .pricing-product-price span { font-family: 'Instrument Sans', system-ui, sans-serif; font-size: .72rem; font-weight: 650; letter-spacing: 0; color: #7b8491; }
    .pricing-product-description { margin-top: .8rem; font-size: .76rem; line-height: 1.55; color: #667085; }
    .pricing-product ul { display: grid; gap: .65rem; margin: 1.1rem 0 0; padding: 1.05rem 0 0; border-top: 1px dashed #dce2ea; list-style: none; }
    .pricing-product li { display: grid; grid-template-columns: auto 1fr; gap: .5rem; font-size: .72rem; line-height: 1.4; color: #344054; }
    .pricing-product li svg { width: .9rem; height: .9rem; margin-top: .05rem; color: #047857; }
    .pricing-product-lock { margin-top: auto; padding-top: 1.1rem; font-size: .64rem; line-height: 1.45; color: #7b8491; }

    .pricing-sources { display: grid; grid-template-columns: minmax(0,.82fr) minmax(0,1.18fr); gap: clamp(1.6rem,3vw,2.4rem); align-items: center; margin-top: 1.4rem; border: 1px solid #dce5ef; border-radius: 1rem; padding: clamp(1.4rem, 3vw, 2.1rem); background: #f7faff; }
    .pricing-sources h3 { font-family: 'Fraunces', Georgia, serif; font-size: clamp(1.45rem,2.2vw,1.85rem); font-weight: 650; line-height: 1.12; color: var(--pricing-ink); }
    .pricing-sources-intro > p { margin-top: .65rem; font-size: .78rem; line-height: 1.65; color: #667085; }
    .pricing-sources-groups { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: 1rem; }
    .pricing-sources-groups span { display: inline-flex; align-items: baseline; gap: .35rem; border: 1px solid #dbe4ee; border-radius: 999px; padding: .3rem .6rem; font-family: ui-monospace, monospace; font-size: .55rem; font-weight: 650; letter-spacing: .04em; text-transform: uppercase; color: #5d6875; background: #fff; }
    .pricing-sources-groups b { font-family: 'Fraunces', Georgia, serif; font-size: .82rem; font-weight: 650; color: var(--pricing-ink); }
    .pricing-sources-price { display: flex; flex-wrap: wrap; align-items: baseline; gap: .3rem .6rem; margin-top: 1rem; border-top: 1px dashed #cfdae7; padding-top: .9rem; }
    .pricing-sources-price small { width: 100%; font-family: ui-monospace, monospace; font-size: .53rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #8a94a3; }
    .pricing-sources-price strong { font-family: 'Fraunces', Georgia, serif; font-size: 1.75rem; font-weight: 650; line-height: 1; color: var(--pricing-ink); }
    .pricing-sources-price span { font-size: .68rem; color: #7a8594; }

    .pricing-source-list { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: .5rem; }
    .pricing-source { position: relative; min-width: 0; border: 1px solid #dbe4ee; border-radius: .75rem; padding: .75rem .85rem; background: #fff; transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .pricing-source:hover { transform: translateY(-2px); border-color: #b9c9da; box-shadow: 0 16px 34px -28px rgba(15,35,65,.55); }
    .pricing-source--wide { grid-column: span 2; }
    .pricing-source-top { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
    .pricing-source-cat { font-family: ui-monospace, monospace; font-size: .5rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #94a0ae; }
    .pricing-source-top i { flex: 0 0 auto; width: .4rem; height: .4rem; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 3px #d1fae5; animation: pricing-source-pulse 2.8s ease-in-out infinite; animation-delay: var(--delay, 0ms); }
    @keyframes pricing-source-pulse { 0%, 100% { box-shadow: 0 0 0 3px #d1fae5; } 50% { box-shadow: 0 0 0 5px rgba(16,185,129,.14); } }
    .pricing-source strong { display: block; margin-top: .4rem; font-size: .74rem; font-weight: 750; line-height: 1.3; color: #202938; }
    .pricing-sources .pricing-source p { margin-top: .3rem; font-size: .64rem; line-height: 1.45; color: #7a8594; }
    @media (prefers-reduced-motion: reduce) { .pricing-source-top i { animation: none; } }

    .pricing-documents { display: grid; grid-template-columns: minmax(0,.8fr) repeat(2,minmax(0,1fr)); gap: 1rem; align-items: stretch; }
    .pricing-documents-intro { display: flex; flex-direction: column; justify-content: space-between; border-radius: 1rem; padding: 1.5rem; color: #fff; background: var(--pricing-ink); }
    .pricing-documents-intro p { margin-top: .8rem; font-size: .78rem; line-height: 1.65; color: rgba(255,255,255,.64); }
    .pricing-document-code { margin-top: 2rem; font-family: ui-monospace, monospace; font-size: .62rem; letter-spacing: .14em; text-transform: uppercase; color: #fde68a; }
    .pricing-document-card { display: flex; flex-direction: column; border: 1px solid var(--pricing-line); border-radius: 1rem; padding: 1.45rem; background: #fff; }
    .pricing-document-top { display: flex; align-items: center; justify-content: space-between; gap: .8rem; }
    .pricing-document-top span:first-child { font-family: ui-monospace, monospace; font-size: .62rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #7b8491; }
    .pricing-availability { display: inline-flex; align-items: center; gap: .35rem; border-radius: 999px; padding: .3rem .5rem; font-size: .58rem; font-weight: 750; text-transform: uppercase; color: #047857; background: #ecfdf5; }
    .pricing-availability::before { content: ''; width: .38rem; height: .38rem; border-radius: 50%; background: currentColor; }
    .pricing-availability--limited { color: #92400e; background: #fffbeb; }
    .pricing-document-card h3 { margin-top: 1.15rem; font-family: 'Fraunces', Georgia, serif; font-size: 1.5rem; font-weight: 650; color: var(--pricing-ink); }
    .pricing-document-price { margin-top: .55rem; font-family: 'Fraunces', Georgia, serif; font-size: 2rem; font-weight: 650; line-height: 1; color: var(--pricing-ink); }
    .pricing-document-price span { font-family: 'Instrument Sans', system-ui, sans-serif; font-size: .72rem; font-weight: 650; color: #7b8491; }
    .pricing-document-card p { margin-top: .8rem; font-size: .76rem; line-height: 1.6; color: #667085; }
    .pricing-document-card ul { display: grid; gap: .55rem; margin: 1rem 0 0; padding: 1rem 0 0; border-top: 1px dashed #dce2ea; list-style: none; }
    .pricing-document-card li { display: grid; grid-template-columns: auto 1fr; gap: .5rem; font-size: .71rem; line-height: 1.4; color: #344054; }
    .pricing-document-card li::before { content: '✓'; color: #047857; font-weight: 800; }

    .pricing-wallet { position: relative; background: var(--pricing-paper); }
    .pricing-wallet-grid { display: grid; grid-template-columns: minmax(0,.85fr) minmax(0,1.15fr); gap: clamp(2rem, 7vw, 6rem); align-items: center; }
    .pricing-wallet-proof { margin-top: 1.3rem; display: grid; gap: .7rem; }
    .pricing-wallet-proof li { display: grid; grid-template-columns: auto 1fr; gap: .65rem; align-items: start; font-size: .82rem; line-height: 1.5; color: #485465; }
    .pricing-wallet-proof svg { width: 1.05rem; height: 1.05rem; margin-top: .1rem; color: #047857; }
    .pricing-wallet-card { border: 1px solid #d6d1c5; border-radius: 1.2rem; padding: 1rem; background: rgba(255,255,255,.68); box-shadow: 0 28px 60px -40px rgba(45,38,25,.45); }
    .pricing-wallet-custom { border-radius: .9rem; padding: 1.4rem; color: #fff; background: var(--pricing-ink); }
    .pricing-wallet-custom small { font-family: ui-monospace, monospace; font-size: .6rem; letter-spacing: .15em; text-transform: uppercase; color: rgba(255,255,255,.52); }
    .pricing-wallet-custom strong { display: block; margin-top: .55rem; font-family: 'Fraunces', Georgia, serif; font-size: 2rem; font-weight: 650; color: #fde68a; }
    .pricing-wallet-custom p { margin-top: .45rem; font-size: .75rem; line-height: 1.5; color: rgba(255,255,255,.65); }
    .pricing-wallet-offers { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: .65rem; margin-top: .65rem; }
    .pricing-wallet-offer { border: 1px solid #ded9ce; border-radius: .85rem; padding: 1rem; background: #fff; }
    .pricing-wallet-offer small { font-family: ui-monospace, monospace; font-size: .58rem; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; color: #8993a1; }
    .pricing-wallet-offer strong { display: block; margin-top: .4rem; font-family: 'Fraunces', Georgia, serif; font-size: 1.5rem; color: var(--pricing-ink); }
    .pricing-wallet-offer p { margin-top: .35rem; font-size: .67rem; line-height: 1.45; color: #667085; }

    .pricing-faq-grid { display: grid; grid-template-columns: minmax(0,.72fr) minmax(0,1.28fr); gap: clamp(2.5rem, 7vw, 6rem); align-items: start; }
    .pricing-faq-help { margin-top: 1.5rem; border-left: 2px solid #dce5ef; padding-left: 1rem; font-size: .78rem; line-height: 1.6; color: #667085; }
    .pricing-faq-help a { color: var(--pricing-blue); font-weight: 700; text-decoration: none; }
    .pricing-faq-item { border: 1px solid var(--pricing-line); border-radius: .85rem; background: #fff; transition: .18s ease; }
    .pricing-faq-item + .pricing-faq-item { margin-top: .7rem; }
    .pricing-faq-item[open] { border-color: #b8cce4; box-shadow: 0 16px 35px -28px rgba(15,35,65,.5); }
    .pricing-faq-item summary { display: grid; grid-template-columns: auto 1fr auto; gap: .8rem; align-items: center; padding: 1rem 1.1rem; cursor: pointer; list-style: none; }
    .pricing-faq-item summary::-webkit-details-marker { display: none; }
    .pricing-faq-num { font-family: ui-monospace, monospace; font-size: .62rem; color: #94a3b8; }
    .pricing-faq-question { font-size: .84rem; font-weight: 750; color: #202938; }
    .pricing-faq-plus { display: grid; place-items: center; width: 1.6rem; height: 1.6rem; border: 1px solid #dfe5ec; border-radius: 50%; color: #64748b; transition: .2s ease; }
    .pricing-faq-plus::before { content: '+'; font-size: 1rem; line-height: 1; }
    .pricing-faq-item[open] .pricing-faq-plus { rotate: 45deg; background: var(--pricing-blue); border-color: var(--pricing-blue); color: #fff; }
    .pricing-faq-answer { padding: 0 1.1rem 1.1rem 3rem; font-size: .8rem; line-height: 1.7; color: #5f6b7a; }

    .pricing-final { position: relative; padding: clamp(4rem, 8vw, 6.5rem) 0; background: var(--pricing-ink); overflow: hidden; }
    .pricing-final::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(to right,rgba(148,197,255,.05) 1px,transparent 1px),linear-gradient(to bottom,rgba(148,197,255,.05) 1px,transparent 1px); background-size: 46px 46px; -webkit-mask-image: radial-gradient(circle at center,#000,transparent 72%); mask-image: radial-gradient(circle at center,#000,transparent 72%); }
    .pricing-final-inner { position: relative; text-align: center; }
    .pricing-final h2 { max-width: 50rem; margin: 1rem auto 0; font-family: 'Fraunces', Georgia, serif; font-size: clamp(2.2rem,5vw,4rem); font-weight: 600; line-height: 1.05; letter-spacing: -.035em; color: #fff; }
    .pricing-final p { max-width: 38rem; margin: 1rem auto 0; font-size: .95rem; line-height: 1.7; color: rgba(255,255,255,.68); }
    .pricing-final-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: .8rem; margin-top: 1.6rem; }

    @media (max-width: 1180px) {
        .pricing-plans { grid-template-columns: repeat(3,minmax(0,1fr)); }
        .pricing-products { grid-template-columns: repeat(2,minmax(0,1fr)); }
    }
    @media (max-width: 900px) {
        .pricing-hero-grid, .pricing-wallet-grid, .pricing-faq-grid { grid-template-columns: 1fr; }
        .pricing-hero-grid { gap: 2.5rem; }
        .pricing-ledger { max-width: 34rem; }
        .pricing-section-head { display: grid; }
        .pricing-cycle { justify-self: start; }
        .pricing-sources { grid-template-columns: 1fr; }
        .pricing-documents { grid-template-columns: 1fr 1fr; }
        .pricing-documents-intro { grid-column: 1 / -1; }
    }
    @media (max-width: 680px) {
        .pricing-shell { width: min(100% - 1.25rem, 80rem); }
        .pricing-hero { padding-top: 3.5rem; }
        .pricing-hero h1 { font-size: clamp(2.55rem,13vw,3.55rem); }
        .pricing-hero-copy { font-size: .96rem; line-height: 1.65; }
        .pricing-hero-actions { display: grid; grid-template-columns: 1fr; }
        .pricing-hero-actions > * { width: 100%; }
        .pricing-hero-facts { display: grid; gap: .65rem; }
        .pricing-ledger { padding: .75rem; border-radius: 1rem; }
        .pricing-balance-card { padding: 1.15rem; }
        .pricing-ledger-row { padding: .75rem; }
        .pricing-paths { margin-top: -1.25rem; }
        .pricing-paths-grid { grid-template-columns: 1fr; }
        .pricing-path + .pricing-path { border-left: 0; border-top: 1px solid var(--pricing-line); }
        .pricing-path { padding: 1.1rem; }
        .pricing-section { padding: 4.25rem 0; }
        .pricing-section-head { margin-bottom: 1.5rem; }
        .pricing-heading { font-size: 2.25rem; }
        .pricing-cycle { width: 100%; }
        .pricing-cycle button { flex: 1; padding-inline: .45rem; }
        .pricing-plans, .pricing-products { grid-template-columns: 1fr; }
        .pricing-plan { border-radius: .9rem; }
        .pricing-plan-body { padding: 1.25rem; }
        .pricing-plan-price { min-height: auto; }
        .pricing-plan-description { min-height: auto; }
        .pricing-plan-features { grid-template-columns: 1fr; }
        .pricing-product { min-height: auto; padding: 1.25rem; }
        .pricing-product-lock { margin-top: 1rem; }
        .pricing-sources { padding: 1.2rem; }
        .pricing-source-list { grid-template-columns: 1fr; }
        .pricing-source--wide { grid-column: auto; }
        .pricing-documents { grid-template-columns: 1fr; }
        .pricing-documents-intro { grid-column: auto; }
        .pricing-wallet-offers { grid-template-columns: 1fr; }
        .pricing-faq-answer { padding-left: 1.1rem; }
        .pricing-final-actions { display: grid; grid-template-columns: 1fr; }
        .pricing-final-actions > * { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .pricing-page *, .pricing-page *::before, .pricing-page *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
    }
</style>

<div class="pricing-page">
    <section class="pricing-hero" aria-labelledby="pricing-title">
        <div class="pricing-shell pricing-hero-grid">
            <div>
                <span class="pricing-kicker pricing-kicker--light">Preços claros, operação flexível</span>
                <h1 id="pricing-title" class="pricing-serif">Comece com <em>@brl($trialBalance) grátis</em>. Cresça no seu ritmo.</h1>
                <p class="pricing-hero-copy">
                    Escolha um plano para automatizar a rotina ou use saldo avulso quando precisar.
                    Você vê o preço antes de consultar e nunca confunde bônus de entrada com cobrança.
                </p>
                <div class="pricing-hero-actions">
                    <a href="{{ route('signup') }}" class="btn-cta">Criar conta com @brl($trialBalance)</a>
                    <a href="#planos" class="pricing-secondary-btn">
                        Comparar planos
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </a>
                </div>
                <div class="pricing-hero-facts" aria-label="Condições para começar">
                    <span class="pricing-hero-fact"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Sem cartão no cadastro</span>
                    <span class="pricing-hero-fact"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Bônus válido por {{ $trialDays }} dias</span>
                    <span class="pricing-hero-fact"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Saldo comprado não expira</span>
                </div>
            </div>

            @php
                // O que o saldo de boas-vindas compra, por produto de consulta (preço real do catálogo).
                $trialRuns = $products
                    ->where('is_gratuito', false)
                    ->where('price', '>', 0)
                    ->sortBy('price')
                    ->map(fn (array $p) => $p + ['runs' => (int) floor($trialBalance / $p['price'])])
                    ->values();
                $bestRun = (int) ($trialRuns->max('runs') ?? 0);
            @endphp

            <aside class="pricing-ledger" aria-label="Conta de demonstração">
                <div class="pricing-ledger-top">
                    <span class="pricing-ledger-label">Conta de demonstração</span>
                    <span class="pricing-live">Sem cartão</span>
                </div>

                <div class="pricing-balance-card">
                    <div class="pricing-balance-head">
                        <small>Saldo de boas-vindas</small>
                        <span class="pricing-balance-chip">{{ $trialDays }} dias</span>
                    </div>
                    <div class="pricing-balance-value">@brl($trialBalance)</div>
                    <p class="pricing-balance-note">Entra automaticamente no cadastro. Nenhum pagamento é exigido.</p>
                </div>

                <div class="pricing-runs">
                    <div class="pricing-runs-head">
                        <span>O que o saldo executa</span>
                        <em>preço por CNPJ</em>
                    </div>
                    @foreach($trialRuns as $run)
                        <div class="pricing-run" style="--fill: {{ $bestRun > 0 ? round($run['runs'] / $bestRun * 100) : 0 }}%; --delay: {{ $loop->index * 260 }}ms">
                            <span class="pricing-run-name">{{ $run['nome'] }}</span>
                            <span class="pricing-run-price">{{ \App\Support\Dinheiro::brl($run['price']) }}</span>
                            <span class="pricing-run-bar"><i></i></span>
                            <strong class="pricing-run-count">{{ $run['runs'] }} CNPJs</strong>
                        </div>
                    @endforeach
                </div>

                <div class="pricing-ledger-rows">
                    <div class="pricing-ledger-row">
                        <span>Primeiro plano pago</span>
                        <strong>{{ $firstPaidPlan ? 'R$ '.number_format($firstPaidPlan->preco_mensal_centavos / 100, 0, ',', '.') . '/mês' : 'consulte' }}</strong>
                    </div>
                    <div class="pricing-ledger-row">
                        <span>Recarga opcional a partir de</span>
                        <strong>R$ {{ number_format($minimumDeposit, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <p class="pricing-ledger-foot">O preço aparece antes de cada consulta. Saldo comprado não expira.</p>
            </aside>
        </div>
    </section>

    <div class="pricing-paths" aria-label="Formas de usar a FiscalDock">
        <div class="pricing-shell pricing-paths-grid">
            <article class="pricing-path">
                <span class="pricing-path-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h7"/></svg></span>
                <div><h2>Planos para rotina recorrente</h2><p>Mensalidade com saldo incluso, monitoramento automático, limites e recursos crescentes.</p></div>
            </article>
            <article class="pricing-path">
                <span class="pricing-path-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-2.2 0-4 .9-4 2s1.8 2 4 2 4 .9 4 2-1.8 2-4 2m0-8V6m0 10v2M5 5h14v14H5z"/></svg></span>
                <div><h2>Saldo avulso para uso pontual</h2><p>Sem assinatura obrigatória: adicione saldo e pague apenas pelas consultas executadas.</p></div>
            </article>
        </div>
    </div>

    <section id="planos" class="pricing-section pricing-section--soft" aria-labelledby="planos-title">
        <div class="pricing-shell">
            <header class="pricing-section-head">
                <div>
                    <span class="pricing-kicker">01 — Planos de assinatura</span>
                    <h2 id="planos-title" class="pricing-heading">Um plano para cada tamanho de operação.</h2>
                    <p class="pricing-lead">Os planos combinam acesso à plataforma, capacidade de monitoramento e saldo mensal. Se o saldo acabar, você pode complementar de forma avulsa.</p>
                </div>
                <div class="pricing-cycle" role="group" aria-label="Ciclo de cobrança">
                    <button type="button" data-billing-cycle="monthly" aria-pressed="true">Mensal</button>
                    <button type="button" data-billing-cycle="annual" aria-pressed="false">Anual <em>economize</em></button>
                </div>
            </header>

            <div class="pricing-plans">
                @foreach($subscriptionPlans as $plan)
                    @php
                        $caps = $plan->capabilities ?? [];
                        $isFree = $plan->codigo === 'free';
                        $isEnterprise = $plan->codigo === 'enterprise';
                        $isFeatured = $plan->codigo === 'profissional';
                        $monthlyPrice = $plan->preco_mensal_centavos / 100;
                        $annualPrice = $plan->preco_anual_centavos / 100;
                        $annualMonthlyEquivalent = $annualPrice > 0 ? $annualPrice / 12 : 0;
                        $includedBalance = app(\App\Services\PricingCatalogService::class)->creditsToCurrency($plan->creditos_inclusos);
                        $frequency = $frequencyLabels[$plan->frequencia_padrao_dias] ?? "a cada {$plan->frequencia_padrao_dias} dias";
                        $depth = $depthLabels[$plan->profundidade_auto_monitor] ?? $plan->profundidade_auto_monitor;
                        $exports = collect($caps['export'] ?? [])->map(fn ($export) => strtoupper($export === 'excel' ? 'XLSX' : $export))->implode(' + ');
                        $storageMb = array_key_exists('armazenamento_mb', $caps)
                            ? $caps['armazenamento_mb']
                            : config("arquivos.quota_por_plano_mb.{$plan->codigo}", config('arquivos.quota_padrao_mb', 250));
                        $storageLabel = $storageMb === null
                            ? 'Armazenamento de arquivos ilimitado'
                            : ($storageMb >= 1024
                                ? number_format($storageMb / 1024, 0, ',', '.').' GB para arquivos e comprovantes'
                                : number_format($storageMb, 0, ',', '.').' MB para arquivos e comprovantes');
                    @endphp
                    <article class="pricing-plan {{ $isFeatured ? 'pricing-plan--featured' : '' }}">
                        <div class="pricing-plan-band">{{ $isFeatured ? 'Mais completo para crescer' : ($isEnterprise ? 'Projeto sob medida' : 'Plano '.$plan->ordem) }}</div>
                        <div class="pricing-plan-body">
                            <span class="pricing-plan-name">{{ $plan->nome }}</span>
                            <div class="pricing-plan-price">
                                @if($isFree)
                                    <strong>Grátis</strong><small>sem cobrança recorrente</small>
                                @elseif($isEnterprise)
                                    <strong>Sob consulta</strong><small>escopo e implantação personalizados</small>
                                @else
                                    <strong data-plan-price data-monthly="{{ number_format($monthlyPrice, 2, '.', '') }}" data-annual-monthly="{{ number_format($annualMonthlyEquivalent, 2, '.', '') }}">R$&nbsp;{{ number_format($monthlyPrice, 0, ',', '.') }} <span>/mês</span></strong>
                                    <small data-plan-billing-note data-monthly-note="cobrança mensal" data-annual-note="R$ {{ number_format($annualPrice, 0, ',', '.') }} cobrados ao ano">cobrança mensal</small>
                                @endif
                            </div>
                            <p class="pricing-plan-description">{{ $planDescriptions[$plan->codigo] ?? 'Plano FiscalDock para organizar e acompanhar sua rotina fiscal.' }}</p>
                            <div class="pricing-plan-divider"></div>
                            <ul class="pricing-plan-features">
                                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span>{{ $includedBalance > 0 ? \App\Support\Dinheiro::brl($includedBalance).' de saldo por mês' : ($isEnterprise ? 'Saldo dimensionado para a operação' : 'Use o bônus ou saldo avulso') }}</span></li>
                                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span>{{ $plan->limite_clientes === null ? 'Clientes monitorados ilimitados' : $plan->limite_clientes.' cliente'.($plan->limite_clientes > 1 ? 's' : '').' monitorado'.($plan->limite_clientes > 1 ? 's' : '') }}</span></li>
                                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span>{{ $plan->limite_cnpjs_monitorados === null ? 'CNPJs monitorados ilimitados' : $plan->limite_cnpjs_monitorados.' CNPJ'.($plan->limite_cnpjs_monitorados > 1 ? 's' : '').' no monitoramento' }}</span></li>
                                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span>{{ $storageLabel }}</span></li>
                                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span>Monitoramento {{ $frequency }} · {{ $depth }}</span></li>
                                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span>{{ ($caps['bi'] ?? 'basico') === 'completo' ? 'BI Fiscal completo' : 'BI Fiscal básico' }}{{ $exports ? ' · exportação '.$exports : '' }}</span></li>
                                @if(($caps['pdf_executivo'] ?? false) || ($caps['score_historico'] ?? false))
                                    <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span>{{ ($caps['pdf_executivo'] ?? false) ? 'PDF executivo' : '' }}{{ ($caps['pdf_executivo'] ?? false) && ($caps['score_historico'] ?? false) ? ' + ' : '' }}{{ ($caps['score_historico'] ?? false) ? 'Score Fiscal com histórico' : '' }}</span></li>
                                @endif
                                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span>{{ $plan->assentos_inclusos >= 9999 ? 'Assentos ilimitados' : $plan->assentos_inclusos.' assento'.($plan->assentos_inclusos > 1 ? 's' : '') }}</span></li>
                            </ul>
                            <div class="pricing-plan-action">
                                <a href="{{ $isEnterprise ? route('agendar') : route('signup') }}" class="pricing-plan-btn">{{ $isEnterprise ? 'Falar com especialista' : ($isFree ? 'Começar grátis' : 'Escolher '.$plan->nome) }}</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <p class="pricing-plans-note"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 3.9L2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg><span>O saldo incluído é liberado por ciclo e segue as regras do plano. O saldo comprado separadamente não expira.</span></p>
        </div>
    </section>

    <section id="precos-consumo" class="pricing-section" aria-labelledby="consultas-title">
        <div class="pricing-shell">
            <header class="pricing-section-head">
                <div>
                    <span class="pricing-kicker">02 — Consultas por CNPJ</span>
                    <h2 id="consultas-title" class="pricing-heading">A profundidade certa para cada decisão.</h2>
                    <p class="pricing-lead">Escolha o nível antes de executar. O valor é debitado do saldo por CNPJ consultado, sem faixas escondidas.</p>
                </div>
            </header>

            <div class="pricing-products">
                @foreach($products as $index => $product)
                    <article class="pricing-product pricing-product--{{ $product['slug'] }}">
                        <div class="pricing-product-index"><span>0{{ $index + 1 }} / {{ str_pad((string) $products->count(), 2, '0', STR_PAD_LEFT) }}</span><span class="pricing-product-status">Operacional</span></div>
                        <h3>{{ $product['nome'] }}</h3>
                        <p class="pricing-product-price">
                            @if($product['price'] > 0)
                                R$&nbsp;{{ number_format($product['price'], 2, ',', '.') }} <span>/ CNPJ</span>
                            @else
                                Grátis <span>/ CNPJ</span>
                            @endif
                        </p>
                        <p class="pricing-product-description">{{ $product['descricao'] }}</p>
                        <ul>
                            @foreach($productHighlights[$product['slug']] ?? [] as $highlight)
                                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span>{{ $highlight }}</span></li>
                            @endforeach
                        </ul>
                        <p class="pricing-product-lock">
                            @if($product['slug'] === 'compliance')
                                Disponível após a primeira recarga confirmada.
                            @elseif($product['slug'] === 'gratuito')
                                O limite inicial deixa de valer após a primeira recarga.
                            @else
                                Débito feito somente quando a consulta é executada.
                            @endif
                        </p>
                    </article>
                @endforeach
            </div>

            @if($complianceSources->isNotEmpty())
                @php
                    $compliancePrice = $products->firstWhere('slug', 'compliance')['price'] ?? null;
                    $sourceGroups = $complianceSources
                        ->groupBy(fn (array $s) => str_contains($s['categoria'], 'Cadastral') ? 'Cadastrais' : (str_contains($s['categoria'], 'Fiscal') ? 'Fiscais' : 'Trabalhista e FGTS'))
                        ->map->count();
                @endphp
                <div class="pricing-sources">
                    <div class="pricing-sources-intro">
                        <h3>Compliance reúne {{ $complianceSources->count() }} fontes em uma consulta.</h3>
                        <p>Uma chamada, um custo, um resultado. Cadastro federal, certidões fiscais, trabalhista e FGTS chegam normalizados — com data, origem e status por fonte.</p>

                        <div class="pricing-sources-groups">
                            @foreach($sourceGroups as $grupo => $total)
                                <span><b>{{ $total }}</b>{{ $grupo }}</span>
                            @endforeach
                        </div>

                        @if($compliancePrice)
                            <div class="pricing-sources-price">
                                <small>Compliance por CNPJ</small>
                                <strong>{{ \App\Support\Dinheiro::brl($compliancePrice) }}</strong>
                                <span>≈ {{ \App\Support\Dinheiro::brl(round($compliancePrice / $complianceSources->count(), 2)) }} por fonte consultada</span>
                            </div>
                        @endif
                    </div>

                    <div class="pricing-source-list" aria-label="Fontes do Compliance">
                        @foreach($complianceSources as $source)
                            <article class="pricing-source {{ $loop->first ? 'pricing-source--wide' : '' }}" style="--delay: {{ $loop->index * 220 }}ms">
                                <div class="pricing-source-top">
                                    <span class="pricing-source-cat">{{ $source['categoria'] }}</span>
                                    <i aria-hidden="true"></i>
                                </div>
                                <strong>{{ $source['nome'] }}</strong>
                                <p>{{ $source['descricao_curta'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section id="clearance" class="pricing-section pricing-section--soft" aria-labelledby="clearance-title">
        <div class="pricing-shell">
            <header class="pricing-section-head">
                <div>
                    <span class="pricing-kicker">03 — Documentos fiscais</span>
                    <h2 id="clearance-title" class="pricing-heading">Clearance para conferir o documento, não só o CNPJ.</h2>
                    <p class="pricing-lead">Valide NF-e e CT-e do acervo ou consulte uma chave diretamente na SEFAZ. Mesma consulta oficial, mesmo preço por documento — muda só por onde você entra.</p>
                </div>
            </header>
            <div class="pricing-documents">
                <div class="pricing-documents-intro">
                    <div>
                        <span class="pricing-kicker pricing-kicker--light">Declared × official</span>
                        <p>O Clearance cruza o que foi declarado nas importações com o snapshot oficial do documento e centraliza os sinais de divergência.</p>
                    </div>
                    <span class="pricing-document-code">NF-e 55/65 · CT-e 57</span>
                </div>
                <article class="pricing-document-card">
                    <div class="pricing-document-top"><span>Acervo FiscalDock</span><span class="pricing-availability">Operacional</span></div>
                    <h3>Clearance do acervo</h3>
                    <p class="pricing-document-price">R$&nbsp;{{ number_format($clearancePricing['batch_basic'], 2, ',', '.') }} <span>/ documento</span></p>
                    <p>Para notas que já chegaram por SPED ou XML e precisam de conferência operacional.</p>
                    <ul><li>Status e snapshot SEFAZ</li><li>Validação contábil local</li><li>Processamento individual ou em lote</li></ul>
                </article>
                <article class="pricing-document-card">
                    <div class="pricing-document-top">
                        <span>Chave de acesso</span>
                        <span class="pricing-availability {{ empty($clearancePricing['search_enabled']) ? 'pricing-availability--limited' : '' }}">{{ !empty($clearancePricing['search_enabled']) ? 'Operacional' : 'Disponibilidade controlada' }}</span>
                    </div>
                    <h3>Busca avulsa</h3>
                    <p class="pricing-document-price">R$&nbsp;{{ number_format($clearancePricing['search'], 2, ',', '.') }} <span>/ documento</span></p>
                    <p>Consulta direta pela chave, sem precisar importar o documento antes.</p>
                    <ul><li>NF-e, NFC-e e CT-e</li><li>Consulta oficial por chave</li><li>Reconsulta somente com confirmação</li></ul>
                </article>
            </div>
        </div>
    </section>

    <section id="saldo-avulso" class="pricing-section pricing-wallet" aria-labelledby="saldo-title">
        <div class="pricing-shell pricing-wallet-grid">
            <div>
                <span class="pricing-kicker">04 — Saldo avulso</span>
                <h2 id="saldo-title" class="pricing-heading">Use sem assinatura. Complemente sem trocar de plano.</h2>
                <p class="pricing-lead">A recarga é opcional e só acontece quando você decide pagar. O saldo comprado fica na conta para usar no seu ritmo.</p>
                <ul class="pricing-wallet-proof">
                    <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span><strong>Recarga mínima de R$&nbsp;{{ number_format($minimumDeposit, 0, ',', '.') }}</strong> — diferente do bônus gratuito de @brl($trialBalance).</span></li>
                    <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span>Você pode informar qualquer valor acima do mínimo ou usar um dos atalhos de recarga.</span></li>
                    <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/></svg><span>Não há conversão confusa: os preços e o saldo são sempre apresentados em reais.</span></li>
                </ul>
            </div>
            <div class="pricing-wallet-card">
                <div class="pricing-wallet-custom">
                    <small>Valor personalizado</small>
                    <strong>A partir de R$&nbsp;{{ number_format($minimumDeposit, 0, ',', '.') }}</strong>
                    <p>Escolha o valor no checkout. Saldo comprado sem prazo de expiração.</p>
                </div>
                <div class="pricing-wallet-offers">
                    @foreach($featuredOffers as $offer)
                        <article class="pricing-wallet-offer">
                            <small>{{ $offer['nome'] }}</small>
                            <strong>R$&nbsp;{{ number_format($offer['preco'], 0, ',', '.') }}</strong>
                            <p>{{ $offer['descricao'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="pricing-section" aria-labelledby="faq-pricing-title">
        <div class="pricing-shell pricing-faq-grid">
            <div>
                <span class="pricing-kicker">05 — Sem letras miúdas</span>
                <h2 id="faq-pricing-title" class="pricing-heading">O essencial antes de decidir.</h2>
                <p class="pricing-lead">Planos e saldo avulso podem coexistir. Você escolhe a estrutura que faz sentido agora.</p>
                <p class="pricing-faq-help">Ainda ficou alguma dúvida? <a href="{{ route('agendar') }}">Converse com um especialista →</a></p>
            </div>
            <div>
                <details class="pricing-faq-item" open>
                    <summary><span class="pricing-faq-num">01</span><span class="pricing-faq-question">Eu recebo @brl($trialBalance) ou preciso pagar R$&nbsp;{{ number_format($minimumDeposit, 0, ',', '.') }} para começar?</span><span class="pricing-faq-plus" aria-hidden="true"></span></summary>
                    <div class="pricing-faq-answer">Você recebe @brl($trialBalance) grátis ao criar a conta, sem cartão. Os R$&nbsp;{{ number_format($minimumDeposit, 0, ',', '.') }} são apenas o mínimo de uma recarga paga, caso você decida adicionar saldo depois.</div>
                </details>
                <details class="pricing-faq-item">
                    <summary><span class="pricing-faq-num">02</span><span class="pricing-faq-question">Preciso assinar um plano para fazer consultas?</span><span class="pricing-faq-plus" aria-hidden="true"></span></summary>
                    <div class="pricing-faq-answer">Não. Você pode permanecer no Free e usar saldo avulso. Os planos pagos fazem mais sentido para quem precisa de monitoramento automático, limites maiores, recursos analíticos e saldo mensal incluído.</div>
                </details>
                <details class="pricing-faq-item">
                    <summary><span class="pricing-faq-num">03</span><span class="pricing-faq-question">O saldo expira?</span><span class="pricing-faq-plus" aria-hidden="true"></span></summary>
                    <div class="pricing-faq-answer">O saldo comprado separadamente não expira. O bônus gratuito do cadastro é promocional e vale por {{ $trialDays }} dias. O saldo incluído em assinatura segue o ciclo e as regras do plano.</div>
                </details>
                <details class="pricing-faq-item">
                    <summary><span class="pricing-faq-num">04</span><span class="pricing-faq-question">Qual é a vantagem do ciclo anual?</span><span class="pricing-faq-plus" aria-hidden="true"></span></summary>
                    <div class="pricing-faq-answer">O seletor acima mostra o valor mensal equivalente e o total cobrado no ano para cada plano. Assim você compara a condição anual vigente com a cobrança mês a mês antes de escolher.</div>
                </details>
                <details class="pricing-faq-item">
                    <summary><span class="pricing-faq-num">05</span><span class="pricing-faq-question">Posso mudar de plano depois?</span><span class="pricing-faq-plus" aria-hidden="true"></span></summary>
                    <div class="pricing-faq-answer">Sim. A área autenticada permite upgrade, downgrade e cancelamento. Seus dados e histórico permanecem na conta; os limites do novo plano passam a valer na troca.</div>
                </details>
            </div>
        </div>
    </section>

    <section class="pricing-final" aria-labelledby="pricing-final-title">
        <div class="pricing-shell pricing-final-inner">
            <span class="pricing-kicker pricing-kicker--light">Seu primeiro dossiê começa aqui</span>
            <h2 id="pricing-final-title">Teste a FiscalDock com @brl($trialBalance). Sem pagar para descobrir se faz sentido.</h2>
            <p>Crie a conta, importe seus dados e use o bônus por {{ $trialDays }} dias. Plano ou recarga só entram quando você decidir avançar.</p>
            <div class="pricing-final-actions">
                <a href="{{ route('signup') }}" class="btn-cta">Criar conta grátis</a>
                <a href="{{ route('agendar') }}" class="pricing-secondary-btn">Falar com especialista</a>
            </div>
        </div>
    </section>
</div>
