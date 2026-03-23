{{-- Minha Empresa - Dashboard --}}
<div class="min-h-screen bg-gray-50" id="minha-empresa-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <style>
            @keyframes card-slide-in {
                from { opacity: 0; transform: translateY(60px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .dash-animate {
                opacity: 0;
                animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .dash-animate { opacity: 1; animation: none; }
            }
        </style>

        {{-- Header compacto — sem card wrapper --}}
        <div class="mb-4 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $empresa->razao_social ?? $empresa->nome }}</h1>
                    <span class="text-sm text-gray-400 font-mono">{{ $empresa->documento_formatado }}</span>

                    @php
                        $situacao = mb_strtoupper($certidoes['situacao_cadastral'] ?? 'NÃO CONSULTADO');
                        $situacaoCor = match($situacao) {
                            'ATIVA' => 'green',
                            'SUSPENSA' => 'yellow',
                            'INAPTA', 'BAIXADA' => 'red',
                            default => 'gray'
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $situacaoCor }}-100 text-{{ $situacaoCor }}-800">
                        {{ $situacao }}
                    </span>

                    @if($certidoes['simples_nacional'] === true)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                            Simples Nacional
                        </span>
                    @endif

                    @if($certidoes['mei'] === true)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                            MEI
                        </span>
                    @endif
                </div>

                <a href="/app/minha-empresa/configurar" data-link class="text-sm text-gray-400 hover:text-blue-600 transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Alterar empresa
                </a>
            </div>
        </div>

        {{-- KPI Strip — Score + Situação Cadastral + Créditos + Participantes --}}
        @php
            $scoreCor = 'gray';
            if ($score) {
                $scoreCor = match($score->classificacao) {
                    'baixo' => 'green',
                    'medio' => 'yellow',
                    'alto' => 'orange',
                    'critico' => 'red',
                    default => 'gray'
                };
            }
        @endphp

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-10">
            {{-- Card 1: Score de Risco --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Score de Risco</p>
                        @if($score)
                            <div class="flex items-baseline gap-1">
                                <p class="text-xl sm:text-3xl font-semibold text-{{ $scoreCor }}-600">{{ $score->score_total }}</p>
                                <span class="text-sm text-gray-400">/100</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1 sm:mt-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-{{ $scoreCor }}-100 text-{{ $scoreCor }}-800">
                                    {{ ucfirst($score->classificacao) }}
                                </span>
                                @if($score->ultima_consulta_em)
                                    <span class="text-xs text-gray-400 hidden sm:inline">{{ $score->ultima_consulta_em->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        @else
                            <p class="text-xl sm:text-3xl font-semibold text-gray-400">--</p>
                            <p class="text-xs text-gray-400 mt-1 sm:mt-2">Não avaliado</p>
                        @endif
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-{{ $scoreCor }}-50 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-{{ $scoreCor }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 2: Situação Cadastral --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Situação Cadastral</p>
                        @if($situacao === 'NÃO CONSULTADO')
                            <p class="text-xl sm:text-3xl font-semibold text-gray-400">--</p>
                            <p class="text-xs text-gray-400 mt-1 sm:mt-2">Não consultado</p>
                        @else
                            <p class="text-xl sm:text-3xl font-semibold text-{{ $situacaoCor }}-600 truncate">{{ $situacao }}</p>
                            @if($certidoes['simples_nacional'] === true || $certidoes['mei'] === true)
                                <div class="flex flex-wrap gap-1.5 mt-1 sm:mt-2">
                                    @if($certidoes['simples_nacional'] === true)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">Simples Nacional</span>
                                    @endif
                                    @if($certidoes['mei'] === true)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800">MEI</span>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs text-gray-400 mt-1 sm:mt-2">Receita Federal</p>
                            @endif
                        @endif
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-{{ $situacaoCor }}-50 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-{{ $situacaoCor }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 3: Créditos --}}
            @php $userCredits = Auth::user()->credits ?? 0; @endphp
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Créditos</p>
                        <p class="text-xl sm:text-3xl font-semibold {{ $userCredits > 0 ? 'text-green-600' : 'text-gray-900' }}">{{ number_format($userCredits, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400 mt-1 sm:mt-2">Disponíveis para consultas</p>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-green-50 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Card 4: Participantes & Notas --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-6 dash-animate" style="animation-delay: 0.4s">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1 sm:mb-2">Participantes & Notas</p>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-xl sm:text-3xl font-semibold text-gray-900">{{ number_format($totalParticipantes, 0, ',', '.') }}</span>
                            <span class="text-xs text-gray-400">part.</span>
                            <span class="text-gray-300 mx-0.5">/</span>
                            <span class="text-xl sm:text-3xl font-semibold text-gray-900">{{ number_format($totalNotas, 0, ',', '.') }}</span>
                            <span class="text-xs text-gray-400">notas</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1 sm:mt-2">Cadastrados no sistema</p>
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 bg-gray-100 rounded-lg flex items-center justify-center ml-2 sm:ml-4 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Dados da Empresa --}}
        @if($empresa->municipio || $empresa->uf || $empresa->telefone || $empresa->email)
        <div class="dash-animate bg-white rounded-lg border border-gray-200 mb-6 sm:mb-8" style="animation-delay: 0.5s">
            <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Dados da Empresa</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                    {{-- CNPJ --}}
                    <div class="flex items-start gap-3">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <div>
                            <span class="text-xs uppercase tracking-wide text-gray-400">CNPJ</span>
                            <p class="text-sm text-gray-900 font-mono">{{ $empresa->documento_formatado }}</p>
                        </div>
                    </div>

                    {{-- Localizacao --}}
                    @if($empresa->municipio || $empresa->uf)
                    <div class="flex items-start gap-3">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <div>
                            <span class="text-xs uppercase tracking-wide text-gray-400">Localização</span>
                            <p class="text-sm text-gray-900">
                                {{ implode(' - ', array_filter([$empresa->municipio, $empresa->uf])) }}
                            </p>
                        </div>
                    </div>
                    @endif

                    {{-- CEP --}}
                    @if($empresa->cep)
                    <div class="flex items-start gap-3">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <span class="text-xs uppercase tracking-wide text-gray-400">CEP</span>
                            <p class="text-sm text-gray-900 font-mono">{{ preg_replace('/(\d{5})(\d{3})/', '$1-$2', preg_replace('/\D/', '', $empresa->cep)) }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Telefone --}}
                    @if($empresa->telefone)
                    <div class="flex items-start gap-3">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <div>
                            <span class="text-xs uppercase tracking-wide text-gray-400">Telefone</span>
                            <p class="text-sm text-gray-900">{{ $empresa->telefone }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Email --}}
                    @if($empresa->email)
                    <div class="flex items-start gap-3">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                        </svg>
                        <div>
                            <span class="text-xs uppercase tracking-wide text-gray-400">Email</span>
                            <p class="text-sm text-gray-900">{{ $empresa->email }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Certidões & Última Consulta --}}
        @php
            $certidaoItems = [
                ['key' => 'cnd_federal', 'nome' => 'CND Federal'],
                ['key' => 'cnd_estadual', 'nome' => 'CND Estadual'],
                ['key' => 'fgts', 'nome' => 'CRF (FGTS)'],
                ['key' => 'cndt', 'nome' => 'CNDT'],
            ];

            $certidaoLinhas = [];
            foreach ($certidaoItems as $item) {
                $d = $certidoes[$item['key']];
                $consultado = $d['consultado'] ?? false;
                $status = strtoupper($d['status'] ?? '');
                $validade = $d['validade'] ?? null;
                $cor = 'gray';
                $label = 'Não consultado';

                if ($consultado && !empty($status)) {
                    if (in_array($status, ['NEGATIVA', 'REGULAR', 'REGULARIDADE'])) {
                        $cor = 'green'; $label = 'Negativa';
                    } elseif (str_contains($status, 'POSITIVA COM EFEITO') || str_contains($status, 'EFEITO DE NEGATIVA')) {
                        $cor = 'yellow'; $label = 'Positiva c/ Efeito';
                    } elseif (in_array($status, ['POSITIVA', 'IRREGULAR', 'IRREGULARIDADE'])) {
                        $cor = 'red'; $label = 'Positiva';
                    } else {
                        $cor = 'blue'; $label = $status;
                    }
                }

                $diasRestantes = null;
                if ($validade) {
                    try {
                        $diasRestantes = now()->diffInDays(\Carbon\Carbon::parse($validade), false);
                    } catch (\Exception $e) {}
                }

                $certidaoLinhas[] = [
                    'nome' => $item['nome'],
                    'cor' => $cor,
                    'label' => $label,
                    'validade' => $validade,
                    'diasRestantes' => $diasRestantes,
                ];
            }
        @endphp
        <div class="dash-animate bg-white rounded-lg border border-gray-200 mb-6 sm:mb-8" style="animation-delay: 0.6s">
            <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Certidões & Última Consulta</h2>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                    @foreach($certidaoLinhas as $linha)
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-2 h-2 rounded-full bg-{{ $linha['cor'] }}-500 flex-shrink-0"></span>
                                <span class="text-xs uppercase tracking-wide text-gray-400">{{ $linha['nome'] }}</span>
                            </div>
                            <p class="text-sm font-medium text-{{ $linha['cor'] }}-700">{{ $linha['label'] }}</p>
                            @if($linha['validade'] && $linha['diasRestantes'] !== null)
                                <p class="text-xs text-gray-400 mt-0.5">
                                    @if($linha['diasRestantes'] <= 0)
                                        <span class="text-red-600 font-medium">Vencida</span>
                                    @elseif($linha['diasRestantes'] <= 7)
                                        <span class="text-yellow-600 font-medium">Vence em {{ (int) $linha['diasRestantes'] }} dias</span>
                                    @else
                                        Val: {{ \Carbon\Carbon::parse($linha['validade'])->format('d/m/Y') }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    @endforeach

                    {{-- Última Consulta --}}
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0"></span>
                            <span class="text-xs uppercase tracking-wide text-gray-400">Última Consulta</span>
                        </div>
                        @if($ultimaConsulta)
                            <p class="text-sm font-medium text-gray-900">
                                {{ $ultimaConsulta->consultado_em ? $ultimaConsulta->consultado_em->format('d/m/Y H:i') : 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ implode(', ', $ultimaConsulta->getConsultasRealizadas()) ?: 'N/A' }}
                            </p>
                        @else
                            <p class="text-sm text-gray-400">Nenhuma consulta</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        <div class="dash-animate bg-white rounded-lg border border-gray-200 mb-6 sm:mb-8" style="animation-delay: 0.7s">
            <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Alertas</h2>
            </div>
            <div class="p-4 sm:p-5">
                @if(count($alertas) > 0)
                    <div class="space-y-3">
                        @foreach($alertas as $alerta)
                            @php
                                $alertaCor = match($alerta['tipo']) {
                                    'critico' => 'red',
                                    'atencao' => 'yellow',
                                    'info' => 'blue',
                                    default => 'gray'
                                };
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full bg-{{ $alertaCor }}-500 flex-shrink-0"></span>
                                <span class="text-sm text-gray-700">{{ $alerta['mensagem'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center gap-3 text-gray-400">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm">Nenhum alerta no momento</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Ações Rápidas — botões inline --}}
        <div class="dash-animate flex flex-wrap items-center gap-3" style="animation-delay: 0.8s">
            <a href="/app/consultas/nova{{ $participante ? '?participante=' . $participante->id : '' }}" data-link
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Atualizar Consultas
            </a>

            <a href="/app/minha-empresa/historico" data-link
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Histórico
            </a>

            @if($participante)
            <a href="/app/score-fiscal/participante/{{ $participante->id }}" data-link
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Score Fiscal
            </a>
            @endif

            @if($ultimaConsulta && $ultimaConsulta->lote)
            <a href="/app/consultas/lote/{{ $ultimaConsulta->lote->id }}/baixar"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Baixar Relatório
            </a>
            @endif
        </div>
    </div>
</div>
