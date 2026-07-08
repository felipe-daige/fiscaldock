@props([
    'titulo',            // título do painel
    'contagem',          // nº de alertas ativos (não dispensados)
    'colunas' => [],      // list<string>: rótulos do cabeçalho. A última coluna alinha à direita (Ação).
    'aberto' => false,    // default recolhido — o usuário clica no cabeçalho p/ ver os alertas (retrátil via <details>)
])
{{--
    Casca padrão de um painel de alerta do Catálogo × Itens (border-l-4 amber + tabela).
    Retrátil via <details>/<summary> nativo (sem JS, SPA-safe): o cabeçalho abre/fecha;
    ajuda + tabela ficam na parte que recolhe. Corpo: `$slot` = linhas <tr>.
    Extraído de catalogo-itens.blade.php (NCM a revisar / Itens sem catálogo eram idênticos).
--}}
<details @if($aberto) open @endif class="group bg-white rounded border border-gray-300 border-l-4 mb-4" style="border-left-color:#b45309">
    {{-- Summary = cabeçalho clicável: ícone de alerta + título + contagem em pill
         (âmbar quando >0, cinza quando 0) + chevron que gira ao abrir. Fundo âmbar-50
         marca o painel como "atenção", distinto dos cards neutros de resumo. --}}
    <summary class="list-none [&::-webkit-details-marker]:hidden cursor-pointer px-4 py-3 flex items-center gap-2 group-open:border-b group-open:border-gray-200 rounded" style="background-color:#fffbeb">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" class="shrink-0" style="color:#b45309" aria-hidden="true">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 9v4m0 4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p class="text-sm font-semibold text-gray-800">{{ $titulo }}</p>
        <span class="ml-auto text-[11px] font-bold px-2 py-0.5 rounded-full text-white tabular-nums" style="background-color:{{ $contagem > 0 ? '#b45309' : '#9ca3af' }}">{{ $contagem }}</span>
        {{-- dicas de estado: "clique para ver" só no recolhido; "clique para fechar" quando aberto --}}
        <span class="text-[11px] font-medium group-open:hidden" style="color:#b45309">clique para ver</span>
        <span class="text-[11px] text-gray-400 hidden group-open:inline">fechar</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="shrink-0 text-gray-400 transition-transform group-open:rotate-180" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </summary>
    @isset($ajuda)
        <p class="text-[11px] text-gray-500 px-4 pt-3 leading-relaxed">{{ $ajuda }}</p>
    @endisset
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <caption class="sr-only">{{ $titulo }}</caption>
            <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                <tr>
                    @foreach ($colunas as $i => $coluna)
                        <th scope="col" class="{{ $loop->last ? 'text-right' : 'text-left' }} px-3 py-2">{{ $coluna }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</details>
