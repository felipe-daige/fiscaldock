<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountInvitation;
use App\Models\AccountMember;
use App\Models\User;
use App\Notifications\AccountInvitationNotification;
use App\Services\Accounts\AccountService;
use App\Support\AccountContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

class AccountTeamController extends Controller
{
    use RespondeAjax;

    public function __construct(private AccountService $accounts) {}

    public function index(Request $request)
    {
        $context = app(AccountContext::class);
        abort_unless($context->canManageTeam(), 403);

        $account = $context->account();
        $members = $account->members()->with('user')->orderBy('id')->get();
        $invitations = $account->invitations()
            ->whereNull('aceito_em')
            ->whereNull('revogado_em')
            ->where('expira_em', '>', now())
            ->latest()
            ->get();

        $roles = $this->availableRoles($context);

        $data = [
            'account' => $account,
            'members' => $members,
            'invitations' => $invitations,
            'seatsIncluded' => $this->accounts->seatsIncluded($account),
            'seatsUsed' => $this->accounts->seatsUsed($account),
            'roles' => $roles,
            'rolePresets' => collect(array_keys($roles))
                ->mapWithKeys(fn (string $role) => [$role => AccountMember::permissoesPadrao($role)])
                ->all(),
            'modules' => $this->moduleLabels(),
            'context' => $context,
        ];

        $view = 'autenticado.equipe.index';
        if ($this->isAjaxRequest($request)) {
            return response(view($view, $data)->render())->header('Content-Type', 'text/html');
        }

        return view('autenticado.layouts.app', array_merge(['initialView' => $view], $data));
    }

    public function invite(Request $request)
    {
        $context = app(AccountContext::class);
        abort_unless($context->canManageTeam(), 403);

        $data = $this->validateMemberData($request, $context, invite: true);
        $email = mb_strtolower(trim($data['email']));
        $account = $context->account();

        $existingUser = User::whereRaw('lower(email) = ?', [$email])->first();
        if ($existingUser?->accountMembership()->exists()) {
            return back()->withErrors(['email' => 'Este e-mail já pertence a uma conta FiscalDock.']);
        }

        if ($account->members()->whereHas('user', fn ($q) => $q->whereRaw('lower(email) = ?', [$email]))->exists()) {
            return back()->withErrors(['email' => 'Esta pessoa já faz parte da equipe.']);
        }

        $rawToken = Str::random(64);
        $permissions = $this->normalizedPermissions($data['papel'], $data['permissoes'] ?? []);

        try {
            DB::transaction(function () use ($account, $context, $email, $data, $permissions, $rawToken, $request) {
                // Serializa convites/aceites da conta para não ultrapassar os assentos em
                // requisições concorrentes. Um reenvio substitui o convite anterior sem
                // consumir um segundo assento.
                $lockedAccount = Account::lockForUpdate()->findOrFail($account->id);
                $this->accounts->revokePendingInvitationsForEmail($lockedAccount, $email);
                $this->accounts->assertHasSeat($lockedAccount);

                $invitation = AccountInvitation::create([
                    'account_id' => $lockedAccount->id,
                    'email' => $email,
                    'papel' => $data['papel'],
                    'permissoes' => $permissions,
                    'token_hash' => hash('sha256', $rawToken),
                    'convidado_por' => $context->actor()->id,
                    'expira_em' => now()->addDays(7),
                ]);

                $this->accounts->log($lockedAccount, $context->actor(), 'convite_criado', $invitation, [
                    'email' => $email,
                    'papel' => $data['papel'],
                ], $request->ip());
            });
        } catch (RuntimeException $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }

        Notification::route('mail', $email)->notify(
            new AccountInvitationNotification($account, $context->actor(), $rawToken)
        );

        return back()->with('status', 'Convite enviado para '.$email.'.');
    }

    public function update(Request $request, int $member)
    {
        $context = app(AccountContext::class);
        abort_unless($context->canManageTeam(), 403);

        $membership = $context->account()->members()->with('user')->findOrFail($member);
        abort_if($membership->isOwner(), 422, 'O papel do dono não pode ser alterado.');
        abort_if($membership->user_id === $context->actor()->id, 422, 'Você não pode alterar seu próprio papel.');
        abort_if(! $context->isOwner() && $membership->papel === AccountMember::PAPEL_ADMIN, 403);

        $data = $this->validateMemberData($request, $context);
        $membership->update([
            'papel' => $data['papel'],
            'permissoes' => $this->normalizedPermissions($data['papel'], $data['permissoes'] ?? []),
        ]);

        $this->accounts->log($context->account(), $context->actor(), 'membro_atualizado', $membership, [
            'papel' => $data['papel'],
        ], $request->ip());

        return back()->with('status', 'Permissões de '.$membership->user->name.' atualizadas.');
    }

    public function remove(Request $request, int $member)
    {
        $context = app(AccountContext::class);
        abort_unless($context->canManageTeam(), 403);

        $membership = $context->account()->members()->with('user')->findOrFail($member);
        abort_if($membership->isOwner(), 422, 'O dono não pode ser removido da conta.');
        abort_if($membership->user_id === $context->actor()->id, 422, 'Você não pode remover seu próprio acesso.');
        abort_if(! $context->isOwner() && $membership->papel === AccountMember::PAPEL_ADMIN, 403);

        $name = $membership->user->name;
        $this->accounts->log($context->account(), $context->actor(), 'membro_removido', $membership, [
            'user_id' => $membership->user_id,
        ], $request->ip());
        $membership->delete();

        return back()->with('status', $name.' foi removido da conta.');
    }

    public function revokeInvitation(Request $request, int $invitation)
    {
        $context = app(AccountContext::class);
        abort_unless($context->canManageTeam(), 403);

        $invite = $context->account()->invitations()->findOrFail($invitation);
        $invite->update(['revogado_em' => now()]);
        $this->accounts->log($context->account(), $context->actor(), 'convite_revogado', $invite, [
            'email' => $invite->email,
        ], $request->ip());

        return back()->with('status', 'Convite revogado.');
    }

    private function validateMemberData(Request $request, AccountContext $context, bool $invite = false): array
    {
        $rules = [
            'papel' => ['required', Rule::in(array_keys($this->availableRoles($context)))],
            'permissoes' => ['nullable', 'array'],
            'permissoes.*' => ['nullable', 'boolean'],
        ];
        if ($invite) {
            $rules['email'] = ['required', 'email', 'max:255'];
        }

        return $request->validate($rules);
    }

    /** @return array<string,string> */
    private function availableRoles(AccountContext $context): array
    {
        $roles = [
            AccountMember::PAPEL_ADMIN => 'Administrador',
            AccountMember::PAPEL_OPERADOR => 'Operador',
            AccountMember::PAPEL_LEITURA => 'Somente leitura',
        ];

        if (! $context->isOwner()) {
            unset($roles[AccountMember::PAPEL_ADMIN]);
        }

        return $roles;
    }

    /** @param array<string,mixed> $input @return array<string,bool> */
    private function normalizedPermissions(string $role, array $input): array
    {
        if ($role === AccountMember::PAPEL_ADMIN) {
            return AccountMember::permissoesPadrao($role);
        }

        return collect(AccountMember::MODULOS)
            ->mapWithKeys(fn ($module) => [$module => (bool) ($input[$module] ?? false)])
            ->all();
    }

    /** @return array<string,string> */
    private function moduleLabels(): array
    {
        return [
            'painel' => 'Painel e alertas',
            'clientes' => 'Clientes e participantes',
            'documentos' => 'Documentos, importações e Clearance',
            'consultas' => 'Consultas, score e monitoramento',
            'relatorios' => 'BI, catálogo e relatórios',
        ];
    }
}
