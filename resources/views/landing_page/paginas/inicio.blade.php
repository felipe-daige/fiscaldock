{{-- Homepage — FiscalDock --}}

{{-- ═══════════════════════════════════════════════════════════
     HERO SECTION
     ═══════════════════════════════════════════════════════════ --}}
<section class="hero-gradient relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 sm:pt-28 sm:pb-32 text-center">
        <p class="text-primary-200 text-sm sm:text-base font-medium tracking-wide uppercase mb-4">
            Plataforma de inteligência fiscal para contadores
        </p>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-bold text-white leading-tight max-w-4xl mx-auto">
            Pare de perder dinheiro com riscos fiscais invisíveis
        </h1>

        <p class="mt-6 text-base sm:text-lg text-primary-100 max-w-2xl mx-auto leading-relaxed">
            O FiscalDock cruza seus arquivos SPED com dados da Receita, SINTEGRA e CEIS
            para revelar riscos que custam multas — antes que o fisco encontre.
        </p>

        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/agendar" class="btn-cta text-base px-8 py-4">
                Testar Gratuitamente
            </a>
            <a href="#como-funciona" class="text-primary-200 hover:text-white transition font-medium text-sm sm:text-base">
                Veja como funciona &darr;
            </a>
        </div>
    </div>

    {{-- Stats bar --}}
    <div class="bg-white/10 backdrop-blur-sm border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-center">
                <div>
                    <span class="block text-2xl sm:text-3xl font-bold text-white">
                        R$&nbsp;<span class="stat-counter" data-target="2300000" data-prefix="" data-suffix="" data-format="compact">0</span>+
                    </span>
                    <span class="text-primary-200 text-xs sm:text-sm mt-1 block">em riscos detectados</span>
                </div>
                <div>
                    <span class="block text-2xl sm:text-3xl font-bold text-white">
                        <span class="stat-counter" data-target="450">0</span>+
                    </span>
                    <span class="text-primary-200 text-xs sm:text-sm mt-1 block">empresas monitoradas</span>
                </div>
                <div>
                    <span class="block text-2xl sm:text-3xl font-bold text-white">
                        <span class="stat-counter" data-target="12000" data-format="thousand">0</span>+
                    </span>
                    <span class="text-primary-200 text-xs sm:text-sm mt-1 block">notas analisadas</span>
                </div>
                <div>
                    <span class="block text-2xl sm:text-3xl font-bold text-white">
                        <span class="stat-counter" data-target="998" data-suffix="%" data-decimal="1">0</span>
                    </span>
                    <span class="text-primary-200 text-xs sm:text-sm mt-1 block">uptime</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     COMO FUNCIONA
     ═══════════════════════════════════════════════════════════ --}}
<section id="como-funciona" class="py-20 sm:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Como funciona</h2>
            <p class="mt-4 text-gray-600 text-lg">
                Em 3 passos simples, seu escritório ganha visibilidade total sobre riscos fiscais
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            {{-- Step 1 --}}
            <div class="bg-white rounded-xl border border-gray-200 border-t-4 border-t-primary-500 p-8 shadow-sm">
                <div class="w-12 h-12 rounded-lg bg-primary-50 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                    </svg>
                </div>
                <div class="text-xs font-semibold text-primary-500 uppercase tracking-wide mb-2">Passo 1</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Importe seu SPED</h3>
                <p class="text-gray-600 leading-relaxed">
                    Faça upload do arquivo EFD ICMS/IPI ou PIS/COFINS. O FiscalDock extrai participantes, notas e valores automaticamente.
                </p>
            </div>

            {{-- Step 2 --}}
            <div class="bg-white rounded-xl border border-gray-200 border-t-4 border-t-primary-500 p-8 shadow-sm">
                <div class="w-12 h-12 rounded-lg bg-primary-50 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                    </svg>
                </div>
                <div class="text-xs font-semibold text-primary-500 uppercase tracking-wide mb-2">Passo 2</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Análise automática</h3>
                <p class="text-gray-600 leading-relaxed">
                    Cruzamos dados com a Receita Federal, SINTEGRA e bases de compliance para identificar irregularidades e riscos.
                </p>
            </div>

            {{-- Step 3 --}}
            <div class="bg-white rounded-xl border border-gray-200 border-t-4 border-t-primary-500 p-8 shadow-sm">
                <div class="w-12 h-12 rounded-lg bg-primary-50 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                    </svg>
                </div>
                <div class="text-xs font-semibold text-primary-500 uppercase tracking-wide mb-2">Passo 3</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Relatório de riscos</h3>
                <p class="text-gray-600 leading-relaxed">
                    Receba alertas claros sobre fornecedores irregulares, notas com problemas e oportunidades de crédito tributário.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     SOLUÇÕES EM DESTAQUE
     ═══════════════════════════════════════════════════════════ --}}
<section class="py-20 sm:py-28 bg-surface-alt">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Soluções que protegem seu escritório</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Card 1: Importação SPED --}}
            <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Importação SPED</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Importe arquivos EFD e extraia participantes, notas e valores em segundos. Suporte a ICMS/IPI e PIS/COFINS.
                </p>
            </div>

            {{-- Card 2: Monitoramento de Participantes --}}
            <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Monitoramento de Participantes</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Acompanhe a situação cadastral de fornecedores e clientes. Alertas automáticos quando algo muda.
                </p>
            </div>

            {{-- Card 3: Consultas Tributárias --}}
            <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-violet-50 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Consultas Tributárias</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Consulte CNPJ, situação cadastral, regime tributário e Simples Nacional em lote ou individualmente.
                </p>
            </div>

            {{-- Card 4: Dashboard Analítico --}}
            <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Dashboard Analítico</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Visualize faturamento, compras, tributos e riscos em dashboards interativos com filtros avançados.
                </p>
            </div>

            {{-- Card 5: Alertas de Risco --}}
            <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Alertas de Risco</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Receba notificações sobre fornecedores com CNPJ irregular, IE suspensa ou listados no CEIS.
                </p>
            </div>

            {{-- Card 6: BI Fiscal --}}
            <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-cyan-50 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">BI Fiscal</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Análise inteligente por CFOP, participante e período. Identifique padrões e anomalias tributárias.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     POR QUE CONTADORES ESCOLHEM O FISCALDOCK
     ═══════════════════════════════════════════════════════════ --}}
<section class="py-20 sm:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Por que contadores escolhem o FiscalDock</h2>
        </div>

        <div class="grid sm:grid-cols-2 gap-8 max-w-4xl mx-auto">
            {{-- Benefit 1 --}}
            <div class="flex gap-5">
                <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Economia de tempo</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Automatize horas de trabalho manual. O que levava dias para conferir, o FiscalDock faz em minutos.
                    </p>
                </div>
            </div>

            {{-- Benefit 2 --}}
            <div class="flex gap-5">
                <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Proteção contra multas</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Identifique riscos antes da fiscalização. Fornecedores irregulares, notas frias e créditos indevidos aparecem no radar.
                    </p>
                </div>
            </div>

            {{-- Benefit 3 --}}
            <div class="flex gap-5">
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H6A2.25 2.25 0 0 0 3.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0 1 20.25 6v1.5m0 9V18A2.25 2.25 0 0 1 18 20.25h-1.5m-9 0H6A2.25 2.25 0 0 1 3.75 18v-1.5M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Visão 360° do cliente</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Centralize dados de SPED, participantes e notas fiscais em um único lugar. Chega de planilhas espalhadas.
                    </p>
                </div>
            </div>

            {{-- Benefit 4 --}}
            <div class="flex gap-5">
                <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Decisões com dados</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Dashboards e relatórios que transformam dados brutos em insights acionáveis para seu cliente.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     CTA FINAL
     ═══════════════════════════════════════════════════════════ --}}
<section class="hero-gradient py-20 sm:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-bold text-white max-w-2xl mx-auto">
            Comece a proteger seus clientes hoje
        </h2>
        <p class="mt-4 text-primary-100 text-lg max-w-xl mx-auto">
            Experimente o FiscalDock gratuitamente. Sem cartão de crédito, sem compromisso.
        </p>
        <div class="mt-10">
            <a href="/agendar" class="btn-cta text-base px-8 py-4">
                Criar Conta Gratuita
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     COUNTER ANIMATION (IntersectionObserver)
     ═══════════════════════════════════════════════════════════ --}}
<script>
(function () {
    var counters = document.querySelectorAll('.stat-counter');
    if (!counters.length) return;

    function formatNumber(n, format, decimal) {
        if (format === 'compact') {
            if (n >= 1000000) return (n / 1000000).toFixed(1).replace('.', ',') + 'M';
            if (n >= 1000) return (n / 1000).toFixed(0) + 'K';
            return n.toString();
        }
        if (format === 'thousand') {
            return n.toLocaleString('pt-BR');
        }
        if (decimal) {
            var d = parseInt(decimal, 10);
            return (n / Math.pow(10, d)).toFixed(d).replace('.', ',');
        }
        return n.toLocaleString('pt-BR');
    }

    function animateCounter(el) {
        if (el.dataset.animated) return;
        el.dataset.animated = '1';

        var target = parseInt(el.dataset.target, 10);
        var format = el.dataset.format || null;
        var decimal = el.dataset.decimal || null;
        var suffix = el.dataset.suffix || '';
        var duration = 1800;
        var start = null;

        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            var ease = 1 - Math.pow(1 - progress, 3);
            var current = Math.round(ease * target);
            el.textContent = formatNumber(current, format, decimal) + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    }

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        counters.forEach(function (el) { observer.observe(el); });
    } else {
        counters.forEach(animateCounter);
    }
})();
</script>
