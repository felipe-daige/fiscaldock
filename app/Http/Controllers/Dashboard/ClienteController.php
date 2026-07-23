<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Concerns\RespondeAjax;
use App\Http\Controllers\Concerns\SetsDownloadToken;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\Entitlements\EntitlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClienteController extends Controller
{
    use RespondeAjax;
    use SetsDownloadToken;

    public function __construct(private EntitlementService $entitlements = new EntitlementService) {}

    public function todosIds(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Nao autenticado'], 401);
        }

        $status = trim($request->string('status')->toString());
        $tipo = strtoupper(trim($request->string('tipo')->toString()));
        $busca = trim($request->string('busca')->toString());
        $regime = trim($request->string('regime')->toString());
        $situacao = trim($request->string('situacao')->toString());
        $uf = strtoupper(trim($request->string('uf')->toString()));
        $importacao = trim($request->string('importacao')->toString());

        $regValida = ['regular', 'irregular', 'indeterminada', 'nao_consultado'];
        $regularidade = in_array($request->string('regularidade')->toString(), $regValida, true)
            ? $request->string('regularidade')->toString() : null;
        $stValida = ['nunca', 'desatualizada', 'recente'];
        $statusConsulta = in_array($request->string('status_consulta')->toString(), $stValida, true)
            ? $request->string('status_consulta')->toString() : null;

        $resumoService = app(\App\Services\Consultas\ParticipanteFiscalResumoService::class);
        $mapaRegularidade = ($regularidade !== null || $statusConsulta !== null)
            ? $resumoService->mapaRegularidadeCliente((int) $user->id)
            : ['consultados' => [], 'porRegularidade' => [], 'ultimaPorDoc' => []];

        $ids = Cliente::where('user_id', $user->id)
            ->where('is_empresa_propria', false)
            ->when($importacao !== '', fn ($query) => $query->whereIn(
                'id',
                \App\Models\EfdImportacao::where('user_id', $user->id)->where('id', $importacao)->select('cliente_id')
            ))
            ->when($status !== '', function ($query) use ($status) {
                if ($status === 'ativos') {
                    $query->where('ativo', true);
                } elseif ($status === 'inativos') {
                    $query->where('ativo', false);
                }
            })
            ->when($tipo !== '', fn ($query) => $query->where('tipo_pessoa', $tipo))
            ->when($busca !== '', function ($query) use ($busca) {
                $documento = preg_replace('/\D/', '', $busca);

                $query->where(function ($sub) use ($busca, $documento) {
                    $sub->where('razao_social', 'ilike', "%{$busca}%")
                        ->orWhere('nome', 'ilike', "%{$busca}%");

                    if ($documento !== '') {
                        $sub->orWhere('documento', 'like', "%{$documento}%");
                    }
                });
            })
            ->when($regime !== '', fn ($query) => $query->where('regime_tributario', 'ilike', $regime))
            ->when($situacao !== '', fn ($query) => $query->where('situacao_cadastral', 'ilike', $situacao))
            ->when($uf !== '', fn ($query) => $query->where('uf', $uf))
            ->when($regularidade !== null || $statusConsulta !== null, fn ($query) => $resumoService
                ->aplicarFiltroRegularidadeCliente($query, $regularidade, $statusConsulta, $mapaRegularidade))
            ->pluck('id');

        return response()->json([
            'success' => true,
            'ids' => $ids,
            'total' => $ids->count(),
        ]);
    }

    /**
     * Store a newly created cliente in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->merge([
                'documento' => preg_replace('/\D/', '', (string) $request->input('documento')),
            ]);
            $tipoPessoa = $request->input('tipo_pessoa');
            $isPJ = $tipoPessoa === 'PJ';

            $rules = [
                'tipo_pessoa' => 'required|in:PF,PJ',
                'documento' => [
                    'required',
                    'string',
                    'max:18',
                    Rule::unique('clientes', 'documento')
                        ->where(fn ($query) => $query->where('user_id', Auth::id())),
                ],
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'uf' => 'nullable|string|size:2',
                'cep' => 'nullable|string|max:9',
                'municipio' => 'nullable|string|max:255',
                // Campos compartilhados PJ/PF
                'nome_fantasia' => 'nullable|string|max:255',
                'endereco' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:20',
                'complemento' => 'nullable|string|max:100',
                'bairro' => 'nullable|string|max:100',
                'situacao_cadastral' => 'nullable|string|max:50',
                'codigo_municipal' => 'nullable|string|max:10',
                // Campos PJ-only
                'inscricao_estadual' => 'nullable|string|max:20',
                'inscricao_municipal' => 'nullable|string|max:30',
                'crt' => 'nullable|in:1,2,3',
                'regime_tributario' => 'nullable|string|max:50',
                'cnpj_matriz' => 'nullable|string|max:14',
                'suframa' => 'nullable|string|max:20',
                'capital_social' => 'nullable|numeric|min:0',
                'natureza_juridica' => 'nullable|string|max:100',
                'porte' => 'nullable|string|max:50',
                'data_inicio_atividade' => 'nullable|date',
                'cnae_principal' => 'nullable|string|max:10',
                'cnae_principal_descricao' => 'nullable|string|max:255',
                'cnaes_secundarios' => 'nullable|array',
                'qsa' => 'nullable|array',
            ];

            if ($isPJ) {
                $rules['razao_social'] = 'required|string|max:255';
                $rules['nome'] = 'nullable|string|max:255';
            } else {
                $rules['nome'] = 'required|string|max:255';
                $rules['razao_social'] = 'nullable|string|max:255';
            }

            $validated = $request->validate($rules);

            $user = Auth::user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario nao autenticado',
                ], 401);
            }

            // A empresa própria nasce com a conta e não pode ser definida pelo CRUD de clientes.
            if (! $this->entitlements->podeAdicionarCliente($user)) {
                $limite = $this->entitlements->limiteClientes($user);
                $msg = "Seu plano permite cadastrar até {$limite} cliente(s) além da sua empresa. Faça upgrade para adicionar mais.";

                if ($this->isAjaxRequest($request)) {
                    return response()->json(['success' => false, 'message' => $msg], 403);
                }

                return redirect()->route('app.clientes')->with('error', $msg);
            }

            $documentoLimpo = preg_replace('/\D/', '', $validated['documento']);

            if ($isPJ) {
                if (strlen($documentoLimpo) !== 14) {
                    throw ValidationException::withMessages([
                        'documento' => 'CNPJ deve ter 14 digitos',
                    ]);
                }
            } else {
                if (strlen($documentoLimpo) !== 11) {
                    throw ValidationException::withMessages([
                        'documento' => 'CPF deve ter 11 digitos',
                    ]);
                }
            }

            $cliente = Cliente::create([
                'user_id' => $user->id,
                'tipo_pessoa' => $validated['tipo_pessoa'],
                'documento' => $documentoLimpo,
                'nome' => $validated['nome'] ?? null,
                'razao_social' => $validated['razao_social'] ?? null,
                'nome_fantasia' => $validated['nome_fantasia'] ?? null,
                'inscricao_estadual' => $isPJ ? ($validated['inscricao_estadual'] ?? null) : null,
                'inscricao_municipal' => $isPJ ? ($validated['inscricao_municipal'] ?? null) : null,
                'crt' => $isPJ ? ($validated['crt'] ?? null) : null,
                'telefone' => $validated['telefone'] ?? null,
                'email' => $validated['email'] ?? null,
                'uf' => isset($validated['uf']) ? strtoupper($validated['uf']) : null,
                'cep' => isset($validated['cep']) ? preg_replace('/\D/', '', $validated['cep']) : null,
                'municipio' => $validated['municipio'] ?? null,
                'endereco' => $validated['endereco'] ?? null,
                'numero' => $validated['numero'] ?? null,
                'complemento' => $validated['complemento'] ?? null,
                'bairro' => $validated['bairro'] ?? null,
                'situacao_cadastral' => $validated['situacao_cadastral'] ?? null,
                'regime_tributario' => $isPJ ? ($validated['regime_tributario'] ?? null) : null,
                'cnpj_matriz' => $isPJ ? ($validated['cnpj_matriz'] ?? null) : null,
                'suframa' => $isPJ ? ($validated['suframa'] ?? null) : null,
                'codigo_municipal' => $validated['codigo_municipal'] ?? null,
                'capital_social' => $isPJ ? ($validated['capital_social'] ?? null) : null,
                'natureza_juridica' => $isPJ ? ($validated['natureza_juridica'] ?? null) : null,
                'porte' => $isPJ ? ($validated['porte'] ?? null) : null,
                'data_inicio_atividade' => $isPJ ? ($validated['data_inicio_atividade'] ?? null) : null,
                'cnae_principal' => $isPJ ? ($validated['cnae_principal'] ?? null) : null,
                'cnae_principal_descricao' => $isPJ ? ($validated['cnae_principal_descricao'] ?? null) : null,
                'cnaes_secundarios' => $isPJ ? ($validated['cnaes_secundarios'] ?? null) : null,
                'qsa' => $isPJ ? ($validated['qsa'] ?? null) : null,
                'is_empresa_propria' => false,
                'ativo' => true,
            ]);

            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente cadastrado com sucesso!',
                    'redirect' => '/app/clientes',
                    'cliente' => [
                        'id' => $cliente->id,
                        'nome' => $cliente->nome,
                        'documento' => $cliente->documento_formatado,
                    ],
                ], 201);
            }

            return redirect()
                ->route('app.clientes')
                ->with('success', 'Cliente cadastrado com sucesso!');

        } catch (ValidationException $e) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validacao',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao cadastrar cliente: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao cadastrar cliente. Tente novamente.')
                ->withInput();
        }
    }

    /**
     * Show the edit form for an existing cliente.
     */
    public function edit(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user) {
            if ($this->isAjaxRequest($request)) {
                return response()->json(['success' => false, 'message' => 'Nao autenticado', 'redirect' => '/login']);
            }

            return redirect('/login');
        }

        $cliente = Cliente::where('user_id', $user->id)->findOrFail($id);

        $viewName = 'autenticado.clientes.novo';
        $data = [
            'cliente' => $cliente,
            'planosMonitoramento' => \App\Models\MonitoramentoPlano::ativos(),
            'assinaturaMonitoramento' => \App\Models\MonitoramentoAssinatura::where('cliente_id', $cliente->id)
                ->whereIn('status', ['ativo', 'pausado'])
                ->first(),
        ];

        if ($this->isAjaxRequest($request)) {
            $renderedView = view($viewName, $data)->render();

            return response($renderedView)->header('Content-Type', 'text/html');
        }

        return view('autenticado.layouts.app', array_merge([
            'initialView' => $viewName,
        ], $data));
    }

    /**
     * Update an existing cliente.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario nao autenticado',
                ], 401);
            }

            $cliente = Cliente::where('user_id', $user->id)->findOrFail($id);
            $request->merge([
                'documento' => preg_replace('/\D/', '', (string) $request->input('documento')),
            ]);

            $tipoPessoa = $cliente->tipo_pessoa;
            $isPJ = $tipoPessoa === 'PJ';

            $rules = [
                'documento' => [
                    'required',
                    'string',
                    'max:18',
                    Rule::unique('clientes', 'documento')
                        ->where(fn ($query) => $query->where('user_id', $user->id))
                        ->ignore($cliente->id),
                ],
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'uf' => 'nullable|string|size:2',
                'cep' => 'nullable|string|max:9',
                'municipio' => 'nullable|string|max:255',
                // Campos compartilhados PJ/PF
                'nome_fantasia' => 'nullable|string|max:255',
                'endereco' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:20',
                'complemento' => 'nullable|string|max:100',
                'bairro' => 'nullable|string|max:100',
                'situacao_cadastral' => 'nullable|string|max:50',
                'codigo_municipal' => 'nullable|string|max:10',
                // Campos PJ-only
                'inscricao_estadual' => 'nullable|string|max:20',
                'inscricao_municipal' => 'nullable|string|max:30',
                'crt' => 'nullable|in:1,2,3',
                'regime_tributario' => 'nullable|string|max:50',
                'cnpj_matriz' => 'nullable|string|max:14',
                'suframa' => 'nullable|string|max:20',
                'capital_social' => 'nullable|numeric|min:0',
                'natureza_juridica' => 'nullable|string|max:100',
                'porte' => 'nullable|string|max:50',
                'data_inicio_atividade' => 'nullable|date',
                'cnae_principal' => 'nullable|string|max:10',
                'cnae_principal_descricao' => 'nullable|string|max:255',
                'cnaes_secundarios' => 'nullable|array',
                'qsa' => 'nullable|array',
            ];

            if ($isPJ) {
                $rules['razao_social'] = 'required|string|max:255';
                $rules['nome'] = 'nullable|string|max:255';
            } else {
                $rules['nome'] = 'required|string|max:255';
                $rules['razao_social'] = 'nullable|string|max:255';
            }

            $validated = $request->validate($rules);

            $cliente->update([
                'documento' => preg_replace('/\D/', '', $validated['documento']),
                'nome' => $validated['nome'] ?? null,
                'razao_social' => $validated['razao_social'] ?? null,
                'nome_fantasia' => $validated['nome_fantasia'] ?? null,
                'inscricao_estadual' => $isPJ ? ($validated['inscricao_estadual'] ?? null) : null,
                'inscricao_municipal' => $isPJ ? ($validated['inscricao_municipal'] ?? null) : null,
                'crt' => $isPJ ? ($validated['crt'] ?? null) : null,
                'telefone' => $validated['telefone'] ?? null,
                'email' => $validated['email'] ?? null,
                'uf' => isset($validated['uf']) ? strtoupper($validated['uf']) : null,
                'cep' => isset($validated['cep']) ? preg_replace('/\D/', '', $validated['cep']) : null,
                'municipio' => $validated['municipio'] ?? null,
                'endereco' => $validated['endereco'] ?? null,
                'numero' => $validated['numero'] ?? null,
                'complemento' => $validated['complemento'] ?? null,
                'bairro' => $validated['bairro'] ?? null,
                'situacao_cadastral' => $validated['situacao_cadastral'] ?? null,
                'regime_tributario' => $isPJ ? ($validated['regime_tributario'] ?? null) : null,
                'cnpj_matriz' => $isPJ ? ($validated['cnpj_matriz'] ?? null) : null,
                'suframa' => $isPJ ? ($validated['suframa'] ?? null) : null,
                'codigo_municipal' => $validated['codigo_municipal'] ?? null,
                'capital_social' => $isPJ ? ($validated['capital_social'] ?? null) : null,
                'natureza_juridica' => $isPJ ? ($validated['natureza_juridica'] ?? null) : null,
                'porte' => $isPJ ? ($validated['porte'] ?? null) : null,
                'data_inicio_atividade' => $isPJ ? ($validated['data_inicio_atividade'] ?? null) : null,
                'cnae_principal' => $isPJ ? ($validated['cnae_principal'] ?? null) : null,
                'cnae_principal_descricao' => $isPJ ? ($validated['cnae_principal_descricao'] ?? null) : null,
                'cnaes_secundarios' => $isPJ ? ($validated['cnaes_secundarios'] ?? null) : null,
                'qsa' => $isPJ ? ($validated['qsa'] ?? null) : null,
            ]);

            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente atualizado com sucesso!',
                    'redirect' => '/app/clientes',
                ]);
            }

            return redirect()
                ->route('app.clientes')
                ->with('success', 'Cliente atualizado com sucesso!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validacao',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            if ($this->isAjaxRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar cliente: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao atualizar cliente. Tente novamente.')
                ->withInput();
        }
    }

    /**
     * Delete an individual cliente.
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Nao autenticado'], 401);
        }

        $cliente = Cliente::where('user_id', $user->id)->find($id);
        if (! $cliente) {
            return response()->json(['success' => false, 'message' => 'Cliente nao encontrado'], 404);
        }

        if ($cliente->is_empresa_propria) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir a empresa própria.',
            ], 403);
        }

        try {
            $nome = $cliente->razao_social ?? $cliente->nome ?? '';
            $documento = $cliente->documento;

            $cliente->delete();

            Log::info('Cliente excluido', [
                'user_id' => $user->id,
                'cliente_id' => $id,
                'documento' => $documento,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cliente excluido com sucesso.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir cliente', [
                'user_id' => $user->id,
                'cliente_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir cliente. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Bulk delete clientes.
     */
    public function bulkDestroy(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Nao autenticado'], 401);
        }

        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'integer',
        ]);

        try {
            $count = Cliente::where('user_id', $user->id)
                ->where('is_empresa_propria', false)
                ->whereIn('id', $validated['ids'])
                ->delete();

            Log::info('Clientes excluidos em lote', [
                'user_id' => $user->id,
                'count' => $count,
                'ids' => $validated['ids'],
            ]);

            return response()->json([
                'success' => true,
                'message' => $count.' cliente(s) excluido(s) com sucesso.',
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir clientes em lote', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir clientes. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Dossiê em lote (PDF): um documento com o dossiê completo de cada cliente
     * selecionado seguido dos dossiês dos seus participantes. POST (form submit)
     * porque a lista de ids pode ser longa; a resposta é o download direto.
     */
    public function dossieLote(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'integer',
            'top' => 'nullable|in:10,20,50',
        ]);

        $dados = app(\App\Services\Clientes\DossieLoteBuilder::class)
            ->montar((int) $user->id, $validated['ids'], (int) ($validated['top'] ?? 10));

        if ($dados === null) {
            return redirect()
                ->route('app.clientes')
                ->with('export_erro', 'Nenhum cliente válido na seleção para gerar o dossiê.');
        }

        $dados['gerado_em'] = now()->format('d/m/Y H:i');

        // Mesmo racional do BI com dossiês anexos (BiController::exportarPdf): dossiê
        // multiplica páginas/tabelas no dompdf (~6MB/dossiê de pico) e o render é
        // síncrono. Teto de 50 itens no builder mantém o pior caso sob esses limites.
        ini_set('memory_limit', '1024M');
        set_time_limit(240);

        return \App\Support\PdfReport::render('reports.dossie.lote', $dados, 'portrait')
            ->download('dossies_clientes_'.now()->format('Ymd_Hi').'.pdf');
    }

    /**
     * Payload da listagem dos clientes selecionados (escopo `user_id`). Base comum de
     * PDF/XLSX/CSV. Devolve `null` quando a seleção não tem nenhum cliente válido.
     */
    private function listagemSelecionada(Request $request, \App\Services\Clientes\ClienteListagemBuilder $builder): ?array
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:1000',
            'ids.*' => 'integer',
        ]);

        return $builder->montar((int) Auth::id(), $validated['ids']);
    }

    private function listagemVaziaRedirect()
    {
        return redirect()
            ->route('app.clientes')
            ->with('export_erro', 'Nenhum cliente válido na seleção para exportar.');
    }

    /**
     * PDF de listagem/carteira ("de uma folha") dos clientes selecionados. Panorama tabular
     * (cadastral + volume movimentado + regularidade), complementar ao dossiê profundo.
     */
    public function exportarPdf(Request $request, \App\Services\Clientes\ClienteListagemBuilder $builder)
    {
        if (! Auth::user()) {
            return redirect('/login');
        }

        $dados = $this->listagemSelecionada($request, $builder);

        if ($dados === null) {
            return $this->listagemVaziaRedirect();
        }

        return $this->comTokenDownload(
            \App\Support\PdfReport::render('reports.clientes-listagem', $dados)
                ->download('clientes_'.now()->format('Ymd_Hi').'.pdf'),
            $request
        );
    }

    /** XLSX da carteira selecionada (mesmas colunas do PDF; movimentado numérico). */
    public function exportarXlsx(Request $request, \App\Services\Clientes\ClienteListagemBuilder $builder)
    {
        if (! Auth::user()) {
            return redirect('/login');
        }

        if (! \App\Support\Reports\XlsxReport::disponivel()) {
            abort(503, 'Exportação XLSX indisponível.');
        }

        $dados = $this->listagemSelecionada($request, $builder);

        if ($dados === null) {
            return $this->listagemVaziaRedirect();
        }

        return $this->comTokenDownload(
            (new \App\Services\Clientes\Export\ClienteListagemXlsxBuilder)
                ->download($dados, 'clientes_'.now()->format('Ymd_Hi').'.xlsx'),
            $request
        );
    }

    /** CSV da carteira selecionada (padrão canônico CsvExport: BOM + ";"). */
    public function exportarCsv(Request $request, \App\Services\Clientes\ClienteListagemBuilder $builder)
    {
        if (! Auth::user()) {
            return redirect('/login');
        }

        $dados = $this->listagemSelecionada($request, $builder);

        if ($dados === null) {
            return $this->listagemVaziaRedirect();
        }

        $fmtRs = fn ($v) => number_format((float) $v, 2, ',', '.');
        $limpa = fn ($v) => ($v === null || $v === '—') ? '' : $v;

        $colunas = ['Cliente', 'Documento', 'Tipo', 'UF', 'Situação', 'Regime', 'Movimentado (R$)', 'Regularidade', 'Últ. consulta'];
        $linhas = array_map(fn (array $c) => [
            $c['nome'],
            $c['documento'],
            $limpa($c['tipo']),
            $limpa($c['uf']),
            $limpa($c['situacao']),
            $limpa($c['regime']),
            $fmtRs($c['movimentado']),
            $c['regularidade'],
            $limpa($c['ultima_consulta']),
        ], $dados['clientes']);
        $linhas[] = ['Total', '', '', '', '', '', $fmtRs($dados['total_movimentado']), '', ''];

        return $this->comTokenDownload(
            \App\Support\CsvExport::download('clientes_'.now()->format('Ymd_Hi').'.csv', $colunas, $linhas),
            $request
        );
    }
}
