(function () {
    'use strict';

    const roleHelp = {
        admin: 'Administrador recebe acesso a todos os módulos. Somente o dono pode conceder este papel.',
        operador: 'Preset operacional: painel, clientes, documentos e consultas. Os módulos podem ser personalizados.',
        leitura: 'Preset de consulta: nenhuma alteração de dados é permitida, mesmo nos módulos liberados.',
    };

    function readPresets() {
        const source = document.getElementById('team-role-presets');
        if (!source) {
            return {};
        }

        try {
            return JSON.parse(source.textContent || '{}');
        } catch (error) {
            console.error('[Equipe] Não foi possível carregar os presets de permissões.', error);
            return {};
        }
    }

    function permissionCheckboxes(form) {
        return Array.from(form.querySelectorAll('[data-team-permission]'));
    }

    function syncRoleState(form, role, presets, applyPreset) {
        const rolePreset = presets[role] || {};
        const isAdmin = role === 'admin';

        permissionCheckboxes(form).forEach((checkbox) => {
            if (applyPreset || isAdmin) {
                checkbox.checked = Boolean(rolePreset[checkbox.dataset.teamPermission]);
            }

            checkbox.disabled = isAdmin;
            const label = checkbox.closest('label');
            if (label) {
                label.style.opacity = isAdmin ? '0.65' : '';
            }
        });

        const help = form.querySelector('[data-team-role-help]');
        if (help) {
            help.textContent = roleHelp[role] || 'Selecione os módulos que esta pessoa poderá acessar.';
        }
    }

    function bindPermissionForm(form, presets) {
        if (form.dataset.teamPermissionsBound === '1') {
            return;
        }

        const roleSelect = form.querySelector('[data-team-role]');
        if (!roleSelect) {
            return;
        }

        form.dataset.teamPermissionsBound = '1';
        syncRoleState(form, roleSelect.value, presets, false);
        roleSelect.addEventListener('change', () => {
            syncRoleState(form, roleSelect.value, presets, true);
        });
    }

    function initEquipe() {
        const presets = readPresets();
        document.querySelectorAll('[data-team-permissions-form]').forEach((form) => {
            bindPermissionForm(form, presets);
        });
    }

    window.initEquipe = initEquipe;
})();
