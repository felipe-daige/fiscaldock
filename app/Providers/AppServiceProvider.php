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
            new \App\Services\Consultas\Fontes\CndFederalFonte,
            new \App\Services\Consultas\Fontes\CndtFonte,
            new \App\Services\Consultas\Fontes\CrfFgtsFonte,
            new \App\Services\Consultas\Fontes\CndEstadualFonte,
            new \App\Services\Consultas\Fontes\SintegraFonte,
            new \App\Services\Consultas\Fontes\CndMunicipalFonte,
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

        // PDFs de relatório (todos estendem reports.layout): decide marca d'água e header executivo
        // pelo entitlement do usuário. Free/sem export pago → marca d'água; Profissional+/trial →
        // header executivo. Controller pode sobrescrever passando as chaves explicitamente.
        View::composer('reports.layout', function ($view) {
            $user = auth()->user();
            $ent = app(\App\Services\Entitlements\EntitlementService::class);

            if (! $view->offsetExists('marcaDagua')) {
                $view->with('marcaDagua', $user !== null && ! $ent->permits($user, 'export'));
            }
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
