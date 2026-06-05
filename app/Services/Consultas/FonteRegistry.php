<?php

namespace App\Services\Consultas;

use App\Services\Consultas\Contracts\Fonte;

class FonteRegistry
{
    /** @var array<string, Fonte> */
    private array $fontes = [];

    /** @param Fonte[] $fontes */
    public function __construct(array $fontes = [])
    {
        foreach ($fontes as $f) {
            $this->fontes[$f->chave()] = $f;
        }
    }

    public function get(string $chave): ?Fonte
    {
        return $this->fontes[$chave] ?? null;
    }

    /** True se TODAS as chaves existem no registry (e a lista não é vazia). */
    public function cobre(array $chaves): bool
    {
        if (empty($chaves)) {
            return false;
        }

        foreach ($chaves as $chave) {
            if (! isset($this->fontes[$chave])) {
                return false;
            }
        }

        return true;
    }

    /** @return Fonte[] */
    public function fontesDe(array $chaves): array
    {
        $out = [];
        foreach ($chaves as $chave) {
            if (isset($this->fontes[$chave])) {
                $out[] = $this->fontes[$chave];
            }
        }

        return $out;
    }
}
