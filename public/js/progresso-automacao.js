/**
 * ProgressoAutomacao — primitivas HONESTAS e reutilizáveis de progresso para qualquer
 * automação assíncrona (Consulta CNPJ, Clearance DF-e, Importação EFD, etc).
 *
 * NÃO inventa progresso: a barra de % continua refletindo o valor real de cada superfície.
 * O que este módulo dá, de forma uniforme, é a sensação de "vivo" durante esperas longas
 * (fontes oficiais/gov lentas, SPED de minutos):
 *   - Cronômetro de tempo decorrido (base no início real → correto após reload; congela no fim).
 *   - Shimmer "trabalhando" na barra (só o brilho; a largura é o % real; respeita reduced-motion).
 *   - Microcopy de expectativa (mostra/esconde o elemento; o TEXTO é de cada superfície).
 *
 * Padrão do projeto: toda tela de automação com barra de progresso deve usar este módulo.
 * Ver memory `project_padrao_progresso_automacao`.
 *
 * Uso:
 *   var p = ProgressoAutomacao.criar({ bar, tempoValor, dica, iniciadoEm });
 *   p.iniciar();                 // ao começar/abrir o stream (processando)
 *   p.trabalhando(true|false);   // a cada tick de progresso (opcional, espelha iniciar/parar)
 *   p.parar();                   // ao concluir/erro: congela o tempo, tira shimmer e dica
 *   p.destruir();                // no cleanup do SPA (limpa o interval)
 */
(function () {
    'use strict';

    var STYLE_ID = 'progresso-automacao-style';

    function ensureStyle() {
        if (document.getElementById(STYLE_ID)) return;
        var css =
            '@keyframes progressoAutomacaoShimmer{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}' +
            '.progresso-automacao-shimmer{position:relative;overflow:hidden}' +
            '.progresso-automacao-shimmer::after{content:"";position:absolute;inset:0;' +
            'background:linear-gradient(90deg,transparent,rgba(255,255,255,0.5),transparent);' +
            'animation:progressoAutomacaoShimmer 1.4s ease-in-out infinite}' +
            '@media (prefers-reduced-motion: reduce){.progresso-automacao-shimmer::after{animation:none}}';
        var el = document.createElement('style');
        el.id = STYLE_ID;
        el.textContent = css;
        document.head.appendChild(el);
    }

    /** Segundos → "MM:SS" (ou "H:MM:SS" acima de 1h). Negativos viram 00:00. */
    function formatarTempo(totalSegundos) {
        var s = Math.max(0, Math.floor(totalSegundos));
        var h = Math.floor(s / 3600);
        var m = Math.floor((s % 3600) / 60);
        var seg = s % 60;
        var pad = function (n) { return n < 10 ? '0' + n : String(n); };
        return h > 0 ? h + ':' + pad(m) + ':' + pad(seg) : pad(m) + ':' + pad(seg);
    }

    function resolveEl(ref) {
        if (!ref) return null;
        return typeof ref === 'string' ? document.getElementById(ref) : ref;
    }

    function criar(opts) {
        opts = opts || {};
        var bar = resolveEl(opts.bar);
        var tempoValor = resolveEl(opts.tempoValor);
        var dica = resolveEl(opts.dica);
        var iniciadoEm = parseInt(opts.iniciadoEm, 10); // epoch em segundos (servidor) ou NaN

        var st = { handle: null, baseMs: null };

        function renderTempo() {
            if (!tempoValor || st.baseMs === null) return;
            tempoValor.textContent = formatarTempo((Date.now() - st.baseMs) / 1000);
        }

        function setShimmer(ativo) {
            if (bar) bar.classList.toggle('progresso-automacao-shimmer', !!ativo);
        }

        function setDica(visivel) {
            if (dica) dica.classList.toggle('hidden', !visivel);
        }

        return {
            iniciar: function () {
                ensureStyle();
                setShimmer(true);
                setDica(true);
                if (tempoValor && !st.handle) {
                    st.baseMs = (Number.isFinite(iniciadoEm) && iniciadoEm > 0)
                        ? iniciadoEm * 1000
                        : Date.now();
                    renderTempo();
                    st.handle = window.setInterval(renderTempo, 1000);
                }
            },
            trabalhando: function (ativo) {
                setShimmer(ativo);
                setDica(ativo);
            },
            parar: function () {
                if (st.handle) {
                    clearInterval(st.handle);
                    st.handle = null;
                    renderTempo(); // congela no último valor (não zera)
                }
                setShimmer(false);
                setDica(false);
            },
            destruir: function () {
                if (st.handle) {
                    clearInterval(st.handle);
                    st.handle = null;
                }
            }
        };
    }

    window.ProgressoAutomacao = { criar: criar, formatarTempo: formatarTempo };
})();
