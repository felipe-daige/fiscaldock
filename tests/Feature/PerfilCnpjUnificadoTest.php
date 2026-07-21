<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function assertFluxoPerfilCnpj(string $html): void
{
    $ordem = [
        'alertas',
        'cadastro',
        'score',
        'certidoes',
        'relacionamento',
        'produtos',
        'cfops',
        'notas',
        'historico',
    ];

    $ultimaPosicao = -1;
    foreach ($ordem as $card) {
        $posicao = strpos($html, 'data-perfil-card="'.$card.'"');

        expect($posicao)
            ->not->toBeFalse("Card {$card} ausente")
            ->toBeGreaterThan($ultimaPosicao, "Card {$card} fora da ordem canônica");

        $ultimaPosicao = $posicao;
    }

    expect($html)
        ->toContain('data-perfil-cnpj-flow')
        ->toContain('data-perfil-acoes-superiores')
        ->toContain('data-perfil-alertas-retratil')
        ->toContain('data-perfil-alertas-preview')
        ->toContain('data-notas-por-pagina="10"')
        ->toContain('Registro Jurídico e Econômico')
        ->toContain('Atividades Econômicas (CNAE)')
        ->toContain('Quadro Societário (QSA)')
        ->toContain('Endereço e Contato Consultados');

    expect(strpos($html, 'data-perfil-acoes-superiores'))
        ->toBeLessThan(strpos($html, 'data-perfil-cnpj-flow'));
}

test('alertas inicia retraído e mostra a primeira ocorrência como preview', function () {
    $html = view('autenticado.perfis._alertas-operacionais', [
        'alertasPerfil' => [
            [
                'tipo' => 'media',
                'titulo' => 'Cadastro incompleto',
                'mensagem' => 'Revise os dados cadastrais.',
            ],
            [
                'tipo' => 'alta',
                'titulo' => 'Certidão vencida',
                'mensagem' => 'A CND Federal precisa ser renovada.',
            ],
        ],
    ])->render();

    expect($html)
        ->toContain('data-perfil-alertas-retratil')
        ->toContain('data-perfil-alertas-preview')
        ->toContain('data-perfil-alerta-destaque="vermelho"')
        ->toContain('border-left-color: #b91c1c')
        ->toContain('Certidão vencida — A CND Federal precisa ser renovada.')
        ->toContain('>ALTA</span>')
        ->not->toMatch('/<details[^>]*\sopen(?:\s|>)/s');
});

test('empresa própria cliente e participante usam o mesmo fluxo de cards e ações superiores', function () {
    $user = User::factory()->trialAtivo()->create();
    $empresa = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000195',
        'razao_social' => 'Empresa Própria Ltda',
        'is_empresa_propria' => true,
        'ativo' => true,
    ]);
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '11444777000161',
        'razao_social' => 'Cliente Perfil Ltda',
        'is_empresa_propria' => false,
        'ativo' => true,
    ]);
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => '07863768000138',
        'razao_social' => 'Participante Perfil Ltda',
        'origem_tipo' => 'MANUAL',
        'latitude' => -23.5,
        'longitude' => -46.6,
    ]);

    foreach ([
        '/app/minha-empresa',
        '/app/cliente/'.$cliente->id,
        '/app/participante/'.$participante->id,
    ] as $url) {
        $html = $this->actingAs($user)->get($url)->assertOk()->getContent();
        assertFluxoPerfilCnpj($html);
    }

    $this->actingAs($user)
        ->get('/app/cliente/'.$cliente->id.'/notas')
        ->assertOk()
        ->assertSee('data-notas-por-pagina="10"', false);
    $this->actingAs($user)
        ->get('/app/participante/'.$participante->id.'/notas')
        ->assertOk()
        ->assertSee('data-notas-por-pagina="10"', false);

    expect($empresa->exists)->toBeTrue();
});

test('perfil do cliente aproveita certidões consultadas no participante espelho do mesmo CNPJ', function () {
    $user = User::factory()->trialAtivo()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '11444777000161',
        'razao_social' => 'Cliente Espelho Ltda',
        'is_empresa_propria' => false,
        'ativo' => true,
    ]);
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => $cliente->documento,
        'razao_social' => 'Cliente Espelho Ltda',
        'origem_tipo' => 'MANUAL',
        'latitude' => -23.5,
        'longitude' => -46.6,
    ]);
    $plano = MonitoramentoPlano::ativos()->first() ?? MonitoramentoPlano::create([
        'nome' => 'Compliance',
        'codigo' => 'compliance',
        'ativo' => true,
        'creditos_por_consulta' => 0,
        'consultas_incluidas' => ['cadastro', 'cnd_federal'],
        'etapas' => [],
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'perfil-espelho-'.uniqid(),
        'processado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $participante->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => [
            'razao_social' => 'Cliente Espelho Ltda',
            'situacao_cadastral' => 'ATIVA',
            'natureza_juridica' => 'Sociedade Empresária Limitada',
            'cnaes' => [[
                'codigo' => '6201-5/01',
                'descricao' => 'Desenvolvimento de programas de computador',
                'principal' => true,
            ]],
            'qsa' => [[
                'nome' => 'Sócia Exemplo',
                'qualificacao' => 'Sócio-Administrador',
            ]],
            'endereco' => [
                'logradouro' => 'Rua Fiscal',
                'numero' => '100',
                'municipio' => 'São Paulo',
                'uf' => 'SP',
            ],
            'telefone_1' => '1133334444',
            'cnd_federal' => ['status' => 'Negativa', 'certidao_codigo' => 'FED-ESPELHO'],
            'consultas_realizadas' => ['cadastro', 'cnd_federal'],
        ],
        'consultado_em' => now(),
    ]);

    $this->actingAs($user)
        ->get('/app/cliente/'.$cliente->id)
        ->assertOk()
        ->assertSee('Certidões e Cadastros Fiscais')
        ->assertSee('CND Federal')
        ->assertSee('FED-ESPELHO')
        ->assertSee('Sociedade Empresária Limitada')
        ->assertSee('Desenvolvimento de programas de computador')
        ->assertSee('Sócia Exemplo')
        ->assertSee('Rua Fiscal');
});

test('participante CPF mantém a sequência com estados não aplicáveis', function () {
    $user = User::factory()->trialAtivo()->create();
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => '12345678901',
        'razao_social' => 'Pessoa Participante',
        'origem_tipo' => 'MANUAL',
        'latitude' => -23.5,
        'longitude' => -46.6,
    ]);

    $html = $this->actingAs($user)
        ->get('/app/participante/'.$participante->id)
        ->assertOk()
        ->assertSee('Risco de Crédito')
        ->assertSee('Registro jurídico e econômico não se aplica a pessoa física.')
        ->assertSee('Certidões de CNPJ e cadastros fiscais não se aplicam a pessoa física.')
        ->assertDontSee('/app/consulta/nova?participantes='.$participante->id, false)
        ->getContent();

    assertFluxoPerfilCnpj($html);
});
