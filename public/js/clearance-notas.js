(function () {
    const root = document.getElementById('validacao-notas-container');
    if (!root) return;

    const idsUrl = root.dataset.idsUrl;
    const validarUrl = root.dataset.validarUrl;
    const temMaisPagina = root.dataset.temMaisPagina === '1';
    const saldoAtual = parseInt(root.dataset.saldoAtual || '0', 10);
    const custos = {
        basico: parseInt(root.dataset.custoBasico || '10', 10),
        full: parseInt(root.dataset.custoFull || '20', 10),
    };

    const TIER_STORAGE_KEY = 'clearance:tier';
    const TIERS_VALIDOS = ['basico', 'full'];
    const TIER_LABEL = { basico: 'Básico', full: 'Full' };

    const form = document.getElementById('validacao-filtros-form');
    const chkMaster = document.getElementById('chk-master');
    const chkNotas = () => Array.from(document.querySelectorAll('.chk-nota'));
    const btnValidar = document.getElementById('btn-validar');
    const btnSelTodas = document.getElementById('btn-selecionar-todas');
    const selLabel = document.getElementById('selecao-label');
    const planosContainer = document.getElementById('clearance-planos');
    const saldoAposLabel = document.getElementById('saldo-apos-label');
    const cards = {
        basico: document.getElementById('plan-card-basico'),
        full: document.getElementById('plan-card-full'),
    };
    const errorRegion = document.getElementById('clearance-notas-error');

    const modalConfirm = document.getElementById('modal-confirmar-validacao');
    const modalSucesso = document.getElementById('modal-sucesso-validacao');
    const modalConfirmQtd = document.getElementById('modal-confirm-qtd');
    const modalConfirmCusto = document.getElementById('modal-confirm-custo');
    const modalConfirmSaldoApos = document.getElementById('modal-confirm-saldo-apos');
    const modalConfirmTierChip = document.getElementById('modal-confirm-tier-chip');
    const modalConfirmTierLabel = document.getElementById('modal-confirm-tier-label');
    const modalConfirmOk = document.getElementById('modal-confirm-ok');
    const modalConfirmCancelar = document.getElementById('modal-confirm-cancelar');
    const modalSucessoOk = document.getElementById('modal-sucesso-ok');
    const modalSucessoCreditos = document.getElementById('modal-sucesso-creditos');

    function abrirModal(el) { if (el) el.classList.remove('hidden'); }
    function fecharModal(el) { if (el) el.classList.add('hidden'); }

    const progressoBloco = document.getElementById('clearance-progresso');
    const progressoBar = document.getElementById('clearance-progresso-bar');
    const progressoPercent = document.getElementById('clearance-progresso-percent');
    const progressoEtapa = document.getElementById('clearance-progresso-etapa');

    const selecionados = new Set();
    const origens = new Map();
    const labelSelTodasOriginal = btnSelTodas ? btnSelTodas.textContent : '';
    let tierSelecionado = tierInicial();
    let currentEventSource = null;
    let currentSseTimeout = null;

    function gerarTabId() {
        return 'clearance-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
    }

    function mostrarProgresso(percent, etapa) {
        if (!progressoBloco) return;
        progressoBloco.classList.remove('hidden');
        const value = Math.max(0, Math.min(100, Number(percent) || 0));
        if (progressoBar) progressoBar.style.width = value + '%';
        if (progressoPercent) progressoPercent.textContent = value + '%';
        if (etapa && progressoEtapa) progressoEtapa.textContent = etapa;
    }

    function esconderProgresso() {
        if (progressoBloco) progressoBloco.classList.add('hidden');
    }

    function fecharSse() {
        if (currentEventSource) {
            try { currentEventSource.close(); } catch (e) {}
            currentEventSource = null;
        }
        if (currentSseTimeout) {
            clearTimeout(currentSseTimeout);
            currentSseTimeout = null;
        }
    }

    function abrirSseProgresso(tabId) {
        fecharSse();
        const url = '/app/consulta/progresso/stream?tab_id=' + encodeURIComponent(tabId);
        const es = new EventSource(url, { withCredentials: true });
        currentEventSource = es;

        currentSseTimeout = setTimeout(() => {
            fecharSse();
            esconderProgresso();
            showError('Clearance externo não retornou em 120 segundos. Recarregue a página mais tarde.', 'clearance-sse-timeout');
        }, 120000);

        es.onmessage = (event) => {
            let data;
            try { data = JSON.parse(event.data); } catch (_) { return; }
            const status = data.status || 'processando';
            const progresso = Number(data.progresso || 0);
            const etapa = data.etapa_label || data.mensagem || 'Processando...';

            if (status === 'processando') {
                mostrarProgresso(progresso, etapa);
                return;
            }
            if (status === 'concluido') {
                mostrarProgresso(100, 'Clearance concluído, atualizando…');
                fecharSse();
                window.location.reload();
                return;
            }
            if (status === 'erro') {
                fecharSse();
                esconderProgresso();
                const msg = data.error_message || data.mensagem || 'Clearance externo falhou.';
                showError(msg, 'clearance-sse-erro');
                return;
            }
            if (status === 'timeout') {
                fecharSse();
                esconderProgresso();
                showError(data.mensagem || 'Clearance externo excedeu o limite.', 'clearance-sse-timeout');
            }
        };

        es.onerror = () => {
            // Se o SSE cair mas ainda estiver processando, o timeout global cobre
        };
    }

    function tierInicial() {
        try {
            const saved = localStorage.getItem(TIER_STORAGE_KEY);
            if (saved && TIERS_VALIDOS.includes(saved)) return saved;
        } catch (e) {}
        return 'basico';
    }

    function persistirTier(tier) {
        try { localStorage.setItem(TIER_STORAGE_KEY, tier); } catch (e) {}
    }

    function getCsrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function queryFiltros() {
        const fd = new FormData(form);
        const params = new URLSearchParams();
        for (const [k, v] of fd.entries()) if (v) params.append(k, v);
        return params.toString();
    }

    function origemDoCheckbox(chk) {
        const tr = chk.closest('tr');
        return tr && tr.dataset ? tr.dataset.origem : 'xml';
    }

    function registrarOrigens() {
        chkNotas().forEach((chk) => {
            const id = parseInt(chk.value, 10);
            origens.set(id, origemDoCheckbox(chk));
        });
    }

    function selecionadosArray() {
        return Array.from(selecionados);
    }

    function origensSelecionadas() {
        const map = {};
        selecionados.forEach((id) => {
            map[id] = origens.get(id) || 'xml';
        });
        return map;
    }

    function selecionarTier(tier) {
        if (!TIERS_VALIDOS.includes(tier)) return;
        tierSelecionado = tier;
        persistirTier(tier);

        TIERS_VALIDOS.forEach((t) => {
            const card = cards[t];
            if (!card) return;
            const ativo = t === tier;
            card.setAttribute('aria-checked', ativo ? 'true' : 'false');
            card.style.borderColor = ativo ? '#1f2937' : '#e5e7eb';
            const chip = card.querySelector('.plan-chip');
            if (chip) chip.classList.toggle('hidden', !ativo);
        });

        atualizarCusto();
    }

    function atualizarCusto() {
        const n = selecionados.size;

        document.querySelectorAll('.plan-total').forEach((el) => {
            const tier = el.dataset.tier;
            el.textContent = String(n * (custos[tier] || 0));
        });

        const total = n * (custos[tierSelecionado] || 0);
        const saldoApos = saldoAtual - total;
        const insuficiente = saldoApos < 0;

        if (saldoAposLabel) {
            saldoAposLabel.textContent = `${saldoApos} créditos`;
            if (insuficiente) {
                saldoAposLabel.style.backgroundColor = '#fee2e2';
                saldoAposLabel.style.color = '#b91c1c';
            } else {
                saldoAposLabel.style.backgroundColor = '#e5e7eb';
                saldoAposLabel.style.color = '#374151';
            }
        }

        if (btnValidar) {
            if (n === 0) {
                btnValidar.textContent = 'Validar';
                btnValidar.disabled = true;
            } else if (insuficiente) {
                btnValidar.textContent = 'Saldo insuficiente';
                btnValidar.disabled = true;
            } else {
                const label = TIER_LABEL[tierSelecionado] || tierSelecionado;
                btnValidar.textContent = `Validar ${n} nota(s) com Clearance ${label} · ${total} créditos`;
                btnValidar.disabled = false;
            }
        }
    }

    function atualizarSelecao() {
        chkNotas().forEach((chk) => {
            chk.checked = selecionados.has(parseInt(chk.value, 10));
        });
        const n = selecionados.size;
        selLabel.textContent = n === 0
            ? 'Nenhuma nota selecionada'
            : `${n} nota(s) selecionada(s)`;

        const visiveis = chkNotas();
        const todasVisSelecionadas = visiveis.length > 0 && visiveis.every((c) => c.checked);
        if (chkMaster) {
            chkMaster.checked = todasVisSelecionadas;
            chkMaster.indeterminate = !todasVisSelecionadas && visiveis.some((c) => c.checked);
        }

        if (btnSelTodas) {
            btnSelTodas.textContent = n > 0 ? 'Desselecionar Todos' : labelSelTodasOriginal;
            if (temMaisPagina || n > 0 || todasVisSelecionadas) {
                btnSelTodas.classList.remove('hidden');
            } else {
                btnSelTodas.classList.add('hidden');
            }
        }

        if (planosContainer) {
            planosContainer.classList.toggle('hidden', n === 0);
        }

        atualizarCusto();
    }

    if (chkMaster) {
        chkMaster.addEventListener('change', () => {
            chkNotas().forEach((chk) => {
                const id = parseInt(chk.value, 10);
                if (chkMaster.checked) selecionados.add(id);
                else selecionados.delete(id);
            });
            atualizarSelecao();
        });
    }

    document.addEventListener('change', (e) => {
        if (e.target.matches('.chk-nota')) {
            const id = parseInt(e.target.value, 10);
            origens.set(id, origemDoCheckbox(e.target));
            if (e.target.checked) selecionados.add(id);
            else selecionados.delete(id);
            atualizarSelecao();
        }
    });

    TIERS_VALIDOS.forEach((tier) => {
        const card = cards[tier];
        if (!card) return;
        card.addEventListener('click', () => selecionarTier(tier));
        card.addEventListener('keydown', (e) => {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                selecionarTier(tier);
            }
        });
    });

    if (btnSelTodas) {
        btnSelTodas.addEventListener('click', async () => {
            if (selecionados.size > 0) {
                selecionados.clear();
                atualizarSelecao();
                return;
            }
            btnSelTodas.disabled = true;
            try {
                const resp = await fetch(`${idsUrl}?${queryFiltros()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await resp.json();
                if (data.success) {
                    const mapa = data.origens || {};
                    (data.ids || []).forEach((id) => {
                        selecionados.add(id);
                        origens.set(id, mapa[id] || mapa[String(id)] || 'xml');
                    });
                    atualizarSelecao();
                }
            } finally {
                btnSelTodas.disabled = false;
            }
        });
    }

    btnValidar.addEventListener('click', () => {
        const ids = selecionadosArray();
        if (ids.length === 0) return;
        const total = ids.length * (custos[tierSelecionado] || 0);
        const saldoApos = saldoAtual - total;
        const label = TIER_LABEL[tierSelecionado] || tierSelecionado;

        if (modalConfirmQtd) modalConfirmQtd.textContent = String(ids.length);
        if (modalConfirmCusto) modalConfirmCusto.textContent = String(total);
        if (modalConfirmTierLabel) modalConfirmTierLabel.textContent = `Clearance ${label}`;
        if (modalConfirmTierChip) modalConfirmTierChip.textContent = label;
        if (modalConfirmSaldoApos) {
            modalConfirmSaldoApos.textContent = `${saldoApos} créditos`;
            modalConfirmSaldoApos.style.color = saldoApos < 0 ? '#b91c1c' : '#111827';
        }

        abrirModal(modalConfirm);
    });

    if (modalConfirmCancelar) {
        modalConfirmCancelar.addEventListener('click', () => fecharModal(modalConfirm));
    }

    if (modalConfirmOk) {
        modalConfirmOk.addEventListener('click', async () => {
            const ids = selecionadosArray();
            if (ids.length === 0) { fecharModal(modalConfirm); return; }
            modalConfirmOk.disabled = true;
            btnValidar.disabled = true;
            clearInlineError();
            const tabId = gerarTabId();
            try {
                const resp = await fetch(validarUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrf(),
                    },
                    body: JSON.stringify({
                        nota_ids: ids,
                        origens: origensSelecionadas(),
                        tipo: tierSelecionado,
                        tab_id: tabId,
                    }),
                });
                const data = await resp.json();
                if (resp.ok) {
                    fecharModal(modalConfirm);
                    if (data.webhook_disparado) {
                        mostrarProgresso(5, 'Clearance despachado, aguardando provedor...');
                        abrirSseProgresso(data.tab_id || tabId);
                    } else {
                        if (modalSucessoCreditos) modalSucessoCreditos.textContent = String(data.creditos_utilizados ?? 0);
                        abrirModal(modalSucesso);
                    }
                } else if (resp.status === 402) {
                    fecharModal(modalConfirm);
                    showError(`Créditos insuficientes. Necessário: ${data.custo_necessario}. Saldo: ${data.saldo_atual}.`, 'clearance-validar');
                } else if (resp.status === 502) {
                    fecharModal(modalConfirm);
                    showError(data.error || 'Webhook de clearance indisponível. Créditos estornados.', 'clearance-webhook');
                } else {
                    fecharModal(modalConfirm);
                    showError(data.message || data.error || 'Falha ao validar notas.', 'clearance-validar');
                }
            } catch (err) {
                fecharModal(modalConfirm);
                showError('Erro de rede ao validar.', 'clearance-validar');
            } finally {
                modalConfirmOk.disabled = false;
                atualizarCusto();
            }
        });
    }

    if (modalSucessoOk) {
        modalSucessoOk.addEventListener('click', () => {
            fecharModal(modalSucesso);
            window.location.reload();
        });
    }

    if (modalConfirm) {
        modalConfirm.addEventListener('click', (e) => {
            if (e.target === modalConfirm) fecharModal(modalConfirm);
        });
    }

    function showError(message, action) {
        if (window.showInlineError) {
            window.showInlineError(errorRegion, {
                message,
                context: {
                    action,
                    url: window.location.pathname + window.location.search,
                },
            });
            return;
        }

        alert(message);
    }

    function clearInlineError() {
        if (window.clearInlineError) {
            window.clearInlineError(errorRegion);
        }
    }

    registrarOrigens();
    selecionarTier(tierSelecionado);
    atualizarSelecao();
})();
