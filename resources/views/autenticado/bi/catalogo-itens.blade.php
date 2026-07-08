@php
    $fmtMoeda = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');

    // Renderiza uma lista "a,b,c" como chips que quebram linha (fim da sobreposição de CFOP/CST
    // em coluna table-fixed estreita). CFOP recebe tinta por entrada/saída; cap em $max + "+N".
    $chips = function (?string $raw, bool $cfop = false, int $max = 6) {
        $vals = array_values(array_filter(array_map('trim', explode(',', (string) $raw)), fn ($v) => $v !== ''));
        if ($vals === []) {
            return '<span class="text-gray-400">—</span>';
        }
        $tint = [
            'entrada' => ['#eff6ff', '#1d4ed8'],
            'saida' => ['#ecfdf5', '#047857'],
            'indefinido' => ['#f3f4f6', '#374151'],
        ];
        $html = '<div class="flex flex-wrap gap-1">';
        foreach (array_slice($vals, 0, $max) as $v) {
            $tipo = $cfop ? \App\Support\Cfop::tipoOperacao($v) : 'indefinido';
            [$bg, $fg] = $tint[$tipo];
            // title por chip: daltônico não distingue entrada(azul)/saída(verde) só pela cor.
            $title = $cfop && $tipo !== 'indefinido' ? ' title="'.e($v.' — '.$tipo).'"' : '';
            $html .= '<span class="font-mono text-[10px] leading-none px-1.5 py-1 rounded"'.$title.' style="background-color:'.$bg.';color:'.$fg.'">'.e($v).'</span>';
        }
        if (count($vals) > $max) {
            $html .= '<span class="text-[10px] leading-none px-1 py-1 text-gray-400 font-semibold">+'.(count($vals) - $max).'</span>';
        }

        return $html.'</div>';
    };

    // Cor do badge de origem (efd/xml/ambas) — reutilizado na tabela, nos cards e nos alertas.
    $origemCor = fn (string $fonte) => ['efd' => '#1d4ed8', 'xml' => '#7c3aed', 'ambas' => '#047857'][$fonte] ?? '#334155';
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Catálogo × Itens de Nota</h1>
                <p class="text-xs text-gray-500 mt-0.5">Itens movimentados nas notas (XML + EFD), cruzados com o catálogo do contribuinte.</p>
            </div>
            <x-acoes-menu label="Exportar" align="right" size="lg">
                <x-acoes-item href="{{ route('app.bi.catalogo-itens.exportar-xlsx', request()->query()) }}">Excel (XLSX)</x-acoes-item>
                <x-acoes-item href="{{ route('app.bi.catalogo-itens.exportar', request()->query()) }}">Excel (CSV)</x-acoes-item>
                <x-acoes-item href="{{ route('app.bi.catalogo-itens.exportar-pdf', request()->query()) }}">PDF</x-acoes-item>
            </x-acoes-menu>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-5">
            {{-- $alerta: KPIs de problema pintam o número (e a borda) só quando > 0 — zero problemas fica
                 mudo, então o olho vai direto pro que precisa de ação, sem 3 blocos âmbar idênticos. --}}
            @foreach([
                ['Itens movimentados', $kpis['total_itens'], '#1d4ed8', false],
                ['Com catálogo', $kpis['com_catalogo'], '#047857', false],
                ['Sem catálogo', $kpis['sem_catalogo'], '#b45309', true],
                ['Sem NCM', $kpis['sem_ncm'], '#b45309', true],
                ['NCM a revisar', $kpis['ncm_revisar'] ?? 0, '#b45309', true],
            ] as [$label, $valor, $cor, $alerta])
                @php $ativo = ! $alerta || $valor > 0; @endphp
                <div class="bg-white rounded border border-gray-300 border-l-4 p-3" style="border-left-color: {{ $ativo ? $cor : '#d1d5db' }}">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $label }}</p>
                    <p class="text-lg font-bold" style="color: {{ $alerta && $valor > 0 ? $cor : ($ativo ? '#111827' : '#9ca3af') }}">{{ number_format($valor, 0, ',', '.') }}</p>
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

        {{-- Toggle dispensados --}}
        @if($mostrarDispensados)
            <div class="mb-4 text-[12px]"><a href="/app/bi/catalogo-itens" data-link class="text-blue-600">← Voltar (ocultar ignorados)</a> <span class="text-gray-400">— alertas ignorados aparecem com opacidade; use “Restaurar” para reativar.</span></div>
        @elseif($totalDispensados > 0)
            <div class="mb-4 text-[12px]"><a href="/app/bi/catalogo-itens?dispensados=1" data-link class="text-blue-600">Mostrar {{ $totalDispensados }} alerta(s) ignorado(s)</a></div>
        @endif

        {{-- NCM a revisar (documento × cadastro) --}}
        @if($divergencias->isNotEmpty())
            <x-catalogo.painel-alerta
                titulo="NCM a revisar (documento × cadastro)"
                :contagem="$divergencias->where('dispensado', false)->count()"
                :colunas="['Código', 'Descrição', 'NCM documento', 'NCM cadastro', 'Importação', 'Ação']">
                <x-slot:ajuda>O NCM informado no documento (XML) difere do cadastrado no seu catálogo (registro 0200). Pode gerar tributação/ST incorreta e malha fiscal. <strong>Como corrigir:</strong> confirme o NCM correto do produto e ajuste o cadastro 0200 na próxima EFD — ou corrija a emissão, se o documento estiver errado. Dispense se já conferiu/corrigiu.</x-slot:ajuda>
                @foreach($divergencias as $d)
                    <tr data-alerta-row @if($d['dispensado']) style="opacity:.5" @endif>
                        <td class="px-3 py-2 font-mono text-gray-900">{{ $d['codigo_item'] }}</td>
                        <td class="px-3 py-2 text-gray-700 truncate max-w-xs" title="{{ $d['descricao'] }}">{{ $d['descricao'] ?: '—' }}</td>
                        <td class="px-3 py-2 font-mono font-semibold" style="color:#b45309">{{ $d['ncm_xml'] ?: '—' }}</td>
                        <td class="px-3 py-2 font-mono text-gray-600">{{ $d['cat_ncm'] ?: '—' }}</td>
                        <td class="px-3 py-2 text-[11px]"><div class="max-w-[200px] truncate text-blue-600 underline" title="{{ $d['importacoes'] }}">{{ $d['importacoes'] ?: '—' }}</div></td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            <x-catalogo.botao-alerta tipo="ncm_divergente" :codigo="$d['codigo_item']" :dispensado="$d['dispensado']" />
                        </td>
                    </tr>
                @endforeach
            </x-catalogo.painel-alerta>
        @endif

        {{-- Itens sem catálogo (0200) --}}
        @if($semCatalogo->isNotEmpty())
            <x-catalogo.painel-alerta
                titulo="Itens sem catálogo (0200)"
                :contagem="$semCatalogo->where('dispensado', false)->count()"
                :colunas="['Código', 'Descrição', 'Origem', 'Importação', 'Ação']">
                <x-slot:ajuda>Código movimentado em nota mas fora do seu registro 0200. <strong>Serviços</strong> (ex.: montagem) não exigem 0200 — pode ignorar. <strong>Produtos</strong> devem ser cadastrados no 0200 para consistência fiscal e para casar NCM/alíquota.</x-slot:ajuda>
                @foreach($semCatalogo as $i)
                    <tr data-alerta-row @if($i['dispensado']) style="opacity:.5" @endif>
                        <td class="px-3 py-2 font-mono text-gray-900">{{ $i['codigo_item'] }}</td>
                        <td class="px-3 py-2 text-gray-700 truncate max-w-xs" title="{{ $i['descricao'] }}">{{ $i['descricao'] ?: '—' }}</td>
                        <td class="px-3 py-2"><span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color: {{ $origemCor($i['fontes']) }}">{{ $i['fontes'] }}</span></td>
                        <td class="px-3 py-2 text-[11px]"><div class="max-w-[200px] truncate text-blue-600 underline" title="{{ $i['importacoes'] }}">{{ $i['importacoes'] ?: '—' }}</div></td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            <x-catalogo.botao-alerta tipo="sem_catalogo" :codigo="$i['codigo_item']" :dispensado="$i['dispensado']" />
                        </td>
                    </tr>
                @endforeach
            </x-catalogo.painel-alerta>
        @endif

        {{-- Filtros (padrão /app/clientes) --}}
        @php
            $cfopsSel = $filtros['cfops'] ?? [];
            $cstsSel = $filtros['csts'] ?? [];
        @endphp
        <form method="GET" class="bg-white rounded border border-gray-300 p-3 mb-4 space-y-3">
            {{-- contexto + CFOP/CST (dropdowns multi-select, alinhados) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
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

                {{-- CFOP --}}
                <x-multi-select-pop grupo="cfop" label="CFOP" :selecionados="$cfopsSel" width="w-80"
                    placeholder="buscar código ou descrição…" :temOpcoes="count($cfopOpcoes) > 0">
                    @forelse($cfopOpcoes as $cf)
                        <label data-row data-search="{{ strtolower($cf['codigo'].' '.$cf['descricao']) }}" class="flex items-center gap-2 px-2.5 py-1.5 text-[12px] cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="cfops[]" value="{{ $cf['codigo'] }}" onchange="catFiltro.contar('cfop')" @checked(in_array($cf['codigo'], $cfopsSel, true))>
                            <span class="font-mono font-semibold text-gray-900">{{ $cf['codigo'] }}</span>
                            @if($cf['tipo'] !== 'indefinido')
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase text-white shrink-0" style="background-color: {{ $cf['tipo'] === 'entrada' ? '#1d4ed8' : '#047857' }}">{{ $cf['tipo'] }}</span>
                            @endif
                            <span class="text-gray-600 truncate" title="{{ $cf['descricao'] }}">{{ $cf['descricao'] ?: '—' }}</span>
                        </label>
                    @empty
                        <span class="block px-2.5 py-2 text-[11px] text-gray-400">Sem dados no período/filtro.</span>
                    @endforelse
                </x-multi-select-pop>

                {{-- CST --}}
                <x-multi-select-pop grupo="cst" label="CST" :selecionados="$cstsSel" width="w-52"
                    panelAlign="right-0 lg:left-0" :temOpcoes="count($facetas['csts'] ?? []) > 0">
                    @forelse($facetas['csts'] ?? [] as $ct)
                        <label data-row data-search="{{ strtolower($ct) }}" class="flex items-center gap-2 px-2.5 py-1.5 text-[12px] cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="csts[]" value="{{ $ct }}" onchange="catFiltro.contar('cst')" @checked(in_array($ct, $cstsSel, true))>
                            <span class="font-mono font-semibold text-gray-900">{{ $ct }}</span>
                        </label>
                    @empty
                        <span class="block px-2.5 py-2 text-[11px] text-gray-400">Sem dados.</span>
                    @endforelse
                </x-multi-select-pop>
            </div>

            {{-- ações --}}
            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-200">
                <button type="submit" class="bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium px-4 py-2">Filtrar</button>
                @if(array_filter($filtros))
                    <a href="/app/bi/catalogo-itens" data-link class="bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded text-sm font-medium px-4 py-2">Limpar</a>
                @endif
            </div>
        </form>

        {{-- Contagem/intervalo (reflete o filtro atual; sem filtro = todos) --}}
        <div class="mb-2">
            <p class="text-[12px] text-gray-500">
                @if($itensPaginados->total() > 0)
                    {{ number_format($itensPaginados->firstItem(), 0, ',', '.') }}–{{ number_format($itensPaginados->lastItem(), 0, ',', '.') }}
                    de {{ number_format($itensPaginados->total(), 0, ',', '.') }} item(ns)
                @else
                    0 item(ns)
                @endif
                · {{ $fmtMoeda($kpis['valor_movimentado']) }}
            </p>
        </div>

        {{-- Tabela (md+) --}}
        <div class="bg-white rounded border border-gray-300 overflow-x-auto hidden md:block">
            <table class="w-full text-sm table-fixed">
                <caption class="sr-only">Itens movimentados em notas cruzados com o catálogo</caption>
                <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                    <tr>
                        <th scope="col" class="text-left px-3 py-2.5" style="width:9%">Código</th>
                        <th scope="col" class="text-left px-3 py-2.5" style="width:15%">Descrição</th>
                        <th scope="col" class="text-left px-3 py-2.5" style="width:5%">Origem</th>
                        <th scope="col" class="text-left px-3 py-2.5 hidden lg:table-cell" style="width:10%">Arquivo de origem</th>
                        <th scope="col" class="text-left px-3 py-2.5 hidden sm:table-cell" style="width:7%">NCM</th>
                        <th scope="col" class="text-left px-3 py-2.5 hidden md:table-cell" style="width:10%">CFOP</th>
                        <th scope="col" class="text-left px-3 py-2.5 hidden xl:table-cell" style="width:7%">CST</th>
                        <th scope="col" class="text-right px-3 py-2.5 hidden sm:table-cell" style="width:4%">Qtd</th>
                        <th scope="col" class="text-right px-3 py-2.5 hidden lg:table-cell" style="width:4%">Ocorr.</th>
                        <th scope="col" class="text-right px-3 py-2.5 hidden md:table-cell" style="width:6%">Alíq. méd.</th>
                        <th scope="col" class="text-right px-3 py-2.5" style="width:12%">Valor movimentado</th>
                        <th scope="col" class="text-left px-3 py-2.5" style="width:11%">Catálogo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($itensPaginados as $item)
                    <tr>
                        <td class="px-3 py-2 font-mono text-gray-900 align-top break-all">{{ $item['codigo_item'] }}</td>
                        <td class="px-3 py-2 text-gray-700 align-top truncate" title="{{ $item['descricao'] }}">{{ $item['descricao'] ?: '—' }}</td>
                        <td class="px-3 py-2 align-top">
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color: {{ $origemCor($item['fontes']) }}">{{ $item['fontes'] }}</span>
                        </td>
                        <td class="px-3 py-2 text-[11px] align-top hidden lg:table-cell">
                            @php $imps = collect($item['importacoes']); @endphp
                            <div class="max-w-[200px] space-y-0.5" @if($imps->count() > 2) title="{{ $imps->pluck('label')->implode(' · ') }}" @endif>
                                @forelse($imps->take(2) as $imp)
                                    <a href="{{ $imp['fonte'] === 'xml' ? route('app.importacao.xml.detalhes', $imp['id']) : route('app.importacao.efd.detalhes', $imp['id']) }}" data-link class="block truncate text-blue-600 underline cursor-pointer" title="{{ $imp['label'] }}">{{ $imp['label'] }}</a>
                                @empty
                                    <span class="text-gray-400">—</span>
                                @endforelse
                                @if($imps->count() > 2)
                                    <span class="block text-[10px] text-gray-400 font-semibold">+{{ $imps->count() - 2 }} outra(s)</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-2 font-mono text-gray-600 align-top hidden sm:table-cell">{{ $item['ncm'] ?: '—' }}</td>
                        <td class="px-3 py-2 align-top hidden md:table-cell">{!! $chips($item['cfops'], cfop: true) !!}</td>
                        <td class="px-3 py-2 align-top hidden xl:table-cell">{!! $chips($item['csts']) !!}</td>
                        <td class="px-3 py-2 text-right text-gray-700 align-top hidden sm:table-cell">{{ number_format($item['quantidade'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right text-gray-700 align-top hidden lg:table-cell">{{ $item['ocorrencias'] }}</td>
                        <td class="px-3 py-2 text-right text-gray-600 align-top hidden md:table-cell">{{ $item['aliquota_media'] !== null ? number_format($item['aliquota_media'], 2, ',', '.').'%' : '—' }}</td>
                        <td class="px-3 py-2 text-right text-gray-900 font-semibold align-top whitespace-nowrap">{{ $fmtMoeda($item['valor_total']) }}</td>
                        <td class="px-3 py-2 align-top truncate">
                            @if($item['tem_catalogo'])
                                <span class="text-[11px] text-gray-600" title="{{ $item['catalogo']['descr_item'] }}">{{ $item['catalogo']['descr_item'] }}</span>
                            @else
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color: #b45309">sem catálogo</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="px-3 py-6 text-center text-gray-400 text-sm">Nenhum item movimentado no período/filtro.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Cards (mobile) --}}
        <div class="md:hidden space-y-2">
            @forelse($itensPaginados as $item)
                <div class="bg-white rounded border border-gray-300 p-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-mono text-[13px] text-gray-900 break-all leading-tight">{{ $item['codigo_item'] }}</p>
                            <p class="text-[12px] text-gray-600 mt-0.5 line-clamp-2">{{ $item['descricao'] ?: '—' }}</p>
                        </div>
                        <span class="shrink-0 inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color: {{ $origemCor($item['fontes']) }}">{{ $item['fontes'] }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-2 mt-2 pt-2 border-t border-gray-100">
                        <div class="min-w-0">
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide">NCM</p>
                            <p class="font-mono text-[12px] text-gray-700">{{ $item['ncm'] ?: '—' }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide">Valor movimentado</p>
                            <p class="text-[13px] font-semibold text-gray-900 whitespace-nowrap">{{ $fmtMoeda($item['valor_total']) }}</p>
                        </div>
                    </div>

                    <div class="mt-2">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wide mb-1">CFOP</p>
                        {!! $chips($item['cfops'], cfop: true, max: 8) !!}
                    </div>

                    <div class="mt-2 pt-2 border-t border-gray-100">
                        @if($item['tem_catalogo'])
                            <p class="text-[9px] text-gray-400 uppercase tracking-wide">Catálogo</p>
                            <p class="text-[12px] text-gray-600 line-clamp-1">{{ $item['catalogo']['descr_item'] }}</p>
                        @else
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color: #b45309">sem catálogo</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded border border-gray-300 px-3 py-6 text-center text-gray-400 text-sm">Nenhum item movimentado no período/filtro.</div>
            @endforelse
        </div>

        @if($itensPaginados->hasPages())
            <div class="mt-4">
                {{ $itensPaginados->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

    {{-- Modal de confirmação de dispensa --}}
    <x-modal id="catalogoAlertaModal" titulo="Ignorar alerta?">
        <p class="text-[12px] text-gray-500 mb-4">O alerta sai da lista e das contagens. Você pode revê-lo depois em “Mostrar ignorados”.</p>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="catalogoAlerta.cancelar()" class="px-3 py-1.5 text-[12px] rounded border border-gray-300 text-gray-600">Cancelar</button>
            <button type="button" onclick="catalogoAlerta.confirmar()" class="px-3 py-1.5 text-[12px] rounded text-white font-semibold" style="background-color:#dc2626">Ignorar</button>
        </div>
    </x-modal>
</div>

<script>
window.catalogoAlerta = window.catalogoAlerta || (function () {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let pendente = null;
    const modal = () => document.getElementById('catalogoAlertaModal');
    async function post(url, body) {
        try {
            const r = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(body),
            });
            return r.ok;
        } catch (e) {
            return false;
        }
    }
    // Refresh parcial via SPA (preserva o shell/sidebar) com fallback pro reload duro.
    const recarregar = () => (typeof window.navigateTo === 'function'
        ? window.navigateTo(window.location.href)
        : window.location.reload());
    return {
        pedir(tipo, codigo) { pendente = { tipo, codigo }; modal()?.classList.remove('hidden'); },
        cancelar() { pendente = null; modal()?.classList.add('hidden'); },
        async confirmar() {
            if (!pendente) return;
            const ok = await post('/app/bi/catalogo-itens/alerta/descartar', { tipo: pendente.tipo, codigo_item: pendente.codigo });
            modal()?.classList.add('hidden');
            pendente = null;
            ok ? recarregar() : alert('Não foi possível ignorar o alerta.');
        },
        async restaurar(tipo, codigo) {
            const ok = await post('/app/bi/catalogo-itens/alerta/restaurar', { tipo, codigo_item: codigo });
            ok ? recarregar() : alert('Não foi possível restaurar o alerta.');
        },
    };
})();

// Filtros CFOP/CST: busca incremental, marcar/limpar visíveis e contador de seleção.
// Opera só sobre elementos do próprio render (sem listeners em document/window) → SPA-safe.
window.catFiltro = window.catFiltro || (function () {
    const box = (g) => document.getElementById(g + 'Box');
    const rows = (g) => box(g) ? Array.from(box(g).querySelectorAll('[data-row]')) : [];
    function contar(g) {
        const el = document.getElementById(g + 'Count');
        if (!el) return;
        const n = rows(g).filter((r) => r.querySelector('input[type=checkbox]')?.checked).length;
        el.textContent = n ? `${n} selecionado${n > 1 ? 's' : ''}` : '';
    }
    return {
        contar,
        buscar(g, q) {
            q = (q || '').toLowerCase().trim();
            rows(g).forEach((r) => { r.style.display = (!q || (r.dataset.search || '').includes(q)) ? '' : 'none'; });
        },
        // toggla só as linhas visíveis (respeita a busca ativa)
        marcar(g, val) {
            rows(g).forEach((r) => {
                if (r.style.display === 'none') return;
                const c = r.querySelector('input[type=checkbox]');
                if (c) c.checked = val;
            });
            contar(g);
        },
    };
})();
catFiltro.contar('cfop');
catFiltro.contar('cst');

// Dropdowns CFOP/CST: abre/fecha painel ancorado; fecha ao clicar fora/em outro. SPA-safe (cleanup).
(function () {
    if (window._cleanupFunctions && window._cleanupFunctions.catalogoItensPops) {
        window._cleanupFunctions.catalogoItensPops();
    }

    function fecharPops(exceto) {
        document.querySelectorAll('[data-pop-panel]').forEach(function (p) {
            if (p === exceto) return;
            p.classList.add('hidden');
            var ch = p.parentElement.querySelector('[data-pop-chevron]');
            if (ch) ch.style.transform = '';
        });
    }

    function handlePopClick(e) {
        var toggle = e.target.closest('[data-pop-toggle]');
        if (toggle) {
            e.preventDefault();
            var panel = toggle.parentElement.querySelector('[data-pop-panel]');
            var aberto = !panel.classList.contains('hidden');
            fecharPops();
            if (!aberto) {
                panel.classList.remove('hidden');
                var ch = toggle.querySelector('[data-pop-chevron]');
                if (ch) ch.style.transform = 'rotate(180deg)';
            }
            return;
        }
        if (!e.target.closest('[data-pop-panel]')) fecharPops();
    }

    document.addEventListener('click', handlePopClick);

    if (!window._cleanupFunctions) window._cleanupFunctions = {};
    window._cleanupFunctions.catalogoItensPops = function () {
        document.removeEventListener('click', handlePopClick);
    };
})();
</script>
