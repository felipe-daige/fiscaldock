<?php

namespace App\Services\Consultas\Persistencia;

use App\Models\ConsultaResultado;
use App\Services\Consultas\Dto\ResultadoFonte;

class PersistenciaCnpj
{
    /**
     * Persiste o mínimo necessário para uma reconsulta reproduzir os parâmetros complementares
     * originais. Fica no próprio resultado agnóstico ao documento; não cria cadastro paralelo.
     *
     * Chamado DUAS vezes por alvo (job): antes do loop de fontes e de novo ao final. A segunda
     * passada é o que salva a reconsulta manual das fontes que exigem complemento vindo do
     * CADASTRO daquele mesmo lote — `data_inicio_atividade` (BCB Valores a Receber de PJ) só
     * entra no alvo quando a minhareceita responde, depois da primeira gravação. Sem ela, um
     * alvo sem a data no cadastro local consultava na primeira vez e ficava INDISPONÍVEL no
     * retry. Gravação sem mudança é descartada (não gera UPDATE por alvo).
     */
    public function gravarContextoAlvo(int $loteId, string $alvoTipo, int $alvoId, array $alvo): void
    {
        $chaveEscopo = $alvoTipo === 'cliente' ? 'cliente_id' : 'participante_id';
        $linha = ConsultaResultado::firstOrNew([
            'consulta_lote_id' => $loteId,
            $chaveEscopo => $alvoId,
        ]);
        $dados = $linha->resultado_dados ?? [];
        $campos = [
            'documento', 'tipo_pessoa', 'nome', 'birthdate', 'nome_mae', 'nome_pai',
            'uf_nascimento', 'titulo_eleitoral', 'ano', 'data_inicio_atividade',
        ];
        $contexto = array_filter(
            array_intersect_key($alvo, array_flip($campos)),
            fn ($valor) => $valor !== null && $valor !== '',
        );

        // `==` (não `===`): compara par a par ignorando a ORDEM das chaves — o alvo enriquecido
        // pelo cadastro pode trazer os mesmos valores em ordem diferente.
        if ($linha->exists && ($dados['_alvo_contexto'] ?? null) == $contexto) {
            return;
        }

        $dados['_alvo_contexto'] = $contexto;
        $linha->resultado_dados = $dados;
        $linha->status ??= ConsultaResultado::STATUS_PENDENTE;
        $linha->save();
    }

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

        // Bloco não-vazio da fonte = produziu conteúdo persistível (sucesso, ou um desfecho
        // reconhecido tipo INDETERMINADO/NAO_ENCONTRADA/impedimento real — ex.: CrfFgtsFonte
        // mapeando `erro_participante` p/ "Irregular") — limpa qualquer marca de erro anterior
        // dela. Sem isso, uma marca de retry de tentativa passada nunca seria limpa (o status
        // aqui não é literalmente 'sucesso'), e o botão de reconsulta continuaria oferecido
        // pra uma fonte que já deu um resultado definitivo.
        if (! empty($resultado->dados) && isset($dados['_fontes_erro'][$resultado->chave])) {
            unset($dados['_fontes_erro'][$resultado->chave]);
            if (empty($dados['_fontes_erro'])) {
                unset($dados['_fontes_erro']);
            }
        }

        $linha->resultado_dados = $dados;
        $linha->status = $resultado->status === 'sucesso'
            ? 'sucesso'
            : ($this->statusResolvido($linha->status) ?: 'erro');
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
        $linha->status = $this->statusResolvido($linha->status) ?: 'erro';
        $linha->consultado_em = now();
        $linha->save();
    }

    /**
     * Status "real" da linha para o cálculo de desfecho. O placeholder PENDENTE gravado por
     * gravarContextoAlvo ANTES do loop de fontes NÃO é um desfecho: precisa ceder para
     * 'sucesso'/'erro' quando uma fonte de fato grava. Sem isso, um alvo cujas fontes TODAS
     * falham (ex.: PF sem cadastro auto) ficaria preso em 'pendente' num lote já CONCLUIDO.
     */
    private function statusResolvido(?string $status): ?string
    {
        return $status === ConsultaResultado::STATUS_PENDENTE ? null : $status;
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
