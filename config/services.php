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
        // Webhooks RAF - modalidade "gratuito" (apenas regime tributário)
        'sped_contribuicoes_url' => env('WEBHOOK_SPED_CONTRIBUICOES_URL'),
        'sped_fiscal_url' => env('WEBHOOK_SPED_FISCAL_URL'),
        // Webhooks RAF - modalidade "completa" (CND + regime tributário)
        'sped_contribuicoes_completa_url' => env('WEBHOOK_SPED_CONTRIBUICOES_COMPLETA_URL'),
        'sped_fiscal_completa_url' => env('WEBHOOK_SPED_FISCAL_COMPLETA_URL'),
        // Webhook Monitoramento - importação de arquivo .txt
        'monitoramento_importacao_txt_url' => env('WEBHOOK_MONITORAMENTO_IMPORTACAO_TXT_URL'),
        // Webhook Monitoramento - consultas avulsas e de assinatura
        'monitoramento_consulta_url' => env('WEBHOOK_MONITORAMENTO_CONSULTA_URL'),
        // Credenciais
        'username' => env('WEBHOOK_SPED_USERNAME'),
        'password' => env('WEBHOOK_SPED_PASSWORD'),
    ],

    'api' => [
        'token' => env('API_TOKEN', ''),
    ],

];
