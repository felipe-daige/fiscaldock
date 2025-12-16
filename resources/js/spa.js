document.addEventListener('DOMContentLoaded', () => {
    const app = document.getElementById('app');
    
    // Sistema de gerenciamento de recursos globais
    window._spaResources = {
        intervals: [],
        swipers: [],
        listeners: []
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
        if (typeof window._faqInitialized !== 'undefined') {
            window._faqInitialized = false;
        }
        if (typeof window._impactosInitialized !== 'undefined') {
            window._impactosInitialized = false;
        }
        if (typeof window._solucoesCarouselInitialized !== 'undefined') {
            window._solucoesCarouselInitialized = false;
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
    }
    
    // 1. INTERCEPTAR CLIQUES EM LINKS
    document.body.addEventListener('click', async (e) => {
        const link = e.target.closest('[data-link]');
        if (link) {
            e.preventDefault(); // Não recarregar página
            await navegar(link.href); // Navegar via JavaScript
        }
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
    
    // 2. FUNÇÃO PRINCIPAL DE NAVEGAÇÃO
    async function navegar(url) {
        try {
            // Verificar se há mudança de contexto (autenticado <-> não autenticado)
            // URLs autenticadas: /dashboard
            // URLs não autenticadas: /inicio, /login, etc.
            const urlPath = new URL(url, window.location.origin).pathname;
            const currentPath = window.location.pathname;
            
            // Detectar se estamos navegando para/da área autenticada
            const isDashboardArea = (path) => path === '/dashboard' || path.startsWith('/dashboard');
            
            const currentIsDashboard = isDashboardArea(currentPath);
            const targetIsDashboard = isDashboardArea(urlPath);
            
            // Se há mudança entre área autenticada e não autenticada, recarregar página completa
            // para garantir que o header seja trocado corretamente
            if (currentIsDashboard !== targetIsDashboard) {
                window.location.href = url;
                return;
            }
            
            // Mostrar loading
            mostrarLoading();
            
            // Limpar recursos antes de navegar
            limparRecursos();
            
            // Buscar conteúdo da nova página
            const resposta = await fetch(url, {
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                }
            });
            
            if (!resposta.ok) throw new Error('Erro ao carregar');
            
            // Verificar se é JSON (erro de autenticação, etc.)
            const contentType = resposta.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const data = await resposta.json();
                
                if (data.redirect) {
                    // Se a mensagem indica mudança de contexto (login/logout), recarregar página completa
                    // para garantir que o header seja trocado corretamente
                    if (data.message && (data.message.includes('logado') || data.message.includes('logout'))) {
                        window.location.href = data.redirect;
                        return;
                    }
                    // Navegar via SPA em vez de recarregar página
                    await navegar(data.redirect);
                    return;
                }
                
                // Se for JSON mas não tem redirect, tratar como erro
                throw new Error('Resposta JSON inesperada');
            }
            
            // Pegar HTML da resposta
            const html = await resposta.text();
            
            // Trocar conteúdo
            app.innerHTML = html;
            
            // Atualizar URL do browser
            history.pushState(null, '', url);
            
            // Destacar link ativo
            destacarLinkAtivo(url);
            
            // Executar scripts da nova página
            executarScripts();
            
            // Inicializar layout (menu mobile, etc.)
            if (window.initLayout && typeof window.initLayout === 'function') {
                try {
                    window.initLayout();
                } catch (error) {
                    console.error('Erro ao inicializar layout:', error);
                }
            }
            
            // Voltar ao topo
            window.scrollTo(0, 0);
            
        } catch (erro) {
            // Só mostrar alert se não for erro de rede
            if (erro.message && !erro.message.includes('Failed to fetch')) {
                // Erro de navegação ignorado
            }
        } finally {
            esconderLoading();
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
            const isButton = link.classList.contains('btn-accent') || link.classList.contains('btn-primary') || link.classList.contains('btn-secondary');
            
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
            const isButton = linkAtivo.classList.contains('btn-accent') || linkAtivo.classList.contains('btn-primary') || linkAtivo.classList.contains('btn-secondary');
            
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
    function executarScripts() {
        const scripts = app.querySelectorAll('script');
        scripts.forEach(script => {
            try {
                const novoScript = document.createElement('script');
                novoScript.textContent = script.textContent;
                
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
        
        // Aguardar um pouco para garantir que scripts foram executados
        setTimeout(() => {
            // Executar funções específicas de cada página
            try {
                executarFuncoesEspecificas();
            } catch (error) {
                console.error('Erro ao executar funções específicas:', error);
            }
        }, 50);
    }
    
    // 4.1. EXECUTAR FUNÇÕES ESPECÍFICAS
    function executarFuncoesEspecificas() {
        const caminho = window.location.pathname;
        
        // Carregar JavaScript específico da página se necessário
        carregarJavaScriptEspecifico(caminho);
        
        // Sistema dinâmico: procura por funções init + nome da página
        const nomePagina = caminho.replace('/', '').replace('-', '');
        const nomeFuncao = `init${nomePagina.charAt(0).toUpperCase() + nomePagina.slice(1)}`;
        
        // Se a função existe, executa
        if (window[nomeFuncao] && typeof window[nomeFuncao] === 'function') {
            window[nomeFuncao]();
        }
        
        // Mapeamento específico para as views da landing page
        const funcoesEspecificas = {
            '/': 'initInicio',
            '/inicio': 'initInicio',
            '/login': 'initLogin',
            '/agendar': 'initAgendar',
            '/solucoes': 'initSolucoes',
            '/sobre': 'initSobre',
            '/beneficios': 'initBeneficios',
            '/faq': 'initFaq',
            '/impactos': 'initImpactos',
            '/precos': 'initPrecos'
        };
        
        if (funcoesEspecificas[caminho] && window[funcoesEspecificas[caminho]]) {
            window[funcoesEspecificas[caminho]]();
        }
    }
    
    // 4.2. CARREGAR JAVASCRIPT ESPECÍFICO (SISTEMA DINÂMICO)
    function carregarJavaScriptEspecifico(caminho) {
        const nomePagina = caminho.replace('/', '').replace('-', '');
        const scriptPath = `/js/${nomePagina}.js`;
        
        // Verificar se arquivo existe (opcional - para debug)
        if (nomePagina && nomePagina !== '') {
            const scriptExistente = document.querySelector(`script[src="${scriptPath}"]`);
            
            if (!scriptExistente) {
                const script = document.createElement('script');
                script.src = scriptPath;
                script.onload = function() {
                    // Aguardar um pouco para garantir que a função foi definida
                    setTimeout(() => {
                        executarFuncaoEspecifica(caminho);
                    }, 100);
                };
                script.onerror = function() {
                    // Arquivo não encontrado (normal se não tiver JavaScript específico)
                };
                document.head.appendChild(script);
            } else {
                // Script já carregado, executar função
                executarFuncaoEspecifica(caminho);
            }
        }
    }
    
    // 4.3. EXECUTAR FUNÇÃO ESPECÍFICA (SISTEMA DINÂMICO)
    function executarFuncaoEspecifica(caminho) {
        // Sistema dinâmico: gera nome da função automaticamente
        // /contato → initContato
        // /sobre → initSobre
        // /dashboard → initDashboard
        const nomePagina = caminho.replace('/', '').replace('-', '');
        const nomeFuncao = `init${nomePagina.charAt(0).toUpperCase() + nomePagina.slice(1)}`;
        
        if (nomePagina && nomePagina !== '') {
            // Tentar executar a função com retry
            tentarExecutarFuncao(nomeFuncao, 0);
        }
    }
    
    // 4.4. TENTAR EXECUTAR FUNÇÃO COM RETRY
    function tentarExecutarFuncao(nomeFuncao, tentativas) {
        if (window[nomeFuncao] && typeof window[nomeFuncao] === 'function') {
            try {
                window[nomeFuncao]();
            } catch (error) {
                console.error(`Erro ao executar função ${nomeFuncao}:`, error);
            }
        } else if (tentativas < 5) {
            // Função ainda não está disponível, tentar novamente
            setTimeout(() => {
                tentarExecutarFuncao(nomeFuncao, tentativas + 1);
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
    window.addEventListener('popstate', () => {
        // Só navegar se não for a página inicial
        if (location.pathname !== '/') {
            navegar(location.pathname);
        }
    });
    
    // 7. INICIALIZAR
    destacarLinkAtivo(window.location.href);
    
    // 8. CARREGAR JAVASCRIPT NA PRIMEIRA CARGA
    carregarJavaScriptInicial();
    
    // 9. CARREGAR JAVASCRIPT NA PRIMEIRA CARGA
    function carregarJavaScriptInicial() {
        const caminho = window.location.pathname;
        
        // Carregar JavaScript específico da página atual
        carregarJavaScriptEspecifico(caminho);
    }
});

