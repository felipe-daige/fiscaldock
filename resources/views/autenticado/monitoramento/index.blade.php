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
                        href="/app/monitoramento/sped"
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

        {{-- Planos Disponiveis --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Planos de Monitoramento</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                @forelse($planos ?? [] as $plano)
                    <div class="bg-white rounded-xl border {{ $plano->is_gratuito ? 'border-green-200' : 'border-gray-200' }} shadow-sm p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="font-semibold text-gray-900">{{ $plano->nome }}</h3>
                            @if($plano->is_gratuito)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                    Gratis
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ $plano->custo_creditos }} cred.
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-3">{{ $plano->descricao }}</p>
                        <div class="text-xs text-gray-500">
                            <strong>Consultas:</strong>
                            <ul class="mt-1 space-y-1">
                                @foreach($plano->consultas_incluidas ?? [] as $consulta)
                                    <li class="flex items-center gap-1">
                                        <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ ucfirst(str_replace('_', ' ', $consulta)) }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @empty
                    {{-- Planos estaticos enquanto nao tem banco --}}
                    @php
                        $planosEstaticos = [
                            ['nome' => 'Basico', 'creditos' => 0, 'gratuito' => true, 'descricao' => 'Situacao Cadastral + Simples Nacional', 'consultas' => ['Situacao Cadastral', 'Simples Nacional']],
                            ['nome' => 'Cadastral+', 'creditos' => 3, 'gratuito' => false, 'descricao' => 'CNPJ completo + SINTEGRA + IE', 'consultas' => ['CNPJ Completo', 'SINTEGRA', 'Inscricao Estadual']],
                            ['nome' => 'Fiscal Federal', 'creditos' => 6, 'gratuito' => false, 'descricao' => 'CND Federal (PGFN) + FGTS', 'consultas' => ['CND Federal', 'FGTS']],
                            ['nome' => 'Fiscal Completo', 'creditos' => 12, 'gratuito' => false, 'descricao' => 'Federal + Estadual + CNDT', 'consultas' => ['CND Federal', 'FGTS', 'CND Estadual', 'CNDT']],
                            ['nome' => 'Due Diligence', 'creditos' => 18, 'gratuito' => false, 'descricao' => 'Completo + Protestos + Processos', 'consultas' => ['Todas CNDs', 'Protestos', 'Processos']],
                        ];
                    @endphp
                    @foreach($planosEstaticos as $plano)
                        <div class="bg-white rounded-xl border {{ $plano['gratuito'] ? 'border-green-200' : 'border-gray-200' }} shadow-sm p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="font-semibold text-gray-900">{{ $plano['nome'] }}</h3>
                                @if($plano['gratuito'])
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                                        Gratis
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                                        {{ $plano['creditos'] }} cred.
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 mb-3">{{ $plano['descricao'] }}</p>
                            <div class="text-xs text-gray-500">
                                <strong>Consultas:</strong>
                                <ul class="mt-1 space-y-1">
                                    @foreach($plano['consultas'] as $consulta)
                                        <li class="flex items-center gap-1">
                                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ $consulta }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>

        {{-- Lista de Participantes --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900">Meus Participantes</h2>
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
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all-participantes" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">CNPJ</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Razao Social</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Situacao</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Regime</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Origem</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Grupos</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ultima Consulta</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="participantes-tbody">
                        @forelse($participantes ?? [] as $participante)
                            <tr class="hover:bg-gray-50 transition-colors" data-participante-id="{{ $participante->id }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="participante-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $participante->id }}">
                                </td>
                                <td class="px-6 py-4 text-sm font-mono text-gray-900">
                                    {{ preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $participante->cnpj) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $participante->razao_social ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if($participante->situacao_cadastral === 'ATIVA')
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">Ativa</span>
                                    @elseif($participante->situacao_cadastral === 'BAIXADA')
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700">Baixada</span>
                                    @elseif($participante->situacao_cadastral === 'SUSPENSA')
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-700">Suspensa</span>
                                    @elseif($participante->situacao_cadastral === 'INAPTA')
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700">Inapta</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">{{ $participante->situacao_cadastral ?? '-' }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $participante->regime_tributario ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $origemTipo = $participante->origem_tipo ?? 'MANUAL';
                                        $origemRef = $participante->origem_ref ?? [];
                                        $importacao = $participante->importacao;

                                        // Determinar cor e texto do badge baseado na origem
                                        $badgeConfig = match($origemTipo) {
                                            'SPED_EFD_FISCAL' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'SPED Fiscal'],
                                            'SPED_EFD_CONTRIB' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => 'SPED Contrib'],
                                            'NFE' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'NF-e'],
                                            'NFSE' => ['bg' => 'bg-teal-100', 'text' => 'text-teal-700', 'label' => 'NFS-e'],
                                            'MANUAL' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => 'Manual'],
                                            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => str_replace('_', ' ', $origemTipo)],
                                        };

                                        // Obter nome do arquivo
                                        $nomeArquivo = $importacao?->filename ?? $origemRef['arquivo'] ?? null;
                                        if ($nomeArquivo && strlen($nomeArquivo) > 25) {
                                            $nomeArquivo = substr($nomeArquivo, 0, 22) . '...';
                                        }
                                    @endphp
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ $badgeConfig['bg'] }} {{ $badgeConfig['text'] }}">
                                            {{ $badgeConfig['label'] }}
                                        </span>
                                        @if($nomeArquivo)
                                            <span class="text-xs text-gray-400 truncate max-w-[150px]" title="{{ $importacao?->filename ?? $origemRef['arquivo'] ?? '' }}">
                                                {{ $nomeArquivo }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($participante->grupos as $grupo)
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                style="background-color: {{ $grupo->cor }}20; color: {{ $grupo->cor }}"
                                            >
                                                {{ $grupo->nome }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400">-</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $participante->ultima_consulta_em ? $participante->ultima_consulta_em->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            class="btn-consultar-participante inline-flex items-center p-2 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                            data-participante-id="{{ $participante->id }}"
                                            title="Consultar agora"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        </button>
                                        <a
                                            href="/app/monitoramento/participante/{{ $participante->id }}"
                                            class="inline-flex items-center p-2 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                            data-link
                                            title="Ver detalhes"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum participante cadastrado</h3>
                                        <p class="text-sm text-gray-600 mb-4">Importe participantes de um SPED ou adicione CNPJs manualmente.</p>
                                        <div class="flex items-center gap-3">
                                            <a
                                                href="/app/monitoramento/sped"
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
        <div id="acoes-massa" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-900 text-white rounded-xl shadow-2xl px-6 py-4 z-50">
            <div class="flex items-center gap-4">
                <span class="text-sm"><strong id="count-selecionados">0</strong> participante(s) selecionado(s)</span>
                <div class="h-6 w-px bg-gray-700"></div>
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
                <button type="button" id="btn-cancelar-selecao" class="inline-flex items-center p-2 rounded-lg text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

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

                    const response = await fetch('/app/monitoramento/participantes/associar-grupo', {
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
