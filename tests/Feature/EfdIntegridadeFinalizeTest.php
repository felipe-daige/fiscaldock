<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Services\EfdAuditoriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** Monta um SPED bruto mínimo com as C100 dadas e embala igual ao arquivo_base64 (JSON string). */
function spedComC100(array $linhasC100): string
{
    $sped = "|0000|LECD|01012026|31012026|UTIDA FOODS|56786908000127|\n"
        .implode("\n", $linhasC100)."\n|9999|10|\n";

    return json_encode($sped); // lerSpedBruto faz json_decode
}

function c100(string $chave, string $codSit = '00', string $codPart = '019075128507', string $mod = '55'): string
{
    // |C100|IND_OPER|IND_EMIT|COD_PART|COD_MOD|COD_SIT|SER|NUM_DOC|CHV_NFE|...
    return "|C100|1|0|{$codPart}|{$mod}|{$codSit}|1|123|{$chave}|15012026|15012026|1000,00|";
}

function novaImportacao(User $u, string $sped): EfdImportacao
{
    return EfdImportacao::create([
        'user_id' => $u->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'processando',
        'arquivo_nome' => 'utida.txt', 'arquivo_base64' => $sped, 'iniciado_em' => now(),
    ]);
}

function inserirNotaBanco(User $u, Cliente $cli, EfdImportacao $imp, string $chave, string $mod = '55'): void
{
    EfdNota::create([
        'user_id' => $u->id, 'cliente_id' => $cli->id, 'importacao_id' => $imp->id,
        'chave_acesso' => $chave, 'modelo' => $mod, 'numero' => 123, 'serie' => '1',
        'data_emissao' => '2026-01-15', 'tipo_operacao' => 'saida', 'origem_arquivo' => 'fiscal',
        'valor_total' => 1000, 'valor_desconto' => 0, 'cancelada' => false,
    ]);
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->cliente = Cliente::create([
        'user_id' => $this->user->id, 'documento' => '56786908000127', 'razao_social' => 'UTIDA',
    ]);
    $this->svc = app(EfdAuditoriaService::class);
});

it('detecta notas dropadas: SPED tem 3 válidas, banco só 2 → faltando 1, ok=false', function () {
    $a = str_pad('A', 44, '0');
    $b = str_pad('B', 44, '0'); // esta o pipeline "dropou"
    $c = str_pad('C', 44, '0'); // NFC-e consumidor final (COD_PART vazio) — válida, deve estar no banco
    $cancel = str_pad('D', 44, '0');

    $sped = spedComC100([
        c100($a),
        c100($b),
        c100($c, '00', '', '65'),        // NFC-e sem COD_PART: é o caso UTIDA, TEM que entrar
        c100($cancel, '02'),             // cancelada: descartável, não conta
    ]);
    $imp = novaImportacao($this->user, $sped);
    inserirNotaBanco($this->user, $this->cliente, $imp, $a);
    inserirNotaBanco($this->user, $this->cliente, $imp, $c, '65');
    // $b ausente de propósito (drop do Merge)

    $r = $this->svc->integridade($imp);

    expect($r['esperadas'])->toBe(3);   // A, B, C (cancelada fora)
    expect($r['no_banco'])->toBe(2);
    expect($r['faltando'])->toBe(1);
    expect($r['ok'])->toBeFalse();
    expect($r['amostra_faltando'])->toContain($b);
});

it('import íntegro: todas as chaves válidas no banco → ok=true', function () {
    $a = str_pad('A', 44, '0');
    $c = str_pad('C', 44, '0');
    $sped = spedComC100([c100($a), c100($c, '00', '', '65')]);
    $imp = novaImportacao($this->user, $sped);
    inserirNotaBanco($this->user, $this->cliente, $imp, $a);
    inserirNotaBanco($this->user, $this->cliente, $imp, $c, '65');

    $r = $this->svc->integridade($imp);

    expect($r['esperadas'])->toBe(2);
    expect($r['faltando'])->toBe(0);
    expect($r['ok'])->toBeTrue();
});

it('sem arquivo retido: degrada seguro (esperadas=0, ok=true, sem alarme falso)', function () {
    $imp = EfdImportacao::create([
        'user_id' => $this->user->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'processando',
        'arquivo_nome' => 'x.txt', 'arquivo_base64' => null, 'iniciado_em' => now(),
    ]);

    $r = $this->svc->integridade($imp);

    expect($r['esperadas'])->toBe(0);
    expect($r['ok'])->toBeTrue();
});
