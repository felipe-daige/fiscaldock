// JavaScript do layout (menu mobile, etc.)
let _layoutInitialized = false;
let _mobileMenuHandler = null;
let _dropdownClickHandler = null;

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
    
    // Update year
    const currentYearElement = document.getElementById('current-year');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }
    
    // Gerenciar link ativo
    updateActiveLink();
    
    _layoutInitialized = true;
}

// Função para resetar inicialização (útil para testes ou re-inicialização)
function resetLayout() {
    if (_mobileMenuHandler) {
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        if (mobileMenuBtn) {
            mobileMenuBtn.removeEventListener('click', _mobileMenuHandler);
        }
        _mobileMenuHandler = null;
    }
    if (_dropdownClickHandler) {
        document.removeEventListener('click', _dropdownClickHandler);
        _dropdownClickHandler = null;
    }
    _layoutInitialized = false;
}

// Tornar resetLayout acessível globalmente
window.resetLayout = resetLayout;

// Função para atualizar o link ativo
function updateActiveLink() {
    const currentPath = window.location.pathname;
    const allLinks = document.querySelectorAll('[data-link]');
    
    // Remove classes ativas de todos os links
    allLinks.forEach(link => {
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
