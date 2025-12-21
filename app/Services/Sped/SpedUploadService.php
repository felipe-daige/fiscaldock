<?php

namespace App\Services\Sped;

use App\Services\CsvParserService;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SpedUploadService
{
    public function __construct(
        protected CsvParserService $csvParser
    ) {}

    /**
     * Faz upload de arquivo SPED para o webhook e retorna dados parseados.
     *
     * @param UploadedFile $file Arquivo SPED
     * @param string $tipo Tipo do SPED (EFD Contribuições ou EFD Fiscal)
     * @param string|null $originalName Nome original do arquivo (opcional)
     * @param bool $isAuthenticated Se é requisição autenticada (afeta timeout e logs)
     * @return array{success: bool, headers?: array, rows?: array, csv?: string, filename?: string, message?: string, errors?: array}
     */
    public function uploadAndProcess(
        UploadedFile $file,
        string $tipo,
        ?string $originalName = null,
        bool $isAuthenticated = false
    ): array {
        $fileName = match ($tipo) {
            'EFD Contribuições' => 'sped_contribuicoes.txt',
            'EFD Fiscal' => 'sped_fiscal.txt',
            default => 'sped.txt',
        };

        // Seleciona a URL do webhook baseado no tipo de SPED
        $webhookUrl = match ($tipo) {
            'EFD Fiscal' => config('services.webhook.sped_fiscal_url')
                ?: 'https://auto.fiscaldock.com.br/webhook-test/consultar-regime-tributario-sped-fiscal',
            'EFD Contribuições' => config('services.webhook.sped_contribuicoes_url')
                ?: 'https://auto.fiscaldock.com.br/webhook-test/consultar-regime-tributario-sped-contribuicoes',
            default => config('services.webhook.sped_contribuicoes_url')
                ?: 'https://auto.fiscaldock.com.br/webhook-test/consultar-regime-tributario-sped-contribuicoes',
        };
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

        if (!empty($webhookUser) && !empty($webhookPass)) {
            $http = $http->withBasicAuth($webhookUser, $webhookPass);
        }

        try {
            $response = $http->attach('sped', file_get_contents($file->getRealPath()), $fileName)
                ->post($webhookUrl, [
                    'tipo' => $tipo,
                ]);
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

        $csv = $response->body();

        if (!$response->successful()) {
            $detail = '';
            $decoded = json_decode($csv, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $detail = $decoded['message'] ?? $decoded['error'] ?? '';
            } else {
                $detail = trim($csv);
            }

            $detail = $detail ? ' Detalhe: ' . mb_substr($detail, 0, 500) : '';

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
                'message' => 'Webhook retornou erro (' . $response->status() . ').' . $detail,
            ];
        }

        $parsed = $this->csvParser->parse($csv);

        // Determinar nome do arquivo final
        $filenameFromHeader = $this->extractFilenameFromResponse($response);
        $finalFilename = $this->normalizeCsvFilename(
            $filenameFromHeader
                ?? $originalName
                ?? ($fileName ? ('resultado_' . $fileName) : 'resultado.csv')
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
        if (!$disposition) {
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
        return ($withoutExt ?: 'resultado') . '.csv';
    }
}

