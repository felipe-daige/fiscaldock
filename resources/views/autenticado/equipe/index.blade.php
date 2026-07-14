@php
    $roleLabels = array_merge(['owner' => 'Dono'], $roles);
    $available = max(0, $seatsIncluded - $seatsUsed);
    $inviteRole = old('papel', 'operador');
    if (!array_key_exists($inviteRole, $roles)) {
        $inviteRole = 'operador';
    }
    $invitePermissions = old('permissoes', $rolePresets[$inviteRole] ?? []);
@endphp

<div class="min-h-screen bg-gray-100 pb-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-5">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Conta</p>
                <h1 class="text-xl font-bold text-gray-900">Equipe e acessos</h1>
                <p class="text-sm text-gray-500 mt-1">Cada pessoa usa um login próprio e trabalha nos dados compartilhados de {{ $account->nome }}.</p>
            </div>
            <div class="bg-white border border-gray-300 rounded px-4 py-3 min-w-[220px]">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-medium text-gray-600">Assentos utilizados</span>
                    <strong class="text-gray-900">{{ $seatsUsed }} / {{ $seatsIncluded }}</strong>
                </div>
                <div class="mt-2 h-1.5 rounded-full overflow-hidden" style="background-color:#e5e7eb">
                    <div class="h-full" style="width:{{ min(100, $seatsIncluded > 0 ? ($seatsUsed / $seatsIncluded) * 100 : 100) }}%;background-color:{{ $available > 0 ? '#047857' : '#b45309' }}"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-2">Membros e convites pendentes ocupam assento.</p>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-4 bg-white border border-gray-300 border-l-4 rounded p-3 text-sm text-gray-700" style="border-left-color:#047857">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 bg-white border border-gray-300 border-l-4 rounded p-3 text-sm text-gray-700" style="border-left-color:#dc2626">
                <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
            <section class="lg:col-span-3 bg-white border border-gray-300 rounded overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-sm font-bold text-gray-900">Pessoas com acesso</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($members as $member)
                        @php
                            $canManageMember = !$member->isOwner()
                                && $member->user_id !== $context->actor()->id
                                && ($context->isOwner() || $member->papel !== 'admin');
                        @endphp
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <strong class="text-sm text-gray-900">{{ $member->user->name }} {{ $member->user->sobrenome }}</strong>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $member->isOwner() ? '#0b1f3a' : ($member->papel === 'admin' ? '#1e4679' : '#6b7280') }}">{{ $roleLabels[$member->papel] ?? $member->papel }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $member->user->email }}</p>
                                </div>
                                @if($canManageMember)
                                    <form method="POST" action="{{ route('app.equipe.membros.remover', $member->id) }}" onsubmit="return confirm('Remover este acesso? Os dados da conta serão preservados.')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-medium" style="color:#b91c1c">Remover</button>
                                    </form>
                                @endif
                            </div>

                            @if($canManageMember)
                                <details class="mt-3 border border-gray-200 rounded">
                                    <summary class="px-3 py-2 text-xs font-medium text-gray-600 cursor-pointer">Editar papel e módulos</summary>
                                    <form method="POST" action="{{ route('app.equipe.membros.atualizar', $member->id) }}" class="p-3 border-t border-gray-200 space-y-3" data-team-permissions-form>
                                        @csrf @method('PATCH')
                                        <select name="papel" class="w-full text-sm border border-gray-300 rounded px-3 py-2" data-team-role>
                                            @foreach($roles as $value => $label)
                                                <option value="{{ $value }}" @selected($member->papel === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-[11px] text-gray-500" data-team-role-help>Ao trocar o papel, os módulos recebem o preset correspondente.</p>
                                        <div class="grid sm:grid-cols-2 gap-2" data-team-permissions>
                                            @foreach($modules as $key => $label)
                                                <label class="flex items-center gap-2 text-xs text-gray-700">
                                                    <input type="hidden" name="permissoes[{{ $key }}]" value="0">
                                                    <input type="checkbox" name="permissoes[{{ $key }}]" value="1" data-team-permission="{{ $key }}" @checked((bool) data_get($member->permissoes, $key)) @disabled($member->papel === 'admin')>
                                                    {{ $label }}
                                                </label>
                                            @endforeach
                                        </div>
                                        <button class="px-3 py-2 rounded text-xs font-bold text-white" style="background-color:#0b1f3a">Salvar permissões</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="lg:col-span-2 space-y-5">
                <section class="bg-white border border-gray-300 rounded overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-sm font-bold text-gray-900">Convidar pessoa</h2>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $available }} assento(s) disponível(is).</p>
                    </div>
                    <form method="POST" action="{{ route('app.equipe.convites.criar') }}" class="p-4 space-y-3" data-team-permissions-form>
                        @csrf
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1">E-mail</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full text-sm border border-gray-300 rounded px-3 py-2" placeholder="pessoa@empresa.com">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-500 mb-1">Papel</label>
                            <select name="papel" class="w-full text-sm border border-gray-300 rounded px-3 py-2" data-team-role>
                                @foreach($roles as $value => $label)<option value="{{ $value }}" @selected($inviteRole === $value)>{{ $label }}</option>@endforeach
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1" data-team-role-help>O papel aplica um preset; você pode ajustar os módulos permitidos.</p>
                        </div>
                        <div class="space-y-2" data-team-permissions>
                            <p class="text-[11px] font-medium text-gray-500">Módulos liberados</p>
                            @foreach($modules as $key => $label)
                                <label class="flex items-center gap-2 text-xs text-gray-700">
                                    <input type="hidden" name="permissoes[{{ $key }}]" value="0">
                                    <input type="checkbox" name="permissoes[{{ $key }}]" value="1" data-team-permission="{{ $key }}" @checked((bool) data_get($invitePermissions, $key)) @disabled($inviteRole === 'admin')>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <button @disabled($available < 1) class="w-full px-3 py-2.5 rounded text-sm font-bold text-white disabled:opacity-50" style="background-color:#0b1f3a">Enviar convite</button>
                    </form>
                </section>

                @if($invitations->isNotEmpty())
                    <section class="bg-white border border-gray-300 rounded overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50"><h2 class="text-sm font-bold text-gray-900">Convites pendentes</h2></div>
                        <div class="divide-y divide-gray-200">
                            @foreach($invitations as $invite)
                                <div class="p-3 flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-gray-800 truncate">{{ $invite->email }}</p>
                                        <p class="text-[10px] text-gray-400">Expira {{ $invite->expira_em->diffForHumans() }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('app.equipe.convites.revogar', $invite->id) }}">
                                        @csrf @method('DELETE')
                                        <button class="text-[11px] font-medium" style="color:#b91c1c">Revogar</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="team-role-presets">@json($rolePresets)</script>
<script src="{{ asset('js/equipe.js') }}?v={{ is_file(public_path('js/equipe.js')) ? filemtime(public_path('js/equipe.js')) : 1 }}"></script>
