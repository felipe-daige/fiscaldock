{{-- Admin — catálogo e preço por consulta. Inclui fontes operacionais e futuras/manutenção. --}}
<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Catálogo de Consultas</h1>
            <p class="text-xs text-gray-500 mt-0.5">Gerencie fontes operacionais e futuras. Fonte em manutenção pode ficar visível, mas nunca é selecionável nem cobrada.</p>
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
                                <th class="text-left px-3 py-2">Situação</th>
                                <th class="text-left px-3 py-2">Documento</th>
                                <th class="text-right px-3 py-2">Preço efetivo</th>
                                <th class="text-right px-3 py-2 w-40">Preço (R$)</th>
                                <th class="text-center px-3 py-2 w-24">No catálogo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($grupo['fontes'] as $f)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2.5" data-label="Consulta">
                                        <span class="font-semibold text-gray-900">{{ $f['nome'] }}</span>
                                        <span class="block text-[11px] text-gray-400">{{ $f['chave'] }}</span>
                                    </td>
                                    <td class="px-3 py-2.5" data-label="Situação">
                                        @if($f['pronta'])
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white" style="background-color:#047857">Operacional</span>
                                        @elseif($f['pausada'])
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white" style="background-color:#b45309">Pausada na origem</span>
                                        @else
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white" style="background-color:#b45309">Em manutenção</span>
                                        @endif
                                        @if($f['requer_autenticacao'])
                                            <span class="mt-1 block text-[10px] text-gray-500">GOV.BR, A1 ou conta externa</span>
                                        @elseif(! $f['registrada'])
                                            <span class="mt-1 block text-[10px] text-gray-400">Classe Laravel ainda não registrada</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-gray-600" data-label="Documento">
                                        <span class="block text-[11px]">{{ $f['tipos_planejados_label'] }}</span>
                                        @if($f['tipos_operacionais_label'] && $f['tipos_operacionais_label'] !== $f['tipos_planejados_label'])
                                            <span class="block text-[10px] text-amber-700">Ativo hoje: {{ $f['tipos_operacionais_label'] }}</span>
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
                                    <td class="px-3 py-2.5 text-center" data-label="No catálogo">
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
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded bg-gray-900 px-5 py-2 text-sm font-semibold text-white transition hover:bg-gray-700">Salvar catálogo</button>
            </div>
        </form>
    </div>
</div>
