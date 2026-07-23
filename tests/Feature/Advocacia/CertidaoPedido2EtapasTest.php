<?php

use App\Jobs\VerificarCertidaoPedidoJob;
use App\Models\CertidaoPedido;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\User;
use App\Services\Consultas\CertidaoPedidoService;
use App\Services\Consultas\Contracts\FonteDuasEtapas;
use App\Services\Consultas\FonteRegistry;
use App\Services\Consultas\Fontes\Advocacia\TjmsPedidoFonte;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

// Suíte sem RefreshDatabase (estado compartilhado): isola as escritas deste arquivo numa transação
// revertida ao fim de cada teste, pra os participantes/clientes/certidões/pedidos criados aqui não
// vazarem e colidirem com outros arquivos.
uses(Illuminate\Foundation\Testing\DatabaseTransactions::class);

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
    config()->set('advocacia.email_solicitante', 'sistema@fiscaldock.com.br');
});

function novoParticipante(int $userId): int
{
    return \DB::table('participantes')->insertGetId([
        'user_id' => $userId, 'documento' => '97551165000193', 'razao_social' => 'HIDRATOP',
        'uf' => 'MS', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

function novoCliente(int $userId): int
{
    return \DB::table('clientes')->insertGetId([
        'user_id' => $userId, 'documento' => '97551165000193', 'nome' => 'HIDRATOP',
        'razao_social' => 'HIDRATOP', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

test('TjmsPedidoFonte: contrato de 2 etapas (params, correlacao, obter)', function () {
    $fonte = new TjmsPedidoFonte;
    $alvo = ['cnpj' => '97.551.165/0001-93', 'uf' => 'MS', 'razao_social' => 'HIDRATOP LTDA', 'municipio' => 'DOURADOS'];

    expect($fonte)->toBeInstanceOf(FonteDuasEtapas::class)
        ->and($fonte->chave())->toBe('certidao_tjms')
        ->and($fonte->aplicavelPara($alvo))->toBeTrue()
        // fora de MS → indisponível sem cobrar
        ->and($fonte->aplicavelPara(['uf' => 'SP', 'razao_social' => 'X', 'municipio' => 'SP']))->toBeFalse();

    // Etapa 1: cnpj + nome_razao_social + comarca (Title-case do município) + modelo + email.
    // Grafia validada por smoke real (pedido 10559945): comarca "Dourados" + modelo "WEB - Ação Cível".
    expect($fonte->params($alvo))->toBe([
        'cnpj' => '97551165000193',
        'nome_razao_social' => 'HIDRATOP LTDA',
        'comarca' => 'Dourados',
        'modelo' => 'WEB - Ação Cível',
        'email' => 'sistema@fiscaldock.com.br',
    ]);

    // Etapa 1 bem-sucedida = pedido pendente (Em andamento).
    $bloco = $fonte->normalizar(['data' => [['numero_pedido' => '12345', 'data_pedido' => '2026-07-22']]], 'sucesso');
    expect($bloco['certidao_tjms']['status'])->toBe('Em andamento');

    // Correlação extraída da etapa 1 → params da etapa 2.
    expect($fonte->extrairCorrelacao(['numero_pedido' => '12345', 'data_pedido' => '2026-07-22']))
        ->toBe(['numero_pedido' => '12345', 'data_pedido' => '2026-07-22'])
        ->and($fonte->extrairCorrelacao(['numero_pedido' => '']))->toBe([]); // etapa 1 incompleta

    // Etapa 2 exige data_pedido em ISO; a etapa 1 devolve BR (dd/mm/aaaa) → converte.
    // Mandar BR dá 607 com errors[] vazio (validado no smoke do pedido 10559945).
    expect($fonte->paramsObter($alvo, ['numero_pedido' => '12345', 'data_pedido' => '22/07/2026']))
        ->toBe(['cnpj' => '97551165000193', 'numero_pedido' => '12345', 'data_pedido' => '2026-07-22'])
        ->and($fonte->paramsObter($alvo, ['numero_pedido' => '1', 'data_pedido' => '2026-07-22'])['data_pedido'])
        ->toBe('2026-07-22'); // já ISO passa direto

    // Etapa 2: ainda não emitida × emitida negativa.
    expect($fonte->mapearObter(['conseguiu_emitir_pdf' => false])['pronta'])->toBeFalse();
    $ok = $fonte->mapearObter(['conseguiu_emitir_pdf' => true, 'nada_consta' => true, 'titulo' => 'CERTIDÃO', 'site_receipt' => 'http://x/cert.pdf']);
    expect($ok['pronta'])->toBeTrue()
        ->and($ok['bloco']['status'])->toBe('Negativa')
        ->and($ok['bloco']['comprovante'])->toBe('http://x/cert.pdf');
});

test('certidao_tjms esta registrada como fonte de 2 etapas', function () {
    $fonte = app(FonteRegistry::class)->get('certidao_tjms');
    expect($fonte)->toBeInstanceOf(FonteDuasEtapas::class);
});

test('criar(): etapa 1 vira CertidaoPedido solicitada; o sweep (nao criar) despacha o follow-up', function () {
    Queue::fake();
    $user = User::factory()->create();
    $pid = novoParticipante($user->id);
    $lote = ConsultaLote::create(['user_id' => $user->id, 'status' => 'concluido', 'total_participantes' => 1]);
    $fonte = new TjmsPedidoFonte;
    $alvo = ['cnpj' => '97551165000193', 'uf' => 'MS', 'razao_social' => 'HIDRATOP', 'municipio' => 'DOURADOS'];

    $pedido = app(CertidaoPedidoService::class)->criar(
        $fonte, $alvo, ['numero_pedido' => '999', 'data_pedido' => '2026-07-22'],
        $user->id, 'participante', $pid, '97551165000193', $lote->id,
    );

    expect($pedido)->not->toBeNull()
        ->and($pedido->estado)->toBe(CertidaoPedido::SOLICITADA)
        ->and($pedido->correlacao)->toBe(['numero_pedido' => '999', 'data_pedido' => '2026-07-22'])
        ->and($pedido->slug_obter)->toBe('tribunal/tjms/obter-certidao')
        ->and($pedido->proxima_verificacao_em)->not->toBeNull();

    // criar() NÃO despacha job (self-dispatch sob unique-lock era descartado); o sweep é o motor.
    Queue::assertNotPushed(VerificarCertidaoPedidoJob::class);

    // Etapa 1 sem correlação → não cria pedido (o motor trata como falha).
    expect(app(CertidaoPedidoService::class)->criar($fonte, $alvo, [], $user->id, 'participante', $pid, '97551165000193', $lote->id))
        ->toBeNull();
});

test('verificar(): etapa 2 emitida finaliza (certidoes + PDF + card do lote + estado baixada)', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $cid = novoCliente($user->id);
    $lote = ConsultaLote::create(['user_id' => $user->id, 'status' => 'concluido', 'total_participantes' => 1]);
    $resultado = ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'participante_id' => null, 'cliente_id' => $cid,
        'status' => 'sucesso', 'resultado_dados' => ['certidao_tjms' => ['status' => 'Em andamento']],
    ]);
    $pedido = CertidaoPedido::create([
        'user_id' => $user->id, 'cliente_id' => $cid, 'alvo_tipo' => 'cliente',
        'alvo_documento' => '97551165000193', 'tipo' => 'certidao_tjms',
        'slug_obter' => 'tribunal/tjms/obter-certidao', 'estado' => CertidaoPedido::SOLICITADA,
        'correlacao' => ['numero_pedido' => '999', 'data_pedido' => '2026-07-22'],
        'consulta_lote_id' => $lote->id, 'solicitado_em' => now(),
    ]);

    Http::fake(function ($request) {
        if (str_contains($request->url(), 'tribunal/tjms/obter-certidao')) {
            return Http::response(['code' => 200, 'code_message' => 'ok', 'errors' => [], 'data' => [[
                'conseguiu_emitir_pdf' => true, 'nada_consta' => true, 'titulo' => 'CERTIDÃO CÍVEL',
                'numero_certidao' => 'TJMS-2026-1', 'site_receipt' => 'https://x/cert.pdf',
            ]]], 200);
        }

        return Http::response('%PDF-1.4 fake', 200, ['Content-Type' => 'application/pdf']); // download do PDF

    });

    app(CertidaoPedidoService::class)->verificar($pedido->fresh());

    $pedido->refresh();
    expect($pedido->estado)->toBe(CertidaoPedido::BAIXADA)
        ->and($pedido->status_certidao)->toBe('Negativa')
        ->and($pedido->arquivo_path)->not->toBeNull()
        ->and($pedido->concluido_em)->not->toBeNull();

    // Gravou em `certidoes`.
    $cert = \DB::table('certidoes')->where('user_id', $user->id)->where('tipo', 'certidao_tjms')->first();
    expect($cert)->not->toBeNull()->and($cert->status)->toBe('Negativa');

    // Atualizou o card do lote (Em andamento → Negativa).
    expect($resultado->fresh()->resultado_dados['certidao_tjms']['status'])->toBe('Negativa');
});

test('verificar(): ainda nao emitida reagenda; estourar a janela marca falhou', function () {
    Queue::fake();
    $user = User::factory()->create();
    Http::fake(fn () => Http::response(['code' => 200, 'code_message' => 'ok', 'errors' => [], 'data' => [[
        'conseguiu_emitir_pdf' => false,
    ]]], 200));

    $pid = novoParticipante($user->id);
    $pedido = CertidaoPedido::create([
        'user_id' => $user->id, 'alvo_tipo' => 'participante', 'participante_id' => $pid,
        'alvo_documento' => '97551165000193', 'tipo' => 'certidao_tjms',
        'slug_obter' => 'tribunal/tjms/obter-certidao', 'estado' => CertidaoPedido::SOLICITADA,
        'correlacao' => ['numero_pedido' => '1', 'data_pedido' => '2026-07-22'], 'tentativas' => 0,
    ]);

    app(CertidaoPedidoService::class)->verificar($pedido->fresh());
    expect($pedido->fresh()->estado)->toBe(CertidaoPedido::PROCESSANDO)
        ->and($pedido->fresh()->proxima_verificacao_em)->not->toBeNull();

    // Esgota a janela → falhou. Reset de proxima pro passado simula o vencimento (o claim atômico
    // exige proxima <= now() pra reivindicar; em prod o sweep só re-dispara após o backoff).
    $pedido->update(['tentativas' => 5, 'proxima_verificacao_em' => now()->subMinute()]);
    app(CertidaoPedidoService::class)->verificar($pedido->fresh());
    expect($pedido->fresh()->estado)->toBe(CertidaoPedido::FALHOU)
        ->and($pedido->fresh()->concluido_em)->not->toBeNull();
});

test('claim atomico: pedido com proxima_verificacao no FUTURO nao e reivindicado (sem chamada paga)', function () {
    $user = User::factory()->create();
    $pid = novoParticipante($user->id);
    Http::fake(); // qualquer chamada externa aqui seria um bug

    $pedido = CertidaoPedido::create([
        'user_id' => $user->id, 'alvo_tipo' => 'participante', 'participante_id' => $pid,
        'alvo_documento' => '97551165000193', 'tipo' => 'certidao_tjms',
        'slug_obter' => 'tribunal/tjms/obter-certidao', 'estado' => CertidaoPedido::SOLICITADA,
        'correlacao' => ['numero_pedido' => '1', 'data_pedido' => '22/07/2026'],
        'proxima_verificacao_em' => now()->addHours(4), // ainda não venceu
    ]);

    app(CertidaoPedidoService::class)->verificar($pedido->fresh());

    Http::assertNothingSent(); // não reivindicou → não consultou → não pagou
    expect($pedido->fresh()->estado)->toBe(CertidaoPedido::SOLICITADA);
});

test('falha TECNICA (615) nao consome conferencia do tribunal e usa retry curto 15s/30s', function () {
    Queue::fake();
    $user = User::factory()->create();
    $pid = novoParticipante($user->id);
    // 615 = origem fora do ar → classe `retry` (transitória), não é "tribunal ainda não emitiu".
    Http::fake(fn () => Http::response(['code' => 615, 'code_message' => 'origem indisponivel', 'errors' => [], 'data' => []], 200));

    $pedido = CertidaoPedido::create([
        'user_id' => $user->id, 'alvo_tipo' => 'participante', 'participante_id' => $pid,
        'alvo_documento' => '97551165000193', 'tipo' => 'certidao_tjms',
        'slug_obter' => 'tribunal/tjms/obter-certidao', 'estado' => CertidaoPedido::SOLICITADA,
        'correlacao' => ['numero_pedido' => '1', 'data_pedido' => '22/07/2026'],
    ]);

    // helper: força o pedido a "vencido" antes de cada verificar (em prod o sweep espera o backoff).
    $vencer = fn () => $pedido->update(['proxima_verificacao_em' => now()->subSecond()]);

    // 1ª falha técnica: tentativas do TRIBUNAL segue 0; técnica vai a 1; reagenda curto (~15s).
    app(CertidaoPedidoService::class)->verificar($pedido->fresh());
    $p = $pedido->fresh();
    expect($p->tentativas)->toBe(0)
        ->and($p->tentativas_tecnicas)->toBe(1)
        ->and($p->proxima_verificacao_em->diffInSeconds(now()))->toBeLessThanOrEqual(20);

    // 2ª falha técnica: técnica 2, ~30s, tribunal ainda 0.
    $vencer();
    app(CertidaoPedidoService::class)->verificar($pedido->fresh());
    $p = $pedido->fresh();
    expect($p->tentativas)->toBe(0)->and($p->tentativas_tecnicas)->toBe(2);

    // 3ª estoura o teto técnico (2) → conta como conferência do tribunal e zera a técnica.
    $vencer();
    app(CertidaoPedidoService::class)->verificar($pedido->fresh());
    $p = $pedido->fresh();
    expect($p->tentativas)->toBe(1)
        ->and($p->tentativas_tecnicas)->toBe(0)
        ->and($p->estado)->toBe(CertidaoPedido::PROCESSANDO);
});

test('backoff da conferencia é ESCALONADO (1h, 4h, 12h, 24h) — cada obter é chamada paga', function () {
    $fonte = new TjmsPedidoFonte;
    expect($fonte->prazoInicialMinutos())->toBe(60)
        ->and($fonte->intervaloVerificacaoMinutos(1))->toBe(60)
        ->and($fonte->intervaloVerificacaoMinutos(2))->toBe(240)
        ->and($fonte->intervaloVerificacaoMinutos(3))->toBe(720)
        ->and($fonte->intervaloVerificacaoMinutos(4))->toBe(1440)
        ->and($fonte->intervaloVerificacaoMinutos(9))->toBe(1440) // satura no último degrau
        ->and($fonte->maxVerificacoes())->toBe(5);
});

test('estado DISPONIVEL: falha na persistencia apos emissao retoma SEM re-consultar (nao re-paga)', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $pid = novoParticipante($user->id);

    // Pedido já em DISPONIVEL com o veredito guardado (etapa 2 emitiu mas a persistência falhou).
    $pedido = CertidaoPedido::create([
        'user_id' => $user->id, 'alvo_tipo' => 'participante', 'participante_id' => $pid,
        'alvo_documento' => '97551165000193', 'tipo' => 'certidao_tjms',
        'slug_obter' => 'tribunal/tjms/obter-certidao', 'estado' => CertidaoPedido::DISPONIVEL,
        'correlacao' => ['numero_pedido' => '1', 'data_pedido' => '22/07/2026'],
        'resultado_bloco' => ['status' => 'Negativa', 'certidao_codigo' => 'TJMS-9', 'comprovante' => 'https://x/c.pdf'],
        'proxima_verificacao_em' => now()->subMinute(),
    ]);

    // Só o DOWNLOAD do PDF pode sair; o obter-certidao (pago) NÃO pode ser chamado de novo.
    Http::fake(function ($request) {
        expect($request->url())->not->toContain('tribunal/tjms/obter-certidao');

        return Http::response('%PDF-1.4 fake', 200, ['Content-Type' => 'application/pdf']);
    });

    app(CertidaoPedidoService::class)->verificar($pedido->fresh());

    $p = $pedido->fresh();
    expect($p->estado)->toBe(CertidaoPedido::BAIXADA)
        ->and($p->status_certidao)->toBe('Negativa')
        ->and($p->resultado_bloco)->toBeNull(); // limpo ao concluir
});

test('sweep certidoes:verificar-pedidos reenfileira os vencidos e ignora os concluidos', function () {
    Queue::fake();
    CertidaoPedido::query()->delete(); // suíte sem RefreshDatabase: parte de estado limpo
    $user = User::factory()->create();
    $pid = novoParticipante($user->id);
    $base = ['user_id' => $user->id, 'alvo_tipo' => 'participante', 'participante_id' => $pid,
        'alvo_documento' => '97551165000193', 'tipo' => 'certidao_tjms', 'slug_obter' => 'tribunal/tjms/obter-certidao'];

    CertidaoPedido::create($base + ['estado' => CertidaoPedido::SOLICITADA, 'proxima_verificacao_em' => now()->subHour()]); // vencido
    CertidaoPedido::create($base + ['estado' => CertidaoPedido::PROCESSANDO, 'proxima_verificacao_em' => now()->addDay()]); // futuro
    CertidaoPedido::create($base + ['estado' => CertidaoPedido::BAIXADA, 'proxima_verificacao_em' => now()->subHour()]);   // concluído

    $this->artisan('certidoes:verificar-pedidos')->assertSuccessful();

    Queue::assertPushed(VerificarCertidaoPedidoJob::class, 1); // só o vencido em aberto
});
