<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\CteConsulta;
use App\Models\NfeConsulta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function brdLote(User $u): ConsultaLote
{
    return ConsultaLote::create([
        'user_id' => $u->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 14, 'tab_id' => 'tab-brd', 'processado_em' => now(),
    ]);
}

it('resultado NF-e exibe detalhes ricos do snapshot (operação, partes, eventos, totais, links)', function () {
    $user = User::factory()->create(['credits' => 100]);
    $lote = brdLote($user);

    NfeConsulta::create([
        'user_id' => $user->id, 'consulta_lote_id' => $lote->id,
        'chave_acesso' => '50240197551165000193550010000248001000214739',
        'tipo_documento' => 'NFE', 'modelo' => '55', 'numero' => '24800', 'serie' => 1,
        'status' => 'AUTORIZADA', 'valor_total' => 51.11,
        'natureza_operacao' => 'VENDA DE MERCADORIA',
        'tipo_operacao' => 'SAÍDA',
        'emit_nome' => 'HIDRATOP COMERCIO', 'emit_cnpj' => '97551165000193',
        'emit_ie' => '283657896', 'emit_uf' => 'MS', 'emit_municipio' => 'CAMPO GRANDE',
        'dest_nome' => 'CLIENTE FINAL LTDA', 'dest_cnpj' => '13305697000150',
        'dest_uf' => 'SP', 'dest_municipio' => 'SAO PAULO',
        'consulta_sem_certificado' => true, 'versao_xml' => '4.00', 'data_emissao' => '2025-08-11',
        'url_html' => 'https://sefaz.example/comprovante', 'url_xml' => 'https://sefaz.example/xml',
        // Fora de ordem de propósito: a linha do tempo deve ordenar pela data do evento
        'eventos' => [
            ['evento' => 'Cancelamento pelo emitente', 'protocolo' => '150240008999999', 'data_autorizacao' => '13/08/2025 às 08:36:57-04:00'],
            ['evento' => 'Autorização de Uso', 'protocolo' => '150240008274469', 'data_autorizacao' => '11/08/2025 às 08:55:28-04:00'],
        ],
        'totais' => ['normalizado_valor_nfe' => 51.11],
        'produtos' => [['descricao' => 'BOMBA HIDRAULICA', 'ncm' => '84137080', 'cfop' => '5102', 'quantidade' => '1', 'valor' => '51,11']],
        'consultado_em' => now(),
    ]);

    $resp = actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=nfe');

    $resp->assertOk()
        ->assertSee('VENDA DE MERCADORIA')
        ->assertSee('Partes do documento')
        ->assertSee('97.551.165/0001-93')
        ->assertSee('IE 283657896')
        ->assertSee('CAMPO GRANDE/MS')
        ->assertSee('CLIENTE FINAL LTDA')
        ->assertSee('Eventos na SEFAZ')
        // Linha do tempo: Emissão primeiro, depois eventos em ordem cronológica
        // (payload traz cancelamento antes da autorização; a view reordena)
        ->assertSeeInOrder(['Emissão', 'Autorizada', '11/08/2025 08:55', '150240008274469', 'Cancelada', '13/08/2025 08:36', '150240008999999'])
        ->assertSee('Totais informados pela SEFAZ')
        ->assertSee('BOMBA HIDRAULICA')
        ->assertSee('Consulta pública (sem certificado)')
        ->assertSee('https://sefaz.example/comprovante')
        ->assertSee('https://sefaz.example/xml');
});

it('resultado CT-e exibe modal, trajeto, carga, componentes e partes', function () {
    $user = User::factory()->create(['credits' => 100]);
    $lote = brdLote($user);

    CteConsulta::create([
        'user_id' => $user->id, 'consulta_lote_id' => $lote->id,
        'chave_acesso' => '50240243648971004576570010001117211468024731',
        'tipo_documento' => 'CTE', 'modelo' => '57', 'numero' => '111721', 'serie' => 1,
        'status' => 'AUTORIZADA', 'valor_prestacao' => 1500.50, 'valor_carga' => 80000,
        'natureza_operacao' => 'PRESTACAO DE SERVICO DE TRANSPORTE',
        'tipo_servico' => 'Normal', 'cfop' => '5353', 'modal' => 'Rodoviário',
        'uf_inicio' => 'MS', 'uf_fim' => 'SP',
        'emit_nome' => 'TRANSPORTADORA XYZ', 'emit_cnpj' => '43648971004576',
        'tomador_nome' => 'TOMADOR ABC', 'tomador_cnpj' => '13305697000150',
        'remet_nome' => 'REMETENTE QWE', 'remet_cnpj' => '97551165000193',
        'dest_nome' => 'DESTINO RTY', 'dest_cnpj' => '00000000000191',
        'nfes_referenciadas_count' => 1,
        'componentes' => [['nome' => 'Frete Peso', 'valor' => '1.500,50']],
        'eventos' => [['evento' => 'Autorização de Uso', 'protocolo' => '935240008630632']],
        'consultado_em' => now(),
    ]);

    $resp = actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=cte');

    $resp->assertOk()
        ->assertSee('PRESTACAO DE SERVICO DE TRANSPORTE')
        ->assertSee('Rodoviário')
        ->assertSee('MS → SP')
        ->assertSee('R$ 80.000,00')
        ->assertSee('Componentes da prestação')
        ->assertSee('Frete Peso')
        ->assertSee('TOMADOR ABC')
        ->assertSee('REMETENTE QWE')
        ->assertSee('NF-e referenciadas')
        ->assertSee('935240008630632');
});

it('consulta pública exibe banner explicando a máscara com atalho pro certificado A1', function () {
    $user = User::factory()->create(['credits' => 100]);
    $lote = brdLote($user);

    NfeConsulta::create([
        'user_id' => $user->id, 'consulta_lote_id' => $lote->id,
        'chave_acesso' => '50240197551165000193550010000248001000214739',
        'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'AUTORIZADA',
        'emit_nome' => 'HIDRATOP COMERCIO', 'emit_cnpj' => '97551165000193',
        'consulta_sem_certificado' => true,
        'produtos' => [['descricao' => 'C...', 'quantidade' => '1', 'valor' => '10,00']],
        'consultado_em' => now(),
    ]);

    actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=nfe')
        ->assertOk()
        ->assertSee('Consulta pública (sem certificado digital)')
        ->assertSee('A SEFAZ oculta parte dos dados')
        ->assertSee('Cadastrar certificado A1')
        ->assertSee('/app/minha-empresa#certificado-digital', false)
        ->assertSee('Descrições reduzidas na consulta pública');
});

it('usuário com certificado A1 válido não recebe CTA de cadastro (copy muda)', function () {
    $user = User::factory()->create(['credits' => 100]);
    $propria = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191', 'razao_social' => 'Propria',
    ]);
    \App\Models\CertificadoDigital::create([
        'user_id' => $user->id, 'cliente_id' => $propria->id, 'cnpj' => '00000000000191',
        'validade' => now()->addYear(), 'arquivo_path' => 'certs/x.pfx', 'senha_cifrada' => 'x',
    ]);
    $lote = brdLote($user);

    NfeConsulta::create([
        'user_id' => $user->id, 'consulta_lote_id' => $lote->id,
        'chave_acesso' => '50240197551165000193550010000248001000214739',
        'tipo_documento' => 'NFE', 'modelo' => '55', 'status' => 'AUTORIZADA',
        'emit_nome' => 'HIDRATOP COMERCIO', 'emit_cnpj' => '97551165000193',
        'consulta_sem_certificado' => true,
        'consultado_em' => now(),
    ]);

    actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=nfe')
        ->assertOk()
        ->assertSee('Consulta pública (sem certificado digital)')
        ->assertSee('Seu certificado A1 já está cadastrado')
        ->assertDontSee('Cadastrar certificado A1');
});

it('resultado sem snapshot rico (acervo XML) não quebra a view', function () {
    $user = User::factory()->create(['credits' => 100]);
    $lote = brdLote($user);

    actingAs($user)->get('/app/clearance/buscar/resultado/'.$lote->id.'?tipo_documento=nfe')
        ->assertOk();
});
