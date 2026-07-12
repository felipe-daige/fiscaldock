@php
    $trialBalance = (float) config('trial.saldo_reais');
    $trialDays = (int) config('trial.validade_dias');
    $reformRates = (array) config('reforma.aliquotas_por_fase', []);
    $fullReformRate = (float) config('reforma.aliquota_referencia', 0.285);
    $activeCnpjSources = 7; // Minha Receita + as 6 fontes InfoSimples operacionais.

    $solutionCatalog = [
        ['name' => 'Importação EFD e XML', 'description' => 'EFD ICMS/IPI, EFD PIS/COFINS e XML de NF-e com extração e histórico.', 'anchor' => 'documentos'],
        ['name' => 'Acervo e catálogo fiscal', 'description' => 'Notas unificadas, itens, NCM, CFOP, CST e histórico do cadastro 0200.', 'anchor' => 'documentos'],
        ['name' => 'Resumo e BI Fiscal', 'description' => 'Competências, apurações, faturamento, compras, tributos e cruzamentos.', 'anchor' => 'inteligencia'],
        ['name' => 'Consultas e monitoramento CNPJ', 'description' => 'Regularidade cadastral, fiscal e trabalhista em consultas avulsas ou recorrentes.', 'anchor' => 'risco'],
        ['name' => 'Score Fiscal', 'description' => 'Leitura consolidada da regularidade e dos sinais de risco de cada contraparte.', 'anchor' => 'risco'],
        ['name' => 'Reforma Tributária e crédito IBS/CBS', 'description' => 'Estimativa de crédito potencial, aproveitável e em risco por fornecedor.', 'anchor' => 'reforma'],
        ['name' => 'Clearance de NF-e e CT-e', 'description' => 'Confronto entre documento declarado e snapshot oficial por chave.', 'anchor' => 'clearance'],
        ['name' => 'Alertas, dossiês e exportações', 'description' => 'Fila de trabalho, histórico, relatórios PDF, XLSX e CSV.', 'anchor' => 'acao'],
    ];
@endphp

@push('structured-data')
@include('landing_page.partials.breadcrumb-schema', [
    'trail' => [
        ['name' => 'Início', 'url' => url('/')],
        ['name' => 'Soluções', 'url' => url('/solucoes')],
    ],
])
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Soluções FiscalDock',
    'itemListElement' => collect($solutionCatalog)->map(fn ($solution, $index) => [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $solution['name'],
        'description' => $solution['description'],
        'url' => url('/solucoes#'.$solution['anchor']),
    ])->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

<style>
    .solutions-page {
        --sol-ink: #0b1424;
        --sol-navy: #10233f;
        --sol-blue: #1e4fa0;
        --sol-sky: #dcecff;
        --sol-paper: #f7f4ed;
        --sol-soft: #f5f7fa;
        --sol-line: #dfe5ec;
        --sol-muted: #5f6b7a;
        --sol-yellow: #facc15;
        --sol-green: #047857;
        color: #111827;
        background: #fff;
        overflow: clip;
    }
    .solutions-page *, .solutions-page *::before, .solutions-page *::after { box-sizing: border-box; }
    .sol-shell { width: min(100% - 2rem, 80rem); margin-inline: auto; }
    .sol-serif { font-family: 'Fraunces', Georgia, serif; font-optical-sizing: auto; letter-spacing: -.025em; }
    .sol-kicker {
        display: inline-flex; align-items: center; gap: .65rem;
        font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
        font-size: .67rem; font-weight: 700; letter-spacing: .2em; line-height: 1.25;
        text-transform: uppercase; color: #728095;
    }
    .sol-kicker::before { content: ''; width: 1.65rem; height: 1px; background: currentColor; opacity: .5; }
    .sol-kicker--light { color: rgba(255,255,255,.62); }
    .sol-heading {
        margin-top: .9rem; font-family: 'Fraunces', Georgia, serif;
        font-size: clamp(2.25rem, 4.6vw, 4rem); font-weight: 600; line-height: 1.03;
        letter-spacing: -.038em; color: var(--sol-ink);
    }
    .sol-lead { margin-top: 1rem; max-width: 44rem; font-size: 1rem; line-height: 1.75; color: var(--sol-muted); }

    .sol-hero {
        position: relative; isolation: isolate;
        padding: clamp(4.5rem, 9vw, 7.5rem) 0 clamp(4rem, 8vw, 7rem);
        color: #fff;
        background:
            radial-gradient(circle at 83% 18%, rgba(55,116,198,.25), transparent 30rem),
            linear-gradient(145deg, #081322 0%, #10233f 62%, #0d1c32 100%);
    }
    .sol-hero::before, .sol-dark-grid::before {
        content: ''; position: absolute; inset: 0; z-index: -1; pointer-events: none;
        background-image:
            linear-gradient(to right, rgba(148,197,255,.055) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(148,197,255,.055) 1px, transparent 1px);
        background-size: 46px 46px;
        -webkit-mask-image: radial-gradient(100% 100% at 70% 15%, #000 10%, transparent 78%);
        mask-image: radial-gradient(100% 100% at 70% 15%, #000 10%, transparent 78%);
    }
    .sol-hero-grid { display: grid; grid-template-columns: minmax(0,1.05fr) minmax(23rem,.8fr); gap: clamp(2.5rem,7vw,6rem); align-items: center; }
    .sol-hero h1 { margin-top: 1.15rem; max-width: 48rem; font-size: clamp(2.9rem,6vw,5.45rem); font-weight: 600; line-height: .97; color: #fff; }
    .sol-hero h1 em { color: #fde68a; font-style: normal; }
    .sol-hero-copy { margin-top: 1.35rem; max-width: 42rem; font-size: clamp(1rem,1.45vw,1.16rem); line-height: 1.75; color: rgba(255,255,255,.72); }
    .sol-hero-actions { display: flex; flex-wrap: wrap; gap: .8rem; margin-top: 1.8rem; }
    .sol-btn-secondary {
        display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 48px;
        padding: .875rem 1.35rem; border: 1px solid rgba(255,255,255,.27); border-radius: 8px;
        color: #fff; font-size: .94rem; font-weight: 700; text-decoration: none; transition: .18s ease;
    }
    .sol-btn-secondary:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.5); }
    .sol-hero-facts { display: flex; flex-wrap: wrap; gap: .7rem 1.35rem; margin-top: 1.6rem; }
    .sol-hero-fact { display: inline-flex; align-items: center; gap: .45rem; font-size: .75rem; color: rgba(255,255,255,.66); }
    .sol-hero-fact::before { content: ''; width: .42rem; height: .42rem; border-radius: 50%; background: #fde68a; }

    .sol-radar {
        position: relative; border: 1px solid rgba(255,255,255,.15); border-radius: 1.3rem;
        padding: 1rem; background: rgba(7,17,31,.64); box-shadow: 0 36px 80px -40px rgba(0,0,0,.75);
        backdrop-filter: blur(14px);
    }
    .sol-radar-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .2rem .25rem 1rem; }
    .sol-radar-label { font-family: ui-monospace, monospace; font-size: .61rem; letter-spacing: .16em; text-transform: uppercase; color: rgba(255,255,255,.47); }
    .sol-radar-live { display: inline-flex; align-items: center; gap: .4rem; font-size: .65rem; font-weight: 700; color: #bbf7d0; }
    .sol-radar-live::before { content: ''; width: .42rem; height: .42rem; border-radius: 50%; background: #34d399; box-shadow: 0 0 0 4px rgba(52,211,153,.12); }
    .sol-radar-stage { border: 1px solid rgba(255,255,255,.09); border-radius: .85rem; padding: 1rem; background: rgba(255,255,255,.045); }
    .sol-radar-stage + .sol-radar-stage { margin-top: .55rem; }
    .sol-radar-stage-head { display: flex; align-items: center; justify-content: space-between; gap: .8rem; }
    .sol-radar-stage-head span:first-child { font-family: ui-monospace, monospace; font-size: .62rem; letter-spacing: .12em; text-transform: uppercase; color: rgba(255,255,255,.5); }
    .sol-radar-stage-head strong { font-size: .72rem; color: #fff; }
    .sol-radar-track { height: 4px; margin-top: .7rem; border-radius: 999px; background: rgba(255,255,255,.1); overflow: hidden; }
    .sol-radar-track i { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg,#fde68a,#34d399); }
    .sol-radar-output { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: .55rem; margin-top: .7rem; }
    .sol-radar-kpi { border-radius: .65rem; padding: .75rem; color: var(--sol-ink); background: linear-gradient(145deg,#fffef8,#f7edcf); }
    .sol-radar-kpi small { display: block; font-family: ui-monospace, monospace; font-size: .53rem; letter-spacing: .1em; text-transform: uppercase; color: #747b86; }
    .sol-radar-kpi strong { display: block; margin-top: .35rem; font-family: 'Fraunces',Georgia,serif; font-size: 1.25rem; color: var(--sol-ink); }

    .sol-anchor-nav { position: sticky; top: 0; z-index: 35; border-bottom: 1px solid rgba(207,216,227,.9); background: rgba(255,255,255,.91); backdrop-filter: blur(14px) saturate(150%); }
    .sol-anchor-list { display: flex; gap: .25rem; align-items: center; overflow-x: auto; padding: .65rem 0; scrollbar-width: none; }
    .sol-anchor-list::-webkit-scrollbar { display: none; }
    .sol-anchor-link { flex: 0 0 auto; border-radius: .55rem; padding: .62rem .78rem; color: #687386; font-size: .7rem; font-weight: 700; text-decoration: none; transition: .15s ease; }
    .sol-anchor-link:hover, .sol-anchor-link.is-active { color: #fff; background: var(--sol-ink); }

    .sol-section { padding: clamp(4.75rem,8vw,7.5rem) 0; }
    .sol-section--soft { background: var(--sol-soft); }
    .sol-section--paper { background: var(--sol-paper); }
    .sol-section-head { display: flex; align-items: end; justify-content: space-between; gap: 2rem; margin-bottom: 2.2rem; }
    .sol-section-head > p { max-width: 31rem; font-size: .82rem; line-height: 1.65; color: #667085; }

    .sol-journey { display: grid; grid-template-columns: repeat(5,minmax(0,1fr)); border: 1px solid var(--sol-line); border-radius: 1rem; background: #fff; overflow: hidden; }
    .sol-journey-step { position: relative; min-height: 13rem; padding: 1.25rem; }
    .sol-journey-step + .sol-journey-step { border-left: 1px solid var(--sol-line); }
    .sol-journey-num { font-family: ui-monospace, monospace; font-size: .6rem; font-weight: 700; letter-spacing: .14em; color: #8b96a5; }
    .sol-journey-step h3 { margin-top: 1.35rem; font-family: 'Fraunces',Georgia,serif; font-size: 1.25rem; font-weight: 650; color: var(--sol-ink); }
    .sol-journey-step p { margin-top: .6rem; font-size: .73rem; line-height: 1.55; color: #667085; }
    .sol-journey-token { position: absolute; right: 1rem; bottom: 1rem; display: grid; place-items: center; width: 2.25rem; height: 2.25rem; border-radius: .65rem; color: var(--sol-blue); background: #edf4fd; }
    .sol-journey-token svg { width: 1.05rem; height: 1.05rem; }

    .sol-chapter { display: grid; grid-template-columns: minmax(17rem,.58fr) minmax(0,1.42fr); gap: clamp(2.5rem,6vw,5.5rem); align-items: start; }
    .sol-chapter-intro { position: sticky; top: 5rem; }
    .sol-chapter-index { display: inline-flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; border: 1px solid #d6e0eb; border-radius: .75rem; font-family: ui-monospace, monospace; font-size: .72rem; font-weight: 700; color: var(--sol-blue); background: #f8fbff; }
    .sol-chapter h2 { margin-top: 1rem; font-family: 'Fraunces',Georgia,serif; font-size: clamp(2rem,3.5vw,3.15rem); font-weight: 600; line-height: 1.04; letter-spacing: -.034em; color: var(--sol-ink); }
    .sol-chapter-intro p { margin-top: 1rem; font-size: .9rem; line-height: 1.72; color: #667085; }
    .sol-text-link { display: inline-flex; align-items: center; gap: .4rem; margin-top: 1.25rem; color: var(--sol-blue); font-size: .78rem; font-weight: 750; text-decoration: none; }
    .sol-text-link:hover { text-decoration: underline; }

    .sol-feature-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: .85rem; }
    .sol-feature-card { display: flex; min-height: 13.5rem; flex-direction: column; border: 1px solid var(--sol-line); border-radius: .95rem; padding: 1.25rem; background: #fff; transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
    .sol-feature-card:hover { transform: translateY(-3px); border-color: #b9c9da; box-shadow: 0 22px 45px -34px rgba(15,35,65,.5); }
    .sol-feature-top { display: flex; align-items: center; justify-content: space-between; gap: .8rem; }
    .sol-feature-icon { display: grid; place-items: center; width: 2.45rem; height: 2.45rem; border-radius: .7rem; color: var(--sol-blue); background: #edf4fd; }
    .sol-feature-icon svg { width: 1.15rem; height: 1.15rem; }
    .sol-feature-state { display: inline-flex; align-items: center; gap: .35rem; font-family: ui-monospace, monospace; font-size: .55rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--sol-green); }
    .sol-feature-state::before { content: ''; width: .38rem; height: .38rem; border-radius: 50%; background: #10b981; }
    .sol-feature-card h3 { margin-top: 1rem; font-family: 'Fraunces',Georgia,serif; font-size: 1.25rem; font-weight: 650; color: var(--sol-ink); }
    .sol-feature-card p { margin-top: .55rem; font-size: .75rem; line-height: 1.6; color: #667085; }
    .sol-feature-meta { margin-top: auto; padding-top: 1rem; font-family: ui-monospace, monospace; font-size: .57rem; line-height: 1.5; letter-spacing: .07em; text-transform: uppercase; color: #8a94a3; }
    .sol-feature-card--wide { grid-column: span 2; min-height: auto; }

    .sol-credit-bridge { border-color: #182c49; color: #fff; background: linear-gradient(145deg,#0b1729,#132a49); box-shadow: 0 25px 55px -38px rgba(11,20,36,.72); }
    .sol-credit-bridge:hover { border-color: #294b78; }
    .sol-credit-bridge .sol-feature-state { color: #a7f3d0; }
    .sol-credit-bridge h3 { color: #fff; }
    .sol-credit-bridge > p { max-width: 48rem; color: rgba(255,255,255,.65); }
    .sol-bridge-flow { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: .55rem; margin-top: 1rem; }
    .sol-bridge-step { border: 1px solid rgba(255,255,255,.1); border-radius: .7rem; padding: .8rem; background: rgba(255,255,255,.045); }
    .sol-bridge-step small { display: block; font-family: ui-monospace, monospace; font-size: .5rem; letter-spacing: .11em; text-transform: uppercase; color: rgba(255,255,255,.4); }
    .sol-bridge-step strong { display: block; margin-top: .35rem; font-size: .7rem; line-height: 1.42; color: #fff; }
    .sol-bridge-note { margin-top: .8rem; border-left: 2px solid rgba(253,230,138,.45); padding-left: .75rem; font-size: .62rem; line-height: 1.55; color: rgba(255,255,255,.5); }

    .sol-console { border: 1px solid #d6deea; border-radius: 1rem; overflow: hidden; background: #fff; box-shadow: 0 28px 65px -45px rgba(15,35,65,.45); }
    .sol-console-bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .75rem 1rem; border-bottom: 1px solid #e5e9ef; background: #f8fafc; }
    .sol-console-dots { display: flex; gap: .35rem; }
    .sol-console-dots i { width: .48rem; height: .48rem; border-radius: 50%; background: #cbd5e1; }
    .sol-console-bar span { font-family: ui-monospace, monospace; font-size: .58rem; letter-spacing: .1em; text-transform: uppercase; color: #7a8594; }
    .sol-console-body { padding: 1.2rem; }
    .sol-console-kpis { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: .65rem; }
    .sol-console-kpi { border: 1px solid #e1e6ed; border-radius: .7rem; padding: .85rem; }
    .sol-console-kpi small { display: block; font-size: .58rem; text-transform: uppercase; color: #8993a1; }
    .sol-console-kpi strong { display: block; margin-top: .35rem; font-family: 'Fraunces',Georgia,serif; font-size: 1.35rem; color: var(--sol-ink); }
    .sol-console-chart { display: grid; grid-template-columns: minmax(0,1.35fr) minmax(12rem,.65fr); gap: .75rem; margin-top: .75rem; }
    .sol-chart-box, .sol-list-box { border: 1px solid #e1e6ed; border-radius: .75rem; padding: 1rem; }
    .sol-chart-bars { display: flex; height: 8.5rem; align-items: end; gap: .55rem; padding-top: 1rem; }
    .sol-chart-bars i { flex: 1; min-height: 12%; border-radius: .35rem .35rem 0 0; background: linear-gradient(180deg,#4b82cb,#1e4fa0); }
    .sol-list-row { display: grid; grid-template-columns: auto 1fr auto; gap: .5rem; align-items: center; padding: .6rem 0; font-size: .66rem; color: #536071; }
    .sol-list-row + .sol-list-row { border-top: 1px solid #edf0f4; }
    .sol-list-row::before { content: ''; width: .4rem; height: .4rem; border-radius: 50%; background: #10b981; }

    .sol-source-cloud { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 1rem; }
    .sol-source-chip { display: inline-flex; align-items: center; gap: .4rem; border: 1px solid #d7e1ec; border-radius: 999px; padding: .5rem .65rem; background: #fff; font-size: .64rem; font-weight: 650; color: #3f4a59; }
    .sol-source-chip::before { content: ''; width: .38rem; height: .38rem; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 3px #d1fae5; }

    .sol-cnpj-workspace { display: grid; grid-template-columns: minmax(20rem,.78fr) minmax(0,1.22fr); gap: .8rem; border: 1px solid #d8e0e9; border-radius: 1.15rem; padding: .8rem; background: #f4f7fa; box-shadow: 0 30px 70px -48px rgba(15,35,65,.55); }
    .sol-cnpj-file { display: flex; flex-direction: column; min-width: 0; border: 1px solid #dbe2ea; border-radius: .9rem; padding: 1.15rem; background: #fff; }
    .sol-cnpj-file-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e8ecf1; }
    .sol-cnpj-file-label { font-family: ui-monospace, monospace; font-size: .56rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #8a94a3; }
    .sol-cnpj-file h3 { margin-top: .45rem; font-family: 'Fraunces',Georgia,serif; font-size: 1.3rem; font-weight: 650; color: var(--sol-ink); }
    .sol-cnpj-doc { display: block; margin-top: .25rem; font-family: ui-monospace, monospace; font-size: .62rem; color: #788391; }
    .sol-cnpj-badge { display: inline-flex; flex: 0 0 auto; align-items: center; gap: .35rem; border-radius: 999px; padding: .38rem .55rem; font-size: .57rem; font-weight: 750; text-transform: uppercase; color: #047857; background: #ecfdf5; }
    .sol-cnpj-badge::before { content: ''; width: .38rem; height: .38rem; border-radius: 50%; background: #10b981; }
    .sol-cnpj-score { display: grid; grid-template-columns: auto 1fr; gap: 1rem; align-items: center; margin-top: 1rem; border-radius: .8rem; padding: 1rem; color: #fff; background: var(--sol-ink); }
    .sol-score-dial { position: relative; display: grid; place-items: center; width: 4.5rem; height: 4.5rem; border-radius: 50%; background: conic-gradient(#34d399 0 18%, rgba(255,255,255,.12) 18%); }
    .sol-score-dial::before { content: ''; position: absolute; inset: .38rem; border-radius: 50%; background: var(--sol-ink); }
    .sol-score-dial strong { position: relative; font-family: 'Fraunces',Georgia,serif; font-size: 1.45rem; color: #fff; }
    .sol-score-copy small { display: block; font-family: ui-monospace, monospace; font-size: .54rem; letter-spacing: .12em; text-transform: uppercase; color: rgba(255,255,255,.42); }
    .sol-score-copy strong { display: block; margin-top: .35rem; font-size: .78rem; color: #a7f3d0; }
    .sol-score-copy span { display: block; margin-top: .25rem; font-size: .62rem; line-height: 1.4; color: rgba(255,255,255,.52); }
    .sol-cnpj-signals { margin-top: .65rem; }
    .sol-cnpj-signal { display: grid; grid-template-columns: auto minmax(0,1fr) auto; gap: .55rem; align-items: center; padding: .65rem 0; font-size: .67rem; color: #4d5968; }
    .sol-cnpj-signal + .sol-cnpj-signal { border-top: 1px solid #edf0f4; }
    .sol-cnpj-signal::before { content: ''; width: .42rem; height: .42rem; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 3px #d1fae5; }
    .sol-cnpj-signal--warning::before { background: #f59e0b; box-shadow: 0 0 0 3px #fef3c7; }
    .sol-cnpj-signal strong { font-size: .61rem; font-weight: 750; color: #344054; }
    .sol-cnpj-file-foot { margin-top: auto; padding-top: .8rem; border-top: 1px dashed #dce2e9; font-family: ui-monospace, monospace; font-size: .55rem; line-height: 1.5; text-transform: uppercase; color: #8a94a3; }
    .sol-cnpj-analysis { min-width: 0; border: 1px solid #dbe2ea; border-radius: .9rem; padding: 1.15rem; background: #fff; }
    .sol-cnpj-analysis-head { display: flex; align-items: end; justify-content: space-between; gap: 1rem; }
    .sol-cnpj-analysis-head h3 { font-family: 'Fraunces',Georgia,serif; font-size: 1.35rem; font-weight: 650; color: var(--sol-ink); }
    .sol-cnpj-analysis-head p { max-width: 23rem; font-size: .66rem; line-height: 1.5; text-align: right; color: #7a8594; }
    .sol-cnpj-layers { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: .6rem; margin-top: 1rem; }
    .sol-cnpj-layer { position: relative; min-height: 9rem; border: 1px solid #e1e6ec; border-radius: .75rem; padding: .9rem; overflow: hidden; }
    .sol-cnpj-layer::after { content: attr(data-layer); position: absolute; right: .65rem; bottom: -.3rem; font-family: 'Fraunces',Georgia,serif; font-size: 3.6rem; font-weight: 650; line-height: 1; color: #f0f3f7; pointer-events: none; }
    .sol-cnpj-layer small { position: relative; z-index: 1; font-family: ui-monospace, monospace; font-size: .52rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--sol-blue); }
    .sol-cnpj-layer h4 { position: relative; z-index: 1; margin-top: .6rem; font-size: .76rem; font-weight: 750; color: #202938; }
    .sol-cnpj-layer p { position: relative; z-index: 1; margin-top: .35rem; max-width: 14rem; font-size: .62rem; line-height: 1.48; color: #667085; }
    .sol-cnpj-actions { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: .5rem; margin-top: .65rem; }
    .sol-cnpj-action { display: grid; grid-template-columns: auto 1fr; gap: .55rem; align-items: center; border: 1px solid #dbe3ec; border-radius: .7rem; padding: .72rem; background: #f8fafc; }
    .sol-cnpj-action i { display: grid; place-items: center; width: 1.7rem; height: 1.7rem; border-radius: .5rem; font-style: normal; font-size: .61rem; font-weight: 800; color: var(--sol-blue); background: #eaf2fc; }
    .sol-cnpj-action small { display: block; font-size: .54rem; color: #8a94a3; }
    .sol-cnpj-action strong { display: block; margin-top: .15rem; font-size: .63rem; color: #344054; }
    .sol-cnpj-sources { display: grid; grid-template-columns: auto 1fr; gap: 1rem; align-items: start; margin-top: .8rem; border-top: 1px solid #e8ecf1; padding-top: .8rem; }
    .sol-cnpj-sources > span { padding-top: .45rem; font-family: ui-monospace, monospace; font-size: .53rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #8a94a3; }

    .sol-reform { position: relative; isolation: isolate; color: #fff; background: linear-gradient(145deg,#081322,#10233f 64%,#0d1c32); }
    .sol-reform .sol-heading { color: #fff; max-width: 55rem; }
    .sol-reform .sol-lead { color: rgba(255,255,255,.68); }
    .sol-reform-grid { display: grid; grid-template-columns: minmax(0,.8fr) minmax(24rem,1.2fr); gap: clamp(2.5rem,7vw,6rem); align-items: center; }
    .sol-reform-bullets { display: grid; gap: .7rem; margin-top: 1.4rem; }
    .sol-reform-bullet { display: grid; grid-template-columns: auto 1fr; gap: .65rem; align-items: start; font-size: .8rem; line-height: 1.55; color: rgba(255,255,255,.73); }
    .sol-reform-bullet::before { content: '✓'; color: #fde68a; font-weight: 800; }
    .sol-credit-sheet { border: 1px solid rgba(255,255,255,.15); border-radius: 1.15rem; padding: 1rem; background: rgba(7,17,31,.62); backdrop-filter: blur(14px); }
    .sol-credit-sheet-head { display: flex; align-items: center; justify-content: space-between; gap: .8rem; padding: .25rem .25rem 1rem; }
    .sol-credit-sheet-head span { font-family: ui-monospace, monospace; font-size: .59rem; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.48); }
    .sol-credit-sheet-head strong { font-size: .66rem; color: #a7f3d0; }
    .sol-credit-formula { border-radius: .85rem; padding: 1.2rem; color: var(--sol-ink); background: linear-gradient(145deg,#fffef8,#f7edcf); }
    .sol-credit-formula small { font-family: ui-monospace, monospace; font-size: .58rem; letter-spacing: .12em; text-transform: uppercase; color: #7b8491; }
    .sol-credit-formula strong { display: block; margin-top: .6rem; font-family: 'Fraunces',Georgia,serif; font-size: clamp(1.45rem,3vw,2.25rem); font-weight: 650; line-height: 1.1; color: var(--sol-ink); }
    .sol-credit-steps { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: .55rem; margin-top: .65rem; }
    .sol-credit-step { border: 1px solid rgba(255,255,255,.1); border-radius: .7rem; padding: .8rem; background: rgba(255,255,255,.045); }
    .sol-credit-step small { display: block; font-family: ui-monospace, monospace; font-size: .52rem; letter-spacing: .1em; text-transform: uppercase; color: rgba(255,255,255,.43); }
    .sol-credit-step strong { display: block; margin-top: .35rem; font-size: .74rem; line-height: 1.4; color: #fff; }
    .sol-transition { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: .5rem; margin-top: .65rem; }
    .sol-transition-year { border-top: 2px solid rgba(253,230,138,.45); padding: .7rem .15rem 0; }
    .sol-transition-year strong { display: block; font-family: 'Fraunces',Georgia,serif; font-size: 1.05rem; color: #fde68a; }
    .sol-transition-year span { display: block; margin-top: .25rem; font-size: .57rem; line-height: 1.4; color: rgba(255,255,255,.5); }
    .sol-reform-note { margin-top: 1rem; border-left: 2px solid rgba(253,230,138,.45); padding-left: .85rem; font-size: .67rem; line-height: 1.55; color: rgba(255,255,255,.5); }

    .sol-clearance-grid { display: grid; grid-template-columns: minmax(0,.75fr) minmax(0,1.25fr); gap: clamp(2.5rem,7vw,6rem); align-items: center; }
    .sol-compare { border: 1px solid #d9e1ea; border-radius: 1rem; padding: 1rem; background: #fff; box-shadow: 0 28px 65px -45px rgba(15,35,65,.45); }
    .sol-compare-head { display: grid; grid-template-columns: 1fr auto 1fr; gap: .6rem; align-items: center; padding: .35rem .2rem 1rem; }
    .sol-compare-head span { font-family: ui-monospace, monospace; font-size: .58rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #7c8796; }
    .sol-compare-head span:last-child { text-align: right; }
    .sol-compare-head i { width: 2rem; height: 1px; background: #cbd5e1; }
    .sol-compare-row { display: grid; grid-template-columns: minmax(0,1fr) auto minmax(0,1fr); gap: .75rem; align-items: center; border: 1px solid #e2e7ed; border-radius: .7rem; padding: .75rem; }
    .sol-compare-row + .sol-compare-row { margin-top: .5rem; }
    .sol-compare-value { font-size: .69rem; line-height: 1.4; color: #3f4a59; }
    .sol-compare-value:last-child { text-align: right; }
    .sol-match { display: grid; place-items: center; width: 1.7rem; height: 1.7rem; border-radius: 50%; font-size: .7rem; font-weight: 800; color: var(--sol-green); background: #ecfdf5; }
    .sol-match--alert { color: #b45309; background: #fffbeb; }

    .sol-atlas { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: .75rem; }
    .sol-atlas-card { min-height: 10.5rem; border: 1px solid var(--sol-line); border-radius: .85rem; padding: 1rem; background: #fff; }
    .sol-atlas-card span { font-family: ui-monospace, monospace; font-size: .56rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--sol-blue); }
    .sol-atlas-card h3 { margin-top: .75rem; font-family: 'Fraunces',Georgia,serif; font-size: 1.08rem; font-weight: 650; color: var(--sol-ink); }
    .sol-atlas-card p { margin-top: .45rem; font-size: .7rem; line-height: 1.55; color: #667085; }

    .sol-final { position: relative; isolation: isolate; padding: clamp(4.5rem,8vw,7rem) 0; text-align: center; color: #fff; background: var(--sol-ink); }
    .sol-final h2 { max-width: 55rem; margin: 1rem auto 0; font-family: 'Fraunces',Georgia,serif; font-size: clamp(2.35rem,5vw,4.25rem); font-weight: 600; line-height: 1.04; letter-spacing: -.038em; }
    .sol-final p { max-width: 42rem; margin: 1rem auto 0; font-size: .95rem; line-height: 1.7; color: rgba(255,255,255,.67); }
    .sol-final-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: .8rem; margin-top: 1.6rem; }

    [data-sol-reveal] { opacity: 1; transform: none; }
    .js .solutions-page [data-sol-reveal] { opacity: 0; transform: translateY(18px); transition: opacity .55s ease, transform .55s ease; }
    .js .solutions-page [data-sol-reveal].is-visible { opacity: 1; transform: none; }

    @media (max-width: 1080px) {
        .sol-journey { grid-template-columns: repeat(3,minmax(0,1fr)); }
        .sol-journey-step + .sol-journey-step { border-left: 0; }
        .sol-journey-step { border: 1px solid var(--sol-line); margin: -1px 0 0 -1px; }
        .sol-atlas { grid-template-columns: repeat(2,minmax(0,1fr)); }
    }
    @media (max-width: 900px) {
        .sol-hero-grid, .sol-reform-grid, .sol-clearance-grid, .sol-chapter { grid-template-columns: 1fr; }
        .sol-cnpj-workspace { grid-template-columns: 1fr; }
        .sol-chapter-intro { position: static; }
        .sol-section-head { display: grid; }
        .sol-console-kpis { grid-template-columns: repeat(2,minmax(0,1fr)); }
    }
    @media (max-width: 680px) {
        .sol-shell { width: min(100% - 1.25rem,80rem); }
        .sol-hero { padding-top: 3.5rem; }
        .sol-hero h1 { font-size: clamp(2.65rem,13vw,3.7rem); }
        .sol-hero-copy { font-size: .95rem; line-height: 1.65; }
        .sol-hero-actions, .sol-final-actions { display: grid; grid-template-columns: 1fr; }
        .sol-hero-actions > *, .sol-final-actions > * { width: 100%; }
        .sol-hero-facts { display: grid; gap: .65rem; }
        .sol-radar { padding: .75rem; border-radius: 1rem; }
        .sol-radar-output, .sol-credit-steps, .sol-transition { grid-template-columns: 1fr; }
        .sol-anchor-list { margin-inline: -.625rem; padding-inline: .625rem; }
        .sol-section { padding: 4.5rem 0; }
        .sol-heading { font-size: 2.35rem; }
        .sol-journey, .sol-feature-grid, .sol-atlas { grid-template-columns: 1fr; }
        .sol-journey-step { min-height: 10.5rem; }
        .sol-feature-card--wide { grid-column: auto; }
        .sol-bridge-flow { grid-template-columns: 1fr; }
        .sol-feature-card { min-height: auto; }
        .sol-feature-meta { margin-top: 1rem; }
        .sol-console-kpis, .sol-console-chart { grid-template-columns: 1fr; }
        .sol-chart-bars { height: 6rem; }
        .sol-source-cloud { display: grid; grid-template-columns: 1fr; }
        .sol-source-chip { border-radius: .65rem; }
        .sol-cnpj-analysis-head { display: grid; }
        .sol-cnpj-analysis-head p { text-align: left; }
        .sol-cnpj-layers, .sol-cnpj-actions { grid-template-columns: 1fr; }
        .sol-cnpj-layer { min-height: 8.4rem; }
        .sol-cnpj-sources { grid-template-columns: 1fr; gap: .25rem; }
        .sol-cnpj-sources .sol-source-cloud { margin-top: .35rem; }
        .sol-credit-sheet { padding: .75rem; }
        .sol-transition-year { display: grid; grid-template-columns: 4rem 1fr; gap: .6rem; align-items: center; }
        .sol-transition-year span { margin-top: 0; }
    }
    @media (prefers-reduced-motion: reduce) {
        .solutions-page *, .solutions-page *::before, .solutions-page *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
    }
</style>

<div class="solutions-page">
    <section class="sol-hero" aria-labelledby="solutions-title">
        <div class="sol-shell sol-hero-grid">
            <div>
                <span class="sol-kicker sol-kicker--light">Plataforma fiscal de ponta a ponta</span>
                <h1 id="solutions-title" class="sol-serif">Do arquivo bruto à <em>decisão fiscal.</em></h1>
                <p class="sol-hero-copy">
                    A FiscalDock organiza documentos, monitora CNPJs, cruza apurações, valida notas e
                    transforma a Reforma Tributária em números que o contador consegue explicar e agir.
                </p>
                <div class="sol-hero-actions">
                    <a href="{{ route('signup') }}" class="btn-cta">Começar com @brl($trialBalance) grátis</a>
                    <a href="#mapa" class="sol-btn-secondary">Explorar a plataforma <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></a>
                </div>
                <div class="sol-hero-facts">
                    <span class="sol-hero-fact">EFD Fiscal + Contribuições</span>
                    <span class="sol-hero-fact">{{ $activeCnpjSources }} fontes cadastrais e fiscais</span>
                    <span class="sol-hero-fact">PDF, XLSX e CSV auditáveis</span>
                </div>
            </div>

            <aside class="sol-radar" aria-label="Fluxo da plataforma">
                <div class="sol-radar-top"><span class="sol-radar-label">Radar operacional</span><span class="sol-radar-live">Dados conectados</span></div>
                <div class="sol-radar-stage">
                    <div class="sol-radar-stage-head"><span>01 · Entrada</span><strong>EFD + XML</strong></div>
                    <div class="sol-radar-track"><i style="width: 100%"></i></div>
                </div>
                <div class="sol-radar-stage">
                    <div class="sol-radar-stage-head"><span>02 · Contexto</span><strong>Notas · CNPJ · Catálogo</strong></div>
                    <div class="sol-radar-track"><i style="width: 86%"></i></div>
                </div>
                <div class="sol-radar-stage">
                    <div class="sol-radar-stage-head"><span>03 · Inteligência</span><strong>BI · Score · Clearance</strong></div>
                    <div class="sol-radar-track"><i style="width: 72%"></i></div>
                </div>
                <div class="sol-radar-output">
                    <div class="sol-radar-kpi"><small>Alertas</small><strong>priorizados</strong></div>
                    <div class="sol-radar-kpi"><small>Crédito</small><strong>IBS/CBS</strong></div>
                    <div class="sol-radar-kpi"><small>Saída</small><strong>dossiê</strong></div>
                </div>
            </aside>
        </div>
    </section>

    <nav class="sol-anchor-nav" aria-label="Navegação das soluções">
        <div class="sol-shell sol-anchor-list">
            <a href="#mapa" class="sol-anchor-link is-active">Visão geral</a>
            <a href="#documentos" class="sol-anchor-link">Documentos</a>
            <a href="#inteligencia" class="sol-anchor-link">BI Fiscal</a>
            <a href="#risco" class="sol-anchor-link">CNPJ e risco</a>
            <a href="#reforma" class="sol-anchor-link">Reforma Tributária</a>
            <a href="#clearance" class="sol-anchor-link">Clearance</a>
            <a href="#acao" class="sol-anchor-link">Alertas e gestão</a>
        </div>
    </nav>

    <section id="mapa" class="sol-section sol-section--soft">
        <div class="sol-shell">
            <header class="sol-section-head" data-sol-reveal>
                <div><span class="sol-kicker">O mapa da operação</span><h2 class="sol-heading">Uma jornada contínua, sem ilhas de informação.</h2></div>
                <p>Cada etapa aproveita o contexto da anterior. O participante extraído do SPED vira CNPJ monitorado; a nota alimenta o BI; o resultado vira alerta e dossiê.</p>
            </header>
            <div class="sol-journey" data-sol-reveal>
                @foreach([
                    ['01 / Ingerir', 'SPED e XML', 'Receba documentos em massa, acompanhe o progresso e preserve o histórico da importação.', 'M4 4v16h16V8l-4-4H4zm8 4v8m-4-4h8'],
                    ['02 / Organizar', 'Acervo fiscal', 'Unifique notas, participantes, clientes, itens e catálogo com rastreabilidade de origem.', 'M4 6h16M4 12h16M4 18h10'],
                    ['03 / Interpretar', 'Resumo e BI', 'Leia competência, apuração, faturamento, compras, CFOP, CST, NCM e cruzamentos.', 'M4 19V9m5 10V5m5 14v-7m5 7V3'],
                    ['04 / Vigiar', 'Risco contínuo', 'Consulte, monitore, pontue e priorize contrapartes e documentos que pedem ação.', 'M12 5c4.5 0 8.3 2.9 9.5 7-1.2 4.1-5 7-9.5 7s-8.3-2.9-9.5-7C3.7 7.9 7.5 5 12 5zm0 4a3 3 0 100 6 3 3 0 000-6z'],
                    ['05 / Agir', 'Alerta e dossiê', 'Leve evidência para o cliente, trate pendências e exporte relatórios auditáveis.', 'M9 12l2 2 4-4m5 2a8 8 0 11-16 0 8 8 0 0116 0z'],
                ] as [$number, $title, $text, $icon])
                    <article class="sol-journey-step"><span class="sol-journey-num">{{ $number }}</span><h3>{{ $title }}</h3><p>{{ $text }}</p><span class="sol-journey-token"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon }}"/></svg></span></article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="documentos" class="sol-section">
        <div class="sol-shell sol-chapter">
            <div class="sol-chapter-intro" data-sol-reveal>
                <span class="sol-chapter-index">01</span>
                <h2>Comece pela escrituração que você já tem.</h2>
                <p>O arquivo deixa de ser uma obrigação arquivada e vira uma base navegável: notas, itens, participantes, apurações, retenções e histórico de cadastro.</p>
                <a href="{{ route('agendar') }}" class="sol-text-link">Ver uma importação real →</a>
            </div>
            <div class="sol-feature-grid" data-sol-reveal>
                <article class="sol-feature-card">
                    <div class="sol-feature-top"><span class="sol-feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0l-4-4m4 4l4-4M4 19h16"/></svg></span><span class="sol-feature-state">Operacional</span></div>
                    <h3>EFD ICMS/IPI e PIS/COFINS</h3>
                    <p>Extração de participantes, catálogo, notas de serviços e mercadorias, transportes, apuração de ICMS/IPI e PIS/COFINS e retenções na fonte.</p>
                    <span class="sol-feature-meta">Blocos A · C · D · E · F · M · progresso SSE</span>
                </article>
                <article class="sol-feature-card">
                    <div class="sol-feature-top"><span class="sol-feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 4h8l4 4v12H4V4h4zm0 0v5h8V4"/></svg></span><span class="sol-feature-state">Operacional</span></div>
                    <h3>XML de NF-e em massa</h3>
                    <p>Envio avulso ou ZIP, detecção de duplicidade, itens tipados, lotes multiempresa e vínculo assistido entre cliente e contraparte.</p>
                    <span class="sol-feature-meta">Laravel · NF-e modelo 55 · histórico unificado</span>
                </article>
                <article class="sol-feature-card">
                    <div class="sol-feature-top"><span class="sol-feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16v12H4zM8 3h8v4H8zM7 11h4m-4 4h8"/></svg></span><span class="sol-feature-state">Operacional</span></div>
                    <h3>Notas unificadas e detalhadas</h3>
                    <p>Uma listagem para documentos EFD e XML, com origem explícita, filtros, partes, itens, tributos e navegação até o detalhe.</p>
                    <span class="sol-feature-meta">Acervo · EFD vence no dedup analítico</span>
                </article>
                <article class="sol-feature-card">
                    <div class="sol-feature-top"><span class="sol-feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 4h14v16H5zM8 8h8m-8 4h8m-8 4h5"/></svg></span><span class="sol-feature-state">Operacional</span></div>
                    <h3>Catálogo × movimentação</h3>
                    <p>Cruze registro 0200, NCM, CFOP, CST, alíquotas e itens reais; acompanhe divergência, movimentação e drift do cadastro ao longo do tempo.</p>
                    <span class="sol-feature-meta">Histórico período-fiel · alertas de NCM</span>
                </article>
            </div>
        </div>
    </section>

    <section id="inteligencia" class="sol-section sol-section--soft">
        <div class="sol-shell">
            <header class="sol-section-head" data-sol-reveal>
                <div><span class="sol-kicker">02 — Inteligência fiscal</span><h2 class="sol-heading">Veja a operação por competência, documento e causa.</h2></div>
                <p>Dashboards servem para investigar. O Resumo Fiscal serve para fechar. Os cruzamentos servem para encontrar aquilo que uma visão isolada não revela.</p>
            </header>
            <div class="sol-console" data-sol-reveal>
                <div class="sol-console-bar"><div class="sol-console-dots"><i></i><i></i><i></i></div><span>cockpit / inteligência fiscal</span></div>
                <div class="sol-console-body">
                    <div class="sol-console-kpis">
                        <div class="sol-console-kpi"><small>Faturamento</small><strong>por período</strong></div>
                        <div class="sol-console-kpi"><small>Compras</small><strong>por fornecedor</strong></div>
                        <div class="sol-console-kpi"><small>Tributos</small><strong>apurados</strong></div>
                        <div class="sol-console-kpi"><small>Alertas</small><strong>por severidade</strong></div>
                    </div>
                    <div class="sol-console-chart">
                        <div class="sol-chart-box"><span class="sol-feature-meta">Evolução por competência</span><div class="sol-chart-bars"><i style="height:38%"></i><i style="height:52%"></i><i style="height:46%"></i><i style="height:68%"></i><i style="height:61%"></i><i style="height:82%"></i><i style="height:74%"></i><i style="height:91%"></i></div></div>
                        <div class="sol-list-box"><span class="sol-feature-meta">Leituras disponíveis</span><div class="sol-list-row"><span>CFOP e operação</span><strong>drill-down</strong></div><div class="sol-list-row"><span>Apuração × notas</span><strong>confronto</strong></div><div class="sol-list-row"><span>Participantes</span><strong>concentração</strong></div><div class="sol-list-row"><span>Catálogo</span><strong>NCM/CST</strong></div></div>
                    </div>
                </div>
            </div>
            <div class="sol-feature-grid" style="margin-top:.85rem" data-sol-reveal>
                <article class="sol-feature-card"><div class="sol-feature-top"><span class="sol-feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 4h14v16H5zM8 8h8m-8 4h5m-5 4h8"/></svg></span><span class="sol-feature-state">Executivo</span></div><h3>Resumo Fiscal por competência</h3><p>Consolide a recolher, apuração de ICMS e PIS/COFINS, retenções, cruzamentos e alertas com trilha até a origem.</p><span class="sol-feature-meta">PDF · XLSX · CSV</span></article>
                <article class="sol-feature-card"><div class="sol-feature-top"><span class="sol-feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V9m5 10V5m5 14v-7m5 7V3"/></svg></span><span class="sol-feature-state">Analítico</span></div><h3>BI e cruzamentos fiscais</h3><p>Faturamento, compras, tributos, EFD, participantes, risco, CFOP, catálogo, ICMS-ST × regime, estoque e notas canceladas.</p><span class="sol-feature-meta">Filtros · drill-down · exportação</span></article>
            </div>
        </div>
    </section>

    <section id="risco" class="sol-section">
        <div class="sol-shell">
            <header class="sol-section-head" data-sol-reveal>
                <div>
                    <span class="sol-kicker">03 — Consulta e monitoramento CNPJ</span>
                    <h2 class="sol-heading">Um CNPJ deixa de ser cadastro. Vira uma decisão acompanhável.</h2>
                </div>
                <p>A consulta é a fotografia; o monitoramento é o filme. Identidade, certidões, regime, Score e movimentação mostram o que mudou, quanto isso importa e qual ação vem depois.</p>
            </header>

            <div class="sol-cnpj-workspace" data-sol-reveal>
                <article class="sol-cnpj-file">
                    <div class="sol-cnpj-file-head">
                        <div><span class="sol-cnpj-file-label">Ficha da contraparte</span><h3>Distribuidora Horizonte Ltda.</h3><span class="sol-cnpj-doc">12.345.678/0001-90</span></div>
                        <span class="sol-cnpj-badge">Ativa</span>
                    </div>
                    <div class="sol-cnpj-score">
                        <div class="sol-score-dial"><strong>18</strong></div>
                        <div class="sol-score-copy"><small>Score Fiscal</small><strong>Baixo risco</strong><span>Um vencimento próximo pede acompanhamento.</span></div>
                    </div>
                    <div class="sol-cnpj-signals">
                        <div class="sol-cnpj-signal"><span>CND Federal</span><strong>Negativa</strong></div>
                        <div class="sol-cnpj-signal"><span>FGTS e CNDT</span><strong>Regulares</strong></div>
                        <div class="sol-cnpj-signal sol-cnpj-signal--warning"><span>CND Municipal</span><strong>Vence em 12 dias</strong></div>
                        <div class="sol-cnpj-signal"><span>Regime tributário</span><strong>Lucro Presumido · estimado</strong></div>
                    </div>
                    <p class="sol-cnpj-file-foot">Última consulta há 2 dias · metodologia e origem visíveis no detalhe</p>
                </article>

                <div class="sol-cnpj-analysis">
                    <div class="sol-cnpj-analysis-head"><h3>Quatro camadas para decidir</h3><p>Cada camada responde uma pergunta diferente antes de contratar, manter, monitorar ou revisar a relação.</p></div>
                    <div class="sol-cnpj-layers">
                        <article class="sol-cnpj-layer" data-layer="1"><small>Identidade</small><h4>Quem é e como opera?</h4><p>Situação cadastral, QSA, CNAEs, endereço, porte, capital e regime tributário.</p></article>
                        <article class="sol-cnpj-layer" data-layer="2"><small>Regularidade</small><h4>Está apto para a decisão?</h4><p>CND Federal, Estadual e Municipal, CNDT, FGTS e inscrição no SINTEGRA.</p></article>
                        <article class="sol-cnpj-layer" data-layer="3"><small>Relevância fiscal</small><h4>Quanto essa relação representa?</h4><p>Compras, vendas, concentração, documentos e período real extraídos da escrituração.</p></article>
                        <article class="sol-cnpj-layer" data-layer="4"><small>Evolução</small><h4>O que mudou desde a última leitura?</h4><p>Ciclos de monitoramento, histórico, alerta de vencimento e nova classificação de risco.</p></article>
                    </div>
                    <div class="sol-cnpj-actions">
                        <div class="sol-cnpj-action"><i>01</i><div><small>Agora</small><strong>Consultar individual ou em lote</strong></div></div>
                        <div class="sol-cnpj-action"><i>02</i><div><small>Contínuo</small><strong>Adicionar a um grupo monitorado</strong></div></div>
                        <div class="sol-cnpj-action"><i>03</i><div><small>Evidência</small><strong>Gerar alerta, histórico e dossiê</strong></div></div>
                    </div>
                    <div class="sol-cnpj-sources">
                        <span>{{ $activeCnpjSources }} fontes conectadas</span>
                        <div class="sol-source-cloud">
                            @foreach(['Cadastro RFB','CND Federal','CND Estadual','CND Municipal','CNDT','FGTS','SINTEGRA'] as $source)
                                <span class="sol-source-chip">{{ $source }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <article class="sol-feature-card sol-feature-card--wide sol-credit-bridge" style="margin-top:.8rem" data-sol-reveal>
                    <div class="sol-feature-top"><span class="sol-feature-icon" style="background-color:rgba(255,255,255,.09);color:#fde68a"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M7 4v6m10-6v6M6 14h4m4 0h4M6 18h12"/></svg></span><span class="sol-feature-state">Consulta + crédito</span></div>
                    <h3>A consulta CNPJ alimenta o eixo de crédito IBS/CBS.</h3>
                    <p>O resultado não fica isolado em uma certidão. Regime, regularidade e movimentação se combinam para explicar quanto crédito o relacionamento pode gerar — e onde a exposição merece atenção.</p>
                    <div class="sol-bridge-flow">
                        <div class="sol-bridge-step"><small>01 · Consulta</small><strong>Identifica MEI, Simples ou Regime Normal</strong></div>
                        <div class="sol-bridge-step"><small>02 · Qualidade</small><strong>Preserva origem real ou estimada do regime</strong></div>
                        <div class="sol-bridge-step"><small>03 · Movimento</small><strong>Usa compras e vendas escrituradas no EFD</strong></div>
                        <div class="sol-bridge-step"><small>04 · Decisão</small><strong>Calcula potencial, aproveitável e em risco</strong></div>
                    </div>
                    <p class="sol-bridge-note">Se a Receita não publicar o regime, a plataforma pode estimá-lo com origem e ressalva explícitas — nunca sobrescreve informação oficial. Certidões e regularidade qualificam o risco, mas não reduzem o valor estimado em R$ sem evidência oficial de recolhimento.</p>
            </article>
            <a href="{{ route('precos') }}#precos-consumo" class="sol-text-link" data-sol-reveal>Comparar os quatro níveis de consulta →</a>
        </div>
    </section>

    <section id="reforma" class="sol-section sol-reform sol-dark-grid">
        <div class="sol-shell sol-reform-grid">
            <div data-sol-reveal>
                <span class="sol-kicker sol-kicker--light">04 — Reforma Tributária</span>
                <h2 class="sol-heading">Preparado para migrar sem perder o fio do crédito.</h2>
                <p class="sol-lead">A base histórica já está organizada para comparar o regime atual com a transição IBS/CBS. A FiscalDock transforma volume de entradas e regime do fornecedor em uma estimativa financeira de crédito tributário.</p>
                <div class="sol-reform-bullets">
                    <div class="sol-reform-bullet">Classifica fornecedor que gera crédito integral, parcial ou nenhum crédito IBS/CBS.</div>
                    <div class="sol-reform-bullet">Calcula crédito potencial, crédito aproveitável e valor estimado em risco por fornecedor.</div>
                    <div class="sol-reform-bullet">Mantém alíquotas parametrizadas por ano da transição, de 2026 ao regime pleno.</div>
                    <div class="sol-reform-bullet">Cruza a estimativa com regularidade fiscal e volume EFD, sem misturar risco de crédito com Score de conformidade.</div>
                </div>
            </div>
            <div class="sol-credit-sheet" data-sol-reveal>
                <div class="sol-credit-sheet-head"><span>Dossiê de crédito IBS/CBS</span><strong>Planejamento de exposição</strong></div>
                <div class="sol-credit-formula"><small>A conta auditável</small><strong>Volume de entradas × alíquota × fator do regime</strong></div>
                <div class="sol-credit-steps">
                    <div class="sol-credit-step"><small>01 · Potencial</small><strong>Quanto existiria no regime regular</strong></div>
                    <div class="sol-credit-step"><small>02 · Aproveitável</small><strong>Quanto o regime transfere ao comprador</strong></div>
                    <div class="sol-credit-step"><small>03 · Em risco</small><strong>A diferença que pede decisão</strong></div>
                </div>
                <div class="sol-transition">
                    <div class="sol-transition-year"><strong>2026</strong><span>fase de teste · {{ number_format(($reformRates[2026] ?? .01) * 100, 1, ',', '.') }}%</span></div>
                    <div class="sol-transition-year"><strong>2027–28</strong><span>CBS ganha peso</span></div>
                    <div class="sol-transition-year"><strong>2029–32</strong><span>IBS sobe por fase</span></div>
                    <div class="sol-transition-year"><strong>2033+</strong><span>estado pleno · ref. {{ number_format($fullReformRate * 100, 1, ',', '.') }}%</span></div>
                </div>
                <p class="sol-reform-note">É uma estimativa parametrizável para planejamento, não apuração oficial nem garantia de aproveitamento. Alíquota real, opção híbrida do Simples e recolhimento efetivo dependem da regulamentação e de fontes oficiais futuras.</p>
            </div>
        </div>
    </section>

    <section id="clearance" class="sol-section sol-section--paper">
        <div class="sol-shell sol-clearance-grid">
            <div data-sol-reveal>
                <span class="sol-kicker">05 — Clearance de documentos</span>
                <h2 class="sol-heading">O declarado de um lado. A situação oficial do outro.</h2>
                <p class="sol-lead">Valide NF-e e CT-e já importados ou consulte uma chave diretamente. O motor preserva o acervo declarado e o snapshot oficial para mostrar divergência sem misturar as fontes.</p>
                <div class="sol-reform-bullets" style="color:var(--sol-ink)">
                    <div class="sol-reform-bullet" style="color:#536071">Clearance básico individual ou em lote sobre documentos do acervo.</div>
                    <div class="sol-reform-bullet" style="color:#536071">Busca avulsa por chave com confirmação antes de reconsulta e nova cobrança.</div>
                    <div class="sol-reform-bullet" style="color:#536071">Classificação operacional, exposição e relatório executivo em PDF.</div>
                </div>
            </div>
            <div class="sol-compare" data-sol-reveal>
                <div class="sol-compare-head"><span>Escrituração</span><i></i><span>Snapshot oficial</span></div>
                @foreach([
                    ['Chave 3524…0917', 'Autorizada', true],
                    ['Total R$ 12.480,00', 'R$ 12.480,00', true],
                    ['Situação declarada: regular', 'Evento: cancelamento', false],
                    ['Emitente identificado', 'CNPJ mascarado resolvido', true],
                ] as [$left, $right, $match])
                    <div class="sol-compare-row"><span class="sol-compare-value">{{ $left }}</span><span class="sol-match {{ $match ? '' : 'sol-match--alert' }}">{{ $match ? '✓' : '!' }}</span><span class="sol-compare-value">{{ $right }}</span></div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="acao" class="sol-section">
        <div class="sol-shell">
            <header class="sol-section-head" data-sol-reveal>
                <div><span class="sol-kicker">06 — Da análise para a rotina</span><h2 class="sol-heading">Tudo o que fecha o ciclo operacional.</h2></div>
                <p>Não basta encontrar. A plataforma organiza o tratamento, preserva o histórico e entrega a evidência no formato que cliente, equipe e auditoria conseguem consumir.</p>
            </header>
            <div class="sol-atlas" data-sol-reveal>
                @foreach([
                    ['Alertas', 'Central priorizada', 'Notas duplicadas, NCM ausente, certidão positiva ou vencendo, fornecedor irregular e gaps de importação.'],
                    ['Workflow', 'Status e histórico', 'Marque, trate, resolva e recalcule alertas com metodologia e fonte de dados visíveis.'],
                    ['Dossiês', 'Um CNPJ ou um lote', 'Gere dossiês de cliente e participante com regularidade, movimentação e crédito IBS/CBS.'],
                    ['Relatórios', 'PDF, XLSX e CSV', 'Exporte dashboards, listas, resumos, cruzamentos, catálogo, notas e Score Fiscal.'],
                    ['Cadastros', 'Clientes e participantes', 'Empresa própria automática, carteira administrada, vínculos, grupos e ações em massa.'],
                    ['Cockpit', 'Dashboard configurável', 'Saldo, consultas, documentos, monitoramento, alertas e atalhos em uma visão operacional.'],
                    ['Governança', 'LGPD e rastreabilidade', 'Consentimentos, centro de privacidade, exportação de dados, solicitação de exclusão e trilhas.'],
                    ['Comercial', 'Plano + saldo em reais', 'Assinaturas com limites e recursos, saldo incluso ou avulso, recarga e controle de consumo.'],
                ] as [$tag, $title, $text])
                    <article class="sol-atlas-card"><span>{{ $tag }}</span><h3>{{ $title }}</h3><p>{{ $text }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="sol-final sol-dark-grid" aria-labelledby="solutions-final-title">
        <div class="sol-shell" style="position:relative">
            <span class="sol-kicker sol-kicker--light">A plataforma cresce com a sua operação</span>
            <h2 id="solutions-final-title">Seu próximo diagnóstico pode começar com um arquivo que você já tem.</h2>
            <p>Crie a conta com @brl($trialBalance) de saldo grátis por {{ $trialDays }} dias, importe uma escrituração real e veja como documentos, CNPJs, crédito tributário e risco passam a conversar.</p>
            <div class="sol-final-actions"><a href="{{ route('signup') }}" class="btn-cta">Criar conta grátis</a><a href="{{ route('agendar') }}" class="sol-btn-secondary">Agendar demonstração</a></div>
        </div>
    </section>
</div>
