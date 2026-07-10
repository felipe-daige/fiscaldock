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
        function supportConfig(data) {
            const cfg = window.systemSupportConfig || {};
            return {
                url: data?.support_url || cfg.whatsappUrl || 'https://wa.me/5567999844366',
                label: data?.support_label || 'Falar no WhatsApp'
            };
        }

        function shouldShowSupport(message, data, status) {
            const texto = String(message || '').toLowerCase();
            return Boolean(data?.support)
                || status === 403
                || status === 419
                || texto.includes('suspensa')
                || texto.includes('suporte')
                || texto.includes('sessão');
        }

        function showAlert(message, options = {}) {
            const texto = message || 'Não foi possível entrar. Tente novamente.';
            if (alertBox) {
                alertBox.innerHTML = '';

                const p = document.createElement('p');
                p.textContent = texto;
                alertBox.appendChild(p);

                if (shouldShowSupport(texto, options.data, options.status)) {
                    const cfg = supportConfig(options.data);
                    const link = document.createElement('a');
                    link.href = cfg.url;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.textContent = cfg.label;
                    link.className = 'mt-3 inline-flex items-center justify-center rounded text-white text-[12px] font-semibold px-3 py-2 hover:opacity-90';
                    link.style.backgroundColor = '#047857';
                    alertBox.appendChild(link);
                }

                alertBox.classList.remove('hidden');
            } else if (typeof showToast === 'function') {
                showToast(texto, 'error');
            }
        }

        function clearAlert() {
            if (alertBox) {
                alertBox.classList.add('hidden');
                alertBox.innerHTML = '';
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

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        }

        function setCsrfToken(token) {
            if (!token) return;

            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                meta.setAttribute('content', token);
            }

            const input = freshForm.querySelector('input[name="_token"]');
            if (input) {
                input.value = token;
            }
        }

        async function refreshCsrfToken(data) {
            if (data?.csrf_token) {
                setCsrfToken(data.csrf_token);
                return data.csrf_token;
            }

            try {
                const response = await fetch('/api/csrf-token', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    return '';
                }

                const payload = await response.json();
                if (payload?.csrf_token) {
                    setCsrfToken(payload.csrf_token);
                    return payload.csrf_token;
                }
            } catch (error) {
                console.error('Erro ao atualizar CSRF:', error);
            }

            return '';
        }

        async function submitLoginRequest(form) {
            const token = getCsrfToken();

            if (!token) {
                throw new Error('csrf-missing');
            }

            const formData = new FormData(form);
            formData.set('_token', token);

            const response = await fetch('/login', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            return { data, status: response.status };
        }

        // Função interna para o handler do login
        async function handleLogin(e) {
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
            const token = getCsrfToken();

            if (!token) {
                showAlert('Sessão expirada. Recarregue a página e tente novamente.');
                resetButton();
                return;
            }

            try {
                let { data, status } = await submitLoginRequest(e.target);

                // O token pode ter rotacionado quando a sessão foi invalidada. Nesse caso,
                // atualiza o CSRF e reenvia uma vez, sem obrigar o usuário a dar F5.
                if (status === 419) {
                    const freshToken = await refreshCsrfToken(data);
                    if (freshToken) {
                        ({ data, status } = await submitLoginRequest(e.target));
                    }
                }

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

                if (status === 419) {
                    mensagem = 'Sua sessão de login expirou antes do envio. Tente novamente. Se continuar acontecendo, fale com o suporte pelo WhatsApp.';
                }

                showAlert(mensagem, { data, status });
                resetButton();
            } catch (error) {
                console.error('Erro completo:', error);
                showAlert('Erro ao fazer login. Tente novamente. Se continuar acontecendo, fale com o suporte pelo WhatsApp.', { status: 419 });
                resetButton();
            }
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
