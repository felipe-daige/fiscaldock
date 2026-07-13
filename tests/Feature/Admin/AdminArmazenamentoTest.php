<?php

use App\Models\AccountSubscription;
use App\Models\EfdImportacao;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Services\Admin\AdminArmazenamentoService;
use App\Services\Arquivos\ArquivoUsuarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    (new Database\Seeders\SubscriptionPlanSeeder)->run();
});

it('protege a tela e entrega o partial SPA somente para admin', function () {
    $this->get('/app/admin/armazenamento')->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/app/admin/armazenamento')
        ->assertForbidden();

    $admin = User::factory()->create(['is_admin' => true]);
    $resposta = $this->actingAs($admin)
        ->get(route('app.admin.armazenamento.index'));
    $resposta
        ->assertOk()
        ->assertSee('Admin — Armazenamento')
        ->assertSee('Capacidade física da VPS')
        ->assertSee('usados de')
        ->assertSee('Uso por conta');
    expect($resposta->getContent())->toMatch('/class="[^"]*whitespace-nowrap[^"]*"[^>]*>Dentro da quota<\/span>/');

    $this->actingAs($admin)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get(route('app.admin.armazenamento.index'))
        ->assertOk()
        ->assertSee('Admin — Armazenamento')
        ->assertDontSee('<!DOCTYPE html>', false);
});

it('soma as origens por conta e mantém paridade com meus arquivos', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $alvo = User::factory()->create([
        'name' => 'Conta Alvo',
        'email' => 'armazenamento@example.com',
        'empresa' => 'Empresa Medida',
    ]);
    $outro = User::factory()->create(['name' => 'Outra Conta']);

    Storage::disk('local')->put("arquivos/{$alvo->id}/2026/07/01J/upload.pdf", str_repeat('U', 10));
    Storage::disk('local')->put("comprovantes/{$alvo->id}/2026/07/01J.pdf", str_repeat('C', 20));
    Storage::disk('local')->put("arquivos/{$outro->id}/2026/07/01K/outro.pdf", str_repeat('O', 500));
    Storage::disk('local')->put('arquivos/999999/2026/07/01Z/orfao.pdf', str_repeat('X', 7));

    EfdImportacao::create([
        'user_id' => $alvo->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'efd.txt',
        'arquivo_base64' => base64_encode(str_repeat('E', 30)),
        'status' => 'concluido',
        'iniciado_em' => now(),
    ]);
    XmlImportacao::create([
        'user_id' => $alvo->id,
        'tipo_documento' => 'NFE',
        'filename' => 'xml.zip',
        'modo_envio' => 'zip',
        'total_arquivos' => 1,
        'total_xmls' => 1,
        'tamanho_total_bytes' => 40,
        'status' => 'concluido',
        'iniciado_em' => now(),
    ]);

    $painel = app(AdminArmazenamentoService::class)->painel(['q' => 'Empresa Medida']);
    $conta = $painel['contas']->sole();
    $resumoUsuario = app(ArquivoUsuarioService::class)->resumo($alvo);

    expect($conta['usuario']->id)->toBe($alvo->id)
        ->and($conta['uploads_bytes'])->toBe(10)
        ->and($conta['comprovantes_bytes'])->toBe(20)
        ->and($conta['importacoes_bytes'])->toBe(70)
        ->and($conta['usado_bytes'])->toBe(100)
        ->and($conta['usado_bytes'])->toBe($resumoUsuario['usado_bytes'])
        ->and($conta['uploads_total'])->toBe(1)
        ->and($conta['comprovantes_total'])->toBe(1)
        ->and($conta['importacoes_total'])->toBe(2)
        ->and($painel['resumo']['nao_atribuido_bytes'])->toBe(7);

    $this->actingAs($admin)
        ->get(route('app.admin.armazenamento.index', ['q' => 'armazenamento@example.com']))
        ->assertOk()
        ->assertSee('Conta Alvo')
        ->assertDontSee('Outra Conta');
});

it('ordena por uso e percentual sem confundir contas ilimitadas', function () {
    $free = SubscriptionPlan::where('codigo', 'free')->firstOrFail();
    $free->capabilities = array_merge($free->capabilities ?? [], ['armazenamento_mb' => 1]);
    $free->save();

    $pequena = User::factory()->create(['name' => 'Pequena']);
    $grande = User::factory()->create(['name' => 'Grande']);
    $ilimitada = User::factory()->create(['name' => 'Ilimitada']);
    $planoIlimitado = SubscriptionPlan::where('codigo', 'profissional')->firstOrFail();
    $planoIlimitado->capabilities = array_merge($planoIlimitado->capabilities ?? [], ['armazenamento_mb' => null]);
    $planoIlimitado->save();
    AccountSubscription::create([
        'user_id' => $ilimitada->id,
        'subscription_plan_id' => $planoIlimitado->id,
        'status' => AccountSubscription::STATUS_ATIVA,
        'ciclo' => 'mensal',
    ]);

    Storage::disk('local')->put("arquivos/{$pequena->id}/a.txt", str_repeat('A', 100));
    Storage::disk('local')->put("arquivos/{$grande->id}/b.txt", str_repeat('B', 900));
    Storage::disk('local')->put("arquivos/{$ilimitada->id}/c.txt", str_repeat('C', 1200));

    $service = app(AdminArmazenamentoService::class);
    $porUso = $service->painel(['ordenar' => 'uso_desc'])['contas']->pluck('usuario.name')->all();
    $porPercentual = $service->painel(['ordenar' => 'percentual_desc'])['contas']->pluck('usuario.name')->all();

    expect($porUso[0])->toBe('Ilimitada')
        ->and($porPercentual[0])->toBe('Grande');

    $contaIlimitada = $service->painel(['q' => 'Ilimitada'])['contas']->sole();
    expect($contaIlimitada['quota_bytes'])->toBeNull()
        ->and($contaIlimitada['percentual'])->toBeNull()
        ->and($contaIlimitada['status_label'])->toBe('Ilimitado');
});

it('sinaliza conta que ultrapassou a quota', function () {
    $free = SubscriptionPlan::where('codigo', 'free')->firstOrFail();
    $free->capabilities = array_merge($free->capabilities ?? [], ['armazenamento_mb' => 0]);
    $free->save();
    $user = User::factory()->create(['name' => 'Sem Espaço']);
    Storage::disk('local')->put("arquivos/{$user->id}/arquivo.txt", '1');

    $conta = app(AdminArmazenamentoService::class)->painel(['q' => 'Sem Espaço'])['contas']->sole();

    expect($conta['usado_bytes'])->toBe(1)
        ->and($conta['quota_bytes'])->toBe(0)
        ->and($conta['percentual'])->toBe(100.0)
        ->and($conta['status'])->toBe('limite')
        ->and($conta['status_label'])->toBe('Acima da quota');
});

it('classifica as bordas do alerta de disco e degrada caminho inválido', function () {
    config()->set('arquivos.disco.atencao_percentual', 70);
    config()->set('arquivos.disco.critico_percentual', 85);
    $service = app(AdminArmazenamentoService::class);

    expect($service->classificarPercentual(69.9)['status'])->toBe('saudavel')
        ->and($service->classificarPercentual(70)['status'])->toBe('atencao')
        ->and($service->classificarPercentual(84.9)['status'])->toBe('atencao')
        ->and($service->classificarPercentual(85)['status'])->toBe('critico');

    $disco = $service->medirDisco('/diretorio/fiscaldock/que-nao-existe');
    expect($disco['disponivel'])->toBeFalse()
        ->and($disco['status'])->toBe('indisponivel')
        ->and($disco['percentual'])->toBeNull();
});
