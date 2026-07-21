@php
    $cfopsPerfil = collect($cfopsPerfil ?? [])->take(10);
@endphp

<x-cockpit.secao
    titulo="Principais CFOPs"
    subtitulo="Operações fiscais com maior valor movimentado no acervo."
    :contagem="$cfopsPerfil->count()"
    body-class="p-0"
    data-perfil-card="cfops"
>
    @if($cfopsPerfil->isEmpty())
        <p class="px-4 py-5 text-sm text-gray-500 sm:px-5">Nenhum CFOP encontrado no acervo fiscal.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-xs">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                        <th class="px-4 py-2 text-left font-semibold sm:px-5">CFOP</th>
                        <th class="px-4 py-2 text-left font-semibold">Descrição</th>
                        <th class="px-4 py-2 text-right font-semibold">Quantidade</th>
                        <th class="px-4 py-2 text-right font-semibold sm:px-5">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($cfopsPerfil as $cfop)
                        @php
                            $descricao = preg_replace('/^\d+\s*[—-]\s*/u', '', (string) ($cfop['descricao'] ?? ''));
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 font-mono font-semibold text-gray-700 sm:px-5">{{ $cfop['cfop'] ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $descricao ?: 'Descrição não informada' }}</td>
                            <td class="px-4 py-2.5 text-right font-mono text-gray-500">{{ number_format((float) ($cfop['qtd'] ?? 0), 0, ',', '.') }}</td>
                            <td class="px-4 py-2.5 text-right font-mono font-semibold text-gray-900 sm:px-5">{{ \App\Support\Dinheiro::brl($cfop['valor'] ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-cockpit.secao>
