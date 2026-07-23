<?php

namespace App\Services\Consultas\Fontes\Advocacia;

/** TCU CNI — relação de inabilitados, disponível somente para CPF. */
class TcuCniInabilitadoFonte extends TcuCniFonteBase
{
    public function chave(): string
    {
        return 'tcu_cni_inabilitado';
    }

    public function aceitaPessoa(): array
    {
        return ['PF'];
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.tcu_cni_inabilitado', 1.00);
    }

    protected function tipoRelacao(): int
    {
        return 2;
    }
}
