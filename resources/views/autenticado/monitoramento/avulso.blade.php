{{-- Monitoramento - Consulta Avulsa --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-avulso-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Consulta Avulsa</h1>
                    <p class="mt-1 text-sm text-gray-600">Consulte a situacao cadastral e fiscal de CNPJs.</p>
                </div>
                <a
                    href="/app/monitoramento"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                    data-link
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>

        {{-- Grid: Formulario + Info --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 items-start">
            {{-- Card Esquerdo: Nova Consulta --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Nova Consulta</h2>
                </div>
                <div class="p-6">
                    <form id="form-consulta-avulsa">
                        {{-- Input CNPJ unico --}}
                        <div class="mb-4">
                            <label for="cnpj-input" class="block text-sm font-medium text-gray-700 mb-2">
                                CNPJ:
                            </label>
                            <input
                                type="text"
                                id="cnpj-input"
                                name="cnpj"
                                class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                                placeholder="00.000.000/0000-00"
                                maxlength="18"
                                autocomplete="off"
                            >
                            <p class="mt-1 text-xs text-gray-500">
                                Digite o CNPJ do fornecedor ou cliente que deseja consultar.
                            </p>
                        </div>

                        {{-- Selecao de Cliente (Opcional) --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Associar a um Cliente: <span class="text-gray-400 font-normal">(opcional)</span>
                            </label>
                            <select id="cliente-select" name="cliente_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Nao associar a um cliente</option>
                                @foreach($clientes ?? [] as $cliente)
                                    <option value="{{ $cliente->id }}">
                                        {{ $cliente->razao_social ?? $cliente->nome }}
                                        ({{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cliente->documento) }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Vincule o participante a um cliente para melhor organizacao.
                            </p>
                        </div>

                        {{-- Selecao do Plano --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de consulta:
                            </label>
                            <div class="space-y-2" id="planos-grid">
                                @php
                                    $planosEstaticos = [
                                        ['codigo' => 'basico', 'nome' => 'Basico', 'creditos' => 0, 'gratuito' => true, 'descricao' => 'Situacao Cadastral + Simples Nacional'],
                                        ['codigo' => 'cadastral', 'nome' => 'Cadastral+', 'creditos' => 3, 'gratuito' => false, 'descricao' => 'CNPJ completo + SINTEGRA + IE'],
                                        ['codigo' => 'fiscal_federal', 'nome' => 'Fiscal Federal', 'creditos' => 6, 'gratuito' => false, 'descricao' => 'CND Federal + FGTS'],
                                        ['codigo' => 'fiscal_completo', 'nome' => 'Fiscal Completo', 'creditos' => 12, 'gratuito' => false, 'descricao' => 'Federal + Estadual + CNDT'],
                                        ['codigo' => 'due_diligence', 'nome' => 'Due Diligence', 'creditos' => 18, 'gratuito' => false, 'descricao' => 'Completo + Protestos + Processos'],
                                    ];
                                @endphp

                                @foreach($planos ?? $planosEstaticos as $index => $plano)
                                    @php
                                        $codigo = is_array($plano) ? $plano['codigo'] : $plano->codigo;
                                        $nome = is_array($plano) ? $plano['nome'] : $plano->nome;
                                        $creditos = is_array($plano) ? $plano['creditos'] : $plano->custo_creditos;
                                        $gratuito = is_array($plano) ? $plano['gratuito'] : $plano->is_gratuito;
                                        $descricao = is_array($plano) ? $plano['descricao'] : $plano->descricao;
                                    @endphp
                                    <label class="plano-option relative cursor-pointer block">
                                        <input
                                            type="radio"
                                            name="plano"
                                            value="{{ $codigo }}"
                                            data-creditos="{{ $creditos }}"
                                            class="sr-only peer"
                                            {{ $index === 0 ? 'checked' : '' }}
                                        >
                                        <div class="p-3 rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-gray-900">{{ $nome }}</span>
                                                    <span class="text-xs text-gray-500">- {{ $descricao }}</span>
                                                </div>
                                                @if($gratuito)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                                        Gratis
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                                        {{ $creditos }} cred.
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Resumo e Submit --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Custo:</p>
                                    <p class="text-lg font-bold text-gray-900">
                                        <span id="custo-total">0</span> creditos
                                    </p>
                                    <p class="text-xs text-gray-500">Saldo: <strong>{{ $credits ?? 0 }}</strong> creditos</p>
                                </div>
                                <button
                                    type="submit"
                                    id="btn-consultar"
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled
                                >
                                    <svg class="w-4 h-4 btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    <svg class="btn-spinner hidden w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span class="btn-text">Consultar</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Card Direito: Como Funciona --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Como Funciona</h3>
                    </div>
                </div>
                <div class="p-6">
                    {{-- Passo a passo --}}
                    <div class="space-y-4 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">1</div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Digite o CNPJ</p>
                                <p class="text-xs text-gray-500">Informe o CNPJ do fornecedor ou cliente que deseja consultar</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">2</div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Associe a um cliente (opcional)</p>
                                <p class="text-xs text-gray-500">Vincule o participante a um cliente para melhor organizacao</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">3</div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Escolha o tipo de consulta</p>
                                <p class="text-xs text-gray-500">Quanto mais completa, mais informacoes voce recebe</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs font-bold">4</div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Resultado salvo automaticamente</p>
                                <p class="text-xs text-gray-500">O participante sera adicionado a sua lista para futuras consultas</p>
                            </div>
                        </div>
                    </div>

                    {{-- Tabela de creditos compacta --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Tabela de Creditos</h4>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="space-y-1.5 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Basico</span>
                                    <span class="font-medium text-green-600">Gratis</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Cadastral+</span>
                                    <span class="font-medium text-gray-900">3 cred.</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Fiscal Federal</span>
                                    <span class="font-medium text-gray-900">6 cred.</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Fiscal Completo</span>
                                    <span class="font-medium text-gray-900">12 cred.</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Due Diligence</span>
                                    <span class="font-medium text-gray-900">18 cred.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Resultado da Consulta --}}
        <div id="resultado-container" class="hidden mb-8">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Resultado da Consulta</h2>
                        <button type="button" id="btn-nova-consulta" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            Nova Consulta
                        </button>
                    </div>
                </div>
                <div class="p-6" id="resultado-content">
                    {{-- Resultados serao renderizados aqui via JavaScript --}}
                </div>
            </div>
        </div>

        {{-- Secao: Participantes Cadastrados --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900">Participantes Cadastrados</h2>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            id="busca-participante"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-64"
                            placeholder="Buscar CNPJ ou razao social..."
                        >
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNPJ</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razao Social</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Situacao</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Regime</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ultima Consulta</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white" id="participantes-tbody">
                        @forelse($participantes ?? [] as $participante)
                            <tr class="hover:bg-gray-50 participante-row" data-cnpj="{{ $participante->cnpj }}" data-razao="{{ $participante->razao_social ?? '' }}">
                                <td class="px-4 py-3 text-sm font-mono text-gray-900">
                                    {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $participante->cnpj) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $participante->razao_social ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($participante->situacao_cadastral === 'ATIVA')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Ativa</span>
                                    @elseif($participante->situacao_cadastral === 'BAIXADA' || $participante->situacao_cadastral === 'INAPTA')
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">{{ $participante->situacao_cadastral }}</span>
                                    @elseif($participante->situacao_cadastral)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">{{ $participante->situacao_cadastral }}</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $participante->regime_tributario ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $participante->ultima_consulta_em ? $participante->ultima_consulta_em->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <button
                                        type="button"
                                        class="btn-reconsultar inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm font-medium"
                                        data-cnpj="{{ $participante->cnpj }}"
                                        data-id="{{ $participante->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Reconsultar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="empty-row">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    Nenhum participante cadastrado ainda. Faca uma consulta para adicionar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initMonitoramentoAvulso() {
        const container = document.getElementById('monitoramento-avulso-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento Avulso] Inicializando...');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const form = document.getElementById('form-consulta-avulsa');
        const cnpjInput = document.getElementById('cnpj-input');
        const clienteSelect = document.getElementById('cliente-select');
        const custoTotal = document.getElementById('custo-total');
        const btnConsultar = document.getElementById('btn-consultar');
        const btnNovaConsulta = document.getElementById('btn-nova-consulta');
        const resultadoContainer = document.getElementById('resultado-container');
        const resultadoContent = document.getElementById('resultado-content');
        const buscaParticipante = document.getElementById('busca-participante');

        // Mascara de CNPJ
        function formatarCnpj(valor) {
            valor = valor.replace(/\D/g, '');
            if (valor.length > 14) valor = valor.slice(0, 14);

            if (valor.length > 12) {
                valor = valor.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2})/, '$1.$2.$3/$4-$5');
            } else if (valor.length > 8) {
                valor = valor.replace(/(\d{2})(\d{3})(\d{3})(\d{0,4})/, '$1.$2.$3/$4');
            } else if (valor.length > 5) {
                valor = valor.replace(/(\d{2})(\d{3})(\d{0,3})/, '$1.$2.$3');
            } else if (valor.length > 2) {
                valor = valor.replace(/(\d{2})(\d{0,3})/, '$1.$2');
            }
            return valor;
        }

        function extrairCnpj(texto) {
            if (!texto) return '';
            return texto.replace(/\D/g, '');
        }

        function getCreditosPlano() {
            const planoSelecionado = document.querySelector('input[name="plano"]:checked');
            return planoSelecionado ? parseInt(planoSelecionado.dataset.creditos || 0) : 0;
        }

        function atualizarCalculos() {
            const cnpj = extrairCnpj(cnpjInput.value);
            const creditosPlano = getCreditosPlano();
            const total = cnpj.length === 14 ? creditosPlano : 0;

            custoTotal.textContent = total.toLocaleString('pt-BR');
            btnConsultar.disabled = cnpj.length !== 14;
        }

        // Event listeners
        if (cnpjInput) {
            cnpjInput.addEventListener('input', function(e) {
                e.target.value = formatarCnpj(e.target.value);
                atualizarCalculos();
            });
        }

        // Mudanca de plano
        document.querySelectorAll('input[name="plano"]').forEach(function(radio) {
            radio.addEventListener('change', atualizarCalculos);
        });

        // Nova consulta
        if (btnNovaConsulta) {
            btnNovaConsulta.addEventListener('click', function() {
                resultadoContainer.classList.add('hidden');
                cnpjInput.value = '';
                atualizarCalculos();
                cnpjInput.focus();
            });
        }

        // Busca de participantes
        if (buscaParticipante) {
            buscaParticipante.addEventListener('input', function() {
                const termo = this.value.toLowerCase().replace(/\D/g, '');
                const termoTexto = this.value.toLowerCase();
                const rows = document.querySelectorAll('.participante-row');

                rows.forEach(function(row) {
                    const cnpj = row.dataset.cnpj || '';
                    const razao = (row.dataset.razao || '').toLowerCase();

                    if (cnpj.includes(termo) || razao.includes(termoTexto) || !termoTexto) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Reconsultar participante
        document.querySelectorAll('.btn-reconsultar').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const cnpj = this.dataset.cnpj;
                if (cnpjInput && cnpj) {
                    cnpjInput.value = formatarCnpj(cnpj);
                    atualizarCalculos();

                    // Scroll para o formulario
                    document.querySelector('#form-consulta-avulsa').scrollIntoView({ behavior: 'smooth', block: 'start' });

                    // Highlight visual
                    cnpjInput.classList.add('ring-2', 'ring-blue-500');
                    setTimeout(function() {
                        cnpjInput.classList.remove('ring-2', 'ring-blue-500');
                    }, 2000);
                }
            });
        });

        // Renderizar resultado
        function renderizarResultado(dados) {
            if (!dados || !dados.resultado) {
                resultadoContent.innerHTML = '<div class="text-center py-8 text-gray-500">Nenhum resultado encontrado.</div>';
                return;
            }

            const r = dados.resultado;
            const cnpjFormatado = r.cnpj ? r.cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5') : '-';

            let situacaoClass = 'bg-gray-100 text-gray-700';
            if (r.situacao_cadastral === 'ATIVA') situacaoClass = 'bg-green-100 text-green-700';
            else if (r.situacao_cadastral === 'BAIXADA' || r.situacao_cadastral === 'INAPTA') situacaoClass = 'bg-red-100 text-red-700';
            else if (r.situacao_cadastral === 'SUSPENSA') situacaoClass = 'bg-amber-100 text-amber-700';

            let html = '<div class="border border-gray-200 rounded-lg p-4">';
            html += '<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-3">';
            html += '<div class="min-w-0">';
            html += '<h3 class="text-base font-semibold text-gray-900 truncate">' + (r.razao_social || 'Razao Social nao informada') + '</h3>';
            html += '<p class="text-sm text-gray-600 font-mono">' + cnpjFormatado + '</p>';
            html += '</div>';
            html += '<div class="flex items-center gap-2 flex-shrink-0">';
            html += '<span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium ' + situacaoClass + '">' + (r.situacao_cadastral || '-') + '</span>';
            if (r.regime_tributario) {
                html += '<span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700">' + r.regime_tributario + '</span>';
            }
            html += '</div>';
            html += '</div>';

            // Detalhes adicionais
            if (r.detalhes && Object.keys(r.detalhes).length > 0) {
                html += '<div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-3 border-t border-gray-200">';

                if (r.detalhes.cnd_federal) {
                    const cndClass = r.detalhes.cnd_federal.status === 'NEGATIVA' ? 'text-green-600' : 'text-red-600';
                    html += '<div><p class="text-xs text-gray-500">CND Federal</p><p class="text-sm font-medium ' + cndClass + '">' + r.detalhes.cnd_federal.status + '</p></div>';
                }
                if (r.detalhes.fgts) {
                    const fgtsClass = r.detalhes.fgts.status === 'REGULAR' ? 'text-green-600' : 'text-red-600';
                    html += '<div><p class="text-xs text-gray-500">FGTS</p><p class="text-sm font-medium ' + fgtsClass + '">' + r.detalhes.fgts.status + '</p></div>';
                }
                if (r.detalhes.cndt) {
                    const cndtClass = r.detalhes.cndt.status === 'NEGATIVA' ? 'text-green-600' : 'text-red-600';
                    html += '<div><p class="text-xs text-gray-500">CNDT</p><p class="text-sm font-medium ' + cndtClass + '">' + r.detalhes.cndt.status + '</p></div>';
                }
                if (r.detalhes.protestos !== undefined) {
                    const protestosClass = r.detalhes.protestos === 0 ? 'text-green-600' : 'text-red-600';
                    html += '<div><p class="text-xs text-gray-500">Protestos</p><p class="text-sm font-medium ' + protestosClass + '">' + r.detalhes.protestos + '</p></div>';
                }

                html += '</div>';
            }

            html += '</div>';

            // Info de creditos
            html += '<div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between text-sm">';
            html += '<span class="text-gray-600">Participante salvo na sua lista</span>';
            html += '<span class="text-gray-600">Creditos utilizados: <strong>' + (dados.creditos_utilizados || 0) + '</strong></span>';
            html += '</div>';

            resultadoContent.innerHTML = html;
        }

        // Submit do formulario
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const cnpj = extrairCnpj(cnpjInput.value);
                if (cnpj.length !== 14) {
                    if (window.showToast) {
                        window.showToast('warning', 'Por favor, insira um CNPJ valido com 14 digitos.');
                    } else {
                        alert('Por favor, insira um CNPJ valido com 14 digitos.');
                    }
                    return;
                }

                const planoSelecionado = document.querySelector('input[name="plano"]:checked');
                if (!planoSelecionado) {
                    if (window.showToast) {
                        window.showToast('warning', 'Por favor, selecione um tipo de consulta.');
                    } else {
                        alert('Por favor, selecione um tipo de consulta.');
                    }
                    return;
                }

                const btnText = btnConsultar.querySelector('.btn-text');
                const btnSpinner = btnConsultar.querySelector('.btn-spinner');
                const btnIcon = btnConsultar.querySelector('.btn-icon');

                btnConsultar.disabled = true;
                if (btnText) btnText.textContent = 'Consultando...';
                if (btnSpinner) btnSpinner.classList.remove('hidden');
                if (btnIcon) btnIcon.classList.add('hidden');

                try {
                    const payload = {
                        cnpj: cnpj,
                        plano: planoSelecionado.value,
                    };

                    // Adicionar cliente_id se selecionado
                    if (clienteSelect && clienteSelect.value) {
                        payload.cliente_id = clienteSelect.value;
                    }

                    const response = await fetch('/app/monitoramento/consulta-avulsa', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        renderizarResultado(data);
                        resultadoContainer.classList.remove('hidden');

                        // Scroll para resultado
                        resultadoContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

                        if (window.showToast) {
                            window.showToast('success', 'Consulta realizada com sucesso!');
                        }
                    } else {
                        throw new Error(data.message || data.error || 'Erro ao realizar consulta');
                    }
                } catch (err) {
                    console.error('[Monitoramento Avulso] Erro:', err);
                    if (window.showToast) {
                        window.showToast('error', err.message || 'Erro ao realizar consulta.');
                    } else {
                        alert('Erro ao realizar consulta: ' + err.message);
                    }
                } finally {
                    btnConsultar.disabled = false;
                    if (btnText) btnText.textContent = 'Consultar';
                    if (btnSpinner) btnSpinner.classList.add('hidden');
                    if (btnIcon) btnIcon.classList.remove('hidden');
                    atualizarCalculos();
                }
            });
        }

        // Inicializar calculos
        atualizarCalculos();

        console.log('[Monitoramento Avulso] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initMonitoramentoAvulso = initMonitoramentoAvulso;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramentoAvulso, { once: true });
    } else {
        initMonitoramentoAvulso();
    }
})();
</script>
