<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\RafConsultaPendente;
use App\Services\CreditService;
use App\Services\Sped\SpedUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RafController extends Controller
{
    private const AUTH_VIEW_PREFIX = 'autenticado.';
    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        protected CreditService $creditService,
        protected SpedUploadService $spedUploadService
    ) {}

    /**
     * Lista todos os relatórios pendentes do usuário autenticado.
     */
    public function historico(Request $request)
    {
        $historicoView = self::AUTH_VIEW_PREFIX . 'raf_historico';

        if (!view()->exists($historicoView)) {
            abort(404);
        }

        if (!Auth::check()) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        $user = Auth::user();
        $userId = (int) $user->id;
        
        // Query principal para buscar relatórios do usuário
        $relatorios = RafConsultaPendente::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalEncontrados = $relatorios->count();
        
        Log::info('RafController::historico - Relatórios encontrados', [
            'user_id' => $userId,
            'total_relatorios' => $totalEncontrados,
        ]);

        $data = [
            'relatorios' => $relatorios,
            'total_pendentes' => $totalEncontrados,
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($historicoView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $historicoView
        ], $data));
    }

    /**
     * Retorna detalhes de um relatório específico em formato JSON.
     */
    public function detalhes(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $relatorio = RafConsultaPendente::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$relatorio) {
            return response()->json([
                'success' => false,
                'message' => 'Relatório não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $relatorio->id,
                'tipo_efd' => $relatorio->tipo_efd,
                'tipo_consulta' => $relatorio->tipo_consulta,
                'qtd_participantes' => $relatorio->qtd_participantes,
                'valor_total_consulta' => (float) $relatorio->valor_total_consulta,
                'custo_unitario' => (float) $relatorio->custo_unitario,
                'resume_url' => $relatorio->resume_url,
                'created_at' => $relatorio->created_at?->toIso8601String(),
                'updated_at' => $relatorio->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Confirma e processa um relatório pendente.
     * Reutiliza a lógica do CreditController@confirm.
     */
    public function confirmar(Request $request, $id)
    {
        set_time_limit(3600);

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $relatorio = RafConsultaPendente::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$relatorio) {
            return response()->json([
                'success' => false,
                'message' => 'Relatório não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $resumeUrl = $relatorio->resume_url;
        $valorCreditos = (float) $relatorio->valor_total_consulta;
        $saldoAtual = $this->creditService->getBalance($user);

        Log::info('Tentativa de confirmação de créditos via histórico', [
            'user_id' => $user->id,
            'relatorio_id' => $id,
            'saldo_atual' => $saldoAtual,
            'valor_solicitado' => $valorCreditos,
        ]);

        // Verifica se tem créditos suficientes
        if (!$this->creditService->hasEnough($user, $valorCreditos)) {
            Log::warning('Créditos insuficientes para operação via histórico', [
                'user_id' => $user->id,
                'relatorio_id' => $id,
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
            Log::error('Falha ao descontar créditos via histórico', [
                'user_id' => $user->id,
                'relatorio_id' => $id,
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
            
            Log::warning('Webhook falhou, créditos reembolsados via histórico', [
                'user_id' => $user->id,
                'relatorio_id' => $id,
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
     * Cancela um relatório pendente.
     * Reutiliza a lógica do CreditController@cancel.
     */
    public function cancelar(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $relatorio = RafConsultaPendente::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$relatorio) {
            return response()->json([
                'success' => false,
                'message' => 'Relatório não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $resumeUrl = $relatorio->resume_url;

        Log::info('Cancelamento de confirmação de créditos via histórico', [
            'user_id' => $user->id,
            'relatorio_id' => $id,
            'resume_url' => $resumeUrl,
        ]);

        // Envia 'answer: decline' para o webhook
        $result = $this->spedUploadService->confirmAndResume($resumeUrl, 'decline');

        if (!$result['success'] && $result['message'] !== 'Operação cancelada pelo usuário.') {
            Log::warning('Falha ao enviar cancelamento para webhook via histórico', [
                'user_id' => $user->id,
                'relatorio_id' => $id,
                'resume_url' => $resumeUrl,
                'error' => $result['message'] ?? 'Erro desconhecido',
            ]);
        }

        // Remove o registro da tabela de pendentes
        $relatorio->delete();

        return response()->json([
            'success' => true,
            'message' => 'Operação cancelada com sucesso.',
        ]);
    }

    /**
     * Verifica se a requisição é AJAX de forma compatível com Laravel 11 e 12
     */
    private function isAjaxRequest(Request $request): bool
    {
        // Verifica se o método ajax() existe (Laravel 11)
        if (method_exists($request, 'ajax')) {
            return $request->ajax();
        }

        // Fallback para Laravel 12: verifica headers
        $xRequestedWith = $request->header('X-Requested-With');
        $wantsJson = $request->wantsJson();
        $expectsJson = $request->expectsJson();
        
        return $wantsJson 
            || $expectsJson
            || $xRequestedWith === 'XMLHttpRequest';
    }
}

