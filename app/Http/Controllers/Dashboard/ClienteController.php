<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ClienteEndereco;
use App\Models\ClienteFuncionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ClienteController extends Controller
{
    /**
     * Store a newly created cliente in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validação baseada no tipo de pessoa
            $tipoPessoa = $request->input('tipo_pessoa');
            
            // Regras base
            $rules = [
                'tipo_pessoa' => 'required|in:PF,PJ',
                'documento' => 'required|string|max:18|unique:clientes,documento',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'faturamento_anual' => 'nullable|string|max:50',
                'preparacao_reforma' => 'nullable|string|max:255',
                
                // Endereço
                'endereco.cep' => 'required|string|max:9',
                'endereco.logradouro' => 'required|string|max:255',
                'endereco.numero' => 'required|string|max:20',
                'endereco.complemento' => 'nullable|string|max:255',
                'endereco.bairro' => 'required|string|max:255',
                'endereco.cidade' => 'required|string|max:255',
                'endereco.estado' => 'required|string|size:2',
                'endereco.pais' => 'nullable|string|max:100',
                
                // Funcionário
                'funcionario.nome' => 'required|string|max:255',
                'funcionario.sobrenome' => 'required|string|max:255',
                'funcionario.email' => 'required|email|max:255|unique:clientes_funcionarios,email',
                'funcionario.senha' => 'required|string|min:8',
                'funcionario.cargo' => 'required|string|max:255',
                'funcionario.departamento' => 'nullable|string|max:255',
                'funcionario.nivel_acesso' => 'required|in:funcionario,admin',
            ];
            
            // Regras condicionais baseadas no tipo de pessoa
            if ($tipoPessoa === 'PJ') {
                // Para PJ: razao_social obrigatório, nome opcional
                $rules['razao_social'] = 'required|string|max:255';
                $rules['nome'] = 'nullable|string|max:255';
            } else {
                // Para PF: nome obrigatório, razao_social não aplicável
                $rules['nome'] = 'required|string|max:255';
                $rules['razao_social'] = 'nullable|string|max:255';
            }
            
            $validated = $request->validate($rules);

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Limpar documento (remover formatação)
            $documentoLimpo = preg_replace('/\D/', '', $validated['documento']);

            // Validar formato do documento
            if ($validated['tipo_pessoa'] === 'PJ') {
                if (strlen($documentoLimpo) !== 14) {
                    throw ValidationException::withMessages([
                        'documento' => 'CNPJ deve ter 14 dígitos'
                    ]);
                }
            } else {
                if (strlen($documentoLimpo) !== 11) {
                    throw ValidationException::withMessages([
                        'documento' => 'CPF deve ter 11 dígitos'
                    ]);
                }
            }

            // Usar transação para garantir consistência
            DB::beginTransaction();

            try {
                // Criar cliente
                $cliente = Cliente::create([
                    'user_id' => $user->id,
                    'tipo_pessoa' => $validated['tipo_pessoa'],
                    'documento' => $documentoLimpo,
                    'nome' => $validated['nome'] ?? null,
                    'razao_social' => $validated['razao_social'] ?? null,
                    'telefone' => $validated['telefone'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'faturamento_anual' => $validated['faturamento_anual'] ?? null,
                    'preparacao_reforma' => $validated['preparacao_reforma'] ?? null,
                    'ativo' => true,
                ]);

                // Criar endereço
                $endereco = ClienteEndereco::create([
                    'cliente_id' => $cliente->id,
                    'tipo' => 'principal',
                    'cep' => preg_replace('/\D/', '', $validated['endereco']['cep']),
                    'logradouro' => $validated['endereco']['logradouro'],
                    'numero' => $validated['endereco']['numero'],
                    'complemento' => $validated['endereco']['complemento'] ?? null,
                    'bairro' => $validated['endereco']['bairro'],
                    'cidade' => $validated['endereco']['cidade'],
                    'estado' => strtoupper($validated['endereco']['estado']),
                    'pais' => $validated['endereco']['pais'] ?? 'Brasil',
                ]);

                // Criar funcionário
                $funcionario = ClienteFuncionario::create([
                    'cliente_id' => $cliente->id,
                    'nome' => $validated['funcionario']['nome'],
                    'sobrenome' => $validated['funcionario']['sobrenome'],
                    'email' => $validated['funcionario']['email'],
                    'senha' => Hash::make($validated['funcionario']['senha']),
                    'cargo' => $validated['funcionario']['cargo'],
                    'departamento' => $validated['funcionario']['departamento'] ?? null,
                    'nivel_acesso' => $validated['funcionario']['nivel_acesso'],
                    'criado_por' => $user->id,
                ]);

                DB::commit();

                // Retornar resposta de sucesso
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Cliente cadastrado com sucesso!',
                        'cliente' => [
                            'id' => $cliente->id,
                            'nome' => $cliente->nome,
                            'documento' => $cliente->documento_formatado,
                        ]
                    ], 201);
                }

                return redirect()
                    ->route('app.clientes')
                    ->with('success', 'Cliente cadastrado com sucesso!');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao cadastrar cliente: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao cadastrar cliente. Tente novamente.')
                ->withInput();
        }
    }
}
