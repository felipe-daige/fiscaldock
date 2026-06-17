// Função específica para a página de login
function initLogin() {
    const loginForm = document.getElementById('login-form');

    if (loginForm) {
        // Remover listeners antigos se existirem
        const newForm = loginForm.cloneNode(true);
        loginForm.parentNode.replaceChild(newForm, loginForm);

        // Agora pegar a referência do novo formulário
        const freshForm = document.getElementById('login-form');
        const submitBtn = document.getElementById('login-submit-btn');

        if (!freshForm || !submitBtn) return;

        // Guardar o HTML original do botão
        const originalButtonHTML = submitBtn.innerHTML;
        const alertBox = document.getElementById('login-alert');

        // Erro de login (credencial inválida ou formato) aparece inline neste
        // banner, abaixo do cabeçalho — nunca em modal/toast.
        function showAlert(message) {
            const texto = message || 'Não foi possível entrar. Tente novamente.';
            if (alertBox) {
                alertBox.textContent = texto;
                alertBox.classList.remove('hidden');
            } else if (typeof showToast === 'function') {
                showToast(texto, 'error');
            }
        }

        function clearAlert() {
            if (alertBox) {
                alertBox.classList.add('hidden');
                alertBox.textContent = '';
            }
        }

        // Função para resetar o botão ao estado original
        function resetButton() {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalButtonHTML;
                submitBtn.blur(); // Remove o foco para evitar estado visual bugado
            }
        }

        // Função interna para o handler do login
        function handleLogin(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!submitBtn) return;

            // Verificar se já está processando
            if (submitBtn.disabled) {
                return;
            }

            clearAlert();

            // Desabilitar botão durante o envio
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Entrando...</span>';

            // 🔥 BUSCAR TOKEN CSRF MAIS RECENTE A CADA TENTATIVA
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!token) {
                showAlert('Sessão expirada. Recarregue a página e tente novamente.');
                resetButton();
                return;
            }

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
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof showToast === 'function') {
                        showToast('Login realizado com sucesso!', 'success');
                    }
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1200);
                    return;
                }

                // Erros de formato (422) trazem `errors`; credencial inválida (401)
                // traz só `message`. Em ambos, exibe inline no banner.
                let mensagem = data.message;
                if (data.errors) {
                    const grupo = Object.values(data.errors)[0];
                    mensagem = Array.isArray(grupo) ? grupo[0] : (data.message || mensagem);
                }
                showAlert(mensagem);
                resetButton();
            })
            .catch(error => {
                console.error('Erro completo:', error);
                showAlert('Erro ao fazer login. Tente novamente.');
                resetButton();
            });
        }

        // Adicionar apenas UM event listener
        freshForm.addEventListener('submit', handleLogin, { once: false });

        // Botão "ver senha": alterna password/text e troca o ícone (olho/olho cortado).
        freshForm.querySelectorAll('.senha-toggle').forEach((btn) => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                if (!input) return;
                const revelar = input.type === 'password';
                input.type = revelar ? 'text' : 'password';
                btn.setAttribute('aria-label', revelar ? 'Ocultar senha' : 'Mostrar senha');
                const eye = btn.querySelector('.icon-eye');
                const eyeOff = btn.querySelector('.icon-eye-off');
                if (eye && eyeOff) {
                    eye.classList.toggle('hidden', revelar);
                    eyeOff.classList.toggle('hidden', !revelar);
                }
            });
        });

        // Limpa o alerta quando o usuário começa a corrigir as credenciais.
        freshForm.addEventListener('input', clearAlert);

        // Prevenir múltiplos cliques no botão diretamente
        submitBtn.addEventListener('click', function(e) {
            if (this.disabled) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    }
}
