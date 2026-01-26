<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaFiscal extends Model
{
    protected $table = 'notas_fiscais';

    protected $fillable = [
        'user_id',
        'importacao_xml_id',
        'cliente_id',
        'chave_acesso',
        'tipo_documento',
        'numero_nota',
        'serie',
        'data_emissao',
        'natureza_operacao',
        'valor_total',
        'tipo_nota',
        'finalidade',
        'chave_referenciada',
        'emit_cnpj',
        'emit_razao_social',
        'emit_uf',
        'emit_participante_id',
        'dest_cnpj',
        'dest_razao_social',
        'dest_uf',
        'dest_participante_id',
        'icms_valor',
        'icms_st_valor',
        'pis_valor',
        'cofins_valor',
        'ipi_valor',
        'tributos_total',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'numero_nota' => 'integer',
            'serie' => 'integer',
            'data_emissao' => 'datetime',
            'valor_total' => 'decimal:2',
            'tipo_nota' => 'integer',
            'finalidade' => 'integer',
            'icms_valor' => 'decimal:2',
            'icms_st_valor' => 'decimal:2',
            'pis_valor' => 'decimal:2',
            'cofins_valor' => 'decimal:2',
            'ipi_valor' => 'decimal:2',
            'tributos_total' => 'decimal:2',
            'payload' => 'array',
        ];
    }

    // Constantes para tipo_nota
    public const TIPO_ENTRADA = 0;
    public const TIPO_SAIDA = 1;

    // Constantes para finalidade
    public const FINALIDADE_NORMAL = 1;
    public const FINALIDADE_COMPLEMENTAR = 2;
    public const FINALIDADE_AJUSTE = 3;
    public const FINALIDADE_DEVOLUCAO = 4;

    // Relacionamentos

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function importacaoXml(): BelongsTo
    {
        return $this->belongsTo(ImportacaoXml::class, 'importacao_xml_id');
    }

    public function emitente(): BelongsTo
    {
        return $this->belongsTo(Participante::class, 'emit_participante_id');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(Participante::class, 'dest_participante_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Nota fiscal referenciada (para devoluções).
     */
    public function notaReferenciada(): ?NotaFiscal
    {
        if (! $this->chave_referenciada) {
            return null;
        }

        return static::where('user_id', $this->user_id)
            ->where('chave_acesso', $this->chave_referenciada)
            ->first();
    }

    // Acessores

    /**
     * CNPJ do emitente formatado.
     */
    public function getEmitCnpjFormatadoAttribute(): string
    {
        $cnpj = preg_replace('/[^0-9]/', '', $this->emit_cnpj);
        if (strlen($cnpj) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
        }

        return $this->emit_cnpj;
    }

    /**
     * CNPJ do destinatário formatado.
     */
    public function getDestCnpjFormatadoAttribute(): string
    {
        $cnpj = preg_replace('/[^0-9]/', '', $this->dest_cnpj);
        if (strlen($cnpj) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
        }

        return $this->dest_cnpj;
    }

    /**
     * Valor formatado em BRL.
     */
    public function getValorFormatadoAttribute(): string
    {
        return 'R$ '.number_format((float) $this->valor_total, 2, ',', '.');
    }

    /**
     * Verifica se é uma devolução.
     */
    public function isDevolucao(): bool
    {
        return $this->finalidade === self::FINALIDADE_DEVOLUCAO;
    }

    /**
     * Verifica se é nota de entrada.
     */
    public function isEntrada(): bool
    {
        return $this->tipo_nota === self::TIPO_ENTRADA;
    }

    /**
     * Verifica se é nota de saída.
     */
    public function isSaida(): bool
    {
        return $this->tipo_nota === self::TIPO_SAIDA;
    }

    /**
     * Descrição legível do tipo de nota.
     */
    public function getTipoNotaDescricaoAttribute(): string
    {
        return match ($this->tipo_nota) {
            self::TIPO_ENTRADA => 'Entrada',
            self::TIPO_SAIDA => 'Saída',
            default => 'Desconhecido',
        };
    }

    /**
     * Descrição legível da finalidade.
     */
    public function getFinalidadeDescricaoAttribute(): string
    {
        return match ($this->finalidade) {
            self::FINALIDADE_NORMAL => 'Normal',
            self::FINALIDADE_COMPLEMENTAR => 'Complementar',
            self::FINALIDADE_AJUSTE => 'Ajuste',
            self::FINALIDADE_DEVOLUCAO => 'Devolução',
            default => 'Não informado',
        };
    }

    /**
     * Total de tributos (soma dos campos individuais).
     */
    public function getTotalTributosCalculadoAttribute(): float
    {
        return (float) $this->icms_valor
            + (float) $this->icms_st_valor
            + (float) $this->pis_valor
            + (float) $this->cofins_valor
            + (float) $this->ipi_valor;
    }

    // Scopes

    public function scopeDoUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDoTipo($query, string $tipo)
    {
        return $query->where('tipo_documento', strtoupper($tipo));
    }

    public function scopeEntradas($query)
    {
        return $query->where('tipo_nota', self::TIPO_ENTRADA);
    }

    public function scopeSaidas($query)
    {
        return $query->where('tipo_nota', self::TIPO_SAIDA);
    }

    public function scopeDevolucoes($query)
    {
        return $query->where('finalidade', self::FINALIDADE_DEVOLUCAO);
    }

    public function scopePorEmitente($query, string $cnpj)
    {
        return $query->where('emit_cnpj', preg_replace('/[^0-9]/', '', $cnpj));
    }

    public function scopePorDestinatario($query, string $cnpj)
    {
        return $query->where('dest_cnpj', preg_replace('/[^0-9]/', '', $cnpj));
    }

    public function scopeNoPeriodo($query, $inicio, $fim)
    {
        return $query->whereBetween('data_emissao', [$inicio, $fim]);
    }

    public function scopeDoCliente($query, int $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }
}
