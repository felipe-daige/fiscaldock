<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpedUploadController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:txt,text/plain'],
            'tipo_efd' => ['nullable', 'string'],
            'cliente_id' => ['nullable', 'integer'],
            'tab_id' => ['nullable', 'string', 'max:36'],
        ]);

        $file = $request->file('file');
        $tipoEfd = $request->input('tipo_efd', 'contribuicoes');
        $tabId = $request->input('tab_id');

        // Selecionar webhook baseado no tipo de EFD
        $isFiscal = str_contains(strtolower($tipoEfd), 'fiscal');
        $webhookUrl = $isFiscal
            ? config('services.webhook.monitoramento_importacao_fiscal_url')
            : config('services.webhook.monitoramento_importacao_contribuicoes_url');

        Log::info('SpedUpload: iniciando envio para n8n', [
            'user_id' => Auth::id(),
            'tipo_efd' => $tipoEfd,
            'is_fiscal' => $isFiscal,
            'tab_id' => $tabId,
            'filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'webhook_url' => $webhookUrl ? 'configurado' : 'NÃO CONFIGURADO',
        ]);

        if (!$webhookUrl) {
            Log::error('SpedUpload: webhook não configurado', [
                'tipo_efd' => $tipoEfd,
                'is_fiscal' => $isFiscal,
            ]);
            return response()->json(['error' => "Webhook para tipo '$tipoEfd' não configurado"], 503);
        }

        try {
            $payload = array_merge($request->except(['file', '_token']), [
                'user_id' => Auth::id(),
                'filename' => $file->getClientOriginalName(),
                'progress_url' => url('/api/monitoramento/sped/importacao-txt/progress'),
            ]);

            Log::debug('SpedUpload: enviando para webhook', [
                'webhook_url' => $webhookUrl,
                'payload_keys' => array_keys($payload),
            ]);

            $response = Http::timeout(30)->attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )->post($webhookUrl, $payload);

            // Normalizar resposta para o frontend (espera {success: true})
            if ($response->successful()) {
                Log::info('SpedUpload: arquivo enviado com sucesso', [
                    'user_id' => Auth::id(),
                    'tab_id' => $tabId,
                    'filename' => $file->getClientOriginalName(),
                    'response_status' => $response->status(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Arquivo enviado para processamento.',
                ]);
            }

            Log::error('SpedUpload: erro na resposta do n8n', [
                'user_id' => Auth::id(),
                'tab_id' => $tabId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao processar arquivo.',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('SpedUpload: exceção ao enviar', [
                'user_id' => Auth::id(),
                'tab_id' => $tabId,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
            ]);
            return response()->json(['error' => 'Erro ao enviar arquivo'], 500);
        }
    }
}
