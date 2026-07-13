<?php

namespace App\Services\Clearance;

/**
 * Fonte única das ETAPAS do clearance — o mesmo papel que o campo `etapas` do
 * MonitoramentoPlanoSeeder cumpre na Consulta CNPJ.
 *
 * A tela de resultado recebe esta lista (`data-etapas`) e monta o strip a partir dela, em vez de
 * adivinhar rótulos no JS (era o que produzia "Etapa 1" quando a consulta terminava).
 *
 * As chaves das etapas da contraparte (`cadastrais`, `certidoes_federais`, `certidoes_estaduais`)
 * NÃO são livres: precisam bater com `config('consultas.fonte_etapa')`, que é como o
 * ProcessarConsultaJob descobre em qual etapa cada fonte cai (cadastro → cadastrais,
 * cnd_federal → certidoes_federais, sintegra → certidoes_estaduais).
 */
final class ClearanceEtapas
{
    /**
     * @return array<int, array{numero:int, chave:string, label:string}>
     */
    public static function para(string $tier): array
    {
        $etapas = [
            ['numero' => 1, 'chave' => 'inicializacao', 'label' => 'Preparando consulta'],
            ['numero' => 2, 'chave' => 'documentos', 'label' => 'Consultando SEFAZ'],
        ];

        // Clearance completo: a contraparte de cada nota também é investigada (Camada A).
        if ($tier === 'full') {
            $etapas[] = ['numero' => 3, 'chave' => 'cadastrais', 'label' => 'Cadastro da contraparte'];
            $etapas[] = ['numero' => 4, 'chave' => 'certidoes_federais', 'label' => 'CND Federal'];
            $etapas[] = ['numero' => 5, 'chave' => 'certidoes_estaduais', 'label' => 'SINTEGRA'];
        }

        return $etapas;
    }

    public static function total(string $tier): int
    {
        return count(self::para($tier));
    }

    /** Etapa dos DOCUMENTOS (a fase SEFAZ) — sempre a 2. */
    public static function documentos(string $tier): array
    {
        return self::para($tier)[1];
    }

    /** Última etapa — usada no payload terminal (o strip fecha com o nome do processo, não "Etapa N"). */
    public static function ultima(string $tier): array
    {
        $etapas = self::para($tier);

        return $etapas[count($etapas) - 1];
    }

    /** Tier do lote, gravado em `consulta_lotes.resultado_resumo['tier']` ao iniciar. */
    public static function tierDoLote(?array $resultadoResumo): string
    {
        return ($resultadoResumo['tier'] ?? null) === 'full' ? 'full' : 'basico';
    }
}
