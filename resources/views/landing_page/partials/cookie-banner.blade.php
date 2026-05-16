{{-- Banner de cookies + modal de configurações. Comportamento controlado por public/js/cookie-banner.js --}}
<div id="fd-cookie-banner"
     data-component="cookie-banner"
     class="fixed z-50 hidden bottom-4 left-4 right-4 sm:right-auto sm:max-w-sm bg-white border border-gray-300 rounded shadow-lg p-4 text-sm text-gray-700">
    <p class="font-semibold text-gray-900 mb-1">Sobre cookies</p>
    <p class="text-[13px] leading-relaxed text-gray-600">
        Usamos cookies essenciais para operar a plataforma. Com seu consentimento, podemos usar cookies opcionais para análise e melhoria. Você pode mudar sua escolha a qualquer momento.
        <a href="{{ route('cookies') }}" class="hover:underline" style="color: #1e4fa0">Política de Cookies</a>.
    </p>
    <div class="mt-3 flex flex-col sm:flex-row gap-2">
        <button type="button" data-action="accept-all"
                class="flex-1 rounded bg-gray-800 text-white text-[13px] font-medium px-3 py-2 hover:bg-gray-700">
            Aceitar todos
        </button>
        <button type="button" data-action="reject-optional"
                class="flex-1 rounded border border-gray-300 bg-white text-gray-700 text-[13px] font-medium px-3 py-2 hover:bg-gray-50">
            Recusar opcionais
        </button>
        <button type="button" data-action="open-cookie-settings"
                class="flex-1 rounded border border-gray-300 bg-white text-gray-700 text-[13px] font-medium px-3 py-2 hover:bg-gray-50">
            Configurar
        </button>
    </div>
</div>

<div id="fd-cookie-modal"
     data-component="cookie-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
    <div role="dialog" aria-modal="true" aria-labelledby="fd-cookie-modal-title"
         class="bg-white rounded border border-gray-300 max-w-md w-full p-5 text-sm text-gray-700">
        <p id="fd-cookie-modal-title" class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">FiscalDock</p>
        <h2 class="text-base font-bold text-gray-900 mt-1">Preferências de cookies</h2>
        <p class="text-[13px] text-gray-600 mt-1">Escolha quais categorias opcionais aceitar. Cookies necessários são sempre ativos.</p>

        <div class="mt-4 space-y-3">
            <label class="flex items-start gap-2">
                <input type="checkbox" checked disabled class="mt-0.5 h-4 w-4">
                <span>
                    <span class="block font-semibold text-gray-900">Necessários</span>
                    <span class="block text-[12px] text-gray-500">Sessão, segurança e armazenamento da sua escolha de cookies.</span>
                </span>
            </label>
            <label class="flex items-start gap-2">
                <input type="checkbox" name="fd-consent-analise" data-category="analise" class="mt-0.5 h-4 w-4">
                <span>
                    <span class="block font-semibold text-gray-900">Análise</span>
                    <span class="block text-[12px] text-gray-500">Medem uso da plataforma para melhorar a experiência. Atualmente: nenhum cookie ativo nessa categoria.</span>
                </span>
            </label>
            <label class="flex items-start gap-2">
                <input type="checkbox" name="fd-consent-marketing" data-category="marketing" class="mt-0.5 h-4 w-4">
                <span>
                    <span class="block font-semibold text-gray-900">Marketing</span>
                    <span class="block text-[12px] text-gray-500">Personalizam comunicações e medem campanhas. Atualmente: nenhum cookie ativo nessa categoria.</span>
                </span>
            </label>
        </div>

        <div class="mt-5 flex flex-col sm:flex-row gap-2">
            <button type="button" data-action="save-preferences"
                    class="flex-1 rounded bg-gray-800 text-white text-[13px] font-medium px-3 py-2 hover:bg-gray-700">
                Salvar preferências
            </button>
            <button type="button" data-action="close-cookie-settings"
                    class="rounded border border-gray-300 bg-white text-gray-700 text-[13px] font-medium px-3 py-2 hover:bg-gray-50">
                Cancelar
            </button>
        </div>
    </div>
</div>
