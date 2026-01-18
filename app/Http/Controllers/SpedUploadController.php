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
        $webhookUrl = config('services.webhook.monitoramento_importacao_txt_url');

        if (!$webhookUrl) {
            return response()->json(['error' => 'Webhook não configurado'], 503);
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

            return response($response->body(), $response->status())
                ->header('Content-Type', $response->header('Content-Type'));

        } catch (\Exception $e) {
            Log::error('Erro upload SPED', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erro ao enviar arquivo'], 500);
        }
    }
}
