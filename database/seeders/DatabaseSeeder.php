<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário de teste
        User::factory()->create([
            'name' => 'Test',
            'sobrenome' => 'User',
            'telefone' => '(11) 99999-9999',
            'email' => 'test@example.com',
            'credits' => 100,
        ]);

        // Popular planos de monitoramento
        $this->call(MonitoramentoPlanoSeeder::class);

        // Popular dados mock de monitoramento (participantes, assinaturas, consultas)
        $this->call(MonitoramentoMockDataSeeder::class);
    }
}

