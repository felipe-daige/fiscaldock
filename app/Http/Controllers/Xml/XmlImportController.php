<?php

namespace App\Http\Controllers\Xml;

use App\Http\Controllers\Controller;
use App\Models\XmlDocumento;
use App\Models\XmlRegraClassificacao;
use App\Models\XmlLancamento;
use App\Models\Fornecedor;
use App\Services\XmlParserService;
use App\Services\XmlClassificationService;
use App\Services\RegimeTributarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class XmlImportController extends Controller
{
    protected XmlParserService $xmlParserService;
    protected XmlClassificationService $classificationService;
    protected RegimeTributarioService $regimeTributarioService;

    public function __construct(
        XmlParserService $xmlParserService,
        XmlClassificationService $classificationService,
        RegimeTributarioService $regimeTributarioService
    ) {
        $this->xmlParserService = $xmlParserService;
        $this->classificationService = $classificationService;
        $this->regimeTributarioService = $regimeTributarioService;
    }

    /**
     * Upload de arquivos XML
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'xmls' => 'required|array',
            'xmls.*' => 'required|file|mimes:xml|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validação falhou',
                'errors' => $validator->errors(),
            ], 422);
        }

        $arquivosProcessados = [];
        $erros = [];

        foreach ($request->file('xmls') as $arquivo) {
            try {
                // Valida tipo MIME
                $mimeType = $arquivo->getMimeType();
                if (!in_array($mimeType, ['application/xml', 'text/xml'])) {
                    $erros[] = [
                        'arquivo' => $arquivo->getClientOriginalName(),
                        'erro' => 'Tipo de arquivo inválido',
                    ];
                    continue;
                }

                // Lê conteúdo do XML
                $xmlContent = file_get_contents($arquivo->getRealPath());

                // Valida estrutura do XML
                if (!$this->xmlParserService->validarXml($xmlContent)) {
                    $erros[] = [
                        'arquivo' => $arquivo->getClientOriginalName(),
                        'erro' => 'XML inválido ou malformado',
                    ];
                    continue;
                }

                // Extrai dados
                $dados = $this->xmlParserService->extrairDados($xmlContent);

                // Verifica se já existe documento com essa chave
                $documentoExistente = XmlDocumento::where('chave_acesso', $dados['chave_acesso'])->first();
                if ($documentoExistente) {
                    $erros[] = [
                        'arquivo' => $arquivo->getClientOriginalName(),
                        'erro' => 'Documento já importado (chave: ' . $dados['chave_acesso'] . ')',
                    ];
                    continue;
                }

                // Salva arquivo
                $arquivoPath = $arquivo->store('xmls', 'local');

                // Cria registro no banco
                $documento = XmlDocumento::create([
                    'empresa_id' => auth()->check() ? auth()->user()->empresas()->first()?->id : null,
                    'chave_acesso' => $dados['chave_acesso'],
                    'cnpj_emitente' => $dados['cnpj_emitente'],
                    'cnpj_destinatario' => $dados['cnpj_destinatario'],
                    'data_emissao' => $dados['data_emissao'],
                    'valor_total' => $dados['valor_total'],
                    'cfop' => $dados['cfop'],
                    'tipo_documento' => $dados['tipo_documento'],
                    'status' => 'pendente',
                    'arquivo_path' => $arquivoPath,
                    'dados_extrados' => $dados,
                ]);

                // Atualiza ou cria fornecedor
                Fornecedor::updateOrCreate(
                    ['cnpj' => $dados['cnpj_emitente']],
                    ['razao_social' => $dados['razao_social_emitente'] ?? null]
                );

                $arquivosProcessados[] = [
                    'id' => $documento->id,
                    'chave_acesso' => $documento->chave_acesso,
                    'arquivo' => $arquivo->getClientOriginalName(),
                ];
            } catch (Exception $e) {
                $erros[] = [
                    'arquivo' => $arquivo->getClientOriginalName(),
                    'erro' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($arquivosProcessados) . ' arquivo(s) processado(s) com sucesso',
            'processados' => $arquivosProcessados,
            'erros' => $erros,
        ]);
    }

    /**
     * Processa documentos pendentes e gera sugestões
     */
    public function processar(Request $request)
    {
        $documentoIds = $request->input('documento_ids', []);

        $query = XmlDocumento::where('status', 'pendente');

        if (!empty($documentoIds)) {
            $query->whereIn('id', $documentoIds);
        }

        $documentos = $query->get();
        $processados = [];
        $erros = [];

        foreach ($documentos as $documento) {
            try {
                // Classifica o documento
                $sugestao = $this->classificationService->classificar($documento);

                // Cria lançamento sugerido
                $lancamento = $this->classificationService->criarLancamentoSugerido($documento, $sugestao);

                // Atualiza status do documento
                $documento->update(['status' => 'processado']);

                $processados[] = [
                    'documento_id' => $documento->id,
                    'lancamento_id' => $lancamento->id,
                    'sugestao' => $sugestao,
                ];
            } catch (Exception $e) {
                $erros[] = [
                    'documento_id' => $documento->id,
                    'erro' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($processados) . ' documento(s) processado(s)',
            'processados' => $processados,
            'erros' => $erros,
        ]);
    }

    /**
     * Aceita um lançamento sugerido
     */
    public function aceitarLancamento(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lancamento_id' => 'required|exists:xml_lancamentos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validação falhou',
                'errors' => $validator->errors(),
            ], 422);
        }

        $lancamento = XmlLancamento::findOrFail($request->lancamento_id);
        $lancamento->update(['status' => 'aceito']);

        $lancamento->xmlDocumento->update(['status' => 'aceito']);

        return response()->json([
            'success' => true,
            'message' => 'Lançamento aceito com sucesso',
            'lancamento' => $lancamento,
        ]);
    }

    /**
     * Ajusta um lançamento manualmente
     */
    public function ajustarLancamento(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lancamento_id' => 'required|exists:xml_lancamentos,id',
            'natureza_operacao' => 'required|string',
            'conta_debito' => 'nullable|string',
            'conta_credito' => 'nullable|string',
            'salvar_como_regra' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validação falhou',
                'errors' => $validator->errors(),
            ], 422);
        }

        $lancamento = XmlLancamento::findOrFail($request->lancamento_id);
        $documento = $lancamento->xmlDocumento;

        // Atualiza lançamento
        $lancamento->update([
            'natureza_operacao' => $request->natureza_operacao,
            'conta_debito' => $request->conta_debito,
            'conta_credito' => $request->conta_credito,
            'status' => 'aceito',
        ]);

        $documento->update(['status' => 'aceito']);

        // Se solicitado, cria regra
        if ($request->salvar_como_regra) {
            $regimeTributario = $this->regimeTributarioService->consultarRegimeTributario(
                $documento->cnpj_emitente
            );

            $this->classificationService->criarRegraDeAjuste(
                'Regra criada manualmente - ' . now()->format('d/m/Y H:i'),
                $documento->cnpj_emitente,
                $documento->cfop,
                $regimeTributario,
                $request->natureza_operacao,
                $request->conta_debito,
                $request->conta_credito
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Lançamento ajustado com sucesso',
            'lancamento' => $lancamento,
        ]);
    }

    /**
     * Lista regras de classificação
     */
    public function listarRegras(Request $request)
    {
        $regras = XmlRegraClassificacao::orderBy('prioridade', 'desc')
            ->orderBy('vezes_usada', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'regras' => $regras,
        ]);
    }

    /**
     * Cria uma nova regra de classificação
     */
    public function criarRegra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome_regra' => 'required|string|max:255',
            'condicoes' => 'required|array',
            'condicoes.cnpj_fornecedor' => 'nullable|string',
            'condicoes.cfop' => 'nullable|string',
            'condicoes.regime_tributario' => 'nullable|string',
            'acao' => 'required|array',
            'acao.natureza_operacao' => 'required|string',
            'acao.conta_debito' => 'nullable|string',
            'acao.conta_credito' => 'nullable|string',
            'prioridade' => 'nullable|integer|min:0|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validação falhou',
                'errors' => $validator->errors(),
            ], 422);
        }

        $regra = XmlRegraClassificacao::create([
            'nome_regra' => $request->nome_regra,
            'condicoes' => $request->condicoes,
            'acao' => $request->acao,
            'prioridade' => $request->prioridade ?? 50,
            'ativo' => true,
            'vezes_usada' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Regra criada com sucesso',
            'regra' => $regra,
        ]);
    }

    /**
     * Lista documentos processados
     */
    public function listarDocumentos(Request $request)
    {
        $query = XmlDocumento::with('lancamentos')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('cnpj_emitente')) {
            $query->where('cnpj_emitente', $request->cnpj_emitente);
        }

        if ($request->has('data_inicio')) {
            $query->where('data_emissao', '>=', $request->data_inicio);
        }

        if ($request->has('data_fim')) {
            $query->where('data_emissao', '<=', $request->data_fim);
        }

        $documentos = $query->paginate(20);

        return response()->json([
            'success' => true,
            'documentos' => $documentos,
        ]);
    }
}

