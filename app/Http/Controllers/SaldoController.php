<?php

namespace App\Http\Controllers;

use App\Services\SaldoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SaldoController extends Controller
{
    public function __construct(
        protected SaldoService $saldoService
    ) {}

    /**
     * Retorna o saldo em reais do usuário autenticado.
     */
    public function balance(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'success' => true,
            'saldo_reais' => app(\App\Services\PricingCatalogService::class)
                ->creditsToCurrency($this->saldoService->getBalance($user)),
        ]);
    }
}
