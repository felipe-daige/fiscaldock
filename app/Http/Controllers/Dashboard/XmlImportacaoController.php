<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ImportacaoXml;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use ZipArchive;

class XmlImportacaoController extends Controller
{
    private const AUTH_VIEW_PREFIX = 'autenticado.monitoramento.';
    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Página de importação de XMLs.
     */
    public function index(Request $request)
    {
        $xmlView = self::AUTH_VIEW_PREFIX . 'xml';

        if (!view()->exists($xmlView)) {
            abort(404);
        }

        if (!Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();

        // Buscar clientes do usuário para o select
        $clientes = Cliente::where('user_id', $user->id)
            ->orderBy('razao_social')
            ->get();

        // Últimas importações do usuário
        $importacoes = ImportacaoXml::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $data = [
            'clientes' => $clientes,
            'importacoes' => $importacoes,
            'credits' => $this->creditService->getBalance($user),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($xmlView, $data)->render();
            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $xmlView
        ], $data));
    }

    /**
     * Inicia importação de XMLs enviando para n8n.
     */
    public function importar(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'tipo_documento' => 'required|in:NFE,NFSE,CTE',
            'modo_envio' => 'required|in:zip,xml',
            'cliente_id' => 'nullable|integer|exists:clientes,id',
            'tab_id' => 'required|string|max:36',
            'arquivos' => 'required|array|min:1|max:100',
            'arquivos.*.nome' => 'required|string|max:255',
            'arquivos.*.tipo' => 'required|string|max:100',
            'arquivos.*.conteudo_base64' => 'required|string',
        ]);

        // Calcular tamanho total
        $tamanhoTotal = 0;
        $totalArquivos = count($validated['arquivos']);

        foreach ($validated['arquivos'] as $arquivo) {
            $tamanhoTotal += strlen(base64_decode($arquivo['conteudo_base64']));
        }

        // Limite de 200MB total
        if ($tamanhoTotal > 200 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'error' => 'Tamanho total dos arquivos excede o limite de 200MB.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verificar webhook configurado
        $webhookUrl = config('services.webhook.monitoramento_importacao_xml_url');

        if (empty($webhookUrl)) {
            Log::error('XmlImportacao: webhook não configurado');
            return response()->json([
                'success' => false,
                'error' => 'Configuração de webhook ausente. Contate o suporte.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            // Criar registro de importação
            $importacao = ImportacaoXml::create([
                'user_id' => $user->id,
                'cliente_id' => $validated['cliente_id'] ?? null,
                'tipo_documento' => $validated['tipo_documento'],
                'modo_envio' => $validated['modo_envio'],
                'total_arquivos' => $totalArquivos,
                'tamanho_total_bytes' => $tamanhoTotal,
                'status' => 'pendente',
                'iniciado_em' => now(),
            ]);

            Log::info('XmlImportacao: registro criado', [
                'importacao_id' => $importacao->id,
                'user_id' => $user->id,
                'tipo_documento' => $validated['tipo_documento'],
                'modo_envio' => $validated['modo_envio'],
                'total_arquivos' => $totalArquivos,
                'tamanho_bytes' => $tamanhoTotal,
            ]);

            // Salvar arquivos em disco (mais eficiente que enviar base64)
            $pasta = storage_path("app/temp/xml-imports/{$importacao->id}");
            if (!is_dir($pasta)) {
                mkdir($pasta, 0755, true);
            }

            $nomesArquivos = [];
            foreach ($validated['arquivos'] as $arquivo) {
                $conteudo = base64_decode($arquivo['conteudo_base64']);
                // Sanitizar nome do arquivo
                $nomeSeguro = preg_replace('/[^a-zA-Z0-9._-]/', '_', $arquivo['nome']);
                $caminhoArquivo = "{$pasta}/{$nomeSeguro}";
                file_put_contents($caminhoArquivo, $conteudo);
                $nomesArquivos[] = $nomeSeguro;
            }

            Log::info('XmlImportacao: arquivos salvos em disco', [
                'importacao_id' => $importacao->id,
                'pasta' => $pasta,
                'arquivos' => $nomesArquivos,
            ]);

            // Montar payload para n8n (apenas paths, sem base64)
            $payload = [
                'user_id' => $user->id,
                'importacao_id' => $importacao->id,
                'tab_id' => $validated['tab_id'],
                'tipo_documento' => $validated['tipo_documento'],
                'modo_envio' => $validated['modo_envio'],
                'cliente_id' => $validated['cliente_id'] ?? null,
                'progress_url' => url('/api/monitoramento/xml/importacao/progress'),
                'pasta' => $pasta,
                'arquivos' => $nomesArquivos,
            ];

            // Enviar para n8n (payload leve, apenas paths)
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-API-Token' => config('services.api.token'),
                ])
                ->post($webhookUrl, $payload);

            if ($response->successful()) {
                $importacao->update(['status' => 'processando']);

                Log::info('XmlImportacao: enviado para n8n com sucesso', [
                    'importacao_id' => $importacao->id,
                    'response_status' => $response->status(),
                ]);

                return response()->json([
                    'success' => true,
                    'importacao_id' => $importacao->id,
                    'message' => 'Importação iniciada com sucesso.',
                ]);
            } else {
                $importacao->update([
                    'status' => 'erro',
                    'erro_mensagem' => 'Erro ao enviar para processamento: ' . $response->status(),
                ]);

                Log::error('XmlImportacao: erro na resposta do n8n', [
                    'importacao_id' => $importacao->id,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Erro ao iniciar processamento. Tente novamente.',
                ], Response::HTTP_BAD_GATEWAY);
            }

        } catch (\Exception $e) {
            Log::error('XmlImportacao: exceção ao enviar', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($importacao)) {
                $importacao->update([
                    'status' => 'erro',
                    'erro_mensagem' => 'Erro interno: ' . $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Erro interno ao processar importação.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * SSE para acompanhar progresso de importação XML.
     */
    public function streamProgresso(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userId = auth()->id();
        $tabId = $request->query('tab_id');

        if (!$tabId) {
            return response()->json([
                'success' => false,
                'error' => 'tab_id obrigatório.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Usa a mesma chave de cache do progresso SPED
        $cacheKey = "progresso:{$userId}:{$tabId}";

        Log::info('SSE XML streamProgresso iniciado', [
            'user_id' => $userId,
            'tab_id' => $tabId,
            'cache_key' => $cacheKey,
        ]);

        return response()->stream(function () use ($cacheKey, $userId, $tabId) {
            $tentativas = 0;
            $maxTentativas = 600; // 10 minutos (XMLs podem demorar mais)
            $lastDataHash = null;

            // Enviar comentário inicial
            echo ": SSE connection established for XML progress stream (user:{$userId}, tab:{$tabId})\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            while ($tentativas < $maxTentativas) {
                try {
                    // Lê dados do cache (n8n envia via API)
                    $data = Cache::get($cacheKey);

                    if ($data) {
                        // Calcular hash para detectar mudanças
                        $currentHash = md5(json_encode($data));

                        // Só enviar se os dados mudaram
                        if ($currentHash !== $lastDataHash) {
                            $lastDataHash = $currentHash;

                            // Enviar dados de progresso
                            echo "data: " . json_encode($data) . "\n\n";

                            if (ob_get_level() > 0) {
                                ob_flush();
                            }
                            flush();

                            // Se status é final, encerrar a conexão
                            if (in_array($data['status'] ?? '', ['concluido', 'erro'])) {
                                Log::info('SSE XML streamProgresso: status final recebido', [
                                    'user_id' => $userId,
                                    'tab_id' => $tabId,
                                    'status' => $data['status'],
                                ]);
                                // Limpar cache após status final
                                Cache::forget($cacheKey);
                                break;
                            }
                        }
                    }

                    // Verificar se a conexão ainda está ativa
                    if (connection_aborted()) {
                        Log::info('SSE XML streamProgresso: conexão abortada pelo cliente', [
                            'user_id' => $userId,
                            'tab_id' => $tabId,
                        ]);
                        break;
                    }

                    sleep(1);
                    $tentativas++;

                } catch (\Exception $e) {
                    Log::error('SSE XML streamProgresso: erro no loop', [
                        'user_id' => $userId,
                        'tab_id' => $tabId,
                        'error' => $e->getMessage(),
                    ]);
                    sleep(1);
                    $tentativas++;
                    if (connection_aborted()) {
                        break;
                    }
                }
            }

            // Se chegou no limite, encerrar
            if ($tentativas >= $maxTentativas) {
                echo "data: " . json_encode([
                    'status' => 'timeout',
                    'progresso' => 0,
                    'mensagem' => 'Tempo limite atingido. Tente novamente.',
                ]) . "\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                Log::warning('SSE XML streamProgresso: timeout', [
                    'user_id' => $userId,
                    'tab_id' => $tabId,
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Valida arquivo antes de importar (conta XMLs em ZIPs, detecta tipo).
     */
    public function validar(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não autenticado.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $validated = $request->validate([
            'arquivo' => 'required|array',
            'arquivo.nome' => 'required|string|max:255',
            'arquivo.conteudo_base64' => 'required|string',
        ]);

        $fileName = $validated['arquivo']['nome'];
        $base64Content = $validated['arquivo']['conteudo_base64'];

        // Check base64 size before decoding (avoid memory issues)
        $estimatedSize = (int) (strlen($base64Content) * 0.75);
        if ($estimatedSize > 50 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'error' => 'Arquivo excede 50MB.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $content = base64_decode($base64Content, true);
        if ($content === false) {
            return response()->json([
                'success' => false,
                'error' => 'Conteúdo base64 inválido.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (str_ends_with(strtolower($fileName), '.zip')) {
            return $this->validarZip($content, $fileName);
        } else {
            return $this->validarXml($content, $fileName);
        }
    }

    /**
     * Valida arquivo ZIP e conta XMLs dentro.
     */
    private function validarZip(string $content, string $fileName): JsonResponse
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xml_validate_');
        if (!$tempFile) {
            return response()->json([
                'success' => false,
                'error' => 'Erro ao criar arquivo temporário.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            file_put_contents($tempFile, $content);

            $zip = new ZipArchive();
            $result = $zip->open($tempFile);

            if ($result !== true) {
                return response()->json([
                    'success' => false,
                    'error' => 'ZIP corrompido ou inválido.',
                ]);
            }

            $totalXmls = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if ($entryName && str_ends_with(strtolower($entryName), '.xml')) {
                    $totalXmls++;
                }
            }

            $zip->close();

            return response()->json([
                'success' => true,
                'tipo' => 'zip',
                'total_xmls' => $totalXmls,
                'mensagem' => $totalXmls === 0 ? 'Nenhum XML encontrado no ZIP' : null,
            ]);

        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Valida arquivo XML e tenta detectar o tipo de documento.
     */
    private function validarXml(string $content, string $fileName): JsonResponse
    {
        // Suppress libxml errors to handle them gracefully
        $previousErrors = libxml_use_internal_errors(true);

        try {
            $dom = new \DOMDocument();
            $loaded = $dom->loadXML($content);

            if (!$loaded) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                return response()->json([
                    'success' => false,
                    'error' => 'XML mal formado.',
                ]);
            }

            libxml_clear_errors();

            // Try to detect document type from root element and content
            $tipoDocumento = $this->detectarTipoDocumento($dom);

            return response()->json([
                'success' => true,
                'tipo' => 'xml',
                'total_xmls' => 1,
                'tipo_documento' => $tipoDocumento,
            ]);

        } finally {
            libxml_use_internal_errors($previousErrors);
        }
    }

    /**
     * Detecta o tipo de documento fiscal a partir do DOM.
     */
    private function detectarTipoDocumento(\DOMDocument $dom): ?string
    {
        $rootElement = $dom->documentElement;
        if (!$rootElement) {
            return null;
        }

        $rootName = strtolower($rootElement->localName);

        // NF-e detection
        if (in_array($rootName, ['nfeproc', 'nfe', 'enviarnfe'])) {
            return 'NFE';
        }

        // CT-e detection
        if (in_array($rootName, ['cteproc', 'cte', 'enviarcte'])) {
            return 'CTE';
        }

        // NFS-e detection (various formats)
        if (str_contains($rootName, 'nfse') || str_contains($rootName, 'infnfse')) {
            return 'NFSE';
        }

        // Check for NFS-e tags inside the document
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('x', $rootElement->namespaceURI ?: '');

        // Look for common NFS-e elements
        $nfseElements = $dom->getElementsByTagName('InfNfse');
        if ($nfseElements->length > 0) {
            return 'NFSE';
        }

        $nfseElements = $dom->getElementsByTagName('Nfse');
        if ($nfseElements->length > 0) {
            return 'NFSE';
        }

        // Look for NF-e elements inside
        $nfeElements = $dom->getElementsByTagName('infNFe');
        if ($nfeElements->length > 0) {
            return 'NFE';
        }

        // Look for CT-e elements inside
        $cteElements = $dom->getElementsByTagName('infCte');
        if ($cteElements->length > 0) {
            return 'CTE';
        }

        return null;
    }

    /**
     * Verifica se a requisição é AJAX (navegação SPA).
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Redireciona para login preservando URL.
     */
    private function redirectToLogin(Request $request)
    {
        session(['url.intended' => $request->fullUrl()]);
        return redirect()->route('login');
    }
}
