{{-- Monitoramento — Painel (DANFE Modernizado) --}}
<div class="min-h-screen bg-gray-100" id="monitoramento-painel-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Compliance Automático</h1>
                <p class="text-xs text-gray-500 mt-1">Checagem recorrente de compliance dos clientes e participantes selecionados.</p>
            </div>
            <button type="button"
                    id="btn-nova-assinatura"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded text-white text-xs font-semibold transition"
                    style="background-color: #047857;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nova assinatura
            </button>
        </div>

        {{-- KPIs --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200">
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Ativas</p>
                    <p class="text-lg font-bold text-gray-900 font-mono">{{ $kpiAtivas }}</p>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Pausadas</p>
                    <p class="text-lg font-bold text-gray-900 font-mono">{{ $kpiPausadas }}</p>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Créditos no mês</p>
                    <p class="text-lg font-bold text-gray-900 font-mono">{{ $kpiCreditosMes }}</p>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Próx. ciclo (est.)</p>
                    <p class="text-lg font-bold text-gray-900 font-mono">{{ $kpiPrevisaoCiclo }}</p>
                </div>
            </div>
        </div>

        @include('autenticado.monitoramento._sub-tabs-tipo', [
            'tipoAtivo' => $tipoAtivo,
            'contagens' => $contagens,
            'rota' => 'app.monitoramento.painel',
        ])

        {{-- Filtros --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros</span>
            </div>
            <form method="GET" action="{{ route('app.monitoramento.painel') }}" class="p-4 flex flex-wrap items-end gap-3">
                <input type="hidden" name="tipo" value="{{ $tipoAtivo }}">
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Status</label>
                    <select name="status" class="px-3 py-2 text-sm border border-gray-300 rounded">
                        <option value="">Todos</option>
                        <option value="ativo" @selected($filtros['status'] === 'ativo')>Ativo</option>
                        <option value="pausado" @selected($filtros['status'] === 'pausado')>Pausado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Plano</label>
                    <select name="plano_id" class="px-3 py-2 text-sm border border-gray-300 rounded">
                        <option value="">Todos</option>
                        @foreach ($planos as $p)
                            <option value="{{ $p->id }}" @selected($filtros['plano_id'] === $p->id)>{{ $p->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Busca</label>
                    <input type="text" name="busca" value="{{ $filtros['busca'] }}" placeholder="CNPJ ou razão social"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded">
                </div>
                <button type="submit" class="px-3 py-2 rounded bg-gray-800 hover:bg-gray-700 text-white text-xs font-semibold">Filtrar</button>
            </form>
        </div>

        {{-- Tabela --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            @if ($assinaturas->isEmpty())
                <div class="p-10 text-center">
                    <p class="text-sm text-gray-600">Nenhuma assinatura encontrada.</p>
                    <p class="text-xs text-gray-500 mt-2">Crie a primeira pelo botão acima ou direto no detalhe de um cliente/participante.</p>
                </div>
            @else
                {{-- Desktop: tabela --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr class="text-left text-[10px] font-semibold text-gray-500 uppercase tracking-widest">
                                <th class="px-4 py-2">Tipo</th>
                                <th class="px-4 py-2">CNPJ</th>
                                <th class="px-4 py-2">Razão Social</th>
                                <th class="px-4 py-2">Plano</th>
                                <th class="px-4 py-2">Freq.</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2">Última situação</th>
                                <th class="px-4 py-2">Próx. exec.</th>
                                <th class="px-4 py-2 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($assinaturas as $a)
                                @php
                                    $alvoTipo = $a->alvoTipo();
                                    $alvo = $a->alvo();
                                    $corTipo = $alvoTipo === 'cliente' ? '#1e40af' : '#7c3aed';
                                    $corStatus = match ($a->status) {
                                        'ativo' => '#047857',
                                        'pausado' => '#d97706',
                                        default => '#6b7280',
                                    };
                                    $ultima = $ultimasConsultas[$a->id] ?? null;
                                    $href = $alvoTipo === 'cliente' ? "/app/cliente/{$alvo?->id}" : "/app/participante/{$alvo?->id}";
                                    $docFormatado = $alvoTipo === 'cliente' ? $alvo?->documento_formatado : $alvo?->cnpj_formatado;
                                @endphp
                                <tr>
                                    <td class="px-4 py-2">
                                        <span class="text-[10px] font-semibold text-white uppercase px-2 py-1 rounded"
                                              style="background-color: {{ $corTipo }};">{{ $alvoTipo }}</span>
                                    </td>
                                    <td class="px-4 py-2 font-mono text-xs whitespace-nowrap">{{ $docFormatado ?? $alvo?->documento ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ $href }}" data-link class="text-gray-900 hover:underline">{{ $alvo?->razao_social ?? '—' }}</a>
                                    </td>
                                    <td class="px-4 py-2">{{ $a->plano?->nome }}</td>
                                    <td class="px-4 py-2 text-xs">{{ $a->frequencia }}</td>
                                    <td class="px-4 py-2">
                                        <span class="text-[10px] font-semibold text-white uppercase px-2 py-1 rounded"
                                              style="background-color: {{ $corStatus }};">{{ $a->status }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-xs">
                                        {{ $ultima?->situacao_geral ?? '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-xs">
                                        {{ $a->proxima_execucao_em?->diffForHumans() ?? '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <div class="inline-flex gap-1">
                                            @if ($a->status === 'ativo')
                                                <button type="button" class="btn-pausar text-xs px-2 py-1 rounded border border-gray-300 hover:bg-gray-50" data-assinatura-id="{{ $a->id }}">Pausar</button>
                                            @else
                                                <button type="button" class="btn-reativar text-xs px-2 py-1 rounded border border-gray-300 hover:bg-gray-50" data-assinatura-id="{{ $a->id }}">Reativar</button>
                                            @endif
                                            <button type="button" class="btn-cancelar text-xs px-2 py-1 rounded border border-red-300 text-red-700 hover:bg-red-50" data-assinatura-id="{{ $a->id }}">Cancelar</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile: cards --}}
                <div class="divide-y divide-gray-200 md:hidden">
                    @foreach ($assinaturas as $a)
                        @php
                            $alvoTipo = $a->alvoTipo();
                            $alvo = $a->alvo();
                            $corTipo = $alvoTipo === 'cliente' ? '#1e40af' : '#7c3aed';
                            $corStatus = match ($a->status) {
                                'ativo' => '#047857',
                                'pausado' => '#d97706',
                                default => '#6b7280',
                            };
                            $ultima = $ultimasConsultas[$a->id] ?? null;
                            $href = $alvoTipo === 'cliente' ? "/app/cliente/{$alvo?->id}" : "/app/participante/{$alvo?->id}";
                            $docFormatado = $alvoTipo === 'cliente' ? $alvo?->documento_formatado : $alvo?->cnpj_formatado;
                        @endphp
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[10px] font-semibold text-white uppercase px-2 py-0.5 rounded"
                                              style="background-color: {{ $corTipo }};">{{ $alvoTipo }}</span>
                                        <span class="text-[10px] font-semibold text-white uppercase px-2 py-0.5 rounded"
                                              style="background-color: {{ $corStatus }};">{{ $a->status }}</span>
                                    </div>
                                    <a href="{{ $href }}" data-link class="text-sm font-medium text-gray-900 hover:underline block truncate">
                                        {{ $alvo?->razao_social ?? '—' }}
                                    </a>
                                    <p class="text-xs font-mono text-gray-500 mt-0.5">{{ $docFormatado ?? $alvo?->documento ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mt-3 text-xs">
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Plano</p>
                                    <p class="text-gray-900 mt-0.5">{{ $a->plano?->nome ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Frequência</p>
                                    <p class="text-gray-900 mt-0.5">{{ $a->frequencia }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Última situação</p>
                                    <p class="text-gray-900 mt-0.5">{{ $ultima?->situacao_geral ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Próx. exec.</p>
                                    <p class="text-gray-900 mt-0.5">{{ $a->proxima_execucao_em?->diffForHumans() ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
                                @if ($a->status === 'ativo')
                                    <button type="button" class="btn-pausar flex-1 text-xs px-3 py-2 rounded border border-gray-300 hover:bg-gray-50" data-assinatura-id="{{ $a->id }}">Pausar</button>
                                @else
                                    <button type="button" class="btn-reativar flex-1 text-xs px-3 py-2 rounded border border-gray-300 hover:bg-gray-50" data-assinatura-id="{{ $a->id }}">Reativar</button>
                                @endif
                                <button type="button" class="btn-cancelar flex-1 text-xs px-3 py-2 rounded border border-red-300 text-red-700 hover:bg-red-50" data-assinatura-id="{{ $a->id }}">Cancelar</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                    {{ $assinaturas->links() }}
                </div>
            @endif
        </div>
    </div>

    @include('autenticado.monitoramento._modal-nova-assinatura')

    {{-- Modal de confirmação de ação --}}
    <div id="modal-confirmar-acao" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded border border-gray-300 max-w-md w-full overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Confirmar ação</span>
            </div>
            <div class="p-4">
                <h2 id="modal-confirmar-titulo" class="text-sm font-semibold text-gray-900 mb-2">—</h2>
                <p id="modal-confirmar-mensagem" class="text-xs text-gray-600">—</p>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex justify-end gap-2">
                <button type="button" id="modal-confirmar-cancelar"
                        class="px-3 py-2 rounded border border-gray-300 bg-white text-gray-700 text-xs font-semibold hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="button" id="modal-confirmar-ok"
                        class="px-4 py-2 rounded text-white text-xs font-semibold"
                        style="background-color: #1f2937;">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/js/monitoramento-modal-nova-assinatura.js?v={{ filemtime(public_path('js/monitoramento-modal-nova-assinatura.js')) }}"></script>

<script>
(function() {
    'use strict';

    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    var modal = document.getElementById('modal-confirmar-acao');
    var modalTitulo = document.getElementById('modal-confirmar-titulo');
    var modalMensagem = document.getElementById('modal-confirmar-mensagem');
    var modalCancelar = document.getElementById('modal-confirmar-cancelar');
    var modalOk = document.getElementById('modal-confirmar-ok');
    var pendingCallback = null;

    function abrirConfirmacao(opts) {
        modalTitulo.textContent = opts.titulo || 'Confirmar ação';
        modalMensagem.textContent = opts.mensagem || '';
        modalOk.textContent = opts.textoConfirmar || 'Confirmar';
        modalOk.style.backgroundColor = opts.destrutivo ? '#b91c1c' : '#1f2937';
        pendingCallback = opts.onConfirm || null;
        modal.classList.remove('hidden');
    }

    function fecharConfirmacao() {
        modal.classList.add('hidden');
        pendingCallback = null;
    }

    modalCancelar.addEventListener('click', fecharConfirmacao);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) fecharConfirmacao();
    });
    modalOk.addEventListener('click', function() {
        var cb = pendingCallback;
        fecharConfirmacao();
        if (typeof cb === 'function') cb();
    });

    function acaoAssinatura(url, method) {
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
        .then(function(res) { return res.json().then(function(data) { return { ok: res.ok, data: data }; }); })
        .then(function(r) {
            if (!r.ok) {
                throw new Error(r.data.error || r.data.message || 'Erro ao atualizar a assinatura');
            }
            window.showToast && window.showToast('Assinatura atualizada.', 'success');
            setTimeout(function() { window.location.reload(); }, 800);
        })
        .catch(function(err) {
            if (window.showToast) {
                window.showToast(err.message || 'Erro ao atualizar a assinatura', 'error');
            } else {
                alert(err.message || 'Erro ao atualizar a assinatura');
            }
        });
    }

    document.querySelectorAll('#monitoramento-painel-container .btn-pausar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.assinaturaId;
            abrirConfirmacao({
                titulo: 'Pausar assinatura',
                mensagem: 'A checagem recorrente fica suspensa até você reativar. Nenhum crédito é cobrado enquanto estiver pausada.',
                textoConfirmar: 'Pausar',
                destrutivo: false,
                onConfirm: function() { acaoAssinatura('/app/monitoramento/assinatura/' + id + '/pausar', 'POST'); },
            });
        });
    });

    document.querySelectorAll('#monitoramento-painel-container .btn-reativar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.assinaturaId;
            abrirConfirmacao({
                titulo: 'Reativar assinatura',
                mensagem: 'A próxima execução é agendada conforme a frequência do plano. Créditos voltam a ser debitados a cada ciclo.',
                textoConfirmar: 'Reativar',
                destrutivo: false,
                onConfirm: function() { acaoAssinatura('/app/monitoramento/assinatura/' + id + '/reativar', 'POST'); },
            });
        });
    });

    document.querySelectorAll('#monitoramento-painel-container .btn-cancelar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.assinaturaId;
            abrirConfirmacao({
                titulo: 'Cancelar assinatura',
                mensagem: 'A assinatura é desativada permanentemente. O histórico de execuções fica preservado, mas você precisa criar uma nova assinatura para retomar.',
                textoConfirmar: 'Cancelar assinatura',
                destrutivo: true,
                onConfirm: function() { acaoAssinatura('/app/monitoramento/assinatura/' + id, 'DELETE'); },
            });
        });
    });
})();
</script>
