// Função específica para a página de início
// Usar propriedades em window para permitir múltiplas execuções do script sem erro de redeclaração
window._inicioInitialized = window._inicioInitialized || false;
window._countdownInterval = window._countdownInterval || null;
window._heroSliderInterval = window._heroSliderInterval || null;
window._solutionsSwiper = window._solutionsSwiper || null;
window._heroSliderHandlers = window._heroSliderHandlers || [];

function initInicio() {
    // Limpar recursos anteriores se já foi inicializado
    if (window._inicioInitialized) {
        cleanupInicio();
    }
    
    // Countdown Timer - Atualizado para nova estrutura
    function initCountdown() {
        const daysElement = document.getElementById('days');
        const hoursElement = document.getElementById('hours');
        const minutesElement = document.getElementById('minutes');
        const secondsElement = document.getElementById('seconds');
        
        if (!daysElement || !hoursElement || !minutesElement || !secondsElement) {
            return; // Se não existir, não inicializa
        }

        const targetDate = new Date('2026-01-01T00:00:00').getTime();
        
        function updateCountdown() {
            const now = Date.now();
            const distance = targetDate - now;

            if (distance < 0) {
                daysElement.textContent = '0';
                hoursElement.textContent = '00';
                minutesElement.textContent = '00';
                if (secondsElement) secondsElement.textContent = '00';
                return;
            }

            const days = Math.floor(distance / 86400000);
            const hours = Math.floor((distance % 86400000) / 3600000);
            const minutes = Math.floor((distance % 3600000) / 60000);
            const seconds = Math.floor((distance % 60000) / 1000);

            // Dias sem zeros à esquerda quando forem 1 ou 2 dígitos (ex.: 7, 63). Para 100+, mantém natural (ex.: 124).
            daysElement.textContent = days.toString();
            hoursElement.textContent = hours.toString().padStart(2, '0');
            minutesElement.textContent = minutes.toString().padStart(2, '0');
            if (secondsElement) secondsElement.textContent = seconds.toString().padStart(2, '0');
        }

        updateCountdown();
        window._countdownInterval = setInterval(updateCountdown, 1000);
        
        // Registrar intervalo no sistema de recursos
        if (window._spaResources) {
            window._spaResources.intervals.push(window._countdownInterval);
        }
    }

    initCountdown();

    // Hero Slider - Simplificado para melhor performance
    function initHeroSlider() {
        const slides = document.querySelectorAll('.slider-slide');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        
        if (slides.length === 0) return; // Se não existir, não inicializa
        
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active', 'prev'));
            dots.forEach(dot => dot.classList.remove('active'));

            slides[index].classList.add('active');
            if (dots[index]) dots[index].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        function prevSlide() {
            currentSlide = currentSlide === 0 ? slides.length - 1 : currentSlide - 1;
            showSlide(currentSlide);
        }

        // Remover listeners antigos se existirem
        window._heroSliderHandlers.forEach(({ element, event, handler }) => {
            if (element && handler) {
                element.removeEventListener(event, handler);
            }
        });
        window._heroSliderHandlers = [];

        // Event listeners simples
        if (nextBtn) {
            nextBtn.addEventListener('click', nextSlide);
            window._heroSliderHandlers.push({ element: nextBtn, event: 'click', handler: nextSlide });
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', prevSlide);
            window._heroSliderHandlers.push({ element: prevBtn, event: 'click', handler: prevSlide });
        }

        dots.forEach((dot, index) => {
            const handler = () => {
                currentSlide = index;
                showSlide(currentSlide);
            };
            dot.addEventListener('click', handler);
            window._heroSliderHandlers.push({ element: dot, event: 'click', handler });
        });

        // Auto-slide simples
        showSlide(0);
        window._heroSliderInterval = setInterval(nextSlide, 5000);
        
        // Registrar intervalo no sistema de recursos
        if (window._spaResources) {
            window._spaResources.intervals.push(window._heroSliderInterval);
        }
    }

    // Initialize slider
    initHeroSlider();

    // Inicializar gráficos se a função existir
    if (typeof initImpactos === 'function') {
        initImpactos();
    }

    // Inicializar FAQ se a função existir
    if (typeof initFaq === 'function') {
        initFaq();
    }

    // Swiper Solutions - Destruir instância anterior se existir
    if (window._solutionsSwiper && typeof window._solutionsSwiper.destroy === 'function') {
        try {
            window._solutionsSwiper.destroy(true, true);
        } catch (error) {
            console.error('Erro ao destruir Swiper anterior:', error);
        }
    }
    
    const swiperElement = document.querySelector('.inicio-solutions-swiper');
    if (swiperElement) {
        window._solutionsSwiper = new Swiper('.inicio-solutions-swiper', {
            slidesPerView: 'auto',
            spaceBetween: 24,
            speed: 1, // Transição instantânea no loop
            autoplay: {
                delay: 1,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
                waitForTransition: false,
            },
            loop: true,
            loopedSlides: 5,
            loopAdditionalSlides: 5,
            allowTouchMove: false,
            simulateTouch: false,
            grabCursor: false,
            freeMode: false,
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
            window._spaResources.swipers.push(window._solutionsSwiper);
        }
    }
    
    // Carrossel custom da seção "Soluções que transformam a rotina" (layout)
    if (typeof window.initSolucoesCarousel === 'function') {
        window.initSolucoesCarousel();
    }

    // Contact Form
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        // Remover listener antigo se existir
        if (window._contactFormHandler) {
            contactForm.removeEventListener('submit', window._contactFormHandler);
        }
        
        window._contactFormHandler = function(e) {
            e.preventDefault();
            alert('Mensagem enviada com sucesso! Entraremos em contato em breve.');
            this.reset();
        };
        
        contactForm.addEventListener('submit', window._contactFormHandler);
    }
    
    // Scroll Indicator
    window.scrollToSolucoes = function() {
        const funcionalidades = document.getElementById('funcionalidades');
        if (funcionalidades) {
            funcionalidades.scrollIntoView({behavior: 'smooth'});
        }
    };

    window._inicioInitialized = true;
}

// Função de limpeza para recursos da página de início
function cleanupInicio() {
    // Limpar intervalos
    if (window._countdownInterval) {
        clearInterval(window._countdownInterval);
        window._countdownInterval = null;
    }
    
    if (window._heroSliderInterval) {
        clearInterval(window._heroSliderInterval);
        window._heroSliderInterval = null;
    }
    
    // Remover listeners do hero slider
    window._heroSliderHandlers.forEach(({ element, event, handler }) => {
        if (element && handler) {
            element.removeEventListener(event, handler);
        }
    });
    window._heroSliderHandlers = [];
    
    // Destruir Swiper
    if (window._solutionsSwiper && typeof window._solutionsSwiper.destroy === 'function') {
        try {
            window._solutionsSwiper.destroy(true, true);
        } catch (error) {
            console.error('Erro ao destruir Swiper:', error);
        }
        window._solutionsSwiper = null;
    }
    
    // Remover handler do formulário
    if (window._contactFormHandler) {
        const contactForm = document.getElementById('contact-form');
        if (contactForm) {
            contactForm.removeEventListener('submit', window._contactFormHandler);
        }
        window._contactFormHandler = null;
    }
    
    window._inicioInitialized = false;
}

// Registrar função de cleanup no sistema global
if (!window._cleanupFunctions) {
    window._cleanupFunctions = {};
}
window._cleanupFunctions.initInicio = cleanupInicio;

// Inicialização é feita pelo spa.js


