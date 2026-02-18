<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\Landing\LandingPageController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\RafConsultaController;
use App\Http\Controllers\Dashboard\ClienteController;
use App\Http\Controllers\Dashboard\AnalyticsController;
use App\Http\Controllers\Dashboard\RiskScoreController;
use App\Http\Controllers\Dashboard\ValidacaoController;
use App\Http\Controllers\Dashboard\MinhaEmpresaController;
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
    });

    Route::prefix('app/solucoes')->name('app.solucoes.')->group(function () {
        Route::get('/', [DashboardController::class, 'solucoes'])->name('index');
        Route::get('/importacao-xml', [DashboardController::class, 'importacaoXml'])->name('importacao-xml');
        Route::get('/conciliacao-bancaria', [DashboardController::class, 'conciliacaoBancaria'])->name('conciliacao-bancaria');
        Route::get('/gestao-cnds', [DashboardController::class, 'gestaoCnds'])->name('gestao-cnds');
        Route::get('/inteligencia-tributaria', [DashboardController::class, 'inteligenciaTributaria'])->name('inteligencia-tributaria');
    });

    // Rotas RAF Consulta (nova arquitetura - seleciona participantes existentes)
    Route::prefix('app/raf')->name('app.raf.')->group(function () {
        // Principal: seleção de participantes para consulta
        Route::get('/consulta', [RafConsultaController::class, 'index'])->name('consulta');
        Route::get('/consulta/participantes', [RafConsultaController::class, 'getParticipantes'])->name('consulta.participantes');
        Route::get('/consulta/participantes/grupo/{id}', [RafConsultaController::class, 'getParticipantesGrupo'])->name('consulta.participantes.grupo');
        Route::post('/consulta/calcular-custo', [RafConsultaController::class, 'calcularCusto'])->name('consulta.calcular-custo');
        Route::post('/consulta/executar', [RafConsultaController::class, 'executar'])->name('consulta.executar');
        Route::get('/consulta/progresso/stream', [RafConsultaController::class, 'streamProgresso'])->name('consulta.progresso.stream');
        Route::get('/lote/{id}/baixar', [RafConsultaController::class, 'baixarLote'])->name('lote.baixar');

        // Histórico unificado
        Route::get('/historico', [RafConsultaController::class, 'historico'])->name('historico');
    });

    // Redirect da rota antiga /app/raf para a nova
    Route::get('/app/raf', function () {
        return redirect()->route('app.raf.consulta');
    });

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
    Route::delete('/app/clientes/bulk-delete', [ClienteController::class, 'bulkDestroy'])->name('app.clientes.bulk-delete');
    Route::get('/app/cliente/{id}/editar', [ClienteController::class, 'edit'])->name('app.cliente.edit');
    Route::put('/app/cliente/{id}', [ClienteController::class, 'update'])->name('app.cliente.update');
    Route::delete('/app/cliente/{id}', [ClienteController::class, 'destroy'])->name('app.cliente.destroy');
    Route::get('/app/cliente/{id}', [DashboardController::class, 'clienteDetalhes'])->name('app.cliente.detalhes');
    
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
        Route::get('/avulso', function () {
            return redirect('/app/consultas/avulso', 301);
        })->name('avulso');
        Route::get('/historico', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'historico'])->name('historico');
        Route::get('/participantes', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'listaParticipantes'])->name('participantes');
        Route::get('/clientes', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'clientes'])->name('clientes');
        Route::get('/participante/nota-fiscal/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'notaFiscalDetalhes'])->name('nota-fiscal.detalhes');
        Route::get('/participante/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'participante'])->name('participante');
        Route::get('/consulta/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'consultaDetalhes'])->name('consulta');

        // Novo Participante (formulário manual)
        Route::get('/novo-participante', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'novoParticipante'])->name('novo-participante');
        Route::post('/novo-participante', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'storeParticipante'])->name('novo-participante.store');

        // Acoes
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
        Route::delete('/participantes/bulk-delete', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'bulkExcluirParticipantes'])->name('participantes.bulk-delete');
        Route::delete('/participante/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'excluirParticipante'])->name('participante.excluir');

        // Editar Participante
        Route::get('/participante/{id}/editar', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'editParticipante'])->name('participante.editar');
        Route::put('/participante/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'updateParticipante'])->name('participante.update');

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
        Route::post('/xml/importacao/{id}/salvar-cnpjs', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'salvarCnpjsNovos'])->name('xml.importacao.salvar-cnpjs');
    });

    // BI Analytics
    Route::prefix('app/analytics')->name('app.analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/faturamento', [AnalyticsController::class, 'faturamento'])->name('faturamento');
        Route::get('/compras', [AnalyticsController::class, 'compras'])->name('compras');
        Route::get('/tributos', [AnalyticsController::class, 'tributos'])->name('tributos');
        Route::get('/resumo', [AnalyticsController::class, 'resumo'])->name('resumo');
    });

    // Risk Score
    Route::prefix('app/risk')->name('app.risk.')->group(function () {
        Route::get('/', [RiskScoreController::class, 'index'])->name('index');
        Route::get('/dashboard', [RiskScoreController::class, 'dashboard'])->name('dashboard');
        Route::get('/participante/{id}', [RiskScoreController::class, 'show'])->name('show');
        Route::post('/participante/{id}/consultar', [RiskScoreController::class, 'consultar'])->name('consultar');
        Route::post('/atualizar-lote', [RiskScoreController::class, 'atualizarEmLote'])->name('atualizar-lote');
    });

    // Validacao Contabil Inteligente (VCI)
    Route::prefix('app/validacao')->name('app.validacao.')->group(function () {
        Route::get('/', [ValidacaoController::class, 'index'])->name('index');
        Route::get('/dashboard', [ValidacaoController::class, 'dashboard'])->name('dashboard');
        Route::get('/alertas', [ValidacaoController::class, 'alertas'])->name('alertas');
        Route::get('/nota/{id}', [ValidacaoController::class, 'notaDetalhes'])->name('nota');
        Route::post('/calcular-custo', [ValidacaoController::class, 'calcularCusto'])->name('calcular-custo');
        Route::post('/validar-notas', [ValidacaoController::class, 'validarNotas'])->name('validar-notas');
        Route::post('/validar-importacao/{id}', [ValidacaoController::class, 'validarImportacao'])->name('validar-importacao');
    });

    // Minha Empresa
    Route::prefix('app/minha-empresa')->name('app.minha-empresa.')->group(function () {
        Route::get('/', [MinhaEmpresaController::class, 'index'])->name('index');
        Route::get('/configurar', [MinhaEmpresaController::class, 'configurar'])->name('configurar');
        Route::post('/definir-principal', [MinhaEmpresaController::class, 'definirPrincipal'])->name('definir-principal');
        Route::get('/historico', [MinhaEmpresaController::class, 'historico'])->name('historico');
    });

    // CONSULTAS (nova estrutura unificada - aliases para RAF)
    Route::prefix('app/consultas')->name('app.consultas.')->group(function () {
        // Nova Consulta (alias para RAF consulta)
        Route::get('/nova', [RafConsultaController::class, 'index'])->name('nova');
        Route::get('/nova/participantes', [RafConsultaController::class, 'getParticipantes'])->name('nova.participantes');
        Route::get('/nova/participantes/grupo/{id}', [RafConsultaController::class, 'getParticipantesGrupo'])->name('nova.participantes.grupo');
        Route::post('/nova/calcular-custo', [RafConsultaController::class, 'calcularCusto'])->name('nova.calcular-custo');
        Route::post('/nova/executar', [RafConsultaController::class, 'executar'])->name('nova.executar');
        Route::post('/nova/adicionar-cnpj', [RafConsultaController::class, 'adicionarCnpj'])->name('nova.adicionar-cnpj');
        Route::get('/nova/progresso/stream', [RafConsultaController::class, 'streamProgresso'])->name('nova.progresso.stream');

        // Consulta Avulsa (redirect para Nova Consulta)
        Route::get('/avulso', fn () => redirect('/app/consultas/nova', 301))->name('avulso');

        // Planos Disponiveis
        Route::get('/planos', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'planos'])->name('planos');

        // Historico (alias para RAF historico)
        Route::get('/historico', [RafConsultaController::class, 'historico'])->name('historico');

        // Download de lote
        Route::get('/lote/{id}/baixar', [RafConsultaController::class, 'baixarLote'])->name('lote.baixar');

        // Relatorios (mesmo que historico, mostra downloads disponiveis)
        Route::get('/relatorios', [RafConsultaController::class, 'historico'])->name('relatorios');
    });
});