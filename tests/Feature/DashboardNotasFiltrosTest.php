<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('exibe as competências dos filtros com os meses em português', function () {
    $user = User::factory()->create();
    $clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $user->id,
        'razao_social' => 'Empresa Teste',
        'documento' => '00000000000100',
        'is_empresa_propria' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $importacao = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteId,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'efd.txt',
        'status' => 'concluido',
        'iniciado_em' => now(),
        'concluido_em' => now(),
    ]);

    foreach (['2024-01-15', '2024-03-15'] as $indice => $dataEmissao) {
        EfdNota::create([
            'user_id' => $user->id,
            'cliente_id' => $clienteId,
            'importacao_id' => $importacao->id,
            'chave_acesso' => str_pad((string) ($indice + 1), 44, '0', STR_PAD_LEFT),
            'modelo' => '55',
            'numero' => $indice + 1,
            'serie' => '1',
            'data_emissao' => $dataEmissao,
            'tipo_operacao' => 'saida',
            'valor_total' => 100,
            'valor_desconto' => 0,
            'origem_arquivo' => 'fiscal',
            'cancelada' => false,
        ]);
    }

    actingAs($user)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get('/app/notas/dashboard')
        ->assertOk()
        ->assertSee('Competência inicial')
        ->assertSee('Competência final')
        ->assertSee('Janeiro de 2024')
        ->assertSee('Fevereiro de 2024')
        ->assertSee('Março de 2024')
        ->assertSee('value="2024-01"', false)
        ->assertSee('value="2024-03"', false)
        ->assertDontSee('type="month"', false);
});
