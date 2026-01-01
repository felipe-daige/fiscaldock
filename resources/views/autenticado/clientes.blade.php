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
                            <p class="text-2xl font-bold text-gray-800">24</p>
                        </div>
                        <div class="text-4xl">👥</div>
                    </div>
                </div>

                {{-- Card: Alertas Pendentes --}}
                <div id="card-alertas-pendentes" class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Alertas Pendentes</p>
                            <p class="text-2xl font-bold text-gray-800">7</p>
                        </div>
                        <div class="text-4xl">🚨</div>
                    </div>
                </div>

                {{-- Card: Sem SPED --}}
                <div id="card-sem-sped" class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow border-l-4 border-amber-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Sem SPED (mês atual)</p>
                            <p class="text-2xl font-bold text-gray-800">5</p>
                        </div>
                        <div class="text-4xl">⏰</div>
                    </div>
                </div>

                {{-- Card: Análises este mês --}}
                <div id="card-analises-mes" class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition-shadow border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Análises este mês</p>
                            <p class="text-2xl font-bold text-gray-800">18</p>
                        </div>
                        <div class="text-4xl">📊</div>
                    </div>
                </div>
            </div>

            {{-- Card de Alertas Urgentes --}}
            <div id="alertas-urgentes" class="bg-red-50 border-2 border-red-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-red-800 flex items-center gap-2">
                        <span class="text-2xl">🚨</span>
                        ATENÇÃO NECESSÁRIA
                    </h3>
                </div>
                <ul class="space-y-2 mb-4">
                    <li class="text-sm text-gray-800">• <strong>ACME Comércio LTDA</strong> - 3 fornecedores em risco crítico</li>
                    <li class="text-sm text-gray-800">• <strong>XYZ Indústria S/A</strong> - Último SPED há 45 dias</li>
                    <li class="text-sm text-gray-800">• <strong>ABC Serviços ME</strong> - 5 alertas não resolvidos</li>
                    <li class="text-sm text-gray-800">• <strong>123 Distribuidora</strong> - CNPJ de fornecedor baixado</li>
                    <li class="text-sm text-gray-800">• <strong>Tech Solutions</strong> - Fornecedor na lista CEIS</li>
                </ul>
                <a href="#" class="text-sm text-red-700 hover:text-red-900 font-medium inline-flex items-center gap-1">
                    Ver todos os alertas →
                </a>
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
                    <select id="filtro-situacao" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="todos">Situação: Todos</option>
                        <option value="com-alertas">Com alertas pendentes</option>
                        <option value="sem-sped">Sem SPED este mês</option>
                        <option value="em-dia">Em dia</option>
                    </select>
                </div>
            </div>

            {{-- Tabela de Clientes --}}
            <div class="bg-white rounded-lg shadow-md p-6">
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-100 sortable" data-column="ultimo-sped">
                                    Último SPED
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-100 sortable" data-column="fornecedores">
                                    Fornecedores
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-100 sortable" data-column="alertas">
                                    Alertas
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-100 sortable" data-column="score">
                                    Score Médio
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody id="clientes-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Dados serão renderizados via JavaScript -->
                        </tbody>
                    </table>
                </div>

                {{-- Paginação --}}
                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Mostrando <span id="pagina-inicio">1</span>-<span id="pagina-fim">10</span> de <span id="total-clientes">24</span> clientes
                    </div>
                    <div class="flex gap-2" id="paginacao">
                        <!-- Paginação será renderizada aqui -->
                    </div>
                </div>
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
                    <button type="button" id="btn-enviar-lembrete" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <span>📧</span>
                        Enviar lembrete SPED
                    </button>
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
// Dados mockados
const clientesMock = [
    {
        id: 1,
        nome: 'ACME Comércio LTDA',
        documento: '12.345.678/0001-90',
        tipo: 'PJ',
        ultimoSped: '28/01/2025',
        fornecedores: 42,
        alertas: { tipo: 'critico', quantidade: 3 },
        score: 45,
        status: 'ativo'
    },
    {
        id: 2,
        nome: 'XYZ Indústria S/A',
        documento: '23.456.789/0001-01',
        tipo: 'PJ',
        ultimoSped: '15/12/2024',
        fornecedores: 38,
        alertas: { tipo: 'atencao', quantidade: 1 },
        score: 72,
        status: 'ativo'
    },
    {
        id: 3,
        nome: 'ABC Serviços ME',
        documento: '34.567.890/0001-12',
        tipo: 'PJ',
        ultimoSped: '20/01/2025',
        fornecedores: 15,
        alertas: { tipo: 'critico', quantidade: 5 },
        score: 38,
        status: 'ativo'
    },
    {
        id: 4,
        nome: 'Distribuidora 123 LTDA',
        documento: '45.678.901/0001-23',
        tipo: 'PJ',
        ultimoSped: '25/01/2025',
        fornecedores: 67,
        alertas: { tipo: 'ok', quantidade: 0 },
        score: 89,
        status: 'ativo'
    },
    {
        id: 5,
        nome: 'Tech Solutions EIRELI',
        documento: '56.789.012/0001-34',
        tipo: 'PJ',
        ultimoSped: '22/01/2025',
        fornecedores: 23,
        alertas: { tipo: 'atencao', quantidade: 2 },
        score: 65,
        status: 'ativo'
    },
    {
        id: 6,
        nome: 'Comercial Brasil LTDA',
        documento: '67.890.123/0001-45',
        tipo: 'PJ',
        ultimoSped: '27/01/2025',
        fornecedores: 51,
        alertas: { tipo: 'ok', quantidade: 0 },
        score: 92,
        status: 'ativo'
    },
    {
        id: 7,
        nome: 'João Silva',
        documento: '123.456.789-00',
        tipo: 'PF',
        ultimoSped: null,
        fornecedores: 3,
        alertas: { tipo: 'ok', quantidade: 0 },
        score: 88,
        status: 'ativo'
    },
    {
        id: 8,
        nome: 'Maria Santos',
        documento: '234.567.890-11',
        tipo: 'PF',
        ultimoSped: null,
        fornecedores: 1,
        alertas: { tipo: 'ok', quantidade: 0 },
        score: 95,
        status: 'ativo'
    },
    {
        id: 9,
        nome: 'Atacado Premium LTDA',
        documento: '78.901.234/0001-56',
        tipo: 'PJ',
        ultimoSped: '10/01/2025',
        fornecedores: 89,
        alertas: { tipo: 'atencao', quantidade: 1 },
        score: 78,
        status: 'ativo'
    },
    {
        id: 10,
        nome: 'Logística Express S/A',
        documento: '89.012.345/0001-67',
        tipo: 'PJ',
        ultimoSped: '26/01/2025',
        fornecedores: 34,
        alertas: { tipo: 'ok', quantidade: 0 },
        score: 85,
        status: 'ativo'
    }
];

// Estado global
let clientesState = {
    clientes: [...clientesMock],
    clientesFiltrados: [...clientesMock],
    clientesSelecionados: new Set(),
    paginaAtual: 1,
    itensPorPagina: 10,
    ordenacao: { coluna: null, direcao: 'asc' }
};

// Função para obter cor do score
function getScoreColor(score) {
    if (score >= 80) return 'bg-green-500';
    if (score >= 60) return 'bg-amber-500';
    if (score >= 40) return 'bg-orange-500';
    return 'bg-red-500';
}

// Função para renderizar alertas
function renderizarAlertas(alertas) {
    if (alertas.tipo === 'ok') {
        return '<span class="text-green-600">✅</span>';
    }
    const icon = alertas.tipo === 'critico' ? '🔴' : '⚠️';
    return `<span class="text-red-600">${icon}</span> <span class="text-sm text-gray-700">${alertas.quantidade}</span>`;
}

// Função para renderizar score
function renderizarScore(score) {
    const cor = getScoreColor(score);
    return `
        <div class="flex items-center gap-2">
            <div class="w-24 bg-gray-200 rounded-full h-2">
                <div class="${cor} h-2 rounded-full" style="width: ${score}%"></div>
            </div>
            <span class="text-sm font-medium text-gray-700">${score}</span>
        </div>
    `;
}

// Função para renderizar tabela
function renderizarTabela() {
    const tbody = document.getElementById('clientes-tbody');
    const inicio = (clientesState.paginaAtual - 1) * clientesState.itensPorPagina;
    const fim = inicio + clientesState.itensPorPagina;
    const clientesPagina = clientesState.clientesFiltrados.slice(inicio, fim);

    tbody.innerHTML = '';

    clientesPagina.forEach(cliente => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-blue-50/50 transition-colors';
        tr.dataset.clienteId = cliente.id;

        const badgeTipo = cliente.tipo === 'PJ' 
            ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">PJ</span>'
            : '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">PF</span>';

        const checked = clientesState.clientesSelecionados.has(cliente.id) ? 'checked' : '';

        tr.innerHTML = `
            <td class="px-4 py-4 whitespace-nowrap">
                <input type="checkbox" class="cliente-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded" data-id="${cliente.id}" ${checked}>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                    <div class="text-sm font-medium text-gray-900">${cliente.nome}</div>
                    ${badgeTipo}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${cliente.documento}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${cliente.ultimoSped || '—'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${cliente.fornecedores}</td>
            <td class="px-6 py-4 whitespace-nowrap">${renderizarAlertas(cliente.alertas)}</td>
            <td class="px-6 py-4 whitespace-nowrap">${renderizarScore(cliente.score)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
                <div class="relative inline-block">
                    <button type="button" class="acoes-btn p-1 text-gray-400 hover:text-gray-600" data-id="${cliente.id}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                        </svg>
                    </button>
                    <div id="dropdown-${cliente.id}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                        <div class="py-1">
                            <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-acao="ver-detalhes" data-id="${cliente.id}">👁️ Ver detalhes</a>
                            <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-acao="importar-sped" data-id="${cliente.id}">📄 Importar SPED</a>
                            <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-acao="nova-analise" data-id="${cliente.id}">🔍 Nova análise de risco</a>
                            <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-acao="editar" data-id="${cliente.id}">✏️ Editar</a>
                            <a href="#" class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 text-red-600" data-acao="excluir" data-id="${cliente.id}">🗑️ Excluir</a>
                        </div>
                    </div>
                </div>
            </td>
        `;

        tbody.appendChild(tr);
    });

    // Atualizar paginação
    atualizarPaginacao();
    atualizarBarraAcoes();
}

// Função para atualizar paginação
function atualizarPaginacao() {
    const totalClientes = clientesState.clientesFiltrados.length;
    const totalPaginas = Math.ceil(totalClientes / clientesState.itensPorPagina);
    const inicio = totalClientes > 0 ? (clientesState.paginaAtual - 1) * clientesState.itensPorPagina + 1 : 0;
    const fim = Math.min(clientesState.paginaAtual * clientesState.itensPorPagina, totalClientes);

    document.getElementById('pagina-inicio').textContent = inicio;
    document.getElementById('pagina-fim').textContent = fim;
    document.getElementById('total-clientes').textContent = totalClientes;

    const paginacao = document.getElementById('paginacao');
    paginacao.innerHTML = '';

    if (totalPaginas <= 1) return;

    // Botão anterior
    if (clientesState.paginaAtual > 1) {
        const btnPrev = document.createElement('button');
        btnPrev.className = 'px-3 py-1.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-50';
        btnPrev.textContent = '<';
        btnPrev.addEventListener('click', () => {
            clientesState.paginaAtual--;
            renderizarTabela();
        });
        paginacao.appendChild(btnPrev);
    }

    // Números de página
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= clientesState.paginaAtual - 1 && i <= clientesState.paginaAtual + 1)) {
            const btn = document.createElement('button');
            btn.className = `px-3 py-1.5 border rounded-lg text-sm ${
                i === clientesState.paginaAtual 
                    ? 'bg-blue-600 text-white border-blue-600' 
                    : 'border-gray-300 hover:bg-gray-50'
            }`;
            btn.textContent = i;
            btn.addEventListener('click', () => {
                clientesState.paginaAtual = i;
                renderizarTabela();
            });
            paginacao.appendChild(btn);
        } else if (i === clientesState.paginaAtual - 2 || i === clientesState.paginaAtual + 2) {
            const span = document.createElement('span');
            span.className = 'px-2 text-gray-500';
            span.textContent = '...';
            paginacao.appendChild(span);
        }
    }

    // Botão próximo
    if (clientesState.paginaAtual < totalPaginas) {
        const btnNext = document.createElement('button');
        btnNext.className = 'px-3 py-1.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-50';
        btnNext.textContent = '>';
        btnNext.addEventListener('click', () => {
            clientesState.paginaAtual++;
            renderizarTabela();
        });
        paginacao.appendChild(btnNext);
    }
}

// Função para aplicar filtros
function aplicarFiltros() {
    let clientes = [...clientesMock];
    const busca = document.getElementById('buscar-cliente').value.toLowerCase();
    const status = document.getElementById('filtro-status').value;
    const tipo = document.getElementById('filtro-tipo').value;
    const situacao = document.getElementById('filtro-situacao').value;

    // Busca
    if (busca) {
        clientes = clientes.filter(c => 
            c.nome.toLowerCase().includes(busca) ||
            c.documento.includes(busca)
        );
    }

    // Filtro status
    if (status !== 'todos') {
        clientes = clientes.filter(c => c.status === status);
    }

    // Filtro tipo
    if (tipo !== 'todos') {
        const tipoFiltro = tipo === 'pj' ? 'PJ' : 'PF';
        clientes = clientes.filter(c => c.tipo === tipoFiltro);
    }

    // Filtro situação
    if (situacao !== 'todos') {
        if (situacao === 'com-alertas') {
            clientes = clientes.filter(c => c.alertas.tipo !== 'ok');
        } else if (situacao === 'sem-sped') {
            const hoje = new Date();
            const primeiroDiaMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
            clientes = clientes.filter(c => {
                if (!c.ultimoSped) return true;
                const dataSped = new Date(c.ultimoSped.split('/').reverse().join('-'));
                return dataSped < primeiroDiaMes;
            });
        } else if (situacao === 'em-dia') {
            clientes = clientes.filter(c => c.alertas.tipo === 'ok');
        }
    }

    clientesState.clientesFiltrados = clientes;
    clientesState.paginaAtual = 1;
    renderizarTabela();
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

// Função para ordenar
function ordenar(coluna) {
    if (clientesState.ordenacao.coluna === coluna) {
        clientesState.ordenacao.direcao = clientesState.ordenacao.direcao === 'asc' ? 'desc' : 'asc';
    } else {
        clientesState.ordenacao.coluna = coluna;
        clientesState.ordenacao.direcao = 'asc';
    }

    clientesState.clientesFiltrados.sort((a, b) => {
        let valorA, valorB;

        switch (coluna) {
            case 'cliente':
                valorA = a.nome.toLowerCase();
                valorB = b.nome.toLowerCase();
                break;
            case 'documento':
                valorA = a.documento;
                valorB = b.documento;
                break;
            case 'ultimo-sped':
                valorA = a.ultimoSped ? new Date(a.ultimoSped.split('/').reverse().join('-')) : new Date(0);
                valorB = b.ultimoSped ? new Date(b.ultimoSped.split('/').reverse().join('-')) : new Date(0);
                break;
            case 'fornecedores':
                valorA = a.fornecedores;
                valorB = b.fornecedores;
                break;
            case 'alertas':
                valorA = a.alertas.quantidade;
                valorB = b.alertas.quantidade;
                break;
            case 'score':
                valorA = a.score;
                valorB = b.score;
                break;
            default:
                return 0;
        }

        if (valorA < valorB) return clientesState.ordenacao.direcao === 'asc' ? -1 : 1;
        if (valorA > valorB) return clientesState.ordenacao.direcao === 'asc' ? 1 : -1;
        return 0;
    });

    renderizarTabela();
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Renderizar tabela inicial
    renderizarTabela();

    // Busca
    document.getElementById('buscar-cliente').addEventListener('input', aplicarFiltros);

    // Filtros
    document.getElementById('filtro-status').addEventListener('change', aplicarFiltros);
    document.getElementById('filtro-tipo').addEventListener('change', aplicarFiltros);
    document.getElementById('filtro-situacao').addEventListener('change', aplicarFiltros);

    // Selecionar todos
    document.getElementById('select-all').addEventListener('change', function(e) {
        const checked = e.target.checked;
        const checkboxes = document.querySelectorAll('.cliente-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checked;
            const id = parseInt(cb.dataset.id);
            if (checked) {
                clientesState.clientesSelecionados.add(id);
            } else {
                clientesState.clientesSelecionados.delete(id);
            }
        });
        atualizarBarraAcoes();
    });

    // Checkboxes individuais
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('cliente-checkbox')) {
            const id = parseInt(e.target.dataset.id);
            if (e.target.checked) {
                clientesState.clientesSelecionados.add(id);
            } else {
                clientesState.clientesSelecionados.delete(id);
                document.getElementById('select-all').checked = false;
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

            dropdown.classList.toggle('hidden');
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
            document.getElementById(`dropdown-${id}`).classList.add('hidden');

            // Executar ação
            console.log(`Ação: ${acao}, Cliente ID: ${id}`);
            // Aqui você pode adicionar lógica para cada ação
        }
    });

    // Cards clicáveis
    document.getElementById('card-total-clientes').addEventListener('click', function() {
        document.getElementById('filtro-status').value = 'ativos';
        aplicarFiltros();
    });

    document.getElementById('card-alertas-pendentes').addEventListener('click', function() {
        document.getElementById('filtro-situacao').value = 'com-alertas';
        aplicarFiltros();
    });

    document.getElementById('card-sem-sped').addEventListener('click', function() {
        document.getElementById('filtro-situacao').value = 'sem-sped';
        aplicarFiltros();
    });

    document.getElementById('card-analises-mes').addEventListener('click', function() {
        // Resetar filtros para mostrar todos
        document.getElementById('filtro-status').value = 'todos';
        document.getElementById('filtro-tipo').value = 'todos';
        document.getElementById('filtro-situacao').value = 'todos';
        aplicarFiltros();
    });

    // Ordenação
    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', function() {
            const coluna = this.dataset.column;
            ordenar(coluna);
        });
    });

    // Ações em lote
    document.getElementById('btn-enviar-lembrete').addEventListener('click', function() {
        console.log('Enviar lembrete para:', Array.from(clientesState.clientesSelecionados));
    });

    document.getElementById('btn-exportar').addEventListener('click', function() {
        console.log('Exportar:', Array.from(clientesState.clientesSelecionados));
    });

    document.getElementById('btn-limpar-selecao').addEventListener('click', function() {
        clientesState.clientesSelecionados.clear();
        document.querySelectorAll('.cliente-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('select-all').checked = false;
        atualizarBarraAcoes();
    });
});
</script>

