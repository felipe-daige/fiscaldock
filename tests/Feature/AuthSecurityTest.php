<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('login aplica rate limit apos 5 tentativas', function () {
    User::factory()->create([
        'email' => 'vitima@example.com',
        'password' => Hash::make('senhaCorreta123'),
    ]);

    // 5 tentativas falhas permitidas
    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => 'vitima@example.com',
            'password' => 'senhaErrada123',
        ]);
    }

    // 6ª tentativa é bloqueada pelo throttle (429), antes de tocar credenciais
    $response = $this->post('/login', [
        'email' => 'vitima@example.com',
        'password' => 'senhaErrada123',
    ]);

    $response->assertStatus(429);
});

test('login regenera a sessao apos autenticar (anti session-fixation)', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('senhaCorreta123'),
    ]);

    $this->startSession();
    $idAntes = session()->getId();

    $this->post('/login', [
        'email' => 'user@example.com',
        'password' => 'senhaCorreta123',
    ]);

    expect(session()->getId())->not->toBe($idAntes);
    $this->assertAuthenticated();
});

test('health endpoint nao expoe token, php_version nem ambiente', function () {
    $response = $this->getJson('/api/health');

    $response->assertOk();
    $json = $response->json();

    expect($json)->not->toHaveKey('token_prefix');
    expect($json)->not->toHaveKey('token_length');
    expect($json)->not->toHaveKey('raw_length');
    expect($json)->not->toHaveKey('php_version');
    expect($json)->not->toHaveKey('laravel_env');
});

test('signup rejeita senha fraca sem letras+numeros', function () {
    $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/criar-conta', [
        'nome' => 'Joao',
        'sobrenome' => 'Silva',
        'email' => 'joao@example.com',
        'telefone' => '67999990000',
        'senha' => 'abcdefgh',
        'senha_confirmation' => 'abcdefgh',
        'empresa' => 'Empresa X',
        'cargo' => 'Contador',
        'documento' => '11144477735',
        'faturamento' => 'ate-1m',
        'desafio_principal' => 'compliance',
        'terms_aceitos' => true,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('senha');
});

test('signup explica em pt-BR o que falta na senha', function () {
    $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/criar-conta', [
        'nome' => 'Joao',
        'sobrenome' => 'Silva',
        'email' => 'joao-senha@example.com',
        'telefone' => '67999990000',
        'senha' => 'abcdefghij', // só letras, sem número
        'senha_confirmation' => 'abcdefghij',
        'empresa' => 'Empresa X',
        'cargo' => 'Contador',
        'documento' => '11144477735',
        'faturamento' => 'ate-360k',
        'desafio_principal' => 'documentos_espalhados',
        'terms_aceitos' => true,
    ]);

    $response->assertStatus(422);
    expect($response->json('errors.senha.0'))->toContain('número');
});

test('signup salva desafio secundario opcional', function () {
    $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/criar-conta', [
        'nome' => 'Ana',
        'sobrenome' => 'Lima',
        'email' => 'ana@example.com',
        'telefone' => '67911110000',
        'senha' => 'Xk9382mZqp01',
        'senha_confirmation' => 'Xk9382mZqp01',
        'empresa' => 'Empresa Ana',
        'cargo' => 'Contadora',
        'documento' => '11144477735',
        'faturamento' => 'ate-360k',
        'desafio_principal' => 'documentos_espalhados',
        'desafio_secundario' => 'falta_visao',
        'terms_aceitos' => true,
    ]);

    $response->assertStatus(200);
    expect(User::where('email', 'ana@example.com')->value('desafio_secundario'))->toBe('falta_visao');
});

test('signup persiste persona do perfil_conta e usa default empresa', function () {
    $payload = [
        'nome' => 'Ana',
        'sobrenome' => 'Lima',
        'email' => 'advogada@example.com',
        'telefone' => '67911110000',
        'senha' => 'Xk9382mZqp01',
        'senha_confirmation' => 'Xk9382mZqp01',
        'empresa' => 'Lima Advogados',
        'cargo' => 'Advogada',
        'documento' => '11144477735',
        'cpf' => '529.982.247-25', // obrigatório p/ advogado (solicitante das certidões judiciais)
        'faturamento' => 'ate-360k',
        'desafio_principal' => 'documentos_espalhados',
        'terms_aceitos' => true,
        'perfil_conta' => 'advogado',
    ];

    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson('/criar-conta', $payload)
        ->assertStatus(200);
    $advogada = User::where('email', 'advogada@example.com')->first();
    expect($advogada->persona)->toBe('advogado')
        ->and($advogada->isAdvogado())->toBeTrue()
        ->and($advogada->cpf)->toBe('52998224725'); // persistido só com dígitos

    // Sem perfil_conta (form antigo/legado) cai no default 'empresa'.
    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson('/criar-conta', array_merge($payload, [
            'email' => 'legado@example.com',
            'telefone' => '67911110001',
            'documento' => '52998224725',
            'perfil_conta' => null,
        ]))
        ->assertStatus(200);
    expect(User::where('email', 'legado@example.com')->value('persona'))->toBe('empresa');
});

test('signup exige cpf valido para advogado (solicitante das certidoes)', function () {
    $base = [
        'nome' => 'Ana', 'sobrenome' => 'Lima', 'telefone' => '67911110000',
        'senha' => 'Xk9382mZqp01', 'senha_confirmation' => 'Xk9382mZqp01',
        'empresa' => 'Lima Advogados', 'cargo' => 'Advogada', 'documento' => '11144477735',
        'faturamento' => 'ate-360k', 'desafio_principal' => 'documentos_espalhados',
        'terms_aceitos' => true, 'perfil_conta' => 'advogado',
    ];

    // Advogado sem CPF → 422 no campo cpf.
    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson('/criar-conta', array_merge($base, ['email' => 'semcpf@example.com']))
        ->assertStatus(422)->assertJsonValidationErrors(['cpf']);

    // CPF com DV inválido → 422 (tribunal/InfoSimples rejeita).
    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson('/criar-conta', array_merge($base, ['email' => 'cpfruim@example.com', 'cpf' => '111.444.777-00']))
        ->assertStatus(422)->assertJsonValidationErrors(['cpf']);

    // Empresa/contador sem CPF → OK (opcional, sem fricção no funil fiscal).
    $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson('/criar-conta', array_merge($base, [
            'email' => 'contador@example.com', 'documento' => '97551165000193', 'perfil_conta' => 'contador',
        ]))
        ->assertStatus(200);
    expect(User::where('email', 'contador@example.com')->value('cpf'))->toBeNull();
});

test('signup rejeita persona desconhecida', function () {
    $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/criar-conta', [
        'nome' => 'Ana',
        'sobrenome' => 'Lima',
        'email' => 'x@example.com',
        'telefone' => '67911110000',
        'senha' => 'Xk9382mZqp01',
        'senha_confirmation' => 'Xk9382mZqp01',
        'empresa' => 'Empresa X',
        'cargo' => 'Outro',
        'documento' => '11144477735',
        'faturamento' => 'ate-360k',
        'desafio_principal' => 'documentos_espalhados',
        'terms_aceitos' => true,
        'perfil_conta' => 'hacker',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['perfil_conta']);
});

test('signup rejeita desafio secundario igual ao principal', function () {
    $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/criar-conta', [
        'nome' => 'Ana',
        'sobrenome' => 'Lima',
        'email' => 'ana2@example.com',
        'telefone' => '67911110001',
        'senha' => 'Xk9382mZqp01',
        'senha_confirmation' => 'Xk9382mZqp01',
        'empresa' => 'Empresa Ana',
        'cargo' => 'Contadora',
        'documento' => '11144477735',
        'faturamento' => 'ate-360k',
        'desafio_principal' => 'documentos_espalhados',
        'desafio_secundario' => 'documentos_espalhados',
        'terms_aceitos' => true,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('desafio_secundario');
});

test('signup permite documento que ja e cliente de outro usuario (unique por usuario)', function () {
    // Cenário do bug em prod: o CNPJ da empresa do novo usuário já existe na base
    // como cliente de OUTRA conta. Com UNIQUE global em clientes.documento isso dava
    // 500 (clientes_documento_unique). O correto é UNIQUE(user_id, documento).
    $outro = User::factory()->create([
        'email' => 'dono@example.com',
        'telefone' => '67900000000',
        'cnpj' => '00000000000191',
        'empresa' => 'Contabilidade do Outro',
    ]);

    \App\Models\Cliente::create([
        'user_id' => $outro->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '97551165000193',
        'nome' => 'Hidratop Comercio',
        'razao_social' => 'Hidratop Comercio',
        'is_empresa_propria' => false,
    ]);

    $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/criar-conta', [
        'nome' => 'Marcio',
        'sobrenome' => 'Oliveira',
        'email' => 'marcio@example.com',
        'telefone' => '67999571609',
        'senha' => 'Xk9382mZqp01',
        'senha_confirmation' => 'Xk9382mZqp01',
        'empresa' => 'Hidratop Comercio',
        'cargo' => 'Contador',
        'documento' => '97.551.165/0001-93',
        'faturamento' => 'ate-360k',
        'desafio_principal' => 'documentos_espalhados',
        'terms_aceitos' => true,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    // Os dois clientes coexistem — um por usuário.
    expect(\App\Models\Cliente::where('documento', '97551165000193')->count())->toBe(2);
});

test('signup retorna creditos e validade do config (nao hardcoded)', function () {
    $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/criar-conta', [
        'nome' => 'Bia',
        'sobrenome' => 'Costa',
        'email' => 'bia@example.com',
        'telefone' => '67911119999',
        'senha' => 'Xk9382mZqp01',
        'senha_confirmation' => 'Xk9382mZqp01',
        'empresa' => 'Empresa Bia',
        'cargo' => 'Contadora',
        'documento' => '11144477735',
        'faturamento' => 'ate-360k',
        'desafio_principal' => 'documentos_espalhados',
        'terms_aceitos' => true,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'saldo_reais' => (float) config('trial.saldo_reais'),
        'validade_dias' => (int) config('trial.validade_dias'),
    ]);
});

test('confirmar-termos do onboarding exige auth e grava consent_log', function () {
    // Sem autenticação → bloqueado.
    $this->postJson('/app/onboarding/confirmar-termos')->assertStatus(401);

    $user = User::factory()->create([
        'terms_version' => config('legal.terms_version'),
        'privacy_version' => config('legal.privacy_version'),
    ]);

    $this->actingAs($user)
        ->postJson('/app/onboarding/confirmar-termos')
        ->assertStatus(200)
        ->assertJson(['success' => true]);

    expect(\App\Models\ConsentLog::where('user_id', $user->id)->where('tipo', 'termos')->where('acao', 'aceite')->exists())->toBeTrue();
    expect(\App\Models\ConsentLog::where('user_id', $user->id)->where('tipo', 'privacidade')->where('acao', 'aceite')->exists())->toBeTrue();
});

test('signup com e-mail novo nao trava por telefone, CNPJ ou nome ja existentes', function () {
    // Bug reportado: ao testar com um e-mail novo mantendo o mesmo telefone/CNPJ/nome
    // de um usuário existente, o cadastro travava. Só o e-mail repetido deve bloquear.
    User::factory()->create([
        'email' => 'existente@example.com',
        'name' => 'Felipe',
        'sobrenome' => 'Daige',
        'empresa' => 'Empresa Repetida',
        'telefone' => '67999844366',
        'cnpj' => '63112970000107',
    ]);

    $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/criar-conta', [
        'nome' => 'Felipe',
        'sobrenome' => 'Daige',
        'email' => 'novo@example.com', // e-mail NOVO
        'telefone' => '(67) 99984-4366', // mesmo telefone
        'senha' => 'Xk9382mZqp01',
        'senha_confirmation' => 'Xk9382mZqp01',
        'empresa' => 'Empresa Repetida', // mesma empresa
        'cargo' => 'Contador',
        'documento' => '63.112.970/0001-07', // mesmo CNPJ
        'faturamento' => 'ate-360k',
        'desafio_principal' => 'documentos_espalhados',
        'terms_aceitos' => true,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    expect(User::where('email', 'novo@example.com')->exists())->toBeTrue();
});

test('conflito de signup usa mensagem generica (anti-enumeracao)', function () {
    User::factory()->create([
        'email' => 'existe@example.com',
        'telefone' => '67988887777',
        'cnpj' => '11222333000181',
    ]);

    $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->postJson('/criar-conta', [
        'nome' => 'Maria',
        'sobrenome' => 'Souza',
        'email' => 'existe@example.com',
        'telefone' => '67911112222',
        'senha' => 'Xk9382mZqp01',
        'senha_confirmation' => 'Xk9382mZqp01',
        'empresa' => 'Outra Empresa',
        'cargo' => 'Diretora',
        'documento' => '11144477735',
        'faturamento' => 'ate-1m',
        'desafio_principal' => 'compliance',
        'terms_aceitos' => true,
    ]);

    $response->assertStatus(422);
    $mensagem = $response->json('errors.email.0');

    // Não pode revelar QUAL campo (e-mail/telefone/CPF) colidiu.
    expect($mensagem)->not->toContain('e-mail');
    expect($mensagem)->not->toContain('telefone');
    expect($mensagem)->not->toContain('CPF');
});

test('csrf mismatch em AJAX retorna 419 JSON com token novo (auto-recuperacao)', function () {
    Route::middleware('web')->post('/__test_csrf_mismatch', function () {
        throw new TokenMismatchException('CSRF token mismatch.');
    });

    $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->postJson('/__test_csrf_mismatch');

    $response->assertStatus(419);
    $response->assertJson([
        'success' => false,
        'support' => true,
        'support_label' => 'Falar no WhatsApp',
    ]);
    expect($response->json('message'))->toContain('fale com o suporte pelo WhatsApp');

    $token = $response->json('csrf_token');
    expect($token)->toBeString()->not->toBeEmpty();
});

test('csrf mismatch em requisicao web normal redireciona para login', function () {
    Route::middleware('web')->post('/__test_csrf_mismatch_web', function () {
        throw new TokenMismatchException('CSRF token mismatch.');
    });

    $response = $this->post('/__test_csrf_mismatch_web');

    $response->assertStatus(302);
    $response->assertRedirect('/login');
});

test('respostas trazem cabecalhos de seguranca', function () {
    $response = $this->get('/login');

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});
