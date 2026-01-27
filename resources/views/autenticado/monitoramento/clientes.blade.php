{{-- Monitoramento de Clientes - Placeholder --}}
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 tracking-tight">
                        Monitoramento de Clientes
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Acompanhe o status fiscal dos seus clientes</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            {{-- Construction Icon --}}
            <div class="mx-auto w-24 h-24 bg-amber-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
            </div>

            <h2 class="text-2xl font-semibold text-gray-900 mb-3">
                Em Desenvolvimento
            </h2>

            <p class="text-gray-600 max-w-md mx-auto mb-8">
                Esta funcionalidade esta sendo desenvolvida para permitir o monitoramento centralizado dos seus clientes.
            </p>

            {{-- Features Preview --}}
            <div class="bg-gray-50 rounded-lg p-6 max-w-lg mx-auto mb-8">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">
                    O que voce podera fazer aqui:
                </h3>
                <ul class="text-left text-sm text-gray-600 space-y-2">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Visualizar status fiscal de todos os clientes
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Acompanhar CNDs e certidoes por cliente
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Receber alertas de vencimento e irregularidades
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Gerar relatorios consolidados por cliente
                    </li>
                </ul>
            </div>

            {{-- Back Button --}}
            <a href="/app/monitoramento/participantes" data-link class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Ver Participantes
            </a>
        </div>

        {{-- Contact Info --}}
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                Tem alguma sugestao? Entre em contato conosco pelo
                <a href="mailto:suporte@fiscaldock.com.br" class="text-blue-600 hover:underline">suporte@fiscaldock.com.br</a>
            </p>
        </div>
    </div>
</div>
