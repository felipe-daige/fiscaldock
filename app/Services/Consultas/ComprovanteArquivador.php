<?php

namespace App\Services\Consultas;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComprovanteArquivador
{
    private const TAMANHO_MAXIMO_BYTES = 10 * 1024 * 1024;

    /** Rótulos amigáveis por chave de fonte (mesmas chaves de resultado_dados). */
    private const ROTULOS_FONTE = [
        'cadastro' => 'Cadastro CNPJ',
        'cnd_federal' => 'CND Federal',
        'cnd_estadual' => 'CND Estadual',
        'cnd_municipal' => 'CND Municipal',
        'crf_fgts' => 'CRF FGTS',
        'sintegra' => 'SINTEGRA',
    ];

    /** Sufixo por tipo de arquivo do snapshot clearance (html/xml/site_receipt). */
    private const ROTULOS_TIPO_ARQUIVO = [
        'html' => 'espelho',
        'xml' => 'XML',
        'site_receipt' => 'recibo',
    ];

    /** Rótulo canônico de comprovante de fonte de consulta CNPJ (ex.: "CND Federal 04252011000110"). */
    public static function rotuloFonte(string $chave, ?string $documento = null): string
    {
        $rotulo = self::ROTULOS_FONTE[$chave]
            ?? Str::of($chave)->replace('_', ' ')->title()->toString();
        $digitos = \App\Support\Cnpj::digitos((string) $documento);

        return trim($rotulo.($digitos !== '' ? " {$digitos}" : ''));
    }

    /** Rótulo canônico de comprovante de documento fiscal (ex.: "NF-e {chave} - espelho"). */
    public static function rotuloDocumento(string $tipoDocumento, string $chaveAcesso, string $tipoArquivo): string
    {
        $doc = strtoupper($tipoDocumento) === 'CTE' ? 'CT-e' : 'NF-e';
        $sufixo = self::ROTULOS_TIPO_ARQUIVO[$tipoArquivo] ?? $tipoArquivo;

        return trim("{$doc} {$chaveAcesso} - {$sufixo}");
    }

    /** Extrai o rótulo do nome do arquivo ("{rotulo}__{ulid}.{ext}"); null em path legado só-ULID. */
    public static function rotuloDePath(string $path): ?string
    {
        $filename = (string) pathinfo($path, PATHINFO_FILENAME);
        if (! str_contains($filename, '__')) {
            return null;
        }

        $rotulo = trim(Str::beforeLast($filename, '__'));

        return $rotulo !== '' ? $rotulo : null;
    }

    /**
     * Renomeia um arquivo legado (só-ULID) para o formato rotulado, preservando o ULID.
     * Retorna o novo path, ou null quando não há o que fazer (já rotulado, rótulo vazio,
     * path fora de comprovantes/ ou arquivo inexistente).
     */
    public function renomearComRotulo(string $path, string $rotulo): ?string
    {
        if (! str_starts_with($path, 'comprovantes/') || str_contains($path, '..')) {
            return null;
        }
        if (self::rotuloDePath($path) !== null) {
            return null;
        }

        $base = $this->sanitizarRotulo($rotulo);
        if ($base === '') {
            return null;
        }

        $disk = Storage::disk('local');
        if (! $disk->exists($path)) {
            return null;
        }

        $novoPath = dirname($path).'/'.$base.'__'.basename($path);
        if ($disk->exists($novoPath) || ! $disk->move($path, $novoPath)) {
            return null;
        }

        return $novoPath;
    }

    /**
     * @return array{path: string, arquivado_em: string}|null
     */
    public function arquivar(?string $url, int $userId, ?string $rotulo = null): ?array
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        if (! config('consultas.comprovantes.arquivar', true)) {
            return null;
        }

        try {
            $response = Http::timeout(20)->get($url);

            if (! $response->successful()) {
                $this->avisar('resposta HTTP sem sucesso', $userId, [
                    'status' => $response->status(),
                ]);

                return null;
            }

            $contentLength = (int) ($response->header('Content-Length') ?: 0);
            if ($contentLength > self::TAMANHO_MAXIMO_BYTES) {
                $this->avisar('arquivo excede o limite de 10 MB', $userId, [
                    'tamanho_bytes' => $contentLength,
                ]);

                return null;
            }

            $conteudo = $response->body();
            $tamanho = strlen($conteudo);
            if ($tamanho === 0) {
                $this->avisar('arquivo vazio', $userId);

                return null;
            }
            if ($tamanho > self::TAMANHO_MAXIMO_BYTES) {
                $this->avisar('arquivo excede o limite de 10 MB', $userId, [
                    'tamanho_bytes' => $tamanho,
                ]);

                return null;
            }

            $agora = now();
            $extensao = $this->extensao(
                (string) $response->header('Content-Type'),
                $url,
            );
            $base = $this->sanitizarRotulo($rotulo);
            $path = sprintf(
                'comprovantes/%d/%s/%s/%s%s.%s',
                $userId,
                $agora->format('Y'),
                $agora->format('m'),
                $base !== '' ? $base.'__' : '',
                Str::ulid(),
                $extensao,
            );

            if (! Storage::disk('local')->put($path, $conteudo)) {
                $this->avisar('storage recusou a gravação', $userId);

                return null;
            }

            return [
                'path' => $path,
                'arquivado_em' => $agora->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            $this->avisar('falha inesperada no download', $userId, [
                'erro' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function expiraEm(string $url): ?int
    {
        if (! preg_match('#/infosimples-storage/[^/]+/(\d{10})/#', $url, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Base segura pro nome do arquivo: só letras/números/espaço/ponto/parênteses/hífen.
     * Underscore é removido de propósito — "__" fica reservado como separador do ULID.
     */
    private function sanitizarRotulo(?string $rotulo): string
    {
        $rotulo = trim((string) $rotulo);
        if ($rotulo === '') {
            return '';
        }

        $rotulo = preg_replace('/[^\pL\pN .()-]+/u', ' ', $rotulo) ?: '';
        $rotulo = trim(preg_replace('/\s+/u', ' ', $rotulo) ?: '', '. ');
        // ".." é bloqueado pelos guards de path (pathPermitido) — colapsa qualquer run de pontos.
        $rotulo = preg_replace('/\.{2,}/', '.', $rotulo) ?: '';

        return Str::limit($rotulo, 120, '');
    }

    private function extensao(string $contentType, string $url): string
    {
        $tipo = strtolower(trim(explode(';', $contentType)[0] ?? ''));
        $porTipo = match ($tipo) {
            'application/pdf' => 'pdf',
            'text/html', 'application/xhtml+xml' => 'html',
            'application/xml', 'text/xml' => 'xml',
            default => null,
        };

        if ($porTipo !== null) {
            return $porTipo;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $sufixo = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        return preg_match('/^[a-z0-9]{1,8}$/', $sufixo) ? $sufixo : 'bin';
    }

    /** @param array<string, mixed> $contexto */
    private function avisar(string $motivo, int $userId, array $contexto = []): void
    {
        Log::warning('Não foi possível arquivar comprovante de consulta.', array_merge([
            'motivo' => $motivo,
            'user_id' => $userId,
        ], $contexto));
    }
}
