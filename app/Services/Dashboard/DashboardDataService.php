<?php

namespace App\Services\Dashboard;

use App\Models\Empresa;
use App\Models\XmlDocumento;
use Carbon\Carbon;

class DashboardDataService
{
    /**
     * Obtém todos os dados do dashboard para um usuário.
     *
     * @param int $userId ID do usuário
     * @return array Dados do dashboard (KPIs, monitoramento de empresas, etc.)
     */
    public function getDashboardData(int $userId): array
    {
        $empresasIds = Empresa::where('user_id', $userId)->pluck('id')->toArray();

        // KPIs - Calculando dados reais quando possível
        $kpi_xml_pendentes = !empty($empresasIds) 
            ? XmlDocumento::where('status', 'pendente')
                ->whereIn('empresa_id', $empresasIds)
                ->count()
            : 0;

        $total_empresas = Empresa::where('user_id', $userId)->count();

        // Dados mock para CND e SPED (até implementação completa)
        $kpi_cnd_risco = 3; // Mock: empresas com CND vencida ou vencendo em < 5 dias
        $kpi_sped_pendentes = 12; // Mock: SPEDs pendentes no mês atual

        // Lista RAF - Mix de dados reais e mock
        $monitoramento_empresas = $this->getMonitoramentoEmpresas($userId);

        // Status da última sincronização (mock)
        $ultima_sincronizacao = Carbon::now()->subHours(2);

        return [
            'kpi_cnd_risco' => $kpi_cnd_risco,
            'kpi_xml_pendentes' => $kpi_xml_pendentes,
            'kpi_sped_pendentes' => $kpi_sped_pendentes,
            'total_empresas' => $total_empresas > 0 ? $total_empresas : count($monitoramento_empresas),
            'monitoramento_empresas' => $monitoramento_empresas,
            'ultima_sincronizacao' => $ultima_sincronizacao,
        ];
    }

    /**
     * Obtém dados de monitoramento das empresas do usuário.
     *
     * @param int $userId ID do usuário
     * @return array Lista de empresas com dados de monitoramento
     */
    private function getMonitoramentoEmpresas(int $userId): array
    {
        $empresas = Empresa::where('user_id', $userId)->get();
        $monitoramento_empresas = [];

        foreach ($empresas as $empresa) {
            // Buscar último XML importado
            $ultimoXml = XmlDocumento::where('empresa_id', $empresa->id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Contar XMLs pendentes
            $xmlPendentes = XmlDocumento::where('empresa_id', $empresa->id)
                ->where('status', 'pendente')
                ->count();

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

            // Calcular conciliação (mock - baseado em XMLs processados)
            $xmlProcessados = XmlDocumento::where('empresa_id', $empresa->id)
                ->where('status', '!=', 'pendente')
                ->count();
            $totalXmls = XmlDocumento::where('empresa_id', $empresa->id)->count();
            $conciliacaoPct = $totalXmls > 0 ? round(($xmlProcessados / $totalXmls) * 100) : 0;

            $monitoramento_empresas[] = [
                'id' => $empresa->id,
                'nome' => $empresa->nome_empresa,
                'cnpj' => $empresa->cnpj,
                'regime' => $regime,
                'cnd_status' => $cndStatus,
                'cnd_vencimento' => $cndVencimento->format('Y-m-d'),
                'xml_pendentes' => $xmlPendentes,
                'ultima_importacao' => $ultimoXml ? $ultimoXml->created_at : null,
                'conciliacao_pct' => $conciliacaoPct,
            ];
        }

        // Se não houver empresas, adicionar dados mock para demonstração
        if (empty($monitoramento_empresas)) {
            $monitoramento_empresas = $this->getMockEmpresas();
        }

        return $monitoramento_empresas;
    }

    /**
     * Retorna dados mock de empresas para demonstração.
     *
     * @return array Lista de empresas mock
     */
    private function getMockEmpresas(): array
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

