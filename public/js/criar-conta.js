function initCriarConta() {
    const signupForm = document.getElementById('signup-form');

    if (!signupForm) {
        return;
    }

    const newForm = signupForm.cloneNode(true);
    signupForm.parentNode.replaceChild(newForm, signupForm);

    const freshForm = document.getElementById('signup-form');
    const submitBtn = document.getElementById('signup-submit-btn');

    if (!freshForm || !submitBtn) {
        return;
    }

    const originalButtonHTML = submitBtn.innerHTML;
    const alertBox = document.getElementById('signup-alert');

    function inputFor(key) {
        return freshForm.querySelector(`[name="${key}"]`);
    }

    function clearFieldError(name) {
        if (!name) {
            return;
        }
        const slot = freshForm.querySelector(`.field-error[data-error="${name}"]`);
        if (slot) {
            slot.textContent = '';
        }
        freshForm.querySelectorAll(`[name="${name}"]`).forEach((el) => {
            el.classList.remove('border-red-500');
            el.removeAttribute('data-invalid');
        });
    }

    function clearErrors() {
        if (alertBox) {
            alertBox.classList.add('hidden');
            alertBox.textContent = '';
        }
        freshForm.querySelectorAll('.field-error').forEach((el) => {
            el.textContent = '';
        });
        freshForm.querySelectorAll('[data-invalid]').forEach((el) => {
            el.classList.remove('border-red-500');
            el.removeAttribute('data-invalid');
        });
    }

    // Mostra cada erro inline, abaixo do campo correspondente. Erros sem campo
    // conhecido caem no alerta de topo. Foca/rola até o primeiro problema.
    function showErrors(errors) {
        let first = null;

        Object.keys(errors).forEach((key) => {
            const value = errors[key];
            const msg = Array.isArray(value) ? value[0] : value;
            const slot = freshForm.querySelector(`.field-error[data-error="${key}"]`);
            const input = inputFor(key);

            if (slot) {
                slot.textContent = msg;
            } else if (alertBox) {
                alertBox.textContent = msg;
                alertBox.classList.remove('hidden');
            }

            if (input && input.type !== 'radio' && input.type !== 'checkbox') {
                input.classList.add('border-red-500');
                input.setAttribute('data-invalid', '');
            }

            if (!first) {
                first = input || slot;
            }
        });

        if (first) {
            if (typeof first.scrollIntoView === 'function') {
                first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            if (typeof first.focus === 'function') {
                try {
                    first.focus({ preventScroll: true });
                } catch (e) {
                    /* noop */
                }
            }
        }
    }

    function showGenericError(message) {
        const texto = message || 'Erro ao criar a conta. Tente novamente.';
        if (alertBox) {
            alertBox.textContent = texto;
            alertBox.classList.remove('hidden');
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else if (typeof showToast === 'function') {
            showToast(texto, 'error');
        }
    }

    // Botão "ver senha": alterna entre password/text e troca o ícone (olho/olho cortado).
    freshForm.querySelectorAll('.senha-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            if (!input) {
                return;
            }
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

    // Limpa o erro do campo assim que o usuário começa a corrigi-lo.
    const limparAoEditar = (e) => clearFieldError(e.target && e.target.name);
    freshForm.addEventListener('input', limparAoEditar);
    freshForm.addEventListener('change', limparAoEditar);

    function applyMasks() {
        if (typeof $ === 'undefined') {
            return;
        }

        const documentoBehavior = function (val) {
            return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
        };

        const documentoOptions = {
            onKeyPress: function (val, e, field, options) {
                field.mask(documentoBehavior.apply({}, arguments), options);
            },
        };

        $('#documento').mask(documentoBehavior, documentoOptions);
        $('#telefone').mask('(00) 00000-0000');
    }

    function resetButton() {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalButtonHTML;
    }

    applyMasks();

    freshForm.addEventListener('submit', function (e) {
        e.preventDefault();

        if (submitBtn.disabled) {
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (!token) {
            showGenericError('Sessão expirada. Recarregue a página e tente novamente.');
            return;
        }

        clearErrors();
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Criando conta...</span>';

        fetch('/criar-conta', {
            method: 'POST',
            body: new FormData(freshForm),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    if (typeof showToast === 'function') {
                        showToast(data.message, 'success');
                    }
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                    return;
                }

                if (data.errors && Object.keys(data.errors).length) {
                    showErrors(data.errors);
                } else {
                    showGenericError(data.message);
                }

                resetButton();
            })
            .catch(() => {
                showGenericError('Erro ao criar a conta. Tente novamente.');
                resetButton();
            });
    });
}
