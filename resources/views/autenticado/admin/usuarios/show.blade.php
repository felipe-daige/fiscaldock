@php
    $pricing = app(\App\Services\PricingCatalogService::class);
    $fmtR = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $fmtN = fn ($v) => number_format((float) $v, 0, ',', '.');
    $fmtData = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '—';
    $fmtDataHora = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y H:i') : '—';
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
        'credito' => 'Crédito',
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
    $ehEu = auth()->id() === $usuario->id;
    $podeImpersonar = ! $usuario->is_admin && ! $ehEu && ! $usuario->bloqueado_em;
    $motivoImpersonacaoBloqueada = $ehEu ? 'Não é possível impersonar a própria conta.' : ($usuario->is_admin ? 'Impersonação de admin é bloqueada.' : ($usuario->bloqueado_em ? 'Usuário bloqueado não pode ser impersonado.' : null));
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6">
            <a href="{{ route('app.admin.usuarios.index') }}" data-link class="text-[12px] text-gray-600 hover:text-gray-900 hover:underline">← Voltar para usuários</a>
            <div class="mt-2 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">{{ $nomeCompleto }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $usuario->email }} · {{ $usuario->empresa ?: 'sem empresa' }} · CNPJ {{ $fmtDoc($usuario->cnpj) }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @if($usuario->is_admin)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#334155">Admin</span>
                    @endif
                    @if($usuario->bloqueado_em)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#b91c1c">Bloqueado</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#047857">Acesso ativo</span>
                    @endif
                    @if($conta['lgpd_solicitada'] ?? false)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#dc2626">LGPD</span>
                    @endif
                    @if($conta['tem_compra_confirmada'] ?? false)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#047857">Compra confirmada</span>
                    @endif
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#374151">{{ $conta['trial_status'] ?? 'Sem trial' }}</span>
                </div>
            </div>
        </div>

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

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
            @foreach([
                ['Saldo', $fmtR($pricing->creditsToCurrency((int) $usuario->credits)), $fmtN($usuario->credits).' em saldo bruto', '#1d4ed8'],
                ['Total pago', $fmtR($financeiro['total_pago'] ?? $kpis['total_pago']), $fmtN($financeiro['compras_aprovadas'] ?? 0).' compra(s) aprovada(s)', '#047857'],
                ['Consumo total', $fmtR($pricing->creditsToCurrency((int) ($financeiro['creditos_consumidos'] ?? $kpis['creditos_consumidos']))), 'débitos de saldo', '#b45309'],
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
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $statusCor($status) }}">{{ $statusLabel[$status] ?? $status }} · {{ $fmtN($total) }}</span>
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
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $statusCor($status) }}">{{ $statusLabel[$status] ?? $status }} · {{ $fmtN($total) }}</span>
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
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $statusCor($status) }}">{{ $statusLabel[$status] ?? $status }} · {{ $fmtN($total) }}</span>
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
                            <p class="text-lg font-bold text-gray-900">{{ $fmtR($pricing->creditsToCurrency((int) $usuario->credits)) }}</p>
                            <p class="text-[11px] text-gray-500">Campo legado `credits`: {{ $fmtN($usuario->credits) }}</p>
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Pago aprovado</p>
                            <p class="text-lg font-bold text-gray-900">{{ $fmtR($financeiro['total_pago'] ?? 0) }}</p>
                            <p class="text-[11px] text-gray-500">Última compra: {{ ($financeiro['ultima_compra_em'] ?? null) ? $fmtData($financeiro['ultima_compra_em']) : '—' }}</p>
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consumo</p>
                            <p class="text-lg font-bold text-gray-900">{{ $fmtR($pricing->creditsToCurrency((int) ($financeiro['creditos_consumidos'] ?? 0))) }}</p>
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
                                <td class="px-3 py-2 text-[12px] text-gray-500">{{ $fmtDataHora($mov->created_at) }}</td>
                                <td class="px-3 py-2" data-label="Tipo"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#374151">{{ $mov->type }}</span></td>
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
                                <td class="px-3 py-2 w-40 text-[12px] text-gray-500">{{ $fmtDataHora($ev['data'] ?? null) }}</td>
                                <td class="px-3 py-2 w-36" data-label="Tipo"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $tipoCor[$ev['tipo']] ?? '#374151' }}">{{ $tipoLabel[$ev['tipo']] ?? $ev['tipo'] }}</span></td>
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
                        <form method="POST" action="{{ route('app.admin.usuarios.creditar', $usuario->id) }}" class="space-y-2" onsubmit="return confirm('Aplicar este ajuste de saldo?');">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Ajuste de saldo</label>
                                <input type="number" step="1" name="valor" placeholder="ex.: 50 ou -20" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                                <p class="text-[11px] text-gray-500 mt-1">Valor negativo debita. A operação fica registrada na trilha administrativa.</p>
                            </div>
                            <input type="text" name="motivo" placeholder="Motivo obrigatório" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                            <button class="w-full text-white text-[12px] font-semibold px-3 py-2 rounded" style="background-color:#1d4ed8">Aplicar ajuste</button>
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
                                <td class="px-3 py-2 text-[12px] text-gray-500">{{ $fmtDataHora($log->created_at) }}</td>
                                <td class="px-3 py-2" data-label="Ação"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#374151">{{ $log->acao }}</span></td>
                                <td class="px-3 py-2 text-[12px] text-gray-700" data-label="Motivo">{{ $log->motivo }} <span class="text-gray-400">· {{ $log->admin->name ?? '—' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-3 py-6 text-center text-gray-400 text-sm">Sem ações administrativas.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
