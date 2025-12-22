<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Confiar nos headers X-Forwarded-* do Traefik (TLS termina no proxy).
        // Isso faz o Laravel reconhecer HTTPS corretamente e gerar asset()/@vite com https://.
        $middleware->append(\App\Http\Middleware\TrustProxies::class);
        // Logar todas as requisições HTTP recebidas
        $middleware->append(\App\Http\Middleware\LogHttpRequests::class);
        
        // Excluir rota de API do CSRF para permitir chamadas externas (n8n)
        $middleware->validateCsrfTokens(except: [
            '/app/solucoes/raf/confirmar',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
