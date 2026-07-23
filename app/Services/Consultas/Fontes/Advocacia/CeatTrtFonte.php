<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

/**
 * CEAT — Certidão Eletrônica de Ações Trabalhistas do TRT da REGIÃO do alvo.
 * ≠ CNDT (TST): a CNDT atesta débitos; a CEAT lista AÇÕES em andamento na região.
 * Slug dinâmico `tribunal/trt{n}/ceat`, resolvido pela UF do alvo (o cadastro roda antes e
 * injeta UF e município autoritativos da RFB).
 *
 * SP é o ÚNICO estado com dois TRTs e por isso resolve por MUNICÍPIO, não por UF: a 2ª Região
 * cobre 46 municípios (Grande São Paulo + Ibiúna + Baixada Santista, menos Mongaguá, Itanhaém e
 * Peruíbe) e a 15ª Região cobre os outros 599 — todo o interior, incluindo Ribeirão Preto,
 * Campinas, Sorocaba e São José dos Campos. Mandar o CNPJ do interior pro TRT2 devolve uma
 * negativa FALSA (o tribunal não enxerga os processos da outra região), então sem município a
 * fonte fica INDISPONÍVEL em vez de chutar.
 */
class CeatTrtFonte extends FonteCertidaoInfoSimples
{
    /** UF → nº do TRT regional. SP fica de fora: resolve por município (ver SP_TRT2_MUNICIPIOS). */
    public const UF_TRT = [
        'RJ' => 1, 'MG' => 3, 'RS' => 4, 'BA' => 5, 'PE' => 6, 'CE' => 7,
        'PA' => 8, 'AP' => 8, 'PR' => 9, 'DF' => 10, 'TO' => 10, 'AM' => 11, 'RR' => 11,
        'SC' => 12, 'PB' => 13, 'RO' => 14, 'AC' => 14, 'MA' => 16, 'ES' => 17, 'GO' => 18,
        'AL' => 19, 'SE' => 20, 'RN' => 21, 'PI' => 22, 'MT' => 23, 'MS' => 24,
    ];

    /**
     * Os 46 municípios da jurisdição do TRT2 (forma canônica de `normalizarCidade`). Qualquer
     * outro município de SP é TRT15/Campinas. Fonte: jurisdição oficial do TRT-2 (Grande SP +
     * Ibiúna + Baixada Santista exceto Mongaguá/Itanhaém/Peruíbe).
     */
    public const SP_TRT2_MUNICIPIOS = [
        'aruja', 'barueri', 'bertioga', 'biritiba-mirim', 'caieiras', 'cajamar', 'carapicuiba',
        'cotia', 'cubatao', 'diadema', 'embu-das-artes', 'embu-guacu', 'ferraz-de-vasconcelos',
        'francisco-morato', 'franco-da-rocha', 'guararema', 'guaruja', 'guarulhos', 'ibiuna',
        'itapecerica-da-serra', 'itapevi', 'itaquaquecetuba', 'jandira', 'juquitiba', 'mairipora',
        'maua', 'mogi-das-cruzes', 'osasco', 'pirapora-do-bom-jesus', 'poa', 'praia-grande',
        'ribeirao-pires', 'rio-grande-da-serra', 'salesopolis', 'santa-isabel',
        'santana-de-parnaiba', 'santo-andre', 'santos', 'sao-bernardo-do-campo',
        'sao-caetano-do-sul', 'sao-lourenco-da-serra', 'sao-paulo', 'sao-vicente', 'suzano',
        'taboao-da-serra', 'vargem-grande-paulista',
    ];

    /** TRT cujo endpoint de CEAT foge do padrão `tribunal/trt{n}/ceat`. */
    private const SLUG_EXCECAO = [6 => 'tribunal/trt6/certidao'];

    /** TRT sem endpoint de CEAT na InfoSimples (o TRT22/PI só publica consulta processual). */
    private const SEM_CEAT = [22];

    public function chave(): string
    {
        return 'ceat_trt';
    }

    public function slug(): string
    {
        return 'tribunal/trt{n}/ceat';
    }

    public function slugPara(array $alvo): string
    {
        $trt = $this->trtPara($alvo);

        if ($trt === null) {
            return $this->slug();
        }

        return self::SLUG_EXCECAO[$trt] ?? "tribunal/trt{$trt}/ceat";
    }

    /**
     * TRT competente para o alvo, ou null quando não dá para resolver com segurança (a fonte
     * então fica INDISPONÍVEL, sem chamada nem cobrança).
     */
    public function trtPara(array $alvo): ?int
    {
        $uf = strtoupper(trim((string) ($alvo['uf'] ?? '')));

        if ($uf === 'SP') {
            $cidade = static::normalizarCidade((string) ($alvo['municipio'] ?? ''));

            if ($cidade === '') {
                return null; // sem município não dá pra distinguir TRT2 de TRT15 — ver docblock
            }

            return in_array($cidade, self::SP_TRT2_MUNICIPIOS, true) ? 2 : 15;
        }

        $trt = self::UF_TRT[$uf] ?? null;

        return $trt !== null && ! in_array($trt, self::SEM_CEAT, true) ? $trt : null;
    }

    public function aplicavelPara(array $alvo): bool
    {
        // Três pré-condições, todas billable-safe (INDISPONÍVEL sem cobrar em vez de 606 pago):
        //  1. TRT resolvido pela UF (e, em SP, pelo município) e com endpoint de CEAT.
        //  2. `nome`/razão social — obrigatório no formulário (smoke lote 260: 606 sem ele).
        //  3. `cpf_solicitante` resolvido — TRT24 (MS) e outros exigem (lote 261: 606 "CPF do
        //     solicitante é obrigatório"). Sem CPF do dono da conta, não firar 606 billable.
        return $this->trtPara($alvo) !== null
            && trim((string) ($alvo['razao_social'] ?? '')) !== ''
            && $this->cpfSolicitante($alvo) !== '';
    }

    public function motivoIndisponivel(array $alvo): string
    {
        $uf = strtoupper(trim((string) ($alvo['uf'] ?? '')));

        if ($this->trtPara($alvo) === null) {
            if ($uf === 'SP') {
                return 'CEAT em SP depende do município da sede para separar TRT2 (Grande São Paulo e '
                    .'Baixada Santista) de TRT15 (interior) — município indisponível no cadastro deste CNPJ.';
            }

            if ((self::UF_TRT[$uf] ?? null) === 22) {
                return 'O TRT22 (PI) não emite CEAT pelo provedor — use a CNDT para débitos trabalhistas.';
            }

            return 'CEAT exige a UF da sede para resolver o TRT regional — UF indisponível no cadastro deste CNPJ.';
        }

        if (trim((string) ($alvo['razao_social'] ?? '')) === '') {
            return 'CEAT exige o nome/razão social do consultado — indisponível no cadastro deste CNPJ.';
        }

        return 'CEAT exige o CPF do solicitante — cadastre o CPF do responsável no seu perfil para emitir esta certidão.';
    }

    public function params(array $alvo): array
    {
        // Doc oficial (docs/infosimples/tribunais-certidoes-judiciais.md + doc pública InfoSimples
        // trt24/ceat): `nome` (razão social do consultado) + `cnpj` + `cpf_solicitante` (CPF de
        // quem PEDE a certidão — o DONO DA CONTA, não o alvo). TRT24 exige `cpf_solicitante` (606
        // sem); TRTs que não o exigem simplesmente o ignoram. O resultado confiável pro CNPJ é
        // processos_encontrados_cpf_cnpj (busca nominal pega homônimo).
        return parent::params($alvo) + [
            'nome' => trim((string) ($alvo['razao_social'] ?? '')),
            'cpf_solicitante' => $this->cpfSolicitante($alvo),
        ];
    }

    /**
     * CPF do solicitante (só dígitos) = SEMPRE o do dono da conta, injetado no alvo pelo job a
     * partir de `users.cpf`. Sem fallback de config de sistema: um CPF de sistema como requerente
     * ligaria a fonte pra QUALQUER usuário sem CPF e emitiria a certidão no tribunal em nome de um
     * terceiro (PII). Sem CPF do dono → fonte fica INDISPONÍVEL (aplicavelPara=false), não cobra.
     */
    private function cpfSolicitante(array $alvo): string
    {
        return \App\Support\Cpf::digitos((string) ($alvo['cpf_solicitante'] ?? ''));
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.ceat_trt', 1.00);
    }

    protected function mapearSucesso(array $data): array
    {
        // Contrato real (doc detalhada): conseguiu_emitir_certidao_negativa/nada_consta,
        // numero_certidao, normalizado_expedicao_datahora ("d/m/Y H:i:s"), total_processos e
        // processos_encontrados_cpf_cnpj. A busca nominal pega homônimos — o dado confiável
        // pro CNPJ é processos_encontrados_cpf_cnpj.
        $expedicao = trim((string) ($data['normalizado_expedicao_datahora'] ?? ''));
        $doCnpj = is_array($data['processos_encontrados_cpf_cnpj'] ?? null)
            ? $data['processos_encontrados_cpf_cnpj']
            : [];

        return [
            'status' => $this->statusCertidao($data),
            'certidao_codigo' => $data['numero_certidao'] ?? null,
            'emissao_data' => preg_match('#^(\d{2}/\d{2}/\d{4})#', $expedicao, $m) ? $m[1] : null,
            'data_validade' => $data['validade_data'] ?? null,
            'nada_consta' => $data['nada_consta'] ?? null,
            'total_processos' => $data['total_processos'] ?? null,
            'processos_cnpj_quantidade' => $doCnpj['quantidade'] ?? null,
            'processos_cnpj' => array_slice((array) ($doCnpj['lista_processos'] ?? []), 0, 20),
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
