<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\Dashboard\BiController;
use App\Http\Controllers\Dashboard\CatalogoController;
use App\Http\Controllers\Dashboard\ClearanceController;
use App\Http\Controllers\Dashboard\ClienteController;
use App\Http\Controllers\Dashboard\ConsultaController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\DashboardNotasFiscaisController;
use App\Http\Controllers\Dashboard\EfdImportacaoController;
use App\Http\Controllers\Dashboard\MinhaEmpresaController;
use App\Http\Controllers\Dashboard\MonitoramentoController;
use App\Http\Controllers\Dashboard\NotaFiscalController;
use App\Http\Controllers\Dashboard\ParticipanteController;
use App\Http\Controllers\Dashboard\ParticipanteGrupoController;
use App\Http\Controllers\Dashboard\ResumoFiscalController;
use App\Http\Controllers\Dashboard\SupportController;
use App\Http\Controllers\Landing\BlogController;
use App\Http\Controllers\Landing\LandingPageController;
use App\Http\Controllers\Landing\SitemapController;
use App\Http\Middleware\RequiresEntitlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'inicio'])->name('home');

Route::get('/inicio', [LandingPageController::class, 'inicio'])->name('inicio');
Route::get('/solucoes', [LandingPageController::class, 'solucoes'])->name('solucoes');
Route::get('/precos', [LandingPageController::class, 'precos'])->name('precos');
Route::get('/duvidas', [LandingPageController::class, 'duvidas'])->name('duvidas');
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/efd', [BlogController::class, 'topicEfd'])->name('blog.efd');
Route::get('/blog/tema/{tema}', [BlogController::class, 'topic'])->name('blog.tema');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.post');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/llms.txt', function () {
    return response(view('landing_page.llms')->render(), 200, [
        'Content-Type' => 'text/plain; charset=utf-8',
    ]);
})->name('llms');

Route::get('/clearance/{any?}', function ($any = '') {
    $path = trim((string) $any, '/');

    if ($path === '') {
        return redirect('/app/clearance/dashboard', 301);
    }

    return redirect('/app/clearance/'.$path, 301);
})->where('any', '.*');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::get('/criar-conta', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/criar-conta', [AuthController::class, 'signup'])->middleware('throttle:5,10')->name('signup.post');
Route::get('/agendar', [AuthController::class, 'showAgendar'])->name('agendar');
Route::post('/agendar', [AuthController::class, 'agendar'])->name('agendar.post');
Route::get('/termos', [AuthController::class, 'showTerms'])->name('termos');
Route::get('/privacidade', [AuthController::class, 'showPrivacy'])->name('privacidade');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/esqueci-senha', [PasswordResetController::class, 'mostrarFormularioEsqueciSenha'])->name('password.forgot');
Route::post('/esqueci-senha', [PasswordResetController::class, 'enviarLinkReset'])->middleware('throttle:5,10')->name('password.forgot.post');
Route::get('/redefinir-senha/{token}', [PasswordResetController::class, 'mostrarFormularioReset'])->name('password.reset');
Route::post('/redefinir-senha', [PasswordResetController::class, 'resetar'])->middleware('throttle:5,10')->name('password.reset.post');

// Verificação e troca de e-mail — os dois links são ASSINADOS e públicos de propósito:
// a posse do link (entregue na caixa alvo) é a prova; exigir sessão só quebraria quem
// abre o e-mail em outro navegador.
Route::get('/email/verificar/{id}/{hash}', [EmailVerificationController::class, 'verificar'])
    ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
Route::get('/perfil/email/confirmar/{user}/{hash}', [EmailVerificationController::class, 'confirmarTroca'])
    ->middleware(['signed', 'throttle:6,1'])->name('perfil.email.confirmar');

Route::post('/lead/banner-contato', [LandingPageController::class, 'capturarLead'])
    ->name('landing.lead.banner');

// Endpoint para obter CSRF token atualizado (usado pelo SPA)
Route::get('/api/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('api.csrf-token');

// Rotas autenticadas
// RequireCurrentTerms por FQCN (bootstrap/app.php não é montado): força re-aceite (LGPD 2.2)
// quando a versão dos documentos sobe. Só intercepta GET full-page; o interstitial app.reaceite.*
// é isento via routeIs (sem loop).
Route::middleware(['auth', \App\Http\Middleware\EnsureNaoBloqueado::class, \App\Http\Middleware\RequireCurrentTerms::class, \App\Http\Middleware\ImpersonacaoReadOnly::class])->group(function () {
    Route::get('/app/reaceite', [\App\Http\Controllers\Dashboard\TermosReaceiteController::class, 'show'])->name('app.reaceite.show');
    Route::post('/app/reaceite', [\App\Http\Controllers\Dashboard\TermosReaceiteController::class, 'aceitar'])->name('app.reaceite.aceitar');

    // Onboarding pós-cadastro: reconfirmação dos termos no modal de boas-vindas.
    Route::post('/app/onboarding/confirmar-termos', [\App\Http\Controllers\Dashboard\OnboardingController::class, 'confirmarTermos'])->name('app.onboarding.confirmar-termos');

    Route::get('/app/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/app/dashboard/dados', [DashboardController::class, 'dados'])->name('app.dashboard.dados');
    Route::post('/app/dashboard/prefs', [DashboardController::class, 'salvarPrefs'])->name('app.dashboard.prefs');
    Route::get('/app/perfil', [DashboardController::class, 'perfil'])->name('app.perfil');
    Route::patch('/app/perfil', [DashboardController::class, 'atualizarPerfil'])->name('app.perfil.update');
    Route::put('/app/perfil/senha', [DashboardController::class, 'atualizarSenha'])->name('app.perfil.senha.update');
    Route::patch('/app/perfil/email', [EmailVerificationController::class, 'solicitarTroca'])->middleware('throttle:5,10')->name('app.perfil.email.trocar');
    Route::delete('/app/perfil/email', [EmailVerificationController::class, 'cancelarTroca'])->name('app.perfil.email.cancelar');
    Route::post('/app/perfil/email/reenviar', [EmailVerificationController::class, 'reenviar'])->middleware('throttle:5,10')->name('verification.send');

    Route::get('/app/alertas', [DashboardController::class, 'alertas'])->name('app.alertas');
    Route::get('/app/alertas/dados', [DashboardController::class, 'alertasDados'])->name('app.alertas.dados');
    Route::get('/app/alertas/resumo', [DashboardController::class, 'alertasResumo'])->name('app.alertas.resumo');
    Route::get('/app/alertas/evolucao', [DashboardController::class, 'alertasEvolucao'])->name('app.alertas.evolucao');
    Route::post('/app/alertas/{id}/status', [DashboardController::class, 'alertasMarcarStatus'])->name('app.alertas.status');
    Route::post('/app/alertas/status-lote', [DashboardController::class, 'alertasMarcarStatusLote'])->name('app.alertas.status-lote');
    Route::post('/app/alertas/recalcular', [DashboardController::class, 'alertasRecalcular'])->name('app.alertas.recalcular');
    Route::get('/app/alertas/exportar-pdf', [DashboardController::class, 'alertasExportarPdf'])->middleware(RequiresEntitlement::class.':export')->name('app.alertas.exportar');
    Route::get('/app/alertas/exportar-csv', [DashboardController::class, 'alertasExportarCsv'])->middleware(RequiresEntitlement::class.':export,csv')->name('app.alertas.exportar-csv');
    Route::get('/app/alertas/exportar-xlsx', [DashboardController::class, 'alertasExportarXlsx'])->middleware(RequiresEntitlement::class.':export,excel')->name('app.alertas.exportar-xlsx');
    Route::get('/app/alertas/historico', [DashboardController::class, 'alertasHistorico'])->name('app.alertas.historico');
    Route::get('/app/alertas/{id}', [DashboardController::class, 'alertaDetalhes'])->whereNumber('id')->name('app.alertas.show');

    // Status das integrações (read-only, qualquer usuário autenticado)
    Route::get('/app/status', [\App\Http\Controllers\Dashboard\StatusController::class, 'index'])->name('app.status.index');

    // Usuário (Placeholder)
    Route::get('/app/configuracoes', [DashboardController::class, 'configuracoes'])->name('app.configuracoes');
    Route::patch('/app/configuracoes/notificacoes', [DashboardController::class, 'atualizarNotificacaoConfiguracao'])->name('app.configuracoes.notificacoes.update');
    Route::get('/app/faixa-comercial', [DashboardController::class, 'meuPlano'])->name('app.faixa-comercial');
    Route::get('/app/planos', [DashboardController::class, 'planos'])->name('app.planos');
    Route::get('/app/checkout/{pacote}', [DashboardController::class, 'checkout'])->name('app.checkout');
    Route::get('/app/creditos', [DashboardController::class, 'creditos'])->name('app.creditos');

    // Mercado Pago — cria o pagamento do pacote (front Bricks envia o meio de pagamento).
    Route::post('/app/pagamento/mercado-pago', [\App\Http\Controllers\Dashboard\PagamentoMercadoPagoController::class, 'criar'])
        ->name('app.pagamento.mercadopago.criar');

    // Assinatura recorrente (preapproval Mercado Pago) — front envia o card_token do Brick.
    Route::post('/app/assinatura', [\App\Http\Controllers\Dashboard\AssinaturaController::class, 'criar'])
        ->name('app.assinatura.criar');
    Route::post('/app/assinatura/trocar', [\App\Http\Controllers\Dashboard\AssinaturaController::class, 'trocar'])
        ->name('app.assinatura.trocar');
    Route::post('/app/assinatura/cancelar', [\App\Http\Controllers\Dashboard\AssinaturaController::class, 'cancelar'])
        ->name('app.assinatura.cancelar');

    // Recarga automática por tempo (preapproval recorrente de um pacote de créditos).
    Route::post('/app/recarga-automatica', [\App\Http\Controllers\Dashboard\RecargaController::class, 'criar'])
        ->name('app.recarga.criar');
    Route::post('/app/recarga-automatica/saldo', [\App\Http\Controllers\Dashboard\RecargaController::class, 'criarPorSaldo'])
        ->name('app.recarga.criar-saldo');
    Route::post('/app/recarga-automatica/cancelar', [\App\Http\Controllers\Dashboard\RecargaController::class, 'cancelar'])
        ->name('app.recarga.cancelar');

    // Rotas de créditos
    Route::prefix('app/credits')->name('app.credits.')->group(function () {
        Route::get('/balance', [CreditController::class, 'balance'])->name('balance');
    });

    // Rota de Novo Cliente (formulário de cadastro)
    Route::redirect('/app/novo-cliente', '/app/cliente/novo');
    Route::get('/app/cliente/novo', [DashboardController::class, 'novoCliente'])->name('app.cliente.novo');
    Route::post('/app/cliente/novo', [ClienteController::class, 'store'])->name('app.cliente.store');

    // Rota de Clientes
    Route::get('/app/clientes', [DashboardController::class, 'clientes'])->name('app.clientes');
    Route::get('/app/clientes/todos-ids', [ClienteController::class, 'todosIds'])->name('app.clientes.todos-ids');
    Route::delete('/app/clientes/bulk-delete', [ClienteController::class, 'bulkDestroy'])->name('app.clientes.bulk-delete');
    Route::post('/app/clientes/dossie-lote', [ClienteController::class, 'dossieLote'])->name('app.clientes.dossie-lote');
    Route::post('/app/clientes/exportar-pdf', [ClienteController::class, 'exportarPdf'])->middleware(RequiresEntitlement::class.':export')->name('app.clientes.exportar-pdf');
    Route::post('/app/clientes/exportar-xlsx', [ClienteController::class, 'exportarXlsx'])->middleware(RequiresEntitlement::class.':export,excel')->name('app.clientes.exportar-xlsx');
    Route::post('/app/clientes/exportar-csv', [ClienteController::class, 'exportarCsv'])->middleware(RequiresEntitlement::class.':export,csv')->name('app.clientes.exportar-csv');
    Route::get('/app/cliente/{id}/editar', [ClienteController::class, 'edit'])->name('app.cliente.edit');
    Route::put('/app/cliente/{id}', [ClienteController::class, 'update'])->name('app.cliente.update');
    Route::delete('/app/cliente/{id}', [ClienteController::class, 'destroy'])->name('app.cliente.destroy');
    Route::get('/app/cliente/{id}/notas', [DashboardController::class, 'clienteNotas'])->name('app.cliente.notas');
    Route::get('/app/cliente/{id}/participantes', [DashboardController::class, 'clienteParticipantes'])->name('app.cliente.participantes');
    Route::get('/app/cliente/{id}/dossie', [DashboardController::class, 'clienteDossiePdf'])->name('app.cliente.dossie');
    Route::get('/app/cliente/{id}', [DashboardController::class, 'clienteDetalhes'])->name('app.cliente.detalhes');

    // Participantes (rotas independentes)
    Route::prefix('app')->name('app.')->group(function () {
        // Lista e ações em massa
        Route::get('/participantes', [ParticipanteController::class, 'index'])->name('participantes');
        Route::get('/participantes/todos-ids', [ParticipanteController::class, 'todosIds'])->name('participantes.todos-ids');
        Route::delete('/participantes/bulk-delete', [ParticipanteController::class, 'bulkExcluir'])->name('participantes.bulk-delete');
        Route::post('/participantes/dossie-lote', [ParticipanteController::class, 'dossieLote'])->name('participantes.dossie-lote');
        Route::post('/participantes/exportar-pdf', [ParticipanteController::class, 'exportarPdf'])->middleware(RequiresEntitlement::class.':export')->name('participantes.exportar-pdf');
        Route::post('/participantes/exportar-xlsx', [ParticipanteController::class, 'exportarXlsx'])->middleware(RequiresEntitlement::class.':export,excel')->name('participantes.exportar-xlsx');
        Route::post('/participantes/exportar-csv', [ParticipanteController::class, 'exportarCsv'])->middleware(RequiresEntitlement::class.':export,csv')->name('participantes.exportar-csv');
        Route::post('/participantes/associar-grupo', [ParticipanteGrupoController::class, 'associar'])->name('participantes.associar-grupo');
        Route::get('/participantes/por-importacao/{id}', [ParticipanteController::class, 'porImportacao'])->name('participantes.por-importacao');
        Route::post('/participantes/por-ids', [ParticipanteController::class, 'porIds'])->name('participantes.por-ids');

        // Novo participante
        Route::redirect('/novo-participante', '/app/participante/novo');
        Route::get('/participante/novo', [ParticipanteController::class, 'create'])->name('participante.novo');
        Route::post('/participante/novo', [ParticipanteController::class, 'store'])->name('participante.novo.store');

        // Participante individual
        Route::get('/participante/nota-fiscal/{id}', [ParticipanteController::class, 'notaFiscalDetalhes'])->name('participante.nota-fiscal');
        Route::get('/participante/{id}/notas', [ParticipanteController::class, 'notas'])->name('participante.notas');
        Route::get('/participante/{id}/dossie', [ParticipanteController::class, 'dossiePdf'])
            ->middleware(\App\Http\Middleware\RequiresEntitlement::class.':export')
            ->name('participante.dossie');
        Route::get('/participante/{id}', [ParticipanteController::class, 'show'])->name('participante');
        Route::get('/participante/{id}/editar', [ParticipanteController::class, 'edit'])->name('participante.editar');
        Route::put('/participante/{id}', [ParticipanteController::class, 'update'])->name('participante.update');
        Route::delete('/participante/{id}', [ParticipanteController::class, 'destroy'])->name('participante.excluir');
    });

    // Rotas de Monitoramento
    Route::prefix('app/monitoramento')->name('app.monitoramento.')->group(function () {

        // Painel: gestão dos monitorados (consulta contínua) + grupos embutidos (2026-07-03)
        Route::get('/painel', [MonitoramentoController::class, 'painel'])->name('painel');

        Route::get('/historico', [MonitoramentoController::class, 'historico'])->name('historico');
        // View de "clientes" removida (2026-07-04): a trava de consumo migrou pro painel
        // e o histórico é alcançado por botão lá. GET antigo redireciona pra não quebrar bookmark.
        Route::get('/clientes', fn () => redirect()->route('app.monitoramento.painel', [], 301))->name('clientes');
        // SSE para acompanhar resultado de consultas em tempo real
        Route::get('/consulta/stream', [MonitoramentoController::class, 'streamConsultas'])->name('consulta.stream');
        Route::get('/consulta/{id}', [MonitoramentoController::class, 'consultaDetalhes'])->name('consulta');

        // Acoes
        Route::post('/adicionar-cnpj', [MonitoramentoController::class, 'adicionarCnpj'])->name('adicionar-cnpj');

        Route::get('/importacao/stream/{id}', [EfdImportacaoController::class, 'streamImportacao'])->name('importacao.stream');

        // Freio de consumo do auto-monitor (§6.2) — o contador define o teto de gasto por ciclo
        Route::post('/limite-consumo', [MonitoramentoController::class, 'definirLimiteConsumo'])->name('limite-consumo');

        // Reconciliação de downgrade: usuário escolhe quais CNPJs manter quando o tier novo
        // comporta menos monitorados que os ativos (excedente vira pausa automática).
        Route::post('/reconciliar-limite', [MonitoramentoController::class, 'reconciliarLimite'])->name('reconciliar-limite');

        // Assinaturas
        Route::post('/assinatura', [MonitoramentoController::class, 'criarAssinatura'])->name('assinatura.criar');
        Route::post('/assinatura/{id}/pausar', [MonitoramentoController::class, 'pausarAssinatura'])->name('assinatura.pausar');
        Route::post('/assinatura/{id}/reativar', [MonitoramentoController::class, 'reativarAssinatura'])->name('assinatura.reativar');
        Route::delete('/assinatura/{id}', [MonitoramentoController::class, 'cancelarAssinatura'])->name('assinatura.cancelar');

        // Grupos de participantes — a gestão migrou pro painel (2026-07-03). GET antigo
        // redireciona; o CRUD (POST/PUT/DELETE + participantes) fica: o painel o consome.
        Route::get('/grupos', fn () => redirect()->route('app.monitoramento.painel', [], 301))->name('grupos');
        Route::post('/grupos', [ParticipanteGrupoController::class, 'store'])->name('grupos.criar');
        Route::get('/grupos/{id}/participantes', [ParticipanteGrupoController::class, 'participantes'])->name('grupos.participantes');
        Route::put('/grupos/{id}', [ParticipanteGrupoController::class, 'update'])->name('grupos.editar');
        Route::delete('/grupos/{id}', [ParticipanteGrupoController::class, 'destroy'])->name('grupos.excluir');

    });

    // Importação (EFD e XML)
    Route::prefix('app/importacao')->name('app.importacao.')->group(function () {
        // EFD (SPED Fiscal/Contribuições)
        Route::get('/efd', [EfdImportacaoController::class, 'index'])->name('efd');
        Route::post('/efd/importar-txt', [EfdImportacaoController::class, 'upload'])->name('efd.importar-txt');
        Route::get('/efd/progresso/stream', [EfdImportacaoController::class, 'streamProgresso'])->name('efd.progresso.stream');
        Route::get('/efd/notas', [EfdImportacaoController::class, 'notasPorIds'])->name('efd.notas.por-ids');
        Route::get('/efd/notas-participante', [EfdImportacaoController::class, 'notasPorParticipante'])->name('efd.notas.por-participante');
        Route::get('/efd/{id}', [EfdImportacaoController::class, 'show'])->name('efd.detalhes');
        Route::get('/efd/{id}/exportar', [EfdImportacaoController::class, 'exportar'])->middleware(RequiresEntitlement::class.':export,csv')->name('efd.exportar');
        Route::get('/efd/{id}/preview-exclusao', [EfdImportacaoController::class, 'previewExclusao'])->name('efd.preview-exclusao');
        Route::delete('/efd/{id}', [EfdImportacaoController::class, 'destroy'])->name('efd.destroy');

        // Histórico unificado
        Route::get('/historico', [EfdImportacaoController::class, 'historico'])->name('historico');

        // XML (NF-e, NFS-e, CT-e)
        Route::get('/xml', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'index'])->name('xml');
        Route::get('/xml/{id}', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'show'])->name('xml.detalhes');
        Route::get('/xml/{id}/preview-exclusao', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'previewExclusao'])->name('xml.preview-exclusao');
        Route::delete('/xml/{id}', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'destroy'])->name('xml.destroy');
        Route::post('/xml/{id}/definir-cliente', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'definirCliente'])->name('xml.definir-cliente');
        Route::post('/xml/{id}/definir-cliente-documento', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'definirClientePorDocumento'])->name('xml.definir-cliente-documento');
        Route::post('/xml/validar', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'validar'])->name('xml.validar');
        Route::post('/xml/importar', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'importar'])->name('xml.importar');
        Route::get('/xml/progresso/stream', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'streamProgresso'])->name('xml.progresso.stream');
        Route::get('/xml/importacao/{id}/participantes', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'getParticipantes'])->name('xml.importacao.participantes');
        Route::post('/xml/importacao/{id}/salvar-cnpjs', [\App\Http\Controllers\Dashboard\XmlImportacaoController::class, 'salvarCnpjsNovos'])->name('xml.importacao.salvar-cnpjs');
    });

    // Notas Fiscais (listagem unificada EFD + XML)
    Route::get('app/notas', [NotaFiscalController::class, 'index'])->name('app.notas.index');
    Route::get('app/notas/{origem}/{id}', [NotaFiscalController::class, 'detalhes'])
        ->name('app.notas.detalhes')
        ->where('origem', 'efd|xml');

    // Dashboard de Notas Fiscais
    Route::get('app/notas/dashboard', [DashboardNotasFiscaisController::class, 'index'])->name('app.notas.dashboard');
    Route::get('app/notas/dashboard/visao-geral', [DashboardNotasFiscaisController::class, 'visaoGeral'])->name('app.notas.dashboard.visao-geral');
    Route::get('app/notas/dashboard/cfop', [DashboardNotasFiscaisController::class, 'cfop'])->name('app.notas.dashboard.cfop');
    Route::get('app/notas/dashboard/participantes', [DashboardNotasFiscaisController::class, 'participantes'])->name('app.notas.dashboard.participantes');
    Route::get('app/notas/dashboard/tributario', [DashboardNotasFiscaisController::class, 'tributario'])->name('app.notas.dashboard.tributario');
    Route::get('app/notas/dashboard/alertas', [DashboardNotasFiscaisController::class, 'alertas'])->name('app.notas.dashboard.alertas');
    Route::get('app/notas/dashboard/compliance', [DashboardNotasFiscaisController::class, 'compliance'])->name('app.notas.dashboard.compliance');
    Route::get('app/notas/dashboard/exportar-pdf', [DashboardNotasFiscaisController::class, 'exportarPdf'])
        ->middleware(RequiresEntitlement::class.':export')->name('app.notas.dashboard.exportar-pdf');
    Route::get('app/notas/dashboard/exportar-xlsx', [DashboardNotasFiscaisController::class, 'exportarXlsx'])
        ->middleware(RequiresEntitlement::class.':export,excel')->name('app.notas.dashboard.exportar-xlsx');
    Route::get('app/notas/dashboard/exportar-csv-zip', [DashboardNotasFiscaisController::class, 'exportarCsvZip'])
        ->middleware(RequiresEntitlement::class.':export,csv')->name('app.notas.dashboard.exportar-csv-zip');

    // Painel Fiscal por Competência
    Route::get('app/resumo-fiscal', [ResumoFiscalController::class, 'index'])->name('app.resumo-fiscal');
    Route::get('app/resumo-fiscal/competencias', [ResumoFiscalController::class, 'competencias'])->name('app.resumo-fiscal.competencias');
    Route::get('app/resumo-fiscal/resumo-executivo', [ResumoFiscalController::class, 'resumoExecutivo'])->name('app.resumo-fiscal.resumo-executivo');
    Route::get('app/resumo-fiscal/a-recolher', [ResumoFiscalController::class, 'aRecolher'])->name('app.resumo-fiscal.a-recolher');
    Route::get('app/resumo-fiscal/exportar', [ResumoFiscalController::class, 'exportar'])
        ->middleware(RequiresEntitlement::class.':export,csv')->name('app.resumo-fiscal.exportar');
    Route::get('app/resumo-fiscal/exportar-pdf', [ResumoFiscalController::class, 'exportarPdf'])
        ->middleware(RequiresEntitlement::class.':export')->name('app.resumo-fiscal.exportar-pdf');
    Route::get('app/resumo-fiscal/exportar-xlsx', [ResumoFiscalController::class, 'exportarXlsx'])
        ->middleware(RequiresEntitlement::class.':export,excel')->name('app.resumo-fiscal.exportar-xlsx');
    Route::get('app/resumo-fiscal/apuracao-icms', [ResumoFiscalController::class, 'apuracaoIcms'])->middleware(RequiresEntitlement::class.':bi_completo')->name('app.resumo-fiscal.apuracao-icms');
    Route::get('app/resumo-fiscal/apuracao-pis-cofins', [ResumoFiscalController::class, 'apuracaoPisCofins'])->middleware(RequiresEntitlement::class.':bi_completo')->name('app.resumo-fiscal.apuracao-pis-cofins');
    Route::get('app/resumo-fiscal/retencoes', [ResumoFiscalController::class, 'retencoesFonte'])->middleware(RequiresEntitlement::class.':bi_completo')->name('app.resumo-fiscal.retencoes');
    Route::get('app/resumo-fiscal/cruzamentos', [ResumoFiscalController::class, 'cruzamentos'])->middleware(RequiresEntitlement::class.':bi_completo')->name('app.resumo-fiscal.cruzamentos');
    Route::get('app/resumo-fiscal/alertas', [ResumoFiscalController::class, 'alertasFiscais'])->middleware(RequiresEntitlement::class.':bi_completo')->name('app.resumo-fiscal.alertas');

    // BI Fiscal
    Route::get('app/bi/dashboard', [BiController::class, 'index'])->name('app.bi.index');
    Route::prefix('app/bi')->name('app.bi.')->group(function () {
        Route::get('/faturamento', [BiController::class, 'faturamento'])->name('faturamento');
        Route::get('/compras', [BiController::class, 'compras'])->name('compras');
        Route::get('/tributos', [BiController::class, 'tributos'])->middleware(RequiresEntitlement::class.':bi_completo')->name('tributos');
        Route::get('/resumo', [BiController::class, 'resumo'])->name('resumo');
        Route::get('/efd', [BiController::class, 'efd'])->name('efd');
        Route::get('/participantes', [BiController::class, 'participantes'])->name('participantes');
        Route::get('/participantes/{id}/ficha', [BiController::class, 'fichaParticipante'])->name('participantes.ficha');
        Route::get('/riscos', [BiController::class, 'riscos'])->middleware(RequiresEntitlement::class.':bi_completo')->name('riscos');
        Route::get('/tributario-efd', [BiController::class, 'tributarioEfd'])->middleware(RequiresEntitlement::class.':bi_completo')->name('tributario-efd');
        Route::get('/apuracao-notas', [BiController::class, 'apuracaoNotas'])->middleware(RequiresEntitlement::class.':bi_completo')->name('apuracao-notas');
        Route::get('/cfop', [BiController::class, 'cfop'])->middleware(RequiresEntitlement::class.':bi_completo')->name('cfop');
        Route::get('/exportar', [BiController::class, 'exportar'])
            ->middleware(RequiresEntitlement::class.':export,csv')->name('exportar');
        Route::get('/exportar-xlsx', [BiController::class, 'exportarXlsx'])
            ->middleware(RequiresEntitlement::class.':export,excel')->name('exportar-xlsx');
        Route::get('/exportar-pdf', [BiController::class, 'exportarPdf'])
            ->middleware(RequiresEntitlement::class.':export')->name('exportar-pdf');
        Route::get('/exportar-csv-zip', [BiController::class, 'exportarCsvZip'])
            ->middleware(RequiresEntitlement::class.':export,csv')->name('exportar-csv-zip');
        Route::get('/catalogo-itens', [\App\Http\Controllers\Dashboard\BiCatalogoItensController::class, 'index'])->name('catalogo-itens');
        Route::get('/catalogo-itens/exportar', [\App\Http\Controllers\Dashboard\BiCatalogoItensController::class, 'exportarCsv'])
            ->middleware(RequiresEntitlement::class.':export,csv')->name('catalogo-itens.exportar');
        Route::get('/catalogo-itens/exportar-pdf', [\App\Http\Controllers\Dashboard\BiCatalogoItensController::class, 'exportarPdf'])
            ->middleware(RequiresEntitlement::class.':export')->name('catalogo-itens.exportar-pdf');
        Route::get('/catalogo-itens/exportar-xlsx', [\App\Http\Controllers\Dashboard\BiCatalogoItensController::class, 'exportarXlsx'])
            ->middleware(RequiresEntitlement::class.':export,excel')->name('catalogo-itens.exportar-xlsx');
        Route::post('/catalogo-itens/alerta/descartar', [\App\Http\Controllers\Dashboard\BiCatalogoItensController::class, 'descartarAlerta'])->middleware(RequiresEntitlement::class.':bi_completo')->name('catalogo-itens.descartar');
        Route::post('/catalogo-itens/alerta/restaurar', [\App\Http\Controllers\Dashboard\BiCatalogoItensController::class, 'restaurarAlerta'])->middleware(RequiresEntitlement::class.':bi_completo')->name('catalogo-itens.restaurar');
        Route::get('/cruzamentos', [\App\Http\Controllers\Dashboard\BiCruzamentosController::class, 'index'])->name('cruzamentos');
        Route::get('/cruzamentos/exportar-pdf', [\App\Http\Controllers\Dashboard\BiCruzamentosController::class, 'exportarPdf'])
            ->middleware([RequiresEntitlement::class.':bi_completo', RequiresEntitlement::class.':export'])->name('cruzamentos.exportar-pdf');
        Route::get('/cruzamentos/fornecedor/{participante}/notas', [\App\Http\Controllers\Dashboard\BiCruzamentosController::class, 'fornecedorNotas'])->whereNumber('participante')->name('cruzamentos.fornecedor-notas');
    });

    // Score Fiscal (Score de Regularidade) — alimentado pelos scores persistidos a cada lote de consulta
    Route::get('app/score-fiscal', [\App\Http\Controllers\Dashboard\RiskScoreController::class, 'index'])->name('app.risk.index');
    Route::get('app/score-fiscal/exportar-pdf', [\App\Http\Controllers\Dashboard\RiskScoreController::class, 'exportarPdf'])
        ->middleware(RequiresEntitlement::class.':export')->name('app.risk.exportar-pdf');
    Route::get('app/score-fiscal/exportar-xlsx', [\App\Http\Controllers\Dashboard\RiskScoreController::class, 'exportarXlsx'])
        ->middleware(RequiresEntitlement::class.':export,excel')->name('app.risk.exportar-xlsx');
    Route::get('app/score-fiscal/exportar-csv', [\App\Http\Controllers\Dashboard\RiskScoreController::class, 'exportarCsv'])
        ->middleware(RequiresEntitlement::class.':export,csv')->name('app.risk.exportar-csv');
    Route::get('app/score-fiscal/participante/{id}', [\App\Http\Controllers\Dashboard\RiskScoreController::class, 'show'])->whereNumber('id')->name('app.risk.show');
    Route::get('app/score-fiscal/participante/{id}/detalhe', [\App\Http\Controllers\Dashboard\RiskScoreController::class, 'detalheParticipante'])->whereNumber('id')->name('app.risk.detalhe');
    Route::get('app/score-fiscal/cliente/{id}/detalhe', [\App\Http\Controllers\Dashboard\RiskScoreController::class, 'detalheCliente'])->whereNumber('id')->name('app.risk.cliente-detalhe');

    // Redirect legado: /app/risk/* -> /app/score-fiscal/*
    Route::get('app/risk/{any?}', fn ($any = '') => redirect("/app/score-fiscal/{$any}"))->where('any', '.*');

    // Clearance DF-e
    Route::prefix('app/clearance')->name('app.clearance.')->group(function () {
        Route::redirect('/', '/app/clearance/dashboard', 301);
        Route::get('/dashboard', [ClearanceController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/exportar-pdf', [ClearanceController::class, 'exportarDashboardPdf'])
            ->middleware(RequiresEntitlement::class.':export')->name('dashboard.exportar-pdf');
        Route::get('/dashboard/exportar-xlsx', [ClearanceController::class, 'exportarDashboardXlsx'])
            ->middleware(RequiresEntitlement::class.':export,excel')->name('dashboard.exportar-xlsx');
        Route::get('/dashboard/exportar-csv-zip', [ClearanceController::class, 'exportarDashboardCsvZip'])
            ->middleware(RequiresEntitlement::class.':export,csv')->name('dashboard.exportar-csv-zip');
        Route::get('/notas', [ClearanceController::class, 'notas'])->name('notas');
        Route::get('/notas/todos-ids', [ClearanceController::class, 'todosIds'])->name('todos-ids');
        Route::post('/notas/validar', [ClearanceController::class, 'validarNotas'])
            ->middleware(RequiresEntitlement::class.':clearance_lote')->name('validar');
        Route::get('/notas/resultado/{consultaLoteId}', [ClearanceController::class, 'resultadoNotas'])->name('notas.resultado');
        Route::get('/notas/resultado/{consultaLoteId}/pdf', [ClearanceController::class, 'resultadoPdf'])
            ->middleware(RequiresEntitlement::class.':export')->name('notas.resultado-pdf');
        Route::get('/notas/resultado/{consultaLoteId}/xlsx', [ClearanceController::class, 'resultadoXlsx'])
            ->middleware(RequiresEntitlement::class.':export,excel')->name('notas.resultado-xlsx');
        Route::post('/sintegra/preview', [ClearanceController::class, 'sintegraPreview'])->name('sintegra.preview');
        Route::post('/sintegra/executar', [ClearanceController::class, 'sintegraExecutar'])->name('sintegra.executar');
        Route::post('/sintegra/status', [ClearanceController::class, 'sintegraStatus'])->name('sintegra.status');
        Route::get('/buscar', [ClearanceController::class, 'buscarNfe'])->name('buscar');
        Route::post('/buscar/precheck', [ClearanceController::class, 'buscarPrecheck'])->name('buscar.precheck');
        Route::post('/buscar/classificar-partes', [ClearanceController::class, 'classificarPartesBusca'])->name('buscar.classificar-partes');
        Route::post('/buscar/consultar', [ClearanceController::class, 'consultarNfe'])->name('buscar.consultar');
        Route::get('/buscar/resultado/{consultaLoteId}', [ClearanceController::class, 'resultadoUltimaConsulta'])->name('buscar.resultado');
        Route::get('/buscar/resultado/{consultaLoteId}/pdf', [ClearanceController::class, 'buscaResultadoPdf'])
            ->middleware(RequiresEntitlement::class.':export')->name('buscar.resultado-pdf');
        Route::get('/buscar/resultado/{consultaLoteId}/xlsx', [ClearanceController::class, 'buscaResultadoXlsx'])
            ->middleware(RequiresEntitlement::class.':export,excel')->name('buscar.resultado-xlsx');
        Route::get('/buscar/resultado/{consultaLoteId}/csv', [ClearanceController::class, 'buscaResultadoCsv'])
            ->middleware(RequiresEntitlement::class.':export,csv')->name('buscar.resultado-csv');
        Route::get('/buscar/resultado-local/{token}', [ClearanceController::class, 'resultadoBuscaLocal'])->name('buscar.resultado-local');
        Route::post('/importacao/{id}/validar', [ClearanceController::class, 'validarImportacao'])
            ->middleware(RequiresEntitlement::class.':clearance_lote')->name('validar-importacao');
        Route::post('/calcular-custo', [ClearanceController::class, 'calcularCusto'])->name('calcular-custo');
        Route::get('/nota/{id}', [ClearanceController::class, 'notaDetalhes'])->name('nota');
        Route::get('/alertas', [ClearanceController::class, 'alertas'])->name('alertas');
    });

    // Catálogo de Produtos/Serviços
    Route::get('app/catalogo', [CatalogoController::class, 'index'])->name('app.catalogo.index');
    Route::get('app/catalogo/exportar-pdf', [CatalogoController::class, 'exportarPdf'])
        ->middleware(RequiresEntitlement::class.':export')->name('app.catalogo.exportar-pdf');
    Route::get('app/catalogo/exportar-xlsx', [CatalogoController::class, 'exportarXlsx'])
        ->middleware(RequiresEntitlement::class.':export,excel')->name('app.catalogo.exportar-xlsx');
    Route::get('app/catalogo/exportar-csv-zip', [CatalogoController::class, 'exportarCsvZip'])
        ->middleware(RequiresEntitlement::class.':export,csv')->name('app.catalogo.exportar-csv-zip');
    Route::get('app/catalogo/historico/{codItem}', [CatalogoController::class, 'historico'])
        ->where('codItem', '.*')->name('app.catalogo.historico');

    // Suporte
    Route::get('/app/suporte', [SupportController::class, 'index'])->name('app.suporte.index');
    Route::post('/app/suporte', [SupportController::class, 'store'])->name('app.suporte.store');

    // Painel admin — analytics + usuários (read-only, somente operador FiscalDock)
    Route::prefix('app/admin')->name('app.admin.')
        ->middleware(\App\Http\Middleware\EnsureAdmin::class)->group(function () {
            Route::get('/', [\App\Http\Controllers\Dashboard\AdminAnalyticsController::class, 'index'])->name('index');
            Route::get('/usuarios', [\App\Http\Controllers\Dashboard\AdminUsuariosController::class, 'index'])->name('usuarios.index');
            Route::get('/usuarios/novo', [\App\Http\Controllers\Dashboard\AdminUsuariosController::class, 'create'])->name('usuarios.create');
            Route::post('/usuarios', [\App\Http\Controllers\Dashboard\AdminUsuariosController::class, 'store'])->name('usuarios.store');
            Route::get('/usuarios/{id}', [\App\Http\Controllers\Dashboard\AdminUsuariosController::class, 'show'])->name('usuarios.show')->where('id', '[0-9]+');
            Route::get('/usuarios/{id}/editar', [\App\Http\Controllers\Dashboard\AdminUsuariosController::class, 'edit'])->name('usuarios.edit')->where('id', '[0-9]+');
            Route::put('/usuarios/{id}', [\App\Http\Controllers\Dashboard\AdminUsuariosController::class, 'update'])->name('usuarios.update')->where('id', '[0-9]+');
            Route::delete('/usuarios/{id}', [\App\Http\Controllers\Dashboard\AdminUsuariosController::class, 'destroy'])->name('usuarios.destroy')->where('id', '[0-9]+');
            Route::post('/usuarios/{id}/creditar', [\App\Http\Controllers\Dashboard\AdminUsuarioAcaoController::class, 'creditar'])->name('usuarios.creditar')->where('id', '[0-9]+');
            Route::post('/usuarios/{id}/bloquear', [\App\Http\Controllers\Dashboard\AdminUsuarioAcaoController::class, 'bloquear'])->name('usuarios.bloquear')->where('id', '[0-9]+');
            Route::post('/usuarios/{id}/admin', [\App\Http\Controllers\Dashboard\AdminUsuarioAcaoController::class, 'admin'])->name('usuarios.admin')->where('id', '[0-9]+');
            Route::post('/usuarios/{id}/assinatura', [\App\Http\Controllers\Dashboard\AdminUsuarioAcaoController::class, 'assinatura'])->name('usuarios.assinatura')->where('id', '[0-9]+');
            Route::post('/usuarios/{id}/trial', [\App\Http\Controllers\Dashboard\AdminUsuarioAcaoController::class, 'trial'])->name('usuarios.trial')->where('id', '[0-9]+');
            Route::post('/usuarios/{id}/impersonar', [\App\Http\Controllers\Dashboard\AdminUsuarioAcaoController::class, 'impersonar'])->name('usuarios.impersonar')->where('id', '[0-9]+');
            Route::get('/auditoria', [\App\Http\Controllers\Dashboard\AdminUsuarioAcaoController::class, 'auditoria'])->name('auditoria');

            // Pendências/notas operacionais do operador FiscalDock
            Route::get('/pendencias', [\App\Http\Controllers\Dashboard\AdminPendenciaController::class, 'index'])->name('pendencias.index');
            Route::post('/pendencias', [\App\Http\Controllers\Dashboard\AdminPendenciaController::class, 'store'])->name('pendencias.store');
            Route::post('/pendencias/{pendencia}/resolver', [\App\Http\Controllers\Dashboard\AdminPendenciaController::class, 'resolver'])->name('pendencias.resolver')->where('pendencia', '[0-9]+');
            Route::post('/pendencias/{pendencia}/reabrir', [\App\Http\Controllers\Dashboard\AdminPendenciaController::class, 'reabrir'])->name('pendencias.reabrir')->where('pendencia', '[0-9]+');
            Route::delete('/pendencias/{pendencia}', [\App\Http\Controllers\Dashboard\AdminPendenciaController::class, 'destroy'])->name('pendencias.destroy')->where('pendencia', '[0-9]+');

            // Status das integrações (gerenciador manual do operador)
            Route::get('/integracoes', [\App\Http\Controllers\Dashboard\AdminIntegracaoController::class, 'index'])->name('integracoes.index');
            Route::put('/integracoes/{integracao}', [\App\Http\Controllers\Dashboard\AdminIntegracaoController::class, 'update'])->name('integracoes.update')->where('integracao', '[0-9]+');
        });

    // Painel admin — parâmetros comerciais (§6.1, somente operador FiscalDock)
    Route::prefix('app/admin/comercial')->name('app.admin.comercial.')
        ->middleware(\App\Http\Middleware\EnsureAdmin::class)->group(function () {
            Route::get('/', [\App\Http\Controllers\Dashboard\AdminComercialController::class, 'index'])->name('index');
            Route::post('/{chave}', [\App\Http\Controllers\Dashboard\AdminComercialController::class, 'update'])->name('update');
            Route::post('/{chave}/reset', [\App\Http\Controllers\Dashboard\AdminComercialController::class, 'reset'])->name('reset');
        });

    // Painel admin — CRUD dos planos de assinatura (subscription_plans, somente operador FiscalDock)
    Route::prefix('app/admin/planos')->name('app.admin.planos.')
        ->middleware(\App\Http\Middleware\EnsureAdmin::class)->group(function () {
            Route::get('/', [\App\Http\Controllers\Dashboard\AdminPlanosController::class, 'index'])->name('index');
            Route::get('/{id}/editar', [\App\Http\Controllers\Dashboard\AdminPlanosController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::post('/{id}', [\App\Http\Controllers\Dashboard\AdminPlanosController::class, 'update'])->name('update')->where('id', '[0-9]+');
        });

    // Impersonação — sair (fora do EnsureAdmin: sessão vira do alvo não-admin durante impersonação)
    Route::post('/app/admin/impersonar/sair', [\App\Http\Controllers\Dashboard\AdminUsuarioAcaoController::class, 'impersonarSair'])->name('app.admin.impersonar.sair');

    // Centro de Privacidade (LGPD fase 2 — direitos do titular)
    Route::prefix('app/privacidade')->name('app.privacidade.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dashboard\PrivacidadeController::class, 'index'])->name('index');
        Route::get('/exportar', [\App\Http\Controllers\Dashboard\PrivacidadeController::class, 'exportarDados'])->name('exportar');
        Route::get('/exportar-csv', [\App\Http\Controllers\Dashboard\PrivacidadeController::class, 'exportarCsv'])->name('exportar-csv');
        Route::post('/marketing/revogar', [\App\Http\Controllers\Dashboard\PrivacidadeController::class, 'revogarMarketing'])->name('marketing.revogar');
        Route::post('/exclusao', [\App\Http\Controllers\Dashboard\PrivacidadeController::class, 'solicitarExclusao'])->name('exclusao.solicitar');
        Route::post('/exclusao/cancelar', [\App\Http\Controllers\Dashboard\PrivacidadeController::class, 'cancelarExclusao'])->name('exclusao.cancelar');
    });

    // Minha Empresa
    Route::prefix('app/minha-empresa')->name('app.minha-empresa.')->group(function () {
        Route::get('/', [MinhaEmpresaController::class, 'index'])->name('index');
        Route::get('/configurar', [MinhaEmpresaController::class, 'configurar'])->name('configurar');
        Route::post('/definir-principal', [MinhaEmpresaController::class, 'definirPrincipal'])->name('definir-principal');
        Route::get('/historico', [MinhaEmpresaController::class, 'historico'])->name('historico');
        Route::post('/certificado', [MinhaEmpresaController::class, 'salvarCertificado'])->name('certificado.salvar');
        Route::delete('/certificado', [MinhaEmpresaController::class, 'removerCertificado'])->name('certificado.remover');
    });

    // CONSULTA (estrutura unificada)
    Route::prefix('app/consulta')->name('app.consulta.')->group(function () {
        // Painel de consulta (ex-"Nova Consulta"). O route name 'nova' fica na rota /painel de
        // propósito: name é identidade interna (dezenas de refs app.consulta.nova.*), URI é o
        // endereço público. Os endpoints AJAX seguem sob /nova/* — só a página HTML mudou.
        Route::get('/painel', [ConsultaController::class, 'index'])->name('nova');
        Route::get('/nova', fn () => redirect('/app/consulta/painel', 301));
        Route::get('/nova/participantes', [ConsultaController::class, 'getParticipantes'])->name('nova.participantes');
        Route::get('/nova/participantes/grupo/{id}', [ConsultaController::class, 'getParticipantesGrupo'])->name('nova.participantes.grupo');
        Route::post('/nova/calcular-custo', [ConsultaController::class, 'calcularCusto'])->name('nova.calcular-custo');
        Route::post('/nova/executar', [ConsultaController::class, 'executar'])->name('nova.executar');
        Route::post('/nova/adicionar-cnpj', [ConsultaController::class, 'adicionarCnpj'])->name('nova.adicionar-cnpj');
        Route::get('/nova/progresso/stream', [ConsultaController::class, 'streamProgresso'])->name('nova.progresso.stream');
        Route::get('/progresso/stream', [ConsultaController::class, 'streamProgresso'])->name('progresso.stream');
        Route::get('/nova/clientes', [ConsultaController::class, 'getClientes'])->name('nova.clientes');
        Route::post('/nova/participantes-por-clientes', [ConsultaController::class, 'getParticipanteIdsByClientes'])->name('nova.participantes-por-clientes');
        Route::get('/nova/grupos', [ConsultaController::class, 'getGrupos'])->name('nova.grupos');

        // Consulta Avulsa (redirect para Nova Consulta)
        Route::get('/avulso', fn () => redirect('/app/consulta/nova', 301))->name('avulso');

        // Planos Disponiveis
        Route::get('/planos', [MonitoramentoController::class, 'planos'])->name('planos');

        // Historico
        Route::get('/historico', [ConsultaController::class, 'historico'])->name('historico');

        // Detalhe do lote
        Route::get('/lote/{id}', [ConsultaController::class, 'showLote'])->name('lote.show');

        // Download de lote
        Route::get('/lote/{id}/baixar', [ConsultaController::class, 'baixarLote'])->name('lote.baixar');

        // Status do lote (polling fallback para SSE)
        Route::get('/lote/{id}/status', [ConsultaController::class, 'statusLote'])->name('lote.status');

        // Resultados de um lote (para exibição inline)
        Route::get('/lote/{id}/resultados', [ConsultaController::class, 'resultadosLote'])->name('lote.resultados');

        // Reconsulta de fontes com falha transitória (50% off, 1x por fonte)
        Route::get('/lote/{id}/retry/pendentes', [ConsultaController::class, 'retryPendentes'])->name('lote.retry.pendentes');
        Route::post('/lote/{id}/retry', [ConsultaController::class, 'retryExecutar'])->name('lote.retry');

    });

    // Compatibilidade legada: /app/consultas/*
    Route::prefix('app/consultas')->group(function () {
        $legacyRedirect = function (Request $request, string $path) {
            $queryString = $request->getQueryString();

            return redirect($queryString ? "{$path}?{$queryString}" : $path, 301);
        };

        Route::get('/nova', fn (Request $request) => $legacyRedirect($request, '/app/consulta/nova'));
        Route::get('/historico', fn (Request $request) => $legacyRedirect($request, '/app/consulta/historico'));
        Route::get('/planos', fn (Request $request) => $legacyRedirect($request, '/app/consulta/planos'));
        Route::get('/avulso', fn (Request $request) => $legacyRedirect($request, '/app/consulta/avulso'));
        Route::get('/lote/{id}', fn (Request $request, $id) => $legacyRedirect($request, "/app/consulta/lote/{$id}"));
        Route::get('/lote/{id}/baixar', fn (Request $request, $id) => $legacyRedirect($request, "/app/consulta/lote/{$id}/baixar"));
        Route::get('/lote/{id}/status', fn (Request $request, $id) => $legacyRedirect($request, "/app/consulta/lote/{$id}/status"));
        Route::get('/lote/{id}/resultados', fn (Request $request, $id) => $legacyRedirect($request, "/app/consulta/lote/{$id}/resultados"));

        Route::get('/nova/participantes', [ConsultaController::class, 'getParticipantes']);
        Route::get('/nova/participantes/grupo/{id}', [ConsultaController::class, 'getParticipantesGrupo']);
        Route::post('/nova/calcular-custo', [ConsultaController::class, 'calcularCusto']);
        Route::post('/nova/executar', [ConsultaController::class, 'executar']);
        Route::post('/nova/adicionar-cnpj', [ConsultaController::class, 'adicionarCnpj']);
        Route::get('/nova/progresso/stream', [ConsultaController::class, 'streamProgresso']);
        Route::get('/nova/clientes', [ConsultaController::class, 'getClientes']);
        Route::post('/nova/participantes-por-clientes', [ConsultaController::class, 'getParticipanteIdsByClientes']);
        Route::get('/nova/grupos', [ConsultaController::class, 'getGrupos']);
    });
});
