{{-- Novo/Editar Participante - Cadastro Manual (PJ/PF) --}}
@php
    $isEditing = isset($participante) && $participante;
    $tipoDoc = $isEditing ? ($participante->tipo_documento ?? 'PJ') : 'PJ';
@endphp
<div class="min-h-screen bg-gray-50" id="novo-participante-container">
    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header inline --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $isEditing ? 'Editar Participante' : 'Novo Participante' }}</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        @if($isEditing)
                            Atualize os dados do participante <strong>{{ $participante->cnpj_formatado }}</strong>.
                        @else
                            Cadastre pessoa juridica (CNPJ) ou fisica (CPF).
                        @endif
                    </p>
                </div>
                <a href="{{ $isEditing ? '/app/monitoramento/participante/' . $participante->id : '/app/monitoramento/participantes' }}" data-link
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
                        <h4 class="text-sm font-semibold text-blue-900">Edicao de Participante</h4>
                        <p class="text-sm text-blue-700 mt-0.5">
                            O tipo de documento e o {{ $tipoDoc === 'PF' ? 'CPF' : 'CNPJ' }} nao podem ser alterados. Atualize os demais campos conforme necessario.
                        </p>
                    @else
                        <h4 class="text-sm font-semibold text-blue-900">Cadastro de Participantes</h4>
                        <p class="text-sm text-blue-700 mt-0.5">
                            Cadastre empresas (CNPJ) ou pessoas fisicas (CPF) para monitoramento fiscal, consultas e analise de risco.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Form Area (2/3) --}}
            <div class="lg:col-span-2 space-y-6">
                <form id="form-novo-participante" method="POST" class="space-y-6"
                    @if($isEditing) data-participante-id="{{ $participante->id }}" @endif>
                    @csrf
                    <input type="hidden" name="tipo_documento" id="np_tipo_documento" value="{{ $tipoDoc }}">

                    {{-- Card: Tipo de Pessoa + Dados --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-base font-semibold text-gray-800">Dados do Participante</h2>
                        </div>
                        <div class="px-6 py-5 space-y-5">

                            {{-- Toggle PF/PJ --}}
                            <div class="grid grid-cols-2 gap-3 {{ $isEditing ? 'pointer-events-none opacity-60' : '' }}">
                                <button type="button" id="np_btn_pj"
                                    class="np-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 {{ $tipoDoc === 'PJ' ? 'border-blue-600 bg-blue-50' : 'border-gray-300 bg-white' }} cursor-pointer transition-all"
                                    onclick="window._npToggleTipo('PJ')">
                                    <div class="w-10 h-10 {{ $tipoDoc === 'PJ' ? 'bg-blue-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 {{ $tipoDoc === 'PJ' ? 'text-blue-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <span class="block text-sm font-semibold text-gray-900">Pessoa Juridica</span>
                                        <span class="block text-xs text-gray-500">CNPJ</span>
                                    </div>
                                </button>
                                <button type="button" id="np_btn_pf"
                                    class="np-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 {{ $tipoDoc === 'PF' ? 'border-blue-600 bg-blue-50' : 'border-gray-300 bg-white' }} cursor-pointer transition-all"
                                    onclick="window._npToggleTipo('PF')">
                                    <div class="w-10 h-10 {{ $tipoDoc === 'PF' ? 'bg-blue-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 {{ $tipoDoc === 'PF' ? 'text-blue-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <label for="np_cnpj" id="np_label_doc" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ $tipoDoc === 'PF' ? 'CPF' : 'CNPJ' }} <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="np_cnpj"
                                    name="cnpj"
                                    required
                                    placeholder="{{ $tipoDoc === 'PF' ? '000.000.000-00' : '00.000.000/0000-00' }}"
                                    maxlength="{{ $tipoDoc === 'PF' ? '14' : '18' }}"
                                    value="{{ $isEditing ? $participante->cnpj_formatado : '' }}"
                                    {{ $isEditing ? 'readonly' : '' }}
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $isEditing ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                >
                                <p id="np_cnpj_error" class="mt-1 text-sm text-red-600 hidden"></p>
                            </div>

                            {{-- Razão Social (PJ only) --}}
                            <div id="np_campo_razao_social">
                                <label for="np_razao_social" class="block text-sm font-medium text-gray-700 mb-1">
                                    Razão Social <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="np_razao_social"
                                    name="razao_social"
                                    value="{{ old('razao_social', $isEditing ? $participante->razao_social : '') }}"
                                    placeholder="Razao social completa"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                <p id="np_razao_social_error" class="mt-1 text-sm text-red-600 hidden"></p>
                            </div>

                            {{-- Nome Fantasia / Nome Completo --}}
                            <div>
                                <label for="np_nome_fantasia" id="np_label_nome_fantasia" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nome Fantasia
                                </label>
                                <input
                                    type="text"
                                    id="np_nome_fantasia"
                                    name="nome_fantasia"
                                    value="{{ old('nome_fantasia', $isEditing ? $participante->nome_fantasia : '') }}"
                                    placeholder="Nome fantasia (opcional)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                <p id="np_nome_fantasia_error" class="mt-1 text-sm text-red-600 hidden"></p>
                            </div>

                            {{-- Inscricao Estadual (PJ only) --}}
                            <div id="np_campo_ie">
                                <label for="np_inscricao_estadual" class="block text-sm font-medium text-gray-700 mb-1">
                                    Inscricao Estadual
                                </label>
                                <input
                                    type="text"
                                    id="np_inscricao_estadual"
                                    name="inscricao_estadual"
                                    value="{{ old('inscricao_estadual', $isEditing ? $participante->inscricao_estadual : '') }}"
                                    placeholder="Inscricao estadual (opcional)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>

                            {{-- CRT + Telefone --}}
                            <div id="np_grid_crt_tel" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div id="np_campo_crt">
                                    <label for="np_crt" class="block text-sm font-medium text-gray-700 mb-1">
                                        CRT (Regime Tributário)
                                    </label>
                                    <select
                                        id="np_crt"
                                        name="crt"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        @php $crtVal = old('crt', $isEditing ? $participante->crt : ''); @endphp
                                        <option value="">Nao informado</option>
                                        <option value="1" {{ $crtVal == '1' ? 'selected' : '' }}>1 - Simples Nacional</option>
                                        <option value="2" {{ $crtVal == '2' ? 'selected' : '' }}>2 - Simples (Excesso)</option>
                                        <option value="3" {{ $crtVal == '3' ? 'selected' : '' }}>3 - Regime Normal</option>
                                    </select>
                                </div>
                                <div id="np_campo_telefone">
                                    <label for="np_telefone" class="block text-sm font-medium text-gray-700 mb-1">
                                        Telefone
                                    </label>
                                    <input
                                        type="text"
                                        id="np_telefone"
                                        name="telefone"
                                        value="{{ old('telefone', $isEditing ? $participante->telefone : '') }}"
                                        placeholder="(00) 00000-0000"
                                        maxlength="15"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                            </div>

                            {{-- Cliente associado --}}
                            <div>
                                <label for="np_cliente_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Cliente Associado
                                </label>
                                <select
                                    id="np_cliente_id"
                                    name="cliente_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    @php $clienteIdVal = old('cliente_id', $isEditing ? $participante->cliente_id : ''); @endphp
                                    <option value="">Nao associar</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ $clienteIdVal == $cliente->id ? 'selected' : '' }}>{{ $cliente->razao_social ?? $cliente->nome }} ({{ $cliente->documento }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Endereço --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-base font-semibold text-gray-800">Endereço</h2>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            {{-- CEP --}}
                            <div>
                                <label for="np_cep" class="block text-sm font-medium text-gray-700 mb-1">
                                    CEP
                                </label>
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        id="np_cep"
                                        name="cep"
                                        value="{{ old('cep', $isEditing ? $participante->cep : '') }}"
                                        placeholder="00000-000"
                                        maxlength="9"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <button
                                        type="button"
                                        id="np_btn_buscar_cep"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200 border border-gray-300 transition-colors"
                                    >
                                        Buscar
                                    </button>
                                </div>
                                <p id="np_cep_status" class="mt-1 text-sm hidden"></p>
                            </div>

                            {{-- Logradouro --}}
                            <div>
                                <label for="np_endereco" class="block text-sm font-medium text-gray-700 mb-1">
                                    Logradouro
                                </label>
                                <input
                                    type="text"
                                    id="np_endereco"
                                    name="endereco"
                                    value="{{ old('endereco', $isEditing ? $participante->endereco : '') }}"
                                    placeholder="Rua, Avenida, etc."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>

                            {{-- Numero + Complemento (2 colunas) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="np_numero" class="block text-sm font-medium text-gray-700 mb-1">
                                        Numero
                                    </label>
                                    <input
                                        type="text"
                                        id="np_numero"
                                        name="numero"
                                        value="{{ old('numero', $isEditing ? $participante->numero : '') }}"
                                        placeholder="123"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                                <div>
                                    <label for="np_complemento" class="block text-sm font-medium text-gray-700 mb-1">
                                        Complemento
                                    </label>
                                    <input
                                        type="text"
                                        id="np_complemento"
                                        name="complemento"
                                        value="{{ old('complemento', $isEditing ? $participante->complemento : '') }}"
                                        placeholder="Apto, Sala, etc."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                            </div>

                            {{-- Bairro --}}
                            <div>
                                <label for="np_bairro" class="block text-sm font-medium text-gray-700 mb-1">
                                    Bairro
                                </label>
                                <input
                                    type="text"
                                    id="np_bairro"
                                    name="bairro"
                                    value="{{ old('bairro', $isEditing ? $participante->bairro : '') }}"
                                    placeholder="Nome do bairro"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>

                            {{-- Municipio + UF (2 colunas) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="np_municipio" class="block text-sm font-medium text-gray-700 mb-1">
                                        Municipio
                                    </label>
                                    <input
                                        type="text"
                                        id="np_municipio"
                                        name="municipio"
                                        value="{{ old('municipio', $isEditing ? $participante->municipio : '') }}"
                                        placeholder="Nome do municipio"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                                <div>
                                    <label for="np_uf" class="block text-sm font-medium text-gray-700 mb-1">
                                        UF
                                    </label>
                                    @php $ufVal = old('uf', $isEditing ? $participante->uf : ''); @endphp
                                    <select
                                        id="np_uf"
                                        name="uf"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Selecione</option>
                                        @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                            <option value="{{ $uf }}" {{ $ufVal === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botoes de Acao --}}
                    <div class="flex gap-4 justify-end">
                        <a href="{{ $isEditing ? '/app/monitoramento/participante/' . $participante->id : '/app/monitoramento/participantes' }}" data-link
                           class="px-6 py-2.5 bg-white text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 border border-gray-300 transition-colors">
                            Cancelar
                        </a>
                        <button
                            type="submit"
                            id="np_btn_salvar"
                            class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ $isEditing ? 'Atualizar Participante' : 'Salvar Participante' }}
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

                            <a href="/app/consultas/avulso" data-link id="np_link_consultar_cnpj"
                               class="block w-full text-center px-4 py-2.5 bg-white text-blue-700 rounded-lg text-sm font-bold hover:bg-blue-50 transition-colors">
                                Consultar na Receita Federal
                            </a>
                        </div>
                    </div>

                    {{-- Card: CPF monitoring em breve --}}
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
<div id="np_toast" class="fixed top-4 right-4 z-50 hidden">
    <div id="np_toast_content" class="flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium max-w-sm">
        <span id="np_toast_icon"></span>
        <span id="np_toast_message"></span>
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

    // === Toggle PF/PJ ===
    function toggleTipoDocumento(tipo) {
        currentTipo = tipo;
        var isPF = tipo === 'PF';

        // Hidden input
        document.getElementById('np_tipo_documento').value = tipo;

        // Toggle buttons visual
        var btnPJ = document.getElementById('np_btn_pj');
        var btnPF = document.getElementById('np_btn_pf');
        var iconPJ = btnPJ.querySelector('.w-10');
        var iconPF = btnPF.querySelector('.w-10');
        var svgPJ = btnPJ.querySelector('svg');
        var svgPF = btnPF.querySelector('svg');

        if (isPF) {
            btnPJ.className = 'np-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 border-gray-300 bg-white cursor-pointer transition-all';
            btnPF.className = 'np-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 border-blue-600 bg-blue-50 cursor-pointer transition-all';
            iconPJ.className = 'w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center shrink-0';
            iconPF.className = 'w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0';
            svgPJ.className.baseVal = 'w-5 h-5 text-gray-500';
            svgPF.className.baseVal = 'w-5 h-5 text-blue-600';
        } else {
            btnPJ.className = 'np-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 border-blue-600 bg-blue-50 cursor-pointer transition-all';
            btnPF.className = 'np-tipo-btn flex items-center gap-3 p-3 rounded-lg border-2 border-gray-300 bg-white cursor-pointer transition-all';
            iconPJ.className = 'w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0';
            iconPF.className = 'w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center shrink-0';
            svgPJ.className.baseVal = 'w-5 h-5 text-blue-600';
            svgPF.className.baseVal = 'w-5 h-5 text-gray-500';
        }

        // Document field: label, placeholder, maxlength
        var labelDoc = document.getElementById('np_label_doc');
        var inputDoc = document.getElementById('np_cnpj');
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
        document.getElementById('np_campo_razao_social').style.display = isPF ? 'none' : '';
        document.getElementById('np_campo_ie').style.display = isPF ? 'none' : '';
        document.getElementById('np_campo_crt').style.display = isPF ? 'none' : '';

        // Adjust telefone grid: if PF, telefone goes full width
        var gridCrtTel = document.getElementById('np_grid_crt_tel');
        if (isPF) {
            gridCrtTel.className = '';
        } else {
            gridCrtTel.className = 'grid grid-cols-1 md:grid-cols-2 gap-4';
        }

        // Nome Fantasia label and required indicator
        var labelNome = document.getElementById('np_label_nome_fantasia');
        var inputNome = document.getElementById('np_nome_fantasia');
        if (isPF) {
            labelNome.innerHTML = 'Nome Completo <span class="text-red-500">*</span>';
            inputNome.placeholder = 'Nome completo da pessoa';
        } else {
            labelNome.innerHTML = 'Nome Fantasia';
            inputNome.placeholder = 'Nome fantasia (opcional)';
        }

        // Clear errors when toggling
        clearAllErrors();

        // Update CTA link
        atualizarLinkConsultaCnpj(inputDoc.value);
    }

    // Expose for inline onclick
    window._npToggleTipo = toggleTipoDocumento;

    // === ViaCEP ===
    async function buscarCEP(cep) {
        var cepLimpo = cep.replace(/\D/g, '');
        if (cepLimpo.length !== 8) return;

        var statusEl = document.getElementById('np_cep_status');
        statusEl.textContent = 'Buscando CEP...';
        statusEl.className = 'mt-1 text-sm text-blue-600';
        statusEl.classList.remove('hidden');

        try {
            var response = await fetch('https://viacep.com.br/ws/' + cepLimpo + '/json/');
            var data = await response.json();

            if (!data.erro) {
                document.getElementById('np_endereco').value = data.logradouro || '';
                document.getElementById('np_bairro').value = data.bairro || '';
                document.getElementById('np_municipio').value = data.localidade || '';
                document.getElementById('np_uf').value = data.uf || '';
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
        var toast = document.getElementById('np_toast');
        var content = document.getElementById('np_toast_content');
        var icon = document.getElementById('np_toast_icon');
        var msg = document.getElementById('np_toast_message');

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
        ['np_cnpj', 'np_razao_social', 'np_nome_fantasia'].forEach(clearFieldError);
    }

    // === Update CTA link with CNPJ ===
    function atualizarLinkConsultaCnpj(cnpjValue) {
        var link = document.getElementById('np_link_consultar_cnpj');
        if (!link) return;
        var cnpjLimpo = cnpjValue.replace(/\D/g, '');
        if (currentTipo === 'PJ' && cnpjLimpo.length === 14) {
            link.href = '/app/consultas/avulso?cnpj=' + cnpjLimpo;
        } else {
            link.href = '/app/consultas/avulso';
        }
    }

    // === Init ===
    function init() {
        var form = document.getElementById('form-novo-participante');
        if (!form) return;

        var editId = form.dataset.participanteId || null;
        var isEditing = !!editId;

        // Read initial tipo from hidden input (set by Blade)
        currentTipo = document.getElementById('np_tipo_documento').value || 'PJ';

        var cnpjInput = document.getElementById('np_cnpj');
        var cepInput = document.getElementById('np_cep');
        var telefoneInput = document.getElementById('np_telefone');
        var btnBuscarCep = document.getElementById('np_btn_buscar_cep');

        // In edit mode, apply toggle visual for the stored tipo
        if (isEditing && currentTipo === 'PF') {
            toggleTipoDocumento('PF');
        }

        // Document input mask (dynamic)
        if (cnpjInput) {
            cnpjInput.addEventListener('input', function() {
                if (currentTipo === 'PF') {
                    this.value = maskCPF(this.value);
                } else {
                    this.value = maskCNPJ(this.value);
                }
                atualizarLinkConsultaCnpj(this.value);
            });
            cnpjInput.addEventListener('blur', function() {
                var val = this.value.replace(/\D/g, '');
                if (currentTipo === 'PF') {
                    if (val.length === 11 && !validarCPF(val)) {
                        showFieldError('np_cnpj', 'CPF invalido. Verifique os digitos.');
                    } else if (val.length > 0 && val.length < 11) {
                        showFieldError('np_cnpj', 'CPF incompleto.');
                    } else {
                        clearFieldError('np_cnpj');
                    }
                } else {
                    if (val.length === 14 && !validarCNPJ(val)) {
                        showFieldError('np_cnpj', 'CNPJ invalido. Verifique os digitos.');
                    } else if (val.length > 0 && val.length < 14) {
                        showFieldError('np_cnpj', 'CNPJ incompleto.');
                    } else {
                        clearFieldError('np_cnpj');
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

        // Buscar CEP
        if (btnBuscarCep) {
            btnBuscarCep.addEventListener('click', function(e) {
                e.preventDefault();
                var cep = document.getElementById('np_cep').value;
                if (cep.replace(/\D/g, '').length === 8) {
                    buscarCEP(cep);
                } else {
                    var statusEl = document.getElementById('np_cep_status');
                    statusEl.textContent = 'Informe um CEP valido com 8 digitos.';
                    statusEl.className = 'mt-1 text-sm text-red-600';
                    statusEl.classList.remove('hidden');
                }
            });
        }

        // Form submit (AJAX)
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            clearAllErrors();

            var isPF = currentTipo === 'PF';
            var docLabel = isPF ? 'CPF' : 'CNPJ';
            var expectedLen = isPF ? 11 : 14;

            // Client-side validation: document (skip in edit mode - readonly)
            if (!isEditing) {
                var docVal = cnpjInput.value.replace(/\D/g, '');
                if (docVal.length !== expectedLen) {
                    showFieldError('np_cnpj', 'Informe um ' + docLabel + ' valido com ' + expectedLen + ' digitos.');
                    cnpjInput.focus();
                    return;
                }
                if (isPF && !validarCPF(docVal)) {
                    showFieldError('np_cnpj', 'CPF invalido. Verifique os digitos.');
                    cnpjInput.focus();
                    return;
                }
                if (!isPF && !validarCNPJ(docVal)) {
                    showFieldError('np_cnpj', 'CNPJ invalido. Verifique os digitos.');
                    cnpjInput.focus();
                    return;
                }
            }

            // Client-side validation: required fields per type
            if (!isPF) {
                var razaoSocial = document.getElementById('np_razao_social').value.trim();
                if (!razaoSocial) {
                    showFieldError('np_razao_social', 'Razao social e obrigatoria.');
                    document.getElementById('np_razao_social').focus();
                    return;
                }
            } else {
                var nomeCompleto = document.getElementById('np_nome_fantasia').value.trim();
                if (!nomeCompleto) {
                    showFieldError('np_nome_fantasia', 'Nome completo e obrigatorio.');
                    document.getElementById('np_nome_fantasia').focus();
                    return;
                }
            }

            // Disable button
            var btnSalvar = document.getElementById('np_btn_salvar');
            btnSalvar.disabled = true;
            btnSalvar.textContent = 'Salvando...';

            // Collect form data
            var formData = new FormData(form);
            var body = {};
            formData.forEach(function(value, key) {
                if (key !== '_token' && value !== '') {
                    body[key] = value;
                }
            });

            // Get CSRF token
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            var token = csrfToken ? csrfToken.getAttribute('content') : formData.get('_token');

            // Determine URL and method based on mode
            var fetchUrl = isEditing
                ? '/app/monitoramento/participante/' + editId
                : '/app/monitoramento/novo-participante';

            if (isEditing) {
                body._method = 'PUT';
            }

            var btnLabel = isEditing ? 'Atualizar Participante' : 'Salvar Participante';

            fetch(fetchUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(body)
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
                    showToast(result.data.message || (isEditing ? 'Participante atualizado!' : 'Participante cadastrado!'), 'success');
                    // Redirect after short delay
                    setTimeout(function() {
                        var redirectUrl = result.data.redirect || '/app/monitoramento/participantes';
                        var spaLink = document.querySelector('a[data-link][href="' + redirectUrl + '"]');
                        if (spaLink) {
                            spaLink.click();
                        } else {
                            window.location.href = redirectUrl;
                        }
                    }, 800);
                } else if (result.status === 422 && result.data.errors) {
                    // Validation errors
                    var errors = result.data.errors;
                    if (errors.cnpj) showFieldError('np_cnpj', errors.cnpj[0]);
                    if (errors.razao_social) showFieldError('np_razao_social', errors.razao_social[0]);
                    if (errors.nome_fantasia) showFieldError('np_nome_fantasia', errors.nome_fantasia[0]);
                    if (errors.cliente_id) showToast(errors.cliente_id[0], 'error');
                } else {
                    showToast(result.data.error || (isEditing ? 'Erro ao atualizar participante.' : 'Erro ao cadastrar participante.'), 'error');
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
    window.initNovoParticipante = init;
})();
</script>
