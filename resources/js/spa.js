document.addEventListener('DOMContentLoaded', () => {
    const app = document.getElementById('app');
    
    // 1. INTERCEPTAR CLIQUES EM LINKS
    document.body.addEventListener('click', async (e) => {
        const link = e.target.closest('[data-link]');
        if (link) {
            e.preventDefault(); // Não recarregar página
            await navegar(link.href); // Navegar via JavaScript
        }
    });
    
    // 2. FUNÇÃO PRINCIPAL DE NAVEGAÇÃO
    async function navegar(url) {
        try {
            // Mostrar loading
            mostrarLoading();
            
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
                    // Redirecionar para a página especificada (sucesso ou erro)
                    window.location.href = data.redirect;
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
            
            // Voltar ao topo
            window.scrollTo(0, 0);
            
        } catch (erro) {
            console.error('Erro:', erro);
            // Só mostrar alert se não for erro de rede
            if (erro.message && !erro.message.includes('Failed to fetch')) {
                console.log('Erro de navegação ignorado');
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
            link.classList.remove('text-blue-500', 'font-semibold');
            link.classList.add('text-gray-600');
        });
        
        // Destacar link atual
        const caminhoAtual = new URL(url).pathname;
        const linkAtivo = document.querySelector(`[data-link][href="${caminhoAtual}"]`);
        if (linkAtivo) {
            linkAtivo.classList.remove('text-gray-600');
            linkAtivo.classList.add('text-blue-500', 'font-semibold');
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
                    
                    script.parentNode.replaceChild(novoScript, script);
                } else {
                    // Remover script vazio
                    script.parentNode.removeChild(script);
                }
            } catch (error) {
                console.error('Erro ao processar script:', error);
                // Continuar com outros scripts mesmo se um falhar
            }
        });
        
        // Executar funções específicas de cada página
        try {
            executarFuncoesEspecificas();
        } catch (error) {
            console.error('Erro ao executar funções específicas:', error);
        }
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
            console.log(`Executando: ${nomeFuncao}()`);
            window[nomeFuncao]();
        }
        
        // Mapeamento específico para as views da landing page
        const funcoesEspecificas = {
            '/': 'initInicio',
            '/inicio': 'initInicio',
            '/login': 'initLogin',
            '/agendar': 'initAgendar',
            '/solucoes': 'initSolucoes',
            '/beneficios': 'initBeneficios',
            '/faq': 'initFaq',
            '/impactos': 'initImpactos'
        };
        
        if (funcoesEspecificas[caminho] && window[funcoesEspecificas[caminho]]) {
            console.log(`Executando: ${funcoesEspecificas[caminho]}()`);
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
                console.log(`Carregando JavaScript: ${scriptPath}`);
                const script = document.createElement('script');
                script.src = scriptPath;
                script.onload = function() {
                    console.log(`JavaScript carregado: ${scriptPath}`);
                    // Aguardar um pouco para garantir que a função foi definida
                    setTimeout(() => {
                        executarFuncaoEspecifica(caminho);
                    }, 100);
                };
                script.onerror = function() {
                    console.log(`Arquivo não encontrado: ${scriptPath} (normal se não tiver JavaScript específico)`);
                };
                document.head.appendChild(script);
            } else {
                console.log(`JavaScript já carregado: ${scriptPath}`);
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
            console.log(`Executando: ${nomeFuncao}()`);
            try {
                window[nomeFuncao]();
            } catch (error) {
                console.error(`Erro ao executar ${nomeFuncao}:`, error);
            }
        } else if (tentativas < 5) {
            // Função ainda não está disponível, tentar novamente
            console.log(`Aguardando ${nomeFuncao}... (tentativa ${tentativas + 1})`);
            setTimeout(() => {
                tentarExecutarFuncao(nomeFuncao, tentativas + 1);
            }, 200);
        } else {
            console.warn(`Função ${nomeFuncao} não encontrada após 5 tentativas`);
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
        console.log('Página inicial:', caminho);
        
        // Carregar JavaScript específico da página atual
        carregarJavaScriptEspecifico(caminho);
    }
});

