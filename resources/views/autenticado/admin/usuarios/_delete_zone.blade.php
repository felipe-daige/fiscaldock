@php
    $deleteUsuario = $deleteUsuario ?? $usuario;
    $deletePodeExcluir = (bool) ($deletePodeExcluir ?? false);
    $deleteClasses = $deleteClasses ?? '';
    $deleteNome = trim(($deleteUsuario->name ?? '').' '.($deleteUsuario->sobrenome ?? '')) ?: 'Usuário #'.$deleteUsuario->id;
    $deleteFormId = 'admin-delete-user-form-'.$deleteUsuario->id;
    $deleteModalId = 'admin-delete-user-modal-'.$deleteUsuario->id;
    $deleteTitleId = 'admin-delete-user-title-'.$deleteUsuario->id;
    $deleteDescId = 'admin-delete-user-desc-'.$deleteUsuario->id;
@endphp

<div class="rounded border border-gray-300 overflow-hidden {{ $deleteClasses }}">
    <div class="px-4 py-3 border-b border-gray-200" style="background-color:#fff7ed">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#b91c1c">Zona de perigo</span>
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Ação administrativa</span>
                </div>
                <h2 class="text-sm font-bold text-gray-900 mt-2">Excluir usuário</h2>
                <p class="text-[12px] text-gray-600 mt-1">
                    Anonimiza dados pessoais, bloqueia o login e cancela a assinatura local. Documentos e dados fiscais permanecem preservados por retenção legal.
                </p>
                @if(! $deletePodeExcluir)
                    <p class="text-[12px] font-semibold mt-2" style="color:#b91c1c">Indisponível para a própria conta, administradores ou usuários já anonimizados.</p>
                @endif
            </div>
            <div class="md:text-right text-[12px] text-gray-600 min-w-0">
                <p class="font-mono font-semibold text-gray-900">ID #{{ $deleteUsuario->id }}</p>
                <p class="truncate">{{ $deleteNome }}</p>
                <p class="truncate">{{ $deleteUsuario->email }}</p>
            </div>
        </div>
    </div>

    <form
        id="{{ $deleteFormId }}"
        method="POST"
        action="{{ route('app.admin.usuarios.destroy', $deleteUsuario->id) }}"
        class="p-4 grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_180px] gap-3 bg-white"
        data-admin-delete-form
    >
        @csrf
        @method('DELETE')

        <div>
            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Motivo obrigatório</label>
            <input
                type="text"
                name="motivo"
                class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded"
                placeholder="Ex.: solicitação LGPD, conta duplicada..."
                required
                @disabled(! $deletePodeExcluir)
            >
        </div>

        <div class="flex lg:items-end">
            <button
                type="button"
                data-admin-delete-open
                data-delete-modal="{{ $deleteModalId }}"
                @disabled(! $deletePodeExcluir)
                class="w-full text-white text-[12px] font-semibold px-3 py-2.5 rounded disabled:opacity-40 disabled:cursor-not-allowed hover:opacity-90"
                style="background-color:#b91c1c"
            >
                Excluir usuário
            </button>
        </div>

        <div
            id="{{ $deleteModalId }}"
            class="fixed inset-0 z-[80] hidden items-center justify-center p-2 sm:px-4 sm:py-6"
            style="background-color:rgba(17,24,39,.62)"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $deleteTitleId }}"
            aria-describedby="{{ $deleteDescId }}"
            data-admin-delete-modal
        >
            <div class="admin-modal-panel w-full max-w-xl max-h-[calc(100dvh-1rem)] rounded border border-gray-300 bg-white shadow-2xl overflow-hidden flex flex-col">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-200" style="background-color:#fff7ed">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white font-bold" style="background-color:#b91c1c">
                            !
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wide" style="color:#b91c1c">Confirmação irreversível</p>
                            <h3 id="{{ $deleteTitleId }}" class="mt-1 text-base font-bold text-gray-900">Anonimizar este usuário?</h3>
                            <p id="{{ $deleteDescId }}" class="mt-1 text-[13px] text-gray-700">
                                Esta ação bloqueia o acesso de <strong>{{ $deleteNome }}</strong>, remove dados pessoais da conta e cancela a assinatura local.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-4 sm:px-5 py-4 space-y-4 overflow-y-auto">
                    <div class="rounded border border-gray-200 bg-gray-50 p-3 text-[12px] text-gray-700">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">ID</p>
                                <p class="mt-0.5 font-mono font-semibold text-gray-900">#{{ $deleteUsuario->id }}</p>
                            </div>
                            <div class="sm:col-span-2 min-w-0">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Conta</p>
                                <p class="mt-0.5 truncate font-semibold text-gray-900">{{ $deleteNome }}</p>
                                <p class="truncate text-gray-500">{{ $deleteUsuario->email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-[12px]">
                        <div class="rounded border border-gray-200 p-3">
                            <p class="font-bold text-gray-900">O que será alterado</p>
                            <ul class="mt-2 space-y-1 text-gray-600">
                                <li>Dados pessoais serão anonimizados.</li>
                                <li>Login e sessões futuras ficam bloqueados.</li>
                                <li>Assinatura local será marcada como cancelada.</li>
                            </ul>
                        </div>
                        <div class="rounded border border-gray-200 p-3">
                            <p class="font-bold text-gray-900">O que permanece</p>
                            <ul class="mt-2 space-y-1 text-gray-600">
                                <li>Documentos e dados fiscais seguem retidos.</li>
                                <li>A trilha administrativa registra o motivo.</li>
                                <li>Não há sincronização automática com Mercado Pago.</li>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Digite ANONIMIZAR para confirmar</label>
                        <input
                            type="text"
                            name="confirmacao"
                            class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded font-mono tracking-wide"
                            placeholder="ANONIMIZAR"
                            autocomplete="off"
                            pattern="ANONIMIZAR"
                            title="Digite ANONIMIZAR em letras maiúsculas."
                            required
                            data-admin-delete-confirm
                            disabled
                        >
                    </div>
                </div>

                <div class="px-4 sm:px-5 py-3 sm:py-4 border-t border-gray-200 bg-gray-50 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <button
                        type="button"
                        class="px-4 py-2.5 rounded border border-gray-300 bg-white text-[12px] font-bold uppercase tracking-wide text-gray-700 hover:bg-gray-100"
                        data-admin-delete-close
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2.5 rounded text-white text-[12px] font-bold uppercase tracking-wide disabled:opacity-40 disabled:cursor-not-allowed hover:opacity-90"
                        style="background-color:#b91c1c"
                        data-admin-delete-submit
                        disabled
                    >
                        Anonimizar e bloquear acesso
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    if (window.__adminDeleteUserModalBound) {
        return;
    }

    window.__adminDeleteUserModalBound = true;

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        var input = modal.querySelector('[data-admin-delete-confirm]');

        if (input) {
            input.value = '';
            input.disabled = true;
        }

        toggleSubmit(modal);
    }

    function toggleSubmit(modal) {
        if (!modal) {
            return;
        }

        var input = modal.querySelector('[data-admin-delete-confirm]');
        var submit = modal.querySelector('[data-admin-delete-submit]');

        if (submit) {
            submit.disabled = !input || input.value !== 'ANONIMIZAR';
        }
    }

    function openModal(openButton) {
        var form = openButton.closest('[data-admin-delete-form]');
        var motivo = form ? form.querySelector('input[name="motivo"]') : null;

        if (motivo && !motivo.checkValidity()) {
            motivo.reportValidity();
            return;
        }

        var modal = document.getElementById(openButton.getAttribute('data-delete-modal'));
        var input = modal ? modal.querySelector('[data-admin-delete-confirm]') : null;

        if (input) {
            input.disabled = false;
            input.value = '';
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            toggleSubmit(modal);

            if (input) {
                window.setTimeout(function () {
                    input.focus();
                }, 0);
            }
        }
    }

    document.addEventListener('click', function (event) {
        var openButton = event.target.closest('[data-admin-delete-open]');
        var closeButton = event.target.closest('[data-admin-delete-close]');
        var modalBackdrop = event.target.matches('[data-admin-delete-modal]') ? event.target : null;

        if (openButton) {
            openModal(openButton);
        }

        if (closeButton || modalBackdrop) {
            closeModal(closeButton ? closeButton.closest('[data-admin-delete-modal]') : modalBackdrop);
        }
    });

    document.addEventListener('input', function (event) {
        if (!event.target.matches('[data-admin-delete-confirm]')) {
            return;
        }

        toggleSubmit(event.target.closest('[data-admin-delete-modal]'));
    });

    document.addEventListener('submit', function (event) {
        if (!event.target.matches('[data-admin-delete-form]')) {
            return;
        }

        var form = event.target;
        var modal = form.querySelector('[data-admin-delete-modal]');
        var input = modal ? modal.querySelector('[data-admin-delete-confirm]') : null;
        var openButton = form.querySelector('[data-admin-delete-open]');
        var modalAberto = modal && !modal.classList.contains('hidden');

        if (!modalAberto) {
            event.preventDefault();

            if (openButton) {
                openModal(openButton);
            }

            return;
        }

        if (!input || input.value !== 'ANONIMIZAR') {
            event.preventDefault();

            if (input) {
                input.reportValidity();
            }
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        document.querySelectorAll('[data-admin-delete-modal]:not(.hidden)').forEach(closeModal);
    });
})();
</script>
