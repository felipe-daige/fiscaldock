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
        // Webhooks EFD (extração completa por tipo)
        'efd_contribuicoes_url' => env('WEBHOOK_EFD_CONTRIBUICOES_URL'),
        'efd_fiscal_url' => env('WEBHOOK_EFD_FISCAL_URL'),
        // Webhook Importação de XMLs (NF-e, NFS-e, CT-e)
        'importacao_xml_url' => env('WEBHOOK_IMPORTACAO_XML_URL'),
        // Webhook Consultas - endpoint unificado (avulsa e lote)
        'consultas_url' => env('WEBHOOK_CONSULTAS_URL'),
        // Credenciais
        'username' => env('WEBHOOK_SPED_USERNAME'),
        'password' => env('WEBHOOK_SPED_PASSWORD'),
    ],

    'api' => [
        'token' => env('API_TOKEN', ''),
    ],

    'receitaws' => [
        'url' => env('RECEITAWS_API_URL', 'https://www.receitaws.com.br/v1'),
    ],

    'viacep' => [
        'url' => env('VIACEP_API_URL', 'https://viacep.com.br/ws'),
    ],

];
