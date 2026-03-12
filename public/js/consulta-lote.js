/**
 * Consulta Lote - JavaScript
 * Gerencia selecao de participantes e execucao de consultas em lote.
 */
(function() {
    'use strict';

    // Guard: previne re-execução da IIFE que sobrescreveria closures
    if (window._consultaLoteModuleLoaded) return;
    window._consultaLoteModuleLoaded = true;

    // Nomes amigaveis das consultas
    const CONSULTA_NOMES = {
        situacao_cadastral: 'Situacao Cadastral',
        dados_cadastrais: 'Dados Cadastrais',
        endereco: 'Endereco',
        cnaes: 'CNAEs',
        qsa: 'Quadro Societario (QSA)',
        simples_nacional: 'Simples Nacional',
        mei: 'MEI',
        sintegra: 'SINTEGRA',
        tcu_consolidada: 'TCU Consolidada',
        cnd_federal: 'CND Federal (PGFN)',
        crf_fgts: 'CRF/FGTS',
        cnd_estadual: 'CND Estadual',
        cndt: 'CNDT (Trabalhista)',
        protestos: 'Protestos',
        lista_devedores_pgfn: 'Lista Devedores PGFN',
        trabalho_escravo: 'Trabalho Escravo',
        ibama_autuacoes: 'IBAMA Autuacoes',
        processos_cnj: 'Processos CNJ'
    };

    // Estado global
    const state = {
        selectedIds: new Set(),
        selectedClienteIds: new Set(),
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
        activeTab: 'participantes',
        filterContext: null, // { type: 'cliente'|'grupo', id: int, label: string }
        expandedClienteId: null, // ID do cliente expandido inline (null = nenhum)
        tabId: generateUUID(),
        consultaLoteId: null,
        eventSource: null,
        credits: window.consultaData?.credits || 0,
        isExecuting: false
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
            console.log('[Consulta Lote] Pre-selecionados da URL:', state.selectedIds.size, 'participantes');
        }

        loadParticipantes();
        updatePlanoStyles();
        updateConsultasIncluidas();
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
            filtroOrigem: document.getElementById('filtro-origem'),
            filtroBusca: document.getElementById('filtro-busca'),

            // Abas
            searchTabs: document.querySelectorAll('.search-tab'),
            viewParticipantes: document.getElementById('view-participantes'),
            viewClientes: document.getElementById('view-clientes'),
            viewGrupos: document.getElementById('view-grupos'),
            listaClientes: document.getElementById('lista-clientes'),
            checkboxTodosClientes: document.getElementById('checkbox-todos-clientes'),
            listaGrupos: document.getElementById('lista-grupos'),
            buscaClientes: document.getElementById('busca-clientes'),
            participantesContext: document.getElementById('participantes-context'),
            filterContextLabel: document.getElementById('filter-context-label'),
            btnClearFilterContext: document.getElementById('btn-clear-filter-context'),
            btnRemoveFilterChip: document.getElementById('btn-remove-filter-chip'),

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

            // Progresso inline
            consultaFormSection: document.getElementById('consulta-form-section'),
            consultaProgressoSection: document.getElementById('consulta-progresso-section'),
            progressoTitulo: document.getElementById('progresso-titulo'),
            progressoMensagem: document.getElementById('progresso-mensagem'),
            progressoBarra: document.getElementById('progresso-barra'),
            progressoPercentual: document.getElementById('progresso-percentual'),
            consultaProgressoIcon: document.getElementById('consulta-progresso-icon'),
            consultaProgressoCard: document.getElementById('consulta-progresso-card'),
            consultaProgressoErro: document.getElementById('consulta-progresso-erro'),
            consultaProgressoErroMsg: document.getElementById('consulta-progresso-erro-msg'),
            btnTentarNovamente: document.getElementById('btn-tentar-novamente'),
            resultadoConsulta: document.getElementById('resultado-consulta'),
            resultadoConsultaInfo: document.getElementById('resultado-consulta-info'),
            linkDownloadRelatorio: document.getElementById('link-download-relatorio'),
            btnNovaConsulta: document.getElementById('btn-nova-consulta'),

            // Adicionar CNPJ
            inputAdicionarCnpj: document.getElementById('input-adicionar-cnpj'),
            selectClienteAssociar: document.getElementById('select-cliente-associar'),
            btnAdicionarCnpj: document.getElementById('btn-adicionar-cnpj'),
            feedbackAdicionarCnpj: document.getElementById('feedback-adicionar-cnpj'),

        };
    }

    /**
     * Vincula eventos aos elementos.
     */
    function bindEvents() {
        // Filtros (apenas origem e busca; grupo/cliente agora via abas)
        if (elements.filtroOrigem) elements.filtroOrigem.addEventListener('change', onFilterChange);
        if (elements.filtroBusca) {
            elements.filtroBusca.addEventListener('input', debounce(onFilterChange, 300));
        }

        // Abas (event delegation - robusto para SPA re-navigation)
        var searchTabsContainer = document.getElementById('search-tabs');
        if (searchTabsContainer && !searchTabsContainer._tabDelegated) {
            searchTabsContainer._tabDelegated = true;
            searchTabsContainer.addEventListener('click', function(e) {
                var tab = e.target.closest('.search-tab');
                if (tab && tab.dataset.tab) {
                    switchTab(tab.dataset.tab);
                }
            });
        }

        // Selecao de todos clientes (checkbox header)
        if (elements.checkboxTodosClientes) {
            elements.checkboxTodosClientes.addEventListener('change', toggleTodosClientes);
        }

        // Busca clientes (dentro da aba Clientes)
        if (elements.buscaClientes) {
            elements.buscaClientes.addEventListener('input', debounce(loadClientes, 300));
        }

        // Limpar filtro de contexto
        if (elements.btnClearFilterContext) {
            elements.btnClearFilterContext.addEventListener('click', clearFilterContext);
        }
        if (elements.btnRemoveFilterChip) {
            elements.btnRemoveFilterChip.addEventListener('click', clearFilterContext);
        }

        // Selecao
        if (elements.checkboxTodos) elements.checkboxTodos.addEventListener('change', toggleTodosPagina);
        if (elements.btnSelecionarTodos) elements.btnSelecionarTodos.addEventListener('click', selecionarTodosFilter);
        if (elements.btnLimparSelecao) elements.btnLimparSelecao.addEventListener('click', limparSelecao);

        // Planos
        document.querySelectorAll('input[name="plano_id"]').forEach(radio => {
            radio.addEventListener('change', () => {
                updatePlanoStyles();
                updateConsultasIncluidas();
                updateResumo();
            });
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

        // Adicionar CNPJ
        if (elements.inputAdicionarCnpj) {
            elements.inputAdicionarCnpj.addEventListener('input', function() {
                this.value = applyCnpjMask(this.value);
            });
            elements.inputAdicionarCnpj.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    adicionarCnpj();
                }
            });
        }
        if (elements.btnAdicionarCnpj) {
            elements.btnAdicionarCnpj.addEventListener('click', adicionarCnpj);
        }

        // Progresso inline
        if (elements.btnTentarNovamente) elements.btnTentarNovamente.addEventListener('click', voltarParaFormulario);
        if (elements.btnNovaConsulta) elements.btnNovaConsulta.addEventListener('click', voltarParaFormulario);
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
            const response = await fetch(`${window.consultaData.routes.getParticipantes}?${params}`, {
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
            tr.className = 'hover:bg-gray-50 transition flex flex-col gap-1 px-4 py-3 md:table-row md:px-0 md:py-0 md:gap-0' + (isSelected ? ' bg-gray-50' : '');
            tr.dataset.id = p.id;

            // Formatar CNPJ
            const cnpjFormatado = formatCnpj(p.cnpj);
            const escNome = (p.razao_social || '').replace(/"/g, '&quot;');

            const clienteNome = (p.cliente && p.cliente.razao_social) ? p.cliente.razao_social : '';
            const isEmpresaPropria = p.cliente && p.cliente.is_empresa_propria;
            const dotIndicator = isEmpresaPropria
                ? '<span class="shrink-0 w-1.5 h-1.5 rounded-full bg-green-500"></span>'
                : '';
            const clienteTitle = clienteNome + (isEmpresaPropria ? ' (Empresa propria)' : '');
            const clienteHtml = clienteNome
                ? `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-blue-50 max-w-full" title="${clienteTitle}">${dotIndicator}<span class="truncate min-w-0 text-xs font-medium text-blue-700">${clienteNome}</span></span>`
                : '<span class="text-gray-300">&mdash;</span>';

            // Badge de situacao cadastral (RF = Receita Federal)
            const situacaoMap = {
                'ATIVA': {
                    bg: 'bg-green-50', text: 'text-green-700', border: 'border-green-200',
                    label: 'Ativa',
                    tooltip: 'Empresa regular perante a Receita Federal',
                    icon: '<svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                },
                'BAIXADA': {
                    bg: 'bg-red-50', text: 'text-red-700', border: 'border-red-200',
                    label: 'Baixada',
                    tooltip: 'Empresa encerrada/fechada na Receita Federal',
                    icon: '<svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                },
                'INAPTA': {
                    bg: 'bg-red-50', text: 'text-red-700', border: 'border-red-200',
                    label: 'Inapta',
                    tooltip: 'Empresa irregular perante a Receita Federal \u2014 omissa em obrigacoes acessorias',
                    icon: '<svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                },
                'SUSPENSA': {
                    bg: 'bg-yellow-50', text: 'text-yellow-700', border: 'border-yellow-200',
                    label: 'Suspensa',
                    tooltip: 'Empresa temporariamente suspensa na Receita Federal',
                    icon: '<svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>'
                },
                'NULA': {
                    bg: 'bg-gray-100', text: 'text-gray-600', border: 'border-gray-300',
                    label: 'Nula',
                    tooltip: 'Inscricao anulada na Receita Federal',
                    icon: '<svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'
                },
            };
            const situacao = p.situacao_cadastral;
            const info = situacaoMap[situacao] || null;
            const situacaoBadge = (situacao && info)
                ? `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded border text-[10px] font-medium ${info.bg} ${info.text} ${info.border}" title="${info.tooltip}"><span class="text-[9px] opacity-60">RF:</span>${info.icon}<span>${info.label}</span></span>`
                : '';

            // Build optional subline: nome_fantasia + cliente badge (mobile) on one flex row
            const sublineParts = [];
            if (p.nome_fantasia) sublineParts.push(`<span class="text-xs text-gray-500 truncate">${p.nome_fantasia}</span>`);
            if (clienteNome) sublineParts.push(`<span class="md:hidden flex-shrink-0">${clienteHtml}</span>`);
            const sublineHtml = sublineParts.length > 0
                ? `<div class="flex items-center gap-2 mt-0.5 min-w-0">${sublineParts.join('')}</div>`
                : '';

            tr.innerHTML = `
                <td class="hidden md:table-cell md:w-10 md:px-4 md:py-3">
                    <input type="checkbox" class="checkbox-participante w-4 h-4 text-gray-600 rounded border-gray-300" data-id="${p.id}" ${isSelected ? 'checked' : ''}>
                </td>
                <td class="block md:table-cell md:w-40 md:px-4 md:py-3 overflow-hidden">
                    <div class="flex items-center gap-1.5 md:block min-w-0">
                        <input type="checkbox" class="checkbox-participante w-4 h-4 text-gray-600 rounded border-gray-300 md:hidden flex-shrink-0" data-id="${p.id}" ${isSelected ? 'checked' : ''}>
                        <span class="text-xs md:text-sm font-mono text-gray-500 md:text-gray-600 whitespace-nowrap tabular-nums">${cnpjFormatado}</span>
                        ${situacaoBadge ? `<span class="md:hidden flex-shrink-0">${situacaoBadge}</span>` : ''}
                    </div>
                </td>
                <td class="block md:table-cell md:px-4 md:py-3 md:max-w-0 overflow-hidden">
                    <div class="text-sm font-medium text-gray-900 truncate min-w-0">${p.razao_social || '-'}</div>
                    ${sublineHtml}
                    ${situacaoBadge ? `<div class="hidden md:block mt-0.5">${situacaoBadge}</div>` : ''}
                </td>
                <td class="hidden md:table-cell md:px-4 md:py-3 text-sm text-gray-500">
                    <div class="overflow-hidden">${clienteHtml}</div>
                </td>
            `;

            // Evento de checkbox (desktop + mobile duplicados, manter sincronizados)
            const checkboxes = tr.querySelectorAll('.checkbox-participante');
            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    checkboxes.forEach(other => { other.checked = cb.checked; });
                    toggleParticipante(p.id, cb.checked);
                });
            });

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
        // grupo_id e cliente_id sao gerenciados pelas abas (filterContext)
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
        try {
            let page = 1;
            let lastPage = 1;

            while (page <= lastPage) {
                const params = new URLSearchParams({
                    page: page,
                    per_page: 100
                });

                if (state.filters.grupo_id) params.append('grupo_id', state.filters.grupo_id);
                if (state.filters.cliente_id) params.append('cliente_id', state.filters.cliente_id);
                if (state.filters.origem_tipo) params.append('origem_tipo', state.filters.origem_tipo);
                if (state.filters.busca) params.append('busca', state.filters.busca);

                const response = await fetch(`${window.consultaData.routes.getParticipantes}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Erro ao buscar participantes');

                const data = await response.json();

                if (data.success && data.data) {
                    data.data.forEach(p => state.selectedIds.add(p.id));
                    lastPage = data.pagination?.last_page || 1;
                } else {
                    break;
                }

                page++;
            }

            // Atualizar checkboxes visiveis na pagina atual
            document.querySelectorAll('.checkbox-participante').forEach(cb => {
                cb.checked = true;
                const id = parseInt(cb.dataset.id);
                updateRowHighlight(id, true);
            });

            updateContadorSelecionados();
            updateResumo();
            updateCheckboxTodos();
        } catch (error) {
            console.error('Erro ao selecionar todos:', error);
        }
    }

    /**
     * Limpa toda a selecao.
     */
    function limparSelecao() {
        state.selectedIds.clear();
        state.selectedClienteIds.clear();
        document.querySelectorAll('.checkbox-participante').forEach(cb => {
            cb.checked = false;
            const id = parseInt(cb.dataset.id);
            updateRowHighlight(id, false);
        });
        if (elements.checkboxTodos) elements.checkboxTodos.checked = false;
        if (elements.checkboxTodosClientes) {
            elements.checkboxTodosClientes.checked = false;
            elements.checkboxTodosClientes.indeterminate = false;
        }
        // Uncheck visible client checkboxes
        document.querySelectorAll('.checkbox-cliente').forEach(function(cb) { cb.checked = false; });
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
     * Atualiza estilos visuais dos labels de plano (selected vs unselected).
     */
    function updatePlanoStyles() {
        document.querySelectorAll('.plano-label').forEach(label => {
            const radio = label.querySelector('input[type="radio"]');
            if (radio.checked) {
                label.classList.remove('border-gray-200', 'hover:border-gray-300', 'hover:bg-gray-500/8');
                label.classList.add('border-blue-500', 'bg-blue-50/60', 'ring-2', 'ring-blue-100');
            } else {
                label.classList.remove('border-blue-500', 'bg-blue-50/60', 'ring-2', 'ring-blue-100');
                label.classList.add('border-gray-200', 'hover:border-gray-300', 'hover:bg-gray-500/8');
            }
        });
    }

    /**
     * Atualiza lista de consultas incluidas no card lateral.
     */
    function updateConsultasIncluidas() {
        const container = document.getElementById('lista-consultas-incluidas');
        if (!container) return;

        const planoRadio = document.querySelector('input[name="plano_id"]:checked');
        if (!planoRadio) return;

        const planoId = planoRadio.value;
        const planoData = window.consultaData?.planos?.[planoId];
        if (!planoData || !planoData.consultas) {
            container.innerHTML = '<p class="text-xs text-gray-400">Nenhuma consulta disponivel.</p>';
            return;
        }

        const checkSvg = '<svg class="w-3.5 h-3.5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';

        container.innerHTML = planoData.consultas.map(consulta => {
            const nome = CONSULTA_NOMES[consulta] || consulta;

            return `<div class="flex items-center gap-2 py-1">
                ${checkSvg}
                <span class="text-xs text-gray-700 flex-1">${nome}</span>
            </div>`;
        }).join('');
    }

    /**
     * Atualiza resumo de custos.
     */
    function updateResumo() {
        const totalParticipantes = state.selectedIds.size;
        const planoSelecionado = document.querySelector('input[name="plano_id"]:checked');
        const custoUnitario = planoSelecionado ? parseInt(planoSelecionado.dataset.custo) : 0;
        const isGratuito = planoSelecionado && planoSelecionado.dataset.gratuito === '1';
        const custoTotal = isGratuito ? 0 : totalParticipantes * custoUnitario;
        const creditosSuficientes = isGratuito || state.credits >= custoTotal;

        if (elements.resumoParticipantes) elements.resumoParticipantes.textContent = totalParticipantes;

        if (isGratuito) {
            if (elements.resumoCustoUnitario) elements.resumoCustoUnitario.textContent = 'Gratis';
            if (elements.resumoCustoTotal) elements.resumoCustoTotal.textContent = 'Gratis';
        } else {
            if (elements.resumoCustoUnitario) elements.resumoCustoUnitario.textContent = `${custoUnitario} ${custoUnitario === 1 ? 'crédito' : 'créditos'}`;
            if (elements.resumoCustoTotal) elements.resumoCustoTotal.textContent = `${custoTotal} ${custoTotal === 1 ? 'crédito' : 'créditos'}`;
        }

        // Alerta de creditos insuficientes
        if (elements.alertaCreditosInsuficientes) {
            elements.alertaCreditosInsuficientes.classList.toggle('hidden', creditosSuficientes);
        }

        // Habilitar/desabilitar botao
        if (elements.btnGerarRelatorio) {
            const shouldDisable = totalParticipantes === 0 || !creditosSuficientes;
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
     * Executa a consulta.
     */
    async function executarConsulta() {
        if (state.isExecuting) return;

        const participanteIds = Array.from(state.selectedIds);
        const planoId = document.querySelector('input[name="plano_id"]:checked')?.value;
        const clienteId = state.filters.cliente_id || null;

        if (participanteIds.length === 0) {
            alert('Selecione pelo menos um participante.');
            return;
        }

        if (!planoId) {
            alert('Selecione um tipo de analise.');
            return;
        }

        state.isExecuting = true;
        if (elements.btnGerarRelatorio) {
            elements.btnGerarRelatorio.disabled = true;
            elements.btnGerarRelatorio.style.backgroundColor = '#d1d5db';
            elements.btnGerarRelatorio.style.color = '#6b7280';
            elements.btnGerarRelatorio.style.cursor = 'not-allowed';
        }

        // Mostrar progresso inline
        mostrarProgressoInline();

        try {
            const response = await fetch(window.consultaData.routes.executar, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.consultaData.csrfToken,
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
                console.error('Consulta Lote: resposta invalida do servidor', e);
                onConsultaErro('Resposta invalida do servidor.');
                return;
            }

            // Verificar sucesso (HTTP status + JSON success field)
            if (!response.ok || !data.success) {
                const errorMsg = data?.error || `Erro ${response.status}: ${response.statusText}`;
                console.error('Consulta Lote erro:', errorMsg, data);
                state.isExecuting = false;
                if (elements.btnGerarRelatorio) elements.btnGerarRelatorio.disabled = false;
                onConsultaErro(errorMsg);
                return;
            }

            // Sucesso
            state.consultaLoteId = data.consulta_lote_id;
            state.credits = data.novo_saldo;
            if (elements.resumoSaldo) elements.resumoSaldo.textContent = `${data.novo_saldo} créditos`;

            // Iniciar SSE para progresso
            iniciarSSE();

        } catch (error) {
            console.error('Consulta Lote excecao:', error);
            state.isExecuting = false;
            if (elements.btnGerarRelatorio) elements.btnGerarRelatorio.disabled = false;
            onConsultaErro(error.message || 'Erro de conexao. Tente novamente.');
        }
    }

    /**
     * Inicia SSE para acompanhar progresso.
     */
    function iniciarSSE() {
        if (state.eventSource) {
            state.eventSource.close();
            state.eventSource = null;
        }

        var reconnectAttempts = 0;
        var maxReconnectAttempts = 3;
        var reconnectDelays = [3000, 6000, 12000];
        var lastDataHash = null;
        var lastUpdate = 0;
        var throttleMs = 500;
        var pollingInterval = null;

        function pararPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        function connect() {
            var url = window.consultaData.routes.progressoStream + '?tab_id=' + state.tabId;
            state.eventSource = new EventSource(url);

            state.eventSource.onmessage = function(event) {
                // Heartbeat: ignorar ping/vazio
                var raw = event.data;
                if (!raw || raw === ':ping') return;

                // Hash deduplication
                var hash = simpleHash(raw);
                if (hash === lastDataHash) return;
                lastDataHash = hash;

                // Reset contador de reconexao em mensagem bem-sucedida
                reconnectAttempts = 0;

                var data;
                try {
                    data = JSON.parse(raw);
                } catch (e) {
                    console.error('Erro ao processar SSE:', e);
                    return;
                }

                // Sempre processar estados terminais (sem throttle)
                if (data.status === 'concluido') {
                    updateProgresso(data.progresso, data.mensagem);
                    state.eventSource.close();
                    state.eventSource = null;
                    pararPolling();
                    onConsultaConcluida();
                    return;
                }
                if (data.status === 'erro') {
                    state.eventSource.close();
                    state.eventSource = null;
                    pararPolling();
                    onConsultaErro(data.error_message || 'Erro desconhecido');
                    return;
                }
                if (data.status === 'timeout') {
                    state.eventSource.close();
                    state.eventSource = null;
                    pararPolling();
                    onConsultaErro(data.mensagem || 'Tempo limite atingido. Verifique o histórico.');
                    return;
                }

                // Throttle de 0.5s para atualizacoes intermediarias
                var now = Date.now();
                if (now - lastUpdate < throttleMs) return;
                lastUpdate = now;

                updateProgresso(data.progresso, data.mensagem);
            };

            state.eventSource.onerror = function() {
                console.error('Erro na conexao SSE (tentativa ' + (reconnectAttempts + 1) + ')');
                state.eventSource.close();
                state.eventSource = null;

                if (reconnectAttempts < maxReconnectAttempts) {
                    var delay = reconnectDelays[reconnectAttempts];
                    reconnectAttempts++;
                    setTimeout(connect, delay);
                } else {
                    onConsultaErro('Conexao perdida. Verifique sua internet e tente novamente.');
                }
            };
        }

        connect();

        // Polling fallback: checa status no DB a cada 5s
        if (state.consultaLoteId && window.consultaData.routes.loteStatus) {
            pollingInterval = setInterval(function() {
                var statusUrl = window.consultaData.routes.loteStatus.replace('{id}', state.consultaLoteId);
                fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!data.success) return;
                        if (data.status === 'concluido') {
                            pararPolling();
                            if (state.eventSource) { state.eventSource.close(); state.eventSource = null; }
                            onConsultaConcluida();
                        } else if (data.status === 'erro') {
                            pararPolling();
                            if (state.eventSource) { state.eventSource.close(); state.eventSource = null; }
                            onConsultaErro('Erro no processamento.');
                        } else {
                            updateProgresso(data.progresso, null);
                        }
                    })
                    .catch(function() {}); // silenciar erros de rede
            }, 5000);

            // Guardar referência para parar no voltarParaFormulario
            state._pollingInterval = pollingInterval;
            state._pararPolling = pararPolling;
        }
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
     * Atualiza ícone e estado visual do card de progresso da consulta.
     */
    function atualizarIconeConsulta(status, errorMessage) {
        const icon = elements.consultaProgressoIcon;
        const card = elements.consultaProgressoCard;
        const barra = elements.progressoBarra;
        const erroDiv = elements.consultaProgressoErro;
        const erroMsg = elements.consultaProgressoErroMsg;

        if (!icon || !card) return;

        card.className = 'bg-white border rounded-lg p-4 shadow-sm';

        if (status === 'concluido') {
            icon.className = 'w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0';
            icon.innerHTML = '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            card.classList.add('border-green-200');
            if (barra) barra.className = 'bg-green-600 h-full rounded-full transition-all duration-500 ease-out';
            if (erroDiv) erroDiv.classList.add('hidden');
        } else if (status === 'erro') {
            icon.className = 'w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0';
            icon.innerHTML = '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            card.classList.add('border-red-200');
            if (barra) barra.className = 'bg-red-600 h-full rounded-full transition-all duration-500 ease-out';
            if (erroDiv) erroDiv.classList.remove('hidden');
            if (erroMsg && errorMessage) erroMsg.textContent = errorMessage;
        } else {
            icon.className = 'w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0';
            icon.innerHTML = '<svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>';
            card.classList.add('border-gray-200');
            if (barra) barra.className = 'bg-blue-600 h-full rounded-full transition-all duration-500 ease-out';
            if (erroDiv) erroDiv.classList.add('hidden');
        }
    }

    /**
     * Handler de consulta concluida.
     */
    function onConsultaConcluida() {
        updateProgresso(100, 'Concluído');
        atualizarIconeConsulta('concluido');

        // Mostrar seção de resultado inline
        if (elements.resultadoConsulta) {
            elements.resultadoConsulta.classList.remove('hidden');
        }

        // Link de download e auto-download
        if (state.consultaLoteId) {
            const downloadUrl = window.consultaData.routes.baixarLote.replace('{id}', state.consultaLoteId);
            if (elements.linkDownloadRelatorio) {
                elements.linkDownloadRelatorio.href = downloadUrl;
            }
            if (elements.resultadoConsultaInfo) {
                const total = state.selectedIds.size;
                elements.resultadoConsultaInfo.textContent = total + ' participante' + (total !== 1 ? 's' : '');
            }
        }

        // Limpar selecao
        limparSelecao();
    }

    /**
     * Handler de erro na consulta.
     */
    function onConsultaErro(mensagem) {
        atualizarIconeConsulta('erro', mensagem);
    }

    /**
     * Mostra a seção de progresso inline e oculta o formulário.
     */
    function mostrarProgressoInline() {
        // Reset estado visual
        atualizarIconeConsulta('processando');
        updateProgresso(0, 'Iniciando consulta...');
        if (elements.consultaProgressoErro) elements.consultaProgressoErro.classList.add('hidden');
        if (elements.resultadoConsulta) elements.resultadoConsulta.classList.add('hidden');

        // Trocar seções (usar fallback direto ao DOM)
        var formSec = elements.consultaFormSection || document.getElementById('consulta-form-section');
        var progressSec = elements.consultaProgressoSection || document.getElementById('consulta-progresso-section');
        if (formSec) formSec.style.display = 'none';
        if (progressSec) { progressSec.classList.remove('hidden'); progressSec.style.display = 'block'; }
        window.scrollTo({ top: 0, behavior: 'instant' });
    }

    /**
     * Volta para o formulário e reseta o estado de progresso.
     */
    function voltarParaFormulario() {
        state.isExecuting = false;
        state.tabId = generateUUID();
        if (elements.btnGerarRelatorio) elements.btnGerarRelatorio.disabled = false;

        // Parar polling fallback
        if (state._pararPolling) { state._pararPolling(); state._pararPolling = null; }
        if (state._pollingInterval) { clearInterval(state._pollingInterval); state._pollingInterval = null; }

        // Fechar SSE se aberta
        if (state.eventSource) {
            state.eventSource.close();
            state.eventSource = null;
        }

        // Reset visual
        atualizarIconeConsulta('processando');
        updateProgresso(0, 'Iniciando...');
        if (elements.consultaProgressoErro) elements.consultaProgressoErro.classList.add('hidden');
        if (elements.resultadoConsulta) elements.resultadoConsulta.classList.add('hidden');
        state.consultaLoteId = null;

        // Trocar seções (usar fallback direto ao DOM)
        var formSec = elements.consultaFormSection || document.getElementById('consulta-form-section');
        var progressSec = elements.consultaProgressoSection || document.getElementById('consulta-progresso-section');
        if (progressSec) { progressSec.classList.add('hidden'); progressSec.style.display = ''; }
        if (formSec) formSec.style.display = '';
        updateResumo();
    }

    /**
     * Hash simples para deduplicação de mensagens SSE.
     */
    function simpleHash(str) {
        var hash = 0;
        for (var i = 0; i < str.length; i++) {
            hash = ((hash << 5) - hash) + str.charCodeAt(i);
            hash |= 0;
        }
        return hash;
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

    // ==========================================
    // Adicionar CNPJ
    // ==========================================

    /**
     * Aplica mascara de CNPJ: XX.XXX.XXX/XXXX-XX
     */
    function applyCnpjMask(value) {
        var digits = value.replace(/\D/g, '').substring(0, 14);
        if (digits.length <= 2) return digits;
        if (digits.length <= 5) return digits.replace(/(\d{2})(\d+)/, '$1.$2');
        if (digits.length <= 8) return digits.replace(/(\d{2})(\d{3})(\d+)/, '$1.$2.$3');
        if (digits.length <= 12) return digits.replace(/(\d{2})(\d{3})(\d{3})(\d+)/, '$1.$2.$3/$4');
        return digits.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d+)/, '$1.$2.$3/$4-$5');
    }

    /**
     * Adiciona um CNPJ como participante via AJAX.
     */
    async function adicionarCnpj() {
        if (!elements.inputAdicionarCnpj) return;

        var rawValue = elements.inputAdicionarCnpj.value;
        var cnpj = rawValue.replace(/\D/g, '');

        if (cnpj.length !== 14) {
            showFeedbackCnpj('error', 'Informe um CNPJ válido com 14 dígitos.');
            return;
        }

        // Associacao opcional a cliente existente
        var clienteId = elements.selectClienteAssociar?.value || null;

        hideFeedbackCnpj();
        setBtnAdicionarLoading(true);

        try {
            var bodyPayload = { cnpj: cnpj };
            if (clienteId) {
                bodyPayload.cliente_id = parseInt(clienteId);
            }

            var response = await fetch(window.consultaData.routes.adicionarCnpj, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.consultaData.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(bodyPayload)
            });

            var data;
            try {
                data = await response.json();
            } catch (e) {
                showFeedbackCnpj('error', 'Resposta inválida do servidor.');
                return;
            }

            if (!response.ok || !data.success) {
                showFeedbackCnpj('error', data.error || 'Erro ao adicionar CNPJ.');
                return;
            }

            // Sucesso
            var participanteId = data.participante.id;
            state.selectedIds.add(participanteId);

            // Limpar input
            elements.inputAdicionarCnpj.value = '';

            if (data.is_new) {
                showFeedbackCnpj('success', data.message);
                // Reload tabela na pagina 1 para mostrar o novo participante
                state.currentPage = 1;
                await loadParticipantes();
            } else {
                showFeedbackCnpj('info', data.message);
                // Tentar selecionar na tabela visivel
                var checkbox = elements.tabelaBody?.querySelector('.checkbox-participante[data-id="' + participanteId + '"]');
                if (checkbox) {
                    checkbox.checked = true;
                    updateRowHighlight(participanteId, true);
                } else {
                    // Se nao esta na pagina visivel, reload
                    state.currentPage = 1;
                    await loadParticipantes();
                }
            }

            updateContadorSelecionados();
            updateResumo();
            updateCheckboxTodos();

        } catch (error) {
            console.error('Erro ao adicionar CNPJ:', error);
            showFeedbackCnpj('error', 'Erro de conexão. Tente novamente.');
        } finally {
            setBtnAdicionarLoading(false);
        }
    }

    /**
     * Mostra feedback do card de adicionar CNPJ.
     */
    function showFeedbackCnpj(type, message) {
        var el = elements.feedbackAdicionarCnpj;
        if (!el) return;

        el.classList.remove('hidden', 'bg-green-50', 'text-green-700', 'border-green-200',
            'bg-blue-50', 'text-blue-700', 'border-blue-200',
            'bg-red-50', 'text-red-700', 'border-red-200');

        if (type === 'success') {
            el.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-200');
        } else if (type === 'info') {
            el.classList.add('bg-blue-50', 'text-blue-700', 'border', 'border-blue-200');
        } else {
            el.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
        }

        el.textContent = message;
        el.classList.remove('hidden');

        // Auto-hide after 5s
        clearTimeout(el._hideTimeout);
        el._hideTimeout = setTimeout(function() { hideFeedbackCnpj(); }, 5000);
    }

    /**
     * Esconde feedback do card de adicionar CNPJ.
     */
    function hideFeedbackCnpj() {
        var el = elements.feedbackAdicionarCnpj;
        if (el) {
            el.classList.add('hidden');
            clearTimeout(el._hideTimeout);
        }
    }

    /**
     * Toggle loading state no botao de adicionar.
     */
    function setBtnAdicionarLoading(loading) {
        var btn = elements.btnAdicionarCnpj;
        if (!btn) return;

        if (loading) {
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Adicionando...';
            btn.classList.add('opacity-75');
        } else {
            btn.disabled = false;
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
            btn.classList.remove('opacity-75');
        }
    }

    // ==========================================
    // Inline Expansion de Participantes no tab Clientes
    // ==========================================

    /**
     * Escapa HTML para prevenir XSS ao inserir texto no DOM.
     */
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    /**
     * Alterna expansao inline de participantes de um cliente.
     */
    async function toggleClienteExpansion(clienteId, clienteLabel) {
        if (state.expandedClienteId === clienteId) {
            collapseClienteExpansion();
            return;
        }

        // Colapsar anterior se houver
        collapseClienteExpansion();

        state.expandedClienteId = clienteId;

        // Highlight a row e rotacionar chevron
        var clienteRow = elements.listaClientes?.querySelector('tr[data-cliente-id="' + clienteId + '"]');
        if (clienteRow) {
            clienteRow.classList.add('bg-blue-50');
            var chevron = clienteRow.querySelector('.chevron-icon');
            if (chevron) chevron.classList.add('rotate-90');
        }

        // Criar row de expansao com loading
        var expansionRow = document.createElement('tr');
        expansionRow.id = 'cliente-expansion-row';
        expansionRow.innerHTML = '<td colspan="4" class="bg-gray-50 border-y border-gray-200 px-5 py-4">'
            + '<div class="flex items-center justify-center gap-2 py-4 text-sm text-gray-500">'
            + '<svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
            + 'Carregando participantes...'
            + '</div>'
            + '</td>';

        if (clienteRow && clienteRow.parentNode) {
            clienteRow.parentNode.insertBefore(expansionRow, clienteRow.nextSibling);
        }

        // Fetch participantes do cliente
        try {
            var params = new URLSearchParams({
                cliente_id: clienteId,
                per_page: 50
            });

            var response = await fetch(window.consultaData.routes.getParticipantes + '?' + params, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Erro ao carregar participantes');

            var data = await response.json();

            // Verificar se ainda esta expandido (usuario pode ter clicado em outro)
            if (state.expandedClienteId !== clienteId) return;

            if (data.success) {
                renderClienteExpansionContent(clienteId, clienteLabel, data.data, data.pagination);
            } else {
                renderClienteExpansionError('Erro ao carregar participantes.');
            }
        } catch (error) {
            console.error('Erro ao carregar participantes do cliente:', error);
            if (state.expandedClienteId === clienteId) {
                renderClienteExpansionError('Erro ao carregar participantes. Tente novamente.');
            }
        }
    }

    /**
     * Colapsa a expansao inline de participantes.
     */
    function collapseClienteExpansion() {
        var expansionRow = document.getElementById('cliente-expansion-row');
        if (expansionRow) {
            expansionRow.remove();
        }

        // Resetar visual da row anterior
        if (state.expandedClienteId && elements.listaClientes) {
            var prevRow = elements.listaClientes.querySelector('tr[data-cliente-id="' + state.expandedClienteId + '"]');
            if (prevRow) {
                prevRow.classList.remove('bg-blue-50');
                var chevron = prevRow.querySelector('.chevron-icon');
                if (chevron) chevron.classList.remove('rotate-90');
            }
        }

        state.expandedClienteId = null;
    }

    /**
     * Renderiza conteudo do painel de expansao com participantes.
     */
    function renderClienteExpansionContent(clienteId, clienteLabel, participantes, pagination) {
        var expansionRow = document.getElementById('cliente-expansion-row');
        if (!expansionRow) return;

        var totalCount = pagination ? pagination.total : participantes.length;

        if (!participantes || participantes.length === 0) {
            expansionRow.innerHTML = '<td colspan="4" class="bg-gray-50 border-y border-gray-200 px-5 py-4">'
                + '<div class="flex items-center justify-between">'
                + '<span class="text-sm text-gray-500">Nenhum participante vinculado a este cliente.</span>'
                + '<button type="button" class="expansion-close text-gray-400 hover:text-gray-600 p-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>'
                + '</div>'
                + '</td>';

            expansionRow.querySelector('.expansion-close')?.addEventListener('click', collapseClienteExpansion);
            return;
        }

        // Verificar quantos estao selecionados deste cliente
        var selectedCount = participantes.filter(function(p) { return state.selectedIds.has(p.id); }).length;
        var allSelected = selectedCount === participantes.length;

        var html = '<td colspan="4" class="bg-gray-50 border-y border-gray-200 px-5 py-4">'
            // Header
            + '<div class="flex items-center justify-between mb-3">'
            + '<div class="flex items-center gap-3">'
            + '<span class="text-sm font-medium text-gray-700">Participantes de ' + escapeHtml(clienteLabel) + '</span>'
            + '<span class="text-xs text-gray-400">' + totalCount + ' encontrado' + (totalCount !== 1 ? 's' : '') + '</span>'
            + '</div>'
            + '<div class="flex items-center gap-3">'
            + '<button type="button" class="expansion-select-all text-xs font-medium text-blue-600 hover:text-blue-800 transition">'
            + (allSelected ? 'Desmarcar todos' : 'Selecionar todos')
            + '</button>'
            + '<button type="button" class="expansion-close text-gray-400 hover:text-gray-600 p-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>'
            + '</div>'
            + '</div>'
            // Lista de participantes
            + '<div class="max-h-80 overflow-y-auto space-y-0.5">';

        participantes.forEach(function(p) {
            var isSelected = state.selectedIds.has(p.id);
            var cnpjFormatado = formatCnpj(p.cnpj);

            html += '<label class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-white cursor-pointer transition'
                + (isSelected ? ' bg-white ring-1 ring-blue-200' : '') + '">'
                + '<input type="checkbox" class="expansion-checkbox w-4 h-4 text-blue-600 rounded border-gray-300" data-participante-id="' + p.id + '"'
                + (isSelected ? ' checked' : '') + '>'
                + '<span class="text-sm font-mono text-gray-600 tabular-nums whitespace-nowrap">' + cnpjFormatado + '</span>'
                + '<span class="text-sm text-gray-900 truncate">' + escapeHtml(p.razao_social || '-') + '</span>'
                + '</label>';
        });

        html += '</div>';

        // Footer com contagem
        if (totalCount > participantes.length) {
            html += '<div class="mt-2 text-xs text-gray-400 text-center">Mostrando ' + participantes.length + ' de ' + totalCount + '</div>';
        }

        html += '</td>';

        expansionRow.innerHTML = html;

        // Bind eventos nos checkboxes
        expansionRow.querySelectorAll('.expansion-checkbox').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var pid = parseInt(cb.dataset.participanteId);
                toggleParticipante(pid, cb.checked);

                // Atualizar visual do label
                var label = cb.closest('label');
                if (label) {
                    if (cb.checked) {
                        label.classList.add('bg-white', 'ring-1', 'ring-blue-200');
                    } else {
                        label.classList.remove('bg-white', 'ring-1', 'ring-blue-200');
                    }
                }

                // Atualizar texto do "Selecionar todos"
                updateExpansionSelectAllText(expansionRow, participantes);
            });
        });

        // Bind "Selecionar todos"
        var selectAllBtn = expansionRow.querySelector('.expansion-select-all');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                var currentlyAllSelected = participantes.every(function(p) { return state.selectedIds.has(p.id); });

                participantes.forEach(function(p) {
                    if (currentlyAllSelected) {
                        state.selectedIds.delete(p.id);
                    } else {
                        state.selectedIds.add(p.id);
                    }
                });

                updateContadorSelecionados();
                updateResumo();
                updateCheckboxTodos();

                // Re-render o conteudo para refletir o novo estado
                renderClienteExpansionContent(clienteId, clienteLabel, participantes, pagination);
            });
        }

        // Bind fechar
        expansionRow.querySelector('.expansion-close')?.addEventListener('click', collapseClienteExpansion);
    }

    /**
     * Atualiza o texto do botao "Selecionar todos" / "Desmarcar todos".
     */
    function updateExpansionSelectAllText(expansionRow, participantes) {
        var btn = expansionRow.querySelector('.expansion-select-all');
        if (!btn) return;
        var allSelected = participantes.every(function(p) { return state.selectedIds.has(p.id); });
        btn.textContent = allSelected ? 'Desmarcar todos' : 'Selecionar todos';
    }

    /**
     * Renderiza mensagem de erro no painel de expansao.
     */
    function renderClienteExpansionError(message) {
        var expansionRow = document.getElementById('cliente-expansion-row');
        if (!expansionRow) return;

        expansionRow.innerHTML = '<td colspan="4" class="bg-gray-50 border-y border-gray-200 px-5 py-4">'
            + '<div class="flex items-center justify-between">'
            + '<span class="text-sm text-red-500">' + escapeHtml(message) + '</span>'
            + '<button type="button" class="expansion-close text-gray-400 hover:text-gray-600 p-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>'
            + '</div>'
            + '</td>';

        expansionRow.querySelector('.expansion-close')?.addEventListener('click', collapseClienteExpansion);
    }

    // ==========================================
    // Client Selection (checkbox-based bulk selection)
    // ==========================================

    /**
     * Alterna selecao de um cliente (adiciona/remove todos seus participantes).
     */
    async function toggleClienteSelection(clienteId, checked) {
        try {
            var response = await fetch(window.consultaData.routes.participantesPorClientes, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.consultaData.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ cliente_ids: [clienteId] })
            });

            var data = await response.json();
            if (!data.success) return;

            var ids = data.ids || [];

            if (checked) {
                state.selectedClienteIds.add(clienteId);
                ids.forEach(function(id) { state.selectedIds.add(id); });
            } else {
                state.selectedClienteIds.delete(clienteId);
                ids.forEach(function(id) { state.selectedIds.delete(id); });
            }

            // Atualizar checkboxes visiveis na aba participantes
            document.querySelectorAll('.checkbox-participante').forEach(function(cb) {
                var pid = parseInt(cb.dataset.id);
                cb.checked = state.selectedIds.has(pid);
                updateRowHighlight(pid, cb.checked);
            });

            updateContadorSelecionados();
            updateResumo();
            updateCheckboxTodos();
            updateCheckboxTodosClientes();
        } catch (error) {
            console.error('Erro ao selecionar cliente:', error);
        }
    }

    /**
     * Alterna selecao de todos os clientes visiveis.
     */
    async function toggleTodosClientes() {
        var isChecked = elements.checkboxTodosClientes ? elements.checkboxTodosClientes.checked : false;

        // Coletar IDs de clientes visiveis no DOM (dedup: cada cliente tem 2 checkboxes mobile/desktop)
        var clienteIdSet = new Set();
        document.querySelectorAll('.checkbox-cliente').forEach(function(cb) {
            clienteIdSet.add(parseInt(cb.dataset.clienteId));
        });
        var clienteIds = Array.from(clienteIdSet);

        if (clienteIds.length === 0) return;

        try {
            var response = await fetch(window.consultaData.routes.participantesPorClientes, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.consultaData.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ cliente_ids: clienteIds })
            });

            var data = await response.json();
            if (!data.success) return;

            var ids = data.ids || [];

            if (isChecked) {
                clienteIds.forEach(function(cid) { state.selectedClienteIds.add(cid); });
                ids.forEach(function(id) { state.selectedIds.add(id); });
            } else {
                clienteIds.forEach(function(cid) { state.selectedClienteIds.delete(cid); });
                ids.forEach(function(id) { state.selectedIds.delete(id); });
            }

            // Atualizar checkboxes de clientes
            document.querySelectorAll('.checkbox-cliente').forEach(function(cb) {
                cb.checked = isChecked;
            });

            // Atualizar checkboxes de participantes visiveis
            document.querySelectorAll('.checkbox-participante').forEach(function(cb) {
                var pid = parseInt(cb.dataset.id);
                cb.checked = state.selectedIds.has(pid);
                updateRowHighlight(pid, cb.checked);
            });

            updateContadorSelecionados();
            updateResumo();
            updateCheckboxTodos();
        } catch (error) {
            console.error('Erro ao selecionar todos clientes:', error);
        }
    }

    /**
     * Atualiza estado do checkbox "todos clientes" (header).
     */
    function updateCheckboxTodosClientes() {
        if (!elements.checkboxTodosClientes) return;

        var checkboxes = document.querySelectorAll('.checkbox-cliente');
        if (checkboxes.length === 0) {
            elements.checkboxTodosClientes.checked = false;
            elements.checkboxTodosClientes.indeterminate = false;
            return;
        }

        var checkedCount = Array.from(checkboxes).filter(function(cb) { return cb.checked; }).length;

        if (checkedCount === 0) {
            elements.checkboxTodosClientes.checked = false;
            elements.checkboxTodosClientes.indeterminate = false;
        } else if (checkedCount === checkboxes.length) {
            elements.checkboxTodosClientes.checked = true;
            elements.checkboxTodosClientes.indeterminate = false;
        } else {
            elements.checkboxTodosClientes.checked = false;
            elements.checkboxTodosClientes.indeterminate = true;
        }
    }

    // ==========================================
    // Tab Switching (Participantes / Clientes / Grupos)
    // ==========================================

    function switchTab(tabName) {
        // Colapsar expansion ao sair do tab Clientes
        if (state.activeTab === 'clientes' && tabName !== 'clientes') {
            collapseClienteExpansion();
        }

        state.activeTab = tabName;

        // Atualizar visuais das abas
        elements.searchTabs?.forEach(function(t) {
            if (t.dataset.tab === tabName) {
                t.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                t.classList.remove('text-gray-500', 'hover:text-gray-700');
            } else {
                t.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                t.classList.add('text-gray-500', 'hover:text-gray-700');
            }
        });

        // Mostrar/esconder views
        document.querySelectorAll('.search-view').forEach(function(v) { v.classList.add('hidden'); });

        if (tabName === 'participantes') {
            if (elements.viewParticipantes) elements.viewParticipantes.classList.remove('hidden');
        } else if (tabName === 'clientes') {
            if (elements.viewClientes) elements.viewClientes.classList.remove('hidden');
            loadClientes();
        } else if (tabName === 'grupos') {
            if (elements.viewGrupos) elements.viewGrupos.classList.remove('hidden');
            loadGrupos();
        }
    }

    async function loadClientes() {
        var busca = elements.buscaClientes?.value || '';
        var params = new URLSearchParams();
        if (busca) params.append('busca', busca);

        try {
            var response = await fetch(window.consultaData.routes.getClientes + '?' + params, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            var data = await response.json();
            if (data.success) {
                renderClientes(data.data);
            }
        } catch (error) {
            console.error('Erro ao carregar clientes:', error);
            if (elements.listaClientes) {
                elements.listaClientes.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-red-500">Erro ao carregar clientes.</td></tr>';
            }
        }
    }

    function renderClientes(clientes) {
        if (!elements.listaClientes) return;

        // Rebuild do tbody colapsa a expansion naturalmente
        state.expandedClienteId = null;

        if (!clientes || clientes.length === 0) {
            elements.listaClientes.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Nenhum cliente encontrado.</td></tr>';
            updateCheckboxTodosClientes();
            return;
        }

        elements.listaClientes.innerHTML = '';

        clientes.forEach(function(c) {
            var isClienteSelected = state.selectedClienteIds.has(c.id);
            var tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 cursor-pointer transition flex flex-col gap-1 px-4 py-3 md:table-row md:px-0 md:py-0 md:gap-0' + (isClienteSelected ? ' bg-gray-50' : '');
            tr.dataset.clienteId = c.id;
            tr.dataset.clienteLabel = (c.razao_social || '').replace(/"/g, '&quot;');

            var cnpjFormatado = formatCnpj(c.documento);
            var tipoBadge = c.tipo_pessoa === 'PJ'
                ? '<span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-700">PJ</span>'
                : '<span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-purple-100 text-purple-700">PF</span>';
            var propriaDot = c.is_empresa_propria
                ? '<span class="shrink-0 w-2 h-2 rounded-full bg-green-500" title="Empresa propria"></span>'
                : '';
            var nomeTitle = (c.razao_social || '-') + (c.is_empresa_propria ? ' (Empresa propria)' : '');

            var chevronSvg = '<svg class="chevron-icon w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>';

            tr.innerHTML =
                '<td class="hidden md:table-cell md:w-10 md:px-4 md:py-3">'
                + '<input type="checkbox" class="checkbox-cliente w-4 h-4 text-gray-600 rounded border-gray-300" data-cliente-id="' + c.id + '"' + (isClienteSelected ? ' checked' : '') + '>'
                + '</td>'
                + '<td class="block overflow-hidden md:table-cell md:w-40 md:px-4 md:py-3">'
                + '<div class="flex items-center gap-2">'
                + '<input type="checkbox" class="checkbox-cliente w-4 h-4 text-gray-600 rounded border-gray-300 md:hidden flex-shrink-0" data-cliente-id="' + c.id + '"' + (isClienteSelected ? ' checked' : '') + '>'
                + '<span class="text-sm font-mono text-gray-600 whitespace-nowrap tabular-nums">' + cnpjFormatado + '</span>'
                + '</div>'
                + '</td>'
                + '<td class="block overflow-hidden md:table-cell md:px-4 md:py-3 md:max-w-0">'
                + '<div class="flex items-center gap-2 min-w-0" title="' + nomeTitle.replace(/"/g, '&quot;') + '">'
                + propriaDot
                + '<div class="text-sm font-medium text-gray-900 truncate min-w-0">' + (c.razao_social || '-') + '</div>'
                + tipoBadge
                + chevronSvg
                + '</div>'
                + (c.nome ? '<div class="text-xs text-gray-500 truncate">' + c.nome + '</div>' : '')
                + '</td>'
                + '<td class="block md:table-cell md:w-36 md:px-4 md:py-3 text-sm text-gray-500 md:whitespace-nowrap">' + c.participantes_count + ' participante' + (c.participantes_count !== 1 ? 's' : '') + '</td>';

            // Checkbox click: select/deselect all participantes of this client (desktop + mobile)
            var clienteCheckboxes = tr.querySelectorAll('.checkbox-cliente');
            clienteCheckboxes.forEach(function(cb) {
                cb.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent row click (expansion)
                });
                cb.addEventListener('change', function() {
                    clienteCheckboxes.forEach(function(other) { other.checked = cb.checked; });
                    toggleClienteSelection(c.id, cb.checked);
                });
            });

            // Row click (outside checkbox): expand/collapse inline
            tr.addEventListener('click', function(e) {
                // Don't expand if clicking the checkbox cell
                if (e.target.closest('.checkbox-cliente')) return;
                toggleClienteExpansion(c.id, c.razao_social || '');
            });

            elements.listaClientes.appendChild(tr);
        });

        updateCheckboxTodosClientes();
    }

    async function loadGrupos() {
        try {
            var response = await fetch(window.consultaData.routes.getGrupos, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            var data = await response.json();
            if (data.success) {
                renderGrupos(data.data);
            }
        } catch (error) {
            console.error('Erro ao carregar grupos:', error);
            if (elements.listaGrupos) {
                elements.listaGrupos.innerHTML = '<div class="px-5 py-8 text-center text-sm text-red-500">Erro ao carregar grupos.</div>';
            }
        }
    }

    function renderGrupos(grupos) {
        if (!elements.listaGrupos) return;

        if (!grupos || grupos.length === 0) {
            elements.listaGrupos.innerHTML = '<div class="px-5 py-8 text-center text-sm text-gray-500">Nenhum grupo criado.</div>';
            return;
        }

        elements.listaGrupos.innerHTML = grupos.map(function(g) {
            var cor = g.cor || '#3B82F6';
            return '<button type="button" class="w-full px-5 py-3 flex items-center justify-between gap-3 hover:bg-gray-50 transition text-left grupo-item" data-grupo-id="' + g.id + '" data-grupo-label="' + (g.nome || '').replace(/"/g, '&quot;') + '">'
                + '<div class="flex items-center gap-2.5 min-w-0">'
                + '<span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: ' + cor + '"></span>'
                + '<span class="text-sm font-medium text-gray-900 truncate">' + (g.nome || '-') + '</span>'
                + '</div>'
                + '<span class="text-xs text-gray-400 flex-shrink-0 whitespace-nowrap">' + g.participantes_count + ' participante' + (g.participantes_count !== 1 ? 's' : '') + '</span>'
                + '</button>';
        }).join('');

        // Bind click events
        elements.listaGrupos.querySelectorAll('.grupo-item').forEach(function(item) {
            item.addEventListener('click', function() {
                var grupoId = parseInt(item.dataset.grupoId);
                var label = item.dataset.grupoLabel;
                setFilterContext('grupo', grupoId, label);
            });
        });
    }

    function setFilterContext(type, id, label) {
        // Clientes agora usam inline expansion; apenas grupos passam por aqui
        if (type !== 'grupo') return;

        state.filterContext = { type: type, id: id, label: label };

        state.filters.grupo_id = id;
        state.filters.cliente_id = '';

        state.currentPage = 1;

        // Voltar para aba participantes com barra de contexto
        switchTab('participantes');
        showFilterContext(label, type);
        loadParticipantes();
    }

    function showFilterContext(label, type) {
        if (elements.participantesContext) {
            elements.participantesContext.classList.remove('hidden');
        }
        if (elements.filterContextLabel) {
            var prefix = type === 'cliente' ? 'Cliente:' : 'Grupo:';
            elements.filterContextLabel.textContent = prefix + ' ' + label;
        }
    }

    function clearFilterContext() {
        state.filterContext = null;
        state.filters.cliente_id = '';
        state.filters.grupo_id = '';
        state.currentPage = 1;

        if (elements.participantesContext) {
            elements.participantesContext.classList.add('hidden');
        }

        loadParticipantes();
    }

    // ==========================================
    // Modal Carousel de Planos (Lote)
    // ==========================================
    var swiperPlanosLote = null;
    var modalPlanosLote = null;

    function initCarouselPlanos() {
        modalPlanosLote = document.getElementById('modal-planos-carousel-lote');
        if (!modalPlanosLote) return;

        var planosData = window.consultaData?.planosDetalhados || [];
        var totalPlanos = planosData.length;
        if (totalPlanos === 0) return;

        // Precompute footer button color classes from corClasses
        var corClassesData = window.consultaData?.corClasses || {};
        var footerBtnColors = planosData.map(function(pd) {
            var cc = corClassesData[pd.cor];
            return cc ? cc.btn : 'bg-blue-600 hover:bg-blue-700';
        });
        var allFooterBtnClasses = ['bg-green-600', 'hover:bg-green-700', 'bg-blue-600', 'hover:bg-blue-700', 'bg-purple-600', 'hover:bg-purple-700', 'bg-amber-600', 'hover:bg-amber-700', 'bg-slate-700', 'hover:bg-slate-800'];

        function showPlanosModalLote(startIndex) {
            if (!modalPlanosLote) return;
            modalPlanosLote.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            setTimeout(function() {
                if (swiperPlanosLote && !swiperPlanosLote.destroyed) {
                    swiperPlanosLote.slideToLoop(startIndex || 0, 0);
                    swiperPlanosLote.update();
                    updateCounterLote(startIndex || 0);
                    updateFooterButtonLote(startIndex || 0);
                    return;
                }

                swiperPlanosLote = new Swiper('#swiper-planos-lote', {
                    slidesPerView: 1,
                    spaceBetween: 0,
                    loop: true,
                    initialSlide: startIndex || 0,
                    navigation: {
                        prevEl: '#swiper-planos-prev-lote',
                        nextEl: '#swiper-planos-next-lote',
                    },
                    pagination: {
                        el: '#swiper-planos-pagination-lote',
                        clickable: true,
                    },
                    on: {
                        slideChange: function() {
                            updateCounterLote(this.realIndex);
                            updateFooterButtonLote(this.realIndex);
                        },
                    },
                });

                updateCounterLote(startIndex || 0);
                updateFooterButtonLote(startIndex || 0);
            }, 50);
        }

        function hidePlanosModalLote() {
            if (!modalPlanosLote) return;
            modalPlanosLote.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function updateCounterLote(index) {
            var counter = document.getElementById('carousel-counter-lote');
            if (counter) {
                counter.textContent = (index + 1) + ' / ' + totalPlanos;
            }
        }

        function updateFooterButtonLote(index) {
            var btn = document.getElementById('btn-selecionar-plano-footer-lote');
            if (!btn) return;
            btn.dataset.planoIndex = index;
            allFooterBtnClasses.forEach(function(c) { btn.classList.remove(c); });
            var corStr = footerBtnColors[index] || 'bg-blue-600 hover:bg-blue-700';
            corStr.split(' ').forEach(function(c) { btn.classList.add(c); });
        }

        // Info button clicks -> open modal at specific slide
        document.querySelectorAll('.btn-info-plano-lote').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(this.dataset.slideIndex) || 0;
                showPlanosModalLote(idx);
            });
        });

        // "Ver detalhes" header button
        var btnVerDetalhes = document.getElementById('btn-ver-detalhes-planos-lote');
        if (btnVerDetalhes) {
            btnVerDetalhes.addEventListener('click', function() {
                showPlanosModalLote(0);
            });
        }

        // Close modal: overlay click
        modalPlanosLote.addEventListener('click', function(e) {
            if (e.target === modalPlanosLote) {
                hidePlanosModalLote();
            }
        });

        // Close modal: ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modalPlanosLote && !modalPlanosLote.classList.contains('hidden')) {
                hidePlanosModalLote();
            }
        });

        // Close modal: X button
        var btnFechar = document.getElementById('btn-fechar-carousel-lote');
        if (btnFechar) {
            btnFechar.addEventListener('click', hidePlanosModalLote);
        }

        // "Selecionar este plano" footer button
        var btnSelecionarFooter = document.getElementById('btn-selecionar-plano-footer-lote');
        if (btnSelecionarFooter) {
            btnSelecionarFooter.addEventListener('click', function() {
                var idx = parseInt(this.dataset.planoIndex) || 0;
                var pd = planosData[idx];
                if (!pd) return;

                // Find the matching plano_id from window.consultaData.planos
                var planoId = null;
                var planosMap = window.consultaData?.planos || {};
                for (var id in planosMap) {
                    if (planosMap[id].codigo === pd.codigo) {
                        planoId = id;
                        break;
                    }
                }

                if (planoId) {
                    var radio = document.querySelector('input[name="plano_id"][value="' + planoId + '"]');
                    if (radio) {
                        radio.checked = true;
                        updatePlanoStyles();
                        updateConsultasIncluidas();
                        updateResumo();
                    }
                }

                hidePlanosModalLote();
            });
        }
    }

    // Expor funcao de inicializacao para SPA
    window.initConsultaLote = function() {
        // Debounce: prevent double-init from SPA + inline auto-init
        var now = Date.now();
        if (window._consultaLoteLastInit && (now - window._consultaLoteLastInit) < 100) return;
        window._consultaLoteLastInit = now;

        // Reset state for SPA re-navigation
        state.selectedIds = new Set();
        state.selectedClienteIds = new Set();
        state.currentPage = 1;
        state.totalPages = 1;
        state.totalItems = 0;
        state.allIdsCurrentFilter = [];
        state.filters = { grupo_id: '', cliente_id: '', origem_tipo: '', busca: '' };
        state.activeTab = 'participantes';
        state.filterContext = null;
        state.expandedClienteId = null;
        voltarParaFormulario();
        state.credits = window.consultaData?.credits || 0;

        init();
        initCarouselPlanos();
    };

    window.reloadParticipantes = function() {
        loadParticipantes();
    };
})();
