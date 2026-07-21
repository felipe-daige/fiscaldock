<?php

return [
    // Master switch. Nada de tracking carrega no frontend com isto em false,
    // mesmo que os IDs abaixo estejam preenchidos. Default desligado.
    'enabled' => (bool) env('TRACKING_ENABLED', false),

    // Meta Pixel (fbq). Ex.: 1234567890.
    'meta_pixel_id' => env('META_PIXEL_ID'),

    // GA4 Measurement ID (analytics de fluxo). Ex.: G-XXXXXXXX.
    'ga4_id' => env('GA4_MEASUREMENT_ID'),

    // Google Ads (conversão). Ex.: AW-XXXXXXXXX. Mesmo gtag.js do GA4.
    'google_ads_id' => env('GOOGLE_ADS_ID'),

    // Labels de conversão do Google Ads por evento interno. Formato do send_to:
    // "{google_ads_id}/{label}". Vazio = não dispara conversão de Ads (GA4/Meta
    // continuam).
    'ads_labels' => [
        'trial_start' => env('GOOGLE_ADS_LABEL_TRIAL'),
        'purchase' => env('GOOGLE_ADS_LABEL_PURCHASE'),
    ],
];
