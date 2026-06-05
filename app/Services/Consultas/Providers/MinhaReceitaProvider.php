<?php

namespace App\Services\Consultas\Providers;

use App\Services\Consultas\Contracts\ConsultaProvider;
use App\Services\Consultas\Dto\RespostaProvider;
use Illuminate\Support\Facades\Http;

class MinhaReceitaProvider implements ConsultaProvider
{
    public function nome(): string
    {
        return 'minhareceita';
    }

    public function consultar(string $slug, array $params): RespostaProvider
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) ($params['cnpj'] ?? ''));
        $base = rtrim((string) config('consultas.providers.minhareceita.base_url'), '/');
        $timeout = (int) config('consultas.providers.minhareceita.timeout', 20);

        $resp = Http::timeout($timeout)->acceptJson()->get("{$base}/{$cnpj}");
        $code = $resp->status();
        $raw = $resp->json() ?? [];

        $status = match (true) {
            $code === 200 || $code === 201 => 'sucesso',
            $code === 404 => 'nao_encontrado',
            $code >= 500 => 'retry',
            default => 'erro_participante',
        };

        return new RespostaProvider(
            $status,
            $code,
            is_array($raw) ? $raw : [],
            is_array($raw) ? ($raw['message'] ?? null) : null,
        );
    }
}
