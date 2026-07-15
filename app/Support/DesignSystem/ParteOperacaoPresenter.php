<?php

namespace App\Support\DesignSystem;

use App\Models\Cliente;
use App\Models\Participante;
use Carbon\CarbonInterface;

final class ParteOperacaoPresenter
{
    public const MODO_COMPLETO = 'completo';

    public const MODO_COMPACTO = 'compacto';

    /**
     * Normaliza Cliente e Participante no mesmo contrato visual.
     *
     * @param  array<string, mixed>  $fallback  Dados identificados no documento quando não há cadastro.
     * @return array<string, mixed>
     */
    public static function card(
        Cliente|Participante|null $entidade,
        string $titulo,
        array $fallback = [],
        string $modo = self::MODO_COMPLETO,
        ?string $papel = null,
        ?string $papelHex = null,
        ?string $descricao = null,
    ): array {
        $isCliente = $entidade instanceof Cliente;
        $nome = self::primeiroValor(
            $entidade?->razao_social,
            $entidade?->nome,
            $fallback['nome'] ?? null,
        );
        $situacao = self::primeiroValor(
            $entidade?->situacao_cadastral,
            $fallback['situacao_cadastral'] ?? null,
        );

        return [
            'titulo' => $titulo,
            'nome' => $nome,
            'descricao' => $descricao ?? self::primeiroValor(
                $entidade?->nome_fantasia,
                $fallback['nome_fantasia'] ?? null,
            ),
            'href' => $entidade
                ? ($isCliente ? "/app/cliente/{$entidade->id}" : "/app/participante/{$entidade->id}")
                : null,
            'situacao' => $situacao ? mb_strtoupper($situacao) : null,
            'situacao_hex' => self::situacaoHex($situacao),
            'papel' => $papel,
            'papel_hex' => $papelHex ?? ($isCliente ? '#1d4ed8' : '#6b7280'),
            'campos' => self::campos($entidade, $fallback, $modo),
        ];
    }

    /**
     * @param  array<string, mixed>  $fallback
     * @return list<array<string, mixed>>
     */
    public static function campos(
        Cliente|Participante|null $entidade,
        array $fallback = [],
        string $modo = self::MODO_COMPLETO,
    ): array {
        $documento = self::formatarDocumento(self::primeiroValor(
            $entidade?->documento,
            $fallback['documento'] ?? null,
        ));
        $municipio = self::primeiroValor($entidade?->municipio, $fallback['municipio'] ?? null);
        $uf = self::primeiroValor($entidade?->uf, $fallback['uf'] ?? null);
        $municipioUf = collect([$municipio, $uf])->filter()->implode(' / ');
        $regime = self::primeiroValor(
            $entidade?->regime_tributario,
            $fallback['regime_tributario'] ?? null,
        );

        $camposCompactos = [
            ['label' => 'CNPJ / CPF', 'valor' => $documento, 'mono' => true],
            [
                'label' => 'Inscrição estadual',
                'valor' => self::primeiroValor(
                    $entidade?->inscricao_estadual,
                    $fallback['inscricao_estadual'] ?? null,
                ),
                'mono' => true,
            ],
            ['label' => 'Município / UF', 'valor' => $municipioUf],
            ['label' => 'Regime tributário', 'valor' => $regime],
        ];

        if ($modo === self::MODO_COMPACTO) {
            return $camposCompactos;
        }

        $cnaeCodigo = self::primeiroValor(
            $entidade?->cnae_principal,
            $fallback['cnae_principal'] ?? null,
        );
        $cnaeDescricao = self::primeiroValor(
            $entidade?->cnae_principal_descricao,
            $fallback['cnae_principal_descricao'] ?? null,
        );
        $cnaeTitle = collect([$cnaeCodigo, $cnaeDescricao])->filter()->implode(' — ');
        $endereco = collect([
            self::primeiroValor($entidade?->endereco, $fallback['endereco'] ?? null),
            self::primeiroValor($entidade?->numero, $fallback['numero'] ?? null),
            self::primeiroValor($entidade?->complemento, $fallback['complemento'] ?? null),
            self::primeiroValor($entidade?->bairro, $fallback['bairro'] ?? null),
        ])->filter()->implode(', ');

        return [
            ...array_slice($camposCompactos, 0, 3),
            [
                'label' => 'CEP',
                'valor' => self::formatarCep(self::primeiroValor(
                    $entidade?->cep,
                    $fallback['cep'] ?? null,
                )),
                'mono' => true,
            ],
            [
                'label' => 'CNAE principal',
                'valor' => $cnaeCodigo ?: $cnaeDescricao,
                'title' => $cnaeTitle,
            ],
            $camposCompactos[3],
            [
                'label' => 'Porte',
                'valor' => self::primeiroValor($entidade?->porte, $fallback['porte'] ?? null),
            ],
            [
                'label' => 'Início de atividade',
                'valor' => self::formatarData(self::primeiroValor(
                    $entidade?->data_inicio_atividade,
                    $fallback['data_inicio_atividade'] ?? null,
                )),
                'mono' => true,
            ],
            ['label' => 'Endereço', 'valor' => $endereco, 'full' => true, 'title' => $endereco],
        ];
    }

    private static function primeiroValor(mixed ...$valores): ?string
    {
        foreach ($valores as $valor) {
            if ($valor === null) {
                continue;
            }

            $normalizado = trim((string) $valor);
            if ($normalizado !== '') {
                return $normalizado;
            }
        }

        return null;
    }

    private static function situacaoHex(?string $situacao): string
    {
        return match (mb_strtoupper(trim((string) $situacao))) {
            'ATIVA', '02' => '#047857',
            '' => '#9ca3af',
            default => '#dc2626',
        };
    }

    private static function formatarDocumento(?string $documento): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $documento);

        return match (strlen($digitos)) {
            14 => preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $digitos),
            11 => preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $digitos),
            default => self::primeiroValor($documento),
        };
    }

    private static function formatarCep(?string $cep): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $cep);

        return strlen($digitos) === 8
            ? preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', $digitos)
            : self::primeiroValor($cep);
    }

    private static function formatarData(mixed $data): ?string
    {
        if ($data instanceof CarbonInterface) {
            return $data->format('d/m/Y');
        }

        if (! self::primeiroValor($data)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($data)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $data;
        }
    }
}
