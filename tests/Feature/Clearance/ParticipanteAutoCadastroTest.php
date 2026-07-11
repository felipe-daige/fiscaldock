<?php

use App\Models\Cliente;
use App\Models\Participante;
use App\Models\User;
use App\Services\Clearance\ParticipanteAutoCadastroService;
use App\Services\Clearance\Sefaz\DocumentoSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function pacSnapshot(string $tipo, array $colunas): DocumentoSnapshot
{
    return new DocumentoSnapshot(
        tipoDocumento: $tipo,
        chaveAcesso: '50240197551165000193550010000248001000214739',
        status: 'AUTORIZADA',
        colunas: $colunas,
        payload: [],
        persistivel: true,
        estornavel: false,
        billable: true,
    );
}

it('cria participantes PJ para emitente e destinatário com CNPJ novo', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191', 'razao_social' => 'Propria',
    ]);

    app(ParticipanteAutoCadastroService::class)->criarDesdeSnapshot(pacSnapshot('NFE', [
        'emit_cnpj' => '97551165000193', 'emit_nome' => 'HIDRATOP COMERCIO',
        'emit_uf' => 'MS', 'emit_municipio' => 'CAMPO GRANDE', 'emit_ie' => '283657896',
        'dest_cnpj' => '13305697000150', 'dest_nome' => 'CLIENTE FINAL LTDA',
        'dest_uf' => 'SP', 'dest_municipio' => 'SAO PAULO',
    ]), $user->id, $cliente->id);

    expect(Participante::where('user_id', $user->id)->count())->toBe(2);

    $emit = Participante::where('user_id', $user->id)->where('documento', '97551165000193')->first();
    expect($emit)->not->toBeNull()
        ->and($emit->razao_social)->toBe('HIDRATOP COMERCIO')
        ->and($emit->uf)->toBe('MS')
        ->and($emit->inscricao_estadual)->toBe('283657896')
        ->and($emit->tipo_documento)->toBe('PJ')
        ->and($emit->origem_tipo)->toBe('NFE')
        ->and($emit->origem_ref['fonte'])->toBe('clearance_snapshot');
});

it('não atualiza participante que já existe (create-only)', function () {
    $user = User::factory()->create();
    Participante::create([
        'user_id' => $user->id, 'documento' => '97551165000193',
        'tipo_documento' => 'PJ', 'razao_social' => 'NOME ORIGINAL', 'origem_tipo' => 'MANUAL',
    ]);

    app(ParticipanteAutoCadastroService::class)->criarDesdeSnapshot(pacSnapshot('NFE', [
        'emit_cnpj' => '97551165000193', 'emit_nome' => 'NOME VINDO DA SEFAZ',
    ]), $user->id, null);

    $p = Participante::where('user_id', $user->id)->where('documento', '97551165000193')->get();
    expect($p)->toHaveCount(1)
        ->and($p->first()->razao_social)->toBe('NOME ORIGINAL')
        ->and($p->first()->origem_tipo)->toBe('MANUAL');
});

it('ignora CPF e CNPJ que já é cliente do usuário', function () {
    $user = User::factory()->create();
    Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191', 'razao_social' => 'Propria',
    ]);

    app(ParticipanteAutoCadastroService::class)->criarDesdeSnapshot(pacSnapshot('NFE', [
        'emit_cnpj' => '00000000000191', // é o próprio cliente
        'dest_cpf' => '12345678901',     // CPF não vira participante
        'dest_cnpj' => null,
    ]), $user->id, null);

    expect(Participante::where('user_id', $user->id)->count())->toBe(0);
});

it('CT-e cadastra também tomador e remetente', function () {
    $user = User::factory()->create();

    app(ParticipanteAutoCadastroService::class)->criarDesdeSnapshot(pacSnapshot('CTE', [
        'emit_cnpj' => '43648971004576', 'emit_nome' => 'TRANSPORTADORA XYZ',
        'dest_cnpj' => '00000000000272', 'dest_nome' => 'DESTINO RTY',
        'tomador_cnpj' => '13305697000150', 'tomador_nome' => 'TOMADOR ABC',
        'remet_cnpj' => '97551165000193', 'remet_nome' => 'REMETENTE QWE',
    ]), $user->id, null);

    expect(Participante::where('user_id', $user->id)->count())->toBe(4)
        ->and(Participante::where('user_id', $user->id)->where('documento', '13305697000150')->value('origem_tipo'))->toBe('CTE');
});

it('isola por usuário: mesmo CNPJ pode existir pra outro usuário', function () {
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();
    Participante::create([
        'user_id' => $u1->id, 'documento' => '97551165000193',
        'tipo_documento' => 'PJ', 'razao_social' => 'DO U1', 'origem_tipo' => 'MANUAL',
    ]);

    app(ParticipanteAutoCadastroService::class)->criarDesdeSnapshot(pacSnapshot('NFE', [
        'emit_cnpj' => '97551165000193', 'emit_nome' => 'DO U2',
    ]), $u2->id, null);

    expect(Participante::where('user_id', $u2->id)->where('documento', '97551165000193')->exists())->toBeTrue();
});
