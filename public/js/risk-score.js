/**
 * Risk Score - JavaScript
 * Gerencia a interface de score de risco
 */

(function() {
    'use strict';

    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Inicializacao
    function init() {
        setupFilters();
        setupConsultarButtons();
        setupDetalheButtons();
    }

    // "Ver detalhes" inline por CNPJ (mesmo conteúdo da Consulta de CNPJ), carregado sob demanda.
    function setupDetalheButtons() {
        document.querySelectorAll('[data-detalhe-url]').forEach(btn => {
            btn.addEventListener('click', () => toggleDetalhe(btn));
        });
    }

    async function toggleDetalhe(btn) {
        const url = btn.dataset.detalheUrl;
        const target = document.getElementById(btn.dataset.detalheTarget);
        if (!url || !target) return;

        if (!target.classList.contains('hidden')) {
            target.classList.add('hidden');
            btn.textContent = 'Ver detalhes';
            return;
        }
        target.classList.remove('hidden');
        btn.textContent = 'Ocultar detalhes';

        const content = target.querySelector('.detalhe-content');
        if (!content || content.dataset.loaded) return;
        content.innerHTML = '<div class="text-xs text-gray-500 py-3">Carregando…</div>';
        try {
            const resp = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await resp.json();
            content.innerHTML = data.html || '<div class="text-xs text-gray-500 py-3">Sem detalhe.</div>';
            content.dataset.loaded = '1';
            setupInlineDetalheToggles(content);
        } catch (err) {
            content.innerHTML = '<div class="text-xs text-red-600 py-3">Erro ao carregar detalhe.</div>';
        }
    }

    function setupInlineDetalheToggles(root) {
        root.querySelectorAll('[data-detalhe-toggle]').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.getElementById(btn.getAttribute('data-detalhe-toggle'));
                if (!target) return;

                const isHidden = target.classList.toggle('hidden');
                btn.setAttribute('aria-expanded', isHidden ? 'false' : 'true');

                const chevron = btn.querySelector('.detalhe-chevron');
                if (chevron) {
                    chevron.style.transform = isHidden ? '' : 'rotate(90deg)';
                }
            });
        });
    }

    // Configura filtros
    function setupFilters() {
        const filtroCliente = document.getElementById('filtro-cliente');
        const filtroClassificacao = document.getElementById('filtro-classificacao');
        const filtroStatus = document.getElementById('filtro-status-score');
        const filtroTipo = document.getElementById('filtro-tipo-score');
        const filtroCredito = document.getElementById('filtro-credito-score');
        const scoreMin = document.getElementById('score-min');
        const scoreMax = document.getElementById('score-max');
        const buscaParticipante = document.getElementById('busca-participante');

        [filtroCliente, filtroClassificacao, filtroStatus, filtroTipo, filtroCredito].forEach(field => {
            if (!field) return;
            field.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); applyFilters(); }
            });
        });

        if (buscaParticipante) {
            buscaParticipante.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); applyFilters(); }
            });
        }

        [scoreMin, scoreMax].forEach(field => {
            if (!field) return;
            field.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); applyFilters(); }
            });
        });

        const btnFiltrar = document.getElementById('btn-filtrar-score');
        if (btnFiltrar) {
            btnFiltrar.addEventListener('click', function() {
                applyFilters();
            });
        }

        const btnLimpar = document.getElementById('btn-limpar-filtros-score');
        if (btnLimpar) {
            btnLimpar.addEventListener('click', function() {
                clearFilters();
            });
        }
    }

    // Aplica filtros via navegacao
    function applyFilters() {
        const cliente = document.getElementById('filtro-cliente')?.value || '';
        const classificacao = document.getElementById('filtro-classificacao')?.value || 'todos';
        const status = document.getElementById('filtro-status-score')?.value || 'todos';
        const tipo = document.getElementById('filtro-tipo-score')?.value || 'todos';
        const credito = document.getElementById('filtro-credito-score')?.value || 'todos';
        const scoreMin = document.getElementById('score-min')?.value || '';
        const scoreMax = document.getElementById('score-max')?.value || '';
        const busca = document.getElementById('busca-participante')?.value || '';

        const params = new URLSearchParams();
        // Visualizacao por cliente e obrigatoria — sempre enviada (id ou "todos").
        if (cliente) params.append('cliente_id', cliente);
        if (status !== 'todos') params.append('status', status);
        if (tipo !== 'todos') params.append('tipo', tipo);
        if (classificacao !== 'todos') params.append('classificacao', classificacao);
        if (credito !== 'todos') params.append('credito', credito);
        if (scoreMin) params.append('score_min', scoreMin);
        if (scoreMax) params.append('score_max', scoreMax);
        if (busca) params.append('busca', busca);

        const url = '/app/score-fiscal' + (params.toString() ? '?' + params.toString() : '');

        // Usa o SPA router se disponivel
        if (window.spaNavigate) {
            window.spaNavigate(url);
        } else {
            window.location.href = url;
        }
    }

    function clearFilters() {
        const cliente = document.getElementById('filtro-cliente');
        const params = new URLSearchParams();
        params.append('cliente_id', cliente?.querySelector('option[value="todos"]') ? 'todos' : (cliente?.value || ''));

        const url = '/app/score-fiscal?' + params.toString();

        if (window.spaNavigate) {
            window.spaNavigate(url);
        } else {
            window.location.href = url;
        }
    }

    // Configura botoes de consultar
    function setupConsultarButtons() {
        // Botoes na lista
        document.querySelectorAll('.btn-consultar').forEach(btn => {
            btn.addEventListener('click', function() {
                const participanteId = this.dataset.id;
                consultarScore(participanteId, this);
            });
        });

        // Botao na pagina de detalhes
        const btnConsultar = document.getElementById('btn-consultar');
        if (btnConsultar) {
            btnConsultar.addEventListener('click', function() {
                const participanteId = this.dataset.id;
                consultarScore(participanteId, this);
            });
        }
    }

    // Consulta o score de um participante
    async function consultarScore(participanteId, buttonElement) {
        if (!participanteId) return;

        // Desabilita botao e mostra loading
        const originalText = buttonElement.textContent;
        buttonElement.disabled = true;
        buttonElement.textContent = 'Consultando...';

        try {
            const response = await fetch(`/app/score-fiscal/participante/${participanteId}/consultar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast('Score atualizado com sucesso!', 'success');

                // Recarrega a pagina para mostrar os novos dados
                if (window.spaNavigate) {
                    window.spaNavigate(window.location.pathname + window.location.search);
                } else {
                    window.location.reload();
                }
            } else {
                showToast(data.message || 'Erro ao consultar score', 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            showToast('Erro ao consultar score', 'error');
        } finally {
            buttonElement.disabled = false;
            buttonElement.textContent = originalText;
        }
    }

    // Mostra notificacao toast
    function showToast(message, type = 'info') {
        // Usa o sistema de toast global se disponivel
        if (window.showToast) {
            window.showToast(message, type);
            return;
        }

        // Fallback simples
        const container = document.getElementById('toast-container') || createToastContainer();

        const toast = document.createElement('div');
        toast.className = `px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium animate-fade-in ${
            type === 'success' ? 'bg-green-600' :
            type === 'error' ? 'bg-red-600' :
            type === 'warning' ? 'bg-yellow-600' :
            'bg-blue-600'
        }`;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
        return container;
    }

    // Inicializa quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expoe funcao de inicializacao para SPA
    window.initRiskScore = init;
})();
