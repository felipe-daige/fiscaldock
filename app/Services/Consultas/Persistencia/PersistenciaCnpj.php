<?php

namespace App\Services\Consultas\Persistencia;

use App\Models\ConsultaResultado;
use App\Services\Consultas\Dto\ResultadoFonte;

class PersistenciaCnpj
{
    /**
     * Chaves de fonte já persistidas para este alvo no lote (top-level de resultado_dados).
     * Usado pelo job p/ idempotência: em retry, não re-consultar (nem re-cobrar) o que já rodou.
     *
     * @param  string  $alvoTipo  'participante' | 'cliente'
     * @return string[]
     */
    public function chavesPersistidas(int $loteId, string $alvoTipo, int $alvoId): array
    {
        $chaveEscopo = $alvoTipo === 'cliente' ? 'cliente_id' : 'participante_id';

        $linha = ConsultaResultado::where('consulta_lote_id', $loteId)
            ->where($chaveEscopo, $alvoId)
            ->first();

        return $linha ? array_keys($linha->resultado_dados ?? []) : [];
    }

    /**
     * @param  string  $alvoTipo  'participante' | 'cliente'
     */
    public function gravar(int $loteId, string $alvoTipo, int $alvoId, ResultadoFonte $resultado): void
    {
        $chaveEscopo = $alvoTipo === 'cliente' ? 'cliente_id' : 'participante_id';

        $linha = ConsultaResultado::firstOrNew([
            'consulta_lote_id' => $loteId,
            $chaveEscopo => $alvoId,
        ]);

        $dados = $linha->resultado_dados ?? [];

        // merge: campos da fonte sobrescrevem; consultas_realizadas acumula sem duplicar
        $realizadas = array_values(array_unique(array_merge(
            $dados['consultas_realizadas'] ?? [],
            $resultado->dados['consultas_realizadas'] ?? [],
        )));

        $dados = array_merge($dados, $resultado->dados);
        if ($realizadas) {
            $dados['consultas_realizadas'] = $realizadas;
        }

        // Sucesso da fonte limpa qualquer marca de erro anterior dela (re-consulta deu certo).
        if ($resultado->status === 'sucesso' && isset($dados['_fontes_erro'][$resultado->chave])) {
            unset($dados['_fontes_erro'][$resultado->chave]);
            if (empty($dados['_fontes_erro'])) {
                unset($dados['_fontes_erro']);
            }
        }

        $linha->resultado_dados = $dados;
        $linha->status = $resultado->status === 'sucesso' ? 'sucesso' : ($linha->status ?: 'erro');
        if ($resultado->status !== 'sucesso' && $resultado->mensagem) {
            $linha->error_message = $resultado->mensagem;
        }
        $linha->consultado_em = now();
        $linha->save();
    }

    /**
     * Registra a ORIGEM da falha de uma fonte que não retornou resultado, num mapa reservado
     * `_fontes_erro` (chave da fonte → objeto {origem, status, codigo, tentativas}). Não é chave
     * de fonte, então NÃO entra na idempotência de retry — a fonte segue re-consultável.
     *
     * `tentativas` é preservado entre marcações (uma re-falha não zera o contador de retries).
     *
     * @param  string  $alvoTipo  'participante' | 'cliente'
     * @param  string  $origem  'interno' (exceção nossa) | 'integracao' (fonte externa falhou)
     * @param  string|null  $status  classe do código: 'retry' (transitório) | 'fatal' (permanente); null p/ 'interno'
     * @param  int|null  $codigo  código bruto do provedor (ex.: 600) — debug/UI
     */
    public function marcarErroFonte(
        int $loteId,
        string $alvoTipo,
        int $alvoId,
        string $chave,
        string $origem,
        ?string $status = null,
        ?int $codigo = null
    ): void {
        $chaveEscopo = $alvoTipo === 'cliente' ? 'cliente_id' : 'participante_id';

        $linha = ConsultaResultado::firstOrNew([
            'consulta_lote_id' => $loteId,
            $chaveEscopo => $alvoId,
        ]);

        $dados = $linha->resultado_dados ?? [];
        $erros = $this->normalizarFontesErro($dados['_fontes_erro'] ?? []);
        $tentativas = $erros[$chave]['tentativas'] ?? 0; // preserva numa re-falha

        $erros[$chave] = [
            'origem' => $origem,
            'status' => $status,
            'codigo' => $codigo,
            'tentativas' => $tentativas,
        ];
        $dados['_fontes_erro'] = $erros;

        $linha->resultado_dados = $dados;
        $linha->status = $linha->status ?: 'erro';
        $linha->consultado_em = now();
        $linha->save();
    }

    /**
     * Normaliza o mapa `_fontes_erro`. Retrocompat: entradas legadas eram string
     * ('integracao'|'interno'); viram objeto. 'integracao' legado assume status 'retry' (era o
     * único caso que disparava marca de integração antes do enriquecimento).
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, array{origem:string,status:?string,codigo:?int,tentativas:int}>
     */
    public function normalizarFontesErro(array $raw): array
    {
        $out = [];
        foreach ($raw as $chave => $val) {
            if (is_string($val)) {
                $out[$chave] = [
                    'origem' => $val,
                    'status' => $val === 'integracao' ? 'retry' : null,
                    'codigo' => null,
                    'tentativas' => 0,
                ];
            } elseif (is_array($val)) {
                $out[$chave] = [
                    'origem' => $val['origem'] ?? 'integracao',
                    'status' => $val['status'] ?? null,
                    'codigo' => $val['codigo'] ?? null,
                    'tentativas' => (int) ($val['tentativas'] ?? 0),
                ];
            }
        }

        return $out;
    }

    /**
     * Incrementa o contador de tentativas de uma fonte em falha (chamado ao disparar uma
     * reconsulta paga). No-op se a fonte não estiver marcada.
     *
     * @param  string  $alvoTipo  'participante' | 'cliente'
     */
    public function incrementarTentativaFonte(int $loteId, string $alvoTipo, int $alvoId, string $chave): void
    {
        $chaveEscopo = $alvoTipo === 'cliente' ? 'cliente_id' : 'participante_id';

        $linha = ConsultaResultado::where('consulta_lote_id', $loteId)
            ->where($chaveEscopo, $alvoId)
            ->first();
        if (! $linha) {
            return;
        }

        $dados = $linha->resultado_dados ?? [];
        $erros = $this->normalizarFontesErro($dados['_fontes_erro'] ?? []);
        if (! isset($erros[$chave])) {
            return;
        }

        $erros[$chave]['tentativas'] = (int) $erros[$chave]['tentativas'] + 1;
        $dados['_fontes_erro'] = $erros;
        $linha->resultado_dados = $dados;
        $linha->save();
    }
}
