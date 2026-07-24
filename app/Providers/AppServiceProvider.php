<?php

namespace App\Providers;

use App\View\Composers\SidebarComposer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Consultas\FonteRegistry::class, fn () => new \App\Services\Consultas\FonteRegistry([
            new \App\Services\Consultas\Fontes\CadastroFonte,
            // Análise Fiscal (paga): desbloqueia o raio-X tributário derivado do cadastro grátis.
            new \App\Services\Consultas\Fontes\AnaliseFiscalFonte,
            new \App\Services\Consultas\Fontes\CndFederalFonte,
            new \App\Services\Consultas\Fontes\CndtFonte,
            new \App\Services\Consultas\Fontes\CrfFgtsFonte,
            new \App\Services\Consultas\Fontes\CndEstadualFonte,
            new \App\Services\Consultas\Fontes\SintegraFonte,
            new \App\Services\Consultas\Fontes\CndMunicipalFonte,
            // Vertical advocacia (consulta à la carte) — docs/advocacia/consultas-certidoes.md.
            // Fora de consultas_incluidas de qualquer plano: só entram em lote via seleção avulsa.
            new \App\Services\Consultas\Fontes\Advocacia\CertidaoStjFonte,
            new \App\Services\Consultas\Fontes\Advocacia\CertidaoTrfFonte,
            new \App\Services\Consultas\Fontes\Advocacia\CeatTrtFonte,
            new \App\Services\Consultas\Fontes\Advocacia\CertidaoMptFonte,
            new \App\Services\Consultas\Fontes\Advocacia\CertidaoMpfFonte,
            new \App\Services\Consultas\Fontes\Advocacia\CertidaoTcuFonte,
            new \App\Services\Consultas\Fontes\Advocacia\ProtestosFonte,
            new \App\Services\Consultas\Fontes\Advocacia\FalenciasFonte,
            new \App\Services\Consultas\Fontes\Advocacia\ImprobidadeFonte,
            new \App\Services\Consultas\Fontes\Advocacia\CeisFonte,
            new \App\Services\Consultas\Fontes\Advocacia\CnepFonte,
            // Expansão pública: código pronto, mas `pronta()` exige validação/smoke por chave.
            // Enquanto não liberadas, aparecem na vitrine e no admin como "Em manutenção".
            new \App\Services\Consultas\Fontes\Advocacia\PgfnDevedoresFonte,
            new \App\Services\Consultas\Fontes\Advocacia\TcuCnpFonte,
            new \App\Services\Consultas\Fontes\Advocacia\TcuCniInidoneoFonte,
            new \App\Services\Consultas\Fontes\Advocacia\TcuCniInabilitadoFonte,
            new \App\Services\Consultas\Fontes\Advocacia\BcbValoresReceberFonte,
            new \App\Services\Consultas\Fontes\Advocacia\InpiMarcasTitularFonte,
            new \App\Services\Consultas\Fontes\Advocacia\IbamaEmbargosFonte,
            new \App\Services\Consultas\Fontes\Advocacia\IbamaDebitosFonte,
            new \App\Services\Consultas\Fontes\Advocacia\IbamaRegularidadeFonte,
            new \App\Services\Consultas\Fontes\Advocacia\IbamaAutuacoesFonte,
            // GOV.BR (login do solicitante obrigatório) — credencial de sistema na fase de validação.
            new \App\Services\Consultas\Fontes\Advocacia\SigefParcelasFonte,
            // Pessoa física: cadastro/situação cadastral + quitação eleitoral. As duas fontes
            // sensíveis ficam registradas, mas `pronta()` exige flag LGPD explícita.
            new \App\Services\Consultas\Fontes\Advocacia\CadastroPfFonte,
            new \App\Services\Consultas\Fontes\Advocacia\QuitacaoEleitoralFonte,
            new \App\Services\Consultas\Fontes\Advocacia\AntecedentesPfFonte,
            new \App\Services\Consultas\Fontes\Advocacia\MandadoPrisaoFonte,
            // Certidão de 2 etapas (fase 4). Registrada p/ o follow-up job resolvê-la; fica oculta
            // da tela via `consultas.fontes_pausadas` até o smoke validar comarca/modelo.
            new \App\Services\Consultas\Fontes\Advocacia\TjmsPedidoFonte,
        ]));

        $this->app->bind(
            \App\Support\Monitoramento\MonitoramentoNotifier::class,
            \App\Support\Monitoramento\AlertaCentralNotifier::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Força https na geração de URLs/Assets em produção (evita Mixed Content
        // quando o TLS termina no Traefik e a app recebe HTTP internamente).
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        View::composer('autenticado.partials.sidebar', SidebarComposer::class);

        // Todos os PDFs gerados pela FiscalDock estendem reports.layout. A marca d'água depende
        // exclusivamente do tier: Free puro sempre recebe; trial e assinaturas pagas recebem
        // o documento limpo. O header executivo segue a capability.
        View::composer('reports.layout', function ($view) {
            $user = auth()->user();
            $ent = app(\App\Services\Entitlements\EntitlementService::class);
            $plan = $user !== null ? $ent->planFor($user) : null;

            // Regra comercial global: um controller/view não pode retirar a marca do Free.
            $view->with('marcaDagua', $plan?->codigo === 'free' && ! $user?->hasActiveTrial());
            if (! $view->offsetExists('pdfExecutivo')) {
                $view->with('pdfExecutivo', $user !== null && $ent->permits($user, 'pdf_executivo'));
            }
        });

        Blade::directive('brl', fn ($e) => "<?php echo \App\Support\Dinheiro::brl($e); ?>");

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return \App\Support\Mail\Blocos::comEtiqueta(new MailMessage, 'Segurança da conta')
                ->subject('Redefinir a senha da sua conta')
                ->greeting('Olá, '.$notifiable->name.'.')
                ->line('Recebemos um pedido para redefinir a senha da sua conta FiscalDock.')
                ->action('Criar uma nova senha', $url)
                ->line('O link vale por 60 minutos e só pode ser usado uma vez.')
                ->line('Se não foi você, ignore este e-mail — sua senha continua a mesma e ninguém teve acesso à conta.');
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            $minutos = (int) config('auth.verification.expire', 60);

            return \App\Support\Mail\Blocos::comEtiqueta(new MailMessage, 'Confirmação de e-mail')
                ->subject('Confirme seu e-mail para não perder avisos')
                ->greeting('Falta um passo, '.$notifiable->name.'.')
                ->line('Confirme este endereço para garantir a entrega dos avisos que não podem se perder na sua caixa:')
                ->line(\App\Support\Mail\Blocos::destaque(
                    '<strong>·</strong> mudança de situação cadastral e certidão positiva de CNPJ que você monitora<br>'
                    .'<strong>·</strong> comprovantes de pagamento e falha de cobrança<br>'
                    .'<strong>·</strong> recuperação de senha (sem e-mail confirmado, você depende do suporte)'
                ))
                ->action('Confirmar meu e-mail', $url)
                ->line('O link vale por '.$minutos.' minutos.')
                ->line('Se você não criou conta no FiscalDock, ignore este e-mail.');
        });
    }
}
