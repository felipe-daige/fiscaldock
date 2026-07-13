<?php

use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\get;

it('publica a Central de Conteúdo Fiscal na nova URL canônica', function () {
    get('/conteudos')
        ->assertOk()
        ->assertSee('Central de', false)
        ->assertSee('Conteúdo Fiscal.', false)
        ->assertSee('Índice temático')
        ->assertSee('Guia de EFD para Contadores.')
        ->assertSee('Biblioteca técnica')
        ->assertSee('id="content-search"', false)
        ->assertSee('<link rel="canonical" href="https://fiscaldock.com.br/conteudos">', false)
        ->assertDontSee('Blog FiscalDock');
});

it('serve temas e artigos pela família de URLs de conteúdos', function () {
    get('/conteudos/efd')
        ->assertOk()
        ->assertSee('EFD para Contadores')
        ->assertSee('https://fiscaldock.com.br/conteudos/efd', false);

    get('/conteudos/checklist-de-revisao-da-efd-antes-da-entrega')
        ->assertOk()
        ->assertSee('Checklist de Revisão da EFD Antes da Entrega')
        ->assertSee('https://fiscaldock.com.br/conteudos/checklist-de-revisao-da-efd-antes-da-entrega', false);
});

it('redireciona permanentemente todas as URLs legadas do blog', function () {
    get('/blog')->assertStatus(301)->assertRedirect('/conteudos');
    get('/blog/efd')->assertStatus(301)->assertRedirect('/conteudos/efd');
    get('/blog/tema/consultas')->assertStatus(301)->assertRedirect('/conteudos/consultas');
    get('/blog/checklist-de-revisao-da-efd-antes-da-entrega')
        ->assertStatus(301)
        ->assertRedirect('/conteudos/checklist-de-revisao-da-efd-antes-da-entrega');
});

it('lista apenas URLs canônicas de conteúdos no sitemap', function () {
    Cache::forget('sitemap_xml_conteudos_v3');

    get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('https://fiscaldock.com.br/conteudos</loc>', false)
        ->assertSee('https://fiscaldock.com.br/conteudos/efd</loc>', false)
        ->assertSee('https://fiscaldock.com.br/conteudos/checklist-de-revisao-da-efd-antes-da-entrega</loc>', false)
        ->assertDontSee('https://fiscaldock.com/blog', false)
        ->assertDontSee('https://fiscaldock.com.br/blog', false);
});

it('posiciona dúvidas depois de conteúdos no sitemap', function () {
    Cache::forget('sitemap_xml_conteudos_v3');

    get('/sitemap.xml')
        ->assertOk()
        ->assertSeeInOrder([
            'https://fiscaldock.com.br/conteudos</loc>',
            'https://fiscaldock.com.br/duvidas</loc>',
        ], false);
});

it('renomeia a navegação pública sem usar o interceptor autenticado', function () {
    get('/conteudos')
        ->assertOk()
        ->assertSeeInOrder(['>Conteúdos</a>', '>Dúvidas</a>'], false)
        ->assertSee('href="/conteudos"', false)
        ->assertSee('>Conteúdos</a>', false)
        ->assertDontSee('href="/conteudos" data-link', false);
});

it('mantém índice, tema e artigo preparados para telas móveis', function () {
    get('/conteudos')
        ->assertOk()
        ->assertSee('@media (max-width: 360px)', false)
        ->assertSee('data-content-filter="all"', false)
        ->assertSee('type="search"', false)
        ->assertSee('cookie-consent-actions', false);

    get('/conteudos/efd')
        ->assertOk()
        ->assertSee('content-topic-page', false)
        ->assertSee('lg:sticky lg:top-24', false)
        ->assertSee('topic-primary-cta', false);

    get('/conteudos/checklist-de-revisao-da-efd-antes-da-entrega')
        ->assertOk()
        ->assertSee('content-article-page', false)
        ->assertSee('blog-table-wrap', false)
        ->assertSee('content-article-cta', false)
        ->assertSee('lg:sticky lg:top-24', false);
});

it('mantém o header público fixo e acessível durante a rolagem mobile', function () {
    get('/conteudos')
        ->assertOk()
        ->assertSee('.lp-header-burger {', false)
        ->assertSee('display: none', false)
        ->assertSee('@media (max-width: 1023px)', false)
        ->assertSee('.lp-header-burger { display: inline-flex; }', false)
        ->assertSee('position: fixed', false)
        ->assertSee('lp-header-mobile-spacer', false)
        ->assertSee('aria-controls="mobile-menu"', false)
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('max-height: calc(100dvh', false);
});

it('anima o card editorial do hero mobile respeitando redução de movimento', function () {
    get('/conteudos')
        ->assertOk()
        ->assertSee('@keyframes ct-cover-arrive', false)
        ->assertSee('@keyframes ct-cover-orbit', false)
        ->assertSee('@keyframes ct-cover-shine', false)
        ->assertSee('prefers-reduced-motion: reduce', false)
        ->assertSee('animation: none !important', false);
});

it('dispara a animação do card somente quando o usuário alcança sua área no mobile', function () {
    get('/conteudos')
        ->assertOk()
        ->assertSee('ct-cover--in-view', false)
        ->assertSee('content-motion-ready', false)
        ->assertSee('data-mobile-activation-ratio="0.45"', false)
        ->assertSee('data-mobile-activation-line="0.72"', false)
        ->assertSee('js/conteudos.js', false);
});
