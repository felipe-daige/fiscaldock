<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RelatorioCompletoController extends Controller
{
    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Recebe o payload completo do relatório e retorna dados formatados para exibição.
     * Aceita autenticação via token (header X-API-Token) ou sessão (para frontend).
     */
    public function confirmarRelatorioCompleto(Request $request)
    {
        // Verifica autenticação via token ou sessão
        $user = $this->authenticate($request);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado. Forneça um token válido (X-API-Token) ou faça login.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Validação do payload
        $validationRules = [
            'resume_url' => 'required|url',
            'valor_total_consulta' => 'required|numeric|min:0',
            'qtd_participantes_unicos' => 'nullable|integer|min:0',
            'custo_unitario' => 'nullable|numeric|min:0',
        ];
        
        // Se autenticado via token, user_id é obrigatório
        $apiToken = $request->header('X-API-Token') ?? $request->input('api_token');
        $expectedToken = config('services.api.token');
        if (!empty($apiToken) && !empty($expectedToken) && $apiToken === $expectedToken) {
            $validationRules['user_id'] = 'required|integer|exists:users,id';
        }
        
        $validated = $request->validate($validationRules, [
            'resume_url.required' => 'O campo resume_url é obrigatório.',
            'resume_url.url' => 'O campo resume_url deve ser uma URL válida.',
            'valor_total_consulta.required' => 'O campo valor_total_consulta é obrigatório.',
            'valor_total_consulta.numeric' => 'O campo valor_total_consulta deve ser um número.',
            'valor_total_consulta.min' => 'O campo valor_total_consulta deve ser maior ou igual a zero.',
            'qtd_participantes_unicos.integer' => 'O campo qtd_participantes_unicos deve ser um número inteiro.',
            'qtd_participantes_unicos.min' => 'O campo qtd_participantes_unicos deve ser maior ou igual a zero.',
            'custo_unitario.numeric' => 'O campo custo_unitario deve ser um número.',
            'custo_unitario.min' => 'O campo custo_unitario deve ser maior ou igual a zero.',
        ]);

        // Buscar saldo de créditos do usuário
        $saldoAtual = $this->creditService->getBalance($user);

        // Calcular valor arredondado de créditos necessários
        $valorTotalConsulta = (float) $validated['valor_total_consulta'];
        $valorCreditosNecessarios = (int) ceil($valorTotalConsulta);

        // Verificar se tem créditos suficientes
        $temCreditosSuficientes = $this->creditService->hasEnough($user, $valorCreditosNecessarios);

        Log::info('API confirmar-relatorio-completo chamada', [
            'user_id' => $user->id,
            'saldo_atual' => $saldoAtual,
            'valor_total_consulta' => $valorTotalConsulta,
            'valor_creditos_necessarios' => $valorCreditosNecessarios,
            'tem_creditos_suficientes' => $temCreditosSuficientes,
        ]);

        // Preparar dados de resposta
        $data = [
            'resume_url' => $validated['resume_url'],
            'valor_total_consulta' => $valorTotalConsulta,
            'valor_creditos_necessarios' => $valorCreditosNecessarios,
            'qtd_participantes_unicos' => isset($validated['qtd_participantes_unicos']) 
                ? (int) $validated['qtd_participantes_unicos'] 
                : null,
            'custo_unitario' => isset($validated['custo_unitario']) 
                ? (float) $validated['custo_unitario'] 
                : null,
            'credits' => [
                'saldo_atual' => $saldoAtual,
                'necessario' => $valorCreditosNecessarios,
                'suficiente' => $temCreditosSuficientes,
            ],
            'actions' => [
                'confirm_url' => '/app/credits/confirm',
                'cancel_url' => '/app/credits/cancel',
            ],
        ];

        // Se não tem créditos suficientes, adicionar flag e mensagem
        if (!$temCreditosSuficientes) {
            return response()->json([
                'success' => true,
                'data' => $data,
                'insufficient_credits' => true,
                'message' => 'Créditos insuficientes. Entre em contato pelo telefone (69) 99999-9999 para adquirir mais créditos.',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ], Response::HTTP_OK);
    }

    /**
     * Autentica via token API ou sessão.
     * Retorna o usuário autenticado ou null.
     */
    private function authenticate(Request $request): ?User
    {
        // Tenta autenticação via token API (para n8n)
        $apiToken = $request->header('X-API-Token') ?? $request->input('api_token');
        $expectedToken = config('services.api.token');
        
        if (!empty($apiToken) && !empty($expectedToken) && $apiToken === $expectedToken) {
            // Token válido - retorna um usuário "sistema" ou o primeiro usuário admin
            // Por enquanto, vamos usar Auth::user() se houver sessão, senão retorna null
            // Mas para n8n funcionar, precisamos de uma forma de identificar o usuário
            // Vou modificar para aceitar user_id no payload quando usar token
            $userId = $request->input('user_id');
            if ($userId) {
                return User::find($userId);
            }
            // Se não tiver user_id, tenta pegar da sessão
            return Auth::user();
        }
        
        // Fallback: autenticação via sessão (para frontend)
        return Auth::user();
    }
}

