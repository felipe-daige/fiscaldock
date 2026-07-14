<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountInvitation;
use App\Models\AccountMember;
use App\Models\ConsentLog;
use App\Models\User;
use App\Services\Accounts\AccountService;
use App\Services\Lgpd\ConsentLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AccountInvitationController extends Controller
{
    public function __construct(private AccountService $accounts) {}

    public function show(Request $request, string $token)
    {
        $invitation = $this->findPending($token);
        if ($invitation === null) {
            return redirect('/login')->withErrors(['email' => 'Este convite é inválido, expirou ou já foi utilizado.']);
        }

        $loggedUser = Auth::user();
        $emailMatches = $loggedUser === null
            || mb_strtolower($loggedUser->email) === mb_strtolower($invitation->email);

        return view('landing_page.layouts.public', [
            'initialView' => 'auth.aceitar-convite',
            'invitation' => $invitation,
            'token' => $token,
            'loggedUser' => $loggedUser,
            'emailMatches' => $emailMatches,
            'seo' => ['title' => 'Aceitar convite — FiscalDock', 'robots' => 'noindex,nofollow'],
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invitation = $this->findPending($token);
        if ($invitation === null) {
            throw ValidationException::withMessages(['email' => 'Este convite é inválido, expirou ou já foi utilizado.']);
        }

        $user = Auth::user();
        if ($user !== null && mb_strtolower($user->email) !== mb_strtolower($invitation->email)) {
            throw ValidationException::withMessages(['email' => 'Saia da conta atual e entre com o e-mail que recebeu o convite.']);
        }

        $registrationData = null;
        if ($user === null) {
            $existing = User::whereRaw('lower(email) = ?', [mb_strtolower($invitation->email)])->first();
            if ($existing !== null) {
                return redirect('/login?redirect='.urlencode(route('equipe.convite.aceitar', ['token' => $token])))
                    ->with('status', 'Entre com '.$invitation->email.' para aceitar o convite.');
            }

            $registrationData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'sobrenome' => ['required', 'string', 'max:255'],
                'telefone' => ['required', 'string', 'max:20'],
                'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
                'terms_aceitos' => ['accepted'],
            ]);

        }

        $user = DB::transaction(function () use ($invitation, $user, $registrationData, $request) {
            // Mesma ordem de lock do envio de convites: conta primeiro, convite depois.
            $account = Account::lockForUpdate()->findOrFail($invitation->account_id);
            $locked = AccountInvitation::lockForUpdate()->findOrFail($invitation->id);
            if (! $locked->isPending()) {
                throw ValidationException::withMessages(['email' => 'Este convite não está mais disponível.']);
            }

            if ($user === null) {
                if (User::whereRaw('lower(email) = ?', [mb_strtolower($locked->email)])->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'Já existe um login com este e-mail. Entre nessa conta para aceitar o convite.',
                    ]);
                }

                $user = User::create([
                    'name' => $registrationData['name'],
                    'sobrenome' => $registrationData['sobrenome'],
                    'telefone' => preg_replace('/\D/', '', $registrationData['telefone']),
                    'email' => mb_strtolower($locked->email),
                    'password' => Hash::make($registrationData['password']),
                    'empresa' => $account->nome,
                    'cargo' => 'Membro da equipe',
                    'terms_accepted_at' => now(),
                    'terms_version' => config('legal.terms_version'),
                    'privacy_version' => config('legal.privacy_version'),
                ]);
                // A posse do link enviado para este endereço já prova o controle da caixa.
                $user->forceFill(['email_verified_at' => now()])->save();

                $consent = new ConsentLogService;
                $consent->registrar(
                    $user->id,
                    ConsentLog::TIPO_TERMOS,
                    ConsentLog::ACAO_ACEITE,
                    versao: config('legal.terms_version'),
                    ip: $request->ip(),
                    userAgent: $request->userAgent(),
                );
                $consent->registrar(
                    $user->id,
                    ConsentLog::TIPO_PRIVACIDADE,
                    ConsentLog::ACAO_ACEITE,
                    versao: config('legal.privacy_version'),
                    ip: $request->ip(),
                    userAgent: $request->userAgent(),
                );
            }

            if (AccountMember::where('user_id', $user->id)->exists()) {
                throw ValidationException::withMessages(['email' => 'Este login já pertence a uma conta FiscalDock.']);
            }
            if ($account->members()->count() >= $this->accounts->seatsIncluded($account)) {
                throw ValidationException::withMessages(['email' => 'A conta não possui mais assentos disponíveis.']);
            }

            $member = AccountMember::create([
                'account_id' => $locked->account_id,
                'user_id' => $user->id,
                'papel' => $locked->papel,
                'permissoes' => $locked->permissoes,
                'convidado_por' => $locked->convidado_por,
                'entrou_em' => now(),
            ]);
            $locked->update(['aceito_em' => now()]);
            $this->accounts->log($account, $user, 'convite_aceito', $member, [], $request->ip());

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/app/dashboard')->with('status', 'Convite aceito. Bem-vindo à equipe!');
    }

    private function findPending(string $token): ?AccountInvitation
    {
        return AccountInvitation::with('account.owner')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('aceito_em')
            ->whereNull('revogado_em')
            ->where('expira_em', '>', now())
            ->first();
    }
}
