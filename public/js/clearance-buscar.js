// Escolha EXCLUSIVA de tier na busca avulsa (radio), igual ao lote. Sem os radios na tela
// (flag off) = 'basico'.
function tierBuscaAtual() {
    const marcado = document.querySelector('input[name="busca_tier"]:checked');
    return marcado && marcado.value === 'full' ? 'full' : 'basico';
}

// Destaca o card do tier escolhido e atualiza o custo exibido (o KPI não pode ficar mostrando
// R$ 1,00 quando o usuário escolheu o completo).
function realcarCardBusca() {
    const atual = tierBuscaAtual();

    document.querySelectorAll('.busca-plan-card').forEach((card) => {
        const selecionado = card.dataset.tier === atual;
        card.style.borderColor = selecionado ? '#1f2937' : '#d1d5db';
        card.style.backgroundColor = selecionado ? '#f9fafb' : '#ffffff';
    });

    const alvo = document.getElementById('busca-custo-tier');
    if (!alvo) return;

    const reais = parseFloat(alvo.dataset[atual === 'full' ? 'custoFull' : 'custoBasico'] || '1');
        const texto = 'R$ ' + (Math.round(reais * 100) / 100)
        .toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    alvo.textContent = texto;
    const explicativo = document.getElementById('busca-custo-tier-explicativo');
    if (explicativo) explicativo.textContent = texto;
}

document.addEventListener('change', (e) => {
    if (e.target && e.target.name === 'busca_tier') realcarCardBusca();
});

function initClearanceBuscar() {
    const root = document.getElementById('buscar-nfe-container');
    if (!root) return;
    if (root.dataset.clearanceBuscarInitialized === '1') return;
    root.dataset.clearanceBuscarInitialized = '1';

    const config = window.BUSCAR_NFE_CONFIG || {};
    const CUSTO = Number(config.custo || 1);
    // Conversão monetária definida pelo backend; toda exibição é em R$.
        const brl = (reais) => 'R$ ' + (Math.round((reais || 0) * 100) / 100)
        .toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const ENDPOINTS = config.endpoints || {};
    const BADGE_CORES = config.cores || {};
    const DEFAULT_CLIENTE_ID = config.defaultClienteId ? String(config.defaultClienteId) : '';
    const POSSUI_CLIENTES_DISPONIVEIS = config.possuiClientesDisponiveis === true;

    const input = document.getElementById('nfe-chave');
    const button = document.getElementById('btn-consultar-nfe');
    const clienteSelect = document.getElementById('nfe-cliente-id');
    const feedback = document.getElementById('nfe-chave-feedback');
    const count = document.getElementById('nfe-chave-count');
    const documentTypeInputs = Array.from(document.querySelectorAll('.documento-tipo'));
    const documentTypeCards = Array.from(document.querySelectorAll('.documento-tipo-card'));
    const saldoLabel = document.getElementById('saldo-atual-label');
    const saldoBadge = document.getElementById('saldo-badge');

    const blocoProgresso = document.getElementById('bloco-progresso');
    const progressoBar = document.getElementById('progresso-bar');
    const progressoPercent = document.getElementById('progresso-percent');
    const progressoEtapa = document.getElementById('progresso-etapa');

    const blocoErro = document.getElementById('bloco-erro');
    const erroTitulo = document.getElementById('erro-titulo');
    const erroMensagem = document.getElementById('erro-mensagem');
    const erroRefund = document.getElementById('erro-refund');
    const erroSuporteLink = document.getElementById('erro-suporte-link');

    const blocoResultado = document.getElementById('bloco-resultado');
    const resultStatusBadge = document.getElementById('resultado-status-badge');
    const resultTipo = document.getElementById('resultado-tipo');
    const resultSituacao = document.getElementById('resultado-situacao');
    const resultValor = document.getElementById('resultado-valor');
    const resultEmissao = document.getElementById('resultado-emissao');
    const resultEmitente = document.getElementById('resultado-emitente');
    const resultDestinatario = document.getElementById('resultado-destinatario');
    const resultChave = document.getElementById('resultado-chave');
    const resultCliente = document.getElementById('resultado-cliente');
    const btnDetalhe = document.getElementById('btn-resultado-detalhe');
    const btnReconsultar = document.getElementById('btn-resultado-reconsultar');

    const modalPrecheck = document.getElementById('modal-precheck');
    const modalPrecheckTitulo = document.getElementById('modal-precheck-titulo');
    const modalPrecheckSubtitulo = document.getElementById('modal-precheck-subtitulo');
    const modalPrecheckNota = document.getElementById('modal-precheck-nota');
    const modalPrecheckOrigem = document.getElementById('modal-precheck-origem');
    const modalPrecheckDoc = document.getElementById('modal-precheck-doc');
    const modalPrecheckPartes = document.getElementById('modal-precheck-partes');
    const modalPrecheckValores = document.getElementById('modal-precheck-valores');
    const modalPrecheckChave = document.getElementById('modal-precheck-chave');
    const modalPrecheckSnapshot = document.getElementById('modal-precheck-snapshot');
    const modalPrecheckCaminho = document.getElementById('modal-precheck-caminho');
    const modalPrecheckPrecoClearance = document.getElementById('modal-precheck-preco-clearance');
    const modalPrecheckMensagem = document.getElementById('modal-precheck-mensagem');
    const modalPrecheckConfirm = document.getElementById('modal-precheck-confirm');
    const modalPrecheckConfirmTexto = document.getElementById('modal-precheck-confirm-texto');
    const modalPrecheckErro = document.getElementById('modal-precheck-erro');
    const modalPrecheckAtalhoListagem = document.getElementById('modal-precheck-atalho-listagem');
    const modalPrecheckDetalhe = document.getElementById('modal-precheck-detalhe');
    const modalPrecheckAcao = document.getElementById('modal-precheck-acao');

    const defaultButtonLabel = button ? button.textContent.trim() : 'Consultar documento';

    let currentEventSource = null;
    let currentTimeoutHandle = null;
    let inFlight = false;

    window._cleanupFunctions = window._cleanupFunctions || {};
    window._cleanupFunctions.initClearanceBuscar = function() {
        try { fecharSseEexpirar(); } catch (_) {}
    };

    function onlyDigits(value) {
        return (value || '').replace(/\D/g, '').slice(0, 44);
    }

    function selectedDocumentTypeKey() {
        const selected = documentTypeInputs.find((item) => item.checked && !item.disabled);
        return selected ? selected.value : 'nfe';
    }

    function selectedCliente() {
        if (!clienteSelect || !clienteSelect.value) {
            return { id: null, nome: 'Sem cliente associado' };
        }
        return {
            id: clienteSelect.value,
            nome: clienteSelect.options[clienteSelect.selectedIndex].text.trim(),
        };
    }

    function updateSelectedCard() {
        const key = selectedDocumentTypeKey();
        documentTypeCards.forEach((card) => {
            if (card.classList.contains('is-disabled')) return;
            card.classList.toggle('is-selected', card.dataset.documentTypeCard === key);
        });
    }

    function setButtonLoading(isLoading) {
        if (!button) return;
        if (isLoading) {
            button.disabled = true;
            button.textContent = 'Consultando...';
        } else {
            button.textContent = defaultButtonLabel;
            updateState();
        }
    }

    function hide(el) {
        if (el) el.classList.add('hidden');
    }

    function show(el) {
        if (el) el.classList.remove('hidden');
    }

    function resetEstadosVisuais() {
        hide(blocoProgresso);
        hide(blocoErro);
        hide(blocoResultado);
        hide(erroRefund);
        hide(erroSuporteLink);
        if (progressoBar) progressoBar.style.width = '8%';
        if (progressoPercent) progressoPercent.textContent = '0%';
        if (progressoEtapa) progressoEtapa.textContent = 'Iniciando consulta...';
    }

    function setProgresso(percent, etapa) {
        const value = Math.max(0, Math.min(100, Number(percent) || 0));
        if (progressoBar) progressoBar.style.width = value + '%';
        if (progressoPercent) progressoPercent.textContent = value + '%';
        if (etapa && progressoEtapa) progressoEtapa.textContent = etapa;
    }

    function buildSystemError(payload, action, overrides) {
        if (!window.SystemCriticalError) {
            return null;
        }

        return window.SystemCriticalError.fromPayload(payload || {}, {
            title: overrides && overrides.title,
            message: overrides && overrides.message,
            context: {
                action: action || 'clearance-buscar',
                url: window.location.pathname + window.location.search,
            },
        });
    }

    function mostrarErro(titulo, mensagem, refund, criticalError) {
        fecharSseEexpirar();
        hide(blocoProgresso);
        hide(blocoResultado);
        if (erroTitulo) erroTitulo.textContent = criticalError && criticalError.title ? criticalError.title : (titulo || 'Não foi possível consultar');
        if (erroMensagem) erroMensagem.textContent = criticalError && criticalError.message ? criticalError.message : (mensagem || '-');
        if (refund) {
            show(erroRefund);
        } else {
            hide(erroRefund);
        }
        if (erroSuporteLink && criticalError && window.SystemCriticalError) {
            window.SystemCriticalError.applyActionLink(erroSuporteLink, criticalError);
            show(erroSuporteLink);
        } else {
            hide(erroSuporteLink);
        }
        show(blocoErro);
        blocoErro && blocoErro.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function fecharSseEexpirar() {
        if (currentEventSource) {
            try { currentEventSource.close(); } catch (_) {}
            currentEventSource = null;
        }
        if (currentTimeoutHandle) {
            clearTimeout(currentTimeoutHandle);
            currentTimeoutHandle = null;
        }
    }

    function atualizarSaldo(novoSaldo) {
        if (typeof novoSaldo !== 'number') return;
        if (saldoLabel) saldoLabel.textContent = brl(novoSaldo);
        if (saldoBadge) {
            const suficiente = novoSaldo >= CUSTO;
            saldoBadge.style.backgroundColor = suficiente ? '#047857' : '#dc2626';
            saldoBadge.textContent = suficiente ? 'Saldo suficiente' : 'Saldo insuficiente';
        }
    }

    function renderResultado(nota) {
        if (!nota) return;

        const situacao = String(nota.situacao || 'INDETERMINADO').toUpperCase();
        const cor = BADGE_CORES[situacao] || '#374151';

        if (resultStatusBadge) {
            resultStatusBadge.textContent = situacao;
            resultStatusBadge.style.backgroundColor = cor;
        }
        if (resultTipo) resultTipo.textContent = (nota.tipo_documento || 'NFE').toUpperCase();
        if (resultSituacao) resultSituacao.textContent = situacao;
        if (resultValor) {
            const valor = Number(nota.valor_total || 0);
            resultValor.textContent = valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }
        if (resultEmissao) resultEmissao.textContent = nota.data_emissao || '-';
        if (resultEmitente) resultEmitente.textContent = nota.emit || '-';
        if (resultDestinatario) resultDestinatario.textContent = nota.dest || '-';
        if (resultChave) resultChave.textContent = nota.nfe_id || '-';
        if (resultCliente) resultCliente.textContent = nota.cliente_nome || 'Sem cliente associado';
        if (btnDetalhe) {
            if (nota.detalhe_url) {
                btnDetalhe.setAttribute('href', nota.detalhe_url);
                btnDetalhe.classList.remove('opacity-50', 'pointer-events-none');
                btnDetalhe.removeAttribute('aria-disabled');
            } else {
                btnDetalhe.setAttribute('href', '#');
                btnDetalhe.classList.add('opacity-50', 'pointer-events-none');
                btnDetalhe.setAttribute('aria-disabled', 'true');
            }
        }

        hide(blocoProgresso);
        hide(blocoErro);
        show(blocoResultado);
        blocoResultado && blocoResultado.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    async function buscarResultado(consultaLoteId) {
        try {
            const url = ENDPOINTS.resultado.replace(/\/$/, '') + '/' + encodeURIComponent(consultaLoteId);
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            });
            if (response.status === 404) {
                mostrarErro(
                    'Nota ainda não persistida',
                    'A consulta concluiu, mas o webhook n8n ainda não gravou o resultado em nfe_consultas/cte_consultas. Tente novamente em alguns segundos.',
                    false
                );
                return;
            }
            if (!response.ok) {
                mostrarErro('Erro ao carregar resultado', 'Resposta HTTP ' + response.status, false);
                return;
            }
            const data = await response.json();
            renderResultado(data.nota);
        } catch (err) {
            mostrarErro('Erro ao carregar resultado', err.message || 'Falha de rede', false);
        }
    }

    function abrirSse(tabId, consultaLoteId) {
        if (!ENDPOINTS.sse) return;

        const url = ENDPOINTS.sse + '?tab_id=' + encodeURIComponent(tabId);
        const es = new EventSource(url, { withCredentials: true });
        currentEventSource = es;

        currentTimeoutHandle = setTimeout(() => {
            const criticalError = buildSystemError({ status: 'timeout' }, 'clearance-buscar-timeout');
            mostrarErro(
                'Tempo esgotado',
                'A consulta não retornou em 60 segundos. Verifique o histórico mais tarde.',
                false,
                criticalError
            );
        }, 60000);

        es.onmessage = (event) => {
            let data;
            try {
                data = JSON.parse(event.data);
            } catch (_) {
                return;
            }

            const status = data.status || 'processando';
            const progresso = Number(data.progresso || 0);
            const etapa = data.etapa_label || data.mensagem || data.etapa || 'Processando...';

            if (status === 'processando') {
                setProgresso(progresso, etapa);
                return;
            }

            if (status === 'concluido') {
                setProgresso(100, etapa);
                return;
            }

            if (status === 'finalizado') {
                setProgresso(100, 'Finalizado, carregando resultado...');
                fecharSseEexpirar();
                buscarResultado(consultaLoteId);
                inFlight = false;
                setButtonLoading(false);
                return;
            }

            if (status === 'erro') {
                const criticalError = buildSystemError(data, 'clearance-buscar-sse-erro');
                const refund = data.refund_credits === true || data.refund_aplicado === true;
                if (refund && data.refund_amount) {
                    atualizarSaldo(Number(data.saldo_atual || 0) + Number(data.refund_amount || 0));
                }
                mostrarErro(
                    'Consulta falhou',
                    data.mensagem || data.error_message || 'O provedor retornou erro.',
                    refund,
                    criticalError
                );
                inFlight = false;
                setButtonLoading(false);
                return;
            }

            if (status === 'timeout') {
                const criticalError = buildSystemError(data, 'clearance-buscar-sse-timeout');
                mostrarErro(
                    'Tempo esgotado',
                    data.mensagem || 'A consulta excedeu o limite do servidor.',
                    false,
                    criticalError
                );
                inFlight = false;
                setButtonLoading(false);
                return;
            }
        };

        es.onerror = () => {
            // Se o SSE cair mas o status ainda estiver processando, deixa o timeout global cuidar
        };
    }

    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function fecharModalPrecheck() {
        if (!modalPrecheck) return;
        modalPrecheck.classList.add('hidden');
        modalPrecheck.classList.remove('flex');
    }

    function mostrarErroModalPrecheck(msg) {
        if (!modalPrecheckErro) return;
        modalPrecheckErro.textContent = msg;
        modalPrecheckErro.classList.remove('hidden');
    }

    async function fazerPrecheck(chave) {
        if (!ENDPOINTS.precheck) return null;
        try {
            const response = await fetch(ENDPOINTS.precheck, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ chave_acesso: chave }),
            });
            if (!response.ok) return null; // fail-open: o guard server-side segue protegendo a cobrança
            return await response.json().catch(() => null);
        } catch (_) {
            return null;
        }
    }

    // Dispara o clearance da nota que já está na base (tier básico — mais barato que a avulsa).
    async function validarNoClearance(pre) {
        if (!ENDPOINTS.validar || !pre || !pre.nota_id) return;
        modalPrecheckAcao.disabled = true;
        modalPrecheckAcao.textContent = 'Iniciando verificação...';
        modalPrecheckErro.classList.add('hidden');

        const origens = {};
        origens[pre.nota_id] = pre.origem || 'xml';
        const tabId = 'dfe-acervo-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);

        try {
            const response = await fetch(ENDPOINTS.validar, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    nota_ids: [pre.nota_id],
                    origens: origens,
                    tipo: 'basico',
                    tab_id: tabId,
                }),
            });
            const data = await response.json().catch(() => ({}));

            if (response.status === 402) {
                mostrarErroModalPrecheck(`Saldo insuficiente. Esta verificação custa ${brl(pre.custo_clearance || CUSTO)}.`);
            } else if (response.status === 403) {
                mostrarErroModalPrecheck(data.error || 'Seu plano não inclui a verificação de notas da base.');
            } else if (!response.ok || data.success === false) {
                mostrarErroModalPrecheck(data.error || data.message || ('Erro ao iniciar a verificação (HTTP ' + response.status + ').'));
            } else {
                if (typeof data.novo_saldo === 'number') atualizarSaldo(data.novo_saldo);
                if (data.resultado_url) {
                    window.location.assign(data.resultado_url);
                    return;
                }
                fecharModalPrecheck();
            }
        } catch (err) {
            mostrarErroModalPrecheck(err.message || 'Falha de rede ao iniciar a verificação.');
        }

        modalPrecheckAcao.disabled = false;
        modalPrecheckAcao.textContent = `Confirmar e pagar — ${brl(pre.custo_clearance || CUSTO)}`;
    }

    // Etapa de confirmação de pagamento: 1º clique arma, 2º clique executa. Nada é
    // debitado antes do "Confirmar e pagar".
    function armarConfirmacaoPagamento(valorLabel, onConfirm) {
        modalPrecheckErro.classList.add('hidden');
        modalPrecheckMensagem.classList.add('hidden');
        modalPrecheckCaminho.classList.add('hidden');
        modalPrecheckConfirmTexto.textContent = 'O valor de ' + valorLabel
            + ' será debitado do seu saldo assim que a consulta iniciar.';
        modalPrecheckConfirm.classList.remove('hidden');
        modalPrecheckAcao.textContent = 'Confirmar e pagar — ' + valorLabel;
        modalPrecheckAcao.onclick = onConfirm;
    }

    function abrirModalPrecheck(pre, chave) {
        if (!modalPrecheck) return;
        [modalPrecheckErro, modalPrecheckSnapshot, modalPrecheckNota, modalPrecheckCaminho,
            modalPrecheckConfirm].forEach((el) => el.classList.add('hidden'));
        [modalPrecheckDetalhe, modalPrecheckAcao, modalPrecheckAtalhoListagem].forEach((el) => {
            el.classList.add('hidden');
            el.classList.remove('inline-flex');
        });
        modalPrecheckMensagem.classList.remove('hidden');
        modalPrecheckAcao.disabled = false;

        if (pre.snapshot) {
            modalPrecheckSnapshot.textContent = 'Última verificação SEFAZ: '
                + (pre.snapshot.status || '—')
                + (pre.snapshot.consultado_em_label ? ' em ' + pre.snapshot.consultado_em_label : '') + '.';
            modalPrecheckSnapshot.classList.remove('hidden');
        }

        if (pre.no_acervo) {
            // Nota já importada (XML/EFD): não deixa pagar a busca avulsa — oferece o clearance da nota.
            modalPrecheckTitulo.textContent = 'Nota já está na sua base';
            modalPrecheckSubtitulo.textContent = 'Encontramos esta chave entre as notas já importadas.';
            const nota = pre.nota || {};
            modalPrecheckOrigem.textContent = nota.origem_acervo_label || (pre.origem || '').toUpperCase();
            modalPrecheckOrigem.style.backgroundColor = nota.origem_acervo_hex || '#4b5563';
            modalPrecheckDoc.textContent = (nota.tipo_documento || 'DF-e')
                + (nota.numero ? ' nº ' + nota.numero : '')
                + (nota.serie ? ' · série ' + nota.serie : '');
            modalPrecheckPartes.textContent = [nota.emit_nome, nota.dest_nome].filter(Boolean).join(' para ') || '';
            modalPrecheckValores.textContent = [nota.valor_total_label, nota.data_emissao_label].filter(Boolean).join(' · ');
            modalPrecheckChave.textContent = chave || '';
            modalPrecheckNota.classList.remove('hidden');

            modalPrecheckPrecoClearance.textContent = brl(pre.custo_clearance || CUSTO);
            modalPrecheckCaminho.classList.remove('hidden');

            modalPrecheckMensagem.textContent = 'Deseja verificar a situação desta nota na SEFAZ pelo clearance?';

            if (pre.listagem_url) {
                modalPrecheckAtalhoListagem.href = pre.listagem_url;
                modalPrecheckAtalhoListagem.classList.remove('hidden');
                modalPrecheckAtalhoListagem.classList.add('inline-flex');
            }
            if (pre.detalhe_url) {
                modalPrecheckDetalhe.href = pre.detalhe_url;
                modalPrecheckDetalhe.classList.remove('hidden');
                modalPrecheckDetalhe.classList.add('inline-flex');
            }
            modalPrecheckAcao.textContent = `Sim, verificar na SEFAZ — ${brl(pre.custo_clearance || CUSTO)}`;
            modalPrecheckAcao.onclick = () => armarConfirmacaoPagamento(
                brl(pre.custo_clearance || CUSTO),
                () => validarNoClearance(pre)
            );
            modalPrecheckAcao.classList.remove('hidden');
            modalPrecheckAcao.classList.add('inline-flex');
        } else {
            // Sem acervo, mas já consultada antes (snapshot): confirma antes de recobrar.
            modalPrecheckTitulo.textContent = 'Documento já consultado';
            modalPrecheckSubtitulo.textContent = 'Esta chave já tem um resultado SEFAZ registrado no sistema.';
            modalPrecheckMensagem.textContent = 'Deseja consultar novamente por '
                + brl(pre.custo_avulsa || CUSTO) + '? O resultado anterior será sobrescrito.';
            modalPrecheckAcao.textContent = `Sim, consultar novamente — ${brl(pre.custo_avulsa || CUSTO)}`;
            modalPrecheckAcao.onclick = () => armarConfirmacaoPagamento(
                brl(pre.custo_avulsa || CUSTO),
                () => {
                    fecharModalPrecheck();
                    enviarConsulta({ skipPrecheck: true, reconsultar: true });
                }
            );
            modalPrecheckAcao.classList.remove('hidden');
            modalPrecheckAcao.classList.add('inline-flex');
        }

        modalPrecheck.classList.remove('hidden');
        modalPrecheck.classList.add('flex');
    }

    async function enviarConsulta(opts = {}) {
        if (inFlight) return;
        if (!button || button.disabled) return;

        const chave = onlyDigits(input.value);
        const tipo = selectedDocumentTypeKey();
        const cliente = selectedCliente();
        const tabId = 'dfe-' + tipo + '-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);

        // Precheck: chave já importada (acervo) não paga busca avulsa — modal oferece o clearance
        // da nota. Chave já consultada (snapshot) pede confirmação antes de recobrar.
        if (!opts.skipPrecheck) {
            setButtonLoading(true);
            const pre = await fazerPrecheck(chave);
            setButtonLoading(false);
            if (pre && pre.success && (pre.no_acervo || pre.snapshot)) {
                abrirModalPrecheck(pre, chave);
                return;
            }
        }

        resetEstadosVisuais();
        show(blocoProgresso);
        setProgresso(5, 'Enviando requisição...');
        setButtonLoading(true);
        inFlight = true;

        const csrfToken = csrf();

        try {
            const response = await fetch(ENDPOINTS.consultar, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    tipo_documento: tipo,
                    chave_acesso: chave,
                    cliente_id: cliente.id,
                    tab_id: tabId,
                    // Clearance completo: + regularidade da contraparte (preço fechado maior por
                    // documento). O backend coage p/ 'basico' se a flag estiver off.
                    tipo: tierBuscaAtual(),
                    reconsultar: opts.reconsultar === true,
                }),
            });

            const data = await response.json().catch(() => ({}));

            if (response.status === 422) {
                const msg = data.message
                    || (data.errors ? Object.values(data.errors).flat().join(' · ') : 'Dados inválidos.');
                mostrarErro('Dados inválidos', msg, false);
                inFlight = false;
                setButtonLoading(false);
                return;
            }

            if (response.status === 402) {
                mostrarErro(
                    'Saldo insuficiente',
                    `Esta consulta custa ${brl(data.custo_necessario || CUSTO)}. Saldo atual: ${brl(data.saldo_atual ?? 0)}.`,
                    false
                );
                inFlight = false;
                setButtonLoading(false);
                return;
            }

            if (response.status === 403) {
                mostrarErro('Acesso negado', data.error || 'Cliente não pertence ao seu usuário.', false);
                inFlight = false;
                setButtonLoading(false);
                return;
            }

            if (response.status === 502) {
                const criticalError = buildSystemError(data, 'clearance-buscar-webhook');
                mostrarErro(
                    'Integração indisponível',
                    data.error || 'O webhook n8n não respondeu. Seu saldo foi estornado.',
                    true,
                    criticalError
                );
                if (typeof data.novo_saldo === 'number') atualizarSaldo(data.novo_saldo);
                inFlight = false;
                setButtonLoading(false);
                return;
            }

            if (!response.ok) {
                const criticalError = buildSystemError(data, 'clearance-buscar-interno');
                mostrarErro(
                    'Erro interno',
                    data.error || ('HTTP ' + response.status),
                    data.refund_aplicado === true,
                    criticalError
                );
                if (typeof data.novo_saldo === 'number') atualizarSaldo(data.novo_saldo);
                inFlight = false;
                setButtonLoading(false);
                return;
            }

            if (typeof data.novo_saldo === 'number') atualizarSaldo(data.novo_saldo);
            if (data.resultado_url) {
                setProgresso(20, 'Consulta iniciada, abrindo tela de resultado...');
                window.location.assign(data.resultado_url);
                return;
            }

            setProgresso(15, 'Consulta iniciada, aguardando provedor...');
            abrirSse(data.tab_id || tabId, data.consulta_lote_id);

            if (btnReconsultar) {
                btnReconsultar.onclick = () => enviarConsulta({ skipPrecheck: true, reconsultar: true });
            }
        } catch (err) {
            mostrarErro('Falha de rede', err.message || 'Não foi possível contatar o servidor.', false);
            inFlight = false;
            setButtonLoading(false);
        }
    }

    function updateState() {
        if (!POSSUI_CLIENTES_DISPONIVEIS) {
            button.disabled = true;
            feedback.textContent = 'Cadastre ou regularize a empresa própria para consultar um documento.';
            feedback.className = 'text-[11px] text-amber-700';
            count.textContent = '0';
            return;
        }

        const digits = onlyDigits(input.value);
        const clienteId = clienteSelect ? String(clienteSelect.value || '') : '';
        input.value = digits;
        updateSelectedCard();

        const length = digits.length;
        count.textContent = String(length);

        if (!clienteId) {
            button.disabled = true;
            feedback.textContent = 'Selecione o cliente associado antes de consultar.';
            feedback.className = 'text-[11px] text-amber-700';
            return;
        }

        if (length === 0) {
            button.disabled = true;
            feedback.textContent = 'Cole a chave com 44 dígitos para consultar o documento do cliente selecionado.';
            feedback.className = 'text-[11px] text-gray-500';
            return;
        }

        if (length < 44) {
            button.disabled = true;
            feedback.textContent = `Chave incompleta: faltam ${44 - length} dígito(s).`;
            feedback.className = 'text-[11px] text-amber-700';
            return;
        }

        button.disabled = false;
        feedback.textContent = 'Chave válida (44 dígitos) e cliente associado. Pronto para consultar.';
        feedback.className = 'text-[11px] text-green-700';
    }

    input.addEventListener('input', updateState);
    input.addEventListener('paste', () => window.setTimeout(updateState, 0));
    if (clienteSelect) {
        if (DEFAULT_CLIENTE_ID && !clienteSelect.value) {
            clienteSelect.value = DEFAULT_CLIENTE_ID;
        }
        clienteSelect.addEventListener('change', updateState);
    }
    documentTypeInputs.forEach((item) => {
        if (!item.disabled) item.addEventListener('change', updateState);
    });

    button.addEventListener('click', () => enviarConsulta());
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !button.disabled) {
            e.preventDefault();
            enviarConsulta();
        }
    });

    document.querySelectorAll('[data-precheck-close]').forEach((el) => {
        el.addEventListener('click', fecharModalPrecheck);
    });

    updateState();
}

window.initClearanceBuscar = initClearanceBuscar;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initClearanceBuscar);
} else {
    initClearanceBuscar();
}
