<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\EfdNotaItem;
use App\Models\Participante;
use App\Models\User;
use App\Services\Participantes\ParticipanteMovimentacaoService;

uses(RefreshDatabase::class);

// helpers criarNotaEfd / criarItemEfd

function criarNotaEfd(User $u, Participante $p, string $tipo, ?string $data, float $valor, bool $cancelada = false): EfdNota
{
    $cliente = Cliente::firstOrCreate(['user_id' => $u->id, 'documento' => '00000000000191'], ['razao_social' => 'Empresa Teste']);
    $imp = EfdImportacao::firstOrCreate(['user_id' => $u->id, 'tipo_efd' => 'EFD ICMS/IPI'], []);
    return EfdNota::create([
        'user_id' => $u->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'participante_id' => $p->id, 'modelo' => '55', 'numero' => (string) random_int(1, 9999999),
        'tipo_operacao' => $tipo, 'valor_total' => $valor, 'data_emissao' => $data, 'cancelada' => $cancelada,
    ]);
}
function criarItemEfd(EfdNota $n, array $attrs): EfdNotaItem
{
    return EfdNotaItem::create(array_merge([
        'efd_nota_id' => $n->id, 'user_id' => $n->user_id, 'numero_item' => 1, 'codigo_item' => 'COD' . random_int(1, 9999),
    ], $attrs));
}

beforeEach(function () {
    $this->svc = app(ParticipanteMovimentacaoService::class);
    $this->user = User::factory()->create();
    $this->p = Participante::create(['user_id' => $this->user->id, 'documento' => '07863768000138', 'razao_social' => 'ACME LTDA', 'uf' => 'SP', 'crt' => '3']);
});

it('kpis somam entradas e saidas e ignoram canceladas', function () {
    criarNotaEfd($this->user, $this->p, 'entrada', '2026-01-10', 100);
    criarNotaEfd($this->user, $this->p, 'entrada', '2026-02-10', 50);
    criarNotaEfd($this->user, $this->p, 'saida', '2026-02-15', 200);
    criarNotaEfd($this->user, $this->p, 'saida', '2026-03-01', 9999, cancelada: true); // ignorada

    $k = $this->svc->kpis($this->p);

    expect($k['total_notas'])->toBe(3)
        ->and($k['entradas_qtd'])->toBe(2)
        ->and($k['entradas_valor'])->toBe(150.0)
        ->and($k['saidas_qtd'])->toBe(1)
        ->and($k['saidas_valor'])->toBe(200.0)
        ->and($k['valor_movimentado'])->toBe(350.0)
        ->and($k['periodo_inicio'])->toBe('2026-01')
        ->and($k['periodo_fim'])->toBe('2026-02');
});

it('porCompetencia pivota entrada/saida e ignora data nula', function () {
    criarNotaEfd($this->user, $this->p, 'entrada', '2026-01-10', 100);
    criarNotaEfd($this->user, $this->p, 'saida', '2026-01-20', 70);
    $n = criarNotaEfd($this->user, $this->p, 'entrada', '2026-02-01', 40);
    EfdNota::where('id', $n->id)->update(['data_emissao' => null]); // não deve aparecer

    $comp = $this->svc->porCompetencia($this->p);

    expect($comp)->toHaveCount(1)
        ->and($comp[0])->toBe(['competencia' => '2026-01', 'entrada' => 100.0, 'saida' => 70.0]);
});

it('porCfop e porCst agregam itens ordenando cfop por valor', function () {
    $n1 = criarNotaEfd($this->user, $this->p, 'saida', '2026-01-10', 100);
    $n2 = criarNotaEfd($this->user, $this->p, 'saida', '2026-01-11', 100);
    criarItemEfd($n1, ['cfop' => '5102', 'cst_icms' => '00', 'valor_total' => 30, 'valor_icms' => 3, 'aliquota_icms' => 10, 'valor_pis' => 1, 'valor_cofins' => 2]);
    criarItemEfd($n2, ['cfop' => '6102', 'cst_icms' => '00', 'valor_total' => 90, 'valor_icms' => 9, 'aliquota_icms' => 10, 'valor_pis' => 1, 'valor_cofins' => 2]);

    $cfop = $this->svc->porCfop($this->p);
    expect($cfop[0]['cfop'])->toBe('6102')->and($cfop[0]['valor'])->toBe(90.0);
    expect($cfop[1]['cfop'])->toBe('5102');

    $cst = $this->svc->porCst($this->p);
    expect($cst)->toHaveCount(1)
        ->and($cst[0])->toMatchArray(['cst' => '00', 'qtd' => 2, 'valor' => 120.0]);

    $imp = $this->svc->impostos($this->p);
    expect($imp['icms'])->toBe(12.0)->and($imp['pis'])->toBe(2.0)->and($imp['cofins'])->toBe(4.0)
        ->and($imp['aliquota_icms_media'])->toBe(10.0);
});
