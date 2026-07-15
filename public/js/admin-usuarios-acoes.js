(function () {
    if (window.__adminUserActionConfirmV2Bound) {
        return;
    }

    window.__adminUserActionConfirmV2Bound = true;

    function brl(value) {
        return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function parseDecimal(value) {
        var normalizado = String(value || '').trim().replace(',', '.');
        var parsed = parseFloat(normalizado);

        return isNaN(parsed) ? 0 : parsed;
    }

    function field(form, name) {
        return form ? form.querySelector('[name="' + name + '"]') : null;
    }

    function fieldValue(form, name, fallback) {
        var el = field(form, name);
        var value = el ? el.value : '';

        return value === '' || value === null || typeof value === 'undefined' ? fallback : value;
    }

    function selectedText(form, name, fallback) {
        var el = field(form, name);

        if (!el || !el.options || el.selectedIndex < 0) {
            return fallback;
        }

        return el.options[el.selectedIndex].text.trim() || fallback;
    }

    function findSubmitButton(form) {
        return form.querySelector('button[type="submit"], button:not([type]), input[type="submit"]');
    }

    function showInlineError(form, message, invalidField) {
        var error = form.querySelector('[data-admin-action-inline-error]');
        var submit = findSubmitButton(form);

        if (!error) {
            error = document.createElement('p');
            error.setAttribute('data-admin-action-inline-error', '');
            error.className = 'text-[11px] font-semibold';
            error.style.color = '#b91c1c';

            if (submit && submit.parentNode === form) {
                form.insertBefore(error, submit);
            } else {
                form.appendChild(error);
            }
        }

        error.textContent = message;
        error.classList.remove('hidden');

        if (invalidField) {
            invalidField.style.borderColor = '#b91c1c';
            invalidField.focus({ preventScroll: false });

            if (typeof invalidField.reportValidity === 'function') {
                invalidField.reportValidity();
            }
        }
    }

    function clearInlineError(form) {
        var error = form.querySelector('[data-admin-action-inline-error]');

        if (error) {
            error.textContent = '';
            error.classList.add('hidden');
        }

        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.style.borderColor = '';
        });
    }

    function firstInvalidField(form) {
        return Array.prototype.find.call(
            form.querySelectorAll('input, select, textarea'),
            function (el) {
                return typeof el.checkValidity === 'function' && !el.checkValidity();
            }
        );
    }

    function appendRow(summary, label, value, strong) {
        var row = document.createElement('div');
        row.className = 'flex justify-between gap-3';

        var dt = document.createElement('dt');
        dt.className = 'text-gray-500';
        dt.textContent = label;

        var dd = document.createElement('dd');
        dd.className = (strong ? 'font-semibold text-gray-900' : 'text-gray-800') + ' text-right';
        dd.textContent = value || '-';

        row.appendChild(dt);
        row.appendChild(dd);
        summary.appendChild(row);
    }

    function buildPlanData(form) {
        var status = fieldValue(form, 'status', 'ativa');
        var plano = selectedText(form, 'subscription_plan_id', 'Sem assinatura local (Free)');
        var cap = fieldValue(form, 'limite_consumo_automatico', 'Default do plano');

        return {
            title: 'Confirmar mudança de plano?',
            kicker: 'Plano e assinatura',
            description: 'A assinatura local deste usuário será atualizada com os dados abaixo.',
            button: 'Salvar plano',
            color: '#0b1f3a',
            rows: [
                ['Plano atual', form.getAttribute('data-admin-action-current-plan') || '-'],
                ['Novo plano', plano, true],
                ['Status', status, true],
                ['Ciclo', fieldValue(form, 'ciclo', 'mensal')],
                ['Bucket incluso', 'R$ ' + fieldValue(form, 'creditos_inclusos_saldo', '0')],
                ['Cap auto', cap === 'Default do plano' ? cap : 'R$ ' + cap],
                ['Motivo', fieldValue(form, 'motivo', '-')],
            ],
            warning: status !== 'ativa'
                ? 'Status diferente de ativa não libera recursos pagos. Esta ação não sincroniza cobrança no Mercado Pago.'
                : 'Esta ação altera apenas a assinatura local. Mercado Pago não é sincronizado automaticamente.',
        };
    }

    function buildCreditData(form) {
        var saldoAtual = parseFloat(form.getAttribute('data-saldo')) || 0;
        var movimento = parseDecimal(fieldValue(form, 'valor', '0'));
        var novoSaldo = saldoAtual + movimento;
        var novoSaldoVisivel = Math.max(0, novoSaldo);
        var operacao = movimento < 0 ? 'debitar saldo' : 'adicionar saldo';

        return {
            title: 'Confirmar ajuste de saldo?',
            kicker: 'Saldo manual',
            description: 'O movimento será registrado na trilha administrativa do usuário.',
            button: movimento < 0 ? 'Debitar saldo' : 'Adicionar saldo',
            color: movimento < 0 ? '#b91c1c' : '#1d4ed8',
            rows: [
                ['Saldo atual', brl(saldoAtual)],
                ['Movimento', brl(movimento), true],
                ['Novo saldo', brl(novoSaldoVisivel), true],
                ['Operação', operacao],
                ['Motivo', fieldValue(form, 'motivo', '-')],
            ],
            warning: novoSaldo < 0
                ? 'O movimento informado ultrapassa o saldo atual. O backend pode recusar a operação ou ajustar conforme a regra vigente.'
                : (movimento < 0 ? 'Valor negativo debita saldo imediatamente.' : 'Valor positivo adiciona saldo manualmente.'),
        };
    }

    function findModal(form) {
        var scope = form.closest('[data-admin-action-scope]');

        return (scope ? scope.querySelector('[data-admin-action-confirm]') : null)
            || document.querySelector('[data-admin-action-confirm]');
    }

    function setText(modal, selector, value) {
        var el = modal.querySelector(selector);

        if (el) {
            el.textContent = value || '-';
        }
    }

    function validateForm(form) {
        clearInlineError(form);

        if (form.checkValidity()) {
            return true;
        }

        var invalidField = firstInvalidField(form);
        var label = invalidField
            ? (invalidField.closest('div') && invalidField.closest('div').querySelector('label'))
            : null;
        var labelText = label ? label.textContent.trim() : '';
        var message = labelText
            ? 'Revise o campo "' + labelText + '" antes de continuar.'
            : 'Preencha os campos obrigatórios antes de continuar.';

        showInlineError(form, message, invalidField);
        return false;
    }

    function openModal(form) {
        if (!validateForm(form)) {
            return;
        }

        var modal = findModal(form);
        var summary = modal ? modal.querySelector('[data-admin-action-confirm-summary]') : null;
        var warning = modal ? modal.querySelector('[data-admin-action-confirm-warning]') : null;
        var submit = modal ? modal.querySelector('[data-admin-action-confirm-submit]') : null;

        if (!modal || !summary || !warning || !submit) {
            submitForm(form);
            return;
        }

        var data = form.getAttribute('data-admin-action-form') === 'credit'
            ? buildCreditData(form)
            : buildPlanData(form);

        modal.__adminActionForm = form;
        summary.innerHTML = '';

        setText(modal, '[data-admin-action-confirm-kicker]', data.kicker);
        setText(modal, '[data-admin-action-confirm-title]', data.title);
        setText(modal, '[data-admin-action-confirm-desc]', data.description);
        setText(modal, '[data-admin-action-confirm-user-id]', '#' + (form.getAttribute('data-admin-action-user-id') || '-'));
        setText(modal, '[data-admin-action-confirm-user-name]', form.getAttribute('data-admin-action-user-name') || '-');
        setText(modal, '[data-admin-action-confirm-user-email]', form.getAttribute('data-admin-action-user-email') || '-');

        data.rows.forEach(function (row) {
            appendRow(summary, row[0], row[1], Boolean(row[2]));
        });

        warning.textContent = data.warning || '';
        warning.classList.toggle('hidden', !data.warning);
        submit.textContent = data.button;
        submit.style.backgroundColor = data.color;
        submit.disabled = false;

        var icon = modal.querySelector('[data-admin-action-confirm-icon]');
        if (icon) {
            icon.style.backgroundColor = data.color;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        window.setTimeout(function () {
            submit.focus();
        }, 0);
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.__adminActionForm = null;
    }

    function updateCreditPreview(form) {
        var saldo = parseFloat(form.getAttribute('data-saldo')) || 0;
        var input = form.querySelector('[data-credit-input]');
        var out = form.querySelector('[data-credit-preview]');

        if (!input || !out) {
            return;
        }

        var movReais = parseDecimal(input.value);

        if (movReais === 0) {
            out.classList.add('hidden');
            return;
        }

        var novoReais = saldo + movReais;
        out.classList.remove('hidden');
        out.style.color = novoReais < 0 ? '#b91c1c' : '#047857';
        out.innerHTML = 'Novo saldo: <strong>' + brl(Math.max(0, novoReais)) + '</strong>'
            + (novoReais < 0 ? ' - <strong>saldo negativo</strong>' : '');
    }

    function submitForm(form) {
        form.setAttribute('data-admin-action-confirmed', 'true');

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
            return;
        }

        HTMLFormElement.prototype.submit.call(form);
    }

    document.addEventListener('click', function (event) {
        var actionButton = event.target.closest('[data-admin-action-form] button[type="submit"], [data-admin-action-form] button:not([type]), [data-admin-action-form] input[type="submit"]');
        var closeButton = event.target.closest('[data-admin-action-confirm-close]');
        var submitButton = event.target.closest('[data-admin-action-confirm-submit]');
        var backdrop = event.target.matches('[data-admin-action-confirm]') ? event.target : null;

        if (actionButton) {
            event.preventDefault();
            openModal(actionButton.closest('[data-admin-action-form]'));
            return;
        }

        if (closeButton || backdrop) {
            closeModal(closeButton ? closeButton.closest('[data-admin-action-confirm]') : backdrop);
            return;
        }

        if (submitButton) {
            var modal = submitButton.closest('[data-admin-action-confirm]');
            var form = modal ? modal.__adminActionForm : null;

            if (!form) {
                closeModal(modal);
                return;
            }

            submitButton.disabled = true;
            submitForm(form);
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form.matches('[data-admin-action-form]')) {
            return;
        }

        if (form.getAttribute('data-admin-action-confirmed') === 'true') {
            form.removeAttribute('data-admin-action-confirmed');
            return;
        }

        event.preventDefault();
        openModal(form);
    });

    document.addEventListener('input', function (event) {
        var actionForm = event.target.closest('[data-admin-action-form]');
        var creditForm = event.target.closest('[data-credit-form]');

        if (actionForm) {
            clearInlineError(actionForm);
        }

        if (creditForm && event.target.matches('[data-credit-input]')) {
            updateCreditPreview(creditForm);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        var openModalEl = document.querySelector('[data-admin-action-confirm]:not(.hidden)');

        if (openModalEl) {
            event.preventDefault();
            event.stopPropagation();
            closeModal(openModalEl);
        }
    }, true);
})();
