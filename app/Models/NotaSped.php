<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaSped extends Model
{
    protected $table = 'notas_sped';

    protected $fillable = [
        'user_id',
        'cliente_id',
        'importacao_sped_id',
        'emit_participante_id',
        'dest_participante_id',
        'tipo_efd',
        'registro',
        'tipo_nota',
        'modelo_doc',
        'serie',
        'numero_nota',
        'chave_acesso',
        'data_emissao',
        'data_entrada_saida',
        'valor_total',
        'valor_icms',
        'valor_icms_st',
        'valor_ipi',
        'valor_pis',
        'valor_cofins',
        'valor_frete',
        'valor_desconto',
        'cfop_principal',
        'payload',
        'validacao',
    ];

    protected function casts(): array
    {
        return [
            'tipo_nota' => 'integer',
            'data_emissao' => 'date',
            'data_entrada_saida' => 'date',
            'valor_total' => 'decimal:2',
            'valor_icms' => 'decimal:2',
            'valor_icms_st' => 'decimal:2',
            'valor_ipi' => 'decimal:2',
            'valor_pis' => 'decimal:2',
            'valor_cofins' => 'decimal:2',
            'valor_frete' => 'decimal:2',
            'valor_desconto' => 'decimal:2',
            'payload' => 'array',
            'validacao' => 'array',
        ];
    }

    // Relacionamentos

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function importacaoSped(): BelongsTo
    {
        return $this->belongsTo(ImportacaoSped::class, 'importacao_sped_id');
    }

    public function emitente(): BelongsTo
    {
        return $this->belongsTo(Participante::class, 'emit_participante_id');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(Participante::class, 'dest_participante_id');
    }

    // Acessores

    /**
     * Verifica se a nota é de entrada.
     */
    public function getIsEntradaAttribute(): bool
    {
        return $this->tipo_nota === 0;
    }

    /**
     * Verifica se a nota é de saída.
     */
    public function getIsSaidaAttribute(): bool
    {
        return $this->tipo_nota === 1;
    }

    /**
     * Retorna o tipo da nota formatado.
     */
    public function getTipoNotaFormatadoAttribute(): string
    {
        return $this->tipo_nota === 0 ? 'Entrada' : 'Saída';
    }

    /**
     * Retorna o modelo do documento formatado.
     */
    public function getModeloDocFormatadoAttribute(): string
    {
        return match ($this->modelo_doc) {
            '01' => 'Nota Fiscal',
            '1B' => 'Nota Fiscal Avulsa',
            '04' => 'Nota Fiscal de Produtor',
            '55' => 'NF-e',
            '57' => 'CT-e',
            '65' => 'NFC-e',
            '67' => 'CT-e OS',
            default => $this->modelo_doc ?? 'N/A',
        };
    }

    /**
     * Total de tributos da nota.
     */
    public function getTotalTributosAttribute(): float
    {
        return $this->valor_icms + $this->valor_icms_st + $this->valor_ipi + $this->valor_pis + $this->valor_cofins;
    }

    // Scopes

    public function scopeDoUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeEntradas($query)
    {
        return $query->where('tipo_nota', 0);
    }

    public function scopeSaidas($query)
    {
        return $query->where('tipo_nota', 1);
    }

    public function scopeEfdFiscal($query)
    {
        return $query->where('tipo_efd', 'EFD_FISCAL');
    }

    public function scopeEfdContrib($query)
    {
        return $query->where('tipo_efd', 'EFD_CONTRIB');
    }

    public function scopePeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_emissao', [$dataInicio, $dataFim]);
    }
}
