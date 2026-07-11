<?php

use App\Models\Cliente;
use App\Models\Participante;
use App\Models\User;
use App\Services\Clearance\CnpjMascaradoResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Portal SEFAZ (consulta sem certificado) zera os 5 primeiros dígitos da contraparte:
// RAIZEN 09538958000105 chega como 00000958000105, nome 'RAIZ***'.

function cmrParticipante(User $u, string $doc, string $razao): Participante
{
    return Participante::create([
        'user_id' => $u->id, 'documento' => $doc, 'tipo_documento' => 'PJ',
        'razao_social' => $razao, 'origem_tipo' => 'MANUAL',
    ]);
}

it('detecta CNPJ mascarado: prefixo zerado com DV inválido', function () {
    $r = app(CnpjMascaradoResolver::class);

    expect($r->estaMascarado('00000958000105'))->toBeTrue()
        // Banco do Brasil: raiz baixa mas DV válido — CNPJ real, não máscara
        ->and($r->estaMascarado('00000000000191'))->toBeFalse()
        ->and($r->estaMascarado('97551165000193'))->toBeFalse()
        ->and($r->estaMascarado('958000105'))->toBeFalse()
        ->and($r->estaMascarado(null))->toBeFalse();
});

it('resolve participante por sufixo quando o match é único', function () {
    $user = User::factory()->create();
    $p = cmrParticipante($user, '09538958000105', 'RAIZEN AGRICOLA CAARAPO LTDA');

    $resolvido = app(CnpjMascaradoResolver::class)
        ->identificarParticipante($user->id, '00000958000105', 'RAIZ***');

    expect($resolvido?->id)->toBe($p->id);
});

it('não resolve quando documento não está mascarado', function () {
    $user = User::factory()->create();
    cmrParticipante($user, '09538958000105', 'RAIZEN AGRICOLA CAARAPO LTDA');

    expect(app(CnpjMascaradoResolver::class)->identificarParticipante($user->id, '09538958000105', 'RAIZEN'))
        ->toBeNull();
});

it('conflito de sufixo: nome mascarado desambigua; sem desambiguação retorna null', function () {
    $user = User::factory()->create();
    $raizen = cmrParticipante($user, '09538958000105', 'RAIZEN AGRICOLA CAARAPO LTDA');
    cmrParticipante($user, '12345958000105', 'BOMBAS HIDRAULICAS LTDA');

    $r = app(CnpjMascaradoResolver::class);

    // Nome desambigua entre os dois candidatos de mesmo sufixo
    expect($r->identificarParticipante($user->id, '00000958000105', 'RAIZ***')?->id)->toBe($raizen->id)
        // Sem nome: ambíguo, não chuta
        ->and($r->identificarParticipante($user->id, '00000958000105', null))->toBeNull();
});

it('match único com nome conflitante retorna null', function () {
    $user = User::factory()->create();
    cmrParticipante($user, '09538958000105', 'OUTRA EMPRESA LTDA');

    expect(app(CnpjMascaradoResolver::class)->identificarParticipante($user->id, '00000958000105', 'RAIZ***'))
        ->toBeNull();
});

it('não vaza participante de outro usuário', function () {
    $dono = User::factory()->create();
    $outro = User::factory()->create();
    cmrParticipante($dono, '09538958000105', 'RAIZEN AGRICOLA CAARAPO LTDA');

    expect(app(CnpjMascaradoResolver::class)->identificarParticipante($outro->id, '00000958000105', 'RAIZ***'))
        ->toBeNull();
});

it('resolve cliente mascarado por sufixo + nome', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id, 'tipo_pessoa' => 'PJ',
        'documento' => '09538958000105', 'razao_social' => 'RAIZEN AGRICOLA CAARAPO LTDA',
    ]);

    expect(app(CnpjMascaradoResolver::class)->identificarCliente($user->id, '00000958000105', 'RAIZ***')?->id)
        ->toBe($cliente->id);
});
