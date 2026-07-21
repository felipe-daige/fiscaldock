@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'FiscalDock',
    'url' => 'https://fiscaldock.com',
    'logo' => asset('binary_files/logo/Logo FiscalDock.png'),
    'description' => 'Radar de riscos fiscais para contadores, escritórios contábeis e empresas. Monitora CNPJs, consolida consultas de compliance e ajuda a detectar inconsistências no SPED antes da malha fiscal.',
    'sameAs' => ['https://instagram.com/fiscaldock'],
    'areaServed' => ['@type' => 'Country', 'name' => 'Brasil'],
    'knowsAbout' => [
        'EFD ICMS/IPI', 'EFD Contribuições', 'SPED Fiscal', 'PIS/COFINS',
        'ICMS', 'IPI', 'NF-e', 'CT-e', 'NFS-e',
        'Compliance fiscal', 'Auditoria fiscal', 'Clearance de notas fiscais',
        'Monitoramento de CNPJ', 'Regime tributário', 'Simples Nacional',
    ],
    'contactPoint' => [
        '@type' => 'ContactPoint',
        'telephone' => '+55-67-99984-4366',
        'email' => 'contato@fiscaldock.com.br',
        'contactType' => 'customer support',
        'areaServed' => 'BR',
        'availableLanguage' => 'Portuguese',
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'FiscalDock',
    'url' => 'https://fiscaldock.com',
    'inLanguage' => 'pt-BR',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => 'Radar de riscos fiscais',
    'provider' => ['@type' => 'Organization', 'name' => 'FiscalDock', 'url' => 'https://fiscaldock.com'],
    'areaServed' => ['@type' => 'Country', 'name' => 'Brasil'],
    'audience' => ['@type' => 'Audience', 'audienceType' => 'Contadores, escritórios contábeis e empresas'],
    'hasOfferCatalog' => [
        '@type' => 'OfferCatalog',
        'name' => 'Soluções FiscalDock',
        'itemListElement' => [
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Monitoramento de CNPJs', 'description' => 'Acompanhamento de situação cadastral, regime tributário e sinais de risco de participantes.']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Consultas de compliance', 'description' => 'Consultas de CNPJ, CND, CNDT, FGTS e fontes fiscais em fluxo consolidado.']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Alertas e inconsistências no SPED', 'description' => 'Cruzamentos entre EFD, XML, apurações, participantes e classificações fiscais antes da malha fiscal.']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Importação EFD ICMS/IPI', 'description' => 'Leitura e extração de blocos C, D, E e H do SPED Fiscal com apuração de ICMS e inventário.']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Importação EFD Contribuições', 'description' => 'Extração dos blocos A, M e F do SPED Contribuições com apuração de PIS/COFINS e retenções na fonte.']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Clearance de NF-e', 'description' => 'Consulta e validação de documentos fiscais contra fontes oficiais, com produto ainda em evolução.']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'BI Fiscal', 'description' => 'Dashboards de cruzamento entre apuração, notas fiscais, participantes e CFOP.']],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

<style>
/* ===================================================================
   Identidade editorial da landing — "dossiê fiscal"
   Headlines serifadas (Fraunces, a mesma do hero), kickers numerados
   em monospace (remetem a registro de SPED) e chips de registro.
   =================================================================== */

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Headlines de todas as seções na mesma família editorial do hero */
#como-funciona h2,
#radar-vivo h2,
#funcionalidades h2,
#reforma h2,
#para-quem-e h2,
#diferenciais h2,
#na-pratica h2,
#duvidas h2,
#contato h2 {
    font-family: 'Fraunces', Georgia, 'Times New Roman', serif;
    font-weight: 600;
    letter-spacing: -0.015em;
    font-optical-sizing: auto;
}

/* Kicker numerado em mono, com réguas laterais — índice do dossiê */
.landing-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: #9ca3af;
    margin-bottom: 0.85rem;
}
.landing-kicker::before,
.landing-kicker::after {
    content: "";
    width: 1.9rem;
    height: 1px;
    background: #d1d5db;
}
.landing-kicker--dark {
    color: rgba(255, 255, 255, 0.55);
}
.landing-kicker--dark::before,
.landing-kicker--dark::after {
    background: rgba(255, 255, 255, 0.22);
}

/* Faixa de fatos do hero (substitui a social proof genérica) */
.hero-facts {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}
@media (min-width: 640px) {
    .hero-facts {
        grid-template-columns: repeat(3, auto);
        justify-content: start;
        gap: 2.25rem;
    }
    .hero-fact + .hero-fact {
        border-left: 1px solid rgba(255, 255, 255, 0.14);
        padding-left: 2.25rem;
    }
}
.hero-fact-num {
    display: block;
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.45rem;
    font-weight: 600;
    line-height: 1.1;
    color: #fde68a;
    white-space: nowrap;
}
.hero-gratis-tag {
    display: inline-block;
    vertical-align: 0.18em;
    margin-left: 0.15rem;
    padding: 0.14rem 0.45rem;
    border-radius: 0.375rem;
    font-family: 'Instrument Sans', system-ui, sans-serif;
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #ffffff;
    background-color: #059669;
}
.hero-fact-label {
    display: block;
    margin-top: 0.3rem;
    font-size: 0.75rem;
    line-height: 1.35;
    color: rgba(255, 255, 255, 0.75);
    max-width: 12rem;
}

/* Chips de prova no mobile — versão estática dos floaties do mockup */
.hero-proof-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
}
/* >=1024px os floaties do mockup assumem esse papel (este bloco vence o
   lg:hidden do build por ordem de cascata, então esconde via media query) */
@media (min-width: 1024px) {
    .hero-proof-chips { display: none; }
}
.hero-proof-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.4rem 0.75rem;
    border-radius: 9999px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(11, 22, 44, 0.55);
    font-size: 0.75rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.88);
    white-space: nowrap;
}
.hpc-dot {
    width: 7px;
    height: 7px;
    border-radius: 9999px;
    flex-shrink: 0;
}

/* CTA secundário do hero */
.btn-ghost-hero {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 48px;
    padding: 0.875rem 1.35rem;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.28);
    color: rgba(255, 255, 255, 0.92);
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    transition: background-color 0.16s ease, border-color 0.16s ease;
}
.btn-ghost-hero:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.5);
}

/* Cards "Na prática" — cenários do dia a dia do contador */
.scenario-card {
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    padding: 1.75rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.scenario-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 22px 45px -28px rgba(15, 23, 42, 0.28);
}
.scenario-tag {
    align-self: flex-start;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #1e4fa0;
    background: #eef2f7;
    border: 1px solid #dce3ed;
    border-radius: 6px;
    padding: 4px 9px;
    margin-bottom: 1.1rem;
}
.scenario-card h3 {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    letter-spacing: -0.01em;
}
.scenario-hora {
    font-size: 0.72rem;
    color: #9ca3af;
    margin-top: 0.2rem;
}
.scenario-card p.scenario-texto {
    font-size: 0.875rem;
    line-height: 1.7;
    color: #4b5563;
    margin-top: 0.85rem;
}
.scenario-outcome {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-top: auto;
    padding-top: 1.1rem;
    border-top: 1px dashed #e5e7eb;
    font-size: 0.8rem;
    font-weight: 600;
    color: #047857;
}
.scenario-outcome svg {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
    margin-top: 1px;
}

/* ── FAQ (dúvidas) — acordeão nativo details/summary ── */
.faq-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 2.5rem;
    align-items: start;
}
@media (min-width: 1024px) {
    .faq-grid {
        grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
        gap: 4rem;
    }
}
.faq-intro {
    position: sticky;
    top: calc(var(--landing-header-height, 88px) + 1.5rem);
}
@media (max-width: 1023.98px) {
    .faq-intro { position: static; }
}
.faq-help {
    margin-top: 1.75rem;
    padding: 1.25rem 1.35rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.9rem;
    background: #f9fafb;
}
.faq-help-title {
    font-size: 0.8rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.6rem;
}
.faq-help-link {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: #1e4fa0;
    text-decoration: none;
    padding: 0.3rem 0;
}
.faq-help-link:hover { text-decoration: underline; }
.faq-help-link svg { transition: transform 0.15s ease; }
.faq-help-link:hover svg { transform: translateX(3px); }

.faq-item {
    border: 1px solid #e5e7eb;
    border-radius: 0.9rem;
    background: #ffffff;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.faq-item + .faq-item { margin-top: 0.75rem; }
.faq-item:hover { border-color: #cbd5e1; }
.faq-item[open] {
    border-color: #bfd0e8;
    box-shadow: 0 10px 30px -18px rgba(15, 23, 42, 0.18);
}
.faq-item summary {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 1.05rem 1.25rem;
    cursor: pointer;
    list-style: none;
    -webkit-tap-highlight-color: transparent;
}
.faq-item summary::-webkit-details-marker { display: none; }
.faq-item summary:focus-visible {
    outline: 2px solid #1e4fa0;
    outline-offset: 2px;
    border-radius: 0.9rem;
}
.faq-num {
    flex-shrink: 0;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: #94a3b8;
    padding-top: 0.1rem;
}
.faq-item[open] .faq-num { color: #1e4fa0; }
.faq-q {
    flex: 1;
    font-size: 0.9rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.35;
}
.faq-icon {
    position: relative;
    flex-shrink: 0;
    width: 1.6rem;
    height: 1.6rem;
    border-radius: 9999px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    transition: transform 0.25s ease, background-color 0.2s ease, border-color 0.2s ease;
}
.faq-icon::before,
.faq-icon::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    background: #64748b;
    border-radius: 2px;
    transition: background-color 0.2s ease;
}
.faq-icon::before {
    width: 0.65rem;
    height: 1.5px;
    transform: translate(-50%, -50%);
}
.faq-icon::after {
    width: 1.5px;
    height: 0.65rem;
    transform: translate(-50%, -50%);
}
.faq-item[open] .faq-icon {
    transform: rotate(45deg);
    background: #1e4fa0;
    border-color: #1e4fa0;
}
.faq-item[open] .faq-icon::before,
.faq-item[open] .faq-icon::after { background: #ffffff; }
.faq-a {
    padding: 0 1.25rem 1.15rem;
    margin-left: calc(0.9rem + 1.6em);
    font-size: 0.875rem;
    line-height: 1.7;
    color: #4b5563;
    animation: faqAnswerIn 0.25s ease both;
}
@keyframes faqAnswerIn {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: none; }
}
@media (max-width: 639.98px) {
    .faq-item summary { padding: 0.95rem 1rem; gap: 0.7rem; }
    .faq-a { padding: 0 1rem 1rem; margin-left: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .faq-a { animation: none; }
}

/* ── Faixas escuras interstitiais (métricas, segurança, contato) ──
   Mesma identidade do radar-vivo: gradiente profundo + malha blueprint */
.lp-blueprint {
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background-image:
        linear-gradient(to right, rgba(148, 197, 255, 0.06) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(148, 197, 255, 0.06) 1px, transparent 1px);
    background-size: 46px 46px;
    -webkit-mask-image: radial-gradient(120% 130% at 50% 0%, #000 25%, transparent 80%);
    mask-image: radial-gradient(120% 130% at 50% 0%, #000 25%, transparent 80%);
}

/* Métricas — números no mesmo desenho dos fatos do hero */
.metricas-band {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 2rem 1.5rem;
}
@media (min-width: 1024px) {
    .metricas-band {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .metrica + .metrica {
        border-left: 1px solid rgba(255, 255, 255, 0.12);
        padding-left: 2.25rem;
    }
}
.metrica-num {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 2.5rem;
    font-weight: 600;
    line-height: 1;
    color: #fde68a;
    font-optical-sizing: auto;
}
.metrica-label {
    margin-top: 0.55rem;
    font-size: 0.8rem;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.72);
    max-width: 13rem;
}

/* Segurança/LGPD — ícone em chip + texto, pareado com as métricas */
.seg-band {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.75rem 1.5rem;
}
@media (min-width: 1024px) {
    .seg-band {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .seg-item + .seg-item {
        border-left: 1px solid rgba(255, 255, 255, 0.12);
        padding-left: 2.25rem;
    }
}
.seg-item {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
}
.seg-icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 0.7rem;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.13);
}
.seg-icon svg {
    width: 1.15rem;
    height: 1.15rem;
    color: #fde68a;
}
.seg-title {
    font-size: 0.86rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.3;
}
.seg-sub {
    margin-top: 0.2rem;
    font-size: 0.75rem;
    line-height: 1.45;
    color: rgba(255, 255, 255, 0.62);
}

/* CTA final — chips de registro (monospace, remetem ao SPED) */
.contato-chips {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.contato-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.38rem 0.8rem;
    border-radius: 9999px;
    border: 1px solid rgba(255, 255, 255, 0.16);
    background: rgba(255, 255, 255, 0.06);
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.66rem;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.85);
    white-space: nowrap;
}
.contato-chip svg {
    width: 0.8rem;
    height: 0.8rem;
    color: #fde68a;
    flex-shrink: 0;
}

/* ===== Vida na página: reveal no scroll, spotlight e radar ao vivo ===== */

/* Reveal progressivo — só arma quando o JS adiciona a classe no body;
   sem JS (ou com prefers-reduced-motion) tudo fica visível. */
body.lp-reveal-armed .lp-reveal {
    opacity: 0;
    transform: translateY(22px);
    transition: opacity 0.65s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.65s cubic-bezier(0.22, 1, 0.36, 1);
    transition-delay: var(--lp-reveal-delay, 0s);
}
body.lp-reveal-armed .lp-reveal.lp-visible {
    opacity: 1;
    transform: none;
}
@media (prefers-reduced-motion: reduce) {
    body.lp-reveal-armed .lp-reveal {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
    }
}

/* Spotlight que segue o cursor nos cards (classe aplicada via JS) */
.lp-spot {
    position: relative;
}
.lp-spot::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    opacity: 0;
    background: radial-gradient(420px circle at var(--spot-x, 50%) var(--spot-y, 50%),
        rgba(30, 79, 160, 0.09), transparent 60%);
    transition: opacity 0.25s ease;
    pointer-events: none;
}
.lp-spot:hover::after {
    opacity: 1;
}

/* Terminal do radar — o SPED sendo auditado linha a linha */
.radar-terminal {
    border-radius: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(9, 18, 38, 0.82);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    box-shadow: 0 30px 70px -30px rgba(0, 0, 0, 0.6);
    overflow: hidden;
}
.radar-terminal-head {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.8rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.radar-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.radar-file {
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.55);
    margin-left: 0.35rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.radar-live {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #fbbf24;
    white-space: nowrap;
}
.radar-live-dot {
    width: 7px;
    height: 7px;
    border-radius: 9999px;
    background: #fbbf24;
    position: relative;
}
.radar-live-dot::after {
    content: "";
    position: absolute;
    inset: -4px;
    border-radius: 9999px;
    border: 1px solid rgba(251, 191, 36, 0.7);
    animation: heroPulse 2.2s ease-out infinite;
}
#radar-feed-list {
    list-style: none;
    margin: 0;
    padding: 0.35rem 0;
}
.radar-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 1rem;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.78rem;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
}
.radar-row:last-child {
    border-bottom: none;
}
.radar-row--enter {
    animation: radarRowIn 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes radarRowIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}
.radar-reg {
    min-width: 3.4rem;
    text-align: center;
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: #93c5fd;
    border: 1px solid rgba(148, 197, 255, 0.3);
    background: rgba(148, 197, 255, 0.08);
    border-radius: 5px;
    padding: 2px 7px;
    flex-shrink: 0;
}
.radar-desc {
    flex: 1;
    color: rgba(255, 255, 255, 0.66);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.radar-status {
    font-size: 0.66rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 9999px;
    white-space: nowrap;
    flex-shrink: 0;
}
.radar-status--ok {
    color: #6ee7b7;
    background: rgba(16, 185, 129, 0.14);
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.radar-status--alerta {
    color: #fca5a5;
    background: rgba(239, 68, 68, 0.14);
    border: 1px solid rgba(239, 68, 68, 0.32);
}
.radar-status--aviso {
    color: #fcd34d;
    background: rgba(245, 158, 11, 0.14);
    border: 1px solid rgba(245, 158, 11, 0.32);
}
.radar-terminal-foot {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 0.75rem 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.5);
}
.radar-terminal-foot b {
    font-weight: 700;
}
.radar-foot-ok b {
    color: #6ee7b7;
}
.radar-foot-alerta b {
    color: #fca5a5;
}

/* ===== Vinhetas de produto nos cards de funcionalidades (fx-*) =====
   Estado final é o default; quando o reveal está armado e o card ainda
   não entrou na tela, os elementos ficam "zerados" e animam ao revelar.
   Sem JS ou com reduced-motion, tudo aparece pronto. */

.fx-panel {
    border-radius: 0.85rem;
    border: 1px solid #e5e7eb;
    background: #f8fafc;
    padding: 1rem 1.1rem;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
}

.fx-file {
    font-size: 0.68rem;
    color: #9ca3af;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fx-badge-alertas {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    color: #b45309;
    background: #fef8ee;
    border: 1px solid #f5e6c8;
    border-radius: 9999px;
    padding: 2px 8px;
    white-space: nowrap;
}

/* Painel de importação (card Auditoria): barras que preenchem */
.fx-import-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.6rem;
    padding-bottom: 0.65rem;
    margin-bottom: 0.65rem;
    border-bottom: 1px dashed #e5e7eb;
}
.fx-import-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.34rem 0;
    font-size: 0.68rem;
    color: #6b7280;
}
.fx-import-label {
    width: 6.2rem;
    flex-shrink: 0;
    white-space: nowrap;
}
.fx-import-val {
    width: 4.6rem;
    flex-shrink: 0;
    text-align: right;
    font-weight: 700;
    color: #047857;
    white-space: nowrap;
}
.fx-bar {
    flex: 1;
    height: 6px;
    border-radius: 9999px;
    background: #e5e7eb;
    overflow: hidden;
}
.fx-bar i {
    display: block;
    height: 100%;
    width: var(--w, 100%);
    border-radius: inherit;
    background: linear-gradient(90deg, #1e4fa0, #3b82f6);
    transition: width 1s cubic-bezier(0.22, 1, 0.36, 1) var(--d, 0.2s);
}
body.lp-reveal-armed .lp-reveal:not(.lp-visible) .fx-bar i {
    width: 0;
}

/* Linhas de resultado (cards Clearance e Regularidade) */
.fx-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.6rem;
    padding: 0.5rem 0;
    border-bottom: 1px dashed #e5e7eb;
    font-size: 0.7rem;
    color: #6b7280;
    transition: opacity 0.5s ease var(--d, 0s), transform 0.5s ease var(--d, 0s);
}
.fx-row:last-child {
    border-bottom: none;
}
body.lp-reveal-armed .lp-reveal:not(.lp-visible) .fx-row {
    opacity: 0;
    transform: translateY(6px);
}
.fx-row-doc {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fx-status {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 9999px;
    white-space: nowrap;
    flex-shrink: 0;
}
.fx-status--ok {
    color: #047857;
    background: #eefbf5;
    border: 1px solid #c6eed8;
}
.fx-status--alerta {
    color: #b91c1c;
    background: #fef2f2;
    border: 1px solid #fecaca;
}
.fx-status--aviso {
    color: #b45309;
    background: #fef8ee;
    border: 1px solid #f5e6c8;
}

/* Vinheta de monitoramento: ATIVA → INAPTA */
.fx-monitor-cnpj {
    font-size: 0.72rem;
    font-weight: 700;
    color: #374151;
    margin-bottom: 0.6rem;
}
.fx-monitor-flow {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.fx-monitor-arrow {
    color: #9ca3af;
    font-size: 0.8rem;
}
.fx-pop {
    transition: opacity 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) 0.55s,
                transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) 0.55s;
}
body.lp-reveal-armed .lp-reveal:not(.lp-visible) .fx-pop {
    opacity: 0;
    transform: scale(0.6);
}
.fx-monitor-meta {
    margin-top: 0.65rem;
    padding-top: 0.6rem;
    border-top: 1px dashed #e5e7eb;
    font-size: 0.64rem;
    color: #9ca3af;
}

/* Mini gráfico do BI: barras que crescem */
.fx-chart-bars {
    display: flex;
    align-items: flex-end;
    gap: 0.45rem;
    height: 64px;
}
.fx-chart-bars i {
    flex: 1;
    border-radius: 4px 4px 0 0;
    background: #1e4fa0;
    opacity: 0.78;
    height: var(--h, 60%);
    transition: height 0.9s cubic-bezier(0.22, 1, 0.36, 1) var(--d, 0.2s);
}
.fx-chart-bars i.fx-chart-bar--destaque {
    background: #047857;
    opacity: 0.9;
}
body.lp-reveal-armed .lp-reveal:not(.lp-visible) .fx-chart-bars i {
    height: 4px;
}
.fx-chart-legend {
    margin-top: 0.6rem;
    padding-top: 0.55rem;
    border-top: 1px dashed #e5e7eb;
    font-size: 0.64rem;
    color: #9ca3af;
}

/* Fluxo do Raio-X (card escuro): chips entram em sequência */
.fx-flow > * {
    transition: opacity 0.5s ease var(--d, 0s), transform 0.5s ease var(--d, 0s);
}
.fx-flow > *:nth-child(1) { --d: 0.05s; }
.fx-flow > *:nth-child(2) { --d: 0.12s; }
.fx-flow > *:nth-child(3) { --d: 0.19s; }
.fx-flow > *:nth-child(4) { --d: 0.26s; }
.fx-flow > *:nth-child(5) { --d: 0.33s; }
.fx-flow > *:nth-child(6) { --d: 0.40s; }
.fx-flow > *:nth-child(7) { --d: 0.47s; }
.fx-flow > *:nth-child(8) { --d: 0.54s; }
.fx-flow > *:nth-child(9) { --d: 0.61s; }
body.lp-reveal-armed .lp-reveal:not(.lp-visible) .fx-flow > * {
    opacity: 0;
    transform: translateY(8px);
}

/* ===== Raio-X do Fornecedor (card escuro) ===== */

/* Equações de cruzamento: [dado A] + [dado B] → resultado */
.fxd-eq {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.45rem;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    transition: opacity 0.5s ease var(--d, 0s), transform 0.5s ease var(--d, 0s);
}
body.lp-reveal-armed .lp-reveal:not(.lp-visible) .fxd-eq {
    opacity: 0;
    transform: translateX(-10px);
}
.fxd-chip {
    font-size: 0.68rem;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 6px;
    white-space: nowrap;
    color: rgba(255, 255, 255, 0.78);
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.14);
}
.fxd-op {
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.38);
}
.fxd-res {
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 9999px;
    white-space: nowrap;
}
.fxd-res--alerta {
    color: #fca5a5;
    background: rgba(239, 68, 68, 0.14);
    border: 1px solid rgba(239, 68, 68, 0.34);
}
.fxd-res--aviso {
    color: #fcd34d;
    background: rgba(245, 158, 11, 0.14);
    border: 1px solid rgba(245, 158, 11, 0.34);
}

/* Mini-dossiê da coluna direita */
.fxd-dossie {
    width: 17rem;
    border-radius: 0.85rem;
    border: 1px solid rgba(255, 255, 255, 0.13);
    background: rgba(255, 255, 255, 0.05);
    padding: 1.1rem 1.2rem;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
}
.fxd-dossie-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.6rem;
    padding-bottom: 0.7rem;
    margin-bottom: 0.35rem;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.12);
}
.fxd-dossie-cnpj {
    font-size: 0.66rem;
    color: rgba(255, 255, 255, 0.5);
    white-space: nowrap;
}
.fxd-score {
    display: flex;
    align-items: baseline;
    gap: 0.4rem;
}
.fxd-score-num {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.9rem;
    font-weight: 600;
    line-height: 1;
    color: #fcd34d;
}
.fxd-score-label {
    font-size: 0.6rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.45);
}
.fxd-dossie .fx-row {
    border-bottom-color: rgba(255, 255, 255, 0.07);
    color: rgba(255, 255, 255, 0.62);
}
.fxd-meter {
    height: 5px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.1);
    overflow: hidden;
    margin-top: 0.8rem;
}
.fxd-meter i {
    display: block;
    height: 100%;
    width: var(--w, 58%);
    border-radius: inherit;
    background: linear-gradient(90deg, #ef4444, #f59e0b);
    transition: width 1s cubic-bezier(0.22, 1, 0.36, 1) 0.45s;
}
body.lp-reveal-armed .lp-reveal:not(.lp-visible) .fxd-meter i {
    width: 0;
}

/* ===== Diferenciais: diff da rotina do escritório =====
   Linha "−" (vermelha) é a rotina antiga, riscada; linha "+" (verde)
   é o que entra no lugar. Estado final é o default. */
.diff-panel {
    max-width: 46rem;
    margin: 0 auto;
    border-radius: 1rem;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    overflow: hidden;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.diff-head,
.diff-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.8rem 1.25rem;
    background: #f8fafc;
    font-size: 0.7rem;
    color: #6b7280;
}
.diff-head { border-bottom: 1px solid #e5e7eb; }
.diff-foot { border-top: 1px solid #e5e7eb; font-size: 0.66rem; color: #9ca3af; }
.diff-counts { display: flex; gap: 0.9rem; font-weight: 700; }
.diff-counts .del { color: #dc2626; }
.diff-counts .add { color: #047857; }
.diff-group {
    display: grid;
    grid-template-columns: 11.5rem 1fr;
    gap: 0.3rem 1rem;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px dashed #e5e7eb;
    transition: background-color 0.25s ease;
}
.diff-group:last-of-type { border-bottom: none; }
.diff-group:hover { background: #f8fafc; }
.diff-label {
    font-size: 0.64rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9ca3af;
    padding-top: 0.4rem;
}
.diff-lines {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    min-width: 0;
}
.diff-line {
    display: flex;
    align-items: baseline;
    gap: 0.6rem;
    font-size: 0.78rem;
    line-height: 1.45;
    padding: 0.3rem 0.65rem;
    border-radius: 0.45rem;
    transition: opacity 0.5s ease var(--d, 0s), transform 0.5s ease var(--d, 0s);
}
.diff-sign {
    width: 0.75rem;
    text-align: center;
    font-weight: 800;
    flex-shrink: 0;
}
.diff-line--del { background: rgba(239, 68, 68, 0.055); color: #6b7280; }
.diff-line--del .diff-sign { color: #dc2626; }
.diff-line--add {
    background: rgba(16, 185, 129, 0.07);
    color: #111827;
    font-weight: 600;
    transition-delay: calc(var(--d, 0s) + 0.18s);
}
.diff-line--add .diff-sign { color: #047857; }
.diff-txt { position: relative; min-width: 0; }
.diff-line--del .diff-txt::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 55%;
    height: 1px;
    background: rgba(220, 38, 38, 0.5);
    transform: scaleX(1);
    transform-origin: left center;
    transition: transform 0.55s ease calc(var(--d, 0s) + 0.5s);
}
body.lp-reveal-armed .lp-reveal:not(.lp-visible) .diff-line {
    opacity: 0;
    transform: translateX(-10px);
}
body.lp-reveal-armed .lp-reveal:not(.lp-visible) .diff-line--del .diff-txt::after {
    transform: scaleX(0);
}
@media (max-width: 640px) {
    .diff-group { grid-template-columns: 1fr; gap: 0.35rem; padding: 0.8rem 0.9rem; }
    .diff-label { padding-top: 0; }
    .diff-head, .diff-foot { padding-left: 0.9rem; padding-right: 0.9rem; }
}

/* ===== Antes × depois (tabela de ganho no topo) ===== */
.gain-panel {
    max-width: 56rem;
    margin: 0 auto;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    background: #ffffff;
    overflow: hidden;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.gain-head,
.gain-row {
    display: grid;
    grid-template-columns: 1.05fr 1fr 1.15fr;
    gap: 0 1.25rem;
    align-items: baseline;
    padding: 0.75rem 1.25rem;
}
.gain-head {
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #9ca3af;
}
.gain-head .gain-h-com { color: #047857; }
.gain-row {
    border-bottom: 1px dashed #e5e7eb;
    font-size: 0.75rem;
    line-height: 1.5;
    transition: background-color 0.25s ease, opacity 0.5s ease var(--d, 0s), transform 0.5s ease var(--d, 0s);
}
.gain-row:last-child { border-bottom: none; }
.gain-row:hover { background: #f8fafc; }
body.lp-reveal-armed .lp-reveal:not(.lp-visible) .gain-row {
    opacity: 0;
    transform: translateY(8px);
}
.gain-rotina { font-weight: 700; color: #111827; }
.gain-hoje { color: #6b7280; }
.gain-hoje::before { content: '− '; color: #dc2626; font-weight: 800; }
.gain-com { color: #065f46; font-weight: 600; }
.gain-com::before { content: '+ '; color: #047857; font-weight: 800; }
@media (max-width: 640px) {
    .gain-head { display: none; }
    .gain-row { grid-template-columns: 1fr; gap: 0.25rem; padding: 0.85rem 0.9rem; }
}

/* ===== Para quem é: fichas de perfil ===== */
.persona-grid {
    display: grid;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    background: #ffffff;
    overflow: hidden;
}
.persona-col {
    display: flex;
    flex-direction: column;
    padding: 1.5rem 1.6rem;
}
@media (min-width: 1024px) {
    .persona-grid { grid-template-columns: repeat(3, 1fr); }
    .persona-col + .persona-col { border-left: 1px dashed #e5e7eb; }
}
@media (max-width: 1023.98px) {
    .persona-col + .persona-col { border-top: 1px dashed #e5e7eb; }
}
.fx-glyph--sm {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 0.6rem;
    font-size: 0.62rem;
}
.persona-quote {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 0.88rem;
    line-height: 1.55;
    color: #374151;
    margin-bottom: 1rem;
}
.persona-ficha {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.68rem;
}
.persona-row {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}
.persona-key {
    flex-shrink: 0;
    color: #9ca3af;
}
.persona-dots {
    flex: 1;
    min-width: 1rem;
    border-bottom: 1px dotted #d1d5db;
    transform: translateY(-3px);
}
.persona-val {
    max-width: 62%;
    text-align: right;
    font-weight: 600;
    color: #374151;
}
.persona-start {
    margin-top: auto;
    padding-top: 0.85rem;
    border-top: 1px dashed #e5e7eb;
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.68rem;
    line-height: 1.5;
    color: #6b7280;
}
.persona-start b {
    font-weight: 700;
}

/* Glifos monospace no lugar de ícones genéricos */
.fx-glyph {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.03em;
    flex-shrink: 0;
}

/* ===== Como funciona: pipeline ===== */
.pipe-panel {
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    background: #ffffff;
    overflow: hidden;
}
.pipe-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.7rem 1.4rem;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.68rem;
    color: #6b7280;
}
.pipe-flow { color: #9ca3af; }
.pipe-grid { display: grid; }
.pipe-col {
    display: flex;
    flex-direction: column;
    padding: 1.5rem 1.6rem;
}
@media (min-width: 1024px) {
    .pipe-grid { grid-template-columns: repeat(4, 1fr); }
    .pipe-col + .pipe-col { border-left: 1px dashed #e5e7eb; }
}
@media (max-width: 1023.98px) {
    .pipe-col + .pipe-col { border-top: 1px dashed #e5e7eb; }
    .pipe-flow { display: none; }
}
.pipe-step {
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: #9ca3af;
    margin-bottom: 1rem;
}
.pipe-step b { color: #111827; font-weight: 700; }
.pipe-tags {
    margin-top: auto;
    padding-top: 0.85rem;
    border-top: 1px dashed #e5e7eb;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.66rem;
    color: #6b7280;
}

/* ===== Cronograma interativo da reforma tributária ===== */
.reforma-progress {
    max-width: 46rem;
    height: 4px;
    margin: 0 auto 1.4rem;
    background: #e5e7eb;
    border-radius: 9999px;
    overflow: hidden;
}
.reforma-progress i {
    display: block;
    height: 100%;
    width: var(--p, 8%);
    border-radius: inherit;
    background: linear-gradient(90deg, #1e4fa0, #facc15);
    transition: width 0.5s cubic-bezier(0.22, 1, 0.36, 1);
}
.reforma-anos {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.75rem;
}
.reforma-ano {
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    border: 1px solid #d1d5db;
    background: #ffffff;
    color: #6b7280;
    cursor: pointer;
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
}
.reforma-ano:hover {
    border-color: #9ca3af;
    color: #111827;
    transform: translateY(-1px);
}
.reforma-ano.ativo {
    background: #0b1f3a;
    border-color: #0b1f3a;
    color: #facc15;
}
.reforma-painel {
    max-width: 46rem;
    margin: 0 auto;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    padding: 1.75rem 2rem;
    box-shadow: 0 18px 40px -28px rgba(15, 23, 42, 0.2);
}
.reforma-painel.trocando {
    animation: reformaFade 0.35s ease;
}
@keyframes reformaFade {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}
.reforma-fase {
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #b45309;
}
.reforma-titulo {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.35rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    color: #111827;
    margin-top: 0.4rem;
}
.reforma-texto {
    font-size: 0.9rem;
    line-height: 1.7;
    color: #4b5563;
    margin-top: 0.6rem;
}
.reforma-dock {
    margin-top: 1.1rem;
    padding: 0.8rem 1rem;
    background: #f8fafc;
    border-left: 3px solid #facc15;
    border-radius: 0 0.5rem 0.5rem 0;
    font-size: 0.82rem;
    line-height: 1.6;
    color: #374151;
}
.reforma-dock strong {
    color: #0b1f3a;
}

/* Hero fix: força gradiente mesmo se classes tailwind falharem */
:root {
    --landing-header-height: 88px;
}

.hero-first-fold {
    min-height: calc(100svh - var(--landing-header-height) - 8.25rem);
    display: flex;
    flex-direction: column;
    background-color: #f3f4f6;
}

#hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e5a9a 50%, #0f172a 100%) !important;
    color: #fff;
    display: flex;
    align-items: center;
    flex: 1 0 auto;
}

.hero-shell {
    width: 100%;
    min-height: calc(100svh - var(--landing-header-height) - 14.75rem);
    display: flex;
    align-items: center;
}

.hero-grid {
    width: 100%;
    position: relative;
}

.hero-copy {
    position: relative;
    z-index: 2;
}

.hero-copy > * {
    max-width: 40rem;
}

.hero-visual {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100%;
}

.hero-visual-glow {
    position: absolute;
    inset: 10% 6% 12% 14%;
    border-radius: 9999px;
    background:
        radial-gradient(circle at center,
            rgba(255, 255, 255, 0.2) 0%,
            rgba(96, 165, 250, 0.12) 30%,
            rgba(15, 23, 42, 0) 72%);
    filter: blur(16px);
    opacity: 0.9;
    pointer-events: none;
}

/* Palco do mockup: define a largura do card e ancora os floaties nele */
.hero-stage {
    position: relative;
    z-index: 1;
    width: min(100%, 30rem);
}

/* ===== Mockup DOM: janela de browser com o painel de riscos =====
   Substitui o PNG de 800px (borrava em retina e mostrava um gráfico
   genérico). DOM escala nítido em qualquer largura/densidade. */
.hero-browser {
    width: 100%;
    border-radius: 0.9rem;
    overflow: hidden;
    background: #ffffff;
    box-shadow:
        0 40px 80px -24px rgba(2, 8, 23, 0.55),
        0 0 0 1px rgba(255, 255, 255, 0.08);
    text-align: left;
}
.hb-bar {
    display: flex;
    align-items: center;
    gap: 0.42rem;
    padding: 0.65rem 0.9rem;
    background: #eef2f7;
    border-bottom: 1px solid #e2e8f0;
}
.hb-dot {
    width: 0.6rem;
    height: 0.6rem;
    border-radius: 9999px;
    flex-shrink: 0;
}
.hb-url {
    margin-left: 0.55rem;
    flex: 1;
    max-width: 18rem;
    padding: 0.24rem 0.7rem;
    border-radius: 0.45rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
    font-size: 0.66rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.hb-body {
    padding: 1.1rem 1.15rem 0.95rem;
}
.hb-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.9rem;
}
.hb-title {
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: #0f172a;
    line-height: 1.2;
}
.hb-sub {
    margin-top: 0.2rem;
    font-size: 0.72rem;
    color: #64748b;
}
.hb-pill {
    flex-shrink: 0;
    padding: 0.28rem 0.65rem;
    border-radius: 9999px;
    font-size: 0.68rem;
    font-weight: 700;
    white-space: nowrap;
}
.hb-rows {
    border: 1px solid #e8edf3;
    border-radius: 0.65rem;
    overflow: hidden;
}
.hb-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.6rem 0.8rem;
}
.hb-row + .hb-row {
    border-top: 1px solid #eef2f7;
}
.hb-row-main {
    min-width: 0;
}
.hb-row-nome {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.hb-row-meta {
    display: block;
    font-size: 0.66rem;
    color: #94a3b8;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.hb-chip {
    flex-shrink: 0;
    padding: 0.22rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.66rem;
    font-weight: 700;
    white-space: nowrap;
}
.hb-footer {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.8rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: #047857;
}

/* ===== Encenação da consulta: linhas resolvem uma a uma (CSS puro) =====
   Cada .hb-swap empilha o chip "Consultando…" e o resultado na mesma célula;
   no --hb-delay o pendente sai e o resultado entra. */
.hb-swap {
    display: inline-grid;
    flex-shrink: 0;
}
.hb-swap > * {
    grid-area: 1 / 1;
    justify-self: end;
    align-self: center;
}
.hb-pending {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background-color: #eef2f7;
    color: #64748b;
    animation: hbPendingOut 0.22s ease var(--hb-delay, 1s) forwards;
}
.hb-pending::before {
    content: "";
    width: 0.62em;
    height: 0.62em;
    flex-shrink: 0;
    border-radius: 9999px;
    border: 1.5px solid currentColor;
    border-top-color: transparent;
    animation: hbSpin 0.7s linear infinite;
}
.hb-result {
    opacity: 0;
    transform: scale(0.8);
    animation: hbResultIn 0.32s cubic-bezier(0.22, 1, 0.36, 1) var(--hb-delay, 1s) forwards;
}
@keyframes hbSpin {
    to { transform: rotate(360deg); }
}
@keyframes hbPendingOut {
    to { opacity: 0; visibility: hidden; }
}
@keyframes hbResultIn {
    to { opacity: 1; transform: none; }
}

/* Fila de investigação: a linha começa esmaecida (aguardando), acende
   quando chega a vez dela, um facho varre a linha durante a consulta e
   um flash na cor do veredito marca o momento da detecção */
.hb-row {
    position: relative;
    overflow: hidden;
}
.hb-rows .hb-row {
    animation: hbRowWake 0.3s ease calc(var(--hb-delay, 1s) - 0.5s) both;
}
@keyframes hbRowWake {
    from { opacity: 0.45; }
    to   { opacity: 1; }
}
.hb-row::before {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    left: -35%;
    width: 30%;
    background: linear-gradient(to right,
        rgba(30, 90, 154, 0) 0%,
        rgba(30, 90, 154, 0.12) 50%,
        rgba(30, 90, 154, 0) 100%);
    transform: skewX(-12deg);
    opacity: 0;
    pointer-events: none;
    animation: hbRowScan 0.5s ease-out calc(var(--hb-delay, 1s) - 0.5s) 1 both;
}
@keyframes hbRowScan {
    0%   { left: -35%; opacity: 0; }
    15%  { opacity: 1; }
    100% { left: 105%; opacity: 0; }
}
.hb-row::after {
    content: "";
    position: absolute;
    inset: 0;
    background-color: var(--hb-flash, transparent);
    opacity: 0;
    pointer-events: none;
    animation: hbRowFlash 0.55s ease var(--hb-delay, 1s) 1;
}
@keyframes hbRowFlash {
    0%   { opacity: 0; }
    30%  { opacity: 0.16; }
    100% { opacity: 0; }
}

/* Barra de progresso do cruzamento — avança a cada fonte respondida */
.hb-progress {
    height: 3px;
    border-radius: 9999px;
    background: #eef2f7;
    margin-bottom: 0.75rem;
    overflow: hidden;
}
.hb-progress > span {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: #1e5a9a;
    width: 0%;
    animation:
        hbProgress 2.9s linear forwards,
        hbProgressDone 0.4s ease 2.9s forwards;
}
@keyframes hbProgress {
    0%    { width: 4%; }
    17%   { width: 16%; }
    34.5% { width: 34%; }
    50%   { width: 52%; }
    65.5% { width: 72%; }
    81%   { width: 100%; }
    100%  { width: 100%; }
}
@keyframes hbProgressDone {
    to { background: #059669; }
}

/* Rodapé de conclusão só aparece depois da última linha resolver */
.hb-footer {
    opacity: 0;
    animation: hbResultIn 0.4s ease 2.9s forwards;
}

/* Varredura de radar em loop sobre as linhas — mantém o painel "vivo" */
.hb-rows {
    position: relative;
}
.hb-rows::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    top: -40%;
    height: 36%;
    background: linear-gradient(to bottom,
        rgba(30, 90, 154, 0) 0%,
        rgba(30, 90, 154, 0.07) 50%,
        rgba(30, 90, 154, 0) 100%);
    pointer-events: none;
    opacity: 0;
    animation: hbSweep 6s ease-in-out 3.4s infinite;
}
@keyframes hbSweep {
    0%        { top: -40%; opacity: 0; }
    8%        { opacity: 1; }
    42%       { top: 110%; opacity: 1; }
    50%, 100% { top: 110%; opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .hb-pending { display: none; }
    .hb-result,
    .hb-footer {
        opacity: 1 !important;
        transform: none !important;
        animation: none !important;
    }
    .hb-rows::after,
    .hb-row::before,
    .hb-row::after { display: none; }
    .hb-rows .hb-row {
        animation: none;
        opacity: 1;
    }
    .hb-progress > span {
        animation: none;
        width: 100%;
        background: #059669;
    }
}

.official-sources-section {
    position: relative;
    z-index: 10;
    margin-top: -3.875rem;
}

/* ===================== Hero — polish editorial-técnico ===================== */
#hero {
    position: relative;
    isolation: isolate;
}

/* Camada 1 — malha "blueprint" (evoca planilha / SPED), some nas bordas */
#hero::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 0;
    background-image:
        linear-gradient(to right, rgba(148, 197, 255, 0.07) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(148, 197, 255, 0.07) 1px, transparent 1px);
    background-size: 46px 46px;
    -webkit-mask-image: radial-gradient(125% 95% at 78% 16%, #000 32%, transparent 80%);
    mask-image: radial-gradient(125% 95% at 78% 16%, #000 32%, transparent 80%);
    pointer-events: none;
}

/* Camada 2 — brilho de "radar" no topo direito + leve calor âmbar embaixo */
#hero::after {
    content: "";
    position: absolute;
    inset: 0;
    z-index: 0;
    background:
        radial-gradient(48% 44% at 84% 12%, rgba(96, 165, 250, 0.24), transparent 70%),
        radial-gradient(34% 34% at 10% 96%, rgba(250, 204, 21, 0.07), transparent 72%);
    pointer-events: none;
}

/* Camada 3 — grão fino para textura */
.hero-grain {
    position: absolute;
    inset: 0;
    z-index: 0;
    opacity: 0.05;
    mix-blend-mode: overlay;
    pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* conteúdo acima das camadas atmosféricas */
.hero-shell {
    position: relative;
    z-index: 1;
}

/* Headline editorial */
.hero-copy h1 {
    font-family: 'Fraunces', Georgia, 'Times New Roman', serif;
    font-weight: 600;
    letter-spacing: -0.018em;
    font-optical-sizing: auto;
}

/* Linha de destaque em ouro (amarra ao CTA âmbar) */
.hero-accent-line {
    display: block;
    background: linear-gradient(92deg, #fde68a 0%, #facc15 52%, #eaa916 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: #facc15;                 /* fallback se background-clip falhar */
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 2px 10px rgba(250, 204, 21, 0.20));
}

/* Pulso "ao vivo" no ponto do badge */
.hero-badge-dot {
    position: relative;
}
.hero-badge-dot::after {
    content: "";
    position: absolute;
    inset: -5px;
    border-radius: 9999px;
    border: 1px solid rgba(96, 165, 250, 0.65);
    animation: heroPulse 2.4s ease-out infinite;
}
@keyframes heroPulse {
    0%   { transform: scale(0.55); opacity: 0.9; }
    70%  { opacity: 0; }
    100% { transform: scale(2); opacity: 0; }
}

/* Revelação orquestrada no load — a copy anima só transform (headline e CTA
   nunca ficam invisíveis esperando animação); fade fica restrito ao visual */
@keyframes heroReveal {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: none; }
}
@keyframes heroRise {
    from { transform: translateY(14px); }
    to   { transform: none; }
}
.hero-copy > * {
    animation: heroRise 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
}
.hero-copy > *:nth-child(1) { animation-delay: 0.02s; }
.hero-copy > *:nth-child(2) { animation-delay: 0.06s; }
.hero-copy > *:nth-child(3) { animation-delay: 0.10s; }
.hero-copy > *:nth-child(4) { animation-delay: 0.14s; }
.hero-copy > *:nth-child(5) { animation-delay: 0.18s; }
.hero-copy > *:nth-child(6) { animation-delay: 0.22s; }
.hero-copy > *:nth-child(7) { animation-delay: 0.26s; }
.hero-visual {
    animation: heroReveal 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
    animation-delay: 0.12s;
}

/* Cartões flutuantes sobre o mockup (prova do produto em ação) */
.hero-floaties {
    position: absolute;
    inset: 0;
    z-index: 3;
    pointer-events: none;
    display: none;
}
.hero-float {
    position: absolute;
    opacity: 0;
    border-radius: 0.95rem;
    background: rgba(11, 22, 44, 0.74);
    backdrop-filter: blur(12px) saturate(140%);
    -webkit-backdrop-filter: blur(12px) saturate(140%);
    border: 1px solid rgba(255, 255, 255, 0.13);
    box-shadow: 0 22px 48px -20px rgba(0, 0, 0, 0.65);
}
.hf-inner {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.72rem 0.95rem;
}
.hf-icon {
    width: 2.1rem;
    height: 2.1rem;
    border-radius: 0.65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.hf-title {
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: #fff;
    line-height: 1.15;
}
.hf-sub {
    font-size: 0.68rem;
    color: rgba(255, 255, 255, 0.58);
    margin-top: 2px;
    line-height: 1.2;
}
.hf-dot {
    width: 7px;
    height: 7px;
    border-radius: 9999px;
    flex-shrink: 0;
    position: relative;
}
.hf-dot::after {
    content: "";
    position: absolute;
    inset: -4px;
    border-radius: 9999px;
    border: 1px solid currentColor;
    opacity: 0.5;
    animation: heroPulse 2.2s ease-out infinite;
}
.hero-float--alert {
    top: -11%;
    left: -32%;
    animation: heroReveal 0.6s 1.35s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
.hero-float--ok {
    bottom: -10%;
    right: -18%;
    animation: heroReveal 0.6s 3.1s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
.hero-float--alert .hf-inner { animation: heroDrift 7s 2.0s ease-in-out infinite; }
.hero-float--ok .hf-inner    { animation: heroDrift 8.5s 3.7s ease-in-out infinite; }
@keyframes heroDrift {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-7px); }
}
@media (min-width: 1024px) {
    .hero-floaties { display: block; }
}

@media (prefers-reduced-motion: reduce) {
    .hero-copy > *,
    .hero-visual,
    .hero-float {
        animation: none !important;
        opacity: 1 !important;
        transform: none !important;
    }
    .hf-inner,
    .hero-badge-dot::after,
    .hf-dot::after {
        animation: none !important;
    }
}

/* ── Como Funciona ── */
.cf-step {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.55s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
}
.cf-step.cf-visible { opacity: 1; transform: translateY(0); }
.cf-step:nth-child(1) { transition-delay: 0s; }
.cf-step:nth-child(2) { transition-delay: 0.12s; }
.cf-step:nth-child(3) { transition-delay: 0.24s; }
.cf-step:nth-child(4) { transition-delay: 0.36s; }

.cf-step .cf-icon-box {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.cf-step:hover .cf-icon-box {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px -8px rgba(11, 31, 58, 0.15);
}

.official-sources-banner {
    opacity: 0;
    transform: translateY(18px);
    animation: fadeInUp 0.7s ease-out 0.15s forwards;
}

.official-sources-marquee {
    --official-sources-gap: 2.5rem;
    position: relative;
    overflow: hidden;
    mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
    -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
    opacity: 0.68;
}

.official-sources-track {
    display: flex;
    width: max-content;
    align-items: center;
    gap: 0;
    animation: officialSourcesScroll var(--official-sources-duration, 28s) linear infinite;
    will-change: transform;
}

.official-sources-group {
    display: flex;
    align-items: center;
    gap: var(--official-sources-gap);
    /* Keep the seam spacing inside the measured cycle width. */
    padding-right: var(--official-sources-gap);
    flex-shrink: 0;
}

.official-sources-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    min-height: 3rem;
    color: #4b5563;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    white-space: nowrap;
}

.official-sources-logo svg {
    width: 1.5rem;
    height: 1.5rem;
    flex-shrink: 0;
}

@keyframes officialSourcesScroll {
    from {
        transform: translate3d(0, 0, 0);
    }
    to {
        transform: translate3d(calc(-1 * var(--official-sources-cycle-width, 50%)), 0, 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .official-sources-banner {
        opacity: 1;
        transform: none;
        animation: none;
    }
    
    .official-sources-track {
        animation: none;
        transform: none;
    }
}

/* Two-column hero, owned ENTIRELY by THIS stylesheet (px breakpoints), independent of
   Tailwind's rem-gated lg:/xl: utilities. The HTML no longer carries lg:grid-cols-12 /
   lg:col-span-6 / xl:col-span-6 — those were the bug.
   Why: Tailwind v4 gates lg: at 64rem; with a non-16px root font-size (browser font-size
   preference, OS "make text bigger", some HiDPI/zoom combos) 64rem != 1024px. When lg:
   DID fire, col-span-6 set `grid-column: span 6 / span 6` on each child; inside the
   2-track grids defined below (>=1536px especially) `span 6` overflows to full width, so
   the two children stack into two rows — copy on top, laptop dropped to the bottom and
   cut off by the px-based full-height rules. That's the "outro computador" bug, hit on
   wide (>=1536px) screens. Pinning grid-column:1/2 across the whole desktop range makes
   the 50/50 split deterministic at every width. The per-band grid-template-columns rules
   (1024–1536 here, plus the asymmetric templates at >=1536/1920/2200 below) all expose
   exactly 2 tracks, so 1->copy / 2->laptop always maps correctly. */
@media (min-width: 1024px) {
    .hero-copy   { grid-column: 1; }
    .hero-visual { grid-column: 2; }
}
@media (min-width: 1024px) and (max-width: 1535.98px) {
    .hero-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* iPad landscape (Mini 1024px / Air ~1180px): the xl tuning only kicks in at 1280px,
   so this range gets cramped. Tune copy width, h1 size, gap and mockup explicitly. */
@media (min-width: 1024px) and (max-width: 1279.98px) {
    .hero-shell {
        min-height: auto;
        padding-top: 2.5rem;
        padding-bottom: 3rem;
    }

    .hero-grid {
        gap: 2.25rem;
        align-items: center;
    }

    .hero-copy > * {
        max-width: 32rem;
    }

    .hero-copy h1 {
        font-size: 2.75rem;
        line-height: 1.05;
    }

    .hero-visual {
        justify-content: center;
    }

    .hero-stage {
        width: min(100%, 26rem);
    }
}

@media (min-width: 1280px) {
    .hero-first-fold {
        min-height: calc(100svh - var(--landing-header-height) - 8.75rem);
    }

    .hero-shell {
        max-width: min(94vw, 104rem);
        min-height: calc(100svh - var(--landing-header-height) - 15.75rem);
    }

    .hero-grid {
        gap: 3rem;
    }

    .hero-copy > * {
        max-width: 36rem;
    }

    .hero-visual {
        justify-content: center;
    }
}

@media (min-width: 1536px) {
    #hero {
        overflow: clip;
    }

    .hero-first-fold {
        min-height: calc(100svh - var(--landing-header-height) - 9.5rem);
    }

    .hero-shell {
        max-width: min(95vw, 118rem);
        min-height: calc(100svh - var(--landing-header-height) - 16.5rem);
        padding-top: 2.625rem;
        padding-bottom: 3.5rem;
    }

    .hero-grid {
        grid-template-columns: minmax(0, 34rem) minmax(0, 1fr);
        gap: 4rem;
        align-items: center;
    }

    .hero-copy > * {
        max-width: 34rem;
    }

    .hero-copy h1 {
        font-size: 3.75rem;
        line-height: 1.02;
    }

    .hero-copy p {
        max-width: 32rem;
    }

    .hero-visual {
        min-height: 34rem;
        justify-content: center;
        padding-right: 0;
    }

    .hero-visual-glow {
        inset: 6% 6% 8% 10%;
    }

    .hero-stage {
        width: min(100%, 30rem);
    }
}

@media (min-width: 1920px) {
    .hero-first-fold {
        min-height: calc(100svh - var(--landing-header-height) - 9.75rem);
    }

    .hero-shell {
        max-width: min(92vw, 120rem);
        min-height: calc(100svh - var(--landing-header-height) - 16.75rem);
    }

    .hero-grid {
        grid-template-columns: clamp(34rem, 24vw, 38rem) minmax(0, 1fr);
        gap: 5rem;
    }

    .hero-copy > * {
        max-width: 35rem;
    }

    .hero-copy h1 {
        font-size: 4.3rem;
    }

    .hero-copy p {
        max-width: 33rem;
    }

    .hero-visual {
        min-height: 36rem;
        padding-right: 0;
    }

    .hero-visual-glow {
        inset: 6% 8% 8% 12%;
    }

    .hero-stage {
        width: min(100%, 31rem);
        transform: scale(1.08);
    }
}

@media (min-width: 2200px) {
    .hero-shell {
        max-width: min(90vw, 128rem);
    }

    .hero-grid {
        grid-template-columns: clamp(36rem, 22vw, 44rem) minmax(0, 1fr);
        gap: clamp(5rem, 5vw, 8rem);
    }

    .hero-copy > * {
        max-width: clamp(36rem, 24vw, 44rem);
    }

    .hero-copy h1 {
        font-size: clamp(4.3rem, 3.6vw, 5.4rem);
        line-height: 1.02;
    }

    .hero-copy p {
        max-width: clamp(34rem, 22vw, 42rem);
    }

    .hero-visual {
        padding-right: 0;
    }

    .hero-visual-glow {
        inset: 6% 10% 8% 14%;
    }

    .hero-stage {
        width: min(100%, 31rem);
        transform: scale(1.16);
    }
}

@media (max-height: 820px) and (min-width: 1024px) {
    .hero-first-fold,
    .hero-shell {
        min-height: 0;
    }

    .hero-shell {
        padding-top: 1.5rem;
        padding-bottom: 2rem;
    }

    .hero-visual {
        min-height: 28rem;
    }

    .hero-stage {
        width: min(100%, 26rem);
    }
}

@media (max-width: 1023px) {
    :root {
        --landing-header-height: 80px;
    }

    .hero-first-fold,
    .hero-shell {
        min-height: auto;
    }

    .hero-visual {
        justify-content: center;
    }

    .hero-stage {
        width: min(100%, 30rem);
        margin-inline: auto;
    }

    .official-sources-section {
        margin-top: -2.25rem;
    }
}

@media (max-width: 639px) {
    .official-sources-marquee {
        --official-sources-gap: 1.75rem;
        mask-image: none;
        -webkit-mask-image: none;
    }

    .official-sources-logo {
        min-height: 2.75rem;
        font-size: 0.95rem;
    }
}
</style>

<div class="hero-first-fold">
<!-- Hero Section (refeito) -->
<section id="hero" class="relative overflow-hidden bg-gradient-to-br from-primary-700 to-primary-500 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e5a9a 50%, #0f172a 100%);">
    <div class="hero-grain" aria-hidden="true"></div>
    <div class="hero-shell mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-16 sm:pt-12 sm:pb-16 lg:pt-14 lg:pb-20">
        <div class="hero-grid grid grid-cols-1 gap-8 items-center justify-center">
            <!-- Coluna Esquerda: Texto -->
            <div class="hero-copy">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold mb-4">
                    <span class="hero-badge-dot w-2 h-2 rounded-full bg-blue-400"></span>
                    <span>Radar de riscos fiscais<span class="hidden sm:inline">&nbsp;para escritórios contábeis</span></span>
                </div>

                <!-- Título -->
                <h1 class="font-extrabold leading-tight tracking-tight text-3xl sm:text-4xl xl:text-5xl">
                    O fisco cruza os dados dos seus clientes.
                    <span class="hero-accent-line">Cruze primeiro.</span>
                </h1>

                <!-- Subtítulo -->
                <p class="mt-4 text-base sm:text-lg text-white/80 max-w-2xl">
                    Importe o SPED e o FiscalDock confronta cada participante e cada nota com Receita Federal, SEFAZ, PGFN e SINTEGRA. Fornecedor inapto, nota cancelada e certidão vencida aparecem no seu painel — não na malha fiscal.
                </p>

                <!-- CTAs -->
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <a href="/criar-conta" data-link data-button="cta" class="btn-cta">
                        <span class="whitespace-nowrap">Criar conta grátis</span>
                        <svg class="h-5 w-5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="#como-funciona" class="btn-ghost-hero">
                        Ver como funciona
                    </a>
                </div>

                <!-- Frase de apoio -->
                <p class="mt-3 text-sm text-white/80 max-w-2xl">
                    Você começa com <strong class="text-white">@brl(config('trial.saldo_reais')) de saldo grátis</strong> — sem cartão de crédito, sem mensalidade. Dá para importar e auditar clientes reais.
                </p>

                <!-- Prova visual compacta para mobile/tablet (os floaties do mockup só aparecem >=1024px) -->
                <div class="hero-proof-chips lg:hidden" aria-hidden="true">
                    <span class="hero-proof-chip">
                        <span class="hpc-dot" style="background-color: #f87171;"></span>
                        Fornecedor inapto detectado
                    </span>
                    <span class="hero-proof-chip">
                        <span class="hpc-dot" style="background-color: #34d399;"></span>
                        CND Federal emitida
                    </span>
                </div>

                <!-- Fatos do produto (trial vem de config/trial.php — nunca hardcodar) -->
                <div class="mt-8 lg:mb-16">
                    <div class="hero-facts">
                        <div class="hero-fact">
                            <span class="hero-fact-num">@brl(config('trial.saldo_reais')) <span class="hero-gratis-tag">grátis</span></span>
                            <span class="hero-fact-label">já entram na sua conta ao se cadastrar — sem pagar nada, válidos por {{ config('trial.validade_dias') }} dias</span>
                        </div>
                        <div class="hero-fact">
                            <span class="hero-fact-num">{{ $fontesConsultadas }} fontes</span>
                            <span class="hero-fact-label">oficiais consultadas — nada estimado ou inferido</span>
                        </div>
                        <div class="hero-fact">
                            <span class="hero-fact-num">Minutos</span>
                            <span class="hero-fact-label">do upload do SPED ao diagnóstico completo</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita: Mockup do painel (DOM — nítido em qualquer densidade de tela) -->
            <div class="hero-visual">
                <div class="hero-visual-glow" aria-hidden="true"></div>
                <div class="hero-stage">
                <div class="hero-browser" role="img"
                     aria-label="Painel do FiscalDock mostrando riscos fiscais detectados entre os participantes do SPED">
                    <div class="hb-bar">
                        <span class="hb-dot" style="background-color: #f87171;"></span>
                        <span class="hb-dot" style="background-color: #fbbf24;"></span>
                        <span class="hb-dot" style="background-color: #34d399;"></span>
                        <span class="hb-url">app.fiscaldock.com.br/consulta</span>
                    </div>
                    <div class="hb-body">
                        <div class="hb-head">
                            <div>
                                <div class="hb-title">Radar de riscos — EFD mar/2026</div>
                                <div class="hb-sub">142 participantes cruzados com {{ $fontesConsultadas }} fontes oficiais</div>
                            </div>
                            <span class="hb-swap" style="--hb-delay: 2.7s;">
                                <span class="hb-pill hb-pending">Cruzando fontes…</span>
                                <span class="hb-pill hb-result" style="background-color: #fef3c7; color: #92400e;">4 riscos ativos</span>
                            </span>
                        </div>
                        <div class="hb-progress" aria-hidden="true"><span></span></div>
                        <div class="hb-rows">
                            <div class="hb-row" style="--hb-delay: 1.0s; --hb-flash: #ef4444;">
                                <div class="hb-row-main">
                                    <span class="hb-row-nome">Transportes Alfa Ltda</span>
                                    <span class="hb-row-meta">12.345.678/0001-90 · Receita Federal</span>
                                </div>
                                <span class="hb-swap">
                                    <span class="hb-chip hb-pending">Consultando</span>
                                    <span class="hb-chip hb-result" style="background-color: #fee2e2; color: #b91c1c;">Inapto</span>
                                </span>
                            </div>
                            <div class="hb-row" style="--hb-delay: 1.45s; --hb-flash: #f59e0b;">
                                <div class="hb-row-main">
                                    <span class="hb-row-nome">Metalúrgica Boreal S.A.</span>
                                    <span class="hb-row-meta">98.765.432/0001-10 · PGFN</span>
                                </div>
                                <span class="hb-swap">
                                    <span class="hb-chip hb-pending">Consultando</span>
                                    <span class="hb-chip hb-result" style="background-color: #fef3c7; color: #b45309;">CND vencida</span>
                                </span>
                            </div>
                            <div class="hb-row" style="--hb-delay: 1.9s; --hb-flash: #ef4444;">
                                <div class="hb-row-main">
                                    <span class="hb-row-nome">Comercial Delta ME</span>
                                    <span class="hb-row-meta">45.678.901/0001-22 · SEFAZ</span>
                                </div>
                                <span class="hb-swap">
                                    <span class="hb-chip hb-pending">Consultando</span>
                                    <span class="hb-chip hb-result" style="background-color: #fee2e2; color: #b91c1c;">Nota cancelada</span>
                                </span>
                            </div>
                            <div class="hb-row" style="--hb-delay: 2.35s; --hb-flash: #10b981;">
                                <div class="hb-row-main">
                                    <span class="hb-row-nome">Indústria Vetor Ltda</span>
                                    <span class="hb-row-meta">11.222.333/0001-44 · SINTEGRA</span>
                                </div>
                                <span class="hb-swap">
                                    <span class="hb-chip hb-pending">Consultando</span>
                                    <span class="hb-chip hb-result" style="background-color: #d1fae5; color: #047857;">Regular</span>
                                </span>
                            </div>
                        </div>
                        <div class="hb-footer">
                            <svg width="13" height="13" fill="none" stroke="#047857" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Cruzamento concluído em 4 min — 138 participantes regulares
                        </div>
                    </div>
                </div>

                <!-- Cartões flutuantes: o produto pegando risco em tempo real -->
                <div class="hero-floaties" aria-hidden="true">
                    <div class="hero-float hero-float--alert">
                        <div class="hf-inner">
                            <span class="hf-icon" style="background-color: rgba(239, 68, 68, 0.16);">
                                <svg width="18" height="18" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M10.34 3.94 1.7 18.06A1.5 1.5 0 0 0 3 20.25h18a1.5 1.5 0 0 0 1.3-2.19L13.66 3.94a1.5 1.5 0 0 0-2.62 0Z"/>
                                </svg>
                            </span>
                            <div>
                                <div class="hf-title" style="display:flex; align-items:center; gap:.4rem;">
                                    <span class="hf-dot" style="background-color:#f87171; color:#f87171;"></span>
                                    Fornecedor inapto
                                </div>
                                <div class="hf-sub">CNPJ 12.345.678/0001-90 · Receita Federal</div>
                            </div>
                        </div>
                    </div>
                    <div class="hero-float hero-float--ok">
                        <div class="hf-inner">
                            <span class="hf-icon" style="background-color: rgba(16, 185, 129, 0.16);">
                                <svg width="18" height="18" fill="none" stroke="#34d399" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                            </span>
                            <div>
                                <div class="hf-title">CND Federal emitida</div>
                                <div class="hf-sub">Certidão negativa · há 2 min</div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Integrações Oficiais Banner -->
<section class="official-sources-section bg-gray-100 border-y border-gray-200 py-5 sm:py-6">
    <div class="official-sources-banner max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-4">
            Dados extraídos e cruzados de fontes oficiais
        </p>
        <div class="official-sources-marquee" aria-label="Fontes oficiais integradas">
            <div class="official-sources-track">
                <div class="official-sources-group" data-official-sources-group aria-hidden="false">
                    <div class="official-sources-logo">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px; flex-shrink: 0; display: block;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3.75h6.586a1 1 0 01.707.293l2.664 2.664a1 1 0 01.293.707V19.25A1.75 1.75 0 0115.5 21h-8A1.75 1.75 0 015.75 19.25V5.5A1.75 1.75 0 017.5 3.75z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3.75V7a1 1 0 001 1h3.25"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.75 10.25h6.5"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.75 13.25h6.5"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.75 16.25h4"></path>
                        </svg>
                        Receita Federal
                    </div>
                    <div class="official-sources-logo">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px; flex-shrink: 0; display: block;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        SEFAZ
                    </div>
                    <div class="official-sources-logo">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px; flex-shrink: 0; display: block;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                        Portal da Transparência
                    </div>
                    <div class="official-sources-logo">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px; flex-shrink: 0; display: block;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        SINTEGRA
                    </div>
                    <div class="official-sources-logo">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px; flex-shrink: 0; display: block;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                        </svg>
                        PGFN
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>

<!-- Como Funciona Section -->
<!-- Antes x Depois — ganho imediato -->
<section id="antes-depois" class="bg-white pt-14 pb-16 sm:pt-16 sm:pb-20 lg:pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-10 lg:mb-12">
            <p class="landing-kicker">01 · Antes × depois</p>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight mb-4">O que você ganha, direto ao ponto</h2>
            <p class="text-sm sm:text-base text-gray-500 max-w-2xl mx-auto">
                Cinco rotinas do escritório — como são hoje e como ficam a partir da primeira importação
            </p>
        </div>

        <div class="gain-panel">
            <div class="gain-head">
                <span>Rotina</span>
                <span>Hoje</span>
                <span class="gain-h-com">Com o FiscalDock</span>
            </div>
            <div class="gain-row" style="--d: 0.05s;">
                <span class="gain-rotina">Consultar os CNPJs do SPED</span>
                <span class="gain-hoje">um a um, no site da Receita</span>
                <span class="gain-com">todos os participantes de uma vez</span>
            </div>
            <div class="gain-row" style="--d: 0.15s;">
                <span class="gain-rotina">Certidões para licitação</span>
                <span class="gain-hoje">5 sites, 5 senhas, 5 PDFs</span>
                <span class="gain-com">CND, CNDT e FGTS em 1 consulta</span>
            </div>
            <div class="gain-row" style="--d: 0.25s;">
                <span class="gain-rotina">Fornecedor que fica inapto</span>
                <span class="gain-hoje">descoberto na malha fina</span>
                <span class="gain-com">alerta automático no painel</span>
            </div>
            <div class="gain-row" style="--d: 0.35s;">
                <span class="gain-rotina">Situação da nota na SEFAZ</span>
                <span class="gain-hoje">conferência por amostragem</span>
                <span class="gain-com">verificação em lote pela chave</span>
            </div>
            <div class="gain-row" style="--d: 0.45s;">
                <span class="gain-rotina">Reforma tributária</span>
                <span class="gain-hoje">planilhas paralelas até 2033</span>
                <span class="gain-com">transição acompanhada por cliente</span>
            </div>
        </div>
    </div>
</section>

<section id="como-funciona" class="bg-gray-50 pt-8 pb-16 sm:pt-10 sm:pb-20 lg:pt-12 lg:pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 sm:mb-14 lg:mb-16">
            <p class="landing-kicker">02 · Como funciona</p>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight mb-4">Da importação ao monitoramento contínuo</h2>
            <p class="text-sm sm:text-base text-gray-500 max-w-2xl mx-auto">
                Quatro passos entre o upload do arquivo e o primeiro alerta no seu painel — sem configuração manual.
            </p>
        </div>

        <div class="pipe-panel">
            <div class="pipe-head">
                <span>como_funciona.pipeline</span>
                <span class="pipe-flow">importar &rarr; cruzar &rarr; identificar &rarr; monitorar</span>
            </div>
            <div class="pipe-grid">
                <article class="pipe-col">
                    <p class="pipe-step"><b>passo 01</b> · upload</p>
                    <div class="fx-glyph fx-glyph--sm mb-4" style="background-color: #eef2f7; border: 1px solid #dce3ed; color: #1e4fa0;">SPED</div>
                    <h3 class="text-lg font-bold text-gray-900">Importe</h3>
                    <p class="text-sm text-gray-600 leading-6 mt-2 mb-5">Envie arquivos SPED, importe XMLs ou consulte documentos fiscais — em segundos a plataforma organiza notas, participantes e apurações para você.</p>
                    <p class="pipe-tags">SPED · XML · Automático</p>
                </article>

                <article class="pipe-col">
                    <p class="pipe-step"><b>passo 02</b> · validação</p>
                    <div class="fx-glyph fx-glyph--sm mb-4" style="background-color: #eef2f7; border: 1px solid #dce3ed; color: #1e4fa0;">RFB</div>
                    <h3 class="text-lg font-bold text-gray-900">Cruze</h3>
                    <p class="text-sm text-gray-600 leading-6 mt-2 mb-5">A plataforma consulta automaticamente Receita Federal, SEFAZ e PGFN — você descobre quem está inapto, o que foi cancelado e onde os valores não batem.</p>
                    <p class="pipe-tags">Receita · SEFAZ · CNDs</p>
                </article>

                <article class="pipe-col">
                    <p class="pipe-step"><b>passo 03</b> · análise</p>
                    <div class="fx-glyph fx-glyph--sm mb-4" style="background-color: #fef8ee; border: 1px solid #f5e6c8; color: #b45309;">[!]</div>
                    <h3 class="text-lg font-bold text-gray-900">Identifique</h3>
                    <p class="text-sm text-gray-600 leading-6 mt-2 mb-5">Receba alertas automáticos de fornecedores inaptos, notas canceladas e divergências fiscais — antes que virem autuação.</p>
                    <p class="pipe-tags">Alertas · Score de Risco</p>
                </article>

                <article class="pipe-col">
                    <p class="pipe-step"><b>passo 04</b> · contínuo</p>
                    <div class="fx-glyph fx-glyph--sm mb-4" style="background-color: #eefbf5; border: 1px solid #c6eed8; color: #047857;">24/7</div>
                    <h3 class="text-lg font-bold text-gray-900">Monitore</h3>
                    <p class="text-sm text-gray-600 leading-6 mt-2 mb-5">Acompanhe mudanças cadastrais de cada participante de forma contínua. Se algo mudar, você é o primeiro a saber.</p>
                    <p class="pipe-tags">Diário · Semanal · Mensal</p>
                </article>
            </div>
        </div>
    </div>
</section>

<!-- Radar ao Vivo Section -->
<section id="radar-vivo" class="relative py-16 sm:py-20 overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0f172a 100%);">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-center">
            <div data-radar-col>
                <p class="landing-kicker landing-kicker--dark">03 · O radar trabalhando</p>
                <h2 class="text-2xl sm:text-3xl font-bold text-white tracking-tight mb-4">Cada linha do SPED vira uma verificação</h2>
                <p class="text-sm sm:text-base mb-6" style="color: rgba(255,255,255,0.65);">
                    O arquivo que você já entrega ao fisco é a matéria-prima. O FiscalDock lê registro por registro e confronta cada um com as bases oficiais — enquanto você cuida do resto.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 text-sm" style="color: rgba(255,255,255,0.75);">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" style="color: #facc15;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Participantes (registro 0150) consultados na Receita Federal e no SINTEGRA
                    </li>
                    <li class="flex items-start gap-3 text-sm" style="color: rgba(255,255,255,0.75);">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" style="color: #facc15;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Notas (C100, D100, A100) confrontadas na SEFAZ pela chave de acesso
                    </li>
                    <li class="flex items-start gap-3 text-sm" style="color: rgba(255,255,255,0.75);">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" style="color: #facc15;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Apurações (M210, E110) e retenções (F600) extraídas e conferidas
                    </li>
                </ul>
            </div>

            <div data-radar-col>
                <div class="radar-terminal">
                    <div class="radar-terminal-head">
                        <span class="radar-dot" style="background-color: #f87171;"></span>
                        <span class="radar-dot" style="background-color: #fbbf24;"></span>
                        <span class="radar-dot" style="background-color: #34d399;"></span>
                        <span class="radar-file">efd_contribuicoes_abr-2026.txt</span>
                        <span class="radar-live"><span class="radar-live-dot"></span>Auditando</span>
                    </div>
                    <ul id="radar-feed-list">
                        <li class="radar-row">
                            <span class="radar-reg">C100</span>
                            <span class="radar-desc">NF-e 3524…0917 · R$ 12.480,00</span>
                            <span class="radar-status radar-status--ok">autorizada</span>
                        </li>
                        <li class="radar-row">
                            <span class="radar-reg">0150</span>
                            <span class="radar-desc">Participante 98.765.432/0001-10</span>
                            <span class="radar-status radar-status--alerta">CNPJ inapto</span>
                        </li>
                        <li class="radar-row">
                            <span class="radar-reg">M210</span>
                            <span class="radar-desc">PIS apurado · R$ 8.912,44</span>
                            <span class="radar-status radar-status--ok">confere</span>
                        </li>
                        <li class="radar-row">
                            <span class="radar-reg">CND</span>
                            <span class="radar-desc">Federal · PGFN</span>
                            <span class="radar-status radar-status--aviso">vence em 12 dias</span>
                        </li>
                        <li class="radar-row">
                            <span class="radar-reg">D100</span>
                            <span class="radar-desc">CT-e 3524…7789 · frete interestadual</span>
                            <span class="radar-status radar-status--ok">autorizado</span>
                        </li>
                        <li class="radar-row">
                            <span class="radar-reg">C100</span>
                            <span class="radar-desc">NF-e 3524…5501 · R$ 8.902,15</span>
                            <span class="radar-status radar-status--alerta">cancelada na SEFAZ</span>
                        </li>
                        <li class="radar-row">
                            <span class="radar-reg">E110</span>
                            <span class="radar-desc">ICMS a recolher · R$ 14.320,00</span>
                            <span class="radar-status radar-status--ok">confere</span>
                        </li>
                    </ul>
                    <div class="radar-terminal-foot">
                        <span class="radar-foot-ok">✓ <b id="radar-feed-ok">5</b> verificações</span>
                        <span class="radar-foot-alerta">⚠ <b id="radar-feed-alertas">2</b> alertas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Soluções Section -->
<section id="funcionalidades" class="bg-white pt-8 pb-20 sm:pt-10 sm:pb-24 lg:pt-12 lg:pb-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center mb-16 sm:mb-20">
            <p class="landing-kicker">04 · Funcionalidades</p>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight mb-4">
                Tudo que o seu escritório precisa
            </h2>
            <p class="text-base text-gray-500 max-w-2xl mx-auto">
                Três pilares — auditoria do SPED, consulta de CNPJ e alinhamento com a reforma tributária — e tudo que os conecta.
            </p>
        </div>

        <!-- Bento Grid — layout assimétrico -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-6">

            <!-- 1. SPED / EFD — Hero Card (2 colunas) -->
            <div class="md:col-span-2 group rounded-2xl border border-gray-200 p-6 lg:p-8 overflow-hidden hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex flex-col">
                <div class="flex flex-col lg:flex-row lg:gap-8 flex-1">
                    <div class="flex-1 flex flex-col">
                        <div class="fx-glyph mb-5" style="background-color: #eef2f7; border: 1px solid #dce3ed; color: #1e4fa0;">EFD</div>
                        <h3 class="text-base font-bold text-gray-900 mb-2">Auditoria e Compliance</h3>
                        <p class="text-sm text-gray-500 leading-relaxed mb-4">
                            Importe o arquivo EFD e receba a radiografia completa: apurações de ICMS e PIS/COFINS, alertas de inconsistência interna e o status fiscal de cada participante — tudo extraído automaticamente.
                        </p>
                        <ul class="space-y-2">
                            <li class="flex items-start text-xs text-gray-600">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                7 alertas automáticos: duplicatas, CFOP invertido, notas zeradas e mais
                            </li>
                            <li class="flex items-start text-xs text-gray-600">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Apuração ICMS, PIS/COFINS e retenções extraídas do arquivo
                            </li>
                        </ul>
                        <div class="flex flex-wrap gap-2" style="margin-top: auto; padding-top: 1.25rem; border-top: 1px solid #e5e7eb;">
                            <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">SPED / EFD</span>
                            <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">ICMS</span>
                            <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">PIS/COFINS</span>
                        </div>
                    </div>
                    <!-- Vinheta: importação EFD em andamento -->
                    <div class="hidden lg:flex w-72 shrink-0 items-center">
                        <div class="fx-panel w-full" aria-hidden="true">
                            <div class="fx-import-head">
                                <span class="fx-file">efd_fiscal_mar-2026.txt</span>
                                <span class="fx-badge-alertas">7 alertas</span>
                            </div>
                            <div class="fx-import-row">
                                <span class="fx-import-label">participantes</span>
                                <span class="fx-bar"><i style="--w: 100%; --d: 0.15s;"></i></span>
                                <span class="fx-import-val">132 ✓</span>
                            </div>
                            <div class="fx-import-row">
                                <span class="fx-import-label">notas fiscais</span>
                                <span class="fx-bar"><i style="--w: 100%; --d: 0.35s;"></i></span>
                                <span class="fx-import-val">4.318 ✓</span>
                            </div>
                            <div class="fx-import-row">
                                <span class="fx-import-label">apuração</span>
                                <span class="fx-bar"><i style="--w: 100%; --d: 0.55s;"></i></span>
                                <span class="fx-import-val">ICMS ✓</span>
                            </div>
                            <div class="fx-import-row">
                                <span class="fx-import-label">retenções</span>
                                <span class="fx-bar"><i style="--w: 74%; --d: 0.75s;"></i></span>
                                <span class="fx-import-val">F600…</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Clearance NF-e — compacto -->
            <div class="group rounded-2xl border border-gray-200 p-6 lg:p-8 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 self-end flex flex-col">
                <div class="fx-glyph mb-5" style="background-color: #eef2f7; border: 1px solid #dce3ed; color: #1e4fa0;">NF-e</div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Clearance NF-e</h3>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    As notas do SPED são confrontadas com a base da SEFAZ pela chave de acesso — canceladas, frias e divergências de valor aparecem na hora, em lote.
                </p>
                <div class="fx-panel" aria-hidden="true">
                    <div class="fx-row" style="--d: 0.1s;">
                        <span class="fx-row-doc">3524 06…&thinsp;0917</span>
                        <span class="fx-status fx-status--ok">autorizada</span>
                    </div>
                    <div class="fx-row" style="--d: 0.25s;">
                        <span class="fx-row-doc">3524 06…&thinsp;5501</span>
                        <span class="fx-status fx-status--alerta">cancelada</span>
                    </div>
                    <div class="fx-row" style="--d: 0.4s;">
                        <span class="fx-row-doc">3524 06…&thinsp;1276</span>
                        <span class="fx-status fx-status--aviso">divergência</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2" style="margin-top: auto; padding-top: 1.25rem; border-top: 1px solid #e5e7eb;">
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">SEFAZ</span>
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">NF-e</span>
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">CT-e</span>
                </div>
            </div>

            <!-- 3. Monitoramento — pill AO VIVO + bullets + métrica -->
            <div class="group rounded-2xl border border-gray-200 p-6 lg:p-8 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex flex-col">
                <div class="flex items-center justify-between mb-5">
                    <div class="fx-glyph" style="background-color: #fef8ee; border: 1px solid #f5e6c8; color: #b45309;">24/7</div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider text-white" style="background-color: #dc2626;">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background-color: #fca5a5;"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                        </span>
                        Ao vivo
                    </span>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Monitoramento 24/7</h3>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    Vigilância contínua por CNPJ — diária, semanal ou mensal. Quando um fornecedor ou cliente muda de status na Receita, você é o primeiro a saber.
                </p>
                <div class="fx-panel" aria-hidden="true">
                    <div class="fx-monitor-cnpj">12.345.678/0001-90</div>
                    <div class="fx-monitor-flow">
                        <span class="fx-status fx-status--ok">ativa</span>
                        <span class="fx-monitor-arrow">→</span>
                        <span class="fx-status fx-status--alerta fx-pop">inapta</span>
                    </div>
                    <div class="fx-monitor-meta">alerta enviado · hoje, 07:12</div>
                </div>
                <div class="flex flex-wrap gap-2" style="margin-top: auto; padding-top: 1.25rem; border-top: 1px solid #e5e7eb;">
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">Participantes</span>
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">CNPJ</span>
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">Alertas</span>
                </div>
            </div>

            <!-- 4. Consulta de CNPJ — Hero Card (2 colunas) -->
            <div class="md:col-span-2 group rounded-2xl border border-gray-200 p-6 lg:p-8 overflow-hidden hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex flex-col">
                <div class="flex flex-col lg:flex-row lg:gap-8 flex-1">
                    <div class="flex-1 flex flex-col">
                        <div class="fx-glyph mb-5" style="background-color: #eef2f7; border: 1px solid #dce3ed; color: #1e4fa0;">CNPJ</div>
                        <h3 class="text-base font-bold text-gray-900 mb-2">Consulta de CNPJ</h3>
                        <p class="text-sm text-gray-500 leading-relaxed mb-4">
                            Um CNPJ entra, um dossiê sai: situação cadastral, regime tributário, certidões federais, estaduais e municipais, CNDT, FGTS e SINTEGRA — fontes oficiais em uma consulta, um a um ou em lote.
                        </p>
                        <ul class="space-y-2">
                            <li class="flex items-start text-xs text-gray-600">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Planos por necessidade: da validação cadastral gratuita à due diligence completa
                            </li>
                            <li class="flex items-start text-xs text-gray-600">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #1e4fa0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Estorno automático do valor quando uma fonte oficial falha
                            </li>
                        </ul>
                        <div class="flex flex-wrap gap-2" style="margin-top: auto; padding-top: 1.25rem; border-top: 1px solid #e5e7eb;">
                            <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">Receita Federal</span>
                            <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">PGFN</span>
                            <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">SINTEGRA · TST</span>
                        </div>
                    </div>
                    <!-- Vinheta: dossiê do CNPJ -->
                    <div class="hidden lg:flex w-72 shrink-0 items-center">
                        <div class="fx-panel w-full" aria-hidden="true">
                            <div class="fx-import-head">
                                <span class="fx-file">dossiê · 12.345.678/0001-90</span>
                                <span class="fx-status fx-status--ok">score 82</span>
                            </div>
                            <div class="fx-row" style="--d: 0.1s;">
                                <span class="fx-row-doc">Situação cadastral</span>
                                <span class="fx-status fx-status--ok">ativa</span>
                            </div>
                            <div class="fx-row" style="--d: 0.22s;">
                                <span class="fx-row-doc">CND Federal · PGFN</span>
                                <span class="fx-status fx-status--ok">negativa</span>
                            </div>
                            <div class="fx-row" style="--d: 0.34s;">
                                <span class="fx-row-doc">FGTS · Caixa</span>
                                <span class="fx-status fx-status--ok">regular</span>
                            </div>
                            <div class="fx-row" style="--d: 0.46s;">
                                <span class="fx-row-doc">CND Municipal · Prefeitura</span>
                                <span class="fx-status fx-status--ok">regular</span>
                            </div>
                            <div class="fx-row" style="--d: 0.58s;">
                                <span class="fx-row-doc">CND Estadual · SEFAZ</span>
                                <span class="fx-status fx-status--aviso">vence em 12d</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. BI Fiscal — compacto -->
            <div class="group rounded-2xl border border-gray-200 p-6 lg:p-8 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex flex-col">
                <div class="fx-glyph mb-5" style="background-color: #eefbf5; border: 1px solid #c6eed8; color: #047857;">BI</div>
                <h3 class="text-base font-bold text-gray-900 mb-2">BI Fiscal</h3>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    Faturamento, compras e tributos de cada cliente em 6 visões: geral, CFOP, participantes, tributário, alertas e compliance. Leitura rápida para priorizar onde agir primeiro.
                </p>
                <div class="fx-panel" aria-hidden="true">
                    <div class="fx-chart-bars">
                        <i style="--h: 42%; --d: 0.10s;"></i>
                        <i style="--h: 58%; --d: 0.20s;"></i>
                        <i style="--h: 34%; --d: 0.30s;"></i>
                        <i class="fx-chart-bar--destaque" style="--h: 88%; --d: 0.40s;"></i>
                        <i style="--h: 64%; --d: 0.50s;"></i>
                        <i style="--h: 76%; --d: 0.60s;"></i>
                    </div>
                    <div class="fx-chart-legend">faturamento × compras · por período</div>
                </div>
                <div class="flex flex-wrap gap-2" style="margin-top: auto; padding-top: 1.25rem; border-top: 1px solid #e5e7eb;">
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">BI Fiscal</span>
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">CFOP</span>
                    <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">Tributário</span>
                </div>
            </div>

            <!-- 6. Reforma Tributária — 2 colunas -->
            <div class="md:col-span-2 group rounded-2xl border border-gray-200 p-6 lg:p-8 overflow-hidden hover:-translate-y-1 hover:shadow-xl transition-all duration-300 flex flex-col">
                <div class="flex flex-col lg:flex-row lg:gap-8 flex-1">
                    <div class="flex-1 flex flex-col">
                        <div class="fx-glyph mb-5" style="background-color: #fef8ee; border: 1px solid #f5e6c8; color: #b45309;">CBS</div>
                        <h3 class="text-base font-bold text-gray-900 mb-2">Alinhado à reforma tributária</h3>
                        <p class="text-sm text-gray-500 leading-relaxed mb-4">
                            2026 é o ano-teste da CBS e do IBS — e os novos campos já chegam nos XMLs e SPEDs que você importa. Acompanhe a transição por cliente, com o sistema antigo e o novo lado a lado até 2033.
                        </p>
                        <ul class="space-y-2 mb-4">
                            <li class="flex items-start text-xs text-gray-600">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #b45309;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Campos de CBS e IBS lidos nos documentos que você já importa hoje
                            </li>
                            <li class="flex items-start text-xs text-gray-600">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #b45309;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Carga antiga × nova comparada por cliente durante os 7 anos de convivência
                            </li>
                            <li class="flex items-start text-xs text-gray-600">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0 mt-0.5" style="color: #b45309;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Cada degrau do phase-out do ICMS/ISS acompanhado ano a ano
                            </li>
                        </ul>
                        <a href="#reforma" class="inline-flex items-center gap-1.5 text-sm font-semibold" style="color: #1e4fa0;">
                            Ver o cronograma da transição
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        </a>
                        <div class="flex flex-wrap gap-2" style="margin-top: auto; padding-top: 1.25rem; border-top: 1px solid #e5e7eb;">
                            <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">CBS</span>
                            <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">IBS</span>
                            <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400 whitespace-nowrap" style="background-color: #f3f4f6; padding: 4px 10px; border-radius: 4px; display: inline-block;">EC 132/2023</span>
                        </div>
                    </div>
                    <!-- Vinheta: transição em andamento -->
                    <div class="hidden lg:flex w-72 shrink-0 items-center">
                        <div class="fx-panel w-full" aria-hidden="true">
                            <div class="fx-import-head">
                                <span class="fx-file">transição · EC 132/2023</span>
                                <span class="fx-badge-alertas">2026 · ano-teste</span>
                            </div>
                            <div class="fx-row" style="--d: 0.1s;">
                                <span class="fx-row-doc">CBS 0,9% · destaque no XML</span>
                                <span class="fx-status fx-status--ok">em vigor</span>
                            </div>
                            <div class="fx-row" style="--d: 0.22s;">
                                <span class="fx-row-doc">IBS 0,1% · ano-teste</span>
                                <span class="fx-status fx-status--ok">em vigor</span>
                            </div>
                            <div class="fx-row" style="--d: 0.34s;">
                                <span class="fx-row-doc">PIS/COFINS</span>
                                <span class="fx-status fx-status--aviso">extinção 2027</span>
                            </div>
                            <div class="fx-row" style="--d: 0.46s;">
                                <span class="fx-row-doc">Imposto Seletivo</span>
                                <span class="fx-status fx-status--aviso">a partir de 2027</span>
                            </div>
                            <div class="fx-row" style="--d: 0.58s;">
                                <span class="fx-row-doc">ICMS · ISS</span>
                                <span class="fx-status fx-status--aviso">phase-out 2029</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. Raio-X do Fornecedor — full width, dark, premium -->
            <div class="md:col-span-2 lg:col-span-3 group rounded-2xl p-6 lg:p-8 lg:px-10 hover:-translate-y-1 hover:shadow-xl transition-all duration-300 overflow-hidden relative" style="background-color: #0b1f3a;">
                <div class="flex flex-col lg:flex-row lg:items-center gap-8">
                    <div class="flex-1">
                        <span class="inline-block mb-4 px-3 py-1.5 rounded-full text-[11px] font-bold uppercase tracking-[0.12em]" style="background-color: #facc15; color: #0b1f3a;">Diferencial</span>
                        <h3 class="text-xl sm:text-2xl font-bold text-white mb-3">Raio-X do Fornecedor</h3>
                        <p class="text-sm leading-relaxed mb-5" style="color: rgba(255,255,255,0.6);">
                            Com um único SPED, o FiscalDock monta o dossiê completo de cada fornecedor: situação cadastral, certidões negativas, notas fiscais verificadas na SEFAZ, score de risco e alertas — tudo cruzado automaticamente.
                        </p>

                        <!-- Fluxo visual: 5 etapas inline -->
                        <div class="fx-flow flex flex-wrap items-center gap-2 sm:gap-3 mb-5">
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg" style="background-color: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                                <svg class="w-4 h-4 shrink-0" style="color: #facc15;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                <span class="text-[11px] font-semibold text-white/70 whitespace-nowrap">SPED</span>
                            </div>
                            <svg class="w-4 h-4 shrink-0 hidden sm:block" style="color: rgba(255,255,255,0.25);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg" style="background-color: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                                <svg class="w-4 h-4 shrink-0" style="color: #facc15;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <span class="text-[11px] font-semibold text-white/70 whitespace-nowrap">Receita</span>
                            </div>
                            <svg class="w-4 h-4 shrink-0 hidden sm:block" style="color: rgba(255,255,255,0.25);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg" style="background-color: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                                <svg class="w-4 h-4 shrink-0" style="color: #facc15;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <span class="text-[11px] font-semibold text-white/70 whitespace-nowrap">CNDs</span>
                            </div>
                            <svg class="w-4 h-4 shrink-0 hidden sm:block" style="color: rgba(255,255,255,0.25);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg" style="background-color: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                                <svg class="w-4 h-4 shrink-0" style="color: #facc15;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-[11px] font-semibold text-white/70 whitespace-nowrap">SEFAZ</span>
                            </div>
                            <svg class="w-4 h-4 shrink-0 hidden sm:block" style="color: rgba(255,255,255,0.25);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg" style="background-color: rgba(250,204,21,0.12); border: 1px solid rgba(250,204,21,0.25);">
                                <svg class="w-4 h-4 shrink-0" style="color: #facc15;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                <span class="text-[11px] font-bold text-white whitespace-nowrap">Dossiê</span>
                            </div>
                        </div>

                        <!-- Cruzamentos: nenhuma fonte sozinha conta a história -->
                        <div class="space-y-2.5 mb-4">
                            <div class="fxd-eq" style="--d: 0.15s;">
                                <span class="fxd-chip">0150 · inapto</span>
                                <span class="fxd-op">+</span>
                                <span class="fxd-chip">C100 · R$ 118k em notas</span>
                                <span class="fxd-op">→</span>
                                <span class="fxd-res fxd-res--alerta">risco de glosa</span>
                            </div>
                            <div class="fxd-eq" style="--d: 0.3s;">
                                <span class="fxd-chip">CND · vencida</span>
                                <span class="fxd-op">+</span>
                                <span class="fxd-chip">compras no período</span>
                                <span class="fxd-op">→</span>
                                <span class="fxd-res fxd-res--aviso">solidariedade tributária</span>
                            </div>
                            <div class="fxd-eq" style="--d: 0.45s;">
                                <span class="fxd-chip">SEFAZ · cancelada</span>
                                <span class="fxd-op">+</span>
                                <span class="fxd-chip">SPED · escriturada</span>
                                <span class="fxd-op">→</span>
                                <span class="fxd-res fxd-res--alerta">divergência fiscal</span>
                            </div>
                        </div>
                        <p class="text-xs mb-4" style="color: rgba(255,255,255,0.45);">
                            Nenhuma fonte sozinha conta a história — o risco aparece quando os dados se cruzam.
                        </p>
                        <div style="margin-top: auto; padding-top: 1.25rem; border-top: 1px solid rgba(255,255,255,0.1);">
                            <span class="text-[10px] font-medium uppercase tracking-wide whitespace-nowrap" style="background-color: rgba(250,204,21,0.15); color: #facc15; padding: 4px 10px; border-radius: 4px; display: inline-block; border: 1px solid rgba(250,204,21,0.25);">Compliance Fiscal</span>
                        </div>
                    </div>
                    <div class="shrink-0 flex flex-col items-center gap-4">
                        <!-- Mini-dossiê: o resultado dos cruzamentos -->
                        <div class="fxd-dossie hidden sm:block" aria-hidden="true">
                            <div class="fxd-dossie-head">
                                <span class="fxd-dossie-cnpj">45.678.912/0001-55</span>
                                <span class="fxd-score">
                                    <span class="fxd-score-num">58</span>
                                    <span class="fxd-score-label">score</span>
                                </span>
                            </div>
                            <div class="fx-row" style="--d: 0.2s;">
                                <span class="fx-row-doc">Situação cadastral</span>
                                <span class="radar-status radar-status--alerta">inapta</span>
                            </div>
                            <div class="fx-row" style="--d: 0.35s;">
                                <span class="fx-row-doc">Notas no SPED</span>
                                <span class="radar-status radar-status--aviso">R$ 118k</span>
                            </div>
                            <div class="fx-row" style="--d: 0.5s;">
                                <span class="fx-row-doc">CNDT · FGTS</span>
                                <span class="radar-status radar-status--ok">regular</span>
                            </div>
                            <div class="fxd-meter"><i style="--w: 58%;"></i></div>
                        </div>
                        <a href="/criar-conta" class="btn-cta">
                            Criar conta grátis
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                        <p class="text-[11px]" style="color: rgba(255,255,255,0.35);">Sem cartão de crédito</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- CTA da seção -->
        <div class="text-center mt-12 sm:mt-16">
            <a href="/criar-conta" class="btn-cta">
                Criar conta grátis
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
            <p class="mt-3 text-xs text-gray-400">Sem mensalidade — pague só pelas consultas que usar</p>
        </div>
    </div>
</section>



<!-- Reforma Tributária — cronograma interativo -->
<section id="reforma" class="bg-gray-50 pt-8 pb-16 sm:pt-10 sm:pb-20 lg:pt-12 lg:pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 sm:mb-12">
            <p class="landing-kicker">05 · Reforma tributária</p>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight mb-4">A transição já começou — e o seu SPED sabe disso</h2>
            <p class="text-sm sm:text-base text-gray-500 max-w-2xl mx-auto">
                O cronograma oficial da EC 132/2023, ano a ano. Clique e veja o que muda — e onde o FiscalDock entra.
            </p>
        </div>

        <div class="reforma-progress" aria-hidden="true"><i id="reforma-progress-bar" style="--p: 8%;"></i></div>

        <div class="reforma-anos" id="reforma-anos" role="tablist" aria-label="Anos da transição da reforma tributária">
            <button type="button" class="reforma-ano ativo" data-ano="2026" role="tab" aria-selected="true">2026</button>
            <button type="button" class="reforma-ano" data-ano="2027" role="tab" aria-selected="false">2027</button>
            <button type="button" class="reforma-ano" data-ano="2029" role="tab" aria-selected="false">2029</button>
            <button type="button" class="reforma-ano" data-ano="2030" role="tab" aria-selected="false">2030</button>
            <button type="button" class="reforma-ano" data-ano="2031" role="tab" aria-selected="false">2031</button>
            <button type="button" class="reforma-ano" data-ano="2032" role="tab" aria-selected="false">2032</button>
            <button type="button" class="reforma-ano" data-ano="2033" role="tab" aria-selected="false">2033</button>
        </div>

        <div class="reforma-painel" id="reforma-painel">
            <p class="reforma-fase" id="reforma-fase">Ano-teste · estamos aqui</p>
            <h3 class="reforma-titulo" id="reforma-titulo">CBS e IBS estreiam nos documentos fiscais</h3>
            <p class="reforma-texto" id="reforma-texto">Alíquotas de teste — 0,9% de CBS e 0,1% de IBS — passam a ser destacadas nas notas e na escrituração, compensáveis com PIS/COFINS. É o ano de validar cadastros, sistemas e escrituração sem impacto de caixa.</p>
            <div class="reforma-dock" id="reforma-dock"><strong>Onde o FiscalDock entra:</strong> os XMLs e SPEDs que você já importa trazem os novos campos — dá para conferir, cliente a cliente, quem está destacando certo.</div>
        </div>
    </div>
</section>

<!-- Métricas Banner -->
<section id="metricas" class="relative py-12 sm:py-14 overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0f172a 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.05), inset 0 -1px 0 rgba(255,255,255,0.05);">
    <div class="lp-blueprint" aria-hidden="true"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-8 sm:mb-10">
            <p class="landing-kicker landing-kicker--dark" style="margin-bottom: 0;">O produto em números</p>
        </div>
        <div class="metricas-band">
            <div class="metrica">
                <div class="metrica-num" data-count="9">9</div>
                <p class="metrica-label">fontes oficiais consultadas em uma consulta só</p>
            </div>
            <div class="metrica">
                <div class="metrica-num" data-count="7">7</div>
                <p class="metrica-label">alertas automáticos em cada SPED importado</p>
            </div>
            <div class="metrica">
                <div class="metrica-num" data-count="44">44</div>
                <p class="metrica-label">dígitos conferidos nota a nota, direto na SEFAZ</p>
            </div>
            <div class="metrica">
                <div class="metrica-num">1</div>
                <p class="metrica-label">painel para todos os clientes do escritório</p>
            </div>
        </div>
    </div>
</section>

<!-- Para Quem E -->
<section id="para-quem-e" class="bg-white pt-8 pb-20 sm:pt-10 sm:pb-24 lg:pt-12 lg:pb-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 sm:mb-14 lg:mb-16">
            <p class="landing-kicker">06 · Para quem é</p>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight mb-4">Feito para quem vive compliance fiscal</h2>
            <p class="text-sm sm:text-base text-gray-500 max-w-2xl mx-auto">
                Três perfis, três formas de usar o mesmo radar
            </p>
        </div>

        <div class="persona-grid">
            <!-- Escritórios Contábeis -->
            <div class="persona-col">
                <div class="flex items-center gap-3 mb-3">
                    <div class="fx-glyph fx-glyph--sm" style="background-color: #eef2f7; border: 1px solid #dce3ed; color: #1e4fa0;">×30</div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Escritórios contábeis</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Dezenas de clientes, centenas de participantes</p>
                    </div>
                </div>
                <p class="persona-quote">“Cada cliente que entra traz o próprio lote de fornecedores pra vigiar — e o fechamento não espera.”</p>
                <div class="persona-ficha">
                    <div class="persona-row">
                        <span class="persona-key">carteira</span><span class="persona-dots"></span>
                        <span class="persona-val">multi-cliente, SPED todo mês</span>
                    </div>
                    <div class="persona-row">
                        <span class="persona-key">rotina</span><span class="persona-dots"></span>
                        <span class="persona-val">importa o EFD, o resto é automático</span>
                    </div>
                    <div class="persona-row">
                        <span class="persona-key">radar</span><span class="persona-dots"></span>
                        <span class="persona-val">situação cadastral + CNDs por cliente</span>
                    </div>
                </div>
                <div class="persona-start">
                    <span>→</span>
                    <span>Comece por: <b style="color: #1e4fa0;">importar o SPED do seu maior cliente</b></span>
                </div>
            </div>

            <!-- Empresas -->
            <div class="persona-col">
                <div class="flex items-center gap-3 mb-3">
                    <div class="fx-glyph fx-glyph--sm" style="background-color: #fef8ee; border: 1px solid #f5e6c8; color: #b45309;">B2B</div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Empresas</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Compras recorrentes, fornecedores em rotação</p>
                    </div>
                </div>
                <p class="persona-quote">“Crédito glosado e solidariedade tributária estouram no nosso CNPJ — não no do fornecedor.”</p>
                <div class="persona-ficha">
                    <div class="persona-row">
                        <span class="persona-key">foco</span><span class="persona-dots"></span>
                        <span class="persona-val">fornecedores e transportadoras</span>
                    </div>
                    <div class="persona-row">
                        <span class="persona-key">rotina</span><span class="persona-dots"></span>
                        <span class="persona-val">monitoramento contínuo, sem planilha</span>
                    </div>
                    <div class="persona-row">
                        <span class="persona-key">visão</span><span class="persona-dots"></span>
                        <span class="persona-val">BI de compras, tributos e riscos</span>
                    </div>
                </div>
                <div class="persona-start">
                    <span>→</span>
                    <span>Comece por: <b style="color: #b45309;">monitorar seus 10 maiores fornecedores</b></span>
                </div>
            </div>

            <!-- Contadores Autônomos -->
            <div class="persona-col">
                <div class="flex items-center gap-3 mb-3">
                    <div class="fx-glyph fx-glyph--sm" style="background-color: #eefbf5; border: 1px solid #c6eed8; color: #047857;">×5</div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Contadores autônomos</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Carteira enxuta, atendimento de perto</p>
                    </div>
                </div>
                <p class="persona-quote">“Não pago mensalidade de ferramenta pra atender meia dúzia de clientes.”</p>
                <div class="persona-ficha">
                    <div class="persona-row">
                        <span class="persona-key">modelo</span><span class="persona-dots"></span>
                        <span class="persona-val">saldo pré-pago em reais, sem mensalidade</span>
                    </div>
                    <div class="persona-row">
                        <span class="persona-key">uso</span><span class="persona-dots"></span>
                        <span class="persona-val">consulta sob demanda, quando precisa</span>
                    </div>
                    <div class="persona-row">
                        <span class="persona-key">entrada</span><span class="persona-dots"></span>
                        <span class="persona-val">trial grátis pra testar de ponta a ponta</span>
                    </div>
                </div>
                <div class="persona-start">
                    <span>→</span>
                    <span>Comece por: <b style="color: #047857;">uma consulta de Validação num CNPJ real</b></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Na Prática Section -->
<section id="na-pratica" class="bg-gray-50 pt-8 pb-20 sm:pt-10 sm:pb-24 lg:pt-12 lg:pb-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-10 lg:mb-12">
            <p class="landing-kicker">07 · Na prática</p>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight mb-4">Três situações que todo contador conhece</h2>
            <p class="text-sm sm:text-base text-gray-500 max-w-2xl mx-auto">
                E como elas terminam quando os dados são cruzados antes — não depois
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <article class="scenario-card">
                <span class="scenario-tag">C100 → Alerta</span>
                <h3>Fechamento do mês</h3>
                <p class="scenario-hora">Todo dia 10, em qualquer escritório</p>
                <p class="scenario-texto">
                    Você importa o SPED do cliente e o radar aponta dois fornecedores que ficaram <strong>inaptos no meio do período</strong>. Os créditos daquelas notas seriam glosados na malha — agora dá tempo de tratar antes de transmitir a obrigação.
                </p>
                <div class="scenario-outcome">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Divergência resolvida antes da entrega
                </div>
            </article>

            <article class="scenario-card">
                <span class="scenario-tag">CND · CNDT · FGTS</span>
                <h3>Licitação para amanhã</h3>
                <p class="scenario-hora">O cliente liga às 17h47</p>
                <p class="scenario-texto">
                    Ele precisa das certidões para um edital que fecha amanhã. Em vez de abrir cinco sites do governo, <strong>uma consulta emite CND Federal, CNDT e regularidade do FGTS de uma vez</strong> — com o documento de cada certidão pronto para anexar.
                </p>
                <div class="scenario-outcome">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Dossiê completo no e-mail em minutos
                </div>
            </article>

            <article class="scenario-card">
                <span class="scenario-tag">Score de risco</span>
                <h3>Fornecedor novo na cadeia</h3>
                <p class="scenario-hora">Antes do primeiro pedido</p>
                <p class="scenario-texto">
                    Um CNPJ desconhecido entra na cadeia de compras do cliente. Antes da primeira nota, ele passa por <strong>situação cadastral e certidões (federais, estaduais e municipais)</strong>. Score baixo? Você recomenda garantias — ou recusar.
                </p>
                <div class="scenario-outcome">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Risco conhecido antes da primeira nota
                </div>
            </article>
        </div>
    </div>
</section>

<!-- Diferenciais — Sem vs Com -->
<section id="diferenciais" class="bg-white pt-8 pb-20 sm:pt-10 sm:pb-24 lg:pt-12 lg:pb-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 sm:mb-14 lg:mb-16">
            <p class="landing-kicker">08 · Diferenciais</p>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight mb-4">O que muda com o FiscalDock</h2>
            <p class="text-sm sm:text-base text-gray-500 max-w-2xl mx-auto">
                A rotina do escritório, linha a linha — o que sai em vermelho, o que entra em verde
            </p>
        </div>

        <div class="diff-panel">
            <div class="diff-head">
                <span>rotina_do_escritorio.diff</span>
                <span class="diff-counts"><span class="del">−5</span><span class="add">+5</span></span>
            </div>

            <div class="diff-group" style="--d: 0.05s;">
                <div class="diff-label">Consulta de CNPJ</div>
                <div class="diff-lines">
                    <div class="diff-line diff-line--del">
                        <span class="diff-sign">−</span>
                        <span class="diff-txt">Consultar cada CNPJ um a um no site da Receita</span>
                    </div>
                    <div class="diff-line diff-line--add">
                        <span class="diff-sign">+</span>
                        <span class="diff-txt">Importar o SPED e consultar todos os participantes de uma vez</span>
                    </div>
                </div>
            </div>

            <div class="diff-group" style="--d: 0.2s;">
                <div class="diff-label">Fornecedor irregular</div>
                <div class="diff-lines">
                    <div class="diff-line diff-line--del">
                        <span class="diff-sign">−</span>
                        <span class="diff-txt">Descobrir o fornecedor inapto só na auditoria</span>
                    </div>
                    <div class="diff-line diff-line--add">
                        <span class="diff-sign">+</span>
                        <span class="diff-txt">Alerta automático assim que a situação cadastral muda</span>
                    </div>
                </div>
            </div>

            <div class="diff-group" style="--d: 0.35s;">
                <div class="diff-label">Controle de CNDs</div>
                <div class="diff-lines">
                    <div class="diff-line diff-line--del">
                        <span class="diff-sign">−</span>
                        <span class="diff-txt">Planilha manual com vencimento de certidões</span>
                    </div>
                    <div class="diff-line diff-line--add">
                        <span class="diff-sign">+</span>
                        <span class="diff-txt">Vencimentos no painel e renovação automática</span>
                    </div>
                </div>
            </div>

            <div class="diff-group" style="--d: 0.5s;">
                <div class="diff-label">Conferência de notas</div>
                <div class="diff-lines">
                    <div class="diff-line diff-line--del">
                        <span class="diff-sign">−</span>
                        <span class="diff-txt">Revisar notas fiscais por amostragem, uma a uma</span>
                    </div>
                    <div class="diff-line diff-line--add">
                        <span class="diff-sign">+</span>
                        <span class="diff-txt">Verificação em lote na SEFAZ pela chave de acesso</span>
                    </div>
                </div>
            </div>

            <div class="diff-group" style="--d: 0.65s;">
                <div class="diff-label">Visão da operação</div>
                <div class="diff-lines">
                    <div class="diff-line diff-line--del">
                        <span class="diff-sign">−</span>
                        <span class="diff-txt">Números espalhados em planilhas e sistemas</span>
                    </div>
                    <div class="diff-line diff-line--add">
                        <span class="diff-sign">+</span>
                        <span class="diff-txt">BI Fiscal com faturamento, compras e tributos por cliente</span>
                    </div>
                </div>
            </div>

            <div class="diff-foot">
                <span>5 rotinas manuais substituídas</span>
                <span>0 planilhas novas</span>
            </div>
        </div>
    </div>
</section>

<!-- Seguranca e LGPD Banner -->
<section id="seguranca-lgpd" class="relative py-12 sm:py-14 overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0f172a 100%); box-shadow: inset 0 1px 0 rgba(255,255,255,0.05), inset 0 -1px 0 rgba(255,255,255,0.05);">
    <div class="lp-blueprint" aria-hidden="true"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-8 sm:mb-10">
            <p class="landing-kicker landing-kicker--dark" style="margin-bottom: 0;">Segurança e LGPD</p>
        </div>
        <div class="seg-band">
            <div class="seg-item">
                <span class="seg-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </span>
                <div>
                    <div class="seg-title">Controle de acesso</div>
                    <p class="seg-sub">Por perfil e por empresa — cada usuário vê só o que precisa</p>
                </div>
            </div>
            <div class="seg-item">
                <span class="seg-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </span>
                <div>
                    <div class="seg-title">Auditoria completa</div>
                    <p class="seg-sub">Registro de todas as ações da equipe</p>
                </div>
            </div>
            <div class="seg-item">
                <span class="seg-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </span>
                <div>
                    <div class="seg-title">Dados criptografados</div>
                    <p class="seg-sub">Em trânsito e em repouso, com segregação por cliente</p>
                </div>
            </div>
            <div class="seg-item">
                <span class="seg-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </span>
                <div>
                    <div class="seg-title">Conformidade LGPD</div>
                    <p class="seg-sub">Boas práticas de tratamento de dados</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Dúvidas Section -->
<section id="duvidas" class="bg-white pt-8 pb-20 sm:pt-10 sm:pb-24 lg:pt-12 lg:pb-28">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="faq-grid">

            <!-- Intro + ajuda (sticky no desktop) -->
            <div class="faq-intro">
                <p class="landing-kicker">09 · Perguntas frequentes</p>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight mb-4">Tire suas dúvidas antes de começar</h2>
                <p class="text-sm sm:text-base text-gray-500">
                    Respostas diretas para as perguntas mais comuns de contadores e escritórios contábeis.
                </p>

                <div class="faq-help">
                    <p class="faq-help-title">Não achou sua resposta?</p>
                    <a href="/duvidas" class="faq-help-link">
                        Ver todas as dúvidas
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="{{ route('agendar') }}" class="faq-help-link">
                        Falar com um especialista
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>

            <!-- Acordeão nativo (details/summary — expande sem JS) -->
            <div>
                <details class="faq-item" open>
                    <summary>
                        <span class="faq-num">01</span>
                        <span class="faq-q">Preciso cancelar meu sistema contábil para usar o FiscalDock?</span>
                        <span class="faq-icon" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        Não. O FiscalDock complementa Domínio, Alterdata, Contmatic e qualquer outro sistema. Você continua usando normalmente — basta exportar o SPED do seu sistema e importar no FiscalDock. Sem integração técnica, sem configuração.
                    </div>
                </details>

                <details class="faq-item">
                    <summary>
                        <span class="faq-num">02</span>
                        <span class="faq-q">Como funciona o saldo pré-pago?</span>
                        <span class="faq-icon" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        Você adiciona saldo em reais e usa conforme a necessidade. Cada tipo de consulta (CNPJ, CND, verificação de nota) tem preço fixo por produto. Sem mensalidade fixa e sem surpresas — pague só pelo que usar.
                    </div>
                </details>

                <details class="faq-item">
                    <summary>
                        <span class="faq-num">03</span>
                        <span class="faq-q">Quais fontes de dados o FiscalDock consulta?</span>
                        <span class="faq-icon" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        Receita Federal, SEFAZ (todos os estados), PGFN, TST (CNDT), Caixa (FGTS), prefeituras (CND Municipal) e SINTEGRA. Todos os dados vêm de fontes oficiais do governo, consultados em tempo real. Nenhuma informação é estimada ou inferida.
                    </div>
                </details>

                <details class="faq-item">
                    <summary>
                        <span class="faq-num">04</span>
                        <span class="faq-q">Meus dados e os de meus clientes ficam seguros?</span>
                        <span class="faq-icon" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        Sim. Controle de acesso por perfil e empresa, segregação completa entre clientes, criptografia em trânsito e repouso, e conformidade com LGPD. Cada usuário vê apenas os dados que precisa.
                    </div>
                </details>

                <details class="faq-item">
                    <summary>
                        <span class="faq-num">05</span>
                        <span class="faq-q">Posso testar antes de adicionar saldo?</span>
                        <span class="faq-icon" aria-hidden="true"></span>
                    </summary>
                    <div class="faq-a">
                        Sim. Ao criar a conta você recebe @brl(config('trial.saldo_reais')) de saldo grátis, válido por {{ config('trial.validade_dias') }} dias — sem cartão de crédito. Dá para importar SPEDs reais, ver os participantes extraídos, explorar os dashboards e rodar consultas de verdade. Quando o saldo acabar, você adiciona mais só se fizer sentido.
                    </div>
                </details>
            </div>
        </div>
    </div>
</section>

<!-- CTA Final -->
<section id="contato" class="relative py-16 sm:py-20 lg:py-24 overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0f172a 100%);">
    <div class="lp-blueprint" aria-hidden="true"></div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <p class="landing-kicker landing-kicker--dark">10 · Comece agora</p>
        <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">
            Da próxima vez, descubra antes do fisco
        </h2>
        <p class="text-base max-w-2xl mx-auto mb-8" style="color: rgba(255,255,255,0.85);">
            Crie a conta, importe um SPED real e veja o diagnóstico em minutos — com
            <strong class="text-white">@brl(config('trial.saldo_reais')) de saldo grátis</strong>
            para testar por {{ config('trial.validade_dias') }} dias.
        </p>

        <form action="{{ route('landing.lead.banner') }}" method="POST"
              class="mx-auto max-w-xl flex flex-col sm:flex-row gap-3">
            @csrf
            <label for="lead-email" class="sr-only">E-mail corporativo</label>
            <input id="lead-email" type="email" name="email" required
                   value="{{ old('email') }}"
                   placeholder="seu@empresa.com.br"
                   class="flex-1 px-4 py-3 rounded-lg text-sm text-gray-900 bg-white border border-white/20 focus:outline-none focus:ring-2 focus:ring-yellow-400 placeholder-gray-400" />
            <button type="submit" data-button="cta" class="btn-cta">
                <span class="whitespace-nowrap">Começar grátis</span>
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </button>
        </form>

        @error('email')
            <p class="mt-3 text-xs text-red-200">{{ $message }}</p>
        @enderror

        <div class="contato-chips mt-6">
            <span class="contato-chip">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Sem cartão de crédito
            </span>
            <span class="contato-chip">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Sem mensalidade
            </span>
            <span class="contato-chip">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                @brl(config('trial.saldo_reais')) grátis no cadastro
            </span>
        </div>

        <p class="mt-6 flex flex-col items-center justify-center gap-2 text-xs sm:flex-row" style="color: rgba(255,255,255,0.88);">
            <span>Prefere falar com alguém?</span>
            <a href="/agendar"
               data-link
               class="inline-flex items-center justify-center rounded-full border border-white/35 bg-white/12 px-3 py-1.5 text-sm font-semibold text-white shadow-sm backdrop-blur-sm transition hover:bg-white/20 hover:border-white/55 focus:outline-none focus:ring-2 focus:ring-white/70 focus:ring-offset-2 focus:ring-offset-slate-900">
                Falar com um especialista
            </a>
        </p>
    </div>
</section>



<!-- Scripts carregados no layout -->
