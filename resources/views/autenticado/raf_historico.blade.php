{{-- Histórico de Relatórios RAF Pendentes --}}
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page title --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Histórico de Relatórios Pendentes</h1>
                    <p class="mt-1 text-sm text-gray-600">Visualize e gerencie seus relatórios RAF que aguardam confirmação de pagamento.</p>
                </div>
                <a 
                    href="/app/solucoes/raf" 
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                    data-link
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar para RAF
                </a>
            </div>
        </div>

        {{-- Estatísticas --}}
        @if($total_pendentes > 0)
        <div class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-semibold text-gray-700">Total de pendentes:</span>
                    <span class="text-lg font-bold text-amber-600">{{ $total_pendentes }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Lista de Relatórios --}}
        {{-- Debug: total_pendentes={{ $total_pendentes }}, relatorios_count={{ $relatorios->count() }} --}}
        @if($relatorios->count() > 0)
            <div class="space-y-4">
                @foreach($relatorios as $relatorio)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-md hover:shadow-lg transition-shadow" data-relatorio-id="{{ $relatorio->id }}">
                        <div class="p-6">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                {{-- Informações principais --}}
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tipo de EFD</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ $relatorio->tipo_efd }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tipo de Consulta</p>
                                        <p class="text-sm font-semibold text-gray-900">
                                            @if($relatorio->tipo_consulta === 'regime')
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                                    Gratuita — Regime Tributário
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700">
                                                    Completa — Regime + CND
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Participantes</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ number_format($relatorio->qtd_participantes, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Valor Total</p>
                                        <p class="text-lg font-bold text-amber-600">
                                            R$ {{ number_format($relatorio->valor_total_consulta, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Ações --}}
                                <div class="flex flex-col sm:flex-row gap-2 lg:flex-shrink-0">
                                    <button
                                        type="button"
                                        class="raf-detalhes-btn inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                                        data-relatorio-id="{{ $relatorio->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Ver Detalhes
                                    </button>
                                    <button
                                        type="button"
                                        class="raf-confirmar-btn inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-semibold shadow-sm transition hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                        data-relatorio-id="{{ $relatorio->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="raf-confirmar-text">Confirmar e Pagar</span>
                                        <svg class="raf-confirmar-spinner hidden w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="raf-cancelar-btn inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-red-300 bg-white text-red-700 text-sm font-semibold shadow-sm transition hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        data-relatorio-id="{{ $relatorio->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        <span class="raf-cancelar-text">Cancelar</span>
                                        <svg class="raf-cancelar-spinner hidden w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Informações adicionais --}}
                            <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center gap-4">
                                    <span>Custo unitário: <strong class="text-gray-700">R$ {{ number_format($relatorio->custo_unitario, 2, ',', '.') }}</strong></span>
                                </div>
                                <span>Criado em: <strong class="text-gray-700">{{ $relatorio->created_at ? $relatorio->created_at->format('d/m/Y \à\s H:i') : 'N/A' }}</strong></span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Estado vazio --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Nenhum relatório pendente</h3>
                <p class="mt-2 text-sm text-gray-600">Você não possui relatórios aguardando confirmação de pagamento.</p>
                <div class="mt-6">
                    <a 
                        href="/app/solucoes/raf" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700"
                        data-link
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Criar Novo Relatório
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modal de Detalhes --}}
<div id="raf-detalhes-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Detalhes do Relatório</h3>
                    <button type="button" class="raf-modal-close text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="raf-detalhes-content" class="space-y-4">
                    {{-- Conteúdo será preenchido via JavaScript --}}
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="raf-modal-close w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 sm:ml-3 sm:w-auto sm:text-sm">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Confirmação de Cancelamento --}}
<div id="raf-cancelar-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="cancelar-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg font-semibold text-gray-900" id="cancelar-modal-title">Confirmar Cancelamento</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Tem certeza que deseja cancelar este relatório? Esta ação não pode ser desfeita.</p>
                        </div>
                    </div>
                    <button type="button" class="raf-cancelar-modal-close text-gray-400 hover:text-gray-500 ml-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="raf-cancelar-confirm-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Confirmar
                </button>
                <button type="button" class="raf-cancelar-modal-close mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Função auxiliar para remover card com animação e atualizar contador
    const removerCard = (relatorioIdOrCard) => {
        // Se receber relatorioId (string ou number), buscar o card no DOM
        let card = null;
        if (typeof relatorioIdOrCard === 'string' || typeof relatorioIdOrCard === 'number') {
            const relatorioId = String(relatorioIdOrCard);
            // Usar seletor direto pelo atributo data-relatorio-id
            card = document.querySelector(`[data-relatorio-id="${relatorioId}"]`);
            
            // Se não encontrou, tentar com seletor mais específico incluindo classes
            if (!card) {
                card = document.querySelector(`div[data-relatorio-id="${relatorioId}"]`);
            }
        } else {
            // Se receber o elemento diretamente, usar ele
            card = relatorioIdOrCard;
        }

        if (!card) {
            console.warn('Card não encontrado para remoção:', relatorioIdOrCard);
            return;
        }

        // Garantir que o card existe no DOM
        if (!card.parentNode) {
            console.warn('Card já foi removido do DOM');
            return;
        }

        // Aplicar animação de fade out
        card.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out, max-height 0.3s ease-out';
        card.style.opacity = '0';
        card.style.transform = 'translateY(-10px)';
        card.style.overflow = 'hidden';
        const originalHeight = card.offsetHeight;
        card.style.maxHeight = originalHeight + 'px';

        // Forçar reflow para garantir que a animação comece
        void card.offsetHeight;

        // Remover do DOM após animação
        setTimeout(() => {
            // Verificar novamente se o card ainda existe no DOM antes de remover
            if (card && card.parentNode) {
                try {
                    card.remove();
                } catch (err) {
                    // Se remove() falhar, tentar removeChild
                    if (card.parentNode) {
                        card.parentNode.removeChild(card);
                    }
                }
            }

            // Atualizar contador de pendentes - contar apenas cards principais (divs), não botões
            // Filtrar apenas os cards principais (não botões ou outros elementos)
            const allCards = document.querySelectorAll('[data-relatorio-id]');
            const cardsRestantes = Array.from(allCards).filter(el => {
                // Verificar se é um card principal (div com classes bg-white rounded-xl)
                return el.tagName === 'DIV' && el.classList.contains('bg-white') && el.classList.contains('rounded-xl');
            });
            const totalPendentes = cardsRestantes.length;

            // Atualizar o contador na interface se existir
            const contadorElement = document.querySelector('.text-lg.font-bold.text-amber-600');
            if (contadorElement) {
                if (totalPendentes === 0) {
                    // Se não houver mais cards, recarregar a página para mostrar estado vazio
                    window.location.reload();
                } else {
                    contadorElement.textContent = totalPendentes;
                }
            } else if (totalPendentes === 0) {
                // Se não houver contador mas não houver mais cards, recarregar
                window.location.reload();
            }

            // Limpar cache e atualizar badge de pendentes na página principal RAF
            if (typeof window.updatePendentesBadge === 'function') {
                window.updatePendentesBadge();
            }
        }, 300);
    };

    // Modal de detalhes
    const detalhesModal = document.getElementById('raf-detalhes-modal');
    const detalhesContent = document.getElementById('raf-detalhes-content');
    const modalCloseBtns = document.querySelectorAll('.raf-modal-close');

    const openModal = () => {
        detalhesModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        detalhesModal.classList.add('hidden');
        document.body.style.overflow = '';
    };

    modalCloseBtns.forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    detalhesModal.addEventListener('click', (e) => {
        if (e.target === detalhesModal || e.target.classList.contains('bg-gray-500')) {
            closeModal();
        }
    });

    // Modal de confirmação de cancelamento
    const cancelarModal = document.getElementById('raf-cancelar-modal');
    const cancelarModalCloseBtns = document.querySelectorAll('.raf-cancelar-modal-close');
    const cancelarConfirmBtn = document.getElementById('raf-cancelar-confirm-btn');
    let pendingCancelData = null; // Armazena apenas relatorioId quando o modal é aberto

    const openCancelarModal = (relatorioId) => {
        pendingCancelData = { relatorioId };
        cancelarModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Configurar listener do botão de confirmação após abrir o modal
        setTimeout(setupConfirmButtonListener, 50);
    };

    const closeCancelarModal = () => {
        cancelarModal.classList.add('hidden');
        document.body.style.overflow = '';
        pendingCancelData = null;
    };

    cancelarModalCloseBtns.forEach(btn => {
        btn.addEventListener('click', closeCancelarModal);
    });

    cancelarModal.addEventListener('click', (e) => {
        if (e.target === cancelarModal || e.target.classList.contains('bg-gray-500')) {
            closeCancelarModal();
        }
    });

    // Botão Ver Detalhes
    document.querySelectorAll('.raf-detalhes-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const relatorioId = btn.dataset.relatorioId;
            
            try {
                const response = await fetch(`/app/solucoes/raf/detalhes/${relatorioId}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Erro ao buscar detalhes');
                }

                const result = await response.json();
                if (!result.success || !result.data) {
                    throw new Error('Dados não encontrados');
                }

                const data = result.data;
                const tipoConsultaLabel = data.tipo_consulta === 'regime' 
                    ? 'Gratuita — Regime Tributário' 
                    : 'Completa — Regime + CND';

                detalhesContent.innerHTML = `
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo de EFD</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">${data.tipo_efd}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo de Consulta</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">${tipoConsultaLabel}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Quantidade de Participantes</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">${data.qtd_participantes.toLocaleString('pt-BR')}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Custo Unitário</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">R$ ${data.custo_unitario.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Valor Total da Consulta</p>
                                <p class="text-lg font-bold text-amber-600 mt-1">R$ ${data.valor_total_consulta.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Data e Horário de Criação</p>
                                <p class="text-sm text-gray-700 mt-1">${data.created_at ? new Date(data.created_at).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                `;

                openModal();
            } catch (err) {
                console.error('Erro ao buscar detalhes:', err);
            }
        });
    });

    // Botão Confirmar
    document.querySelectorAll('.raf-confirmar-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const relatorioId = btn.dataset.relatorioId;
            const card = btn.closest('[data-relatorio-id]');
            const confirmarText = btn.querySelector('.raf-confirmar-text');
            const confirmarSpinner = btn.querySelector('.raf-confirmar-spinner');

            if (!card) {
                console.error('Card não encontrado para confirmação');
                return;
            }

            if (!confirm('Tem certeza que deseja confirmar e pagar este relatório?')) {
                return;
            }

            btn.disabled = true;
            confirmarText.classList.add('hidden');
            confirmarSpinner.classList.remove('hidden');

            try {
                const response = await fetch(`/app/solucoes/raf/confirmar/${relatorioId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json, text/csv',
                    },
                    credentials: 'same-origin',
                });

                const contentType = response.headers.get('content-type');

                if (response.status === 402) {
                    const data = await response.json();
                    console.error('Créditos insuficientes:', data.message);
                    btn.disabled = false;
                    confirmarText.classList.remove('hidden');
                    confirmarSpinner.classList.add('hidden');
                    return;
                }

                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    console.error('Erro ao confirmar:', data.message || `Erro ${response.status}`);
                    btn.disabled = false;
                    confirmarText.classList.remove('hidden');
                    confirmarSpinner.classList.add('hidden');
                    return;
                }

                // Se a resposta é CSV, fazer download
                if (contentType && contentType.includes('text/csv')) {
                    const blob = await response.blob();
                    const disposition = response.headers.get('content-disposition');
                    let filename = 'resultado.csv';
                    const match = disposition && disposition.match(/filename=\"?([^\";]+)\"?/i);
                    if (match && match[1]) {
                        filename = match[1];
                    }

                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);

                    // Remover card da lista usando função auxiliar - passar relatorioId
                    removerCard(relatorioId);
                } else {
                    console.error('Resposta inesperada do servidor:', contentType);
                    btn.disabled = false;
                    confirmarText.classList.remove('hidden');
                    confirmarSpinner.classList.add('hidden');
                }
            } catch (err) {
                console.error('Erro ao confirmar:', err);
                btn.disabled = false;
                confirmarText.classList.remove('hidden');
                confirmarSpinner.classList.add('hidden');
            }
        });
    });

    // Botão Cancelar - abre modal de confirmação
    document.querySelectorAll('.raf-cancelar-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const relatorioId = btn.dataset.relatorioId;
            if (!relatorioId) {
                console.error('Relatório ID não encontrado');
                return;
            }

            // Abre o modal de confirmação
            openCancelarModal(relatorioId);
        });
    });

    // Botão Confirmar do modal de cancelamento - usar listener direto no botão
    // Adicionar listener quando o modal é aberto para garantir que o botão existe
    const setupConfirmButtonListener = () => {
        const confirmBtn = document.getElementById('raf-cancelar-confirm-btn');
        if (!confirmBtn) return;
        
        // Remover listener anterior se existir (evitar múltiplos listeners)
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        // Adicionar listener no novo botão
        newConfirmBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (!pendingCancelData || !pendingCancelData.relatorioId) {
                console.error('Dados de cancelamento não encontrados');
                return;
            }

            const { relatorioId } = pendingCancelData;
            const btn = document.querySelector(`.raf-cancelar-btn[data-relatorio-id="${relatorioId}"]`);
            const cancelarText = btn?.querySelector('.raf-cancelar-text');
            const cancelarSpinner = btn?.querySelector('.raf-cancelar-spinner');

            // Fecha o modal imediatamente
            closeCancelarModal();

            // Remover card imediatamente da interface (sem depender da resposta do servidor)
            removerCard(relatorioId);

            // Desabilita o botão e mostra spinner
            if (btn) {
                btn.disabled = true;
                if (cancelarText) cancelarText.classList.add('hidden');
                if (cancelarSpinner) cancelarSpinner.classList.remove('hidden');
            }

            // Tentar cancelar no servidor em background (não bloqueia a remoção do card)
            try {
                const response = await fetch(`/app/solucoes/raf/cancelar/${relatorioId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                // Log apenas para debug, não afeta a remoção do card
                if (!response.ok || !data.success) {
                    console.warn('Aviso: Erro ao cancelar no servidor, mas card já foi removido:', data.message || 'Erro desconhecido');
                }
            } catch (err) {
                // Log apenas para debug, não afeta a remoção do card
                console.warn('Aviso: Erro ao cancelar no servidor, mas card já foi removido:', err);
            }
        });
    };
})();
</script>

