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

        // Selecionar webhook baseado no tipo de EFD
        $webhookUrl = match ($tipoEfd) {
            'fiscal' => config('services.webhook.monitoramento_importacao_fiscal_url'),
            default => config('services.webhook.monitoramento_importacao_contribuicoes_url'),
        };

        if (!$webhookUrl) {
            return response()->json(['error' => "Webhook para tipo '$tipoEfd' não configurado"], 503);
        }

        try {
            $response = Http::attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )->post($webhookUrl, array_merge($request->except(['file', '_token']), [
                'user_id' => Auth::id(),
                'filename' => $file->getClientOriginalName(),
                'progress_url' => url('/api/monitoramento/sped/importacao-txt/progress'),
            ]));

            // Normalizar resposta para o frontend (espera {success: true})
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Arquivo enviado para processamento.',
                ]);
            }

            Log::error('Erro ao enviar SPED para n8n', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao processar arquivo.',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Erro upload SPED', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao enviar arquivo'], 500);
        }
    }
}
