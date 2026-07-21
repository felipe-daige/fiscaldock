@php
    $produtosPerfil = collect($produtosPerfil ?? [])->take(10);
@endphp

<x-cockpit.secao
    titulo="Principais Produtos"
    subtitulo="Produtos com maior valor movimentado no acervo fiscal."
    :contagem="$produtosPerfil->count()"
    body-class="p-0"
    data-perfil-card="produtos"
>
    @if($produtosPerfil->isEmpty())
        <p class="px-4 py-5 text-sm text-gray-500 sm:px-5">Nenhum produto encontrado no acervo fiscal.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-xs">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                        <th class="px-4 py-2 text-left font-semibold sm:px-5">Produto</th>
                        <th class="px-4 py-2 text-left font-semibold">NCM</th>
                        <th class="px-4 py-2 text-right font-semibold">Quantidade</th>
                        <th class="px-4 py-2 text-right font-semibold sm:px-5">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($produtosPerfil as $produto)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-gray-700 sm:px-5">{{ $produto['descricao'] ?? $produto['cod_item'] ?? '—' }}</td>
                            <td class="px-4 py-2.5 font-mono text-gray-500">{{ $produto['ncm'] ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right font-mono text-gray-500">{{ number_format((float) ($produto['qtd'] ?? 0), 2, ',', '.') }}</td>
                            <td class="px-4 py-2.5 text-right font-mono font-semibold text-gray-900 sm:px-5">{{ \App\Support\Dinheiro::brl($produto['valor'] ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-cockpit.secao>
