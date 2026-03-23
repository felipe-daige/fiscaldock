{{-- Cliente - Detalhes --}}
<div class="min-h-screen bg-gray-50" id="cliente-show-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        {{-- Page Header --}}
        <div class="mb-4 sm:mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <a
                        href="/app/clientes"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                        data-link
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar
                    </a>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">
                            {{ $cliente->razao_social ?? $cliente->nome ?? 'Cliente' }}
                        </h1>
                        <p class="text-sm text-gray-600 font-mono whitespace-nowrap tabular-nums">
                            {{ $cliente->documento_formatado }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    {{-- Badge Tipo Pessoa --}}
                    @if($cliente->tipo_pessoa === 'PJ')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                            Pessoa Jurídica
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                            Pessoa Física
                        </span>
                    @endif

                    {{-- Badge Status --}}
                    @if($cliente->ativo)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            Ativo
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                            Inativo
                        </span>
                    @endif

                    {{-- Badge Empresa Propria --}}
                    @if($cliente->is_empresa_propria)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 border border-green-200">
                            Empresa Própria
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Acoes Rapidas --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="text-sm text-gray-500">
                    Cadastrado em: <strong class="text-gray-900">{{ $cliente->created_at?->format('d/m/Y') ?? '-' }}</strong>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('app.cliente.edit', $cliente->id) }}"
                        data-link
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    @if(!$cliente->is_empresa_propria)
                        <button
                            type="button"
                            id="btn-excluir-cliente"
                            data-id="{{ $cliente->id }}"
                            data-nome="{{ $cliente->razao_social ?? $cliente->nome }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-red-200 bg-white text-red-600 text-sm font-semibold shadow-sm transition hover:bg-red-50"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Excluir
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Grid: Dados Cadastrais + Estatisticas --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- Dados Cadastrais --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Dados Cadastrais</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 sm:gap-x-6 gap-y-4">
                    @if($cliente->tipo_pessoa === 'PJ' && $cliente->razao_social)
                        <div>
                            <dt class="text-xs text-gray-500 font-medium">Razão Social</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $cliente->razao_social }}</dd>
                        </div>
                    @endif
                    @if($cliente->nome)
                        <div>
                            <dt class="text-xs text-gray-500 font-medium">{{ $cliente->tipo_pessoa === 'PJ' ? 'Nome Fantasia' : 'Nome' }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $cliente->nome }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-500 font-medium">{{ $cliente->tipo_pessoa === 'PJ' ? 'CNPJ' : 'CPF' }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $cliente->documento_formatado }}</dd>
                    </div>
                    @if($cliente->email)
                        <div>
                            <dt class="text-xs text-gray-500 font-medium">E-mail</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $cliente->email }}</dd>
                        </div>
                    @endif
                    @if($cliente->telefone)
                        <div>
                            <dt class="text-xs text-gray-500 font-medium">Telefone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $cliente->telefone }}</dd>
                        </div>
                    @endif
                    @if($cliente->municipio || $cliente->uf)
                        <div>
                            <dt class="text-xs text-gray-500 font-medium">Município/UF</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ implode(' - ', array_filter([$cliente->municipio, $cliente->uf])) }}
                            </dd>
                        </div>
                    @endif
                    @if($cliente->cep)
                        <div>
                            <dt class="text-xs text-gray-500 font-medium">CEP</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $cliente->cep }}</dd>
                        </div>
                    @endif
                    @if($cliente->endereco ?? null)
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-500 font-medium">Endereço</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $cliente->endereco }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Estatisticas --}}
            <div class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Estatísticas</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-600">Participantes</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900">{{ $totalParticipantes }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-600">Notas Fiscais</span>
                            </div>
                            <span class="text-xl font-bold text-gray-900">{{ $totalNotas }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notas Fiscais (EFD + XML unificadas) --}}
        <div class="mt-6">
            @include('autenticado.partials.notas-fiscais-card', [
                'notas' => $notasFiscais,
                'totalNotas' => $totalNotasFiscais,
                'ajaxUrl' => $notasAjaxUrl,
                'contexto' => $notasContexto,
                'entityId' => $notasEntityId,
            ])
        </div>
    </div>

    {{-- Modal de confirmacao de exclusao --}}
    @if(!$cliente->is_empresa_propria)
    <div id="modal-excluir-show" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="modal-excluir-show-overlay"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Excluir cliente</h3>
                        <p class="text-sm text-gray-500">Esta ação não pode ser desfeita.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-700 mb-6">
                    Tem certeza que deseja excluir <strong>{{ $cliente->razao_social ?? $cliente->nome }}</strong>?
                </p>
                <div class="flex gap-3 justify-end">
                    <button type="button" id="btn-cancelar-excluir-show" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="button" id="btn-confirmar-excluir-show" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors" data-id="{{ $cliente->id }}">
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var btnExcluir = document.getElementById('btn-excluir-cliente');
        var modal = document.getElementById('modal-excluir-show');
        var overlay = document.getElementById('modal-excluir-show-overlay');
        var btnCancelar = document.getElementById('btn-cancelar-excluir-show');
        var btnConfirmar = document.getElementById('btn-confirmar-excluir-show');

        if (btnExcluir) {
            btnExcluir.addEventListener('click', function() {
                if (modal) modal.classList.remove('hidden');
            });
        }

        function fecharModal() {
            if (modal) modal.classList.add('hidden');
        }

        if (overlay) overlay.addEventListener('click', fecharModal);
        if (btnCancelar) btnCancelar.addEventListener('click', fecharModal);

        if (btnConfirmar) {
            btnConfirmar.addEventListener('click', function() {
                var id = this.dataset.id;
                var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                    || document.querySelector('input[name="_token"]')?.value
                    || '';

                fetch('/app/cliente/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        if (window.navigateTo) {
                            window.navigateTo('/app/clientes');
                        } else {
                            window.location.href = '/app/clientes';
                        }
                    } else {
                        fecharModal();
                        alert(data.message || 'Erro ao excluir cliente.');
                    }
                })
                .catch(function() {
                    fecharModal();
                    alert('Erro ao excluir cliente.');
                });
            });
        }
    })();
    </script>
    @endif
</div>
