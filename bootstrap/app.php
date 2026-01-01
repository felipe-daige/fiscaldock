<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

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
        
        // Excluir rotas de API do CSRF para permitir chamadas externas (n8n)
        // No Laravel 12, rotas em api.php não têm CSRF por padrão, mas garantimos aqui
        // Usando array com padrões para garantir que todas as rotas /api/* sejam excluídas
        $middleware->validateCsrfTokens(except: [
            'api/*',
            '/api/*',
            'api/data/receive',
            '/api/data/receive',
            '/app/raf/confirmar',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tratar exceções de autenticação para rotas API
        // Garantir que sempre retornem JSON 401 em vez de redirecionar
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            // Verificar se é rota API ou requisição que aceita JSON
            $isApiRoute = $request->is('api/*') 
                || str_starts_with($request->path(), 'api/')
                || str_starts_with($request->url(), $request->getSchemeAndHttpHost() . '/api/');
            
            $acceptsJson = $request->header('Accept') && str_contains($request->header('Accept'), 'application/json');
            $isAjax = $request->header('X-Requested-With') === 'XMLHttpRequest';
            
            // Se for rota API ou requisição que aceita JSON, retornar JSON 401
            if ($isApiRoute || $acceptsJson || $isAjax || $request->expectsJson()) {
                \Illuminate\Support\Facades\Log::info('[DEBUG] AuthenticationException tratada - retornando JSON 401', [
                    'path' => $request->path(),
                    'isApiRoute' => $isApiRoute,
                    'acceptsJson' => $acceptsJson,
                    'isAjax' => $isAjax,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.',
                ], 401);
            }
            
            // Para requisições web normais, deixar o Laravel tratar (redirecionar)
            return null;
        });
    })->create();
