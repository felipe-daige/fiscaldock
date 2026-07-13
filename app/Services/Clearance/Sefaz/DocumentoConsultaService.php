<?php

namespace App\Services\Clearance\Sefaz;

use App\Services\Clearance\CertificadoDigitalService;
use App\Services\Consultas\Providers\InfoSimplesProvider;
use App\Services\Consultas\ThrottleProvider;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class DocumentoConsultaService
{
    public function __construct(
        private InfoSimplesProvider $provider,
        private ThrottleProvider $throttle,
        private NfeSnapshotNormalizer $nfeNormalizer,
        private CteSnapshotNormalizer $cteNormalizer,
        private CertificadoDigitalService $certificados,
    ) {}

    /**
     * Núcleo PURO: consulta a SEFAZ por chave e devolve o snapshot normalizado.
     * NÃO persiste — o caller (lote/busca-notas) decide a persistência.
     *
     * Quando o cliente tem certificado digital A1 válido cadastrado, a consulta vai
     * ASSINADA (`pkcs12_cert`/`pkcs12_pass`) e a SEFAZ devolve o documento COMPLETO
     * (tributos, itens com NCM/CFOP/CST, XML, contraparte sem máscara). Sem certificado,
     * roda a consulta pública (comportamento de sempre). O gate é o próprio dado — não há
     * flag: quem tem cert recebe completo, quem não tem recebe público.
     *
     * @param  string  $tipoDocumento  'nfe' | 'cte'
     */
    public function consultar(string $chave, string $tipoDocumento, ?int $clienteId = null): DocumentoSnapshot
    {
        $chave = preg_replace('/\D/', '', $chave);
        if (strlen($chave) !== 44) {
            throw new InvalidArgumentException('Chave de acesso deve ter 44 dígitos.');
        }

        $modelo = substr($chave, 20, 2);
        $tipo = strtolower($tipoDocumento);

        // $param = nome do argumento que o InfoSimples espera pra chave: receita-federal/nfe → 'nfe',
        // receita-federal/cte → 'cte' (confirmado pela própria API: "O parâmetro 'nfe' não pode ser vazio").
        [$slug, $param, $modelosOk, $normalizer] = match ($tipo) {
            'nfe' => ['receita-federal/nfe', 'nfe', ['55', '65'], $this->nfeNormalizer],
            'cte' => ['receita-federal/cte', 'cte', ['57'], $this->cteNormalizer],
            default => throw new InvalidArgumentException("Tipo de documento inválido: {$tipoDocumento}"),
        };

        if (! in_array($modelo, $modelosOk, true)) {
            throw new InvalidArgumentException("Modelo {$modelo} incompatível com {$tipoDocumento}.");
        }

        $cert = config('clearance.certificado.habilitado')
            ? $this->certificados->materialParaConsulta($clienteId)
            : null;

        $resp = $this->chamar($slug, $param, $chave, $cert);

        // Retry 1x em status retryável (espelha "Wait 30s + Retry" dos Code Nodes).
        $statusFinal = $resp->status;
        if ($resp->status === 'retry') {
            $resp2 = $this->chamar($slug, $param, $chave, $cert);
            $resp = $resp2;
            $statusFinal = $resp2->status === 'sucesso' ? 'sucesso' : 'retry';
        }

        // Rede de segurança do certificado: o formato exato de `pkcs12_cert` não está na doc
        // pública do InfoSimples. Se a consulta ASSINADA for recusada (erro de parâmetro
        // 608/619/620 ou fatal), reconsulta SEM o certificado — o clearance básico (pago!)
        // não pode regredir por causa do cert. Log alto: se isto disparar, o contrato do
        // parâmetro está errado e precisa de ajuste (ver docs/clearance/certificado-a1.md).
        if ($cert !== null && in_array($statusFinal, ['erro_participante', 'fatal'], true)) {
            Log::warning('Clearance: consulta com certificado recusada — refazendo sem certificado', [
                'slug' => $slug,
                'cliente_id' => $clienteId,
                'code' => $resp->httpCode,
                'status' => $statusFinal,
                'mensagem' => $resp->mensagem,
            ]);

            $resp = $this->chamar($slug, $param, $chave, null);
            $statusFinal = $resp->status;
        }

        $billable = (bool) ($resp->raw['header']['billable'] ?? false);

        return $normalizer->normalizar($resp->raw, $statusFinal, $chave, $billable);
    }

    /** @param array{pkcs12_cert: string, pkcs12_pass: string}|null $cert */
    private function chamar(string $slug, string $param, string $chave, ?array $cert = null)
    {
        $this->throttle->aguardar('infosimples');

        // O nome do argumento é o tipo do doc ('nfe'/'cte'), não 'chave'/'chave_acesso'.
        $params = [$param => $chave];

        if ($cert !== null) {
            $params += $cert; // pkcs12_cert (base64 do .pfx) + pkcs12_pass
        }

        return $this->provider->consultar($slug, $params);
    }
}
