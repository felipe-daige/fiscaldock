<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\Landing\LandingPageController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ConsultaController;
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

    Route::get('/app/alertas', [DashboardController::class, 'alertas'])->name('app.alertas');

    // Usuário (Placeholder)
    Route::get('/app/configuracoes', [DashboardController::class, 'configuracoes'])->name('app.configuracoes');
    Route::get('/app/plano', [DashboardController::class, 'meuPlano'])->name('app.plano');
    Route::get('/app/checkout/{pacote}', [DashboardController::class, 'checkout'])->name('app.checkout');
    Route::get('/app/creditos', [DashboardController::class, 'creditos'])->name('app.creditos');

    Route::post('/app/sped/upload', SpedUploadController::class)->name('sped.upload');

    // Rotas de créditos
    Route::prefix('app/credits')->name('app.credits.')->group(function () {
        Route::get('/balance', [CreditController::class, 'balance'])->name('balance');
    });

    // Rota de Novo Cliente (formulário de cadastro)
    Route::get('/app/novo-cliente', [DashboardController::class, 'novoCliente'])->name('app.novo.cliente');
    Route::post('/app/novo-cliente', [ClienteController::class, 'store'])->name('app.cliente.store');
    
    // Rota de Clientes
    Route::get('/app/clientes', [DashboardController::class, 'clientes'])->name('app.clientes');
    Route::delete('/app/clientes/bulk-delete', [ClienteController::class, 'bulkDestroy'])->name('app.clientes.bulk-delete');
    Route::get('/app/cliente/{id}/editar', [ClienteController::class, 'edit'])->name('app.cliente.edit');
    Route::put('/app/cliente/{id}', [ClienteController::class, 'update'])->name('app.cliente.update');
    Route::delete('/app/cliente/{id}', [ClienteController::class, 'destroy'])->name('app.cliente.destroy');
    Route::get('/app/cliente/{id}', [DashboardController::class, 'clienteDetalhes'])->name('app.cliente.detalhes');
    
    // Participantes (rotas independentes)
    Route::prefix('app')->name('app.')->group(function () {
        // Lista e ações em massa
        Route::get('/participantes', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'listaParticipantes'])->name('participantes');
        Route::get('/participantes/todos-ids', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'todosIdsParticipantes'])->name('participantes.todos-ids');
        Route::delete('/participantes/bulk-delete', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'bulkExcluirParticipantes'])->name('participantes.bulk-delete');
        Route::post('/participantes/associar-grupo', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'associarGrupo'])->name('participantes.associar-grupo');
        Route::get('/participantes/por-importacao/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'participantesPorImportacao'])->name('participantes.por-importacao');
        Route::post('/participantes/por-ids', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'participantesPorIds'])->name('participantes.por-ids');

        // Participante individual
        Route::get('/participante/nota-fiscal/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'notaFiscalDetalhes'])->name('participante.nota-fiscal');
        Route::get('/participante/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'participante'])->name('participante');
        Route::get('/participante/{id}/editar', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'editParticipante'])->name('participante.editar');
        Route::put('/participante/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'updateParticipante'])->name('participante.update');
        Route::delete('/participante/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'excluirParticipante'])->name('participante.excluir');

        // Novo participante
        Route::get('/novo-participante', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'novoParticipante'])->name('novo-participante');
        Route::post('/novo-participante', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'storeParticipante'])->name('novo-participante.store');
    });

    // Rotas de Monitoramento
    Route::prefix('app/monitoramento')->name('app.monitoramento.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'index'])->name('index');
        Route::get('/planos', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'planos'])->name('planos');
        // Redirect legado: /app/monitoramento/sped -> /app/importacao/sped
        Route::get('/sped', fn () => redirect('/app/importacao/sped', 301))->name('sped.redirect');
        Route::get('/avulso', function () {
            return redirect('/app/consultas/avulso', 301);
        })->name('avulso');
        Route::get('/historico', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'historico'])->name('historico');
        Route::get('/clientes', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'clientes'])->name('clientes');
        Route::get('/consulta/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'consultaDetalhes'])->name('consulta');

        // Acoes
        Route::post('/adicionar-cnpj', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'adicionarCnpj'])->name('adicionar-cnpj');
        Route::post('/consulta-avulsa', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'executarConsultaAvulsa'])->name('consulta-avulsa');

        Route::get('/importacao/stream/{id}', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'streamImportacao'])->name('importacao.stream');

        // SSE para acompanhar resultado de consultas em tempo real
        Route::get('/consulta/stream', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'streamConsultas'])->name('consulta.stream');

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

        // Redirect legado: /app/monitoramento/xml -> /app/importacao/xml
        Route::get('/xml', fn () => redirect('/app/importacao/xml', 301))->name('xml.redirect');
    });

    // Importação (XMLs e SPED)
    Route::prefix('app/importacao')->name('app.importacao.')->group(function () {
        // SPED (EFD Fiscal/Contribuições)
        Route::get('/sped', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'importarSped'])->name('sped');
        Route::post('/sped/importar-txt', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'importarTxt'])->name('sped.importar-txt');
        Route::get('/sped/progresso/stream', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'streamProgresso'])->name('sped.progresso.stream');

        // XMLs (NF-e, NFS-e, CT-e)
        Route::get('/xml', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'index'])->name('xml');
        Route::get('/xml-dev', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'indexDev'])->name('xml.dev');
        Route::post('/xml/validar', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'validar'])->name('xml.validar');
        Route::post('/xml/importar', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'importar'])->name('xml.importar');
        Route::get('/xml/progresso/stream', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'streamProgresso'])->name('xml.progresso.stream');
        Route::get('/xml/importacao/{id}/participantes', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'getParticipantes'])->name('xml.importacao.participantes');
        Route::post('/xml/importacao/{id}/salvar-cnpjs', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'salvarCnpjsNovos'])->name('xml.importacao.salvar-cnpjs');
    });

    // BI Fiscal (placeholder público)
    Route::get('app/analytics', [DashboardController::class, 'analyticsPlaceholder'])->name('app.analytics.index.placeholder');

    // BI Fiscal (funcional - dev only)
    Route::prefix('app/analytics-dev')->name('app.analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/faturamento', [AnalyticsController::class, 'faturamento'])->name('faturamento');
        Route::get('/compras', [AnalyticsController::class, 'compras'])->name('compras');
        Route::get('/tributos', [AnalyticsController::class, 'tributos'])->name('tributos');
        Route::get('/resumo', [AnalyticsController::class, 'resumo'])->name('resumo');
    });

    // Score Fiscal (placeholder público)
    Route::get('app/score-fiscal', [DashboardController::class, 'scoreFiscalPlaceholder'])->name('app.risk.index.placeholder');

    // Score Fiscal (funcional - dev only)
    Route::prefix('app/score-fiscal-dev')->name('app.risk.')->group(function () {
        Route::get('/', [RiskScoreController::class, 'index'])->name('index');
        Route::get('/dashboard', [RiskScoreController::class, 'dashboard'])->name('dashboard');
        Route::get('/participante/{id}', [RiskScoreController::class, 'show'])->name('show');
        Route::post('/participante/{id}/consultar', [RiskScoreController::class, 'consultar'])->name('consultar');
        Route::post('/atualizar-lote', [RiskScoreController::class, 'atualizarEmLote'])->name('atualizar-lote');
    });

    // Redirect legado: /app/risk/* -> /app/score-fiscal/*
    Route::get('app/risk/{any?}', fn ($any = '') => redirect("/app/score-fiscal/{$any}"))->where('any', '.*');

    // Validação Contábil (placeholder público)
    Route::get('app/validacao', [DashboardController::class, 'validacaoPlaceholder'])->name('app.validacao.index.placeholder');

    // Validação Contábil (funcional - dev only)
    Route::prefix('app/validacao-dev')->name('app.validacao.')->group(function () {
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

    // CONSULTAS (estrutura unificada)
    Route::prefix('app/consultas')->name('app.consultas.')->group(function () {
        // Nova Consulta
        Route::get('/nova', [ConsultaController::class, 'index'])->name('nova');
        Route::get('/nova/participantes', [ConsultaController::class, 'getParticipantes'])->name('nova.participantes');
        Route::get('/nova/participantes/grupo/{id}', [ConsultaController::class, 'getParticipantesGrupo'])->name('nova.participantes.grupo');
        Route::post('/nova/calcular-custo', [ConsultaController::class, 'calcularCusto'])->name('nova.calcular-custo');
        Route::post('/nova/executar', [ConsultaController::class, 'executar'])->name('nova.executar');
        Route::post('/nova/adicionar-cnpj', [ConsultaController::class, 'adicionarCnpj'])->name('nova.adicionar-cnpj');
        Route::get('/nova/progresso/stream', [ConsultaController::class, 'streamProgresso'])->name('nova.progresso.stream');
        Route::get('/nova/clientes', [ConsultaController::class, 'getClientes'])->name('nova.clientes');
        Route::post('/nova/participantes-por-clientes', [ConsultaController::class, 'getParticipanteIdsByClientes'])->name('nova.participantes-por-clientes');
        Route::get('/nova/grupos', [ConsultaController::class, 'getGrupos'])->name('nova.grupos');

        // Consulta Avulsa (redirect para Nova Consulta)
        Route::get('/avulso', fn () => redirect('/app/consultas/nova', 301))->name('avulso');

        // Planos Disponiveis
        Route::get('/planos', [\App\Http\Controllers\Dashboard\MonitoramentoController::class, 'planos'])->name('planos');

        // Historico
        Route::get('/historico', [ConsultaController::class, 'historico'])->name('historico');

        // Download de lote
        Route::get('/lote/{id}/baixar', [ConsultaController::class, 'baixarLote'])->name('lote.baixar');

        // Relatorios (mesmo que historico, mostra downloads disponiveis)
        Route::get('/relatorios', [ConsultaController::class, 'historico'])->name('relatorios');
    });
});