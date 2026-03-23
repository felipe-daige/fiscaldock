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
        // Criar usuario principal (idempotente)
        $user = User::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Felipe',
                'sobrenome' => 'FiscalDock',
                'email' => 'felipedaige@gmail.com',
                'telefone' => '67 999844366',
                'password' => bcrypt('12312312'),
                'empresa' => 'F. DEVECCHI DAIGE E CIA LTDA',
                'cargo' => 'Socio-Administrador',
                'cnpj' => '00000000000191',
                'credits' => 100,
            ]
        );

        // Popular planos de monitoramento
        $this->call(MonitoramentoPlanoSeeder::class);

        // Popular dados mock de monitoramento (participantes, assinaturas, consultas)
        $this->call(MonitoramentoMockDataSeeder::class);

    }
}
