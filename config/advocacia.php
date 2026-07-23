<?php

return [
    // Vertical advocacia — consulta à la carte por fonte (docs/advocacia/consultas-certidoes.md).
    // PREÇO DE VENDA por fonte, em R$ (≠ consultas.fontes.*, que é o CUSTO InfoSimples usado no
    // estorno). Default R$ 1,00/fonte (decisão 2026-07-21); ajuste guiado pelo header.price
    // observado nas respostas. Override por fonte em 'precos'.
    'preco_fonte_default' => (float) env('ADVOCACIA_PRECO_FONTE_DEFAULT', 1.00),

    // E-mail para onde a certidão é enviada quando a fonte exige `email` no formulário (TRF
    // unificada via CJF; TJs 2-etapas na fase 4). MVP: e-mail de sistema do Felipe (recebe,
    // baixa, repassa ao usuário). Depois: caixa de sistema dedicada. Nunca o e-mail do usuário.
    'email_solicitante' => env('ADVOCACIA_EMAIL_SOLICITANTE', 'felipedaige@gmail.com'),

    // (Removido: `cpf_solicitante` de sistema. O CPF do solicitante da CEAT é SEMPRE o do dono da
    // conta — users.cpf, injetado no alvo pelo job. Um CPF de sistema como requerente emitiria
    // certidão em nome de terceiro, PII. Sem CPF do dono → CEAT INDISPONÍVEL, não cobra.)

    // Teto de presets pessoais ("meus planos") por usuário — a tela de consulta carrega todos a
    // cada render, então a lista precisa de fim.
    'max_presets_por_usuario' => (int) env('ADVOCACIA_MAX_PRESETS', 30),

    // Fontes JÁ VALIDADAS com CPF real autorizado. As classes podem ter o branch PF pronto, mas
    // `aceitaPessoa()` só libera o CPF quando a chave estiver nesta lista — evita vender uma
    // capacidade apenas teórica antes de confirmar params, payload, cobrança e comprovante.
    // CSV de CHAVES (não slugs). Ex.: cndt,certidao_stj.
    'fontes_pf_liberadas' => array_values(array_filter(array_map(
        fn ($chave) => trim((string) $chave),
        explode(',', (string) env('ADVOCACIA_FONTES_PF_LIBERADAS', ''))
    ))),

    // Fontes inteiramente novas com código pronto, mas ainda sem smoke/payload real validado.
    // Permanecem registradas e visíveis como manutenção até a chave entrar neste CSV.
    'fontes_publicas_liberadas' => array_values(array_filter(array_map(
        fn ($chave) => trim((string) $chave),
        explode(',', (string) env('ADVOCACIA_FONTES_PUBLICAS_LIBERADAS', ''))
    ))),

    // Dados criminais/mandados são sensíveis. Código/testes podem existir, mas nenhuma fonte
    // aparece nem roda em produção sem liberação individual após base legal + retenção.
    'fontes_sensiveis' => [
        'antecedentes_pf' => (bool) env('ADVOCACIA_ANTECEDENTES_PF_HABILITADO', false),
        'mandado_prisao' => (bool) env('ADVOCACIA_MANDADO_PRISAO_HABILITADO', false),
    ],

    // Campos adicionais do alvo exigidos antes de cobrar uma fonte PF. O controller valida de
    // novo; o JS usa o mesmo mapa renderizado nos data-attributes para habilitar o submit.
    'requisitos_pf' => [
        'cadastro_pf' => ['birthdate'],
        'cnd_federal' => ['birthdate'],
        'bcb_valores_receber' => ['birthdate'],
        'quitacao_eleitoral' => ['nome', 'birthdate'],
        'antecedentes_pf' => ['nome', 'birthdate', 'nome_mae', 'nome_pai', 'uf_nascimento'],
        'mandado_prisao' => [],
    ],

    // Campos exigidos de TODO alvo (PF ou PJ), além dos requisitos específicos de PF.
    'requisitos_alvo' => [
        'ibama_autuacoes' => ['ano'],
    ],

    // Metadados da VITRINE, inclusive para fontes que ainda não possuem classe Laravel. Estes
    // campos nunca liberam execução: a autoridade operacional continua sendo FonteRegistry +
    // Fonte::pronta() + Fonte::aceitaPessoa(). `tipos_pessoa` aqui descreve o destino planejado.
    'catalogo_fontes' => [
        // Ampliações PF de fontes já operacionais para CNPJ.
        'cnd_federal' => ['tipos_pessoa' => ['PF', 'PJ']],
        'cndt' => ['tipos_pessoa' => ['PF', 'PJ']],
        'certidao_stj' => ['tipos_pessoa' => ['PF', 'PJ']],
        'certidao_trf' => ['tipos_pessoa' => ['PF', 'PJ']],
        'certidao_mpt' => ['tipos_pessoa' => ['PF', 'PJ']],
        'certidao_mpf' => ['tipos_pessoa' => ['PF', 'PJ']],
        'improbidade' => ['tipos_pessoa' => ['PF', 'PJ']],
        'ceis' => ['tipos_pessoa' => ['PF', 'PJ']],
        'cnep' => ['tipos_pessoa' => ['PF', 'PJ']],
        'crf_fgts' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'descricao' => 'Para pessoa física, aplica-se ao empregador inscrito no FGTS.',
        ],

        // Novas fontes públicas prioritárias.
        'pgfn_devedores' => ['tipos_pessoa' => ['PF', 'PJ']],
        'tcu_cnp' => ['tipos_pessoa' => ['PF', 'PJ']],
        'tcu_cni_inidoneo' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'descricao' => 'Relação de inidôneos do TCU (tipo de relação 1).',
        ],
        'tcu_cni_inabilitado' => [
            'tipos_pessoa' => ['PF'],
            'descricao' => 'Relação de inabilitados do TCU (tipo de relação 2).',
        ],
        'bcb_valores_receber' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'descricao' => 'Informa se existem valores; não revela saldo ou instituição.',
        ],
        'inpi_marcas_titular' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'descricao' => 'Consulta a primeira página e informa quando existem páginas adicionais.',
        ],
        'simples_nacional' => ['tipos_pessoa' => ['PJ']],

        // Ambiental.
        'ibama_embargos' => ['tipos_pessoa' => ['PF', 'PJ']],
        'ibama_debitos' => ['tipos_pessoa' => ['PF', 'PJ']],
        'ibama_regularidade' => ['tipos_pessoa' => ['PF', 'PJ']],
        'ibama_autuacoes' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'descricao' => 'Consulta um ano por chamada e retorna a primeira página.',
        ],

        // Imóveis/rural: algumas fontes começam pela pessoa; outras pelo identificador do bem.
        'sigef_parcelas' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'requer_autenticacao' => true,
        ],
        'sigef_requerimentos' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'requer_autenticacao' => true,
        ],
        'sigef_detalhes_parcela' => [
            'tipos_pessoa' => [],
            'documentos_label' => 'Código da parcela',
        ],
        'car_imovel' => [
            'tipos_pessoa' => [],
            'documentos_label' => 'Número do CAR',
        ],
        'car_demonstrativo' => [
            'tipos_pessoa' => [],
            'documentos_label' => 'Número do CAR',
        ],
        'cafir_imovel' => [
            'tipos_pessoa' => [],
            'documentos_label' => 'CIB do imóvel',
        ],
        'nirf_imovel' => [
            'tipos_pessoa' => [],
            'documentos_label' => 'NIRF ou CIB',
        ],
        'sncr_ccir' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'documentos_label' => 'CPF/CNPJ + imóvel',
        ],
        'spu_imovel' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'documentos_label' => 'CPF/CNPJ + RIP',
        ],
        'onr_mapa_imovel' => [
            'tipos_pessoa' => [],
            'documentos_label' => 'CAR ou endereço',
        ],
        'arisp_matricula' => [
            'tipos_pessoa' => [],
            'documentos_label' => 'Matrícula',
            'requer_autenticacao' => true,
        ],
        'arisp_certidao' => [
            'tipos_pessoa' => [],
            'documentos_label' => 'Matrícula',
            'requer_autenticacao' => true,
        ],

        // Consulta processual por parte.
        'tse_processos' => ['tipos_pessoa' => ['PF', 'PJ']],
        'trf2_processos' => ['tipos_pessoa' => ['PF', 'PJ']],
        'trf3_processos' => ['tipos_pessoa' => ['PF', 'PJ']],
        'trf5_processos' => ['tipos_pessoa' => ['PF', 'PJ']],
        'tjsc_processos' => ['tipos_pessoa' => ['PF', 'PJ']],
        'tjrj_processos' => ['tipos_pessoa' => ['PF', 'PJ']],

        // Próxima etapa autenticada.
        'receita_situacao_fiscal' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'requer_autenticacao' => true,
        ],
        'bcb_cheques_sem_fundo' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'requer_autenticacao' => true,
        ],
        'protestos' => [
            'tipos_pessoa' => ['PF', 'PJ'],
            'requer_autenticacao' => true,
        ],
    ],

    'precos' => [
        // Consulta cadastral (situação) é GRÁTIS (minhareceita, custo zero) — plano Gratuito.
        'cadastro' => 0.00,
        // 'cnd_municipal' => 1.50,  // exemplo de override pontual

        // Fonte de 2 ETAPAS custa ≥2 chamadas pagas (pedido + N conferências ~R$0,24 cada), então
        // não cabe no R$ 1,00 das single-call — a R$ 1,00 a margem virava negativa no pior caso.
        // Reverter é 1 linha se a decisão comercial for outra.
        'certidao_tjms' => 2.00,
    ],

    // TJMS — certidão cível de 2 etapas (fase 4). `modelo`/`comarca` exigem GRAFIA EXATA do eSAJ.
    // VALIDADO por smoke real (pedido 10559945, 2026-07-22): modelo "WEB - Ação Cível" +
    // comarca "Dourados" → 200. `comarca` default = Title-case do município (minhareceita manda
    // em caixa alta); o mapa abaixo cobre exceções acentuadas / grafia divergente.
    'tjms' => [
        'modelo' => env('ADVOCACIA_TJMS_MODELO', 'WEB - Ação Cível'),
        // Override município (CAIXA ALTA, como vem da minhareceita) → comarca (grafia exata eSAJ).
        'comarca_por_municipio' => [
            // exceções que o Title-case não acerta (acento / preposição minúscula):
            // 'AGUA CLARA' => 'Água Clara', 'CORUMBA' => 'Corumbá', 'PONTA PORA' => 'Ponta Porã',
        ],
        // Cada conferência (`obter-certidao`) é uma chamada PAGA (~R$ 0,24). Intervalo curto e fixo
        // dava margem NEGATIVA (12 polls = ~R$ 2,88 contra R$ 1,00 de receita). Escala + teto:
        // 1ª em 1h (o TJMS costuma emitir no mesmo dia → resolve em 1 chamada), depois 4h/12h/24h.
        // 1ª conferência 1h após o pedido; depois o backoff da escala. `prazo_inicial_min` é o
        // 1º degrau: mantenha-o = intervalos_min[0] para uma cadência única (1h,+1h,+4h,+12h,+24h).
        'prazo_inicial_min' => (int) env('ADVOCACIA_TJMS_PRAZO_INICIAL_MIN', 60),
        'intervalos_min' => [60, 240, 720, 1440],
        'max_verificacoes' => (int) env('ADVOCACIA_TJMS_MAX_VERIFICACOES', 5),
    ],

    // Grupos de apresentação da tela de seleção (ordem = ordem visual). As fontes judiciais
    // da fase 2 entram em grupos novos (judicial, trabalhista, integridade, passivo).
    'grupos' => [
        'pessoa_fisica' => [
            'label' => 'Pessoa física',
            'fontes' => ['cadastro_pf', 'quitacao_eleitoral', 'antecedentes_pf', 'mandado_prisao'],
        ],
        'judicial' => [
            'label' => 'Certidões judiciais',
            'fontes' => ['certidao_stj', 'certidao_trf', 'ceat_trt', 'certidao_mpt', 'certidao_mpf', 'certidao_tjms'],
        ],
        'integridade' => [
            'label' => 'Integridade e sanções',
            'fontes' => [
                'certidao_tcu', 'tcu_cnp', 'tcu_cni_inidoneo', 'tcu_cni_inabilitado',
                'improbidade', 'ceis', 'cnep',
            ],
        ],
        'ambiental' => [
            'label' => 'Ambiental',
            'fontes' => ['ibama_embargos', 'ibama_debitos', 'ibama_regularidade', 'ibama_autuacoes'],
        ],
        'patrimonio' => [
            'label' => 'Patrimônio e ativos',
            'fontes' => ['bcb_valores_receber', 'inpi_marcas_titular', 'bcb_cheques_sem_fundo'],
        ],
        'imoveis' => [
            'label' => 'Imóveis e propriedade rural',
            'fontes' => [
                'sigef_parcelas', 'sigef_requerimentos', 'sigef_detalhes_parcela',
                'car_imovel', 'car_demonstrativo', 'cafir_imovel', 'nirf_imovel',
                'sncr_ccir', 'spu_imovel', 'onr_mapa_imovel',
                'arisp_matricula', 'arisp_certidao',
            ],
        ],
        'processual' => [
            'label' => 'Consultas processuais',
            'fontes' => [
                'tse_processos', 'trf2_processos', 'trf3_processos',
                'trf5_processos', 'tjsc_processos', 'tjrj_processos',
            ],
        ],
        'passivo' => [
            'label' => 'Passivo e insolvência',
            'fontes' => ['pgfn_devedores', 'protestos', 'falencias'],
        ],
        'fiscal' => [
            'label' => 'Cadastro e certidões fiscais',
            // `cadastro` (situação, GRÁTIS) é selecionável p/ rodar a consulta cadastral pura (plano
            // Gratuito). analise_fiscal (paga) abre o raio-X tributário derivado do mesmo cadastro.
            'fontes' => [
                'cadastro', 'analise_fiscal', 'simples_nacional', 'cnd_federal',
                'cnd_estadual', 'cnd_municipal', 'crf_fgts', 'cndt', 'sintegra',
                'receita_situacao_fiscal',
            ],
        ],
    ],
];
