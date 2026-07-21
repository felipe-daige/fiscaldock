<?php

namespace App\Services\Consultas;

use App\Models\Cliente;
use App\Models\Participante;
use App\Support\Cnpj;
use Illuminate\Support\Facades\DB;

/**
 * Resolve a inscrição municipal (IM) de um CNPJ e a PERSISTE no perfil.
 *
 * ┌─ INVARIANTE DO PRODUTO ───────────────────────────────────────────────────┐
 * │ O NÚMERO da IM é ESTÁVEL. Resolvido UMA ÚNICA VEZ e gravado no perfil       │
 * │ (cliente / participante / empresa-própria). A partir daí NUNCA é            │
 * │ reconsultado — as próximas consultas leem do perfil. Só a CERTIDÃO          │
 * │ municipal (CND) é reconsultada a cada consulta, porque o STATUS dela muda;  │
 * │ o número da inscrição, não. Por isso o passo 1 (perfil) faz short-circuit.  │
 * └────────────────────────────────────────────────────────────────────────────┘
 *
 * Cadeia (passos 2+ SÓ rodam quando o perfil está vazio; ao achar, grava no perfil):
 *   1. Perfil (coluna `inscricao_municipal`) — fonte da verdade, custo zero, definitivo.
 *   2. Cross-cadastro por CNPJ — IM já conhecida DESSE CNPJ em qualquer outro cadastro do
 *      usuário (outro cliente/participante). O número da IM é do CNPJ, não do registro; então
 *      reaproveita entre a empresa-própria (cujo EFD 0000 traz a IM), clientes e participantes.
 *   3. Acervo XML (`xml_notas.emit_im`/`dest_im` do CNPJ) — custo zero, do que já importamos.
 *
 * Todas GRÁTIS. Sem null → só quando o número ainda é desconhecido em TODAS as fontes locais.
 *
 * Duas outras vias preenchem o perfil FORA deste resolver (e caem no passo 1 na próxima vez):
 *   · EFD registro 0000 — IM do declarante, gravada no perfil pelo n8n ao criar o cliente.
 *   · Colheita da CND — a resposta da CND Municipal traz `inscricao_municipal`; o
 *     ProcessarConsultaJob grava no perfil após uma CND com sucesso (grátis, sem chamada
 *     extra). Cobre os municípios cuja CND roda por CNPJ. NÃO há consulta paga dedicada de
 *     IM (o CCM do InfoSimples só existe p/ SP capital; a colheita o torna desnecessário).
 */
class InscricaoMunicipalResolver
{
    /**
     * Cadeia sem custo externo (perfil → cross-cadastro → acervo XML). Retorna a IM e garante
     * que ela fica persistida no perfil (resolução definitiva). Null = número ainda desconhecido;
     * a CND Municipal degrada p/ INDISPONIVEL e a IM entra depois (colheita da CND / EFD / manual).
     */
    public function resolver(array $alvo, string $alvoTipo, int $alvoId, int $userId): ?string
    {
        // 1. Perfil. Se já temos, retorna e NÃO consulta mais nada — o número não muda.
        if ($salva = $this->doPerfil($alvoTipo, $alvoId)) {
            return $salva;
        }

        $cnpj = Cnpj::digitos((string) ($alvo['cnpj'] ?? ''));
        if ($cnpj === '') {
            return null;
        }

        // 2. Cross-cadastro: IM já conhecida desse CNPJ em outro cadastro do usuário.
        if ($doCadastro = $this->doCrossCadastro($cnpj, $userId, $alvoTipo, $alvoId)) {
            $this->persistir($alvoTipo, $alvoId, $doCadastro);

            return $doCadastro;
        }

        // 3. Acervo XML — IM do emitente/destinatário desse CNPJ já importado.
        if ($doXml = $this->doAcervoXml($cnpj, $userId)) {
            $this->persistir($alvoTipo, $alvoId, $doXml);

            return $doXml;
        }

        // Desconhecido nas fontes locais. Sem consulta paga dedicada de IM: a CND degrada p/
        // INDISPONIVEL e o número entra depois (colheita da CND, EFD 0000 via n8n, ou manual).
        return null;
    }

    /**
     * IM já cadastrada para o MESMO CNPJ em qualquer cliente/participante do usuário (menos o
     * próprio alvo, que o passo 1 já cobriu). O número da IM pertence ao CNPJ, então vale entre
     * todos os cadastros dele — inclusive a empresa-própria cujo EFD 0000 preencheu a IM.
     */
    private function doCrossCadastro(string $cnpj, int $userId, string $alvoTipo, int $alvoId): ?string
    {
        $digitos = "regexp_replace(documento, '[^0-9]', '', 'g') = ?";

        $cli = Cliente::where('user_id', $userId)
            ->whereRaw($digitos, [$cnpj])
            ->whereNotNull('inscricao_municipal')->where('inscricao_municipal', '!=', '')
            ->when($alvoTipo === 'cliente', fn ($q) => $q->whereKeyNot($alvoId))
            ->value('inscricao_municipal');

        if (! $cli) {
            $cli = Participante::where('user_id', $userId)
                ->whereRaw($digitos, [$cnpj])
                ->whereNotNull('inscricao_municipal')->where('inscricao_municipal', '!=', '')
                ->when($alvoTipo === 'participante', fn ($q) => $q->whereKeyNot($alvoId))
                ->value('inscricao_municipal');
        }

        $cli = trim((string) $cli);

        return $cli !== '' ? $cli : null;
    }

    /**
     * IM já salva no perfil, SEM nenhuma consulta externa. Ponto único de leitura — quem
     * precisar saber "já resolvemos a IM deste alvo?" chama aqui.
     */
    public function doPerfil(string $alvoTipo, int $alvoId): ?string
    {
        $im = $alvoTipo === 'cliente'
            ? Cliente::whereKey($alvoId)->value('inscricao_municipal')
            : Participante::whereKey($alvoId)->value('inscricao_municipal');

        $im = trim((string) $im);

        return $im !== '' ? $im : null;
    }

    private function doAcervoXml(string $cnpj, int $userId): ?string
    {
        $im = DB::table('xml_notas')
            ->where('user_id', $userId)
            ->where('emit_documento', $cnpj)
            ->whereNotNull('emit_im')->where('emit_im', '!=', '')
            ->value('emit_im');

        if (! $im) {
            $im = DB::table('xml_notas')
                ->where('user_id', $userId)
                ->where('dest_documento', $cnpj)
                ->whereNotNull('dest_im')->where('dest_im', '!=', '')
                ->value('dest_im');
        }

        $im = trim((string) $im);

        return $im !== '' ? $im : null;
    }

    /**
     * Grava a IM resolvida no perfil — torna a resolução DEFINITIVA (o passo 1 passa a
     * short-circuitar em toda consulta futura). Só grava se ainda estiver vazia (a IM
     * informada manualmente pelo usuário sempre vence; nunca sobrescrevemos).
     */
    public function persistir(string $alvoTipo, int $alvoId, string $im): void
    {
        $im = trim($im);
        if ($im === '') {
            return;
        }

        $query = $alvoTipo === 'cliente'
            ? Cliente::whereKey($alvoId)
            : Participante::whereKey($alvoId);

        // Só grava se AINDA não há IM — a informada manualmente vence e nunca é sobrescrita.
        // Cobre NULL e string vazia (n8n/EFD pode gravar ''): sem o orWhere, um perfil com ''
        // nunca receberia a IM resolvida e o número seria re-resolvido em toda consulta.
        $query->where(fn ($q) => $q->whereNull('inscricao_municipal')->orWhere('inscricao_municipal', ''))
            ->update(['inscricao_municipal' => $im]);
    }
}
