<?php

namespace App\Services\Consultas\Providers;

use App\Services\Consultas\ClassificadorCodigo;
use App\Services\Consultas\Contracts\ConsultaProvider;
use App\Services\Consultas\Dto\RespostaProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InfoSimplesProvider implements ConsultaProvider
{
    public function __construct(private ClassificadorCodigo $classificador) {}

    public function nome(): string
    {
        return 'infosimples';
    }

    public function consultar(string $slug, array $params): RespostaProvider
    {
        // GUARD DE TESTE: bloqueia (sem chamar/cobrar) CNPJ fora da allowlist quando ela
        // está configurada. Protege o saldo durante os testes pagos.
        if (! $this->cnpjPermitido($params)) {
            return new RespostaProvider(
                'nao_aplicavel', 0, [],
                'Modo teste InfoSimples: CNPJ fora da allowlist — não consultado (sem cobrança).'
            );
        }

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

        // Log de gasto: billable/price por chamada (acompanhar consumo real do saldo).
        Log::info('InfoSimples consulta', [
            'slug' => $slug,
            'cnpj' => preg_replace('/[^0-9]/', '', (string) ($params['cnpj'] ?? '')),
            'code' => $code,
            'status' => $status,
            'billable' => $body['header']['billable'] ?? null,
            'price' => $body['header']['price'] ?? null,
        ]);

        $mensagem = $body['code_message'] ?? null;
        if (! empty($body['errors']) && is_array($body['errors'])) {
            $mensagem = trim(($mensagem ? $mensagem.' ' : '').implode('; ', $body['errors']));
        }

        return new RespostaProvider($status, $code, is_array($body) ? $body : [], $mensagem ?: null);
    }

    /** Allowlist de teste: vazia = todos liberados; só governa consultas com `cnpj` nos params. */
    private function cnpjPermitido(array $params): bool
    {
        $allowlist = (array) config('consultas.infosimples_teste_cnpjs', []);
        if (empty($allowlist)) {
            return true;
        }

        // Consultas sem `cnpj` (ex: clearance por chave_acesso) não são governadas pela allowlist.
        if (! array_key_exists('cnpj', $params)) {
            return true;
        }

        $cnpj = preg_replace('/[^0-9]/', '', (string) ($params['cnpj'] ?? ''));

        return in_array($cnpj, $allowlist, true);
    }
}
