{{-- Base de tracking (Meta Pixel + Google gtag = GA4 + Google Ads).
     Fonte única: config/tracking.php. Env-gated (nada renderiza sem ID) e
     consent-gated (nada CARREGA sem fd_cookie_consent === 'accepted' — LGPD).
     Define window.fdTracking { load, track }. Ver docs/marketing/instrumentacao-tracking.md --}}
@php
    $trackingEnabled = config('tracking.enabled')
        && (config('tracking.meta_pixel_id') || config('tracking.ga4_id') || config('tracking.google_ads_id'));

    // @json() NÃO aceita array literal aqui: o Blade quebra a expressão nas vírgulas
    // de topo (tenta ler $options/$depth) e trunca o array. Montar em variável.
    $trackingCfg = [
        'metaPixelId' => (string) config('tracking.meta_pixel_id'),
        'ga4Id' => (string) config('tracking.ga4_id'),
        'googleAdsId' => (string) config('tracking.google_ads_id'),
        'adsLabels' => array_filter([
            'trial_start' => config('tracking.ads_labels.trial_start'),
            'purchase' => config('tracking.ads_labels.purchase'),
        ]),
    ];
@endphp
@if($trackingEnabled)
<script>
window.fdTracking = (function () {
    var cfg = @json($trackingCfg);

    var CONSENT_KEY = 'fd_cookie_consent';
    var META_MAP = { trial_start: 'StartTrial', purchase: 'Purchase', lead: 'Lead' };
    var loaded = false;
    var queue = [];

    function consented() {
        try { return window.localStorage.getItem(CONSENT_KEY) === 'accepted'; }
        catch (e) { return false; }
    }

    function loadMeta() {
        if (!cfg.metaPixelId) return;
        !function (f, b, e, v, n, t, s) {
            if (f.fbq) return; n = f.fbq = function () {
                n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
            };
            if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = [];
            t = b.createElement(e); t.async = !0; t.src = v;
            s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s);
        }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
        window.fbq('init', cfg.metaPixelId);
        window.fbq('track', 'PageView');
    }

    function loadGoogle() {
        var firstId = cfg.ga4Id || cfg.googleAdsId;
        if (!firstId) return;
        var s = document.createElement('script');
        s.async = true;
        s.src = 'https://www.googletagmanager.com/gtag/js?id=' + firstId;
        document.head.appendChild(s);
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        if (cfg.ga4Id) window.gtag('config', cfg.ga4Id);
        if (cfg.googleAdsId) window.gtag('config', cfg.googleAdsId);
    }

    function fire(name, params) {
        params = params || {};
        var money = (params.value != null);

        if (window.fbq && META_MAP[name]) {
            window.fbq('track', META_MAP[name], money ? { value: params.value, currency: params.currency || 'BRL' } : {});
        }
        if (window.gtag && cfg.ga4Id) {
            window.gtag('event', name, money ? { value: params.value, currency: params.currency || 'BRL' } : {});
        }
        if (window.gtag && cfg.googleAdsId && cfg.adsLabels[name]) {
            var conv = { send_to: cfg.googleAdsId + '/' + cfg.adsLabels[name] };
            if (money) { conv.value = params.value; conv.currency = params.currency || 'BRL'; }
            window.gtag('event', 'conversion', conv);
        }
    }

    function load() {
        if (loaded || !consented()) return;
        loaded = true;
        loadMeta();
        loadGoogle();
        queue.splice(0).forEach(function (ev) { fire(ev.name, ev.params); });
    }

    function track(name, params) {
        if (!consented()) return; // LGPD: sem consentimento, não rastreia nem enfileira
        if (!loaded) { queue.push({ name: name, params: params }); load(); return; }
        fire(name, params);
    }

    if (document.readyState !== 'loading') load();
    else document.addEventListener('DOMContentLoaded', load);
    window.addEventListener('fd:cookie-consent-accepted', load);

    return { load: load, track: track };
})();
</script>
@endif
