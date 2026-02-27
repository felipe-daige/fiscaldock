{{-- Monitoramento - Detalhe do Participante --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-participante-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <a
                        href="/app/monitoramento"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                        data-link
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $participante->razao_social ?? 'Participante' }}</h1>
                        <p class="text-sm text-gray-600 font-mono whitespace-nowrap tabular-nums">{{ $participante->cnpj_formatado }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Badge Situacao Cadastral --}}
                    @if($participante->situacao_cadastral)
                        @php
                            $situacaoClass = match(strtoupper($participante->situacao_cadastral)) {
                                'ATIVA' => 'bg-green-100 text-green-700',
                                'INAPTA', 'SUSPENSA' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $situacaoClass }}">
                            {{ strtoupper($participante->situacao_cadastral) }}
                        </span>
                    @endif
                    {{-- Badge Regime Tributario --}}
                    @if($participante->regime_tributario)
                        @php
                            $regimeClass = match(strtoupper($participante->regime_tributario)) {
                                'SIMPLES NACIONAL', 'SIMPLES' => 'bg-blue-100 text-blue-700',
                                'LUCRO PRESUMIDO' => 'bg-purple-100 text-purple-700',
                                'LUCRO REAL' => 'bg-amber-100 text-amber-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $regimeClass }}">
                            {{ strtoupper($participante->regime_tributario) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Acoes Rapidas --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    @php
                        $origemLabel = match($participante->origem_tipo) {
                            'SPED_EFD_FISCAL' => 'EFD Fiscal',
                            'SPED_EFD_CONTRIB' => 'EFD Contribuições',
                            'NFE' => 'NF-e',
                            'NFSE' => 'NFS-e',
                            'MANUAL' => 'Manual',
                            default => $participante->origem_tipo ?? 'Manual',
                        };
                        $arquivoOrigem = $participante->origem_ref['arquivo'] ?? null;
                    @endphp
                    <span>Origem: <strong class="text-gray-900">{{ $origemLabel }}</strong></span>
                    @if($arquivoOrigem)
                        <span class="mx-2">|</span>
                        <span>Arquivo: <strong class="text-gray-900">{{ $arquivoOrigem }}</strong></span>
                    @endif
                    @if($participante->ultima_consulta_em)
                        <span class="mx-2">|</span>
                        <span>Ultima consulta: <strong class="text-gray-900">{{ $participante->ultima_consulta_em->format('d/m/Y H:i') }}</strong></span>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <a
                        href="/app/participante/{{ $participante->id }}/editar"
                        data-link
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    <button
                        type="button"
                        id="btn-consulta-avulsa"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Consulta Avulsa
                    </button>
                    @if(!$assinaturaAtiva)
                        <button
                            type="button"
                            id="btn-criar-assinatura"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Criar Assinatura
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Coluna Principal --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Dados Cadastrais --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Dados Cadastrais</h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide">CNPJ</dt>
                                <dd class="mt-1 text-sm font-mono text-gray-900">{{ $participante->cnpj_formatado }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide">Razao Social</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $participante->razao_social ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide">Nome Fantasia</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $participante->nome_fantasia ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide">Situacao Cadastral</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $participante->situacao_cadastral ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide">Regime Tributario</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $participante->regime_tributario ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide">Inscricao Estadual</dt>
                                <dd class="mt-1 text-sm font-mono text-gray-900">{{ $participante->inscricao_estadual ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide">UF</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $participante->uf ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 uppercase tracking-wide">Cadastrado em</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $participante->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Dados da Ultima Consulta --}}
                @if($ultimaConsulta && $ultimaConsulta->resultado_dados)
                    @php
                        $dados = $ultimaConsulta->resultado_dados;
                        $consultasRealizadas = $dados['consultas_realizadas'] ?? [];
                    @endphp
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Dados da Ultima Consulta</h2>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Consultado em {{ $ultimaConsulta->consultado_em?->format('d/m/Y H:i') }}
                                        @if($ultimaConsulta->lote)
                                        <span class="mx-1">|</span>
                                        <a href="/app/consultas/historico?lote={{ $ultimaConsulta->lote->id }}"
                                           class="text-blue-600 hover:text-blue-800 hover:underline"
                                           data-link>
                                            Lote #{{ $ultimaConsulta->lote->id }}
                                        </a>
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($ultimaConsulta->lote?->plano)
                                    @php
                                        $planoBadgeColors = [
                                            'gratuito' => 'bg-gray-100 text-gray-700',
                                            'validacao' => 'bg-blue-100 text-blue-700',
                                            'licitacao' => 'bg-purple-100 text-purple-700',
                                            'compliance' => 'bg-amber-100 text-amber-700',
                                            'due_diligence' => 'bg-rose-100 text-rose-700',
                                            'enterprise' => 'bg-indigo-100 text-indigo-700',
                                        ];
                                        $badgeColor = $planoBadgeColors[$ultimaConsulta->lote->plano->codigo] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $ultimaConsulta->lote->plano->nome }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="p-6 space-y-6">
                            {{-- Situacao e Regime --}}
                            @if(isset($dados['situacao_cadastral']) || isset($dados['regime_tributario']) || isset($dados['simples_nacional']))
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Situacao e Regime Tributario</h3>
                                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Situacao Cadastral</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ ($dados['situacao_cadastral'] ?? '') === 'ATIVA' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $dados['situacao_cadastral'] ?? '-' }}
                                        </dd>
                                    </div>
                                    @if(isset($dados['regime_tributario']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Regime Tributario</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-700">
                                            {{ $dados['regime_tributario'] }}
                                        </dd>
                                    </div>
                                    @endif
                                    @if(isset($dados['simples_nacional']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Simples Nacional</dt>
                                        <dd class="mt-1 flex items-center gap-1.5">
                                            @if($dados['simples_nacional'])
                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm font-medium text-green-700">Optante</span>
                                            @else
                                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-500">-</span>
                                            @endif
                                        </dd>
                                    </div>
                                    @endif
                                    @if(isset($dados['mei']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">MEI</dt>
                                        <dd class="mt-1 flex items-center gap-1.5">
                                            @if($dados['mei'])
                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm font-medium text-green-700">Sim</span>
                                            @else
                                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-gray-500">-</span>
                                            @endif
                                        </dd>
                                    </div>
                                    @endif
                                </dl>
                            </div>
                            @endif

                            {{-- Dados Cadastrais Completos --}}
                            @if(isset($dados['razao_social']) || isset($dados['natureza_juridica']) || isset($dados['capital_social']))
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Dados Cadastrais</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @if(isset($dados['razao_social']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Razao Social</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $dados['razao_social'] }}</dd>
                                    </div>
                                    @endif
                                    @if(isset($dados['nome_fantasia']) && $dados['nome_fantasia'])
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Nome Fantasia</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $dados['nome_fantasia'] }}</dd>
                                    </div>
                                    @endif
                                    @if(isset($dados['natureza_juridica']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Natureza Juridica</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $dados['natureza_juridica'] }}</dd>
                                    </div>
                                    @endif
                                    @if(isset($dados['capital_social']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Capital Social</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">R$ {{ number_format($dados['capital_social'], 2, ',', '.') }}</dd>
                                    </div>
                                    @endif
                                    @if(isset($dados['data_inicio_atividade']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Inicio Atividade</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($dados['data_inicio_atividade'])->format('d/m/Y') }}</dd>
                                    </div>
                                    @endif
                                </dl>
                            </div>
                            @endif

                            {{-- Endereco --}}
                            @if(isset($dados['endereco']) && is_array($dados['endereco']))
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Endereco</h3>
                                @php $end = $dados['endereco']; @endphp
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm text-gray-900">
                                        {{ $end['logradouro'] ?? '' }}{{ isset($end['numero']) ? ', ' . $end['numero'] : '' }}
                                        @if(isset($end['complemento']) && $end['complemento'])
                                        - {{ $end['complemento'] }}
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $end['bairro'] ?? '' }} - {{ $end['municipio'] ?? '' }}/{{ $end['uf'] ?? '' }}
                                    </p>
                                    @if(isset($end['cep']))
                                    <p class="text-sm text-gray-500 mt-1 font-mono">CEP: {{ preg_replace('/(\d{5})(\d{3})/', '$1-$2', $end['cep']) }}</p>
                                    @endif
                                </div>
                                {{-- Telefones empilhados com icone --}}
                                @if((isset($dados['telefone_1']) && $dados['telefone_1']) || (isset($dados['telefone_2']) && $dados['telefone_2']))
                                <div class="mt-3">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500 mb-2">Telefones</dt>
                                        <dd class="space-y-2">
                                            @php
                                                // Funcao para formatar telefone
                                                $formatarTelefone = function($tel) {
                                                    $tel = preg_replace('/\D/', '', $tel);
                                                    if (strlen($tel) === 11) {
                                                        return '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 5) . '-' . substr($tel, 7);
                                                    } elseif (strlen($tel) === 10) {
                                                        return '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 4) . '-' . substr($tel, 6);
                                                    }
                                                    return $tel;
                                                };
                                            @endphp
                                            @if(isset($dados['telefone_1']) && $dados['telefone_1'])
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                <span class="text-sm font-mono text-gray-900">{{ $formatarTelefone($dados['telefone_1']) }}</span>
                                            </div>
                                            @endif
                                            @if(isset($dados['telefone_2']) && $dados['telefone_2'])
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                <span class="text-sm font-mono text-gray-900">{{ $formatarTelefone($dados['telefone_2']) }}</span>
                                            </div>
                                            @endif
                                        </dd>
                                    </div>
                                </div>
                                @endif

                                {{-- Mapa de Localizacao --}}
                                @php
                                    $enderecoCompleto = trim(implode(', ', array_filter([
                                        $end['logradouro'] ?? '',
                                        $end['numero'] ?? '',
                                        $end['bairro'] ?? '',
                                        $end['municipio'] ?? '',
                                        $end['uf'] ?? '',
                                        'Brasil'
                                    ])));
                                    $cepMapa = preg_replace('/\D/', '', $end['cep'] ?? '');
                                @endphp
                                <div id="participante-mapa-container" class="mt-4">
                                    <div id="participante-mapa"
                                         class="h-48 rounded-lg border border-gray-200 bg-gray-100"
                                         data-endereco="{{ $enderecoCompleto }}"
                                         data-cep="{{ $cepMapa }}">
                                    </div>
                                    <p id="mapa-loading" class="text-xs text-gray-500 mt-1 text-center">
                                        <svg class="animate-spin inline w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Carregando mapa...
                                    </p>
                                    <p id="mapa-error" class="hidden text-xs text-red-500 mt-1 text-center"></p>
                                </div>
                            </div>
                            @endif

                            {{-- CNAEs --}}
                            @if(isset($dados['cnaes']) && is_array($dados['cnaes']))
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Atividades Economicas (CNAEs)</h3>
                                @if(isset($dados['cnaes']['principal']))
                                <div class="bg-blue-50 rounded-lg p-3 mb-3">
                                    <dt class="text-xs text-blue-600 font-semibold">CNAE Principal</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="font-mono text-blue-700">{{ $dados['cnaes']['principal']['codigo'] ?? '' }}</span>
                                        - {{ $dados['cnaes']['principal']['descricao'] ?? '' }}
                                    </dd>
                                </div>
                                @endif
                                @php
                                    $cnaesSecundariosValidos = isset($dados['cnaes']['secundarios'])
                                        ? array_filter($dados['cnaes']['secundarios'], fn($c) => !empty($c['codigo']) || !empty($c['descricao']))
                                        : [];
                                @endphp
                                @if(count($cnaesSecundariosValidos) > 0)
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <dt class="text-xs text-gray-500 font-semibold mb-2">CNAEs Secundarios ({{ count($cnaesSecundariosValidos) }})</dt>
                                    <dd class="space-y-1 max-h-40 overflow-y-auto">
                                        @foreach(array_slice($cnaesSecundariosValidos, 0, 10) as $cnae)
                                        <div class="text-xs text-gray-700">
                                            <span class="font-mono text-gray-500">{{ $cnae['codigo'] ?? '' }}</span>
                                            - {{ Str::limit($cnae['descricao'] ?? '', 60) }}
                                        </div>
                                        @endforeach
                                        @if(count($cnaesSecundariosValidos) > 10)
                                        <p class="text-xs text-gray-400 mt-2">... e mais {{ count($cnaesSecundariosValidos) - 10 }} CNAEs</p>
                                        @endif
                                    </dd>
                                </div>
                                @endif
                            </div>
                            @endif

                            {{-- SINTEGRA --}}
                            @if(in_array('sintegra', $consultasRealizadas) && isset($dados['sintegra']))
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">SINTEGRA</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Inscricao Estadual</dt>
                                        <dd class="mt-1 text-sm font-mono text-gray-900">{{ $dados['sintegra']['ie'] ?? '-' }}</dd>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Situacao IE</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ ($dados['sintegra']['situacao'] ?? '') === 'HABILITADO' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $dados['sintegra']['situacao'] ?? '-' }}
                                        </dd>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Regime Apuracao</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $dados['sintegra']['regime_apuracao'] ?? '-' }}</dd>
                                    </div>
                                </dl>
                            </div>
                            @endif

                            {{-- CNDs --}}
                            @if(in_array('cnd_federal', $consultasRealizadas) || in_array('cnd_estadual', $consultasRealizadas) || in_array('crf_fgts', $consultasRealizadas) || in_array('cndt', $consultasRealizadas) || isset($dados['cnd_federal']) || isset($dados['cnd_estadual']) || isset($dados['crf_fgts']) || isset($dados['cndt']))
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Certidoes Negativas</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    @if(isset($dados['cnd_federal']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">CND Federal</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ ($dados['cnd_federal']['status'] ?? '') === 'NEGATIVA' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $dados['cnd_federal']['status'] ?? '-' }}
                                        </dd>
                                        @if(isset($dados['cnd_federal']['data_validade']))
                                        <dd class="text-xs text-gray-500 mt-1">Val: {{ $dados['cnd_federal']['data_validade'] }}</dd>
                                        @endif
                                    </div>
                                    @endif
                                    @if(isset($dados['cnd_estadual']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">CND Estadual</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ ($dados['cnd_estadual']['status'] ?? '') === 'NEGATIVA' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $dados['cnd_estadual']['status'] ?? '-' }}
                                        </dd>
                                        @if(isset($dados['cnd_estadual']['data_validade']))
                                        <dd class="text-xs text-gray-500 mt-1">Val: {{ $dados['cnd_estadual']['data_validade'] }}</dd>
                                        @endif
                                    </div>
                                    @endif
                                    @if(isset($dados['crf_fgts']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">CRF (FGTS)</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ ($dados['crf_fgts']['situacao'] ?? '') === 'REGULAR' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $dados['crf_fgts']['situacao'] ?? '-' }}
                                        </dd>
                                        @if(isset($dados['crf_fgts']['data_validade']))
                                        <dd class="text-xs text-gray-500 mt-1">Val: {{ $dados['crf_fgts']['data_validade'] }}</dd>
                                        @endif
                                    </div>
                                    @endif
                                    @if(isset($dados['cndt']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">CNDT (Trabalhista)</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ ($dados['cndt']['status'] ?? '') === 'NEGATIVA' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $dados['cndt']['status'] ?? '-' }}
                                        </dd>
                                        @if(isset($dados['cndt']['data_validade']))
                                        <dd class="text-xs text-gray-500 mt-1">Val: {{ $dados['cndt']['data_validade'] }}</dd>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            {{-- Compliance (TCU/CEIS/CNEP) --}}
                            @if(in_array('tcu_consolidada', $consultasRealizadas) && isset($dados['tcu_consolidada']))
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Compliance</h3>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">CEIS</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ !($dados['tcu_consolidada']['ceis'] ?? false) ? 'text-green-600' : 'text-red-600' }}">
                                            {{ ($dados['tcu_consolidada']['ceis'] ?? false) ? 'Consta' : 'Nada consta' }}
                                        </dd>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">CNEP</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ !($dados['tcu_consolidada']['cnep'] ?? false) ? 'text-green-600' : 'text-red-600' }}">
                                            {{ ($dados['tcu_consolidada']['cnep'] ?? false) ? 'Consta' : 'Nada consta' }}
                                        </dd>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Acordao TCU</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ !($dados['tcu_consolidada']['acordao_tcu'] ?? false) ? 'text-green-600' : 'text-red-600' }}">
                                            {{ ($dados['tcu_consolidada']['acordao_tcu'] ?? false) ? 'Consta' : 'Nada consta' }}
                                        </dd>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Licitacao Impedida</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ !($dados['tcu_consolidada']['licitacao_impedida'] ?? false) ? 'text-green-600' : 'text-red-600' }}">
                                            {{ ($dados['tcu_consolidada']['licitacao_impedida'] ?? false) ? 'Sim' : 'Nao' }}
                                        </dd>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- QSA (Socios) --}}
                            @if(in_array('qsa', $consultasRealizadas) && !empty($dados['qsa']))
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Quadro Societario (QSA)</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">CPF/CNPJ</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qualificacao</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($dados['qsa'] as $socio)
                                            <tr>
                                                <td class="px-3 py-2 text-gray-900">{{ $socio['nome'] ?? '-' }}</td>
                                                <td class="px-3 py-2 text-gray-600 font-mono">{{ $socio['cpf_cnpj'] ?? '-' }}</td>
                                                <td class="px-3 py-2 text-gray-600">{{ $socio['qualificacao'] ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            {{-- Protestos --}}
                            @if(in_array('protestos', $consultasRealizadas) && isset($dados['protestos']))
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Protestos</h3>
                                @if(empty($dados['protestos']))
                                    <p class="text-sm text-green-600 font-medium">Nenhum protesto encontrado</p>
                                @else
                                    <div class="bg-red-50 rounded-lg p-4">
                                        <p class="text-sm font-semibold text-red-700">{{ count($dados['protestos']) }} protesto(s) encontrado(s)</p>
                                        <p class="text-xs text-red-600 mt-1">Consulte o relatorio completo para detalhes</p>
                                    </div>
                                @endif
                            </div>
                            @endif

                            {{-- ESG --}}
                            @if((in_array('trabalho_escravo', $consultasRealizadas) || in_array('ibama_autuacoes', $consultasRealizadas)) && (isset($dados['trabalho_escravo']) || isset($dados['ibama_autuacoes'])))
                            <div class="border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">ESG</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    @if(isset($dados['trabalho_escravo']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Lista Trabalho Escravo</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ !$dados['trabalho_escravo'] ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $dados['trabalho_escravo'] ? 'Consta' : 'Nada consta' }}
                                        </dd>
                                    </div>
                                    @endif
                                    @if(isset($dados['ibama_autuacoes']))
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <dt class="text-xs text-gray-500">Autuacoes IBAMA</dt>
                                        <dd class="mt-1 text-sm font-semibold {{ empty($dados['ibama_autuacoes']) ? 'text-green-600' : 'text-red-600' }}">
                                            {{ empty($dados['ibama_autuacoes']) ? 'Nenhuma' : count($dados['ibama_autuacoes']) . ' autuacao(oes)' }}
                                        </dd>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Estado vazio - nenhuma consulta realizada --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Dados da Ultima Consulta</h2>
                        </div>
                        <div class="p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Nenhuma consulta realizada para este participante</p>
                            <p class="mt-1 text-xs text-gray-400">Clique em "Consulta Avulsa" para obter dados atualizados</p>
                        </div>
                    </div>
                @endif

                {{-- Historico de Consultas --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Historico de Consultas</h2>
                            <span class="text-sm text-gray-500">{{ $consultas->total() }} consulta(s)</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plano</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acoes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($consultas as $consulta)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ $consulta->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                            {{ $consulta->plano->nome ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($consulta->tipo === 'avulso')
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                                    Avulso
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700">
                                                    Assinatura
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($consulta->status === 'sucesso')
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                                    Sucesso
                                                </span>
                                            @elseif($consulta->status === 'pendente')
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-700">
                                                    Pendente
                                                </span>
                                            @elseif($consulta->status === 'processando')
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                                    Processando
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700">
                                                    Erro
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <button
                                                type="button"
                                                class="btn-ver-consulta inline-flex items-center p-2 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                data-consulta-id="{{ $consulta->id }}"
                                                title="Ver detalhes"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            Nenhuma consulta realizada para este participante.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($consultas->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $consultas->links() }}
                        </div>
                    @endif
                </div>

                {{-- Consultas em Lote --}}
                @if(isset($lotesDoParticipante) && $lotesDoParticipante->count() > 0)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Consultas em Lote</h2>
                            <span class="text-sm text-gray-500">{{ $lotesDoParticipante->count() }} lote(s)</span>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @php
                            $lotePlanoBadgeColors = [
                                'gratuito' => 'bg-gray-100 text-gray-700',
                                'validacao' => 'bg-blue-100 text-blue-700',
                                'licitacao' => 'bg-purple-100 text-purple-700',
                                'compliance' => 'bg-amber-100 text-amber-700',
                                'due_diligence' => 'bg-rose-100 text-rose-700',
                                'enterprise' => 'bg-indigo-100 text-indigo-700',
                            ];
                        @endphp
                        @foreach($lotesDoParticipante as $lote)
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">Lote #{{ $lote->id }}</p>
                                        <p class="text-xs text-gray-500">{{ $lote->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($lote->plano)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $lotePlanoBadgeColors[$lote->plano->codigo] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $lote->plano->nome }}
                                    </span>
                                    @endif
                                    @php
                                        $loteStatusColors = [
                                            'pendente' => 'bg-gray-100 text-gray-700',
                                            'processando' => 'bg-blue-100 text-blue-700',
                                            'concluido' => 'bg-green-100 text-green-700',
                                            'erro' => 'bg-red-100 text-red-700',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ $loteStatusColors[$lote->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($lote->status) }}
                                    </span>
                                    <a href="/app/consultas/historico?lote={{ $lote->id }}"
                                       class="inline-flex items-center p-2 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                       data-link
                                       title="Ver detalhes do lote">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Notas Fiscais --}}
                @if(($totalNotasFiscais ?? 0) > 0)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Notas Fiscais</h2>
                            <span class="text-sm text-gray-500">{{ $totalNotasFiscais }} nota(s)</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Numero/Serie</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Papel</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contraparte</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Valor</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acoes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($notasFiscais as $nota)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ $nota->data_emissao?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                                {{ $nota->tipo_documento }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap font-mono">
                                            {{ $nota->numero_nota ?? '-' }}/{{ $nota->serie ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($nota->papel === 'emitente')
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                                    Emitente
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                                    Destinatario
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 max-w-[200px]">
                                            <div class="truncate" title="{{ $nota->contraparte_razao }}">
                                                {{ $nota->contraparte_razao ?? '-' }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono">
                                                @php
                                                    $cpCnpj = $nota->contraparte_cnpj ?? '';
                                                    if (strlen($cpCnpj) === 14) {
                                                        $cpCnpj = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cpCnpj);
                                                    }
                                                @endphp
                                                {{ $cpCnpj ?: '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-right whitespace-nowrap font-semibold">
                                            R$ {{ number_format((float) $nota->valor_total, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-1">
                                                <button
                                                    type="button"
                                                    class="btn-ver-nota inline-flex items-center p-2 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                    data-nota-id="{{ $nota->id }}"
                                                    title="Ver detalhes"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </button>
                                                @if($nota->contraparte_participante_id)
                                                    <a
                                                        href="/app/participante/{{ $nota->contraparte_participante_id }}"
                                                        class="inline-flex items-center p-2 rounded-lg text-gray-500 hover:text-green-600 hover:bg-green-50 transition-colors"
                                                        data-link
                                                        title="Ver contraparte"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($notasFiscais->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $notasFiscais->appends(request()->except('notas_page'))->links() }}
                        </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Coluna Lateral --}}
            <div class="space-y-6">
                {{-- Assinatura Ativa --}}
                @if($assinaturaAtiva)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Assinatura Ativa</h2>
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                    Ativa
                                </span>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Plano</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $assinaturaAtiva->plano->nome ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Frequencia</p>
                                <p class="mt-1 text-sm text-gray-900">
                                    @php
                                        $frequencias = [
                                            'diario' => 'Diaria',
                                            'semanal' => 'Semanal',
                                            'quinzenal' => 'Quinzenal',
                                            'mensal' => 'Mensal',
                                        ];
                                    @endphp
                                    {{ $frequencias[$assinaturaAtiva->frequencia] ?? ucfirst($assinaturaAtiva->frequencia) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Proxima Execucao</p>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $assinaturaAtiva->proxima_execucao_em ? $assinaturaAtiva->proxima_execucao_em->format('d/m/Y H:i') : '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Ultima Execucao</p>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $assinaturaAtiva->ultima_execucao_em ? $assinaturaAtiva->ultima_execucao_em->format('d/m/Y H:i') : 'Nunca' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Creditos/Execucao</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $assinaturaAtiva->plano->custo_creditos ?? 0 }} creditos</p>
                            </div>
                            <div class="pt-4 border-t border-gray-200 flex gap-2">
                                @if($assinaturaAtiva->status === 'ativo')
                                    <button
                                        type="button"
                                        class="btn-pausar-assinatura flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-amber-300 bg-amber-50 text-amber-700 text-sm font-semibold transition hover:bg-amber-100"
                                        data-assinatura-id="{{ $assinaturaAtiva->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Pausar
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        class="btn-reativar-assinatura flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-green-300 bg-green-50 text-green-700 text-sm font-semibold transition hover:bg-green-100"
                                        data-assinatura-id="{{ $assinaturaAtiva->id }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Reativar
                                    </button>
                                @endif
                                <button
                                    type="button"
                                    class="btn-cancelar-assinatura inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-red-300 bg-red-50 text-red-700 text-sm font-semibold transition hover:bg-red-100"
                                    data-assinatura-id="{{ $assinaturaAtiva->id }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Estatisticas --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Estatisticas</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total de consultas</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $estatisticas['total_consultas'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Consultas com sucesso</span>
                            <span class="text-sm font-semibold text-green-600">{{ $estatisticas['consultas_sucesso'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Consultas com erro</span>
                            <span class="text-sm font-semibold text-red-600">{{ $estatisticas['consultas_erro'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Creditos utilizados</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $estatisticas['creditos_utilizados'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Notas fiscais</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $totalNotasFiscais ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                {{-- Saldo de Creditos --}}
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl shadow-sm p-6 text-white">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-white/80">Saldo de Creditos</p>
                            <p class="text-2xl font-bold">{{ number_format($credits ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <a
                        href="/app/creditos"
                        class="block w-full text-center px-4 py-2 rounded-lg bg-white/20 text-white text-sm font-semibold transition hover:bg-white/30"
                        data-link
                    >
                        Comprar Creditos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Consulta Avulsa --}}
<div id="modal-consulta-avulsa" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Consulta Avulsa</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <form id="form-consulta-avulsa">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selecione o Plano</label>
                    <select name="plano_id" id="select-plano-avulsa" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Selecione...</option>
                        @foreach($planos as $plano)
                            <option value="{{ $plano->id }}" data-creditos="{{ $plano->custo_creditos }}">
                                {{ $plano->nome }} ({{ $plano->custo_creditos }} creditos)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-2">Participante:</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $participante->razao_social ?? $participante->cnpj_formatado }}</p>
                    <p class="text-xs text-gray-500 font-mono">{{ $participante->cnpj_formatado }}</p>
                </div>
                <div id="info-creditos-avulsa" class="hidden bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-blue-700">
                        Custo: <strong id="custo-avulsa">0</strong> creditos
                    </p>
                    <p class="text-sm text-blue-600">
                        Seu saldo: <strong>{{ number_format($credits ?? 0, 0, ',', '.') }}</strong> creditos
                    </p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                <button type="button" class="modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                    Executar Consulta
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Criar Assinatura --}}
<div id="modal-criar-assinatura" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Criar Assinatura</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <form id="form-criar-assinatura">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selecione o Plano</label>
                    <select name="plano_id" id="select-plano-assinatura" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Selecione...</option>
                        @foreach($planos as $plano)
                            <option value="{{ $plano->id }}" data-creditos="{{ $plano->custo_creditos }}">
                                {{ $plano->nome }} ({{ $plano->custo_creditos }} creditos)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Frequencia</label>
                    <select name="frequencia" id="select-frequencia" class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="diario">Diaria</option>
                        <option value="semanal">Semanal</option>
                        <option value="quinzenal" selected>Quinzenal</option>
                        <option value="mensal">Mensal</option>
                    </select>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-2">Participante:</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $participante->razao_social ?? $participante->cnpj_formatado }}</p>
                    <p class="text-xs text-gray-500 font-mono">{{ $participante->cnpj_formatado }}</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                <button type="button" class="modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                    Criar Assinatura
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Ver Consulta --}}
<div id="modal-ver-consulta" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Resultado da Consulta</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6" id="modal-consulta-content">
            {{-- Conteudo sera preenchido via JavaScript --}}
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button type="button" class="modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                Fechar
            </button>
        </div>
    </div>
</div>

{{-- Modal Ver Nota Fiscal --}}
<div id="modal-ver-nota" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Detalhes da Nota Fiscal</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6" id="modal-nota-content">
            {{-- Conteudo sera preenchido via JavaScript --}}
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button type="button" class="modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initMonitoramentoParticipante() {
        const container = document.getElementById('monitoramento-participante-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento Participante] Inicializando...');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const participanteId = {{ $participante->id }};

        // Elementos
        const btnConsultaAvulsa = document.getElementById('btn-consulta-avulsa');
        const btnCriarAssinatura = document.getElementById('btn-criar-assinatura');
        const modalConsultaAvulsa = document.getElementById('modal-consulta-avulsa');
        const modalCriarAssinatura = document.getElementById('modal-criar-assinatura');
        const modalVerConsulta = document.getElementById('modal-ver-consulta');
        const modalConsultaContent = document.getElementById('modal-consulta-content');
        const formConsultaAvulsa = document.getElementById('form-consulta-avulsa');
        const formCriarAssinatura = document.getElementById('form-criar-assinatura');
        const selectPlanoAvulsa = document.getElementById('select-plano-avulsa');
        const infoCreditosAvulsa = document.getElementById('info-creditos-avulsa');
        const custoAvulsa = document.getElementById('custo-avulsa');

        // Abrir modal consulta avulsa
        if (btnConsultaAvulsa) {
            btnConsultaAvulsa.addEventListener('click', function() {
                if (modalConsultaAvulsa) {
                    modalConsultaAvulsa.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            });
        }

        // Abrir modal criar assinatura
        if (btnCriarAssinatura) {
            btnCriarAssinatura.addEventListener('click', function() {
                if (modalCriarAssinatura) {
                    modalCriarAssinatura.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            });
        }

        // Mostrar custo ao selecionar plano
        if (selectPlanoAvulsa) {
            selectPlanoAvulsa.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                const creditos = option.dataset.creditos || 0;

                if (this.value) {
                    infoCreditosAvulsa.classList.remove('hidden');
                    custoAvulsa.textContent = creditos;
                } else {
                    infoCreditosAvulsa.classList.add('hidden');
                }
            });
        }

        // Submit consulta avulsa
        if (formConsultaAvulsa) {
            formConsultaAvulsa.addEventListener('submit', async function(e) {
                e.preventDefault();

                const planoId = selectPlanoAvulsa.value;
                if (!planoId) {
                    window.showToast && window.showToast('Selecione um plano', 'error');
                    return;
                }

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processando...';

                try {
                    const response = await fetch('/app/monitoramento/consulta-avulsa', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            participante_id: participanteId,
                            plano_id: planoId,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Erro ao processar consulta');
                    }

                    window.showToast && window.showToast('Consulta iniciada com sucesso!', 'success');
                    modalConsultaAvulsa.classList.add('hidden');
                    document.body.style.overflow = '';

                    // Recarregar pagina para atualizar historico
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);

                } catch (err) {
                    console.error('[Monitoramento Participante] Erro:', err);
                    window.showToast && window.showToast(err.message || 'Erro ao processar consulta', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        }

        // Submit criar assinatura
        if (formCriarAssinatura) {
            formCriarAssinatura.addEventListener('submit', async function(e) {
                e.preventDefault();

                const planoId = document.getElementById('select-plano-assinatura').value;
                const frequencia = document.getElementById('select-frequencia').value;

                if (!planoId) {
                    window.showToast && window.showToast('Selecione um plano', 'error');
                    return;
                }

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Criando...';

                try {
                    const response = await fetch('/app/monitoramento/assinatura', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            participante_id: participanteId,
                            plano_id: planoId,
                            frequencia: frequencia,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Erro ao criar assinatura');
                    }

                    window.showToast && window.showToast('Assinatura criada com sucesso!', 'success');
                    modalCriarAssinatura.classList.add('hidden');
                    document.body.style.overflow = '';

                    // Recarregar pagina para atualizar
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);

                } catch (err) {
                    console.error('[Monitoramento Participante] Erro:', err);
                    window.showToast && window.showToast(err.message || 'Erro ao criar assinatura', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        }

        // Botoes ver consulta
        document.querySelectorAll('.btn-ver-consulta').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const consultaId = this.dataset.consultaId;

                modalConsultaContent.innerHTML = '<div class="flex items-center justify-center py-8"><svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
                modalVerConsulta.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                try {
                    const response = await fetch('/app/monitoramento/consulta/' + consultaId, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Erro ao carregar resultado');
                    }

                    const data = await response.json();
                    renderizarResultado(data);
                } catch (err) {
                    console.error('[Monitoramento Participante] Erro:', err);
                    modalConsultaContent.innerHTML = '<div class="text-center py-8 text-red-600">Erro ao carregar resultado. Tente novamente.</div>';
                }
            });
        });

        // Funcao para renderizar resultado no modal
        function renderizarResultado(data) {
            if (!data || !data.resultado) {
                modalConsultaContent.innerHTML = '<div class="text-center py-8 text-gray-500">Resultado nao disponivel ou consulta ainda em processamento.</div>';
                return;
            }

            const r = data.resultado;
            const cnpjFormatado = r.cnpj ? r.cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5') : '-';

            let html = '<div class="space-y-4">';

            // Header
            html += '<div class="border-b border-gray-200 pb-4">';
            html += '<h4 class="text-lg font-semibold text-gray-900">' + (r.razao_social || 'Razao Social nao informada') + '</h4>';
            html += '<p class="text-sm text-gray-600 font-mono">' + cnpjFormatado + '</p>';
            html += '</div>';

            // Informacoes basicas
            html += '<div class="grid grid-cols-2 gap-4">';
            html += '<div><p class="text-xs text-gray-500">Situacao Cadastral</p><p class="text-sm font-semibold text-gray-900">' + (r.situacao_cadastral || '-') + '</p></div>';
            html += '<div><p class="text-xs text-gray-500">Regime Tributario</p><p class="text-sm font-semibold text-gray-900">' + (r.regime_tributario || '-') + '</p></div>';
            html += '</div>';

            // Detalhes adicionais (se houver)
            if (r.detalhes && Object.keys(r.detalhes).length > 0) {
                html += '<div class="border-t border-gray-200 pt-4">';
                html += '<h5 class="text-sm font-semibold text-gray-900 mb-3">Detalhes da Consulta</h5>';
                html += '<div class="grid grid-cols-2 gap-4">';

                Object.entries(r.detalhes).forEach(function([key, value]) {
                    if (typeof value === 'object' && value !== null) {
                        const statusClass = (value.status === 'NEGATIVA' || value.status === 'REGULAR') ? 'text-green-600' : 'text-red-600';
                        html += '<div class="bg-gray-50 rounded-lg p-3">';
                        html += '<p class="text-xs text-gray-500">' + key.toUpperCase().replace(/_/g, ' ') + '</p>';
                        html += '<p class="text-sm font-semibold ' + statusClass + '">' + (value.status || JSON.stringify(value)) + '</p>';
                        if (value.validade) {
                            html += '<p class="text-xs text-gray-500 mt-1">Validade: ' + value.validade + '</p>';
                        }
                        html += '</div>';
                    } else {
                        html += '<div class="bg-gray-50 rounded-lg p-3">';
                        html += '<p class="text-xs text-gray-500">' + key.toUpperCase().replace(/_/g, ' ') + '</p>';
                        html += '<p class="text-sm font-semibold text-gray-900">' + value + '</p>';
                        html += '</div>';
                    }
                });

                html += '</div>';
                html += '</div>';
            }

            // Metadados
            html += '<div class="border-t border-gray-200 pt-4 text-xs text-gray-500">';
            html += '<p>Consulta realizada em: ' + (data.executado_em || data.created_at || '-') + '</p>';
            html += '<p>Plano: ' + (data.plano?.nome || '-') + '</p>';
            html += '<p>Creditos utilizados: ' + (data.creditos_cobrados || 0) + '</p>';
            html += '</div>';

            html += '</div>';

            modalConsultaContent.innerHTML = html;
        }

        // Pausar assinatura
        document.querySelectorAll('.btn-pausar-assinatura').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                if (!confirm('Deseja pausar esta assinatura?')) return;

                const assinaturaId = this.dataset.assinaturaId;
                try {
                    const response = await fetch('/app/monitoramento/assinatura/' + assinaturaId + '/pausar', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Erro ao pausar assinatura');
                    }

                    window.showToast && window.showToast('Assinatura pausada com sucesso!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } catch (err) {
                    window.showToast && window.showToast(err.message, 'error');
                }
            });
        });

        // Reativar assinatura
        document.querySelectorAll('.btn-reativar-assinatura').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const assinaturaId = this.dataset.assinaturaId;
                try {
                    const response = await fetch('/app/monitoramento/assinatura/' + assinaturaId + '/reativar', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Erro ao reativar assinatura');
                    }

                    window.showToast && window.showToast('Assinatura reativada com sucesso!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } catch (err) {
                    window.showToast && window.showToast(err.message, 'error');
                }
            });
        });

        // Cancelar assinatura
        document.querySelectorAll('.btn-cancelar-assinatura').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                if (!confirm('Tem certeza que deseja cancelar esta assinatura? Esta acao nao pode ser desfeita.')) return;

                const assinaturaId = this.dataset.assinaturaId;
                try {
                    const response = await fetch('/app/monitoramento/assinatura/' + assinaturaId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Erro ao cancelar assinatura');
                    }

                    window.showToast && window.showToast('Assinatura cancelada com sucesso!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } catch (err) {
                    window.showToast && window.showToast(err.message, 'error');
                }
            });
        });

        // Ver detalhes da nota fiscal
        const modalVerNota = document.getElementById('modal-ver-nota');
        const modalNotaContent = document.getElementById('modal-nota-content');

        document.querySelectorAll('.btn-ver-nota').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const notaId = this.dataset.notaId;

                modalNotaContent.innerHTML = '<div class="flex items-center justify-center py-8"><svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
                modalVerNota.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                try {
                    const response = await fetch('/app/participante/nota-fiscal/' + notaId, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Erro ao carregar detalhes da nota');
                    }

                    const result = await response.json();
                    if (result.success) {
                        renderizarNotaFiscal(result.data);
                    } else {
                        throw new Error(result.message || 'Erro ao carregar detalhes');
                    }
                } catch (err) {
                    console.error('[Monitoramento Participante] Erro:', err);
                    modalNotaContent.innerHTML = '<div class="text-center py-8 text-red-600">Erro ao carregar detalhes. Tente novamente.</div>';
                }
            });
        });

        // Funcao para renderizar nota fiscal no modal
        function renderizarNotaFiscal(data) {
            let html = '<div class="space-y-6">';

            // Dados do documento
            html += '<div class="border-b border-gray-200 pb-4">';
            html += '<h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Dados do Documento</h4>';
            html += '<div class="bg-gray-50 rounded-lg p-3 mb-3">';
            html += '<p class="text-xs text-gray-500">Chave de Acesso</p>';
            html += '<p class="text-sm font-mono text-gray-900 break-all">' + (data.nfe_id || '-') + '</p>';
            html += '</div>';
            html += '<div class="grid grid-cols-3 gap-4">';
            html += '<div><p class="text-xs text-gray-500">Tipo</p><p class="text-sm font-semibold text-gray-900">' + (data.tipo_documento || '-') + '</p></div>';
            html += '<div><p class="text-xs text-gray-500">Numero/Serie</p><p class="text-sm font-semibold text-gray-900">' + (data.numero_nota || '-') + '/' + (data.serie || '-') + '</p></div>';
            html += '<div><p class="text-xs text-gray-500">Data Emissao</p><p class="text-sm font-semibold text-gray-900">' + (data.data_emissao || '-') + '</p></div>';
            html += '</div>';
            html += '<div class="mt-3">';
            html += '<p class="text-xs text-gray-500">Natureza da Operacao</p>';
            html += '<p class="text-sm text-gray-900">' + (data.natureza_operacao || '-') + '</p>';
            html += '</div>';
            html += '<div class="grid grid-cols-2 gap-4 mt-3">';
            html += '<div><p class="text-xs text-gray-500">Tipo</p><p class="text-sm font-semibold text-gray-900">' + (data.tipo_nota || '-') + '</p></div>';
            html += '<div><p class="text-xs text-gray-500">Finalidade</p><p class="text-sm font-semibold text-gray-900">' + (data.finalidade || '-') + '</p></div>';
            html += '</div>';
            html += '</div>';

            // Emitente
            html += '<div class="border-b border-gray-200 pb-4">';
            html += '<h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Emitente</h4>';
            html += '<div class="grid grid-cols-2 gap-4">';
            html += '<div><p class="text-xs text-gray-500">CNPJ</p><p class="text-sm font-mono text-gray-900">' + (data.emit_cnpj || '-') + '</p></div>';
            html += '<div><p class="text-xs text-gray-500">UF</p><p class="text-sm text-gray-900">' + (data.emit_uf || '-') + '</p></div>';
            html += '</div>';
            html += '<div class="mt-2"><p class="text-xs text-gray-500">Razao Social</p><p class="text-sm text-gray-900">' + (data.emit_razao_social || '-') + '</p></div>';
            html += '</div>';

            // Destinatario
            html += '<div class="border-b border-gray-200 pb-4">';
            html += '<h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Destinatario</h4>';
            html += '<div class="grid grid-cols-2 gap-4">';
            html += '<div><p class="text-xs text-gray-500">CNPJ</p><p class="text-sm font-mono text-gray-900">' + (data.dest_cnpj || '-') + '</p></div>';
            html += '<div><p class="text-xs text-gray-500">UF</p><p class="text-sm text-gray-900">' + (data.dest_uf || '-') + '</p></div>';
            html += '</div>';
            html += '<div class="mt-2"><p class="text-xs text-gray-500">Razao Social</p><p class="text-sm text-gray-900">' + (data.dest_razao_social || '-') + '</p></div>';
            html += '</div>';

            // Valores
            html += '<div>';
            html += '<h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Valores</h4>';
            html += '<div class="bg-blue-50 rounded-lg p-4 mb-3">';
            html += '<p class="text-xs text-blue-600">Valor Total</p>';
            html += '<p class="text-2xl font-bold text-blue-700">R$ ' + (data.valor_total || '0,00') + '</p>';
            html += '</div>';
            html += '<div class="grid grid-cols-3 gap-3">';
            html += '<div class="bg-gray-50 rounded-lg p-3">';
            html += '<p class="text-xs text-gray-500">ICMS</p>';
            html += '<p class="text-sm font-semibold text-gray-900">R$ ' + (data.icms_valor || '0,00') + '</p>';
            html += '</div>';
            html += '<div class="bg-gray-50 rounded-lg p-3">';
            html += '<p class="text-xs text-gray-500">ICMS-ST</p>';
            html += '<p class="text-sm font-semibold text-gray-900">R$ ' + (data.icms_st_valor || '0,00') + '</p>';
            html += '</div>';
            html += '<div class="bg-gray-50 rounded-lg p-3">';
            html += '<p class="text-xs text-gray-500">PIS</p>';
            html += '<p class="text-sm font-semibold text-gray-900">R$ ' + (data.pis_valor || '0,00') + '</p>';
            html += '</div>';
            html += '<div class="bg-gray-50 rounded-lg p-3">';
            html += '<p class="text-xs text-gray-500">COFINS</p>';
            html += '<p class="text-sm font-semibold text-gray-900">R$ ' + (data.cofins_valor || '0,00') + '</p>';
            html += '</div>';
            html += '<div class="bg-gray-50 rounded-lg p-3">';
            html += '<p class="text-xs text-gray-500">IPI</p>';
            html += '<p class="text-sm font-semibold text-gray-900">R$ ' + (data.ipi_valor || '0,00') + '</p>';
            html += '</div>';
            html += '<div class="bg-amber-50 rounded-lg p-3">';
            html += '<p class="text-xs text-amber-600">Total Tributos</p>';
            html += '<p class="text-sm font-semibold text-amber-700">R$ ' + (data.tributos_total || '0,00') + '</p>';
            html += '</div>';
            html += '</div>';
            html += '</div>';

            html += '</div>';

            modalNotaContent.innerHTML = html;
        }

        // Copiar chave de acesso
        document.querySelectorAll('.btn-copiar-chave').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const chave = this.dataset.chave;

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(chave).then(function() {
                        window.showToast && window.showToast('Chave copiada para a area de transferencia!', 'success');
                    }).catch(function() {
                        fallbackCopyTextToClipboard(chave);
                    });
                } else {
                    fallbackCopyTextToClipboard(chave);
                }
            });
        });

        // Fallback para copiar texto
        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                window.showToast && window.showToast('Chave copiada para a area de transferencia!', 'success');
            } catch (err) {
                window.showToast && window.showToast('Erro ao copiar chave', 'error');
            }

            document.body.removeChild(textArea);
        }

        // Fechar modais
        document.querySelectorAll('.modal-close').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const modal = btn.closest('[id^="modal-"]');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });

        // Fechar modal clicando fora
        [modalConsultaAvulsa, modalCriarAssinatura, modalVerConsulta, modalVerNota].forEach(function(modal) {
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                });
            }
        });

        // Inicializar mapa de localizacao
        var mapContainer = document.getElementById('participante-mapa');
        if (mapContainer && typeof L !== 'undefined') {
            var endereco = mapContainer.dataset.endereco;
            var cep = (mapContainer.dataset.cep || '').replace(/\D/g, '');
            var loadingEl = document.getElementById('mapa-loading');
            var errorEl = document.getElementById('mapa-error');

            // Verifica se tem endereco valido
            if (!endereco || endereco.trim() === '' || endereco.trim() === 'Brasil') {
                if (loadingEl) loadingEl.classList.add('hidden');
                var mapaContainerEl = document.getElementById('participante-mapa-container');
                if (mapaContainerEl) mapaContainerEl.classList.add('hidden');
            } else {
                // Funcao para inicializar mapa com coordenadas
                function initMap(lat, lon) {
                    var map = L.map('participante-mapa').setView([lat, lon], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        maxZoom: 19
                    }).addTo(map);
                    L.marker([lat, lon]).addTo(map)
                        .bindPopup('<strong>Localizacao</strong><br>' + endereco)
                        .openPopup();
                    setTimeout(function() { map.invalidateSize(); }, 100);
                }

                // Funcao para mostrar erro
                function showMapError(msg) {
                    if (loadingEl) loadingEl.classList.add('hidden');
                    if (errorEl) {
                        errorEl.textContent = msg || 'Endereco nao encontrado';
                        errorEl.classList.remove('hidden');
                    }
                    mapContainer.classList.add('hidden');
                }

                // Funcao para geocodificar via Nominatim (fallback)
                function geocodeWithNominatim(addr) {
                    var url = 'https://nominatim.openstreetmap.org/search?' + new URLSearchParams({
                        q: addr,
                        format: 'json',
                        limit: 1,
                        countrycodes: 'br'
                    });

                    fetch(url, {
                        headers: { 'User-Agent': 'FiscalDock/1.0 (contato@fiscaldock.com.br)' }
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (loadingEl) loadingEl.classList.add('hidden');
                        if (data && data.length > 0) {
                            initMap(parseFloat(data[0].lat), parseFloat(data[0].lon));
                        } else {
                            showMapError('Endereco nao encontrado no mapa');
                        }
                    })
                    .catch(function(err) {
                        console.error('[Mapa] Erro Nominatim:', err);
                        showMapError('Erro ao carregar mapa');
                    });
                }

                // Tentar primeiro por CEP usando BrasilAPI (mais confiavel para BR)
                if (cep && cep.length === 8) {
                    fetch('https://brasilapi.com.br/api/cep/v2/' + cep)
                    .then(function(res) {
                        if (!res.ok) throw new Error('CEP nao encontrado');
                        return res.json();
                    })
                    .then(function(data) {
                        if (loadingEl) loadingEl.classList.add('hidden');
                        if (data.location && data.location.coordinates && data.location.coordinates.latitude && data.location.coordinates.longitude) {
                            initMap(data.location.coordinates.latitude, data.location.coordinates.longitude);
                        } else {
                            // CEP encontrado mas sem coordenadas - tentar Nominatim
                            console.log('[Mapa] CEP sem coordenadas, tentando Nominatim...');
                            geocodeWithNominatim(endereco);
                        }
                    })
                    .catch(function(err) {
                        console.log('[Mapa] BrasilAPI falhou, tentando Nominatim...', err);
                        // Fallback para Nominatim
                        geocodeWithNominatim(endereco);
                    });
                } else {
                    // Sem CEP valido - usar Nominatim diretamente
                    geocodeWithNominatim(endereco);
                }
            }
        }

        console.log('[Monitoramento Participante] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initMonitoramentoParticipante = initMonitoramentoParticipante;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramentoParticipante, { once: true });
    } else {
        initMonitoramentoParticipante();
    }
})();
</script>
