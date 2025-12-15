
<section id="login" class="flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-[calc(100vh-80px)] px-4 py-4">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg border border-gray-100 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-100 to-transparent rounded-full -translate-y-16 translate-x-16"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-blue-100 to-transparent rounded-full translate-y-12 -translate-x-12"></div>
        
        <div class="relative z-10">
            <!-- Logo e título centralizados -->
            <div class="text-center mb-6">
                <!-- Ícone do logo -->
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 rounded-3xl flex items-center justify-center mx-auto mb-3 shadow-2xl transform hover:scale-105 transition-transform duration-300 ring-4 ring-blue-100 ring-opacity-50">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                
                <!-- Nome da marca centralizado -->
                <div class="flex items-center justify-center gap-2 mb-4">
                    <div class="w-8 h-8 brand-mark rounded-lg flex items-center justify-center font-bold text-white text-lg shadow-md">H</div>
                    <span class="text-lg font-bold text-gray-900">FiscalDock</span>
                </div>
                
                <!-- Título e subtítulo -->
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Bem-vindo de volta</h2>
                <p class="text-gray-600">Entre na sua conta para continuar</p>
            </div>

            <form id="login-form" class="space-y-5" method="POST" action="/login">
                @csrf
                <!-- Campos de entrada -->
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">E-mail</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                                placeholder="seu@empresa.com"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Senha</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                                placeholder="Digite sua senha"
                            >
                        </div>
                    </div>
                </div>

                <!-- Opções e ações -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 text-sm text-gray-600">Lembrar-me</label>
                    </div>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors">Esqueceu a senha?</a>
                </div>

                <!-- Botão de submit -->
                <button type="submit" id="login-submit-btn" class="w-full bg-blue-600 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 active:from-blue-700 active:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-white font-semibold py-2.5 px-6 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:from-blue-500 disabled:hover:to-blue-600">
                    Entrar
                </button>

                <!-- Link para criar conta -->
                <div class="text-center text-gray-600">
                    Não tem uma conta? 
                    <a href="/agendar" data-link class="text-blue-600 hover:text-blue-700 font-semibold transition-colors">Abrir Conta</a>
                </div>
            </form>
        </div>
    </div>
</section>
<!-- Scripts carregados no layout -->
