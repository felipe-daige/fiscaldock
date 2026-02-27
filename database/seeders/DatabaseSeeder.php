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
        // Criar usuario principal
        $user = User::factory()->create([
            'name' => 'Felipe',
            'sobrenome' => 'FiscalDock',
            'email' => 'admin@fiscaldock.test',
            'telefone' => '67 999844366',
            'password' => 'password',
            'empresa' => 'F. DEVECCHI DAIGE E CIA LTDA',
            'cargo' => 'Socio-Administrador',
            'cnpj' => '00000000000191',
            'credits' => 100,
        ]);

        // Criar cliente (empresa propria)
        $cliente = Cliente::create([
            'user_id' => $user->id,
            'tipo_pessoa' => 'PJ',
            'documento' => '00000000000191',
            'nome' => 'F. FiscalDock e Cia LTDA',
            'razao_social' => 'F. FiscalDock e Cia LTDA',
            'telefone' => '67 999844366',
            'email' => 'admin@fiscaldock.test',
            'is_empresa_propria' => true,
            'uf' => 'MS',
            'cep' => '01310100',
            'municipio' => 'Sao Paulo',
        ]);

        // Popular planos de monitoramento
        $this->call(MonitoramentoPlanoSeeder::class);

        // Popular dados mock de monitoramento (participantes, assinaturas, consultas)
        $this->call(MonitoramentoMockDataSeeder::class);
    }
}
