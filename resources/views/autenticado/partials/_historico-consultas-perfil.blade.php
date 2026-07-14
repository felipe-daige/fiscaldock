@php
    $historicoPaginado = $historicoConsultasPerfil ?? null;
    $historicoItens = $historicoPaginado instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? $historicoPaginado->getCollection()
        : collect($historicoPaginado ?? []);
    $totalHistorico = $historicoPaginado instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        ? $historicoPaginado->total()
        : $historicoItens->count();
    $documentoPerfil = preg_replace('/\D/', '', (string) ($documentoPerfil ?? ''));
    $tituloDocumento = strlen($documentoPerfil) === 14 ? 'deste CNPJ' : 'deste cadastro';
@endphp

<div class="bg-white rounded border border-gray-300 overflow-hidden" id="historico-consultas-perfil">
    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
        <div class="flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Últimas Consultas {{ $tituloDocumento }}</span>
                <p class="text-[11px] text-gray-500 mt-1">Consultas cadastrais e verificações de documentos fiscais, da mais recente para a mais antiga.</p>
            </div>
            <span class="text-[10px] font-semibold text-gray-600 px-2 py-0.5 rounded" style="background-color: #e5e7eb">
                {{ $totalHistorico }} {{ $totalHistorico === 1 ? 'consulta' : 'consultas' }}
            </span>
        </div>
    </div>

    @if($historicoItens->isEmpty())
        <div class="px-6 py-10 text-center">
            <p class="text-sm text-gray-500">Nenhuma consulta encontrada para este cadastro.</p>
            <p class="text-xs text-gray-400 mt-1">Consultas CNPJ e verificações NF-e/CT-e aparecerão aqui.</p>
        </div>
    @else
        <div class="divide-y divide-gray-200">
            @foreach($historicoItens as $consultaPerfil)
                <div class="px-4 py-4 sm:px-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $consultaPerfil['origem_hex'] }}">
                                    {{ $consultaPerfil['origem_label'] }}
                                </span>
                                <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $consultaPerfil['status_hex'] }}">
                                    {{ $consultaPerfil['status_label'] }}
                                </span>
                                <span class="text-[11px] text-gray-500">{{ $consultaPerfil['consultado_em']?->format('d/m/Y H:i') ?? '-' }}</span>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-gray-900">{{ $consultaPerfil['titulo'] }}</p>
                            @if(!empty($consultaPerfil['descricao']))
                                <p class="mt-0.5 text-xs text-gray-500">{{ $consultaPerfil['descricao'] }}</p>
                            @endif
                            @if(!empty($consultaPerfil['identificador']))
                                <p class="mt-1 text-[11px] font-mono text-gray-400 break-all">{{ $consultaPerfil['identificador'] }}</p>
                            @endif
                        </div>
                        @if(!empty($consultaPerfil['url']))
                            <a href="{{ $consultaPerfil['url'] }}" data-link class="shrink-0 text-xs font-semibold text-gray-600 hover:text-gray-900 hover:underline">
                                Ver resultado
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($historicoPaginado instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $historicoPaginado->hasPages())
            @php
                $paginaInicial = max(1, $historicoPaginado->currentPage() - 2);
                $paginaFinal = min($historicoPaginado->lastPage(), $historicoPaginado->currentPage() + 2);
            @endphp
            <nav class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between" aria-label="Paginação das últimas consultas">
                <p class="text-[11px] text-gray-500">
                    Exibindo {{ $historicoPaginado->firstItem() }}–{{ $historicoPaginado->lastItem() }} de {{ $historicoPaginado->total() }}
                </p>
                <div class="flex items-center gap-1">
                    @if($historicoPaginado->onFirstPage())
                        <span class="inline-flex h-8 items-center rounded border border-gray-200 px-3 text-xs font-semibold text-gray-300">Anterior</span>
                    @else
                        <a href="{{ $historicoPaginado->previousPageUrl() }}" data-link class="inline-flex h-8 items-center rounded border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-600 hover:bg-gray-100 hover:text-gray-900">Anterior</a>
                    @endif

                    @foreach($historicoPaginado->getUrlRange($paginaInicial, $paginaFinal) as $pagina => $url)
                        @if($pagina === $historicoPaginado->currentPage())
                            <span aria-current="page" class="inline-flex h-8 min-w-8 items-center justify-center rounded px-2 text-xs font-bold text-white" style="background-color: #374151">{{ $pagina }}</span>
                        @else
                            <a href="{{ $url }}" data-link class="inline-flex h-8 min-w-8 items-center justify-center rounded border border-gray-300 bg-white px-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 hover:text-gray-900">{{ $pagina }}</a>
                        @endif
                    @endforeach

                    @if($historicoPaginado->hasMorePages())
                        <a href="{{ $historicoPaginado->nextPageUrl() }}" data-link class="inline-flex h-8 items-center rounded border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-600 hover:bg-gray-100 hover:text-gray-900">Próxima</a>
                    @else
                        <span class="inline-flex h-8 items-center rounded border border-gray-200 px-3 text-xs font-semibold text-gray-300">Próxima</span>
                    @endif
                </div>
            </nav>
        @endif
    @endif
</div>
