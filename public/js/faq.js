// FAQ Accordion - Otimizado
let _faqHandlers = [];

function initFaq() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    if (faqItems.length === 0) return;
    
    // Remover listeners antigos se existirem
    _faqHandlers.forEach(({ element, handler }) => {
        if (element && handler) {
            element.removeEventListener('click', handler);
        }
    });
    _faqHandlers = [];
    
    faqItems.forEach((item) => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        
        if (!question || !answer) return;
        
        // Criar handler para este item
        const handler = function(e) {
            e.preventDefault();
            
            const isActive = item.classList.contains('active');
            const svg = question.querySelector('svg');
            
            // Fecha todos os outros
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                    const otherAnswer = otherItem.querySelector('.faq-answer');
                    const otherSvg = otherItem.querySelector('.faq-question svg');
                    if (otherAnswer) {
                        otherAnswer.style.display = 'none';
                        otherAnswer.style.maxHeight = '0';
                        otherAnswer.style.opacity = '0';
                    }
                    if (otherSvg) {
                        otherSvg.style.transform = 'rotate(0deg)';
                    }
                }
            });
            
            // Toggle atual
            if (isActive) {
                item.classList.remove('active');
                answer.style.display = 'none';
                answer.style.maxHeight = '0';
                answer.style.opacity = '0';
                if (svg) {
                    svg.style.transform = 'rotate(0deg)';
                }
            } else {
                item.classList.add('active');
                answer.style.display = 'block';
                answer.style.maxHeight = answer.scrollHeight + 'px';
                answer.style.opacity = '1';
                if (svg) {
                    svg.style.transform = 'rotate(180deg)';
                }
            }
        };
        
        question.addEventListener('click', handler);
        _faqHandlers.push({ element: question, handler });
        
        // Estado inicial
        answer.style.display = 'none';
        answer.style.maxHeight = '0';
        answer.style.opacity = '0';
        answer.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
    });
}

// Função de limpeza para recursos da página FAQ
function cleanupFaq() {
    _faqHandlers.forEach(({ element, handler }) => {
        if (element && handler) {
            element.removeEventListener('click', handler);
        }
    });
    _faqHandlers = [];
}

// Registrar função de cleanup no sistema global
if (!window._cleanupFunctions) {
    window._cleanupFunctions = {};
}
window._cleanupFunctions.initFaq = cleanupFaq;