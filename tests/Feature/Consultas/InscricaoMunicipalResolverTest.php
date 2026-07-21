<?php

use App\Models\Cliente;
use App\Models\Participante;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Services\Consultas\Fontes\CndMunicipalFonte;
use App\Services\Consultas\InscricaoMunicipalResolver;
use App\Services\Xml\NfeXmlParser;
use App\Services\Xml\XmlNotaImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\NfeFixtureMint;

uses(RefreshDatabase::class);

function novoUser(): User
{
    return User::factory()->create();
}

function inserirXmlNota(int $userId, array $over): void
{
    DB::table('xml_notas')->insert(array_merge([
        'user_id' => $userId,
        'tipo_documento' => 'nfe',
        'tipo_nota' => 1,
        'numero_documento' => '1',
        'chave_acesso' => str_repeat('9', 44),
        'data_emissao' => now()->toDateString(),
        'valor_total' => 0,
        'emit_documento' => '00000000000000',
        'dest_documento' => '00000000000000',
        'created_at' => now(),
        'updated_at' => now(),
    ], $over));
}

it('perfil com IM salva: retorna e NÃO consulta o acervo (número resolvido 1x)', function () {
    $u = novoUser();
    $cli = Cliente::create([
        'user_id' => $u->id, 'documento' => '56786908000127', 'razao_social' => 'COPECAR',
        'inscricao_municipal' => '123456',
    ]);

    // Mesmo com IM diferente no acervo XML, o perfil vence e nada é reconsultado.
    inserirXmlNota($u->id, ['emit_documento' => '56786908000127', 'emit_im' => '999999', 'chave_acesso' => str_repeat('9', 44)]);

    $im = app(InscricaoMunicipalResolver::class)
        ->resolver(['cnpj' => '56786908000127'], 'cliente', $cli->id, $u->id);

    expect($im)->toBe('123456');
});

it('sem IM no perfil: puxa do acervo XML (emit_im) e PERSISTE no perfil', function () {
    $u = novoUser();
    $cli = Cliente::create([
        'user_id' => $u->id, 'documento' => '56786908000127', 'razao_social' => 'COPECAR',
    ]);
    inserirXmlNota($u->id, ['emit_documento' => '56786908000127', 'emit_im' => '778899', 'chave_acesso' => str_repeat('8', 44)]);

    $im = app(InscricaoMunicipalResolver::class)
        ->resolver(['cnpj' => '56786908000127'], 'cliente', $cli->id, $u->id);

    expect($im)->toBe('778899');
    // Persistido: próxima resolução vem do perfil, sem tocar o acervo.
    expect($cli->fresh()->inscricao_municipal)->toBe('778899');
});

it('acervo XML pelo lado destinatário (dest_im) também resolve', function () {
    $u = novoUser();
    $part = Participante::create([
        'user_id' => $u->id, 'documento' => '11222333000181', 'razao_social' => 'ACME',
    ]);
    inserirXmlNota($u->id, ['dest_documento' => '11222333000181', 'dest_im' => '445566', 'chave_acesso' => str_repeat('7', 44)]);

    $im = app(InscricaoMunicipalResolver::class)
        ->resolver(['cnpj' => '11222333000181'], 'participante', $part->id, $u->id);

    expect($im)->toBe('445566');
    expect($part->fresh()->inscricao_municipal)->toBe('445566');
});

it('persistir nunca sobrescreve IM já informada (manual vence)', function () {
    $u = novoUser();
    $cli = Cliente::create([
        'user_id' => $u->id, 'documento' => '56786908000127', 'razao_social' => 'COPECAR',
        'inscricao_municipal' => 'MANUAL-1',
    ]);

    app(InscricaoMunicipalResolver::class)->persistir('cliente', $cli->id, 'OUTRA');

    expect($cli->fresh()->inscricao_municipal)->toBe('MANUAL-1');
});

it('persistir preenche perfil com IM string-vazia (não só NULL)', function () {
    $u = novoUser();
    $cli = Cliente::create([
        'user_id' => $u->id, 'documento' => '56786908000127', 'razao_social' => 'COPECAR',
        'inscricao_municipal' => '', // ex.: EFD 0000 gravou sentinela vazia
    ]);

    app(InscricaoMunicipalResolver::class)->persistir('cliente', $cli->id, '990141600');

    expect($cli->fresh()->inscricao_municipal)->toBe('990141600');
});

it('nada no perfil nem no acervo: retorna null (CND cai em INDISPONIVEL até colher/manual)', function () {
    $u = novoUser();
    $part = Participante::create([
        'user_id' => $u->id, 'documento' => '11222333000199', 'razao_social' => 'SEM IM',
    ]);

    $im = app(InscricaoMunicipalResolver::class)
        ->resolver(['cnpj' => '11222333000199'], 'participante', $part->id, $u->id);

    expect($im)->toBeNull();
});

it('cross-cadastro: IM do mesmo CNPJ em outro cadastro serve e persiste no alvo', function () {
    $u = novoUser();
    // Empresa-própria (cliente) já tem a IM (ex.: veio do EFD 0000 via n8n).
    Cliente::create([
        'user_id' => $u->id, 'documento' => '56786908000127', 'razao_social' => 'COPECAR MATRIZ',
        'is_empresa_propria' => true, 'inscricao_municipal' => 'CROSS-777',
    ]);
    // Mesmo CNPJ aparece como participante de outro cliente, sem IM.
    $part = Participante::create([
        'user_id' => $u->id, 'documento' => '56786908000127', 'razao_social' => 'COPECAR PART',
    ]);

    $im = app(InscricaoMunicipalResolver::class)
        ->resolver(['cnpj' => '56.786.908/0001-27'], 'participante', $part->id, $u->id);

    expect($im)->toBe('CROSS-777');
    expect($part->fresh()->inscricao_municipal)->toBe('CROSS-777');
});

it('cross-cadastro não vaza IM entre usuários diferentes', function () {
    $u1 = novoUser();
    $u2 = novoUser();
    Cliente::create([
        'user_id' => $u1->id, 'documento' => '56786908000127', 'razao_social' => 'DELE',
        'inscricao_municipal' => 'PRIVADA',
    ]);
    $part = Participante::create([
        'user_id' => $u2->id, 'documento' => '56786908000127', 'razao_social' => 'OUTRO USER',
    ]);

    $im = app(InscricaoMunicipalResolver::class)
        ->resolver(['cnpj' => '56786908000127'], 'participante', $part->id, $u2->id);

    expect($im)->toBeNull();
});

it('importar XML grava a IM (emit_im) direto no perfil do participante', function () {
    $u = novoUser();
    // Dono da nota = cliente pelo lado dest; o emit vira participante (contraparte).
    Cliente::create([
        'user_id' => $u->id, 'documento' => '22222222000191', 'tipo_pessoa' => 'PJ',
        'razao_social' => 'DONO', 'ativo' => true, 'is_empresa_propria' => false,
    ]);
    $imp = XmlImportacao::create([
        'user_id' => $u->id, 'tipo_documento' => 'NFE', 'modo_envio' => 'xml',
        'status' => 'concluido', 'iniciado_em' => now(), 'cliente_id' => null,
    ]);

    $parsed = app(NfeXmlParser::class)->parse(
        NfeFixtureMint::make('11111111000191', '22222222000191', str_pad('3', 44, '0'))
    );
    $parsed['header']['emit_im'] = '778811'; // NFS-e/ISSQN traria a IM do emitente

    app(XmlNotaImporter::class)->importar($parsed, '22222222000191', $imp);

    $part = Participante::where('user_id', $u->id)->where('documento', '11111111000191')->first();
    expect($part)->not->toBeNull();
    expect($part->inscricao_municipal)->toBe('778811');
});

it('CndMunicipalFonte::params envia inscricao_municipal só quando presente', function () {
    $f = new CndMunicipalFonte;

    $sem = $f->params(['cnpj' => '56786908000127', 'uf' => 'SP', 'municipio' => 'RIBEIRAO PRETO']);
    expect($sem)->not->toHaveKey('inscricao_municipal');

    $com = $f->params([
        'cnpj' => '56786908000127', 'uf' => 'SP', 'municipio' => 'RIBEIRAO PRETO',
        'inscricao_municipal' => '123456',
    ]);
    expect($com['inscricao_municipal'])->toBe('123456');
});
