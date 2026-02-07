<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\ClienteEndereco;
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
            'sobrenome' => 'FiscalDock',
            'email' => 'admin@fiscaldock.test',
            'telefone' => '67 999844366',
            'password' => 'password',
            'empresa' => 'F. DEVECCHI DAIGE E CIA LTDA',
            'cargo' => 'Socio-Administrador',
            'cnpj' => '00000000000191',
            'credits' => 100,
        ]);

        // Criar cliente (empresa própria)
        $cliente = Cliente::create([
            'user_id' => $user->id,
            'tipo_pessoa' => 'PJ',
            'documento' => '00000000000191',
            'nome' => 'F. FiscalDock e Cia LTDA',
            'razao_social' => 'F. FiscalDock e Cia LTDA',
            'telefone' => '67 999844366',
            'email' => 'admin@fiscaldock.test',
            'is_empresa_propria' => true,
        ]);

        // Endereço da empresa
        ClienteEndereco::create([
            'cliente_id' => $cliente->id,
            'tipo' => 'principal',
            'cep' => '01310100',
            'logradouro' => 'Avenida Paulista',
            'numero' => '6385',
            'complemento' => 'Sala 7',
            'bairro' => 'Bela Vista',
            'cidade' => 'Sao Paulo',
            'estado' => 'MS',
        ]);

        // Popular planos de monitoramento
        $this->call(MonitoramentoPlanoSeeder::class);

        // Popular dados mock de monitoramento (participantes, assinaturas, consultas)
        $this->call(MonitoramentoMockDataSeeder::class);
    }
}

