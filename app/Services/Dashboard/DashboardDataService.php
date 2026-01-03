<?php

namespace App\Services\Dashboard;

use App\Models\Cliente;
use Carbon\Carbon;

class DashboardDataService
{
    /**
     * Obtém todos os dados do dashboard para um usuário.
     *
     * @param int $userId ID do usuário
     * @return array Dados do dashboard (KPIs, monitoramento de clientes, etc.)
     */
    public function getDashboardData(int $userId): array
    {
        $clientesIds = Cliente::where('user_id', $userId)->pluck('id')->toArray();

        // KPIs - Calculando dados reais quando possível
        $kpi_xml_pendentes = 0; // Funcionalidade removida

        $total_clientes = Cliente::where('user_id', $userId)->count();

        // Dados mock para CND e SPED (até implementação completa)
        $kpi_cnd_risco = 3; // Mock: clientes com CND vencida ou vencendo em < 5 dias
        $kpi_sped_pendentes = 12; // Mock: SPEDs pendentes no mês atual

        // Lista RAF - Mix de dados reais e mock
        $monitoramento_clientes = $this->getMonitoramentoClientes($userId);

        // Status da última sincronização (mock)
        $ultima_sincronizacao = Carbon::now()->subHours(2);

        return [
            'kpi_cnd_risco' => $kpi_cnd_risco,
            'kpi_xml_pendentes' => $kpi_xml_pendentes,
            'kpi_sped_pendentes' => $kpi_sped_pendentes,
            'total_empresas' => $total_clientes > 0 ? $total_clientes : count($monitoramento_clientes),
            'monitoramento_empresas' => $monitoramento_clientes,
            'ultima_sincronizacao' => $ultima_sincronizacao,
        ];
    }

    /**
     * Obtém dados de monitoramento dos clientes do usuário.
     *
     * @param int $userId ID do usuário
     * @return array Lista de clientes com dados de monitoramento
     */
    private function getMonitoramentoClientes(int $userId): array
    {
        $clientes = Cliente::where('user_id', $userId)->get();
        $monitoramento_clientes = [];

        foreach ($clientes as $cliente) {
            // Funcionalidade de XML removida
            $xmlPendentes = 0;

            // Dados mock para CND e regime tributário (até implementação completa)
            $regimes = ['Simples Nacional', 'Lucro Presumido', 'Lucro Real'];
            $regime = $regimes[array_rand($regimes)];

            $cndStatuses = ['regular', 'warning', 'danger'];
            $cndStatus = $cndStatuses[array_rand($cndStatuses)];

            // Calcular vencimento CND (mock)
            $cndVencimento = match($cndStatus) {
                'danger' => Carbon::now()->subDays(rand(1, 30)), // Vencida
                'warning' => Carbon::now()->addDays(rand(1, 5)), // Vence em breve
                default => Carbon::now()->addDays(rand(30, 365)) // Regular
            };

            // Calcular conciliação (mock - funcionalidade de XML removida)
            $conciliacaoPct = 0;

            $monitoramento_clientes[] = [
                'id' => $cliente->id,
                'nome' => $cliente->nome,
                'cnpj' => $cliente->documento,
                'regime' => $regime,
                'cnd_status' => $cndStatus,
                'cnd_vencimento' => $cndVencimento->format('Y-m-d'),
                'xml_pendentes' => $xmlPendentes,
                'ultima_importacao' => null,
                'conciliacao_pct' => $conciliacaoPct,
            ];
        }

        // Se não houver clientes, adicionar dados mock para demonstração
        if (empty($monitoramento_clientes)) {
            $monitoramento_clientes = $this->getMockClientes();
        }

        return $monitoramento_clientes;
    }

    /**
     * Retorna dados mock de clientes para demonstração.
     *
     * @return array Lista de clientes mock
     */
    private function getMockClientes(): array
    {
        return [
            [
                'id' => 1,
                'nome' => 'Tech Solutions Ltda',
                'cnpj' => '12.345.678/0001-90',
                'regime' => 'Lucro Presumido',
                'cnd_status' => 'regular',
                'cnd_vencimento' => Carbon::now()->addDays(120)->format('Y-m-d'),
                'xml_pendentes' => 0,
                'ultima_importacao' => Carbon::now()->subHours(2),
                'conciliacao_pct' => 100,
            ],
            [
                'id' => 2,
                'nome' => 'Mercado Silva',
                'cnpj' => '98.765.432/0001-10',
                'regime' => 'Simples Nacional',
                'cnd_status' => 'danger',
                'cnd_vencimento' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'xml_pendentes' => 45,
                'ultima_importacao' => Carbon::now()->subDays(5),
                'conciliacao_pct' => 60,
            ],
            [
                'id' => 3,
                'nome' => 'Indústria ABC',
                'cnpj' => '11.222.333/0001-44',
                'regime' => 'Lucro Real',
                'cnd_status' => 'warning',
                'cnd_vencimento' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'xml_pendentes' => 12,
                'ultima_importacao' => Carbon::now()->subHours(8),
                'conciliacao_pct' => 85,
            ],
        ];
    }
}
