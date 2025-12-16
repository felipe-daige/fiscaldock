
<section id="agendar-demo" class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-blue-50 py-12 px-4">
    <div class="bg-white rounded-2xl shadow-2xl p-10 w-full max-w-5xl border border-gray-100 relative" style="overflow: visible !important; min-height: auto !important;">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-blue-100 to-transparent rounded-full -translate-y-20 translate-x-20 z-0"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-blue-100 to-transparent rounded-full translate-y-16 -translate-x-16 z-0"></div>
        
        <div class="relative z-10" style="display: block !important; visibility: visible !important;">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Abrir Conta</h2>
                <p class="text-gray-600">Agende sua consultoria gratuita e comece seu teste de 30 dias</p>
            </div>

            <form id="registerForm" class="space-y-8" method="POST" action="/agendar" style="display: block !important; visibility: visible !important;">
            @csrf
            <!-- Seção 1: Dados do Usuário -->
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-8 border border-gray-200 block relative z-20" style="display: block !important; visibility: visible !important; min-height: auto !important; margin-bottom: 2rem !important;">
                <h3 class="text-xl font-bold text-gray-900 mb-8 flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-5 shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    Dados do Usuário
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="nome" class="block text-sm font-semibold text-gray-700 mb-2">Nome *</label>
                        <input 
                            type="text" 
                            id="nome" 
                            name="nome"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="João"
                        />
                    </div>

                    <div>
                        <label for="sobrenome" class="block text-sm font-semibold text-gray-700 mb-2">Sobrenome *</label>
                        <input 
                            type="text" 
                            id="sobrenome" 
                            name="sobrenome"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Silva"
                        />
                    </div>
                </div>

                <div class="mb-6">
                    <label for="registerEmail" class="block text-sm font-semibold text-gray-700 mb-2">E-mail Corporativo *</label>
                    <input 
                        type="email" 
                        id="registerEmail" 
                        name="email"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                        placeholder="seu@empresa.com"
                    />
                </div>

                <div class="mb-6">
                    <label for="telefone" class="block text-sm font-semibold text-gray-700 mb-2">Telefone *</label>
                    <input 
                        type="tel" 
                        id="telefone" 
                        name="telefone"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                        placeholder="(11) 99999-9999"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="senha" class="block text-sm font-semibold text-gray-700 mb-2">Senha *</label>
                        <input 
                            type="password" 
                            id="senha" 
                            name="senha"
                            required
                            minlength="8"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Mínimo 8 caracteres"
                        />
                        <p class="text-sm text-gray-500 mt-1">Use pelo menos 8 caracteres com letras e números</p>
                    </div>

                    <div>
                        <label for="senha_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirmar Senha *</label>
                        <input 
                            type="password" 
                            id="senha_confirmation" 
                            name="senha_confirmation"
                            required
                            minlength="8"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Digite a senha novamente"
                        />
                    </div>
                </div>
            </div>

            <!-- Seção 2: Dados da Empresa -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-8 border border-blue-200 block relative z-20" style="display: block !important; visibility: visible !important; min-height: auto !important; margin-bottom: 2rem !important;">
                <h3 class="text-xl font-bold text-gray-900 mb-8 flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-5 shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    Dados da Empresa
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="empresa" class="block text-sm font-semibold text-gray-700 mb-2">Nome da Empresa *</label>
                        <input 
                            type="text" 
                            id="empresa" 
                            name="empresa"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Nome da Empresa"
                        />
                    </div>

                    <div>
                        <label for="cargo" class="block text-sm font-semibold text-gray-700 mb-2">Cargo na Empresa *</label>
                        <input 
                            type="text" 
                            id="cargo" 
                            name="cargo"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="Ex: CEO, Contador, Gerente Financeiro"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="cnpj" class="block text-sm font-semibold text-gray-700 mb-2">CNPJ *</label>
                        <input 
                            type="text" 
                            id="cnpj" 
                            name="cnpj"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                            placeholder="00.000.000/0000-00"
                        />
                    </div>

                    <div>
                        <label for="faturamento" class="block text-sm font-semibold text-gray-700 mb-2">Faturamento Anual *</label>
                        <select 
                            id="faturamento" 
                            name="faturamento"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                        >
                            <option value="">Selecione uma opção</option>
                            <option value="ate-360k">Até R$ 360 mil</option>
                            <option value="360k-4.8m">R$ 360 mil a R$ 4,8 milhões</option>
                            <option value="4.8m-300m">R$ 4,8 milhões a R$ 300 milhões</option>
                            <option value="acima-300m">Acima de R$ 300 milhões</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Qual é o principal desafio que você enfrenta hoje? *</label>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="radio" name="desafio_principal" value="documentos_espalhados" required class="mr-3 text-blue-500 focus:ring-blue-500">
                            <span class="text-gray-700">Documentos espalhados sem histórico</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="desafio_principal" value="pendencias_fim_mes" required class="mr-3 text-blue-500 focus:ring-blue-500">
                            <span class="text-gray-700">Corrida no fim do mês com pendências</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="desafio_principal" value="comunicacao_manual" required class="mr-3 text-blue-500 focus:ring-blue-500">
                            <span class="text-gray-700">Comunicação manual sem rastreabilidade</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="desafio_principal" value="falta_visao" required class="mr-3 text-blue-500 focus:ring-blue-500">
                            <span class="text-gray-700">Falta de visão do que está certo ou falta</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Termos e Condições -->
            <div class="space-y-4 block relative z-20" style="display: block !important; visibility: visible !important; margin-bottom: 2rem !important;">
                <div class="flex items-start">
                    <input type="checkbox" id="terms" required class="mt-1 mr-3">
                    <label for="terms" class="text-sm text-gray-600">
                        Concordo com os <a href="#" class="text-blue-500 hover:text-blue-600">Termos de Uso</a> 
                        e <a href="#" class="text-blue-500 hover:text-blue-600">Política de Privacidade</a>
                    </label>
                </div>

                <div class="flex items-start">
                    <input type="checkbox" id="marketing" class="mt-1 mr-3">
                    <label for="marketing" class="text-sm text-gray-600">
                        Desejo receber novidades, atualizações sobre o FiscalDock e ofertas por e-mail
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl text-lg block relative z-20" style="display: block !important; visibility: visible !important; margin-bottom: 2rem !important;">
                Agendar Consultoria Gratuita
            </button>

            <div class="bg-green-50 border border-green-200 rounded-lg p-6 block relative z-20" style="display: block !important; visibility: visible !important; margin-bottom: 2rem !important;">
                <p class="font-semibold text-gray-900 mb-4">✓ Incluído no seu agendamento:</p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Consultoria personalizada de 30 minutos
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        30 dias de acesso completo à plataforma
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Análise personalizada do impacto da reforma
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Sem compromisso de contratação
                    </li>
                </ul>
            </div>

            <div class="text-center text-gray-600 block relative z-20" style="display: block !important; visibility: visible !important;">
                Já tem uma conta? 
                <a href="/login" data-link class="text-blue-600 hover:text-blue-700 font-semibold transition-colors">Entrar</a>
            </div>
        </form>
        </div>
    </div>
</section>

<!-- Scripts carregados no layout -->