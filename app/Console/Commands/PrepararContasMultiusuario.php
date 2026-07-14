<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Accounts\AccountService;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Instala de forma idempotente a estrutura multiusuário em bancos já existentes.
 *
 * A regra do projeto impede novas migrations; ambientes novos recebem as tabelas pela
 * migration inicial editada e ambientes já migrados executam este comando uma vez no deploy.
 */
class PrepararContasMultiusuario extends Command
{
    protected $signature = 'accounts:instalar';

    protected $description = 'Cria a estrutura multiusuário e vincula usuários existentes como donos de suas contas';

    public function handle(AccountService $accounts): int
    {
        $this->createSchema();

        $provisionados = 0;
        User::query()->orderBy('id')->eachById(function (User $user) use ($accounts, &$provisionados) {
            $accounts->ensureForOwner($user);
            $provisionados++;
        });

        $this->info("Estrutura multiusuário pronta; {$provisionados} login(s) verificado(s).");

        return self::SUCCESS;
    }

    private function createSchema(): void
    {
        if (! Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('nome');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('account_members')) {
            Schema::create('account_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('papel')->default('leitura');
                $table->jsonb('permissoes')->nullable();
                $table->foreignId('convidado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('entrou_em')->nullable();
                $table->timestamps();
                $table->index(['account_id', 'papel']);
            });
        }

        if (! Schema::hasTable('account_invitations')) {
            Schema::create('account_invitations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
                $table->string('email');
                $table->string('papel')->default('leitura');
                $table->jsonb('permissoes')->nullable();
                $table->string('token_hash', 64)->unique();
                $table->foreignId('convidado_por')->constrained('users')->cascadeOnDelete();
                $table->timestamp('expira_em');
                $table->timestamp('aceito_em')->nullable();
                $table->timestamp('revogado_em')->nullable();
                $table->timestamps();
                $table->index(['account_id', 'email']);
                $table->index(['account_id', 'aceito_em', 'revogado_em']);
            });
        }

        if (! Schema::hasTable('account_activity_logs')) {
            Schema::create('account_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('acao');
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->jsonb('detalhes')->nullable();
                $table->string('ip', 64)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['account_id', 'created_at']);
            });
        }

        if (Schema::hasTable('subscription_plans')
            && ! Schema::hasColumn('subscription_plans', 'preco_assento_extra_centavos')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->integer('preco_assento_extra_centavos')->default(0);
            });
        }
    }
}
