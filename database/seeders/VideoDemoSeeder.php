<?php

namespace Database\Seeders;

use App\Services\RiskScoreService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Conta demo para as capturas do vídeo de marketing (docs/video-demo/README.md).
 *
 * Cria o usuário demo.video@fiscaldock.com.br com dados fictícios coerentes com os
 * mocks aprovados do vídeo (Transportes Alfa inapta, Metalúrgica Boreal, Comercial
 * Delta em risco, 2.618 notas EFD). Idempotente: reexecutar apaga e recria tudo.
 *
 * NUNCA rodar apontando pro usuário de um cliente real — o wipe apaga por user_id.
 */
class VideoDemoSeeder extends Seeder
{
    public const EMAIL = 'demo.video@fiscaldock.com.br';

    public const SENHA = 'FiscalDock@Demo2026';

    private int $userId;

    private array $clientes = [];

    private array $participantes = [];

    private array $catalogo = [];

    public function run(): void
    {
        DB::transaction(function () {
            $this->wipeExistente();
            $this->criarUsuarioContaPlano();
            $this->criarClientes();
            $this->criarParticipantes();
            $this->criarImportacoesEfdENotas();
            $this->criarXmlEClearance();
            $this->criarConsultasEScores();
            $this->criarMonitoramento();
        });

        // Alertas reais via detectores do produto + retro-datação: espalha os created_at
        // por ~10 semanas (tendência crescente) pro gráfico "Evolução de alertas" da
        // central não ficar com barra única na semana atual; os N mais graves ficam de
        // hoje pra manter o badge "+N novos hoje".
        \Illuminate\Support\Facades\Artisan::call('alertas:recalcular', ['user' => $this->userId]);
        $ids = DB::table('alertas')->where('user_id', $this->userId)
            ->orderByRaw("CASE severidade WHEN 'alta' THEN 0 WHEN 'media' THEN 1 ELSE 2 END")
            ->orderBy('id')
            ->pluck('id')->values();
        $diasPorPosicao = [0, 0, 0, 0, 7, 7, 14, 14, 28, 28, 35, 35, 49, 49, 63];
        foreach ($ids as $i => $id) {
            $dias = $diasPorPosicao[$i] ?? 63;
            DB::table('alertas')->where('id', $id)->update([
                'created_at' => Carbon::now('UTC')->subDays($dias)->subHours(2 + ($id % 5)),
            ]);
        }

        $this->command?->info('Demo pronta: '.self::EMAIL.' / '.self::SENHA.' (user_id='.$this->userId.')');
        $this->command?->info('Alertas recalculados e retro-datados — nada mais a rodar.');
    }

    private function wipeExistente(): void
    {
        $existente = DB::table('users')->where('email', self::EMAIL)->value('id');
        if (! $existente) {
            return;
        }

        $loteIds = DB::table('consulta_lotes')->where('user_id', $existente)->pluck('id');
        if ($loteIds->isNotEmpty()) {
            DB::table('consulta_lote_participantes')->whereIn('consulta_lote_id', $loteIds)->delete();
        }

        // Toda tabela com coluna user_id é limpa dinamicamente (ordem: filhas antes por FK).
        $comUserId = DB::select("
            select c.table_name from information_schema.columns c
            join information_schema.tables t on t.table_name = c.table_name and t.table_schema = 'public'
            where c.table_schema = 'public' and c.column_name = 'user_id'
              and t.table_type = 'BASE TABLE' and c.table_name <> 'users'
        ");
        $ordem = ['efd_notas_itens', 'xml_notas_itens', 'efd_catalogo_historico', 'consulta_resultados', 'alertas', 'participante_scores'];
        $tabelas = collect($comUserId)->pluck('table_name')
            ->sortBy(fn ($t) => array_search($t, $ordem) === false ? 99 : array_search($t, $ordem))
            ->values();
        foreach ($tabelas as $tabela) {
            DB::table($tabela)->where('user_id', $existente)->delete();
        }

        DB::table('accounts')->where('owner_user_id', $existente)->delete();
        DB::table('users')->where('id', $existente)->delete();
    }

    private function criarUsuarioContaPlano(): void
    {
        $agora = Carbon::now();

        $this->userId = DB::table('users')->insertGetId([
            'name' => 'Mariana',
            'sobrenome' => 'Duarte',
            'telefone' => '(67) 99900-0000',
            'email' => self::EMAIL,
            'email_verified_at' => $agora,
            'password' => Hash::make(self::SENHA),
            'empresa' => 'Meridiana Contabilidade',
            'cargo' => 'Contadora responsável',
            'credits' => 250,
            'terms_accepted_at' => $agora,
            'terms_version' => config('legal.terms_version', '1.0'),
            'privacy_version' => config('legal.privacy_version', '1.0'),
            'trial_used' => true,
            'alertas_operacionais' => true,
            'alertas_monitoramento' => true,
            'resumo_periodico' => true,
            'created_at' => $agora->copy()->subMonths(6),
            'updated_at' => $agora,
        ]);

        $accountId = DB::table('accounts')->insertGetId([
            'owner_user_id' => $this->userId,
            'nome' => 'Meridiana Contabilidade',
            'created_at' => $agora->copy()->subMonths(6),
            'updated_at' => $agora,
        ]);
        DB::table('account_members')->insert([
            'account_id' => $accountId,
            'user_id' => $this->userId,
            'papel' => 'owner',
            'entrou_em' => $agora->copy()->subMonths(6),
            'created_at' => $agora,
            'updated_at' => $agora,
        ]);

        $planoEscritorio = DB::table('subscription_plans')->where('codigo', 'escritorio')->value('id');
        if ($planoEscritorio) {
            DB::table('account_subscriptions')->insert([
                'user_id' => $this->userId,
                'subscription_plan_id' => $planoEscritorio,
                'status' => 'ativa',
                'ciclo' => 'mensal',
                'iniciada_em' => $agora->copy()->subMonths(3),
                'renova_em' => $agora->copy()->addDays(22),
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        }

        // 'purchase' > 0 desbloqueia os planos de consulta avançados.
        DB::table('credit_transactions')->insert([
            'user_id' => $this->userId,
            'amount' => 250,
            'balance_after' => 250,
            'type' => 'purchase',
            'description' => 'Recarga de saldo (demo)',
            'created_at' => $agora->copy()->subMonths(3),
            'updated_at' => $agora->copy()->subMonths(3),
        ]);
    }

    private function criarClientes(): void
    {
        $agora = Carbon::now();
        $defs = [
            ['Meridiana Contabilidade Ltda', '27.845.163/0001-04', true, 'ATIVA', 'Lucro Presumido', 'MS', 'Campo Grande'],
            ['Metalúrgica Boreal S.A.', '98.765.432/0001-10', false, 'ATIVA', 'Lucro Real', 'SP', 'São Paulo'],
            ['Comercial Delta ME', '45.678.901/0001-22', false, 'ATIVA', 'Simples Nacional', 'MS', 'Dourados'],
            ['Indústria Vetor Ltda', '11.222.333/0001-44', false, 'ATIVA', 'Lucro Real', 'PR', 'Curitiba'],
            ['Distribuidora Aurora Ltda', '22.333.444/0001-55', false, 'ATIVA', 'Lucro Presumido', 'SP', 'Campinas'],
            ['Serviços Orion Ltda', '33.444.555/0001-66', false, 'ATIVA', 'Simples Nacional', 'MS', 'Campo Grande'],
            ['Alimentos Prisma S.A.', '44.555.666/0001-77', false, 'ATIVA', 'Lucro Real', 'SC', 'Joinville'],
            ['Logística Meridian Ltda', '55.666.777/0001-88', false, 'ATIVA', 'Lucro Presumido', 'MT', 'Cuiabá'],
            ['Construtora Atlas Ltda', '66.777.888/0001-99', false, 'ATIVA', 'Lucro Presumido', 'GO', 'Goiânia'],
            ['Farmacêutica Lumen S.A.', '77.888.999/0001-05', false, 'ATIVA', 'Lucro Real', 'SP', 'Ribeirão Preto'],
        ];

        foreach ($defs as [$nome, $doc, $propria, $situacao, $regime, $uf, $municipio]) {
            $id = DB::table('clientes')->insertGetId([
                'user_id' => $this->userId,
                'tipo_pessoa' => 'PJ',
                'documento' => preg_replace('/\D/', '', $doc),
                'nome' => $nome,
                'razao_social' => $nome,
                'ativo' => true,
                'is_empresa_propria' => $propria,
                'situacao_cadastral' => $situacao,
                'regime_tributario' => $regime,
                'regime_tributario_origem' => 'rfb',
                'uf' => $uf,
                'municipio' => $municipio,
                'origem_tipo' => 'manual',
                'created_at' => $agora->copy()->subMonths(6),
                'updated_at' => $agora,
            ]);
            $this->clientes[$nome] = $id;
        }
    }

    private function criarParticipantes(): void
    {
        $agora = Carbon::now();
        $boreal = $this->clientes['Metalúrgica Boreal S.A.'];

        // [razão social, cnpj, situação, regime, uf, classificação de risco]
        $defs = [
            ['Transportes Alfa Ltda', '12.345.678/0001-90', 'INAPTA', 'Lucro Presumido', 'SP', 'critico'],
            ['Fornecedor Horizonte Ltda', '31.415.926/0001-53', 'ATIVA', 'Lucro Presumido', 'SP', 'baixo'],
            ['Comercial Delta ME', '45.678.901/0001-22', 'ATIVA', 'Simples Nacional', 'MS', 'alto'],
            ['Indústria Vetor Ltda', '11.222.333/0001-44', 'ATIVA', 'Lucro Real', 'PR', 'baixo'],
            ['Distribuidora Aurora Ltda', '22.333.444/0001-55', 'ATIVA', 'Lucro Presumido', 'SP', 'baixo'],
            ['Serviços Orion Ltda', '33.444.555/0001-66', 'ATIVA', 'Simples Nacional', 'MS', 'medio'],
            ['Alimentos Prisma S.A.', '44.555.666/0001-77', 'ATIVA', 'Lucro Real', 'SC', 'baixo'],
            ['Logística Meridian Ltda', '55.666.777/0001-88', 'ATIVA', 'Lucro Presumido', 'MT', 'baixo'],
            ['Agropecuária Cerrado Ltda', '18.293.746/0001-11', 'ATIVA', 'Lucro Presumido', 'GO', 'baixo'],
            ['Embalagens Vitória Ltda', '28.374.651/0001-23', 'ATIVA', 'Simples Nacional', 'SP', 'medio'],
            ['Química Andina S.A.', '38.475.629/0001-34', 'ATIVA', 'Lucro Real', 'SP', 'baixo'],
            ['Têxtil Planalto Ltda', '48.576.938/0001-45', 'BAIXADA', 'Lucro Presumido', 'SC', 'alto'],
            ['Metalcorte Aços Ltda', '58.679.213/0001-56', 'ATIVA', 'Lucro Real', 'MG', 'baixo'],
            ['Plásticos Iguaçu Ltda', '68.791.324/0001-67', 'ATIVA', 'Lucro Presumido', 'PR', 'baixo'],
            ['TransRápido Cargas Ltda', '79.813.542/0001-78', 'ATIVA', 'Simples Nacional', 'MS', 'medio'],
            ['Papelaria Central Ltda', '81.924.653/0001-89', 'ATIVA', 'Simples Nacional', 'MS', 'baixo'],
            ['Energia Solaris S.A.', '91.234.567/0001-91', 'ATIVA', 'Lucro Real', 'SP', 'baixo'],
            ['Gráfica Sul Horizonte Ltda', '13.579.246/0001-13', 'ATIVA', 'Lucro Presumido', 'RS', 'medio'],
        ];

        foreach ($defs as [$nome, $doc, $situacao, $regime, $uf, $risco]) {
            $id = DB::table('participantes')->insertGetId([
                'user_id' => $this->userId,
                'cliente_id' => $boreal,
                'documento' => preg_replace('/\D/', '', $doc),
                'tipo_documento' => 'PJ',
                'razao_social' => $nome,
                'situacao_cadastral' => $situacao,
                'regime_tributario' => $regime,
                'regime_tributario_origem' => 'rfb',
                'uf' => $uf,
                'origem_tipo' => 'efd',
                'ultima_consulta_em' => $agora->copy()->subDays(rand(1, 12)),
                'created_at' => $agora->copy()->subMonths(5),
                'updated_at' => $agora,
            ]);
            $this->participantes[] = ['id' => $id, 'nome' => $nome, 'doc' => preg_replace('/\D/', '', $doc), 'situacao' => $situacao, 'risco' => $risco, 'uf' => $uf];
        }
    }

    private function criarCatalogo(int $importacaoId): void
    {
        $agora = Carbon::now();
        $boreal = $this->clientes['Metalúrgica Boreal S.A.'];
        $itens = [
            ['MP-001', 'Chapa de aço laminado 3mm', '72085100', 18],
            ['MP-002', 'Bobina de alumínio 0,8mm', '76061190', 18],
            ['MP-003', 'Tubo de aço carbono 2"', '73063090', 18],
            ['MP-004', 'Resina industrial epóxi', '39073019', 18],
            ['MP-005', 'Óleo lubrificante sintético', '27101932', 18],
            ['MP-006', 'Tinta anticorrosiva cinza', '32089010', 18],
            ['MP-007', 'Eletrodo de solda E6013', '83111000', 12],
            ['MP-008', 'Parafuso sextavado M8', '73181500', 18],
            ['MP-009', 'Chapa galvanizada 2mm', '72104910', 18],
            ['MP-010', 'Perfil U de aço 100mm', '72161000', 18],
            ['MP-011', 'Disco de corte 7"', '68042290', 18],
            ['MP-012', 'Gás argônio industrial', '28042100', 18],
            ['PA-101', 'Estrutura metálica modular', '73089090', 12],
            ['PA-102', 'Suporte industrial reforçado', '73269090', 12],
            ['PA-103', 'Grade de proteção padrão', '73143900', 12],
            ['PA-104', 'Escada marinheiro 6m', '73089010', 12],
            ['PA-105', 'Plataforma de acesso', '73089090', 12],
            ['PA-106', 'Corrimão tubular inox', '73241000', 12],
            ['SV-201', 'Serviço de galvanização', null, null],
            ['MP-013', 'Notebook corporativo', '84713012', 18],
            ['MP-014', 'Embalagem técnica de madeira', '44152000', 7],
            // Mercadoria (tipo 01) SEM NCM de propósito: alimenta o KPI "NCM faltando"
            // do catálogo e o alerta detectarNcmFaltando (serviço tipo 09 não conta).
            ['MP-015', 'Etiqueta metálica gravada', null, 18],
            ['MP-016', 'Abraçadeira de aço inox', '73269090', 18],
            ['MP-017', 'Manta térmica cerâmica', '69032090', 18],
        ];

        foreach ($itens as [$cod, $descr, $ncm, $aliq]) {
            $id = DB::table('efd_catalogo_itens')->insertGetId([
                'user_id' => $this->userId,
                'cliente_id' => $boreal,
                'importacao_id' => $importacaoId,
                'cod_item' => $cod,
                'descr_item' => $descr,
                'tipo_item' => str_starts_with($cod, 'PA') ? '04' : (str_starts_with($cod, 'SV') ? '09' : '01'),
                'cod_ncm' => $ncm,
                'aliq_icms' => $aliq,
                'unid_inv' => 'UN',
                'created_at' => $agora->copy()->subMonths(5),
                'updated_at' => $agora,
            ]);
            $this->catalogo[] = ['id' => $id, 'cod' => $cod, 'descr' => $descr, 'ncm' => $ncm, 'aliq' => $aliq];
        }
    }

    private function chave(string $cnpj, string $modelo, int $numero, string $anoMes): string
    {
        $base = '35'.$anoMes.$cnpj.$modelo.'001'.str_pad((string) $numero, 9, '0', STR_PAD_LEFT).'1'.str_pad((string) (($numero * 7919) % 100000000), 8, '0', STR_PAD_LEFT);

        return $base.((array_sum(array_map('intval', str_split($base))) % 10));
    }

    private function criarImportacoesEfdENotas(): void
    {
        $agora = Carbon::now();
        $boreal = $this->clientes['Metalúrgica Boreal S.A.'];
        $cnpjBoreal = '98765432000110';

        // 2.618 notas no total (número canônico do marketing), jan-jun/2026.
        $meses = [
            ['2026-01', 320], ['2026-02', 410], ['2026-03', 560],
            ['2026-04', 450], ['2026-05', 480], ['2026-06', 398],
        ];

        // 1 importação POR MÊS por tipo (como o contador faz de verdade): o Resumo
        // Fiscal acha a apuração PIS/COFINS pela importação das notas do mês —
        // importação trimestral faria 3 competências apontarem pra mesma apuração.
        $importacoes = [];
        foreach (['EFD PIS/COFINS', 'EFD ICMS/IPI'] as $tipo) {
            foreach ($meses as $i => [$mes, $qtdMes]) {
                $ini = $mes.'-01';
                $fim = Carbon::parse($ini)->endOfMonth()->format('Y-m-d');
                $concluido = Carbon::parse($fim)->addDays(8)->setTime(9, rand(5, 55), 0);
                $importacoes[] = [
                    'id' => DB::table('efd_importacoes')->insertGetId([
                        'user_id' => $this->userId,
                        'cliente_id' => $boreal,
                        'cnpj' => $cnpjBoreal,
                        'tipo_efd' => $tipo,
                        'filename' => sprintf('efd_%s_boreal_%s.txt', $tipo === 'EFD PIS/COFINS' ? 'contribuicoes' : 'fiscal', str_replace('-', '', substr($fim, 0, 7))),
                        'status' => 'concluido',
                        'total_participantes' => count($this->participantes),
                        'total_cnpjs_unicos' => count($this->participantes),
                        'novos' => $i === 0 ? count($this->participantes) : 0,
                        'duplicados' => $i === 0 ? 0 : count($this->participantes),
                        'periodo_inicio' => $ini,
                        'periodo_fim' => $fim,
                        'iniciado_em' => $concluido->copy()->subMinutes(6),
                        'concluido_em' => $concluido,
                        'tempo_processamento_segundos' => rand(180, 360),
                        'creditos_cobrados' => 0,
                        'created_at' => $concluido,
                        'updated_at' => $concluido,
                    ]),
                    'tipo' => $tipo,
                    'ini' => $ini,
                    'fim' => $fim,
                ];
            }
        }

        $this->criarCatalogo($importacoes[0]['id']);

        $cfopsEntrada = ['1102', '2102', '1556', '1252', '2551'];
        $cfopsSaida = ['5102', '6102', '5405', '5101', '6108'];
        $numero = 9000;
        $linhas = [];
        $itens = [];
        $consolidados = [];
        $porMes = [];
        $notaId = (int) (DB::table('efd_notas')->max('id') ?? 0);

        foreach ($meses as [$mes, $qtd]) {
            $anoMes = substr($mes, 2, 2).substr($mes, 5, 2);
            $diasNoMes = Carbon::parse($mes.'-01')->daysInMonth;
            $porMes[$mes] = [
                'entradas' => 0.0, 'saidas' => 0.0,
                'icms_deb' => 0.0, 'icms_cred' => 0.0,
                'pis_deb' => 0.0, 'pis_cred' => 0.0,
                'cofins_deb' => 0.0, 'cofins_cred' => 0.0,
            ];
            for ($n = 0; $n < $qtd; $n++) {
                $numero++;
                $p = $this->participantes[$numero % count($this->participantes)];
                $saida = ($numero % 10) < 4; // 40% vendas, 60% compras
                // 18 participantes (par): origem não pode ser `numero % 2`, senão a paridade
                // de `numero % 18` fixa a origem e metade dos participantes fica sem notas
                // fiscais (dossiê/top CFOPs vazios). O intdiv quebra a correlação.
                $origem = (($numero + intdiv($numero, count($this->participantes))) % 2) === 0 ? 'contribuicoes' : 'fiscal';
                $modelo = ($numero % 17) === 0 ? '57' : '55';
                $valor = ($numero % 97 === 0) ? 0 : round(280 + (($numero * 137) % 44000) + rand(0, 99) / 100, 2);
                // Cauda pesada: ~1 a cada 89 notas é um pedido grande (até ~R$ 350 mil).
                // Sem isso a listagem ordenada por valor mostra dezenas de "R$ 44,2 mil"
                // quase idênticos no topo (teto da distribuição uniforme) — parece fake.
                if ($valor > 0 && ($numero % 89) === 0) {
                    $valor = round($valor * (3 + ($numero % 6)), 2);
                }
                $cancelada = ($numero % 401) === 0;
                $data = sprintf('%s-%02d', $mes, ($numero % $diasNoMes) + 1);
                $cnpjEmit = $saida ? $cnpjBoreal : $p['doc'];
                $importacao = collect($importacoes)->first(fn ($imp) => ($origem === 'contribuicoes') === ($imp['tipo'] === 'EFD PIS/COFINS') && $data >= $imp['ini'] && $data <= $imp['fim'])
                    ?? $importacoes[$origem === 'contribuicoes' ? 0 : count($meses)];

                $notaId++;
                $linhas[] = [
                    'id' => $notaId,
                    'user_id' => $this->userId,
                    'cliente_id' => $boreal,
                    'participante_id' => $p['id'],
                    'importacao_id' => $importacao['id'],
                    'chave_acesso' => $this->chave($cnpjEmit, $modelo, $numero, $anoMes),
                    'modelo' => $modelo,
                    'numero' => (string) $numero,
                    'serie' => '1',
                    'data_emissao' => $data,
                    'tipo_operacao' => $saida ? 'saida' : 'entrada',
                    'origem_arquivo' => $origem,
                    'valor_total' => $valor,
                    'cancelada' => $cancelada,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ];

                if (! $cancelada && $valor > 0) {
                    $porMes[$mes][$saida ? 'saidas' : 'entradas'] += $valor;
                }

                // C190/D190: consolidado por nota fiscal (base do Top CFOPs do dossiê e
                // dos agregados ICMS do BI — "ICMS sempre C190"). Entradas: ~55% geram
                // crédito (CST 000) e o resto é ST (CST 060, sem crédito) — o espelho
                // "Está batendo?" do Resumo Fiscal compara a apuração E110 com ESTAS
                // somas, então elas alimentam criarApuracoes() abaixo.
                if ($origem === 'fiscal' && $valor > 0) {
                    $temCredito = ! $saida && ($numero % 20) < 11;
                    $icmsNota = ($saida || $temCredito) ? round($valor * 0.12, 2) : 0;
                    if (! $cancelada) {
                        $porMes[$mes][$saida ? 'icms_deb' : 'icms_cred'] += $saida || $temCredito ? $icmsNota : 0;
                    }
                    $consolidados[] = [
                        'efd_nota_id' => $notaId,
                        'user_id' => $this->userId,
                        'cst_icms' => ($saida || $temCredito) ? '000' : '060',
                        'cfop' => (int) ($modelo === '57'
                            ? ($saida ? '5353' : '1353')
                            : ($saida ? $cfopsSaida[$numero % 5] : $cfopsEntrada[$numero % 5])),
                        'aliquota_icms' => ($saida || $temCredito) ? 12 : null,
                        'valor_operacao' => $valor,
                        'valor_bc_icms' => ($saida || $temCredito) ? $valor : 0,
                        'valor_icms' => $icmsNota,
                        'valor_bc_icms_st' => 0,
                        'valor_icms_st' => 0,
                        'valor_reducao_bc' => 0,
                        'valor_ipi' => 0,
                        'created_at' => $agora,
                        'updated_at' => $agora,
                    ];
                }

                if ($modelo === '55' && $valor > 0) {
                    $qtdItens = 1 + ($numero % 3);
                    $restante = $valor;
                    for ($k = 0; $k < $qtdItens; $k++) {
                        $item = $this->catalogo[($numero + $k) % count($this->catalogo)];
                        $vi = $k === $qtdItens - 1 ? $restante : round($restante / ($qtdItens - $k), 2);
                        $restante = round($restante - $vi, 2);
                        // Espelho PIS/COFINS do Resumo Fiscal soma valor_pis/cofins dos
                        // itens de notas 'contribuicoes' — acumula pra apuração bater.
                        if ($origem === 'contribuicoes' && ! $cancelada) {
                            $porMes[$mes][$saida ? 'pis_deb' : 'pis_cred'] += round($vi * 0.0165, 2);
                            $porMes[$mes][$saida ? 'cofins_deb' : 'cofins_cred'] += round($vi * 0.076, 2);
                        }
                        $itens[] = [
                            'efd_nota_id' => $notaId,
                            'user_id' => $this->userId,
                            'numero_item' => $k + 1,
                            'codigo_item' => $item['cod'],
                            'descricao' => $item['descr'],
                            'quantidade' => 1 + ($numero % 40),
                            'unidade_medida' => 'UN',
                            'valor_total' => $vi,
                            'cfop' => $saida ? $cfopsSaida[($numero + $k) % 5] : $cfopsEntrada[($numero + $k) % 5],
                            'cst_icms' => $saida ? '000' : '060',
                            'aliquota_icms' => $item['aliq'],
                            'valor_icms' => $item['aliq'] ? round($vi * $item['aliq'] / 100, 2) : null,
                            'cst_pis' => '01',
                            'aliquota_pis' => 1.65,
                            'valor_pis' => round($vi * 0.0165, 2),
                            'cst_cofins' => '01',
                            'aliquota_cofins' => 7.6,
                            'valor_cofins' => round($vi * 0.076, 2),
                            'created_at' => $agora,
                            'updated_at' => $agora,
                        ];
                    }
                }
            }
        }

        foreach (array_chunk($linhas, 500) as $chunk) {
            DB::table('efd_notas')->insert($chunk);
        }
        foreach (array_chunk($itens, 500) as $chunk) {
            DB::table('efd_notas_itens')->insert($chunk);
        }
        foreach (array_chunk($consolidados, 500) as $chunk) {
            DB::table('efd_notas_consolidados')->insert($chunk);
        }
        DB::statement("select setval(pg_get_serial_sequence('efd_notas','id'), (select max(id) from efd_notas))");

        $this->criarApuracoes($importacoes, $porMes);

        // resumo_final da importação principal (chaves descritivas canônicas)
        DB::table('efd_importacoes')->where('id', $importacoes[0]['id'])->update([
            'total_notas' => 970,
            'notas_extraidas' => 970,
            'resumo_final' => json_encode([
                'participantes' => ['novos' => 18, 'atualizados' => 0],
                'catalogo' => ['itens' => 24],
                'notas_mercadorias' => ['notas' => 890, 'itens' => 1780],
                'notas_transportes' => ['notas' => 80, 'itens' => 80],
                'apuracao_pis_cofins' => ['registros' => 12],
                'retencoes_fonte' => ['registros' => 4],
            ]),
        ]);
    }

    /**
     * Apurações E110 (ICMS) e M200/M600 (PIS/COFINS) por competência, derivadas dos
     * totais reais de notas do mês — alimentam o Resumo/Fechamento Fiscal, que sem
     * elas mostra "Sem dados para este período" em todas as competências.
     */
    private function criarApuracoes(array $importacoes, array $porMes): void
    {
        $agora = Carbon::now();
        $boreal = $this->clientes['Metalúrgica Boreal S.A.'];

        foreach ($porMes as $mes => $tot) {
            $inicio = Carbon::parse($mes.'-01')->startOfMonth();
            $fim = $inicio->copy()->endOfMonth();
            $data = $inicio->format('Y-m-d');

            $porTipo = fn (string $tipo) => collect($importacoes)
                ->first(fn ($imp) => $imp['tipo'] === $tipo && $data >= $imp['ini'] && $data <= $imp['fim'])
                ?? collect($importacoes)->first(fn ($imp) => $imp['tipo'] === $tipo);

            // Débitos/créditos = EXATAMENTE o que os consolidados C190 do mês somam —
            // o espelho "Está batendo?" (CruzamentoApuracaoService) compara E110 × C190
            // e precisa fechar 100% verde pra Boreal (cliente exemplar do vídeo).
            $debitos = round($tot['icms_deb'], 2);
            $creditos = round($tot['icms_cred'], 2);
            $saldo = round($debitos - $creditos, 2);
            $venc = $inicio->copy()->addMonthNoOverflow()->day(20)->format('dmY');
            DB::table('efd_apuracoes_icms')->insert([
                'importacao_id' => $porTipo('EFD ICMS/IPI')['id'],
                'user_id' => $this->userId,
                'cliente_id' => $boreal,
                'periodo_inicio' => $inicio->format('Y-m-d'),
                'periodo_fim' => $fim->format('Y-m-d'),
                'icms_tot_debitos' => $debitos,
                'icms_aj_debitos' => 0,
                'icms_tot_aj_debitos' => 0,
                'icms_estornos_credito' => 0,
                'icms_tot_creditos' => $creditos,
                'icms_aj_creditos' => 0,
                'icms_tot_aj_creditos' => 0,
                'icms_estornos_debito' => 0,
                'icms_sld_credor_ant' => 0,
                'icms_sld_apurado' => max(0, $saldo),
                'icms_tot_deducoes' => 0,
                'icms_a_recolher' => max(0, $saldo),
                'icms_sld_credor_transportar' => max(0, -$saldo),
                'icms_deb_especiais' => 0,
                'st_uf' => 'SP',
                'st_ind_movimentacao' => '0',
                'st_sld_credor_ant' => 0,
                'st_devolucoes' => 0,
                'st_ressarcimentos' => 0,
                'st_outros_creditos' => 0,
                'st_aj_creditos' => 0,
                'st_retencao' => 0,
                'st_outros_debitos' => 0,
                'st_aj_debitos' => 0,
                'st_sld_devedor_ant' => 0,
                'st_deducoes' => 0,
                'st_icms_recolher' => 0,
                'st_sld_credor_transportar' => 0,
                'st_deb_especiais' => 0,
                // Guia E116 (código 310 = ICMS apuração): sem ela a aba "A recolher"
                // não lista ICMS e o card "ICMS a recolher" fica R$ 0,00.
                'icms_obrigacoes' => $saldo > 0 ? json_encode(['items' => [[
                    'ICMS_COD_RECEITA' => '310',
                    'ICMS_VALOR_OBRIGACAO' => max(0, $saldo),
                    'ICMS_DATA_VENCIMENTO' => $venc,
                ]]]) : null,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);

            // Devido bruto = débito dos itens nas notas (espelho M200/M600 × itens);
            // crédito descontado parcial (45%) mantém "a recolher" positivo todo mês.
            $pisDevida = round($tot['pis_deb'], 2);
            $pisCred = round($pisDevida * 0.45, 2);
            $pisRec = max(0, round($pisDevida - $pisCred, 2));
            $cofDevida = round($tot['cofins_deb'], 2);
            $cofCred = round($cofDevida * 0.45, 2);
            $cofRec = max(0, round($cofDevida - $cofCred, 2));
            // Sem periodo_inicio/fim: a tabela de contribuições não tem essas colunas —
            // o scopePeriodo() resolve a competência pela importação das notas do mês.
            DB::table('efd_apuracoes_contribuicoes')->insert([
                'importacao_id' => $porTipo('EFD PIS/COFINS')['id'],
                'user_id' => $this->userId,
                'cliente_id' => $boreal,
                'pis_nao_cumulativo' => $pisDevida,
                'pis_credito_descontado' => $pisCred,
                'pis_credito_desc_ant' => 0,
                'pis_nc_devida' => $pisDevida,
                'pis_retencao_nc' => 0,
                'pis_outras_deducoes_nc' => 0,
                'pis_nc_recolher' => $pisRec,
                'pis_cumulativo' => 0,
                'pis_retencao_cum' => 0,
                'pis_outras_deducoes_cum' => 0,
                'pis_cum_recolher' => 0,
                'pis_total_recolher' => $pisRec,
                'cofins_nao_cumulativo' => $cofDevida,
                'cofins_credito_descontado' => $cofCred,
                'cofins_credito_desc_ant' => 0,
                'cofins_nc_devida' => $cofDevida,
                'cofins_retencao_nc' => 0,
                'cofins_outras_deducoes_nc' => 0,
                'cofins_nc_recolher' => $cofRec,
                'cofins_cumulativo' => 0,
                'cofins_retencao_cum' => 0,
                'cofins_outras_deducoes_cum' => 0,
                'cofins_cum_recolher' => 0,
                'cofins_total_recolher' => $cofRec,
                'cod_inc_tributaria' => '1',
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        }
    }

    private function criarXmlEClearance(): void
    {
        $agora = Carbon::now();
        $boreal = $this->clientes['Metalúrgica Boreal S.A.'];
        $cnpjBoreal = '98765432000110';

        $xmlImportId = DB::table('xml_importacoes')->insertGetId([
            'user_id' => $this->userId,
            'cliente_id' => $boreal,
            'tipo_documento' => 'nfe',
            'modo_envio' => 'upload',
            'filename' => 'nfe_saidas_junho_2026.zip',
            'total_arquivos' => 1,
            'total_xmls' => 220,
            'xmls_processados' => 220,
            'xmls_novos' => 220,
            'status' => 'concluido',
            'valor_total' => 1834520.40,
            'iniciado_em' => $agora->copy()->subDays(18),
            'concluido_em' => $agora->copy()->subDays(18)->addMinutes(2),
            'created_at' => $agora->copy()->subDays(18),
            'updated_at' => $agora->copy()->subDays(18),
        ]);

        $xmlLinhas = [];
        $consultas = [];
        for ($n = 1; $n <= 220; $n++) {
            $p = $this->participantes[$n % count($this->participantes)];
            $valor = round(900 + (($n * 631) % 38000) + rand(0, 99) / 100, 2);
            $data = Carbon::parse('2026-06-01')->addDays($n % 28);
            $chave = $this->chave($cnpjBoreal, '55', 30000 + $n, '2606');
            $cancelada = in_array($n, [37, 91, 164], true);

            $xmlLinhas[] = [
                'user_id' => $this->userId,
                'importacao_xml_id' => $xmlImportId,
                'cliente_id' => $boreal,
                'chave_acesso' => $chave,
                'tipo_documento' => 'nfe',
                'modelo' => '55',
                'origem' => 'importacao_xml',
                'numero_documento' => (string) (30000 + $n),
                'serie' => '1',
                'data_emissao' => $data,
                'natureza_operacao' => 'VENDA DE PRODUCAO DO ESTABELECIMENTO',
                'valor_total' => $valor,
                'tipo_nota' => 1,
                'emit_documento' => $cnpjBoreal,
                'emit_razao_social' => 'Metalúrgica Boreal S.A.',
                'emit_uf' => 'SP',
                'emit_cliente_id' => $boreal,
                'dest_documento' => $p['doc'],
                'dest_razao_social' => $p['nome'],
                'dest_uf' => $p['uf'],
                'dest_participante_id' => $p['id'],
                'icms_valor' => round($valor * 0.12, 2),
                'pis_valor' => round($valor * 0.0165, 2),
                'cofins_valor' => round($valor * 0.076, 2),
                'tributos_total' => round($valor * 0.2125, 2),
                'status_autorizacao' => $cancelada ? '101' : '100',
                'protocolo_autorizacao' => '1352600'.str_pad((string) $n, 8, '0', STR_PAD_LEFT),
                'data_autorizacao' => $data->copy()->addMinutes(2),
                'created_at' => $agora->copy()->subDays(18),
                'updated_at' => $agora->copy()->subDays(18),
            ];

            // Snapshot SEFAZ pra ~1 em cada 9 notas (3 canceladas incluídas)
            if ($n % 9 === 1 || $cancelada) {
                $consultas[] = [
                    'user_id' => $this->userId,
                    'cliente_id' => $boreal,
                    'chave_acesso' => $chave,
                    'tipo_documento' => 'nfe',
                    'modelo' => '55',
                    'numero' => (string) (30000 + $n),
                    'serie' => '1',
                    'data_emissao' => $data,
                    'status' => $cancelada ? 'CANCELADA' : 'AUTORIZADA',
                    'natureza_operacao' => 'VENDA DE PRODUCAO DO ESTABELECIMENTO',
                    'tipo_operacao' => 'saida',
                    'valor_total' => $valor,
                    'emit_cnpj' => $cnpjBoreal,
                    'emit_nome' => 'Metalúrgica Boreal S.A.',
                    'emit_uf' => 'SP',
                    'dest_cnpj' => $p['doc'],
                    'dest_nome' => $p['nome'],
                    'dest_uf' => $p['uf'],
                    'nfe_completa' => false,
                    'consulta_sem_certificado' => true,
                    'custo' => 1.00,
                    'infosimples_code' => 200,
                    'consultado_em' => $agora->copy()->subDays(rand(2, 15)),
                    'created_at' => $agora->copy()->subDays(15),
                    'updated_at' => $agora->copy()->subDays(2),
                ];
            }
        }

        foreach (array_chunk($xmlLinhas, 200) as $chunk) {
            DB::table('xml_notas')->insert($chunk);
        }
        DB::table('nfe_consultas')->insert($consultas);
    }

    private function criarConsultasEScores(): void
    {
        $agora = Carbon::now();
        $boreal = $this->clientes['Metalúrgica Boreal S.A.'];
        $planoCompliance = DB::table('monitoramento_planos')->where('codigo', 'compliance')->value('id') ?? 4;

        $loteId = DB::table('consulta_lotes')->insertGetId([
            'user_id' => $this->userId,
            'cliente_id' => $boreal,
            'plano_id' => $planoCompliance,
            'status' => 'concluido',
            'total_participantes' => count($this->participantes),
            'creditos_cobrados' => count($this->participantes) * 5,
            'tab_id' => 'demo-video-'.uniqid(),
            'processado_em' => $agora->copy()->subDays(3),
            'created_at' => $agora->copy()->subDays(3),
            'updated_at' => $agora->copy()->subDays(3),
        ]);

        $risk = app(RiskScoreService::class);

        foreach ($this->participantes as $p) {
            DB::table('consulta_lote_participantes')->insert([
                'consulta_lote_id' => $loteId,
                'participante_id' => $p['id'],
                'created_at' => $agora->copy()->subDays(3),
            ]);

            // Certidões coerentes com o RiskScoreService: estadual Positiva a partir de
            // 'medio' (7 fornecedores irregulares no Cruzamentos), federal Positiva a
            // partir de 'alto'. Classificação/score saem do PRÓPRIO produto (abaixo) —
            // tela, dossiê PDF e alertas leem a mesma conta.
            $estadualPositiva = $p['risco'] !== 'baixo';
            $federalPositiva = in_array($p['risco'], ['alto', 'critico'], true);
            $venceEmSeteDias = $p['nome'] === 'Serviços Orion Ltda';
            $dados = [
                'razao_social' => $p['nome'],
                'situacao_cadastral' => $p['situacao'],
                'regime_tributario' => 'Lucro Presumido',
                'matriz_filial' => 'matriz',
                'porte' => 'DEMAIS',
                'endereco' => ['uf' => $p['uf'], 'municipio' => 'São Paulo'],
                'cnd_federal' => [
                    'status' => $federalPositiva ? 'Positiva' : 'Negativa',
                    'situacao' => 'Válida',
                    'emissao_data' => $agora->copy()->subDays(20)->format('d/m/Y'),
                    'data_validade' => $agora->copy()->addDays(160)->format('d/m/Y'),
                    'conseguiu_emitir' => true,
                ],
                'cnd_estadual' => [
                    'status' => $estadualPositiva ? 'Positiva' : 'Negativa',
                    'situacao' => 'Válida',
                    'data_validade' => $agora->copy()->addDays($venceEmSeteDias ? 7 : 120)->format('d/m/Y'),
                    'conseguiu_emitir' => true,
                ],
                'cnd_municipal' => ['status' => 'Negativa', 'situacao' => 'Válida', 'conseguiu_emitir' => true],
                // Chave canônica é crf_fgts ('fgts' o presenter trata como fonte que falhou
                // e o badge vira "erro com o site do provedor" no dossiê).
                'crf_fgts' => ['status' => 'Regular', 'situacao' => 'Regular', 'data_validade' => $agora->copy()->addDays(25)->format('d/m/Y'), 'conseguiu_emitir' => true],
                'sintegra' => ['situacao' => $p['risco'] === 'critico' ? 'Não habilitado' : 'Ativo'],
            ];

            DB::table('consulta_resultados')->insert([
                'consulta_lote_id' => $loteId,
                'participante_id' => $p['id'],
                'cliente_id' => null,
                'resultado_dados' => json_encode($dados),
                'status' => 'sucesso',
                'consultado_em' => $agora->copy()->subDays(3),
                'created_at' => $agora->copy()->subDays(3),
                'updated_at' => $agora->copy()->subDays(3),
            ]);

            $scores = $risk->calcularScores($dados);
            DB::table('participante_scores')->insert([
                'participante_id' => $p['id'],
                'cliente_id' => null,
                'user_id' => $this->userId,
                'score_cadastral' => $scores['cadastral'] ?? 0,
                'score_cnd_federal' => $scores['cnd_federal'] ?? 0,
                'score_cnd_estadual' => $scores['cnd_estadual'] ?? 0,
                'score_fgts' => $scores['fgts'] ?? 0,
                'score_total' => $risk->calcularScoreTotal($scores) ?? 0,
                'classificacao' => $risk->classificarComCobertura($scores),
                'ultima_consulta_em' => $agora->copy()->subDays(3),
                'proxima_consulta_em' => $agora->copy()->addDays(27),
                'dados_consultados' => json_encode(['fontes' => ['cadastro', 'cnd_federal', 'cnd_estadual', 'cnd_municipal', 'fgts', 'sintegra']]),
                'created_at' => $agora->copy()->subDays(3),
                'updated_at' => $agora->copy()->subDays(3),
            ]);
        }
    }

    private function criarMonitoramento(): void
    {
        $agora = Carbon::now();
        $boreal = $this->clientes['Metalúrgica Boreal S.A.'];
        $planoCompliance = DB::table('monitoramento_planos')->where('codigo', 'compliance')->value('id') ?? 4;

        foreach (array_slice($this->participantes, 0, 8) as $p) {
            DB::table('monitoramento_assinaturas')->insert([
                'user_id' => $this->userId,
                'participante_id' => $p['id'],
                'cliente_id' => null,
                'plano_id' => $planoCompliance,
                'status' => 'ativo',
                'frequencia_dias' => 30,
                'ultima_execucao_em' => $agora->copy()->subDays(3),
                'proxima_execucao_em' => $agora->copy()->addDays(27),
                'created_at' => $agora->copy()->subMonths(2),
                'updated_at' => $agora,
            ]);
        }
    }
}
