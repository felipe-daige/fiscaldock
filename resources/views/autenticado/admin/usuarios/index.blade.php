@php
    $fmtN = fn ($v) => number_format((float) $v, 0, ',', '.');
    $quando = fn ($ts) => $ts ? \Carbon\Carbon::createFromTimestamp((int) $ts)->format('d/m/Y H:i') : '—';
    $precos = app(\App\Services\PricingCatalogService::class);
    $unitPrice = $precos->creditUnitPrice();
    $emReais = fn ($cred) => \App\Support\Dinheiro::brl($precos->creditsToCurrency((int) $cred));
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Usuários</h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    Gerenciamento operacional de contas, planos, saldo e acesso.
                    <span class="text-gray-400">· {{ $fmtN($usuarios->total()) }} no total</span>
                </p>
            </div>
            <a href="{{ route('app.admin.usuarios.create') }}" data-link class="admin-action w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded text-[12px] font-bold uppercase tracking-wide text-white hover:opacity-90 whitespace-nowrap" style="background-color:#0b1f3a">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Novo usuário
            </a>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'usuarios'])

        @if($errors->any())
            <div class="mb-4 rounded border p-3 text-[12px]" style="border-color:#fca5a5; background-color:#fef2f2; color:#991b1b" role="alert">
                <p class="font-bold">A alteração não foi aplicada.</p>
                <ul class="mt-1 list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Barra de filtro: busca + ordenação --}}
        <form method="GET" class="bg-white rounded border border-gray-300 p-3 mb-4 flex flex-col sm:flex-row sm:items-end gap-3" data-mobile-filters>
            <div class="flex-1">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Buscar</label>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="q" value="{{ $q }}" placeholder="nome, e-mail, empresa ou CNPJ" class="w-full text-[13px] py-2.5 pl-9 pr-3 border border-gray-300 rounded focus:border-gray-800 focus:ring-0 focus:outline-none">
                </div>
            </div>
            <div class="sm:w-52">
                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Ordenar por</label>
                <select name="ordenar" onchange="this.form.submit()" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded bg-white focus:border-gray-800 focus:ring-0 focus:outline-none">
                    @foreach([
                        'created_at' => 'Mais recentes',
                        'ultima_atividade_ts' => 'Última atividade',
                        'credits' => 'Maior saldo',
                        'qtd_consultas' => 'Mais consultas',
                    ] as $val => $lbl)
                        <option value="{{ $val }}" @selected(($ordenar ?? 'created_at') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="filtro-acao w-full sm:w-auto text-[12px] font-bold uppercase tracking-wide py-2.5 px-5 rounded text-white hover:opacity-90" style="background-color:#1d4ed8">Filtrar</button>
            @if($q !== '')
                <a href="{{ route('app.admin.usuarios.index') }}" data-link class="filtro-acao admin-action w-full sm:w-auto inline-flex items-center justify-center text-[12px] font-semibold text-gray-500 hover:text-gray-800 py-2.5 px-2">Limpar</a>
            @endif
        </form>

        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <table class="admin-users-table w-full text-sm tabela-cards">
                <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="text-left px-3 py-2.5">Usuário</th>
                        <th class="text-left px-3 py-2.5">Empresa</th>
                        <th class="text-left px-3 py-2.5">Plano</th>
                        <th class="text-right px-3 py-2.5">Saldo</th>
                        <th class="text-left px-3 py-2.5">Atividade</th>
                        <th class="text-right px-3 py-2.5">Uso</th>
                        <th class="text-right px-3 py-2.5 w-56">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($usuarios as $u)
                    @php
                        $trialAtivo = $u->trial_used && $u->trial_expires_at && \Carbon\Carbon::parse($u->trial_expires_at)->isFuture();
                        $plano = $u->plano_nome ?: ($trialAtivo ? 'Trial' : 'Gratuito');
                        $planoCor = $u->plano_nome ? '#1e4679' : ($trialAtivo ? '#7c3aed' : '#6b7280');
                        $iniciais = collect([mb_substr((string) $u->name, 0, 1), mb_substr((string) $u->sobrenome, 0, 1)])
                            ->filter()->join('');
                    @endphp
                    <tr class="admin-user-card-row hover:bg-gray-50/60">
                        {{-- Usuário --}}
                        <td class="admin-user-card__identity px-3 py-2.5" data-label="Usuário">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-[11px] font-bold uppercase text-white" style="background-color:#1e4679" aria-hidden="true">{{ $iniciais ?: '#' }}</span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                        <button type="button" onclick="document.getElementById('admin-user-modal-{{ $u->id }}').showModal()" class="admin-user-card__name text-left font-semibold text-gray-900 hover:text-blue-700 hover:underline cursor-pointer">{{ $u->name }} {{ $u->sobrenome }}</button>
                                        <span class="text-[10px] font-mono text-gray-400">#{{ $u->id }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-400 truncate">{{ $u->email }}</div>
                                    @if($u->is_admin || $u->deletion_requested_at || $u->bloqueado_em)
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @if($u->is_admin)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase text-white" style="background-color:#475569">admin</span>
                                            @endif
                                            @if($u->bloqueado_em)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase text-white" style="background-color:#b91c1c">bloqueado</span>
                                            @endif
                                            @if($u->deletion_requested_at)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase text-white" style="background-color:#dc2626">exclusão</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        {{-- Empresa --}}
                        <td class="admin-user-card__company px-3 py-2.5 text-gray-700" data-label="Empresa">
                            <span class="block truncate max-w-[180px]">{{ $u->empresa ?: '—' }}</span>
                            @if($u->cnpj)<span class="block text-[11px] text-gray-400 font-mono">{{ $u->cnpj }}</span>@endif
                        </td>
                        {{-- Plano --}}
                        <td class="admin-user-card__plan px-3 py-2.5" data-label="Plano">
                            <span class="whitespace-nowrap inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color:{{ $planoCor }}">{{ $plano }}</span>
                            @if($u->assinatura_status && $u->assinatura_status !== 'ativa')
                                <span class="block text-[10px] text-gray-400 mt-0.5">{{ $u->assinatura_status }}</span>
                            @endif
                        </td>
                        {{-- Saldo --}}
                        <td class="admin-user-card__balance px-3 py-2.5 text-right" data-label="Saldo">
                            <span class="block font-semibold text-gray-900">{{ $emReais($u->credits) }}</span>
                        </td>
                        {{-- Atividade --}}
                        <td class="admin-user-card__activity px-3 py-2.5 text-[12px] text-gray-600" data-label="Atividade">
                            <span class="block">{{ $quando($u->ultima_atividade_ts) }}</span>
                            <span class="block text-[10px] text-gray-400">criado {{ \Carbon\Carbon::parse($u->created_at)->format('d/m/Y') }}</span>
                        </td>
                        {{-- Uso --}}
                        <td class="admin-user-card__usage px-3 py-2.5 text-right text-[12px] text-gray-600" data-label="Uso">
                            <span class="block">{{ $fmtN($u->qtd_consultas) }} <span class="text-gray-400">consultas</span></span>
                            <span class="block text-[10px] text-gray-400">{{ $fmtN($u->qtd_importacoes) }} importações</span>
                        </td>
                        {{-- Ações --}}
                        <td class="admin-user-card__actions px-3 py-2.5 text-right" data-label="Ações">
                            <div class="flex flex-col sm:flex-row justify-end gap-1.5">
                                <button type="button" onclick="document.getElementById('admin-user-modal-{{ $u->id }}').showModal()" class="admin-action inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 px-3 py-2 rounded-md text-[11px] font-bold uppercase tracking-wide text-white hover:opacity-90 transition-opacity whitespace-nowrap" style="background-color:#0b1f3a">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path></svg>
                                    Plano/saldo
                                </button>
                                <a href="{{ route('app.admin.usuarios.edit', $u->id) }}" data-link class="admin-action inline-flex flex-1 sm:flex-none items-center justify-center gap-1.5 px-3 py-2 rounded-md border border-gray-300 text-[11px] font-bold uppercase tracking-wide text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-colors whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Cadastro
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="admin-user-card-empty"><td colspan="7" class="px-3 py-10 text-center text-gray-400 text-sm">
                        Nenhum usuário{{ $q !== '' ? ' para “'.$q.'”' : '' }}.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @foreach($usuarios as $u)
            @php
                $modalPlano = old('subscription_plan_id', $u->assinatura_plan_id ?? '');
                $modalStatus = old('status', $u->assinatura_status ?? 'ativa');
                $modalCiclo = old('ciclo', $u->assinatura_ciclo ?? 'mensal');
                $modalBucket = old('creditos_inclusos_saldo') !== null
                    ? (float) old('creditos_inclusos_saldo')
                    : $precos->creditsToCurrency((int) ($u->assinatura_bucket ?? 0));
                $podeExcluirModal = auth()->id() !== (int) $u->id && ! $u->is_admin && ! $u->anonimizado_em;
            @endphp
            <dialog
                id="admin-user-modal-{{ $u->id }}"
                data-admin-user-modal
                class="admin-dialog rounded-lg border border-gray-300 p-0 backdrop:bg-black/50 shadow-2xl"
                style="width:min(94vw, 1000px); height:88vh; max-height:88vh; overflow:hidden; position:fixed; inset:0; margin:auto"
            >
                <div class="bg-white relative" data-admin-action-scope style="display:flex; flex-direction:column; height:100%; width:100%">
                    {{-- Cabeçalho --}}
                    <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-200 flex items-start justify-between gap-3 sm:gap-4" style="background-color:#0b1f3a; flex:0 0 auto">
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-widest" style="color:#93a4bd">Plano, saldo e acesso rápido</p>
                            <h2 class="text-base font-bold text-white truncate">{{ $u->name }} {{ $u->sobrenome }}</h2>
                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[12px]" style="color:#c7d2e0">
                                <span class="font-mono">#{{ $u->id }}</span>
                                <span class="opacity-50">·</span>
                                <span class="truncate">{{ $u->email }}</span>
                                <span class="opacity-50">·</span>
                                <span class="truncate">{{ $u->empresa ?: 'sem empresa' }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color:#1e4679">{{ $plano }}</span>
                                @if($u->is_admin)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color:#475569">admin</span>
                                @endif
                                @if($u->bloqueado_em)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color:#b91c1c">bloqueado</span>
                                @endif
                            </div>
                        </div>
                        <form method="dialog">
                            <button class="w-8 h-8 rounded text-white hover:bg-white/10 text-lg leading-none" aria-label="Fechar" title="Fechar (Esc)">&times;</button>
                        </form>
                    </div>

                    <div class="p-3 sm:p-5 grid grid-cols-1 lg:grid-cols-2 gap-4 bg-gray-50" style="flex:1 1 auto; min-height:0; overflow-y:auto">
                        {{-- Plano e assinatura --}}
                        <form
                            method="POST"
                            action="{{ route('app.admin.usuarios.assinatura', $u->id) }}"
                            class="bg-white border border-gray-200 border-l-2 rounded p-4 space-y-3"
                            style="border-left-color:#0b1f3a"
                            data-admin-action-form="plan"
                            data-admin-action-user-id="{{ $u->id }}"
                            data-admin-action-user-name="{{ trim(($u->name ?? '').' '.($u->sobrenome ?? '')) ?: 'Usuário #'.$u->id }}"
                            data-admin-action-user-email="{{ $u->email }}"
                            data-admin-action-current-plan="{{ $plano }}"
                        >
                            @csrf
                            <div>
                                <p class="text-[11px] font-bold text-gray-900 uppercase tracking-wide">Plano e assinatura</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Altera o plano local usado pelos entitlements. <strong>Não</strong> sincroniza cobrança no Mercado Pago.</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-medium text-gray-500 mb-1">Plano</label>
                                <select name="subscription_plan_id" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    <option value="">Sem assinatura (Free)</option>
                                    @foreach($planos as $pl)
                                        <option value="{{ $pl->id }}" @selected((string) $modalPlano === (string) $pl->id)>{{ $pl->nome }} ({{ $pl->codigo }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-1 min-[420px]:grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-medium text-gray-500 mb-1">Status</label>
                                    <select name="status" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                        @foreach(\App\Services\Admin\AdminAcaoService::STATUS_ASSINATURA as $st)
                                            <option value="{{ $st }}" @selected($modalStatus === $st)>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-medium text-gray-500 mb-1">Ciclo</label>
                                    <select name="ciclo" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                        @foreach(['mensal', 'anual'] as $ciclo)
                                            <option value="{{ $ciclo }}" @selected($modalCiclo === $ciclo)>{{ $ciclo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 min-[420px]:grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-medium text-gray-500 mb-1">Bucket incluso (R$)</label>
                                    <input type="number" step="0.01" min="0" name="creditos_inclusos_saldo" value="{{ number_format((float) $modalBucket, 2, '.', '') }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    <p class="text-[10px] text-gray-400 mt-1">Saldo do plano já concedido (rollover).</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-medium text-gray-500 mb-1">Cap auto (R$)</label>
                                    <input type="number" step="0.01" min="0" name="limite_consumo_automatico" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="default">
                                    <p class="text-[10px] text-gray-400 mt-1">Teto do auto-monitor/ciclo. Vazio = inclusos.</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-medium text-gray-500 mb-1">Motivo</label>
                                <input type="text" name="motivo" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="Motivo obrigatório (trilha admin)" required>
                            </div>
                            <button type="submit" class="w-full text-white text-[12px] font-bold uppercase tracking-wide px-3 py-2.5 rounded hover:opacity-90" style="background-color:#0b1f3a">Salvar plano</button>
                            <p class="text-[11px] flex items-start gap-1.5" style="color:#b45309">
                                <span class="font-bold">⚠</span>
                                <span>Status precisa ser <strong>ativa</strong> para liberar recursos pagos.</span>
                            </p>
                        </form>

                        {{-- Saldo --}}
                        <form
                            method="POST"
                            action="{{ route('app.admin.usuarios.creditar', $u->id) }}"
                            class="bg-white border border-gray-200 border-l-2 rounded p-4 space-y-3"
                            style="border-left-color:#1e4679"
                            data-credit-form
                            data-saldo="{{ (int) $u->credits }}"
                            data-unit="{{ $unitPrice }}"
                            data-admin-action-form="credit"
                            data-admin-action-user-id="{{ $u->id }}"
                            data-admin-action-user-name="{{ trim(($u->name ?? '').' '.($u->sobrenome ?? '')) ?: 'Usuário #'.$u->id }}"
                            data-admin-action-user-email="{{ $u->email }}"
                        >
                            @csrf
                            <div>
                                <p class="text-[11px] font-bold text-gray-900 uppercase tracking-wide">Saldo</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">Ajuste manual com trilha administrativa. Valor negativo debita.</p>
                            </div>
                            <div class="rounded border border-gray-200 p-3 bg-gray-50 flex items-baseline justify-between gap-2">
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Saldo atual</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $emReais($u->credits) }}</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-medium text-gray-500 mb-1">Movimento (R$)</label>
                                <input type="number" step="0.01" name="valor" data-credit-input class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="ex.: 10,00 ou -4,00" required>
                                <p class="text-[11px] mt-1.5 hidden" data-credit-preview></p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-medium text-gray-500 mb-1">Motivo</label>
                                <input type="text" name="motivo" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="Motivo obrigatório (trilha admin)" required>
                            </div>
                            <button type="submit" class="w-full text-white text-[12px] font-bold uppercase tracking-wide px-3 py-2.5 rounded hover:opacity-90" style="background-color:#1d4ed8">Aplicar ajuste</button>
                        </form>

                        @include('autenticado.admin.usuarios._delete_zone', [
                            'deleteUsuario' => $u,
                            'deletePodeExcluir' => $podeExcluirModal,
                            'deleteClasses' => 'lg:col-span-2',
                        ])
                    </div>

                    <div class="px-3 sm:px-5 py-2 sm:py-3 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-white" style="flex:0 0 auto">
                        <p class="hidden sm:block text-[11px] text-gray-500">Para bloqueio, trial ou impersonação, abra o detalhe completo.</p>
                        <div class="grid grid-cols-2 sm:flex sm:flex-wrap sm:justify-end gap-2 w-full sm:w-auto">
                            <a href="{{ route('app.admin.usuarios.show', $u->id) }}" data-link class="admin-action inline-flex items-center justify-center px-3 py-2 rounded text-[12px] font-semibold text-white hover:opacity-90" style="background-color:#334155"><span class="sm:hidden">Detalhe</span><span class="hidden sm:inline">Detalhe completo</span></a>
                            <a href="{{ route('app.admin.usuarios.edit', $u->id) }}" data-link class="admin-action inline-flex items-center justify-center px-3 py-2 rounded border border-gray-300 text-[12px] font-semibold text-gray-700 bg-white hover:bg-gray-50"><span class="sm:hidden">Editar</span><span class="hidden sm:inline">Editar cadastro</span></a>
                        </div>
                    </div>

                    @include('autenticado.admin.usuarios._action_confirm_modal', [
                        'adminActionConfirmMode' => 'absolute',
                        'adminActionConfirmId' => 'admin-user-action-confirm-'.$u->id,
                    ])
                </div>
            </dialog>
        @endforeach

        <div class="mt-4">{{ $usuarios->withQueryString()->links() }}</div>
    </div>
</div>

<script>
(function () {
    // Fechar no clique fora (backdrop) — <dialog> nativo só fecha no Esc.
    document.querySelectorAll('[data-admin-user-modal]').forEach(function (dlg) {
        dlg.addEventListener('click', function (e) {
            if (e.target === dlg) { dlg.close(); }
        });
    });
})();
</script>
<script src="/js/admin-usuarios-acoes.js?v={{ @filemtime(public_path('js/admin-usuarios-acoes.js')) ?: time() }}"></script>
