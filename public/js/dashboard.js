// public/js/dashboard.js
// Cockpit do dashboard. Exposto como window.initDashboard() — o spa.js chama essa
// função a cada navegação para /app/dashboard (e na carga inicial), depois de garantir
// que apexcharts.min.js carregou. NÃO usar IIFE auto-executável: arquivos externos são
// deduplicados pelo spa e não re-rodam nas voltas via SPA.
(function () {
    function initDashboardCockpit() {
        const root = document.getElementById('dashboard-cockpit');
        if (!root) return;

        // Idempotência: se um init anterior não foi limpo, limpa antes de re-vincular.
        if (window._cleanupFunctions && window._cleanupFunctions.dashboardCockpit) {
            try { window._cleanupFunctions.dashboardCockpit(); } catch (_) {}
        }

        const $ = (sel) => root.querySelector(sel);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const fmtN = (v) => Number(v || 0).toLocaleString('pt-BR');
        const fmtR = (v) => 'R$ ' + Number(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const fmtCompact = (v) => {
            v = Number(v || 0);
            if (Math.abs(v) >= 1e6) return 'R$ ' + (v / 1e6).toLocaleString('pt-BR', { maximumFractionDigits: 1 }) + 'M';
            if (Math.abs(v) >= 1e3) return 'R$ ' + (v / 1e3).toLocaleString('pt-BR', { maximumFractionDigits: 1 }) + 'k';
            return fmtR(v);
        };
        const fmtDocumento = (v) => {
            const digits = String(v ?? '').replace(/\D/g, '');
            if (!digits) return 'Documento não informado';
            if (digits.length <= 11) {
                return digits.padStart(11, '0').replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
            }
            return digits.padStart(14, '0').replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
        };
        const fmtData = (v) => v
            ? new Date(`${v}T12:00:00`).toLocaleDateString('pt-BR')
            : '';
        const fmtMesAno = (v) => v
            ? new Date(`${v}T12:00:00`).toLocaleDateString('pt-BR', { month: '2-digit', year: 'numeric' })
            : '';
        const escapeHtml = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
        const escapeAttr = escapeHtml;
        const parseJson = (id, fallback) => {
            try {
                const el = document.getElementById(id);
                return el ? JSON.parse(el.textContent || 'null') : fallback;
            } catch (_) {
                return fallback;
            }
        };

        const charts = { tendencia: null, risco: null };
        const atalhosCatalogo = parseJson('dashboard-atalhos', {});
        let metrica = $('[data-control="metrica"]')?.value || 'valor';
        let estado = parseJson('cockpit-initial', null);
        let prefsTimer = null;
        let statusTimer = null;
        let apexTimer = null;
        let apexTentativas = 0;
        let pendingController = null;

        const apex = () => typeof ApexCharts !== 'undefined';
        const chartBase = {
            fontFamily: 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            toolbar: { show: false },
            animations: { enabled: true, speed: 260 },
            zoom: { enabled: false },
        };
        const cardVisivel = (chave) => {
            const card = root.querySelector(`[data-card="${chave}"]`);
            return !!card && !card.classList.contains('hidden');
        };
        const destruirChart = (chave) => {
            if (!charts[chave]) return;
            try { charts[chave].destroy(); } catch (_) {}
            charts[chave] = null;
        };

        function setStatus(texto, tipo) {
            const el = $('[data-dashboard-status]');
            if (!el) return;
            clearTimeout(statusTimer);
            if (!texto) {
                el.classList.add('hidden');
                el.textContent = '';
                return;
            }
            el.textContent = texto;
            el.className = 'px-3 py-2 bg-white border border-gray-300 rounded text-xs text-gray-600';
            el.style.color = tipo === 'error' ? '#b91c1c' : '';
            if (tipo === 'success') {
                statusTimer = setTimeout(() => setStatus(''), 1400);
            }
        }

        function renderEscopo() {
            const cliente = $('[data-control="cliente"]');
            const periodo = $('[data-control="periodo"]');
            const clienteLabel = cliente?.selectedOptions?.[0]?.textContent?.trim() || 'Todos os clientes';
            const periodoValor = periodo?.value || estado?.meta?.periodo || '6';
            const referencia = estado?.meta?.referencia || '';
            const sufixo = estado?.meta?.ancorado && referencia ? ` até ${fmtMesAno(referencia)}` : '';
            const texto = `${clienteLabel} — ${periodoValor} meses${sufixo}`;
            const scope = $('[data-dashboard-scope]');
            const meta = $('[data-dashboard-meta]');
            if (scope) scope.textContent = texto;
            if (meta) meta.textContent = `${periodoValor} meses`;
        }

        function renderDefasagem(meta) {
            const banner = $('[data-dashboard-stale]');
            const copy = $('[data-dashboard-stale-copy]');
            if (!banner) return;

            const desatualizado = Boolean(meta?.dados_desatualizados && meta?.referencia);
            banner.classList.toggle('hidden', !desatualizado);
            if (!desatualizado || !copy) return;

            const data = escapeHtml(fmtData(meta.referencia));
            if (meta.ancorado) {
                const periodo = escapeHtml(meta.periodo || 6);
                copy.innerHTML = `A movimentação mais recente é de <strong>${data}</strong>. O recorte de <strong>${periodo} meses</strong> foi ancorado nessa data, não nos meses atuais.`;
                return;
            }

            copy.innerHTML = `A movimentação mais recente é de <strong>${data}</strong>. O período selecionado ainda inclui essa base, mas não há movimentação fiscal mais recente.`;
        }

        function renderTendencia(t) {
            const el = $('#chartTendencia');
            if (!cardVisivel('tendencia')) return;
            if (!el || !apex() || !t) return;
            const saida = metrica === 'qtd' ? t.saida_qtd : t.saida_valor;
            const entrada = metrica === 'qtd' ? t.entrada_qtd : t.entrada_valor;
            const opts = {
                chart: { ...chartBase, type: 'area', height: 268 },
                series: [{ name: 'Saída', data: saida || [] }, { name: 'Entrada', data: entrada || [] }],
                xaxis: { categories: t.meses || [] },
                colors: ['#374151', '#047857'],
                dataLabels: { enabled: false },
                fill: { type: 'solid', opacity: 0.08 },
                grid: { borderColor: '#e5e7eb', strokeDashArray: 3 },
                stroke: { curve: 'smooth', width: 2 },
                legend: { position: 'top', fontSize: '11px' },
                tooltip: { y: { formatter: (v) => metrica === 'qtd' ? fmtN(v) : fmtR(v) } },
                yaxis: { labels: { formatter: (v) => metrica === 'qtd' ? fmtN(v) : fmtCompact(v) } },
            };
            if (charts.tendencia) {
                charts.tendencia.updateOptions(opts, false, true);
            } else {
                charts.tendencia = new ApexCharts(el, opts);
                charts.tendencia.render();
            }
        }

        function renderRisco(dist) {
            const el = $('#chartRisco'); const vazio = $('#risco-vazio');
            if (!cardVisivel('risco')) return;
            if (!el || !apex()) return;
            const has = dist && dist.length;
            if (vazio) vazio.classList.toggle('hidden', !!has);
            el.classList.toggle('hidden', !has);
            if (!has) { destruirChart('risco'); return; }
            const opts = {
                chart: { ...chartBase, type: 'donut', height: 244 },
                series: dist.map((d) => d.valor),
                labels: dist.map((d) => d.label),
                colors: dist.map((d) => d.hex),
                legend: { position: 'bottom', fontSize: '11px' },
                dataLabels: { enabled: true },
                stroke: { width: 2, colors: ['#ffffff'] },
            };
            if (charts.risco) {
                charts.risco.updateOptions(opts, false, true);
            } else {
                charts.risco = new ApexCharts(el, opts);
                charts.risco.render();
            }
        }

        function renderFornecedores(rows) {
            const el = $('#chartFornecedores'); const vazio = $('#fornecedores-vazio');
            if (!cardVisivel('fornecedores')) return;
            if (!el) return;
            const itens = Array.isArray(rows) ? rows.slice(0, 5) : [];
            const has = itens.length > 0;
            if (vazio) vazio.classList.toggle('hidden', !!has);
            el.classList.toggle('hidden', !has);
            if (!has) { el.innerHTML = ''; return; }

            const maiorTotal = Math.max(...itens.map((item) => Number(item.total || 0)), 1);
            el.innerHTML = itens.map((item, index) => {
                const nome = item.razao_social || fmtDocumento(item.cnpj);
                const total = Number(item.total || 0);
                const notas = Number(item.qtd_notas || 0);
                const participacao = Math.max(4, Math.round((total / maiorTotal) * 100));
                const notasLabel = notas === 1 ? '1 nota' : `${fmtN(notas)} notas`;

                return `
                    <div class="py-2 first:pt-0 last:pb-0 border-b border-gray-100 last:border-b-0" data-fornecedor-row>
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded text-[10px] font-bold text-gray-600" style="background-color: #f3f4f6">${index + 1}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="min-w-0 text-xs font-semibold text-gray-800 leading-4 truncate" title="${escapeAttr(nome)}">${escapeHtml(nome)}</p>
                                    <strong class="flex-shrink-0 text-xs font-semibold text-gray-900 leading-4" title="${escapeAttr(fmtR(total))}">${escapeHtml(fmtCompact(total))}</strong>
                                </div>
                                <p class="text-[10px] text-gray-400 leading-3 truncate">${escapeHtml(fmtDocumento(item.cnpj))} · ${escapeHtml(notasLabel)}</p>
                                <div class="mt-1 h-1 rounded-full overflow-hidden" style="background-color: #e5e7eb" role="progressbar" aria-label="Participação relativa de ${escapeAttr(nome)}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="${participacao}">
                                    <span class="block h-full rounded-full" style="width: ${participacao}%; background-color: #374151"></span>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }).join('');
        }

        function renderKpis(kpis) {
            if (!kpis) return;
            const set = (kpi, valor, sub) => {
                const card = root.querySelector(`[data-kpi="${kpi}"]`);
                if (!card) return;
                const elValor = card.querySelector('[data-kpi-valor]');
                const elSub = card.querySelector('[data-kpi-sub]');
                if (elValor) elValor.textContent = valor;
                if (elSub && sub !== null) elSub.textContent = sub;
            };
            set('volume', fmtN(kpis.volume?.notas), fmtR(kpis.volume?.valor));
            set('saude', fmtN(kpis.saude?.total), Number(kpis.saude?.total || 0) > 0 ? 'pontos de atenção' : 'tudo em dia');
            // saldo/usados_mes são exibidos em R$.
            set('saldo', fmtR((kpis.saldo?.disponivel || 0) * 0.20), fmtR((kpis.saldo?.usado_mes || 0) * 0.20) + ' usados este mês');
        }

        // Espelha resources/views/autenticado/dashboard/partials/triagem.blade.php — manter os dois em sincronia.
        function renderTriagem(triagem) {
            const lista = $('#triagem-lista');
            if (!lista) return;
            const itens = Array.isArray(triagem) ? triagem : [];
            const total = itens.length;
            const pendentes = itens.filter((i) => i.count > 0).length;

            if (!total || pendentes === 0) {
                lista.innerHTML = `
                    <div class="flex-1 flex flex-col items-center justify-center text-center px-4 py-10">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full mb-3" style="background-color: #dcfce7">
                            <svg class="w-5 h-5" style="color: #16a34a" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </span>
                        <p class="text-sm font-semibold text-gray-700">Carteira em dia</p>
                        <p class="text-xs text-gray-500 mt-0.5">Nenhuma pendência aberta</p>
                    </div>`;
                return;
            }

            const rows = itens.map((i) => {
                if (i.count > 0) {
                    return `
                        <a href="${escapeAttr(i.url)}" data-link class="group relative flex items-center justify-between gap-3 pl-5 pr-4 py-3 hover:bg-gray-50 transition-colors">
                            <span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r" style="background-color: ${escapeAttr(i.hex)}"></span>
                            <span class="min-w-0 text-sm text-gray-800 truncate">${escapeHtml(i.label)}</span>
                            <span class="flex-shrink-0 flex items-center gap-1.5">
                                <span class="inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 rounded-full text-[11px] font-bold text-white" style="background-color: ${escapeAttr(i.hex)}">${fmtN(i.count)}</span>
                                <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        </a>`;
                }
                return `
                    <a href="${escapeAttr(i.url)}" data-link class="flex items-center justify-between gap-3 pl-5 pr-4 py-3 hover:bg-gray-50/50 transition-colors">
                        <span class="min-w-0 flex items-center gap-2 text-sm text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background-color: #d1d5db"></span>
                            <span class="truncate">${escapeHtml(i.label)}</span>
                        </span>
                        <span class="text-sm font-semibold text-gray-300 flex-shrink-0">${fmtN(i.count)}</span>
                    </a>`;
            }).join('');

            const verbo = pendentes === 1 ? 'pede' : 'pedem';
            lista.innerHTML = `
                <div class="divide-y divide-gray-100">${rows}</div>
                <div class="mt-auto border-t border-gray-200 px-4 py-2.5 flex items-center justify-between gap-2">
                    <span class="text-[11px] text-gray-500">${pendentes} de ${total} ${verbo} ação</span>
                    <a href="/app/alertas" data-link class="text-[11px] font-semibold text-gray-600 hover:text-gray-900 inline-flex items-center gap-1">
                        Central de alertas
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>`;
        }

        function renderAtalhos() {
            const grid = $('#atalhos-grid');
            if (!grid) return;
            const selecionados = Array.from(root.querySelectorAll('[data-pref-atalho]'))
                .filter((cb) => cb.checked)
                .map((cb) => cb.dataset.prefAtalho)
                .filter((slug) => atalhosCatalogo[slug]);

            if (!selecionados.length) {
                grid.innerHTML = '<div class="px-4 py-8 text-center text-sm text-gray-500 border border-gray-200 rounded" style="grid-column: 1 / -1">Nenhum atalho fixado</div>';
                return;
            }

            grid.innerHTML = selecionados.map((slug) => {
                const item = atalhosCatalogo[slug];
                return `<a href="${escapeAttr(item.url)}" data-link class="inline-flex items-center justify-center min-h-[40px] text-center px-3 py-2 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors">${escapeHtml(item.label)}</a>`;
            }).join('');
        }

        function renderCharts() {
            if (!estado) return;
            renderFornecedores(estado.top_fornecedores);
            if (!apex()) {
                if (apexTentativas < 20) {
                    clearTimeout(apexTimer);
                    apexTimer = setTimeout(renderCharts, 150);
                    apexTentativas += 1;
                }
                return;
            }
            apexTentativas = 0;
            renderTendencia(estado.tendencia);
            renderRisco(estado.risco_distribuicao);
        }

        function render(dados) {
            if (!dados) return;
            estado = dados;
            renderKpis(dados.kpis);
            renderTriagem(dados.triagem);
            renderEscopo();
            renderDefasagem(dados.meta);
            requestAnimationFrame(renderCharts);
        }

        async function pivotar() {
            const cliente = $('[data-control="cliente"]')?.value || '';
            const periodo = $('[data-control="periodo"]')?.value || '6';
            if (pendingController) pendingController.abort();
            const controller = new AbortController();
            pendingController = controller;
            setStatus('Atualizando...');
            try {
                const params = new URLSearchParams({ cliente, periodo });
                const resp = await fetch(`/app/dashboard/dados?${params.toString()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });
                if (!resp.ok) throw new Error('Falha ao carregar dashboard');
                render(await resp.json());
                setStatus('Atualizado', 'success');
            } catch (error) {
                if (error && error.name === 'AbortError') return;
                setStatus('Não foi possível atualizar', 'error');
            } finally {
                if (pendingController === controller) pendingController = null;
            }
        }

        function coletarPrefs() {
            const cards = {};
            root.querySelectorAll('[data-pref-card]').forEach((cb) => { cards[cb.dataset.prefCard] = { visivel: cb.checked }; });
            const atalhos_fixos = Array.from(root.querySelectorAll('[data-pref-atalho]')).filter((cb) => cb.checked).map((cb) => cb.dataset.prefAtalho);
            return { cards, atalhos_fixos, atalhos_ordem: Object.keys(atalhosCatalogo) };
        }
        function aplicarVisibilidade() {
            root.querySelectorAll('[data-pref-card]').forEach((cb) => {
                const card = root.querySelector(`[data-card="${cb.dataset.prefCard}"]`);
                if (card) card.classList.toggle('hidden', !cb.checked);
                if (!cb.checked && charts[cb.dataset.prefCard]) destruirChart(cb.dataset.prefCard);
            });
            requestAnimationFrame(renderCharts);
        }
        async function salvarPrefs() {
            const payload = coletarPrefs();
            try {
                const resp = await fetch('/app/dashboard/prefs', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(payload),
                });
                if (!resp.ok) throw new Error('Falha ao salvar preferências');
                setStatus('Preferências salvas', 'success');
            } catch (_) {
                setStatus('Não foi possível salvar', 'error');
            }
        }

        const onChange = (e) => {
            if (e.target.matches('[data-control="cliente"], [data-control="periodo"]')) { renderEscopo(); pivotar(); }
            if (e.target.matches('[data-control="metrica"]')) { metrica = e.target.value; renderTendencia(estado && estado.tendencia); }
            if (e.target.matches('[data-pref-card]')) aplicarVisibilidade();
            if (e.target.matches('[data-pref-atalho]')) renderAtalhos();
            if (e.target.matches('[data-pref-card], [data-pref-atalho]')) {
                clearTimeout(prefsTimer);
                prefsTimer = setTimeout(salvarPrefs, 600);
            }
        };
        const onClick = (e) => {
            const toggle = e.target.closest('[data-personalizar-toggle]');
            if (!toggle) return;
            const panel = root.querySelector('[data-personalizar-panel]');
            if (!panel) return;
            const aberto = panel.classList.toggle('hidden') === false;
            toggle.setAttribute('aria-expanded', aberto ? 'true' : 'false');
        };
        root.addEventListener('change', onChange);
        root.addEventListener('click', onClick);

        // Primeira pintura usa o estado embutido (sem refetch).
        renderEscopo();
        aplicarVisibilidade();
        renderAtalhos();
        render(estado);

        window._cleanupFunctions = window._cleanupFunctions || {};
        window._cleanupFunctions.dashboardCockpit = function () {
            root.removeEventListener('change', onChange);
            root.removeEventListener('click', onClick);
            ['tendencia', 'risco'].forEach(destruirChart);
            clearTimeout(prefsTimer);
            clearTimeout(statusTimer);
            clearTimeout(apexTimer);
            if (pendingController) pendingController.abort();
        };
    }

    window.initDashboard = initDashboardCockpit;
})();
