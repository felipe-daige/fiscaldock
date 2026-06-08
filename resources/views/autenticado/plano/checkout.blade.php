@php
    $isCustomCheckout = (bool) ($pacote['is_custom'] ?? false);
    $isFeaturedCheckout = ($pacote['kind'] ?? null) === 'featured';
    $publicKey = config('services.mercadopago.public_key');
@endphp

{{-- Checkout — Mercado Pago Checkout Bricks (DANFE Modernizado) --}}
<div class="min-h-screen bg-gray-100" id="checkout-container"
     data-mp-public-key="{{ $publicKey }}"
     data-mp-endpoint="{{ route('app.pagamento.mercadopago.criar') }}"
     data-mp-pacote="{{ $pacote['slug'] }}"
     data-mp-amount="{{ number_format($pacote['preco'], 2, '.', '') }}"
     data-mp-creditos="{{ (int) $pacote['creditos'] }}">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <style>
            @keyframes ck-fade-in { from { opacity: 0; transform: translateY(20px); } }
            @keyframes ck-spin { to { transform: rotate(360deg); } }
            .ck-spinner { animation: ck-spin 0.8s linear infinite; }
            @keyframes ck-scale-in { from { opacity: 0; transform: scale(0.5); } to { opacity: 1; transform: scale(1); } }
            .ck-scale-in { animation: ck-scale-in 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        </style>

        {{-- Voltar --}}
        <a href="/app/plano" data-link class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-900 hover:underline mb-6 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Faixa Comercial
        </a>

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Finalizar Compra</h1>
            <p class="mt-1 text-xs text-gray-500">
                @if($isCustomCheckout)
                    Confirme sua recarga personalizada para adicionar créditos pré-pagos à conta.
                @else
                    Complete o pagamento para adicionar os créditos da oferta promocional à sua conta.
                @endif
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Pagamento (3/5) --}}
            <div class="lg:col-span-3 space-y-6" id="ck-form-area">

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Método de Pagamento</span>
                    </div>
                    <div class="p-5">

                        @if(empty($publicKey))
                            {{-- Gateway não configurado — fallback honesto, sem simulação --}}
                            <div class="p-4 rounded border border-gray-300 border-l-4 border-l-amber-500 bg-amber-50">
                                <p class="text-sm text-gray-800 font-semibold mb-1">Pagamento on-line indisponível</p>
                                <p class="text-xs text-gray-600">O gateway de pagamento ainda não está configurado nesta conta. Tente novamente em instantes.</p>
                            </div>
                        @else
                            {{-- Estado de carregamento do Brick --}}
                            <div id="ck-brick-loading" class="py-10 text-center">
                                <svg class="w-6 h-6 ck-spinner text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2" stroke-dasharray="31.4 31.4" stroke-linecap="round"/>
                                </svg>
                                <span class="text-[11px] text-gray-500 uppercase tracking-wide">Carregando pagamento seguro…</span>
                            </div>

                            {{-- Container do Payment Brick (cartão + Pix renderizados pelo MP) --}}
                            <div id="paymentBrick_container"></div>

                            {{-- Resultado Pix --}}
                            <div id="ck-pix-result" class="hidden text-center py-6">
                                <p class="text-sm text-gray-700 mb-3 font-semibold">Escaneie o QR Code para pagar via Pix</p>
                                <img id="ck-pix-qr" alt="QR Code Pix" class="w-48 h-48 mx-auto mb-4 border border-gray-200 rounded">
                                <div class="flex items-center gap-2 max-w-sm mx-auto">
                                    <input type="text" readonly id="ck-pix-code"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded text-xs text-gray-500 bg-gray-50 font-mono truncate">
                                    <button type="button" onclick="window._ckCopyPix && window._ckCopyPix()"
                                            class="px-3 py-2 bg-white border border-gray-300 hover:bg-gray-50 rounded text-xs font-medium text-gray-700 transition-colors whitespace-nowrap">
                                        Copiar
                                    </button>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-3">Os créditos entram automaticamente após a confirmação do pagamento.</p>
                            </div>

                            {{-- Erro --}}
                            <div id="ck-error" class="hidden mt-4 p-3 rounded border border-gray-300 border-l-4 border-l-red-500 bg-red-50">
                                <p class="text-xs text-gray-800" id="ck-error-msg">Não foi possível processar o pagamento.</p>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- Resumo do Pedido (2/5) --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded border border-gray-300 overflow-hidden lg:sticky lg:top-6">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo do Pedido</span>
                    </div>
                    <div class="p-5">

                    <div class="space-y-3 pb-4 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Origem</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $pacote['nome'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Modelo</span>
                            <span class="text-sm font-medium text-gray-700">{{ $isCustomCheckout ? 'Valor livre' : 'Oferta promocional' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Créditos</span>
                            <span class="text-sm font-medium text-gray-700">{{ number_format($pacote['creditos'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Valor pago</span>
                            <span class="text-sm font-semibold text-gray-900 font-mono">R$ {{ number_format($pacote['preco'], 2, ',', '.') }}</span>
                        </div>
                        @if($isFeaturedCheckout)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Benefício</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white"
                                      style="background-color: #047857">{{ $pacote['badge'] ?? 'Oferta' }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <span class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Total</span>
                        <span class="text-xl font-bold text-gray-900 font-mono">R$ {{ number_format($pacote['preco'], 2, ',', '.') }}</span>
                    </div>

                    <div class="mt-4 p-3 bg-white rounded border border-gray-300 border-l-4 border-l-blue-500">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-gray-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-gray-700">
                                Créditos adicionados após a confirmação do pagamento.
                                @if($isCustomCheckout)
                                    Você escolheu um valor livre acima do mínimo de R$ {{ number_format($pricing['minimum_deposit'] ?? 50, 0, ',', '.') }}.
                                @endif
                                Sua faixa comercial sobe conforme o histórico acumulado de créditos pagos.
                            </p>
                        </div>
                    </div>

                    {{-- Seguranca --}}
                    <div class="mt-4 flex items-center gap-2 text-[11px] text-gray-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Pagamento processado pelo Mercado Pago</span>
                    </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Sucesso Overlay (hidden) --}}
        <div id="ck-success-overlay" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
            <div class="bg-white rounded border border-gray-300 p-8 max-w-sm w-full text-center ck-scale-in">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background-color: #ecfdf5">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #047857">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1 uppercase tracking-wide">Pagamento Aprovado</h3>
                <p class="text-sm text-gray-600 mb-2">
                    <span class="font-semibold" style="color: #047857">{{ number_format($pacote['creditos'], 0, ',', '.') }} créditos</span> serão liberados na sua conta em instantes.
                </p>
                <p class="text-[11px] text-gray-500 mb-6">{{ $pacote['nome'] }} — R$ {{ number_format($pacote['preco'], 2, ',', '.') }}</p>
                <a href="/app/plano" data-link
                   class="inline-flex items-center justify-center w-full py-2.5 text-white rounded text-sm font-semibold transition-colors"
                   style="background-color: #047857"
                   onmouseover="this.style.backgroundColor='#065f46'"
                   onmouseout="this.style.backgroundColor='#047857'">
                    Voltar para Faixa Comercial
                </a>
            </div>
        </div>

    </div>
</div>

<script>
window.initCheckout = function() {
    var root = document.getElementById('checkout-container');
    if (!root) return;

    var PUBLIC_KEY = root.getAttribute('data-mp-public-key');
    var ENDPOINT = root.getAttribute('data-mp-endpoint');
    var PACOTE = root.getAttribute('data-mp-pacote');
    var AMOUNT = parseFloat(root.getAttribute('data-mp-amount'));
    if (!PUBLIC_KEY) return; // gateway não configurado — view já mostra o fallback

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var CSRF = csrfMeta ? csrfMeta.getAttribute('content') : '';

    function show(id) { var el = document.getElementById(id); if (el) el.classList.remove('hidden'); }
    function hide(id) { var el = document.getElementById(id); if (el) el.classList.add('hidden'); }

    function showError(msg) {
        var el = document.getElementById('ck-error-msg');
        if (el && msg) el.textContent = msg;
        show('ck-error');
    }

    window._ckCopyPix = function() {
        var code = document.getElementById('ck-pix-code');
        if (!code) return;
        code.select();
        try { document.execCommand('copy'); } catch (e) {}
        if (navigator.clipboard) { navigator.clipboard.writeText(code.value).catch(function(){}); }
    };

    function handleResult(d) {
        var poi = d.point_of_interaction;
        var pix = poi && poi.transaction_data;
        if (pix && (pix.qr_code || pix.qr_code_base64)) {
            // Pix: renderiza QR + copia-e-cola; crédito entra via webhook após pagar.
            hide('paymentBrick_container');
            if (pix.qr_code_base64) {
                document.getElementById('ck-pix-qr').src = 'data:image/png;base64,' + pix.qr_code_base64;
            }
            if (pix.qr_code) {
                document.getElementById('ck-pix-code').value = pix.qr_code;
            }
            show('ck-pix-result');
            return;
        }
        if (d.status === 'approved') {
            show('ck-success-overlay');
            return;
        }
        if (d.status === 'in_process' || d.status === 'pending' || d.status === 'authorized') {
            showError('Pagamento em processamento. Avisaremos assim que for confirmado — os créditos entram automaticamente.');
            return;
        }
        showError('Pagamento não aprovado' + (d.status_detail ? ' (' + d.status_detail + ').' : '.') + ' Tente outro meio de pagamento.');
    }

    function boot() {
        if (!window.MercadoPago) { showError('Falha ao carregar o pagamento seguro.'); return; }

        // Desmonta brick anterior (re-navegação SPA) antes de recriar.
        if (window._ckBrickController && window._ckBrickController.unmount) {
            try { window._ckBrickController.unmount(); } catch (e) {}
            window._ckBrickController = null;
        }

        var mp = new window.MercadoPago(PUBLIC_KEY, { locale: 'pt-BR' });
        var bricks = mp.bricks();

        bricks.create('payment', 'paymentBrick_container', {
            initialization: { amount: AMOUNT },
            customization: {
                paymentMethods: {
                    creditCard: 'all',
                    debitCard: 'all',
                    bankTransfer: 'all', // Pix
                    maxInstallments: 1,
                },
            },
            callbacks: {
                onReady: function () { hide('ck-brick-loading'); },
                onSubmit: function (params) {
                    var formData = (params && params.formData) ? params.formData : params;
                    hide('ck-error');
                    return new Promise(function (resolve, reject) {
                        fetch(ENDPOINT, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ pacote: PACOTE, amount: AMOUNT, payment_data: formData }),
                        }).then(function (r) {
                            return r.json().then(function (d) { return { ok: r.ok, d: d }; });
                        }).then(function (res) {
                            if (!res.ok) { showError((res.d && res.d.error) || 'Não foi possível processar o pagamento.'); reject(); return; }
                            handleResult(res.d);
                            resolve();
                        }).catch(function () { showError('Falha de conexão. Tente novamente.'); reject(); });
                    });
                },
                onError: function (error) {
                    console.error('Brick error:', error);
                    showError('Erro no formulário de pagamento.');
                },
            },
        }).then(function (controller) {
            window._ckBrickController = controller;
        }).catch(function (e) {
            console.error('Falha ao montar o Brick:', e);
            hide('ck-brick-loading');
            showError('Não foi possível carregar o pagamento.');
        });
    }

    // Carrega o SDK do Mercado Pago dinamicamente (scripts inline do SPA rodam antes
    // de externos — então não confiamos numa <script src> na view).
    if (window.MercadoPago) {
        boot();
    } else {
        var existing = document.querySelector('script[data-mp-sdk]');
        if (existing) {
            existing.addEventListener('load', boot);
        } else {
            var s = document.createElement('script');
            s.src = 'https://sdk.mercadopago.com/js/v2';
            s.setAttribute('data-mp-sdk', '1');
            s.onload = boot;
            s.onerror = function () { hide('ck-brick-loading'); showError('Falha ao carregar o pagamento seguro.'); };
            document.head.appendChild(s);
        }
    }

    // Cleanup SPA: desmonta o brick ao sair da página (evita vazamento entre navegações).
    window._cleanupFunctions = window._cleanupFunctions || [];
    window._cleanupFunctions.push(function () {
        if (window._ckBrickController && window._ckBrickController.unmount) {
            try { window._ckBrickController.unmount(); } catch (e) {}
        }
        window._ckBrickController = null;
        window._ckCopyPix = null;
    });
};

if (document.readyState !== 'loading') {
    window.initCheckout();
} else {
    document.addEventListener('DOMContentLoaded', window.initCheckout);
}
</script>
