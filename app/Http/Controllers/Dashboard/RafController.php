<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\RafConsultaPendente;
use App\Services\CreditService;
use App\Services\Sped\SpedUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        
        // #region agent log
        try {
            $debugLogPath = '/opt/hub_contabil/.cursor/debug.log';
            $debugLogDir = dirname($debugLogPath);
            if (!is_dir($debugLogDir)) {
                @mkdir($debugLogDir, 0755, true);
            }
            if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                file_put_contents($debugLogPath, json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'A',
                    'location' => 'RafController.php:46',
                    'message' => 'Function entry - historico method',
                    'data' => [
                        'user_id' => $userId,
                        'user_id_type' => gettype($userId),
                        'user_authenticated' => $user !== null,
                        'user_email' => $user?->email ?? null,
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n", FILE_APPEND);
            }
        } catch (\Throwable $e) {
            // Ignorar erros de log de debug - não são críticos
        }
        // #endregion
        
        Log::debug('RafController::historico - Buscando relatórios pendentes', [
            'user_id' => $userId,
            'user_id_type' => gettype($userId),
            'user_authenticated' => $user !== null,
        ]);
        
        // Verificar TODOS os registros na tabela (independente de user_id)
        $todosRegistros = RafConsultaPendente::all();
        $totalGeral = $todosRegistros->count();
        $registrosPorUserId = $todosRegistros->groupBy('user_id')->map->count();
        
        // #region agent log
        try {
            if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                file_put_contents($debugLogPath, json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'C',
                    'location' => 'RafController.php:56',
                    'message' => 'Before query - all records check',
                    'data' => [
                        'total_geral' => $totalGeral,
                        'registros_por_user_id' => $registrosPorUserId->toArray(),
                        'todos_user_ids' => $todosRegistros->pluck('user_id')->unique()->toArray(),
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n", FILE_APPEND);
            }
        } catch (\Throwable $e) {}
        // #endregion
        
        // Query alternativa direta para verificar se há registros no banco
        $totalNoBanco = RafConsultaPendente::where('user_id', $userId)->count();
        
        // #region agent log
        try {
            if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                file_put_contents($debugLogPath, json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'RafController.php:62',
                    'message' => 'After direct query - before scope query',
                    'data' => [
                        'user_id' => $userId,
                        'total_no_banco_direto' => $totalNoBanco,
                        'query_sql' => RafConsultaPendente::where('user_id', $userId)->toSql(),
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n", FILE_APPEND);
            }
        } catch (\Throwable $e) {}
        // #endregion
        
        Log::debug('RafController::historico - Total de registros no banco (query direta)', [
            'user_id' => $userId,
            'total_no_banco' => $totalNoBanco,
        ]);
        
        // #region agent log
        try {
            if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                file_put_contents($debugLogPath, json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'RafController.php:68',
                    'message' => 'Before scope query execution',
                    'data' => [
                        'user_id' => $userId,
                        'scope_method' => 'doUsuario',
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n", FILE_APPEND);
            }
        } catch (\Throwable $e) {}
        // #endregion
        
        // Query de teste direta usando DB::table() para verificar se os dados existem
        $testQuery = DB::table('raf_consulta_pendente')
            ->where('user_id', $userId)
            ->get();
        
        Log::debug('RafController::historico - Query de teste direta (DB::table)', [
            'user_id' => $userId,
            'total_encontrados_teste' => $testQuery->count(),
            'registros_teste' => $testQuery->toArray(),
        ]);
        
        // Query principal corrigida - removendo scope pendentes() que não faz nada
        // e usando where diretamente para garantir que funciona
        $relatorios = RafConsultaPendente::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // #region agent log
        try {
            if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                file_put_contents($debugLogPath, json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'RafController.php:75',
                    'message' => 'After scope query execution',
                    'data' => [
                        'user_id' => $userId,
                        'total_encontrados' => $relatorios->count(),
                        'relatorios_ids' => $relatorios->pluck('id')->toArray(),
                        'relatorios_user_ids' => $relatorios->pluck('user_id')->unique()->toArray(),
                        'is_collection' => $relatorios instanceof \Illuminate\Database\Eloquent\Collection,
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n", FILE_APPEND);
            }
        } catch (\Throwable $e) {}
        // #endregion
        
        $totalEncontrados = $relatorios->count();
        
        Log::info('RafController::historico - Relatórios encontrados', [
            'user_id' => $userId,
            'total_relatorios' => $totalEncontrados,
            'total_no_banco' => $totalNoBanco,
            'total_geral' => $totalGeral,
            'relatorios_ids' => $relatorios->pluck('id')->toArray(),
        ]);

        $data = [
            'relatorios' => $relatorios,
            'total_pendentes' => $totalEncontrados,
        ];
        
        // #region agent log
        try {
            if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                file_put_contents($debugLogPath, json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'D',
                    'location' => 'RafController.php:120',
                    'message' => 'Data prepared for view',
                    'data' => [
                        'user_id' => $userId,
                        'total_pendentes' => $totalEncontrados,
                        'relatorios_count' => $relatorios->count(),
                        'relatorios_is_collection' => $relatorios instanceof \Illuminate\Database\Eloquent\Collection,
                        'data_keys' => array_keys($data),
                        'data_relatorios_count' => isset($data['relatorios']) ? $data['relatorios']->count() : 0,
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n", FILE_APPEND);
            }
        } catch (\Throwable $e) {}
        // #endregion
        
        // Verificação adicional: garantir que os dados estão sendo passados corretamente
        Log::debug('RafController::historico - Dados preparados para view', [
            'user_id' => $userId,
            'total_pendentes' => $totalEncontrados,
            'relatorios_count' => $relatorios->count(),
            'relatorios_is_collection' => $relatorios instanceof \Illuminate\Database\Eloquent\Collection,
            'data_keys' => array_keys($data),
        ]);

        // #region agent log
        try {
            if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                file_put_contents($debugLogPath, json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'D',
                    'location' => 'RafController.php:229',
                    'message' => 'Before returning view',
                    'data' => [
                        'is_ajax' => $this->isAjaxRequest($request),
                        'total_pendentes' => $totalEncontrados,
                        'relatorios_count' => $relatorios->count(),
                        'view_name' => $historicoView,
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n", FILE_APPEND);
            }
        } catch (\Throwable $e) {}
        // #endregion
        
        Log::debug('RafController::historico - Retornando view', [
            'is_ajax' => $this->isAjaxRequest($request),
            'total_pendentes' => $totalEncontrados,
            'relatorios_count' => $relatorios->count(),
        ]);

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($historicoView, $data)->render();
            
            // #region agent log
            try {
                if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                    file_put_contents($debugLogPath, json_encode([
                        'sessionId' => 'debug-session',
                        'runId' => 'run1',
                        'hypothesisId' => 'D',
                        'location' => 'RafController.php:250',
                        'message' => 'View rendered for AJAX',
                        'data' => [
                            'html_length' => strlen($renderedView),
                            'contains_relatorios' => strpos($renderedView, 'data-relatorio-id') !== false,
                            'relatorios_count_in_html' => substr_count($renderedView, 'data-relatorio-id'),
                        ],
                        'timestamp' => time() * 1000
                    ]) . "\n", FILE_APPEND);
                }
            } catch (\Throwable $e) {}
            // #endregion
            
            Log::debug('RafController::historico - View renderizada para AJAX', [
                'html_length' => strlen($renderedView),
                'contains_relatorios' => strpos($renderedView, 'data-relatorio-id') !== false,
                'relatorios_count_in_html' => substr_count($renderedView, 'data-relatorio-id'),
            ]);
            
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
        $valorCreditos = (int) ceil((float) $relatorio->valor_total_consulta);
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
            $isAjax = $request->ajax();
            Log::debug('RafController::isAjaxRequest - método ajax()', [
                'is_ajax' => $isAjax,
                'x_requested_with' => $request->header('X-Requested-With'),
                'accept' => $request->header('Accept'),
            ]);
            return $isAjax;
        }

        // Fallback para Laravel 12: verifica headers
        $xRequestedWith = $request->header('X-Requested-With');
        $wantsJson = $request->wantsJson();
        $expectsJson = $request->expectsJson();
        $isAjax = $wantsJson 
            || $expectsJson
            || $xRequestedWith === 'XMLHttpRequest';
        
        Log::debug('RafController::isAjaxRequest - fallback', [
            'is_ajax' => $isAjax,
            'x_requested_with' => $xRequestedWith,
            'wants_json' => $wantsJson,
            'expects_json' => $expectsJson,
            'accept' => $request->header('Accept'),
        ]);
        
        return $isAjax;
    }
}

