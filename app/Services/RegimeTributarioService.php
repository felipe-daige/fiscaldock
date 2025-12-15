<?php

namespace App\Services;

use App\Models\Fornecedor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Exception;

class RegimeTributarioService
{
    /**
     * Consulta o regime tributário de um CNPJ
     *
     * @param string $cnpj CNPJ sem formatação
     * @return string|null Regime tributário ou null se não encontrado
     */
    public function consultarRegimeTributario(string $cnpj): ?string
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return null;
        }

        // Verifica cache primeiro
        $cacheKey = "regime_tributario_{$cnpj}";
        $regime = Cache::get($cacheKey);

        if ($regime !== null) {
            return $regime;
        }

        // Verifica no banco de dados
        $fornecedor = Fornecedor::where('cnpj', $cnpj)->first();
        
        if ($fornecedor && $fornecedor->regime_tributario) {
            // Cacheia por 30 dias
            Cache::put($cacheKey, $fornecedor->regime_tributario, now()->addDays(30));
            return $fornecedor->regime_tributario;
        }

        // Consulta API externa
        try {
            $regime = $this->consultarApiExterna($cnpj);
            
            if ($regime) {
                // Salva no banco
                if ($fornecedor) {
                    $fornecedor->update([
                        'regime_tributario' => $regime,
                        'ultima_consulta_regime' => now(),
                    ]);
                } else {
                    Fornecedor::create([
                        'cnpj' => $cnpj,
                        'regime_tributario' => $regime,
                        'ultima_consulta_regime' => now(),
                    ]);
                }

                // Cacheia por 30 dias
                Cache::put($cacheKey, $regime, now()->addDays(30));
                
                return $regime;
            }
        } catch (Exception $e) {
            // Log do erro (pode usar Log::error em produção)
            // Por enquanto, retorna null
        }

        return null;
    }

    /**
     * Consulta regime tributário via API externa
     * Usa ReceitaWS como exemplo, mas pode ser adaptado para outras APIs
     */
    private function consultarApiExterna(string $cnpj): ?string
    {
        try {
            // API ReceitaWS (gratuita, mas com limitações)
            $response = Http::timeout(10)->get("https://www.receitaws.com.br/v1/{$cnpj}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['situacao']) && $data['situacao'] === 'ATIVA') {
                    // Mapeia situação tributária para regime
                    // A API não retorna diretamente o regime, então usamos heurísticas
                    // Em produção, pode ser necessário usar outra API ou consulta manual
                    
                    // Verifica se é MEI
                    if (isset($data['porte']) && $data['porte'] === 'MICRO EMPRESA') {
                        // Pode ser MEI ou Simples, mas por padrão assumimos Simples
                        return 'simples_nacional';
                    }

                    // Por padrão, retorna null (será necessário classificação manual ou outra API)
                    // Em produção, considere usar APIs pagas que retornam regime tributário diretamente
                    return null;
                }
            }
        } catch (Exception $e) {
            // Em caso de erro, retorna null
        }

        return null;
    }

    /**
     * Atualiza o regime tributário de um fornecedor manualmente
     */
    public function atualizarRegimeTributario(string $cnpj, string $regime): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        $fornecedor = Fornecedor::firstOrCreate(
            ['cnpj' => $cnpj],
            ['regime_tributario' => $regime, 'ultima_consulta_regime' => now()]
        );

        if ($fornecedor->regime_tributario !== $regime) {
            $fornecedor->update([
                'regime_tributario' => $regime,
                'ultima_consulta_regime' => now(),
            ]);
        }

        // Limpa cache
        Cache::forget("regime_tributario_{$cnpj}");

        return true;
    }

    /**
     * Mapeia código de situação tributária para regime tributário
     * Útil quando temos informações parciais
     */
    public function mapearRegimePorCodigo(?string $codigo): ?string
    {
        $mapeamento = [
            '1' => 'lucro_real',
            '2' => 'lucro_presumido',
            '3' => 'simples_nacional',
            '4' => 'mei',
        ];

        return $mapeamento[$codigo] ?? null;
    }
}
