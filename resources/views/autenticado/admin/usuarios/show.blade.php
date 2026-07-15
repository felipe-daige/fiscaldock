@php
    $pricing = app(\App\Services\PricingCatalogService::class);
    $fmtR = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $fmtN = fn ($v) => number_format((float) $v, 0, ',', '.');
    $fmtData = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '—';
    $fmtDataHora = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y H:i') : '—';
    $fmtInputDataHora = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('Y-m-d\TH:i') : '';
    $quandoSessao = fn ($ts) => $ts ? \Carbon\Carbon::createFromTimestamp((int) $ts)->format('d/m/Y H:i') : '—';
    $fmtDoc = function ($v) {
        $d = preg_replace('/\D/', '', (string) $v);
        return strlen($d) === 14
            ? substr($d, 0, 2).'.'.substr($d, 2, 3).'.'.substr($d, 5, 3).'/'.substr($d, 8, 4).'-'.substr($d, 12, 2)
            : ($v ?: '—');
    };
    $statusLabel = [
        'approved' => 'Aprovado',
        'cancelled' => 'Cancelado',
        'concluido' => 'Concluído',
        'erro' => 'Erro',
        'finalizado' => 'Finalizado',
        'pending' => 'Pendente',
        'pendente' => 'Pendente',
        'processando' => 'Processando',
        'rejected' => 'Rejeitado',
        'refunded' => 'Estornado',
    ];
    $statusCor = fn ($s) => match ($s) {
        'approved', 'ativa', 'ativo', 'concluido', 'finalizado' => '#047857',
        'cancelled', 'erro', 'rejected' => '#b91c1c',
        'pending', 'pendente', 'processando' => '#b45309',
        'refunded' => '#7c3aed',
        default => '#374151',
    };
    $tipoLabel = [
        'consulta' => 'Consulta',
        'importacao_efd' => 'Importação EFD',
        'importacao_xml' => 'Importação XML',
        'credito' => 'Saldo adicionado',
        'pagamento' => 'Pagamento',
    ];
    $tipoCor = [
        'consulta' => '#1d4ed8',
        'importacao_efd' => '#4338ca',
        'importacao_xml' => '#0f766e',
        'credito' => '#047857',
        'pagamento' => '#b45309',
    ];
    $detalheAdmin = $detalheAdmin ?? ['conta' => [], 'financeiro' => [], 'uso' => []];
    $conta = $detalheAdmin['conta'] ?? [];
    $financeiro = $detalheAdmin['financeiro'] ?? [];
    $uso = $detalheAdmin['uso'] ?? [];
    $nomeCompleto = trim(($usuario->name ?? '').' '.($usuario->sobrenome ?? '')) ?: 'Usuário #'.$usuario->id;
    $planoAtual = $assinatura->plano_nome ?? (($usuario->trial_used && $usuario->trial_expires_at && \Carbon\Carbon::parse($usuario->trial_expires_at)->isFuture()) ? 'Trial' : 'Gratuito');
    $assinaturaAtual = $assinaturaAtual ?? null;
    $ehEu = auth()->id() === $usuario->id;
    $podeImpersonar = ! $usuario->is_admin && ! $ehEu && ! $usuario->bloqueado_em;
    $motivoImpersonacaoBloqueada = $ehEu ? 'Não é possível impersonar a própria conta.' : ($usuario->is_admin ? 'Impersonação de admin é bloqueada.' : ($usuario->bloqueado_em ? 'Usuário bloqueado não pode ser impersonado.' : null));
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8" data-admin-action-scope>
        <div class="mb-4 sm:mb-6">
            <a href="{{ route('app.admin.usuarios.index') }}" data-link class="text-[12px] text-gray-600 hover:text-gray-900 hover:underline">← Voltar para usuários</a>
            <div class="mt-2 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">{{ $nomeCompleto }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $usuario->email }} · {{ $usuario->empresa ?: 'sem empresa' }} · CNPJ {{ $fmtDoc($usuario->cnpj) }}
                    </p>
                </div>
                <div class="flex flex-col items-start lg:items-end gap-2">
                    <div class="flex flex-wrap gap-1.5 lg:justify-end">
                        @if($usuario->is_admin)
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#334155">Admin</span>
                        @endif
                        @if($usuario->bloqueado_em)
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#b91c1c">Bloqueado</span>
                        @else
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#047857">Acesso ativo</span>
                        @endif
                        @if($conta['lgpd_solicitada'] ?? false)
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#dc2626">LGPD</span>
                        @endif
                        @if($conta['tem_compra_confirmada'] ?? false)
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#047857">Compra confirmada</span>
                        @endif
                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#374151">{{ $conta['trial_status'] ?? 'Sem trial' }}</span>
                    </div>
                    <a href="{{ route('app.admin.usuarios.edit', $usuario->id) }}" data-link class="admin-action w-full sm:w-auto inline-flex items-center justify-center px-3 py-2 rounded text-[12px] font-bold uppercase tracking-wide text-white" style="background-color:#0b1f3a">Editar cadastro</a>
                </div>
            </div>
        </div>

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

        @include('autenticado.admin.partials.nav', ['tab' => 'usuarios'])

        @if(session('status'))
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color:#047857">
                {{ session('status') }}
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color:#dc2626">
                {{ $errors->first() }}
            </div>
        @endif

        @if($usuario->deletion_requested_at)
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color:#dc2626">
                Exclusão de conta solicitada em {{ $fmtData($usuario->deletion_requested_at) }} (LGPD).
            </div>
        @endif

        <div class="grid grid-cols-1 min-[380px]:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
            @foreach([
                ['Saldo', $fmtR(($usuario->credits)), $fmtN($usuario->credits).' em saldo bruto', '#1d4ed8'],
                ['Total pago', $fmtR($financeiro['total_pago'] ?? $kpis['total_pago']), $fmtN($financeiro['compras_aprovadas'] ?? 0).' compra(s) aprovada(s)', '#047857'],
                ['Consumo total', $fmtR((($financeiro['creditos_consumidos'] ?? $kpis['creditos_consumidos']))), 'débitos de saldo', '#b45309'],
                ['Plano', $planoAtual, $assinatura ? 'assinatura ativa' : 'sem assinatura ativa', '#334155'],
                ['Consultas', $fmtN($kpis['qtd_consultas']), $fmtN($uso['consultas_30d'] ?? 0).' nos últimos 30 dias', '#1d4ed8'],
                ['Importações', $fmtN($kpis['qtd_importacoes']), $fmtN($uso['importacoes_30d'] ?? 0).' nos últimos 30 dias', '#4338ca'],
                ['Participantes', $fmtN($uso['participantes_total'] ?? 0), $fmtN($uso['monitoramentos_ativos'] ?? 0).' monitorado(s)', '#0f766e'],
                ['Última sessão', $sessao ? $quandoSessao($sessao->last_activity) : '—', $sessao->ip_address ?? 'sem sessão registrada', '#334155'],
            ] as [$label, $valor, $sub, $cor])
                <div class="bg-white rounded border border-gray-300 border-l-4 p-3 min-w-0" style="border-left-color: {{ $cor }}">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $label }}</p>
                    <p class="text-base font-bold text-gray-900 truncate">{{ $valor }}</p>
                    <p class="text-[11px] text-gray-500 mt-0.5 truncate">{{ $sub }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <div class="xl:col-span-2 space-y-4">
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Conta e acesso</p>
                    </div>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 p-4 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">ID interno</dt><dd class="font-semibold text-gray-900 text-right">#{{ $usuario->id }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Criado em</dt><dd class="text-gray-900 text-right">{{ $fmtDataHora($usuario->created_at) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Status de acesso</dt><dd class="text-gray-900 text-right">{{ $usuario->bloqueado_em ? 'Bloqueado desde '.$fmtDataHora($usuario->bloqueado_em) : 'Ativo' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Permissão</dt><dd class="text-gray-900 text-right">{{ $usuario->is_admin ? 'Administrador' : 'Usuário comum' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Trial</dt><dd class="text-gray-900 text-right">{{ $conta['trial_status'] ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Expiração do trial</dt><dd class="text-gray-900 text-right">{{ $fmtData($conta['trial_expira_em'] ?? null) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Última sessão</dt><dd class="text-gray-900 text-right">{{ $sessao ? $quandoSessao($sessao->last_activity) : '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">IP / navegador</dt><dd class="text-gray-900 text-right">{{ $sessao ? (($sessao->ip_address ?: '—').' · '.\Illuminate\Support\Str::limit($sessao->user_agent ?: 'sem user-agent', 42)) : '—' }}</dd></div>
                    </dl>
                </div>

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Uso do produto</p>
                        <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">30 dias + total</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                        <div class="p-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consultas CNPJ</p>
                            <p class="text-lg font-bold text-gray-900">{{ $fmtN($kpis['qtd_consultas']) }}</p>
                            <p class="text-[11px] text-gray-500">{{ $fmtN($uso['consultas_30d'] ?? 0) }} nos últimos 30 dias</p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                @forelse($uso['consultas_por_status'] ?? [] as $status => $total)
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $statusCor($status) }}">{{ $statusLabel[$status] ?? $status }} · {{ $fmtN($total) }}</span>
                                @empty
                                    <span class="text-[11px] text-gray-400">Sem consultas.</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Documentos</p>
                            <p class="text-lg font-bold text-gray-900">{{ $fmtN(($uso['efd_notas'] ?? 0) + ($uso['xml_notas'] ?? 0)) }}</p>
                            <p class="text-[11px] text-gray-500">{{ $fmtN($uso['efd_notas'] ?? 0) }} EFD · {{ $fmtN($uso['xml_notas'] ?? 0) }} XML</p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                @forelse($uso['importacoes_por_status'] ?? [] as $status => $total)
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $statusCor($status) }}">{{ $statusLabel[$status] ?? $status }} · {{ $fmtN($total) }}</span>
                                @empty
                                    <span class="text-[11px] text-gray-400">Sem importações.</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Clearance DF-e</p>
                            <p class="text-lg font-bold text-gray-900">{{ $fmtN(array_sum($uso['clearance_por_status'] ?? [])) }}</p>
                            <p class="text-[11px] text-gray-500">{{ $fmtN($uso['clearance_30d'] ?? 0) }} nos últimos 30 dias</p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                @forelse($uso['clearance_por_status'] ?? [] as $status => $total)
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $statusCor($status) }}">{{ $statusLabel[$status] ?? $status }} · {{ $fmtN($total) }}</span>
                                @empty
                                    <span class="text-[11px] text-gray-400">Sem verificações.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Financeiro</p>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-gray-200">
                        <div class="p-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Saldo atual</p>
                            <p class="text-lg font-bold text-gray-900">{{ $fmtR(($usuario->credits)) }}</p>
                            <p class="text-[11px] text-gray-500">Saldo disponível para uso</p>
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Pago aprovado</p>
                            <p class="text-lg font-bold text-gray-900">{{ $fmtR($financeiro['total_pago'] ?? 0) }}</p>
                            <p class="text-[11px] text-gray-500">Última compra: {{ ($financeiro['ultima_compra_em'] ?? null) ? $fmtData($financeiro['ultima_compra_em']) : '—' }}</p>
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consumo</p>
                            <p class="text-lg font-bold text-gray-900">{{ $fmtR((($financeiro['creditos_consumidos'] ?? 0))) }}</p>
                            <p class="text-[11px] text-gray-500">Débitos acumulados na conta</p>
                        </div>
                    </div>
                    <table class="w-full text-sm tabela-cards border-t border-gray-200">
                        <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="text-left px-3 py-2.5">Data</th>
                                <th class="text-left px-3 py-2.5">Tipo</th>
                                <th class="text-right px-3 py-2.5">Movimento</th>
                                <th class="text-right px-3 py-2.5">Saldo após</th>
                                <th class="text-left px-3 py-2.5">Descrição</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        @forelse($financeiro['movimentos_recentes'] ?? [] as $mov)
                            <tr>
                                <td class="px-3 py-2 text-[12px] text-gray-500" data-label="Data">{{ $fmtDataHora($mov->created_at) }}</td>
                                <td class="px-3 py-2" data-label="Tipo"><span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#374151">{{ $mov->type }}</span></td>
                                <td class="px-3 py-2 text-right font-mono font-semibold" data-label="Movimento" style="color:{{ ((float) $mov->amount) >= 0 ? '#047857' : '#b91c1c' }}">{{ ((float) $mov->amount) >= 0 ? '+' : '' }}{{ $fmtN($mov->amount) }}</td>
                                <td class="px-3 py-2 text-right font-mono text-gray-900" data-label="Saldo após">{{ $fmtN($mov->balance_after) }}</td>
                                <td class="px-3 py-2 text-[12px] text-gray-500" data-label="Descrição">{{ $mov->description ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-6 text-center text-gray-400 text-sm">Sem movimentos de saldo.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Atividade recente</p>
                    </div>
                    <table class="w-full text-sm tabela-cards">
                        <tbody class="divide-y divide-gray-100">
                        @forelse($timeline as $ev)
                            <tr>
                                <td class="px-3 py-2 w-40 text-[12px] text-gray-500" data-label="Data">{{ $fmtDataHora($ev['data'] ?? null) }}</td>
                                <td class="px-3 py-2 w-36" data-label="Tipo"><span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $tipoCor[$ev['tipo']] ?? '#374151' }}">{{ $tipoLabel[$ev['tipo']] ?? $ev['tipo'] }}</span></td>
                                <td class="px-3 py-2 text-gray-800" data-label="Título">{{ $ev['titulo'] }}</td>
                                <td class="px-3 py-2 text-[12px] text-gray-500" data-label="Detalhe">{{ $ev['detalhe'] ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-6 text-center text-gray-400 text-sm">Sem atividade registrada.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Ações administrativas</p>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="border border-gray-200 rounded p-3">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Assinatura e plano</p>
                                    <p class="text-[11px] text-gray-500">{{ $assinaturaAtual ? (($assinaturaAtual->plano_nome ?? 'Plano').' · '.$assinaturaAtual->status) : 'Sem assinatura local: cai no Free.' }}</p>
                                </div>
                            </div>
                            <form
                                method="POST"
                                action="{{ route('app.admin.usuarios.assinatura', $usuario->id) }}"
                                class="space-y-2"
                                data-admin-action-form="plan"
                                data-admin-action-user-id="{{ $usuario->id }}"
                                data-admin-action-user-name="{{ $nomeCompleto }}"
                                data-admin-action-user-email="{{ $usuario->email }}"
                                data-admin-action-current-plan="{{ $planoAtual }}"
                            >
                                @csrf
                                <select name="subscription_plan_id" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    <option value="">Sem assinatura local (Free fallback)</option>
                                    @foreach($planos as $pl)
                                        <option value="{{ $pl->id }}" @selected((string) old('subscription_plan_id', $assinaturaAtual->subscription_plan_id ?? '') === (string) $pl->id)>
                                            {{ $pl->nome }} ({{ $pl->codigo }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="grid grid-cols-1 min-[420px]:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-gray-400 mb-1">Status</label>
                                        <select name="status" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                            @foreach(\App\Services\Admin\AdminAcaoService::STATUS_ASSINATURA as $st)
                                                <option value="{{ $st }}" @selected(old('status', $assinaturaAtual->status ?? 'ativa') === $st)>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-400 mb-1">Ciclo</label>
                                        <select name="ciclo" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                            @foreach(['mensal', 'anual'] as $ciclo)
                                                <option value="{{ $ciclo }}" @selected(old('ciclo', $assinaturaAtual->ciclo ?? 'mensal') === $ciclo)>{{ $ciclo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 min-[420px]:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-gray-400 mb-1">Início</label>
                                        <input type="datetime-local" name="iniciada_em" value="{{ old('iniciada_em', $fmtInputDataHora($assinaturaAtual->iniciada_em ?? null)) }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-400 mb-1">Renova em</label>
                                        <input type="datetime-local" name="renova_em" value="{{ old('renova_em', $fmtInputDataHora($assinaturaAtual->renova_em ?? null)) }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 min-[420px]:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-gray-400 mb-1">Próximo grant</label>
                                        <input type="datetime-local" name="proximo_grant_em" value="{{ old('proximo_grant_em', $fmtInputDataHora($assinaturaAtual->proximo_grant_em ?? null)) }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-400 mb-1">Último grant</label>
                                        <input type="datetime-local" name="ultimo_grant_em" value="{{ old('ultimo_grant_em', $fmtInputDataHora($assinaturaAtual->ultimo_grant_em ?? null)) }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-gray-400 mb-1">Bucket</label>
                                        <input type="number" min="0" name="creditos_inclusos_saldo" value="{{ old('creditos_inclusos_saldo', $assinaturaAtual->creditos_inclusos_saldo ?? 0) }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-400 mb-1">Cap auto</label>
                                        <input type="number" min="0" name="limite_consumo_automatico" value="{{ old('limite_consumo_automatico', $assinaturaAtual->limite_consumo_automatico ?? '') }}" placeholder="default" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-400 mb-1">Assentos extra</label>
                                        <input type="number" min="0" name="assentos_extras" value="{{ old('assentos_extras', $assinaturaAtual->assentos_extras ?? 0) }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    </div>
                                </div>
                                <input type="text" name="mp_preapproval_id" value="{{ old('mp_preapproval_id', $assinaturaAtual->mp_preapproval_id ?? '') }}" placeholder="mp_preapproval_id (opcional)" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                <input type="text" name="motivo" placeholder="Motivo obrigatório" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                                <button type="submit" class="w-full text-white text-[12px] font-semibold px-3 py-2 rounded" style="background-color:#0b1f3a">Salvar assinatura/plano</button>
                                <p class="text-[11px] text-gray-500">Status diferente de <strong>ativa</strong> não libera entitlements pagos. Esta ação não sincroniza cobrança no Mercado Pago.</p>
                            </form>
                        </div>

                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Trial</p>
                            <form method="POST" action="{{ route('app.admin.usuarios.trial', $usuario->id) }}" class="space-y-2 mt-2" onsubmit="return confirm('Alterar os campos de trial deste usuário?');">
                                @csrf
                                <label class="inline-flex items-center gap-2 text-[13px] text-gray-700">
                                    <input type="hidden" name="trial_used" value="0">
                                    <input type="checkbox" name="trial_used" value="1" @checked(old('trial_used', $usuario->trial_used)) class="h-4 w-4 rounded border-gray-300">
                                    Trial usado/ativo
                                </label>
                                <div class="grid grid-cols-1 min-[420px]:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-gray-400 mb-1">Início</label>
                                        <input type="datetime-local" name="trial_started_at" value="{{ old('trial_started_at', $fmtInputDataHora($usuario->trial_started_at)) }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-400 mb-1">Expira em</label>
                                        <input type="datetime-local" name="trial_expires_at" value="{{ old('trial_expires_at', $fmtInputDataHora($usuario->trial_expires_at)) }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <input type="number" min="0" name="trial_credits_granted" value="{{ old('trial_credits_granted', $usuario->trial_credits_granted) }}" title="Concedidos" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="Concedidos">
                                    <input type="number" min="0" name="trial_credits_remaining" value="{{ old('trial_credits_remaining', $usuario->trial_credits_remaining) }}" title="Restantes" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="Restantes">
                                    <input type="number" min="0" name="trial_credits_expired" value="{{ old('trial_credits_expired', $usuario->trial_credits_expired) }}" title="Expirados" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="Expirados">
                                </div>
                                <input type="text" name="trial_source" value="{{ old('trial_source', $usuario->trial_source) }}" placeholder="Origem do trial" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                <input type="text" name="motivo" placeholder="Motivo obrigatório" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                                <button class="w-full text-white text-[12px] font-semibold px-3 py-2 rounded" style="background-color:#334155">Salvar trial</button>
                            </form>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('app.admin.usuarios.creditar', $usuario->id) }}"
                            class="space-y-2"
                            data-credit-form
                            data-saldo="{{ $usuario->credits }}"
                            data-admin-action-form="credit"
                            data-admin-action-user-id="{{ $usuario->id }}"
                            data-admin-action-user-name="{{ $nomeCompleto }}"
                            data-admin-action-user-email="{{ $usuario->email }}"
                        >
                            @csrf
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Ajuste de saldo (R$)</label>
                                <input type="number" step="0.01" name="valor" data-credit-input placeholder="ex.: 50,00 ou -20,00" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                                <p class="text-[11px] mt-1.5 hidden" data-credit-preview></p>
                                <p class="text-[11px] text-gray-500 mt-1">Valor negativo debita. A operação fica registrada na trilha administrativa.</p>
                            </div>
                            <input type="text" name="motivo" placeholder="Motivo obrigatório" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                            <button type="submit" class="w-full text-white text-[12px] font-semibold px-3 py-2 rounded" style="background-color:#1d4ed8">Aplicar ajuste</button>
                        </form>

                        <div class="border-t border-gray-200 pt-4 space-y-3">
                            <form method="POST" action="{{ route('app.admin.usuarios.bloquear', $usuario->id) }}" class="space-y-2" onsubmit="return confirm('Confirmar alteração do bloqueio de acesso?');">
                                @csrf
                                <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Acesso</label>
                                <input type="text" name="motivo" placeholder="Motivo obrigatório" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required @disabled($ehEu)>
                                <button @disabled($ehEu) class="w-full text-white text-[12px] font-semibold px-3 py-2 rounded disabled:opacity-40" style="background-color:{{ $usuario->bloqueado_em ? '#047857' : '#b91c1c' }}">{{ $usuario->bloqueado_em ? 'Desbloquear usuário' : 'Bloquear usuário' }}</button>
                                @if($ehEu)<p class="text-[11px] text-gray-500">Operador não pode bloquear a própria conta.</p>@endif
                            </form>

                            <form method="POST" action="{{ route('app.admin.usuarios.admin', $usuario->id) }}" class="space-y-2" onsubmit="return confirm('Confirmar alteração de permissão administrativa?');">
                                @csrf
                                <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Permissão admin</label>
                                <input type="text" name="motivo" placeholder="Motivo obrigatório" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required @disabled($ehEu)>
                                <button @disabled($ehEu) class="w-full text-white text-[12px] font-semibold px-3 py-2 rounded disabled:opacity-40" style="background-color:#334155">{{ $usuario->is_admin ? 'Remover admin' : 'Tornar admin' }}</button>
                                @if($ehEu)<p class="text-[11px] text-gray-500">Operador não pode rebaixar a própria conta.</p>@endif
                            </form>

                            @if($podeImpersonar)
                                <form method="POST" action="{{ route('app.admin.usuarios.impersonar', $usuario->id) }}" class="space-y-2" onsubmit="return confirm('Entrar como este usuário em modo de leitura?');">
                                    @csrf
                                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Impersonação</label>
                                    <input type="text" name="motivo" placeholder="Motivo obrigatório" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                                    <button class="w-full text-white text-[12px] font-semibold px-3 py-2 rounded" style="background-color:#7c3aed">Impersonar em leitura</button>
                                </form>
                            @else
                                <div class="border border-gray-200 rounded p-3">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Impersonação</p>
                                    <p class="text-[12px] text-gray-500 mt-1">{{ $motivoImpersonacaoBloqueada ?: 'Indisponível para este usuário.' }}</p>
                                </div>
                            @endif
                        </div>

                        @include('autenticado.admin.usuarios._delete_zone', [
                            'deleteUsuario' => $usuario,
                            'deletePodeExcluir' => ! $ehEu && ! $usuario->is_admin && ! $usuario->anonimizado_em,
                        ])
                    </div>
                </div>

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Perfil de cadastro</p>
                    </div>
                    <dl class="p-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Empresa</dt><dd class="text-gray-900 text-right">{{ $usuario->empresa ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">CNPJ</dt><dd class="text-gray-900 text-right">{{ $fmtDoc($usuario->cnpj) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Cargo</dt><dd class="text-gray-900 text-right">{{ $usuario->cargo ?: '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Faturamento anual</dt><dd class="text-gray-900 text-right">{{ config('cadastro.faturamento.'.$usuario->faturamento_anual, $usuario->faturamento_anual ?: '—') }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Desafio principal</dt><dd class="text-gray-900 text-right">{{ config('cadastro.desafios.'.$usuario->desafio_principal, $usuario->desafio_principal ?: '—') }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Desafio secundário</dt><dd class="text-gray-900 text-right">{{ config('cadastro.desafios.'.$usuario->desafio_secundario, $usuario->desafio_secundario ?: '—') }}</dd></div>
                    </dl>
                </div>

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Trilha administrativa</p>
                    </div>
                    <table class="w-full text-sm tabela-cards">
                        <tbody class="divide-y divide-gray-100">
                        @forelse($trilhaAdmin as $log)
                            <tr>
                                <td class="px-3 py-2 text-[12px] text-gray-500" data-label="Data">{{ $fmtDataHora($log->created_at) }}</td>
                                <td class="px-3 py-2" data-label="Ação"><span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#374151">{{ $log->acao }}</span></td>
                                <td class="px-3 py-2 text-[12px] text-gray-700" data-label="Motivo">{{ $log->motivo }} <span class="text-gray-400">· {{ $log->admin->name ?? '—' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-3 py-6 text-center text-gray-400 text-sm">Sem ações administrativas.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @include('autenticado.admin.usuarios._action_confirm_modal', [
                'adminActionConfirmMode' => 'fixed',
                'adminActionConfirmId' => 'admin-user-action-confirm-'.$usuario->id,
            ])
        </div>
    </div>
</div>
<script src="/js/admin-usuarios-acoes.js?v={{ @filemtime(public_path('js/admin-usuarios-acoes.js')) ?: time() }}"></script>
