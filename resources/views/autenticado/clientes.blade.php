{{-- Clientes - Autenticado --}}
<div class="min-h-screen bg-gray-50" id="clientes-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">
                        Clientes
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Gerencie seus clientes e monitore alertas e analises
                    </p>
                </div>
                <a href="/app/novo_cliente" data-link class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Novo Cliente
                </a>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="space-y-6">
            {{-- Cards de Resumo --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Card: Clientes Ativos --}}
                <div id="card-total-clientes" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 cursor-pointer hover:border-blue-300 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Clientes Ativos</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalAtivos ?? 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Card: Clientes Inativos --}}
                <div id="card-inativos" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 cursor-pointer hover:border-gray-400 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Clientes Inativos</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalInativos ?? 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Card: Pessoa Juridica --}}
                <div id="card-pj" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 cursor-pointer hover:border-amber-300 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Pessoa Juridica</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalPJ ?? 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Card: Pessoa Fisica --}}
                <div id="card-pf" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 cursor-pointer hover:border-green-300 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Pessoa Fisica</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalPF ?? 0 }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Barra de Busca e Filtros --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="mb-4">
                    <div class="relative">
                        <input
                            type="text"
                            id="buscar-cliente"
                            placeholder="Buscar por nome, CNPJ ou CPF..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <select id="filtro-status" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="todos">Status: Todos</option>
                        <option value="ativos">Ativos</option>
                        <option value="inativos">Inativos</option>
                    </select>
                    <select id="filtro-tipo" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="todos">Tipo: Todos</option>
                        <option value="pj">Pessoa Juridica (CNPJ)</option>
                        <option value="pf">Pessoa Fisica (CPF)</option>
                    </select>
                </div>
            </div>

            {{-- Acoes em lote (aparece quando ha selecao) --}}
            <div id="acoes-lote" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-bold" id="clientes-selecionados-count">0</span>
                        <span class="text-sm font-medium text-blue-900">cliente(s) selecionado(s)</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="btn-exportar" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Exportar lista
                        </button>
                        <button type="button" id="btn-limpar-selecao" class="px-4 py-2 rounded-lg border border-blue-300 bg-white text-blue-700 text-sm font-semibold shadow-sm transition hover:bg-blue-50">
                            Limpar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabela de Clientes --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                @if(isset($clientes) && $clientes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full" id="tabela-clientes">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Cliente
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    CNPJ/CPF
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    E-mail
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Telefone
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Cidade/UF
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Acoes
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="clientes-tbody">
                            @foreach($clientes as $cliente)
                            <tr class="hover:bg-gray-50 transition-colors cliente-row"
                                data-cliente-id="{{ $cliente->id }}"
                                data-nome="{{ strtolower($cliente->tipo_pessoa === 'PJ' ? ($cliente->razao_social ?? $cliente->nome ?? '') : ($cliente->nome ?? '')) }}"
                                data-documento="{{ $cliente->documento }}"
                                data-tipo="{{ $cliente->tipo_pessoa }}"
                                data-status="{{ $cliente->ativo ? 'ativos' : 'inativos' }}">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="cliente-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-id="{{ $cliente->id }}">
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        @if($cliente->tipo_pessoa === 'PJ')
                                            <div class="text-sm font-medium text-gray-900">{{ $cliente->razao_social ?? '-' }}</div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-700">PJ</span>
                                        @else
                                            <div class="text-sm font-medium text-gray-900">{{ $cliente->nome ?? '-' }}</div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-purple-100 text-purple-700">PF</span>
                                        @endif
                                    </div>
                                    @if($cliente->tipo_pessoa === 'PJ' && $cliente->nome)
                                        <div class="text-xs text-gray-500">{{ $cliente->nome }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-mono text-gray-700">
                                    {{ $cliente->documento_formatado }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $cliente->email ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $cliente->telefone ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($cliente->endereco)
                                        {{ $cliente->endereco->cidade }}/{{ $cliente->endereco->estado }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($cliente->ativo)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                            Ativo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700">
                                            Inativo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    <button type="button" class="acoes-btn p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                                        data-id="{{ $cliente->id }}"
                                        data-nome="{{ $cliente->tipo_pessoa === 'PJ' ? ($cliente->razao_social ?? $cliente->nome ?? '') : ($cliente->nome ?? '') }}"
                                        data-documento="{{ $cliente->documento_formatado }}">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginacao --}}
                <div class="px-4 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Mostrando <span id="pagina-inicio">1</span>-<span id="pagina-fim">{{ min(10, $clientes->count()) }}</span> de <span id="total-clientes">{{ $clientes->count() }}</span> clientes
                        </div>
                    </div>
                </div>
                @else
                {{-- Estado Vazio --}}
                <div class="text-center py-12 px-6">
                    <div class="flex flex-col items-center">
                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum cliente cadastrado</h3>
                        <p class="text-sm text-gray-600 mb-4">Comece cadastrando seu primeiro cliente para gerencia-lo aqui.</p>
                        <a href="/app/novo_cliente" data-link class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Cadastrar Primeiro Cliente
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    </div>
</div>

{{-- Modais (fora do container para overlay correto) --}}

{{-- Dropdown de acoes do cliente (menu kebab) --}}
<div id="dropdown-acoes" class="hidden fixed z-[9999] bg-white rounded-xl shadow-lg ring-1 ring-gray-200 w-56 py-1">
    <div class="px-3 py-2 border-b border-gray-100">
        <p class="text-sm font-semibold text-gray-900 truncate" id="dropdown-acoes-nome"></p>
        <p class="text-xs text-gray-500 font-mono" id="dropdown-acoes-documento"></p>
    </div>
    <button type="button" id="dropdown-acoes-ver"
       class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        Ver detalhes
    </button>
    <a id="dropdown-acoes-editar" href="#"
       class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
       data-link>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Editar
    </a>
    <button type="button" id="dropdown-acoes-excluir"
        class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        Excluir
    </button>
</div>

{{-- Modal de detalhes do cliente --}}
<div id="modal-detalhes" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 bg-black/50 transition-opacity" id="modal-detalhes-overlay"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full z-10">
            {{-- Loading state --}}
            <div id="modal-detalhes-loading" class="p-8 text-center">
                <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm text-gray-500">Carregando dados...</p>
            </div>

            {{-- Content (hidden until loaded) --}}
            <div id="modal-detalhes-content" class="hidden">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-semibold text-gray-900" id="det-nome"></h3>
                            <span id="det-badge-tipo" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium"></span>
                            <span id="det-badge-status" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium"></span>
                        </div>
                        <p class="text-sm text-gray-500 font-mono" id="det-documento"></p>
                    </div>
                    <button type="button" id="btn-fechar-detalhes" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 space-y-5 max-h-[60vh] overflow-y-auto">
                    {{-- Dados Cadastrais --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Dados Cadastrais
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Tipo Pessoa</p>
                                <p class="text-sm text-gray-900" id="det-tipo-pessoa">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">E-mail</p>
                                <p class="text-sm text-gray-900" id="det-email">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Telefone</p>
                                <p class="text-sm text-gray-900" id="det-telefone">-</p>
                            </div>
                            <div id="det-faturamento-wrapper">
                                <p class="text-xs text-gray-500">Faturamento Anual</p>
                                <p class="text-sm text-gray-900" id="det-faturamento">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Empresa Propria</p>
                                <p class="text-sm" id="det-empresa-propria">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Cadastrado em</p>
                                <p class="text-sm text-gray-900" id="det-created-at">-</p>
                            </div>
                        </div>
                    </div>

                    {{-- Endereco --}}
                    <div id="det-endereco-section">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Endereco
                        </h4>
                        <p class="text-sm text-gray-900" id="det-endereco-texto">-</p>
                    </div>

                    {{-- Funcionarios --}}
                    <div id="det-funcionarios-section" class="hidden">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Funcionarios
                        </h4>
                        <div id="det-funcionarios-lista" class="space-y-2"></div>
                    </div>

                    {{-- Estatisticas --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Estatisticas
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-500">Participantes vinculados</p>
                                <p class="text-xl font-bold text-gray-900" id="det-total-participantes">0</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-500">Notas fiscais</p>
                                <p class="text-xl font-bold text-gray-900" id="det-total-notas">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button type="button" id="btn-fechar-detalhes-footer" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de confirmacao de exclusao --}}
<div id="modal-excluir" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" id="modal-excluir-overlay"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Excluir cliente?</h3>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-700 mb-2">
                    <span class="font-medium" id="modal-excluir-documento"></span> - <span id="modal-excluir-nome"></span>
                </p>
                <p class="text-sm text-gray-500">
                    Todo o historico associado sera removido (enderecos, funcionarios, solicitacoes). Os participantes vinculados serao mantidos.
                </p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" id="btn-cancelar-exclusao" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="button" id="btn-confirmar-exclusao" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold shadow-sm transition hover:bg-red-700">
                    Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initClientes() {
        var container = document.getElementById('clientes-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        var clientesSelecionados = new Set();

        function escHtml(str) {
            var div = document.createElement('div');
            div.textContent = str || '';
            return div.innerHTML;
        }

        // === Filtros ===
        var buscarInput = document.getElementById('buscar-cliente');
        var filtroStatus = document.getElementById('filtro-status');
        var filtroTipo = document.getElementById('filtro-tipo');

        function aplicarFiltros() {
            var busca = (buscarInput ? buscarInput.value : '').toLowerCase();
            var status = filtroStatus ? filtroStatus.value : 'todos';
            var tipo = filtroTipo ? filtroTipo.value : 'todos';

            var rows = container.querySelectorAll('.cliente-row');
            var visibleCount = 0;

            rows.forEach(function(row) {
                var nome = row.dataset.nome || '';
                var documento = row.dataset.documento || '';
                var tipoCliente = row.dataset.tipo;
                var statusCliente = row.dataset.status;

                var show = true;

                if (busca && nome.indexOf(busca) === -1 && documento.indexOf(busca) === -1) {
                    show = false;
                }
                if (status !== 'todos' && statusCliente !== status) {
                    show = false;
                }
                if (tipo !== 'todos') {
                    var tipoFiltro = tipo === 'pj' ? 'PJ' : 'PF';
                    if (tipoCliente !== tipoFiltro) show = false;
                }

                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            var elInicio = document.getElementById('pagina-inicio');
            var elFim = document.getElementById('pagina-fim');
            var elTotal = document.getElementById('total-clientes');
            if (elInicio) elInicio.textContent = visibleCount > 0 ? 1 : 0;
            if (elFim) elFim.textContent = visibleCount;
            if (elTotal) elTotal.textContent = visibleCount;
        }

        if (buscarInput) buscarInput.addEventListener('input', aplicarFiltros);
        if (filtroStatus) filtroStatus.addEventListener('change', aplicarFiltros);
        if (filtroTipo) filtroTipo.addEventListener('change', aplicarFiltros);

        // === Selecao ===
        var selectAll = document.getElementById('select-all');

        function atualizarBarraAcoes() {
            var acoesLote = document.getElementById('acoes-lote');
            var countEl = document.getElementById('clientes-selecionados-count');
            var count = clientesSelecionados.size;

            if (countEl) countEl.textContent = count;
            if (acoesLote) {
                if (count > 0) {
                    acoesLote.classList.remove('hidden');
                } else {
                    acoesLote.classList.add('hidden');
                }
            }

            var checkboxes = container.querySelectorAll('.cliente-checkbox');
            var visibleChecked = 0;
            var visibleTotal = 0;
            checkboxes.forEach(function(cb) {
                var row = cb.closest('.cliente-row');
                if (row && row.style.display !== 'none') {
                    visibleTotal++;
                    if (cb.checked) visibleChecked++;
                }
            });
            if (selectAll) {
                selectAll.checked = visibleChecked === visibleTotal && visibleTotal > 0;
                selectAll.indeterminate = visibleChecked > 0 && visibleChecked < visibleTotal;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                var checked = selectAll.checked;
                container.querySelectorAll('.cliente-checkbox').forEach(function(cb) {
                    var row = cb.closest('.cliente-row');
                    if (row && row.style.display !== 'none') {
                        cb.checked = checked;
                        var id = parseInt(cb.dataset.id);
                        if (checked) {
                            clientesSelecionados.add(id);
                        } else {
                            clientesSelecionados.delete(id);
                        }
                    }
                });
                atualizarBarraAcoes();
            });
        }

        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('cliente-checkbox')) {
                var id = parseInt(e.target.dataset.id);
                if (e.target.checked) {
                    clientesSelecionados.add(id);
                } else {
                    clientesSelecionados.delete(id);
                }
                atualizarBarraAcoes();
            }
        });

        // Botao limpar selecao
        var btnLimpar = document.getElementById('btn-limpar-selecao');
        if (btnLimpar) {
            btnLimpar.addEventListener('click', function() {
                clientesSelecionados.clear();
                container.querySelectorAll('.cliente-checkbox').forEach(function(cb) { cb.checked = false; });
                if (selectAll) selectAll.checked = false;
                atualizarBarraAcoes();
            });
        }

        // Botao exportar (placeholder)
        var btnExportar = document.getElementById('btn-exportar');
        if (btnExportar) {
            btnExportar.addEventListener('click', function() {
                if (window.showToast) {
                    window.showToast('Funcionalidade de exportacao em desenvolvimento', 'info');
                }
            });
        }

        // === Cards clicaveis ===
        var cardTotal = document.getElementById('card-total-clientes');
        if (cardTotal) {
            cardTotal.addEventListener('click', function() {
                if (filtroStatus) { filtroStatus.value = 'ativos'; }
                aplicarFiltros();
            });
        }

        var cardInativos = document.getElementById('card-inativos');
        if (cardInativos) {
            cardInativos.addEventListener('click', function() {
                if (filtroStatus) { filtroStatus.value = 'inativos'; }
                aplicarFiltros();
            });
        }

        var cardPJ = document.getElementById('card-pj');
        if (cardPJ) {
            cardPJ.addEventListener('click', function() {
                if (filtroTipo) { filtroTipo.value = 'pj'; }
                aplicarFiltros();
            });
        }

        var cardPF = document.getElementById('card-pf');
        if (cardPF) {
            cardPF.addEventListener('click', function() {
                if (filtroTipo) { filtroTipo.value = 'pf'; }
                aplicarFiltros();
            });
        }

        // === Dropdown de acoes (kebab) ===
        var dropdownAcoes = document.getElementById('dropdown-acoes');
        var dropdownAcoesNome = document.getElementById('dropdown-acoes-nome');
        var dropdownAcoesDocumento = document.getElementById('dropdown-acoes-documento');
        var dropdownAcoesVer = document.getElementById('dropdown-acoes-ver');
        var dropdownAcoesEditar = document.getElementById('dropdown-acoes-editar');
        var dropdownAcoesExcluir = document.getElementById('dropdown-acoes-excluir');
        var acaoClienteId = null;
        var acaoClienteNome = null;
        var acaoClienteDocumento = null;
        var dropdownBtnAtual = null;

        function posicionarDropdown(btnElement) {
            if (!dropdownAcoes || !btnElement) return;
            // Temporarily show to measure height
            dropdownAcoes.style.visibility = 'hidden';
            dropdownAcoes.classList.remove('hidden');
            var dropdownHeight = dropdownAcoes.offsetHeight;
            var dropdownWidth = dropdownAcoes.offsetWidth;
            dropdownAcoes.classList.add('hidden');
            dropdownAcoes.style.visibility = '';

            var rect = btnElement.getBoundingClientRect();
            var spaceBelow = window.innerHeight - rect.bottom;
            var spaceAbove = rect.top;

            // Horizontal: align right edge of dropdown with right edge of button
            var left = rect.right - dropdownWidth;
            if (left < 8) left = 8;

            // Vertical: prefer below, fall back to above
            var top;
            if (spaceBelow >= dropdownHeight + 4) {
                top = rect.bottom + 4;
            } else if (spaceAbove >= dropdownHeight + 4) {
                top = rect.top - dropdownHeight - 4;
            } else {
                top = rect.bottom + 4;
            }

            dropdownAcoes.style.top = top + 'px';
            dropdownAcoes.style.left = left + 'px';
        }

        function abrirDropdownAcoes(btnElement, id, nome, documento) {
            // Toggle: if clicking the same button, close
            if (!dropdownAcoes.classList.contains('hidden') && dropdownBtnAtual === btnElement) {
                fecharDropdownAcoes();
                return;
            }
            acaoClienteId = id;
            acaoClienteNome = nome;
            acaoClienteDocumento = documento;
            dropdownBtnAtual = btnElement;
            if (dropdownAcoesNome) dropdownAcoesNome.textContent = nome || 'Sem nome';
            if (dropdownAcoesDocumento) dropdownAcoesDocumento.textContent = documento || '';
            if (dropdownAcoesEditar) dropdownAcoesEditar.href = '/app/novo_cliente?id=' + id;
            posicionarDropdown(btnElement);
            dropdownAcoes.classList.remove('hidden');
        }

        function fecharDropdownAcoes() {
            if (dropdownAcoes) dropdownAcoes.classList.add('hidden');
            dropdownBtnAtual = null;
        }

        container.addEventListener('click', function(e) {
            var acaoBtn = e.target.closest('.acoes-btn');
            if (acaoBtn) {
                e.stopPropagation();
                abrirDropdownAcoes(acaoBtn, acaoBtn.dataset.id, acaoBtn.dataset.nome, acaoBtn.dataset.documento);
            }
        });

        // Close on click outside
        document.addEventListener('click', function(e) {
            if (dropdownAcoes && !dropdownAcoes.classList.contains('hidden') && !dropdownAcoes.contains(e.target)) {
                fecharDropdownAcoes();
            }
        });

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && dropdownAcoes && !dropdownAcoes.classList.contains('hidden')) {
                fecharDropdownAcoes();
            }
        });

        // Close on scroll (capture mode to catch scrolling in any container)
        window.addEventListener('scroll', function() {
            if (dropdownAcoes && !dropdownAcoes.classList.contains('hidden')) {
                fecharDropdownAcoes();
            }
        }, true);

        // "Ver detalhes" -> abre modal de detalhes
        if (dropdownAcoesVer) {
            dropdownAcoesVer.addEventListener('click', function() {
                var id = acaoClienteId;
                fecharDropdownAcoes();
                abrirModalDetalhes(id);
            });
        }

        // "Editar" -> fechar dropdown (SPA navega via data-link)
        if (dropdownAcoesEditar) {
            dropdownAcoesEditar.addEventListener('click', fecharDropdownAcoes);
        }

        // "Excluir" -> fechar dropdown, abrir modal de exclusao
        if (dropdownAcoesExcluir) {
            dropdownAcoesExcluir.addEventListener('click', function() {
                var id = acaoClienteId;
                var nome = acaoClienteNome;
                var documento = acaoClienteDocumento;
                fecharDropdownAcoes();
                abrirModalExclusao(id, nome, documento);
            });
        }

        // === Modal de detalhes ===
        var modalDetalhes = document.getElementById('modal-detalhes');
        var modalDetalhesOverlay = document.getElementById('modal-detalhes-overlay');
        var modalDetalhesLoading = document.getElementById('modal-detalhes-loading');
        var modalDetalhesContent = document.getElementById('modal-detalhes-content');
        var btnFecharDetalhes = document.getElementById('btn-fechar-detalhes');
        var btnFecharDetalhesFooter = document.getElementById('btn-fechar-detalhes-footer');

        function abrirModalDetalhes(id) {
            if (!modalDetalhes) return;

            // Show modal with loading
            modalDetalhes.classList.remove('hidden');
            if (modalDetalhesLoading) modalDetalhesLoading.classList.remove('hidden');
            if (modalDetalhesContent) modalDetalhesContent.classList.add('hidden');

            fetch('/app/cliente/' + id, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) {
                    throw new Error(data.message || 'Erro ao carregar dados');
                }

                var c = data.cliente;
                var end = data.endereco;
                var funcs = data.funcionarios || [];
                var stats = data.stats || {};

                // Header
                var elNome = document.getElementById('det-nome');
                if (elNome) elNome.textContent = c.razao_social || c.nome || '-';

                var elDoc = document.getElementById('det-documento');
                if (elDoc) elDoc.textContent = c.documento_formatado || '-';

                var badgeTipo = document.getElementById('det-badge-tipo');
                if (badgeTipo) {
                    if (c.tipo_pessoa === 'PJ') {
                        badgeTipo.textContent = 'PJ';
                        badgeTipo.className = 'inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-700';
                    } else {
                        badgeTipo.textContent = 'PF';
                        badgeTipo.className = 'inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-purple-100 text-purple-700';
                    }
                }

                var badgeStatus = document.getElementById('det-badge-status');
                if (badgeStatus) {
                    if (c.ativo) {
                        badgeStatus.textContent = 'Ativo';
                        badgeStatus.className = 'inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-700';
                    } else {
                        badgeStatus.textContent = 'Inativo';
                        badgeStatus.className = 'inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-700';
                    }
                }

                // Dados cadastrais
                var elTipoPessoa = document.getElementById('det-tipo-pessoa');
                if (elTipoPessoa) elTipoPessoa.textContent = c.tipo_pessoa === 'PJ' ? 'Pessoa Juridica' : 'Pessoa Fisica';

                var elEmail = document.getElementById('det-email');
                if (elEmail) elEmail.textContent = c.email || '-';

                var elTel = document.getElementById('det-telefone');
                if (elTel) elTel.textContent = c.telefone || '-';

                var elFat = document.getElementById('det-faturamento');
                var elFatWrap = document.getElementById('det-faturamento-wrapper');
                if (elFat && elFatWrap) {
                    if (c.tipo_pessoa === 'PJ' && c.faturamento_anual) {
                        elFatWrap.style.display = '';
                        elFat.textContent = c.faturamento_anual;
                    } else {
                        elFatWrap.style.display = 'none';
                    }
                }

                var elEmpresa = document.getElementById('det-empresa-propria');
                if (elEmpresa) {
                    if (c.is_empresa_propria) {
                        elEmpresa.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-700">Sim</span>';
                    } else {
                        elEmpresa.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700">Nao</span>';
                    }
                }

                var elCreated = document.getElementById('det-created-at');
                if (elCreated) elCreated.textContent = c.created_at || '-';

                // Endereco
                var endSection = document.getElementById('det-endereco-section');
                var endTexto = document.getElementById('det-endereco-texto');
                if (endSection && endTexto) {
                    if (end) {
                        var parts = [];
                        if (end.logradouro) parts.push(end.logradouro);
                        if (end.numero) parts.push(end.numero);
                        if (end.complemento) parts.push(end.complemento);

                        var line2 = [];
                        if (end.bairro) line2.push(end.bairro);
                        if (end.cidade) line2.push(end.cidade);
                        if (end.estado) line2.push(end.estado);
                        if (end.cep) line2.push('CEP: ' + end.cep);

                        var texto = parts.join(', ');
                        if (line2.length > 0) texto += (texto ? ' - ' : '') + line2.join(', ');
                        endTexto.textContent = texto || '-';
                        endSection.style.display = '';
                    } else {
                        endSection.style.display = 'none';
                    }
                }

                // Funcionarios
                var funcSection = document.getElementById('det-funcionarios-section');
                var funcLista = document.getElementById('det-funcionarios-lista');
                if (funcSection && funcLista) {
                    if (funcs.length > 0) {
                        funcSection.classList.remove('hidden');
                        funcLista.innerHTML = funcs.map(function(f) {
                            return '<div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">' +
                                '<div>' +
                                    '<p class="text-sm font-medium text-gray-900">' + escHtml(f.nome_completo || '-') + '</p>' +
                                    '<p class="text-xs text-gray-500">' + escHtml(f.email || '') + '</p>' +
                                '</div>' +
                                '<div class="text-right">' +
                                    '<p class="text-xs text-gray-700">' + escHtml(f.cargo || '-') + '</p>' +
                                    '<p class="text-xs text-gray-500">' + escHtml(f.nivel_acesso || '') + '</p>' +
                                '</div>' +
                            '</div>';
                        }).join('');
                    } else {
                        funcSection.classList.add('hidden');
                    }
                }

                // Estatisticas
                var elParticipantes = document.getElementById('det-total-participantes');
                if (elParticipantes) elParticipantes.textContent = stats.total_participantes || 0;

                var elNotas = document.getElementById('det-total-notas');
                if (elNotas) elNotas.textContent = stats.total_notas || 0;

                // Show content, hide loading
                if (modalDetalhesLoading) modalDetalhesLoading.classList.add('hidden');
                if (modalDetalhesContent) modalDetalhesContent.classList.remove('hidden');
            })
            .catch(function(err) {
                console.error('[Clientes] Erro ao carregar detalhes:', err);
                if (window.showToast) window.showToast(err.message || 'Erro ao carregar detalhes', 'error');
                fecharModalDetalhes();
            });
        }

        function fecharModalDetalhes() {
            if (modalDetalhes) modalDetalhes.classList.add('hidden');
        }

        if (modalDetalhesOverlay) modalDetalhesOverlay.addEventListener('click', fecharModalDetalhes);
        if (btnFecharDetalhes) btnFecharDetalhes.addEventListener('click', fecharModalDetalhes);
        if (btnFecharDetalhesFooter) btnFecharDetalhesFooter.addEventListener('click', fecharModalDetalhes);

        // === Modal de exclusao ===
        var modalExcluir = document.getElementById('modal-excluir');
        var modalExcluirOverlay = document.getElementById('modal-excluir-overlay');
        var modalExcluirNome = document.getElementById('modal-excluir-nome');
        var modalExcluirDocumento = document.getElementById('modal-excluir-documento');
        var btnCancelarExclusao = document.getElementById('btn-cancelar-exclusao');
        var btnConfirmarExclusao = document.getElementById('btn-confirmar-exclusao');
        var clienteIdParaExcluir = null;

        function abrirModalExclusao(id, nome, documento) {
            clienteIdParaExcluir = id;
            if (modalExcluirNome) modalExcluirNome.textContent = nome || 'Sem nome';
            if (modalExcluirDocumento) modalExcluirDocumento.textContent = documento || '';
            if (modalExcluir) modalExcluir.classList.remove('hidden');
        }

        function fecharModalExclusao() {
            if (modalExcluir) modalExcluir.classList.add('hidden');
            clienteIdParaExcluir = null;
        }

        if (btnCancelarExclusao) btnCancelarExclusao.addEventListener('click', fecharModalExclusao);
        if (modalExcluirOverlay) modalExcluirOverlay.addEventListener('click', fecharModalExclusao);

        if (btnConfirmarExclusao) {
            btnConfirmarExclusao.addEventListener('click', function() {
                if (!clienteIdParaExcluir) return;

                btnConfirmarExclusao.disabled = true;
                btnConfirmarExclusao.textContent = 'Excluindo...';

                var tokenMeta = document.querySelector('meta[name="csrf-token"]');
                fetch('/app/cliente/' + clienteIdParaExcluir, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': tokenMeta ? tokenMeta.content : '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (!data.success) {
                        throw new Error(data.message || 'Erro ao excluir cliente');
                    }
                    if (window.showToast) window.showToast(data.message || 'Cliente excluido com sucesso!', 'success');

                    // Remover linha da tabela
                    var row = container.querySelector('tr[data-cliente-id="' + clienteIdParaExcluir + '"]');
                    if (row) row.remove();

                    fecharModalExclusao();
                    atualizarBarraAcoes();
                })
                .catch(function(err) {
                    console.error('[Clientes] Erro ao excluir:', err);
                    if (window.showToast) window.showToast(err.message || 'Erro ao excluir cliente', 'error');
                    fecharModalExclusao();
                })
                .finally(function() {
                    btnConfirmarExclusao.disabled = false;
                    btnConfirmarExclusao.textContent = 'Excluir';
                });
            });
        }
    }

    // Expor globalmente para SPA
    window.initClientes = initClientes;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initClientes, { once: true });
    } else {
        initClientes();
    }
})();
</script>
