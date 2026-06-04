<?php

namespace App\Support;

/**
 * Helper estático para resolução de CFOPs brasileiros.
 *
 * Fonte do mapa: CONFAZ — Anexo CFOP, Ajuste SINIEF 07/2001 e alterações.
 * Configuração canônica: config/cfop.php
 */
class Cfop
{
    /**
     * Retorna a descrição humana de um CFOP.
     *
     * Busca primeiro na tabela de descrições exatas (config cfop.descricoes).
     * Se não encontrar, faz fallback para a família pelo 1º dígito (config cfop.familias).
     * Se não houver família, devolve apenas o código.
     */
    public static function descricao(?string $codigo): string
    {
        $codigo = trim((string) $codigo);
        if ($codigo === '') {
            return 'CFOP não informado';
        }

        $desc = config("cfop.descricoes.{$codigo}");
        if ($desc) {
            return "{$codigo} — {$desc}";
        }

        $familia = config('cfop.familias.' . substr($codigo, 0, 1));

        return $familia ? "{$codigo} — {$familia}" : $codigo;
    }

    /**
     * Classifica o tipo de operação pelo 1º dígito do CFOP.
     *
     * 1/2/3 → entrada | 5/6/7 → saida | demais → indefinido
     */
    public static function tipoOperacao(?string $codigo): string
    {
        $d = substr(trim((string) $codigo), 0, 1);

        return in_array($d, ['1', '2', '3'], true) ? 'entrada'
            : (in_array($d, ['5', '6', '7'], true) ? 'saida' : 'indefinido');
    }
}
