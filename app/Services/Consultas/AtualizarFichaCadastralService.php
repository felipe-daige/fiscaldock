<?php

namespace App\Services\Consultas;

use Illuminate\Database\Eloquent\Model;

/**
 * Atualiza a ficha cadastral (Participante ou Cliente) com os dados da consulta
 * (CadastroFonte / minhareceita), preenchendo o que está vazio e atualizando os
 * campos voláteis e autoritativos da RFB (situação cadastral, regime tributário).
 *
 * Pós-cutover (2026-06-07), a consulta de CNPJ roda no Laravel; antes era o n8n quem
 * preenchia a ficha. Sem isto, a ficha do participante/cliente fica vazia após consultar.
 */
class AtualizarFichaCadastralService
{
    /** Campos da RFB que mudam no tempo → SEMPRE atualizam (sobrescrevem). */
    private const VOLATEIS = ['situacao_cadastral'];

    /**
     * @param  array<string,mixed>  $dados  resultado_dados (top-level mescla as fontes)
     */
    public function aplicar(Model $alvo, array $dados): bool
    {
        // Só age quando a consulta trouxe bloco cadastral (CadastroFonte).
        if (! isset($dados['razao_social']) && ! isset($dados['situacao_cadastral']) && ! isset($dados['cnaes'])) {
            return false;
        }

        $end = (array) ($dados['endereco'] ?? []);
        $cnaes = (array) ($dados['cnaes'] ?? []);
        $principal = collect($cnaes)->firstWhere('principal', true) ?? ($cnaes[0] ?? null);
        $secundarios = collect($cnaes)->where('principal', false)->values()->all();
        $logradouro = trim(implode(' ', array_filter([$end['tipo_logradouro'] ?? null, $end['logradouro'] ?? null])));

        $map = [
            'razao_social' => $dados['razao_social'] ?? null,
            'nome_fantasia' => $dados['nome_fantasia'] ?? null,
            'situacao_cadastral' => $dados['situacao_cadastral'] ?? null,
            'uf' => $end['uf'] ?? null,
            'cep' => $end['cep'] ?? null,
            'municipio' => $end['municipio'] ?? null,
            'codigo_municipal' => $end['codigo_municipio'] ?? null,
            'endereco' => $logradouro ?: null,
            'numero' => $end['numero'] ?? null,
            'complemento' => $end['complemento'] ?? null,
            'bairro' => $end['bairro'] ?? null,
            'telefone' => $dados['telefone_1'] ?? null,
            'capital_social' => $dados['capital_social'] ?? null,
            'natureza_juridica' => $dados['natureza_juridica'] ?? null,
            'porte' => $dados['porte'] ?? null,
            'data_inicio_atividade' => $dados['data_inicio_atividade'] ?? null,
            'cnae_principal' => $principal['codigo'] ?? null,
            'cnae_principal_descricao' => $principal['descricao'] ?? null,
            'cnaes_secundarios' => $secundarios ?: null,
            'qsa' => $dados['qsa'] ?? null,
        ];

        $fillable = $alvo->getFillable();
        $mudou = false;

        foreach ($map as $col => $valor) {
            if ($valor === null || $valor === '' || ! in_array($col, $fillable, true)) {
                continue;
            }

            $atual = $alvo->{$col};
            $vazio = $atual === null || $atual === '' || (is_array($atual) && count($atual) === 0);

            if (in_array($col, self::VOLATEIS, true) || $vazio) {
                $alvo->{$col} = $valor;
                $mudou = true;
            }
        }

        if ($this->aplicarRegime($alvo, $dados, $fillable)) {
            $mudou = true;
        }

        if (in_array('ultima_consulta_em', $fillable, true)) {
            $alvo->ultima_consulta_em = now();
            $mudou = true;
        }

        if ($mudou) {
            $alvo->save();
        }

        return $mudou;
    }

    /**
     * Regime tributário é volátil (sempre atualiza), MAS com hierarquia de origem:
     * RFB direta > RFB da matriz > estimado pelo sistema. Estimativa nunca sobrescreve
     * regime real já persistido; regime real sempre vence (e limpa origem/nota antigas).
     * A nota anda JUNTO com o regime — consulta nova com regime direto (sem nota) limpa
     * a nota antiga, senão a ficha exibiria "Lucro Real — foi optante do Simples..." velho.
     */
    private function aplicarRegime(Model $alvo, array $dados, array $fillable): bool
    {
        $regime = trim((string) ($dados['regime_tributario'] ?? ''));
        if ($regime === '' || ! in_array('regime_tributario', $fillable, true)) {
            return false;
        }

        $origem = $dados['regime_tributario_origem'] ?? null; // null (RFB) | 'matriz' | 'estimado'

        $atual = trim((string) $alvo->regime_tributario);
        $origemAtual = in_array('regime_tributario_origem', $fillable, true)
            ? $alvo->regime_tributario_origem
            : null;
        $atualEhReal = $atual !== ''
            && strcasecmp($atual, 'Não informado') !== 0
            && $origemAtual !== 'estimado';

        if ($origem === 'estimado' && $atualEhReal) {
            return false;
        }

        $mudou = false;
        $novos = [
            'regime_tributario' => $regime,
            'regime_tributario_origem' => $origem,
            'regime_tributario_nota' => $dados['regime_tributario_nota'] ?? null,
        ];
        foreach ($novos as $col => $valor) {
            if (! in_array($col, $fillable, true)) {
                continue;
            }
            if ($alvo->{$col} !== $valor) {
                $alvo->{$col} = $valor;
                $mudou = true;
            }
        }

        return $mudou;
    }
}
