import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const app = document.getElementById('app');
    if (!app) return;

    const _SPA_STATE_KEY = '__fiscaldockSpa';
    const _HISTORY_CACHE_LIMIT = 24;
    const _historyCache = new Map();
    const _newHistoryKey = () => `spa-${Date.now()}-${Math.random().toString(36).slice(2)}`;
    const _browserUrl = (url = window.location.href) => {
        const parsed = new URL(url, window.location.origin);
        return `${parsed.pathname}${parsed.search}${parsed.hash}`;
    };
    const _spaState = (state = history.state) => (
        state && typeof state === 'object' && state[_SPA_STATE_KEY]
            ? state[_SPA_STATE_KEY]
            : null
    );
    const _stateWithSpa = (key, url) => ({
        ...(history.state && typeof history.state === 'object' ? history.state : {}),
        [_SPA_STATE_KEY]: { key, url },
    });
    const _storeHistoryEntry = (key, html, url, scrollY = 0) => {
        if (!key || typeof html !== 'string') return;

        _historyCache.delete(key);
        _historyCache.set(key, { html, url, scrollY: Math.max(0, Number(scrollY) || 0) });

        while (_historyCache.size > _HISTORY_CACHE_LIMIT) {
            _historyCache.delete(_historyCache.keys().next().value);
        }
    };

    // O HTML inicial precisa ser capturado antes de qualquer processamento dos scripts.
    // Ele vira o snapshot restaurável da primeira entrada do histórico desta aba.
    const _initialHistoryMeta = _spaState();
    let _renderedHistoryKey = _initialHistoryMeta?.key || _newHistoryKey();
    if (!_initialHistoryMeta?.key) {
        history.replaceState(_stateWithSpa(_renderedHistoryKey, _browserUrl()), '', window.location.href);
    }
    _storeHistoryEntry(_renderedHistoryKey, app.innerHTML, _browserUrl(), window.scrollY);

    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    function salvarEntradaRenderizada() {
        const entry = _historyCache.get(_renderedHistoryKey);
        if (entry) {
            entry.scrollY = Math.max(0, Number(window.scrollY) || 0);
        }
    }

    // Sistema de gerenciamento de recursos globais
    window._spaResources = {
        intervals: [],
        swipers: [],
        listeners: []
    };

    // Registro de scripts externos já carregados, por PATH normalizado (sem querystring).
    // Evita double-load quando a view versiona o src com ?v=filemtime (cache-busting): sem
    // isso o dedup por src exato tratava `/js/bi.js?v=1` e `/js/bi.js` como scripts distintos,
    // e o tag do load inicial (fora do <head>) não era encontrado na re-navegação → recarga +
    // listeners/gráficos duplicados. Semeado no boot com todo <script src> já presente.
    const _scriptsCarregados = new Set();
    // Scripts de view são carregados uma única vez, mas precisam reinicializar seus
    // bindings quando o HTML volta ao #app. A chave é o PATH normalizado do src.
    const _spaScriptInitializers = {
        '/js/dashboard.js': 'initDashboard',
        '/js/bi.js': 'initBi',
        '/js/equipe.js': 'initEquipe',
        '/js/risk-score.js': 'initRiskScore',
        '/js/clearance.js': 'initValidacao',
        '/js/clearance-resultado.js': 'initClearanceResultado',
        '/js/clearance-notas.js': 'initClearanceNotas',
        '/js/clearance-buscar.js': 'initClearanceBuscar',
        '/js/consulta-lote.js': 'initConsultaLote',
        '/js/consulta-lote-detalhe.js': 'initConsultaLoteDetalhe',
        '/js/recarga.js': 'initRecarga',
        '/js/assinatura.js': 'initPlanos',
        '/js/efd-importacao-progresso.js': 'initEfdImportacaoProgresso',
        '/js/xml-importacao-progresso.js': 'initXmlImportacaoProgresso',
    };
    const _normalizarScriptSrc = (src) => {
        try {
            return new URL(src, window.location.origin).pathname;
        } catch (e) {
            return String(src).split('?')[0];
        }
    };
    document.querySelectorAll('script[src]').forEach((s) => {
        _scriptsCarregados.add(_normalizarScriptSrc(s.getAttribute('src')));
    });

    // Funcao para atualizar CSRF token apos navegacao SPA
    async function atualizarCsrfToken() {
        try {
            const response = await fetch('/api/csrf-token', {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (response.ok) {
                const data = await response.json();
                if (data.csrf_token) {
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta) {
                        meta.setAttribute('content', data.csrf_token);
                        console.log('[SPA] CSRF token atualizado');
                    }
                }
            }
        } catch (e) {
            console.error('[SPA] Erro ao atualizar CSRF token:', e);
        }
    }

    // Configuração de mapeamento para páginas com nomes de arquivo diferentes
    const _spaScriptOverrides = {
        monitoramento: null, // Código inline na view
        importacaoEfd: null, // Código inline na view
        importacao: null, // Código inline na view (importação XML)
        monitoramentoAvulso: null, // Código inline na view
        consultasAvulso: null, // Código inline na view (mesma view do monitoramentoAvulso)
        monitoramentoHistorico: null, // Código inline na view
        monitoramentoParticipante: null, // Código inline na view
        consultas: '/js/consulta-lote.js',
        consultaLote: '/js/consulta-lote.js',
        bi: null, // Script carregado como tag externa na view — nao tentar recarregar no SPA
        notas: null, // Código inline na view
        alertas: null, // Código inline na view
        clearance: null, // Código inline/script externo por view de clearance
        cliente: null, // Código inline na view
        clientes: null, // Código inline na view
        perfil: '/js/perfil.js',
        configuracoes: null, // Código inline na view (sem isso o SPA busca /js/configuracoes.js → 404)
        arquivos: null, // Página usa formulários/downloads nativos; não há bundle JS dedicado
    };

    // Converte slug (com hífen/underscore) para camelCase
    function slugToCamel(slug) {
        return slug
            .split(/[-_]+/)
            .filter(Boolean)
            .map((parte, index) => index === 0 
                ? parte.toLowerCase() 
                : parte.charAt(0).toUpperCase() + parte.slice(1).toLowerCase()
            )
            .join('');
    }

    // Resolve nome da página, função init e caminho do script a partir da URL
    function obterInfoPagina(caminho) {
        const segmentos = caminho.split('/').filter(Boolean);

        let baseSlug = 'inicio';
        if (segmentos.length > 0) {
            if (segmentos[0] === 'app') {
                // Rotas autenticadas
                if (segmentos[1] === 'participante') {
                    // Rota de participante específico: /app/participante/{id}
                    baseSlug = 'monitoramentoParticipante';
                } else {
                    baseSlug = segmentos[1] || 'dashboard';
                }
            } else {
                baseSlug = segmentos[0];
            }
        }

        const paginaCamel = slugToCamel(baseSlug);
        const nomePagina = paginaCamel || 'inicio';
        const nomeFuncao = nomePagina === 'cliente'
            ? null
            : `init${nomePagina.charAt(0).toUpperCase() + nomePagina.slice(1)}`;
        const autenticada = segmentos[0] === 'app';
        const scriptPath = Object.prototype.hasOwnProperty.call(_spaScriptOverrides, nomePagina)
            ? _spaScriptOverrides[nomePagina]
            : (autenticada ? null : `/js/${nomePagina}.js`);

        return { nomePagina, scriptPath, nomeFuncao };
    }

    // Mapeamento específico para views
    const funcoesEspecificas = {
        '/': 'initInicio',
        '/inicio': 'initInicio',
        '/login': 'initLogin',
        '/criar-conta': 'initCriarConta',
        '/agendar': 'initAgendar',
        '/solucoes': 'initSolucoes',
        '/app/importacao/efd': 'initImportacaoEfd',
        '/app/consultas/avulso': 'initMonitoramentoAvulso',
        '/app/consulta/avulso': 'initMonitoramentoAvulso',
        '/app/novo-participante': 'initNovoParticipante',
        '/app/novo-cliente': 'initNovoCliente',
        '/app/monitoramento/historico': 'initMonitoramentoHistorico',
        // Nota: /app/participante/{id} é tratada dinamicamente em obterInfoPagina()
        '/app/importacao/xml': 'initMonitoramentoXml',
        '/app/consultas/nova': 'initConsultaLote',
        '/app/consulta/nova': 'initConsultaLote',
        '/app/perfil': 'initPerfil',
        '/app/bi/dashboard': 'initBi',
        '/app/dashboard': 'initDashboard',
        '/app/notas': null, // IIFE inline na view, sem init function
        '/app/notas/dashboard': null, // IIFE inline na view
        '/app/alertas': null, // IIFE inline na view
        '/app/alertas/historico': null, // página estática (filtros via GET form)
        '/app/clearance/dashboard': null, // Clearance dashboard — IIFE inline
        '/app/clearance/notas': 'initClearanceNotas', // Clearance notas — reinicializa via initClearanceNotas no SPA
        '/app/clearance/buscar': 'initClearanceBuscar', // Clearance busca avulsa — reinicializa via initClearanceBuscar no SPA
    };
    
    // 0. LIMPAR RECURSOS ANTES DE NAVEGAR
    function limparRecursos() {
        // Resetar flags de inicialização ANTES de limpar recursos
        // Isso garante que as páginas possam ser reinicializadas após navegação
        
        // Resetar layout
        if (window.resetLayout && typeof window.resetLayout === 'function') {
            try {
                window.resetLayout();
            } catch (error) {
                console.error('Erro ao resetar layout:', error);
            }
        }
        
        // Resetar flags globais de inicialização se existirem
        if (typeof window._layoutInitialized !== 'undefined') {
            window._layoutInitialized = false;
        }
        if (typeof window._inicioInitialized !== 'undefined') {
            window._inicioInitialized = false;
        }
        if (typeof window._precosInitialized !== 'undefined') {
            window._precosInitialized = false;
        }
        if (typeof window._solucoesInitialized !== 'undefined') {
            window._solucoesInitialized = false;
        }
        if (typeof window._duvidasInitialized !== 'undefined') {
            window._duvidasInitialized = false;
        }
        if (typeof window._impactosInitialized !== 'undefined') {
            window._impactosInitialized = false;
        }
        if (typeof window._consultaLoteLastInit !== 'undefined') {
            window._consultaLoteLastInit = 0;
        }
        if (typeof window._consultaLoteModuleLoaded !== 'undefined') {
            window._consultaLoteModuleLoaded = false;
        }

        // Limpar todos os intervalos
        window._spaResources.intervals.forEach(intervalId => {
            clearInterval(intervalId);
        });
        window._spaResources.intervals = [];
        
        // Destruir todas as instâncias Swiper
        window._spaResources.swipers.forEach(swiper => {
            try {
                if (swiper && typeof swiper.destroy === 'function') {
                    swiper.destroy(true, true);
                }
            } catch (error) {
                // Ignorar erros ao destruir Swiper
            }
        });
        window._spaResources.swipers = [];
        
        // Remover listeners específicos (se necessário)
        window._spaResources.listeners.forEach(({ element, event, handler }) => {
            try {
                if (element && handler) {
                    element.removeEventListener(event, handler);
                }
            } catch (error) {
                // Ignorar erros ao remover listeners
            }
        });
        window._spaResources.listeners = [];
        
        // Limpar recursos de funções init específicas se existirem
        if (window._cleanupFunctions) {
            Object.values(window._cleanupFunctions).forEach(cleanup => {
                try {
                    if (typeof cleanup === 'function') {
                        cleanup();
                    }
                } catch (error) {
                    // Ignorar erros de cleanup
                }
            });
            window._cleanupFunctions = {};
        }
        
        // Destruir instâncias ApexCharts e resetar estado do módulo BI
        try {
            if (typeof window.cleanupBi === 'function') {
                window.cleanupBi();
            }
        } catch (error) {
            // Ignorar erro se cleanupBi não estiver definida (BI não foi carregado)
        }

        // Limpar alerta de erro inline ao navegar entre páginas
        try {
            if (typeof window.hideErrorAlert === 'function') {
                window.hideErrorAlert();
            } else {
                const errBox = document.getElementById('error-alert-container');
                if (errBox) errBox.innerHTML = '';
            }
        } catch (error) {
            // Ignorar
        }
    }
    
    // 1. INTERCEPTAR CLIQUES EM LINKS
    document.body.addEventListener('click', async (e) => {
        const link = e.target.closest('[data-link]');
        if (link) {
            const linkUrl = new URL(link.href, window.location.origin);

            // Paginação dentro de [data-spa-list] é tratada pelo navegarLista (swap parcial
            // do container), não pelo swap completo do #app. Deixar o handler dedicado pegar.
            if (linkUrl.searchParams.has('page') && link.closest('[data-spa-list]')) {
                return;
            }

            // Ignorar navegação SPA para rotas que NÃO começam com /app/
            const linkPath = linkUrl.pathname;
            if (!linkPath.startsWith('/app/')) {
                return; // Deixar o browser fazer navegação normal (full page reload)
            }

            e.preventDefault(); // Não recarregar página
            e.stopPropagation(); // Evitar propagação
            console.log('[SPA] Link clicado:', link.href, 'Target:', e.target);
            try {
                await navegar(link.href); // Navegar via JavaScript
            } catch (error) {
                console.error('[SPA] Erro ao navegar:', error);
                // Fallback: recarregar página completa
                window.location.href = link.href;
            }
        }
    });

    // 1.0.1. INTERCEPTAR LINKS DE PAGINAÇÃO DENTRO DE [data-spa-list]
    document.body.addEventListener('click', (e) => {
        const link = e.target.closest('[data-spa-list] a[href]');
        if (!link) return;

        const url = new URL(link.href, window.location.origin);

        // Só links de paginação (têm ?page=) de rotas autenticadas
        if (!url.searchParams.has('page')) return;
        if (!url.pathname.startsWith('/app/')) return;

        const container = link.closest('[data-spa-list]');
        if (!container || !container.id) return;

        e.preventDefault();
        e.stopPropagation();
        navegarLista(link, container).catch((error) => {
            console.error('[SPA] Falha em navegarLista:', error);
            window.location.href = link.href;
        });
    });

    // 1.1. INTERCEPTAR FORMULÁRIO DE LOGOUT
    document.body.addEventListener('submit', async (e) => {
        const form = e.target;
        if (form && (form.id === 'logout-form' || form.id === 'logout-form-header' || form.id === 'logout-form-mobile')) {
            e.preventDefault(); // Não recarregar página
            
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': formData.get('_token')
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success && data.redirect) {
                    // Limpar recursos antes de navegar
                    limparRecursos();
                    // Recarregar página completa para trocar o header (layout muda entre autenticado/não autenticado)
                    window.location.href = data.redirect;
                } else {
                    // Fallback: recarregar página se não for JSON
                    window.location.href = data.redirect || '/inicio';
                }
            } catch (error) {
                console.error('Erro ao fazer logout:', error);
                // Fallback: recarregar página em caso de erro
                window.location.href = '/inicio';
            }
        }
    });
    
    // 1.2. PREFETCH + CACHE DE PÁGINAS (esconde o ping BR→servidor)
    // Guarda o HTML de views /app/* buscadas no hover. TTL curto porque views
    // podem embutir dados; dashboards buscam via AJAX próprio, então o shell é seguro.
    const _prefetchCache = new Map();       // urlAbs -> { html, ts }
    const _prefetchInFlight = new Map();    // urlAbs -> Promise (dedup)
    const _PREFETCH_TTL = 20000;            // 20s

    function _cacheGet(urlAbs) {
        const hit = _prefetchCache.get(urlAbs);
        if (!hit) return null;
        if (Date.now() - hit.ts > _PREFETCH_TTL) {
            _prefetchCache.delete(urlAbs);
            return null;
        }
        return hit.html;
    }

    async function prefetch(href) {
        let abs;
        try {
            abs = new URL(href, window.location.origin);
        } catch (e) {
            return;
        }
        if (!abs.pathname.startsWith('/app/')) return;
        const key = abs.toString();
        if (_cacheGet(key) || _prefetchInFlight.has(key)) return;

        const p = (async () => {
            try {
                const resposta = await fetch(key, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                    },
                    credentials: 'same-origin'
                });
                if (!resposta.ok) return;
                const ct = resposta.headers.get('content-type') || '';
                if (ct.includes('application/json')) return; // redirect/erro — não cachear
                const html = await resposta.text();
                _prefetchCache.set(key, { html, ts: Date.now() });
            } catch (e) {
                // best-effort — silencioso
            } finally {
                _prefetchInFlight.delete(key);
            }
        })();
        _prefetchInFlight.set(key, p);
    }

    // Dispara prefetch no hover/foco do link. mouseover borbulha (mouseenter não);
    // prefetch é idempotente (cache + inFlight), então a repetição é barata.
    function _talvezPrefetch(e) {
        const alvo = e.target;
        if (!alvo || !alvo.closest) return;
        const link = alvo.closest('[data-link]');
        if (!link || !link.href) return;
        const url = new URL(link.href, window.location.origin);
        if (!url.pathname.startsWith('/app/')) return;
        // Paginação de lista usa swap parcial (navegarLista) — não cachear a página inteira
        if (url.searchParams.has('page') && link.closest('[data-spa-list]')) return;
        prefetch(link.href);
    }
    document.body.addEventListener('mouseover', _talvezPrefetch);
    document.body.addEventListener('focusin', _talvezPrefetch);

    // 2. FUNÇÃO PRINCIPAL DE NAVEGAÇÃO
    // Token de sequência: com ping alto (BR→servidor), o usuário clica um 2º link
    // enquanto o 1º ainda busca. Sem guard, as duas navegações renderizam por cima
    // uma da outra (DOM/URL/scripts errados). Cada navegar() captura seu token; ao
    // voltar de um await, se o token mudou, uma navegação mais nova assumiu → aborta.
    let _navToken = 0;
    // Caminho do que está RENDERIZADO no #app. Não usar window.location.pathname pra
    // comparar contexto: no popstate o location já mudou pro destino antes do handler,
    // então a comparação daria sempre igual e o reload de troca de layout nunca ocorreria.
    let _caminhoRenderizado = window.location.pathname;
    async function navegar(url, options = {}) {
        const meuToken = ++_navToken;
        const targetUrl = new URL(url, window.location.origin);
        const browserUrl = `${targetUrl.pathname}${targetUrl.search}${targetUrl.hash}`;

        try {
            const {
                updateHistory = true,
                historyKey = null,
                historyHtml = null,
                restoreScroll = null,
            } = options;

            // Fechar sidebar drawer no mobile antes de navegar
            if (window.closeSidebarDrawer) {
                window.closeSidebarDrawer();
            }

            // Verificar se há mudança de contexto (autenticado <-> não autenticado)
            // URLs autenticadas: /dashboard, /app/*
            // URLs não autenticadas: /inicio, /login, etc.
            const urlPath = targetUrl.pathname;
            const currentPath = _caminhoRenderizado;
            
            // Detectar se estamos navegando para/da área autenticada
            const isDashboardArea = (path) => path.startsWith('/app/');
            
            const currentIsDashboard = isDashboardArea(currentPath);
            const targetIsDashboard = isDashboardArea(urlPath);
            
            // Se há mudança entre área autenticada e não autenticada, recarregar página completa
            // para garantir que o header seja trocado corretamente
            if (currentIsDashboard !== targetIsDashboard) {
                window.location.href = targetUrl.toString();
                return;
            }
            
            // Mostrar loading
            mostrarLoading();

            // No popstate, o snapshot da entrada deixa URL e DOM coerentes no mesmo
            // instante, sem manter a página antiga já desmontada durante um novo fetch.
            if (typeof historyHtml === 'string') {
                renderizar(historyHtml);
                return;
            }

            // Cache hit de prefetch → renderiza instantâneo, sem round-trip ao servidor.
            // Só HTML puro (200, não-JSON) é cacheado; consome ao usar pra não servir velho.
            const chaveCache = targetUrl.toString();
            const htmlCacheado = _cacheGet(chaveCache);
            if (htmlCacheado !== null) {
                _prefetchCache.delete(chaveCache);
                renderizar(htmlCacheado);
                return;
            }

            // Prefetch dessa URL ainda em voo (hover → clique rápido)? Aproveita em vez
            // de disparar um 2º fetch idêntico ao servidor.
            if (_prefetchInFlight.has(chaveCache)) {
                try { await _prefetchInFlight.get(chaveCache); } catch (e) { /* segue pro fetch normal */ }
                if (meuToken !== _navToken) return;
                const htmlPre = _cacheGet(chaveCache);
                if (htmlPre !== null) {
                    _prefetchCache.delete(chaveCache);
                    renderizar(htmlPre);
                    return;
                }
            }

            // Buscar conteúdo da nova página
            const resposta = await fetch(targetUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                },
                credentials: 'same-origin'
            });

            // Navegação mais nova assumiu enquanto buscávamos → descarta esta resposta.
            if (meuToken !== _navToken) return;

            console.log('[SPA] Resposta recebida:', {
                url,
                status: resposta.status,
                ok: resposta.ok,
                contentType: resposta.headers.get('content-type'),
                redirected: resposta.redirected
            });
            
            // Verificar se é erro de autenticação (sessão expirada)
            if (resposta.status === 401 || resposta.status === 419) {
                console.warn('[SPA] Erro de autenticação, redirecionando para login');
                window.location.href = '/login';
                return;
            }
            
            if (!resposta.ok) {
                console.error('[SPA] Resposta não OK:', resposta.status, resposta.statusText);
                window.location.href = targetUrl.toString();
                return;
            }
            
            // Verificar se é JSON (erro de autenticação, etc.)
            const contentType = resposta.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const data = await resposta.json();
                if (meuToken !== _navToken) return;

                // Só processar redirects se:
                // 1. A URL solicitada não for uma rota de API (/api/*)
                // 2. A resposta contém redirect E indica explicitamente que é um redirect de navegação
                const isApiRoute = urlPath.startsWith('/api/');
                
                if (data.redirect && !isApiRoute) {
                    // Se a mensagem indica mudança de contexto (login/logout), recarregar página completa
                    // para garantir que o header seja trocado corretamente
                    if (data.message && (data.message.includes('logado') || data.message.includes('logout') || data.message.includes('não está logado'))) {
                        window.location.href = data.redirect;
                        return;
                    }
                    // Navegar via SPA em vez de recarregar página
                    await navegar(data.redirect);
                    return;
                }
                
                // Se for JSON mas não tem redirect ou é uma rota de API, tratar como erro de navegação
                // (requisições de API não devem ser processadas pela função navegar)
                if (isApiRoute) {
                    throw new Error('Resposta JSON de API recebida em requisição de navegação');
                }
                
                throw new Error('Resposta JSON inesperada');
            }
            
            // Pegar HTML da resposta e renderizar
            const html = await resposta.text();
            if (meuToken !== _navToken) return;
            renderizar(html);

            // Renderiza o HTML no #app + pós-processamento (scripts, layout, scroll).
            // Usado tanto pelo fetch normal quanto pelo cache de prefetch (função
            // declarada = hoisted, então a chamada do cache-check acima também a acha).
            function renderizar(htmlNovo) {
                if (meuToken !== _navToken) return;

                // Mesma página (paginação/filtros/ordenação) → preserva a posição de
                // scroll em vez de jogar ao topo. Captura ANTES de trocar o conteúdo.
                const mesmaPagina = urlPath === currentPath;
                const scrollAnterior = window.scrollY;

                // A view atual continua funcional enquanto a próxima está em trânsito.
                // O cleanup só acontece quando a resposta vencedora vai de fato trocar o DOM.
                salvarEntradaRenderizada();
                limparRecursos();
                app.innerHTML = htmlNovo;

                let entryKey = historyKey;
                if (updateHistory) {
                    entryKey = _newHistoryKey();
                    history.pushState(_stateWithSpa(entryKey, browserUrl), '', browserUrl);
                } else if (!entryKey) {
                    entryKey = _spaState()?.key || _newHistoryKey();
                    history.replaceState(_stateWithSpa(entryKey, browserUrl), '', browserUrl);
                }

                const scrollDestino = restoreScroll !== null && Number.isFinite(Number(restoreScroll))
                    ? Math.max(0, Number(restoreScroll))
                    : (mesmaPagina ? scrollAnterior : 0);
                _renderedHistoryKey = entryKey;
                _storeHistoryEntry(entryKey, htmlNovo, browserUrl, scrollDestino);

                // Marca o que está de fato renderizado antes de qualquer init da view.
                _caminhoRenderizado = urlPath;

                // Atualizar CSRF token após navegação SPA
                atualizarCsrfToken();
                // Destacar link ativo
                destacarLinkAtivo(targetUrl.toString());
                // Executar scripts da nova página
                executarScripts(meuToken);

                // Inicializar layout (menu mobile, etc.)
                if (window.initLayout && typeof window.initLayout === 'function') {
                    try {
                        window.initLayout();
                    } catch (error) {
                        console.error('Erro ao inicializar layout:', error);
                    }
                }

                window.scrollTo(0, scrollDestino);

                // Deep-link por âncora (#id) após render SPA — ex.: banner do clearance
                // apontando pro cadastro de certificado em /app/minha-empresa#certificado-digital.
                if (targetUrl.hash) {
                    const alvoAncora = document.getElementById(targetUrl.hash.slice(1));
                    if (alvoAncora) {
                        alvoAncora.scrollIntoView({ block: 'start' });
                    }
                }

            }

        } catch (erro) {
            // Log do erro para debug
            console.error('[SPA] Erro ao navegar:', {
                url,
                error: erro.message,
                stack: erro.stack
            });
            
            if (meuToken !== _navToken) return;

            // Inclusive em falha de rede: no popstate a URL já mudou, então deixar o DOM
            // anterior seria uma inconsistência permanente. O reload é o fallback seguro.
            console.warn('[SPA] Erro na navegação SPA, recarregando página completa:', url);
            window.location.href = targetUrl.toString();
            return;
        } finally {
            esconderLoading();
        }
    }

    window.navigateTo = function(url, options = {}) {
        return navegar(url, options);
    };

    // 2.1. NAVEGAÇÃO PARCIAL DE LISTA PAGINADA (troca só o container, preserva scroll)
    async function navegarLista(link, container) {
        const meuToken = ++_navToken;
        const url = link.href;
        const targetUrl = new URL(url, window.location.origin);
        const browserUrl = `${targetUrl.pathname}${targetUrl.search}${targetUrl.hash}`;
        const containerId = container.id;

        // Mostrar loading localizado (overlay esmaecido sobre a lista)
        container.setAttribute('data-spa-loading', '');

        try {
            const resposta = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                },
                credentials: 'same-origin'
            });

            if (meuToken !== _navToken) return;

            // Sessão expirada → login (igual ao navegar())
            if (resposta.status === 401 || resposta.status === 419) {
                window.location.href = '/login';
                return;
            }

            if (!resposta.ok) {
                // Fallback: deixar o browser carregar a página inteira
                window.location.href = url;
                return;
            }

            const html = await resposta.text();
            if (meuToken !== _navToken) return;

            const doc = new DOMParser().parseFromString(html, 'text/html');
            const novo = doc.getElementById(containerId);

            if (!novo) {
                // Estrutura inesperada → fallback full reload
                window.location.href = url;
                return;
            }

            // Trocar SÓ o miolo da lista — não tocar no scroll
            salvarEntradaRenderizada();
            container.innerHTML = novo.innerHTML;

            // Atualizar URL (back/forward e F5 mantêm a página)
            const entryKey = _newHistoryKey();
            history.pushState(_stateWithSpa(entryKey, browserUrl), '', browserUrl);
            _renderedHistoryKey = entryKey;
            _caminhoRenderizado = targetUrl.pathname;
            _storeHistoryEntry(entryKey, html, browserUrl, window.scrollY);

            // Avisar quem precisa reagir ao swap (estado derivado, badges, etc.)
            document.dispatchEvent(new CustomEvent('spa:list-updated', {
                detail: { container, url }
            }));
        } catch (erro) {
            console.error('[SPA] Erro ao paginar lista, recarregando página:', erro);
            window.location.href = url;
        } finally {
            container.removeAttribute('data-spa-loading');
        }
    }

    // 3. DESTACAR LINK ATIVO
    function destacarLinkAtivo(url) {
        // Usar a função do layout.js se disponível
        if (window.setActiveLink) {
            const caminhoAtual = new URL(url).pathname;
            window.setActiveLink(caminhoAtual);
            return;
        }
        
        // Fallback: remover destaque de todos
        document.querySelectorAll('[data-link]').forEach(link => {
            const isButton = link.dataset.button !== undefined;
            
            if (isButton) {
                // Para botões, remover indicadores visuais de ativo
                link.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
            } else {
                // Para links normais, remover classes de texto ativo
                link.classList.remove('text-blue-500', 'font-semibold');
                link.classList.add('text-gray-600');
            }
        });
        
        // Destacar link atual
        const caminhoAtual = new URL(url).pathname;
        const linkAtivo = document.querySelector(`[data-link][href="${caminhoAtual}"]`);
        if (linkAtivo) {
            const isButton = linkAtivo.dataset.button !== undefined;
            
            if (isButton) {
                // Para botões, usar ring como indicador visual sem alterar o peso da fonte
                linkAtivo.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
            } else {
                // Para links normais, usar classes de texto ativo
                linkAtivo.classList.remove('text-gray-600');
                linkAtivo.classList.add('text-blue-500', 'font-semibold');
            }
        }
    }
    
    // 4. EXECUTAR SCRIPTS
    function executarScripts(navigationToken = _navToken) {
        // Remover os <script> inline já executados da navegação anterior. Eles ficam inertes
        // no <head> após rodar (a IIFE já executou; estado/listeners são limpos por limparRecursos),
        // então acumulavam ~1 tag por navegação. Os scripts externos (src) NÃO são tocados —
        // são deduplicados por src e reusados entre navegações.
        document.head.querySelectorAll('script[data-spa-inline]').forEach((s) => s.remove());

        const scripts = app.querySelectorAll('script');
        const loadPromises = [];
        const initializersToReplay = new Set();

        scripts.forEach((script, index) => {
            try {
                // Data-islands (ex.: <script type="application/json" id="cockpit-initial">) NÃO são
                // JS executável. Recriá-los apaga type/id e quebra quem lê o dado (gráficos do cockpit
                // ficam vazios). Deixar intactos no DOM — só JS de verdade é reprocessado.
                const tipoScript = (script.getAttribute('type') || '').toLowerCase();
                if (tipoScript && tipoScript !== 'text/javascript' && tipoScript !== 'application/javascript' && tipoScript !== 'module') {
                    return;
                }

                // Script externo com src - carregar dinamicamente.
                // Dedup por PATH normalizado (ignora ?v=): já carregado (inclusive no load
                // inicial, fora do <head>) não recarrega — evita double-load e listeners dobrados.
                if (script.src) {
                    const scriptSrc = script.getAttribute('src');
                    const scriptPath = _normalizarScriptSrc(scriptSrc);
                    const initializer = script.dataset.spaInit || _spaScriptInitializers[scriptPath] || null;
                    if (!_scriptsCarregados.has(scriptPath)) {
                        _scriptsCarregados.add(scriptPath);
                        const novoScript = document.createElement('script');
                        novoScript.src = scriptSrc;
                        // Collect a promise for each NEW external script
                        const p = new Promise((resolve) => {
                            novoScript.onload = resolve;
                            novoScript.onerror = resolve; // resolve even on error so we don't hang
                        });
                        loadPromises.push(p);
                        document.head.appendChild(novoScript);
                    } else if (initializer) {
                        // O código global já existe, mas os nós do DOM são novos.
                        // Reexecuta apenas o init declarado, nunca o arquivo inteiro.
                        initializersToReplay.add(initializer);
                    }
                    script.parentNode.removeChild(script);
                    return;
                }

                const novoScript = document.createElement('script');
                novoScript.textContent = script.textContent;
                novoScript.setAttribute('data-spa-inline', '1'); // marca p/ remoção na próxima navegação

                // Validar se o script tem conteúdo válido antes de executar
                if (script.textContent && script.textContent.trim() !== '') {
                    // Adicionar handler de erro para capturar erros de sintaxe
                    novoScript.onerror = function(error) {
                        console.error('Erro ao executar script:', error);
                    };

                    // Adicionar ao head para executar
                    document.head.appendChild(novoScript);

                    // Remover script original do app
                    script.parentNode.removeChild(script);
                } else {
                    // Remover script vazio
                    script.parentNode.removeChild(script);
                }
            } catch (error) {
                console.error('Erro ao processar script:', error);
                // Continuar com outros scripts mesmo se um falhar
            }
        });

        function chamarFuncoesEspecificas() {
            if (navigationToken !== _navToken) return;

            initializersToReplay.forEach((initializer) => {
                if (navigationToken !== _navToken) return;
                if (typeof window[initializer] !== 'function') return;

                try {
                    window[initializer]();
                } catch (error) {
                    console.error(`Erro ao reinicializar ${initializer}:`, error);
                }
            });

            try {
                executarFuncoesEspecificas(navigationToken, initializersToReplay);
            } catch (error) {
                console.error('Erro ao executar funções específicas:', error);
            }
        }

        if (loadPromises.length > 0) {
            // Wait for all newly added external scripts to load before calling init functions
            Promise.all(loadPromises).then(chamarFuncoesEspecificas);
        } else {
            // No new external scripts — keep short fallback for inline scripts
            setTimeout(chamarFuncoesEspecificas, 50);
        }
    }
    
    // 4.1. EXECUTAR FUNÇÕES ESPECÍFICAS
    function executarFuncoesEspecificas(navigationToken = _navToken, funcoesJaExecutadas = new Set()) {
        if (navigationToken !== _navToken) return;

        const caminho = window.location.pathname;
        const infoPagina = obterInfoPagina(caminho);
        const funcaoAlvo = Object.prototype.hasOwnProperty.call(funcoesEspecificas, caminho)
            ? funcoesEspecificas[caminho]
            : infoPagina.nomeFuncao;

        if (!funcaoAlvo || funcoesJaExecutadas.has(funcaoAlvo)) {
            return;
        }

        if (window[funcaoAlvo] && typeof window[funcaoAlvo] === 'function') {
            try {
                window[funcaoAlvo]();
            } catch (error) {
                console.error(`Erro ao executar função ${funcaoAlvo}:`, error);
            }
            return;
        }

        carregarJavaScriptEspecifico(caminho, navigationToken, funcaoAlvo);
    }
    
    // 4.2. CARREGAR JAVASCRIPT ESPECÍFICO (SISTEMA DINÂMICO)
    function carregarJavaScriptEspecifico(caminho, navigationToken = _navToken, nomeFuncao = null) {
        if (navigationToken !== _navToken) return;

        const infoPagina = obterInfoPagina(caminho);
        const funcaoAlvo = nomeFuncao || (Object.prototype.hasOwnProperty.call(funcoesEspecificas, caminho)
            ? funcoesEspecificas[caminho]
            : infoPagina.nomeFuncao);
        const scriptPath = infoPagina.scriptPath;

        if (!funcaoAlvo || !scriptPath) return;

        const normalizedPath = _normalizarScriptSrc(scriptPath);
        if (_scriptsCarregados.has(normalizedPath)) {
            tentarExecutarFuncao(funcaoAlvo, 0, navigationToken);
            return;
        }

        _scriptsCarregados.add(normalizedPath);
        const script = document.createElement('script');
        script.src = scriptPath;
        script.onload = () => tentarExecutarFuncao(funcaoAlvo, 0, navigationToken);
        script.onerror = () => console.warn(`[SPA] Script de página não encontrado: ${scriptPath}`);
        document.head.appendChild(script);
    }
    
    // 4.3. EXECUTAR FUNÇÃO ESPECÍFICA (SISTEMA DINÂMICO)
    function executarFuncaoEspecifica(caminho, navigationToken = _navToken) {
        if (navigationToken !== _navToken) return;

        // Sistema dinâmico: gera nome da função automaticamente
        // /contato → initContato
        // /sobre → initSobre
        // /dashboard → initDashboard
        const infoPagina = obterInfoPagina(caminho);
        const nomeFuncao = Object.prototype.hasOwnProperty.call(funcoesEspecificas, caminho)
            ? funcoesEspecificas[caminho]
            : infoPagina.nomeFuncao;

        if (nomeFuncao && infoPagina.nomePagina !== '') {
            // Tentar executar a função com retry
            tentarExecutarFuncao(nomeFuncao, 0, navigationToken);
        }
    }
    
    // 4.4. TENTAR EXECUTAR FUNÇÃO COM RETRY
    function tentarExecutarFuncao(nomeFuncao, tentativas, navigationToken = _navToken) {
        if (navigationToken !== _navToken) return;

        if (window[nomeFuncao] && typeof window[nomeFuncao] === 'function') {
            try {
                window[nomeFuncao]();
            } catch (error) {
                console.error(`Erro ao executar função ${nomeFuncao}:`, error);
            }
        } else if (tentativas < 15) {
            // Função ainda não está disponível, tentar novamente
            setTimeout(() => {
                tentarExecutarFuncao(nomeFuncao, tentativas + 1, navigationToken);
            }, 200);
        } else {
            console.warn(`Função ${nomeFuncao} não encontrada após ${tentativas} tentativas`);
        }
    }
    
    // 5. LOADING (desabilitado)
    function mostrarLoading() {
        // Loading desabilitado - sem barra no topo
    }
    
    function esconderLoading() {
        // Loading desabilitado - sem barra no topo
    }
    
    // 6. BOTÕES VOLTAR/AVANÇAR
    window.addEventListener('popstate', (event) => {
        // Cruzou a fronteira autenticado (/app/*) ↔ público? O layout/header muda, então
        // recarrega a página inteira (o location já é o destino) em vez de fazer swap SPA.
        const alvoIsApp = location.pathname.startsWith('/app/');
        const renderIsApp = _caminhoRenderizado.startsWith('/app/');
        if (alvoIsApp !== renderIsApp) {
            window.location.reload();
            return;
        }

        salvarEntradaRenderizada();
        const meta = _spaState(event.state);
        const cached = meta?.key ? _historyCache.get(meta.key) : null;

        navegar(window.location.href, {
            updateHistory: false,
            historyKey: meta?.key || null,
            historyHtml: cached?.url === _browserUrl() ? cached.html : null,
            restoreScroll: cached?.scrollY ?? null,
        });
    });
    
    // 7. INICIALIZAR
    destacarLinkAtivo(window.location.href);
    
    // 8. CARREGAR JAVASCRIPT NA PRIMEIRA CARGA
    function carregarJavaScriptInicial() {
        // Scripts presentes no HTML inicial já foram executados pelo parser. Recriá-los aqui
        // executava cada IIFE duas vezes e deixava listeners órfãos após a primeira navegação.
        // Só chamamos o init de página (ou carregamos o bundle convencional, se explícito).
        setTimeout(() => {
            try {
                executarFuncoesEspecificas(_navToken);
            } catch (error) {
                console.error('Erro ao executar funções específicas na primeira carga:', error);
            }
        }, 150);
    }
    
    // 9. CARREGAR JAVASCRIPT NA PRIMEIRA CARGA
    carregarJavaScriptInicial();

});
