{{-- Monitoramento - Dashboard --}}
<div class="min-h-screen bg-gray-50" id="monitoramento-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Monitoramento de Participantes</h1>
                    <p class="mt-1 text-sm text-gray-600">Acompanhe a situacao cadastral e fiscal dos seus fornecedores e parceiros.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        href="/app/importacao/efd"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                        data-link
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Importar do SPED
                    </a>
                    <a
                        href="/app/importacao/xml"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                        data-link
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Importar XMLs
                    </a>
                    <a
                        href="/app/monitoramento/avulso"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700"
                        data-link
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Consulta Avulsa
                    </a>
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {{-- Total Participantes --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalParticipantes ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Participantes</p>
                    </div>
                </div>
            </div>

            {{-- Monitoramentos Ativos --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $monitoramentosAtivos ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Monitoramentos Ativos</p>
                    </div>
                </div>
            </div>

            {{-- Alertas --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $alertas ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Alertas</p>
                    </div>
                </div>
            </div>

            {{-- Consultas este mes --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $consultasMes ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Consultas este mes</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Planos Disponiveis (Compacto) --}}
        <div class="mb-6">
            <div class="flex justify-center">
                <div class="inline-flex flex-col items-center gap-3 p-4 rounded-2xl bg-white border border-gray-200 shadow-sm">
                    {{-- Titulo --}}
                    <span class="text-sm font-medium text-gray-700">Planos Disponiveis</span>

                    {{-- Badges centralizados --}}
                    @php
                        $planosCompactos = [
                            ['codigo' => 'basico', 'nome' => 'Basico', 'creditos' => 0, 'cor' => 'green', 'descricao' => 'Dados cadastrais + Simples/MEI'],
                            ['codigo' => 'cadastral_plus', 'nome' => 'Cadastral+', 'creditos' => 3, 'cor' => 'blue', 'descricao' => 'Basico + SINTEGRA + TCU'],
                            ['codigo' => 'fiscal_federal', 'nome' => 'Fiscal Fed.', 'creditos' => 6, 'cor' => 'blue', 'descricao' => 'Cadastral+ + CND Federal + FGTS'],
                            ['codigo' => 'fiscal_completo', 'nome' => 'Fiscal Comp.', 'creditos' => 12, 'cor' => 'blue', 'descricao' => 'Fiscal Federal + CND Estadual + CNDT'],
                            ['codigo' => 'due_diligence', 'nome' => 'Due Diligence', 'creditos' => 16, 'cor' => 'purple', 'descricao' => 'Fiscal Completo + Lista PGFN'],
                            ['codigo' => 'esg', 'nome' => 'ESG', 'creditos' => 6, 'cor' => 'emerald', 'descricao' => 'Trabalho Escravo + IBAMA'],
                            ['codigo' => 'completo', 'nome' => 'Completo', 'creditos' => 22, 'cor' => 'amber', 'descricao' => 'Todas as consultas'],
                        ];
                    @endphp
                    <div class="flex items-center justify-center gap-2 flex-wrap">
                        @foreach($planosCompactos as $plano)
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium cursor-pointer shadow-sm hover:shadow-md hover:scale-105 transition-all
                                @if($plano['cor'] === 'green') bg-green-100 text-green-700 hover:bg-green-200
                                @elseif($plano['cor'] === 'blue') bg-blue-100 text-blue-700 hover:bg-blue-200
                                @elseif($plano['cor'] === 'purple') bg-purple-100 text-purple-700 hover:bg-purple-200
                                @elseif($plano['cor'] === 'emerald') bg-emerald-100 text-emerald-700 hover:bg-emerald-200
                                @elseif($plano['cor'] === 'amber') bg-amber-100 text-amber-700 hover:bg-amber-200
                                @endif"
                                data-plano="{{ $plano['codigo'] }}"
                                title="{{ $plano['descricao'] }}"
                            >
                                {{ $plano['nome'] }}
                                @if($plano['creditos'] === 0)
                                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    <span class="opacity-75">{{ $plano['creditos'] }}</span>
                                @endif
                            </span>
                        @endforeach
                    </div>

                    {{-- Texto explicativo --}}
                    <span class="text-xs text-gray-500">Clique em um plano para ver detalhes</span>
                </div>
            </div>
        </div>

        {{-- Card Resumo da Base --}}
        @if(($resumoBase['total'] ?? 0) > 0)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Resumo da Base</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Por Situação Cadastral --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Por Situação Cadastral</h3>
                    <div class="space-y-2">
                        @php
                            $total = $resumoBase['total'];
                            $ativas = $resumoBase['por_situacao']['ativas'] ?? 0;
                            $inaptas = $resumoBase['por_situacao']['inaptas'] ?? 0;
                            $outras = $resumoBase['por_situacao']['outras'] ?? 0;
                        @endphp
                        {{-- Barra Ativas --}}
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-600 w-14 shrink-0">Ativas</span>
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full" style="width: {{ $total > 0 ? ($ativas / $total * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 w-8 text-right shrink-0">{{ $ativas }}</span>
                        </div>
                        {{-- Barra Inaptas --}}
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-600 w-14 shrink-0" title="Inaptas, Suspensas ou Baixadas">Inaptas</span>
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-red-500 rounded-full" style="width: {{ $total > 0 ? ($inaptas / $total * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 w-8 text-right shrink-0">{{ $inaptas }}</span>
                        </div>
                        {{-- Barra Outras --}}
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-600 w-14 shrink-0" title="Sem situação definida">Outras</span>
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gray-400 rounded-full" style="width: {{ $total > 0 ? ($outras / $total * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 w-8 text-right shrink-0">{{ $outras }}</span>
                        </div>
                    </div>
                </div>

                {{-- Por Regime --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Por Regime Tributário</h3>
                    <div class="space-y-2">
                        @php
                            $regimes = [
                                'simples_nacional' => ['label' => 'Simples Nacional', 'color' => 'bg-blue-500'],
                                'lucro_presumido' => ['label' => 'Lucro Presumido', 'color' => 'bg-purple-500'],
                                'lucro_real' => ['label' => 'Lucro Real', 'color' => 'bg-amber-500'],
                            ];
                        @endphp
                        @foreach($regimes as $key => $config)
                            @php $count = $resumoBase['por_regime'][$key] ?? 0; @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-600 w-28 shrink-0">{{ $config['label'] }}</span>
                                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $config['color'] }} rounded-full" style="width: {{ $total > 0 ? ($count / $total * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 w-8 text-right shrink-0">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Top Estados --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Top Estados (UF)</h3>
                    <div class="space-y-2">
                        @forelse($resumoBase['top_ufs'] ?? [] as $uf => $count)
                            <div class="flex items-center gap-4 py-1">
                                <span class="text-sm font-medium text-gray-900 w-8">{{ $uf }}</span>
                                <span class="text-sm text-gray-600">{{ $count }} participantes</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">Sem dados de UF</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Lista de Participantes --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Meus Participantes</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            <strong class="text-gray-700">{{ $participantesStats['total'] ?? 0 }}</strong> total
                            <span class="text-gray-400 mx-2">&bull;</span>
                            <strong class="text-gray-700">{{ $participantesStats['ativos'] ?? 0 }}</strong> ativos
                            <span class="text-gray-400 mx-2">&bull;</span>
                            <strong class="text-gray-700">{{ $participantesStats['com_monitoramento'] ?? 0 }}</strong> monitorados
                            @if(($participantesStats['novos_mes'] ?? 0) > 0)
                                <span class="text-gray-400 mx-2">&bull;</span>
                                <strong class="text-gray-700">+{{ $participantesStats['novos_mes'] }}</strong> este mes
                            @endif
                            @if(($participantesStats['inaptos'] ?? 0) > 0)
                                <span class="text-gray-400 mx-2">&bull;</span>
                                <strong class="text-gray-700">{{ $participantesStats['inaptos'] }}</strong> inaptos
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Filtro por Grupo --}}
                        <select
                            id="filtro-grupo"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                        >
                            <option value="">Todos os grupos</option>
                            @foreach($grupos ?? [] as $grupo)
                                <option value="{{ $grupo->id }}" {{ ($grupoAtivo ?? '') == $grupo->id ? 'selected' : '' }}>
                                    {{ $grupo->nome }} ({{ $grupo->participantes_count }})
                                </option>
                            @endforeach
                        </select>

                        {{-- Botao Gerenciar Grupos --}}
                        <button
                            type="button"
                            id="btn-gerenciar-grupos"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors"
                            title="Gerenciar grupos"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Grupos
                        </button>

                        <div class="relative">
                            <input
                                type="text"
                                id="busca-participante"
                                placeholder="Buscar por CNPJ ou razao social..."
                                class="w-64 pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="select-all-participantes" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <select id="select-modo-selecao" class="text-[10px] border border-gray-300 rounded bg-gray-50 text-gray-500 focus:ring-1 focus:ring-blue-500 cursor-pointer px-0.5 py-0 hover:bg-gray-100 h-5 w-7" title="Selecionar múltiplos">
                                        <option value="">▾</option>
                                        <option value="pagina">Pág</option>
                                        <option value="todos">Todos</option>
                                        <option value="nenhum">Limpar</option>
                                    </select>
                                </div>
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">CNPJ</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Razao Social</th>
                            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-500 uppercase w-12">UF</th>
                            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Regime</th>
                            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Situacao</th>
                            <th class="px-2 py-2 text-center text-xs font-semibold text-gray-500 uppercase w-16">Score</th>
                            <th class="px-2 py-2 text-right text-xs font-semibold text-gray-500 uppercase w-24">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="participantes-tbody">
                        @forelse($participantes ?? [] as $participante)
                            @if(empty($participante->cnpj))
                                @continue
                            @endif
                            <tr class="hover:bg-gray-50 transition-colors" data-participante-id="{{ $participante->id }}">
                                {{-- Checkbox --}}
                                <td class="px-3 py-2">
                                    <input type="checkbox" class="participante-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $participante->id }}">
                                </td>

                                {{-- CNPJ --}}
                                <td class="px-3 py-2 text-xs font-mono text-gray-900 whitespace-nowrap">
                                    {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $participante->cnpj) }}
                                </td>

                                {{-- Razao Social --}}
                                <td class="px-3 py-2 text-sm text-gray-900 max-w-[220px] truncate" title="{{ $participante->razao_social ?? '' }}">
                                    {{ $participante->razao_social ?? '-' }}
                                </td>

                                {{-- UF --}}
                                <td class="px-2 py-2 text-center text-xs text-gray-600 w-12">
                                    {{ $participante->uf ?? '-' }}
                                </td>

                                {{-- Regime Tributario (badge compacto) --}}
                                <td class="px-2 py-2 text-center">
                                    @php
                                        $regimeBadges = [
                                            'simples_nacional' => ['label' => 'SN', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
                                            'simples nacional' => ['label' => 'SN', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
                                            'lucro_presumido' => ['label' => 'LP', 'bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
                                            'lucro presumido' => ['label' => 'LP', 'bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
                                            'lucro_real' => ['label' => 'LR', 'bg' => 'bg-amber-100', 'text' => 'text-amber-700'],
                                            'lucro real' => ['label' => 'LR', 'bg' => 'bg-amber-100', 'text' => 'text-amber-700'],
                                        ];
                                        $regimeKey = strtolower($participante->regime_tributario ?? '');
                                        $badge = $regimeBadges[$regimeKey] ?? ['label' => '-', 'bg' => 'bg-gray-100', 'text' => 'text-gray-500'];
                                    @endphp
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $badge['bg'] }} {{ $badge['text'] }}" title="{{ $participante->regime_tributario ?? 'Nao definido' }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </td>

                                {{-- Situacao (badge compacto) --}}
                                <td class="px-2 py-2 text-center">
                                    @if($participante->situacao_cadastral === 'ATIVA')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Ativa</span>
                                    @elseif($participante->situacao_cadastral === 'BAIXADA')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Baixada</span>
                                    @elseif($participante->situacao_cadastral === 'SUSPENSA')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">Suspensa</span>
                                    @elseif($participante->situacao_cadastral === 'INAPTA')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Inapta</span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $participante->situacao_cadastral ?? '-' }}</span>
                                    @endif
                                </td>

                                {{-- Score (placeholder para implementacao futura) --}}
                                <td class="px-2 py-2 text-center">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-400 cursor-not-allowed"
                                        title="Score em breve"
                                        disabled
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        --
                                    </button>
                                </td>

                                {{-- Acoes --}}
                                <td class="px-2 py-2">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Grupos (badge) --}}
                                        @if($participante->grupos->count() > 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700" title="{{ $participante->grupos->pluck('nome')->join(', ') }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                                {{ $participante->grupos->count() }}
                                            </span>
                                        @endif

                                        {{-- Consultar / Monitorar --}}
                                        <button
                                            type="button"
                                            class="btn-monitorar-participante inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors"
                                            data-participante-id="{{ $participante->id }}"
                                            data-participante-cnpj="{{ $participante->cnpj }}"
                                            data-tem-plano="{{ $participante->monitoramento_ativo ? '1' : '0' }}"
                                            title="{{ $participante->monitoramento_ativo ? 'Consultar agora' : 'Configurar monitoramento' }}"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                            </svg>
                                            {{ $participante->monitoramento_ativo ? 'Consultar' : 'Monitorar' }}
                                        </button>

                                        {{-- Ver detalhes --}}
                                        <a
                                            href="/app/participante/{{ $participante->id }}"
                                            class="text-xs font-medium hover:underline transition-colors"
                                            style="color: #2563eb;"
                                            data-link
                                        >
                                            Ver
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum participante cadastrado</h3>
                                        <p class="text-sm text-gray-600 mb-4">Importe participantes de um SPED ou adicione CNPJs manualmente.</p>
                                        <div class="flex items-center gap-3">
                                            <a
                                                href="/app/importacao/efd"
                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50"
                                                data-link
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                </svg>
                                                Importar do SPED
                                            </a>
                                            <a
                                                href="/app/monitoramento/avulso"
                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700"
                                                data-link
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Adicionar CNPJ
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(isset($participantes) && $participantes->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $participantes->links() }}
                </div>
            @endif
        </div>

        {{-- Acoes em massa (aparece quando seleciona participantes) --}}
        <div id="acoes-massa" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 bg-white border border-gray-200 text-gray-900 rounded-xl shadow-2xl px-6 py-4 z-50">
            <div class="flex items-center gap-4">
                <span class="text-sm"><strong id="count-selecionados">0</strong> participante(s) selecionado(s)</span>
                <div class="h-6 w-px bg-gray-300"></div>
                <button type="button" id="btn-criar-monitoramento" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold transition hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    Criar Monitoramento
                </button>
                <button type="button" id="btn-consulta-avulsa-massa" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold transition hover:bg-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Consultar Agora
                </button>
                <button type="button" id="btn-adicionar-grupo" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-semibold transition hover:bg-purple-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    Adicionar ao Grupo
                </button>
                <button type="button" id="btn-cancelar-selecao" class="inline-flex items-center p-2 rounded-lg text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Monitorar Participante Individual --}}
<div id="modal-monitorar-individual" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" id="modal-monitorar-titulo">Configurar Monitoramento</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-4">
            {{-- Info do participante --}}
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 mb-1">Participante</p>
                <p class="text-sm font-mono font-semibold text-gray-900" id="modal-monitorar-cnpj">00.000.000/0001-00</p>
                <p class="text-sm text-gray-600" id="modal-monitorar-razao">Razao Social</p>
            </div>

            {{-- Selecao de plano --}}
            <p class="text-sm font-medium text-gray-700 mb-3">Selecione o plano de monitoramento:</p>
            <div class="space-y-2" id="modal-monitorar-planos">
                @php
                    $planosDisponiveis = [
                        ['id' => 'basico', 'nome' => 'Basico', 'creditos' => 0, 'gratuito' => true, 'descricao' => 'Dados cadastrais + Simples/MEI'],
                        ['id' => 'cadastral_plus', 'nome' => 'Cadastral+', 'creditos' => 3, 'gratuito' => false, 'descricao' => 'Basico + SINTEGRA + TCU Consolidada'],
                        ['id' => 'fiscal_federal', 'nome' => 'Fiscal Federal', 'creditos' => 6, 'gratuito' => false, 'descricao' => 'Cadastral+ + CND Federal + CRF FGTS'],
                        ['id' => 'fiscal_completo', 'nome' => 'Fiscal Completo', 'creditos' => 12, 'gratuito' => false, 'descricao' => 'Fiscal Federal + CND Estadual + CNDT'],
                        ['id' => 'due_diligence', 'nome' => 'Due Diligence', 'creditos' => 16, 'gratuito' => false, 'descricao' => 'Fiscal Completo + Lista Devedores PGFN'],
                        ['id' => 'esg', 'nome' => 'ESG', 'creditos' => 6, 'gratuito' => false, 'descricao' => 'Trabalho Escravo + IBAMA Autuacoes'],
                        ['id' => 'completo', 'nome' => 'Completo', 'creditos' => 22, 'gratuito' => false, 'descricao' => 'Todas as consultas disponiveis'],
                    ];
                @endphp
                @foreach($planosDisponiveis as $plano)
                    <label class="plano-option flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="plano_selecionado" value="{{ $plano['id'] }}" data-creditos="{{ $plano['creditos'] }}" class="text-blue-600 focus:ring-blue-500">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">{{ $plano['nome'] }}</span>
                                @if($plano['gratuito'])
                                    <span class="px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Gratis</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $plano['creditos'] }} cred.</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $plano['descricao'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- Resumo --}}
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Frequencia:</span>
                    <span class="font-medium text-gray-900">Mensal (30 dias)</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600">Custo por consulta:</span>
                    <span class="font-semibold text-blue-600" id="modal-monitorar-custo">0 creditos</span>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
            <button type="button" class="modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                Cancelar
            </button>
            <button type="button" id="btn-confirmar-monitorar" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Ativar Monitoramento
            </button>
        </div>
    </div>
</div>
<input type="hidden" id="modal-monitorar-participante-id" value="">

{{-- Modal Criar Monitoramento --}}
<div id="modal-criar-monitoramento" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Criar Monitoramento</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-4">
            <p class="text-sm text-gray-600 mb-4">Selecione o plano de monitoramento para os <strong id="modal-count-participantes">0</strong> participante(s) selecionado(s).</p>

            <div class="space-y-3" id="modal-planos-lista">
                {{-- Planos serao listados aqui --}}
            </div>

            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Frequencia:</span>
                    <span class="font-semibold text-gray-900">Mensal (30 dias)</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-2">
                    <span class="text-gray-600">Custo mensal estimado:</span>
                    <span class="font-semibold text-blue-600" id="modal-custo-estimado">0 creditos</span>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
            <button type="button" class="modal-close px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                Cancelar
            </button>
            <button type="button" id="btn-confirmar-monitoramento" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                Confirmar Monitoramento
            </button>
        </div>
    </div>
</div>

{{-- Modal Gerenciar Grupos --}}
<div id="modal-gerenciar-grupos" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Gerenciar Grupos</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-4">
            {{-- Formulario para criar novo grupo --}}
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Criar novo grupo</h4>
                <div class="flex gap-2">
                    <input
                        type="text"
                        id="novo-grupo-nome"
                        placeholder="Nome do grupo"
                        class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <input
                        type="color"
                        id="novo-grupo-cor"
                        value="#3B82F6"
                        class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer"
                        title="Cor do grupo"
                    >
                    <button
                        type="button"
                        id="btn-criar-grupo"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold transition hover:bg-blue-700"
                    >
                        Criar
                    </button>
                </div>
                {{-- Cores predefinidas --}}
                <div class="flex gap-2 mt-2">
                    @foreach($coresPredefinidas ?? ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#6B7280'] as $cor)
                        <button
                            type="button"
                            class="cor-predefinida w-6 h-6 rounded-full border-2 border-transparent hover:border-gray-400 transition-colors"
                            style="background-color: {{ $cor }}"
                            data-cor="{{ $cor }}"
                            title="{{ $cor }}"
                        ></button>
                    @endforeach
                </div>
            </div>

            {{-- Lista de grupos existentes --}}
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Grupos existentes</h4>
                <div id="lista-grupos" class="space-y-2 max-h-64 overflow-y-auto">
                    @forelse($grupos ?? [] as $grupo)
                        <div class="grupo-item flex items-center justify-between p-3 bg-gray-50 rounded-lg" data-grupo-id="{{ $grupo->id }}">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-4 h-4 rounded-full"
                                    style="background-color: {{ $grupo->cor }}"
                                ></span>
                                <span class="grupo-nome text-sm font-medium text-gray-900">{{ $grupo->nome }}</span>
                                <span class="text-xs text-gray-500">({{ $grupo->participantes_count }} participantes)</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    class="btn-editar-grupo p-1.5 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                    title="Editar"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="btn-excluir-grupo p-1.5 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors"
                                    title="Excluir"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">Nenhum grupo criado ainda.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button type="button" class="modal-close w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                Fechar
            </button>
        </div>
    </div>
</div>

{{-- Modal Selecionar Grupo (para acao em massa) --}}
<div id="modal-selecionar-grupo" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Adicionar ao Grupo</h3>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-4">
            <p class="text-sm text-gray-600 mb-4">Selecione o grupo para adicionar <strong id="modal-grupo-count">0</strong> participante(s):</p>

            <div id="lista-grupos-selecao" class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($grupos ?? [] as $grupo)
                    <button
                        type="button"
                        class="grupo-selecao-item w-full flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition-colors text-left"
                        data-grupo-id="{{ $grupo->id }}"
                    >
                        <span
                            class="w-4 h-4 rounded-full shrink-0"
                            style="background-color: {{ $grupo->cor }}"
                        ></span>
                        <span class="text-sm font-medium text-gray-900">{{ $grupo->nome }}</span>
                        <span class="text-xs text-gray-500">({{ $grupo->participantes_count }})</span>
                    </button>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Nenhum grupo disponivel. Crie um grupo primeiro.</p>
                @endforelse
            </div>

            @if(empty($grupos) || count($grupos ?? []) === 0)
                <button
                    type="button"
                    id="btn-criar-grupo-rapido"
                    class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-dashed border-gray-300 text-gray-600 text-sm font-medium hover:border-blue-500 hover:text-blue-600 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Criar novo grupo
                </button>
            @endif
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button type="button" class="modal-close w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm transition hover:bg-gray-50">
                Cancelar
            </button>
        </div>
    </div>
</div>

{{-- Modal Detalhes do Plano --}}
<div id="modal-detalhes-plano" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-semibold text-gray-900" id="modal-plano-nome">Plano</h3>
                    <span id="modal-plano-badge" class="px-2 py-1 rounded text-xs font-medium">0 cred</span>
                </div>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-6 py-4">
            <p class="text-sm text-gray-600 mb-4" id="modal-plano-descricao">Descricao do plano</p>

            {{-- Secao 1: Consultas incluidas --}}
            <div class="mb-5">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Consultas incluidas:</h4>
                <ul id="modal-plano-consultas" class="space-y-1 text-sm text-gray-600">
                    {{-- Lista de consultas sera preenchida via JS --}}
                </ul>
            </div>

            {{-- Secao 2: Casos de Uso --}}
            <div class="mb-5" id="modal-plano-casos-uso">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Ideal para:</h4>

                {{-- Card Contador --}}
                <div class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg mb-2">
                    <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <span class="font-medium text-blue-900 text-sm">Contadores</span>
                        <p class="text-sm text-blue-700 mt-0.5" id="modal-plano-caso-contador">-</p>
                    </div>
                </div>

                {{-- Card Empresario --}}
                <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg">
                    <svg class="w-5 h-5 text-green-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <span class="font-medium text-green-900 text-sm">Empresarios</span>
                        <p class="text-sm text-green-700 mt-0.5" id="modal-plano-caso-empresario">-</p>
                    </div>
                </div>
            </div>

            {{-- Secao 3: Exemplo Pratico --}}
            <div class="mb-5 p-3 bg-amber-50 border border-amber-200 rounded-lg" id="modal-plano-exemplo-container">
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    <div>
                        <span class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Exemplo pratico</span>
                        <p class="text-sm text-amber-900 mt-1" id="modal-plano-exemplo">-</p>
                    </div>
                </div>
            </div>

            {{-- Secao 4: Calculadora de Custo --}}
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg" id="modal-plano-calculadora">
                <h4 class="text-sm font-semibold text-gray-800 mb-3">Calcular custo mensal</h4>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    {{-- Input: Quantidade de CNPJs --}}
                    <div>
                        <label for="calc-qtd-cnpjs" class="block text-xs text-gray-600 mb-1">Quantidade de CNPJs</label>
                        <input type="number" id="calc-qtd-cnpjs" min="1" value="10"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    {{-- Select: Frequencia --}}
                    <div>
                        <label for="calc-frequencia" class="block text-xs text-gray-600 mb-1">Frequencia</label>
                        <select id="calc-frequencia" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="30">Mensal</option>
                            <option value="15">Quinzenal</option>
                            <option value="7">Semanal</option>
                        </select>
                    </div>
                </div>

                {{-- Resultado --}}
                <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg">
                    <span class="text-sm text-gray-600">Custo mensal estimado:</span>
                    <div class="text-right">
                        <span id="calc-resultado" class="text-lg font-bold text-blue-600">0</span>
                        <span class="text-sm text-gray-500">creditos</span>
                        <p class="text-xs text-gray-400">= R$ <span id="calc-valor-reais">0,00</span></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button type="button" class="modal-close w-full px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-semibold shadow-sm hover:bg-gray-50">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initMonitoramento() {
        const container = document.getElementById('monitoramento-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento] Inicializando...');

        const selectAllCheckbox = document.getElementById('select-all-participantes');
        const participanteCheckboxes = document.querySelectorAll('.participante-checkbox');
        const acoesMassa = document.getElementById('acoes-massa');
        const countSelecionados = document.getElementById('count-selecionados');
        const btnCancelarSelecao = document.getElementById('btn-cancelar-selecao');
        const btnCriarMonitoramento = document.getElementById('btn-criar-monitoramento');
        const modalCriarMonitoramento = document.getElementById('modal-criar-monitoramento');
        const buscaInput = document.getElementById('busca-participante');

        // Funcao para atualizar UI de selecao
        function atualizarSelecao() {
            const selecionados = document.querySelectorAll('.participante-checkbox:checked');
            const count = selecionados.length;

            if (count > 0) {
                acoesMassa.classList.remove('hidden');
                countSelecionados.textContent = count;
            } else {
                acoesMassa.classList.add('hidden');
            }

            // Atualizar checkbox "selecionar todos"
            if (selectAllCheckbox) {
                const total = participanteCheckboxes.length;
                selectAllCheckbox.checked = count === total && total > 0;
                selectAllCheckbox.indeterminate = count > 0 && count < total;
            }
        }

        // Event listener para checkboxes individuais
        participanteCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', atualizarSelecao);
        });

        // Event listener para "selecionar todos"
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                participanteCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                atualizarSelecao();
            });
        }

        // Cancelar selecao
        if (btnCancelarSelecao) {
            btnCancelarSelecao.addEventListener('click', function() {
                participanteCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = false;
                });
                if (selectAllCheckbox) selectAllCheckbox.checked = false;
                // Resetar dropdown
                const selectModo = document.getElementById('select-modo-selecao');
                if (selectModo) selectModo.value = '';
                atualizarSelecao();
            });
        }

        // Dropdown de selecao (Pagina, Todos, Nenhum)
        const selectModoSelecao = document.getElementById('select-modo-selecao');
        if (selectModoSelecao) {
            selectModoSelecao.addEventListener('change', function() {
                const modo = selectModoSelecao.value;

                if (modo === 'pagina') {
                    // Seleciona apenas os visiveis (nao ocultos por busca)
                    participanteCheckboxes.forEach(function(checkbox) {
                        const row = checkbox.closest('tr');
                        checkbox.checked = row && row.style.display !== 'none';
                    });
                } else if (modo === 'todos') {
                    // Seleciona todos
                    participanteCheckboxes.forEach(function(checkbox) {
                        checkbox.checked = true;
                    });
                } else if (modo === 'nenhum') {
                    // Desmarca todos
                    participanteCheckboxes.forEach(function(checkbox) {
                        checkbox.checked = false;
                    });
                }

                // Resetar dropdown para "--"
                selectModoSelecao.value = '';

                atualizarSelecao();
            });
        }

        // Abrir modal de criar monitoramento
        if (btnCriarMonitoramento && modalCriarMonitoramento) {
            btnCriarMonitoramento.addEventListener('click', function() {
                const selecionados = document.querySelectorAll('.participante-checkbox:checked');
                document.getElementById('modal-count-participantes').textContent = selecionados.length;
                modalCriarMonitoramento.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
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
        if (modalCriarMonitoramento) {
            modalCriarMonitoramento.addEventListener('click', function(e) {
                if (e.target === modalCriarMonitoramento) {
                    modalCriarMonitoramento.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        }

        // Busca de participantes
        if (buscaInput) {
            let debounceTimer;
            buscaInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    const termo = buscaInput.value.toLowerCase().trim();
                    const linhas = document.querySelectorAll('#participantes-tbody tr[data-participante-id]');

                    linhas.forEach(function(linha) {
                        const cnpj = linha.querySelector('td:nth-child(2)').textContent.toLowerCase();
                        const razaoSocial = linha.querySelector('td:nth-child(3)').textContent.toLowerCase();

                        if (termo === '' || cnpj.includes(termo) || razaoSocial.includes(termo)) {
                            linha.style.display = '';
                        } else {
                            linha.style.display = 'none';
                        }
                    });
                }, 300);
            });
        }

        // =====================================================
        // GRUPOS - Filtro, Modais e CRUD
        // =====================================================

        const filtroGrupo = document.getElementById('filtro-grupo');
        const btnGerenciarGrupos = document.getElementById('btn-gerenciar-grupos');
        const btnAdicionarGrupo = document.getElementById('btn-adicionar-grupo');
        const modalGerenciarGrupos = document.getElementById('modal-gerenciar-grupos');
        const modalSelecionarGrupo = document.getElementById('modal-selecionar-grupo');
        const btnCriarGrupo = document.getElementById('btn-criar-grupo');
        const novoGrupoNome = document.getElementById('novo-grupo-nome');
        const novoGrupoCor = document.getElementById('novo-grupo-cor');

        // Filtro por grupo - redireciona com query param
        if (filtroGrupo) {
            filtroGrupo.addEventListener('change', function() {
                const grupoId = filtroGrupo.value;
                const url = new URL(window.location.href);

                if (grupoId) {
                    url.searchParams.set('grupo', grupoId);
                } else {
                    url.searchParams.delete('grupo');
                }

                window.location.href = url.toString();
            });
        }

        // Abrir modal gerenciar grupos
        if (btnGerenciarGrupos && modalGerenciarGrupos) {
            btnGerenciarGrupos.addEventListener('click', function() {
                modalGerenciarGrupos.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });

            modalGerenciarGrupos.addEventListener('click', function(e) {
                if (e.target === modalGerenciarGrupos) {
                    modalGerenciarGrupos.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        }

        // Cores predefinidas
        document.querySelectorAll('.cor-predefinida').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (novoGrupoCor) {
                    novoGrupoCor.value = btn.dataset.cor;
                }
            });
        });

        // Criar grupo
        if (btnCriarGrupo) {
            btnCriarGrupo.addEventListener('click', async function() {
                const nome = novoGrupoNome ? novoGrupoNome.value.trim() : '';
                const cor = novoGrupoCor ? novoGrupoCor.value : '#3B82F6';

                if (!nome) {
                    alert('Digite o nome do grupo.');
                    return;
                }

                try {
                    btnCriarGrupo.disabled = true;
                    btnCriarGrupo.textContent = 'Criando...';

                    const response = await fetch('/app/monitoramento/grupos', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ nome, cor }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Recarregar pagina para atualizar lista
                        window.location.reload();
                    } else {
                        alert(data.error || 'Erro ao criar grupo.');
                    }
                } catch (error) {
                    console.error('Erro ao criar grupo:', error);
                    alert('Erro ao criar grupo. Tente novamente.');
                } finally {
                    btnCriarGrupo.disabled = false;
                    btnCriarGrupo.textContent = 'Criar';
                }
            });
        }

        // Excluir grupo
        document.querySelectorAll('.btn-excluir-grupo').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const grupoItem = btn.closest('.grupo-item');
                const grupoId = grupoItem ? grupoItem.dataset.grupoId : null;
                const grupoNome = grupoItem ? grupoItem.querySelector('.grupo-nome').textContent : '';

                if (!grupoId) return;

                if (!confirm('Tem certeza que deseja excluir o grupo "' + grupoNome + '"? Os participantes nao serao excluidos.')) {
                    return;
                }

                try {
                    const response = await fetch('/app/monitoramento/grupos/' + grupoId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.error || 'Erro ao excluir grupo.');
                    }
                } catch (error) {
                    console.error('Erro ao excluir grupo:', error);
                    alert('Erro ao excluir grupo. Tente novamente.');
                }
            });
        });

        // Abrir modal selecionar grupo (acao em massa)
        if (btnAdicionarGrupo && modalSelecionarGrupo) {
            btnAdicionarGrupo.addEventListener('click', function() {
                const selecionados = document.querySelectorAll('.participante-checkbox:checked');
                document.getElementById('modal-grupo-count').textContent = selecionados.length;
                modalSelecionarGrupo.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });

            modalSelecionarGrupo.addEventListener('click', function(e) {
                if (e.target === modalSelecionarGrupo) {
                    modalSelecionarGrupo.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        }

        // Selecionar grupo para adicionar participantes
        document.querySelectorAll('.grupo-selecao-item').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const grupoId = btn.dataset.grupoId;
                const selecionados = document.querySelectorAll('.participante-checkbox:checked');
                const participanteIds = Array.from(selecionados).map(function(cb) {
                    return cb.value;
                });

                if (participanteIds.length === 0) {
                    alert('Nenhum participante selecionado.');
                    return;
                }

                try {
                    btn.disabled = true;
                    btn.style.opacity = '0.5';

                    const response = await fetch('/app/participantes/associar-grupo', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            grupo_id: grupoId,
                            participantes: participanteIds,
                            acao: 'adicionar',
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.error || 'Erro ao adicionar participantes ao grupo.');
                    }
                } catch (error) {
                    console.error('Erro ao associar grupo:', error);
                    alert('Erro ao adicionar ao grupo. Tente novamente.');
                } finally {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                }
            });
        });

        // Botao criar grupo rapido (quando nao tem grupos)
        const btnCriarGrupoRapido = document.getElementById('btn-criar-grupo-rapido');
        if (btnCriarGrupoRapido) {
            btnCriarGrupoRapido.addEventListener('click', function() {
                if (modalSelecionarGrupo) {
                    modalSelecionarGrupo.classList.add('hidden');
                }
                if (modalGerenciarGrupos) {
                    modalGerenciarGrupos.classList.remove('hidden');
                    if (novoGrupoNome) novoGrupoNome.focus();
                }
            });
        }

        // =====================================================
        // MONITORAR PARTICIPANTE INDIVIDUAL
        // =====================================================

        const modalMonitorarIndividual = document.getElementById('modal-monitorar-individual');
        const modalMonitorarTitulo = document.getElementById('modal-monitorar-titulo');
        const modalMonitorarCnpj = document.getElementById('modal-monitorar-cnpj');
        const modalMonitorarRazao = document.getElementById('modal-monitorar-razao');
        const modalMonitorarCusto = document.getElementById('modal-monitorar-custo');
        const modalMonitorarParticipanteId = document.getElementById('modal-monitorar-participante-id');
        const btnConfirmarMonitorar = document.getElementById('btn-confirmar-monitorar');

        // Atualizar custo quando selecionar plano
        document.querySelectorAll('input[name="plano_selecionado"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const creditos = radio.dataset.creditos;
                if (modalMonitorarCusto) {
                    modalMonitorarCusto.textContent = creditos + ' creditos';
                }
                if (btnConfirmarMonitorar) {
                    btnConfirmarMonitorar.disabled = false;
                }
            });
        });

        // Click nos botoes de monitorar
        document.querySelectorAll('.btn-monitorar-participante').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const participanteId = btn.dataset.participanteId;
                const cnpj = btn.dataset.participanteCnpj;
                const temPlano = btn.dataset.temPlano === '1';
                const row = btn.closest('tr');
                const razaoSocial = row ? row.querySelector('td:nth-child(3)').textContent.trim() : '';

                // Formatar CNPJ
                const cnpjFormatado = cnpj ? cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5') : '';

                if (temPlano) {
                    // Ja tem plano - executar consulta diretamente (ou confirmar)
                    if (confirm('Executar consulta agora para este participante?\n\nCNPJ: ' + cnpjFormatado)) {
                        executarConsulta(participanteId);
                    }
                } else {
                    // Nao tem plano - abrir modal para selecionar
                    if (modalMonitorarIndividual) {
                        if (modalMonitorarCnpj) modalMonitorarCnpj.textContent = cnpjFormatado;
                        if (modalMonitorarRazao) modalMonitorarRazao.textContent = razaoSocial || '-';
                        if (modalMonitorarParticipanteId) modalMonitorarParticipanteId.value = participanteId;
                        if (modalMonitorarTitulo) modalMonitorarTitulo.textContent = 'Configurar Monitoramento';
                        if (btnConfirmarMonitorar) {
                            btnConfirmarMonitorar.textContent = 'Ativar Monitoramento';
                            btnConfirmarMonitorar.disabled = true;
                        }
                        if (modalMonitorarCusto) modalMonitorarCusto.textContent = '0 creditos';

                        // Limpar selecao anterior
                        document.querySelectorAll('input[name="plano_selecionado"]').forEach(function(r) {
                            r.checked = false;
                        });

                        modalMonitorarIndividual.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                }
            });
        });

        // Fechar modal monitorar individual clicando fora
        if (modalMonitorarIndividual) {
            modalMonitorarIndividual.addEventListener('click', function(e) {
                if (e.target === modalMonitorarIndividual) {
                    modalMonitorarIndividual.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        }

        // Confirmar monitoramento
        if (btnConfirmarMonitorar) {
            btnConfirmarMonitorar.addEventListener('click', async function() {
                const participanteId = modalMonitorarParticipanteId ? modalMonitorarParticipanteId.value : null;
                const planoSelecionado = document.querySelector('input[name="plano_selecionado"]:checked');

                if (!participanteId || !planoSelecionado) {
                    alert('Selecione um plano de monitoramento.');
                    return;
                }

                try {
                    btnConfirmarMonitorar.disabled = true;
                    btnConfirmarMonitorar.textContent = 'Ativando...';

                    const response = await fetch('/app/participante/' + participanteId + '/ativar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            plano: planoSelecionado.value,
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Fechar modal e atualizar pagina
                        if (modalMonitorarIndividual) {
                            modalMonitorarIndividual.classList.add('hidden');
                            document.body.style.overflow = '';
                        }
                        window.location.reload();
                    } else {
                        alert(data.error || 'Erro ao ativar monitoramento.');
                    }
                } catch (error) {
                    console.error('Erro ao ativar monitoramento:', error);
                    alert('Erro ao ativar monitoramento. Tente novamente.');
                } finally {
                    btnConfirmarMonitorar.disabled = false;
                    btnConfirmarMonitorar.textContent = 'Ativar Monitoramento';
                }
            });
        }

        // Funcao para executar consulta
        async function executarConsulta(participanteId) {
            try {
                const response = await fetch('/app/participante/' + participanteId + '/consultar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    alert('Consulta iniciada com sucesso! Os resultados serao atualizados em breve.');
                    window.location.reload();
                } else {
                    alert(data.error || 'Erro ao executar consulta.');
                }
            } catch (error) {
                console.error('Erro ao executar consulta:', error);
                alert('Erro ao executar consulta. Tente novamente.');
            }
        }

        // =====================================================
        // MODAL DETALHES DO PLANO
        // =====================================================

        const planosDetalhes = {
            basico: {
                nome: 'Basico',
                creditos: 0,
                gratuito: true,
                descricao: 'Consulta gratuita com dados cadastrais completos da Receita Federal e situacao no Simples Nacional.',
                consultas: ['Situacao Cadastral', 'Dados Cadastrais', 'Endereco', 'CNAEs', 'QSA (Socios)', 'Simples Nacional', 'MEI'],
                casosUso: {
                    contador: 'Consulta rapida de situacao cadastral ao receber novo cliente. Verificar se empresa esta ativa e qual o regime tributario.',
                    empresario: 'Validar dados basicos de um potencial fornecedor ou parceiro antes de iniciar negociacao.',
                },
                exemploPratico: 'Um escritorio contabil recebe proposta de novo cliente. Com o plano Basico, verifica em segundos se o CNPJ esta ativo e se e optante do Simples Nacional.'
            },
            cadastral_plus: {
                nome: 'Cadastral+',
                creditos: 3,
                gratuito: false,
                descricao: 'Inclui dados do plano Basico mais consultas em listas restritivas e SINTEGRA.',
                consultas: ['Tudo do Basico', 'SINTEGRA (inscricao estadual)', 'TCU Consolidada (CEIS, CNEP, CNJ)'],
                casosUso: {
                    contador: 'Validar Inscricao Estadual para operacoes interestaduais e verificar se cliente esta em lista de impedimentos.',
                    empresario: 'Verificar se fornecedor pode participar de licitacoes (TCU/CEIS) e se tem IE ativa para emitir NF-e.',
                },
                exemploPratico: 'Empresa de logistica precisa verificar se transportadora contratada esta regular no SINTEGRA de todos estados onde opera e se nao esta impedida de licitar.'
            },
            fiscal_federal: {
                nome: 'Fiscal Federal',
                creditos: 6,
                gratuito: false,
                descricao: 'Inclui Cadastral+ mais certidoes negativas federais.',
                consultas: ['Tudo do Cadastral+', 'CND Federal (PGFN/RFB)', 'CRF FGTS'],
                casosUso: {
                    contador: 'Obter certidoes exigidas em licitacoes: CND Federal (PGFN/RFB) e CRF do FGTS sao requisitos basicos de editais.',
                    empresario: 'Exigir CNDs de fornecedores como pre-requisito contratual. Protege contra responsabilidade solidaria.',
                },
                exemploPratico: 'Escritorio prepara documentacao para cliente participar de pregao. Com Fiscal Federal, obtem as duas certidoes federais necessarias automaticamente.'
            },
            fiscal_completo: {
                nome: 'Fiscal Completo',
                creditos: 12,
                gratuito: false,
                descricao: 'Inclui Fiscal Federal mais certidoes estaduais e trabalhistas.',
                consultas: ['Tudo do Fiscal Federal', 'CND Estadual', 'CNDT (Certidao Trabalhista)'],
                casosUso: {
                    contador: 'Kit completo para licitacoes: Federal + Estadual + Trabalhista. Atende 100% dos editais publicos.',
                    empresario: 'Due diligence completa de fornecedores para atender Lei Anticorrupcao (Lei 12.846/13). Prova de diligencia em caso de auditoria.',
                },
                exemploPratico: 'Grande empresa precisa qualificar 50 fornecedores para renovacao de contratos. Com Fiscal Completo, obtem todas as certidoes de uma vez.'
            },
            due_diligence: {
                nome: 'Due Diligence',
                creditos: 16,
                gratuito: false,
                descricao: 'Inclui Fiscal Completo mais lista detalhada de devedores.',
                consultas: ['Tudo do Fiscal Completo', 'Lista Devedores PGFN (valor da divida)'],
                casosUso: {
                    contador: 'Analise de risco financeiro de clientes: saber valor exato de divida federal, nao apenas se existe.',
                    empresario: 'Avaliacao pre-aquisicao de empresa: conhecer passivo fiscal detalhado antes de M&A.',
                },
                exemploPratico: 'Investidor analisa compra de empresa. Lista PGFN mostra divida de R$ 2,3 milhoes - informacao crucial para negociacao.'
            },
            esg: {
                nome: 'ESG',
                creditos: 6,
                gratuito: false,
                descricao: 'Consultas de compliance ambiental e trabalhista.',
                consultas: ['Cadastro de Trabalho Escravo', 'IBAMA Autuacoes'],
                casosUso: {
                    contador: 'Compliance de fornecedores para clientes que precisam reportar ESG. Trabalho escravo + IBAMA.',
                    empresario: 'Evitar associacao com fornecedores na "lista suja" do trabalho escravo ou com autuacoes ambientais.',
                },
                exemploPratico: 'Industria textil precisa garantir que nenhum fornecedor esta na lista de trabalho escravo para manter certificacao de compliance.'
            },
            completo: {
                nome: 'Completo',
                creditos: 22,
                gratuito: false,
                descricao: 'Pacote completo com todas as consultas disponiveis.',
                consultas: ['Todas as consultas dos demais planos'],
                casosUso: {
                    contador: 'Gestao de carteira de clientes: monitoramento mensal completo de todas as obrigacoes fiscais e compliance.',
                    empresario: 'Programa de gestao de riscos de terceiros: monitoramento continuo de todos os fornecedores ativos.',
                },
                exemploPratico: 'Escritorio contabil monitora 200 clientes mensalmente. Recebe alertas automaticos quando qualquer certidao vence ou surgem pendencias.'
            }
        };

        // Variavel para armazenar creditos do plano atual (para calculadora)
        let planoAtualCreditos = 0;

        // Funcao para calcular custo mensal
        function calcularCusto() {
            const qtd = parseInt(document.getElementById('calc-qtd-cnpjs').value) || 0;
            const freqDias = parseInt(document.getElementById('calc-frequencia').value) || 30;

            // Quantas consultas por mes: 30 dias / frequencia
            const consultasPorMes = Math.floor(30 / freqDias);

            // Custo total = CNPJs x consultas/mes x creditos/consulta
            const custoMensal = qtd * consultasPorMes * planoAtualCreditos;

            // R$ 1,00 por credito
            const valorReais = custoMensal.toFixed(2).replace('.', ',');

            document.getElementById('calc-resultado').textContent = custoMensal;
            document.getElementById('calc-valor-reais').textContent = valorReais;
        }

        // Event listeners para recalcular
        ['calc-qtd-cnpjs', 'calc-frequencia'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', calcularCusto);
                el.addEventListener('change', calcularCusto);
            }
        });

        const modalDetalhesPlano = document.getElementById('modal-detalhes-plano');

        document.querySelectorAll('[data-plano]').forEach(function(badge) {
            badge.addEventListener('click', function() {
                const codigoPlano = badge.dataset.plano;
                const plano = planosDetalhes[codigoPlano];
                if (!plano || !modalDetalhesPlano) return;

                // Preencher modal
                document.getElementById('modal-plano-nome').textContent = plano.nome;
                document.getElementById('modal-plano-descricao').textContent = plano.descricao;

                // Badge de creditos
                const badgeEl = document.getElementById('modal-plano-badge');
                if (plano.gratuito) {
                    badgeEl.textContent = 'Gratis';
                    badgeEl.className = 'px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-700';
                } else {
                    badgeEl.textContent = plano.creditos + ' creditos';
                    badgeEl.className = 'px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700';
                }

                // Lista de consultas
                const listaEl = document.getElementById('modal-plano-consultas');
                listaEl.innerHTML = plano.consultas.map(function(c) {
                    return '<li class="flex items-center gap-2">' +
                        '<svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>' +
                        '</svg>' +
                        c +
                        '</li>';
                }).join('');

                // Casos de uso
                if (plano.casosUso) {
                    document.getElementById('modal-plano-caso-contador').textContent = plano.casosUso.contador || '-';
                    document.getElementById('modal-plano-caso-empresario').textContent = plano.casosUso.empresario || '-';
                }

                // Exemplo pratico
                if (plano.exemploPratico) {
                    document.getElementById('modal-plano-exemplo').textContent = plano.exemploPratico;
                }

                // Atualizar creditos do plano atual e calcular custo
                planoAtualCreditos = plano.creditos;
                calcularCusto();

                modalDetalhesPlano.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
        });

        // Fechar modal detalhes plano clicando fora
        if (modalDetalhesPlano) {
            modalDetalhesPlano.addEventListener('click', function(e) {
                if (e.target === modalDetalhesPlano) {
                    modalDetalhesPlano.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        }

        console.log('[Monitoramento] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initMonitoramento = initMonitoramento;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramento, { once: true });
    } else {
        initMonitoramento();
    }
})();
</script>
