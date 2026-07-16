/**
 * Acompanhamento (throttle) da importação de XML NF-e na própria view de detalhe/histórico.
 * Espelha o padrão do EFD (efd-importacao-progresso.js): barra real + cronômetro/shimmer
 * honestos (ProgressoAutomacao), e ao concluir/errar RECARREGA a página — o resultado
 * consolidado é renderizado pelo servidor a partir do banco.
 *
 * SSE: /app/importacao/xml/progresso/stream?tab_id=... (cache progresso:{user}:{tab}).
 * Sem tab_id (revisita pelo histórico durante o processamento) → re-check periódico.
 */
(function () {
    'use strict';

    function initXmlImportacaoProgresso() {
        const container = document.getElementById('xml-progresso-root');
        if (!container || container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        const tabId = container.dataset.tabId || '';

        const barra = document.getElementById('xml-progresso-bar');
        const porcentagem = document.getElementById('xml-progresso-percent');
        const etapaAtual = document.getElementById('xml-progresso-etapa');

        // Cronômetro + shimmer + microcopy via o módulo reutilizável (padrão de toda automação).
        const progAuto = window.ProgressoAutomacao
            ? window.ProgressoAutomacao.criar({
                bar: barra,
                tempoValor: document.getElementById('xml-progresso-tempo-valor'),
                dica: document.getElementById('xml-progresso-dica'),
                iniciadoEm: container.dataset.iniciadoEm,
            })
            : { iniciar: function () {}, parar: function () {}, trabalhando: function () {}, destruir: function () {} };

        const MAX_RECONEXOES = 3;
        const DELAY_RECONEXAO_BASE = 3000;

        let eventSource = null;
        let reconnectAttempts = 0;
        let reconnectTimer = null;
        let finalizado = false;

        let currentProgress = 0;
        let targetProgress = 0;
        let animFrameId = null;

        function animar() {
            if (currentProgress < targetProgress) {
                currentProgress = Math.min(currentProgress + 0.6, targetProgress);
            } else {
                currentProgress = targetProgress;
            }
            const pct = Math.round(currentProgress);
            if (barra) barra.style.width = pct + '%';
            if (porcentagem) porcentagem.textContent = pct + '%';
            animFrameId = currentProgress !== targetProgress ? requestAnimationFrame(animar) : null;
        }

        function aplicar(payload) {
            targetProgress = parseInt(payload.progresso) || 0;
            if (animFrameId === null) animFrameId = requestAnimationFrame(animar);

            const status = payload.status || 'processando';
            if (etapaAtual && payload.mensagem) etapaAtual.textContent = payload.mensagem;
            if (barra) barra.style.backgroundColor = (status === 'erro' || status === 'timeout') ? '#b91c1c' : '#1f2937';
        }

        function finalizar() {
            if (finalizado) return;
            finalizado = true;
            progAuto.parar();
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
            if (reconnectTimer) {
                clearTimeout(reconnectTimer);
                reconnectTimer = null;
            }
            window.location.reload();
        }

        function conectar() {
            if (eventSource) eventSource.close();

            const url = '/app/importacao/xml/progresso/stream?tab_id=' + encodeURIComponent(tabId);
            eventSource = new EventSource(url);

            eventSource.onopen = function () {
                reconnectAttempts = 0;
            };

            eventSource.onmessage = function (event) {
                try {
                    const payload = JSON.parse(event.data);
                    aplicar(payload);
                    if (['concluido', 'erro', 'timeout'].indexOf(payload.status) !== -1) {
                        finalizar();
                    }
                } catch (e) {
                    console.error('[XML progresso] erro ao parsear SSE', e);
                }
            };

            eventSource.onerror = function () {
                if (finalizado) return;

                const tentativa = reconnectAttempts;
                if (eventSource) {
                    eventSource.close();
                    eventSource = null;
                }

                if (tentativa < MAX_RECONEXOES) {
                    reconnectAttempts++;
                    reconnectTimer = setTimeout(function () {
                        reconnectTimer = null;
                        if (!finalizado) conectar();
                    }, DELAY_RECONEXAO_BASE * Math.pow(2, tentativa));
                } else {
                    progAuto.parar();
                    if (etapaAtual) {
                        etapaAtual.textContent = 'Conexão perdida. Atualize a página para ver o resultado.';
                    }
                    if (barra) {
                        barra.style.backgroundColor = '#b91c1c';
                    }
                }
            };
        }

        progAuto.iniciar();

        function onVisibilityChange() {
            if (document.hidden || finalizado) return;
            if (!eventSource || eventSource.readyState === EventSource.CLOSED) {
                reconnectAttempts = 0;
                conectar();
            }
        }

        if (tabId) {
            document.addEventListener('visibilitychange', onVisibilityChange);
            conectar();
        } else {
            // Revisita pelo histórico sem tab_id: não há stream ao vivo desta sessão.
            // Faz um re-check periódico — quando o servidor marcar concluído/erro, esta
            // mesma view deixa de renderizar o bloco de progresso e mostra o resultado.
            if (etapaAtual) etapaAtual.textContent = 'Processando no servidor…';
            reconnectTimer = setTimeout(function () { window.location.reload(); }, 8000);
        }

        window._cleanupFunctions = window._cleanupFunctions || {};
        window._cleanupFunctions.xmlImportacaoProgresso = function () {
            finalizado = true;
            document.removeEventListener('visibilitychange', onVisibilityChange);
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
            if (reconnectTimer) {
                clearTimeout(reconnectTimer);
                reconnectTimer = null;
            }
            if (animFrameId !== null) {
                cancelAnimationFrame(animFrameId);
                animFrameId = null;
            }
            progAuto.destruir();
            container.dataset.initialized = '0';
        };
    }

    window.initXmlImportacaoProgresso = initXmlImportacaoProgresso;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initXmlImportacaoProgresso, { once: true });
    } else {
        initXmlImportacaoProgresso();
    }
})();
