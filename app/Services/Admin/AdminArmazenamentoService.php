<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Services\Arquivos\ArquivoUsuarioService;
use App\Services\Entitlements\EntitlementService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Métricas read-only de armazenamento para o console administrativo.
 *
 * O disco da VPS é uma medida física do volume local. O uso das contas é uma
 * medida lógica de produto, idêntica à quota de /app/arquivos: uploads,
 * comprovantes e tamanho original das importações EFD/XML.
 */
class AdminArmazenamentoService
{
    private const DISK = 'local';

    public function __construct(
        private ArquivoUsuarioService $arquivos,
        private EntitlementService $entitlements,
    ) {}

    /**
     * @param  array{q?:string,ordenar?:string}  $filtros
     * @return array{disco:array<string,mixed>,resumo:array<string,mixed>,contas:Collection<int,array<string,mixed>>}
     */
    public function painel(array $filtros = []): array
    {
        $usuarios = User::query()
            ->with('subscription.plan')
            ->orderBy('id')
            ->get();

        $medicao = $this->medirContas($usuarios);
        $contas = $this->filtrarEOrdenar(
            $medicao['contas'],
            (string) ($filtros['q'] ?? ''),
            (string) ($filtros['ordenar'] ?? 'uso_desc'),
        );

        return [
            'disco' => $this->medirDisco(),
            'resumo' => $medicao['resumo'],
            'contas' => $contas,
        ];
    }

    /**
     * A raiz opcional existe para validar a degradação segura em testes. Na
     * aplicação, a fonte é sempre a raiz real do disco privado `local`.
     *
     * @return array<string, int|float|string|bool|null>
     */
    public function medirDisco(?string $raiz = null): array
    {
        try {
            $raiz ??= Storage::disk(self::DISK)->path('');
            $total = @disk_total_space($raiz);
            $livre = @disk_free_space($raiz);
        } catch (\Throwable) {
            $total = false;
            $livre = false;
        }

        if ($total === false || $livre === false || $total <= 0) {
            return [
                'disponivel' => false,
                'total_bytes' => null,
                'total_formatado' => 'Indisponível',
                'usado_bytes' => null,
                'usado_formatado' => 'Indisponível',
                'livre_bytes' => null,
                'livre_formatado' => 'Indisponível',
                'percentual' => null,
                'status' => 'indisponivel',
                'status_label' => 'Medição indisponível',
                'status_cor' => '#64748b',
            ];
        }

        $totalBytes = max(0, (int) $total);
        $livreBytes = min($totalBytes, max(0, (int) $livre));
        $usadoBytes = max(0, $totalBytes - $livreBytes);
        $percentual = round(($usadoBytes / $totalBytes) * 100, 1);
        $status = $this->classificarPercentual($percentual);

        return [
            'disponivel' => true,
            'total_bytes' => $totalBytes,
            'total_formatado' => $this->arquivos->formatarBytes($totalBytes),
            'usado_bytes' => $usadoBytes,
            'usado_formatado' => $this->arquivos->formatarBytes($usadoBytes),
            'livre_bytes' => $livreBytes,
            'livre_formatado' => $this->arquivos->formatarBytes($livreBytes),
            'percentual' => $percentual,
            'status' => $status['status'],
            'status_label' => $status['label'],
            'status_cor' => $status['cor'],
        ];
    }

    /** @return array{status:string,label:string,cor:string} */
    public function classificarPercentual(float $percentual): array
    {
        $atencao = max(0, (float) config('arquivos.disco.atencao_percentual', 70));
        $critico = max($atencao, (float) config('arquivos.disco.critico_percentual', 85));

        if ($percentual >= $critico) {
            return ['status' => 'critico', 'label' => 'Crítico', 'cor' => '#b91c1c'];
        }

        if ($percentual >= $atencao) {
            return ['status' => 'atencao', 'label' => 'Atenção', 'cor' => '#b45309'];
        }

        return ['status' => 'saudavel', 'label' => 'Saudável', 'cor' => '#047857'];
    }

    /**
     * @param  Collection<int, User>  $usuarios
     * @return array{contas:Collection<int,array<string,mixed>>,resumo:array<string,mixed>}
     */
    private function medirContas(Collection $usuarios): array
    {
        $ids = $usuarios->pluck('id')->map(fn ($id) => (int) $id)->all();
        $uso = [];

        foreach ($ids as $id) {
            $uso[$id] = [
                'uploads_bytes' => 0,
                'uploads_total' => 0,
                'comprovantes_bytes' => 0,
                'comprovantes_total' => 0,
                'importacoes_bytes' => 0,
                'importacoes_total' => 0,
            ];
        }

        $errosLeitura = 0;
        $naoAtribuidoBytes = 0;

        foreach (['arquivos' => 'uploads', 'comprovantes' => 'comprovantes'] as $raiz => $chave) {
            try {
                $paths = Storage::disk(self::DISK)->allFiles($raiz);
            } catch (\Throwable) {
                $errosLeitura++;

                continue;
            }

            foreach ($paths as $path) {
                if (! preg_match('#^'.preg_quote($raiz, '#').'/([0-9]+)(?:/|$)#', $path, $matches)) {
                    continue;
                }

                try {
                    $bytes = max(0, (int) Storage::disk(self::DISK)->size($path));
                } catch (\Throwable) {
                    $errosLeitura++;

                    continue;
                }

                $userId = (int) $matches[1];
                if (! isset($uso[$userId])) {
                    $naoAtribuidoBytes += $bytes;

                    continue;
                }

                $uso[$userId]["{$chave}_bytes"] += $bytes;
                $uso[$userId]["{$chave}_total"]++;
            }
        }

        if ($ids !== []) {
            $efds = DB::table('efd_importacoes')
                ->whereIn('user_id', $ids)
                ->select('user_id')
                ->selectRaw('COUNT(*) AS total_arquivos')
                ->selectRaw("COALESCE(SUM(FLOOR(LENGTH(COALESCE(arquivo_base64, '')) * 3 / 4)), 0) AS total_bytes")
                ->groupBy('user_id')
                ->get();

            foreach ($efds as $efd) {
                $userId = (int) $efd->user_id;
                $uso[$userId]['importacoes_bytes'] += (int) $efd->total_bytes;
                $uso[$userId]['importacoes_total'] += (int) $efd->total_arquivos;
            }

            $xmls = DB::table('xml_importacoes')
                ->whereIn('user_id', $ids)
                ->select('user_id')
                ->selectRaw('COUNT(*) AS total_arquivos')
                ->selectRaw('COALESCE(SUM(tamanho_total_bytes), 0) AS total_bytes')
                ->groupBy('user_id')
                ->get();

            foreach ($xmls as $xml) {
                $userId = (int) $xml->user_id;
                $uso[$userId]['importacoes_bytes'] += (int) $xml->total_bytes;
                $uso[$userId]['importacoes_total'] += (int) $xml->total_arquivos;
            }
        }

        $contas = $usuarios->map(function (User $usuario) use ($uso) {
            $metricas = $uso[(int) $usuario->id];
            $usado = (int) $metricas['uploads_bytes']
                + (int) $metricas['comprovantes_bytes']
                + (int) $metricas['importacoes_bytes'];
            $quota = $this->arquivos->quotaBytes($usuario);
            $percentual = $this->percentualDaQuota($usado, $quota);
            $status = $this->classificarConta($usado, $quota, $percentual);
            $plano = $this->entitlements->planFor($usuario);

            return array_merge($metricas, [
                'usuario' => $usuario,
                'plano_nome' => $plano->nome,
                'plano_codigo' => $plano->codigo,
                'usado_bytes' => $usado,
                'usado_formatado' => $this->arquivos->formatarBytes($usado),
                'quota_bytes' => $quota,
                'quota_formatada' => $quota === null ? 'Ilimitado' : $this->arquivos->formatarBytes($quota),
                'percentual' => $percentual,
                'status' => $status['status'],
                'status_label' => $status['label'],
                'status_cor' => $status['cor'],
                'uploads_formatado' => $this->arquivos->formatarBytes((int) $metricas['uploads_bytes']),
                'comprovantes_formatado' => $this->arquivos->formatarBytes((int) $metricas['comprovantes_bytes']),
                'importacoes_formatado' => $this->arquivos->formatarBytes((int) $metricas['importacoes_bytes']),
            ]);
        })->values();

        $usoLogico = (int) $contas->sum('usado_bytes');
        $usoFilesystem = (int) $contas->sum('uploads_bytes') + (int) $contas->sum('comprovantes_bytes');
        $usoImportacoes = (int) $contas->sum('importacoes_bytes');

        return [
            'contas' => $contas,
            'resumo' => [
                'contas_total' => $contas->count(),
                'uso_logico_bytes' => $usoLogico,
                'uso_logico_formatado' => $this->arquivos->formatarBytes($usoLogico),
                'uso_filesystem_bytes' => $usoFilesystem,
                'uso_filesystem_formatado' => $this->arquivos->formatarBytes($usoFilesystem),
                'uso_importacoes_bytes' => $usoImportacoes,
                'uso_importacoes_formatado' => $this->arquivos->formatarBytes($usoImportacoes),
                'contas_atencao' => $contas->whereIn('status', ['atencao', 'critico', 'limite'])->count(),
                'contas_criticas' => $contas->whereIn('status', ['critico', 'limite'])->count(),
                'contas_acima_quota' => $contas->filter(fn (array $conta) => $conta['quota_bytes'] !== null
                    && $conta['usado_bytes'] > $conta['quota_bytes'])->count(),
                'erros_leitura' => $errosLeitura,
                'nao_atribuido_bytes' => $naoAtribuidoBytes,
                'nao_atribuido_formatado' => $this->arquivos->formatarBytes($naoAtribuidoBytes),
            ],
        ];
    }

    private function percentualDaQuota(int $usado, ?int $quota): ?float
    {
        if ($quota === null) {
            return null;
        }

        if ($quota <= 0) {
            return $usado > 0 ? 100.0 : 0.0;
        }

        return round(($usado / $quota) * 100, 1);
    }

    /** @return array{status:string,label:string,cor:string} */
    private function classificarConta(int $usado, ?int $quota, ?float $percentual): array
    {
        if ($quota === null) {
            return ['status' => 'ilimitado', 'label' => 'Ilimitado', 'cor' => '#334155'];
        }

        if ($usado >= $quota && ($usado > 0 || $quota === 0)) {
            return [
                'status' => 'limite',
                'label' => $usado > $quota ? 'Acima da quota' : 'Limite atingido',
                'cor' => '#b91c1c',
            ];
        }

        $faixa = $this->classificarPercentual($percentual ?? 0);

        if ($faixa['status'] === 'critico') {
            return ['status' => 'critico', 'label' => 'Próximo do limite', 'cor' => '#dc2626'];
        }

        if ($faixa['status'] === 'atencao') {
            return ['status' => 'atencao', 'label' => 'Atenção', 'cor' => '#b45309'];
        }

        return ['status' => 'saudavel', 'label' => 'Dentro da quota', 'cor' => '#047857'];
    }

    /**
     * @param  Collection<int,array<string,mixed>>  $contas
     * @return Collection<int,array<string,mixed>>
     */
    private function filtrarEOrdenar(Collection $contas, string $busca, string $ordenar): Collection
    {
        $busca = trim($busca);
        if ($busca !== '') {
            $termo = Str::lower($busca);
            $digitos = preg_replace('/\D/', '', $busca) ?: '';

            $contas = $contas->filter(function (array $conta) use ($termo, $digitos) {
                /** @var User $usuario */
                $usuario = $conta['usuario'];
                $texto = Str::lower(implode(' ', array_filter([
                    $usuario->name,
                    $usuario->sobrenome,
                    $usuario->email,
                    $usuario->empresa,
                    $usuario->cnpj,
                    $conta['plano_nome'],
                ])));

                return Str::contains($texto, $termo)
                    || ($digitos !== '' && str_contains((string) preg_replace('/\D/', '', (string) $usuario->cnpj), $digitos));
            });
        }

        return match ($ordenar) {
            'percentual_desc' => $contas->sortByDesc(fn (array $conta) => $conta['percentual'] ?? -1)->values(),
            'nome_asc' => $contas->sortBy(fn (array $conta) => Str::lower(trim(
                $conta['usuario']->name.' '.$conta['usuario']->sobrenome,
            )))->values(),
            default => $contas->sortByDesc('usado_bytes')->values(),
        };
    }
}
