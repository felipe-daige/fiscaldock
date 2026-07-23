<?php

namespace App\Services\Consultas\Fontes;

class CndMunicipalFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'cnd_municipal';
    }

    public function slug(): string
    {
        return ''; // dinâmico por UF/cidade — ver slugPara()
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.cnd_municipal', 0.40);
    }

    public function slugPara(array $alvo): string
    {
        return $this->resolverSlug($alvo) ?? '';
    }

    public function aplicavelPara(array $alvo): bool
    {
        // Cobertura InfoSimples por município é parcial — só aplica quando há slug mapeado
        // para a (UF, cidade) do alvo. A cidade vem do cadastro (minhareceita).
        if ($this->resolverSlug($alvo) === null) {
            return false;
        }

        // Município que EXIGE inscrição municipal e não a temos → não consulta (evita o 606
        // billable, cobrado por consulta fadada). O job resolve a IM ANTES desta checagem, então
        // aqui `$alvo['inscricao_municipal']` já reflete perfil/cross-cadastro/XML.
        if ($this->requerIm($alvo) && $this->imDoAlvo($alvo) === '') {
            return false;
        }

        return true;
    }

    public function motivoIndisponivel(array $alvo): string
    {
        $cidade = trim((string) ($alvo['municipio'] ?? ''));
        $uf = strtoupper((string) ($alvo['uf'] ?? ''));

        if ($cidade === '' || $uf === '') {
            return 'CND Municipal não consultada: município/UF do contribuinte não identificado.';
        }

        if ($this->requerIm($alvo) && $this->imDoAlvo($alvo) === '') {
            return "CND Municipal de {$cidade}/{$uf} exige inscrição municipal. "
                .'Cadastre a IM no perfil do CNPJ para consultar.';
        }

        return "CND Municipal não disponível para {$cidade}/{$uf} no provedor.";
    }

    /** Município cujo endpoint InfoSimples exige `inscricao_municipal` de entrada (config). */
    private function requerIm(array $alvo): bool
    {
        $uf = strtolower(trim((string) ($alvo['uf'] ?? '')));
        $cidade = static::normalizarCidade((string) ($alvo['municipio'] ?? ''));

        if ($uf === '' || $cidade === '') {
            return false;
        }

        return in_array($uf.':'.$cidade, (array) config('consultas.cnd_municipal.requer_im', []), true);
    }

    private function imDoAlvo(array $alvo): string
    {
        return trim((string) ($alvo['inscricao_municipal'] ?? ''));
    }

    public function params(array $alvo): array
    {
        // UF/cidade já estão na URL (slug); o provider só precisa do CNPJ.
        $params = parent::params($alvo) + [
            'uf' => strtoupper((string) ($alvo['uf'] ?? '')),
            'municipio' => (string) ($alvo['municipio'] ?? ''),
        ];

        // Manda `inscricao_municipal` APENAS aos municípios que exigem (config `requer_im`). Nos
        // demais a consulta roda por CNPJ; mandar o param extra arriscaria 607 (param inválido) e
        // quebraria município que hoje funciona. O NÚMERO é resolvido 1x e injetado no alvo pelo
        // ProcessarConsultaJob (InscricaoMunicipalResolver) — aqui só repassamos.
        $im = $this->imDoAlvo($alvo);
        if ($im !== '' && $this->requerIm($alvo)) {
            $params['inscricao_municipal'] = $im;
        }

        return $params;
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        // SAFETY-NET p/ code 606 ("parâmetros obrigatórios não enviados"): município que exige
        // `inscricao_municipal` mas ainda NÃO está no mapa `requer_im` (a gate do aplicavelPara já
        // pula os conhecidos sem IM, sem chamar). Se um desconhecido escapar e o provedor rejeitar
        // com 606, degrada p/ INDISPONIVEL em vez do erro vermelho de `_fontes_erro`. Estorno
        // intacto (status segue 'fatal' → ehFalhaEstornavel), então o cliente não paga a falha.
        // Ao ver 606 recorrente de um município, adicioná-lo a config `cnd_municipal.requer_im`.
        if ((int) ($raw['code'] ?? 0) === 606) {
            return $this->blocoIndisponivel([
                '_motivo' => 'CND Municipal indisponível: esta prefeitura exige inscrição municipal. '
                    .'Cadastre a IM no perfil do CNPJ para consultar.',
            ]);
        }

        return parent::normalizar($raw, $status);
    }

    protected function mapearSucesso(array $data): array
    {
        // Cada prefeitura InfoSimples tem seu próprio schema no data[0] — os campos
        // equivalentes vêm com nomes diferentes por município. Coalescer os dialetos
        // conhecidos (ex: emissao_data/data_emissao, numero_certidao/codigo_controle_certidao).
        $endereco = is_array($data['endereco'] ?? null) ? $data['endereco'] : [];

        return [
            'uf' => $data['uf'] ?? ($endereco['uf'] ?? null),
            'municipio' => $data['municipio'] ?? ($data['cidade'] ?? ($endereco['cidade'] ?? null)),
            // A resposta da CND costuma trazer a inscrição municipal do contribuinte. Expomos
            // pra o job colher e gravar no perfil (grátis — sem chamada extra). Reusada depois
            // via cross-cadastro e pelas prefeituras que EXIGEM a IM de entrada.
            // SÓ chaves inequívocas de IM — nada de fallback genérico (`inscricao` poderia ser
            // inscrição estadual/protocolo e envenenaria o perfil, propagando via cross-cadastro).
            'inscricao_municipal' => $data['inscricao_municipal']
                ?? ($data['normalizado_inscricao_municipal'] ?? null),
            'status' => $this->statusCertidao($data),
            'certidao_codigo' => $data['certidao_codigo']
                ?? ($data['numero_certidao'] ?? ($data['codigo_controle_certidao'] ?? null)),
            'emissao_data' => $data['emissao_data']
                ?? ($data['data_emissao'] ?? ($data['normalizado_datahora_emissao'] ?? null)),
            'data_validade' => $data['validade_data'] ?? ($data['validade'] ?? null),
            'conseguiu_emitir' => (bool) ($data['conseguiu_emitir_certidao_negativa'] ?? false),
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }

    private function resolverSlug(array $alvo): ?string
    {
        $uf = strtolower(trim((string) ($alvo['uf'] ?? '')));
        $cidade = static::normalizarCidade((string) ($alvo['municipio'] ?? ''));

        if ($uf === '' || $cidade === '') {
            return null;
        }

        return config('consultas.cnd_municipal.slugs')[$uf.':'.$cidade] ?? null;
    }
}
