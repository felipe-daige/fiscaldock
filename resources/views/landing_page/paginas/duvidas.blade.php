@php
    // FONTE ÚNICA: este array gera o accordion, o índice lateral e o FAQPage JSON-LD.
    // Não existe HTML espelhado — editar aqui é editar a página inteira.
    // Preço, fontes e disponibilidade de módulo vêm do catálogo (via controller), nunca hardcodados.
    $pricingData = $pricingData ?? [];
    $clearancePricing = $clearancePricing ?? ['batch_basic' => 1.00, 'search' => 1.00, 'search_enabled' => false];
    $products = collect($pricingData['products'] ?? []);
    $sources = collect($pricingData['compliance_sources'] ?? [])->where('status', 'ativo');
    $minimumDeposit = (float) ($pricingData['minimum_deposit'] ?? 100);

    $trialBalance = (float) config('trial.saldo_reais');
    $trialDays = (int) config('trial.validade_dias');
    $sourceCount = max($sources->count(), 7);

    $brl = fn (?float $v) => $v === null ? null : \App\Support\Dinheiro::brl($v);
    $priceOf = fn (string $slug) => $brl($products->firstWhere('slug', $slug)['price'] ?? null);

    $precoValidacao = $priceOf('validacao') ?? 'R$ 3,00';
    $precoCompliance = $priceOf('compliance') ?? 'R$ 5,00';
    $precoClearanceLote = $brl((float) $clearancePricing['batch_basic']);
    $precoClearanceAvulsa = $brl((float) $clearancePricing['search']);
    $buscaAvulsaAtiva = (bool) $clearancePricing['search_enabled'];

    $categorias = [
        'comecar' => 'Começar',
        'documentos' => 'Documentos e importação',
        'consultas' => 'Consultas e monitoramento',
        'clearance' => 'Clearance de notas',
        'reforma' => 'Reforma Tributária',
        'precos' => 'Preços e saldo',
        'seguranca' => 'Segurança e dados',
    ];

    $faqs = [
        [
            'cat' => 'comecar',
            'q' => 'O que é a FiscalDock e o que ela faz que meu sistema não faz?',
            'a' => 'A FiscalDock é uma camada de inteligência fiscal sobre os dados que você já tem. Ela importa seus arquivos EFD (ICMS/IPI e PIS/COFINS) e XMLs de notas, consulta '.$sourceCount.' fontes oficiais sobre cada CNPJ da sua carteira, confronta o que foi declarado com a situação oficial dos documentos e transforma isso em alertas, dossiês e estimativa de crédito IBS/CBS. O sistema contábil escritura; a FiscalDock lê a escrituração e diz onde está o risco e o dinheiro.',
            'link' => ['label' => 'Ver a plataforma de ponta a ponta', 'url' => route('solucoes')],
        ],
        [
            'cat' => 'comecar',
            'q' => 'A FiscalDock substitui o Domínio, Alterdata ou Contmatic?',
            'a' => 'Não, e não tenta. Você continua escriturando no seu ERP contábil e apenas exporta o SPED de lá. A FiscalDock trabalha ao lado dele, fazendo a análise de risco, os cruzamentos e o monitoramento de CNPJ que o ERP não faz. Não há migração, não há substituição de sistema e nenhum dado precisa sair do seu fluxo atual.',
        ],
        [
            'cat' => 'comecar',
            'q' => 'Como começo? Preciso de treinamento técnico?',
            'a' => 'Crie a conta, cadastre sua empresa e seus clientes e suba um arquivo SPED (.txt) ou os XMLs. O processamento é automático e o progresso aparece em tempo real na tela. A conta já nasce com '.\App\Support\Dinheiro::brl($trialBalance).' de saldo por '.$trialDays.' dias, sem cartão — dá para importar arquivos reais e avaliar o resultado antes de pagar qualquer coisa.',
            'link' => ['label' => 'Criar conta com '.\App\Support\Dinheiro::brl($trialBalance).' grátis', 'url' => route('signup')],
        ],
        [
            'cat' => 'documentos',
            'q' => 'Quais arquivos posso importar e o que exatamente é extraído?',
            'a' => 'EFD ICMS/IPI, EFD Contribuições (PIS/COFINS) e XML de NF-e e CT-e. Do SPED saem participantes (registro 0150), notas e itens por bloco (C, D, A), catálogo de produtos (0200 com NCM, CFOP, CST e alíquotas), apuração de ICMS e IPI (bloco E), apuração de PIS/COFINS (bloco M) e retenções na fonte (F600). Tudo fica navegável até o documento de origem — nenhum número da plataforma é um número solto.',
            'link' => ['label' => 'Documentos e acervo fiscal', 'url' => route('solucoes').'#documentos'],
        ],
        [
            'cat' => 'documentos',
            'q' => 'Quanto tempo leva a importação de um SPED?',
            'a' => 'Um SPED de PIS/COFINS típico leva cerca de 5 minutos, com progresso por bloco em tempo real na tela — você acompanha participantes, notas, itens e apurações sendo extraídos. Arquivos maiores levam mais, e a plataforma sinaliza sozinha se uma importação travar, em vez de deixar você olhando para uma barra parada.',
        ],
        [
            'cat' => 'documentos',
            'q' => 'O que a plataforma faz com o catálogo de itens (registro 0200)?',
            'a' => 'O catálogo é cruzado com os itens efetivamente movimentados nas notas, por período. Isso revela item cadastrado sem NCM, alíquota do cadastro divergente da praticada nas notas e mudanças de cadastro ao longo do tempo (drift), com histórico período-fiel: uma nota de janeiro é lida com o cadastro que valia em janeiro, não com o de hoje.',
            'link' => ['label' => 'Catálogo × movimentação', 'url' => route('solucoes').'#documentos'],
        ],
        [
            'cat' => 'consultas',
            'q' => 'O que a consulta de CNPJ verifica?',
            'a' => 'São '.$sourceCount.' fontes oficiais em uma única consulta: cadastro da Receita Federal (situação, CNAEs, QSA, capital, regime), CND Federal (PGFN/RFB), CND Estadual (SEFAZ), CND Municipal (prefeitura do participante), CNDT (TST), CRF do FGTS (Caixa) e SINTEGRA (inscrição estadual). O resultado vem normalizado, com data, origem e status por fonte — e vira Score Fiscal e alerta quando algo vence ou fica irregular.',
            'link' => ['label' => 'Consulta e monitoramento de CNPJ', 'url' => route('solucoes').'#risco'],
        ],
        [
            'cat' => 'consultas',
            'q' => 'Qual a diferença entre consultar e monitorar?',
            'a' => 'A consulta é a fotografia: você pergunta agora e recebe a situação agora. O monitoramento é o filme: o CNPJ entra em um grupo com ciclo definido, a plataforma reconsulta sozinha, guarda o histórico, recalcula o Score e abre alerta quando uma certidão vence, quando a situação cadastral muda ou quando um fornecedor relevante fica irregular. Quem compra de 80 fornecedores não consegue conferir 80 certidões toda semana na mão.',
        ],
        [
            'cat' => 'consultas',
            'q' => 'E quando a Receita não publica o regime tributário do CNPJ?',
            'a' => 'A plataforma estima o regime a partir de evidências (CNAE, natureza jurídica, porte e volume de vendas escriturado) e marca explicitamente a origem como "estimado", sempre exibida junto do valor. A estimativa nunca sobrescreve um regime informado oficialmente: se a RFB publicar depois, o dado real prevalece. Você nunca vê um "não informado" mudo, nem um palpite disfarçado de fato.',
        ],
        [
            'cat' => 'clearance',
            'q' => 'O Clearance de notas já está funcionando?',
            'a' => 'Sim, está em produção. Você valida NF-e e CT-e do seu acervo em lote ou consulta uma chave avulsa diretamente, e a plataforma guarda dois lados separados: o que o contador declarou (XML e SPED) e o snapshot oficial do documento. O confronto mostra a divergência sem misturar as fontes — o caso clássico é a nota cancelada depois da escrituração, que continua gerando crédito indevido na apuração.',
            'link' => ['label' => 'Clearance de documentos', 'url' => route('solucoes').'#clearance'],
        ],
        [
            'cat' => 'clearance',
            'q' => 'Quanto custa validar uma nota?',
            'a' => match (true) {
                // Preço único (estado atual): lote e busca avulsa fazem a mesma chamada oficial
                // e gravam o mesmo snapshot — cobrar diferente seria cobrar pelo caminho, não pelo trabalho.
                $buscaAvulsaAtiva && $precoClearanceLote === $precoClearanceAvulsa => 'Um preço só: '.$precoClearanceLote.' por documento, debitado do saldo em reais. Vale tanto para validar uma nota que já está no seu acervo quanto para buscar uma chave avulsa que a plataforma ainda não conhece — os dois caminhos fazem a mesma consulta oficial e guardam o mesmo snapshot, então não faria sentido cobrar diferente. Antes de rodar um lote você vê o custo total; e se a chave já estiver no acervo, a plataforma avisa em vez de cobrar de novo.',
                $buscaAvulsaAtiva => 'O clearance sobre documento que já está no seu acervo custa '.$precoClearanceLote.' por documento. A busca avulsa por chave — quando o documento ainda não está na plataforma — custa '.$precoClearanceAvulsa.'. Se a chave já estiver no seu acervo, a plataforma avisa antes e oferece o caminho mais barato em vez de cobrar o mais caro.',
                default => 'O clearance sobre documento que já está no seu acervo custa '.$precoClearanceLote.' por documento, cobrado do saldo em reais. O preço aparece antes da execução e você confirma o custo total do lote antes de rodar.',
            },
            'link' => ['label' => 'Preços por documento', 'url' => route('precos').'#clearance'],
        ],
        [
            'cat' => 'reforma',
            'q' => 'O que a FiscalDock faz sobre a Reforma Tributária?',
            'a' => 'Ela transforma o que você já escriturou em uma estimativa financeira de crédito IBS/CBS: volume de entradas × alíquota do ano da transição × fator do regime do fornecedor. O resultado é dividido em crédito potencial, crédito aproveitável e valor em risco — este último é o que vem de fornecedor no Simples, MEI ou com regime indefinido. É uma estimativa parametrizável para planejamento, não apuração oficial nem garantia de aproveitamento.',
            'link' => ['label' => 'Reforma e crédito IBS/CBS', 'url' => route('solucoes').'#reforma'],
        ],
        [
            'cat' => 'precos',
            'q' => 'Como funciona a cobrança: assinatura ou pré-pago?',
            'a' => 'Os dois, e você escolhe. O plano mensal cobre a rotina recorrente (monitoramento automático, limites e recursos). O saldo pré-pago em reais paga o que é consumo real: cada consulta e cada documento validado tem preço fixo, debitado do saldo. Você vê o preço antes de executar, e saldo comprado não expira. A recarga opcional começa em R$ '.number_format($minimumDeposit, 0, ',', '.').'.',
            'link' => ['label' => 'Comparar planos e preços', 'url' => route('precos')],
        ],
        [
            'cat' => 'precos',
            'q' => 'Quanto custa consultar um CNPJ?',
            'a' => 'Depende da profundidade. A consulta cadastral básica é gratuita e não consome saldo. A partir daí o preço é fixo por produto: '.$precoValidacao.' por CNPJ na Validação (regime, QSA, CNAEs, parecer fiscal) e '.$precoCompliance.' por CNPJ no Compliance, que reúne as '.$sourceCount.' fontes — algo em torno de '.$brl(round(((float) ($products->firstWhere('slug', 'compliance')['price'] ?? 5)) / $sourceCount, 2)).' por fonte oficial consultada.',
            'link' => ['label' => 'Ver todos os níveis de consulta', 'url' => route('precos').'#precos-consumo'],
        ],
        [
            'cat' => 'precos',
            'q' => 'Posso testar antes de colocar dinheiro?',
            'a' => 'Sim. A conta nasce com '.\App\Support\Dinheiro::brl($trialBalance).' de saldo válidos por '.$trialDays.' dias, sem cartão. Dá para importar SPED real, ver dashboards, alertas e rodar consultas pagas com esse saldo. A única ressalva honesta: consultas em bases oficiais consomem saldo mesmo durante o teste, porque cada chamada tem custo real do outro lado.',
        ],
        [
            'cat' => 'seguranca',
            'q' => 'Meus dados e os dos meus clientes ficam isolados?',
            'a' => 'Sim. O acesso é isolado por conta: cada escritório enxerga apenas os próprios clientes, documentos e consultas. Nada é compartilhado entre contas e nenhum dado da sua carteira alimenta a carteira de outro escritório.',
        ],
        [
            'cat' => 'seguranca',
            'q' => 'E a LGPD? Consigo exportar e excluir meus dados?',
            'a' => 'Sim. A plataforma tem centro de privacidade com registro de consentimentos, exportação dos seus dados e solicitação de exclusão. A exclusão é irreversível por definição, então ela passa por confirmação explícita — e o que você exportou continua seu, nos formatos PDF, XLSX e CSV.',
            'link' => ['label' => 'Política de privacidade', 'url' => url('/privacidade')],
        ],
        [
            'cat' => 'seguranca',
            'q' => 'O que acontece se eu parar de usar a plataforma?',
            'a' => 'Os dados continuam disponíveis para exportação por um período, e depois são removidos. Se você voltar, basta reimportar os arquivos SPED: a plataforma reprocessa a base e os cruzamentos do zero, porque tudo o que ela mostra é derivado dos seus documentos — nada depende de um histórico que só existe dentro dela.',
        ],
    ];

    $faqsPorCategoria = collect($faqs)->groupBy('cat');
    $totalFaqs = count($faqs);
@endphp

@push('structured-data')
@include('landing_page.partials.breadcrumb-schema', [
    'trail' => [
        ['name' => 'Início', 'url' => url('/')],
        ['name' => 'Dúvidas', 'url' => url('/duvidas')],
    ],
])
@include('landing_page.paginas.partials.duvidas-faq-schema', ['faqs' => $faqs])
@endpush

<style>
    .faq-page {
        --faq-ink: #0b1424;
        --faq-blue: #1e4fa0;
        --faq-line: #e3e8ee;
        --faq-soft: #f4f7fa;
        --faq-muted: #667085;
        color: #111827;
        background: #fff;
        overflow: clip;
    }
    .faq-page *, .faq-page *::before, .faq-page *::after { box-sizing: border-box; }
    .faq-shell { width: min(100% - 2rem, 80rem); margin-inline: auto; }
    .faq-kicker {
        display: inline-flex; align-items: center; gap: .65rem;
        font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
        font-size: .67rem; font-weight: 700; letter-spacing: .2em; line-height: 1.25;
        text-transform: uppercase; color: #728095;
    }
    .faq-kicker::before { content: ''; width: 1.65rem; height: 1px; background: currentColor; opacity: .5; }
    .faq-kicker--light { color: rgba(255,255,255,.62); }

    /* Hero — mesmos tokens de .sol-hero (/solucoes) e .pricing-hero (/precos). Manter em sincronia. */
    .faq-hero {
        position: relative; isolation: isolate;
        display: flex; align-items: center;
        min-height: clamp(28rem, 52vw, 36rem);
        padding: clamp(4.5rem, 9vw, 7.5rem) 0 clamp(4rem, 8vw, 7rem);
        color: #fff;
        background:
            radial-gradient(circle at 83% 18%, rgba(55,116,198,.25), transparent 30rem),
            linear-gradient(145deg, #081322 0%, #10233f 62%, #0d1c32 100%);
    }
    .faq-hero::before {
        content: ''; position: absolute; inset: 0; z-index: -1; pointer-events: none;
        background-image:
            linear-gradient(to right, rgba(148,197,255,.055) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(148,197,255,.055) 1px, transparent 1px);
        background-size: 46px 46px;
        -webkit-mask-image: radial-gradient(100% 100% at 70% 20%, #000 10%, transparent 75%);
        mask-image: radial-gradient(100% 100% at 70% 20%, #000 10%, transparent 75%);
    }
    .faq-hero-grid { display: grid; grid-template-columns: minmax(0,1.05fr) minmax(21rem,.72fr); gap: clamp(2.5rem,7vw,5rem); align-items: center; }
    .faq-hero h1 { margin-top: 1.15rem; max-width: 42rem; font-family: 'Fraunces', Georgia, serif; font-size: clamp(2.6rem,5.2vw,4.4rem); font-weight: 600; line-height: 1; letter-spacing: -.035em; color: #fff; }
    .faq-hero h1 em { color: #fde68a; font-style: normal; }
    .faq-hero-copy { margin-top: 1.3rem; max-width: 38rem; font-size: clamp(.98rem,1.4vw,1.1rem); line-height: 1.72; color: rgba(255,255,255,.72); }
    .faq-hero-facts { display: flex; flex-wrap: wrap; gap: .7rem 1.35rem; margin-top: 1.5rem; }
    .faq-hero-fact { display: inline-flex; align-items: center; gap: .45rem; font-size: .75rem; color: rgba(255,255,255,.66); }
    .faq-hero-fact::before { content: ''; width: .42rem; height: .42rem; border-radius: 50%; background: #fde68a; }

    /* Card lateral do hero — “o que mais perguntam” */
    .faq-top {
        position: relative; border: 1px solid rgba(255,255,255,.15); border-radius: 1.3rem;
        padding: 1rem; background: rgba(7,17,31,.64); box-shadow: 0 36px 80px -40px rgba(0,0,0,.75);
        backdrop-filter: blur(14px);
    }
    .faq-top::after {
        content: ''; position: absolute; inset: -18% -12% 40%; z-index: -1; pointer-events: none;
        border-radius: 50%; opacity: .55; filter: blur(26px);
        background: radial-gradient(closest-side, rgba(94,152,224,.35), transparent 72%);
    }
    .faq-top-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .2rem .25rem .9rem; }
    .faq-top-head span { font-family: ui-monospace, monospace; font-size: .6rem; letter-spacing: .16em; text-transform: uppercase; color: rgba(255,255,255,.47); }
    .faq-top-head em { font-family: ui-monospace, monospace; font-size: .58rem; font-style: normal; font-weight: 700; color: #a7f3d0; }
    .faq-top-link { display: grid; grid-template-columns: auto minmax(0,1fr) auto; gap: .6rem; align-items: center; border: 1px solid rgba(255,255,255,.09); border-radius: .8rem; padding: .8rem .85rem; background: rgba(255,255,255,.045); text-decoration: none; transition: border-color .18s ease, background .18s ease, transform .18s ease; }
    .faq-top-link + .faq-top-link { margin-top: .45rem; }
    .faq-top-link:hover { transform: translateY(-2px); border-color: rgba(255,255,255,.22); background: rgba(255,255,255,.075); }
    .faq-top-link i { display: grid; place-items: center; width: 1.6rem; height: 1.6rem; border-radius: .5rem; font-family: ui-monospace, monospace; font-style: normal; font-size: .58rem; font-weight: 800; color: #fde68a; background: rgba(253,230,138,.12); }
    .faq-top-link strong { min-width: 0; font-size: .74rem; font-weight: 650; line-height: 1.35; color: #fff; }
    .faq-top-link svg { width: .8rem; height: .8rem; color: rgba(255,255,255,.35); transition: transform .18s ease, color .18s ease; }
    .faq-top-link:hover svg { transform: translateX(2px); color: #fde68a; }
    .faq-top-foot { margin-top: .8rem; padding-top: .75rem; border-top: 1px dashed rgba(255,255,255,.12); font-family: ui-monospace, monospace; font-size: .53rem; line-height: 1.5; letter-spacing: .04em; text-transform: uppercase; color: rgba(255,255,255,.38); }

    /* Corpo — índice sticky + accordions */
    .faq-body { padding: clamp(4rem,7vw,6.5rem) 0; background: var(--faq-soft); }
    .faq-body-grid { display: grid; grid-template-columns: minmax(0,.32fr) minmax(0,1fr); gap: clamp(2rem,4vw,3.5rem); align-items: start; }
    .faq-index { position: sticky; top: 6rem; }
    .faq-index h2 { font-family: 'Fraunces', Georgia, serif; font-size: 1.6rem; font-weight: 650; line-height: 1.1; color: var(--faq-ink); }
    .faq-index > p { margin-top: .6rem; font-size: .76rem; line-height: 1.6; color: var(--faq-muted); }
    .faq-index-list { display: grid; gap: .2rem; margin-top: 1.2rem; }
    .faq-index-link { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: .6rem; align-items: center; border-radius: .6rem; padding: .55rem .65rem; font-size: .76rem; font-weight: 650; color: #4d5968; text-decoration: none; transition: background .18s ease, color .18s ease; }
    .faq-index-link:hover { background: #fff; color: var(--faq-ink); }
    .faq-index-link b { font-family: ui-monospace, monospace; font-size: .58rem; font-weight: 700; color: #9aa3b0; }
    .faq-index-cta { margin-top: 1.2rem; border-top: 1px dashed #d5dde6; padding-top: 1.1rem; }
    .faq-index-cta p { font-size: .75rem; line-height: 1.55; color: var(--faq-muted); }
    .faq-index-cta a { display: inline-flex; align-items: center; gap: .4rem; margin-top: .6rem; font-size: .78rem; font-weight: 700; color: var(--faq-blue); text-decoration: none; }
    .faq-index-cta a:hover { text-decoration: underline; }

    .faq-group + .faq-group { margin-top: 2.2rem; }
    .faq-group-head { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding-bottom: .7rem; border-bottom: 1px solid #dde4ec; scroll-margin-top: 6rem; }
    .faq-group-head h3 { font-family: ui-monospace, monospace; font-size: .6rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #6b7684; }
    .faq-group-head span { font-family: ui-monospace, monospace; font-size: .55rem; letter-spacing: .06em; text-transform: uppercase; color: #a4adb9; }

    .faq-item { margin-top: .5rem; border: 1px solid var(--faq-line); border-radius: .8rem; background: #fff; transition: border-color .18s ease, box-shadow .18s ease; }
    .faq-item:hover { border-color: #c6d2de; box-shadow: 0 16px 34px -30px rgba(15,35,65,.5); }
    .faq-item[open] { border-color: #b9c9da; box-shadow: 0 20px 42px -32px rgba(15,35,65,.55); }
    .faq-item summary {
        display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 1rem; align-items: center;
        padding: 1rem 1.1rem; cursor: pointer; list-style: none;
        font-size: .88rem; font-weight: 700; line-height: 1.45; color: #202938;
    }
    .faq-item summary::-webkit-details-marker { display: none; }
    .faq-item summary:hover { color: var(--faq-blue); }
    .faq-item summary svg { width: 1rem; height: 1rem; flex: 0 0 auto; color: #9aa3b0; transition: transform .22s ease, color .18s ease; }
    .faq-item[open] summary svg { transform: rotate(180deg); color: var(--faq-blue); }
    .faq-answer { padding: 0 1.1rem 1.1rem; }
    .faq-answer p { border-top: 1px solid #eef1f5; padding-top: .85rem; font-size: .82rem; line-height: 1.75; color: #536071; }
    .faq-answer a.faq-answer-link { display: inline-flex; align-items: center; gap: .35rem; margin-top: .75rem; font-size: .76rem; font-weight: 700; color: var(--faq-blue); text-decoration: none; }
    .faq-answer a.faq-answer-link:hover { text-decoration: underline; }
    .faq-item[open] .faq-answer { animation: faq-open .26s ease both; }
    @keyframes faq-open { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }

    /* CTA final */
    .faq-final { position: relative; isolation: isolate; padding: clamp(4rem,7vw,6rem) 0; text-align: center; color: #fff; background: linear-gradient(145deg,#081322,#10233f 64%,#0d1c32); }
    .faq-final::before {
        content: ''; position: absolute; inset: 0; z-index: -1; pointer-events: none;
        background-image:
            linear-gradient(to right, rgba(148,197,255,.055) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(148,197,255,.055) 1px, transparent 1px);
        background-size: 46px 46px;
        -webkit-mask-image: radial-gradient(100% 100% at 50% 25%, #000 10%, transparent 75%);
        mask-image: radial-gradient(100% 100% at 50% 25%, #000 10%, transparent 75%);
    }
    .faq-final h2 { max-width: 44rem; margin: 1rem auto 0; font-family: 'Fraunces',Georgia,serif; font-size: clamp(2rem,4vw,3.2rem); font-weight: 600; line-height: 1.06; letter-spacing: -.035em; }
    .faq-final p { max-width: 38rem; margin: 1rem auto 0; font-size: .92rem; line-height: 1.7; color: rgba(255,255,255,.67); }
    .faq-final-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: .8rem; margin-top: 1.6rem; }
    .faq-btn-secondary {
        display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 48px;
        padding: .875rem 1.35rem; border: 1px solid rgba(255,255,255,.27); border-radius: 8px;
        color: #fff; font-size: .94rem; font-weight: 700; text-decoration: none; transition: .18s ease;
    }
    .faq-btn-secondary:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.5); }

    @media (max-width: 980px) {
        .faq-hero-grid, .faq-body-grid { grid-template-columns: 1fr; }
        .faq-index { position: static; }
        .faq-index-list { grid-template-columns: repeat(2,minmax(0,1fr)); }
    }
    @media (max-width: 680px) {
        .faq-shell { width: min(100% - 1.25rem, 80rem); }
        .faq-hero { padding-top: 3.5rem; }
        .faq-hero h1 { font-size: clamp(2.4rem,11vw,3.4rem); }
        .faq-index-list { grid-template-columns: 1fr; }
        .faq-final-actions { display: grid; grid-template-columns: 1fr; }
        .faq-final-actions > * { width: 100%; }
        .faq-item summary { font-size: .84rem; padding: .9rem 1rem; }
    }
</style>

<div class="faq-page">
    <section class="faq-hero" aria-labelledby="faq-title">
        <div class="faq-shell faq-hero-grid">
            <div>
                <span class="faq-kicker faq-kicker--light">Central de dúvidas</span>
                <h1 id="faq-title">As respostas <em>antes</em> de você criar a conta.</h1>
                <p class="faq-hero-copy">
                    O que a plataforma importa, o que ela consulta, quanto custa cada coisa e o que acontece
                    com os seus dados. {{ $totalFaqs }} perguntas respondidas sem rodeio — incluindo as
                    ressalvas que a maioria dos fornecedores prefere não escrever.
                </p>
                <div class="faq-hero-facts">
                    <span class="faq-hero-fact">{{ \App\Support\Dinheiro::brl($trialBalance) }} para testar, sem cartão</span>
                    <span class="faq-hero-fact">{{ $sourceCount }} fontes oficiais por consulta</span>
                    <span class="faq-hero-fact">Preço visível antes de executar</span>
                </div>
            </div>

            <aside class="faq-top" aria-label="Perguntas mais acessadas">
                <div class="faq-top-head">
                    <span>As mais procuradas</span>
                    <em>{{ $totalFaqs }} respostas</em>
                </div>
                @foreach([
                    ['01', 'Substitui meu sistema contábil?', 'comecar'],
                    ['02', 'Quanto custa consultar um CNPJ?', 'precos'],
                    ['03', 'O Clearance já está funcionando?', 'clearance'],
                    ['04', 'Posso testar antes de pagar?', 'precos'],
                ] as [$n, $label, $anchor])
                    <a class="faq-top-link" href="#cat-{{ $anchor }}">
                        <i>{{ $n }}</i>
                        <strong>{{ $label }}</strong>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endforeach
                <p class="faq-top-foot">Nenhuma resposta exige cadastro · preços vêm do catálogo em produção</p>
            </aside>
        </div>
    </section>

    <section class="faq-body">
        <div class="faq-shell faq-body-grid">
            <aside class="faq-index">
                <h2>Por onde você quer começar?</h2>
                <p>As perguntas seguem o caminho real de uso: entrar, importar, consultar, validar e pagar.</p>

                <nav class="faq-index-list" aria-label="Categorias de dúvidas">
                    @foreach($categorias as $slug => $nome)
                        @if($faqsPorCategoria->has($slug))
                            <a class="faq-index-link" href="#cat-{{ $slug }}">
                                {{ $nome }}
                                <b>{{ $faqsPorCategoria[$slug]->count() }}</b>
                            </a>
                        @endif
                    @endforeach
                </nav>

                <div class="faq-index-cta">
                    <p>Não achou a sua? A resposta específica costuma vir mais rápido de gente do que de página.</p>
                    <a href="{{ route('agendar') }}" data-link>Falar com um especialista →</a>
                </div>
            </aside>

            <div>
                @foreach($categorias as $slug => $nome)
                    @if($faqsPorCategoria->has($slug))
                        <div class="faq-group">
                            <div class="faq-group-head" id="cat-{{ $slug }}">
                                <h3>{{ $nome }}</h3>
                                <span>{{ $faqsPorCategoria[$slug]->count() }} {{ $faqsPorCategoria[$slug]->count() === 1 ? 'pergunta' : 'perguntas' }}</span>
                            </div>

                            @foreach($faqsPorCategoria[$slug] as $faq)
                                <details class="faq-item">
                                    <summary>
                                        {{ $faq['q'] }}
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </summary>
                                    <div class="faq-answer">
                                        <p>{{ $faq['a'] }}</p>
                                        @isset($faq['link'])
                                            <a class="faq-answer-link" href="{{ $faq['link']['url'] }}" @if(str_starts_with($faq['link']['url'], url('/app'))) data-link @endif>
                                                {{ $faq['link']['label'] }} →
                                            </a>
                                        @endisset
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <section class="faq-final" aria-labelledby="faq-final-title">
        <div class="faq-shell">
            <span class="faq-kicker faq-kicker--light" style="justify-content:center">Ainda com dúvida?</span>
            <h2 id="faq-final-title">Traga um SPED real e veja o que a plataforma encontra.</h2>
            <p>
                A conta começa com {{ \App\Support\Dinheiro::brl($trialBalance) }} de saldo por {{ $trialDays }} dias,
                sem cartão. Importe um arquivo de verdade — é assim que dá para julgar.
            </p>
            <div class="faq-final-actions">
                <a href="{{ route('signup') }}" class="btn-cta">Criar conta com {{ \App\Support\Dinheiro::brl($trialBalance) }}</a>
                <a href="{{ route('agendar') }}" class="faq-btn-secondary">Falar com especialista</a>
            </div>
        </div>
    </section>
</div>
