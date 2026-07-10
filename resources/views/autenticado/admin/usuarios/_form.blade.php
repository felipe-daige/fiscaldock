@php
    $isEdit = isset($usuario) && $usuario;
    $u = $usuario;
    $input = 'w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded focus:border-gray-800 focus:ring-0 focus:outline-none';
    $label = 'block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1';
@endphp

<form method="POST" action="{{ $isEdit ? route('app.admin.usuarios.update', $u->id) : route('app.admin.usuarios.store') }}" class="space-y-4">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    {{-- 1. Conta --}}
    <div class="bg-white rounded border border-gray-300 border-l-2 overflow-hidden" style="border-left-color:#0b1f3a">
        <div class="px-4 py-2.5 border-b border-gray-200 bg-gray-50">
            <h2 class="text-[12px] font-bold text-gray-900 uppercase tracking-wide"><span class="text-gray-400">1.</span> Conta</h2>
            <p class="text-[10px] text-gray-400">Dados de acesso e identificação.</p>
        </div>
        <div class="p-4 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="{{ $label }}">Nome</label>
                    <input name="name" value="{{ old('name', $u->name ?? '') }}" class="{{ $input }}" required>
                </div>
                <div>
                    <label class="{{ $label }}">Sobrenome</label>
                    <input name="sobrenome" value="{{ old('sobrenome', $u->sobrenome ?? '') }}" class="{{ $input }}" required>
                </div>
                <div>
                    <label class="{{ $label }}">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $u->email ?? '') }}" class="{{ $input }}" required>
                </div>
                <div>
                    <label class="{{ $label }}">Telefone</label>
                    <input name="telefone" value="{{ old('telefone', $u->telefone ?? '') }}" class="{{ $input }}" required>
                </div>
                <div>
                    <label class="{{ $label }}">{{ $isEdit ? 'Nova senha' : 'Senha inicial' }}</label>
                    <input type="password" name="password" class="{{ $input }}" {{ $isEdit ? '' : 'required' }} autocomplete="new-password">
                    @if($isEdit)
                        <p class="text-[10px] text-gray-400 mt-1">Deixe em branco para manter a senha atual.</p>
                    @endif
                </div>
                @if(! $isEdit)
                    <div>
                        <label class="{{ $label }}">Saldo inicial (R$)</label>
                        <input type="number" step="0.01" min="0" name="credits" value="{{ old('credits', 0) }}" class="{{ $input }}" required>
                    </div>
                @endif
            </div>

            {{-- Flags de conta: as sensíveis com destaque --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 pt-1">
                @foreach([
                    'email_verified' => ['E-mail verificado', (bool) ($u->email_verified_at ?? false), false],
                    'marketing_opt_in' => ['Marketing opt-in', (bool) ($u->marketing_opt_in ?? false), false],
                    'is_admin' => ['Operador admin', (bool) ($u->is_admin ?? false), true],
                    'bloqueado' => ['Acesso bloqueado', (bool) ($u->bloqueado_em ?? false), true],
                ] as $field => [$txt, $checked, $sensivel])
                    <label class="flex items-center gap-2 text-[13px] rounded border px-3 py-2 cursor-pointer {{ $sensivel ? 'hover:bg-orange-50' : 'hover:bg-gray-50' }}"
                           style="{{ $sensivel ? 'border-color:#fed7aa' : 'border-color:#e5e7eb' }}">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $checked)) class="h-4 w-4 rounded border-gray-300">
                        <span class="{{ $sensivel ? 'text-gray-900 font-medium' : 'text-gray-700' }}">{{ $txt }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 2. Perfil comercial --}}
    <div class="bg-white rounded border border-gray-300 border-l-2 overflow-hidden" style="border-left-color:#1e4679">
        <div class="px-4 py-2.5 border-b border-gray-200 bg-gray-50">
            <h2 class="text-[12px] font-bold text-gray-900 uppercase tracking-wide"><span class="text-gray-400">2.</span> Perfil comercial</h2>
            <p class="text-[10px] text-gray-400">Dados de segmentação (não afetam entitlements).</p>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="{{ $label }}">Empresa</label>
                <input name="empresa" value="{{ old('empresa', $u->empresa ?? '') }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">CNPJ</label>
                <input name="cnpj" value="{{ old('cnpj', $u->cnpj ?? '') }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Cargo</label>
                <input name="cargo" value="{{ old('cargo', $u->cargo ?? '') }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $label }}">Faturamento anual</label>
                <select name="faturamento_anual" class="{{ $input }}">
                    <option value="">Não informado</option>
                    @foreach($faturamentos as $key => $txt)
                        <option value="{{ $key }}" @selected(old('faturamento_anual', $u->faturamento_anual ?? '') === $key)>{{ $txt }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Desafio principal</label>
                <select name="desafio_principal" class="{{ $input }}">
                    <option value="">Não informado</option>
                    @foreach($desafios as $key => $txt)
                        <option value="{{ $key }}" @selected(old('desafio_principal', $u->desafio_principal ?? '') === $key)>{{ $txt }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $label }}">Desafio secundário</label>
                <select name="desafio_secundario" class="{{ $input }}">
                    <option value="">Não informado</option>
                    @foreach($desafios as $key => $txt)
                        <option value="{{ $key }}" @selected(old('desafio_secundario', $u->desafio_secundario ?? '') === $key)>{{ $txt }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- 3. Preferências e auditoria --}}
    <div class="bg-white rounded border border-gray-300 border-l-2 overflow-hidden" style="border-left-color:#047857">
        <div class="px-4 py-2.5 border-b border-gray-200 bg-gray-50">
            <h2 class="text-[12px] font-bold text-gray-900 uppercase tracking-wide"><span class="text-gray-400">3.</span> Preferências e auditoria</h2>
            <p class="text-[10px] text-gray-400">Notificações e ações administrativas.</p>
        </div>
        <div class="p-4 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach([
                    'alertas_operacionais' => ['Alertas operacionais', (bool) ($u->alertas_operacionais ?? true), false],
                    'alertas_monitoramento' => ['Alertas de monitoramento', (bool) ($u->alertas_monitoramento ?? true), false],
                    'resumo_periodico' => ['Resumo periódico', (bool) ($u->resumo_periodico ?? true), false],
                    'force_terms_reaccept' => ['Forçar reaceite legal no próximo acesso', false, true],
                ] as $field => [$txt, $checked, $sensivel])
                    <label class="flex items-center gap-2 text-[13px] rounded border px-3 py-2 cursor-pointer {{ $sensivel ? 'hover:bg-orange-50' : 'hover:bg-gray-50' }}"
                           style="{{ $sensivel ? 'border-color:#fed7aa' : 'border-color:#e5e7eb' }}">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $checked)) class="h-4 w-4 rounded border-gray-300">
                        <span class="{{ $sensivel ? 'text-gray-900 font-medium' : 'text-gray-700' }}">{{ $txt }}</span>
                    </label>
                @endforeach
            </div>
            <div>
                <label class="{{ $label }}">Motivo (trilha administrativa)</label>
                <input name="motivo" value="{{ old('motivo') }}" class="{{ $input }}" required maxlength="500" placeholder="Ex.: ajuste solicitado pelo suporte">
                <p class="text-[10px] text-gray-400 mt-1">Registrado em <code>admin_action_logs</code>.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 min-[380px]:grid-cols-2 sm:flex sm:items-center sm:justify-end gap-2 pt-1">
        <a href="{{ $isEdit ? route('app.admin.usuarios.show', $u->id) : route('app.admin.usuarios.index') }}" data-link class="admin-action inline-flex items-center justify-center px-4 py-2.5 rounded text-[12px] font-bold uppercase tracking-wide text-white" style="background-color:#6b7280">Cancelar</a>
        <button class="w-full sm:w-auto px-5 py-2.5 rounded text-[12px] font-bold uppercase tracking-wide text-white hover:opacity-90" style="background-color:#0b1f3a">{{ $isEdit ? 'Salvar usuário' : 'Criar usuário' }}</button>
    </div>
</form>
