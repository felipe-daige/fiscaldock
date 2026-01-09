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
                        Gerencie seus clientes e monitore alertas e análises
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Card: Clientes Ativos --}}
                <div id="card-total-clientes" class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Clientes Ativos</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $totalAtivos ?? 0 }}</p>
                        </div>
                        <div class="text-4xl">👥</div>
                    </div>
                </div>

                {{-- Card: Clientes Inativos --}}
                <div id="card-inativos" class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow border-l-4 border-gray-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Clientes Inativos</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $totalInativos ?? 0 }}</p>
                        </div>
                        <div class="text-4xl">🚫</div>
                    </div>
                </div>

                {{-- Card: Pessoa Jurídica --}}
                <div id="card-pj" class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow border-l-4 border-amber-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Pessoa Jurídica</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $totalPJ ?? 0 }}</p>
                        </div>
                        <div class="text-4xl">🏢</div>
                    </div>
                </div>

                {{-- Card: Pessoa Física --}}
                <div id="card-pf" class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Pessoa Física</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $totalPF ?? 0 }}</p>
                        </div>
                        <div class="text-4xl">👤</div>
                    </div>
                </div>
            </div>

            {{-- Barra de Busca e Filtros --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="mb-4">
                    <div>
                        <input 
                            type="text" 
                            id="buscar-cliente" 
                            placeholder="Buscar por nome, CNPJ ou CPF..." 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
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
                        <option value="pj">Pessoa Jurídica (CNPJ)</option>
                        <option value="pf">Pessoa Física (CPF)</option>
                    </select>
                </div>
            </div>

            {{-- Tabela de Clientes --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                @if(isset($clientes) && $clientes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="tabela-clientes">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-100 sortable" data-column="cliente">
                                    Cliente
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-100 sortable" data-column="documento">
                                    CNPJ/CPF
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    E-mail
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Telefone
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Cidade/UF
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody id="clientes-tbody" class="bg-white divide-y divide-gray-200">
                            @foreach($clientes as $cliente)
                            <tr class="hover:bg-blue-50/50 transition-colors cliente-row" 
                                data-cliente-id="{{ $cliente->id }}"
                                data-nome="{{ strtolower($cliente->nome) }}"
                                data-documento="{{ $cliente->documento }}"
                                data-tipo="{{ $cliente->tipo_pessoa }}"
                                data-status="{{ $cliente->ativo ? 'ativos' : 'inativos' }}">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="cliente-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded" data-id="{{ $cliente->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="text-sm font-medium text-gray-900">{{ $cliente->nome }}</div>
                                        @if($cliente->tipo_pessoa === 'PJ')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">PJ</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">PF</span>
                                        @endif
                                    </div>
                                    @if($cliente->razao_social && $cliente->razao_social !== $cliente->nome)
                                        <div class="text-xs text-gray-500">{{ $cliente->razao_social }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $cliente->documento_formatado }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $cliente->email ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $cliente->telefone ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($cliente->endereco)
                                        {{ $cliente->endereco->cidade }}/{{ $cliente->endereco->estado }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($cliente->ativo)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Ativo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Inativo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="relative inline-block">
                                        <button type="button" class="acoes-btn p-1 text-gray-400 hover:text-gray-600" data-id="{{ $cliente->id }}">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>
                                        <div id="dropdown-{{ $cliente->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                            <div class="py-1">
                                                <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-acao="ver-detalhes" data-id="{{ $cliente->id }}">👁️ Ver detalhes</a>
                                                <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-acao="importar-sped" data-id="{{ $cliente->id }}">📄 Importar SPED</a>
                                                <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-acao="nova-analise" data-id="{{ $cliente->id }}">🔍 Nova análise de risco</a>
                                                <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-acao="editar" data-id="{{ $cliente->id }}">✏️ Editar</a>
                                                <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-red-600" data-acao="excluir" data-id="{{ $cliente->id }}">🗑️ Excluir</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginação --}}
                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Mostrando <span id="pagina-inicio">1</span>-<span id="pagina-fim">{{ min(10, $clientes->count()) }}</span> de <span id="total-clientes">{{ $clientes->count() }}</span> clientes
                    </div>
                    <div class="flex gap-2" id="paginacao">
                        <!-- Paginação será renderizada via JavaScript se necessário -->
                    </div>
                </div>
                @else
                {{-- Estado Vazio --}}
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">📋</div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Nenhum cliente cadastrado</h3>
                    <p class="text-gray-600 mb-6">Comece cadastrando seu primeiro cliente para gerenciá-lo aqui.</p>
                    <a href="/app/novo_cliente" data-link class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Cadastrar Primeiro Cliente
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Barra de Ações em Lote --}}
    <div id="acoes-lote" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50 hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700">
                        ✓ <span id="clientes-selecionados-count">0</span> clientes selecionados
                    </span>
                </div>
                <div class="flex gap-3">
                    <button type="button" id="btn-exportar" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors flex items-center gap-2">
                        <span>📥</span>
                        Exportar lista
                    </button>
                    <button type="button" id="btn-limpar-selecao" class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm font-medium">
                        ❌ Limpar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Estado global
let clientesState = {
    clientesSelecionados: new Set(),
    paginaAtual: 1,
    itensPorPagina: 10
};

// Função para aplicar filtros
function aplicarFiltros() {
    const busca = document.getElementById('buscar-cliente').value.toLowerCase();
    const status = document.getElementById('filtro-status').value;
    const tipo = document.getElementById('filtro-tipo').value;

    const rows = document.querySelectorAll('.cliente-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const nome = row.dataset.nome;
        const documento = row.dataset.documento;
        const tipoCliente = row.dataset.tipo;
        const statusCliente = row.dataset.status;

        let show = true;

        // Filtro de busca
        if (busca && !nome.includes(busca) && !documento.includes(busca)) {
            show = false;
        }

        // Filtro de status
        if (status !== 'todos' && statusCliente !== status) {
            show = false;
        }

        // Filtro de tipo
        if (tipo !== 'todos') {
            const tipoFiltro = tipo === 'pj' ? 'PJ' : 'PF';
            if (tipoCliente !== tipoFiltro) {
                show = false;
            }
        }

        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    // Atualizar contadores
    document.getElementById('pagina-inicio').textContent = visibleCount > 0 ? 1 : 0;
    document.getElementById('pagina-fim').textContent = visibleCount;
    document.getElementById('total-clientes').textContent = visibleCount;
}

// Função para atualizar barra de ações
function atualizarBarraAcoes() {
    const acoesLote = document.getElementById('acoes-lote');
    const count = clientesState.clientesSelecionados.size;

    if (count > 0) {
        acoesLote.classList.remove('hidden');
        document.getElementById('clientes-selecionados-count').textContent = count;
    } else {
        acoesLote.classList.add('hidden');
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Busca
    const buscarInput = document.getElementById('buscar-cliente');
    if (buscarInput) {
        buscarInput.addEventListener('input', aplicarFiltros);
    }

    // Filtros
    const filtroStatus = document.getElementById('filtro-status');
    const filtroTipo = document.getElementById('filtro-tipo');
    
    if (filtroStatus) {
        filtroStatus.addEventListener('change', aplicarFiltros);
    }
    if (filtroTipo) {
        filtroTipo.addEventListener('change', aplicarFiltros);
    }

    // Selecionar todos
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function(e) {
            const checked = e.target.checked;
            const checkboxes = document.querySelectorAll('.cliente-checkbox');
            checkboxes.forEach(cb => {
                const row = cb.closest('.cliente-row');
                if (row && row.style.display !== 'none') {
                    cb.checked = checked;
                    const id = parseInt(cb.dataset.id);
                    if (checked) {
                        clientesState.clientesSelecionados.add(id);
                    } else {
                        clientesState.clientesSelecionados.delete(id);
                    }
                }
            });
            atualizarBarraAcoes();
        });
    }

    // Checkboxes individuais
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('cliente-checkbox')) {
            const id = parseInt(e.target.dataset.id);
            if (e.target.checked) {
                clientesState.clientesSelecionados.add(id);
            } else {
                clientesState.clientesSelecionados.delete(id);
                const selectAllEl = document.getElementById('select-all');
                if (selectAllEl) selectAllEl.checked = false;
            }
            atualizarBarraAcoes();
        }
    });

    // Dropdown de ações
    document.addEventListener('click', function(e) {
        if (e.target.closest('.acoes-btn')) {
            e.stopPropagation();
            const btn = e.target.closest('.acoes-btn');
            const id = btn.dataset.id;
            const dropdown = document.getElementById(`dropdown-${id}`);
            
            // Fechar outros dropdowns
            document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
                if (d.id !== `dropdown-${id}`) {
                    d.classList.add('hidden');
                }
            });

            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        } else {
            // Fechar todos os dropdowns ao clicar fora
            document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
                d.classList.add('hidden');
            });
        }
    });

    // Ações do dropdown
    document.addEventListener('click', function(e) {
        if (e.target.closest('.dropdown-item')) {
            e.preventDefault();
            const item = e.target.closest('.dropdown-item');
            const acao = item.dataset.acao;
            const id = item.dataset.id;
            
            // Fechar dropdown
            const dropdown = document.getElementById(`dropdown-${id}`);
            if (dropdown) dropdown.classList.add('hidden');

            // Executar ação
            console.log(`Ação: ${acao}, Cliente ID: ${id}`);
            // Aqui você pode adicionar lógica para cada ação
        }
    });

    // Cards clicáveis
    const cardTotal = document.getElementById('card-total-clientes');
    if (cardTotal) {
        cardTotal.addEventListener('click', function() {
            document.getElementById('filtro-status').value = 'ativos';
            aplicarFiltros();
        });
    }

    const cardInativos = document.getElementById('card-inativos');
    if (cardInativos) {
        cardInativos.addEventListener('click', function() {
            document.getElementById('filtro-status').value = 'inativos';
            aplicarFiltros();
        });
    }

    const cardPJ = document.getElementById('card-pj');
    if (cardPJ) {
        cardPJ.addEventListener('click', function() {
            document.getElementById('filtro-tipo').value = 'pj';
            aplicarFiltros();
        });
    }

    const cardPF = document.getElementById('card-pf');
    if (cardPF) {
        cardPF.addEventListener('click', function() {
            document.getElementById('filtro-tipo').value = 'pf';
            aplicarFiltros();
        });
    }

    // Ações em lote
    const btnExportar = document.getElementById('btn-exportar');
    if (btnExportar) {
        btnExportar.addEventListener('click', function() {
            console.log('Exportar:', Array.from(clientesState.clientesSelecionados));
        });
    }

    const btnLimpar = document.getElementById('btn-limpar-selecao');
    if (btnLimpar) {
        btnLimpar.addEventListener('click', function() {
            clientesState.clientesSelecionados.clear();
            document.querySelectorAll('.cliente-checkbox').forEach(cb => cb.checked = false);
            const selectAllEl = document.getElementById('select-all');
            if (selectAllEl) selectAllEl.checked = false;
            atualizarBarraAcoes();
        });
    }
});
</script>
