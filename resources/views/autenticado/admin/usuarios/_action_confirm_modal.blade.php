@php
    $adminActionConfirmMode = $adminActionConfirmMode ?? 'fixed';
    $adminActionConfirmId = $adminActionConfirmId ?? 'admin-action-confirm-'.uniqid();
    $adminActionConfirmTitleId = $adminActionConfirmId.'-title';
    $adminActionConfirmDescId = $adminActionConfirmId.'-desc';
    $adminActionConfirmClasses = $adminActionConfirmMode === 'absolute'
        ? 'absolute inset-0 hidden items-center justify-center p-2 sm:px-4 sm:py-6'
        : 'fixed inset-0 hidden items-center justify-center p-2 sm:px-4 sm:py-6';
@endphp

<div
    id="{{ $adminActionConfirmId }}"
    class="{{ $adminActionConfirmClasses }}"
    style="z-index:90; background-color:rgba(17,24,39,.62)"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $adminActionConfirmTitleId }}"
    aria-describedby="{{ $adminActionConfirmDescId }}"
    data-admin-action-confirm
>
    <div class="admin-modal-panel w-full max-w-xl max-h-[calc(100dvh-1rem)] rounded border border-gray-300 bg-white shadow-2xl overflow-hidden flex flex-col">
        <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-200" style="background-color:#f8fafc">
            <div class="flex items-start gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white font-bold"
                    style="background-color:#0b1f3a"
                    data-admin-action-confirm-icon
                >
                    !
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wide" style="color:#475569" data-admin-action-confirm-kicker>Ação administrativa</p>
                    <h3 id="{{ $adminActionConfirmTitleId }}" class="mt-1 text-base font-bold text-gray-900" data-admin-action-confirm-title>Confirmar ação?</h3>
                    <p id="{{ $adminActionConfirmDescId }}" class="mt-1 text-[13px] text-gray-700" data-admin-action-confirm-desc>
                        Revise os dados antes de aplicar a alteração.
                    </p>
                </div>
            </div>
        </div>

        <div class="px-4 sm:px-5 py-4 space-y-4 overflow-y-auto">
            <div class="rounded border border-gray-200 bg-gray-50 p-3 text-[12px] text-gray-700">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">ID</p>
                        <p class="mt-0.5 font-mono font-semibold text-gray-900" data-admin-action-confirm-user-id>—</p>
                    </div>
                    <div class="sm:col-span-2 min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Conta</p>
                        <p class="mt-0.5 truncate font-semibold text-gray-900" data-admin-action-confirm-user-name>—</p>
                        <p class="truncate text-gray-500" data-admin-action-confirm-user-email>—</p>
                    </div>
                </div>
            </div>

            <div class="rounded border border-gray-200 p-3">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Resumo da alteração</p>
                <dl class="mt-2 space-y-2 text-[12px]" data-admin-action-confirm-summary></dl>
            </div>

            <div
                class="rounded border p-3 text-[12px] hidden"
                style="border-color:#f59e0b; background-color:#fffbeb; color:#92400e"
                data-admin-action-confirm-warning
            ></div>
        </div>

        <div class="px-4 sm:px-5 py-3 sm:py-4 border-t border-gray-200 bg-gray-50 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
            <button
                type="button"
                class="px-4 py-2.5 rounded border border-gray-300 bg-white text-[12px] font-bold uppercase tracking-wide text-gray-700 hover:bg-gray-100"
                data-admin-action-confirm-close
            >
                Revisar campos
            </button>
            <button
                type="button"
                class="px-4 py-2.5 rounded text-white text-[12px] font-bold uppercase tracking-wide hover:opacity-90 disabled:opacity-40 disabled:cursor-wait"
                style="background-color:#0b1f3a"
                data-admin-action-confirm-submit
            >
                Confirmar
            </button>
        </div>
    </div>
</div>
