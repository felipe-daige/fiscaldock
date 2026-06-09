<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\MercadoPago\CancelarAssinaturaMercadoPago;
use App\Actions\MercadoPago\CriarAssinaturaMercadoPago;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class AssinaturaController extends Controller
{
    public function criar(Request $request, CriarAssinaturaMercadoPago $action): JsonResponse
    {
        $dados = $request->validate([
            'plano' => ['required', 'string'],
            'ciclo' => ['required', 'in:mensal,anual'],
            'token' => ['required', 'string'],
        ]);

        try {
            $sub = $action->execute(Auth::user(), $dados['plano'], $dados['ciclo'], $dados['token']);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => $sub->status,
            'assinatura_id' => $sub->id,
            'mensagem' => 'Assinatura criada. Aguardando confirmação do pagamento.',
        ]);
    }

    public function cancelar(CancelarAssinaturaMercadoPago $action): JsonResponse
    {
        try {
            $action->execute(Auth::user());
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['status' => 'cancelada', 'mensagem' => 'Assinatura cancelada.']);
    }
}
