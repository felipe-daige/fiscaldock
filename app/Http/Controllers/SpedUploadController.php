<?php

namespace App\Http\Controllers;

use App\Models\SpedImportacao;
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
            'extrair_notas' => ['nullable'],
        ]);

        $user = Auth::user();
        $file = $request->file('file');
        $tipoEfd = $request->input('tipo_efd', 'contribuicoes');
        $tabId = $request->input('tab_id');
        $clienteId = $request->input('cliente_id');
        $extrairNotas = filter_var($request->input('extrair_notas', false), FILTER_VALIDATE_BOOLEAN);

        // Normalizar tipo_efd para formato do banco
        $tipoEfdNormalizado = str_contains(strtolower($tipoEfd), 'fiscal')
            ? 'EFD_FISCAL'
            : 'EFD_CONTRIB';

        // Selecionar webhook baseado no tipo de EFD
        $isFiscal = $tipoEfdNormalizado === 'EFD_FISCAL';
        $webhookUrl = $isFiscal
            ? config('services.webhook.monitoramento_importacao_fiscal_url')
            : config('services.webhook.monitoramento_importacao_contribuicoes_url');

        Log::info('SpedUpload: iniciando envio para n8n', [
            'user_id' => $user->id,
            'tipo_efd' => $tipoEfdNormalizado,
            'is_fiscal' => $isFiscal,
            'extrair_notas' => $extrairNotas,
            'tab_id' => $tabId,
            'filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'webhook_url' => $webhookUrl ? 'configurado' : 'NÃO CONFIGURADO',
        ]);

        if (!$webhookUrl) {
            Log::error('SpedUpload: webhook não configurado', [
                'tipo_efd' => $tipoEfdNormalizado,
                'is_fiscal' => $isFiscal,
            ]);
            return response()->json(['error' => "Webhook para tipo '$tipoEfd' não configurado"], 503);
        }

        // Criar registro ANTES de enviar para n8n
        $importacao = SpedImportacao::create([
            'user_id' => $user->id,
            'tipo_efd' => $tipoEfdNormalizado,
            'filename' => $file->getClientOriginalName(),
            'status' => 'pendente',
            'extrair_notas' => $extrairNotas,
            'iniciado_em' => now(),
        ]);

        Log::info('SpedUpload: registro SpedImportacao criado', [
            'importacao_id' => $importacao->id,
            'user_id' => $user->id,
        ]);

        try {
            $payload = [
                'user_id' => $user->id,
                'importacao_id' => $importacao->id,
                'tab_id' => $tabId,
                'tipo_efd' => $tipoEfdNormalizado,
                'cliente_id' => $clienteId,
                'extrair_notas' => $extrairNotas,
                'filename' => $file->getClientOriginalName(),
                'progress_url' => url('/api/monitoramento/sped/importacao-txt/progress'),
            ];

            Log::debug('SpedUpload: enviando para webhook', [
                'webhook_url' => $webhookUrl,
                'payload_keys' => array_keys($payload),
                'importacao_id' => $importacao->id,
            ]);

            $response = Http::timeout(30)->attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )->post($webhookUrl, $payload);

            if ($response->successful()) {
                // Atualizar para processando após resposta bem-sucedida
                $importacao->update(['status' => 'processando']);

                Log::info('SpedUpload: arquivo enviado com sucesso', [
                    'user_id' => $user->id,
                    'tab_id' => $tabId,
                    'importacao_id' => $importacao->id,
                    'filename' => $file->getClientOriginalName(),
                    'response_status' => $response->status(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Arquivo enviado para processamento.',
                    'importacao_id' => $importacao->id,
                ]);
            }

            // Marcar erro se webhook falhou
            $importacao->update(['status' => 'erro']);

            Log::error('SpedUpload: erro na resposta do n8n', [
                'user_id' => $user->id,
                'tab_id' => $tabId,
                'importacao_id' => $importacao->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao processar arquivo.',
            ], $response->status());

        } catch (\Exception $e) {
            // Marcar erro em caso de exceção
            $importacao->update(['status' => 'erro']);

            Log::error('SpedUpload: exceção ao enviar', [
                'user_id' => $user->id,
                'tab_id' => $tabId,
                'importacao_id' => $importacao->id,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
            ]);

            return response()->json(['error' => 'Erro ao enviar arquivo'], 500);
        }
    }
}
