@props([
    'titulo',            // título do painel
    'contagem',          // nº de alertas ativos (não dispensados)
    'colunas' => [],      // list<string>: rótulos do cabeçalho. A última coluna alinha à direita (Ação).
])
{{--
    Casca padrão de um painel de alerta do Catálogo × Itens (border-l-4 amber + tabela).
    Cabeçalho: título + contagem + explicação (slot `ajuda`). Corpo: `$slot` = linhas <tr>.
    Extraído de catalogo-itens.blade.php (NCM a revisar / Itens sem catálogo eram idênticos).
--}}
<div class="bg-white rounded border border-gray-300 border-l-4 mb-4" style="border-left-color:#b45309">
    <div class="px-4 py-2.5 border-b border-gray-200">
        <p class="text-sm font-semibold text-gray-800">{{ $titulo }} — {{ $contagem }}</p>
        @isset($ajuda)
            <p class="text-[11px] text-gray-500 mt-1">{{ $ajuda }}</p>
        @endisset
    </div>
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
</div>
