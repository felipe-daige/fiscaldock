<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin(Request $request){
        if(!view()->exists("landing_page.login_public")){
            abort(404);
        }

        if(Auth::check()){
            if($request->ajax()){
                return response()->json([
                    'success' => true,
                    'message' => 'Você já está logado',
                    'redirect' => '/dashboard'
                ]);
            }
            return redirect('/dashboard');
        }
        
        if($request->ajax()){
            return view("landing_page.login_public");
        }
        return view("landing_page.layout_public", ['initialView' => 'login_public']);
    }
    public function showAgendar(Request $request){
        if(!view()->exists("landing_page.agendar_public")){
            abort(404);
        }

        if(Auth::check()){
            if($request->ajax()){
                return response()->json([
                    'success' => true,
                    'message' => 'Você já está logado',
                    'redirect' => '/dashboard'
                ]);
            }
            return redirect('/dashboard');
        }
        
        if($request->ajax()){
            return view("landing_page.agendar_public");
        }
        return view("landing_page.layout_public", ['initialView' => 'agendar_public']);
    }

    public function login(Request $request){
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:8',
            ], [
                'email.required' => 'O campo email é obrigatório',
                'email.email' => 'O campo email deve ser um email válido',
                'password.required' => 'O campo senha é obrigatório',
                'password.min' => 'A senha deve ter pelo menos 8 caracteres',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            LOG::info($e->errors());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        }

        $credentials = $request->only('email', 'password');

        // Log para debug
        Log::info('Tentativa de login', [
            'email' => $credentials['email'] ?? 'não fornecido',
            'password_length' => isset($credentials['password']) ? strlen($credentials['password']) : 0,
            'credentials_keys' => array_keys($credentials)
        ]);

        $user = Auth::attempt($credentials);

        if(!$user){
            // Verificar se o usuário existe
            $userExists = User::where('email', $credentials['email'] ?? '')->first();
            Log::warning('Falha no login', [
                'email' => $credentials['email'] ?? 'não fornecido',
                'user_exists' => $userExists ? 'sim' : 'não',
                'user_id' => $userExists?->id
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email ou senha inválidos',
                ], 401);
            }
            return back()->withErrors(['email' => 'Email ou senha inválidos'])->withInput();
        }

        Log::info('Login bem-sucedido', [
            'user_id' => Auth::id(),
            'email' => Auth::user()->email
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'redirect' => '/dashboard'
            ]);
        }
        return redirect('/dashboard');
    }

    public function agendar(Request $request){
        try {
            $request->validate([
                'nome' => 'required|string|max:255',
                'sobrenome' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'telefone' => 'required|string|max:20',
                'senha' => 'required|min:8|confirmed',
                'empresa' => 'required|string|max:255',
                'cargo' => 'required|string|max:255',
                'cnpj' => 'required|string|max:18',
                'faturamento' => 'required|string',
                'preparacao_reforma' => 'required|string|in:sim,parcialmente,nao',
            ], [
                'nome.required' => 'O campo nome é obrigatório',
                'nome.string' => 'O campo nome deve ser uma string',
                'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
                'sobrenome.required' => 'O campo sobrenome é obrigatório',
                'sobrenome.string' => 'O campo sobrenome deve ser uma string',
                'sobrenome.max' => 'O campo sobrenome deve ter no máximo 255 caracteres',
                'email.required' => 'O campo email é obrigatório',
                'email.email' => 'O campo email deve ser um email válido',
                'email.max' => 'O campo email deve ter no máximo 255 caracteres',
                'telefone.required' => 'O campo telefone é obrigatório',
                'telefone.string' => 'O campo telefone deve ser uma string',
                'telefone.max' => 'O campo telefone deve ter no máximo 20 caracteres',
                'senha.required' => 'O campo senha é obrigatório',
                'senha.min' => 'A senha deve ter pelo menos 8 caracteres',
                'senha.confirmed' => 'As senhas não conferem',
                'empresa.required' => 'O campo empresa é obrigatório',
                'empresa.string' => 'O campo empresa deve ser uma string',
                'empresa.max' => 'O campo empresa deve ter no máximo 255 caracteres',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        }

        return $this->criarClienteNovo($request);
    }

    private function criarClienteNovo(Request $request){
        DB::beginTransaction();

        try{
            $user = User::create([
                'name' => $request->nome,
                'sobrenome' => $request->sobrenome,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'password' => Hash::make($request->senha),
            ]);

            // Detectar se é CPF ou CNPJ baseado no tamanho do documento
            $documento = preg_replace('/\D/', '', $request->cnpj);
            $tipoPessoa = strlen($documento) <= 11 ? 'PF' : 'PJ';

            $cliente = Cliente::create([
                'user_id' => $user->id,
                'tipo_pessoa' => $tipoPessoa,
                'documento' => $request->cnpj,
                'nome' => $request->empresa,
                'razao_social' => $tipoPessoa === 'PJ' ? $request->empresa : null,
                'telefone' => $request->telefone,
                'email' => $request->email,
                'faturamento_anual' => $request->faturamento,
                'preparacao_reforma' => $request->preparacao_reforma,
            ]);

            Auth::attempt($request->only('email', 'senha'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso!',
                'redirect' => '/dashboard'
            ]);

        } catch (Exception $e) {

            LOG::info($e->getMessage());

            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar cadastro.'
            ], 500);
        }
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        if($request->ajax()){
            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
                'redirect' => '/inicio'
            ]);
        }
        
        return redirect('/inicio');
    }
}
