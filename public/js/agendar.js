// Função específica para a página de agendamento
function initAgendar() {
    console.log('initAgendar chamado');
    const registerForm = document.getElementById('registerForm');
    
    if (!registerForm) {
        console.warn('Formulário registerForm não encontrado');
        // Tentar novamente após um pequeno delay (para SPA)
        setTimeout(() => {
            initAgendar();
        }, 100);
        return;
    }
    
    console.log('Formulário encontrado, inicializando...');
    
    // 🔥 REMOVER TODOS OS LISTENERS ANTIGOS
    const newForm = registerForm.cloneNode(true);
    registerForm.parentNode.replaceChild(newForm, registerForm);
    
    // Agora pegar a referência do novo formulário
    const freshForm = document.getElementById('registerForm');
    
    if (!freshForm) {
        console.error('Erro ao recriar formulário');
        return;
    }
    
    // Garantir que o formulário está visível
    freshForm.style.display = 'block';
    freshForm.style.visibility = 'visible';
    
    // Garantir que todas as seções estão visíveis
    const secoes = freshForm.querySelectorAll('div[class*="rounded-2xl"]');
    secoes.forEach(secao => {
        secao.style.display = 'block';
        secao.style.visibility = 'visible';
    });
    
    // Aplicar máscaras no novo formulário
    // Aguardar jQuery estar disponível
    if (typeof $ !== 'undefined') {
        $(document).ready(function() {
            // Máscara para CNPJ
            $('#cnpj').mask('00.000.000/0000-00');
            $('#telefone').mask('(00) 00000-0000');
        });
    } else {
        // Se jQuery não estiver disponível, tentar novamente
        setTimeout(() => {
            if (typeof $ !== 'undefined') {
                $('#cnpj').mask('00.000.000/0000-00');
                $('#telefone').mask('(00) 00000-0000');
            }
        }, 200);
    }
    
    // Função interna para o handler
    function handleSubmit(e) {
        e.preventDefault();
        
        // Buscar token CSRF mais recente
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!token) {
            showToast('Erro: Token CSRF não encontrado. Recarregue a página.', 'error');
            return;
        }
        
        // Coletar dados do formulário
        const formData = new FormData(e.target);
        
        // Enviar para o Laravel
        fetch('/agendar', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Agendamento realizado com sucesso! Entraremos em contato em breve.', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Erro ao realizar agendamento. Tente novamente.', 'error');
        });
    }
    
    // Adicionar apenas UM event listener
    freshForm.addEventListener('submit', handleSubmit);
    
    console.log('initAgendar concluído');
}