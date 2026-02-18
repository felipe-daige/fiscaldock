{{-- Novo/Editar Cliente --}}
@php
    $isEditing = isset($cliente) && $cliente;
    $tipoPessoa = $isEditing ? $cliente->tipo_pessoa : 'PJ';
    $endereco = $isEditing ? $cliente->endereco : null;
    $funcionario = $isEditing ? $cliente->funcionarios->first() : null;
@endphp
<div class="min-h-screen bg-gray-50" id="novo-cliente-container">
    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header inline --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $isEditing ? 'Editar Cliente' : 'Novo Cliente' }}</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        @if($isEditing)
                            Atualize os dados do cliente. Tipo de pessoa e documento nao podem ser alterados.
                        @else
                            Cadastre pessoa juridica (CNPJ) ou fisica (CPF) com endereco e responsavel.
                        @endif
                    </p>
                </div>
                <a href="/app/clientes" data-link
                   class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 bg-white text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>

        {{-- Info box --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    @if($isEditing)
                        <h4 class="text-sm font-semibold text-blue-900">Editando Cliente</h4>
                        <p class="text-sm text-blue-700 mt-0.5">
                            O tipo de pessoa (PJ/PF) e o documento (CNPJ/CPF) nao podem ser alterados. Atualize os demais dados conforme necessario.
                        </p>
                    @else
                        <h4 class="text-sm font-semibold text-blue-900">Cadastro de Clientes</h4>
                        <p class="text-sm text-blue-700 mt-0.5">
                            Cadastre clientes com dados de endereco e responsavel para gestao completa no sistema.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Form Area (2/3) --}}
            <div class="lg:col-span-2 space-y-6">
                <form id="nc_form" method="POST" action="{{ $isEditing ? route('app.cliente.update', $cliente->id) : route('app.cliente.store') }}" class="space-y-6"
                    @if($isEditing) data-cliente-id="{{ $cliente->id }}" @endif>
                    @csrf
                    @if($isEditing) @method('PUT') @endif
                    <input type="hidden" name="tipo_pessoa" id="nc_tipo_pessoa" value="{{ $tipoPessoa }}">

                    {{-- Card 1: Dados do Cliente --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-base font-semibold text-gray-800">Dados do Cliente</h2>
                        </div>
                        <div class="px-6 py-5 space-y-5">

                            {{-- Toggle PJ/PF --}}
                            <div class="grid grid-cols-2 gap-3 {{ $isEditing ? 'pointer-events-none opacity-60' : '' }}">
                                <button type="button" id="nc_btn_pj"
                                    class="nc-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 {{ $tipoPessoa === 'PJ' ? 'border-blue-600 bg-blue-50' : 'border-gray-300 bg-white' }} cursor-pointer transition-all"
                                    onclick="window._ncToggleTipo('PJ')">
                                    <div class="w-10 h-10 {{ $tipoPessoa === 'PJ' ? 'bg-blue-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 {{ $tipoPessoa === 'PJ' ? 'text-blue-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <span class="block text-sm font-semibold text-gray-900">Pessoa Juridica</span>
                                        <span class="block text-xs text-gray-500">CNPJ</span>
                                    </div>
                                </button>
                                <button type="button" id="nc_btn_pf"
                                    class="nc-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 {{ $tipoPessoa === 'PF' ? 'border-blue-600 bg-blue-50' : 'border-gray-300 bg-white' }} cursor-pointer transition-all"
                                    onclick="window._ncToggleTipo('PF')">
                                    <div class="w-10 h-10 {{ $tipoPessoa === 'PF' ? 'bg-blue-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 {{ $tipoPessoa === 'PF' ? 'text-blue-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <span class="block text-sm font-semibold text-gray-900">Pessoa Fisica</span>
                                        <span class="block text-xs text-gray-500">CPF</span>
                                    </div>
                                </button>
                            </div>

                            {{-- Documento (CNPJ/CPF) --}}
                            <div>
                                <label for="nc_documento" id="nc_label_documento" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ $tipoPessoa === 'PF' ? 'CPF' : 'CNPJ' }} <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="nc_documento"
                                    name="documento"
                                    required
                                    value="{{ old('documento', $isEditing ? $cliente->documento_formatado : '') }}"
                                    placeholder="{{ $tipoPessoa === 'PF' ? '000.000.000-00' : '00.000.000/0000-00' }}"
                                    maxlength="{{ $tipoPessoa === 'PF' ? 14 : 18 }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $isEditing ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    {{ $isEditing ? 'readonly' : '' }}
                                >
                                <p id="nc_documento_error" class="mt-1 text-sm text-red-600 hidden"></p>
                            </div>

                            {{-- Razao Social (PJ only) --}}
                            <div id="nc_campo_razao_social" @if($tipoPessoa === 'PF') style="display:none" @endif>
                                <label for="nc_razao_social" class="block text-sm font-medium text-gray-700 mb-1">
                                    Razao Social <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="nc_razao_social"
                                    name="razao_social"
                                    value="{{ old('razao_social', $isEditing ? $cliente->razao_social : '') }}"
                                    placeholder="Razao social completa"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                <p id="nc_razao_social_error" class="mt-1 text-sm text-red-600 hidden"></p>
                            </div>

                            {{-- Nome Fantasia / Nome Completo --}}
                            <div>
                                <label for="nc_nome" id="nc_label_nome" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ $tipoPessoa === 'PF' ? 'Nome Completo' : 'Nome Fantasia' }}
                                    @if($tipoPessoa === 'PF') <span class="text-red-500">*</span> @endif
                                </label>
                                <input
                                    type="text"
                                    id="nc_nome"
                                    name="nome"
                                    value="{{ old('nome', $isEditing ? $cliente->nome : '') }}"
                                    placeholder="{{ $tipoPessoa === 'PF' ? 'Nome completo da pessoa' : 'Nome fantasia ou nome completo' }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                <p id="nc_nome_error" class="mt-1 text-sm text-red-600 hidden"></p>
                            </div>

                            {{-- Telefone + Email (2 colunas) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="nc_telefone" class="block text-sm font-medium text-gray-700 mb-1">
                                        Telefone
                                    </label>
                                    <input
                                        type="text"
                                        id="nc_telefone"
                                        name="telefone"
                                        value="{{ old('telefone', $isEditing ? $cliente->telefone : '') }}"
                                        placeholder="(00) 00000-0000"
                                        maxlength="15"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                                <div>
                                    <label for="nc_email" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email
                                    </label>
                                    <input
                                        type="email"
                                        id="nc_email"
                                        name="email"
                                        value="{{ old('email', $isEditing ? $cliente->email : '') }}"
                                        placeholder="email@exemplo.com"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                            </div>

                            {{-- Faturamento Anual (PJ only) --}}
                            <div id="nc_campo_faturamento" @if($tipoPessoa === 'PF') style="display:none" @endif>
                                <label for="nc_faturamento_anual" class="block text-sm font-medium text-gray-700 mb-1">
                                    Faturamento Anual
                                </label>
                                <input
                                    type="text"
                                    id="nc_faturamento_anual"
                                    name="faturamento_anual"
                                    value="{{ old('faturamento_anual', $isEditing ? $cliente->faturamento_anual : '') }}"
                                    placeholder="R$ 0,00"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>
                        </div>
                    </div>

                    {{-- Card 2: Endereco Principal --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-base font-semibold text-gray-800">Endereco Principal</h2>
                        </div>
                        <div class="px-6 py-5 space-y-5">
                            {{-- CEP --}}
                            <div>
                                <label for="nc_cep" class="block text-sm font-medium text-gray-700 mb-1">
                                    CEP <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        id="nc_cep"
                                        name="endereco[cep]"
                                        required
                                        value="{{ old('endereco.cep', $endereco ? $endereco->cep : '') }}"
                                        placeholder="00000-000"
                                        maxlength="9"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <button
                                        type="button"
                                        id="nc_btn_buscar_cep"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200 border border-gray-300 transition-colors"
                                    >
                                        Buscar
                                    </button>
                                </div>
                                <p id="nc_cep_status" class="mt-1 text-sm hidden"></p>
                            </div>

                            {{-- Logradouro --}}
                            <div>
                                <label for="nc_logradouro" class="block text-sm font-medium text-gray-700 mb-1">
                                    Logradouro <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="nc_logradouro"
                                    name="endereco[logradouro]"
                                    required
                                    value="{{ old('endereco.logradouro', $endereco ? $endereco->logradouro : '') }}"
                                    placeholder="Rua, Avenida, etc."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>

                            {{-- Numero + Complemento (2 colunas) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="nc_numero" class="block text-sm font-medium text-gray-700 mb-1">
                                        Numero <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="nc_numero"
                                        name="endereco[numero]"
                                        required
                                        value="{{ old('endereco.numero', $endereco ? $endereco->numero : '') }}"
                                        placeholder="123"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                                <div>
                                    <label for="nc_complemento" class="block text-sm font-medium text-gray-700 mb-1">
                                        Complemento
                                    </label>
                                    <input
                                        type="text"
                                        id="nc_complemento"
                                        name="endereco[complemento]"
                                        value="{{ old('endereco.complemento', $endereco ? $endereco->complemento : '') }}"
                                        placeholder="Apto, Sala, etc."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                            </div>

                            {{-- Bairro --}}
                            <div>
                                <label for="nc_bairro" class="block text-sm font-medium text-gray-700 mb-1">
                                    Bairro <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="nc_bairro"
                                    name="endereco[bairro]"
                                    required
                                    value="{{ old('endereco.bairro', $endereco ? $endereco->bairro : '') }}"
                                    placeholder="Nome do bairro"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>

                            {{-- Cidade + UF (2 colunas) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="nc_cidade" class="block text-sm font-medium text-gray-700 mb-1">
                                        Cidade <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="nc_cidade"
                                        name="endereco[cidade]"
                                        required
                                        value="{{ old('endereco.cidade', $endereco ? $endereco->cidade : '') }}"
                                        placeholder="Nome da cidade"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                                <div>
                                    <label for="nc_estado" class="block text-sm font-medium text-gray-700 mb-1">
                                        Estado (UF) <span class="text-red-500">*</span>
                                    </label>
                                    @php $estadoVal = old('endereco.estado', $endereco ? $endereco->estado : ''); @endphp
                                    <select
                                        id="nc_estado"
                                        name="endereco[estado]"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Selecione</option>
                                        @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                            <option value="{{ $uf }}" {{ $estadoVal === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card 3: Funcionario/Responsavel --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-base font-semibold text-gray-800">Funcionario / Responsavel</h2>
                        </div>
                        <div class="px-6 py-5 space-y-5">
                            {{-- Nome + Sobrenome --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="nc_func_nome" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nome <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="nc_func_nome"
                                        name="funcionario[nome]"
                                        required
                                        value="{{ old('funcionario.nome', $funcionario ? $funcionario->nome : '') }}"
                                        placeholder="Nome"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <p id="nc_func_nome_error" class="mt-1 text-sm text-red-600 hidden"></p>
                                </div>
                                <div>
                                    <label for="nc_func_sobrenome" class="block text-sm font-medium text-gray-700 mb-1">
                                        Sobrenome <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="nc_func_sobrenome"
                                        name="funcionario[sobrenome]"
                                        required
                                        value="{{ old('funcionario.sobrenome', $funcionario ? $funcionario->sobrenome : '') }}"
                                        placeholder="Sobrenome"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <p id="nc_func_sobrenome_error" class="mt-1 text-sm text-red-600 hidden"></p>
                                </div>
                            </div>

                            {{-- Email + Senha --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="nc_func_email" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        id="nc_func_email"
                                        name="funcionario[email]"
                                        required
                                        value="{{ old('funcionario.email', $funcionario ? $funcionario->email : '') }}"
                                        placeholder="email@exemplo.com"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <p id="nc_func_email_error" class="mt-1 text-sm text-red-600 hidden"></p>
                                </div>
                                <div>
                                    <label for="nc_func_senha" class="block text-sm font-medium text-gray-700 mb-1">
                                        Senha @if(!$isEditing)<span class="text-red-500">*</span>@endif
                                    </label>
                                    <input
                                        type="password"
                                        id="nc_func_senha"
                                        name="funcionario[senha]"
                                        {{ $isEditing ? '' : 'required' }}
                                        placeholder="{{ $isEditing ? 'Deixe em branco para manter a atual' : 'Minimo 8 caracteres' }}"
                                        minlength="8"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <p id="nc_func_senha_error" class="mt-1 text-sm text-red-600 hidden"></p>
                                </div>
                            </div>

                            {{-- Cargo + Departamento --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="nc_func_cargo" class="block text-sm font-medium text-gray-700 mb-1">
                                        Cargo <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="nc_func_cargo"
                                        name="funcionario[cargo]"
                                        required
                                        value="{{ old('funcionario.cargo', $funcionario ? $funcionario->cargo : '') }}"
                                        placeholder="Ex: Gerente, Diretor, etc."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <p id="nc_func_cargo_error" class="mt-1 text-sm text-red-600 hidden"></p>
                                </div>
                                <div>
                                    <label for="nc_func_departamento" class="block text-sm font-medium text-gray-700 mb-1">
                                        Departamento
                                    </label>
                                    <input
                                        type="text"
                                        id="nc_func_departamento"
                                        name="funcionario[departamento]"
                                        value="{{ old('funcionario.departamento', $funcionario ? $funcionario->departamento : '') }}"
                                        placeholder="Ex: Financeiro, TI, etc."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                            </div>

                            {{-- Nivel de Acesso --}}
                            @php $nivelVal = old('funcionario.nivel_acesso', $funcionario ? $funcionario->nivel_acesso : 'funcionario'); @endphp
                            <div>
                                <label for="nc_func_nivel_acesso" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nivel de Acesso <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="nc_func_nivel_acesso"
                                    name="funcionario[nivel_acesso]"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="funcionario" {{ $nivelVal === 'funcionario' ? 'selected' : '' }}>Funcionario</option>
                                    <option value="admin" {{ $nivelVal === 'admin' ? 'selected' : '' }}>Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-4 justify-end">
                        <a href="/app/clientes" data-link
                           class="px-6 py-2.5 bg-white text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 border border-gray-300 transition-colors">
                            Cancelar
                        </a>
                        <button
                            type="submit"
                            id="nc_btn_salvar"
                            class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ $isEditing ? 'Atualizar Cliente' : 'Cadastrar Cliente' }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- CTA Sidebar (1/3) --}}
            <div class="lg:col-span-1">
                <div class="sticky top-6 space-y-4">
                    {{-- Card: Consultar CNPJ --}}
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl shadow-sm text-white overflow-hidden">
                        <div class="px-5 py-5">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold">Consultar CNPJ</h3>
                            </div>

                            <p class="text-sm text-blue-100 mb-4">
                                Preencha os dados automaticamente consultando a Receita Federal.
                            </p>

                            <div class="space-y-2 mb-5">
                                <div class="flex items-center gap-2 text-sm text-blue-100">
                                    <svg class="w-4 h-4 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Razão Social
                                </div>
                                <div class="flex items-center gap-2 text-sm text-blue-100">
                                    <svg class="w-4 h-4 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Endereço completo
                                </div>
                                <div class="flex items-center gap-2 text-sm text-blue-100">
                                    <svg class="w-4 h-4 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    CRT / Regime Tributário
                                </div>
                                <div class="flex items-center gap-2 text-sm text-blue-100">
                                    <svg class="w-4 h-4 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Situação cadastral
                                </div>
                                <div class="flex items-center gap-2 text-sm text-blue-100">
                                    <svg class="w-4 h-4 text-green-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    CNAEs e QSA (sócios)
                                </div>
                            </div>

                            <div class="mb-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-500/20 text-green-200 text-xs font-semibold rounded-full border border-green-400/30">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Gratuito
                                </span>
                                <span class="text-xs text-blue-200 ml-1">(Disponível para PJ)</span>
                            </div>

                            <a href="/app/consultas/avulso" data-link id="nc_link_consultar_cnpj"
                               class="block w-full text-center px-4 py-2.5 bg-white text-blue-700 rounded-lg text-sm font-bold hover:bg-blue-50 transition-colors">
                                Consultar na Receita Federal
                            </a>
                        </div>
                    </div>

                    {{-- Card: CPF em breve --}}
                    <div class="bg-amber-50 border border-amber-200 rounded-xl shadow-sm p-5">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-amber-900 mb-1">Monitoramento de CPF</h4>
                                <p class="text-xs text-amber-700 leading-relaxed">
                                    Em breve voce podera monitorar pessoas fisicas com as mesmas funcionalidades de PJ.
                                </p>
                                <a href="/app/consultas/planos" data-link class="inline-block mt-2 text-xs font-semibold text-amber-700 hover:text-amber-900 transition-colors">
                                    Ver planos disponiveis &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Info complementar --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-800 mb-1">Precisa de mais dados?</h4>
                                <p class="text-xs text-gray-500 leading-relaxed">
                                    Use nossos planos de consulta para obter CNDs, SINTEGRA, listas restritivas e muito mais.
                                </p>
                                <a href="/app/consultas/planos" data-link class="inline-block mt-2 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                                    Ver planos disponiveis &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toast notification container --}}
<div id="nc_toast" class="fixed top-4 right-4 z-50 hidden">
    <div id="nc_toast_content" class="flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-sm">
        <span id="nc_toast_icon"></span>
        <span id="nc_toast_message"></span>
    </div>
</div>

<script>
(function() {
    'use strict';

    var currentTipo = 'PJ';

    // === Masks ===
    function maskCNPJ(value) {
        return value
            .replace(/\D/g, '')
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1/$2')
            .replace(/(\d{4})(\d)/, '$1-$2')
            .substring(0, 18);
    }

    function maskCPF(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
            .substring(0, 14);
    }

    function maskCEP(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{5})(\d)/, '$1-$2')
            .substring(0, 9);
    }

    function maskTelefone(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4,5})(\d{4})$/, '$1-$2')
            .substring(0, 15);
    }

    function maskMoeda(value) {
        var digits = value.replace(/\D/g, '');
        if (!digits) return '';
        var num = parseInt(digits, 10);
        var formatted = (num / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return 'R$ ' + formatted;
    }

    // === Validations ===
    function validarCNPJ(cnpj) {
        cnpj = cnpj.replace(/\D/g, '');
        if (cnpj.length !== 14) return false;
        if (/^(\d)\1+$/.test(cnpj)) return false;

        var tamanho = cnpj.length - 2;
        var numeros = cnpj.substring(0, tamanho);
        var digitos = cnpj.substring(tamanho);
        var soma = 0;
        var pos = tamanho - 7;

        for (var i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        var resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(0)) return false;

        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;

        for (var j = tamanho; j >= 1; j--) {
            soma += numeros.charAt(tamanho - j) * pos--;
            if (pos < 2) pos = 9;
        }
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(1)) return false;

        return true;
    }

    function validarCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        if (cpf.length !== 11) return false;
        if (/^(\d)\1+$/.test(cpf)) return false;

        var soma = 0;
        for (var i = 0; i < 9; i++) {
            soma += parseInt(cpf.charAt(i)) * (10 - i);
        }
        var resto = (soma * 10) % 11;
        if (resto === 10) resto = 0;
        if (resto !== parseInt(cpf.charAt(9))) return false;

        soma = 0;
        for (var j = 0; j < 10; j++) {
            soma += parseInt(cpf.charAt(j)) * (11 - j);
        }
        resto = (soma * 10) % 11;
        if (resto === 10) resto = 0;
        if (resto !== parseInt(cpf.charAt(10))) return false;

        return true;
    }

    // === Toggle PJ/PF ===
    function toggleTipo(tipo) {
        currentTipo = tipo;
        var isPF = tipo === 'PF';

        // Hidden input
        document.getElementById('nc_tipo_pessoa').value = tipo;

        // Toggle buttons visual
        var btnPJ = document.getElementById('nc_btn_pj');
        var btnPF = document.getElementById('nc_btn_pf');
        var iconPJ = btnPJ.querySelector('.w-10');
        var iconPF = btnPF.querySelector('.w-10');
        var svgPJ = btnPJ.querySelector('svg');
        var svgPF = btnPF.querySelector('svg');

        if (isPF) {
            btnPJ.className = 'nc-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 border-gray-300 bg-white cursor-pointer transition-all';
            btnPF.className = 'nc-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 border-blue-600 bg-blue-50 cursor-pointer transition-all';
            iconPJ.className = 'w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center shrink-0';
            iconPF.className = 'w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0';
            svgPJ.className.baseVal = 'w-5 h-5 text-gray-500';
            svgPF.className.baseVal = 'w-5 h-5 text-blue-600';
        } else {
            btnPJ.className = 'nc-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 border-blue-600 bg-blue-50 cursor-pointer transition-all';
            btnPF.className = 'nc-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 border-gray-300 bg-white cursor-pointer transition-all';
            iconPJ.className = 'w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0';
            iconPF.className = 'w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center shrink-0';
            svgPJ.className.baseVal = 'w-5 h-5 text-blue-600';
            svgPF.className.baseVal = 'w-5 h-5 text-gray-500';
        }

        // Document field: label, placeholder, maxlength
        var labelDoc = document.getElementById('nc_label_documento');
        var inputDoc = document.getElementById('nc_documento');
        if (isPF) {
            labelDoc.innerHTML = 'CPF <span class="text-red-500">*</span>';
            inputDoc.placeholder = '000.000.000-00';
            inputDoc.maxLength = 14;
        } else {
            labelDoc.innerHTML = 'CNPJ <span class="text-red-500">*</span>';
            inputDoc.placeholder = '00.000.000/0000-00';
            inputDoc.maxLength = 18;
        }

        // Re-apply mask to current value
        var rawVal = inputDoc.value.replace(/\D/g, '');
        if (rawVal) {
            inputDoc.value = isPF ? maskCPF(rawVal) : maskCNPJ(rawVal);
        }

        // Show/hide PJ-only fields
        document.getElementById('nc_campo_razao_social').style.display = isPF ? 'none' : '';
        document.getElementById('nc_campo_faturamento').style.display = isPF ? 'none' : '';

        // Nome label change
        var labelNome = document.getElementById('nc_label_nome');
        var inputNome = document.getElementById('nc_nome');
        if (isPF) {
            labelNome.innerHTML = 'Nome Completo <span class="text-red-500">*</span>';
            inputNome.placeholder = 'Nome completo da pessoa';
        } else {
            labelNome.innerHTML = 'Nome Fantasia';
            inputNome.placeholder = 'Nome fantasia ou nome completo';
        }

        // Clear errors when toggling
        clearAllErrors();

        // Update CTA link
        atualizarLinkConsulta(inputDoc.value);
    }

    // Expose for inline onclick
    window._ncToggleTipo = toggleTipo;

    // === ViaCEP ===
    async function buscarCEP(cep) {
        var cepLimpo = cep.replace(/\D/g, '');
        if (cepLimpo.length !== 8) return;

        var statusEl = document.getElementById('nc_cep_status');
        statusEl.textContent = 'Buscando CEP...';
        statusEl.className = 'mt-1 text-sm text-blue-600';
        statusEl.classList.remove('hidden');

        try {
            var response = await fetch('https://viacep.com.br/ws/' + cepLimpo + '/json/');
            var data = await response.json();

            if (!data.erro) {
                document.getElementById('nc_logradouro').value = data.logradouro || '';
                document.getElementById('nc_bairro').value = data.bairro || '';
                document.getElementById('nc_cidade').value = data.localidade || '';
                document.getElementById('nc_estado').value = data.uf || '';
                statusEl.textContent = 'CEP encontrado!';
                statusEl.className = 'mt-1 text-sm text-green-600';
                setTimeout(function() { statusEl.classList.add('hidden'); }, 2000);
            } else {
                statusEl.textContent = 'CEP nao encontrado.';
                statusEl.className = 'mt-1 text-sm text-red-600';
            }
        } catch (error) {
            statusEl.textContent = 'Erro ao buscar CEP. Tente novamente.';
            statusEl.className = 'mt-1 text-sm text-red-600';
        }
    }

    // === Toast ===
    function showToast(message, type) {
        var toast = document.getElementById('nc_toast');
        var content = document.getElementById('nc_toast_content');
        var icon = document.getElementById('nc_toast_icon');
        var msg = document.getElementById('nc_toast_message');

        msg.textContent = message;

        if (type === 'success') {
            content.className = 'flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-sm bg-green-600 text-white';
            icon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        } else {
            content.className = 'flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-sm bg-red-600 text-white';
            icon.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        }

        toast.classList.remove('hidden');
        setTimeout(function() { toast.classList.add('hidden'); }, 4000);
    }

    // === Field error helpers ===
    function showFieldError(fieldId, message) {
        var errorEl = document.getElementById(fieldId + '_error');
        var inputEl = document.getElementById(fieldId);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }
        if (inputEl) {
            inputEl.classList.remove('border-gray-300');
            inputEl.classList.add('border-red-500');
        }
    }

    function clearFieldError(fieldId) {
        var errorEl = document.getElementById(fieldId + '_error');
        var inputEl = document.getElementById(fieldId);
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }
        if (inputEl) {
            inputEl.classList.remove('border-red-500');
            inputEl.classList.add('border-gray-300');
        }
    }

    function clearAllErrors() {
        ['nc_documento', 'nc_razao_social', 'nc_nome', 'nc_func_nome', 'nc_func_sobrenome', 'nc_func_email', 'nc_func_senha', 'nc_func_cargo'].forEach(clearFieldError);
    }

    // === Update CTA link with CNPJ ===
    function atualizarLinkConsulta(docValue) {
        var link = document.getElementById('nc_link_consultar_cnpj');
        if (!link) return;
        var cnpjLimpo = docValue.replace(/\D/g, '');
        if (currentTipo === 'PJ' && cnpjLimpo.length === 14) {
            link.href = '/app/consultas/avulso?cnpj=' + cnpjLimpo;
        } else {
            link.href = '/app/consultas/avulso';
        }
    }

    // === Client-side validation ===
    function validarFormulario(isEditing) {
        clearAllErrors();
        var isPF = currentTipo === 'PF';
        var valido = true;

        // Documento (skip in edit mode - readonly)
        if (!isEditing) {
            var docLabel = isPF ? 'CPF' : 'CNPJ';
            var expectedLen = isPF ? 11 : 14;
            var docInput = document.getElementById('nc_documento');
            var docVal = docInput.value.replace(/\D/g, '');
            if (docVal.length !== expectedLen) {
                showFieldError('nc_documento', 'Informe um ' + docLabel + ' valido com ' + expectedLen + ' digitos.');
                if (valido) docInput.focus();
                valido = false;
            } else if (isPF && !validarCPF(docVal)) {
                showFieldError('nc_documento', 'CPF invalido. Verifique os digitos.');
                if (valido) docInput.focus();
                valido = false;
            } else if (!isPF && !validarCNPJ(docVal)) {
                showFieldError('nc_documento', 'CNPJ invalido. Verifique os digitos.');
                if (valido) docInput.focus();
                valido = false;
            }
        }

        // Razao Social (PJ) or Nome (PF)
        if (!isPF) {
            var razaoInput = document.getElementById('nc_razao_social');
            if (!razaoInput.value.trim()) {
                showFieldError('nc_razao_social', 'Razao social e obrigatoria.');
                if (valido) razaoInput.focus();
                valido = false;
            }
        } else {
            var nomeInput = document.getElementById('nc_nome');
            if (!nomeInput.value.trim()) {
                showFieldError('nc_nome', 'Nome completo e obrigatorio.');
                if (valido) nomeInput.focus();
                valido = false;
            }
        }

        // Funcionario required fields
        var funcFields = [
            { id: 'nc_func_nome', msg: 'Nome do responsavel e obrigatorio.' },
            { id: 'nc_func_sobrenome', msg: 'Sobrenome e obrigatorio.' },
            { id: 'nc_func_email', msg: 'Email do responsavel e obrigatorio.' },
            { id: 'nc_func_cargo', msg: 'Cargo e obrigatorio.' }
        ];
        funcFields.forEach(function(f) {
            var el = document.getElementById(f.id);
            if (el && !el.value.trim()) {
                showFieldError(f.id, f.msg);
                if (valido) el.focus();
                valido = false;
            }
        });

        // Senha min length (required only for create, optional for edit)
        var senhaInput = document.getElementById('nc_func_senha');
        if (senhaInput) {
            var senhaVal = senhaInput.value;
            if (!isEditing && senhaVal.length < 8) {
                showFieldError('nc_func_senha', 'Senha deve ter no minimo 8 caracteres.');
                if (valido) senhaInput.focus();
                valido = false;
            } else if (isEditing && senhaVal.length > 0 && senhaVal.length < 8) {
                showFieldError('nc_func_senha', 'Senha deve ter no minimo 8 caracteres.');
                if (valido) senhaInput.focus();
                valido = false;
            }
        }

        return valido;
    }

    // === Init ===
    function init() {
        var form = document.getElementById('nc_form');
        if (!form) return;

        var editId = form.dataset.clienteId || null;
        var isEditing = !!editId;

        // Read initial tipo from hidden input (set by Blade)
        currentTipo = document.getElementById('nc_tipo_pessoa').value || 'PJ';

        var docInput = document.getElementById('nc_documento');
        var cepInput = document.getElementById('nc_cep');
        var telefoneInput = document.getElementById('nc_telefone');
        var faturamentoInput = document.getElementById('nc_faturamento_anual');
        var btnBuscarCep = document.getElementById('nc_btn_buscar_cep');

        // In edit mode, apply toggle visual for the stored tipo
        if (isEditing && currentTipo === 'PF') {
            toggleTipo('PF');
        }

        // Document input mask (dynamic)
        if (docInput) {
            docInput.addEventListener('input', function() {
                if (currentTipo === 'PF') {
                    this.value = maskCPF(this.value);
                } else {
                    this.value = maskCNPJ(this.value);
                }
                atualizarLinkConsulta(this.value);
            });
            docInput.addEventListener('blur', function() {
                var val = this.value.replace(/\D/g, '');
                if (currentTipo === 'PF') {
                    if (val.length === 11 && !validarCPF(val)) {
                        showFieldError('nc_documento', 'CPF invalido. Verifique os digitos.');
                    } else if (val.length > 0 && val.length < 11) {
                        showFieldError('nc_documento', 'CPF incompleto.');
                    } else {
                        clearFieldError('nc_documento');
                    }
                } else {
                    if (val.length === 14 && !validarCNPJ(val)) {
                        showFieldError('nc_documento', 'CNPJ invalido. Verifique os digitos.');
                    } else if (val.length > 0 && val.length < 14) {
                        showFieldError('nc_documento', 'CNPJ incompleto.');
                    } else {
                        clearFieldError('nc_documento');
                    }
                }
            });
        }

        // CEP mask
        if (cepInput) {
            cepInput.addEventListener('input', function() {
                this.value = maskCEP(this.value);
            });
        }

        // Telefone mask
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function() {
                this.value = maskTelefone(this.value);
            });
        }

        // Faturamento mask
        if (faturamentoInput) {
            faturamentoInput.addEventListener('input', function() {
                this.value = maskMoeda(this.value);
            });
        }

        // Buscar CEP
        if (btnBuscarCep) {
            btnBuscarCep.addEventListener('click', function(e) {
                e.preventDefault();
                var cep = document.getElementById('nc_cep').value;
                if (cep.replace(/\D/g, '').length === 8) {
                    buscarCEP(cep);
                } else {
                    var statusEl = document.getElementById('nc_cep_status');
                    statusEl.textContent = 'Informe um CEP valido com 8 digitos.';
                    statusEl.className = 'mt-1 text-sm text-red-600';
                    statusEl.classList.remove('hidden');
                }
            });
        }

        // Form submit (AJAX)
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validarFormulario(isEditing)) return;

            // Disable button
            var btnSalvar = document.getElementById('nc_btn_salvar');
            var btnLabel = isEditing ? 'Atualizar Cliente' : 'Cadastrar Cliente';
            btnSalvar.disabled = true;
            btnSalvar.textContent = 'Salvando...';

            // Use FormData to preserve nested field names
            var formData = new FormData(form);

            // Get CSRF token
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var token = csrfMeta ? csrfMeta.getAttribute('content') : formData.get('_token');

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: formData
            })
            .then(function(response) {
                return response.json().then(function(data) {
                    return { status: response.status, data: data };
                });
            })
            .then(function(result) {
                btnSalvar.disabled = false;
                btnSalvar.textContent = btnLabel;

                if (result.data.success) {
                    showToast(result.data.message || (isEditing ? 'Cliente atualizado com sucesso!' : 'Cliente cadastrado com sucesso!'), 'success');
                    // Redirect after short delay
                    setTimeout(function() {
                        var redirectUrl = result.data.redirect || '/app/clientes';
                        var spaLink = document.querySelector('a[data-link][href="' + redirectUrl + '"]');
                        if (spaLink) {
                            spaLink.click();
                        } else {
                            window.location.href = redirectUrl;
                        }
                    }, 800);
                } else if (result.status === 422 && result.data.errors) {
                    // Map server validation errors to fields
                    var errors = result.data.errors;
                    if (errors.documento) showFieldError('nc_documento', errors.documento[0]);
                    if (errors.razao_social) showFieldError('nc_razao_social', errors.razao_social[0]);
                    if (errors.nome) showFieldError('nc_nome', errors.nome[0]);
                    if (errors['funcionario.nome']) showFieldError('nc_func_nome', errors['funcionario.nome'][0]);
                    if (errors['funcionario.sobrenome']) showFieldError('nc_func_sobrenome', errors['funcionario.sobrenome'][0]);
                    if (errors['funcionario.email']) showFieldError('nc_func_email', errors['funcionario.email'][0]);
                    if (errors['funcionario.senha']) showFieldError('nc_func_senha', errors['funcionario.senha'][0]);
                    if (errors['funcionario.cargo']) showFieldError('nc_func_cargo', errors['funcionario.cargo'][0]);
                    // Toast for errors without specific field mapping
                    var unmappedKeys = Object.keys(errors).filter(function(k) {
                        return !['documento','razao_social','nome','funcionario.nome','funcionario.sobrenome','funcionario.email','funcionario.senha','funcionario.cargo'].includes(k);
                    });
                    if (unmappedKeys.length > 0) {
                        showToast(errors[unmappedKeys[0]][0], 'error');
                    }
                } else {
                    showToast(result.data.message || (isEditing ? 'Erro ao atualizar cliente.' : 'Erro ao cadastrar cliente.'), 'error');
                }
            })
            .catch(function(error) {
                btnSalvar.disabled = false;
                btnSalvar.textContent = btnLabel;
                showToast('Erro de conexao. Tente novamente.', 'error');
            });
        });
    }

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-init for SPA navigation
    window.initNovoCliente = init;
})();
</script>
