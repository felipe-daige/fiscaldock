<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\Landing\LandingPageController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\RafController;
use App\Http\Controllers\Dashboard\ClienteController;
use App\Http\Controllers\SpedUploadController;
use Illuminate\Support\Facades\Route;



Route::get('/', [LandingPageController::class, 'inicio'])->name('home');

Route::get('/inicio', [LandingPageController::class, 'inicio'])->name('inicio');
Route::get('/solucoes', [LandingPageController::class, 'solucoes'])->name('solucoes');
Route::get('/sobre', [LandingPageController::class, 'sobre'])->name('sobre');
Route::get('/beneficios', [LandingPageController::class, 'beneficios'])->name('beneficios');
Route::get('/impactos', [LandingPageController::class, 'impactos'])->name('impactos');
Route::get('/precos', [LandingPageController::class, 'precos'])->name('precos');
Route::get('/faq', [LandingPageController::class, 'faq'])->name('faq');
Route::get('/questionario', [LandingPageController::class, 'questionario'])->name('questionario');
Route::get('/solucoes/importacao-xml', [LandingPageController::class, 'importacaoXml'])->name('solucoes.importacao-xml');
Route::get('/solucoes/conciliacao-bancaria', [LandingPageController::class, 'conciliacaoBancaria'])->name('solucoes.conciliacao-bancaria');
Route::get('/solucoes/gestao-cnds', [LandingPageController::class, 'gestaoCnds'])->name('solucoes.gestao-cnds');
Route::get('/solucoes/inteligencia-tributaria', [LandingPageController::class, 'inteligenciaTributaria'])->name('solucoes.inteligencia-tributaria');
Route::get('/solucoes/raf', [LandingPageController::class, 'raf'])->name('solucoes.raf');
Route::post('/raf/upload-public', [LandingPageController::class, 'uploadSpedPublic'])->name('raf.upload.public');
Route::post('/raf/cancel-public', [LandingPageController::class, 'cancelSpedPublic'])->name('raf.cancel.public');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/agendar', [AuthController::class, 'showAgendar'])->name('agendar');
Route::post('/agendar', [AuthController::class, 'agendar'])->name('agendar.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Endpoint para obter CSRF token atualizado (usado pelo SPA)
Route::get('/api/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('api.csrf-token');

// Rotas autenticadas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/app/perfil', [DashboardController::class, 'perfil'])->name('app.perfil');

    // Certidões (Placeholder)
    Route::get('/app/certidoes', [DashboardController::class, 'certidoes'])->name('app.certidoes');
    Route::get('/app/certidoes/emitir', [DashboardController::class, 'certidoesEmitir'])->name('app.certidoes.emitir');
    Route::get('/app/certidoes/licitacao', [DashboardController::class, 'certidoesLicitacao'])->name('app.certidoes.licitacao');

    // Consultas (Placeholder)
    Route::get('/app/consultas/cpf', [DashboardController::class, 'consultarCpf'])->name('app.consultas.cpf');
    Route::get('/app/consultas/simples', [DashboardController::class, 'consultarSimples'])->name('app.consultas.simples');

    // Notas Fiscais (Placeholder)
    Route::get('/app/notas/historico', [DashboardController::class, 'notasHistorico'])->name('app.notas.historico');

    // Relatórios (Placeholder)
    Route::get('/app/relatorios/diagnostico', [DashboardController::class, 'relatoriosDiagnostico'])->name('app.relatorios.diagnostico');
    Route::get('/app/relatorios/exportar', [DashboardController::class, 'relatoriosExportar'])->name('app.relatorios.exportar');
    Route::get('/app/alertas', [DashboardController::class, 'alertas'])->name('app.alertas');

    // Usuário (Placeholder)
    Route::get('/app/configuracoes', [DashboardController::class, 'configuracoes'])->name('app.configuracoes');
    Route::get('/app/plano', [DashboardController::class, 'meuPlano'])->name('app.plano');

    Route::post('/app/sped/upload', SpedUploadController::class)->name('sped.upload');

    // Rotas de créditos
    Route::prefix('app/credits')->name('app.credits.')->group(function () {
        Route::get('/balance', [CreditController::class, 'balance'])->name('balance');
        Route::post('/confirm', [CreditController::class, 'confirm'])->name('confirm');
        Route::post('/cancel', [CreditController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('app/solucoes')->name('app.solucoes.')->group(function () {
        Route::get('/', [DashboardController::class, 'solucoes'])->name('index');
        Route::get('/importacao-xml', [DashboardController::class, 'importacaoXml'])->name('importacao-xml');
        Route::get('/conciliacao-bancaria', [DashboardController::class, 'conciliacaoBancaria'])->name('conciliacao-bancaria');
        Route::get('/gestao-cnds', [DashboardController::class, 'gestaoCnds'])->name('gestao-cnds');
        Route::get('/inteligencia-tributaria', [DashboardController::class, 'inteligenciaTributaria'])->name('inteligencia-tributaria');
    });

    // Rotas RAF (movidas de app/solucoes para app/raf)
    Route::get('/app/raf', [DashboardController::class, 'raf'])->name('app.raf');
    Route::post('/app/raf/upload', [DashboardController::class, 'uploadSped'])->name('app.raf.upload');
    
    // Rotas de histórico de relatórios RAF
    Route::get('/app/raf/historico', [RafController::class, 'historico'])->name('app.raf.historico');
    Route::get('/app/raf/detalhes/{id}', [RafController::class, 'detalhes'])->name('app.raf.detalhes');
    Route::post('/app/raf/confirmar/{id}', [RafController::class, 'confirmar'])->name('app.raf.confirmar');
    Route::post('/app/raf/cancelar/{id}', [RafController::class, 'cancelar'])->name('app.raf.cancelar');
    Route::get('/app/raf/baixar/{id}', [RafController::class, 'baixar'])->name('app.raf.baixar');
    Route::post('/app/raf/excluir/{id}', [RafController::class, 'excluir'])->name('app.raf.excluir');

    // Rota de Importação de SPED
    Route::get('/app/sped_importar', [DashboardController::class, 'spedImportar'])->name('app.sped.importar');

    // Rota de Análise de Risco SPED
    Route::get('/app/sped-analise-risco', [DashboardController::class, 'spedAnaliseRisco'])->name('app.sped.analise.risco');

    // Rota de validação de XML
    Route::get('/app/validar_xml', [DashboardController::class, 'validarXml'])->name('app.validar_xml');
    
    // Rota de Análise de Risco XMLs
    Route::get('/app/xml_analise_risco', [DashboardController::class, 'xmlAnaliseRisco'])->name('app.xml.analise.risco');
    
    // Rota de Novo Cliente (formulário de cadastro)
    Route::get('/app/novo_cliente', [DashboardController::class, 'novoCliente'])->name('app.novo.cliente');
    Route::post('/app/novo_cliente', [ClienteController::class, 'store'])->name('app.cliente.store');
    
    // Rota de Consultar Cliente (análise de risco)
    Route::get('/app/consultar_cliente', [DashboardController::class, 'consultarCliente'])->name('app.consultar.cliente');
    
    // Rota de Clientes
    Route::get('/app/clientes', [DashboardController::class, 'clientes'])->name('app.clientes');
    
    // Rota de Consultar Inscrição Estadual
    Route::get('/app/consultar', [DashboardController::class, 'consultarInscricaoEstadual'])->name('app.consultar');
    
    // Rota de Consultar Listas Restritivas
    Route::get('/app/consultar_listas_restritivas', [DashboardController::class, 'consultarListasRestritivas'])->name('app.consultar.listas.restritivas');
    
    // Rota de Consultar CNPJ
    Route::get('/app/consultar_cnpj', [DashboardController::class, 'consultarCnpj'])->name('app.consultar.cnpj');
    
    // Rota SSE para notificações em tempo real
    Route::get('/api/data/notifications/stream', [\App\Http\Controllers\Api\DataReceiverController::class, 'streamNotifications'])
        ->name('api.data.notifications.stream');

    // Rotas de Monitoramento
    Route::prefix('app/monitoramento')->name('app.monitoramento.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'index'])->name('index');
        Route::get('/planos', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'planos'])->name('planos');
        Route::get('/sped', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'importarSped'])->name('sped');
        Route::get('/avulso', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'consultaAvulsa'])->name('avulso');
        Route::get('/historico', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'historico'])->name('historico');
        Route::get('/participantes', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'listaParticipantes'])->name('participantes');
        Route::get('/clientes', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'clientes'])->name('clientes');
        Route::get('/participante/nota-fiscal/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'notaFiscalDetalhes'])->name('nota-fiscal.detalhes');
        Route::get('/participante/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'participante'])->name('participante');
        Route::get('/consulta/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'consultaDetalhes'])->name('consulta');
        Route::get('/participantes-raf/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'participantesRaf'])->name('participantes-raf');

        // Acoes
        Route::post('/importar-raf/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'importarDoRaf'])->name('importar-raf');
        Route::post('/adicionar-cnpj', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'adicionarCnpj'])->name('adicionar-cnpj');
        Route::post('/consulta-avulsa', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'executarConsultaAvulsa'])->name('consulta-avulsa');

        // Importação de arquivo .txt com SSE
        Route::post('/importar-txt', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'importarTxt'])->name('importar-txt');
        Route::get('/importacao/stream/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'streamImportacao'])->name('importacao.stream');

        // SSE para acompanhar resultado de consultas em tempo real
        Route::get('/consulta/stream', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'streamConsultas'])->name('consulta.stream');

        // SSE para acompanhar progresso de processamento SPED em tempo real
        Route::get('/progresso/stream', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'streamProgresso'])->name('progresso.stream');

        // Assinaturas
        Route::post('/assinatura', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'criarAssinatura'])->name('assinatura.criar');
        Route::post('/assinatura/{id}/pausar', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'pausarAssinatura'])->name('assinatura.pausar');
        Route::post('/assinatura/{id}/reativar', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'reativarAssinatura'])->name('assinatura.reativar');
        Route::delete('/assinatura/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'cancelarAssinatura'])->name('assinatura.cancelar');

        // Grupos de participantes
        Route::get('/grupos', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'grupos'])->name('grupos');
        Route::post('/grupos', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'criarGrupo'])->name('grupos.criar');
        Route::put('/grupos/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'editarGrupo'])->name('grupos.editar');
        Route::delete('/grupos/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'excluirGrupo'])->name('grupos.excluir');
        Route::post('/participantes/associar-grupo', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'associarGrupo'])->name('participantes.associar-grupo');

        // Participantes por importação (AJAX)
        Route::get('/participantes/por-importacao/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'participantesPorImportacao'])->name('participantes.por-importacao');

        // Participantes por IDs (AJAX) - usado quando n8n envia participante_ids
        Route::post('/participantes/por-ids', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'participantesPorIds'])->name('participantes.por-ids');

        // Importação de XMLs (NF-e, NFS-e, CT-e)
        Route::get('/xml', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'index'])->name('xml');
        Route::post('/xml/validar', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'validar'])->name('xml.validar');
        Route::post('/xml/importar', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'importar'])->name('xml.importar');
        Route::get('/xml/progresso/stream', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'streamProgresso'])->name('xml.progresso.stream');
        Route::get('/xml/importacao/{id}/participantes', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'getParticipantes'])->name('xml.importacao.participantes');
    });
});