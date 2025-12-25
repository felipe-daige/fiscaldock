{{-- Perfil do Usuário --}}
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                {{-- Avatar --}}
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg shrink-0">
                    <span class="text-3xl font-bold text-white">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(Auth::user()->sobrenome ?? '', 0, 1)) }}
                    </span>
                </div>
                
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 tracking-tight">
                        {{ Auth::user()->name ?? 'Usuário' }} {{ Auth::user()->sobrenome ?? '' }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ Auth::user()->email ?? '' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Card: Informações Pessoais --}}
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Informações Pessoais</h2>
                    </div>
                </div>
                
                <div class="p-6 space-y-5">
                    {{-- Nome --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                        <label class="text-sm font-medium text-gray-500 sm:w-32 shrink-0">Nome</label>
                        <div class="flex-1 px-4 py-3 bg-gray-50 rounded-lg text-gray-900">
                            {{ Auth::user()->name ?? '-' }}
                        </div>
                    </div>
                    
                    {{-- Sobrenome --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                        <label class="text-sm font-medium text-gray-500 sm:w-32 shrink-0">Sobrenome</label>
                        <div class="flex-1 px-4 py-3 bg-gray-50 rounded-lg text-gray-900">
                            {{ Auth::user()->sobrenome ?? '-' }}
                        </div>
                    </div>
                    
                    {{-- Email --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                        <label class="text-sm font-medium text-gray-500 sm:w-32 shrink-0">E-mail</label>
                        <div class="flex-1 px-4 py-3 bg-gray-50 rounded-lg text-gray-900">
                            {{ Auth::user()->email ?? '-' }}
                        </div>
                    </div>
                    
                    {{-- Telefone --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                        <label class="text-sm font-medium text-gray-500 sm:w-32 shrink-0">Telefone</label>
                        <div class="flex-1 px-4 py-3 bg-gray-50 rounded-lg text-gray-900">
                            {{ Auth::user()->telefone ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Card: Créditos --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Créditos</h2>
                    </div>
                </div>
                
                <div class="p-6">
                    {{-- Saldo --}}
                    <div class="text-center mb-6">
                        <p class="text-sm text-gray-500 mb-2">Saldo disponível</p>
                        <p class="text-5xl font-bold text-gray-900">
                            {{ number_format(Auth::user()->credits ?? 0, 0, ',', '.') }}
                        </p>
                        <p class="text-sm text-gray-400 mt-1">créditos</p>
                    </div>
                    
                    {{-- Botão Adicionar Créditos --}}
                    <button 
                        type="button" 
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg hover:from-emerald-600 hover:to-emerald-700 transition-all shadow-sm hover:shadow-md"
                        onclick="alert('Funcionalidade em breve!')"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Adicionar Créditos
                    </button>
                </div>
            </div>
            
            {{-- Card: Informações da Conta --}}
            <div class="lg:col-span-3 bg-white rounded-lg shadow-sm border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Informações da Conta</h2>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {{-- Membro desde --}}
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Membro desde</p>
                                <p class="text-base font-semibold text-gray-900">
                                    {{ Auth::user()->created_at ? Auth::user()->created_at->format('d/m/Y') : '-' }}
                                </p>
                            </div>
                        </div>
                        
                        {{-- E-mail verificado --}}
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 {{ Auth::user()->email_verified_at ? 'bg-emerald-100' : 'bg-yellow-100' }} rounded-lg flex items-center justify-center">
                                @if(Auth::user()->email_verified_at)
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">E-mail</p>
                                <p class="text-base font-semibold {{ Auth::user()->email_verified_at ? 'text-emerald-600' : 'text-yellow-600' }}">
                                    {{ Auth::user()->email_verified_at ? 'Verificado' : 'Não verificado' }}
                                </p>
                            </div>
                        </div>
                        
                        {{-- Status da conta --}}
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <p class="text-base font-semibold text-blue-600">Ativo</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>








