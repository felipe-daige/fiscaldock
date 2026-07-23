<?php

namespace App\Services\Consultas;

use App\Models\CertidaoPedido;
use App\Models\ConsultaResultado;
use App\Services\Consultas\Contracts\FonteDuasEtapas;
use Illuminate\Support\Facades\Log;

/**
 * Máquina de estados dos pedidos de certidão de 2 etapas (docs/advocacia/consultas-certidoes.md
 * fase 4). `criar()` nasce da etapa 1 bem-sucedida (chamada pelo ProcessarConsultaJob); `verificar()`
 * é o passo do follow-up disparado UNICAMENTE pelo sweep `certidoes:verificar-pedidos` (everyMinute):
 * reivindica o pedido (claim atômico), polla a etapa 2, e ao emitir arquiva o PDF, grava em
 * `certidoes` e atualiza o card do lote. Sem self-redispatch — todo agendamento é via
 * `proxima_verificacao_em`, que o sweep observa (o unique-lock de fila descartava re-dispatch).
 */
class CertidaoPedidoService
{
    public function __construct(
        private FonteRegistry $registry,
        private ThrottleProvider $throttle,
        private ComprovanteArquivador $arquivador,
        private CertidaoRegistro $registro,
        private ProviderResolver $providerResolver,
    ) {}

    /**
     * Cria o pedido a partir da etapa 1 bem-sucedida e agenda a 1ª verificação da etapa 2.
     * Null quando a etapa 1 não devolveu as chaves de correlação (o chamador trata como falha).
     */
    public function criar(
        FonteDuasEtapas $fonte,
        array $alvo,
        array $etapa1Data,
        int $userId,
        string $alvoTipo,
        int $alvoId,
        string $documento,
        ?int $loteId,
    ): ?CertidaoPedido {
        $correlacao = $fonte->extrairCorrelacao($etapa1Data);
        if ($correlacao === []) {
            return null;
        }

        $documento = preg_replace('/\D/', '', $documento) ?? '';
        $prox = now()->addMinutes($fonte->prazoInicialMinutos());

        $pedido = CertidaoPedido::create([
            'user_id' => $userId,
            'cliente_id' => $alvoTipo === 'cliente' ? $alvoId : null,
            'participante_id' => $alvoTipo === 'participante' ? $alvoId : null,
            'alvo_tipo' => $alvoTipo,
            'alvo_documento' => $documento,
            'tipo' => $fonte->chave(),
            'slug_obter' => $fonte->slugObter($alvo),
            'estado' => CertidaoPedido::SOLICITADA,
            'correlacao' => $correlacao,
            'tentativas' => 0,
            'proxima_verificacao_em' => $prox,
            'consulta_lote_id' => $loteId,
            'solicitado_em' => now(),
        ]);

        // Não despacha o job aqui: o sweep `certidoes:verificar-pedidos` (everyMinute) é o ÚNICO
        // dispatcher e pega o pedido quando `proxima_verificacao_em` vence. Um único caminho de
        // agendamento evita o descarte silencioso que o self-redispatch sob unique-lock causava.
        return $pedido;
    }

    /**
     * Passo do follow-up: confere a etapa 2 e transiciona o pedido. Único dispatcher é o sweep
     * `certidoes:verificar-pedidos` (everyMinute) — não há self-redispatch, então o unique-lock de
     * fila não descarta re-agendamentos. A não-duplicação vem do CLAIM ATÔMICO abaixo, não do lock.
     */
    public function verificar(CertidaoPedido $pedido): void
    {
        // CLAIM atômico: empurra a próxima verificação pro futuro SÓ se o pedido ainda está aberto
        // e vencido. Se 0 linhas (outro worker já reivindicou, ou não está mais vencido/aberto),
        // sai — sem isto, dois sweeps sobrepostos pagariam a conferência 2×.
        if (! $this->reivindicar($pedido)) {
            return;
        }

        $fonte = $this->registry->get($pedido->tipo);
        if (! $fonte instanceof FonteDuasEtapas) {
            // Fonte fora do registry (deploy transitório sem a classe) é CONDIÇÃO PASSAGEIRA, não
            // motivo de falha terminal de um pedido já pago — parqueia e reconfere depois.
            $pedido->update([
                'proxima_verificacao_em' => now()->addHour(),
                'erro' => 'Fonte de 2 etapas indisponível no momento: '.$pedido->tipo,
            ]);

            return;
        }

        // Retomada do estado DISPONIVEL: a etapa 2 já emitiu numa passada anterior (veredito+PDF em
        // `resultado_bloco`), mas a persistência final falhou. NÃO re-consultar (é pago) — só concluir.
        if ($pedido->estado === CertidaoPedido::DISPONIVEL && is_array($pedido->resultado_bloco)) {
            $this->finalizar($pedido, $fonte, $pedido->resultado_bloco);

            return;
        }

        try {
            $alvo = ['cnpj' => $pedido->alvo_documento];
            $this->throttle->aguardar($fonte->provider());
            $resp = $this->providerResolver->resolver($fonte->provider())->consultar(
                $pedido->slug_obter,
                $fonte->paramsObter($alvo, $pedido->correlacao ?? []),
            );
        } catch (\Throwable $e) {
            // Exceção do provider (DNS/TLS, resposta não-classificável) é transitória: trata como
            // retry técnico, não deixa o pedido órfão nem manda o job pra failed_jobs sem teto.
            $this->retryTecnico($pedido, $fonte, $e->getMessage());

            return;
        }

        // FALHA TÉCNICA (transitória: 615 origem fora do ar, 605 timeout) ≠ "tribunal ainda não
        // emitiu". Não consome tentativa do tribunal e usa o retry CURTO da casa (15s → 30s).
        if ($resp->status === 'retry') {
            $this->retryTecnico($pedido, $fonte, $resp->mensagem);

            return;
        }

        if ($resp->status !== 'sucesso') {
            // Falha determinística (fatal/param): re-tentar dá o mesmo erro e fatura — desiste.
            $this->falhar($pedido, 'Etapa 2 '.$resp->status.($resp->mensagem ? ': '.$resp->mensagem : ''));

            return;
        }

        $pedido->increment('tentativas');            // conferência efetiva ao tribunal
        $pedido->update(['tentativas_tecnicas' => 0]); // sucesso técnico zera o contador transitório

        $map = $fonte->mapearObter($resp->raw['data'][0] ?? []);

        if (empty($map['pronta'])) {
            // Tribunal ainda não emitiu — segue em conferência, com backoff escalonado.
            $pedido->update(['estado' => CertidaoPedido::PROCESSANDO]);
            $this->reagendarOuFalhar($pedido, $fonte, null);

            return;
        }

        $this->finalizar($pedido, $fonte, (array) $map['bloco']);
    }

    /**
     * Reivindicação atômica do pedido: UPDATE condicional que empurra `proxima_verificacao_em` pro
     * futuro (janela de segurança) SÓ se ainda estiver aberto e vencido. Devolve false se não
     * reivindicou (outro worker pegou) — a única defesa contra conferência paga em duplicidade.
     */
    private function reivindicar(CertidaoPedido $pedido): bool
    {
        $afetadas = CertidaoPedido::where('id', $pedido->id)
            ->whereIn('estado', CertidaoPedido::ABERTOS)
            ->where(function ($q) {
                $q->whereNull('proxima_verificacao_em')->orWhere('proxima_verificacao_em', '<=', now());
            })
            ->update(['proxima_verificacao_em' => now()->addMinutes(10)]);

        if ($afetadas === 0) {
            return false;
        }

        $pedido->refresh();

        return true;
    }

    /**
     * Emitiu: arquiva o PDF, registra em `certidoes`, atualiza o card do lote e conclui. Se QUALQUER
     * passo de persistência falhar DEPOIS da emissão paga, o pedido vai pro estado DISPONIVEL com o
     * veredito guardado em `resultado_bloco` — o sweep retoma daqui SEM re-pagar o obter-certidao.
     */
    private function finalizar(CertidaoPedido $pedido, FonteDuasEtapas $fonte, array $bloco): void
    {
        try {
            // Arquiva o PDF (site_receipt expira em ~7d) — mesmo padrão do motor single-call.
            $arquivoPath = $pedido->arquivo_path; // pode já ter sido arquivado numa passada anterior
            if ($arquivoPath === null && ! empty($bloco['comprovante'])) {
                $arq = $this->arquivador->arquivar(
                    $bloco['comprovante'],
                    $pedido->user_id,
                    ComprovanteArquivador::rotuloFonte($fonte->chave(), $pedido->alvo_documento),
                );
                $arquivoPath = $arq['path'] ?? null;
            }
            $bloco['comprovante_arquivo'] = $arquivoPath;

            $this->registro->registrar(
                $fonte->chave(),
                $bloco,
                $pedido->user_id,
                $pedido->alvo_tipo,
                (int) ($pedido->cliente_id ?? $pedido->participante_id),
                $pedido->alvo_documento,
                (int) $pedido->consulta_lote_id,
            );

            // Atualiza o card da fonte no resultado do lote original (o "Em andamento" vira o veredito).
            $this->atualizarResultadoLote($pedido, $fonte->chave(), $bloco);

            $pedido->update([
                'estado' => CertidaoPedido::BAIXADA,
                'status_certidao' => $bloco['status'] ?? null,
                'certidao_codigo' => $bloco['certidao_codigo'] ?? null,
                'arquivo_path' => $arquivoPath,
                'resultado_bloco' => null,
                'erro' => null,
                'proxima_verificacao_em' => null,
                'concluido_em' => now(),
            ]);

            $this->notificarPronta($pedido);
        } catch (\Throwable $e) {
            // Emitiu (chamada paga já feita) mas a persistência falhou: guarda o veredito e marca
            // DISPONIVEL. O sweep retoma o finalizar SEM re-consultar o tribunal.
            Log::error('CertidaoPedido: falha ao finalizar após emissão — retoma sem re-pagar', [
                'pedido' => $pedido->id, 'erro' => $e->getMessage(),
            ]);
            $pedido->update([
                'estado' => CertidaoPedido::DISPONIVEL,
                'resultado_bloco' => $bloco,
                'arquivo_path' => $arquivoPath ?? $pedido->arquivo_path,
                'proxima_verificacao_em' => now()->addMinutes(5),
                'erro' => 'Falha ao concluir: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Tribunal ainda não emitiu: reagenda a próxima conferência no backoff escalonado (o sweep
     * dispara quando `proxima_verificacao_em` vence — não há self-redispatch aqui, senão o
     * unique-lock do job em execução descartaria silenciosamente o novo dispatch).
     */
    private function reagendarOuFalhar(CertidaoPedido $pedido, FonteDuasEtapas $fonte, ?string $erro): void
    {
        if ($pedido->tentativas >= $fonte->maxVerificacoes()) {
            $this->falhar($pedido, $erro ?? 'Tribunal não emitiu a certidão dentro da janela de verificação.');

            return;
        }

        $prox = now()->addMinutes($fonte->intervaloVerificacaoMinutos($pedido->tentativas));
        $pedido->update(['proxima_verificacao_em' => $prox, 'erro' => $erro]);
    }

    /**
     * Falha transitória na conferência: re-tenta rápido (backoff da casa 15s → 30s) sem gastar
     * conferência do tribunal — só empurra `proxima_verificacao_em`; o sweep everyMinute redispara.
     * Esgotado o teto técnico, trata como conferência normal (backoff longo) — assim uma origem
     * persistentemente fora do ar não vira loop curto infinito.
     */
    private function retryTecnico(CertidaoPedido $pedido, FonteDuasEtapas $fonte, ?string $mensagem): void
    {
        $max = (int) config('consultas.pedidos.retry_tecnico_max', 2);
        $cooldown = (int) config('consultas.retry.auto.cooldown_segundos', 15);

        // Incremento no BANCO (expressão SQL), não em PHP: um read-modify-write deixava dois
        // sweeps sobrepostos lerem o mesmo valor, gravarem o mesmo +1 e estourarem o teto — e cada
        // conferência excedente é uma chamada PAGA ao provedor. `increment` já emite
        // `tentativas_tecnicas = tentativas_tecnicas + 1` e sincroniza o atributo no modelo.
        $pedido->increment('tentativas_tecnicas', 1, ['erro' => $mensagem]);
        $tecnicas = (int) $pedido->tentativas_tecnicas;

        if ($tecnicas > $max) {
            // Esgotou o retry rápido: conta como 1 conferência e cai no backoff longo.
            $pedido->increment('tentativas', 1, ['tentativas_tecnicas' => 0, 'estado' => CertidaoPedido::PROCESSANDO]);
            $pedido->refresh();
            $this->reagendarOuFalhar($pedido, $fonte, $mensagem);

            return;
        }

        $prox = now()->addSeconds($cooldown * $tecnicas);
        $pedido->update(['proxima_verificacao_em' => $prox]);
    }

    private function falhar(CertidaoPedido $pedido, string $erro): void
    {
        $pedido->update([
            'estado' => CertidaoPedido::FALHOU,
            'erro' => $erro,
            'proxima_verificacao_em' => null,
            'concluido_em' => now(),
        ]);
    }

    /** Mescla o bloco final no resultado_dados do lote original (a tela do lote passa a mostrá-lo). */
    private function atualizarResultadoLote(CertidaoPedido $pedido, string $chave, array $bloco): void
    {
        if (! $pedido->consulta_lote_id) {
            return;
        }

        $query = ConsultaResultado::where('consulta_lote_id', $pedido->consulta_lote_id);
        $pedido->alvo_tipo === 'cliente'
            ? $query->where('cliente_id', $pedido->cliente_id)
            : $query->where('participante_id', $pedido->participante_id);

        $resultado = $query->first();
        if (! $resultado) {
            return;
        }

        $dados = (array) ($resultado->resultado_dados ?? []);
        $dados[$chave] = $bloco;
        $resultado->resultado_dados = $dados;
        $resultado->save();
    }

    /** E-mail "certidão pronta" — TODO fase 4: Notification temática. Por ora registra no log. */
    private function notificarPronta(CertidaoPedido $pedido): void
    {
        try {
            Log::info('CertidaoPedido pronta', [
                'pedido' => $pedido->id,
                'user' => $pedido->user_id,
                'tipo' => $pedido->tipo,
                'documento' => $pedido->alvo_documento,
                'status' => $pedido->status_certidao,
            ]);
            // TODO fase 4: disparar Notification "certidão pronta" (após commit + try/catch — regra da casa).
        } catch (\Throwable $e) {
            // nunca derruba a finalização
        }
    }
}
