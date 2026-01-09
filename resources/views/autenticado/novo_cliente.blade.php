{{-- Novo Cliente - Cadastro --}}
<div class="min-h-screen bg-gray-50" id="novo-cliente-container">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div>
                <h1 class="text-xl font-bold text-gray-800">
                    Novo Cliente
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    Cadastre um novo cliente no sistema
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <form id="form-novo-cliente" method="POST" action="{{ route('app.cliente.store') }}" class="space-y-6">
            @csrf

            {{-- Grid: Dados do Cliente + Endereço Principal --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Seção 1: Dados do Cliente --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Dados do Cliente</h2>

                <div class="space-y-4">
                    {{-- Tipo de Pessoa --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Pessoa:</label>
                        <div class="flex flex-row gap-4">
                            <label id="card-tipo-pj" class="flex-1 flex items-center justify-center p-4 border-2 border-blue-600 rounded-lg cursor-pointer bg-blue-50 hover:bg-blue-100 transition-colors">
                                <input type="radio" name="tipo_pessoa" value="PJ" checked class="sr-only" id="radio-pj">
                                <div class="text-center">
                                    <div class="text-2xl mb-2">🏢</div>
                                    <div class="font-semibold text-gray-800 text-sm">Pessoa Jurídica</div>
                                    <div class="text-xs text-gray-600">CNPJ</div>
                                </div>
                            </label>

                            <label id="card-tipo-pf" class="flex-1 flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-gray-50 transition-colors">
                                <input type="radio" name="tipo_pessoa" value="PF" class="sr-only" id="radio-pf">
                                <div class="text-center">
                                    <div class="text-2xl mb-2">👤</div>
                                    <div class="font-semibold text-gray-800 text-sm">Pessoa Física</div>
                                    <div class="text-xs text-gray-600">CPF</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Documento --}}
                    <div>
                        <label for="documento" class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="label-documento">CNPJ</span> <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="documento" 
                            name="documento" 
                            required
                            placeholder="00.000.000/0000-00"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('documento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nome / Nome Fantasia --}}
                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="label-nome">Nome Fantasia</span> <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nome" 
                            name="nome" 
                            required
                            placeholder="Nome fantasia ou nome completo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('nome')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Razão Social (apenas PJ) --}}
                    <div id="campo-razao-social">
                        <label for="razao_social" class="block text-sm font-medium text-gray-700 mb-2">
                            Razão Social
                        </label>
                        <input 
                            type="text" 
                            id="razao_social" 
                            name="razao_social" 
                            placeholder="Razão social completa"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('razao_social')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Telefone --}}
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefone
                        </label>
                        <input 
                            type="text" 
                            id="telefone" 
                            name="telefone" 
                            placeholder="(00) 00000-0000"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('telefone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="email@exemplo.com"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Faturamento Anual (apenas PJ) --}}
                    <div id="campo-faturamento">
                        <label for="faturamento_anual" class="block text-sm font-medium text-gray-700 mb-2">
                            Faturamento Anual
                        </label>
                        <input 
                            type="text" 
                            id="faturamento_anual" 
                            name="faturamento_anual" 
                            placeholder="R$ 0,00"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('faturamento_anual')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Seção 2: Endereço Principal --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-6">Endereço Principal</h2>

                <div class="space-y-4">
                    {{-- CEP --}}
                    <div>
                        <label for="cep" class="block text-sm font-medium text-gray-700 mb-2">
                            CEP <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                id="cep" 
                                name="endereco[cep]" 
                                required
                                placeholder="00000-000"
                                maxlength="9"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <button 
                                type="button" 
                                id="btn-buscar-cep" 
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors"
                            >
                                Buscar
                            </button>
                        </div>
                        @error('endereco.cep')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Logradouro --}}
                    <div>
                        <label for="logradouro" class="block text-sm font-medium text-gray-700 mb-2">
                            Logradouro <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="logradouro" 
                            name="endereco[logradouro]" 
                            required
                            placeholder="Rua, Avenida, etc."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('endereco.logradouro')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Número e Complemento --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                                Número <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="numero" 
                                name="endereco[numero]" 
                                required
                                placeholder="123"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('endereco.numero')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="complemento" class="block text-sm font-medium text-gray-700 mb-2">
                                Complemento
                            </label>
                            <input 
                                type="text" 
                                id="complemento" 
                                name="endereco[complemento]" 
                                placeholder="Apto, Sala, etc."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('endereco.complemento')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Bairro --}}
                    <div>
                        <label for="bairro" class="block text-sm font-medium text-gray-700 mb-2">
                            Bairro <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="bairro" 
                            name="endereco[bairro]" 
                            required
                            placeholder="Nome do bairro"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('endereco.bairro')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Cidade e Estado --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="cidade" class="block text-sm font-medium text-gray-700 mb-2">
                                Cidade <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="cidade" 
                                name="endereco[cidade]" 
                                required
                                placeholder="Nome da cidade"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('endereco.cidade')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                Estado (UF) <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="estado" 
                                name="endereco[estado]" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Selecione</option>
                                <option value="AC">Acre</option>
                                <option value="AL">Alagoas</option>
                                <option value="AP">Amapá</option>
                                <option value="AM">Amazonas</option>
                                <option value="BA">Bahia</option>
                                <option value="CE">Ceará</option>
                                <option value="DF">Distrito Federal</option>
                                <option value="ES">Espírito Santo</option>
                                <option value="GO">Goiás</option>
                                <option value="MA">Maranhão</option>
                                <option value="MT">Mato Grosso</option>
                                <option value="MS">Mato Grosso do Sul</option>
                                <option value="MG">Minas Gerais</option>
                                <option value="PA">Pará</option>
                                <option value="PB">Paraíba</option>
                                <option value="PR">Paraná</option>
                                <option value="PE">Pernambuco</option>
                                <option value="PI">Piauí</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="RN">Rio Grande do Norte</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <option value="RO">Rondônia</option>
                                <option value="RR">Roraima</option>
                                <option value="SC">Santa Catarina</option>
                                <option value="SP">São Paulo</option>
                                <option value="SE">Sergipe</option>
                                <option value="TO">Tocantins</option>
                            </select>
                            @error('endereco.estado')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            </div>
            {{-- Fim do Grid 2 colunas --}}

            {{-- Seção 3: Funcionário/Responsável Inicial (largura total) --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-6">Funcionário/Responsável Inicial</h2>

                <div class="space-y-4">
                    {{-- Nome e Sobrenome --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="funcionario_nome" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="funcionario_nome" 
                                name="funcionario[nome]" 
                                required
                                placeholder="Nome"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('funcionario.nome')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="funcionario_sobrenome" class="block text-sm font-medium text-gray-700 mb-2">
                                Sobrenome <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="funcionario_sobrenome" 
                                name="funcionario[sobrenome]" 
                                required
                                placeholder="Sobrenome"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('funcionario.sobrenome')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Email e Senha --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="funcionario_email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="funcionario_email" 
                                name="funcionario[email]" 
                                required
                                placeholder="email@exemplo.com"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('funcionario.email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="funcionario_senha" class="block text-sm font-medium text-gray-700 mb-2">
                                Senha <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="funcionario_senha" 
                                name="funcionario[senha]" 
                                required
                                placeholder="Mínimo 8 caracteres"
                                minlength="8"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('funcionario.senha')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Cargo e Departamento --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="funcionario_cargo" class="block text-sm font-medium text-gray-700 mb-2">
                                Cargo <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="funcionario_cargo" 
                                name="funcionario[cargo]" 
                                required
                                placeholder="Ex: Gerente, Diretor, etc."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('funcionario.cargo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="funcionario_departamento" class="block text-sm font-medium text-gray-700 mb-2">
                                Departamento
                            </label>
                            <input 
                                type="text" 
                                id="funcionario_departamento" 
                                name="funcionario[departamento]" 
                                placeholder="Ex: Financeiro, TI, etc."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('funcionario.departamento')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Nível de Acesso --}}
                    <div>
                        <label for="funcionario_nivel_acesso" class="block text-sm font-medium text-gray-700 mb-2">
                            Nível de Acesso <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="funcionario_nivel_acesso" 
                            name="funcionario[nivel_acesso]" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="funcionario">Funcionário</option>
                            <option value="admin">Administrador</option>
                        </select>
                        @error('funcionario.nivel_acesso')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Botões de Ação --}}
            <div class="flex gap-4 justify-end">
                <a 
                    href="{{ route('app.clientes') }}" 
                    data-link
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition-colors"
                >
                    Cancelar
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors"
                >
                    Cadastrar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

{{-- JavaScript --}}
<script>
(function() {
    'use strict';

    // Função para aplicar máscara de CNPJ
    function maskCNPJ(value) {
        return value
            .replace(/\D/g, '')
            .replace(/^(\d{2})(\d)/, '$1.$2')
            .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1/$2')
            .replace(/(\d{4})(\d)/, '$1-$2')
            .substring(0, 18);
    }

    // Função para aplicar máscara de CPF
    function maskCPF(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
            .substring(0, 14);
    }

    // Função para aplicar máscara de CEP
    function maskCEP(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{5})(\d)/, '$1-$2')
            .substring(0, 9);
    }

    // Função para aplicar máscara de telefone
    function maskTelefone(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4,5})(\d{4})$/, '$1-$2')
            .substring(0, 15);
    }

    // Função para aplicar máscara de moeda
    function maskMoeda(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d)(\d{2})$/, '$1,$2')
            .replace(/(?=(\d{3})+(\D))\B/g, '.')
            .replace(/^/, 'R$ ');
    }

    // Função para buscar CEP via API ViaCEP
    async function buscarCEP(cep) {
        const cepLimpo = cep.replace(/\D/g, '');
        if (cepLimpo.length !== 8) {
            return;
        }

        try {
            const response = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
            const data = await response.json();

            if (!data.erro) {
                document.getElementById('logradouro').value = data.logradouro || '';
                document.getElementById('bairro').value = data.bairro || '';
                document.getElementById('cidade').value = data.localidade || '';
                document.getElementById('estado').value = data.uf || '';
            } else {
                alert('CEP não encontrado');
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
            alert('Erro ao buscar CEP. Tente novamente.');
        }
    }

    // Função para alternar entre PF e PJ
    function toggleTipoPessoa(tipo) {
        const isPJ = tipo === 'PJ';
        const cardPJ = document.getElementById('card-tipo-pj');
        const cardPF = document.getElementById('card-tipo-pf');
        const campoRazaoSocial = document.getElementById('campo-razao-social');
        const campoFaturamento = document.getElementById('campo-faturamento');
        const labelDocumento = document.getElementById('label-documento');
        const labelNome = document.getElementById('label-nome');
        const inputDocumento = document.getElementById('documento');

        // Atualizar cards
        if (isPJ) {
            cardPJ.classList.remove('border-gray-300', 'hover:bg-gray-50');
            cardPJ.classList.add('border-blue-600', 'bg-blue-50');
            cardPF.classList.remove('border-blue-600', 'bg-blue-50');
            cardPF.classList.add('border-gray-300', 'hover:bg-gray-50');
            
            campoRazaoSocial.style.display = 'block';
            campoFaturamento.style.display = 'block';
            labelDocumento.textContent = 'CNPJ';
            labelNome.textContent = 'Nome Fantasia';
            inputDocumento.placeholder = '00.000.000/0000-00';
        } else {
            cardPF.classList.remove('border-gray-300', 'hover:bg-gray-50');
            cardPF.classList.add('border-blue-600', 'bg-blue-50');
            cardPJ.classList.remove('border-blue-600', 'bg-blue-50');
            cardPJ.classList.add('border-gray-300', 'hover:bg-gray-50');
            
            campoRazaoSocial.style.display = 'none';
            campoFaturamento.style.display = 'none';
            labelDocumento.textContent = 'CPF';
            labelNome.textContent = 'Nome';
            inputDocumento.placeholder = '000.000.000-00';
        }

        // Limpar e aplicar máscara no documento
        inputDocumento.value = '';
    }

    // Inicialização
    function init() {
        const radioPJ = document.getElementById('radio-pj');
        const radioPF = document.getElementById('radio-pf');
        const inputDocumento = document.getElementById('documento');
        const inputCEP = document.getElementById('cep');
        const inputTelefone = document.getElementById('telefone');
        const inputFaturamento = document.getElementById('faturamento_anual');
        const btnBuscarCEP = document.getElementById('btn-buscar-cep');

        // Toggle tipo pessoa
        if (radioPJ) {
            radioPJ.addEventListener('change', function() {
                if (this.checked) {
                    toggleTipoPessoa('PJ');
                }
            });
        }

        if (radioPF) {
            radioPF.addEventListener('change', function() {
                if (this.checked) {
                    toggleTipoPessoa('PF');
                }
            });
        }

        // Máscara documento (CNPJ/CPF)
        if (inputDocumento) {
            inputDocumento.addEventListener('input', function(e) {
                const tipoPessoa = document.querySelector('input[name="tipo_pessoa"]:checked').value;
                if (tipoPessoa === 'PJ') {
                    this.value = maskCNPJ(this.value);
                } else {
                    this.value = maskCPF(this.value);
                }
            });
        }

        // Máscara CEP
        if (inputCEP) {
            inputCEP.addEventListener('input', function(e) {
                this.value = maskCEP(this.value);
            });

            // Buscar CEP ao perder foco
            inputCEP.addEventListener('blur', function(e) {
                if (this.value.length === 9) {
                    buscarCEP(this.value);
                }
            });
        }

        // Botão buscar CEP
        if (btnBuscarCEP) {
            btnBuscarCEP.addEventListener('click', function(e) {
                e.preventDefault();
                const cep = document.getElementById('cep').value;
                if (cep.length === 9) {
                    buscarCEP(cep);
                } else {
                    alert('Por favor, informe um CEP válido');
                }
            });
        }

        // Máscara telefone
        if (inputTelefone) {
            inputTelefone.addEventListener('input', function(e) {
                this.value = maskTelefone(this.value);
            });
        }

        // Máscara faturamento
        if (inputFaturamento) {
            inputFaturamento.addEventListener('input', function(e) {
                this.value = maskMoeda(this.value);
            });
        }

        // Inicializar com PJ selecionado
        toggleTipoPessoa('PJ');
    }

    // Aguardar DOM estar pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-inicializar se a página for carregada via SPA
    if (typeof window !== 'undefined') {
        window.addEventListener('load', function() {
            setTimeout(init, 100);
        });
    }
})();
</script>
