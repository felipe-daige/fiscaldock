<section id="login" class="bg-gray-100 min-h-[calc(100vh-80px)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="max-w-sm mx-auto">
            <div class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Acesso FiscalDock</p>
                            <h1 class="text-lg font-bold text-gray-900 uppercase tracking-wide mt-1">Entrar no painel</h1>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">
                            Seguro
                        </span>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    <div class="mb-5 text-center">
                        <img
                            src="{{ asset('binary_files/logo/logo-fiscaldock_whitebg-removebg.png') }}"
                            alt="FiscalDock"
                            class="h-12 mx-auto object-contain"
                        >
                        <p class="text-xs text-gray-500 mt-3">Informe seu e-mail corporativo e senha para acessar o ambiente autenticado.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 bg-white rounded border border-gray-300 p-4 border-l-4 border-l-red-500">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Falha de autenticação</p>
                            <ul class="mt-2 space-y-1 text-sm text-gray-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="login-form" class="space-y-4" method="POST" action="/login">
                        @csrf

                        <div>
                            <label for="email" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">E-mail</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                oninvalid="this.setCustomValidity('Inclua um @ e insira um e-mail válido.')"
                                oninput="this.setCustomValidity('')"
                                class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                                placeholder="seu@empresa.com.br"
                            >
                        </div>

                        <div>
                            <div class="flex items-center justify-between gap-3 mb-1">
                                <label for="password" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Senha</label>
                                <span class="text-[10px] text-gray-500 uppercase tracking-wide">Mínimo de 8 caracteres</span>
                            </div>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                                placeholder="Digite sua senha"
                            >
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <label for="remember" class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <input
                                    type="checkbox"
                                    id="remember"
                                    name="remember"
                                    class="h-4 w-4 border border-gray-300 rounded text-gray-800 focus:ring-1 focus:ring-gray-400"
                                >
                                <span>Lembrar-me</span>
                            </label>
                            <a href="/agendar" data-link class="text-xs text-gray-600 hover:text-gray-900 hover:underline">
                                Precisa de acesso?
                            </a>
                        </div>

                        <button
                            type="submit"
                            id="login-submit-btn"
                            class="w-full bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium px-4 py-2.5 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Entrar no painel
                        </button>

                        <div class="bg-gray-50 border border-gray-200 rounded px-4 py-3 text-sm text-gray-600">
                            <span class="font-medium text-gray-900">Novo por aqui?</span>
                            Solicite acesso comercial em
                            <a href="/agendar" data-link class="text-gray-900 hover:text-gray-600 hover:underline">Abrir Conta</a>.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
