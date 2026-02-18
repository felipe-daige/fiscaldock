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

    // Consultas gratuitas (Minha Receita)
    const CONSULTAS_GRATUITAS = [
        'situacao_cadastral', 'dados_cadastrais', 'endereco',
        'cnaes', 'qsa', 'simples_nacional', 'mei'
    ];

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
        credits: window.consultaData?.credits || 0
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
            btnFecharErro: document.getElementById('btn-fechar-erro'),

            // Adicionar CNPJ
            inputAdicionarCnpj: document.getElementById('input-adicionar-cnpj'),
            selectClienteCnpj: document.getElementById('select-cliente-cnpj'),
            btnAdicionarCnpj: document.getElementById('btn-adicionar-cnpj'),
            feedbackAdicionarCnpj: document.getElementById('feedback-adicionar-cnpj'),
            radioTipoParticipante: document.getElementById('radio-tipo-participante'),
            radioTipoCliente: document.getElementById('radio-tipo-cliente'),
            containerSelectCliente: document.getElementById('container-select-cliente'),

            // Modal Excluir
            modalExcluir: document.getElementById('modal-excluir-participante'),
            modalExcluirOverlay: document.getElementById('modal-excluir-overlay'),
            modalExcluirCnpj: document.getElementById('modal-excluir-cnpj'),
            modalExcluirNome: document.getElementById('modal-excluir-nome'),
            btnCancelarExclusao: document.getElementById('btn-cancelar-exclusao'),
            btnConfirmarExclusao: document.getElementById('btn-confirmar-exclusao')
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

        // Radio tipo cadastro (Participante/Cliente)
        if (elements.radioTipoParticipante) {
            elements.radioTipoParticipante.addEventListener('change', toggleSelectCliente);
        }
        if (elements.radioTipoCliente) {
            elements.radioTipoCliente.addEventListener('change', toggleSelectCliente);
        }

        // Modal Excluir
        if (elements.btnCancelarExclusao) elements.btnCancelarExclusao.addEventListener('click', hideDeleteModal);
        if (elements.btnConfirmarExclusao) elements.btnConfirmarExclusao.addEventListener('click', confirmarExclusao);
        if (elements.modalExcluirOverlay) elements.modalExcluirOverlay.addEventListener('click', hideDeleteModal);

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
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
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
            const escNome = (p.razao_social || '').replace(/"/g, '&quot;');

            tr.innerHTML = `
                <td class="w-10 px-4 py-3">
                    <input type="checkbox" class="checkbox-participante w-4 h-4 text-gray-600 rounded border-gray-300" data-id="${p.id}" ${isSelected ? 'checked' : ''}>
                </td>
                <td class="w-40 px-4 py-3 text-sm font-mono text-gray-600 whitespace-nowrap tabular-nums">${cnpjFormatado}</td>
                <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900">${p.razao_social || '-'}</div>
                    ${p.nome_fantasia ? `<div class="text-xs text-gray-500">${p.nome_fantasia}</div>` : ''}
                </td>
                <td class="w-16 px-4 py-3 text-sm text-gray-600 text-center">${p.uf || '-'}</td>
                <td class="w-12 px-2 py-3 text-center">
                    <button type="button" class="btn-excluir-participante p-1 text-gray-400 hover:text-red-500 transition rounded" data-id="${p.id}" data-cnpj="${cnpjFormatado}" data-nome="${escNome}" title="Excluir">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            `;

            // Evento de checkbox
            const checkbox = tr.querySelector('.checkbox-participante');
            checkbox.addEventListener('change', () => toggleParticipante(p.id, checkbox.checked));

            // Evento de excluir
            const btnExcluir = tr.querySelector('.btn-excluir-participante');
            btnExcluir.addEventListener('click', function() {
                showDeleteModal(parseInt(this.dataset.id), this.dataset.cnpj, this.dataset.nome);
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
            const response = await fetch(`${window.consultaData.routes.getParticipantes}?${params}`, {
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
            const isGratuita = CONSULTAS_GRATUITAS.includes(consulta);
            const badge = isGratuita
                ? '<span class="px-1.5 py-0.5 bg-green-50 text-green-600 text-[10px] font-medium rounded">Gratis</span>'
                : '<span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-medium rounded">1 cr</span>';

            return `<div class="flex items-center gap-2 py-1">
                ${checkSvg}
                <span class="text-xs text-gray-700 flex-1">${nome}</span>
                ${badge}
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
                console.error('Consulta Lote erro:', errorMsg, data);
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

        const url = `${window.consultaData.routes.progressoStream}?tab_id=${state.tabId}`;
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
            const downloadUrl = window.consultaData.routes.baixarLote.replace('{id}', state.consultaLoteId);
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
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-red-500">
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

        // Determinar tipo de cadastro (participante ou cliente)
        var isCliente = elements.radioTipoCliente?.checked || false;
        var clienteId = null;
        var criarCliente = false;

        if (isCliente) {
            criarCliente = true;
            var selectVal = elements.selectClienteCnpj?.value || 'novo';
            clienteId = selectVal; // 'novo' ou ID numerico
        }

        hideFeedbackCnpj();
        setBtnAdicionarLoading(true);

        try {
            var bodyPayload = { cnpj: cnpj };
            if (criarCliente) {
                bodyPayload.criar_cliente = true;
                bodyPayload.cliente_id = clienteId === 'novo' ? 'novo' : parseInt(clienteId);
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
    // Toggle Select Cliente (Radio)
    // ==========================================

    function toggleSelectCliente() {
        if (!elements.containerSelectCliente) return;
        var isCliente = elements.radioTipoCliente?.checked || false;
        elements.containerSelectCliente.classList.toggle('hidden', !isCliente);
    }

    // ==========================================
    // Excluir Participante (Modal + Delete)
    // ==========================================

    var deleteParticipanteId = null;

    function showDeleteModal(id, cnpj, nome) {
        deleteParticipanteId = id;
        if (elements.modalExcluirCnpj) elements.modalExcluirCnpj.textContent = cnpj;
        if (elements.modalExcluirNome) elements.modalExcluirNome.textContent = nome || '';
        if (elements.modalExcluir) elements.modalExcluir.classList.remove('hidden');
    }

    function hideDeleteModal() {
        deleteParticipanteId = null;
        if (elements.modalExcluir) elements.modalExcluir.classList.add('hidden');
    }

    async function confirmarExclusao() {
        if (!deleteParticipanteId) return;

        var id = deleteParticipanteId;
        var btn = elements.btnConfirmarExclusao;
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Excluindo...';
        }

        try {
            var response = await fetch('/app/monitoramento/participante/' + id, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.consultaData.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            var data;
            try {
                data = await response.json();
            } catch (e) {
                data = {};
            }

            if (response.ok && (data.success !== false)) {
                // Remove da selecao
                state.selectedIds.delete(id);
                updateContadorSelecionados();
                updateResumo();

                // Reload tabela
                await loadParticipantes();
                updateCheckboxTodos();
            } else {
                alert(data.error || data.message || 'Erro ao excluir participante.');
            }
        } catch (error) {
            console.error('Erro ao excluir participante:', error);
            alert('Erro de conexao. Tente novamente.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Excluir';
            }
            hideDeleteModal();
        }
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
        state.currentPage = 1;
        state.totalPages = 1;
        state.totalItems = 0;
        state.allIdsCurrentFilter = [];
        state.filters = { grupo_id: '', cliente_id: '', origem_tipo: '', busca: '' };
        state.consultaLoteId = null;
        state.credits = window.consultaData?.credits || 0;

        // Close any existing SSE connection
        if (state.eventSource) {
            state.eventSource.close();
            state.eventSource = null;
        }

        init();
        initCarouselPlanos();
    };

    window.reloadParticipantes = function() {
        loadParticipantes();
    };
})();
