<?php

namespace App\Services\Perfis;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PerfilCnpjViewData
{
    /** @return array<string, mixed> */
    public function cadastro(Model $entidade, array $dados, ?Carbon $consultadoEm = null): array
    {
        $documento = preg_replace('/\D/', '', (string) $entidade->documento);
        $isCpf = strlen($documento) === 11;
        $documentoFormatado = $entidade->documento_formatado
            ?? $entidade->cnpj_formatado
            ?? $documento;

        $situacao = $this->texto($dados['situacao_cadastral'] ?? $entidade->situacao_cadastral);
        $regime = $this->texto($dados['regime_tributario'] ?? $entidade->regime_tributario);
        $razao = $this->texto($dados['razao_social'] ?? $entidade->razao_social ?? $entidade->nome);
        $fantasia = $this->texto($dados['nome_fantasia'] ?? $entidade->nome_fantasia ?? $entidade->nome);

        $basicos = [
            ['label' => $isCpf ? 'Nome' : 'Razão Social', 'valor' => $razao, 'destaque' => true],
            ['label' => $isCpf ? 'CPF' : 'CNPJ', 'valor' => $documentoFormatado, 'mono' => true],
        ];

        if (! $isCpf) {
            $basicos[] = ['label' => 'Nome Fantasia', 'valor' => $fantasia];
            $basicos[] = [
                'label' => 'Situação Cadastral',
                'badge' => [
                    'label' => $situacao ?: 'Não consultada',
                    'hex' => \App\Support\Reports\ReportTheme::statusHex($situacao ?: 'Não consultada'),
                ],
            ];
            $basicos[] = [
                'label' => 'Regime Tributário',
                'badge' => [
                    'label' => $regime ?: 'Não consultado',
                    'hex' => \App\Support\Reports\ReportTheme::regimeHex($regime ?: 'Não consultado'),
                ],
            ];
        }

        $basicos[] = [
            'label' => 'Município / UF',
            'valor' => implode(' / ', array_filter([
                $dados['endereco']['municipio'] ?? $entidade->municipio,
                $dados['endereco']['uf'] ?? $entidade->uf,
            ])) ?: 'Não informado',
        ];

        $capital = $dados['capital_social'] ?? $entidade->capital_social;
        $registro = [
            ['label' => 'Natureza Jurídica', 'valor' => $dados['natureza_juridica'] ?? $entidade->natureza_juridica],
            ['label' => 'Porte', 'valor' => $dados['porte'] ?? $entidade->porte],
            ['label' => 'Regime Tributário', 'valor' => $regime],
            ['label' => 'Capital Social', 'valor' => is_numeric($capital) ? 'R$ '.number_format((float) $capital, 2, ',', '.') : null, 'mono' => true],
            ['label' => 'Matriz / Filial', 'valor' => $dados['matriz_filial'] ?? null],
            ['label' => 'Início de Atividade', 'valor' => $this->data($dados['data_inicio_atividade'] ?? $entidade->data_inicio_atividade)],
            ['label' => 'Inscrição Estadual', 'valor' => $dados['inscricao_estadual'] ?? $entidade->inscricao_estadual, 'mono' => true],
            ['label' => 'Inscrição Municipal', 'valor' => $dados['inscricao_municipal'] ?? $entidade->inscricao_municipal, 'mono' => true],
        ];

        if (array_key_exists('simples_nacional', $dados)) {
            $registro[] = ['label' => 'Simples Nacional', 'valor' => $dados['simples_nacional'] ? 'Optante' : 'Não optante'];
        }
        if (array_key_exists('mei', $dados)) {
            $registro[] = ['label' => 'MEI', 'valor' => $dados['mei'] ? 'Sim' : 'Não'];
        }

        $enderecoConsulta = is_array($dados['endereco'] ?? null) ? $dados['endereco'] : [];
        $logradouro = trim(implode(' ', array_filter([
            $enderecoConsulta['tipo_logradouro'] ?? null,
            $enderecoConsulta['logradouro'] ?? $entidade->endereco,
        ])));
        $numero = $enderecoConsulta['numero'] ?? $entidade->numero;
        $complemento = $enderecoConsulta['complemento'] ?? $entidade->complemento;

        $enderecoCompleto = $logradouro;
        if ($numero) {
            $enderecoCompleto .= ($enderecoCompleto ? ', ' : '').$numero;
        }
        if ($complemento) {
            $enderecoCompleto .= ($enderecoCompleto ? ' — ' : '').$complemento;
        }

        $contato = [
            ['label' => 'Endereço', 'valor' => $enderecoCompleto],
            ['label' => 'Bairro', 'valor' => $enderecoConsulta['bairro'] ?? $entidade->bairro],
            ['label' => 'Município / UF', 'valor' => implode(' / ', array_filter([
                $enderecoConsulta['municipio'] ?? $entidade->municipio,
                $enderecoConsulta['uf'] ?? $entidade->uf,
            ]))],
            ['label' => 'CEP', 'valor' => $this->cep($enderecoConsulta['cep'] ?? $entidade->cep), 'mono' => true],
            ['label' => 'Telefone principal', 'valor' => $dados['telefone_1'] ?? $entidade->telefone, 'mono' => true],
            ['label' => 'Telefone alternativo', 'valor' => $dados['telefone_2'] ?? null, 'mono' => true],
            ['label' => 'E-mail', 'valor' => $dados['email'] ?? $entidade->email],
        ];

        return [
            'is_cpf' => $isCpf,
            'basicos' => $basicos,
            'registro' => $registro,
            'cnaes' => $this->cnaes($dados['cnaes'] ?? null, $entidade),
            'qsa' => $this->qsa($dados['qsa'] ?? null, $entidade),
            'contato' => $contato,
            'consultado_em' => $consultadoEm,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function cnaes(mixed $raw, Model $entidade): array
    {
        $itens = [];

        if (is_array($raw) && isset($raw['principal']) && is_array($raw['principal'])) {
            $principal = $raw['principal'];
            $principal['principal'] = true;
            $itens[] = $principal;
            foreach ((array) ($raw['secundarios'] ?? []) as $secundario) {
                if (is_array($secundario)) {
                    $secundario['principal'] = false;
                    $itens[] = $secundario;
                }
            }
        } elseif (is_array($raw)) {
            foreach ($raw as $item) {
                if (is_array($item)) {
                    $itens[] = $item;
                }
            }
        }

        if ($itens === [] && $entidade->cnae_principal) {
            $itens[] = [
                'codigo' => $entidade->cnae_principal,
                'descricao' => $entidade->cnae_principal_descricao,
                'principal' => true,
            ];
            foreach ((array) $entidade->cnaes_secundarios as $secundario) {
                if (is_array($secundario)) {
                    $secundario['principal'] = false;
                    $itens[] = $secundario;
                }
            }
        }

        return array_values($itens);
    }

    /** @return array<int, array<string, mixed>> */
    private function qsa(mixed $raw, Model $entidade): array
    {
        $qsa = is_array($raw) ? $raw : (is_array($entidade->qsa) ? $entidade->qsa : []);

        return array_values(array_filter($qsa, 'is_array'));
    }

    private function texto(mixed $valor): ?string
    {
        if (! is_scalar($valor)) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto !== '' ? $texto : null;
    }

    private function data(mixed $valor): ?string
    {
        if (! $valor) {
            return null;
        }

        try {
            return Carbon::parse($valor)->format('d/m/Y');
        } catch (\Throwable) {
            return $this->texto($valor);
        }
    }

    private function cep(mixed $valor): ?string
    {
        $cep = preg_replace('/\D/', '', (string) $valor);

        return strlen($cep) === 8
            ? preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep)
            : ($cep ?: null);
    }
}
