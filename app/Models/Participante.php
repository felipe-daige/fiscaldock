<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Participante extends Model
{
    use HasFactory;

    protected $table = 'participantes';

    protected $fillable = [
        'user_id',
        'cliente_id',
        'importacao_sped_id',
        'importacao_xml_id',
        'cnpj',
        'razao_social',
        'nome_fantasia',
        'situacao_cadastral',
        'regime_tributario',
        'uf',
        'cep',
        'municipio',
        'telefone',
        'crt',
        'origem_tipo',
        'origem_ref',
        'ultima_consulta_em',
    ];

    protected $casts = [
        'origem_ref' => 'array',
        'ultima_consulta_em' => 'datetime',
    ];

    /**
     * Usuário dono do participante.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cliente associado (opcional).
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Importação SPED que criou este participante (opcional).
     */
    public function importacaoSped(): BelongsTo
    {
        return $this->belongsTo(ImportacaoSped::class, 'importacao_sped_id');
    }

    /**
     * Importação XML que criou este participante (opcional).
     */
    public function importacaoXml(): BelongsTo
    {
        return $this->belongsTo(ImportacaoXml::class, 'importacao_xml_id');
    }

    /**
     * Assinaturas de monitoramento do participante.
     */
    public function assinaturas(): HasMany
    {
        return $this->hasMany(MonitoramentoAssinatura::class);
    }

    /**
     * Consultas realizadas para o participante.
     */
    public function consultas(): HasMany
    {
        return $this->hasMany(MonitoramentoConsulta::class);
    }

    /**
     * Grupos aos quais o participante pertence.
     */
    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(ParticipanteGrupo::class, 'participantes_grupos_pivot', 'participante_id', 'participantes_grupo_id')
            ->withTimestamps();
    }

    /**
     * Notas fiscais (XML) onde o participante é o emitente.
     */
    public function notasComoEmitente(): HasMany
    {
        return $this->hasMany(NotaFiscal::class, 'emit_participante_id');
    }

    /**
     * Notas fiscais (XML) onde o participante é o destinatário.
     */
    public function notasComoDestinatario(): HasMany
    {
        return $this->hasMany(NotaFiscal::class, 'dest_participante_id');
    }

    /**
     * Notas SPED onde o participante é o emitente.
     */
    public function notasSpedComoEmitente(): HasMany
    {
        return $this->hasMany(NotaSped::class, 'emit_participante_id');
    }

    /**
     * Notas SPED onde o participante é o destinatário.
     */
    public function notasSpedComoDestinatario(): HasMany
    {
        return $this->hasMany(NotaSped::class, 'dest_participante_id');
    }

    /**
     * XMLs processados onde o participante é o emitente.
     */
    public function xmlsComoEmitente(): HasMany
    {
        return $this->hasMany(XmlChaveProcessada::class, 'emit_participante_id');
    }

    /**
     * XMLs processados onde o participante é o destinatário.
     */
    public function xmlsComoDestinatario(): HasMany
    {
        return $this->hasMany(XmlChaveProcessada::class, 'dest_participante_id');
    }

    /**
     * Score de risco do participante.
     */
    public function score(): HasOne
    {
        return $this->hasOne(ParticipanteScore::class);
    }

    /**
     * CNPJ formatado.
     */
    public function getCnpjFormatadoAttribute(): string
    {
        $cnpj = preg_replace('/[^0-9]/', '', $this->cnpj);
        if (strlen($cnpj) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
        }
        return $this->cnpj;
    }

    /**
     * Verifica se o participante tem assinatura ativa.
     */
    public function temAssinaturaAtiva(): bool
    {
        return $this->assinaturas()->where('status', 'ativo')->exists();
    }

    /**
     * Retorna a última consulta realizada.
     */
    public function ultimaConsulta(): ?MonitoramentoConsulta
    {
        return $this->consultas()->latest('executado_em')->first();
    }
}
