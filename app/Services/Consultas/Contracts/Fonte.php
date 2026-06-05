<?php

namespace App\Services\Consultas\Contracts;

interface Fonte
{
    /** Chave canônica usada em resultado_dados / consultas_realizadas (ex: 'cadastro'). */
    public function chave(): string;

    /** Provider responsável: 'minhareceita' | 'infosimples'. */
    public function provider(): string;

    /** Slug do endpoint no provider (vazio quando o provider monta a URL pelo CNPJ). */
    public function slug(): string;

    /** Monta os params da chamada a partir do alvo (participante normalizado). */
    public function params(array $alvo): array;

    /** Converte o raw do provider no shape canônico mergeado em resultado_dados. */
    public function normalizar(array $raw): array;

    /** Custo em créditos desta fonte (0 = grátis, ex: cadastro/minhareceita). */
    public function custoCreditos(): int;
}
