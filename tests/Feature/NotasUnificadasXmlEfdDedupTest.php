<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Services\NotaFiscalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Dedup EFD × XML na listagem unificada (/app/notas) e nos KPIs.
 * A MESMA nota (mesma chave_acesso) pode existir nos dois acervos — importada
 * via XML pelo contador E escriturada no SPED. Na visão unificada a EFD vence
 * (mesma regra do NotaItemUnificadoService); com origem=xml explícito o acervo
 * XML aparece completo.
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->cliente = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'EMPRESA TESTE',
        'documento' => '00000000000100', 'is_empresa_propria' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $impEfd = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente,
        'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'i.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
    $impXml = XmlImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente,
        'status' => 'concluido', 'tipo_documento' => 'NFE',
    ]);

    $this->chaveDup = str_pad('7', 44, '7');
    $chaveSoXml = str_pad('8', 44, '8');
    $chaveSoEfd = str_pad('9', 44, '9');

    $mkEfd = fn (array $a) => EfdNota::create(array_merge([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'importacao_id' => $impEfd->id,
        'numero' => random_int(1, 99999), 'serie' => '1', 'modelo' => '55', 'data_emissao' => '2024-01-15',
        'valor_desconto' => 0, 'cancelada' => false, 'origem_arquivo' => 'fiscal',
    ], $a));

    $mkXml = fn (array $a) => XmlNota::create(array_merge([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'importacao_xml_id' => $impXml->id,
        'tipo_documento' => 'NFE', 'numero_documento' => random_int(1, 99999), 'serie' => 1,
        'data_emissao' => '2024-01-15 10:00:00', 'tipo_nota' => XmlNota::TIPO_SAIDA,
        'emit_documento' => '00000000000100', 'emit_razao_social' => 'EMPRESA TESTE',
        'dest_documento' => '13305697000150', 'dest_razao_social' => 'DESTINATARIO XYZ',
    ], $a));

    // Mesma nota nos 2 acervos (chave duplicada)
    $mkEfd(['chave_acesso' => $this->chaveDup, 'tipo_operacao' => 'saida', 'valor_total' => 1000]);
    $mkXml(['chave_acesso' => $this->chaveDup, 'valor_total' => 1000]);

    // Só no XML
    $mkXml(['chave_acesso' => $chaveSoXml, 'valor_total' => 300]);

    // Só no EFD (entrada)
    $mkEfd(['chave_acesso' => $chaveSoEfd, 'tipo_operacao' => 'entrada', 'valor_total' => 700]);

    // NFS-e só no XML, sem chave de acesso — nunca pode sumir por dedup
    $mkXml(['chave_acesso' => null, 'tipo_documento' => 'NFSE', 'valor_total' => 200]);

    $this->service = app(NotaFiscalService::class);
});

it('nota com mesma chave nos 2 acervos aparece 1x na lista unificada (EFD vence)', function () {
    $notas = $this->service->listarUnificadas($this->user->id, []);

    $daChave = collect($notas->items())->where('chave_acesso', $this->chaveDup);

    expect($daChave)->toHaveCount(1)
        ->and($daChave->first()['origem'])->toBe('efd')
        ->and($notas->total())->toBe(4); // dup(1) + só-xml + só-efd + nfs-e sem chave
});

it('KPIs unificados contam a nota duplicada uma vez só', function () {
    $kpis = $this->service->calcularKpis($this->user->id, []);

    // Saídas: 1000 (dup, conta só a EFD) + 300 (só XML) + 200 (NFS-e) — NÃO 2500
    expect($kpis['operacoes']['saidas']['valor'])->toBe(1500.0)
        ->and($kpis['operacoes']['saidas']['quantidade'])->toBe(3) // dup + só-xml + nfs-e
        ->and($kpis['operacoes']['entradas']['valor'])->toBe(700.0)
        ->and($kpis['operacoes']['entradas']['quantidade'])->toBe(1);
});

it('filtro origem=xml mostra o acervo XML completo, inclusive a chave duplicada', function () {
    $notas = $this->service->listarUnificadas($this->user->id, ['origem' => 'xml']);

    $chaves = collect($notas->items())->pluck('chave_acesso');

    expect($notas->total())->toBe(3) // dup + só-xml + nfs-e
        ->and($chaves->contains($this->chaveDup))->toBeTrue();

    $kpis = $this->service->calcularKpis($this->user->id, ['origem' => 'xml']);
    expect($kpis['operacoes']['saidas']['valor'])->toBe(1500.0); // 1000 + 300 + 200
});

it('filtro origem=efd segue mostrando só o acervo EFD', function () {
    $notas = $this->service->listarUnificadas($this->user->id, ['origem' => 'efd']);

    expect($notas->total())->toBe(2); // dup + só-efd
});
