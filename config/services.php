<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'webhook' => [
        // Webhooks para modalidade "gratuito" (apenas regime tributário)
        'sped_contribuicoes_url' => env('WEBHOOK_SPED_CONTRIBUICOES_URL', 'https://autowebhook.fiscaldock.com.br/webhook/raf-consulta-gratuita-sped-contribuicoes'),
        'sped_fiscal_url' => env('WEBHOOK_SPED_FISCAL_URL', 'https://autowebhook.fiscaldock.com.br/webhook/raf-consulta-gratuita-sped-fiscal'),
        // Webhooks para modalidade "completa" (CND + regime tributário)
        'sped_contribuicoes_completa_url' => env('WEBHOOK_SPED_CONTRIBUICOES_COMPLETA_URL', 'https://autowebhook.fiscaldock.com.br/webhook/raf-consulta-completa-sped-contribuicoes'),
        'sped_fiscal_completa_url' => env('WEBHOOK_SPED_FISCAL_COMPLETA_URL', 'https://autowebhook.fiscaldock.com.br/webhook/raf-consulta-completa-sped-fiscal'),
        'username' => env('WEBHOOK_SPED_USERNAME'),
        'password' => env('WEBHOOK_SPED_PASSWORD'),
    ],

    'api' => [
        'token' => env('API_TOKEN', ''),
    ],

];
