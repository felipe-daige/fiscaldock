{{-- Página Principal de Soluções - Autenticado --}}
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Soluções Disponíveis
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Acesse as ferramentas e funcionalidades do FiscalDock
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Grid de Soluções --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Card: Importação de XMLs --}}
            <a href="/app/solucoes/importacao-xml" data-link class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all hover:scale-105 border border-gray-200 group">
                <div class="flex flex-col h-full">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors mb-2">
                        Importação de XMLs
                    </h3>
                    <p class="text-sm text-gray-500 mb-4 flex-grow">
                        Importe e processe arquivos XML de notas fiscais, NFS-e e CT-e de forma automatizada.
                    </p>
                    <div class="flex items-center text-blue-600 font-medium text-sm">
                        <span>Acessar</span>
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Card: Conciliação Bancária --}}
            <a href="/app/solucoes/conciliacao-bancaria" data-link class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all hover:scale-105 border border-gray-200 group">
                <div class="flex flex-col h-full">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 group-hover:text-green-600 transition-colors mb-2">
                        Conciliação Bancária
                    </h3>
                    <p class="text-sm text-gray-500 mb-4 flex-grow">
                        Concilie extratos bancários automaticamente e identifique divergências.
                    </p>
                    <div class="flex items-center text-green-600 font-medium text-sm">
                        <span>Acessar</span>
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Card: Gestão de CNDs --}}
            <a href="/app/solucoes/gestao-cnds" data-link class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all hover:scale-105 border border-gray-200 group">
                <div class="flex flex-col h-full">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 group-hover:text-orange-600 transition-colors mb-2">
                        Gestão de CNDs
                    </h3>
                    <p class="text-sm text-gray-500 mb-4 flex-grow">
                        Gerencie Certidões Negativas de Débito e monitore vencimentos.
                    </p>
                    <div class="flex items-center text-orange-600 font-medium text-sm">
                        <span>Acessar</span>
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Card: RAF - Inteligência Fiscal --}}
            <a href="/app/solucoes/raf" data-link class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all hover:scale-105 border border-gray-200 group">
                <div class="flex flex-col h-full">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 group-hover:text-purple-600 transition-colors mb-2">
                        RAF - Inteligência Fiscal
                    </h3>
                    <p class="text-sm text-gray-500 mb-4 flex-grow">
                        Monitoramento em tempo real do regime tributário e status CND das empresas.
                    </p>
                    <div class="flex items-center text-purple-600 font-medium text-sm">
                        <span>Acessar</span>
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Card: Inteligência Tributária --}}
            <a href="/app/solucoes/inteligencia-tributaria" data-link class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all hover:scale-105 border border-gray-200 group">
                <div class="flex flex-col h-full">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 group-hover:text-indigo-600 transition-colors mb-2">
                        Inteligência Tributária
                    </h3>
                    <p class="text-sm text-gray-500 mb-4 flex-grow">
                        Análises avançadas e insights sobre situação tributária e oportunidades de otimização.
                    </p>
                    <div class="flex items-center text-indigo-600 font-medium text-sm">
                        <span>Acessar</span>
                        <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

