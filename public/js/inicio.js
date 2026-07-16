// Função específica para a página de início
// Usar propriedades em window para permitir múltiplas execuções do script sem erro de redeclaração
window._inicioInitialized = window._inicioInitialized || false;
window._countdownInterval = window._countdownInterval || null;
window._officialSourcesResizeHandler = window._officialSourcesResizeHandler || null;
window._officialSourcesRaf = window._officialSourcesRaf || null;
window._lpRevealObserver = window._lpRevealObserver || null;
window._lpCounterObserver = window._lpCounterObserver || null;
window._lpFeedObserver = window._lpFeedObserver || null;
window._lpFeedTimer = window._lpFeedTimer || null;
window._lpSpotHandler = window._lpSpotHandler || null;
window._lpReformaHandler = window._lpReformaHandler || null;

function initInicio() {
    // Limpar recursos anteriores se já foi inicializado
    if (window._inicioInitialized) {
        cleanupInicio();
    }
    
    // Countdown Timer - Atualizado para nova estrutura
    function initCountdown() {
        const daysElement = document.getElementById('days');
        const hoursElement = document.getElementById('hours');
        const minutesElement = document.getElementById('minutes');
        const secondsElement = document.getElementById('seconds');
        
        if (!daysElement || !hoursElement || !minutesElement || !secondsElement) {
            return; // Se não existir, não inicializa
        }

        const targetDate = new Date('2026-01-01T00:00:00').getTime();
        
        function updateCountdown() {
            const now = Date.now();
            const distance = targetDate - now;

            if (distance < 0) {
                daysElement.textContent = '0';
                hoursElement.textContent = '00';
                minutesElement.textContent = '00';
                if (secondsElement) secondsElement.textContent = '00';
                return;
            }

            const days = Math.floor(distance / 86400000);
            const hours = Math.floor((distance % 86400000) / 3600000);
            const minutes = Math.floor((distance % 3600000) / 60000);
            const seconds = Math.floor((distance % 60000) / 1000);

            // Dias sem zeros à esquerda quando forem 1 ou 2 dígitos (ex.: 7, 63). Para 100+, mantém natural (ex.: 124).
            daysElement.textContent = days.toString();
            hoursElement.textContent = hours.toString().padStart(2, '0');
            minutesElement.textContent = minutes.toString().padStart(2, '0');
            if (secondsElement) secondsElement.textContent = seconds.toString().padStart(2, '0');
        }

        updateCountdown();
        window._countdownInterval = setInterval(updateCountdown, 1000);
        
        // Registrar intervalo no sistema de recursos
        if (window._spaResources) {
            window._spaResources.intervals.push(window._countdownInterval);
        }
    }

    initCountdown();

    function initOfficialSourcesMarquee() {
        const marquee = document.querySelector('.official-sources-marquee');
        const track = marquee?.querySelector('.official-sources-track');
        const sourceGroup = track?.querySelector('[data-official-sources-group]');

        if (!marquee || !track || !sourceGroup) {
            return;
        }

        track.querySelectorAll('[data-official-sources-clone="true"]').forEach((clone) => clone.remove());
        track.style.removeProperty('--official-sources-cycle-width');
        track.style.removeProperty('--official-sources-duration');

        // Measure the rendered group width after layout so the loop includes the seam spacing.
        const cycleWidth = Math.ceil(sourceGroup.getBoundingClientRect().width);
        const marqueeWidth = Math.ceil(marquee.getBoundingClientRect().width);

        if (!cycleWidth || !marqueeWidth) {
            return;
        }

        const minTrackWidth = marqueeWidth + (cycleWidth * 2);
        let currentWidth = cycleWidth;
        let cloneIndex = 0;

        while (currentWidth < minTrackWidth) {
            const clone = sourceGroup.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            clone.dataset.officialSourcesClone = 'true';
            cloneIndex += 1;
            clone.dataset.officialSourcesCloneIndex = String(cloneIndex);
            track.appendChild(clone);
            currentWidth += cycleWidth;
        }

        const pixelsPerSecond = 72;
        const duration = cycleWidth / pixelsPerSecond;

        track.style.setProperty('--official-sources-cycle-width', `${cycleWidth}px`);
        track.style.setProperty('--official-sources-duration', `${duration}s`);
    }

    function scheduleOfficialSourcesMarquee() {
        if (window._officialSourcesRaf) {
            cancelAnimationFrame(window._officialSourcesRaf);
        }

        window._officialSourcesRaf = requestAnimationFrame(() => {
            initOfficialSourcesMarquee();
            window._officialSourcesRaf = null;
        });
    }

    scheduleOfficialSourcesMarquee();

    if (document.fonts && typeof document.fonts.ready?.then === 'function') {
        document.fonts.ready.then(() => {
            scheduleOfficialSourcesMarquee();
        });
    }

    window._officialSourcesResizeHandler = function() {
        scheduleOfficialSourcesMarquee();
    };

    window.addEventListener('resize', window._officialSourcesResizeHandler);

    // Inicializar FAQ se a função existir
    if (typeof initFaq === 'function') {
        initFaq();
    }

    // Contact Form
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        // Remover listener antigo se existir
        if (window._contactFormHandler) {
            contactForm.removeEventListener('submit', window._contactFormHandler);
        }
        
        window._contactFormHandler = function(e) {
            e.preventDefault();
            alert('Mensagem enviada com sucesso! Entraremos em contato em breve.');
            this.reset();
        };
        
        contactForm.addEventListener('submit', window._contactFormHandler);
    }
    
    // Scroll Indicator
    window.scrollToSolucoes = function() {
        const funcionalidades = document.getElementById('funcionalidades');
        if (funcionalidades) {
            funcionalidades.scrollIntoView({behavior: 'smooth'});
        }
    };

    const lpReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Reveal progressivo no scroll (auto-tag: sem JS a página fica 100% visível)
    function initLpReveal() {
        if (lpReducedMotion || typeof IntersectionObserver === 'undefined') return;

        const grupos = [
            '#antes-depois .gain-panel',
            '#como-funciona article',
            '#radar-vivo [data-radar-col]',
            '#funcionalidades .grid > div',
            '#reforma .reforma-anos, #reforma .reforma-painel',
            '#metricas .grid > div',
            '#para-quem-e .persona-col',
            '#diferenciais .diff-panel',
            '#na-pratica .scenario-card',
            '#duvidas .faq-item',
        ];

        const alvos = [];
        grupos.forEach((sel) => {
            document.querySelectorAll(sel).forEach((el, i) => {
                el.classList.add('lp-reveal');
                el.style.setProperty('--lp-reveal-delay', `${(i % 4) * 0.09}s`);
                alvos.push(el);
            });
        });

        // Cabeçalhos de seção (div que contém o kicker numerado)
        document.querySelectorAll('.landing-kicker').forEach((kicker) => {
            const header = kicker.parentElement;
            if (header && !header.classList.contains('lp-reveal')) {
                header.classList.add('lp-reveal');
                alvos.push(header);
            }
        });

        if (!alvos.length) return;

        document.body.classList.add('lp-reveal-armed');

        window._lpRevealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('lp-visible');
                    window._lpRevealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

        alvos.forEach((el) => window._lpRevealObserver.observe(el));
    }

    // Contadores da faixa de métricas
    function initLpCounters() {
        const nums = document.querySelectorAll('#metricas [data-count]');
        if (!nums.length || lpReducedMotion || typeof IntersectionObserver === 'undefined') return;

        window._lpCounterObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                window._lpCounterObserver.unobserve(el);

                const alvo = parseInt(el.dataset.count, 10) || 0;
                const duracao = 1100;
                const t0 = performance.now();

                function tick(t) {
                    const p = Math.min((t - t0) / duracao, 1);
                    const eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = String(Math.round(alvo * eased));
                    if (p < 1) requestAnimationFrame(tick);
                }

                el.textContent = '0';
                requestAnimationFrame(tick);
            });
        }, { threshold: 0.6 });

        nums.forEach((el) => window._lpCounterObserver.observe(el));
    }

    // Feed do radar: registros SPED sendo auditados "ao vivo"
    function initLpRadarFeed() {
        const list = document.getElementById('radar-feed-list');
        if (!list || lpReducedMotion) return;

        const okEl = document.getElementById('radar-feed-ok');
        const alertaEl = document.getElementById('radar-feed-alertas');

        const eventos = [
            { reg: '0150', desc: 'Participante 12.345.678/0001-90', status: 'situação ativa', tipo: 'ok' },
            { reg: 'C170', desc: 'Item 0042 · NCM 8471.30.19', status: 'catálogo ok', tipo: 'ok' },
            { reg: 'C100', desc: 'NF-e 3524…8834 · R$ 4.310,77', status: 'autorizada', tipo: 'ok' },
            { reg: '0150', desc: 'Participante 45.678.912/0001-55', status: 'baixado', tipo: 'alerta' },
            { reg: 'F600', desc: 'Retenção fonte · CSRF', status: 'confere', tipo: 'ok' },
            { reg: 'CRF FGTS', desc: 'Caixa Econômica Federal', status: 'regular emitido', tipo: 'ok' },
            { reg: 'C100', desc: 'NF-e 3524…1276 · R$ 22.940,00', status: 'divergência de valor', tipo: 'alerta' },
            { reg: 'A100', desc: 'NFS-e 000.412 · serviços', status: 'escriturada', tipo: 'ok' },
            { reg: 'M210', desc: 'COFINS apurada · R$ 41.087,90', status: 'confere', tipo: 'ok' },
            { reg: 'CND', desc: 'Estadual · SEFAZ-MS', status: 'vence em 4 dias', tipo: 'aviso' },
            { reg: 'D100', desc: 'CT-e 3524…9911 · frete', status: 'autorizado', tipo: 'ok' },
            { reg: 'FGTS', desc: 'Regularidade · Caixa', status: 'regular', tipo: 'ok' },
        ];

        const maxLinhas = list.children.length || 7;
        let idx = 0;
        let visivel = false;
        let ok = parseInt(okEl?.textContent || '0', 10);
        let alertas = parseInt(alertaEl?.textContent || '0', 10);

        function novaLinha() {
            if (!visivel || document.hidden) return;

            const ev = eventos[idx % eventos.length];
            idx += 1;

            const li = document.createElement('li');
            li.className = 'radar-row radar-row--enter';

            const reg = document.createElement('span');
            reg.className = 'radar-reg';
            reg.textContent = ev.reg;

            const desc = document.createElement('span');
            desc.className = 'radar-desc';
            desc.textContent = ev.desc;

            const status = document.createElement('span');
            status.className = `radar-status radar-status--${ev.tipo}`;
            status.textContent = ev.status;

            li.append(reg, desc, status);
            list.insertBefore(li, list.firstChild);

            while (list.children.length > maxLinhas) {
                list.removeChild(list.lastChild);
            }

            if (ev.tipo === 'alerta') {
                alertas += 1;
                if (alertaEl) alertaEl.textContent = String(alertas);
            } else {
                ok += 1;
                if (okEl) okEl.textContent = String(ok);
            }
        }

        if (typeof IntersectionObserver !== 'undefined') {
            window._lpFeedObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => { visivel = entry.isIntersecting; });
            }, { threshold: 0.2 });
            window._lpFeedObserver.observe(list);
        } else {
            visivel = true;
        }

        window._lpFeedTimer = setInterval(novaLinha, 1900);
        if (window._spaResources) {
            window._spaResources.intervals.push(window._lpFeedTimer);
        }
    }

    // Spotlight que segue o cursor nos cards
    function initLpSpotlight() {
        const seletores = '#funcionalidades .grid > div, #para-quem-e .persona-col, #na-pratica .scenario-card, #como-funciona article';
        document.querySelectorAll(seletores).forEach((el) => el.classList.add('lp-spot'));

        window._lpSpotHandler = function (e) {
            const card = e.target.closest ? e.target.closest('.lp-spot') : null;
            if (!card) return;
            const r = card.getBoundingClientRect();
            card.style.setProperty('--spot-x', `${e.clientX - r.left}px`);
            card.style.setProperty('--spot-y', `${e.clientY - r.top}px`);
        };

        document.addEventListener('pointermove', window._lpSpotHandler, { passive: true });
    }

    // Cronograma interativo da reforma tributária (EC 132/2023)
    function initLpReforma() {
        const anosWrap = document.getElementById('reforma-anos');
        const painel = document.getElementById('reforma-painel');
        if (!anosWrap || !painel) return;

        const faseEl = document.getElementById('reforma-fase');
        const tituloEl = document.getElementById('reforma-titulo');
        const textoEl = document.getElementById('reforma-texto');
        const dockEl = document.getElementById('reforma-dock');
        const progressEl = document.getElementById('reforma-progress-bar');

        const dockPrefixo = '<strong>Onde o FiscalDock entra:</strong> ';
        const fases = {
            2026: {
                fase: 'Ano-teste · estamos aqui',
                titulo: 'CBS e IBS estreiam nos documentos fiscais',
                texto: 'Alíquotas de teste — 0,9% de CBS e 0,1% de IBS — passam a ser destacadas nas notas e na escrituração, compensáveis com PIS/COFINS. É o ano de validar cadastros, sistemas e escrituração sem impacto de caixa.',
                dock: 'os XMLs e SPEDs que você já importa trazem os novos campos — dá para conferir, cliente a cliente, quem está destacando certo.',
                p: 8,
            },
            2027: {
                fase: 'Virada federal',
                titulo: 'CBS pra valer — PIS e COFINS extintos',
                texto: 'A CBS substitui de vez o PIS e a COFINS, o Imposto Seletivo entra em vigor e o IPI é zerado (exceto Zona Franca de Manaus). A apuração federal muda de natureza.',
                dock: 'com o histórico de apurações organizado, você compara a carga antiga × nova por cliente no BI Fiscal.',
                p: 24,
            },
            2029: {
                fase: 'Transição ICMS/ISS',
                titulo: 'ICMS e ISS começam a encolher: 90% do nível atual',
                texto: 'Primeiro degrau da transição estadual e municipal: as alíquotas de ICMS e ISS caem para 90% dos níveis atuais, e o IBS cresce na mesma proporção. Dois sistemas convivem na mesma nota.',
                dock: 'escrituração em dois regimes é onde nascem as divergências — o radar cruza os dois mundos no mesmo painel.',
                p: 44,
            },
            2030: {
                fase: 'Transição ICMS/ISS',
                titulo: 'Segundo degrau: 80% do nível atual',
                texto: 'ICMS e ISS caem para 80% dos níveis de referência. A cada degrau, a proporção entre sistema antigo e IBS muda — e a conferência por cliente fica mais sensível a erro.',
                dock: 'os alertas automáticos apontam nota a nota quem escriturou na proporção errada do ano.',
                p: 57,
            },
            2031: {
                fase: 'Transição ICMS/ISS',
                titulo: 'Terceiro degrau: 70% do nível atual',
                texto: 'A transição avança: 70% para ICMS e ISS. Benefícios fiscais estaduais perdem força gradualmente e o planejamento tributário dos clientes precisa ser revisto ano a ano.',
                dock: 'o histórico consolidado mostra a evolução da carga de cada cliente ao longo dos degraus.',
                p: 70,
            },
            2032: {
                fase: 'Transição ICMS/ISS',
                titulo: 'Último degrau: 60% do nível atual',
                texto: 'Ano final de convivência: ICMS e ISS a 60%, IBS quase pleno. É a última chance de sanear pendências e divergências acumuladas antes da virada definitiva.',
                dock: 'o dossiê por cliente resume o que ainda precisa ser regularizado antes de 2033.',
                p: 84,
            },
            2033: {
                fase: 'Sistema pleno',
                titulo: 'IBS integral — fim do ICMS e do ISS',
                texto: 'O novo sistema opera completo: ICMS e ISS deixam de existir e o IBS assume integralmente, junto da CBS federal. Sete anos de transição terminam aqui.',
                dock: 'quem atravessou a transição com dados estruturados chega em 2033 sem susto — a história inteira de cada cliente já está contada.',
                p: 100,
            },
        };

        window._lpReformaHandler = function (e) {
            const btn = e.target.closest ? e.target.closest('.reforma-ano') : null;
            if (!btn || !anosWrap.contains(btn)) return;

            const dados = fases[btn.dataset.ano];
            if (!dados) return;

            anosWrap.querySelectorAll('.reforma-ano').forEach((b) => {
                b.classList.toggle('ativo', b === btn);
                b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
            });

            if (faseEl) faseEl.textContent = dados.fase;
            if (tituloEl) tituloEl.textContent = dados.titulo;
            if (textoEl) textoEl.textContent = dados.texto;
            if (dockEl) dockEl.innerHTML = dockPrefixo + dados.dock;
            if (progressEl) progressEl.style.setProperty('--p', `${dados.p}%`);

            painel.classList.remove('trocando');
            void painel.offsetWidth;
            painel.classList.add('trocando');
        };

        anosWrap.addEventListener('click', window._lpReformaHandler);
    }

    initLpReveal();
    initLpCounters();
    initLpRadarFeed();
    initLpSpotlight();
    initLpReforma();

    window._inicioInitialized = true;
}

// Função de limpeza para recursos da página de início
function cleanupInicio() {
    // Limpar intervalos
    if (window._countdownInterval) {
        clearInterval(window._countdownInterval);
        window._countdownInterval = null;
    }

    if (window._officialSourcesRaf) {
        cancelAnimationFrame(window._officialSourcesRaf);
        window._officialSourcesRaf = null;
    }

    if (window._officialSourcesResizeHandler) {
        window.removeEventListener('resize', window._officialSourcesResizeHandler);
        window._officialSourcesResizeHandler = null;
    }

    // Remover handler do formulário
    if (window._contactFormHandler) {
        const contactForm = document.getElementById('contact-form');
        if (contactForm) {
            contactForm.removeEventListener('submit', window._contactFormHandler);
        }
        window._contactFormHandler = null;
    }

    document
        .querySelectorAll('.official-sources-track [data-official-sources-clone="true"]')
        .forEach((clone) => clone.remove());

    // Vida da LP: reveal, contadores, radar e spotlight
    if (window._lpRevealObserver) {
        window._lpRevealObserver.disconnect();
        window._lpRevealObserver = null;
    }
    if (window._lpCounterObserver) {
        window._lpCounterObserver.disconnect();
        window._lpCounterObserver = null;
    }
    if (window._lpFeedObserver) {
        window._lpFeedObserver.disconnect();
        window._lpFeedObserver = null;
    }
    if (window._lpFeedTimer) {
        clearInterval(window._lpFeedTimer);
        window._lpFeedTimer = null;
    }
    if (window._lpSpotHandler) {
        document.removeEventListener('pointermove', window._lpSpotHandler);
        window._lpSpotHandler = null;
    }
    if (window._lpReformaHandler) {
        const anosWrap = document.getElementById('reforma-anos');
        if (anosWrap) {
            anosWrap.removeEventListener('click', window._lpReformaHandler);
        }
        window._lpReformaHandler = null;
    }
    document.body.classList.remove('lp-reveal-armed');

    window._inicioInitialized = false;
}

// Registrar função de cleanup no sistema global
if (!window._cleanupFunctions) {
    window._cleanupFunctions = {};
}
window._cleanupFunctions.initInicio = cleanupInicio;

// Inicialização é feita pelo spa.js
