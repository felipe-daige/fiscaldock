@php
    $notas = $notas ?? collect();
    $clientes = $clientes ?? collect();
    $filtros = $filtros ?? [];
    $escopoNotas = $escopoNotas ?? [];

    $statusOptions = [
        'todos' => 'Todos',
        'nao_validadas' => 'Não validadas',
        'validadas' => 'Validadas',
        'com_alertas' => 'Com alertas bloqueantes',
    ];

    $statusBadge = function ($nota) {
        if (is_null($nota->validacao)) {
            return ['label' => 'Não validada', 'hex' => '#6b7280'];
        }
        $alertas = $nota->validacao['alertas'] ?? [];
        foreach ($alertas as $a) {
            if (($a['nivel'] ?? null) === 'bloqueante') {
                return ['label' => 'Bloqueante', 'hex' => '#b91c1c'];
            }
        }
        foreach ($alertas as $a) {
            if (($a['nivel'] ?? null) === 'atencao') {
                return ['label' => 'Atenção', 'hex' => '#d97706'];
            }
        }
        return ['label' => 'Validada', 'hex' => '#047857'];
    };
@endphp

<div class="min-h-screen bg-gray-100" id="validacao-notas-container" data-ids-url="{{ route('app.clearance.todos-ids') }}" data-validar-url="{{ route('app.clearance.validar') }}" data-custo-url="{{ route('app.clearance.calcular-custo') }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Verificar Notas</h1>
                <p class="text-xs text-gray-500 mt-1">Selecione notas XML e dispare a validação contábil em lote.</p>
            </div>
            <a href="/app/validacao" data-link class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-sm font-medium self-start">
                Voltar ao Painel
            </a>
        </div>

        <div id="clearance-notas-error" class="mb-4"></div>

        <div class="bg-white rounded border border-gray-300 p-4 mb-4 border-l-4 border-l-blue-500">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Escopo da Listagem</p>
            <p class="mt-2 text-sm text-gray-700">Esta grade exibe apenas `xml_notas`. Notas importadas por EFD/SPED não aparecem aqui.</p>
            <div class="mt-3 flex flex-wrap gap-2 text-[10px] font-bold uppercase tracking-wide">
                <span class="px-2 py-0.5 rounded text-white" style="background-color: #374151">XML: {{ number_format($escopoNotas['total_xml'] ?? 0, 0, ',', '.') }}</span>
                <span class="px-2 py-0.5 rounded text-white" style="background-color: #9ca3af">EFD: {{ number_format($escopoNotas['total_efd'] ?? 0, 0, ',', '.') }}</span>
                <span class="px-2 py-0.5 rounded text-white" style="background-color: #1f2937">Base Unificada: {{ number_format($escopoNotas['total_unificado'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="/app/validacao/notas" class="bg-white rounded border border-gray-300 overflow-hidden mb-4" id="validacao-filtros-form">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros</span>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div>
                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">De</label>
                    <input type="date" name="periodo_de" value="{{ $filtros['periodo_de'] ?? '' }}" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Até</label>
                    <input type="date" name="periodo_ate" value="{{ $filtros['periodo_ate'] ?? '' }}" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Cliente</label>
                    <select name="cliente_id" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                        <option value="">Todos</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}" {{ ($filtros['cliente_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->razao_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">CNPJ Participante</label>
                    <input type="text" name="participante_cnpj" value="{{ $filtros['participante_cnpj'] ?? '' }}" placeholder="00.000.000/0000-00" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Tipo</label>
                    <select name="tipo_nota" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                        <option value="">Todos</option>
                        <option value="entrada" {{ ($filtros['tipo_nota'] ?? '') === 'entrada' ? 'selected' : '' }}>Entrada</option>
                        <option value="saida" {{ ($filtros['tipo_nota'] ?? '') === 'saida' ? 'selected' : '' }}>Saída</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Status</label>
                    <select name="status_validacao" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ ($filtros['status_validacao'] ?? 'todos') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-2 border-t border-gray-200 flex gap-2">
                <button type="submit" class="px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">Aplicar</button>
                <a href="/app/validacao/notas" data-link class="px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide border border-gray-300 text-gray-700">Limpar</a>
            </div>
        </form>

        {{-- Barra de ações bulk --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-sm text-gray-700">
                    <span id="selecao-label">Nenhuma nota selecionada</span>
                    <span class="text-gray-400"> · {{ number_format($notas->total(), 0, ',', '.') }} resultado(s)</span>
                    <button type="button" id="btn-selecionar-todas" class="ml-3 text-xs text-gray-600 hover:text-gray-900 hover:underline hidden">Selecionar todas ({{ number_format($notas->total(), 0, ',', '.') }}) dos filtros atuais</button>
                </div>
                <div class="flex flex-wrap gap-2">
                    <select id="tipo-validacao" class="px-3 py-1.5 rounded text-[11px] font-medium border border-gray-300 text-gray-700">
                        <option value="local">Regras locais</option>
                        <option value="completa" selected>Validação completa</option>
                        <option value="deep">Deep analysis</option>
                    </select>
                    <button type="button" id="btn-calcular-custo" class="px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide border border-gray-300 text-gray-700 disabled:opacity-40" disabled>Calcular custo</button>
                    <button type="button" id="btn-validar" class="px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide text-white disabled:opacity-40" style="background-color: #047857" disabled>Validar selecionadas</button>
                </div>
            </div>
        </div>

        {{-- Tabela --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left"><input type="checkbox" id="chk-master" class="w-4 h-4"></th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Nota</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Emissão</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Emitente</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Destinatário</th>
                            <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Valor</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Tipo</th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-500 uppercase tracking-wide"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="tbody-notas">
                        @forelse($notas as $n)
                            @php $s = $statusBadge($n); @endphp
                            <tr data-nota-id="{{ $n->id }}" class="hover:bg-gray-50">
                                <td class="px-3 py-2"><input type="checkbox" class="w-4 h-4 chk-nota" value="{{ $n->id }}"></td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $n->numero_nota }}/{{ $n->serie }}</td>
                                <td class="px-3 py-2 text-xs">{{ optional($n->data_emissao)->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 text-xs text-gray-700 truncate max-w-[180px]">{{ $n->emit_razao_social }}</td>
                                <td class="px-3 py-2 text-xs text-gray-700 truncate max-w-[180px]">{{ $n->dest_razao_social }}</td>
                                <td class="px-3 py-2 text-xs text-right font-mono">R$ {{ number_format($n->valor_total, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 text-xs">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $n->tipo_nota === 0 ? '#047857' : '#d97706' }}">
                                        {{ $n->tipo_nota === 0 ? 'Entrada' : 'Saída' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-xs">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white td-status" style="background-color: {{ $s['hex'] }}">{{ $s['label'] }}</span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <a href="/app/validacao/nota/{{ $n->id }}" data-link class="text-xs text-gray-600 hover:text-gray-900 hover:underline">Detalhes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-8 text-center">
                                    @if(($escopoNotas['possui_apenas_efd'] ?? false) === true)
                                        <p class="text-sm text-gray-700">Você possui notas em EFD/SPED, mas nenhuma nota XML disponível para esta tela.</p>
                                        <p class="text-[11px] text-gray-500 mt-2">As {{ number_format($escopoNotas['total_efd'] ?? 0, 0, ',', '.') }} notas EFD aparecem nas telas unificadas de notas fiscais, não na Validação XML.</p>
                                        <a href="/app/notas-fiscais" data-link class="mt-3 inline-flex text-xs text-gray-600 hover:text-gray-900 hover:underline">
                                            Abrir Notas Fiscais
                                        </a>
                                    @else
                                        <p class="text-sm text-gray-500">Nenhuma nota XML encontrada com os filtros atuais.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                {{ $notas->links() }}
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/clearance-notas.js') }}?v={{ @filemtime(public_path('js/clearance-notas.js')) ?: time() }}" defer></script>
