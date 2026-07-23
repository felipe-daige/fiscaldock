{{-- Admin — Preço por consulta (fonte_precos). Modelo à la carte: cada consulta tem preço próprio. --}}
<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Preço das Consultas</h1>
            <p class="text-xs text-gray-500 mt-0.5">Preço de venda por consulta (R$). Vazio = usa o default do sistema. Desmarcar “À venda” esconde a consulta da tela de seleção.</p>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'fontes'])

        @if(session('status'))
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color: #047857">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="/app/admin/fontes">
            @csrf

            @foreach($grupos as $grupo)
                <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
                    <div class="bg-gray-50 px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-gray-600">{{ $grupo['label'] }}</div>
                    <table class="w-full text-[13px] tabela-cards">
                        <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="text-left px-3 py-2">Consulta</th>
                                <th class="text-right px-3 py-2">Preço efetivo</th>
                                <th class="text-right px-3 py-2 w-40">Preço (R$)</th>
                                <th class="text-center px-3 py-2 w-24">À venda</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($grupo['fontes'] as $f)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2.5" data-label="Consulta">
                                        <span class="font-semibold text-gray-900">{{ $f['nome'] }}</span>
                                        <span class="block text-[11px] text-gray-400">{{ $f['chave'] }}</span>
                                        @if($f['pausada'])
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white mt-1" style="background-color: #b45309">Pausada na origem</span>
                                        @elseif(! $f['registrada'])
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white mt-1" style="background-color: #6b7280">Não registrada</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-right text-gray-700" data-label="Preço efetivo">{{ \App\Support\Dinheiro::brl($f['preco']) }}</td>
                                    <td class="px-3 py-2.5 text-right" data-label="Preço (R$)">
                                        <input type="number" step="0.01" min="0" max="9999.99"
                                               name="precos[{{ $f['chave'] }}]"
                                               value="{{ $f['tem_override'] ? number_format($f['preco'], 2, '.', '') : '' }}"
                                               placeholder="{{ number_format($f['preco'], 2, '.', '') }}"
                                               class="w-28 rounded border border-gray-300 px-2 py-1 text-right text-[13px] focus:border-gray-500 focus:outline-none">
                                    </td>
                                    <td class="px-3 py-2.5 text-center" data-label="À venda">
                                        <input type="checkbox" name="ativos[{{ $f['chave'] }}]" value="1"
                                               {{ $f['ativo'] ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-gray-300">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded bg-gray-900 px-5 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">Salvar preços</button>
            </div>
        </form>
    </div>
</div>
