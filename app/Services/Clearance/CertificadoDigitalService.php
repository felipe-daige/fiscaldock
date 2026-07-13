<?php

namespace App\Services\Clearance;

use App\Models\CertificadoDigital;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CertificadoDigitalService
{
    private const DISK = 'local';

    public function validarEArmazenar(UploadedFile $arquivo, string $senha, Cliente $cliente): CertificadoDigital
    {
        $conteudo = file_get_contents($arquivo->getRealPath());
        $info = [];

        if (! @openssl_pkcs12_read($conteudo, $info, $senha)) {
            throw ValidationException::withMessages([
                'certificado' => 'Não foi possível abrir o certificado: senha incorreta ou arquivo inválido.',
            ]);
        }

        $parsed = openssl_x509_parse($info['cert'] ?? '');
        if (! $parsed) {
            throw ValidationException::withMessages(['certificado' => 'Certificado inválido (não foi possível ler os dados).']);
        }

        $validade = Carbon::createFromTimestamp((int) ($parsed['validTo_time_t'] ?? 0));
        if ($validade->isPast()) {
            throw ValidationException::withMessages(['certificado' => 'Certificado expirado em '.$validade->format('d/m/Y').'.']);
        }

        $cn = (string) ($parsed['subject']['CN'] ?? '');
        $cnpjCert = $this->extrairCnpj($cn);
        $docEmpresa = preg_replace('/\D/', '', (string) $cliente->documento);
        if ($cnpjCert && $docEmpresa && $cnpjCert !== $docEmpresa) {
            throw ValidationException::withMessages(['certificado' => "O certificado pertence a outro CNPJ ({$cnpjCert})."]);
        }

        // Substituição: remove o anterior (arquivo + linha) antes de gravar o novo.
        $this->remover($cliente);

        $path = "certificados/{$cliente->id}/".Str::random(40).'.pfx';
        Storage::disk(self::DISK)->put($path, $conteudo);

        return CertificadoDigital::create([
            'user_id' => $cliente->user_id,
            'cliente_id' => $cliente->id,
            'arquivo_path' => $path,
            'senha_cifrada' => Crypt::encryptString($senha),
            'cnpj' => $cnpjCert ?: null,
            'titular_nome' => $this->extrairNome($cn),
            'validade' => $validade->toDateString(),
            'status' => 'ativo',
        ]);
    }

    /**
     * Material do certificado A1 pronto pra ir na consulta InfoSimples (`pkcs12_cert` +
     * `pkcs12_pass`). Null quando o cliente não tem certificado, ele está expirado/inativo
     * ou o arquivo sumiu do storage — nesses casos a consulta roda pública (sem cert), que
     * é o comportamento de sempre.
     *
     * O .pfx é binário; a chamada do provider é form-urlencoded → vai em base64. O formato
     * exato NÃO está na doc pública do InfoSimples (só os nomes dos params) — por isso o
     * DocumentoConsultaService tem fallback pra consulta sem cert se o provedor recusar o
     * parâmetro. Validar com `clearance:smoke {chave} {tipo} --cliente={id}` antes de confiar.
     *
     * ATENÇÃO (segurança): isto envia o certificado A1 do cliente + a senha em claro ao
     * InfoSimples a cada consulta. É o contrato que o provedor impõe (não há cofre/upload
     * prévio nesta API). A senha sai do `Crypt` só aqui, no momento do envio.
     *
     * @return array{pkcs12_cert: string, pkcs12_pass: string}|null
     */
    public function materialParaConsulta(?int $clienteId): ?array
    {
        if (! $clienteId) {
            return null;
        }

        $cert = CertificadoDigital::where('cliente_id', $clienteId)
            ->where('status', 'ativo')
            ->whereDate('validade', '>=', now()->toDateString())
            ->first();

        if (! $cert || ! $cert->arquivo_path || ! Storage::disk(self::DISK)->exists($cert->arquivo_path)) {
            return null;
        }

        try {
            $conteudo = Storage::disk(self::DISK)->get($cert->arquivo_path);
            $senha = Crypt::decryptString($cert->senha_cifrada);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        if (! $conteudo) {
            return null;
        }

        return [
            'pkcs12_cert' => base64_encode($conteudo),
            'pkcs12_pass' => $senha,
        ];
    }

    public function remover(Cliente $cliente): void
    {
        $cert = CertificadoDigital::where('cliente_id', $cliente->id)->first();
        if (! $cert) {
            return;
        }
        if ($cert->arquivo_path && Storage::disk(self::DISK)->exists($cert->arquivo_path)) {
            Storage::disk(self::DISK)->delete($cert->arquivo_path);
        }
        $cert->delete();
    }

    /** @return array<string,mixed>|null */
    public function status(Cliente $cliente): ?array
    {
        $cert = CertificadoDigital::where('cliente_id', $cliente->id)->first();
        if (! $cert) {
            return null;
        }
        $dias = (int) now()->startOfDay()->diffInDays($cert->validade, false);

        return [
            'cnpj' => $cert->cnpj,
            'titular_nome' => $cert->titular_nome,
            'validade' => $cert->validade,
            'dias_para_expirar' => $dias,
            'expirado' => $dias < 0,
            'badge_hex' => $dias < 0 ? '#dc2626' : ($dias <= 30 ? '#b45309' : '#047857'),
            'atualizado_em' => $cert->updated_at,
        ];
    }

    /** e-CNPJ A1: CN = "NOME:CNPJ" → 14 dígitos do último segmento. */
    private function extrairCnpj(string $cn): ?string
    {
        $partes = explode(':', $cn);
        $cand = preg_replace('/\D/', '', (string) end($partes));

        return strlen($cand) === 14 ? $cand : null;
    }

    private function extrairNome(string $cn): ?string
    {
        $nome = trim((string) (explode(':', $cn)[0] ?? ''));

        return $nome !== '' ? $nome : null;
    }
}
