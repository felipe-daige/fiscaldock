{{-- Minha Empresa - Historico de Consultas --}}
<div class="min-h-screen bg-gray-50" id="minha-empresa-historico">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="/app/minha-empresa" data-link class="hover:text-blue-600 transition-colors">Minha Empresa</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span>Historico</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Historico de Consultas</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $empresa->razao_social ?? $empresa->nome }} - {{ $empresa->documento_formatado }}</p>
                </div>
                <div>
                    <a href="/app/consultas/nova?participante={{ $participante->id ?? '' }}" data-link class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Nova Consulta
                    </a>
                </div>
            </div>
        </div>

        {{-- Lista de Consultas --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            @if(($consultas ?? collect())->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plano</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consultas Realizadas</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acoes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($consultas as $consulta)
                                @php
                                    $scoreData = $consulta->calcularScore();
                                    $scoreCor = match($scoreData['classificacao']) {
                                        'baixo' => 'green',
                                        'medio' => 'yellow',
                                        'alto' => 'orange',
                                        'critico' => 'red',
                                        default => 'gray'
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $consulta->consultado_em ? $consulta->consultado_em->format('d/m/Y') : 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $consulta->consultado_em ? $consulta->consultado_em->format('H:i') : '' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $consulta->lote->plano->nome ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($consulta->getConsultasRealizadas() as $tipo)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                    {{ str_replace('_', ' ', ucfirst($tipo)) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($consulta->isSucesso())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Sucesso
                                            </span>
                                        @elseif($consulta->isErro())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Erro
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pendente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($consulta->isSucesso())
                                            <span class="text-lg font-bold text-{{ $scoreCor }}-600">{{ $scoreData['score_total'] }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        @if($consulta->lote)
                                            <a href="/app/consultas/lote/{{ $consulta->lote->id }}/baixar" class="text-blue-600 hover:text-blue-900">
                                                Baixar CSV
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginacao --}}
                @if($consultas->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $consultas->links() }}
                    </div>
                @endif
            @else
                {{-- Estado vazio --}}
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhuma consulta realizada</h3>
                    <p class="mt-2 text-sm text-gray-500">Ainda nao foram realizadas consultas para esta empresa.</p>
                    <a href="/app/consultas/nova?participante={{ $participante->id ?? '' }}" data-link class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Realizar Primeira Consulta
                    </a>
                </div>
            @endif
        </div>

        {{-- Voltar --}}
        <div class="mt-6 text-center">
            <a href="/app/minha-empresa" data-link class="text-gray-600 hover:text-blue-600 transition-colors">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar para Dashboard
            </a>
        </div>
    </div>
</div>
