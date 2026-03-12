<?php

namespace App\Http\Controllers;

use App\Jobs\SendSpedToN8n;
use App\Models\SpedImportacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SpedUploadController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:txt,text/plain'],
            'tipo_efd' => ['nullable', 'string'],
            'tab_id' => ['nullable', 'string', 'max:36'],
            'extrair_notas' => ['nullable'],
        ]);

        $user = Auth::user();
        $file = $request->file('file');
        $tipoEfd = $request->input('tipo_efd', 'contribuicoes');
        $tabId = $request->input('tab_id');
        $extrairNotas = config('features.extrair_notas')
            ? filter_var($request->input('extrair_notas', false), FILTER_VALIDATE_BOOLEAN)
            : false;

        // Normalizar tipo_efd para formato do banco
        $tipoEfdNormalizado = str_contains(strtolower($tipoEfd), 'fiscal')
            ? 'EFD_FISCAL'
            : 'EFD_CONTRIB';

        // Selecionar webhook baseado no tipo de EFD
        $isFiscal = $tipoEfdNormalizado === 'EFD_FISCAL';
        $tipoEfdLabel = $isFiscal ? 'EFD Fiscal' : 'EFD Contribuições';
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

        $fileName = $file->getClientOriginalName();
        $filePath = 'temp/sped/' . Str::uuid() . '.txt';
        Storage::put($filePath, file_get_contents($file->getRealPath()));

        $payload = [
            'user_id' => $user->id,
            'importacao_id' => $importacao->id,
            'tab_id' => $tabId,
            'tipo_efd' => $tipoEfdLabel,
            'extrair_notas' => $extrairNotas,
            'filename' => $fileName,
            'progress_url' => url('/api/importacao/efd/importacao-txt/progress'),
        ];

        SendSpedToN8n::dispatch(
            $importacao->id,
            $webhookUrl,
            $filePath,
            $fileName,
            $payload,
        );

        Log::info('SpedUpload: job enfileirado', [
            'importacao_id' => $importacao->id,
            'user_id' => $user->id,
            'tab_id' => $tabId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Arquivo enviado para processamento.',
            'importacao_id' => $importacao->id,
        ]);
    }
}
