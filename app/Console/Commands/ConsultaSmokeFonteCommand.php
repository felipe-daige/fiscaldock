<?php

namespace App\Console\Commands;

use App\Services\Consultas\FonteRegistry;
use App\Services\Consultas\Providers\InfoSimplesProvider;
use Illuminate\Console\Command;

/**
 * Smoke test de UMA fonte de consulta (advocacia/PF) num único alvo: chama o InfoSimples de
 * verdade (1 consulta paga), monta os params pela própria Fonte e imprime o contrato cru
 * (code, header.price/billable, errors[], chaves de data[0]). NÃO debita saldo, NÃO persiste,
 * NÃO cria lote — bypassa Fonte::pronta() de propósito (o objetivo é validar params ANTES de
 * abrir o gate). Serve pra confirmar a grafia dos params sem re-consultar às cegas.
 *
 *   php artisan consulta:smoke-fonte cadastro_pf --cpf=08403652178 --birthdate=2002-08-07
 *   php artisan consulta:smoke-fonte pgfn_devedores --cnpj=19131243000197
 *
 * SEGURANÇA: configure CONSULTAS_INFOSIMPLES_TESTE_CPFS (ou _CNPJS) com o documento de teste
 * ANTES de rodar — o InfoSimplesProvider bloqueia qualquer documento fora da allowlist sem
 * chamar (sem cobrar). O comando avisa quando a allowlist não cobre o documento.
 */
class ConsultaSmokeFonteCommand extends Command
{
    protected $signature = 'consulta:smoke-fonte {fonte : chave da fonte (ex.: cadastro_pf)}'
        .' {--cpf= : CPF do alvo (11 dígitos)}'
        .' {--cnpj= : CNPJ do alvo (14 dígitos)}'
        .' {--birthdate= : data de nascimento ISO AAAA-MM-DD (fontes PF que exigem)}'
        .' {--nome= : nome/razão social do alvo}'
        .' {--nome-mae= : nome da mãe (antecedentes)}'
        .' {--nome-pai= : nome do pai (antecedentes)}'
        .' {--uf= : UF (nascimento/sede, conforme a fonte)}'
        .' {--ano= : ano-base (ibama_autuacoes)}'
        .' {--municipio= : município do alvo}'
        .' {--force : pula a confirmação interativa (execução não-interativa)}';

    protected $description = 'Consulta UMA fonte no InfoSimples e imprime o contrato cru — sem cobrar saldo nem persistir';

    public function handle(FonteRegistry $registry, InfoSimplesProvider $provider): int
    {
        $chave = (string) $this->argument('fonte');
        $fonte = $registry->get($chave);

        if ($fonte === null) {
            $this->error("Fonte '{$chave}' não registrada.");

            return self::FAILURE;
        }

        $cpf = preg_replace('/\D/', '', (string) $this->option('cpf'));
        $cnpj = preg_replace('/\D/', '', (string) $this->option('cnpj'));
        $tipoPessoa = $cpf !== '' ? 'PF' : 'PJ';

        if ($cpf === '' && $cnpj === '') {
            $this->error('Informe --cpf ou --cnpj.');

            return self::FAILURE;
        }

        // Monta o alvo no mesmo shape que o ProcessarConsultaJob injeta na Fonte.
        $alvo = array_filter([
            'tipo_pessoa' => $tipoPessoa,
            'documento' => $cpf !== '' ? $cpf : $cnpj,
            'cpf' => $cpf ?: null,
            'cnpj' => $cnpj ?: null,
            'birthdate' => $this->option('birthdate'),
            'nome' => $this->option('nome'),
            'razao_social' => $this->option('nome'),
            'nome_mae' => $this->option('nome-mae'),
            'nome_pai' => $this->option('nome-pai'),
            'uf' => $this->option('uf'),
            'municipio' => $this->option('municipio'),
            'ano' => $this->option('ano'),
        ], fn ($v) => $v !== null && $v !== '');

        if (! $fonte->aplicavelPara($alvo)) {
            $this->warn('Fonte não aplicável a este alvo (não cobraria em produção): '.$fonte->motivoIndisponivel($alvo));
            $this->line('Faltam campos de --option? Requisitos PF em config/advocacia.php (requisitos_pf).');

            return self::FAILURE;
        }

        $slug = $fonte->slugPara($alvo);
        $params = $fonte->params($alvo);

        // Espelha o guard do provider: avisa se a allowlist de teste NÃO cobre o documento
        // (nesse caso a chamada seria bloqueada sem cobrar — o smoke não testaria nada).
        $docParam = $cpf !== '' ? $cpf : $cnpj;
        $allowlist = (array) config($cpf !== '' ? 'consultas.infosimples_teste_cpfs' : 'consultas.infosimples_teste_cnpjs', []);
        $coberto = $allowlist === [] || in_array($docParam, $allowlist, true);

        $this->warn('⚠️  Isto faz 1 consulta PAGA ao InfoSimples (~R$0,20–0,26). Não cobra saldo nem persiste.');
        $this->line("Fonte: {$chave} | slug: {$slug} | tipo: {$tipoPessoa}");
        $this->line('Params enviados: '.json_encode($this->mascarar($params), JSON_UNESCAPED_UNICODE));
        $this->line('Allowlist de teste cobre o documento? '.($coberto ? 'SIM' : 'NÃO — provider vai BLOQUEAR sem chamar'));

        if ($allowlist === []) {
            $this->warn('Allowlist VAZIA: qualquer documento passa. Configure CONSULTAS_INFOSIMPLES_TESTE_'
                .($cpf !== '' ? 'CPFS' : 'CNPJS').' para proteger o saldo.');
        }

        if (! $coberto) {
            $this->error('Documento fora da allowlist de teste — a chamada seria bloqueada sem testar nada. Abortado.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Prosseguir com a chamada paga?', false)) {
            $this->line('Abortado.');

            return self::SUCCESS;
        }

        $resp = $provider->consultar($slug, $params);

        $this->newLine();
        $this->info('── Contrato cru ──');
        $this->line('status classificado: '.$resp->status);
        $this->line('code: '.$resp->httpCode);

        $raw = $resp->raw;
        $this->line('header.billable: '.json_encode($raw['header']['billable'] ?? null));
        $this->line('header.price: '.json_encode($raw['header']['price'] ?? null));
        $this->line('code_message: '.json_encode($raw['code_message'] ?? null, JSON_UNESCAPED_UNICODE));
        $this->line('errors: '.json_encode($raw['errors'] ?? null, JSON_UNESCAPED_UNICODE));

        $data0 = $raw['data'][0] ?? null;
        if (is_array($data0)) {
            $this->line('data[0] chaves: '.implode(', ', array_keys($data0)));
            $this->line('site_receipt: '.json_encode($data0['site_receipt'] ?? ($raw['site_receipts'][0] ?? null)));
        } else {
            $this->line('data[0]: (vazio)');
        }

        $this->newLine();
        $this->info('── Normalizado pela Fonte ──');
        $this->line(json_encode($fonte->normalizar($raw, $resp->status), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }

    /** Mascara o CPF nos params ecoados no terminal (dado pessoal). */
    private function mascarar(array $params): array
    {
        if (isset($params['cpf'])) {
            $cpf = preg_replace('/\D/', '', (string) $params['cpf']);
            $params['cpf'] = strlen($cpf) === 11 ? '*******'.substr($cpf, -4) : $params['cpf'];
        }

        return $params;
    }
}
