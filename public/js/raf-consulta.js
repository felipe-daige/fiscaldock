/**
 * RAF Consulta - JavaScript
 * Gerencia selecao de participantes e execucao de consultas RAF.
 */
(function() {
    'use strict';

    // Estado global
    const state = {
        selectedIds: new Set(),
        currentPage: 1,
        perPage: 50,
        totalPages: 1,
        totalItems: 0,
        allIdsCurrentFilter: [],
        filters: {
            grupo_id: '',
            cliente_id: '',
            origem_tipo: '',
            busca: ''
        },
        tabId: generateUUID(),
        consultaLoteId: null,
        eventSource: null,
        credits: window.rafConsultaData?.credits || 0
    };

    // Elementos DOM
    let elements = {};

    /**
     * Inicializa o modulo quando o DOM estiver pronto.
     */
    function init() {
        cacheElements();
        bindEvents();

        // Pre-selecionar participantes da URL (vindos da lista de participantes)
        const urlParams = new URLSearchParams(window.location.search);
        const participantesParam = urlParams.get('participantes');
        if (participantesParam) {
            participantesParam.split(',').forEach(function(id) {
                const numId = parseInt(id, 10);
                if (!isNaN(numId)) {
                    state.selectedIds.add(numId);
                }
            });
            console.log('[RAF Consulta] Pre-selecionados da URL:', state.selectedIds.size, 'participantes');
        }

        loadParticipantes();
        updateResumo();
    }

    /**
     * Cache de elementos DOM.
     */
    function cacheElements() {
        elements = {
            tabelaBody: document.getElementById('tabela-participantes'),
            loadingRow: document.getElementById('loading-row'),
            checkboxTodos: document.getElementById('checkbox-todos'),
            totalSelecionados: document.getElementById('total-selecionados'),

            // Filtros
            filtroGrupo: document.getElementById('filtro-grupo'),
            filtroOrigem: document.getElementById('filtro-origem'),
            filtroCliente: document.getElementById('filtro-cliente'),
            filtroBusca: document.getElementById('filtro-busca'),

            // Botoes
            btnSelecionarTodos: document.getElementById('btn-selecionar-todos'),
            btnLimparSelecao: document.getElementById('btn-limpar-selecao'),
            btnGerarRelatorio: document.getElementById('btn-gerar-relatorio'),
            btnPagAnterior: document.getElementById('btn-pag-anterior'),
            btnPagProximo: document.getElementById('btn-pag-proximo'),

            // Paginacao
            pagInicio: document.getElementById('pag-inicio'),
            pagFim: document.getElementById('pag-fim'),
            pagTotal: document.getElementById('pag-total'),
            pagAtual: document.getElementById('pag-atual'),

            // Resumo
            resumoParticipantes: document.getElementById('resumo-participantes'),
            resumoCustoUnitario: document.getElementById('resumo-custo-unitario'),
            resumoCustoTotal: document.getElementById('resumo-custo-total'),
            resumoSaldo: document.getElementById('resumo-saldo'),
            alertaCreditosInsuficientes: document.getElementById('alerta-creditos-insuficientes'),

            // Modais
            modalProgresso: document.getElementById('modal-progresso'),
            progressoTitulo: document.getElementById('progresso-titulo'),
            progressoMensagem: document.getElementById('progresso-mensagem'),
            progressoBarra: document.getElementById('progresso-barra'),
            progressoPercentual: document.getElementById('progresso-percentual'),

            modalSucesso: document.getElementById('modal-sucesso'),
            linkDownloadManual: document.getElementById('link-download-manual'),
            btnFecharSucesso: document.getElementById('btn-fechar-sucesso'),

            modalErro: document.getElementById('modal-erro'),
            erroMensagem: document.getElementById('erro-mensagem'),
            btnFecharErro: document.getElementById('btn-fechar-erro')
        };
    }

    /**
     * Vincula eventos aos elementos.
     */
    function bindEvents() {
        // Filtros
        if (elements.filtroGrupo) {
            elements.filtroGrupo.addEventListener('change', onFilterChange);
        }
        if (elements.filtroOrigem) elements.filtroOrigem.addEventListener('change', onFilterChange);
        if (elements.filtroCliente) elements.filtroCliente.addEventListener('change', onFilterChange);
        if (elements.filtroBusca) {
            elements.filtroBusca.addEventListener('input', debounce(onFilterChange, 300));
        }

        // Selecao
        if (elements.checkboxTodos) elements.checkboxTodos.addEventListener('change', toggleTodosPagina);
        if (elements.btnSelecionarTodos) elements.btnSelecionarTodos.addEventListener('click', selecionarTodosFilter);
        if (elements.btnLimparSelecao) elements.btnLimparSelecao.addEventListener('click', limparSelecao);

        // Planos
        document.querySelectorAll('input[name="plano_id"]').forEach(radio => {
            radio.addEventListener('change', updateResumo);
        });

        // Acoes
        if (elements.btnGerarRelatorio) {
            elements.btnGerarRelatorio.addEventListener('click', executarConsulta);

            // Efeito hover no botao
            elements.btnGerarRelatorio.addEventListener('mouseenter', function() {
                if (!this.disabled) {
                    this.style.backgroundColor = '#1f2937'; // gray-800
                }
            });
            elements.btnGerarRelatorio.addEventListener('mouseleave', function() {
                if (!this.disabled) {
                    this.style.backgroundColor = '#111827'; // gray-900
                }
            });
        }

        // Paginacao
        if (elements.btnPagAnterior) elements.btnPagAnterior.addEventListener('click', () => changePage(-1));
        if (elements.btnPagProximo) elements.btnPagProximo.addEventListener('click', () => changePage(1));

        // Modais
        if (elements.btnFecharSucesso) elements.btnFecharSucesso.addEventListener('click', () => hideModal('sucesso'));
        if (elements.btnFecharErro) elements.btnFecharErro.addEventListener('click', () => hideModal('erro'));
    }

    /**
     * Carrega participantes do servidor.
     */
    async function loadParticipantes() {
        showLoading(true);

        const params = new URLSearchParams({
            page: state.currentPage,
            per_page: state.perPage
        });

        if (state.filters.grupo_id) params.append('grupo_id', state.filters.grupo_id);
        if (state.filters.cliente_id) params.append('cliente_id', state.filters.cliente_id);
        if (state.filters.origem_tipo) params.append('origem_tipo', state.filters.origem_tipo);
        if (state.filters.busca) params.append('busca', state.filters.busca);

        try {
            const response = await fetch(`${window.rafConsultaData.routes.getParticipantes}?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Erro ao carregar participantes');

            const data = await response.json();

            if (data.success) {
                renderParticipantes(data.data);
                updatePaginacao(data.pagination);
            } else {
                showError('Erro ao carregar participantes');
            }
        } catch (error) {
            console.error('Erro:', error);
            showError('Erro ao carregar participantes');
        } finally {
            showLoading(false);
        }
    }

    /**
     * Renderiza lista de participantes na tabela.
     */
    function renderParticipantes(participantes) {
        if (!elements.tabelaBody) return;

        elements.tabelaBody.innerHTML = '';

        if (!participantes || participantes.length === 0) {
            elements.tabelaBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                        Nenhum participante encontrado.
                    </td>
                </tr>
            `;
            return;
        }

        participantes.forEach(p => {
            const isSelected = state.selectedIds.has(p.id);
            const tr = document.createElement('tr');
            tr.className = `hover:bg-gray-50 transition ${isSelected ? 'bg-gray-50' : ''}`;
            tr.dataset.id = p.id;

            // Formatar CNPJ
            const cnpjFormatado = formatCnpj(p.cnpj);

            tr.innerHTML = `
                <td class="w-10 px-4 py-3">
                    <input type="checkbox" class="checkbox-participante w-4 h-4 text-gray-600 rounded border-gray-300" data-id="${p.id}" ${isSelected ? 'checked' : ''}>
                </td>
                <td class="w-40 px-4 py-3 text-sm font-mono text-gray-600">${cnpjFormatado}</td>
                <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900">${p.razao_social || '-'}</div>
                    ${p.nome_fantasia ? `<div class="text-xs text-gray-500">${p.nome_fantasia}</div>` : ''}
                </td>
                <td class="w-16 px-4 py-3 text-sm text-gray-600 text-center">${p.uf || '-'}</td>
            `;

            // Evento de checkbox
            const checkbox = tr.querySelector('.checkbox-participante');
            checkbox.addEventListener('change', () => toggleParticipante(p.id, checkbox.checked));

            elements.tabelaBody.appendChild(tr);
        });

        updateCheckboxTodos();
    }

    /**
     * Atualiza controles de paginacao.
     */
    function updatePaginacao(pagination) {
        if (!pagination) return;

        state.currentPage = pagination.current_page;
        state.totalPages = pagination.last_page;
        state.totalItems = pagination.total;

        const inicio = (pagination.current_page - 1) * pagination.per_page + 1;
        const fim = Math.min(pagination.current_page * pagination.per_page, pagination.total);

        if (elements.pagInicio) elements.pagInicio.textContent = pagination.total > 0 ? inicio : 0;
        if (elements.pagFim) elements.pagFim.textContent = fim;
        if (elements.pagTotal) elements.pagTotal.textContent = pagination.total;
        if (elements.pagAtual) elements.pagAtual.textContent = `${pagination.current_page} / ${pagination.last_page}`;

        if (elements.btnPagAnterior) elements.btnPagAnterior.disabled = pagination.current_page <= 1;
        if (elements.btnPagProximo) elements.btnPagProximo.disabled = pagination.current_page >= pagination.last_page;
    }

    /**
     * Muda de pagina.
     */
    function changePage(delta) {
        const newPage = state.currentPage + delta;
        if (newPage >= 1 && newPage <= state.totalPages) {
            state.currentPage = newPage;
            loadParticipantes();
        }
    }

    /**
     * Handler de mudanca de filtros.
     */
    function onFilterChange() {
        state.filters.grupo_id = elements.filtroGrupo?.value || '';
        state.filters.cliente_id = elements.filtroCliente?.value || '';
        state.filters.origem_tipo = elements.filtroOrigem?.value || '';
        state.filters.busca = elements.filtroBusca?.value || '';
        state.currentPage = 1;
        loadParticipantes();
    }

    /**
     * Alterna selecao de um participante.
     */
    function toggleParticipante(id, isSelected) {
        if (isSelected) {
            state.selectedIds.add(id);
        } else {
            state.selectedIds.delete(id);
        }
        updateContadorSelecionados();
        updateResumo();
        updateCheckboxTodos();
        updateRowHighlight(id, isSelected);
    }

    /**
     * Alterna selecao de todos na pagina atual.
     */
    function toggleTodosPagina() {
        const isChecked = elements.checkboxTodos.checked;
        document.querySelectorAll('.checkbox-participante').forEach(cb => {
            const id = parseInt(cb.dataset.id);
            cb.checked = isChecked;
            if (isChecked) {
                state.selectedIds.add(id);
            } else {
                state.selectedIds.delete(id);
            }
            updateRowHighlight(id, isChecked);
        });
        updateContadorSelecionados();
        updateResumo();
    }

    /**
     * Seleciona todos os participantes do filtro atual (todas as paginas).
     */
    async function selecionarTodosFilter() {
        // Buscar todos os IDs do filtro atual
        const params = new URLSearchParams({
            page: 1,
            per_page: 10000 // Limite alto para pegar todos
        });

        if (state.filters.grupo_id) params.append('grupo_id', state.filters.grupo_id);
        if (state.filters.cliente_id) params.append('cliente_id', state.filters.cliente_id);
        if (state.filters.origem_tipo) params.append('origem_tipo', state.filters.origem_tipo);
        if (state.filters.busca) params.append('busca', state.filters.busca);

        try {
            const response = await fetch(`${window.rafConsultaData.routes.getParticipantes}?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success && data.data) {
                data.data.forEach(p => state.selectedIds.add(p.id));

                // Atualizar checkboxes visiveis
                document.querySelectorAll('.checkbox-participante').forEach(cb => {
                    cb.checked = true;
                    const id = parseInt(cb.dataset.id);
                    updateRowHighlight(id, true);
                });

                updateContadorSelecionados();
                updateResumo();
                updateCheckboxTodos();
            }
        } catch (error) {
            console.error('Erro ao selecionar todos:', error);
        }
    }

    /**
     * Limpa toda a selecao.
     */
    function limparSelecao() {
        state.selectedIds.clear();
        document.querySelectorAll('.checkbox-participante').forEach(cb => {
            cb.checked = false;
            const id = parseInt(cb.dataset.id);
            updateRowHighlight(id, false);
        });
        if (elements.checkboxTodos) elements.checkboxTodos.checked = false;
        updateContadorSelecionados();
        updateResumo();
    }

    /**
     * Atualiza highlight da linha.
     */
    function updateRowHighlight(id, isSelected) {
        const tr = elements.tabelaBody?.querySelector(`tr[data-id="${id}"]`);
        if (tr) {
            if (isSelected) {
                tr.classList.add('bg-gray-50');
            } else {
                tr.classList.remove('bg-gray-50');
            }
        }
    }

    /**
     * Atualiza estado do checkbox "todos".
     */
    function updateCheckboxTodos() {
        if (!elements.checkboxTodos) return;

        const checkboxes = document.querySelectorAll('.checkbox-participante');
        if (checkboxes.length === 0) {
            elements.checkboxTodos.checked = false;
            elements.checkboxTodos.indeterminate = false;
            return;
        }

        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;

        if (checkedCount === 0) {
            elements.checkboxTodos.checked = false;
            elements.checkboxTodos.indeterminate = false;
        } else if (checkedCount === checkboxes.length) {
            elements.checkboxTodos.checked = true;
            elements.checkboxTodos.indeterminate = false;
        } else {
            elements.checkboxTodos.checked = false;
            elements.checkboxTodos.indeterminate = true;
        }
    }

    /**
     * Atualiza contador de selecionados.
     */
    function updateContadorSelecionados() {
        if (elements.totalSelecionados) {
            elements.totalSelecionados.textContent = state.selectedIds.size;
        }
    }

    /**
     * Atualiza resumo de custos.
     */
    function updateResumo() {
        const totalParticipantes = state.selectedIds.size;
        const planoSelecionado = document.querySelector('input[name="plano_id"]:checked');
        const custoUnitario = planoSelecionado ? parseInt(planoSelecionado.dataset.custo) : 0;
        const isGratuito = planoSelecionado?.dataset.gratuito === '1';
        const custoTotal = isGratuito ? 0 : totalParticipantes * custoUnitario;
        const creditosSuficientes = state.credits >= custoTotal;

        if (elements.resumoParticipantes) elements.resumoParticipantes.textContent = totalParticipantes;
        if (elements.resumoCustoUnitario) elements.resumoCustoUnitario.textContent = isGratuito ? 'Grátis' : `${custoUnitario} ${custoUnitario === 1 ? 'crédito' : 'créditos'}`;
        if (elements.resumoCustoTotal) elements.resumoCustoTotal.textContent = isGratuito ? 'Grátis' : `${custoTotal} ${custoTotal === 1 ? 'crédito' : 'créditos'}`;

        // Alerta de creditos insuficientes
        if (elements.alertaCreditosInsuficientes) {
            elements.alertaCreditosInsuficientes.classList.toggle('hidden', creditosSuficientes || isGratuito);
        }

        // Habilitar/desabilitar botao
        if (elements.btnGerarRelatorio) {
            const shouldDisable = totalParticipantes === 0 || (!creditosSuficientes && !isGratuito);
            elements.btnGerarRelatorio.disabled = shouldDisable;

            // Atualizar estilos inline do botao
            if (shouldDisable) {
                elements.btnGerarRelatorio.style.backgroundColor = '#d1d5db'; // gray-300
                elements.btnGerarRelatorio.style.color = '#6b7280'; // gray-500
                elements.btnGerarRelatorio.style.cursor = 'not-allowed';
            } else {
                elements.btnGerarRelatorio.style.backgroundColor = '#111827'; // gray-900
                elements.btnGerarRelatorio.style.color = '#ffffff'; // white
                elements.btnGerarRelatorio.style.cursor = 'pointer';
            }
        }
    }

    /**
     * Executa a consulta RAF.
     */
    async function executarConsulta() {
        const participanteIds = Array.from(state.selectedIds);
        const planoId = document.querySelector('input[name="plano_id"]:checked')?.value;
        const clienteId = elements.filtroCliente?.value || null;

        if (participanteIds.length === 0) {
            alert('Selecione pelo menos um participante.');
            return;
        }

        if (!planoId) {
            alert('Selecione um tipo de analise.');
            return;
        }

        // Mostrar modal de progresso
        showModal('progresso');
        updateProgresso(0, 'Iniciando consulta...');

        try {
            const response = await fetch(window.rafConsultaData.routes.executar, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.rafConsultaData.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    participante_ids: participanteIds,
                    plano_id: parseInt(planoId),
                    cliente_id: clienteId ? parseInt(clienteId) : null,
                    tab_id: state.tabId
                })
            });

            // Parse JSON - handle invalid JSON response
            let data;
            try {
                data = await response.json();
            } catch (e) {
                console.error('RAF Consulta: resposta invalida do servidor', e);
                hideModal('progresso');
                showModal('erro');
                if (elements.erroMensagem) elements.erroMensagem.textContent = 'Resposta invalida do servidor.';
                return;
            }

            // Verificar sucesso (HTTP status + JSON success field)
            if (!response.ok || !data.success) {
                hideModal('progresso');
                showModal('erro');
                const errorMsg = data?.error || `Erro ${response.status}: ${response.statusText}`;
                if (elements.erroMensagem) elements.erroMensagem.textContent = errorMsg;
                console.error('RAF Consulta erro:', errorMsg, data);
                return;
            }

            // Sucesso
            state.consultaLoteId = data.consulta_lote_id;
            state.credits = data.novo_saldo;
            if (elements.resumoSaldo) elements.resumoSaldo.textContent = `${data.novo_saldo} créditos`;

            // Iniciar SSE para progresso
            iniciarSSE();

        } catch (error) {
            console.error('RAF Consulta excecao:', error);
            hideModal('progresso');
            showModal('erro');
            if (elements.erroMensagem) {
                elements.erroMensagem.textContent = error.message || 'Erro de conexao. Tente novamente.';
            }
        }
    }

    /**
     * Inicia SSE para acompanhar progresso.
     */
    function iniciarSSE() {
        if (state.eventSource) {
            state.eventSource.close();
        }

        const url = `${window.rafConsultaData.routes.progressoStream}?tab_id=${state.tabId}`;
        state.eventSource = new EventSource(url);

        state.eventSource.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);

                updateProgresso(data.progresso, data.mensagem);

                if (data.status === 'concluido') {
                    state.eventSource.close();
                    onConsultaConcluida();
                } else if (data.status === 'erro') {
                    state.eventSource.close();
                    onConsultaErro(data.error_message || 'Erro desconhecido');
                }
            } catch (e) {
                console.error('Erro ao processar SSE:', e);
            }
        };

        state.eventSource.onerror = function() {
            console.error('Erro na conexao SSE');
            state.eventSource.close();
            // Nao mostrar erro imediatamente - pode ser apenas reconexao
        };
    }

    /**
     * Atualiza indicadores de progresso.
     */
    function updateProgresso(percentual, mensagem) {
        if (elements.progressoBarra) elements.progressoBarra.style.width = `${percentual}%`;
        if (elements.progressoPercentual) elements.progressoPercentual.textContent = `${percentual}%`;
        if (elements.progressoMensagem && mensagem) elements.progressoMensagem.textContent = mensagem;
    }

    /**
     * Handler de consulta concluida.
     */
    function onConsultaConcluida() {
        hideModal('progresso');
        showModal('sucesso');

        // Link de download
        if (elements.linkDownloadManual && state.consultaLoteId) {
            const downloadUrl = window.rafConsultaData.routes.baixarLote.replace('{id}', state.consultaLoteId);
            elements.linkDownloadManual.href = downloadUrl;

            // Iniciar download automatico
            window.location.href = downloadUrl;
        }

        // Limpar selecao
        limparSelecao();
    }

    /**
     * Handler de erro na consulta.
     */
    function onConsultaErro(mensagem) {
        hideModal('progresso');
        showModal('erro');
        if (elements.erroMensagem) elements.erroMensagem.textContent = mensagem;
    }

    /**
     * Mostra modal.
     */
    function showModal(tipo) {
        const modal = document.getElementById(`modal-${tipo}`);
        if (modal) modal.classList.remove('hidden');
    }

    /**
     * Esconde modal.
     */
    function hideModal(tipo) {
        const modal = document.getElementById(`modal-${tipo}`);
        if (modal) modal.classList.add('hidden');
    }

    /**
     * Mostra/esconde loading.
     */
    function showLoading(show) {
        if (elements.loadingRow) {
            elements.loadingRow.style.display = show ? '' : 'none';
        }
    }

    /**
     * Mostra erro generico.
     */
    function showError(message) {
        if (elements.tabelaBody) {
            elements.tabelaBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-red-500">
                        ${message}
                    </td>
                </tr>
            `;
        }
    }

    /**
     * Formata CNPJ.
     */
    function formatCnpj(cnpj) {
        if (!cnpj) return '-';
        const numeros = cnpj.replace(/\D/g, '');
        if (numeros.length !== 14) return cnpj;
        return numeros.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    }

    /**
     * Gera UUID v4.
     */
    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    /**
     * Debounce function.
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Inicializar quando pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expor funcao de inicializacao para SPA
    window.initRafConsulta = init;
})();
