<?php

namespace Database\Seeders;

use App\Models\Cliente;
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
        // Criar usuário principal
        $user = User::factory()->create([
            'name' => 'Felipe',
            'sobrenome' => 'Devecchi Daige',
            'email' => 'felipedaige@gmail.com',
            'telefone' => '67 999844366',
            'password' => '12312312',
            'empresa' => 'F. DEVECCHI DAIGE E CIA LTDA',
            'cargo' => 'Socio-Administrador',
            'cnpj' => '63.112.970/0001-07',
            'credits' => 100,
        ]);

        // Criar cliente (empresa própria)
        Cliente::create([
            'user_id' => $user->id,
            'tipo_pessoa' => 'PJ',
            'documento' => '63.112.970/0001-07',
            'nome' => 'F. DEVECCHI DAIGE E CIA LTDA',
            'razao_social' => 'F. DEVECCHI DAIGE E CIA LTDA',
            'telefone' => '67 999844366',
            'email' => 'felipedaige@gmail.com',
            'is_empresa_propria' => true,
        ]);

        // Popular planos de monitoramento
        $this->call(MonitoramentoPlanoSeeder::class);

        // Popular dados mock de monitoramento (participantes, assinaturas, consultas)
        $this->call(MonitoramentoMockDataSeeder::class);
    }
}

