<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * 0150 (participante) → `participantes`.
 * |0150|COD_PART|NOME|COD_PAIS|CNPJ|CPF|IE|COD_MUN|SUFRAMA|END|NUM|COMPL|BAIRRO|
 *
 * Índices confirmados no arquivo real UTIDA + BackfillParticipantesFiscal:130 (CNPJ=$p[5]).
 * O set-node PIS/COFINS estava off-by-one no CNPJ ($p[4]=COD_PAIS) — ignorado.
 * documento = CNPJ (senão CPF), só dígitos. ON CONFLICT (user_id, documento) DO NOTHING.
 * Engine adiciona user_id/cliente_id/importacao_efd_id.
 *
 * Layout 0150 é idêntico entre EFD ICMS/IPI e PIS/COFINS — só muda a tag de origem;
 * o driver passa 'SPED_EFD_FISCAL' (default) ou 'SPED_EFD_CONTRIB'.
 */
class Handler0150 implements SpedRegistroHandler
{
    public function __construct(
        private string $origemTipo = 'SPED_EFD_FISCAL',
    ) {}

    public function registros(): array
    {
        return ['0150'];
    }

    public function tabela(): string
    {
        return 'participantes';
    }

    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array
    {
        $cnpj = preg_replace('/\D/', '', (string) $rec->campo(5));
        $cpf = preg_replace('/\D/', '', (string) $rec->campo(6));
        $documento = $cnpj !== '' ? $cnpj : $cpf;

        // Sem documento não há participante persistível (unique é por documento).
        if ($documento === '') {
            return null;
        }

        $codMun = Campos::texto($rec->campo(8));

        return [
            'documento' => $documento,
            // O engine insere direto (DB::table), sem passar pelo hook `saving` do Model
            // que derivaria isto — então o handler seta explicitamente. tipo_documento
            // (PJ/PF) e UF (do prefixo IBGE do COD_MUN) sem nenhuma chamada externa.
            // `?? 'PJ'` casa com o DEFAULT da coluna (NOT NULL): um documento malformado
            // (nº de dígitos ≠ 11/14) não pode injetar NULL e derrubar a transação inteira.
            'tipo_documento' => Campos::tipoDocumento($documento) ?? 'PJ',
            'razao_social' => Campos::texto($rec->campo(3)),
            'inscricao_estadual' => Campos::texto($rec->campo(7)),
            'codigo_municipal' => $codMun,
            'uf' => Campos::ufPorCodigoMunicipio($codMun),
            'suframa' => Campos::texto($rec->campo(9)),
            'endereco' => Campos::texto($rec->campo(10)),
            'numero' => Campos::texto($rec->campo(11)),
            'complemento' => Campos::texto($rec->campo(12)),
            'bairro' => Campos::texto($rec->campo(13)),
            'origem_tipo' => $this->origemTipo,
        ];
    }
}
