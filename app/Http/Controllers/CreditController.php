<?php

namespace App\Http\Controllers;

use App\Models\RafConsultaPendente;
use App\Services\CreditService;
use App\Services\Sped\SpedUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CreditController extends Controller
{
    public function __construct(
        protected CreditService $creditService,
        protected SpedUploadService $spedUploadService
    ) {}

    /**
     * Retorna o saldo de créditos do usuário autenticado.
     */
    public function balance(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'success' => true,
            'credits' => $this->creditService->getBalance($user),
        ]);
    }

    /**
     * Confirma o uso de créditos e envia a confirmação para o webhook n8n.
     * Se confirm_receipt for true, apenas confirma o recebimento sem descontar créditos novamente.
     */
    public function confirm(Request $request)
    {
        // Define timeout longo para aguardar processamento
        set_time_limit(3600);

        $validated = $request->validate([
            'resume_url' => 'required|url',
            'valor_total_consulta' => 'required|numeric|min:0',
            'confirm_receipt' => 'sometimes|boolean',
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $resumeUrl = $validated['resume_url'];
        $confirmReceipt = $validated['confirm_receipt'] ?? false;

        // Buscar o registro no banco de dados para garantir que o resume_url está atualizado
        $relatorio = RafConsultaPendente::where('resume_url', $resumeUrl)
            ->where('user_id', $user->id)
            ->first();

        if (!$relatorio) {
            Log::warning('Registro não encontrado no banco de dados para confirmação de créditos', [
                'user_id' => $user->id,
                'resume_url' => $resumeUrl,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registro não encontrado. Por favor, tente novamente ou entre em contato com o suporte.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Usar o resume_url do banco de dados (mais atualizado)
        $resumeUrl = $relatorio->resume_url;

        // Se for apenas confirmação de recebimento, não descontar créditos novamente
        if ($confirmReceipt) {
            Log::info('Confirmação de recebimento do CSV', [
                'user_id' => $user->id,
                'resume_url' => $resumeUrl,
                'relatorio_id' => $relatorio->id,
            ]);

            // Envia confirmação para o webhook (apenas confirmação, sem aguardar CSV)
            $result = $this->spedUploadService->confirmAndResume($resumeUrl, 'confirmado');

            if (!$result['success']) {
                Log::warning('Falha ao confirmar recebimento do CSV no webhook', [
                    'user_id' => $user->id,
                    'resume_url' => $resumeUrl,
                    'error' => $result['message'] ?? 'Erro desconhecido',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Erro ao confirmar recebimento do CSV.',
                ], Response::HTTP_BAD_GATEWAY);
            }

            return response()->json([
                'success' => true,
                'message' => 'Recebimento do CSV confirmado com sucesso.',
            ]);
        }

        // Usar o valor_total_consulta do banco de dados (mais atualizado)
        $valorTotalConsulta = (float) $relatorio->valor_total_consulta;
        
        // Arredonda para cima para garantir que tenha créditos suficientes
        $valorCreditos = (int) ceil($valorTotalConsulta);
        $saldoAtual = $this->creditService->getBalance($user);

        Log::info('Tentativa de confirmação de créditos', [
            'user_id' => $user->id,
            'relatorio_id' => $relatorio->id,
            'saldo_atual' => $saldoAtual,
            'valor_solicitado' => $valorCreditos,
            'resume_url' => $resumeUrl,
        ]);

        // Verifica se tem créditos suficientes
        if (!$this->creditService->hasEnough($user, $valorCreditos)) {
            Log::warning('Créditos insuficientes para operação', [
                'user_id' => $user->id,
                'saldo_atual' => $saldoAtual,
                'valor_solicitado' => $valorCreditos,
            ]);

            // Envia negação para o webhook
            $this->spedUploadService->confirmAndResume($resumeUrl, 'negado');

            return response()->json([
                'success' => false,
                'insufficient_credits' => true,
                'credits' => $saldoAtual,
                'required' => $valorCreditos,
                'message' => 'Créditos insuficientes. Entre em contato pelo telefone (69) 99999-9999 para adquirir mais créditos.',
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        // Desconta os créditos
        $deducted = $this->creditService->deduct($user, $valorCreditos);

        if (!$deducted) {
            Log::error('Falha ao descontar créditos', [
                'user_id' => $user->id,
                'valor' => $valorCreditos,
            ]);

            // Envia negação para o webhook
            $this->spedUploadService->confirmAndResume($resumeUrl, 'negado');

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar créditos. Tente novamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Envia confirmação para o webhook e aguarda o CSV
        $result = $this->spedUploadService->confirmAndResume($resumeUrl, 'confirmado');

        if (!$result['success']) {
            // Reembolsa os créditos em caso de falha no webhook
            $this->creditService->add($user, $valorCreditos);
            
            Log::warning('Webhook falhou, créditos reembolsados', [
                'user_id' => $user->id,
                'relatorio_id' => $relatorio->id,
                'valor' => $valorCreditos,
                'error' => $result['message'] ?? 'Erro desconhecido',
            ]);

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erro ao processar. Créditos foram reembolsados.',
                'credits' => $this->creditService->getBalance($user),
            ], Response::HTTP_BAD_GATEWAY);
        }

        // Remove o registro da tabela de pendentes após sucesso
        $relatorio->delete();

        // Retorna o CSV como resposta
        $csv = $result['csv'] ?? '';
        $filename = $result['filename'] ?? 'resultado.csv';

        return response($csv, Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'X-Credits-Remaining' => $this->creditService->getBalance($user),
        ]);
    }

    /**
     * Cancela a operação e envia 'answer: decline' para o webhook n8n.
     */
    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'resume_url' => 'required|url',
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $resumeUrl = $validated['resume_url'];

        // Buscar o registro no banco de dados para garantir que o resume_url está atualizado
        $relatorio = RafConsultaPendente::where('resume_url', $resumeUrl)
            ->where('user_id', $user->id)
            ->first();

        if (!$relatorio) {
            Log::warning('Registro não encontrado no banco de dados para cancelamento', [
                'user_id' => $user->id,
                'resume_url' => $resumeUrl,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registro não encontrado. Por favor, tente novamente ou entre em contato com o suporte.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Usar o resume_url do banco de dados (mais atualizado)
        $resumeUrl = $relatorio->resume_url;

        Log::info('Cancelamento de confirmação de créditos', [
            'user_id' => $user->id,
            'relatorio_id' => $relatorio->id,
            'resume_url' => $resumeUrl,
        ]);

        // Envia 'answer: decline' para o webhook
        $result = $this->spedUploadService->confirmAndResume($resumeUrl, 'decline');

        // Remove o registro da tabela de pendentes após cancelamento
        $relatorio->delete();

        if (!$result['success'] && $result['message'] !== 'Operação cancelada pelo usuário.') {
            Log::warning('Falha ao enviar cancelamento para webhook', [
                'user_id' => $user->id,
                'resume_url' => $resumeUrl,
                'error' => $result['message'] ?? 'Erro desconhecido',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Operação cancelada com sucesso.',
        ]);
    }
}

