// Função específica para a página de soluções
function initSolucoes() {
    // Inicializar Swiper com scroll contínuo fluido profissional
    const swiper = new Swiper('.solutions-swiper', {
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
}
