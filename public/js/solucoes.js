// Função específica para a página de soluções
let _solucoesSwiper = null;

function initSolucoes() {
    // Destruir instância Swiper anterior se existir
    if (_solucoesSwiper && typeof _solucoesSwiper.destroy === 'function') {
        try {
            _solucoesSwiper.destroy(true, true);
        } catch (error) {
            console.error('Erro ao destruir Swiper anterior:', error);
        }
        _solucoesSwiper = null;
    }
    
    // Verificar se o elemento existe antes de criar Swiper
    const swiperElement = document.querySelector('.solutions-swiper');
    if (!swiperElement) {
        return;
    }
    
    // Inicializar Swiper com scroll contínuo fluido profissional
    _solucoesSwiper = new Swiper('.solutions-swiper', {
        slidesPerView: 'auto',
        spaceBetween: 24,
        freeMode: true,
        freeModeMomentum: false,
        speed: 4000,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
            pauseOnMouseEnter: false,
            stopOnLastSlide: false,
        },
        loop: true,
        allowTouchMove: false,
        simulateTouch: false,
        grabCursor: false,
        breakpoints: {
            320: {
                slidesPerView: 1.2,
                spaceBetween: 16,
            },
            640: {
                slidesPerView: 2.2,
                spaceBetween: 20,
            },
            1024: {
                slidesPerView: 3.2,
                spaceBetween: 24,
            },
            1280: {
                slidesPerView: 4.2,
                spaceBetween: 24,
            }
        }
    });
    
    // Registrar Swiper no sistema de recursos
    if (window._spaResources) {
        window._spaResources.swipers.push(_solucoesSwiper);
    }
}

// Função de limpeza para recursos da página de soluções
function cleanupSolucoes() {
    if (_solucoesSwiper && typeof _solucoesSwiper.destroy === 'function') {
        try {
            _solucoesSwiper.destroy(true, true);
        } catch (error) {
            console.error('Erro ao destruir Swiper:', error);
        }
        _solucoesSwiper = null;
    }
}

// Registrar função de cleanup no sistema global
if (!window._cleanupFunctions) {
    window._cleanupFunctions = {};
}
window._cleanupFunctions.initSolucoes = cleanupSolucoes;
