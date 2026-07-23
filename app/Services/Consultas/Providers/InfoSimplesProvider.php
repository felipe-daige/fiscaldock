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
        // GUARD DE TESTE: bloqueia (sem chamar/cobrar) CPF/CNPJ fora da allowlist do respectivo
        // tipo quando ela está configurada. Protege o saldo durante os testes pagos.
        if (! $this->documentoPermitido($params)) {
            return new RespostaProvider(
                'nao_aplicavel', 0, [],
                'Modo teste InfoSimples: documento fora da allowlist — não consultado (sem cobrança).'
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
        $cpf = preg_replace('/[^0-9]/', '', (string) ($params['cpf'] ?? ''));
        Log::info('InfoSimples consulta', [
            'slug' => $slug,
            'cnpj' => preg_replace('/[^0-9]/', '', (string) ($params['cnpj'] ?? '')),
            // CPF é dado pessoal: o log operacional guarda só o final, suficiente para correlação.
            'cpf_final' => $cpf !== '' ? substr($cpf, -4) : null,
            'code' => $code,
            'status' => $status,
            'billable' => $body['header']['billable'] ?? null,
            'price' => $body['header']['price'] ?? null,
            // Em falha, errors[] nomeia a causa exata (ex.: qual parâmetro falta no 606) —
            // sem isso o diagnóstico de fonte nova exige re-consulta paga só pra ler o erro.
            'errors' => $code !== 200 ? ($body['errors'] ?? null) : null,
            'code_message' => $code !== 200 ? ($body['code_message'] ?? null) : null,
        ]);

        $mensagem = $body['code_message'] ?? null;
        if (! empty($body['errors']) && is_array($body['errors'])) {
            $mensagem = trim(($mensagem ? $mensagem.' ' : '').implode('; ', $body['errors']));
        }

        return new RespostaProvider($status, $code, is_array($body) ? $body : [], $mensagem ?: null);
    }

    /** Allowlists de teste: vazias liberam o respectivo tipo de documento. */
    private function documentoPermitido(array $params): bool
    {
        if (array_key_exists('cpf', $params)) {
            $allowlistCpf = (array) config('consultas.infosimples_teste_cpfs', []);
            if ($allowlistCpf === []) {
                return true;
            }

            $cpf = preg_replace('/[^0-9]/', '', (string) $params['cpf']);

            return in_array($cpf, $allowlistCpf, true);
        }

        if (! array_key_exists('cnpj', $params)) {
            return true;
        }

        $allowlist = (array) config('consultas.infosimples_teste_cnpjs', []);
        if ($allowlist === []) {
            return true;
        }

        $cnpj = preg_replace('/[^0-9]/', '', (string) ($params['cnpj'] ?? ''));

        return in_array($cnpj, $allowlist, true);
    }
}
