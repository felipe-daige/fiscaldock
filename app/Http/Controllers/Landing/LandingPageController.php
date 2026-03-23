<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingPageController extends Controller
{
    /**
     * Tema padrão usado nas páginas públicas.
     */
    protected string $themeClass = 'theme-default';

    public function inicio(Request $request)
    {
        return $this->renderLanding($request, 'paginas.inicio', [
            'title' => 'FiscalDock | Inteligência Fiscal para Contadores',
            'description' => 'Importe seus arquivos SPED, monitore participantes e detecte riscos fiscais antes da auditoria. Plataforma completa para contadores e escritórios contábeis.',
        ]);
    }

    public function solucoes(Request $request)
    {
        return $this->renderLanding($request, 'solucoes.index', [
            'title' => 'Soluções — FiscalDock | Compliance Fiscal Automatizado',
            'description' => 'Importação de SPED, monitoramento de fornecedores, consultas tributárias e dashboards analíticos. Tudo que seu escritório precisa.',
        ]);
    }

    public function faq(Request $request)
    {
        return $this->renderLanding($request, 'paginas.faq', [
            'title' => 'Perguntas Frequentes — FiscalDock',
            'description' => 'Tire suas dúvidas sobre importação de SPED, monitoramento fiscal, créditos e segurança dos dados.',
        ]);
    }

    public function precos(Request $request)
    {
        return $this->renderLanding($request, 'paginas.precos', [
            'title' => 'Preços — FiscalDock | Planos para Contadores',
            'description' => 'Planos acessíveis para escritórios contábeis de todos os tamanhos. Comece gratuitamente.',
        ]);
    }

    /**
     * Renderiza uma view da landing page aplicando o tema padrão e redirecionando
     * usuários autenticados para o dashboard.
     */
    private function renderLanding(Request $request, string $viewName, array $seo = [])
    {
        $fullViewName = "landing_page.$viewName";

        if (!view()->exists($fullViewName)) {
            abort(404);
        }

        if (Auth::check()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Você já está logado',
                    'redirect' => '/app/dashboard',
                ]);
            }

            return redirect('/app/dashboard');
        }

        if ($request->ajax()) {
            return view($fullViewName);
        }

        return view('landing_page.layouts.public', [
            'initialView' => $viewName,
            'themeClass' => $this->themeClass,
            'seo' => $seo,
        ]);
    }
}
