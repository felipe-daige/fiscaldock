<?php

namespace App\Services\Consultas\Fontes\Advocacia;

/** TCU CNI — relação de inidôneos, disponível para CPF e CNPJ. */
class TcuCniInidoneoFonte extends TcuCniFonteBase
{
    public function chave(): string
    {
        return 'tcu_cni_inidoneo';
    }

    public function aceitaPessoa(): array
    {
        return ['PF', 'PJ'];
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.tcu_cni_inidoneo', 1.00);
    }

    protected function tipoRelacao(): int
    {
        return 1;
    }
}
