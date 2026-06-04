<?php

/**
 * Mapa de CFOPs canônicos.
 * Fonte: CONFAZ — Anexo CFOP, Ajuste SINIEF 07/2001 e alterações.
 * '5999' NÃO é listado aqui intencionalmente; deve cair no fallback de família.
 */

return [
    // -----------------------------------------------------------------------
    // Famílias por 1º dígito (fallback quando o código não está em 'descricoes')
    // -----------------------------------------------------------------------
    'familias' => [
        '1' => 'Entrada estadual',
        '2' => 'Entrada interestadual',
        '3' => 'Entrada do exterior',
        '5' => 'Saída estadual',
        '6' => 'Saída interestadual',
        '7' => 'Saída para o exterior',
    ],

    // -----------------------------------------------------------------------
    // Top ~130 CFOPs mais usados em comércio/serviço no Brasil (CONFAZ)
    // -----------------------------------------------------------------------
    'descricoes' => [

        // ===== ENTRADAS ESTADUAIS (1xxx) ====================================

        // Compras para industrialização / comercialização
        '1101' => 'Compra para industrialização',
        '1102' => 'Compra para comercialização',
        '1111' => 'Compra para industrialização de mercadoria recebida anteriormente em consignação industrial',
        '1113' => 'Compra para comercialização, de mercadoria adquirida e não recebida anteriormente',
        '1116' => 'Compra para industrialização originada de encomenda para recebimento futuro',
        '1117' => 'Compra para comercialização originada de encomenda para recebimento futuro',
        '1118' => 'Compra de mercadoria para comercialização pelo adquirente originário, com entrega pelo vendedor à ordem',
        '1120' => 'Compra para industrialização, em venda à ordem, já recebida do vendedor remetente',
        '1121' => 'Compra para comercialização, em venda à ordem, já recebida do vendedor remetente',
        '1122' => 'Compra para industrialização em venda à ordem',
        '1123' => 'Compra para comercialização em venda à ordem',
        '1124' => 'Industrialização efetuada por outra empresa',
        '1125' => 'Industrialização efetuada por outra empresa quando a mercadoria remetida para esse fim não transitou pelo estabelecimento autor da encomenda',

        // Devoluções de vendas
        '1201' => 'Devolução de venda de produção do estabelecimento',
        '1202' => 'Devolução de venda de mercadoria adquirida de terceiros',
        '1203' => 'Devolução de venda de produção do estabelecimento destinada à Zona Franca de Manaus ou Áreas de Livre Comércio',
        '1204' => 'Devolução de venda de mercadoria adquirida de terceiros destinada à Zona Franca de Manaus ou Áreas de Livre Comércio',
        '1205' => 'Anulação de valor relativo à prestação de serviço de comunicação',
        '1206' => 'Anulação de valor relativo à prestação de serviço de transporte',
        '1207' => 'Anulação de valor relativo à venda de energia elétrica',

        // Transferências
        '1301' => 'Transferência para industrialização',
        '1302' => 'Transferência para comercialização',
        '1303' => 'Transferência de bem do ativo imobilizado',
        '1304' => 'Transferência de material de uso ou consumo',

        // Retornos e remessas
        '1401' => 'Compra para industrialização em operação com mercadoria sujeita ao regime de substituição tributária',
        '1403' => 'Compra para comercialização em operação com mercadoria sujeita ao regime de substituição tributária',
        '1406' => 'Compra de bem para o ativo imobilizado cuja mercadoria está sujeita ao regime de substituição tributária',
        '1407' => 'Compra de mercadoria para uso ou consumo cuja mercadoria está sujeita ao regime de substituição tributária',
        '1408' => 'Transferência para industrialização em operação com mercadoria sujeita ao regime de substituição tributária',
        '1409' => 'Transferência para comercialização em operação com mercadoria sujeita ao regime de substituição tributária',

        // Entradas de bens e serviços
        '1551' => 'Compra de bem para o ativo imobilizado',
        '1552' => 'Transferência de bem do ativo imobilizado',
        '1553' => 'Devolução de venda de bem do ativo imobilizado',
        '1554' => 'Retorno de bem do ativo imobilizado remetido para uso fora do estabelecimento',
        '1555' => 'Entrada de bem do ativo imobilizado de terceiro, remetido para uso no estabelecimento',
        '1556' => 'Compra de material para uso ou consumo',
        '1557' => 'Transferência de material de uso ou consumo',

        // Crédito do ICMS
        '1601' => 'Recebimento, por transferência, de crédito de ICMS',

        // Remessas para industrialização / demonstração
        '1901' => 'Entrada para industrialização por encomenda',
        '1902' => 'Retorno de mercadoria remetida para industrialização por encomenda',
        '1903' => 'Entrada de mercadoria remetida para industrialização e não aplicada no referido processo',
        '1904' => 'Retorno de remessa para venda fora do estabelecimento',
        '1905' => 'Entrada de mercadoria recebida para depósito em depósito fechado ou armazém geral',
        '1906' => 'Retorno de mercadoria remetida para depósito fechado ou armazém geral',
        '1907' => 'Retorno simbólico de mercadoria remetida para depósito fechado ou armazém geral',
        '1908' => 'Entrada de bem por conta de contrato de comodato',
        '1909' => 'Retorno de bem remetido por conta de contrato de comodato',
        '1910' => 'Entrada de bonificação, doação ou brinde',
        '1911' => 'Entrada de amostra grátis',
        '1912' => 'Entrada de mercadoria ou bem recebido para demonstração',
        '1913' => 'Retorno de mercadoria ou bem remetido para demonstração',
        '1914' => 'Retorno de mercadoria ou bem remetido em consignação mercantil',
        '1915' => 'Entrada de mercadoria ou bem recebido em consignação mercantil',
        '1916' => 'Entrada de mercadoria recebida em consignação industrial',
        '1917' => 'Retorno de mercadoria remetida em consignação industrial',
        '1918' => 'Entrada de mercadoria proveniente de doação',
        '1919' => 'Entrada de sobras e resíduos de industrialização',
        '1920' => 'Entrada de vasilhame ou sacaria',
        '1921' => 'Retorno de vasilhame ou sacaria',

        // ===== ENTRADAS INTERESTADUAIS (2xxx) ================================

        '2101' => 'Compra para industrialização (interestadual)',
        '2102' => 'Compra para comercialização (interestadual)',
        '2111' => 'Compra para industrialização de mercadoria recebida anteriormente em consignação industrial (interestadual)',
        '2201' => 'Devolução de venda de produção do estabelecimento (interestadual)',
        '2202' => 'Devolução de venda de mercadoria adquirida de terceiros (interestadual)',
        '2301' => 'Transferência para industrialização (interestadual)',
        '2302' => 'Transferência para comercialização (interestadual)',
        '2303' => 'Transferência de bem do ativo imobilizado (interestadual)',
        '2401' => 'Compra para industrialização em operação com mercadoria sujeita ao regime de substituição tributária (interestadual)',
        '2403' => 'Compra para comercialização em operação com mercadoria sujeita ao regime de substituição tributária (interestadual)',
        '2551' => 'Compra de bem para o ativo imobilizado (interestadual)',
        '2556' => 'Compra de material para uso ou consumo (interestadual)',
        '2910' => 'Entrada de bonificação, doação ou brinde (interestadual)',
        '2912' => 'Entrada de mercadoria ou bem recebido para demonstração (interestadual)',
        '2914' => 'Retorno de mercadoria ou bem remetido em consignação mercantil (interestadual)',

        // ===== ENTRADAS DO EXTERIOR (3xxx) ===================================

        '3101' => 'Compra para industrialização — importação direta',
        '3102' => 'Compra para comercialização — importação direta',
        '3201' => 'Devolução de venda — exportação',
        '3551' => 'Compra de bem para o ativo imobilizado — importação direta',
        '3556' => 'Compra de material para uso ou consumo — importação direta',

        // ===== SAÍDAS ESTADUAIS (5xxx) =======================================

        // Vendas
        '5101' => 'Venda de produção do estabelecimento',
        '5102' => 'Venda de mercadoria adquirida de terceiros',
        '5103' => 'Venda de produção do estabelecimento efetuada fora do estabelecimento',
        '5104' => 'Venda de mercadoria adquirida de terceiros efetuada fora do estabelecimento',
        '5105' => 'Venda de produção do estabelecimento que não deva por ele transitar',
        '5106' => 'Venda de mercadoria adquirida de terceiros que não deva por ele transitar',
        '5109' => 'Venda de arroz, feijão, fubá de milho, farinha de mandioca e farinha de trigo em vasilhames retornáveis',
        '5110' => 'Venda de produção do estabelecimento remetida anteriormente em consignação industrial',
        '5111' => 'Venda de mercadoria adquirida de terceiros remetida anteriormente em consignação industrial',
        '5113' => 'Venda de produção do estabelecimento com entrega a cargo de terceiros',
        '5114' => 'Venda de produção do estabelecimento remetida para industrialização, por conta e ordem do adquirente, com entrega ao destinatário',
        '5115' => 'Venda de mercadoria adquirida de terceiros, recebida anteriormente em consignação industrial',
        '5116' => 'Venda de produção do estabelecimento originada de encomenda para entrega futura',
        '5117' => 'Venda de mercadoria adquirida de terceiros originada de encomenda para entrega futura',
        '5118' => 'Venda de produção do estabelecimento entregue ao destinatário por conta e ordem do adquirente originário',
        '5119' => 'Venda de mercadoria adquirida de terceiros entregue ao destinatário por conta e ordem do adquirente originário',
        '5120' => 'Venda de produção do estabelecimento em venda à ordem',
        '5121' => 'Venda de mercadoria adquirida de terceiros em venda à ordem',
        '5122' => 'Venda de produção do estabelecimento remetida para industrialização em venda à ordem',
        '5123' => 'Venda de mercadoria adquirida de terceiros remetida para industrialização em venda à ordem',
        '5124' => 'Industrialização efetuada para outra empresa',
        '5125' => 'Industrialização efetuada para outra empresa quando a mercadoria não transitou pelo estabelecimento autor da encomenda',

        // Devoluções de compras
        '5201' => 'Devolução de compra para industrialização',
        '5202' => 'Devolução de compra para comercialização',
        '5205' => 'Anulação de valor relativo à aquisição de serviço de comunicação',
        '5206' => 'Anulação de valor relativo à aquisição de serviço de transporte',
        '5207' => 'Anulação de valor relativo à compra de energia elétrica',

        // Transferências
        '5301' => 'Transferência de produção do estabelecimento',
        '5302' => 'Transferência de mercadoria adquirida de terceiros',
        '5303' => 'Transferência de bem do ativo imobilizado',
        '5304' => 'Transferência de material de uso ou consumo',

        // Substituição tributária
        '5401' => 'Venda de produção do estabelecimento em operação com produto sujeito ao regime de substituição tributária',
        '5402' => 'Venda de produção do estabelecimento de produto sujeito ao regime de substituição tributária, em operação entre contribuintes substitutos do mesmo produto',
        '5403' => 'Venda de mercadoria adquirida de terceiros em operação com produto sujeito ao regime de substituição tributária',
        '5405' => 'Venda de mercadoria adquirida de terceiros, com cobrança do ICMS por substituição tributária',
        '5408' => 'Transferência de produção do estabelecimento em operação com produto sujeito ao regime de substituição tributária',
        '5409' => 'Transferência de mercadoria adquirida de terceiros em operação com produto sujeito ao regime de substituição tributária',
        '5411' => 'Devolução de compra para industrialização em operação com mercadoria sujeita ao regime de substituição tributária',
        '5412' => 'Devolução de compra para comercialização em operação com mercadoria sujeita ao regime de substituição tributária',
        '5413' => 'Ressarcimento de ICMS retido por substituição tributária',
        '5414' => 'Remessa de produção do estabelecimento para venda fora do estabelecimento em operação com produto sujeito ao regime de substituição tributária',
        '5415' => 'Remessa de mercadoria adquirida de terceiros para venda fora do estabelecimento em operação com mercadoria sujeita ao regime de substituição tributária',

        // Ativo imobilizado e uso/consumo
        '5551' => 'Venda de bem do ativo imobilizado',
        '5552' => 'Transferência de bem do ativo imobilizado',
        '5553' => 'Devolução de compra de bem para o ativo imobilizado',
        '5554' => 'Remessa de bem do ativo imobilizado para uso fora do estabelecimento',
        '5555' => 'Remessa de bem do ativo imobilizado de terceiro remetido para uso no estabelecimento',
        '5556' => 'Venda de material de uso ou consumo',
        '5557' => 'Transferência de material de uso ou consumo',

        // Serviços de transporte e comunicação
        '5651' => 'Venda de combustível ou lubrificante de produção do estabelecimento destinados à industrialização subsequente',
        '5652' => 'Venda de combustível ou lubrificante de produção do estabelecimento destinados à comercialização',
        '5653' => 'Venda de combustível ou lubrificante de produção do estabelecimento destinados a consumidor ou usuário final',
        '5656' => 'Venda de combustível ou lubrificante de terceiros destinados à industrialização subsequente',
        '5657' => 'Venda de combustível ou lubrificante de terceiros destinados à comercialização',
        '5658' => 'Venda de combustível ou lubrificante de terceiros destinados a consumidor ou usuário final',
        '5661' => 'Venda de combustível ou lubrificante adquirido de terceiros destinados à industrialização subsequente, em operação com mercadoria sujeita ao regime de substituição tributária',
        '5662' => 'Venda de combustível ou lubrificante adquirido de terceiros destinados à comercialização, em operação com mercadoria sujeita ao regime de substituição tributária',
        '5663' => 'Venda de combustível ou lubrificante adquirido de terceiros destinados a consumidor ou usuário final, em operação com mercadoria sujeita ao regime de substituição tributária',

        // Remessas diversas
        '5901' => 'Remessa para industrialização por encomenda',
        '5902' => 'Retorno de mercadoria utilizada na industrialização por encomenda',
        '5903' => 'Retorno de mercadoria recebida para industrialização e não aplicada no referido processo',
        '5904' => 'Remessa para venda fora do estabelecimento',
        '5905' => 'Remessa para depósito fechado ou armazém geral',
        '5906' => 'Retorno de mercadoria depositada em depósito fechado ou armazém geral',
        '5907' => 'Retorno simbólico de mercadoria depositada em depósito fechado ou armazém geral',
        '5908' => 'Remessa de bem por conta de contrato de comodato',
        '5909' => 'Retorno de bem recebido por conta de contrato de comodato',
        '5910' => 'Remessa em bonificação, doação ou brinde',
        '5911' => 'Remessa de amostra grátis',
        '5912' => 'Remessa de mercadoria ou bem para demonstração',
        '5913' => 'Retorno de mercadoria ou bem recebido para demonstração',
        '5914' => 'Remessa de mercadoria ou bem para consignação mercantil',
        '5915' => 'Retorno de mercadoria ou bem recebido em consignação mercantil',
        '5916' => 'Remessa de mercadoria em consignação industrial',
        '5917' => 'Retorno de mercadoria recebida em consignação industrial',
        '5918' => 'Remessa de mercadoria proveniente de doação',
        '5919' => 'Remessa de sobras e resíduos de industrialização',
        '5920' => 'Remessa de vasilhame ou sacaria',
        '5921' => 'Retorno de vasilhame ou sacaria',
        '5922' => 'Lançamento efetuado a título de simples faturamento decorrente de venda para entrega futura',
        '5923' => 'Remessa de mercadoria por conta e ordem de terceiros em operação com produtos sujeitos ao regime de substituição tributária',
        '5924' => 'Remessa para industrialização por conta e ordem do adquirente da mercadoria, quando esta não transitar pelo estabelecimento do adquirente',
        '5925' => 'Retorno de mercadoria recebida para industrialização por conta e ordem do adquirente da mercadoria, quando esta não transitou pelo estabelecimento do adquirente',

        // ===== SAÍDAS INTERESTADUAIS (6xxx) ==================================

        '6101' => 'Venda de produção do estabelecimento (interestadual)',
        '6102' => 'Venda de mercadoria adquirida de terceiros (interestadual)',
        '6103' => 'Venda de produção do estabelecimento efetuada fora do estabelecimento (interestadual)',
        '6104' => 'Venda de mercadoria adquirida de terceiros efetuada fora do estabelecimento (interestadual)',
        '6105' => 'Venda de produção do estabelecimento que não deva por ele transitar (interestadual)',
        '6106' => 'Venda de mercadoria adquirida de terceiros que não deva por ele transitar (interestadual)',
        '6107' => 'Venda de produção do estabelecimento destinada à Zona Franca de Manaus ou Áreas de Livre Comércio',
        '6108' => 'Venda de mercadoria adquirida de terceiros destinada à Zona Franca de Manaus ou Áreas de Livre Comércio',
        '6109' => 'Venda de produção do estabelecimento destinada à Zona Franca de Manaus ou Áreas de Livre Comércio, em operação com mercadoria sujeita ao regime de substituição tributária',
        '6110' => 'Venda de mercadoria adquirida de terceiros destinada à Zona Franca de Manaus ou Áreas de Livre Comércio, em operação com mercadoria sujeita ao regime de substituição tributária',
        '6116' => 'Venda de produção do estabelecimento originada de encomenda para entrega futura (interestadual)',
        '6117' => 'Venda de mercadoria adquirida de terceiros originada de encomenda para entrega futura (interestadual)',
        '6120' => 'Venda de produção do estabelecimento em venda à ordem (interestadual)',
        '6121' => 'Venda de mercadoria adquirida de terceiros em venda à ordem (interestadual)',
        '6201' => 'Devolução de compra para industrialização (interestadual)',
        '6202' => 'Devolução de compra para comercialização (interestadual)',
        '6301' => 'Transferência de produção do estabelecimento (interestadual)',
        '6302' => 'Transferência de mercadoria adquirida de terceiros (interestadual)',
        '6303' => 'Transferência de bem do ativo imobilizado (interestadual)',
        '6304' => 'Transferência de material de uso ou consumo (interestadual)',
        '6401' => 'Venda de produção do estabelecimento em operação com produto sujeito ao regime de substituição tributária (interestadual)',
        '6403' => 'Venda de mercadoria adquirida de terceiros em operação com produto sujeito ao regime de substituição tributária (interestadual)',
        '6404' => 'Venda de mercadoria sujeita ao regime de substituição tributária cujo imposto já tenha sido retido anteriormente (interestadual)',
        '6551' => 'Venda de bem do ativo imobilizado (interestadual)',
        '6556' => 'Venda de material de uso ou consumo (interestadual)',
        '6901' => 'Remessa para industrialização por encomenda (interestadual)',
        '6902' => 'Retorno de mercadoria utilizada na industrialização por encomenda (interestadual)',
        '6903' => 'Retorno de mercadoria recebida para industrialização e não aplicada no processo (interestadual)',
        '6904' => 'Remessa para venda fora do estabelecimento (interestadual)',
        '6905' => 'Remessa para depósito fechado ou armazém geral (interestadual)',
        '6906' => 'Retorno de mercadoria depositada em depósito fechado ou armazém geral (interestadual)',
        '6907' => 'Retorno simbólico de mercadoria depositada em depósito fechado ou armazém geral (interestadual)',
        '6908' => 'Remessa de bem por conta de contrato de comodato (interestadual)',
        '6910' => 'Remessa em bonificação, doação ou brinde (interestadual)',
        '6911' => 'Remessa de amostra grátis (interestadual)',
        '6912' => 'Remessa de mercadoria ou bem para demonstração (interestadual)',
        '6914' => 'Remessa de mercadoria ou bem para consignação mercantil (interestadual)',
        '6915' => 'Retorno de mercadoria ou bem recebido em consignação mercantil (interestadual)',
        '6916' => 'Remessa de mercadoria em consignação industrial (interestadual)',
        '6922' => 'Lançamento efetuado a título de simples faturamento decorrente de venda para entrega futura (interestadual)',

        // ===== SAÍDAS PARA O EXTERIOR (7xxx) =================================

        '7101' => 'Venda de produção do estabelecimento — exportação',
        '7102' => 'Venda de mercadoria adquirida de terceiros — exportação',
        '7105' => 'Venda de produção do estabelecimento que não deva por ele transitar — exportação',
        '7106' => 'Venda de mercadoria adquirida de terceiros que não deva por ele transitar — exportação',
        '7127' => 'Venda de produção do estabelecimento sob o regime aduaneiro especial de drawback',
        '7201' => 'Devolução de compra para industrialização — exportação',
        '7202' => 'Devolução de compra para comercialização — exportação',
        '7211' => 'Devolução de compras para industrialização sob o regime aduaneiro especial de drawback',
        '7301' => 'Transferência de produção do estabelecimento — exportação',
        '7302' => 'Transferência de mercadoria adquirida de terceiros — exportação',
        '7501' => 'Exportação de mercadorias recebidas com fim específico de exportação',
        '7551' => 'Venda de bem do ativo imobilizado — exportação',
        '7930' => 'Lançamento efetuado a título de devolução de bem do ativo imobilizado — exportação',
        '7949' => 'Outra saída de mercadoria ou prestação de serviço não especificado — exportação',
    ],
];
