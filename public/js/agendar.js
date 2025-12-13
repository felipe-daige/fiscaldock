// Função específica para a página de agendamento
function initAgendar() {
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        // 🔥 REMOVER TODOS OS LISTENERS ANTIGOS
        const newForm = registerForm.cloneNode(true);
        registerForm.parentNode.replaceChild(newForm, registerForm);
        
        // Agora pegar a referência do novo formulário
        const freshForm = document.getElementById('registerForm');
        
        // Aplicar máscaras no novo formulário
        $(document).ready(function() {
            // Máscara para CNPJ
            $('#cnpj').mask('00.000.000/0000-00');
            $('#telefone').mask('(00) 00000-0000');
        });
        
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
    }
}