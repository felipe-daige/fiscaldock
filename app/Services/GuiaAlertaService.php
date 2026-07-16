<?php

namespace App\Services;

use App\Models\Alerta;

/**
 * Fonte única da "Orientação de Tratativa" dos alertas: o que significa, como
 * resolver e qual a ação (CTA). Antes vivia hardcoded no show.blade.php — agora
 * é testável e consistente entre lista e detalhe, com CTA resolvido por alerta
 * (corrige o caso agregado, sem participante_id).
 */
class GuiaAlertaService
{
    /**
     * @return array{titulo_o_que_e:string, texto_o_que_e:string, titulo_acao:string, texto_acao:string, cta_text:string, cta_url:?string}
     */
    public function para(Alerta $alerta): array
    {
        $tipo = (string) $alerta->tipo;
        $guia = $this->base();

        $mapa = $this->textos();
        foreach ($mapa as $def) {
            if (in_array($tipo, $def['tipos'], true)) {
                $guia = array_merge($guia, $def['guia']);
                break;
            }
        }

        $guia['cta_url'] = $this->resolverCta($alerta, $guia['cta_url'] ?? null);

        return $guia;
    }

    /**
     * Versão resumida pra renderizar na LISTA (card) — só o essencial pra agir.
     *
     * @return array{cta_text:string, cta_url:?string}
     */
    public function resumo(Alerta $alerta): array
    {
        $g = $this->para($alerta);

        return ['cta_text' => $g['cta_text'], 'cta_url' => $g['cta_url']];
    }

    private function resolverCta(Alerta $alerta, ?string $ctaUrl): ?string
    {
        // Placeholder dinâmico: rotas que dependem do alerta concreto.
        if ($ctaUrl === ':participante') {
            if ($alerta->participante_id) {
                return '/app/participante/'.$alerta->participante_id;
            }
            if ($alerta->cliente_id) {
                return '/app/cliente/'.$alerta->cliente_id;
            }

            return '/app/participantes';
        }

        return $ctaUrl;
    }

    /**
     * @return array{titulo_o_que_e:string, texto_o_que_e:string, titulo_acao:string, texto_acao:string, cta_text:string, cta_url:?string, metodologia:?string}
     */
    private function base(): array
    {
        return [
            'titulo_o_que_e' => 'O que isso significa?',
            'texto_o_que_e' => 'Encontramos algumas inconsistências em nossos registros ou integrações automáticas.',
            'titulo_acao' => 'Como resolver',
            'texto_acao' => 'Corrija a origem (no ERP/sistema fiscal e, quando for o caso, gere e reimporte o SPED) e marque como resolvido. O alerta é recalculado a partir dos dados: some sozinho quando o problema não for mais detectado — e reaparece se você marcar resolvido mas o problema persistir. Use "Ignorar" só para dispensar de vez um alerta que não se aplica.',
            'cta_text' => 'Marcar como Resolvido',
            'cta_url' => null,
            // Transparência auditável: fonte de dados, gatilho, janela de tempo e limiar de cada
            // alerta. Renderizado como "Como calculamos" no detalhe. Racional completo: docs/alertas/README.md.
            'metodologia' => null,
        ];
    }

    /**
     * @return array<int, array{tipos: array<int,string>, guia: array<string,mixed>}>
     */
    private function textos(): array
    {
        return [
            [
                'tipos' => ['nunca_consultado', 'consulta_vencida'],
                'guia' => [
                    'texto_o_que_e' => 'Participante(s) com notas fiscais que nunca tiveram o CNPJ verificado junto à Receita Federal, ou cuja consulta foi feita há mais de 90 dias. Manter a situação cadastral em dia evita negócios com empresas inaptas.',
                    'texto_acao' => 'Acesse a consulta agora e verifique a situação cadastral na Receita Federal. Ao concluir com sucesso, o alerta some sozinho do painel (ou resolva manualmente).',
                    'cta_text' => 'Ir para Consulta',
                    'cta_url' => ':participante',
                    'metodologia' => 'Fonte: cadastro de participantes que têm notas EFD, cruzado com a data da última consulta (participante_scores.ultima_consulta_em). Gatilho: participante nunca consultado, ou última consulta há mais de 90 dias. A empresa própria é excluída do "nunca consultado".',
                ],
            ],
            [
                'tipos' => ['certidao_positiva'],
                'guia' => [
                    'texto_o_que_e' => 'Uma ou mais certidões de regularidade (CND Federal, Estadual ou FGTS) deste fornecedor/cliente estão POSITIVAS — indicam débito(s) exigível(is) na fonte oficial. Certidão positiva é risco fiscal concreto, não apenas cadastral.',
                    'texto_acao' => 'Verifique as certidões no perfil, confirme os débitos junto ao fornecedor/cliente e avalie o impacto (crédito, contratação, licitação). Uma nova consulta regularizada faz o alerta sumir sozinho.',
                    'cta_text' => 'Ver perfil e certidões',
                    'cta_url' => ':participante',
                    'metodologia' => 'Fonte: última consulta de regularidade persistida (participante_scores). Gatilho: ao menos uma certidão (CND Federal, Estadual ou FGTS/CRF) classificada como POSITIVA pelo padrão CertidaoBadge — "Negativa" e "Positiva com efeitos de negativa" contam como REGULARES; só "Positiva" pura dispara. Severidade: CND Federal/Estadual = alta; FGTS = média (mesma gravidade que aplica o piso no Score de Risco). Valor comprado = soma de entradas fiscais (efd_notas, origem ICMS/IPI, tipo_operacao "entrada"), em três janelas por data de emissão: ÚLTIMOS 12 MESES (exposição corrente / risco vivo), 5 ANOS (janela de decadência do CTN — exposição sujeita a glosa de crédito) e TOTAL histórico (contexto, reconcilia com o Cruzamentos). Débito antigo sem compra recente aparece como baixa exposição; compra fora dos 5 anos é sinalizada como sem exposição a glosa. Racional do período: docs/alertas/README.md.',
                ],
            ],
            [
                'tipos' => ['certidao_vencendo'],
                'guia' => [
                    'texto_o_que_e' => 'Uma certidão de regularidade (CND Federal, Estadual ou FGTS) hoje REGULAR está prestes a vencer (ou já venceu). Certidão vencida deixa de comprovar a regularidade — trava licitação, contrato e crédito até ser renovada.',
                    'texto_acao' => 'Emita a nova certidão junto ao órgão (Receita/PGFN, SEFAZ, Caixa, TST) antes do vencimento. Ao registrar a nova consulta com a certidão renovada, o alerta some sozinho.',
                    'cta_text' => 'Ver perfil e certidões',
                    'cta_url' => ':participante',
                    'metodologia' => 'Fonte: última consulta de regularidade persistida (participante_scores). Gatilho: certidão REGULAR (subscore 0) cuja data_validade já passou ou vence dentro de 30 dias. Só entram certidões efetivamente avaliadas e regulares — as positivas viram o alerta "Certidão Positiva". O prazo do alerta (vence_em) é a certidão que vence primeiro; severidade alta quando vencida ou faltando ≤7 dias, média caso contrário. Racional: docs/alertas/README.md.',
                ],
            ],
            [
                'tipos' => ['situacao_irregular', 'cnpj_situacao_irregular', 'participante_inativo', 'participante_sem_ie', 'fornecedor_irregular'],
                'guia' => [
                    'texto_o_que_e' => 'Este participante está com pendências cadastrais na Receita Federal (ex.: Baixada, Inapta, Suspensa). Operar com este CNPJ pode causar rejeições de notas fiscais e pesadas multas.',
                    'texto_acao' => 'Recomende ao responsável financeiro interromper operações comerciais e bloquear o cadastro no ERP até a total regularização. Abra a ficha do participante para conferir os detalhes.',
                    'cta_text' => 'Ver participante',
                    'cta_url' => ':participante',
                    'metodologia' => 'Fonte: participantes.situacao_cadastral (Receita Federal, atualizada pelo monitoramento). Gatilho: situação NÃO pertence a {\'02\', \'ATIVA\'} — ex.: Baixada, Inapta, Suspensa (o código "02" da Receita é ATIVA e não dispara). No "fornecedor_irregular", cruza com notas EFD e soma o valor em risco = entradas fiscais, deduplicando a mesma NF-e entre ICMS/IPI e PIS/COFINS e excluindo canceladas.',
                ],
            ],
            [
                'tipos' => ['notas_duplicadas'],
                'guia' => [
                    'texto_o_que_e' => 'Há duas ou mais notas registradas com exatamente a mesma numeração, série, modelo e participante. Normalmente indica notas importadas em duplicidade ou duplo input no ERP.',
                    'texto_acao' => 'Confira a listagem abaixo no seu ERP/Contábil, cancele e apague o registro excedente para os livros refletirem a realidade, e gere novo SPED.',
                    'cta_text' => '',
                    'cta_url' => null,
                    'metodologia' => 'Fonte: efd_notas do usuário. Gatilho: duas ou mais notas com a mesma combinação de numeração + série + modelo + participante. A contagem exibida é o número de notas excedentes (duplicatas além da primeira).',
                ],
            ],
            [
                'tipos' => ['notas_valor_zerado', 'notas_sem_itens', 'cfops_inconsistentes', 'participantes_sem_cnpj'],
                'guia' => [
                    'texto_o_que_e' => 'Existem notas importadas com inconsistências (sem valor, sem itens, ou CFOP de entrada/saída cruzado). Isso impede escriturações corretas e pode gerar passivos.',
                    'texto_acao' => 'Acesse os dados da(s) nota(s) afetada(s) no seu ERP. Revise se os itens foram integrados corretamente e se os CFOPs estão de acordo com o padrão SEFAZ.',
                    'cta_text' => '',
                    'cta_url' => null,
                    'metodologia' => 'Fonte: efd_notas e efd_notas_itens. Gatilho: nota com valor total zerado, sem itens vinculados, com CFOP incoerente com o tipo de operação (entrada/saída cruzados) ou participante sem CNPJ válido. A contagem é o total de notas afetadas.',
                ],
            ],
            [
                'tipos' => ['gap_importacao', 'gap_temporal'],
                'guia' => [
                    'texto_o_que_e' => 'Detectamos meses sem escrituração EFD importada num período onde seria esperado ter arquivos fiscais — possível obrigação acessória não entregue.',
                    'texto_acao' => 'Faça o upload do(s) arquivo(s) SPED (EFD ICMS/IPI ou Contribuições) dos meses indicados abaixo dentro da plataforma.',
                    'cta_text' => 'Ir para Importações SPED',
                    'cta_url' => '/app/importacao/efd',
                    'metodologia' => 'Fonte: efd_importacoes concluídas, contadas por COMPETÊNCIA (periodo_inicio/periodo_fim), não pela data de upload. Janela: últimos 12 meses. Gatilho: mês da janela sem nenhuma EFD (ICMS/IPI ou Contribuições). Lista os meses faltantes.',
                ],
            ],
            [
                'tipos' => ['pis_cofins_incompleto'],
                'guia' => [
                    'texto_o_que_e' => 'Um volume alto de itens (PIS/COFINS) veio sem detalhamento de impostos ou sem as alíquotas base no arquivo exportado.',
                    'texto_acao' => 'Provável erro no cadastro de produtos ou no mapeamento Tributário/NCM do ERP fiscal. Revise o cadastro e gere novo SPED.',
                    'cta_text' => '',
                    'cta_url' => null,
                    'metodologia' => 'Fonte: itens de EFD Contribuições. Gatilho: proporção elevada de itens sem detalhamento de imposto ou sem alíquota base. Indica cadastro de produto ou mapeamento tributário/NCM incompleto no ERP de origem.',
                ],
            ],
        ];
    }
}
