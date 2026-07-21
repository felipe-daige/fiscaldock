<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * As 6 telas de export trocaram os 3 botões inline por 1 botão "Exportar" que abre um
 * modal de formato. Estes testes renderizam as views por HTTP — compilar o Blade não basta:
 * componente não fechado gera PHP inválido que só estoura no render (bug de 2026-07-08).
 */
beforeEach(function () {
    $this->user = User::factory()->create([
        'trial_used' => true,
        'trial_expires_at' => now()->addDays(30),
        'trial_credits_remaining' => 50,
    ]);
    DB::table('clientes')->insert([
        'user_id' => $this->user->id, 'razao_social' => 'EMPRESA PROPRIA', 'documento' => '00000000000100',
        'is_empresa_propria' => true, 'ativo' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
});

dataset('telas de export', [
    'clientes' => ['/app/clientes', 'modal-exportar-clientes'],
    'participantes' => ['/app/participantes', 'modal-exportar-participantes'],
    'resumo fiscal' => ['/app/resumo-fiscal', 'modal-exportar-rf'],
    'alertas' => ['/app/alertas', 'modal-exportar-alertas'],
    'bi fiscal' => ['/app/bi/dashboard', 'modal-exportar-bi'],
    'catálogo de itens' => ['/app/bi/catalogo-itens', 'modal-exportar-catalogo'],
]);

it('renderiza o botão único Exportar e o modal de formato', function (string $url, string $modalId) {
    $resp = $this->actingAs($this->user)->get($url);

    $resp->assertOk();
    $html = $resp->getContent();

    // Botão único + modal de formato com as 3 opções.
    expect($html)->toContain('id="app" class="auth-ui flex-1"');
    expect($html)->toContain('data-export-menu="'.$modalId.'"');
    expect($html)->toContain('auth-control');
    expect($html)->toContain('data-export-option="pdf"');
    expect($html)->toContain('data-export-option="xlsx"');
    expect($html)->toContain('data-export-option="csv"');

    // Não sobrou nenhum botão inline do componente antigo.
    expect($html)->not->toContain('title="Em breve"');

    // XLSX e CSV precisam se apresentar como PLANILHAS, agrupadas sob a legenda.
    expect($html)->toContain('Planilha Excel (.xlsx)');
    expect($html)->toContain('Planilha CSV (.csv)');
    expect($html)->toContain('Planilhas');
    expect($html)->toContain('Documento');

    // Ícone próprio por formato (tinta inline — nunca classe Tailwind de cor).
    expect($html)->toContain('stroke="#b91c1c"');  // pdf
    expect($html)->toContain('stroke="#047857"');  // xlsx
    expect($html)->toContain('stroke="#1d4ed8"');  // csv
})->with('telas de export');

it('explica a diferença entre XLSX e CSV', function () {
    $html = $this->actingAs($this->user)->get('/app/clientes')->getContent();

    expect($html)->toContain('Abre no Excel/Google Sheets já formatado');
    expect($html)->toContain('somar, filtrar e pivotar');
    expect($html)->toContain('Texto puro, uma tabela só');
    expect($html)->toContain('abre em qualquer sistema');
});

it('BI: o CSV é ZIP, então não promete "uma tabela só"', function () {
    $html = $this->actingAs($this->user)->get('/app/bi/dashboard')->getContent();

    expect($html)->toContain('Um arquivo .csv por seção, empacotados num .zip.');
    expect($html)->not->toContain('Texto puro, uma tabela só');
});

it('clientes e participantes expõem a função de ids da seleção e o overlay', function () {
    $clientes = $this->actingAs($this->user)->get('/app/clientes')->getContent();
    expect($clientes)->toContain('window.exportClientesIds');
    expect($clientes)->toContain('download-overlay-clientes');
    expect($clientes)->toContain('/app/clientes/exportar-xlsx');

    $participantes = $this->actingAs($this->user)->get('/app/participantes')->getContent();
    expect($participantes)->toContain('window.exportParticipantesIds');
    expect($participantes)->toContain('download-overlay-participantes');
    expect($participantes)->toContain('/app/participantes/exportar-csv');
});

it('BI encadeia o PDF no modal de escopo (não baixa direto)', function () {
    $html = $this->actingAs($this->user)->get('/app/bi/dashboard')->getContent();

    // A opção PDF abre o 2º modal; o modal de escopo continua existindo intacto.
    expect($html)->toContain('modal-export-bi-pdf');
    expect($html)->toContain('export-pdf-dossies');
    // XLSX/CSV do BI seguem baixando direto.
    expect($html)->toContain('/app/bi/exportar-xlsx');
    expect($html)->toContain('/app/bi/exportar-csv-zip');
});

it('POST de export anexa o cookie bi_download para o overlay fechar', function () {
    $clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'ACME', 'documento' => '11222333000181',
        'is_empresa_propria' => false, 'ativo' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->post('/app/clientes/exportar-csv', ['ids' => [$clienteId], 'download_token' => 'tok123'])
        ->assertOk()
        ->assertCookie('bi_download', 'tok123');
});

/**
 * Detalhe de cliente e participante trocaram os botões/links de export bespoke pelo mesmo
 * botão único "Exportar" + modal de formato do design system (x-export-menu). Single-entity
 * só tem PDF + XLSX (não há rota CSV), então o modal traz 2 opções — não 3.
 */
it('perfil do cliente usa o modal DS de export (PDF + XLSX) e não o bespoke antigo', function () {
    $cliente = \App\Models\Cliente::create([
        'user_id' => $this->user->id, 'documento' => '11222333000181',
        'tipo_pessoa' => 'PJ', 'razao_social' => 'ACME LTDA', 'ativo' => true,
    ]);

    $html = $this->actingAs($this->user)->get('/app/cliente/'.$cliente->id)->assertOk()->getContent();

    // Botão único + modal de formato do design system.
    expect($html)->toContain('data-export-menu="modal-exportar-cliente-show"');
    expect($html)->toContain('data-export-option="pdf"');
    expect($html)->toContain('data-export-option="xlsx"');
    expect($html)->toContain('download-overlay-cliente-show');
    expect($html)->toContain('/app/cliente/'.$cliente->id.'/dossie');
    // Seletor de participantes (top N) preservado dentro do modal — só afeta o PDF.
    expect($html)->toContain('id="dossie-show-top"');

    // Single-entity não tem CSV: o modal traz só PDF + XLSX.
    expect($html)->not->toContain('data-export-option="csv"');

    // Sobrou nada do modal/botões bespoke antigos.
    expect($html)->not->toContain('id="btn-dossie-cliente"');
    expect($html)->not->toContain('id="btn-dossie-xlsx-show"');
    expect($html)->not->toContain('id="modal-dossie-show"');
});

it('perfil do participante usa o modal DS de export (PDF + XLSX) e não os links soltos', function () {
    $participante = \App\Models\Participante::create([
        'user_id' => $this->user->id, 'documento' => '44555666000177',
        'razao_social' => 'FORNECEDOR SA', 'latitude' => -15.0, 'longitude' => -47.0,
    ]);

    $html = $this->actingAs($this->user)->get('/app/participante/'.$participante->id)->assertOk()->getContent();

    expect($html)->toContain('data-export-menu="modal-exportar-participante"');
    expect($html)->toContain('data-export-option="pdf"');
    expect($html)->toContain('data-export-option="xlsx"');
    expect($html)->toContain('download-overlay-participante-show');
    expect($html)->toContain('/app/participante/'.$participante->id.'/dossie');
    expect($html)->not->toContain('data-export-option="csv"');

    // Os dois <a> soltos ("Exportar PDF"/"Exportar XLSX", target=_blank) sumiram.
    expect($html)->not->toContain('>Exportar PDF</a>');
    expect($html)->not->toContain('>Exportar XLSX</a>');
});

it('dossiê de entidade única anexa o cookie bi_download quando vem download_token', function () {
    $cliente = \App\Models\Cliente::create([
        'user_id' => $this->user->id, 'documento' => '11222333000182',
        'tipo_pessoa' => 'PJ', 'razao_social' => 'ACME 2', 'ativo' => true,
    ]);
    $participante = \App\Models\Participante::create([
        'user_id' => $this->user->id, 'documento' => '44555666000178',
        'razao_social' => 'FORNECEDOR 2', 'latitude' => -15.0, 'longitude' => -47.0,
    ]);

    $this->actingAs($this->user)
        ->get('/app/cliente/'.$cliente->id.'/dossie?formato=xlsx&download_token=tokcli')
        ->assertOk()
        ->assertCookie('bi_download', 'tokcli');

    $this->actingAs($this->user)
        ->get('/app/participante/'.$participante->id.'/dossie?formato=xlsx&download_token=tokpar')
        ->assertOk()
        ->assertCookie('bi_download', 'tokpar');
});
