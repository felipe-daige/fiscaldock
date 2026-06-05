<?php

namespace App\Services\Consultas\Providers;

use App\Services\Consultas\ClassificadorCodigo;
use App\Services\Consultas\Contracts\ConsultaProvider;
use App\Services\Consultas\Dto\RespostaProvider;
use Illuminate\Support\Facades\Http;

class InfoSimplesProvider implements ConsultaProvider
{
    public function __construct(private ClassificadorCodigo $classificador) {}

    public function nome(): string
    {
        return 'infosimples';
    }

    public function consultar(string $slug, array $params): RespostaProvider
    {
        $base = rtrim((string) config('consultas.providers.infosimples.base_url'), '/');
        $timeout = (int) config('consultas.providers.infosimples.timeout', 120);
        $token = (string) config('consultas.providers.infosimples.token');

        $resp = Http::timeout($timeout + 10)->asForm()->post("{$base}/{$slug}", array_merge($params, [
            'token' => $token,
            'timeout' => $timeout,
        ]));

        $body = $resp->json() ?? [];

        // InfoSimples sempre retorna HTTP 200; o resultado real vem no campo `code` do corpo.
        $code = (int) ($body['code'] ?? 0);
        $status = $this->classificador->classificar($code);

        $mensagem = $body['code_message'] ?? null;
        if (! empty($body['errors']) && is_array($body['errors'])) {
            $mensagem = trim(($mensagem ? $mensagem.' ' : '').implode('; ', $body['errors']));
        }

        return new RespostaProvider($status, $code, is_array($body) ? $body : [], $mensagem ?: null);
    }
}
