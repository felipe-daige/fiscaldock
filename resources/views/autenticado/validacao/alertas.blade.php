{{-- Validacao Contabil - Lista de Alertas --}}
<div class="min-h-screen bg-gray-50" id="validacao-alertas-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-6">
            <a href="/app/validacao" data-link class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Voltar ao Dashboard
            </a>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Alertas de Validacao</h1>
                    <p class="mt-1 text-sm text-gray-600">Notas fiscais com alertas identificados na validacao.</p>
                </div>
            </div>
        </div>

        {{-- Contadores --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <a href="/app/validacao/alertas?nivel=bloqueante" data-link
               class="bg-white rounded-xl border {{ ($filtroNivel ?? '') === 'bloqueante' ? 'border-red-500 ring-2 ring-red-200' : 'border-gray-200' }} shadow-sm p-5 hover:border-red-300 transition">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-red-600">{{ $contadores['bloqueante'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Bloqueantes</p>
                    </div>
                </div>
            </a>

            <a href="/app/validacao/alertas?nivel=atencao" data-link
               class="bg-white rounded-xl border {{ ($filtroNivel ?? '') === 'atencao' ? 'border-yellow-500 ring-2 ring-yellow-200' : 'border-gray-200' }} shadow-sm p-5 hover:border-yellow-300 transition">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-yellow-600">{{ $contadores['atencao'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Atencao</p>
                    </div>
                </div>
            </a>

            <a href="/app/validacao/alertas?nivel=info" data-link
               class="bg-white rounded-xl border {{ ($filtroNivel ?? '') === 'info' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200' }} shadow-sm p-5 hover:border-blue-300 transition">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-blue-600">{{ $contadores['info'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Informativos</p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Nivel:</label>
                    <select id="filtro-nivel" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="bloqueante" {{ ($filtroNivel ?? '') === 'bloqueante' ? 'selected' : '' }}>Bloqueante</option>
                        <option value="atencao" {{ ($filtroNivel ?? '') === 'atencao' ? 'selected' : '' }}>Atencao</option>
                        <option value="info" {{ ($filtroNivel ?? '') === 'info' ? 'selected' : '' }}>Informativo</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Categoria:</label>
                    <select id="filtro-categoria" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas</option>
                        @foreach($categorias ?? [] as $key => $categoria)
                        <option value="{{ $key }}" {{ ($filtroCategoria ?? '') === $key ? 'selected' : '' }}>{{ $categoria['nome'] }}</option>
                        @endforeach
                    </select>
                </div>

                @if(($filtroNivel ?? '') || ($filtroCategoria ?? ''))
                <a href="/app/validacao/alertas" data-link class="text-sm text-blue-600 hover:text-blue-800">
                    Limpar filtros
                </a>
                @endif
            </div>
        </div>

        {{-- Lista de Notas com Alertas --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            @if(($notas ?? collect())->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Emitente</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Classificacao</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Alertas</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($notas as $nota)
                        @php
                            $alertasBloqueantes = collect($nota->validacao_alertas)->where('nivel', 'bloqueante')->count();
                            $alertasAtencao = collect($nota->validacao_alertas)->where('nivel', 'atencao')->count();
                            $alertasInfo = collect($nota->validacao_alertas)->where('nivel', 'info')->count();
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">NF {{ $nota->numero_nota }}</div>
                                <div class="text-xs text-gray-500">{{ $nota->data_emissao?->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $nota->emitente->razao_social ?? $nota->emit_razao_social ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $nota->emit_cnpj_formatado }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-lg font-bold {{ ($nota->validacao_score ?? 0) >= 50 ? 'text-red-600' : (($nota->validacao_score ?? 0) >= 30 ? 'text-orange-600' : (($nota->validacao_score ?? 0) >= 10 ? 'text-yellow-600' : 'text-green-600')) }}">
                                    {{ $nota->validacao_score ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $nota->validacao_badge_class }}">
                                    {{ $nota->validacao_classificacao_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($alertasBloqueantes > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800" title="Bloqueantes">
                                        {{ $alertasBloqueantes }}
                                    </span>
                                    @endif
                                    @if($alertasAtencao > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800" title="Atencao">
                                        {{ $alertasAtencao }}
                                    </span>
                                    @endif
                                    @if($alertasInfo > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800" title="Info">
                                        {{ $alertasInfo }}
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="/app/validacao/nota/{{ $nota->id }}" data-link class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                    Ver Detalhes
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginacao --}}
            @if($notas->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $notas->withQueryString()->links() }}
            </div>
            @endif

            @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum alerta encontrado</h3>
                <p class="mt-2 text-sm text-gray-500">
                    @if(($filtroNivel ?? '') || ($filtroCategoria ?? ''))
                        Nenhuma nota corresponde aos filtros selecionados.
                    @else
                        Todas as notas validadas estao conformes.
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<script src="{{ asset('js/validacao.js') }}"></script>
