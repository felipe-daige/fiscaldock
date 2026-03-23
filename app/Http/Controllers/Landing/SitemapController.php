<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $urls = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => '/solucoes', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => '/precos', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => '/faq', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/blog', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/blog/5-riscos-fiscais-que-todo-contador-deveria-monitorar', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/blog/importacao-sped-automatizada-economiza-horas', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/blog/fornecedor-irregular-como-identificar-antes-da-auditoria', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/login', 'priority' => '0.5', 'changefreq' => 'yearly'],
            ['loc' => '/agendar', 'priority' => '0.6', 'changefreq' => 'yearly'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>https://fiscaldock.com' . $url['loc'] . '</loc>' . "\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
