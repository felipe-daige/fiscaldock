<?php

namespace App\Services\Efd\Handlers;

/**
 * Handler que agrega MUITOS registros numa ÚNICA linha (bloco E de apuração → 1 linha
 * por importação). `mapear` acumula estado e devolve null a cada registro; a linha
 * final sai em `finalizar()`, chamado pela engine após esgotar o stream.
 *
 * Handler com estado ⇒ exige instância nova por importação (o FiscalDriver constrói
 * uma vez e reusa a MESMA instância durante todo o job).
 */
interface HandlerAgregador extends SpedRegistroHandler
{
    /**
     * Linha agregada do bloco (ou null se o bloco não existiu no arquivo).
     *
     * @return array<string, mixed>|null
     */
    public function finalizar(): ?array;
}
