<?php

namespace Database\Seeders;

use App\Models\Alerta;
use App\Models\Cliente;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Database\Seeder;

class CodexAlertasMobileSeeder extends Seeder
{
    public function run(): void
    {
        $existing = User::where('email', 'auditoria-mobile@fiscaldock.test')->get();
        foreach ($existing as $user) {
            Alerta::where('user_id', $user->id)->delete();
            Participante::where('user_id', $user->id)->delete();
            Cliente::where('user_id', $user->id)->delete();
            $user->forceDelete();
        }

        $user = User::factory()->trialAtivo()->create([
            'email' => 'auditoria-mobile@fiscaldock.test',
            'password' => 'mobile-audit-2026',
            'name' => 'Auditoria',
            'sobrenome' => 'Mobile',
        ]);

        $cliente = Cliente::create([
            'user_id' => $user->id,
            'documento' => '11222333000181',
            'razao_social' => 'Empresa Demonstração Mobile',
        ]);

        $participante = Participante::create([
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'documento' => '99887766000155',
            'razao_social' => 'Fornecedor Nacional com Razão Social Muito Longa',
            'situacao_cadastral' => 'ATIVA',
            'origem_tipo' => 'MANUAL',
        ]);

        $base = [
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'participante_id' => $participante->id,
            'status' => 'ativo',
        ];

        $alertas = [
            [
                'tipo' => 'certidao_vencendo',
                'categoria' => 'compliance',
                'severidade' => 'alta',
                'titulo' => 'Fornecedor com certidão vencendo',
                'descricao' => 'A certidão federal deste fornecedor vence em poucos dias. Renove antes do prazo para manter a regularidade.',
                'total_afetados' => 2,
                'vence_em' => now()->addDays(3)->toDateString(),
                'detalhes' => [
                    'tipo_alvo' => 'participante',
                    'razao_social' => $participante->razao_social,
                    'documento' => $participante->documento,
                    'certidoes' => [
                        ['label' => 'CND Federal', 'validade' => now()->addDays(3)->format('d/m/Y'), 'vencida' => false, 'dias' => 3],
                        ['label' => 'FGTS/CRF', 'validade' => now()->subDay()->format('d/m/Y'), 'vencida' => true, 'dias' => -1],
                    ],
                ],
            ],
            [
                'tipo' => 'certidao_positiva',
                'categoria' => 'compliance',
                'severidade' => 'alta',
                'titulo' => 'Certidão positiva',
                'descricao' => 'O fornecedor apresenta certidões positivas e exige revisão antes de novas operações.',
                'total_afetados' => 2,
                'valor_risco' => 184320.50,
                'detalhes' => [
                    'participante_id' => $participante->id,
                    'razao_social' => $participante->razao_social,
                    'documento' => $participante->documento,
                    'certidoes' => [
                        ['label' => 'CND Federal', 'status' => 'Positiva', 'severidade' => 'alta'],
                        ['label' => 'CND Estadual', 'status' => 'Positiva', 'severidade' => 'alta'],
                    ],
                    'valor_12m' => 184320.50,
                    'valor_5anos' => 420000.00,
                    'valor_total' => 625430.80,
                    'qtd_notas' => 38,
                ],
            ],
            [
                'tipo' => 'notas_duplicadas',
                'categoria' => 'notas_fiscais',
                'severidade' => 'alta',
                'titulo' => 'Notas fiscais duplicadas',
                'descricao' => 'Foram encontradas notas com mesma numeração, série e participante.',
                'total_afetados' => 3,
                'valor_risco' => 24590.90,
                'detalhes' => [
                    'notas' => [
                        ['numero' => '123456789', 'serie' => '1', 'modelo' => '55', 'participante' => $participante->razao_social, 'data_emissao' => now()->subDays(3)->toDateString(), 'valor' => 12590.90],
                        ['numero' => '987654321', 'serie' => '2', 'modelo' => '55', 'participante' => 'Outro fornecedor de demonstração', 'data_emissao' => now()->subDays(8)->toDateString(), 'valor' => 12000.00],
                    ],
                ],
            ],
            [
                'tipo' => 'pis_cofins_incompleto',
                'categoria' => 'pis_cofins',
                'severidade' => 'media',
                'titulo' => 'PIS/COFINS incompleto',
                'descricao' => 'Itens sem informação suficiente para conferência tributária.',
                'total_afetados' => 14,
                'detalhes' => [
                    'stats' => ['total_notas' => 86, 'com_pis_cofins' => 72, 'sem_pis_cofins' => 14],
                    'mensagem' => 'Revise a escrituração antes do fechamento.',
                ],
            ],
            [
                'tipo' => 'fornecedor_irregular',
                'categoria' => 'fornecedores',
                'severidade' => 'alta',
                'titulo' => 'Fornecedor irregular',
                'descricao' => 'Fornecedor com situação cadastral irregular e movimentação fiscal vinculada.',
                'total_afetados' => 1,
                'valor_risco' => 98840.10,
                'detalhes' => [
                    'participante_id' => $participante->id,
                    'razao_social' => $participante->razao_social,
                    'cnpj' => $participante->documento,
                    'situacao_cadastral' => 'SUSPENSA',
                    'total_notas' => 19,
                    'valor_em_risco' => 98840.10,
                ],
            ],
            [
                'tipo' => 'gap_importacao',
                'categoria' => 'importacao',
                'severidade' => 'media',
                'titulo' => 'Meses sem importação EFD',
                'descricao' => 'Há competências sem arquivo EFD importado no período analisado.',
                'total_afetados' => 4,
                'detalhes' => [
                    'meses_faltantes' => ['mar/26', 'abr/26', 'mai/26', 'jun/26'],
                    'mensagem' => 'Importe os arquivos faltantes para completar a análise.',
                ],
            ],
            [
                'tipo' => 'consulta_vencida',
                'categoria' => 'compliance',
                'severidade' => 'baixa',
                'titulo' => 'Consulta desatualizada',
                'descricao' => 'A última consulta cadastral ultrapassou a janela recomendada de atualização.',
                'total_afetados' => 1,
                'detalhes' => [
                    'participante_id' => $participante->id,
                    'razao_social' => $participante->razao_social,
                    'cnpj' => $participante->documento,
                    'ultima_consulta_em' => now()->subDays(120)->toIso8601String(),
                ],
            ],
        ];

        foreach ($alertas as $index => $alerta) {
            Alerta::create(array_merge($base, $alerta, [
                'hash' => hash('sha256', 'mobile-audit-'.$index),
            ]));
        }
    }
}
