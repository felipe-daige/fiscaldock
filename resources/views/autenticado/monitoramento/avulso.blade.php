{{-- Monitoramento - Consulta Avulsa --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-avulso-container">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

        {{-- Formulario de Consulta --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Inserir CNPJs</h2>
            </div>
            <div class="p-6">
                <form id="form-consulta-avulsa">
                    {{-- Textarea para CNPJs --}}
                    <div class="mb-4">
                        <label for="cnpjs-input" class="block text-sm font-medium text-gray-700 mb-2">
                            CNPJs para consulta
                        </label>
                        <textarea
                            id="cnpjs-input"
                            name="cnpjs"
                            rows="6"
                            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                            placeholder="Digite os CNPJs (um por linha ou separados por virgula)&#10;&#10;Exemplo:&#10;12.345.678/0001-99&#10;98765432000188&#10;11.222.333/0001-44"
                        ></textarea>
                        <p class="mt-2 text-xs text-gray-500">
                            Aceita CNPJs com ou sem formatacao. Separe multiplos CNPJs por quebra de linha ou virgula.
                        </p>
                    </div>

                    {{-- Contador de CNPJs --}}
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-sm text-gray-600">
                            <strong id="count-cnpjs">0</strong> CNPJ(s) identificado(s)
                        </span>
                        <button type="button" id="btn-limpar-cnpjs" class="text-sm text-red-600 hover:text-red-700 font-medium">
                            Limpar
                        </button>
                    </div>

                    {{-- Selecao do Plano --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Selecione o tipo de consulta
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3" id="planos-grid">
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
                                <label class="plano-option relative cursor-pointer">
                                    <input
                                        type="radio"
                                        name="plano"
                                        value="{{ $codigo }}"
                                        data-creditos="{{ $creditos }}"
                                        class="sr-only peer"
                                        {{ $index === 0 ? 'checked' : '' }}
                                    >
                                    <div class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-start justify-between mb-2">
                                            <span class="text-sm font-semibold text-gray-900">{{ $nome }}</span>
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
                                        <p class="text-xs text-gray-600">{{ $descricao }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Resumo e Submit --}}
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Custo total estimado:</p>
                                <p class="text-xl font-bold text-gray-900">
                                    <span id="custo-total">0</span> creditos
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 mb-1">Seu saldo atual: <strong>{{ Auth::user()->credits ?? 0 }}</strong> creditos</p>
                                <button
                                    type="submit"
                                    id="btn-consultar"
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    <span class="btn-text">Realizar Consulta</span>
                                    <svg class="btn-spinner hidden w-4 h-4 animate-spin" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Resultado da Consulta --}}
        <div id="resultado-container" class="hidden">
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

        {{-- Status de processamento --}}
        <div id="processando-container" class="hidden">
            <div class="bg-white rounded-xl border border-blue-200 shadow-sm p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-blue-600 animate-spin mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Processando consulta...</h3>
                <p class="text-sm text-gray-600">Aguarde enquanto consultamos as bases de dados. Isso pode levar alguns segundos.</p>
                <p class="text-xs text-gray-500 mt-4">Consultando <span id="processando-count">0</span> CNPJ(s)...</p>
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
        const cnpjsInput = document.getElementById('cnpjs-input');
        const countCnpjs = document.getElementById('count-cnpjs');
        const custoTotal = document.getElementById('custo-total');
        const btnConsultar = document.getElementById('btn-consultar');
        const btnLimpar = document.getElementById('btn-limpar-cnpjs');
        const btnNovaConsulta = document.getElementById('btn-nova-consulta');
        const resultadoContainer = document.getElementById('resultado-container');
        const resultadoContent = document.getElementById('resultado-content');
        const processandoContainer = document.getElementById('processando-container');
        const processandoCount = document.getElementById('processando-count');

        // Funcao para extrair CNPJs do texto
        function extrairCnpjs(texto) {
            if (!texto || !texto.trim()) return [];

            // Remove formatacao e separa por quebra de linha ou virgula
            const linhas = texto.split(/[\n,;]+/);
            const cnpjs = [];

            linhas.forEach(function(linha) {
                // Remove tudo que nao e numero
                const numeros = linha.replace(/\D/g, '');

                // Verifica se tem 14 digitos (CNPJ)
                if (numeros.length === 14) {
                    // Evita duplicados
                    if (!cnpjs.includes(numeros)) {
                        cnpjs.push(numeros);
                    }
                }
            });

            return cnpjs;
        }

        // Funcao para obter creditos do plano selecionado
        function getCreditosPlano() {
            const planoSelecionado = document.querySelector('input[name="plano"]:checked');
            return planoSelecionado ? parseInt(planoSelecionado.dataset.creditos || 0) : 0;
        }

        // Funcao para atualizar calculos
        function atualizarCalculos() {
            const cnpjs = extrairCnpjs(cnpjsInput.value);
            const qtdCnpjs = cnpjs.length;
            const creditosPlano = getCreditosPlano();
            const total = qtdCnpjs * creditosPlano;

            countCnpjs.textContent = qtdCnpjs;
            custoTotal.textContent = total.toLocaleString('pt-BR');
            btnConsultar.disabled = qtdCnpjs === 0;
        }

        // Event listeners
        if (cnpjsInput) {
            cnpjsInput.addEventListener('input', atualizarCalculos);
        }

        // Mudanca de plano
        document.querySelectorAll('input[name="plano"]').forEach(function(radio) {
            radio.addEventListener('change', atualizarCalculos);
        });

        // Limpar CNPJs
        if (btnLimpar) {
            btnLimpar.addEventListener('click', function() {
                cnpjsInput.value = '';
                atualizarCalculos();
            });
        }

        // Nova consulta
        if (btnNovaConsulta) {
            btnNovaConsulta.addEventListener('click', function() {
                resultadoContainer.classList.add('hidden');
                cnpjsInput.value = '';
                atualizarCalculos();
                cnpjsInput.focus();
            });
        }

        // Funcao para renderizar resultado
        function renderizarResultado(dados) {
            if (!dados || !dados.resultados || dados.resultados.length === 0) {
                resultadoContent.innerHTML = '<div class="text-center py-8 text-gray-500">Nenhum resultado encontrado.</div>';
                return;
            }

            let html = '<div class="space-y-4">';

            dados.resultados.forEach(function(r) {
                const cnpjFormatado = r.cnpj ? r.cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5') : '-';

                // Determinar cor do badge de situacao
                let situacaoClass = 'bg-gray-100 text-gray-700';
                if (r.situacao_cadastral === 'ATIVA') situacaoClass = 'bg-green-100 text-green-700';
                else if (r.situacao_cadastral === 'BAIXADA' || r.situacao_cadastral === 'INAPTA') situacaoClass = 'bg-red-100 text-red-700';
                else if (r.situacao_cadastral === 'SUSPENSA') situacaoClass = 'bg-amber-100 text-amber-700';

                html += '<div class="border border-gray-200 rounded-lg p-4">';
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
                if (r.detalhes) {
                    html += '<div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-3 border-t border-gray-200">';

                    if (r.detalhes.cnd_federal) {
                        const cndClass = r.detalhes.cnd_federal.status === 'NEGATIVA' ? 'text-green-600' : 'text-red-600';
                        html += '<div>';
                        html += '<p class="text-xs text-gray-500">CND Federal</p>';
                        html += '<p class="text-sm font-medium ' + cndClass + '">' + r.detalhes.cnd_federal.status + '</p>';
                        html += '</div>';
                    }

                    if (r.detalhes.fgts) {
                        const fgtsClass = r.detalhes.fgts.status === 'REGULAR' ? 'text-green-600' : 'text-red-600';
                        html += '<div>';
                        html += '<p class="text-xs text-gray-500">FGTS</p>';
                        html += '<p class="text-sm font-medium ' + fgtsClass + '">' + r.detalhes.fgts.status + '</p>';
                        html += '</div>';
                    }

                    if (r.detalhes.cndt) {
                        const cndtClass = r.detalhes.cndt.status === 'NEGATIVA' ? 'text-green-600' : 'text-red-600';
                        html += '<div>';
                        html += '<p class="text-xs text-gray-500">CNDT</p>';
                        html += '<p class="text-sm font-medium ' + cndtClass + '">' + r.detalhes.cndt.status + '</p>';
                        html += '</div>';
                    }

                    if (r.detalhes.protestos !== undefined) {
                        const protestosClass = r.detalhes.protestos === 0 ? 'text-green-600' : 'text-red-600';
                        html += '<div>';
                        html += '<p class="text-xs text-gray-500">Protestos</p>';
                        html += '<p class="text-sm font-medium ' + protestosClass + '">' + r.detalhes.protestos + '</p>';
                        html += '</div>';
                    }

                    html += '</div>';
                }

                html += '</div>';
            });

            html += '</div>';

            // Resumo
            html += '<div class="mt-6 pt-6 border-t border-gray-200">';
            html += '<div class="flex items-center justify-between text-sm">';
            html += '<span class="text-gray-600">Total consultado: <strong>' + dados.resultados.length + '</strong> CNPJ(s)</span>';
            html += '<span class="text-gray-600">Creditos utilizados: <strong>' + (dados.creditos_utilizados || 0) + '</strong></span>';
            html += '</div>';
            html += '</div>';

            resultadoContent.innerHTML = html;
        }

        // Submit do formulario
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const cnpjs = extrairCnpjs(cnpjsInput.value);
                if (cnpjs.length === 0) {
                    alert('Por favor, insira pelo menos um CNPJ valido.');
                    return;
                }

                const planoSelecionado = document.querySelector('input[name="plano"]:checked');
                if (!planoSelecionado) {
                    alert('Por favor, selecione um tipo de consulta.');
                    return;
                }

                const btnText = btnConsultar.querySelector('.btn-text');
                const btnSpinner = btnConsultar.querySelector('.btn-spinner');

                btnConsultar.disabled = true;
                if (btnText) btnText.classList.add('hidden');
                if (btnSpinner) btnSpinner.classList.remove('hidden');

                processandoCount.textContent = cnpjs.length;
                processandoContainer.classList.remove('hidden');
                resultadoContainer.classList.add('hidden');

                try {
                    const response = await fetch('/app/monitoramento/consulta-avulsa', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            cnpjs: cnpjs,
                            plano: planoSelecionado.value,
                        }),
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        renderizarResultado(data);
                        resultadoContainer.classList.remove('hidden');
                    } else {
                        throw new Error(data.message || 'Erro ao realizar consulta');
                    }
                } catch (err) {
                    console.error('[Monitoramento Avulso] Erro:', err);
                    alert('Erro ao realizar consulta: ' + err.message);
                } finally {
                    processandoContainer.classList.add('hidden');
                    btnConsultar.disabled = false;
                    if (btnText) btnText.classList.remove('hidden');
                    if (btnSpinner) btnSpinner.classList.add('hidden');
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
