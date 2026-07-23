<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Contracts\FonteDuasEtapas;
use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

/**
 * Certidão Cível do TJMS (1º grau) — fluxo de 2 ETAPAS (docs/advocacia/consultas-certidoes.md fase 4).
 *   Etapa 1 `tribunal/tjms/pedido-cert`: cadastra o pedido → devolve numero_pedido + data_pedido.
 *   Etapa 2 `tribunal/tjms/obter-certidao`: confere/emite (até ~3 dias úteis) → PDF + veredito.
 * O motor detecta `FonteDuasEtapas`, cria um `CertidaoPedido` (máquina de estados) e o
 * `VerificarCertidaoPedidoJob` polla a etapa 2 até concluir.
 *
 * TJMS é MS-only. `comarca`/`modelo` exigem GRAFIA EXATA do site de origem (doc alerta: grafia
 * errada falha silenciosamente) — vêm de config/advocacia.php e são validadas por smoke pago.
 */
class TjmsPedidoFonte extends FonteCertidaoInfoSimples implements FonteDuasEtapas
{
    public function chave(): string
    {
        return 'certidao_tjms';
    }

    public function slug(): string
    {
        return 'tribunal/tjms/pedido-cert';
    }

    public function slugObter(array $alvo): string
    {
        return 'tribunal/tjms/obter-certidao';
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.certidao_tjms', 1.00);
    }

    public function aplicavelPara(array $alvo): bool
    {
        // MS-only (comarcas do TJMS) + razão social + comarca resolvível + e-mail de sistema.
        return strtoupper(trim((string) ($alvo['uf'] ?? ''))) === 'MS'
            && trim((string) ($alvo['razao_social'] ?? '')) !== ''
            && $this->comarca($alvo) !== ''
            && filled(config('advocacia.email_solicitante'));
    }

    public function motivoIndisponivel(array $alvo): string
    {
        if (strtoupper(trim((string) ($alvo['uf'] ?? ''))) !== 'MS') {
            return 'Certidão do TJMS só se aplica a empresas de Mato Grosso do Sul.';
        }
        if (trim((string) ($alvo['razao_social'] ?? '')) === '') {
            return 'TJMS exige a razão social do consultado — indisponível no cadastro deste CNPJ.';
        }

        return 'TJMS exige a comarca da sede — não foi possível resolver a comarca deste município.';
    }

    public function params(array $alvo): array
    {
        // Etapa 1 (PJ): cnpj + nome_razao_social + comarca + modelo + email. `email` é a caixa de
        // SISTEMA (o TJMS envia a certidão por e-mail; repassamos ao usuário — MVP).
        return parent::params($alvo) + [
            'nome_razao_social' => trim((string) ($alvo['razao_social'] ?? '')),
            'comarca' => $this->comarca($alvo),
            // SEM default inline: o único valor válido no eSAJ é 'WEB - Ação Cível' (config); um
            // fallback com grafia diferente falharia silenciosamente no site (607 billable).
            'modelo' => (string) config('advocacia.tjms.modelo'),
            'email' => (string) config('advocacia.email_solicitante'),
        ];
    }

    /** Etapa 1 bem-sucedida: pedido SOLICITADO (ainda não emitido). Bloco pendente âmbar. */
    protected function mapearSucesso(array $data): array
    {
        return [
            'status' => \App\Support\CertidaoBadge::STATUS_EM_ANDAMENTO,
            'certidao_codigo' => null,
            'emissao_data' => null,
            'data_validade' => null,
            'mensagem' => 'Certidão solicitada ao TJMS. A emissão pode levar até 3 dias úteis; '
                .'você será avisado quando a certidão estiver disponível.',
        ];
    }

    public function extrairCorrelacao(array $data): array
    {
        $numero = trim((string) ($data['numero_pedido'] ?? ''));
        $dataPedido = trim((string) ($data['data_pedido'] ?? ''));
        if ($numero === '' || $dataPedido === '') {
            return [];
        }

        return ['numero_pedido' => $numero, 'data_pedido' => $dataPedido];
    }

    public function paramsObter(array $alvo, array $correlacao): array
    {
        return [
            'cnpj' => preg_replace('/[^0-9]/', '', (string) ($alvo['cnpj'] ?? '')),
            'numero_pedido' => (string) ($correlacao['numero_pedido'] ?? ''),
            'data_pedido' => $this->dataIso((string) ($correlacao['data_pedido'] ?? '')),
        ];
    }

    /**
     * `data_pedido` na etapa 2 é ISO 8601 (`2026-07-22`) — a etapa 1 devolve BR (`22/07/2026`).
     * Mandar no formato BR dá **607 com errors[] vazio** (sem pista); validado no smoke do pedido
     * 10559945. Parse pelo DataBr (Carbon::parse leria d/m/Y como m/d/Y).
     */
    private function dataIso(string $data): string
    {
        $data = trim($data);
        if ($data === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return $data; // já ISO (ou vazio)
        }

        return \App\Support\DataBr::parse($data)?->format('Y-m-d') ?? $data;
    }

    public function mapearObter(array $data): array
    {
        // Enquanto o tribunal não emite, `conseguiu_emitir_pdf` é falso → repetir depois.
        if (($data['conseguiu_emitir_pdf'] ?? null) !== true) {
            return ['pronta' => false, 'bloco' => []];
        }

        // `nada_consta` true → Negativa (regular); false → Positiva (constam feitos).
        $nadaConsta = (bool) ($data['nada_consta'] ?? false);

        return [
            'pronta' => true,
            'bloco' => [
                'status' => $nadaConsta ? 'Negativa' : 'Positiva',
                'nada_consta' => $nadaConsta,
                'certidao_codigo' => $data['numero_certidao'] ?? ($data['numero_pedido'] ?? null),
                'emissao_data' => $data['emissao_data'] ?? null,
                'data_validade' => null, // TJMS não devolve validade
                'comprovante' => $data['site_receipt'] ?? null,
                'mensagem' => $data['titulo'] ?? ($data['mensagem'] ?? null),
            ],
        ];
    }

    /**
     * 1ª conferência 1h após o pedido: o TJMS costuma emitir em minutos/horas (pedido 10559945 saiu
     * no mesmo dia), então a maioria resolve na PRIMEIRA chamada — 1 `obter` pago, margem sadia.
     */
    public function prazoInicialMinutos(): int
    {
        return (int) config('advocacia.tjms.prazo_inicial_min', 60);
    }

    /**
     * Backoff ESCALONADO (1h → 4h → 12h → 24h): cada conferência é PAGA (~R$ 0,24). Intervalo fixo
     * curto multiplicava o custo e virava margem negativa. A escala cobre o SLA de 3 dias úteis do
     * tribunal com poucas chamadas.
     */
    public function intervaloVerificacaoMinutos(int $tentativa): int
    {
        $escala = (array) config('advocacia.tjms.intervalos_min', [60, 240, 720, 1440]);
        $idx = max(0, min($tentativa - 1, count($escala) - 1));

        return (int) ($escala[$idx] ?? 1440);
    }

    /** Teto de conferências ao tribunal: 5 × ~R$0,24 no pior caso (ver preço da fonte em config). */
    public function maxVerificacoes(): int
    {
        return (int) config('advocacia.tjms.max_verificacoes', 5);
    }

    /**
     * Comarca na GRAFIA EXATA do eSAJ (Title case: "Dourados", "Campo Grande", "Água Clara").
     * A minhareceita devolve o município em CAIXA ALTA → Title-case cobre os nomes sem acento;
     * o override `comarca_por_municipio` cobre exceções (acentos, "de/do/das" minúsculos, e casos
     * em que a comarca ≠ município). Smoke validado: DOURADOS → "Dourados" (lote real 10559945).
     */
    private function comarca(array $alvo): string
    {
        $municipio = trim((string) ($alvo['municipio'] ?? ''));
        if ($municipio === '') {
            return '';
        }

        $mapa = (array) config('advocacia.tjms.comarca_por_municipio', []);
        foreach ($mapa as $mun => $comarca) {
            if (mb_strtoupper($mun) === mb_strtoupper($municipio)) {
                return (string) $comarca;
            }
        }

        return mb_convert_case($municipio, MB_CASE_TITLE, 'UTF-8');
    }
}
