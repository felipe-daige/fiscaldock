<?php

namespace App\Services\Arquivos;

use App\Models\User;
use App\Services\Consultas\ComprovanteArquivador;
use App\Services\Entitlements\EntitlementService;
use App\Support\Cnpj;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ArquivoUsuarioService
{
    private const DISK = 'local';

    public function __construct(private EntitlementService $entitlements) {}

    /**
     * @return Collection<int, array{
     *   id:string,path:string,nome:string,nome_download:string,previewavel:bool,historico_url:?string,dono_documento:?string,dono_nome:?string,origem:string,origem_label:string,
     *   extensao:string,mime_type:string,tamanho_bytes:int,tamanho_formatado:string,
     *   modificado_em:Carbon,pode_excluir:bool
     * }>
     */
    public function listar(User $user): Collection
    {
        $disk = Storage::disk(self::DISK);
        $arquivos = collect();
        $origens = $this->origensPorPath($user);

        foreach ($this->raizesDoUsuario($user) as $origem => $raiz) {
            foreach ($disk->allFiles($raiz) as $path) {
                if (! $this->pathPermitido($path, $user) || ! $disk->exists($path)) {
                    continue;
                }

                try {
                    $tamanho = max(0, (int) $disk->size($path));
                    $timestamp = (int) $disk->lastModified($path);
                    $mime = (string) ($disk->mimeType($path) ?: 'application/octet-stream');
                } catch (\Throwable) {
                    continue;
                }

                $extensao = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
                $modificadoEm = Carbon::createFromTimestamp($timestamp);

                $nome = $origem === 'upload'
                    ? basename($path)
                    : $this->nomeComprovante($path, $modificadoEm, $extensao);

                $arquivos->push([
                    'id' => $this->identificador($path),
                    'path' => $path,
                    'nome' => $nome,
                    'nome_download' => $this->nomeDownload($nome, $extensao, $origem),
                    'previewavel' => $this->previewContentType($extensao) !== null,
                    'baixavel' => true,
                    'historico_url' => $origens[$path]['url'] ?? null,
                    'dono_documento' => $this->donoDocumento($origens[$path] ?? null, $origem, $path),
                    'dono_nome' => $origens[$path]['nome'] ?? null,
                    'origem' => $origem,
                    'origem_label' => $origem === 'upload' ? 'Enviado por você' : 'Gerado pelo sistema',
                    'extensao' => $extensao !== '' ? strtoupper($extensao) : 'ARQUIVO',
                    'mime_type' => $mime,
                    'tamanho_bytes' => $tamanho,
                    'tamanho_formatado' => $this->formatarBytes($tamanho),
                    'modificado_em' => $modificadoEm,
                    'pode_excluir' => $origem === 'upload',
                ]);
            }
        }

        return $arquivos
            ->merge($this->itensImportacao($user))
            ->sortByDesc(fn (array $arquivo) => $arquivo['modificado_em']->timestamp)
            ->values();
    }

    /**
     * Itens virtuais das importações EFD/XML (vivem no banco, não no disco).
     * EFD guarda o SPED bruto em `arquivo_base64` e é baixável; do XML só restam
     * os metadados (o job apaga os originais após processar) — lista sem download.
     * O peso de ambos conta na quota do plano.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function itensImportacao(User $user): Collection
    {
        $efds = \App\Models\EfdImportacao::query()
            ->where('user_id', $user->id)
            ->select('id', 'filename', 'tipo_efd', 'cnpj', 'cliente_id', 'created_at')
            // Peso real do arquivo (base64 infla 33%); nunca carregar o blob aqui.
            ->selectRaw('COALESCE(FLOOR(LENGTH(arquivo_base64) * 3 / 4), 0)::bigint AS tamanho_estimado')
            ->get();

        $xmls = \App\Models\XmlImportacao::query()
            ->where('user_id', $user->id)
            ->get(['id', 'filename', 'tipo_documento', 'tamanho_total_bytes', 'total_xmls', 'cliente_id', 'created_at']);

        $clientes = \App\Models\Cliente::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $efds->pluck('cliente_id')->merge($xmls->pluck('cliente_id'))->filter()->unique()->all())
            ->get(['id', 'documento', 'nome', 'razao_social'])
            ->keyBy('id');

        $itens = collect();

        foreach ($efds as $efd) {
            $cliente = $clientes[$efd->cliente_id] ?? null;
            $itens->push($this->itemImportacao(
                pseudoPath: "importacao/efd/{$efd->id}",
                nome: (string) ($efd->filename ?: "Importação EFD #{$efd->id}"),
                tipoLabel: (string) ($efd->tipo_efd ?: 'EFD'),
                tamanho: (int) $efd->tamanho_estimado,
                criadoEm: $efd->created_at ?? now(),
                historicoUrl: route('app.importacao.efd.detalhes', $efd->id),
                documento: $cliente?->documento ?? ($efd->cnpj ?: null),
                donoNome: $cliente?->razao_social ?? $cliente?->nome,
                baixavel: $efd->tamanho_estimado > 0,
                extensaoPadrao: 'TXT',
            ));
        }

        foreach ($xmls as $xml) {
            $cliente = $clientes[$xml->cliente_id] ?? null;
            $notas = (int) $xml->total_xmls;
            $itens->push($this->itemImportacao(
                pseudoPath: "importacao/xml/{$xml->id}",
                nome: (string) ($xml->filename ?: "Importação XML #{$xml->id}"),
                tipoLabel: 'Lote XML '.($xml->tipo_documento ?: 'NFE').($notas > 0 ? " · {$notas} nota(s)" : ''),
                tamanho: max(0, (int) $xml->tamanho_total_bytes),
                criadoEm: $xml->created_at ?? now(),
                historicoUrl: route('app.importacao.xml.detalhes', $xml->id),
                documento: $cliente?->documento,
                donoNome: $cliente?->razao_social ?? $cliente?->nome,
                baixavel: false,
                extensaoPadrao: 'XML',
            ));
        }

        return $itens;
    }

    /** @return array<string, mixed> */
    private function itemImportacao(
        string $pseudoPath,
        string $nome,
        string $tipoLabel,
        int $tamanho,
        Carbon $criadoEm,
        string $historicoUrl,
        ?string $documento,
        ?string $donoNome,
        bool $baixavel,
        string $extensaoPadrao,
    ): array {
        $extensao = strtoupper((string) pathinfo($nome, PATHINFO_EXTENSION)) ?: $extensaoPadrao;
        $documento = $documento !== null && $documento !== '' ? Cnpj::formatar($documento) : null;

        return [
            'id' => $this->identificador($pseudoPath),
            'path' => $pseudoPath,
            'nome' => $nome,
            'nome_download' => $this->nomeDownload($nome, '', 'importacao'),
            'previewavel' => false,
            'baixavel' => $baixavel,
            'historico_url' => $historicoUrl,
            'dono_documento' => $documento,
            'dono_nome' => $donoNome,
            'origem' => 'importacao',
            'origem_label' => 'Importado por você',
            'extensao' => $extensao,
            'mime_type' => $tipoLabel,
            'tamanho_bytes' => $tamanho,
            'tamanho_formatado' => $this->formatarBytes($tamanho),
            'modificado_em' => Carbon::instance($criadoEm),
            'pode_excluir' => false,
        ];
    }

    /** @return array<string, int|float|string|null> */
    public function resumo(User $user, ?Collection $arquivos = null): array
    {
        $arquivos ??= $this->listar($user);
        $usado = (int) $arquivos->sum('tamanho_bytes');
        $quota = $this->quotaBytes($user);
        $percentual = $quota === null || $quota <= 0
            ? 0.0
            : min(100, round(($usado / $quota) * 100, 1));

        return [
            'usado_bytes' => $usado,
            'usado_formatado' => $this->formatarBytes($usado),
            'quota_bytes' => $quota,
            'quota_formatada' => $quota === null ? 'Ilimitado' : $this->formatarBytes($quota),
            'disponivel_bytes' => $quota === null ? null : max(0, $quota - $usado),
            'disponivel_formatado' => $quota === null ? 'Ilimitado' : $this->formatarBytes(max(0, $quota - $usado)),
            'percentual' => $percentual,
            'total_arquivos' => $arquivos->count(),
            'total_uploads' => $arquivos->where('origem', 'upload')->count(),
            'total_comprovantes' => $arquivos->where('origem', 'comprovante')->count(),
            'total_importados' => $arquivos->where('origem', 'importacao')->count(),
        ];
    }

    /**
     * Bloqueia gravação/importação que estoure a quota do plano. Compartilhado
     * entre upload manual, importação EFD e importação XML — quota cheia trava
     * os três até o usuário liberar espaço ou subir de plano.
     *
     * @throws ValidationException
     */
    public function garantirEspaco(User $user, int $bytesNovos, string $campo = 'arquivos'): void
    {
        $resumo = $this->resumo($user);
        $quota = $resumo['quota_bytes'];

        if ($quota !== null && ((int) $resumo['usado_bytes'] + $bytesNovos) > $quota) {
            $faltam = ((int) $resumo['usado_bytes'] + $bytesNovos) - $quota;

            throw ValidationException::withMessages([
                $campo => 'Este envio ultrapassa o espaço do seu plano em '
                    .$this->formatarBytes($faltam).'. Remova uploads ou aumente seu armazenamento.',
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $uploads
     * @return array<int, string> paths gravados
     */
    public function armazenar(User $user, array $uploads): array
    {
        return Cache::lock("arquivos:upload:{$user->id}", 15)->block(5, function () use ($user, $uploads) {
            $bytesNovos = array_sum(array_map(
                fn (UploadedFile $arquivo) => max(0, (int) $arquivo->getSize()),
                $uploads,
            ));
            $this->garantirEspaco($user, $bytesNovos);

            $gravados = [];

            try {
                foreach ($uploads as $arquivo) {
                    $agora = now();
                    $diretorio = sprintf(
                        'arquivos/%d/%s/%s/%s',
                        $user->id,
                        $agora->format('Y'),
                        $agora->format('m'),
                        Str::ulid(),
                    );
                    $nome = $this->sanitizarNome($arquivo->getClientOriginalName());
                    $path = Storage::disk(self::DISK)->putFileAs($diretorio, $arquivo, $nome);

                    if (! is_string($path) || $path === '') {
                        throw new RuntimeException('O storage recusou a gravação do arquivo.');
                    }

                    $gravados[] = $path;
                }
            } catch (\Throwable $e) {
                foreach ($gravados as $path) {
                    Storage::disk(self::DISK)->deleteDirectory(dirname($path));
                }

                throw $e;
            }

            return $gravados;
        });
    }

    /**
     * @return array{id:string,path:string,nome:string,nome_download:string,previewavel:bool,historico_url:?string,dono_documento:?string,dono_nome:?string,origem:string,origem_label:string,
     *   extensao:string,mime_type:string,tamanho_bytes:int,tamanho_formatado:string,
     *   modificado_em:Carbon,pode_excluir:bool}|null
     */
    public function localizar(User $user, string $id): ?array
    {
        $path = $this->pathDoIdentificador($id);
        if ($path === null) {
            return null;
        }

        // Pseudo-path de importação (importacao/efd/{id} | importacao/xml/{id}):
        // o escopo por usuário já vem do listar — id de outro usuário não aparece.
        if (! preg_match('#^importacao/(efd|xml)/\d+$#', $path) && ! $this->pathPermitido($path, $user)) {
            return null;
        }

        return $this->listar($user)->firstWhere('path', $path);
    }

    /**
     * Download do SPED bruto de uma importação EFD (vive em arquivo_base64).
     * Só chega aqui item já localizado/escopado pelo listar do próprio usuário.
     *
     * @param  array<string, mixed>  $item
     */
    public function downloadImportacaoEfd(User $user, array $item): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (! preg_match('#^importacao/efd/(\d+)$#', (string) $item['path'], $m)) {
            abort(404, 'Arquivo não encontrado.');
        }

        $base64 = \App\Models\EfdImportacao::query()
            ->where('user_id', $user->id)
            ->where('id', (int) $m[1])
            ->value('arquivo_base64');
        abort_if($base64 === null || $base64 === '', 404, 'O arquivo original desta importação não está disponível.');

        return response()->streamDownload(
            function () use ($base64) {
                echo base64_decode($base64);
            },
            $item['nome_download'],
            ['Content-Type' => 'text/plain; charset=UTF-8', 'X-Content-Type-Options' => 'nosniff'],
        );
    }

    public function excluirUpload(User $user, string $id): bool
    {
        $arquivo = $this->localizar($user, $id);
        if ($arquivo === null || ! $arquivo['pode_excluir']) {
            return false;
        }

        $apagado = Storage::disk(self::DISK)->delete($arquivo['path']);
        if ($apagado) {
            Storage::disk(self::DISK)->deleteDirectory(dirname($arquivo['path']));
        }

        return $apagado;
    }

    /**
     * Mapa path do comprovante => origem: URL da tela (lote de consulta CNPJ,
     * resultado da busca avulsa ou do lote clearance) + CNPJ/nome do titular do
     * documento. Comprovante sem referência no banco (órfão/legado) fica de fora.
     *
     * @return array<string, array{url:string,documento:?string,nome:?string}>
     */
    private function origensPorPath(User $user): array
    {
        $mapa = [];

        $resultados = \App\Models\ConsultaResultado::query()
            ->with(['participante:id,documento,razao_social', 'cliente:id,documento,nome,razao_social'])
            ->whereHas('lote', fn ($q) => $q->where('user_id', $user->id))
            ->whereRaw("resultado_dados::text LIKE '%comprovante_arquivo%'")
            ->get(['id', 'consulta_lote_id', 'participante_id', 'cliente_id', 'resultado_dados']);
        foreach ($resultados as $resultado) {
            $origem = [
                'url' => route('app.consulta.lote.show', $resultado->consulta_lote_id),
                'documento' => $resultado->participante?->documento ?? $resultado->cliente?->documento,
                'nome' => $resultado->participante?->razao_social
                    ?? $resultado->cliente?->razao_social
                    ?? $resultado->cliente?->nome,
            ];
            foreach ((array) $resultado->resultado_dados as $bloco) {
                if (is_array($bloco) && ! empty($bloco['comprovante_arquivo'])) {
                    $mapa[(string) $bloco['comprovante_arquivo']] = $origem;
                }
            }
        }

        $snapshots = collect();
        foreach ([\App\Models\NfeConsulta::class, \App\Models\CteConsulta::class] as $model) {
            $snapshots = $snapshots->merge(
                $model::query()
                    ->where('user_id', $user->id)
                    ->whereNotNull('consulta_lote_id')
                    ->whereRaw("payload::text LIKE '%comprovantes_arquivos%'")
                    ->get(['id', 'chave_acesso', 'tipo_documento', 'consulta_lote_id', 'emit_cnpj', 'emit_nome', 'payload']),
            );
        }
        if ($snapshots->isEmpty()) {
            return $mapa;
        }

        // fluxo_origem do lote decide a tela: busca avulsa × clearance em lote (legado null = lote).
        $fluxos = \App\Models\ConsultaLote::query()
            ->whereIn('id', $snapshots->pluck('consulta_lote_id')->unique()->all())
            ->pluck('resultado_resumo', 'id')
            ->map(fn ($resumo) => is_array($resumo) ? ($resumo['fluxo_origem'] ?? 'lote') : 'lote');

        foreach ($snapshots as $snapshot) {
            $origem = [
                'url' => $fluxos[$snapshot->consulta_lote_id] === 'avulsa'
                    ? route('app.clearance.buscar.resultado', $snapshot->consulta_lote_id).'?'.http_build_query([
                        'chave_acesso' => $snapshot->chave_acesso,
                        'tipo_documento' => $snapshot->tipo_documento ?: 'NFE',
                    ])
                    : route('app.clearance.notas.resultado', $snapshot->consulta_lote_id),
                // Emitente do documento; fallback = CNPJ embutido na chave (pos. 7-20).
                'documento' => $snapshot->emit_cnpj ?: $this->documentoDoRotulo((string) $snapshot->chave_acesso),
                'nome' => $snapshot->emit_nome,
            ];

            foreach ((array) data_get($snapshot->payload, 'comprovantes_arquivos', []) as $path) {
                if (is_string($path) && $path !== '') {
                    $mapa[$path] = $origem;
                }
            }
        }

        return $mapa;
    }

    /**
     * CNPJ formatado do titular do documento: origem no banco vence; comprovante
     * sem registro cai no parse do rótulo do filename. Upload não tem titular.
     *
     * @param  array{url:string,documento:?string,nome:?string}|null  $origemInfo
     */
    private function donoDocumento(?array $origemInfo, string $origem, string $path): ?string
    {
        $documento = $origemInfo['documento'] ?? null;
        if ($documento === null && $origem === 'comprovante') {
            $documento = $this->documentoDoRotulo(ComprovanteArquivador::rotuloDePath($path));
        }

        return $documento !== null && $documento !== '' ? Cnpj::formatar($documento) : null;
    }

    /**
     * CNPJ derivado de um texto de rótulo/chave: run de 44 dígitos = chave de
     * acesso (CNPJ do emitente nas posições 7-20); run de 14 dígitos = o próprio.
     */
    private function documentoDoRotulo(?string $texto): ?string
    {
        if ($texto === null || $texto === '') {
            return null;
        }
        if (preg_match('/(?<!\d)(\d{44})(?!\d)/', $texto, $m)) {
            return substr($m[1], 6, 14);
        }
        if (preg_match('/(?<!\d)(\d{14})(?!\d)/', $texto, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Content-Type do preview inline por extensão; null = formato sem preview
     * (XLS/XLSX/ZIP etc. só baixam). XML/TXT/CSV saem como text/plain de
     * propósito — nunca renderizar/executar conteúdo estruturado no navegador.
     */
    public function previewContentType(string $extensao): ?string
    {
        return match (strtolower($extensao)) {
            'pdf' => 'application/pdf',
            'html' => 'text/html; charset=UTF-8',
            'xml', 'txt', 'csv' => 'text/plain; charset=UTF-8',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => null,
        };
    }

    public function quotaBytes(User $user): ?int
    {
        $plano = $this->entitlements->planFor($user);
        $capabilities = is_array($plano->capabilities) ? $plano->capabilities : [];
        $quotaMb = array_key_exists('armazenamento_mb', $capabilities)
            ? $capabilities['armazenamento_mb']
            : config(
                "arquivos.quota_por_plano_mb.{$plano->codigo}",
                config('arquivos.quota_padrao_mb', 250),
            );

        if ($quotaMb === null) {
            return null; // plano ilimitado: pacotes extras não fazem sentido
        }

        // Add-on de espaço adicional: soma os pacotes contratados (mensal via saldo).
        $owner = $user->accountOwner();
        $sub = $owner->subscription()
            ->where('status', \App\Models\AccountSubscription::STATUS_ATIVA)
            ->first();
        $pacotes = $sub ? (int) $sub->espaco_extra_pacotes : 0;
        if ($pacotes > 0) {
            $quotaMb = (int) $quotaMb + $pacotes * app(\App\Services\Subscription\AddonService::class)->pacoteEspacoMb();
        }

        return max(0, (int) $quotaMb) * 1024 * 1024;
    }

    public function identificador(string $path): string
    {
        return rtrim(strtr(base64_encode($path), '+/', '-_'), '=');
    }

    public function formatarBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $unidades = ['KB', 'MB', 'GB', 'TB'];
        $valor = $bytes / 1024;

        foreach ($unidades as $unidade) {
            if ($valor < 1024 || $unidade === 'TB') {
                $casas = $valor >= 100 ? 0 : ($valor >= 10 ? 1 : 2);

                return number_format($valor, $casas, ',', '.').' '.$unidade;
            }
            $valor /= 1024;
        }

        return number_format($valor, 2, ',', '.').' TB';
    }

    /** @return array<string, string> */
    private function raizesDoUsuario(User $user): array
    {
        return [
            'upload' => "arquivos/{$user->id}",
            'comprovante' => "comprovantes/{$user->id}",
        ];
    }

    private function pathPermitido(string $path, User $user): bool
    {
        if ($path === '' || str_contains($path, '..') || str_contains($path, '\\')) {
            return false;
        }

        foreach ($this->raizesDoUsuario($user) as $raiz) {
            if (str_starts_with($path, $raiz.'/')) {
                return true;
            }
        }

        return false;
    }

    private function pathDoIdentificador(string $id): ?string
    {
        if ($id === '' || ! preg_match('/^[A-Za-z0-9_-]+$/', $id)) {
            return null;
        }

        $base64 = strtr($id, '-_', '+/');
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);
        $path = base64_decode($base64, true);

        return is_string($path) && $path !== '' ? $path : null;
    }

    private function sanitizarNome(string $nome): string
    {
        $nome = basename(str_replace('\\', '/', trim($nome)));
        $extensao = strtolower((string) pathinfo($nome, PATHINFO_EXTENSION));
        $base = (string) pathinfo($nome, PATHINFO_FILENAME);
        $base = preg_replace('/[^\pL\pN._ -]+/u', '_', $base) ?: 'arquivo';
        $base = trim(preg_replace('/\s+/u', ' ', $base) ?: 'arquivo', '. ');
        $base = Str::limit($base !== '' ? $base : 'arquivo', 140, '');

        return $extensao !== '' ? "{$base}.{$extensao}" : $base;
    }

    private function nomeComprovante(string $path, Carbon $data, string $extensao): string
    {
        // Arquivos novos carregam o rótulo descritivo no próprio nome ("{rotulo}__{ulid}.{ext}").
        $rotulo = ComprovanteArquivador::rotuloDePath($path);
        if ($rotulo !== null) {
            return preg_replace_callback(
                '/(?<!\d)(\d{14})(?!\d)/',
                fn (array $m) => Cnpj::formatar($m[1]),
                $rotulo,
            ) ?? $rotulo;
        }

        // Legado: só-ULID no nome, sem metadado recuperável.
        $codigo = strtoupper(substr((string) pathinfo($path, PATHINFO_FILENAME), -6));
        $tipo = $extensao !== '' ? strtoupper($extensao) : 'ARQUIVO';

        return "Comprovante {$tipo} · {$data->format('d/m/Y H:i')} · {$codigo}";
    }

    /**
     * Nome usado no Content-Disposition do download. Upload mantém o nome original
     * (já tem extensão); comprovante ganha extensão e troca chars inválidos em
     * filename ("/" da máscara de CNPJ e de datas legadas quebra o Symfony).
     */
    private function nomeDownload(string $nome, string $extensao, string $origem): string
    {
        if ($origem === 'upload') {
            return $nome;
        }

        $base = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $nome);
        $extensao = strtolower($extensao);

        return $extensao !== '' ? "{$base}.{$extensao}" : $base;
    }
}
