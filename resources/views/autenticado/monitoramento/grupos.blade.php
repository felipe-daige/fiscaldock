{{-- Monitoramento - Grupos de Participantes --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-grupos-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <style>
            @keyframes card-slide-in {
                from { opacity: 0; transform: translateY(60px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .grp-animate {
                opacity: 0;
                animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .grp-animate { opacity: 1; animation: none; }
            }
        </style>

        {{-- Page Header --}}
        <div class="mb-4 sm:mb-8 grp-animate">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Grupos de Participantes</h1>
                    <p class="mt-1 text-sm text-gray-500">Organize seus participantes em grupos para facilitar a gestao.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        href="/app/dashboard"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                        data-link
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar
                    </a>
                    <button
                        type="button"
                        id="btn-criar-grupo"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Novo Grupo
                    </button>
                </div>
            </div>
        </div>

        {{-- Lista de Grupos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 grp-animate" style="animation-delay: 0.1s">
            @forelse($grupos ?? [] as $grupo)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow" data-grupo-id="{{ $grupo->id }}">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-4 h-4 rounded-full shrink-0"
                                    style="background-color: {{ $grupo->cor ?? '#3B82F6' }}"
                                ></div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $grupo->nome }}</h3>
                            </div>
                            @if($grupo->is_auto)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
                                    Auto
                                </span>
                            @endif
                        </div>

                        @if($grupo->descricao)
                            <p class="text-sm text-gray-600 mb-4">{{ $grupo->descricao }}</p>
                        @endif

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>{{ $grupo->participantes_count ?? 0 }} participante(s)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="btn-editar-grupo inline-flex items-center p-2 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                    data-grupo-id="{{ $grupo->id }}"
                                    data-grupo-nome="{{ $grupo->nome }}"
                                    data-grupo-cor="{{ $grupo->cor }}"
                                    data-grupo-descricao="{{ $grupo->descricao }}"
                                    title="Editar grupo"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="btn-excluir-grupo inline-flex items-center p-2 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors"
                                    data-grupo-id="{{ $grupo->id }}"
                                    data-grupo-nome="{{ $grupo->nome }}"
                                    title="Excluir grupo"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum grupo criado</h3>
                        <p class="text-sm text-gray-600 mb-4">Crie grupos para organizar seus participantes por categoria.</p>
                        <button
                            type="button"
                            id="btn-criar-grupo-empty"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Criar Primeiro Grupo
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Modal Criar/Editar Grupo --}}
<div id="modal-grupo" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" id="modal-grupo-titulo">Novo Grupo</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <form id="form-grupo">
            <input type="hidden" name="grupo_id" id="input-grupo-id" value="">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Grupo *</label>
                    <input
                        type="text"
                        name="nome"
                        id="input-grupo-nome"
                        class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ex: Fornecedores, Clientes Premium..."
                        required
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cor do Badge</label>
                    <div class="flex flex-wrap gap-2" id="cores-grupo">
                        @foreach($coresPredefinidas ?? [] as $index => $cor)
                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    name="cor"
                                    value="{{ $cor }}"
                                    class="sr-only cor-radio"
                                    {{ $index === 0 ? 'checked' : '' }}
                                >
                                <span
                                    class="block w-8 h-8 rounded-full border-2 border-transparent transition-all hover:scale-110 cor-preview"
                                    style="background-color: {{ $cor }}"
                                    data-cor="{{ $cor }}"
                                ></span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descricao (opcional)</label>
                    <textarea
                        name="descricao"
                        id="input-grupo-descricao"
                        rows="2"
                        class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Descreva o proposito deste grupo..."
                    ></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                <button type="button" class="modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" id="btn-salvar-grupo" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initMonitoramentoGrupos() {
        const container = document.getElementById('monitoramento-grupos-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento Grupos] Inicializando...');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const modalGrupo = document.getElementById('modal-grupo');
        const formGrupo = document.getElementById('form-grupo');
        const modalTitulo = document.getElementById('modal-grupo-titulo');
        const inputGrupoId = document.getElementById('input-grupo-id');
        const inputGrupoNome = document.getElementById('input-grupo-nome');
        const inputGrupoDescricao = document.getElementById('input-grupo-descricao');
        const btnCriarGrupo = document.getElementById('btn-criar-grupo');
        const btnCriarGrupoEmpty = document.getElementById('btn-criar-grupo-empty');

        // Funcao para abrir modal de criar grupo
        function abrirModalCriar() {
            modalTitulo.textContent = 'Novo Grupo';
            inputGrupoId.value = '';
            inputGrupoNome.value = '';
            inputGrupoDescricao.value = '';

            // Selecionar primeira cor
            const primeiraCor = document.querySelector('.cor-radio');
            if (primeiraCor) primeiraCor.checked = true;
            atualizarCorSelecionada();

            modalGrupo.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            inputGrupoNome.focus();
        }

        // Funcao para atualizar visual da cor selecionada
        function atualizarCorSelecionada() {
            document.querySelectorAll('.cor-preview').forEach(function(el) {
                const radio = el.previousElementSibling;
                if (radio && radio.checked) {
                    el.classList.add('ring-2', 'ring-offset-2', 'ring-blue-500');
                } else {
                    el.classList.remove('ring-2', 'ring-offset-2', 'ring-blue-500');
                }
            });
        }

        // Event listeners para selecao de cor
        document.querySelectorAll('.cor-radio').forEach(function(radio) {
            radio.addEventListener('change', atualizarCorSelecionada);
        });

        // Botao criar grupo
        if (btnCriarGrupo) {
            btnCriarGrupo.addEventListener('click', abrirModalCriar);
        }
        if (btnCriarGrupoEmpty) {
            btnCriarGrupoEmpty.addEventListener('click', abrirModalCriar);
        }

        // Botoes editar grupo
        document.querySelectorAll('.btn-editar-grupo').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const grupoId = this.dataset.grupoId;
                const grupoNome = this.dataset.grupoNome;
                const grupoCor = this.dataset.grupoCor;
                const grupoDescricao = this.dataset.grupoDescricao || '';

                modalTitulo.textContent = 'Editar Grupo';
                inputGrupoId.value = grupoId;
                inputGrupoNome.value = grupoNome;
                inputGrupoDescricao.value = grupoDescricao;

                // Selecionar cor do grupo
                const corRadio = document.querySelector('.cor-radio[value="' + grupoCor + '"]');
                if (corRadio) {
                    corRadio.checked = true;
                }
                atualizarCorSelecionada();

                modalGrupo.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                inputGrupoNome.focus();
            });
        });

        // Botoes excluir grupo
        document.querySelectorAll('.btn-excluir-grupo').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const grupoId = this.dataset.grupoId;
                const grupoNome = this.dataset.grupoNome;

                if (!confirm('Tem certeza que deseja excluir o grupo "' + grupoNome + '"?\n\nOs participantes nao serao excluidos, apenas a associacao com o grupo.')) {
                    return;
                }

                try {
                    const response = await fetch('/app/monitoramento/grupos/' + grupoId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Erro ao excluir grupo');
                    }

                    window.showToast && window.showToast(data.message || 'Grupo excluido com sucesso!', 'success');

                    // Remover card do grupo
                    const card = document.querySelector('[data-grupo-id="' + grupoId + '"]');
                    if (card) {
                        card.remove();
                    }

                    // Se nao houver mais grupos, recarregar para mostrar estado vazio
                    if (document.querySelectorAll('[data-grupo-id]').length === 0) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 500);
                    }

                } catch (err) {
                    console.error('[Monitoramento Grupos] Erro:', err);
                    window.showToast && window.showToast(err.message || 'Erro ao excluir grupo', 'error');
                }
            });
        });

        // Submit form grupo
        if (formGrupo) {
            formGrupo.addEventListener('submit', async function(e) {
                e.preventDefault();

                const grupoId = inputGrupoId.value;
                const nome = inputGrupoNome.value.trim();
                const corRadio = document.querySelector('.cor-radio:checked');
                const cor = corRadio ? corRadio.value : '#3B82F6';
                const descricao = inputGrupoDescricao.value.trim();

                if (!nome) {
                    window.showToast && window.showToast('Nome do grupo e obrigatorio', 'error');
                    inputGrupoNome.focus();
                    return;
                }

                const submitBtn = document.getElementById('btn-salvar-grupo');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Salvando...';

                try {
                    const url = grupoId
                        ? '/app/monitoramento/grupos/' + grupoId
                        : '/app/monitoramento/grupos';

                    const method = grupoId ? 'PUT' : 'POST';

                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            nome: nome,
                            cor: cor,
                            descricao: descricao || null,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Erro ao salvar grupo');
                    }

                    window.showToast && window.showToast(data.message || 'Grupo salvo com sucesso!', 'success');
                    modalGrupo.classList.add('hidden');
                    document.body.style.overflow = '';

                    // Recarregar pagina para atualizar lista
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);

                } catch (err) {
                    console.error('[Monitoramento Grupos] Erro:', err);
                    window.showToast && window.showToast(err.message || 'Erro ao salvar grupo', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        }

        // Fechar modais
        document.querySelectorAll('.modal-close').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const modal = btn.closest('[id^="modal-"]');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });

        // Fechar modal clicando fora
        if (modalGrupo) {
            modalGrupo.addEventListener('click', function(e) {
                if (e.target === modalGrupo) {
                    modalGrupo.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        }

        // Inicializar visual das cores
        atualizarCorSelecionada();

        console.log('[Monitoramento Grupos] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initMonitoramentoGrupos = initMonitoramentoGrupos;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramentoGrupos, { once: true });
    } else {
        initMonitoramentoGrupos();
    }
})();
</script>
