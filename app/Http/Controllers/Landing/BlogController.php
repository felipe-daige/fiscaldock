<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Support\Landing\BlogPostCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    private const BASE_URL = 'https://fiscaldock.com.br';

    protected string $themeClass = 'theme-default';

    public function index(Request $request)
    {
        if (Auth::check()) {
            return $request->ajax()
                ? response()->json(['success' => true, 'redirect' => '/app/dashboard'])
                : redirect('/app/dashboard');
        }

        $posts = BlogPostCatalog::all();
        $seriesPosts = BlogPostCatalog::featuredSeriesPosts();
        $featuredPost = collect($seriesPosts)->firstWhere('featured', true) ?? ($seriesPosts[0] ?? null);
        $topics = BlogPostCatalog::topics();

        $seo = [
            'title' => 'Conteúdos Fiscais e Tributários | FiscalDock',
            'description' => 'Guias e análises para contadores sobre SPED, EFD, consultas CNPJ, regularidade fiscal, compliance e validação de documentos.',
            'canonical' => self::BASE_URL . '/conteudos',
            'og_title' => 'Central de Conteúdo Fiscal — FiscalDock',
        ];

        if ($request->ajax()) {
            return view('landing_page.blog.index', [
                'posts' => $posts,
                'seriesPosts' => $seriesPosts,
                'featuredPost' => $featuredPost,
                'topics' => $topics,
            ]);
        }

        return view('landing_page.layouts.public', [
            'initialView' => 'blog.index',
            'themeClass' => $this->themeClass,
            'seo' => $seo,
            'posts' => $posts,
            'seriesPosts' => $seriesPosts,
            'featuredPost' => $featuredPost,
            'topics' => $topics,
        ]);
    }

    public function topicEfd(Request $request)
    {
        return $this->topic($request, 'efd');
    }

    public function topic(Request $request, string $tema)
    {
        if (Auth::check()) {
            return $request->ajax()
                ? response()->json(['success' => true, 'redirect' => '/app/dashboard'])
                : redirect('/app/dashboard');
        }

        $topic = BlogPostCatalog::findTopic($tema);
        if (!$topic) {
            abort(404);
        }

        $posts = BlogPostCatalog::postsByTopic($tema);
        $featuredPost = collect($posts)->firstWhere('featured', true) ?? ($posts[0] ?? null);

        $canonical = self::BASE_URL . '/conteudos/' . $tema;

        $seo = [
            'title' => $topic['title'] . ' | Central de Conteúdo Fiscal',
            'description' => $topic['description'],
            'canonical' => $canonical,
            'og_title' => $topic['title'] . ' — FiscalDock',
        ];

        $viewData = [
            'topic' => $topic,
            'posts' => $posts,
            'featuredPost' => $featuredPost,
        ];

        if ($request->ajax()) {
            return view('landing_page.blog.topic', $viewData);
        }

        return view('landing_page.layouts.public', array_merge([
            'initialView' => 'blog.topic',
            'themeClass' => $this->themeClass,
            'seo' => $seo,
        ], $viewData));
    }

    public function show(Request $request, string $slug)
    {
        if (Auth::check()) {
            return $request->ajax()
                ? response()->json(['success' => true, 'redirect' => '/app/dashboard'])
                : redirect('/app/dashboard');
        }

        $post = BlogPostCatalog::findBySlug($slug);

        if (!$post || !view()->exists($post['view'])) {
            abort(404);
        }

        $otherPosts = BlogPostCatalog::relatedByTags($post, 6);
        $seriesPosts = [];
        $seriePrev = null;
        $serieNext = null;
        $seriePos = null;
        $serieTotal = null;

        if (!empty($post['serie'])) {
            $serieAll = collect(BlogPostCatalog::featuredSeriesPosts())
                ->where('serie', $post['serie'])
                ->values();

            $serieTotal = $serieAll->count();
            $currentIdx = $serieAll->search(fn ($p) => $p['slug'] === $slug);

            if ($currentIdx !== false) {
                $seriePos = $currentIdx + 1;
                $seriePrev = $currentIdx > 0 ? $serieAll[$currentIdx - 1] : null;
                $serieNext = $currentIdx < $serieTotal - 1 ? $serieAll[$currentIdx + 1] : null;
            }

            $seriesPosts = $serieAll->where('slug', '!=', $slug)->values()->all();
        }

        $seo = [
            'title' => $post['title'] . ' | FiscalDock',
            'description' => $post['meta_description'],
            'canonical' => self::BASE_URL . '/conteudos/' . $post['slug'],
            'og_type' => 'article',
        ];

        $viewData = [
            'post' => $post,
            'otherPosts' => $otherPosts,
            'seriesPosts' => $seriesPosts,
            'seriePrev' => $seriePrev,
            'serieNext' => $serieNext,
            'seriePos' => $seriePos,
            'serieTotal' => $serieTotal,
        ];

        if ($request->ajax()) {
            return view('landing_page.blog.show', $viewData);
        }

        return view('landing_page.layouts.public', array_merge([
            'initialView' => 'blog.show',
            'themeClass' => $this->themeClass,
            'seo' => $seo,
        ], $viewData));
    }
}
