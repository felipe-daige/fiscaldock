<section class="relative bg-gradient-to-br from-[#0b1f3a] via-[#133a73] to-[#1e4fa0] text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                Pré-avaliação
            </div>
            <h1 class="font-extrabold leading-tight tracking-tight text-3xl sm:text-5xl">
                Questionário de Análise de Risco
            </h1>
            <p class="mt-4 text-white/80">
                Responda algumas perguntas rápidas para entendermos o cenário tributário da sua empresa.
            </p>
        </div>
    </div>
    <div class="pointer-events-none absolute inset-x-0 bottom-[-1px] leading-none">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="block w-full h-[80px] sm:h-[100px]">
            <path d="M0,64 C240,96 480,32 720,64 C960,96 1200,32 1440,64 L1440,120 L0,120 Z" fill="#f9fafb"></path>
        </svg>
    </div>
  </section>

<section class="bg-gray-50 py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Questionário</h2>
                <p class="text-gray-600">Selecione uma opção por etapa. Use os botões abaixo para avançar ou voltar.</p>
            </div>

            <!-- Steps Wrapper -->
            <div id="steps" class="space-y-10">
                <!-- Q1 -->
                <section class="step" data-step="1">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Q1 🏢 Qual é o ramo de atividade da sua empresa?</h2>
                    <ul class="options space-y-3" data-question="R1" role="group" aria-label="Ramo de atividade">
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="70" data-option-key="venda" aria-pressed="false">Venda</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="90" data-option-key="servico" aria-pressed="false">Serviço</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="40" data-option-key="industria" aria-pressed="false">Indústria</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="80" data-option-key="venda_servico" aria-pressed="false">Venda e Serviço</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="55" data-option-key="rural" aria-pressed="false">Rural</button></li>
                    </ul>
                </section>

                <!-- Q2 -->
                <section class="step hidden" data-step="2">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Q2 👥 Quantos funcionários a empresa possui?</h2>
                    <ul class="options space-y-3" data-question="R2" role="group" aria-label="Número de funcionários">
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="40" data-option-key="1-10" aria-pressed="false">Entre 1 e 10</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="50" data-option-key="10-30" aria-pressed="false">Entre 10 e 30</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="70" data-option-key="30-50" aria-pressed="false">Entre 30 e 50</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="80" data-option-key="50-80" aria-pressed="false">Entre 50 e 80</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="100" data-option-key="80+" aria-pressed="false">Acima de 80</button></li>
                    </ul>
                </section>

                <!-- Q3 -->
                <section class="step hidden" data-step="3">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Q3 💰 Qual é o faturamento anual da sua empresa?</h2>
                    <ul class="options space-y-3" data-question="R3" role="group" aria-label="Faturamento anual">
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="35" data-option-key="ate_300k" aria-pressed="false">Até R$ 300 mil por ano</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="60" data-option-key="300_500k" aria-pressed="false">Entre R$ 300 mil e R$ 500 mil por ano</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="75" data-option-key="500k_1m" aria-pressed="false">Entre R$ 500 mil e R$ 1 milhão por ano</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="80" data-option-key="1m_2m" aria-pressed="false">Entre R$ 1 milhão e R$ 2 milhões por ano</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="95" data-option-key="acima_2m" aria-pressed="false">Acima de R$ 2 milhões por ano</button></li>
                    </ul>
                </section>

                <!-- Q4 -->
                <section class="step hidden" data-step="4">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Q4 🧾 Qual é o regime de tributação atual da empresa?</h2>
                    <ul class="options space-y-3" data-question="Q1" role="group" aria-label="Regime de tributação">
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="10" data-option-key="mei" aria-pressed="false">MEI</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="40" data-option-key="simples" aria-pressed="false">Simples Nacional</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="80" data-option-key="lucro_presumido" aria-pressed="false">Lucro Presumido</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="80" data-option-key="lucro_real" aria-pressed="false">Lucro Real</button></li>
                    </ul>
                </section>

                <!-- Q5 -->
                <section class="step hidden" data-step="5">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Q5 🧭 Você conhece o regime tributário dos seus fornecedores?</h2>
                    <ul class="options space-y-3" data-question="Q2" role="group" aria-label="Regime dos fornecedores">
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="90" data-option-key="simples_mei" aria-pressed="false">A maioria é do Simples Nacional ou MEI</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="10" data-option-key="regime_normal" aria-pressed="false">Mais Lucro Real ou Lucro Presumido</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="90" data-option-key="pessoa_fisica" aria-pressed="false">Muitas compras de pessoas físicas</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="70" data-option-key="nao_sabe" aria-pressed="false">Não tenho conhecimento</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="90" data-option-key="sem_nota" aria-pressed="false">Não costumo pedir nota</button></li>
                    </ul>
                </section>

                <!-- Q6 -->
                <section class="step hidden" data-step="6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Q6 👥 Qual é o perfil de clientes da sua empresa?</h2>
                    <ul class="options space-y-3" data-question="Q3" role="group" aria-label="Perfil de clientes">
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="30" data-option-key="b2c" aria-pressed="false">Maioria B2C (vende para consumidor final CPF)</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="80" data-option-key="misto" aria-pressed="false">Misto (vendo para CNPJ e CPF)</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="100" data-option-key="b2b" aria-pressed="false">Maioria B2B (vendo mais para empresas CNPJ)</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="30" data-option-key="export" aria-pressed="false">Exportação</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="70" data-option-key="nao_sei" aria-pressed="false">Não sei responder</button></li>
                    </ul>
                </section>

                <!-- Q7 -->
                <section class="step hidden" data-step="7">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Q7 ⚙️ Quem cadastra os produtos domina CST, CFOP, NCM e CEST?</h2>
                    <ul class="options space-y-3" data-question="Q4" role="group" aria-label="Conhecimento fiscal no cadastro">
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="100" data-option-key="nao_entende" aria-pressed="false">Não entende nada</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="70" data-option-key="razoavel" aria-pressed="false">Entende razoavelmente</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="35" data-option-key="bom" aria-pressed="false">Tem bom conhecimento</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="10" data-option-key="domina" aria-pressed="false">Domina 100%</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-value="80" data-option-key="nao_sabe" aria-pressed="false">Não sei responder</button></li>
                    </ul>
                </section>

                <!-- Q8 -->
                <section class="step hidden" data-step="8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Q8 🧾 Como está o financeiro da empresa hoje?</h2>
                    <p class="text-gray-600 mb-6">Selecione a alternativa que melhor representa a sua realidade.</p>
                    <ul class="options space-y-3" data-question="Q5" role="group" aria-label="Maturidade financeira">
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-option-key="despesas_separadas" aria-pressed="false">Separo 100% as despesas pessoais das da empresa</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-option-key="pede_nota" aria-pressed="false">Peço nota fiscal de todas as compras e pagamentos</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-option-key="sistema_contas" aria-pressed="false">Tenho um sistema de contas a pagar e receber</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-option-key="relatorio_mensal" aria-pressed="false">Tenho relatório mensal sobre entradas e saídas</button></li>
                        <li><button class="option w-full px-4 py-3 rounded-lg border border-gray-300 text-left hover:bg-gray-50 hover:bg-blue-200" data-option-key="financeiro_basico" aria-pressed="false">Meu financeiro ainda é muito básico: misturo contas, não peço nota e não tenho relatórios</button></li>
                    </ul>
                </section>

                <!-- Resultado -->
                <section id="resultado" class="step hidden" data-step="9">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Resultado</h2>
                    <p class="text-gray-600 mb-6">Veja abaixo sua pontuação, classificação e recomendações.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="text-sm text-gray-500">Pontuação Total</div>
                            <div id="score-sum" class="text-3xl font-extrabold text-gray-900 mt-1">0</div>
                        </div>
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="text-sm text-gray-500">Percentual</div>
                            <div id="score-pct" class="text-3xl font-extrabold text-gray-900 mt-1">0%</div>
                        </div>
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="text-sm text-gray-500">Classificação</div>
                            <div class="flex items-center gap-3 mt-1">
                                <div id="score-level" class="text-3xl font-extrabold text-gray-900">—</div>
                                <div id="score-icon" class="text-2xl">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Explicação da Classificação -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">O que significa sua classificação?</h3>
                        <div id="explicacao-classificacao" class="bg-gray-50 rounded-xl p-6">
                            <!-- Conteúdo será preenchido pelo JavaScript -->
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Resumo das respostas</h3>
                        <ul id="answers-list" class="list-disc pl-5 space-y-2 text-gray-700"></ul>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Recomendações</h3>
                        <div id="recomendacoes" class="space-y-2 text-gray-700"></div>
                    </div>

                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Receber relatório no WhatsApp (opcional)</h3>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input id="whatsapp" type="text" placeholder="(00) 00000-0000" class="w-full sm:max-w-xs px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            <button id="reiniciar" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 font-semibold text-gray-700 hover:bg-gray-50">Reiniciar</button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Actions -->
            <div class="mt-10 flex items-center justify-between" id="actions-bar">
                <button class="btn-prev inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 font-semibold text-gray-700 hover:bg-gray-50" data-action="prev">Voltar</button>
                <button class="btn-next inline-flex items-center justify-center rounded-lg bg-blue-500 hover:bg-blue-600 text-white px-5 py-3 font-semibold disabled:opacity-50 disabled:cursor-not-allowed" data-action="next" disabled>Próximo</button>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('inicio') }}" data-link class="text-gray-600 hover:text-blue-600 font-medium">Voltar ao início</a>
            </div>
        </div>
    </div>
</section>

<script>
    (function () {
        const STORAGE_KEY = 'questionarioFiscal';
        const MAX_TOTAL = 655; // Soma dos maiores valores de Q1–Q7

        const stepsEl = document.getElementById('steps');
        const actionsBar = document.getElementById('actions-bar');
        const btnPrev = actionsBar.querySelector('.btn-prev');
        const btnNext = actionsBar.querySelector('.btn-next');

        const state = {
            step: 1,
            answers: { R1: null, R2: null, R3: null, Q1: null, Q2: null, Q3: null, Q4: null, Q5: null },
        };

        function saveState() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        }

        function loadState() {
            try {
                const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
                if (saved && typeof saved === 'object') {
                    if (saved.step) state.step = saved.step;
                    if (saved.answers) {
                        // Só copia respostas válidas (não null, undefined ou string vazia)
                        Object.entries(saved.answers).forEach(([key, val]) => {
                            if (val !== null && val !== undefined && val !== '') {
                                state.answers[key] = val;
                            }
                        });
                    }
                }
            } catch (_) {}
        }

        function getStepEl(step) {
            return stepsEl.querySelector('.step[data-step="' + step + '"]');
        }

        function showStep(step) {
            const all = stepsEl.querySelectorAll('.step');
            all.forEach(s => s.classList.add('hidden'));
            const current = getStepEl(step);
            if (current) {
                current.classList.remove('hidden');
            }

            // Limpar todas as seleções visuais ao trocar de etapa
            if (step !== 9) { // não limpar na tela de resultado
                const currentStepEl = getStepEl(step);
                if (currentStepEl) {
                    currentStepEl.querySelectorAll('.option').forEach(o => {
                        o.classList.remove('border-blue-400', 'bg-blue-100', 'text-blue-800', 'hover:bg-blue-200');
                        o.classList.add('hover:bg-gray-50');
                        o.setAttribute('aria-pressed', 'false');
                    });
                }
            }

            // Controle de botões
            btnPrev.disabled = step === 1;
            if (step === 9) {
                actionsBar.classList.add('hidden');
            } else {
                actionsBar.classList.remove('hidden');
                btnNext.disabled = !hasSelection(step);
            }
        }

        function hasSelection(step) {
            const el = getStepEl(step);
            if (!el) return false;
            const selected = el.querySelector('.option[aria-pressed="true"]');
            return !!selected;
        }

        function selectOption(button) {
            const container = button.closest('.options');
            const questionKey = container.getAttribute('data-question');

            // Desmarcar anteriores
            container.querySelectorAll('.option').forEach(o => {
                // limpar estilos de seleção anteriores (azul escuro e indigo)
                o.classList.remove('border-blue-500', 'border-blue-600', 'bg-blue-500', 'bg-blue-50', 'hover:bg-blue-600');
                o.classList.remove('border-indigo-600', 'bg-indigo-500', 'hover:bg-indigo-600');
                // limpar estilos de azul claro atual
                o.classList.remove('border-blue-400', 'bg-blue-100', 'hover:bg-blue-200', 'text-blue-800');
                o.classList.remove('text-white');
                // restaurar hover padrão
                o.classList.add('hover:bg-gray-50');
                o.setAttribute('aria-pressed', 'false');
            });

            // Marcar atual
            button.classList.remove('hover:bg-gray-50'); // remover hover padrão
            button.classList.add('border-blue-400', 'bg-blue-100', 'text-blue-800', 'hover:bg-blue-200');
            button.setAttribute('aria-pressed', 'true');

            // Persistir resposta
            if (questionKey) {
                const valueAttr = button.getAttribute('data-value');
                state.answers[questionKey] = valueAttr ? Number(valueAttr) : button.getAttribute('data-option-key');
                saveState();
            }

            // Habilitar Próximo
            btnNext.disabled = false;
        }

        function computeScore() {
            const { R1, R2, R3, Q1, Q2, Q3, Q4 } = state.answers;
            const values = [R1, R2, R3, Q1, Q2, Q3, Q4].map(v => Number(v || 0));
            const sum = values.reduce((a, b) => a + b, 0);
            const pct = Math.round((sum / MAX_TOTAL) * 100);
            const level = pct <= 40 ? 'Baixo' : pct <= 70 ? 'Médio' : 'Alto';
            return { sum, pct, level };
        }

        function buildSummary() {
            const answersList = document.getElementById('answers-list');
            const recap = [];

            function addRecap(step, questionSelector) {
                const el = getStepEl(step);
                const selected = el ? el.querySelector('.option[aria-pressed="true"]') : null;
                if (selected) {
                    const text = selected.textContent.trim();
                    recap.push(text);
                }
            }

            addRecap(1, 'R1');
            addRecap(2, 'R2');
            addRecap(3, 'R3');
            addRecap(4, 'Q1');
            addRecap(5, 'Q2');
            addRecap(6, 'Q3');
            addRecap(7, 'Q4');
            addRecap(8, 'Q5');

            answersList.innerHTML = '';
            recap.forEach((t, i) => {
                const li = document.createElement('li');
                li.textContent = 'Q' + (i + 1) + ': ' + t;
                answersList.appendChild(li);
            });
        }

        function buildRecomendacoes() {
            const recEl = document.getElementById('recomendacoes');
            const { level } = computeScore();
            const financeiro = state.answers.Q5;
            const recs = [];

            if (level === 'Baixo') {
                recs.push('Manter práticas atuais e realizar auditoria tributária anual.');
            } else if (level === 'Médio') {
                recs.push('Revisar parametrizações fiscais (CST/CFOP/NCM).');
                recs.push('Mapear regimes dos fornecedores e reforçar documentação.');
            } else {
                recs.push('Realizar revisão tributária completa e ajustar processos.');
                recs.push('Implementar controles e governança fiscal/financeira.');
            }

            if (financeiro === 'financeiro_basico') {
                recs.push('Implantar imediatamente separação de despesas, exigência de notas e relatórios mensais.');
            }

            recEl.innerHTML = '';
            recs.forEach(r => {
                const p = document.createElement('p');
                p.textContent = '• ' + r;
                recEl.appendChild(p);
            });
        }

        function buildExplicacaoClassificacao() {
            const explicacaoEl = document.getElementById('explicacao-classificacao');
            const { level, pct } = computeScore();
            
            let explicacao = '';
            let cor = '';
            let icone = '';
            
            if (level === 'Baixo') {
                cor = 'text-green-600';
                icone = '🟢';
                explicacao = `
                    <div class="flex items-start gap-4">
                        <div class="text-4xl">🟢</div>
                        <div>
                            <h4 class="text-xl font-bold text-green-600 mb-2">Risco Tributário BAIXO</h4>
                            <p class="text-gray-700 mb-3">Sua empresa apresenta um cenário tributário relativamente controlado, com ${pct}% de pontuação de risco.</p>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h5 class="font-semibold text-green-800 mb-2">O que isso significa:</h5>
                                <ul class="text-sm text-green-700 space-y-1">
                                    <li>• Processos fiscais bem estruturados</li>
                                    <li>• Baixa probabilidade de autuações</li>
                                    <li>• Conformidade tributária adequada</li>
                                    <li>• Necessidade de manutenção preventiva</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
            } else if (level === 'Médio') {
                cor = 'text-yellow-600';
                icone = '🟡';
                explicacao = `
                    <div class="flex items-start gap-4">
                        <div class="text-4xl">🟡</div>
                        <div>
                            <h4 class="text-xl font-bold text-yellow-600 mb-2">Risco Tributário MÉDIO</h4>
                            <p class="text-gray-700 mb-3">Sua empresa apresenta um cenário tributário que requer atenção, com ${pct}% de pontuação de risco.</p>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <h5 class="font-semibold text-yellow-800 mb-2">O que isso significa:</h5>
                                <ul class="text-sm text-yellow-700 space-y-1">
                                    <li>• Alguns pontos de atenção fiscal identificados</li>
                                    <li>• Necessidade de ajustes em processos</li>
                                    <li>• Risco moderado de autuações</li>
                                    <li>• Oportunidade de melhoria na conformidade</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                cor = 'text-red-600';
                icone = '🔴';
                explicacao = `
                    <div class="flex items-start gap-4">
                        <div class="text-4xl">🔴</div>
                        <div>
                            <h4 class="text-xl font-bold text-red-600 mb-2">Risco Tributário ALTO</h4>
                            <p class="text-gray-700 mb-3">Sua empresa apresenta um cenário tributário que requer ação imediata, com ${pct}% de pontuação de risco.</p>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <h5 class="font-semibold text-red-800 mb-2">O que isso significa:</h5>
                                <ul class="text-sm text-red-700 space-y-1">
                                    <li>• Múltiplos pontos críticos identificados</li>
                                    <li>• Alto risco de autuações fiscais</li>
                                    <li>• Necessidade urgente de revisão completa</li>
                                    <li>• Implementação imediata de controles</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            explicacaoEl.innerHTML = explicacao;
        }

        function goNext() {
            if (!hasSelection(state.step)) return;
            if (state.step < 8) {
                state.step += 1;
                saveState();
                showStep(state.step);
                // Reaplicar seleção visual se houver resposta salva para esta etapa
                const currentStepEl = getStepEl(state.step);
                if (currentStepEl) {
                    const questionKey = currentStepEl.querySelector('.options')?.getAttribute('data-question');
                    if (questionKey && state.answers[questionKey] !== null && state.answers[questionKey] !== undefined) {
                        const isNumeric = typeof state.answers[questionKey] === 'number';
                        const selector = isNumeric
                            ? '.option[data-value="' + state.answers[questionKey] + '"]'
                            : '.option[data-option-key="' + state.answers[questionKey] + '"]';
                        const btn = currentStepEl.querySelector(selector);
                        if (btn) {
                            btn.classList.remove('hover:bg-gray-50');
                            btn.classList.add('border-blue-400', 'bg-blue-100', 'text-blue-800', 'hover:bg-blue-200');
                            btn.setAttribute('aria-pressed', 'true');
                        }
                    }
                }
                return;
            }
            if (state.step === 8) {
                // Após Q8, mostrar resultado (step 9)
                state.step = 9;
                saveState();
                const { sum, pct, level } = computeScore();
                document.getElementById('score-sum').textContent = String(sum);
                document.getElementById('score-pct').textContent = pct + '%';
                document.getElementById('score-level').textContent = level;
                
                // Adicionar cores e ícone baseado na classificação
                const levelEl = document.getElementById('score-level');
                const iconEl = document.getElementById('score-icon');
                levelEl.className = 'text-3xl font-extrabold';
                iconEl.className = 'text-2xl';
                
                if (level === 'Baixo') {
                    levelEl.classList.add('text-green-600');
                    iconEl.textContent = '🟢';
                } else if (level === 'Médio') {
                    levelEl.classList.add('text-yellow-600');
                    iconEl.textContent = '🟡';
                } else {
                    levelEl.classList.add('text-red-600');
                    iconEl.textContent = '🔴';
                }
                buildSummary();
                buildRecomendacoes();
                buildExplicacaoClassificacao();
                showStep(9);
                // Aplicar máscara ao WhatsApp
                if (window.jQuery && typeof jQuery.fn.mask === 'function') {
                    jQuery('#whatsapp').mask('(00) 00000-0000');
                }
            }
        }

        function goPrev() {
            if (state.step > 1 && state.step <= 8) {
                state.step -= 1;
                saveState();
                showStep(state.step);
            } else if (state.step === 9) {
                // Voltar para Q8 a partir do resultado
                state.step = 8;
                saveState();
                showStep(state.step);
            }
        }

        function reiniciar() {
            localStorage.removeItem(STORAGE_KEY);
            state.step = 1;
            Object.keys(state.answers).forEach(k => state.answers[k] = null);
            // Limpar seleções visuais
            stepsEl.querySelectorAll('.option').forEach(o => {
                o.classList.remove('border-blue-500', 'bg-blue-50');
                o.setAttribute('aria-pressed', 'false');
            });
            showStep(state.step);
            saveState();
        }

        // Event delegation para opções (resposta imediata no pointerdown)
        let lastSelectAt = 0;
        function handleSelectImmediate(btn) {
            selectOption(btn);
            lastSelectAt = Date.now();
        }

        stepsEl.addEventListener('pointerdown', function (e) {
            const btn = e.target.closest('.option');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            handleSelectImmediate(btn);
        });

        // Fallback para click (desktop), evita dupla seleção após pointerdown
        stepsEl.addEventListener('click', function (e) {
            const btn = e.target.closest('.option');
            if (!btn) return;
            if (Date.now() - lastSelectAt < 150) return;
            handleSelectImmediate(btn);
        });

        // Acessibilidade via teclado (Enter/Espaço)
        stepsEl.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            const btn = e.target.closest('.option');
            if (!btn) return;
            e.preventDefault();
            handleSelectImmediate(btn);
        });

        // Navegação
        btnNext.addEventListener('click', function () { goNext(); });
        btnPrev.addEventListener('click', function () { goPrev(); });

        // Reiniciar
        document.addEventListener('click', function (e) {
            const r = e.target.closest('#reiniciar');
            if (r) reiniciar();
        });

        // Inicialização
        loadState();

        // Reaplicar seleções salvas
        Object.entries(state.answers).forEach(([key, val]) => {
            if (val === null || val === undefined || val === '') return;
            const isNumeric = typeof val === 'number';
            const selector = isNumeric
                ? '.options[data-question="' + key + '"] .option[data-value="' + val + '"]'
                : '.options[data-question="' + key + '"] .option[data-option-key="' + val + '"]';
            const btn = stepsEl.querySelector(selector);
            if (btn) {
                btn.classList.remove('hover:bg-gray-50'); // remover hover padrão
                btn.classList.add('border-blue-400', 'bg-blue-100', 'text-blue-800', 'hover:bg-blue-200');
                btn.setAttribute('aria-pressed', 'true');
            }
        });

        // Mostrar step salvo ou 1
        if (state.step === 9) {
            const { sum, pct, level } = computeScore();
            document.getElementById('score-sum').textContent = String(sum);
            document.getElementById('score-pct').textContent = pct + '%';
            document.getElementById('score-level').textContent = level;
            
            // Adicionar cores e ícone baseado na classificação
            const levelEl = document.getElementById('score-level');
            const iconEl = document.getElementById('score-icon');
            levelEl.className = 'text-3xl font-extrabold';
            iconEl.className = 'text-2xl';
            
            if (level === 'Baixo') {
                levelEl.classList.add('text-green-600');
                iconEl.textContent = '🟢';
            } else if (level === 'Médio') {
                levelEl.classList.add('text-yellow-600');
                iconEl.textContent = '🟡';
            } else {
                levelEl.classList.add('text-red-600');
                iconEl.textContent = '🔴';
            }
            buildSummary();
            buildRecomendacoes();
            buildExplicacaoClassificacao();
        }

        showStep(state.step);
    })();
</script>


