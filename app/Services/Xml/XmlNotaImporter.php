<?php

namespace App\Services\Xml;

use App\Models\Cliente;
use App\Models\Participante;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Models\XmlNotaItem;
use Illuminate\Support\Facades\DB;

/**
 * Persiste uma NF-e parseada: dedup por (user_id, chave_acesso), grava
 * xml_notas + xml_notas_itens, classifica entrada/saída pela perspectiva do
 * dono e liga emit/dest a participantes (find-or-create) e clientes (match).
 */
class XmlNotaImporter
{
    /** @return 'novo'|'duplicado'|'duplicado_atualizado'|'sem_dono' */
    public function importar(array $parsed, string $ownerDoc, XmlImportacao $imp): string
    {
        $userId = (int) $imp->user_id;
        $h = $parsed['header'];
        $ownerDoc = preg_replace('/[^0-9]/', '', $ownerDoc);

        $existente = XmlNota::where('user_id', $userId)
            ->where('chave_acesso', $h['chave_acesso'])
            ->first();

        if ($existente) {
            if (empty($existente->status_autorizacao) && ! empty($h['status_autorizacao'])) {
                $existente->update([
                    'protocolo_autorizacao' => $h['protocolo_autorizacao'],
                    'data_autorizacao'      => $h['data_autorizacao'],
                    'status_autorizacao'    => $h['status_autorizacao'],
                    'motivo_autorizacao'    => $h['motivo_autorizacao'],
                ]);

                return 'duplicado_atualizado';
            }

            return 'duplicado';
        }

        $emitDoc = $h['emit_documento'];
        $destDoc = $h['dest_documento'];
        $donoAusente = false;
        if ($ownerDoc !== '' && $ownerDoc === $emitDoc) {
            $h['tipo_nota'] = XmlNota::TIPO_SAIDA;
        } elseif ($ownerDoc !== '' && $ownerDoc === $destDoc) {
            $h['tipo_nota'] = XmlNota::TIPO_ENTRADA;
        } else {
            $donoAusente = true; // mantém tipo_nota cru (tpNF) como fallback
        }

        // xml_notas.emit_documento/dest_documento são NOT NULL em prod.
        $h['emit_documento'] = $h['emit_documento'] ?? '';
        $h['dest_documento'] = $h['dest_documento'] ?? '';

        return DB::transaction(function () use ($parsed, $h, $userId, $imp, $emitDoc, $destDoc, $donoAusente) {
            $payload = $parsed['payload'];
            if ($donoAusente) {
                $payload['_dono_ausente'] = true;
            }

            $emitPart = $this->participante($userId, $emitDoc, $h['emit_razao_social'], $h['emit_uf'], $h['emit_municipio_ibge'], $h['emit_ie'], $imp);
            $destPart = $this->participante($userId, $destDoc, $h['dest_razao_social'], $h['dest_uf'], $h['dest_municipio_ibge'], $h['dest_ie'], $imp);

            $nota = XmlNota::create(array_merge($h, [
                'user_id'              => $userId,
                'importacao_xml_id'    => $imp->id,
                'cliente_id'           => $imp->cliente_id,
                'payload'              => $payload,
                'emit_participante_id' => $emitPart?->id,
                'dest_participante_id' => $destPart?->id,
                'emit_cliente_id'      => $this->clienteId($userId, $emitDoc),
                'dest_cliente_id'      => $this->clienteId($userId, $destDoc),
            ]));

            foreach ($parsed['itens'] as $item) {
                XmlNotaItem::create(array_merge($item, [
                    'xml_nota_id' => $nota->id,
                    'user_id'     => $userId,
                ]));
            }

            return $donoAusente ? 'sem_dono' : 'novo';
        });
    }

    private function participante(int $userId, ?string $doc, ?string $razao, ?string $uf, ?string $mun, ?string $ie, XmlImportacao $imp): ?Participante
    {
        if (empty($doc)) {
            return null; // dest exterior / sem documento
        }
        $tipoDoc = strlen($doc) === 11 ? 'CPF' : 'CNPJ';

        return Participante::firstOrCreate(
            ['user_id' => $userId, 'documento' => $doc],
            [
                'razao_social'       => $razao,
                'uf'                 => $uf,
                'codigo_municipal'   => $mun,
                'inscricao_estadual' => $ie,
                'origem_tipo'        => 'xml',
                'importacao_xml_id'  => $imp->id,
                'tipo_documento'     => $tipoDoc,
            ]
        );
        // NÃO atualizamos situacao_cadastral/regime_tributario/ultima_consulta_em (regra do projeto).
    }

    private function clienteId(int $userId, ?string $doc): ?int
    {
        if (empty($doc)) {
            return null;
        }

        return Cliente::where('user_id', $userId)->where('documento', $doc)->value('id');
    }
}
