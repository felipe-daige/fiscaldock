<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ConfirmarTrocaEmailNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

/**
 * Verificação de e-mail (soft — não gateia /app/*) e troca de e-mail no perfil.
 *
 * Troca de e-mail: confirmação SEMPRE no e-mail NOVO antes de aplicar (protege
 * contra digitar o e-mail de terceiro). Até confirmar, o e-mail antigo continua
 * válido pra login. Sem tabela de token — link assinado + `users.pending_email`.
 */
class EmailVerificationController extends Controller
{
    use RespondeAjax;

    /** Reenvia o link de verificação pro e-mail atual da conta. */
    public function reenviar(Request $request)
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return $this->resposta($request, false, 'Seu e-mail já está verificado.', 422);
        }

        $user->sendEmailVerificationNotification();

        return $this->resposta($request, true, 'Enviamos um novo link de verificação para '.$user->email.'.');
    }

    /**
     * Confirma o e-mail atual da conta (link assinado do VerifyEmail nativo).
     * Público — a posse do link assinado é a prova; não exige sessão ativa.
     */
    public function verificar(Request $request, int $id, string $hash)
    {
        $user = User::find($id);

        if (! $user || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return $this->redirectConfirmacao('error', 'Link de verificação inválido ou já utilizado.');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->redirectConfirmacao('success', 'Seu e-mail já estava verificado.');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return $this->redirectConfirmacao('success', 'E-mail verificado com sucesso.');
    }

    /**
     * Pede a troca do e-mail de acesso. Não aplica nada ainda: grava `pending_email`
     * e manda o link de confirmação PRO E-MAIL NOVO.
     */
    public function solicitarTroca(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password' => ['required', 'current_password'],
        ], [
            'email.required' => 'Informe o novo e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso por outra conta.',
            'current_password.required' => 'Confirme sua senha atual.',
            'current_password.current_password' => 'Senha atual incorreta.',
        ]);

        $novoEmail = mb_strtolower(trim($validated['email']));

        if ($novoEmail === mb_strtolower($user->email)) {
            return $this->resposta($request, false, 'Este já é o e-mail da sua conta.', 422);
        }

        $user->pending_email = $novoEmail;
        $user->save();

        Notification::route('mail', $novoEmail)->notify(
            new ConfirmarTrocaEmailNotification($user->id, $user->name, $novoEmail)
        );

        return $this->resposta($request, true, 'Enviamos um link de confirmação para '.$novoEmail.'. A troca só vale depois que você confirmar por lá.');
    }

    /** Cancela um pedido de troca pendente. */
    public function cancelarTroca(Request $request)
    {
        $user = Auth::user();
        $user->pending_email = null;
        $user->save();

        return $this->resposta($request, true, 'Pedido de troca de e-mail cancelado.');
    }

    /**
     * Aplica a troca. Público + assinado (a posse do link prova o controle da caixa nova).
     * Revalida tudo no clique: o link pode ter sido substituído por um pedido mais novo,
     * e o e-mail pode ter sido tomado por outra conta nesse meio-tempo.
     */
    public function confirmarTroca(Request $request, int $user, string $hash)
    {
        $alvo = User::find($user);

        if (! $alvo || ! $alvo->pending_email || ! hash_equals(sha1($alvo->pending_email), $hash)) {
            return $this->redirectConfirmacao('error', 'Link de confirmação inválido, expirado ou substituído por um pedido mais recente.');
        }

        if (User::where('email', $alvo->pending_email)->where('id', '!=', $alvo->id)->exists()) {
            $alvo->pending_email = null;
            $alvo->save();

            return $this->redirectConfirmacao('error', 'Este e-mail foi cadastrado por outra conta enquanto você não confirmava. Escolha outro.');
        }

        $alvo->email = $alvo->pending_email;
        $alvo->pending_email = null;
        $alvo->email_verified_at = now();
        $alvo->save();

        return $this->redirectConfirmacao('success', 'E-mail de acesso atualizado para '.$alvo->email.'.');
    }

    /**
     * Destino dos links de e-mail (públicos): se o usuário está logado, cai no perfil
     * (que exibe success/error); se está deslogado — caso comum de abrir o link em
     * outro aparelho/navegador — cai no /login, que exibe `session('status')`. Sem
     * isto, o redirect pro /app/perfil autenticado bounce pro /login e o flash some.
     */
    private function redirectConfirmacao(string $tipo, string $mensagem)
    {
        if (Auth::check()) {
            return redirect('/app/perfil')->with($tipo, $mensagem);
        }

        // Deslogado: o /login exibe `status`; `status_ok` diz se pinta de verde (sucesso)
        // ou vermelho (link inválido/erro), pra um erro não aparecer como sucesso.
        return redirect('/login')
            ->with('status', $mensagem)
            ->with('status_ok', $tipo === 'success');
    }

    private function resposta(Request $request, bool $ok, string $mensagem, int $statusErro = 200)
    {
        if ($this->isAjaxRequest($request)) {
            return response()->json(['success' => $ok, 'message' => $mensagem], $ok ? 200 : $statusErro);
        }

        return $ok
            ? back()->with('success', $mensagem)
            : back()->with('error', $mensagem);
    }
}
