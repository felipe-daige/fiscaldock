<?php

namespace App\Services\Consultas;

use App\Services\Consultas\Contracts\ConsultaProvider;
use App\Services\Consultas\Providers\InfoSimplesProvider;
use App\Services\Consultas\Providers\MinhaReceitaProvider;

/**
 * Resolve o nome de provider declarado pela Fonte (`provider()`) na instância concreta. Fonte única
 * do mapa nome→classe — antes duplicado em ProcessarConsultaJob e CertidaoPedidoService, o que fazia
 * um provider novo precisar ser adicionado em dois match e podia divergir (um estoura, o outro degrada).
 */
class ProviderResolver
{
    public function resolver(string $nome): ConsultaProvider
    {
        return match ($nome) {
            'minhareceita' => app(MinhaReceitaProvider::class),
            'infosimples' => app(InfoSimplesProvider::class),
            default => throw new \RuntimeException("Provider não suportado: {$nome}"),
        };
    }
}
