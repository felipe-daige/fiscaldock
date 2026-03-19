<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\Landing\LandingPageController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ConsultaController;
use App\Http\Controllers\Dashboard\ClienteController;
use App\Http\Controllers\Dashboard\MinhaEmpresaController;
use App\Http\Controllers\SpedUploadController;
use App\Http\Controllers\Dashboard\BiController;
use App\Http\Controllers\Dashboard\MonitoramentoController;
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
        Route::get('/participantes', [MonitoramentoController::class, 'listaParticipantes'])->name('participantes');
        Route::get('/participantes/todos-ids', [MonitoramentoController::class, 'todosIdsParticipantes'])->name('participantes.todos-ids');
        Route::delete('/participantes/bulk-delete', [MonitoramentoController::class, 'bulkExcluirParticipantes'])->name('participantes.bulk-delete');
        Route::post('/participantes/associar-grupo', [MonitoramentoController::class, 'associarGrupo'])->name('participantes.associar-grupo');
        Route::get('/participantes/por-importacao/{id}', [MonitoramentoController::class, 'participantesPorImportacao'])->name('participantes.por-importacao');
        Route::post('/participantes/por-ids', [MonitoramentoController::class, 'participantesPorIds'])->name('participantes.por-ids');

        // Participante individual
        Route::get('/participante/nota-fiscal/{id}', [MonitoramentoController::class, 'notaFiscalDetalhes'])->name('participante.nota-fiscal');
        Route::get('/participante/{id}', [MonitoramentoController::class, 'participante'])->name('participante');
        Route::get('/participante/{id}/editar', [MonitoramentoController::class, 'editParticipante'])->name('participante.editar');
        Route::put('/participante/{id}', [MonitoramentoController::class, 'updateParticipante'])->name('participante.update');
        Route::delete('/participante/{id}', [MonitoramentoController::class, 'excluirParticipante'])->name('participante.excluir');

        // Novo participante
        Route::get('/novo-participante', [MonitoramentoController::class, 'novoParticipante'])->name('novo-participante');
        Route::post('/novo-participante', [MonitoramentoController::class, 'storeParticipante'])->name('novo-participante.store');
    });

    // Rotas de Monitoramento
    Route::prefix('app/monitoramento')->name('app.monitoramento.')->group(function () {
        Route::get('/', [MonitoramentoController::class, 'index'])->name('index');
        Route::get('/historico', [MonitoramentoController::class, 'historico'])->name('historico');
        Route::get('/clientes', [MonitoramentoController::class, 'clientes'])->name('clientes');
        // SSE para acompanhar resultado de consultas em tempo real
        Route::get('/consulta/stream', [MonitoramentoController::class, 'streamConsultas'])->name('consulta.stream');
        Route::get('/consulta/{id}', [MonitoramentoController::class, 'consultaDetalhes'])->name('consulta');

        // Acoes
        Route::post('/adicionar-cnpj', [MonitoramentoController::class, 'adicionarCnpj'])->name('adicionar-cnpj');

        Route::get('/importacao/stream/{id}', [MonitoramentoController::class, 'streamImportacao'])->name('importacao.stream');

        // Assinaturas
        Route::post('/assinatura', [MonitoramentoController::class, 'criarAssinatura'])->name('assinatura.criar');
        Route::post('/assinatura/{id}/pausar', [MonitoramentoController::class, 'pausarAssinatura'])->name('assinatura.pausar');
        Route::post('/assinatura/{id}/reativar', [MonitoramentoController::class, 'reativarAssinatura'])->name('assinatura.reativar');
        Route::delete('/assinatura/{id}', [MonitoramentoController::class, 'cancelarAssinatura'])->name('assinatura.cancelar');

        // Grupos de participantes
        Route::get('/grupos', [MonitoramentoController::class, 'grupos'])->name('grupos');
        Route::post('/grupos', [MonitoramentoController::class, 'criarGrupo'])->name('grupos.criar');
        Route::put('/grupos/{id}', [MonitoramentoController::class, 'editarGrupo'])->name('grupos.editar');
        Route::delete('/grupos/{id}', [MonitoramentoController::class, 'excluirGrupo'])->name('grupos.excluir');

    });

    // Importação (EFD e XML)
    Route::prefix('app/importacao')->name('app.importacao.')->group(function () {
        // EFD (SPED Fiscal/Contribuições)
        Route::get('/efd', [MonitoramentoController::class, 'importarSped'])->name('efd');
        Route::post('/efd/importar-txt', [MonitoramentoController::class, 'importarTxt'])->name('efd.importar-txt');
        Route::get('/efd/progresso/stream', [MonitoramentoController::class, 'streamProgresso'])->name('efd.progresso.stream');
        Route::get('/efd/notas', [MonitoramentoController::class, 'notasPorIds'])->name('efd.notas.por-ids');
        Route::get('/efd/notas-participante', [MonitoramentoController::class, 'notasPorParticipante'])->name('efd.notas.por-participante');
        Route::get('/efd/{id}', [MonitoramentoController::class, 'efdDetalhes'])->name('efd.detalhes');

        // Histórico unificado
        Route::get('/historico', [MonitoramentoController::class, 'historicoImportacoes'])->name('historico');

        // XML (NF-e, NFS-e, CT-e)
        Route::get('/xml', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'index'])->name('xml');
        Route::get('/xml/{id}', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'show'])->name('xml.detalhes');
        Route::post('/xml/validar', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'validar'])->name('xml.validar');
        Route::post('/xml/importar', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'importar'])->name('xml.importar');
        Route::get('/xml/progresso/stream', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'streamProgresso'])->name('xml.progresso.stream');
        Route::get('/xml/importacao/{id}/participantes', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'getParticipantes'])->name('xml.importacao.participantes');
        Route::post('/xml/importacao/{id}/salvar-cnpjs', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'salvarCnpjsNovos'])->name('xml.importacao.salvar-cnpjs');
    });

    // Business Intelligence
    Route::prefix('app/bi')->name('app.bi.')->group(function () {
        Route::get('/', [BiController::class, 'index'])->name('index');
        // Próximos módulos serão adicionados aqui
    });

    // BI Fiscal (placeholder público)
    Route::get('app/analytics', [DashboardController::class, 'analyticsPlaceholder'])->name('app.analytics.index.placeholder');

    // Score Fiscal (placeholder público)
    Route::get('app/score-fiscal', [DashboardController::class, 'scoreFiscalPlaceholder'])->name('app.risk.index.placeholder');

    // Redirect legado: /app/risk/* -> /app/score-fiscal/*
    Route::get('app/risk/{any?}', fn ($any = '') => redirect("/app/score-fiscal/{$any}"))->where('any', '.*');

    // Validação Contábil (placeholder público)
    Route::get('app/validacao', [DashboardController::class, 'validacaoPlaceholder'])->name('app.validacao.index.placeholder');

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
        Route::get('/planos', [MonitoramentoController::class, 'planos'])->name('planos');

        // Historico
        Route::get('/historico', [ConsultaController::class, 'historico'])->name('historico');

        // Download de lote
        Route::get('/lote/{id}/baixar', [ConsultaController::class, 'baixarLote'])->name('lote.baixar');

        // Status do lote (polling fallback para SSE)
        Route::get('/lote/{id}/status', [ConsultaController::class, 'statusLote'])->name('lote.status');

        // Resultados de um lote (para exibição inline)
        Route::get('/lote/{id}/resultados', [ConsultaController::class, 'resultadosLote'])->name('lote.resultados');

    });
});