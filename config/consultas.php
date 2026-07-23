<?php

return [
    'comprovantes' => [
        // Best-effort: uma falha de arquivamento nunca pode invalidar a consulta já cobrada.
        'arquivar' => (bool) env('CONSULTAS_ARQUIVAR_COMPROVANTES', true),
    ],

    'providers' => [
        'minhareceita' => [
            'base_url' => env('MINHARECEITA_BASE_URL', 'https://minhareceita.org'),
            'timeout' => (int) env('MINHARECEITA_TIMEOUT', 20),
            'tries' => (int) env('MINHARECEITA_TRIES', 2),
        ],
        'infosimples' => [
            'base_url' => env('INFOSIMPLES_BASE_URL', 'https://api.infosimples.com/api/v2/consultas'),
            'token' => env('INFOSIMPLES_TOKEN'),
            'timeout' => (int) env('INFOSIMPLES_TIMEOUT', 120),
            'tries' => (int) env('INFOSIMPLES_TRIES', 3),
            'rate_limit_por_segundo' => (float) env('INFOSIMPLES_RATE_LIMIT', 1),
        ],
    ],

    // Liga/desliga do provider InfoSimples: enquanto false, as fontes InfoSimples
    // não são consultadas. Ligar só após pagar/validar o InfoSimples e confirmar o
    // estorno preciso por fonte. ENV: CONSULTAS_INFOSIMPLES_ATIVO.
    'infosimples_ativo' => (bool) env('CONSULTAS_INFOSIMPLES_ATIVO', false),

    // GUARD DE TESTE: se NÃO vazio, só estes CNPJs (14 dígitos, CSV) realmente chamam o
    // InfoSimples — qualquer outro é bloqueado ANTES da chamada (sem cobrança). Use durante os
    // testes pagos p/ não consumir saldo por engano. Vazio = produção normal (todos passam).
    // ENV: CONSULTAS_INFOSIMPLES_TESTE_CNPJS.
    'infosimples_teste_cnpjs' => array_values(array_filter(array_map(
        fn ($c) => preg_replace('/[^0-9]/', '', (string) $c),
        explode(',', (string) env('CONSULTAS_INFOSIMPLES_TESTE_CNPJS', ''))
    ))),

    // Mesmo guard para fontes de pessoa física. Vazio = todos os CPFs liberados.
    'infosimples_teste_cpfs' => array_values(array_filter(array_map(
        fn ($c) => preg_replace('/[^0-9]/', '', (string) $c),
        explode(',', (string) env('CONSULTAS_INFOSIMPLES_TESTE_CPFS', ''))
    ))),

    // Fontes TEMPORARIAMENTE pausadas na origem (InfoSimples pausa o endpoint globalmente quando
    // o site oficial está instável/em manutenção — ex.: tst/banco-falencias=falencias,
    // ieptb/protestos=protestos). Enquanto na lista, a fonte fica `pronta()=false`: some da tela
    // de seleção, não entra no registry, não é cobrada. Não é bug de código nem permissão —
    // reabrir REMOVENDO da lista quando a InfoSimples despausar (re-testar). CSV de CHAVES de fonte
    // (não slugs). Controle OPERACIONAL por ENV (não baked no código): CONSULTAS_FONTES_PAUSADAS.
    // Prod hoje (2026-07-22): `falencias,protestos` (pausadas na origem InfoSimples).
    'fontes_pausadas' => array_values(array_filter(array_map(
        fn ($c) => trim((string) $c),
        explode(',', (string) env('CONSULTAS_FONTES_PAUSADAS', ''))
    ))),

    // Mapa fonte → etapa (grupo) do progresso. Várias fontes compartilham a mesma etapa
    // (ex: cnd_federal/cndt/crf_fgts = certidoes_federais), p/ o strip avançar por grupo e não
    // repetir um "loop" por fonte. As chaves de etapa vêm de PlanoCatalog (resolvedEtapas).
    'fonte_etapa' => [
        'cadastro' => 'cadastrais',
        'analise_fiscal' => 'cadastrais',
        'cnd_federal' => 'certidoes_federais',
        'cndt' => 'certidoes_federais',
        'crf_fgts' => 'certidoes_federais',
        'cnd_estadual' => 'certidoes_estaduais',
        'cnd_municipal' => 'certidoes_estaduais',
        'sintegra' => 'certidoes_estaduais',
        // Vertical advocacia (grupos novos do strip — só aparecem em lote avulso).
        'certidao_stj' => 'certidoes_judiciais',
        'certidao_trf' => 'certidoes_judiciais',
        'ceat_trt' => 'certidoes_judiciais',
        'certidao_mpt' => 'certidoes_judiciais',
        'certidao_mpf' => 'certidoes_judiciais',
        'certidao_tjms' => 'certidoes_judiciais',
        'certidao_tcu' => 'integridade',
        'improbidade' => 'integridade',
        'ceis' => 'integridade',
        'cnep' => 'integridade',
        'protestos' => 'passivo',
        'falencias' => 'passivo',
        'cadastro_pf' => 'cadastrais',
        'quitacao_eleitoral' => 'certidoes_judiciais',
        'antecedentes_pf' => 'certidoes_judiciais',
        'mandado_prisao' => 'integridade',
        // Catálogo futuro: estes grupos só passam a ser usados pelo job quando a fonte for
        // registrada e operacional; antes disso servem apenas à ordenação/documentação.
        'simples_nacional' => 'cadastrais',
        'pgfn_devedores' => 'passivo',
        'tcu_cnp' => 'integridade',
        'tcu_cni_inidoneo' => 'integridade',
        'tcu_cni_inabilitado' => 'integridade',
        'bcb_valores_receber' => 'patrimonio',
        'inpi_marcas_titular' => 'patrimonio',
        'ibama_embargos' => 'ambiental',
        'ibama_debitos' => 'ambiental',
        'ibama_regularidade' => 'ambiental',
        'ibama_autuacoes' => 'ambiental',
        'sigef_parcelas' => 'imoveis',
        'sigef_requerimentos' => 'imoveis',
        'sigef_detalhes_parcela' => 'imoveis',
        'car_imovel' => 'imoveis',
        'car_demonstrativo' => 'imoveis',
        'cafir_imovel' => 'imoveis',
        'nirf_imovel' => 'imoveis',
        'sncr_ccir' => 'imoveis',
        'spu_imovel' => 'imoveis',
        'onr_mapa_imovel' => 'imoveis',
        'arisp_matricula' => 'imoveis',
        'arisp_certidao' => 'imoveis',
        'tse_processos' => 'processual',
        'trf2_processos' => 'processual',
        'trf3_processos' => 'processual',
        'trf5_processos' => 'processual',
        'tjsc_processos' => 'processual',
        'tjrj_processos' => 'processual',
        'receita_situacao_fiscal' => 'cadastrais',
        'bcb_cheques_sem_fundo' => 'patrimonio',
    ],

    // Nome amigável de cada fonte, usado na mensagem de progresso ("Consultando {nome} (i de N)").
    // Dá feedback textual por fonte mesmo quando várias caem na mesma etapa/grupo (ex: as 3
    // federais). Fallback = a própria chave quando não mapeada.
    'fonte_nome' => [
        'cadastro' => 'Situação Cadastral (grátis)',
        'analise_fiscal' => 'Análise Fiscal (regime, Simples, parecer)',
        'cnd_federal' => 'CND Federal (Receita/PGFN)',
        'cndt' => 'CNDT (débitos trabalhistas)',
        'crf_fgts' => 'CRF FGTS (Caixa)',
        'cnd_estadual' => 'CND Estadual (SEFAZ)',
        'cnd_municipal' => 'CND Municipal',
        'sintegra' => 'SINTEGRA',
        'certidao_stj' => 'Certidão STJ',
        'certidao_trf' => 'Certidão Justiça Federal (TRFs)',
        'ceat_trt' => 'CEAT — Ações Trabalhistas (TRT da sede)',
        'certidao_mpt' => 'Certidão MPT (feitos trabalhistas)',
        'certidao_mpf' => 'Certidão MPF',
        'certidao_tjms' => 'Certidão Cível TJMS (2 etapas)',
        'certidao_tcu' => 'TCU — Consolidada (inidôneos/inabilitados)',
        'improbidade' => 'CNJ — Improbidade Administrativa',
        'ceis' => 'CEIS (inidôneas e suspensas)',
        'cnep' => 'CNEP (Lei Anticorrupção)',
        'protestos' => 'Protestos em Cartório (IEPTB)',
        'falencias' => 'Falências e Recuperações (TST)',
        'cadastro_pf' => 'Cadastro e situação do CPF (Receita Federal)',
        'quitacao_eleitoral' => 'Quitação Eleitoral (TSE)',
        'antecedentes_pf' => 'Antecedentes Criminais (Polícia Federal)',
        'mandado_prisao' => 'Mandados de Prisão vigentes (CNJ/BNMP)',
        'simples_nacional' => 'Simples Nacional e SIMEI',
        'pgfn_devedores' => 'Dívida Ativa — Lista de Devedores PGFN',
        'tcu_cnp' => 'TCU — Certidão Negativa de Processo',
        'tcu_cni_inidoneo' => 'TCU — Certidão de Inidôneo',
        'tcu_cni_inabilitado' => 'TCU — Certidão de Inabilitado',
        'bcb_valores_receber' => 'Banco Central — Valores a Receber',
        'inpi_marcas_titular' => 'INPI — Marcas por titular',
        'ibama_embargos' => 'IBAMA — Certidão de Embargos',
        'ibama_debitos' => 'IBAMA — Certidão de Débitos',
        'ibama_regularidade' => 'IBAMA — Certificado de Regularidade',
        'ibama_autuacoes' => 'IBAMA — Autuações Ambientais',
        'sigef_parcelas' => 'INCRA/SIGEF — Parcelas por titular',
        'sigef_requerimentos' => 'INCRA/SIGEF — Requerimentos',
        'sigef_detalhes_parcela' => 'INCRA/SIGEF — Detalhes da Parcela',
        'car_imovel' => 'CAR — Dados e polígono do imóvel',
        'car_demonstrativo' => 'CAR — Demonstrativo de regularidade',
        'cafir_imovel' => 'Receita Federal — CAFIR do imóvel',
        'nirf_imovel' => 'Receita Federal — Certidão NIRF/CIB',
        'sncr_ccir' => 'SNCR — Certificado CCIR',
        'spu_imovel' => 'SPU — Dados de Imóvel da União',
        'onr_mapa_imovel' => 'ONR — Mapa do Registro de Imóveis',
        'arisp_matricula' => 'RI Digital — Matrícula do imóvel',
        'arisp_certidao' => 'RI Digital — Certidão digital do imóvel',
        'tse_processos' => 'TSE/PJe — Processos por parte',
        'trf2_processos' => 'TRF2 — Processos por parte',
        'trf3_processos' => 'TRF3 — Processos por parte',
        'trf5_processos' => 'TRF5 — Processos por parte',
        'tjsc_processos' => 'TJSC — Processos por parte',
        'tjrj_processos' => 'TJRJ — Processos por parte',
        'receita_situacao_fiscal' => 'Receita Federal — Situação Fiscal',
        'bcb_cheques_sem_fundo' => 'Banco Central — Cheques sem Fundo',
    ],

    // Atributos de consultas_incluidas que NÃO são fontes — renderizados inline a partir dos
    // dados já obtidos (ex: parecer_fiscal é um parecer gerado dos dados cadastrais). Não
    // bloqueiam o roteamento pro Laravel nem geram chamada externa.
    'atributos_inline' => ['parecer_fiscal'],

    // Grupos de código InfoSimples → status canônico (fonte: docs/infosimples/endpoints-catalog.md)
    'codigos' => [
        'sucesso' => [200, 201],
        'nao_encontrado' => [612],
        // 611 = a fonte oficial não conseguiu emitir pela internet (dados insuficientes).
        // NÃO é irregularidade — vira INDETERMINADO, preservando a mensagem. Não estorna.
        'indeterminado' => [611],
        'erro_participante' => [608, 619, 620],
        'retry' => [600, 605, 609, 610, 613, 614, 615, 618],
        'fatal' => [601, 602, 603, 604, 606, 607, 617, 621, 622],
    ],

    // Cobertura parcial do InfoSimples para CND Estadual (SEFAZ por UF). Só estas UFs são
    // consultadas; alvo em UF fora da lista é pulado (sem cobrar). Ajustar à cobertura real
    // do plano InfoSimples. ENV: CONSULTA_CND_ESTADUAL_UFS (CSV). CND Municipal terá tabela
    // cidade→slug própria (cobertura por município).
    'cnd_estadual' => [
        // Default = as 27 UFs listadas no catálogo do repo (docs/infosimples/endpoints-catalog.md,
        // `sefaz/{uf}/certidao-debitos`). NÃO foi verificado contra a cobertura real do plano
        // InfoSimples — ao ativar, conferir e TRIMAR via ENV CONSULTA_CND_ESTADUAL_UFS (CSV) para
        // a cobertura efetiva, senão UFs não atendidas serão chamadas (e cobradas) à toa.
        'ufs_cobertas' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env(
                'CONSULTA_CND_ESTADUAL_UFS',
                'AC,AL,AM,AP,BA,CE,DF,ES,GO,MA,MG,MS,MT,PA,PB,PE,PI,PR,RJ,RN,RO,RR,RS,SC,SE,SP,TO'
            ))
        ))),
    ],

    // Cobertura CND Municipal: mapa "{uf}:{cidade-normalizada}" → slug InfoSimples. O slug NÃO
    // é gerável do nome (ex: "Rio de Janeiro" → pref/rj/rio-janeiro/cnd, sem "de"), então é
    // explícito. Cidade do alvo vem do cadastro (minhareceita). Cidade fora do mapa → INDISPONÍVEL.
    // Inicial = cidades com slug /cnd explícito no catálogo do repo; COMPLETAR/validar via InfoSimples.
    'cnd_municipal' => [
        // Municípios cujo endpoint InfoSimples EXIGE `inscricao_municipal` de entrada (não basta
        // CNPJ). Chave "{uf}:{cidade-normalizada}", mesmas chaves de `slugs`. Consequências:
        //   · só ESTES recebem o param `inscricao_municipal` no request (os demais rodam por CNPJ;
        //     mandar IM neles arriscaria 607/param inválido);
        //   · sem IM no perfil, a consulta é PULADA (INDISPONIVEL, sem chamar) — evita o 606
        //     billable que o InfoSimples cobra por consulta fadada.
        // Fonte: doc InfoSimples do serviço (input `cnpj, cpf, inscricao_municipal`). Confirmado:
        // Ribeirão Preto/SP. ADICIONAR aqui ao descobrir outros (via 606 recorrente). Override:
        // CONSULTA_CND_MUNICIPAL_REQUER_IM (CSV de chaves).
        'requer_im' => array_values(array_filter(array_map('trim', explode(',', (string) env(
            'CONSULTA_CND_MUNICIPAL_REQUER_IM',
            'sp:ribeirao-preto'
        ))))),

        // Mapa completo extraído da doc oficial InfoSimples (docs/infosimples.md). Chave =
        // "{uf}:{cidade-normalizada}". Aliases adicionais cobrem nomes oficiais cujo slug difere
        // (ex: rio-de-janeiro→rio-janeiro), pra casar com o município que a minhareceita retorna.
        'slugs' => [
            'ap:macapa' => 'pref/ap/macapa/cnd',
            'ap:santana' => 'pref/ap/santana/cnd',
            'ba:camacari' => 'pref/ba/camacari/cnd',
            'ba:ilheus' => 'pref/ba/ilheus/cnd',
            'ba:juazeiro' => 'pref/ba/juazeiro/cnd',
            'ba:salvador' => 'pref/ba/salvador/cnd',
            'ce:caucaia' => 'pref/ce/caucaia/cnd',
            'ce:fortaleza' => 'pref/ce/fortaleza/cnd',
            'ce:jaguaretama' => 'pref/ce/jaguaretama/cnd',
            'go:anapolis' => 'pref/go/anapolis/cnd',
            'go:aparecida-goiania' => 'pref/go/aparecida-goiania/cnd',
            'go:aparecida-de-goiania' => 'pref/go/aparecida-goiania/cnd',
            'go:campos-verdes' => 'pref/go/campos-verdes/cnd',
            'go:catalao' => 'pref/go/catalao/cnd',
            'go:firminopolis' => 'pref/go/firminopolis/cnd',
            'go:goiania' => 'pref/go/goiania/cnd',
            'go:itumbiara' => 'pref/go/itumbiara/cnd',
            'go:jatai' => 'pref/go/jatai/cnd',
            'go:morrinhos' => 'pref/go/morrinhos/cnd',
            'go:rio-verde' => 'pref/go/rio-verde/cnd',
            'go:uruacu' => 'pref/go/uruacu/cnd',
            'ma:balsas' => 'pref/ma/balsas/cnd',
            'ma:sao-luis' => 'pref/ma/sao-luis/cnd',
            'mg:araujos' => 'pref/mg/araujos/cnd',
            'mg:belo-horizonte' => 'pref/mg/belo-horizonte/cnd',
            'mg:betim' => 'pref/mg/betim/cnd',
            'mg:contagem' => 'pref/mg/contagem/cnd',
            'mg:divinopolis' => 'pref/mg/divinopolis/cnd',
            'mg:dores-indaia' => 'pref/mg/dores-indaia/cnd',
            'mg:dores-do-indaia' => 'pref/mg/dores-indaia/cnd',
            'mg:itauna' => 'pref/mg/itauna/cnd',
            'mg:janauba' => 'pref/mg/janauba/cnd',
            'mg:juatuba' => 'pref/mg/juatuba/cnd',
            'mg:luz' => 'pref/mg/luz/cnd',
            'mg:martinho-campos' => 'pref/mg/martinho-campos/cnd',
            'mg:montes-claros' => 'pref/mg/montes-claros/cnd',
            'mg:nova-serrana' => 'pref/mg/nova-serrana/cnd',
            'mg:para-minas' => 'pref/mg/para-minas/cnd',
            'mg:para-de-minas' => 'pref/mg/para-minas/cnd',
            'mg:santa-vitoria' => 'pref/mg/santa-vitoria/cnd',
            'mg:uba' => 'pref/mg/uba/cnd',
            'mg:uberaba' => 'pref/mg/uberaba/cnd',
            'mg:uberlandia' => 'pref/mg/uberlandia/cnd',
            'ms:chapadao-do-sul' => 'pref/ms/chapadao-do-sul/cnd',
            'ms:mundo-novo' => 'pref/ms/mundo-novo/cnd',
            'ms:navirai' => 'pref/ms/navirai/cnd',
            'mt:cuiaba' => 'pref/mt/cuiaba/cnd',
            'mt:rondonopolis' => 'pref/mt/rondonopolis/cnd',
            'pa:itaituba' => 'pref/pa/itaituba/cnd',
            'pb:mataraca' => 'pref/pb/mataraca/cnd',
            'pe:recife' => 'pref/pe/recife/cnd',
            'pr:ampere' => 'pref/pr/ampere/cnd',
            'pr:curitiba' => 'pref/pr/curitiba/cnd',
            'pr:francisco-beltrao' => 'pref/pr/francisco-beltrao/cnd',
            'pr:maringa' => 'pref/pr/maringa/cnd',
            'rj:duque-de-caxias' => 'pref/rj/duque-de-caxias/cnd',
            'rj:rio-janeiro' => 'pref/rj/rio-janeiro/cnd',
            'rj:rio-de-janeiro' => 'pref/rj/rio-janeiro/cnd',
            'rn:natal' => 'pref/rn/natal/cnd',
            'rn:touros' => 'pref/rn/touros/cnd',
            'rs:canela' => 'pref/rs/canela/cnd',
            'rs:caxias-do-sul' => 'pref/rs/caxias-do-sul/cnd',
            'rs:montenegro' => 'pref/rs/montenegro/cnd',
            'rs:porto-alegre' => 'pref/rs/porto-alegre/cnd',
            'rs:santa-cruz-do-sul' => 'pref/rs/santa-cruz-do-sul/cnd',
            'rs:tres-coroas' => 'pref/rs/tres-coroas/cnd',
            'sc:balneario' => 'pref/sc/balneario/cnd',
            'sc:balneario-camboriu' => 'pref/sc/balneario/cnd',
            'sc:blumenau' => 'pref/sc/blumenau/cnd',
            'sc:florianopolis' => 'pref/sc/florianopolis/cnd',
            'sc:imbituba' => 'pref/sc/imbituba/cnd',
            'sc:itajai' => 'pref/sc/itajai/cnd',
            'sc:joinville' => 'pref/sc/joinville/cnd',
            'se:laranjeiras' => 'pref/se/laranjeiras/cnd',
            'sp:campinas' => 'pref/sp/campinas/cnd',
            'sp:guarulhos' => 'pref/sp/guarulhos/cnd',
            'sp:hortolandia' => 'pref/sp/hortolandia/cnd',
            'sp:mairipora' => 'pref/sp/mairipora/cnd',
            'sp:ribeirao-preto' => 'pref/sp/ribeirao-preto/cnd',
            'sp:sao-bernardo' => 'pref/sp/sao-bernardo/cnd',
            'sp:sao-bernardo-do-campo' => 'pref/sp/sao-bernardo/cnd',
            'sp:sao-carlos' => 'pref/sp/sao-carlos/cnd',
            'to:colinas' => 'pref/to/colinas/cnd',
            'to:colinas-do-tocantins' => 'pref/to/colinas/cnd',
            'to:palmas' => 'pref/to/palmas/cnd',
        ],
    ],

    // Custo por fonte paga, em R$, usado no estorno preciso.
    'fontes' => [
        'cnd_federal' => (float) env('CONSULTA_CREDITOS_CND_FEDERAL', 0.40),
        'cndt' => (float) env('CONSULTA_CREDITOS_CNDT', 0.40),
        'crf_fgts' => (float) env('CONSULTA_CREDITOS_CRF_FGTS', 0.40),
        'cnd_estadual' => (float) env('CONSULTA_CREDITOS_CND_ESTADUAL', 0.40),
        // SINTEGRA: R$ 1,00 por CNPJ.
        'sintegra' => (float) env('CONSULTA_CREDITOS_SINTEGRA', 1.00),
        'cnd_municipal' => (float) env('CONSULTA_CREDITOS_CND_MUNICIPAL', 0.40),
        // Vertical advocacia: custo interno DEFAULT = preço de venda (R$ 1,00) até o
        // header.price real ser observado no smoke — essas fontes só rodam em lote avulso,
        // cujo estorno usa precosVenda (não este valor); aqui é fallback/consistência.
        'certidao_stj' => (float) env('CONSULTA_CREDITOS_CERTIDAO_STJ', 1.00),
        'certidao_trf' => (float) env('CONSULTA_CREDITOS_CERTIDAO_TRF', 1.00),
        'ceat_trt' => (float) env('CONSULTA_CREDITOS_CEAT_TRT', 1.00),
        'certidao_mpt' => (float) env('CONSULTA_CREDITOS_CERTIDAO_MPT', 1.00),
        'certidao_mpf' => (float) env('CONSULTA_CREDITOS_CERTIDAO_MPF', 1.00),
        'certidao_tjms' => (float) env('CONSULTA_CREDITOS_CERTIDAO_TJMS', 1.00),
        'certidao_tcu' => (float) env('CONSULTA_CREDITOS_CERTIDAO_TCU', 1.00),
        'improbidade' => (float) env('CONSULTA_CREDITOS_IMPROBIDADE', 1.00),
        'ceis' => (float) env('CONSULTA_CREDITOS_CEIS', 1.00),
        'cnep' => (float) env('CONSULTA_CREDITOS_CNEP', 1.00),
        'protestos' => (float) env('CONSULTA_CREDITOS_PROTESTOS', 1.00),
        'falencias' => (float) env('CONSULTA_CREDITOS_FALENCIAS', 1.00),
        'cadastro_pf' => (float) env('CONSULTA_CREDITOS_CADASTRO_PF', 0.20),
        'quitacao_eleitoral' => (float) env('CONSULTA_CREDITOS_QUITACAO_ELEITORAL', 1.00),
        'antecedentes_pf' => (float) env('CONSULTA_CREDITOS_ANTECEDENTES_PF', 1.00),
        'mandado_prisao' => (float) env('CONSULTA_CREDITOS_MANDADO_PRISAO', 1.00),
        // Fontes novas: custo conservador até observar `header.price` em smoke autorizado.
        'pgfn_devedores' => (float) env('CONSULTA_CREDITOS_PGFN_DEVEDORES', 1.00),
        'tcu_cnp' => (float) env('CONSULTA_CREDITOS_TCU_CNP', 1.00),
        'tcu_cni_inidoneo' => (float) env('CONSULTA_CREDITOS_TCU_CNI_INIDONEO', 1.00),
        'tcu_cni_inabilitado' => (float) env('CONSULTA_CREDITOS_TCU_CNI_INABILITADO', 1.00),
        'bcb_valores_receber' => (float) env('CONSULTA_CREDITOS_BCB_VALORES_RECEBER', 1.00),
        'inpi_marcas_titular' => (float) env('CONSULTA_CREDITOS_INPI_MARCAS_TITULAR', 1.00),
        'ibama_embargos' => (float) env('CONSULTA_CREDITOS_IBAMA_EMBARGOS', 1.00),
        'ibama_debitos' => (float) env('CONSULTA_CREDITOS_IBAMA_DEBITOS', 1.00),
        'ibama_regularidade' => (float) env('CONSULTA_CREDITOS_IBAMA_REGULARIDADE', 1.00),
        'ibama_autuacoes' => (float) env('CONSULTA_CREDITOS_IBAMA_AUTUACOES', 1.00),
    ],

    // Reconsulta de fontes com falha transitória (classe `retry`, ex. código 600).
    // 'desconto_pct' = desconto sobre o preço do PLANO na reconsulta (cobrada por CNPJ afetado).
    // Retry é ilimitado. Re-falha da classe `retry` é estornada (provedor não fatura instabilidade
    // → líquido zero); re-falha `erro_participante` mantém a cobrança (a fonte oficial responde
    // recusando os dados e o provedor FATURA a chamada, ex. 620 FGTS billable).
    'retry' => [
        'desconto_pct' => (int) env('CONSULTAS_RETRY_DESCONTO_PCT', 50),

        // Auto-retry DENTRO do job: fonte que falha com classe `retry` (transitória, não
        // cobrada — ex.: 615 origem oficial fora do ar) é retentada ao fim do alvo. O cooldown
        // é o da 1ª tentativa e CRESCE por tentativa (backoff linear): 15s, depois 30s. A espera
        // é contada desde a falha, então o tempo já gasto nas fontes seguintes abate.
        // `erro_participante`/`fatal`/`indeterminado`(611) não entram (re-falha determinística —
        // reconsultar dá o mesmo resultado e/ou fatura). 0 tentativas desliga. Recuperar a fonte
        // cancela o estorno dela no fechamento.
        'auto' => [
            'max_tentativas' => (int) env('CONSULTAS_AUTO_RETRY_TENTATIVAS', 2),
            'cooldown_segundos' => (int) env('CONSULTAS_AUTO_RETRY_COOLDOWN', 15),
        ],

        // Códigos InfoSimples das classes reconsultáveis (`retry` + `erro_participante`)
        // agrupados em motivos acionáveis (o que o usuário FAZ muda). Usado pela tela do
        // lote p/ orientar a reconsulta.
        // 'codigo_motivo': código InfoSimples → motivo. 'motivos': apresentação por motivo.
        'codigo_motivo' => [
            605 => 'origem_instavel', 613 => 'origem_instavel', 614 => 'origem_instavel',
            615 => 'origem_instavel', 618 => 'origem_instavel',
            600 => 'tecnica_pontual', 610 => 'tecnica_pontual',
            609 => 'origem_persistente',
            608 => 'dados_participante', 619 => 'dados_participante', 620 => 'dados_participante',
        ],
        'motivos' => [
            'origem_instavel' => [
                'rotulo' => 'Fonte oficial instável',
                'aguardar_minutos' => 30,
                'icone' => '⏳',
                'orientacao' => 'A fonte oficial (ex.: Receita Federal) está fora do ar ou lenta. Aguarde ~30 min e reconsulte.',
            ],
            'tecnica_pontual' => [
                'rotulo' => 'Falha técnica pontual',
                'aguardar_minutos' => 2,
                'icone' => '↻',
                'orientacao' => 'Falha momentânea do provedor. Pode reconsultar já.',
            ],
            'origem_persistente' => [
                'rotulo' => 'Fonte com problema persistente',
                'aguardar_minutos' => 60,
                'icone' => '⌛',
                'orientacao' => 'A fonte não respondeu após várias tentativas. Aguarde ~60 min e reconsulte.',
            ],
            'dados_participante' => [
                'rotulo' => 'Fonte recusou os dados do CNPJ',
                'aguardar_minutos' => 0,
                'icone' => '⚠',
                'orientacao' => 'A fonte oficial recusou a consulta com os dados deste CNPJ. Confira o cadastro (CNPJ/UF/situação) antes de reconsultar: essa resposta é faturada pela fonte, então a reconsulta é cobrada mesmo que a recusa se repita. Se persistir, comunique o suporte.',
            ],
        ],
    ],

    // Pedidos de certidão de 2 ETAPAS (fase 4) — genérico a TODO tribunal (não é TJMS-específico).
    // Bloco PRÓPRIO, irmão de `retry`: aninhá-lo dentro do retry (como já aconteceu uma vez)
    // desloca `codigo_motivo`/`motivos` e o RetryConsultaService passa a ler config vazia.
    // `retry_tecnico_max`: quantas re-conferências CURTAS (15s→30s) uma falha transitória (615/605)
    // ganha antes de contar como conferência efetiva ao tribunal.
    'pedidos' => [
        'retry_tecnico_max' => (int) env('CONSULTAS_PEDIDOS_RETRY_TECNICO_MAX', 2),
    ],

    // Card "Relacionamento & Movimentação Fiscal" no resultado da consulta.
    // 'visivel' = itens por lista (produtos/contrapartes) mostrados antes do "ver mais";
    // 'maximo'  = teto buscado/expandível por lista. CFOPs usam 'visivel' (lista curta).
    'panorama_fiscal' => [
        'visivel' => (int) env('CONSULTA_PANORAMA_VISIVEL', 10),
        'maximo' => (int) env('CONSULTA_PANORAMA_MAXIMO', 30),
    ],
];
