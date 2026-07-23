{{-- Admin — Kits da consulta avulsa por fontes (vertical advocacia) — docs/advocacia/consultas-certidoes.md fase 3 --}}
<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 sm:mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Kits de Consulta</h1>
                <p class="text-xs text-gray-500 mt-0.5">Presets de seleção para a Consulta por Fontes. Preço por desconto (%) ou fixo em R$; visível/cobrado para todos os usuários ou só os selecionados. O preço só se aplica quando a seleção do usuário bate exatamente com o kit.</p>
            </div>
            <a href="/app/admin/kits/novo" data-link class="inline-flex items-center justify-center gap-1.5 rounded bg-gray-900 px-4 py-2 text-xs sm:text-sm font-semibold text-white transition hover:bg-gray-700 flex-shrink-0">Novo kit</a>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'kits'])

        @if(session('status'))
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color: #047857">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <table class="w-full text-[13px] tabela-cards">
                <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="text-left px-3 py-2">Kit</th>
                        <th class="text-right px-3 py-2">Fontes</th>
                        <th class="text-right px-3 py-2">Preço cheio</th>
                        <th class="text-right px-3 py-2">Preço / regra</th>
                        <th class="text-right px-3 py-2">Preço final</th>
                        <th class="text-center px-3 py-2">Público</th>
                        <th class="text-center px-3 py-2">Ativo</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($kits as $k)
                        @php
                            $resumoKit = $catalogoPrecos->resumoKit($k);
                            $fontesKit = $resumoKit['fontes'];
                            $qtdUsuarios = $k->publico === 'selecionados' ? $k->usuarios()->count() : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2.5" data-label="Kit">
                                <span class="font-semibold text-gray-900">{{ $k->nome }}</span>
                                <span class="block text-[11px] text-gray-400">{{ $k->slug }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-right text-gray-700" data-label="Fontes">{{ count($fontesKit) }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-700" data-label="Preço cheio">{{ \App\Support\Dinheiro::brl($resumoKit['bruto']) }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-700" data-label="Preço / regra">
                                @if($k->preco_fixo !== null)
                                    <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #1d4ed8">fixo</span>
                                @else
                                    {{ number_format((float) $k->desconto_percentual, 0, ',', '.') }}%
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right font-semibold text-gray-900" data-label="Preço final">{{ \App\Support\Dinheiro::brl($resumoKit['total']) }}</td>
                            <td class="px-3 py-2.5 text-center" data-label="Público">
                                @if($qtdUsuarios === null)
                                    <span class="text-[12px] text-gray-600">Todos</span>
                                @else
                                    <span class="text-[12px] text-gray-600">{{ $qtdUsuarios }} usuário{{ $qtdUsuarios === 1 ? '' : 's' }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-center" data-label="Ativo">
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $k->ativo ? '#047857' : '#6b7280' }}">{{ $k->ativo ? 'Sim' : 'Não' }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-right" data-label="">
                                <a href="/app/admin/kits/{{ $k->id }}/editar" data-link class="text-[12px] font-medium text-gray-700 underline hover:text-gray-900">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-3 py-6 text-center text-sm text-gray-500">Nenhum kit cadastrado. Crie o primeiro em “Novo kit”.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
