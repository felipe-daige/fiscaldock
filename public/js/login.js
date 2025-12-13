// Função específica para a página de login
function initLogin() {
    const loginForm = document.getElementById('login-form');
    
    if (loginForm) {
        // 🔥 REMOVER TODOS OS LISTENERS ANTIGOS
        const newForm = loginForm.cloneNode(true);
        loginForm.parentNode.replaceChild(newForm, loginForm);
        
        // Agora pegar a referência do novo formulário
        const freshForm = document.getElementById('login-form');
        
        // Função interna para o handler do login
        function handleLogin(e) {
            e.preventDefault();
            
            // 🔥 BUSCAR TOKEN CSRF MAIS RECENTE A CADA TENTATIVA
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            if (!token) {
                showToast('Erro: Token CSRF não encontrado. Recarregue a página.', 'error');
                return;
            }
            
            console.log('Token CSRF atual:', token); // Debug
            
            // Coletar dados do formulário
            const formData = new FormData(e.target);
            
            // Enviar para o Laravel
            fetch('/login', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => {
                console.log('Response status:', response.status); // Debug
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data); // Debug
                if (data.success) {
                    showToast('Login realizado com sucesso!', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro completo:', error);
                showToast('Erro ao fazer login. Tente novamente.', 'error');
            });
        }
        
        // Adicionar apenas UM event listener
        freshForm.addEventListener('submit', handleLogin);
    }
}