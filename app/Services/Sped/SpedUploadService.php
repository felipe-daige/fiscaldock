<?php

namespace App\Services\Sped;

use App\Services\CsvParserService;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpedUploadService
{
    public function __construct(
        protected CsvParserService $csvParser
    ) {}

    /**
     * Faz upload de arquivo SPED para o webhook e retorna dados parseados.
     *
     * @param  UploadedFile  $file  Arquivo SPED
     * @param  string  $tipo  Tipo do SPED (EFD Contribuições ou EFD Fiscal)
     * @param  string|null  $originalName  Nome original do arquivo (opcional)
     * @param  bool  $isAuthenticated  Se é requisição autenticada (afeta timeout e logs)
     * @param  string  $modalidade  Modalidade da consulta: 'gratuito' ou 'completa'
     * @param  int|null  $userId  ID do usuário (opcional, para requisições autenticadas)
     * @return array{success: bool, headers?: array, rows?: array, csv?: string, filename?: string, message?: string, errors?: array}
     */
    public function uploadAndProcess(
        UploadedFile $file,
        string $tipo,
        ?string $originalName = null,
        bool $isAuthenticated = false,
        string $modalidade = 'gratuito',
        ?int $userId = null,
        ?string $tabId = null,
        int $clienteId = 0
    ): array {
        $fileName = match ($tipo) {
            'EFD Contribuições' => 'sped_contribuicoes.txt',
            'EFD Fiscal' => 'sped_fiscal.txt',
            default => 'sped.txt',
        };

        // Seleciona a URL do webhook baseado no tipo de SPED e modalidade
        $webhookUrl = $this->getWebhookUrl($tipo, $modalidade);

        Log::debug('Webhook URL selecionada', [
            'tipo' => $tipo,
            'modalidade' => $modalidade,
            'webhook_url' => $webhookUrl,
        ]);

        $webhookUser = config('services.webhook.username');
        $webhookPass = config('services.webhook.password');

        if (empty($webhookUrl)) {
            return [
                'success' => false,
                'message' => 'Webhook não configurado.',
            ];
        }

        // Timeout de 1 hora (3600 segundos) para processamento que pode demorar até 1 hora
        $timeout = 3600;
        $http = Http::timeout($timeout);

        if (! empty($webhookUser) && ! empty($webhookPass)) {
            $http = $http->withBasicAuth($webhookUser, $webhookPass);
        }

        try {
            // Preparar payload com tipo, user_id, tab_id e cliente_id
            $payload = ['tipo' => $tipo];
            if ($userId !== null) {
                $payload['user_id'] = $userId;
            }
            if ($tabId !== null) {
                $payload['tab_id'] = $tabId;
            }
            // Sempre enviar cliente_id (0 se não selecionado)
            $payload['cliente_id'] = $clienteId;

            $response = $http->attach('sped', file_get_contents($file->getRealPath()), $fileName)
                ->post($webhookUrl, $payload);
        } catch (\Throwable $e) {
            $logContext = [
                'exception' => $e->getMessage(),
                'exception_class' => get_class($e),
                'tipo' => $tipo,
                'original_name' => $originalName,
            ];

            if ($isAuthenticated) {
                Log::error('Falha ao contatar webhook SPED (auth)', $logContext);
            }

            // Verifica se é um erro de timeout
            $isTimeout = str_contains($e->getMessage(), 'timeout')
                || str_contains($e->getMessage(), 'Connection timed out')
                || str_contains($e->getMessage(), 'timed out');

            $message = $isTimeout
                ? 'O processamento está demorando mais que o esperado. O SPED pode estar sendo processado em segundo plano. Aguarde alguns minutos e verifique novamente.'
                : 'Falha ao contatar o webhook. Tente novamente em instantes.';

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $body = $response->body();
        $contentType = $response->header('Content-Type') ?? '';
        $isJsonContentType = str_contains($contentType, 'application/json') || str_contains($contentType, 'text/json');

        // Verificar se a resposta é JSON
        $decoded = null;
        $isJson = false;

        if ($isJsonContentType) {
            $decoded = json_decode($body, true);
            $isJson = json_last_error() === JSON_ERROR_NONE && is_array($decoded);
        } else {
            $decoded = json_decode($body, true);
            $isJson = json_last_error() === JSON_ERROR_NONE && is_array($decoded);

            if ($isJson) {
                $trimmedBody = trim($body);
                $isJson = ($trimmedBody[0] ?? '') === '{' || ($trimmedBody[0] ?? '') === '[';
            }
        }

        if ($isJson && is_array($decoded)) {
            $data = isset($decoded[0]) && is_array($decoded[0]) ? $decoded[0] : $decoded;

            Log::debug('Webhook SPED resposta JSON detectada', [
                'tipo' => $tipo,
                'content_type' => $contentType,
                'data_keys' => array_keys($data),
            ]);

            // JSON válido com "message" = resposta assíncrona
            if (isset($data['message'])) {
                Log::info('Webhook SPED retornou resposta assíncrona', [
                    'tipo' => $tipo,
                    'modalidade' => $modalidade,
                    'message' => $data['message'],
                    'user_id' => $userId,
                ]);

                return [
                    'success' => true,
                    'async' => true,
                    'message' => $data['message'] ?? 'Processamento iniciado. Aguarde o relatório.',
                ];
            }
        }

        $csv = $body;

        // Log quando o CSV está vazio
        if (trim($csv) === '' && $response->successful()) {
            Log::warning('Webhook SPED retornou resposta vazia', [
                'tipo' => $tipo,
                'original_name' => $originalName,
                'status' => $response->status(),
                'content_type' => $contentType,
                'is_authenticated' => $isAuthenticated,
                'user_id' => $userId,
            ]);
        }

        if (! $response->successful()) {
            $detail = '';
            $decoded = json_decode($csv, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $detail = $decoded['message'] ?? $decoded['error'] ?? '';
            } else {
                $detail = trim($csv);
            }

            $detail = $detail ? ' Detalhe: '.mb_substr($detail, 0, 500) : '';

            $logContext = [
                'status' => $response->status(),
                'detail' => $detail,
                'tipo' => $tipo,
                'original_name' => $originalName,
            ];

            if ($isAuthenticated) {
                $logContext['content_disposition'] = $response->header('Content-Disposition');
                Log::warning('Webhook SPED falhou (auth)', $logContext);
            } else {
                Log::warning('Webhook SPED falhou', $logContext);
            }

            return [
                'success' => false,
                'message' => 'Webhook retornou erro ('.$response->status().').'.$detail,
            ];
        }

        $parsed = $this->csvParser->parse($csv);

        // Determinar nome do arquivo final
        $filenameFromHeader = $this->extractFilenameFromResponse($response);
        $finalFilename = $this->normalizeCsvFilename(
            $filenameFromHeader
                ?? $originalName
                ?? ($fileName ? ('resultado_'.$fileName) : 'resultado.csv')
        );

        if ($isAuthenticated) {
            Log::info('Webhook SPED sucesso (auth)', [
                'tipo' => $tipo,
                'original_name' => $originalName,
                'status' => $response->status(),
                'header_name' => $response->header('name'),
                'content_disposition' => $response->header('Content-Disposition'),
                'filename_returned' => $filenameFromHeader,
                'filename_final' => $finalFilename,
            ]);
        }

        return [
            'success' => true,
            'headers' => $parsed['headers'],
            'rows' => $parsed['rows'],
            'csv' => $csv,
            'filename' => $finalFilename,
        ];
    }

    /**
     * Envia apenas o status (approved/declined) para o webhook sem aguardar CSV.
     *
     * @param  string  $resumeUrl  URL de callback do n8n
     * @param  string  $status  'approved' ou 'declined' (aceita também 'denied', 'negado' para compatibilidade)
     * @return array{success: bool, message?: string}
     */
    public function sendWebhookStatus(string $resumeUrl, string $status): array
    {
        $webhookUser = config('services.webhook.username');
        $webhookPass = config('services.webhook.password');

        // Timeout curto (30s) - não precisa aguardar processamento longo
        $timeout = 30;
        $http = Http::timeout($timeout);

        if (! empty($webhookUser) && ! empty($webhookPass)) {
            $http = $http->withBasicAuth($webhookUser, $webhookPass);
        }

        // Garantir que o status está no formato correto
        $webhookStatus = match ($status) {
            'approved', 'confirmado', 'confirmed' => 'approved',
            'denied', 'negado', 'declined' => 'declined',
            default => 'declined',
        };

        try {
            $response = $http->post($resumeUrl, [
                'status' => $webhookStatus,
            ]);
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar status para webhook', [
                'resume_url' => $resumeUrl,
                'status' => $webhookStatus,
                'exception' => $e->getMessage(),
            ]);

            $isTimeout = str_contains($e->getMessage(), 'timeout')
                || str_contains($e->getMessage(), 'timed out');

            return [
                'success' => false,
                'message' => $isTimeout
                    ? 'Timeout ao enviar confirmação. Tente novamente.'
                    : 'Falha ao contatar o servidor. Tente novamente.',
            ];
        }

        if (! $response->successful()) {
            $detail = '';
            $body = $response->body();
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $detail = $decoded['message'] ?? $decoded['error'] ?? '';
            } else {
                $detail = trim($body);
            }

            Log::warning('Webhook status falhou', [
                'resume_url' => $resumeUrl,
                'status_code' => $response->status(),
                'status_sent' => $webhookStatus,
                'detail' => mb_substr($detail, 0, 500),
            ]);

            return [
                'success' => false,
                'http_code' => $response->status(),
                'message' => 'Erro ao enviar confirmação ('.$response->status().'). '.mb_substr($detail, 0, 200),
            ];
        }

        Log::info('Webhook status enviado com sucesso', [
            'resume_url' => $resumeUrl,
            'status' => $webhookStatus,
        ]);

        return [
            'success' => true,
            'http_code' => $response->status(),
        ];
    }

    /**
     * Envia confirmação ou negação para o webhook de resumo do n8n.
     *
     * @param  string  $resumeUrl  URL de callback do n8n
     * @param  string  $status  'confirmado' ou 'negado' (aceita também 'confirm'/'decline' para compatibilidade)
     * @return array{success: bool, headers?: array, rows?: array, csv?: string, filename?: string, message?: string}
     */
    public function confirmAndResume(string $resumeUrl, string $status): array
    {
        $webhookUser = config('services.webhook.username');
        $webhookPass = config('services.webhook.password');

        // Timeout de 1 hora para processamento
        $timeout = 3600;
        $http = Http::timeout($timeout);

        if (! empty($webhookUser) && ! empty($webhookPass)) {
            $http = $http->withBasicAuth($webhookUser, $webhookPass);
        }

        // Converte status para formato esperado pelo webhook n8n
        $webhookStatus = match ($status) {
            'confirmado', 'confirmed', 'approved' => 'approved',
            'negado', 'denied', 'declined' => 'declined',
            'confirm' => 'approved',
            'decline' => 'declined',
            default => 'declined',
        };

        try {
            $response = $http->post($resumeUrl, [
                'status' => $webhookStatus,
            ]);
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar confirmação para webhook', [
                'resume_url' => $resumeUrl,
                'status' => $status,
                'exception' => $e->getMessage(),
            ]);

            $isTimeout = str_contains($e->getMessage(), 'timeout')
                || str_contains($e->getMessage(), 'timed out');

            return [
                'success' => false,
                'message' => $isTimeout
                    ? 'O processamento está demorando mais que o esperado. Aguarde alguns minutos.'
                    : 'Falha ao contatar o servidor. Tente novamente.',
            ];
        }

        // Se o status foi negado/declined, não espera CSV de volta
        if ($status === 'negado' || $webhookStatus === 'declined') {
            return [
                'success' => false,
                'message' => 'Operação cancelada pelo usuário.',
            ];
        }

        $csv = $response->body();
        $contentType = $response->header('Content-Type') ?? '';

        if (! $response->successful()) {
            $detail = '';
            $decoded = json_decode($csv, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $detail = $decoded['message'] ?? $decoded['error'] ?? '';
            } else {
                $detail = trim($csv);
            }

            Log::warning('Webhook confirmação falhou', [
                'resume_url' => $resumeUrl,
                'status_code' => $response->status(),
                'detail' => mb_substr($detail, 0, 500),
            ]);

            return [
                'success' => false,
                'http_code' => $response->status(),
                'message' => 'Erro ao processar ('.$response->status().'). '.mb_substr($detail, 0, 200),
            ];
        }

        // Verificar se a resposta é JSON (n8n retorna resposta assíncrona)
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode($csv, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['message'])) {
                Log::info('Webhook retornou resposta assíncrona', [
                    'resume_url' => $resumeUrl,
                    'message' => $decoded['message'],
                ]);

                return [
                    'success' => true,
                    'async' => true,
                    'message' => $decoded['message'] ?? 'Processamento iniciado. Aguarde.',
                ];
            }
        }

        $parsed = $this->csvParser->parse($csv);

        $filenameFromHeader = $this->extractFilenameFromResponse($response);
        $finalFilename = $this->normalizeCsvFilename($filenameFromHeader ?? 'resultado.csv');

        Log::info('Webhook confirmação sucesso', [
            'resume_url' => $resumeUrl,
            'filename' => $finalFilename,
        ]);

        return [
            'success' => true,
            'headers' => $parsed['headers'],
            'rows' => $parsed['rows'],
            'csv' => $csv,
            'filename' => $finalFilename,
        ];
    }

    /**
     * Extrai nome de arquivo da resposta do webhook.
     * Prioridade: header 'name' (API EFD), depois Content-Disposition (RFC 6266).
     */
    private function extractFilenameFromResponse(ClientResponse $response): ?string
    {
        // Prioridade: header 'name' da API EFD Contribuicoes
        $nameHeader = $response->header('name');
        if ($nameHeader && trim($nameHeader) !== '') {
            return trim($nameHeader);
        }

        // Fallback: Content-Disposition (RFC 6266)
        $disposition = $response->header('Content-Disposition');
        if (! $disposition) {
            return null;
        }

        // filename*=UTF-8''nome.csv
        if (preg_match("/filename\\*=UTF-8''([^;]+)/i", $disposition, $matches)) {
            $raw = trim($matches[1], " \t\n\r\0\x0B\"'");

            return urldecode($raw);
        }

        // filename="nome.csv" ou filename=nome.csv
        if (preg_match('/filename=\"?([^\";]+)\"?/i', $disposition, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Garante extensão .csv no nome final.
     */
    private function normalizeCsvFilename(string $name): string
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            return 'resultado.csv';
        }

        // Remove query/fragment se vier junto
        $base = preg_split('/[?#]/', $trimmed, 2)[0];

        // Se já termina com .csv (case-insensitive), mantém
        if (preg_match('/\\.csv$/i', $base)) {
            return $base;
        }

        // Caso contrário, troca extensão existente ou adiciona .csv
        $withoutExt = preg_replace('/\\.[^.]+$/', '', $base);

        return ($withoutExt ?: 'resultado').'.csv';
    }

    /**
     * Retorna a URL do webhook baseado no tipo de SPED e modalidade.
     *
     * @throws \InvalidArgumentException Se a modalidade ou webhook não estiverem configurados
     */
    private function getWebhookUrl(string $tipo, string $modalidade): string
    {
        // Validação de segurança - apenas modalidades permitidas
        if (! in_array($modalidade, ['gratuito', 'completa'], true)) {
            throw new \InvalidArgumentException('Modalidade inválida: '.$modalidade);
        }

        // RAF Completo: CND + Regime Tributário (modalidade paga)
        if ($modalidade === 'completa') {
            $url = match ($tipo) {
                'EFD Fiscal' => config('services.webhook.sped_fiscal_completa_url'),
                'EFD Contribuições' => config('services.webhook.sped_contribuicoes_completa_url'),
                default => config('services.webhook.sped_contribuicoes_completa_url'),
            };
        } else {
            // RAF Gratuito: apenas Regime Tributário (modalidade gratuita)
            $url = match ($tipo) {
                'EFD Fiscal' => config('services.webhook.sped_fiscal_url'),
                'EFD Contribuições' => config('services.webhook.sped_contribuicoes_url'),
                default => config('services.webhook.sped_contribuicoes_url'),
            };
        }

        // Webhook não configurado
        if (empty($url)) {
            Log::error('Webhook não configurado no .env', [
                'tipo' => $tipo,
                'modalidade' => $modalidade,
            ]);
            throw new \InvalidArgumentException('Webhook não configurado. Verifique as variáveis de ambiente.');
        }

        Log::debug('getWebhookUrl', [
            'tipo' => $tipo,
            'modalidade' => $modalidade,
            'url' => $url,
        ]);

        return $url;
    }
}
