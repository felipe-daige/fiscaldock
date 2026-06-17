@push('structured-data')
    @include('landing_page.partials.breadcrumb-schema', [
        'trail' => [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Criar Conta Grátis', 'url' => url('/criar-conta')],
        ],
    ])
@endpush

<section id="criar-conta" class="bg-gray-100 min-h-[calc(100vh-80px)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-7">
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Trial FiscalDock</p>
                                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide mt-1">Criar conta grátis</h1>
                                <p class="text-xs text-gray-500 mt-1">Receba 100 créditos para usar em até 30 dias.</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #047857">
                                100 créditos
                            </span>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5">
                        {{-- Alerta de topo só para erro genérico do servidor (sem campo). Erros de
                             campo aparecem inline, abaixo de cada campo (ver .field-error). --}}
                        <div id="signup-alert" class="mb-4 hidden bg-white rounded border border-gray-300 p-3 border-l-4 border-l-red-500 text-sm text-gray-700"></div>

                        <form id="signup-form" class="space-y-5" method="POST" action="{{ route('signup.post') }}">
                            @csrf

                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-3">Dados do usuário</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="nome" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Nome</label>
                                        <input type="text" id="nome" name="nome" value="{{ old('nome') }}" required class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400" placeholder="João">
                                        <p class="field-error empty:hidden mt-1 text-[11px] text-red-600" data-error="nome">@error('nome'){{ $message }}@enderror</p>
                                    </div>
                                    <div>
                                        <label for="sobrenome" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Sobrenome</label>
                                        <input type="text" id="sobrenome" name="sobrenome" value="{{ old('sobrenome') }}" required class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400" placeholder="Silva">
                                        <p class="field-error empty:hidden mt-1 text-[11px] text-red-600" data-error="sobrenome">@error('sobrenome'){{ $message }}@enderror</p>
                                    </div>
                                    <div>
                                        <label for="signup-email" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">E-mail corporativo</label>
                                        <input type="email" id="signup-email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400" placeholder="seu@empresa.com.br">
                                        <p class="field-error empty:hidden mt-1 text-[11px] text-red-600" data-error="email">@error('email'){{ $message }}@enderror</p>
                                    </div>
                                    <div>
                                        <label for="telefone" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Telefone</label>
                                        <input type="tel" id="telefone" name="telefone" value="{{ old('telefone') }}" required class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400" placeholder="(67) 99984-4366">
                                        <p class="field-error empty:hidden mt-1 text-[11px] text-red-600" data-error="telefone">@error('telefone'){{ $message }}@enderror</p>
                                    </div>
                                    <div>
                                        <label for="senha" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Senha</label>
                                        <div class="relative">
                                            <input type="password" id="senha" name="senha" required minlength="8" class="w-full border border-gray-300 rounded text-sm pl-3 pr-10 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400" placeholder="Mínimo 8 caracteres">
                                            <button type="button" class="senha-toggle absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600" data-target="senha" aria-label="Mostrar senha" tabindex="-1">
                                                <svg class="icon-eye w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                                <svg class="icon-eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.22A10.5 10.5 0 002.25 12s3.75 7.5 9.75 7.5c1.6 0 3.06-.38 4.35-1.01M9.88 5.09A10.6 10.6 0 0112 4.5c6 0 9.75 7.5 9.75 7.5a17 17 0 01-2.83 3.74M9.9 9.9a3 3 0 104.2 4.2M3 3l18 18"/></svg>
                                            </button>
                                        </div>
                                        <p class="mt-1 text-[11px] text-gray-500">Pelo menos 8 caracteres, com uma letra e um número.</p>
                                        <p class="field-error empty:hidden mt-1 text-[11px] text-red-600" data-error="senha">@error('senha'){{ $message }}@enderror</p>
                                    </div>
                                    <div>
                                        <label for="senha_confirmation" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Confirmar senha</label>
                                        <div class="relative">
                                            <input type="password" id="senha_confirmation" name="senha_confirmation" required minlength="8" class="w-full border border-gray-300 rounded text-sm pl-3 pr-10 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400" placeholder="Repita a senha">
                                            <button type="button" class="senha-toggle absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600" data-target="senha_confirmation" aria-label="Mostrar senha" tabindex="-1">
                                                <svg class="icon-eye w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                                <svg class="icon-eye-off w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.22A10.5 10.5 0 002.25 12s3.75 7.5 9.75 7.5c1.6 0 3.06-.38 4.35-1.01M9.88 5.09A10.6 10.6 0 0112 4.5c6 0 9.75 7.5 9.75 7.5a17 17 0 01-2.83 3.74M9.9 9.9a3 3 0 104.2 4.2M3 3l18 18"/></svg>
                                            </button>
                                        </div>
                                        <p class="field-error empty:hidden mt-1 text-[11px] text-red-600" data-error="senha_confirmation">@error('senha_confirmation'){{ $message }}@enderror</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-5">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-3">Dados da empresa</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="empresa" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Empresa</label>
                                        <input type="text" id="empresa" name="empresa" value="{{ old('empresa') }}" required class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400" placeholder="Nome da empresa">
                                        <p class="field-error empty:hidden mt-1 text-[11px] text-red-600" data-error="empresa">@error('empresa'){{ $message }}@enderror</p>
                                    </div>
                                    <div>
                                        <label for="cargo" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Cargo</label>
                                        <input type="text" id="cargo" name="cargo" value="{{ old('cargo') }}" required class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400" placeholder="Ex: Contador, Financeiro, Sócio">
                                        <p class="field-error empty:hidden mt-1 text-[11px] text-red-600" data-error="cargo">@error('cargo'){{ $message }}@enderror</p>
                                    </div>
                                    <div>
                                        <label for="documento" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">CPF ou CNPJ</label>
                                        <input type="text" id="documento" name="documento" value="{{ old('documento') }}" required class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400" placeholder="000.000.000-00 ou 00.000.000/0000-00">
                                        <p class="field-error empty:hidden mt-1 text-[11px] text-red-600" data-error="documento">@error('documento'){{ $message }}@enderror</p>
                                    </div>
                                    <div>
                                        <label for="faturamento" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Faturamento anual</label>
                                        <select id="faturamento" name="faturamento" required class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                                            <option value="">Selecione</option>
                                            @foreach(config('cadastro.faturamento') as $valor => $label)
                                                <option value="{{ $valor }}" @selected(old('faturamento') === $valor)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <p class="field-error empty:hidden mt-1 text-[11px] text-red-600" data-error="faturamento">@error('faturamento'){{ $message }}@enderror</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-5">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-3">Principal desafio</p>
                                <div class="space-y-2 text-sm text-gray-700">
                                    @php $desafios = config('cadastro.desafios'); @endphp
                                    @foreach($desafios as $valor => $label)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="desafio_principal" value="{{ $valor }}" @checked(old('desafio_principal') === $valor) required class="text-gray-700 focus:ring-gray-500">
                                            <span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="field-error empty:hidden mt-2 text-[11px] text-red-600" data-error="desafio_principal">@error('desafio_principal'){{ $message }}@enderror</p>

                                <div class="mt-4">
                                    <label for="desafio_secundario" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Desafio secundário (opcional)</label>
                                    <select id="desafio_secundario" name="desafio_secundario" class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                                        <option value="">Nenhum</option>
                                        @foreach($desafios as $valor => $label)
                                            <option value="{{ $valor }}" @selected(old('desafio_secundario') === $valor)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p class="field-error empty:hidden mt-1 text-[11px] text-red-600" data-error="desafio_secundario">@error('desafio_secundario'){{ $message }}@enderror</p>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-5 space-y-3">
                                <label for="terms_aceitos" class="flex items-start gap-2 text-sm text-gray-600">
                                    <input type="checkbox" id="terms_aceitos" name="terms_aceitos" value="1" @checked(old('terms_aceitos')) required class="mt-0.5 h-4 w-4 border border-gray-300 rounded text-gray-800 focus:ring-1 focus:ring-gray-400">
                                    <span>
                                        Concordo com os
                                        <a href="{{ route('termos') }}" class="hover:underline" style="color: #1e4fa0">Termos de Uso</a>
                                        e a
                                        <a href="{{ route('privacidade') }}" class="hover:underline" style="color: #1e4fa0">Política de Privacidade</a>.
                                    </span>
                                </label>
                                <p class="field-error empty:hidden text-[11px] text-red-600" data-error="terms_aceitos">@error('terms_aceitos'){{ $message }}@enderror</p>

                                <label for="marketing_opt_in" class="flex items-start gap-2 text-sm text-gray-600">
                                    <input type="checkbox" id="marketing_opt_in" name="marketing_opt_in" value="1" @checked(old('marketing_opt_in')) class="mt-0.5 h-4 w-4 border border-gray-300 rounded text-gray-800 focus:ring-1 focus:ring-gray-400">
                                    <span>Quero receber novidades e conteúdos sobre FiscalDock por e-mail.</span>
                                </label>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                                <button type="submit" id="signup-submit-btn" class="bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium px-4 py-3 text-center">
                                    Criar conta grátis
                                </button>
                                <a href="{{ route('agendar') }}" class="bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded text-sm font-medium px-4 py-3 text-center">
                                    Falar com especialista
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">O que você recebe</span>
                    </div>
                    <div class="p-4 space-y-4 text-sm text-gray-700">
                        <div class="border border-gray-200 rounded p-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Bônus inicial</p>
                            <p class="text-lg font-bold text-gray-900">100 créditos grátis</p>
                            <p class="text-xs text-gray-500 mt-1">Liberados automaticamente na criação da conta.</p>
                        </div>
                        <div class="border border-gray-200 rounded p-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Validade</p>
                            <p class="text-lg font-bold text-gray-900">30 dias</p>
                            <p class="text-xs text-gray-500 mt-1">O saldo promocional restante expira ao fim do período.</p>
                        </div>
                        <div class="border border-gray-200 rounded p-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Próximo passo</p>
                            <p class="text-sm text-gray-700">Depois do trial, você pode comprar pacotes de créditos e continuar usando a plataforma no seu ritmo.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Preferir ajuda humana?</span>
                    </div>
                    <div class="p-4 space-y-3 text-sm text-gray-700">
                        <p>Se quiser confirmar aderência, pacotes de créditos, faixas ou regras de uso antes de criar a conta, fale direto com nosso time comercial.</p>
                        <a href="{{ route('agendar') }}" class="inline-flex items-center justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Falar com especialista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
