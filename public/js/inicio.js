// Função específica para a página de início
// Usar propriedades em window para permitir múltiplas execuções do script sem erro de redeclaração
window._inicioInitialized = window._inicioInitialized || false;
window._countdownInterval = window._countdownInterval || null;

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

    // Inicializar FAQ se a função existir
    if (typeof initFaq === 'function') {
        initFaq();
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


