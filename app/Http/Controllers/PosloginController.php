<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\XmlDocumento;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PosloginController extends Controller
{
    private const AUTH_VIEW_PREFIX = 'autenticado.autenticado.autenticado_';
    private const AUTH_LAYOUT_VIEW = 'autenticado.autenticado.autenticado_layout';

    public function dashboard(Request $request){
        $dashboardView = self::AUTH_VIEW_PREFIX . 'dashboard';

        if(!view()->exists($dashboardView)){
            abort(404);
        }

        if(!Auth::check()){
            if($request->ajax()){
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        $user = Auth::user();
        $empresasIds = Empresa::where('user_id', $user->id)->pluck('id')->toArray();

        // KPIs - Calculando dados reais quando possível
        $kpi_xml_pendentes = !empty($empresasIds) 
            ? XmlDocumento::where('status', 'pendente')
                ->whereIn('empresa_id', $empresasIds)
                ->count()
            : 0;

        $total_empresas = Empresa::where('user_id', $user->id)->count();

        // Dados mock para CND e SPED (até implementação completa)
        $kpi_cnd_risco = 3; // Mock: empresas com CND vencida ou vencendo em < 5 dias
        $kpi_sped_pendentes = 12; // Mock: SPEDs pendentes no mês atual

        // Lista RAF - Mix de dados reais e mock
        $empresas = Empresa::where('user_id', $user->id)->get();
        $monitoramento_empresas = [];

        foreach ($empresas as $empresa) {
            // Buscar último XML importado
            $ultimoXml = XmlDocumento::where('empresa_id', $empresa->id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Contar XMLs pendentes
            $xmlPendentes = XmlDocumento::where('empresa_id', $empresa->id)
                ->where('status', 'pendente')
                ->count();

            // Dados mock para CND e regime tributário (até implementação completa)
            $regimes = ['Simples Nacional', 'Lucro Presumido', 'Lucro Real'];
            $regime = $regimes[array_rand($regimes)];

            $cndStatuses = ['regular', 'warning', 'danger'];
            $cndStatus = $cndStatuses[array_rand($cndStatuses)];

            // Calcular vencimento CND (mock)
            $cndVencimento = match($cndStatus) {
                'danger' => Carbon::now()->subDays(rand(1, 30)), // Vencida
                'warning' => Carbon::now()->addDays(rand(1, 5)), // Vence em breve
                default => Carbon::now()->addDays(rand(30, 365)) // Regular
            };

            // Calcular conciliação (mock - baseado em XMLs processados)
            $xmlProcessados = XmlDocumento::where('empresa_id', $empresa->id)
                ->where('status', '!=', 'pendente')
                ->count();
            $totalXmls = XmlDocumento::where('empresa_id', $empresa->id)->count();
            $conciliacaoPct = $totalXmls > 0 ? round(($xmlProcessados / $totalXmls) * 100) : 0;

            $monitoramento_empresas[] = [
                'id' => $empresa->id,
                'nome' => $empresa->nome_empresa,
                'cnpj' => $empresa->cnpj,
                'regime' => $regime,
                'cnd_status' => $cndStatus,
                'cnd_vencimento' => $cndVencimento->format('Y-m-d'),
                'xml_pendentes' => $xmlPendentes,
                'ultima_importacao' => $ultimoXml ? $ultimoXml->created_at : null,
                'conciliacao_pct' => $conciliacaoPct,
            ];
        }

        // Se não houver empresas, adicionar dados mock para demonstração
        if (empty($monitoramento_empresas)) {
            $monitoramento_empresas = [
                [
                    'id' => 1,
                    'nome' => 'Tech Solutions Ltda',
                    'cnpj' => '12.345.678/0001-90',
                    'regime' => 'Lucro Presumido',
                    'cnd_status' => 'regular',
                    'cnd_vencimento' => Carbon::now()->addDays(120)->format('Y-m-d'),
                    'xml_pendentes' => 0,
                    'ultima_importacao' => Carbon::now()->subHours(2),
                    'conciliacao_pct' => 100,
                ],
                [
                    'id' => 2,
                    'nome' => 'Mercado Silva',
                    'cnpj' => '98.765.432/0001-10',
                    'regime' => 'Simples Nacional',
                    'cnd_status' => 'danger',
                    'cnd_vencimento' => Carbon::now()->subDays(15)->format('Y-m-d'),
                    'xml_pendentes' => 45,
                    'ultima_importacao' => Carbon::now()->subDays(5),
                    'conciliacao_pct' => 60,
                ],
                [
                    'id' => 3,
                    'nome' => 'Indústria ABC',
                    'cnpj' => '11.222.333/0001-44',
                    'regime' => 'Lucro Real',
                    'cnd_status' => 'warning',
                    'cnd_vencimento' => Carbon::now()->addDays(3)->format('Y-m-d'),
                    'xml_pendentes' => 12,
                    'ultima_importacao' => Carbon::now()->subHours(8),
                    'conciliacao_pct' => 85,
                ],
            ];
        }

        // Status da última sincronização (mock)
        $ultima_sincronizacao = Carbon::now()->subHours(2);

        $data = [
            'kpi_cnd_risco' => $kpi_cnd_risco,
            'kpi_xml_pendentes' => $kpi_xml_pendentes,
            'kpi_sped_pendentes' => $kpi_sped_pendentes,
            'total_empresas' => $total_empresas > 0 ? $total_empresas : count($monitoramento_empresas),
            'monitoramento_empresas' => $monitoramento_empresas,
            'ultima_sincronizacao' => $ultima_sincronizacao,
        ];

        if($request->ajax()){
            return view($dashboardView, $data);
        }
        
        // Para requisições não-AJAX, passar dados para o layout
        // As variáveis serão automaticamente disponíveis na view incluída
        return view(self::AUTH_LAYOUT_VIEW, array_merge([
            'initialView' => $dashboardView
        ], $data));
    }

    private function renderAutenticado(Request $request, string $viewName){
        $autenticadoView = self::AUTH_VIEW_PREFIX . $viewName;

        if(!view()->exists($autenticadoView)){
            abort(404);
        }

        if(!Auth::check()){
            if($request->ajax()){
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está logado',
                    'redirect' => '/login'
                ]);
            }
            return redirect('/login');
        }

        if($request->ajax()){
            return view($autenticadoView);
        }
        
        return view(self::AUTH_LAYOUT_VIEW, [
            'initialView' => $autenticadoView
        ]);
    }

    public function solucoes(Request $request){
        return $this->renderAutenticado($request, 'solucoes');
    }

    public function importacaoXml(Request $request){
        return $this->renderAutenticado($request, 'importacao_xml');
    }

    public function conciliacaoBancaria(Request $request){
        return $this->renderAutenticado($request, 'conciliacao_bancaria');
    }

    public function gestaoCnds(Request $request){
        return $this->renderAutenticado($request, 'gestao_cnds');
    }

    public function inteligenciaTributaria(Request $request){
        return $this->renderAutenticado($request, 'inteligencia_tributaria');
    }

    public function raf(Request $request){
        return $this->renderAutenticado($request, 'raf');
    }

    /**
     * Upload de SPED e envio ao webhook n8n.
     */
    public function uploadSped(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo' => 'required|in:EFD Contribuições,EFD Fiscal',
                'sped' => 'required|file|mimes:txt,text/plain|max:10240', // 10 MB
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            
            if (isset($errors['sped'])) {
                $errorMessages = array_merge($errorMessages, $errors['sped']);
            }
            if (isset($errors['tipo'])) {
                $errorMessages = array_merge($errorMessages, $errors['tipo']);
            }
            
            $message = !empty($errorMessages) 
                ? implode(', ', $errorMessages) 
                : 'Dados inválidos';
            
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $file = $request->file('sped');
        $originalName = $file->getClientOriginalName();
        $fileName = match ($validated['tipo']) {
            'EFD Contribuições' => 'sped_contribuicoes.txt',
            'EFD Fiscal' => 'sped_fiscal.txt',
            default => 'sped.txt',
        };

        $webhookUrl = config('services.webhook.sped_contribuicoes_url')
            ?: 'https://auto.fiscaldock.com.br/webhook-test/consultar-regime-tributario-sped-contribuicoes';
        $webhookUser = config('services.webhook.username');
        $webhookPass = config('services.webhook.password');

        if (empty($webhookUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook não configurado.',
            ], Response::HTTP_BAD_GATEWAY);
        }

        $http = Http::timeout(60);

        if (!empty($webhookUser) && !empty($webhookPass)) {
            $http = $http->withBasicAuth($webhookUser, $webhookPass);
        }

        try {
            $response = $http->attach('sped', file_get_contents($file->getRealPath()), $fileName)
                ->post($webhookUrl, [
                    'tipo' => $validated['tipo'],
                ]);
        } catch (\Throwable $e) {
            Log::error('Falha ao contatar webhook SPED (auth)', [
                'exception' => $e->getMessage(),
                'tipo' => $validated['tipo'],
                'original_name' => $originalName,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Falha ao contatar o webhook. Tente novamente em instantes.',
            ], Response::HTTP_BAD_GATEWAY);
        }

        $csv = $response->body();
        $filenameFromHeader = $this->extractFilenameFromResponse($response);
        $finalFilename = $this->normalizeCsvFilename(
            $filenameFromHeader
                ?? $originalName
                ?? ($fileName ? ('resultado_' . $fileName) : 'resultado.csv')
        );

        if (!$response->successful()) {
            $detail = '';
            $decoded = json_decode($csv, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $detail = $decoded['message'] ?? $decoded['error'] ?? '';
            } else {
                $detail = trim($csv);
            }

            $detail = $detail ? ' Detalhe: ' . mb_substr($detail, 0, 500) : '';

            Log::warning('Webhook SPED falhou (auth)', [
                'status' => $response->status(),
                'detail' => $detail,
                'tipo' => $validated['tipo'],
                'original_name' => $originalName,
                'content_disposition' => $response->header('Content-Disposition'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook retornou erro (' . $response->status() . ').' . $detail,
            ], $response->status());
        }

        $parsed = $this->parseCsvString($csv);

        Log::info('Webhook SPED sucesso (auth)', [
            'tipo' => $validated['tipo'],
            'original_name' => $originalName,
            'status' => $response->status(),
            'header_name' => $response->header('name'),
            'content_disposition' => $response->header('Content-Disposition'),
            'filename_returned' => $filenameFromHeader,
            'filename_final' => $finalFilename,
        ]);

        return response()->json([
            'success' => true,
            'headers' => $parsed['headers'],
            'rows' => $parsed['rows'],
            'csv' => $csv,
            'filename' => $finalFilename,
        ]);
    }

    /**
     * Extrai nome de arquivo da resposta do webhook.
     * Prioridade: header 'name' (API EFD), depois Content-Disposition (RFC 6266).
     */
    private function extractFilenameFromResponse(ClientResponse $response): ?string
    {
        // Prioridade: header 'name' da API EFD Contribuicoes
        $nameHeader = $response->header('name');
        if ($nameHeader && trim($nameHeader) !== '') {
            return trim($nameHeader);
        }

        // Fallback: Content-Disposition (RFC 6266)
        $disposition = $response->header('Content-Disposition');
        if (!$disposition) {
            return null;
        }

        // filename*=UTF-8''nome.csv
        if (preg_match("/filename\\*=UTF-8''([^;]+)/i", $disposition, $matches)) {
            $raw = trim($matches[1], " \t\n\r\0\x0B\"'");
            return urldecode($raw);
        }

        // filename="nome.csv" ou filename=nome.csv
        if (preg_match('/filename=\"?([^\";]+)\"?/i', $disposition, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Garante extensão .csv no nome final.
     */
    private function normalizeCsvFilename(string $name): string
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            return 'resultado.csv';
        }

        // Remove query/fragment se vier junto
        $base = preg_split('/[?#]/', $trimmed, 2)[0];

        // Se já termina com .csv (case-insensitive), mantém
        if (preg_match('/\\.csv$/i', $base)) {
            return $base;
        }

        // Caso contrário, troca extensão existente ou adiciona .csv
        $withoutExt = preg_replace('/\\.[^.]+$/', '', $base);
        return ($withoutExt ?: 'resultado') . '.csv';
    }

    /**
     * Converte string CSV em headers e rows.
     */
    private function parseCsvString(string $csv): array
    {
        $lines = preg_split("/\\r\\n|\\r|\\n/", trim($csv));
        $rows = [];
        $headers = [];

        foreach ($lines as $index => $line) {
            if ($line === '') {
                continue;
            }
            $columns = str_getcsv($line, ';');

            if ($index === 0) {
                $headers = $columns;
                continue;
            }

            $rows[] = $columns;
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }
}
