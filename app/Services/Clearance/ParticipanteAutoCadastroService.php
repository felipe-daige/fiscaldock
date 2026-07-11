<?php

namespace App\Services\Clearance;

use App\Models\Cliente;
use App\Models\Participante;
use App\Services\Clearance\Sefaz\DocumentoSnapshot;
use Illuminate\Support\Facades\Log;

/**
 * Cadastra como Participante os CNPJs de contraparte que aparecem no snapshot SEFAZ
 * (emitente/destinatário/tomador/remetente) e ainda não existem pro usuário.
 *
 * Create-only: nunca atualiza participante existente (regra do projeto — atualização de
 * participante é responsabilidade de outros fluxos). Sem consulta externa: o usuário decide
 * depois se consulta o CNPJ pra enriquecer dados e certidões.
 */
class ParticipanteAutoCadastroService
{
    public function __construct(private readonly CnpjMascaradoResolver $mascaradoResolver) {}

    public function criarDesdeSnapshot(DocumentoSnapshot $snapshot, int $userId, ?int $clienteId): void
    {
        $this->criarDesdeColunas(
            $snapshot->colunas,
            $snapshot->tipoDocumento,
            $snapshot->chaveAcesso,
            $userId,
            $clienteId
        );
    }

    /**
     * Mesma lógica a partir das colunas cruas (linha de nfe_consultas/cte_consultas já persistida).
     */
    public function criarDesdeColunas(array $c, string $tipoDocumento, ?string $chaveAcesso, int $userId, ?int $clienteId): void
    {
        $tipo = strtoupper($tipoDocumento) === 'CTE' ? 'CTE' : 'NFE';

        $candidatos = [
            ['documento' => $c['emit_cnpj'] ?? null, 'nome' => $c['emit_nome'] ?? null, 'uf' => $c['emit_uf'] ?? null, 'municipio' => $c['emit_municipio'] ?? null, 'ie' => $c['emit_ie'] ?? null],
            ['documento' => $c['dest_cnpj'] ?? null, 'nome' => $c['dest_nome'] ?? null, 'uf' => $c['dest_uf'] ?? null, 'municipio' => $c['dest_municipio'] ?? null, 'ie' => null],
        ];

        if ($tipo === 'CTE') {
            $candidatos[] = ['documento' => $c['tomador_cnpj'] ?? null, 'nome' => $c['tomador_nome'] ?? null, 'uf' => $c['tomador_uf'] ?? null, 'municipio' => $c['tomador_municipio'] ?? null, 'ie' => null];
            $candidatos[] = ['documento' => $c['remet_cnpj'] ?? null, 'nome' => $c['remet_nome'] ?? null, 'uf' => $c['remet_uf'] ?? null, 'municipio' => null, 'ie' => null];
        }

        // CNPJ que já é cliente do usuário não vira participante (é a própria empresa dele).
        $documentosClientes = Cliente::where('user_id', $userId)
            ->pluck('documento')
            ->map(fn ($d) => preg_replace('/\D/', '', (string) $d))
            ->filter()
            ->flip();

        foreach ($candidatos as $candidato) {
            $documento = preg_replace('/\D/', '', (string) $candidato['documento']);

            // Só CNPJ — CPF não entra no produto de consulta de contraparte.
            if (strlen($documento) !== 14 || isset($documentosClientes[$documento])) {
                continue;
            }

            // Consulta sem certificado mascara a contraparte (5 primeiros dígitos zerados,
            // nome 'RAIZ***'): não dá pra cadastrar CNPJ incompleto. Se o sufixo casar com
            // participante existente, ele já está no sistema; senão, fica sem cadastro —
            // o usuário identifica depois (nunca criamos participante com documento lixo).
            if ($this->mascaradoResolver->estaMascarado($documento)) {
                continue;
            }

            try {
                Participante::firstOrCreate(
                    ['user_id' => $userId, 'documento' => $documento],
                    [
                        'cliente_id' => $clienteId,
                        'tipo_documento' => 'PJ',
                        'razao_social' => $candidato['nome'] ?: null,
                        'uf' => $candidato['uf'] ?: null,
                        'municipio' => $candidato['municipio'] ?: null,
                        'inscricao_estadual' => $candidato['ie'] ?: null,
                        'origem_tipo' => $tipo,
                        'origem_ref' => [
                            'fonte' => 'clearance_snapshot',
                            'chave_acesso' => $chaveAcesso,
                            'criado_em' => now()->toIso8601String(),
                        ],
                    ]
                );
            } catch (\Throwable $e) {
                // Cadastro é acessório: nunca derruba o job de consulta.
                Log::warning('Auto-cadastro de participante via clearance falhou', [
                    'user_id' => $userId,
                    'documento' => $documento,
                    'chave_acesso' => $chaveAcesso,
                    'erro' => $e->getMessage(),
                ]);
            }
        }
    }
}
