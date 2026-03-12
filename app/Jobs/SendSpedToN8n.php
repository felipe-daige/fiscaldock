<?php

namespace App\Jobs;

use App\Models\SpedImportacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendSpedToN8n implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 150;
    public int $tries = 1;

    public function __construct(
        private int $importacaoId,
        private string $webhookUrl,
        private string $filePath,
        private string $fileName,
        private array $payload,
    ) {}

    public function handle(): void
    {
        $importacao = SpedImportacao::find($this->importacaoId);

        if (!$importacao) {
            Log::error('SendSpedToN8n: SpedImportacao não encontrada', [
                'importacao_id' => $this->importacaoId,
            ]);
            return;
        }

        $fileContents = Storage::get($this->filePath);

        if ($fileContents === null) {
            Log::error('SendSpedToN8n: arquivo temporário não encontrado', [
                'importacao_id' => $this->importacaoId,
                'file_path' => $this->filePath,
            ]);
            $importacao->update(['status' => 'erro']);
            return;
        }

        try {
            Log::debug('SendSpedToN8n: enviando para webhook', [
                'webhook_url' => $this->webhookUrl,
                'importacao_id' => $this->importacaoId,
                'file_path' => $this->filePath,
            ]);

            $response = Http::timeout(120)->attach(
                'file',
                $fileContents,
                $this->fileName
            )->post($this->webhookUrl, $this->payload);

            Log::info('SendSpedToN8n: resposta do n8n', [
                'importacao_id' => $this->importacaoId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $importacao->update([
                'status' => $response->successful() ? 'processando' : 'erro',
            ]);

            if (!$response->successful()) {
                Log::error('SendSpedToN8n: n8n retornou erro', [
                    'importacao_id' => $this->importacaoId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } else {
                Log::info('SendSpedToN8n: arquivo enviado com sucesso', [
                    'importacao_id' => $this->importacaoId,
                    'filename' => $this->fileName,
                ]);
            }
        } catch (\Exception $e) {
            $importacao->update(['status' => 'erro']);

            Log::error('SendSpedToN8n: exceção ao enviar', [
                'importacao_id' => $this->importacaoId,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
            ]);
        } finally {
            Storage::delete($this->filePath);
        }
    }
}
