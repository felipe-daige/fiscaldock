<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class LandingPage extends Controller
{
    /**
     * Tema padrão usado nas páginas públicas.
     */
    protected string $themeClass = 'theme-default';

    public function inicio(Request $request){
        return $this->renderLanding($request, 'inicio');
    }

    public function solucoes(Request $request){
        return $this->renderLanding($request, 'solucoes');
    }

    public function sobre(Request $request){
        return $this->renderLanding($request, 'sobre');
    }

    public function beneficios(Request $request){
        return $this->renderLanding($request, 'beneficios');
    }

    public function impactos(Request $request){
        return $this->renderLanding($request, 'impactos');
    }

    public function faq(Request $request){
        return $this->renderLanding($request, 'faq');
    }

    public function precos(Request $request){
        return $this->renderLanding($request, 'precos');
    }

    public function questionario(Request $request){
        return $this->renderLanding($request, 'questionario');
    }

    public function importacaoXml(Request $request){
        return $this->renderLanding($request, 'importacao_xml');
    }

    public function conciliacaoBancaria(Request $request){
        return $this->renderLanding($request, 'conciliacao_bancaria');
    }

    public function gestaoCnds(Request $request){
        return $this->renderLanding($request, 'gestao_cnds');
    }

    public function inteligenciaTributaria(Request $request){
        return $this->renderLanding($request, 'inteligencia_tributaria');
    }

    public function raf(Request $request){
        return $this->renderLanding($request, 'raf');
    }

    /**
     * Endpoint público para upload de SPED (EFD Contribuições) que encaminha
     * o arquivo ao webhook e retorna JSON para renderização da tabela/CSV.
     */
    public function uploadSpedPublic(Request $request)
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

        $http = Http::timeout(120);

        if (!empty($webhookUser) && !empty($webhookPass)) {
            $http = $http->withBasicAuth($webhookUser, $webhookPass);
        }

        try {
            $response = $http->attach('sped', file_get_contents($file->getRealPath()), $fileName)
                ->post($webhookUrl, [
                    'tipo' => $validated['tipo'],
                ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Falha ao contatar o webhook. Tente novamente em instantes.',
            ], Response::HTTP_BAD_GATEWAY);
        }

        $csv = $response->body();

        if (!$response->successful()) {
            $detail = '';
            $decoded = json_decode($csv, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $detail = $decoded['message'] ?? $decoded['error'] ?? '';
            } else {
                $detail = trim($csv);
            }

            $detail = $detail ? ' Detalhe: ' . mb_substr($detail, 0, 500) : '';

            Log::warning('Webhook SPED falhou', [
                'status' => $response->status(),
                'detail' => $detail,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook retornou erro (' . $response->status() . ').' . $detail,
            ], $response->status());
        }

        $parsed = $this->parseCsvString($csv);

        return response()->json([
            'success' => true,
            'headers' => $parsed['headers'],
            'rows' => $parsed['rows'],
            'csv' => $csv,
            'filename' => $fileName ? ('resultado_' . $fileName) : 'resultado.csv',
        ]);
    }

    /**
     * Converte string CSV (separador ;) em headers e rows.
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

    /**
     * Renderiza uma view da landing page aplicando o tema padrão e redirecionando
     * usuários autenticados para o dashboard.
     */
    private function renderLanding(Request $request, string $viewName){
        $fullViewName = "landing_page.$viewName";

        if(!view()->exists($fullViewName)){
            abort(404);
        }

        if(Auth::check()){
            if($request->ajax()){
                return response()->json([
                    'success' => true,
                    'message' => 'Você já está logado',
                    'redirect' => '/dashboard'
                ]);
            }

            return redirect('/dashboard');
        }

        if($request->ajax()){
            return view($fullViewName);
        }

        return view("landing_page.layout", [
            'initialView' => $viewName,
            'themeClass' => $this->themeClass
        ]);
    }
}
