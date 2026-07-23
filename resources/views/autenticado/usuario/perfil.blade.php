@php
    $user ??= Auth::user();
    $fullName = trim(($user->name ?? '') . ' ' . ($user->sobrenome ?? ''));
    $fullName = $fullName !== '' ? $fullName : ($user->email ?? 'Usuário');
    $initials = strtoupper(trim(sprintf('%s%s',
        mb_substr(trim($user->name ?? ''), 0, 1),
        ! empty($user->sobrenome) ? mb_substr($user->sobrenome, 0, 1) : ''
    )));
    $initials = $initials !== '' ? $initials : 'U';
    $memberSince = $user->created_at ? $user->created_at->format('d/m/Y') : '—';
    $saldoReais ??= 0;
    $trialAtivo ??= false;
    $trialExpiraEm ??= null;
    $trialCreditosRestantes ??= null;
    $planoLabel = $trialAtivo ? 'Trial ativo' : 'Conta ativa';
    $planoHex = $trialAtivo ? '#2563eb' : '#047857';
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">

        <div>
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Perfil</h1>
            <p class="text-xs text-gray-500 mt-1">Gerencie seus dados, senha e informações da conta.</p>
        </div>

        @if(session('success'))
            <div class="rounded px-3 py-2 text-[12px]" style="background-color: #ecfdf5; color: #047857">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded px-3 py-2 text-[12px]" style="background-color: #fef2f2; color: #b91c1c">{{ session('error') }}</div>
        @endif

        {{-- Hero --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="px-4 py-5 flex items-center gap-4">
                <div class="w-14 h-14 rounded border border-gray-200 bg-gray-50 flex items-center justify-center text-xl font-bold text-gray-900">
                    {{ $initials }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $fullName }}</p>
                    <p class="text-[11px] text-gray-500 truncate">{{ $user->email ?? '—' }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $planoHex }}">{{ $planoLabel }}</span>
                        <span class="text-[10px] text-gray-500">Membro desde {{ $memberSince }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Coluna esquerda: forms --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Dados pessoais --}}
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Dados pessoais</span>
                    </div>
                    <form id="form-perfil" class="px-4 py-5 space-y-4">
                        <div id="msg-perfil" class="hidden text-[12px] rounded px-3 py-2"></div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[11px] text-gray-500 block mb-1">Nome</label>
                                <input name="name" value="{{ $user->name ?? '' }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                            </div>
                            <div>
                                <label class="text-[11px] text-gray-500 block mb-1">Sobrenome</label>
                                <input name="sobrenome" value="{{ $user->sobrenome ?? '' }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                            </div>
                            <div>
                                <label class="text-[11px] text-gray-500 block mb-1">Telefone</label>
                                <input name="telefone" value="{{ $user->telefone ?? '' }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                            </div>
                            <div>
                                <label class="text-[11px] text-gray-500 block mb-1">CPF do responsável</label>
                                <input name="cpf" value="{{ $user->cpf ? \App\Support\Cpf::formatar($user->cpf) : '' }}" inputmode="numeric" placeholder="000.000.000-00" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                                <p class="text-[11px] text-gray-500 mt-1">Solicitante das certidões judiciais (CEAT e afins).</p>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded" style="background-color: #1f2937">Salvar</button>
                        </div>
                    </form>
                </div>

                {{-- E-mail de acesso --}}
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">E-mail de acesso</span>
                        @if($user->hasVerifiedEmail())
                            <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded text-white" style="background-color: #047857">Verificado</span>
                        @else
                            <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded text-white" style="background-color: #b45309">Não verificado</span>
                        @endif
                    </div>
                    <div class="px-4 py-5 space-y-4">
                        <div id="msg-email" class="hidden text-[12px] rounded px-3 py-2"></div>

                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <div>
                                <p class="text-[13px] text-gray-900 font-medium">{{ $user->email }}</p>
                                @unless($user->hasVerifiedEmail())
                                    <p class="text-[11px] text-gray-500 mt-0.5">Confirme seu e-mail para receber avisos de pagamento, alertas e monitoramento.</p>
                                @endunless
                            </div>
                            @unless($user->hasVerifiedEmail())
                                <button type="button" id="btn-reenviar-verificacao" class="px-3 py-2 text-[12px] font-medium text-gray-700 border border-gray-300 rounded">Reenviar verificação</button>
                            @endunless
                        </div>

                        @if($user->pending_email)
                            <div class="rounded px-3 py-2 text-[12px] flex items-center justify-between gap-3 flex-wrap" style="background-color: #fffbeb; color: #92400e">
                                <span>Troca pendente para <strong>{{ $user->pending_email }}</strong> — confirme pelo link enviado a esse endereço. Até lá, o e-mail atual continua valendo.</span>
                                <button type="button" id="btn-cancelar-troca-email" class="px-2 py-1 text-[11px] font-medium border rounded" style="border-color: #92400e">Cancelar</button>
                            </div>
                        @endif

                        <form id="form-email" class="space-y-4 pt-2 border-t border-gray-100">
                            <p class="text-[11px] text-gray-500 pt-3">Trocar o e-mail exige confirmação no endereço NOVO. Nada muda até você clicar no link que enviarmos para lá.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[11px] text-gray-500 block mb-1">Novo e-mail</label>
                                    <input type="email" name="email" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                                </div>
                                <div>
                                    <label class="text-[11px] text-gray-500 block mb-1">Senha atual</label>
                                    <input type="password" name="current_password" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded">Enviar confirmação</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Segurança --}}
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Segurança</span>
                    </div>
                    <form id="form-senha" class="px-4 py-5 space-y-4">
                        <div id="msg-senha" class="hidden text-[12px] rounded px-3 py-2"></div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="text-[11px] text-gray-500 block mb-1">Senha atual</label>
                                <input type="password" name="current_password" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                            </div>
                            <div>
                                <label class="text-[11px] text-gray-500 block mb-1">Nova senha</label>
                                <input type="password" name="password" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                            </div>
                            <div>
                                <label class="text-[11px] text-gray-500 block mb-1">Confirmar nova senha</label>
                                <input type="password" name="password_confirmation" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded" style="background-color: #1f2937">Alterar senha</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Coluna direita: conta & plano --}}
            <div class="space-y-6">
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Conta &amp; Plano</span>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="text-center py-2">
                            <p class="text-[11px] text-gray-500">Saldo disponível</p>
                            <p class="text-3xl font-bold text-gray-900">@brl($saldoReais)</p>
                        </div>
                        @if($trialAtivo)
                            <div class="rounded border border-gray-200 px-3 py-2 text-[12px] text-gray-700">
                                Trial ativo — @brl((($trialCreditosRestantes ?? 0))) em saldo promocional
                                @if($trialExpiraEm) · expira {{ $trialExpiraEm->format('d/m/Y') }} @endif
                            </div>
                        @endif
                        <dl class="space-y-2 text-[12px]">
                            <div class="flex justify-between"><dt class="text-gray-500">Membro desde</dt><dd class="text-gray-900">{{ $memberSince }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">ID</dt><dd class="text-gray-900">{{ $user->id ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Marketing</dt><dd class="text-gray-900">{{ $user->marketing_opt_in ? 'Inscrito' : 'Não' }}</dd></div>
                        </dl>
                        <div class="grid grid-cols-1 gap-2 pt-2">
                            <a href="/app/saldo" data-link class="text-center px-3 py-2 text-[13px] font-medium text-white rounded" style="background-color: #1f2937">Adicionar saldo</a>
                            <a href="/app/planos" data-link class="text-center px-3 py-2 text-[13px] font-medium text-gray-700 border border-gray-300 rounded">Meu plano</a>
                            <a href="/app/configuracoes" data-link class="text-center px-3 py-2 text-[13px] font-medium text-gray-700 border border-gray-300 rounded">Notificações</a>
                            <a href="/app/privacidade" data-link class="text-center px-3 py-2 text-[13px] font-medium text-gray-700 border border-gray-300 rounded">Privacidade &amp; excluir conta</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta ? meta.getAttribute('content') : '';

    function showMsg(el, ok, text) {
        el.classList.remove('hidden');
        el.style.backgroundColor = ok ? '#ecfdf5' : '#fef2f2';
        el.style.color = ok ? '#047857' : '#b91c1c';
        el.textContent = text;
    }

    function submitForm(form, url, method, msgEl, onOk) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var body = {};
            new FormData(form).forEach(function (v, k) { body[k] = v; });
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(body)
            }).then(function (r) {
                return r.json().then(function (data) { return { ok: r.ok, data: data }; });
            }).then(function (res) {
                if (res.ok && res.data.success) {
                    showMsg(msgEl, true, res.data.message || 'Salvo com sucesso.');
                    if (onOk) onOk();
                } else {
                    var first = res.data.errors ? Object.values(res.data.errors)[0][0] : (res.data.message || 'Erro ao salvar.');
                    showMsg(msgEl, false, first);
                }
            }).catch(function () {
                showMsg(msgEl, false, 'Falha de conexão.');
            });
        });
    }

    var formPerfil = document.getElementById('form-perfil');
    if (formPerfil) submitForm(formPerfil, '/app/perfil', 'PATCH', document.getElementById('msg-perfil'));

    var formSenha = document.getElementById('form-senha');
    if (formSenha) submitForm(formSenha, '/app/perfil/senha', 'PUT', document.getElementById('msg-senha'), function () {
        formSenha.reset();
    });

    var msgEmail = document.getElementById('msg-email');

    var formEmail = document.getElementById('form-email');
    if (formEmail) submitForm(formEmail, '/app/perfil/email', 'PATCH', msgEmail, function () {
        formEmail.reset();
    });

    function acaoEmail(url, method) {
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (r) {
            return r.json().then(function (data) { return { ok: r.ok, data: data }; });
        }).then(function (res) {
            showMsg(msgEmail, res.ok && res.data.success, res.data.message || 'Erro na operação.');
        }).catch(function () {
            showMsg(msgEmail, false, 'Falha de conexão.');
        });
    }

    var btnReenviar = document.getElementById('btn-reenviar-verificacao');
    if (btnReenviar) btnReenviar.addEventListener('click', function () {
        acaoEmail('/app/perfil/email/reenviar', 'POST');
    });

    var btnCancelarTroca = document.getElementById('btn-cancelar-troca-email');
    if (btnCancelarTroca) btnCancelarTroca.addEventListener('click', function () {
        acaoEmail('/app/perfil/email', 'DELETE');
    });
})();
</script>
