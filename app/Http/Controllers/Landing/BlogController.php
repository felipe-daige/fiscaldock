<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    protected string $themeClass = 'theme-default';

    private array $posts = [
        [
            'slug' => '5-riscos-fiscais-que-todo-contador-deveria-monitorar',
            'title' => '5 Riscos Fiscais que Todo Contador Deveria Monitorar',
            'excerpt' => 'Fornecedores irregulares, IE suspensa, empresas no CEIS — descubra os riscos que podem custar multas aos seus clientes e como monitorá-los automaticamente.',
            'meta_description' => 'Conheça os 5 principais riscos fiscais que contadores precisam monitorar: CNPJ irregular, IE suspensa, CEIS, Simples Nacional e divergências no SPED.',
            'categoria' => 'Compliance',
            'data' => '2026-03-15',
            'tempo_leitura' => '6 min',
            'view' => 'landing_page.blog.posts.riscos-fiscais',
        ],
        [
            'slug' => 'importacao-sped-automatizada-economiza-horas',
            'title' => 'Como a Importação Automatizada de SPED Economiza Horas do Seu Escritório',
            'excerpt' => 'O processo manual de análise de SPED consome dias. Veja como a importação automática com extração de participantes e notas pode transformar sua rotina.',
            'meta_description' => 'Saiba como automatizar a importação de arquivos SPED EFD ICMS/IPI e PIS/COFINS para economizar horas de trabalho no seu escritório contábil.',
            'categoria' => 'Produtividade',
            'data' => '2026-03-10',
            'tempo_leitura' => '5 min',
            'view' => 'landing_page.blog.posts.importacao-sped',
        ],
        [
            'slug' => 'fornecedor-irregular-como-identificar-antes-da-auditoria',
            'title' => 'Fornecedor Irregular: Como Identificar Antes da Auditoria',
            'excerpt' => 'Um fornecedor com CNPJ baixado ou listado no CEIS pode invalidar créditos tributários inteiros. Aprenda a identificar esses riscos antes que o fisco encontre.',
            'meta_description' => 'Aprenda a identificar fornecedores irregulares antes da auditoria fiscal. Consulta CNPJ, SINTEGRA, CEIS e monitoramento contínuo para contadores.',
            'categoria' => 'Due Diligence',
            'data' => '2026-03-05',
            'tempo_leitura' => '7 min',
            'view' => 'landing_page.blog.posts.fornecedor-irregular',
        ],
    ];

    public function index(Request $request)
    {
        if (Auth::check()) {
            return $request->ajax()
                ? response()->json(['success' => true, 'redirect' => '/app/dashboard'])
                : redirect('/app/dashboard');
        }

        $seo = [
            'title' => 'Blog — FiscalDock | Conteúdo para Contadores',
            'description' => 'Artigos sobre compliance fiscal, SPED, riscos tributários e boas práticas para escritórios contábeis.',
        ];

        if ($request->ajax()) {
            return view('landing_page.blog.index', ['posts' => $this->posts]);
        }

        return view('landing_page.layouts.public', [
            'initialView' => 'blog.index',
            'themeClass' => $this->themeClass,
            'seo' => $seo,
            'posts' => $this->posts,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        if (Auth::check()) {
            return $request->ajax()
                ? response()->json(['success' => true, 'redirect' => '/app/dashboard'])
                : redirect('/app/dashboard');
        }

        $post = collect($this->posts)->firstWhere('slug', $slug);

        if (!$post || !view()->exists($post['view'])) {
            abort(404);
        }

        $otherPosts = collect($this->posts)->where('slug', '!=', $slug)->values()->all();

        $seo = [
            'title' => $post['title'] . ' — Blog FiscalDock',
            'description' => $post['meta_description'],
        ];

        if ($request->ajax()) {
            return view('landing_page.blog.show', ['post' => $post, 'otherPosts' => $otherPosts]);
        }

        return view('landing_page.layouts.public', [
            'initialView' => 'blog.show',
            'themeClass' => $this->themeClass,
            'seo' => $seo,
            'post' => $post,
            'otherPosts' => $otherPosts,
        ]);
    }
}
