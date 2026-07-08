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

    // Só o item XML carrega NCM; o EFD resolve pelo catálogo 0200. Com fonte explícita o cabeçalho
    // diz de onde veio o número — em `ambas` a coluna mistura (XML órfão traz o documentado).
    $fonteSel = $filtros['fonte'] ?? null;
    $ncmLabel = match ($fonteSel) { 'xml' => 'NCM (documento)', 'efd' => 'NCM (catálogo)', default => 'NCM' };
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Catálogo × Itens de Nota</h1>
                <p class="text-xs text-gray-500 mt-0.5">Itens movimentados nas notas (XML + EFD), cruzados com o catálogo do contribuinte.</p>
            </div>
            {{-- Botão único Exportar → modal de formato → overlay (preserva os filtros da URL). --}}
            @php $qsCatalogo = http_build_query(request()->query()); @endphp
            <x-export-menu id="modal-exportar-catalogo" titulo="Exportar catálogo × itens"
                           descricao="O arquivo preserva os filtros aplicados na tela."
                           overlay="download-overlay-catalogo">
                <x-export-grupo label="Documento" />
                <x-export-option format="pdf" modal-id="modal-exportar-catalogo" overlay="download-overlay-catalogo"
                                 path="{{ route('app.bi.catalogo-itens.exportar-pdf') }}" query="{{ $qsCatalogo }}"
                                 descricao="Itens movimentados, em uma folha." />
                <x-export-grupo label="Planilhas" />
                <x-export-option format="xlsx" modal-id="modal-exportar-catalogo" overlay="download-overlay-catalogo"
                                 path="{{ route('app.bi.catalogo-itens.exportar-xlsx') }}" query="{{ $qsCatalogo }}"
                                 descricao="Uma linha por item: NCM, CFOPs, CSTs, quantidade e valor." />
                <x-export-option format="csv" modal-id="modal-exportar-catalogo" overlay="download-overlay-catalogo"
                                 path="{{ route('app.bi.catalogo-itens.exportar') }}" query="{{ $qsCatalogo }}"
                                 descricao="Mesmas colunas do XLSX, uma linha por item." />
            </x-export-menu>
        </div>

        <x-download-overlay id="download-overlay-catalogo" texto="Gerando arquivo…" />

        {{-- ── Resumo: herói (valor) + cobertura em % com barra ─────────────────
             Antes eram 6 cards de mesmo peso (3 âmbar idênticos competindo). Agora o
             valor movimentado é o número herói; "com catálogo/sem NCM" viram % de
             COBERTURA (mais legível que contagem crua); e as 3 pendências foram
             agrupadas num único painel abaixo. Tudo derivado de $kpis — sem tocar backend. --}}
        @php
            $tot         = (int) $kpis['total_itens'];
            $totSafe     = max(1, $tot);
            $comCatalogo = (int) $kpis['com_catalogo'];
            $comNcm      = $tot - (int) $kpis['sem_ncm'];
            $pctCatalogo = (int) round($comCatalogo / $totSafe * 100);
            $pctNcm      = (int) round($comNcm / $totSafe * 100);
            $pendencias  = (int) $kpis['sem_catalogo'] + (int) $kpis['sem_ncm'] + (int) ($kpis['ncm_revisar'] ?? 0);
            // faixa de cobertura → cor: alta verde, média âmbar, baixa vermelha
            $corPct = fn (int $p) => $p >= 90 ? '#047857' : ($p >= 60 ? '#b45309' : '#dc2626');
        @endphp
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-3 mb-3">
            {{-- HERO: valor movimentado --}}
            <div class="bg-white rounded border border-gray-300 border-l-4 p-4 lg:col-span-2 flex flex-col justify-center" style="border-left-color:#334155">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Valor movimentado</p>
                <p class="text-2xl sm:text-3xl font-bold text-gray-900 tabular-nums mt-1">{{ $fmtMoeda($kpis['valor_movimentado']) }}</p>
                <p class="text-[11px] text-gray-500 mt-1">{{ number_format($tot, 0, ',', '.') }} item(ns) movimentado(s) no período/filtro</p>
            </div>

            {{-- Cobertura de catálogo (0200) --}}
            <div class="bg-white rounded border border-gray-300 p-4 flex flex-col justify-center">
                <div class="flex items-baseline justify-between gap-2">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Cobertura de catálogo</p>
                    <p class="text-lg font-bold tabular-nums" style="color:{{ $corPct($pctCatalogo) }}">{{ $pctCatalogo }}%</p>
                </div>
                <div class="h-1.5 rounded-full mt-2 overflow-hidden" style="background-color:#f3f4f6">
                    <div class="h-full rounded-full" style="width:{{ $pctCatalogo }}%;background-color:{{ $corPct($pctCatalogo) }}"></div>
                </div>
                <p class="text-[11px] text-gray-500 mt-1.5">{{ number_format($comCatalogo, 0, ',', '.') }} de {{ number_format($tot, 0, ',', '.') }} com registro 0200</p>
            </div>

            {{-- Cobertura de NCM --}}
            <div class="bg-white rounded border border-gray-300 p-4 flex flex-col justify-center">
                <div class="flex items-baseline justify-between gap-2">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Itens com NCM</p>
                    <p class="text-lg font-bold tabular-nums" style="color:{{ $corPct($pctNcm) }}">{{ $pctNcm }}%</p>
                </div>
                <div class="h-1.5 rounded-full mt-2 overflow-hidden" style="background-color:#f3f4f6">
                    <div class="h-full rounded-full" style="width:{{ $pctNcm }}%;background-color:{{ $corPct($pctNcm) }}"></div>
                </div>
                <p class="text-[11px] text-gray-500 mt-1.5">{{ number_format($comNcm, 0, ',', '.') }} de {{ number_format($tot, 0, ',', '.') }} com NCM preenchido</p>
            </div>
        </div>

        {{-- Pendências agrupadas: um painel só (fim dos 3 blocos âmbar). Cada número
             só ganha cor quando > 0; tudo-certo mostra selo verde. --}}
        <div class="bg-white rounded border border-gray-300 p-3 mb-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Pendências de catálogo</p>
                @if($pendencias === 0)
                    <span class="text-[11px] font-semibold" style="color:#047857">✓ Nenhuma pendência</span>
                @endif
            </div>
            <div class="grid grid-cols-3 divide-x divide-gray-100">
                @foreach([
                    ['Sem catálogo', $kpis['sem_catalogo']],
                    ['Sem NCM', $kpis['sem_ncm']],
                    ['NCM a revisar', $kpis['ncm_revisar'] ?? 0],
                ] as [$label, $valor])
                    <div class="px-3 first:pl-0">
                        <p class="text-lg font-bold tabular-nums" style="color:{{ $valor > 0 ? '#b45309' : '#9ca3af' }}">{{ number_format($valor, 0, ',', '.') }}</p>
                        <p class="text-[10px] text-gray-500 uppercase tracking-wide">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        @if(($reconciliacao['documentadas'] ?? 0) > 0)
            {{-- Reconciliação XML×EFD por chave. As 3 categorias particionam `documentadas`
                 (reconciliadas + divergência + não declaradas = total), então a barra empilhada
                 é fiel. Headline = taxa de reconciliação; cada cor tem sublabel do critério. --}}
            @php
                $rDoc  = (int) $reconciliacao['documentadas'];
                $rOk   = (int) $reconciliacao['reconciliadas'];
                $rDiv  = (int) $reconciliacao['divergencia_total'];
                $rNao  = (int) $reconciliacao['nao_declaradas'];
                $rBase = max(1, $rDoc);
                $pctOk = (int) round($rOk / $rBase * 100);
                $corTaxa = $pctOk >= 90 ? '#047857' : ($pctOk >= 60 ? '#b45309' : '#dc2626');
            @endphp
            <div class="bg-white rounded border border-gray-300 p-4 mb-4">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Reconciliação documento × declarado</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">XML (contador) × EFD (SPED), por chave de acesso</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xl font-bold tabular-nums leading-none" style="color:{{ $corTaxa }}">{{ $pctOk }}%</p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide mt-1">reconciliado</p>
                    </div>
                </div>

                {{-- barra empilhada: verde reconciliadas / âmbar divergência / vermelho não declaradas --}}
                <div class="flex h-2.5 rounded-full overflow-hidden mb-4" style="background-color:#f3f4f6">
                    @if($rOk > 0)<div style="width:{{ $rOk / $rBase * 100 }}%;background-color:#047857" title="Reconciliadas"></div>@endif
                    @if($rDiv > 0)<div style="width:{{ $rDiv / $rBase * 100 }}%;background-color:#b45309" title="Divergência de total"></div>@endif
                    @if($rNao > 0)<div style="width:{{ $rNao / $rBase * 100 }}%;background-color:#dc2626" title="Não declaradas"></div>@endif
                </div>

                {{-- legenda com contagem + critério --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach([
                        ['Documentadas (XML)', $rDoc, '#334155', 'base do cruzamento'],
                        ['Reconciliadas', $rOk, '#047857', 'total XML = EFD'],
                        ['Divergência de total', $rDiv, '#b45309', 'total XML ≠ EFD'],
                        ['Não declaradas', $rNao, '#dc2626', 'XML sem EFD'],
                    ] as [$lbl, $val, $cor, $sub])
                        <div class="flex items-start gap-2">
                            <span class="w-2 h-2 rounded-full mt-1.5 shrink-0" style="background-color:{{ $cor }}"></span>
                            <div class="min-w-0">
                                <p class="text-lg font-bold tabular-nums leading-none" style="color:{{ $cor }}">{{ number_format($val, 0, ',', '.') }}</p>
                                <p class="text-[10px] text-gray-500 uppercase tracking-wide mt-1">{{ $lbl }}</p>
                                <p class="text-[10px] text-gray-400">{{ $sub }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if(($reconciliacao['efd_sem_xml'] ?? 0) > 0)
                    <p class="text-[11px] text-gray-400 mt-3 pt-3 border-t border-gray-100">Cobertura: {{ number_format($reconciliacao['efd_sem_xml'], 0, ',', '.') }} nota(s) declarada(s) no EFD sem XML no acervo (informativo, não é alerta).</p>
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
            $ncmsSel = $filtros['ncms'] ?? [];
            $ncmAusente = ! empty($filtros['ncm_ausente']);
            // máscara NCM 4.2.2 só para exibição (o value do checkbox é o código cru de 8 dígitos)
            $fmtNcm = function ($n) {
                $d = preg_replace('/\D/', '', (string) $n);
                return strlen($d) === 8 ? substr($d, 0, 4).'.'.substr($d, 4, 2).'.'.substr($d, 6, 2) : $d;
            };
        @endphp
        @php
            // Avançados = dimensões fiscais (CFOP/CST/NCM). Contagem p/ o badge do "Mais filtros";
            // começa expandido se algum estiver ativo (igual /app/clientes).
            $avancadosAtivos = (count($cfopsSel) > 0 ? 1 : 0) + (count($cstsSel) > 0 ? 1 : 0)
                + ((count($ncmsSel) > 0 || $ncmAusente) ? 1 : 0);
        @endphp
        {{-- Sem overflow-hidden (ao contrário de /app/clientes): os dropdowns CFOP/CST/NCM abrem
             painel absoluto que seria cortado. Header arredondado com rounded-t no lugar. --}}
        <form method="GET" class="bg-white rounded border border-gray-300 mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 rounded-t">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros</span>
            </div>
            <div class="p-4">
                {{-- Básicos (sempre visíveis): contexto do cruzamento --}}
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Cliente</label>
                        <select name="cliente_id" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                            <option value="">Todos</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}" @selected(($filtros['cliente_id'] ?? null) == $c->id)>{{ $c->razao_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Fonte</label>
                        <select name="fonte" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                            <option value="">Ambas</option>
                            <option value="efd" @selected(($filtros['fonte'] ?? null) === 'efd')>EFD</option>
                            <option value="xml" @selected(($filtros['fonte'] ?? null) === 'xml')>XML</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">De</label>
                        <input type="date" name="periodo_de" value="{{ $filtros['periodo_de'] ?? '' }}" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Até</label>
                        <input type="date" name="periodo_ate" value="{{ $filtros['periodo_ate'] ?? '' }}" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                    </div>
                </div>

                {{-- Toggle "Mais filtros" --}}
                <div class="mt-3">
                    <button type="button" onclick="var a=document.getElementById('filtros-avancados-cat'); a.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180');"
                        class="inline-flex items-center gap-1.5 text-[13px] text-gray-600 hover:text-gray-900 font-medium">
                        <svg class="w-3.5 h-3.5 transition-transform {{ $avancadosAtivos > 0 ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        Mais filtros
                        @if($avancadosAtivos > 0)
                            <span class="text-[10px] text-white rounded-full px-1.5 py-0.5" style="background-color:#374151;">{{ $avancadosAtivos }}</span>
                        @endif
                    </button>
                </div>

                {{-- Avançados (colapsável): dimensões fiscais CFOP/CST/NCM (multi-select) --}}
                <div id="filtros-avancados-cat" class="{{ $avancadosAtivos > 0 ? '' : 'hidden' }} grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mt-3 pt-4 border-t border-gray-200">
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
                        :temOpcoes="count($facetas['csts'] ?? []) > 0">
                        @forelse($facetas['csts'] ?? [] as $ct)
                            <label data-row data-search="{{ strtolower($ct) }}" class="flex items-center gap-2 px-2.5 py-1.5 text-[12px] cursor-pointer hover:bg-gray-50">
                                <input type="checkbox" name="csts[]" value="{{ $ct }}" onchange="catFiltro.contar('cst')" @checked(in_array($ct, $cstsSel, true))>
                                <span class="font-mono font-semibold text-gray-900">{{ $ct }}</span>
                            </label>
                        @empty
                            <span class="block px-2.5 py-2 text-[11px] text-gray-400">Sem dados.</span>
                        @endforelse
                    </x-multi-select-pop>

                    {{-- NCM (+ opção sentinela "Sem NCM (ausente)") --}}
                    <x-multi-select-pop grupo="ncm" label="NCM" :selecionados="array_merge($ncmsSel, $ncmAusente ? ['__sem__'] : [])"
                        width="w-72" placeholder="buscar NCM…" :temOpcoes="count($facetas['ncms'] ?? []) > 0">
                        <label data-row data-search="sem ncm ausente vazio" class="flex items-center gap-2 px-2.5 py-1.5 text-[12px] cursor-pointer hover:bg-amber-50" style="background-color:#fffbeb">
                            <input type="checkbox" name="ncms[]" value="__sem__" onchange="catFiltro.contar('ncm')" @checked($ncmAusente)>
                            <span class="font-semibold" style="color:#b45309">Sem NCM (ausente)</span>
                        </label>
                        @forelse($facetas['ncms'] ?? [] as $ncm)
                            <label data-row data-search="{{ $ncm.' '.$fmtNcm($ncm) }}" class="flex items-center gap-2 px-2.5 py-1.5 text-[12px] cursor-pointer hover:bg-gray-50">
                                <input type="checkbox" name="ncms[]" value="{{ $ncm }}" onchange="catFiltro.contar('ncm')" @checked(in_array($ncm, $ncmsSel, true))>
                                <span class="font-mono font-semibold text-gray-900">{{ $fmtNcm($ncm) }}</span>
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
                        <th scope="col" class="text-left px-3 py-2.5 hidden sm:table-cell" style="width:7%">{{ $ncmLabel }}</th>
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
catFiltro.contar('ncm');

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
