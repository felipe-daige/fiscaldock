<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EfdRetencaoFonte extends Model
{
    protected $table = 'efd_retencoes_fonte';

    protected $fillable = [
        'importacao_id',
        'user_id',
        'cliente_id',
        'natureza',
        'data_retencao',
        'base_calculo',
        'valor_total',
        'cod_receita',
        'natureza_receita',
        'cnpj',
        'valor_pis',
        'valor_cofins',
        'ind_declarante',
        'dados_brutos',
    ];

    protected function casts(): array
    {
        return [
            'data_retencao' => 'date',
            'base_calculo' => 'decimal:2',
            'valor_total' => 'decimal:2',
            'valor_pis' => 'decimal:2',
            'valor_cofins' => 'decimal:2',
            'dados_brutos' => 'array',
        ];
    }

    // Relacionamentos

    public function importacao(): BelongsTo
    {
        return $this->belongsTo(EfdImportacao::class, 'importacao_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // Acessores

    /**
     * Natureza da retenção (F600 IND_NAT_RET) = QUEM reteve, não qual tributo. O tributo vem
     * do código de receita (ver tributoFormatado). Tabela 5.1.16 do Guia EFD Contribuições.
     */
    public function getNaturezaFormatadaAttribute(): string
    {
        return match ($this->natureza) {
            '01' => 'Órgão/Autarquia/Fundação Federal',
            '02' => 'Outras Entidades da Adm. Pública Federal',
            '03' => 'PJ de Direito Privado',
            '04' => 'Sociedade Cooperativa',
            '05' => 'Fabricante de Máquinas/Veículos',
            '99' => 'Outras Retenções',
            default => $this->natureza ?: '—',
        };
    }

    /**
     * Tributos retidos, derivados do código de receita (DARF). É o que a coluna "Natureza"
     * mostrava antes (mas via `natureza`, que é outra coisa). Ex.: 5952 = CSRF + IRRF
     * (IRRF 1,5% + CSLL 1% + COFINS 3% + PIS 0,65%). O EFD Contribuições só detalha PIS e
     * COFINS; IRRF/CSLL ficam embutidos no valor_total.
     */
    public function getTributoFormatadoAttribute(): string
    {
        return match ($this->cod_receita) {
            '5952' => 'IRRF/CSLL/PIS/COFINS',
            '5979' => 'CSLL/PIS/COFINS',
            '5960' => 'PIS/COFINS',
            '1708' => 'IRRF',
            default => $this->cod_receita ? 'Cód. '.$this->cod_receita : '—',
        };
    }

    public function getCnpjFormatadoAttribute(): string
    {
        $cnpj = $this->cnpj;
        if (strlen($cnpj) !== 14) {
            return $cnpj;
        }

        return substr($cnpj, 0, 2).'.'.substr($cnpj, 2, 3).'.'.substr($cnpj, 5, 3).'/'.substr($cnpj, 8, 4).'-'.substr($cnpj, 12, 2);
    }

    // Scopes

    public function scopeDoUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDoCliente($query, int $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopePorCnpj($query, string $cnpj)
    {
        return $query->where('cnpj', preg_replace('/\D/', '', $cnpj));
    }

    public function scopePeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_retencao', [$dataInicio, $dataFim]);
    }
}
