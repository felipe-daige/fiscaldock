<?php

namespace App\Services\Efd\Sped;

/**
 * Uma linha do arquivo SPED já tokenizada. Register-agnostic — não sabe o que
 * cada campo significa (isso é do handler). 100% compartilhado entre EFD
 * ICMS/IPI e PIS/COFINS.
 *
 * Índice canônico = o do SPED via explode('|'): campos[0]='' (antes do 1º pipe),
 * campos[1]=REG, campos[2]=1º campo de dados. Portanto campo(N) casa 1:1 com a
 * notação `$p[N]` da spec (motor-laravel.md §10.1) e com a regra de porte dos
 * Set-nodes do n8n: `fields[N]` do n8n ⟺ campo(N+2) aqui.
 */
class SpedRecord
{
    /**
     * @param  array<int, string>  $campos  Array cru do explode('|'): campos[1]=REG.
     */
    public function __construct(
        public readonly string $reg,
        public readonly array $campos,
    ) {}

    /**
     * Valor do campo na posição SPED $i (1=REG, 2=1º campo), com trim.
     *
     * Campo presente porém vazio devolve '' — NUNCA null: o handler precisa
     * distinguir "veio vazio" de "não veio" (ex.: COD_PART vazio de NFC-e é
     * legítimo; foi o que o merge do n8n droppou no bug UTIDA). Índice além do
     * fim da linha devolve null (campo ausente).
     */
    public function campo(int $i): ?string
    {
        if (! array_key_exists($i, $this->campos)) {
            return null;
        }

        return trim($this->campos[$i]);
    }
}
