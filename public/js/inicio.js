// Função específica para a página de início
function initInicio() {
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
        setInterval(updateCountdown, 1000);
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
        let slideInterval;

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

        // Event listeners simples
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentSlide = index;
                showSlide(currentSlide);
            });
        });

        // Auto-slide simples
        showSlide(0);
        slideInterval = setInterval(nextSlide, 5000);
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

    // Swiper Solutions
    const solutionsSwiper = new Swiper('.inicio-solutions-swiper', {
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
    
    // Carrossel custom da seção "Soluções que transformam a rotina" (layout)
    if (typeof window.initSolucoesCarousel === 'function') {
        window.initSolucoesCarousel();
    }

    // Contact Form
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Mensagem enviada com sucesso! Entraremos em contato em breve.');
            this.reset();
        });
    }
    
    // Scroll Indicator
    window.scrollToSolucoes = function() {
        document.getElementById('funcionalidades').scrollIntoView({behavior: 'smooth'});
    };

}

// Inicialização é feita pelo spa.js


