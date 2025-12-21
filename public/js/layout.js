// JavaScript do layout (menu mobile, etc.)
let _layoutInitialized = false;
let _mobileMenuHandler = null;
let _dropdownClickHandler = null;
let _dropdownHoverHandlers = [];
let _sidebarOpenHandler = null;
let _sidebarCloseHandler = null;
let _sidebarOverlayHandler = null;
let _sidebarLinkClickHandler = null;
let _sidebarCollapsibleHandler = null;
let _sidebarToggleHandler = null;
const _dropdownOpenTimers = new WeakMap();
const _dropdownCloseTimers = new WeakMap();
const DROPDOWN_DELAY_MS = 100; // delay adicional para hover do menu Soluções

function initLayout() {
    // Sempre remover listeners antigos antes de adicionar novos
    // Isso permite reinicialização após navegação quando o DOM é substituído
    
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        // Sempre remover listener antigo se existir
        if (_mobileMenuHandler) {
            mobileMenuBtn.removeEventListener('click', _mobileMenuHandler);
            _mobileMenuHandler = null;
        }
        
        // Criar novo handler
        _mobileMenuHandler = function() {
            mobileMenu.classList.toggle('hidden');
            if (!mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('flex');
            } else {
                mobileMenu.classList.remove('flex');
            }
        };
        
        mobileMenuBtn.addEventListener('click', _mobileMenuHandler);
    }

    // Sidebar (área autenticada) - drawer no mobile + recolher/expandir no desktop
    const sidebar = document.getElementById('app-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarOpenBtn = document.getElementById('sidebar-open-btn');
    const sidebarCloseBtn = document.getElementById('sidebar-close-btn');
    const root = document.body;

    const isDesktop = () => window.matchMedia('(min-width: 768px)').matches;

    const openSidebarDrawer = () => {
        if (!sidebar || !sidebarOverlay) return;
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        sidebarOverlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeSidebarDrawer = () => {
        if (!sidebar || !sidebarOverlay) return;
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        sidebarOverlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    // Abrir (mobile)
    if (sidebarOpenBtn && sidebar) {
        if (_sidebarOpenHandler) {
            sidebarOpenBtn.removeEventListener('click', _sidebarOpenHandler);
            _sidebarOpenHandler = null;
        }
        _sidebarOpenHandler = function () {
            openSidebarDrawer();
        };
        sidebarOpenBtn.addEventListener('click', _sidebarOpenHandler);
    }

    // Fechar (mobile)
    if (sidebarCloseBtn && sidebar) {
        if (_sidebarCloseHandler) {
            sidebarCloseBtn.removeEventListener('click', _sidebarCloseHandler);
            _sidebarCloseHandler = null;
        }
        _sidebarCloseHandler = function () {
            closeSidebarDrawer();
        };
        sidebarCloseBtn.addEventListener('click', _sidebarCloseHandler);
    }

    // Overlay click (mobile)
    if (sidebarOverlay) {
        if (_sidebarOverlayHandler) {
            sidebarOverlay.removeEventListener('click', _sidebarOverlayHandler);
            _sidebarOverlayHandler = null;
        }
        _sidebarOverlayHandler = function () {
            closeSidebarDrawer();
        };
        sidebarOverlay.addEventListener('click', _sidebarOverlayHandler);
    }

    // Fechar drawer ao clicar em link (mobile)
    if (sidebar) {
        if (_sidebarLinkClickHandler) {
            sidebar.removeEventListener('click', _sidebarLinkClickHandler);
            _sidebarLinkClickHandler = null;
        }
        _sidebarLinkClickHandler = function (e) {
            const link = e.target && e.target.closest ? e.target.closest('[data-link]') : null;
            if (!link) return;
            if (isDesktop()) return;
            closeSidebarDrawer();
        };
        sidebar.addEventListener('click', _sidebarLinkClickHandler);
    }
    
    // Dropdown menu - fechar ao clicar fora (opcional, melhora UX)
    // Remover listener antigo se existir
    if (_dropdownClickHandler) {
        document.removeEventListener('click', _dropdownClickHandler);
        _dropdownClickHandler = null;
    }
    
    // Criar novo handler para fechar dropdowns ao clicar fora
    _dropdownClickHandler = function(e) {
        const dropdownGroups = document.querySelectorAll('.relative.group');
        dropdownGroups.forEach(group => {
            const dropdownMenu = group.querySelector('.dropdown-menu');
            if (dropdownMenu && !group.contains(e.target)) {
                // Verificar se o dropdown está visível antes de fechar
                if (!dropdownMenu.classList.contains('opacity-0')) {
                    dropdownMenu.classList.add('opacity-0', 'invisible');
                    dropdownMenu.classList.remove('opacity-100', 'visible');
                }
            }
        });
    };
    
    document.addEventListener('click', _dropdownClickHandler);

    // Dropdown com delay de hover (menu Soluções)
    // Remover handlers anteriores para evitar duplicação
    if (_dropdownHoverHandlers.length) {
        _dropdownHoverHandlers.forEach(({ element, enterHandler, leaveHandler }) => {
            element.removeEventListener('mouseenter', enterHandler);
            element.removeEventListener('mouseleave', leaveHandler);
        });
        _dropdownHoverHandlers = [];
    }

    const dropdownGroups = document.querySelectorAll('.nav-dropdown-buffer');

    const showPanel = (panel) => {
        panel.classList.remove('opacity-0', 'invisible', 'pointer-events-none', 'translate-y-2');
        panel.classList.add('opacity-100', 'visible', 'pointer-events-auto', 'translate-y-0');
    };

    const hidePanel = (panel) => {
        panel.classList.add('opacity-0', 'invisible', 'pointer-events-none', 'translate-y-2');
        panel.classList.remove('opacity-100', 'visible', 'pointer-events-auto', 'translate-y-0');
    };

    dropdownGroups.forEach(group => {
        const panel = group.querySelector('.nav-dropdown-panel');
        if (!panel) return;

        // Remover comportamentos de hover direto (Tailwind) para controlar via JS com delay
        panel.classList.remove('group-hover:translate-y-0', 'group-hover:visible', 'group-hover:opacity-100', 'group-hover:pointer-events-auto');

        const enterHandler = () => {
            const closeTimer = _dropdownCloseTimers.get(group);
            if (closeTimer) {
                clearTimeout(closeTimer);
                _dropdownCloseTimers.delete(group);
            }

            const openTimer = setTimeout(() => {
                showPanel(panel);
            }, DROPDOWN_DELAY_MS);
            _dropdownOpenTimers.set(group, openTimer);
        };

        const leaveHandler = () => {
            const openTimer = _dropdownOpenTimers.get(group);
            if (openTimer) {
                clearTimeout(openTimer);
                _dropdownOpenTimers.delete(group);
            }

            const closeTimer = setTimeout(() => {
                hidePanel(panel);
            }, DROPDOWN_DELAY_MS);
            _dropdownCloseTimers.set(group, closeTimer);
        };

        // Inicia estado escondido para garantir consistência
        hidePanel(panel);

        group.addEventListener('mouseenter', enterHandler);
        group.addEventListener('mouseleave', leaveHandler);

        _dropdownHoverHandlers.push({ element: group, panel, enterHandler, leaveHandler });
    });
    
    // Update year
    const currentYearElement = document.getElementById('current-year');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }
    
    // Gerenciar link ativo
    updateActiveLink();
    
    // Sidebar collapsible (menu Soluções)
    // Remover handler antigo se existir
    if (_sidebarCollapsibleHandler) {
        const oldCollapsible = document.querySelector('.sidebar-collapsible');
        const oldSummary = oldCollapsible?.querySelector('.sidebar-summary');
        if (oldSummary && _sidebarCollapsibleHandler) {
            oldSummary.removeEventListener('click', _sidebarCollapsibleHandler);
        }
        _sidebarCollapsibleHandler = null;
    }
    
    const sidebarCollapsible = document.querySelector('.sidebar-collapsible');
    const sidebarSummary = sidebarCollapsible?.querySelector('.sidebar-summary');
    const sidebarSubmenuWrapper = sidebarCollapsible?.querySelector('.sidebar-submenu-wrapper');
    const sidebarArrow = sidebarCollapsible?.querySelector('.sidebar-arrow');
    
    if (sidebarCollapsible && sidebarSummary && sidebarSubmenuWrapper) {
        // Inicializar estado expandido
        // Usar requestAnimationFrame para garantir que o DOM está renderizado
        requestAnimationFrame(() => {
            if (sidebarCollapsible.classList.contains('expanded')) {
                const height = sidebarSubmenuWrapper.scrollHeight;
                sidebarSubmenuWrapper.style.maxHeight = height + 'px';
                sidebarSubmenuWrapper.style.opacity = '1';
                sidebarSubmenuWrapper.style.visibility = 'visible';
            } else {
                sidebarSubmenuWrapper.style.maxHeight = '0';
                sidebarSubmenuWrapper.style.opacity = '0';
                sidebarSubmenuWrapper.style.visibility = 'hidden';
            }
        });
        
        // Handler de clique
        _sidebarCollapsibleHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isExpanded = sidebarCollapsible.classList.contains('expanded');
            
            if (isExpanded) {
                // Retrair
                sidebarCollapsible.classList.remove('expanded');
                sidebarCollapsible.classList.add('collapsed');
                sidebarSubmenuWrapper.style.maxHeight = '0';
                sidebarSubmenuWrapper.style.opacity = '0';
                sidebarSubmenuWrapper.style.visibility = 'hidden';
                if (sidebarArrow) {
                    sidebarArrow.style.transform = 'rotate(0deg)';
                }
            } else {
                // Expandir
                sidebarCollapsible.classList.remove('collapsed');
                sidebarCollapsible.classList.add('expanded');
                // Calcular altura e aplicar
                const height = sidebarSubmenuWrapper.scrollHeight;
                sidebarSubmenuWrapper.style.maxHeight = height + 'px';
                sidebarSubmenuWrapper.style.opacity = '1';
                sidebarSubmenuWrapper.style.visibility = 'visible';
                if (sidebarArrow) {
                    sidebarArrow.style.transform = 'rotate(180deg)';
                }
            }
        };
        
        sidebarSummary.addEventListener('click', _sidebarCollapsibleHandler);
    }
    
    // Sidebar toggle (desktop) - encolher/expandir
    const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
    
    // Função para restaurar estado do localStorage
    const restoreSidebarState = () => {
        if (!sidebar) return;
        // Só restaurar no desktop
        if (window.matchMedia('(min-width: 768px)').matches) {
            const savedState = localStorage.getItem('sidebar-collapsed');
            if (savedState === 'true') {
                sidebar.classList.add('sidebar-collapsed');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
            }
        }
    };
    
    // Restaurar estado ao carregar
    restoreSidebarState();
    
    // Handler para toggle
    if (sidebarToggleBtn && sidebar) {
        // Remover handler antigo se existir
        if (_sidebarToggleHandler) {
            sidebarToggleBtn.removeEventListener('click', _sidebarToggleHandler);
            _sidebarToggleHandler = null;
        }
        
        _sidebarToggleHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Chamar a função global toggleSidebar que já tem toda a lógica
            if (window.toggleSidebar && typeof window.toggleSidebar === 'function') {
                window.toggleSidebar();
            }
        };
        
        sidebarToggleBtn.addEventListener('click', _sidebarToggleHandler);
        
        // Não sobrescrever o onclick do HTML, apenas garantir que o event listener funcione
    }
    
    _layoutInitialized = true;
}

// Função para resetar inicialização (útil para testes ou re-inicialização)
function resetLayout() {
    // Garantir que não fica scroll travado por drawer/overlays após navegação
    document.body.classList.remove('overflow-hidden');

    if (_mobileMenuHandler) {
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        if (mobileMenuBtn) {
            mobileMenuBtn.removeEventListener('click', _mobileMenuHandler);
        }
        _mobileMenuHandler = null;
    }
    if (_sidebarOpenHandler) {
        const sidebarOpenBtn = document.getElementById('sidebar-open-btn');
        if (sidebarOpenBtn) {
            sidebarOpenBtn.removeEventListener('click', _sidebarOpenHandler);
        }
        _sidebarOpenHandler = null;
    }
    if (_sidebarCloseHandler) {
        const sidebarCloseBtn = document.getElementById('sidebar-close-btn');
        if (sidebarCloseBtn) {
            sidebarCloseBtn.removeEventListener('click', _sidebarCloseHandler);
        }
        _sidebarCloseHandler = null;
    }
    if (_sidebarOverlayHandler) {
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        if (sidebarOverlay) {
            sidebarOverlay.removeEventListener('click', _sidebarOverlayHandler);
        }
        _sidebarOverlayHandler = null;
    }
    if (_sidebarLinkClickHandler) {
        const sidebar = document.getElementById('app-sidebar');
        if (sidebar) {
            sidebar.removeEventListener('click', _sidebarLinkClickHandler);
        }
        _sidebarLinkClickHandler = null;
    }
    if (_dropdownClickHandler) {
        document.removeEventListener('click', _dropdownClickHandler);
        _dropdownClickHandler = null;
    }

    if (_dropdownHoverHandlers.length) {
        _dropdownHoverHandlers.forEach(({ element, panel, enterHandler, leaveHandler }) => {
            try {
                element.removeEventListener('mouseenter', enterHandler);
                element.removeEventListener('mouseleave', leaveHandler);
                if (panel) {
                    panel.classList.add('opacity-0', 'invisible', 'pointer-events-none', 'translate-y-2');
                    panel.classList.remove('opacity-100', 'visible', 'pointer-events-auto', 'translate-y-0');
                }
                const openTimer = _dropdownOpenTimers.get(element);
                if (openTimer) {
                    clearTimeout(openTimer);
                    _dropdownOpenTimers.delete(element);
                }
                const closeTimer = _dropdownCloseTimers.get(element);
                if (closeTimer) {
                    clearTimeout(closeTimer);
                    _dropdownCloseTimers.delete(element);
                }
            } catch (e) {
                // ignore
            }
        });
        _dropdownHoverHandlers = [];
    }
    if (_sidebarCollapsibleHandler) {
        const sidebarCollapsible = document.querySelector('.sidebar-collapsible');
        const sidebarSummary = sidebarCollapsible?.querySelector('.sidebar-summary');
        if (sidebarSummary) {
            sidebarSummary.removeEventListener('click', _sidebarCollapsibleHandler);
        }
        _sidebarCollapsibleHandler = null;
    }
    if (_sidebarToggleHandler) {
        const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
        if (sidebarToggleBtn) {
            sidebarToggleBtn.removeEventListener('click', _sidebarToggleHandler);
        }
        _sidebarToggleHandler = null;
    }
    _layoutInitialized = false;
}

// Função global para toggle da sidebar (chamada diretamente do HTML)
window.toggleSidebar = function() {
    const sidebar = document.getElementById('app-sidebar');
    if (!sidebar) {
        console.warn('Sidebar não encontrada');
        return;
    }
    
    // Só funciona no desktop
    if (!window.matchMedia('(min-width: 768px)').matches) {
        return;
    }
    
    const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
    
    if (isCollapsed) {
        sidebar.classList.remove('sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed', 'false');
    } else {
        sidebar.classList.add('sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed', 'true');
    }
};

// Inicializar toggle da sidebar imediatamente quando o DOM estiver pronto (fallback)
(function initSidebarToggle() {
    function attachToggleListener() {
        const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
        
        if (sidebarToggleBtn && !sidebarToggleBtn.hasAttribute('data-toggle-attached')) {
            sidebarToggleBtn.setAttribute('data-toggle-attached', 'true');
            
            // Adicionar listener que chama a função global
            sidebarToggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (window.toggleSidebar && typeof window.toggleSidebar === 'function') {
                    window.toggleSidebar();
                }
            });
        }
    }
    
    // Tentar imediatamente
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachToggleListener);
    } else {
        attachToggleListener();
    }
    
    // Também tentar após um pequeno delay para garantir que o DOM está totalmente renderizado
    setTimeout(attachToggleListener, 100);
    setTimeout(attachToggleListener, 500);
})();

// Tornar resetLayout e initLayout acessíveis globalmente
window.resetLayout = resetLayout;
window.initLayout = initLayout;

// Função para atualizar o link ativo
function updateActiveLink() {
    const currentPath = window.location.pathname;
    const allLinks = document.querySelectorAll('[data-link]');
    
    // Remove classes ativas de todos os links
    allLinks.forEach(link => {
        // Não aplicar estilo de link ativo/inativo em links marcados (ex.: logo/wordmark)
        if (link.hasAttribute('data-no-active')) {
            return;
        }
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
    
    // Adiciona classes ativas ao link atual
    allLinks.forEach(link => {
        if (link.hasAttribute('data-no-active')) {
            return;
        }
        if (link.getAttribute('href') === currentPath) {
            const isButton = link.classList.contains('btn-accent') || link.classList.contains('btn-primary') || link.classList.contains('btn-secondary');
            
            if (isButton) {
                // Para botões, usar ring como indicador visual sem alterar o peso da fonte
                link.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
            } else {
                // Para links normais, usar classes de texto ativo
                link.classList.remove('text-gray-600');
                link.classList.add('text-blue-500', 'font-semibold');
            }
        }
    });
}

// Função para ser chamada pelo SPA quando a página muda
function setActiveLink(path) {
    const allLinks = document.querySelectorAll('[data-link]');
    
    // Remove classes ativas de todos os links
    allLinks.forEach(link => {
        // Não aplicar estilo de link ativo/inativo em links marcados (ex.: logo/wordmark)
        if (link.hasAttribute('data-no-active')) {
            return;
        }
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
    
    // Adiciona classes ativas ao link atual
    allLinks.forEach(link => {
        if (link.hasAttribute('data-no-active')) {
            return;
        }
        if (link.getAttribute('href') === path) {
            const isButton = link.classList.contains('btn-accent') || link.classList.contains('btn-primary') || link.classList.contains('btn-secondary');
            
            if (isButton) {
                // Para botões, usar ring como indicador visual sem alterar o peso da fonte
                link.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
            } else {
                // Para links normais, usar classes de texto ativo
                link.classList.remove('text-gray-600');
                link.classList.add('text-blue-500', 'font-semibold');
            }
        }
    });
}

// Inicializar no primeiro carregamento também (não só após navegação SPA)
document.addEventListener('DOMContentLoaded', () => {
    try {
        initLayout();
    } catch (e) {
        // ignore
    }
});
