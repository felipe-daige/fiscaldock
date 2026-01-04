<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\RafConsultaPendente;
use App\Models\RafRelatorioProcessado;
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
     * Lista todos os relatórios (pendentes e processados) do usuário autenticado.
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
        
        // Obter status da query string (default: 'pendente')
        $status = $request->query('status', 'pendente');
        
        // Buscar todos os resume_urls dos processados para excluir pendentes duplicados
        $resumeUrlsProcessados = RafRelatorioProcessado::where('user_id', $userId)
            ->whereNotNull('resume_url')
            ->pluck('resume_url')
            ->toArray();
        
        // Calcular contadores para ambas as tabs independente do filtro atual
        // Excluir pendentes que já foram processados (têm resume_url correspondente em processados)
        $totalPendentes = RafConsultaPendente::where('user_id', $userId)
            ->where(function($query) use ($resumeUrlsProcessados) {
                $query->whereNull('resume_url')
                    ->orWhereNotIn('resume_url', $resumeUrlsProcessados);
            })
            ->count();
        $totalProcessados = RafRelatorioProcessado::where('user_id', $userId)->count();
        
        // Buscar relatórios conforme status
        if ($status === 'processado') {
            $relatorios = RafRelatorioProcessado::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Excluir pendentes que já foram processados
            $relatorios = RafConsultaPendente::where('user_id', $userId)
                ->where(function($query) use ($resumeUrlsProcessados) {
                    $query->whereNull('resume_url')
                        ->orWhereNotIn('resume_url', $resumeUrlsProcessados);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        Log::info('RafController::historico - Relatórios encontrados', [
            'user_id' => $userId,
            'status' => $status,
            'total_pendentes' => $totalPendentes,
            'total_processados' => $totalProcessados,
            'relatorios_count' => $relatorios->count(),
        ]);

        $data = [
            'relatorios' => $relatorios,
            'status_atual' => $status,
            'total_pendentes' => $totalPendentes,
            'total_processados' => $totalProcessados,
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
     * Suporta tanto relatórios pendentes quanto processados.
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
        
        // Tentar buscar como pendente primeiro
        $relatorio = RafConsultaPendente::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($relatorio) {
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

        // Se não encontrou como pendente, tentar como processado
        $relatorioProcessado = RafRelatorioProcessado::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($relatorioProcessado) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $relatorioProcessado->id,
                    'document_type' => $relatorioProcessado->document_type,
                    'consultant_type' => $relatorioProcessado->consultant_type,
                    'total_participants' => $relatorioProcessado->total_participants,
                    'total_price' => (float) $relatorioProcessado->total_price,
                    'cnpj_empresa_analisada' => $relatorioProcessado->cnpj_empresa_analisada,
                    'razao_social_empresa' => $relatorioProcessado->razao_social_empresa,
                    'total_fornecedores' => $relatorioProcessado->total_fornecedores,
                    'qtd_ativos' => $relatorioProcessado->qtd_ativos,
                    'qtd_inaptos' => $relatorioProcessado->qtd_inaptos,
                    'qtd_simples' => $relatorioProcessado->qtd_simples,
                    'qtd_presumido' => $relatorioProcessado->qtd_presumido,
                    'qtd_real' => $relatorioProcessado->qtd_real,
                    'qtd_regime_indeterminado' => $relatorioProcessado->qtd_regime_indeterminado,
                    'qtd_cnd_regular' => $relatorioProcessado->qtd_cnd_regular,
                    'qtd_cnd_pendencia' => $relatorioProcessado->qtd_cnd_pendencia,
                    'filename' => $relatorioProcessado->filename,
                    'processed_at' => $relatorioProcessado->processed_at?->toIso8601String(),
                    'created_at' => $relatorioProcessado->created_at?->toIso8601String(),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Relatório não encontrado.',
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Baixa um relatório processado em formato CSV.
     */
    public function baixar(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        $relatorio = RafRelatorioProcessado::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$relatorio) {
            return response()->json([
                'success' => false,
                'message' => 'Relatório não encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (empty($relatorio->report_csv_base64)) {
            return response()->json([
                'success' => false,
                'message' => 'Arquivo CSV não disponível.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Decodificar o CSV base64
        $csv = base64_decode($relatorio->report_csv_base64);
        
        // Sanitizar nome do arquivo
        $filename = $this->sanitizeFilename($relatorio->filename ?? 'resultado.csv', $id);

        return response($csv, Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Sanitiza o nome do arquivo para garantir formato correto.
     * Remove links, garante sufixo -fiscaldock.com.br e extensão .csv
     */
    private function sanitizeFilename(?string $filename, $id): string
    {
        if (empty($filename)) {
            return 'raf_relatorio_' . $id . '-fiscaldock.com.br.csv';
        }

        // Remover extensão existente para processar
        $name = preg_replace('/\.(csv|br)$/i', '', $filename);
        
        // Remover sufixos como (1), (2), etc.
        $name = preg_replace('/\s*\(\d+\)$/', '', $name);
        
        // Remover link fiscaldock.com ou variações no final
        // Padrões: fiscaldock.com, fiscaldock.com.br, -fiscaldock.com, etc.
        $name = preg_replace('/-?fiscaldock\.com(\.br)?$/i', '', $name);
        
        // Limpar espaços extras no final
        $name = rtrim($name, " \t\n\r\0\x0B-");
        
        // Se o nome ficou vazio, usar fallback
        if (empty($name)) {
            $name = 'raf_relatorio_' . $id;
        }
        
        // Adicionar sufixo padrão e extensão
        return $name . '-fiscaldock.com.br.csv';
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
            
            // Verificar se é erro 409 (execução expirada no n8n)
            $httpCode = $result['http_code'] ?? null;
            $message = $result['message'] ?? '';
            
            $isExecutionExpired = $httpCode === 409 || 
                str_contains($message, '409') || 
                str_contains($message, 'has finished already');
            
            if ($isExecutionExpired) {
                Log::info('Execução n8n expirada (409), deletando registro e informando usuário', [
                    'user_id' => $user->id,
                    'relatorio_id' => $id,
                    'valor' => $valorCreditos,
                ]);
                
                // Deletar o registro localmente já que o n8n não tem mais essa execução
                $relatorio->delete();
                
                return response()->json([
                    'success' => false,
                    'expired' => true,
                    'message' => 'O tempo para confirmação deste relatório expirou. Por favor, envie o arquivo SPED novamente para gerar um novo relatório.',
                    'credits' => $this->creditService->getBalance($user),
                ], Response::HTTP_GONE); // 410 Gone - recurso não existe mais
            }
            
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

        if (!$resumeUrl) {
            Log::warning('Resume URL não encontrado para cancelamento', [
                'user_id' => $user->id,
                'relatorio_id' => $id,
            ]);
            
            // Sem resume_url, não é possível notificar o n8n
            // Retornar erro pois não podemos cancelar sem notificar o webhook
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível cancelar: resume_url não encontrado.',
            ], Response::HTTP_BAD_REQUEST);
        }

        Log::info('Cancelamento de confirmação de créditos via histórico', [
            'user_id' => $user->id,
            'relatorio_id' => $id,
            'resume_url' => $resumeUrl,
        ]);

        // Envia 'declined' para o webhook usando sendWebhookStatus (método correto para cancelamentos)
        // O n8n será responsável por deletar o registro após receber o declined
        $result = $this->spedUploadService->sendWebhookStatus($resumeUrl, 'declined');

        if (!$result['success']) {
            // Verificar se é erro 409 (execução já finalizada no n8n)
            // Nesse caso, a execução já não existe mais, então podemos deletar localmente
            $httpCode = $result['http_code'] ?? null;
            $message = $result['message'] ?? '';
            
            $isExecutionFinished = $httpCode === 409 || 
                str_contains($message, '409') || 
                str_contains($message, 'has finished already');
            
            if ($isExecutionFinished) {
                Log::info('Execução n8n já finalizada (409), deletando registro localmente', [
                    'user_id' => $user->id,
                    'relatorio_id' => $id,
                    'resume_url' => $resumeUrl,
                ]);
                
                // Deletar o registro localmente já que o n8n não tem mais essa execução
                $relatorio->delete();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Operação cancelada com sucesso.',
                ]);
            }
            
            Log::warning('Falha ao enviar cancelamento para webhook via histórico', [
                'user_id' => $user->id,
                'relatorio_id' => $id,
                'resume_url' => $resumeUrl,
                'error' => $result['message'] ?? 'Erro desconhecido',
                'http_code' => $httpCode,
            ]);
            // Retornar erro se não conseguir enviar para o webhook
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erro ao enviar cancelamento para o servidor.',
            ], Response::HTTP_BAD_GATEWAY);
        }

        Log::info('Cancelamento enviado com sucesso para webhook', [
            'user_id' => $user->id,
            'relatorio_id' => $id,
            'resume_url' => $resumeUrl,
        ]);

        // Deletar o registro localmente após sucesso no envio
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

