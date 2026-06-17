@php
    $fmtMoeda = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Catálogo × Itens de Nota</h1>
            <p class="text-xs text-gray-500 mt-0.5">Itens movimentados nas notas (XML + EFD), cruzados com o catálogo do contribuinte.</p>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-5">
            @foreach([
                ['Itens movimentados', $kpis['total_itens'], '#1d4ed8'],
                ['Com catálogo', $kpis['com_catalogo'], '#047857'],
                ['Sem catálogo', $kpis['sem_catalogo'], '#b45309'],
                ['Sem NCM', $kpis['sem_ncm'], '#b45309'],
                ['NCM a revisar', $kpis['ncm_revisar'] ?? 0, '#b45309'],
            ] as [$label, $valor, $cor])
                <div class="bg-white rounded border border-gray-300 border-l-4 p-3" style="border-left-color: {{ $cor }}">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $label }}</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($valor, 0, ',', '.') }}</p>
                </div>
            @endforeach
            <div class="bg-white rounded border border-gray-300 border-l-4 p-3" style="border-left-color: #334155">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Valor movimentado</p>
                <p class="text-lg font-bold text-gray-900">{{ $fmtMoeda($kpis['valor_movimentado']) }}</p>
            </div>
        </div>

        @if(($reconciliacao['documentadas'] ?? 0) > 0)
            <div class="bg-white rounded border border-gray-300 p-3 mb-4">
                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2">Reconciliação documento × declarado (por chave)</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                    <div><span class="font-bold text-gray-900">{{ number_format($reconciliacao['documentadas'], 0, ',', '.') }}</span> <span class="text-gray-500 text-[11px]">documentadas (XML)</span></div>
                    <div><span class="font-bold" style="color:#047857">{{ number_format($reconciliacao['reconciliadas'], 0, ',', '.') }}</span> <span class="text-gray-500 text-[11px]">reconciliadas</span></div>
                    <div><span class="font-bold" style="color:#b45309">{{ number_format($reconciliacao['divergencia_total'], 0, ',', '.') }}</span> <span class="text-gray-500 text-[11px]">divergência de total</span></div>
                    <div><span class="font-bold" style="color:#dc2626">{{ number_format($reconciliacao['nao_declaradas'], 0, ',', '.') }}</span> <span class="text-gray-500 text-[11px]">não declaradas</span></div>
                </div>
                @if(($reconciliacao['efd_sem_xml'] ?? 0) > 0)
                    <p class="text-[11px] text-gray-400 mt-2">Cobertura: {{ number_format($reconciliacao['efd_sem_xml'], 0, ',', '.') }} nota(s) declarada(s) no EFD sem XML no acervo (informativo, não é alerta).</p>
                @endif
            </div>
        @endif

        @if($divergencias->isNotEmpty())
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4" style="border-left-color:#b45309">
                <div class="px-4 py-2.5 border-b border-gray-200">
                    <p class="text-sm font-semibold text-gray-800">NCM a revisar (documento × cadastro) — {{ $divergencias->count() }}</p>
                    <p class="text-[11px] text-gray-500">O NCM do XML diverge do catálogo 0200. Pode ser erro de classificação ou atualização posterior do cadastro.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="text-left px-3 py-2">Código</th>
                                <th class="text-left px-3 py-2">Descrição</th>
                                <th class="text-left px-3 py-2">NCM documento</th>
                                <th class="text-left px-3 py-2">NCM cadastro</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($divergencias as $d)
                                <tr>
                                    <td class="px-3 py-2 font-mono text-gray-900">{{ $d['codigo_item'] }}</td>
                                    <td class="px-3 py-2 text-gray-700 truncate max-w-xs" title="{{ $d['descricao'] }}">{{ $d['descricao'] }}</td>
                                    <td class="px-3 py-2 font-mono"><span class="px-1.5 py-0.5 rounded text-[10px] font-bold text-white" style="background-color:#b45309">{{ $d['ncm_xml'] ?: '—' }}</span></td>
                                    <td class="px-3 py-2 font-mono text-gray-600">{{ $d['cat_ncm'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($kpis['sem_catalogo'] > 0)
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-4" style="border-left-color: #b45309">
                <p class="text-sm text-gray-700"><span class="font-semibold">{{ $kpis['sem_catalogo'] }} item(ns)</span> movimentado(s) em nota mas sem catálogo cadastrado — confira a coluna "Catálogo".</p>
            </div>
        @endif

        {{-- Filtros (padrão /app/clientes) --}}
        <form method="GET" class="bg-white rounded border border-gray-300 p-3 mb-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] text-gray-500 mb-1">Cliente</label>
                <select name="cliente_id" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                    <option value="">Todos</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" @selected(($filtros['cliente_id'] ?? null) == $c->id)>{{ $c->razao_social }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] text-gray-500 mb-1">Fonte</label>
                <select name="fonte" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                    <option value="">Ambas</option>
                    <option value="efd" @selected(($filtros['fonte'] ?? null) === 'efd')>EFD</option>
                    <option value="xml" @selected(($filtros['fonte'] ?? null) === 'xml')>XML</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] text-gray-500 mb-1">De</label>
                <input type="date" name="periodo_de" value="{{ $filtros['periodo_de'] ?? '' }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
            </div>
            <div>
                <label class="block text-[11px] text-gray-500 mb-1">Até</label>
                <input type="date" name="periodo_ate" value="{{ $filtros['periodo_ate'] ?? '' }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
            </div>
        </form>

        {{-- Tabela --}}
        <div class="bg-white rounded border border-gray-300 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="text-left px-3 py-2.5">Código</th>
                        <th class="text-left px-3 py-2.5">Descrição</th>
                        <th class="text-left px-3 py-2.5">Origem</th>
                        <th class="text-left px-3 py-2.5">NCM</th>
                        <th class="text-left px-3 py-2.5">CFOP</th>
                        <th class="text-left px-3 py-2.5">CST</th>
                        <th class="text-right px-3 py-2.5">Qtd</th>
                        <th class="text-right px-3 py-2.5">Ocorr.</th>
                        <th class="text-right px-3 py-2.5">Alíq. méd.</th>
                        <th class="text-right px-3 py-2.5">Valor movimentado</th>
                        <th class="text-left px-3 py-2.5">Catálogo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($itens as $item)
                    @php
                        $origemCor = ['efd' => '#1d4ed8', 'xml' => '#7c3aed', 'ambas' => '#047857'][$item['fontes']] ?? '#334155';
                    @endphp
                    <tr>
                        <td class="px-3 py-2 font-mono text-gray-900">{{ $item['codigo_item'] }}</td>
                        <td class="px-3 py-2 text-gray-700 truncate max-w-xs" title="{{ $item['descricao'] }}">{{ $item['descricao'] }}</td>
                        <td class="px-3 py-2">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color: {{ $origemCor }}">{{ $item['fontes'] }}</span>
                        </td>
                        <td class="px-3 py-2 font-mono text-gray-600">{{ $item['ncm'] ?: '—' }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $item['cfops'] ?: '—' }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $item['csts'] ?: '—' }}</td>
                        <td class="px-3 py-2 text-right text-gray-700">{{ number_format($item['quantidade'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right text-gray-700">{{ $item['ocorrencias'] }}</td>
                        <td class="px-3 py-2 text-right text-gray-600">{{ $item['aliquota_media'] !== null ? number_format($item['aliquota_media'], 2, ',', '.').'%' : '—' }}</td>
                        <td class="px-3 py-2 text-right text-gray-900 font-semibold">{{ $fmtMoeda($item['valor_total']) }}</td>
                        <td class="px-3 py-2">
                            @if($item['tem_catalogo'])
                                <span class="text-[11px] text-gray-600">{{ $item['catalogo']['descr_item'] }}</span>
                            @else
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color: #b45309">sem catálogo</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="px-3 py-6 text-center text-gray-400 text-sm">Nenhum item movimentado no período/filtro.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
