@php
    $fmtMoeda = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $flagCores = ['verde' => '#16a34a', 'amarelo' => '#b45309', 'vermelho' => '#dc2626', 'neutro' => '#6b7280', 'sem_dado' => '#9ca3af'];
    $flagLabels = ['verde' => 'OK', 'amarelo' => 'Atenção', 'vermelho' => 'Divergente', 'neutro' => 'Sem movimento', 'sem_dado' => 'Sem dado'];
    $fmtCompetencia = fn ($c) => \Illuminate\Support\Carbon::parse($c.'-01')->translatedFormat('m/Y');
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Cruzamentos Fiscais</h1>
                <p class="text-xs text-gray-500 mt-0.5">Consultas, notas e apuração cruzadas entre si: risco de fornecedor, receitas não tributadas, retenções na fonte e notas canceladas.</p>
            </div>
            {{-- Relatório A4: parecer executivo + fornecedor irregular × compras + notas canceladas + providências + metodologia.
                 Preserva os filtros da URL (cliente/período). Sem data-link (é download). --}}
            @php $qsCruzamentos = http_build_query(request()->only(['cliente_id', 'data_inicio', 'data_fim'])); @endphp
            <x-export-menu id="modal-exportar-cruzamentos" titulo="Exportar cruzamentos"
                           descricao="Relatório A4 com parecer, achados e providências — preserva os filtros da tela."
                           overlay="download-overlay-cruzamentos">
                <x-export-option format="pdf" modal-id="modal-exportar-cruzamentos" overlay="download-overlay-cruzamentos"
                                 path="{{ route('app.bi.cruzamentos.exportar-pdf') }}" query="{{ $qsCruzamentos }}"
                                 descricao="Fornecedores irregulares × compras, notas canceladas, parecer e checklist de providências." />
            </x-export-menu>
        </div>

        <x-download-overlay id="download-overlay-cruzamentos" texto="Gerando relatório…" />

        {{-- Como interpretamos: transparência + alinhamento de fonte com Score e Alertas --}}
        <details class="bg-white rounded border border-gray-300 border-l-4 mb-5 group" style="border-left-color: #2563eb;">
            <summary class="cursor-pointer px-4 py-3 flex items-center justify-between list-none hover:bg-gray-50">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-semibold text-gray-900">Como interpretamos esta tela</span>
                </div>
                <span class="text-[11px] font-semibold text-gray-500 group-open:hidden">Abrir</span>
                <span class="text-[11px] font-semibold text-gray-500 hidden group-open:inline">Fechar</span>
            </summary>
            <div class="border-t border-gray-200 px-4 py-4 space-y-3">
                <div>
                    <p class="text-[11px] font-bold text-gray-900 uppercase tracking-wide mb-0.5">Fornecedor irregular × compras</p>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        <strong>Irregular</strong> = a última consulta do CNPJ apontou certidão positiva (CND Federal, Estadual ou CNDT com débito)
                        ou situação cadastral não ativa (Baixada, Inapta, Suspensa, Nula). É a <strong>mesma classificação</strong> do
                        <a href="{{ route('app.risk.index') }}" data-link class="text-blue-600 hover:underline">Score de Risco</a> e da
                        <a href="{{ route('app.alertas') }}" data-link class="text-blue-600 hover:underline">Central de Alertas</a> — as telas nunca divergem.
                        <strong>Compras</strong> = entradas do EFD (sem contar duas vezes a nota que aparece no ICMS/IPI e no PIS/COFINS) + entradas de XML importado
                        (exceto devoluções; nota que já está no EFD não soma de novo). Só aparece aqui quem foi consultado <em>e</em> é fornecedor nas suas notas.
                    </p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-gray-900 uppercase tracking-wide mb-0.5">Receitas não tributadas (M400)</p>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Compara, mês a mês, o que foi <strong>declarado como receita não tributada</strong> no SPED Contribuições (registro M400: isenção,
                        alíquota zero, suspensão) com a <strong>soma dos itens de saída classificados como não tributados</strong> nas notas do mesmo arquivo (CST de PIS 04–09).
                        Os dois números deveriam bater. Diferença = receita possivelmente <strong>classificada errado</strong> — tributada lançada como isenta, ou o contrário.
                        O confronto é do PIS; o registro equivalente de COFINS (M800) não é extraído, mas na prática espelha o M400.
                    </p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-gray-900 uppercase tracking-wide mb-0.5">Retenções na fonte (F600)</p>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Tudo que <strong>retiveram de você</strong> na fonte (registro F600), agrupado por <strong>quem reteve</strong>.
                        Esse valor é <strong>crédito seu</strong> — compensável na apuração de cada tributo. O "Total retido" é o valor cheio do documento
                        e pode incluir outras contribuições além de PIS/COFINS (ex.: CSLL/IRRF na retenção de 4,65%). A coluna de regularidade mostra a situação
                        da fonte pagadora na última consulta; fonte "Não consultada" é um convite: consulte o CNPJ para saber com quem você está operando.
                    </p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-gray-900 uppercase tracking-wide mb-0.5">ICMS-ST nas compras × regime do fornecedor</p>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Compras com <strong>ICMS-ST destacado</strong> (substituição tributária, registro C190), agrupadas por fornecedor com o
                        <strong>regime tributário</strong> dele na última consulta. Serve pra conferir de quem vem o ST que você paga embutido —
                        fornecedor do Simples destacando ST, ou ST relevante de fornecedor não consultado, merece conferência de CEST/MVA.
                        Ao lado, o <strong>ST a recolher</strong> da sua própria apuração (registro E210), quando você é o substituto.
                    </p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-gray-900 uppercase tracking-wide mb-0.5">Estoque declarado (H010) × movimentação</p>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        O <strong>inventário mais recente</strong> de cada empresa (bloco H do SPED Fiscal), item a item, contra a
                        <strong>movimentação do item nas notas</strong> dos 12 meses até a data do inventário. Item parado em estoque sem nenhuma
                        entrada ou saída = capital imobilizado — ou saída sem escrituração do item. Limitação: saída lançada só no consolidado
                        (C190, sem detalhe de item) não conta como movimentação aqui.
                    </p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-gray-900 uppercase tracking-wide mb-0.5">Nota cancelada na SEFAZ</p>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Notas do seu acervo que constam <strong>canceladas na SEFAZ</strong> (via Clearance), lado a lado com a situação do emitente.
                        Nota cancelada de emitente irregular merece atenção antes de aproveitar crédito.
                    </p>
                </div>
                <p class="text-xs text-gray-500 leading-relaxed border-t border-gray-100 pt-2">
                    O confronto <strong>apuração declarada × notas</strong> (ICMS e PIS/COFINS apurados contra o que as notas escrituram) tem tela própria no
                    <a href="{{ route('app.resumo-fiscal') }}" data-link class="text-blue-600 hover:underline">Resumo Fiscal</a>, por competência — mesma fonte de dados, por isso não repetimos aqui.
                </p>
            </div>
        </details>

        {{-- Diagnóstico de cobertura: explica quando o cruzamento aparece (e por que pode estar vazio) --}}
        <div class="bg-white rounded border border-gray-300 border-l-4 p-3 mb-5" style="border-left-color: #0b1f3a">
            <div class="flex flex-wrap items-center gap-x-8 gap-y-2 text-sm">
                <span class="inline-flex items-baseline gap-1.5 text-gray-700"><strong class="text-base text-gray-900">{{ number_format($diagnostico['consultados_qtd'], 0, ',', '.') }}</strong><span>CNPJs consultados</span></span>
                <span class="inline-flex items-baseline gap-1.5 text-gray-700"><strong class="text-base text-gray-900">{{ number_format($diagnostico['fornecedores_entrada_qtd'], 0, ',', '.') }}</strong><span>fornecedores nas notas de entrada</span></span>
                <span class="inline-flex items-baseline gap-1.5 text-gray-700"><strong class="text-base text-gray-900">{{ number_format($diagnostico['fornecedores_consultados_qtd'], 0, ',', '.') }}</strong><span>consultados que são fornecedores</span></span>
            </div>
            @if($diagnostico['fornecedores_consultados_qtd'] === 0)
                <p class="text-[12px] text-gray-500 mt-2">
                    Os cruzamentos aparecem quando um CNPJ que você <strong>consultou</strong> também é <strong>fornecedor</strong> nas suas notas de entrada. Hoje não há sobreposição — não é erro, é cobertura de dado.
                    @if($diagnostico['fornecedores_entrada_qtd'] > 0)
                        Para alimentar esta tela, consulte os CNPJs dos seus fornecedores em <a href="{{ route('app.consulta.nova') }}" data-link class="text-blue-600 hover:underline">Consulta CNPJ</a>.
                    @endif
                </p>
            @endif
        </div>

        {{-- KPIs: um por cruzamento, derivados das mesmas coleções das seções (números batem por construção) --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-5">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo dos cruzamentos</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-6 divide-x divide-gray-200">
                <div class="px-4 py-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Fornecedores irregulares</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($resumo['irregulares_qtd'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">{{ $fmtMoeda($resumo['irregulares_valor']) }} em compras</p>
                </div>
                <div class="px-4 py-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">M400 × CST divergentes</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($resumo['nao_trib_divergentes_qtd'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Δ {{ $fmtMoeda($resumo['nao_trib_delta']) }} em {{ number_format($resumo['nao_trib_competencias_qtd'], 0, ',', '.') }} comp.</p>
                </div>
                <div class="px-4 py-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Retido na fonte (F600)</p>
                    <p class="text-lg font-bold text-gray-900">{{ $fmtMoeda($resumo['retencoes_total']) }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">{{ number_format($resumo['retencoes_fontes_qtd'], 0, ',', '.') }} fontes · {{ number_format($resumo['retencoes_nao_consultadas_qtd'], 0, ',', '.') }} não consultadas</p>
                </div>
                <div class="px-4 py-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">ICMS-ST nas compras</p>
                    <p class="text-lg font-bold text-gray-900">{{ $fmtMoeda($resumo['icms_st_total']) }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">{{ number_format($resumo['icms_st_fornecedores_qtd'], 0, ',', '.') }} fornecedores</p>
                </div>
                <div class="px-4 py-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Estoque sem giro 12m</p>
                    <p class="text-lg font-bold text-gray-900">{{ $fmtMoeda($estoque['parados_valor']) }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">{{ number_format($estoque['parados_qtd'], 0, ',', '.') }} itens parados</p>
                </div>
                <div class="px-4 py-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Canceladas na SEFAZ</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($resumo['canceladas_qtd'], 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">{{ $fmtMoeda($resumo['canceladas_valor']) }} em notas</p>
                </div>
            </div>
        </div>

        {{-- Filtros (padrão /app/clientes) --}}
        <form method="GET" class="bg-white rounded border border-gray-300 overflow-hidden mb-5" data-mobile-filters>
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros</span>
            </div>
            <div class="p-3 flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-3">
                <div class="min-w-0 w-full flex-1 sm:w-auto sm:min-w-[220px] sm:flex-none">
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Cliente</label>
                    <select name="cliente_id" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                        <option value="">Todos</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}" @selected(($filtros['cliente_id'] ?? null) == $c->id)>{{ $c->razao_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-0 w-full flex-1 sm:w-auto sm:min-w-[150px] sm:flex-none">
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Emissão de</label>
                    <input type="date" name="data_inicio" value="{{ $filtros['data_inicio'] ?? '' }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                </div>
                <div class="min-w-0 w-full flex-1 sm:w-auto sm:min-w-[150px] sm:flex-none">
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Emissão até</label>
                    <input type="date" name="data_fim" value="{{ $filtros['data_fim'] ?? '' }}" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                </div>
                <button type="submit" class="filtro-acao w-full sm:w-auto px-4 py-2.5 rounded text-[12px] font-bold uppercase tracking-wide text-white hover:opacity-90" style="background-color: #0b1f3a">Aplicar filtro</button>
                @if(! empty($filtros))
                    <a href="{{ route('app.bi.cruzamentos') }}" data-link class="text-[12px] text-gray-500 hover:underline self-center">Limpar</a>
                @endif
            </div>
        </form>

        {{-- 1. Fornecedor irregular × compras --}}
        <div class="bg-white rounded border border-gray-300 mb-5 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full" style="background-color: #dc2626"></span>
                <h2 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Fornecedor com certidão/situação irregular × compras</h2>
                @if($irregulares->isNotEmpty())
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $irregulares->count() }}</span>
                @endif
            </div>
            @if($irregulares->isEmpty())
                <p class="px-4 py-6 text-sm text-gray-500">Nenhum fornecedor com certidão ou situação irregular entre os que você comprou. Nada a tratar neste cruzamento.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm tabela-cards">
                        <thead class="bg-gray-50 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="text-left px-4 py-2">Fornecedor</th>
                                <th class="text-left px-4 py-2">Motivo</th>
                                <th class="text-right px-4 py-2">Comprado</th>
                                <th class="text-right px-4 py-2">Notas</th>
                                <th class="text-right px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($irregulares as $f)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-2.5">
                                        <div class="font-medium text-gray-900">{{ $f['razao_social'] }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $f['documento'] }}</div>
                                    </td>
                                    <td class="px-4 py-2.5" data-label="Motivo">
                                        @foreach($f['motivos'] as $m)
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold text-white mb-0.5" style="background-color: #dc2626">{{ $m }}</span>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono font-semibold text-gray-900" data-label="Comprado">{{ $fmtMoeda($f['valor_comprado']) }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-600" data-label="Notas">{{ $f['qtd_notas'] }}</td>
                                    <td class="px-4 py-2.5 text-right" data-label="">
                                        <button type="button"
                                                class="text-[11px] font-semibold text-blue-600 hover:underline whitespace-nowrap"
                                                onclick="cruzamentosDrill.toggle({{ $f['participante_id'] }}, this)">Ver documentos</button>
                                    </td>
                                </tr>
                                <tr id="drill-{{ $f['participante_id'] }}" class="hidden">
                                    <td colspan="5" class="px-4 py-3 bg-gray-50">
                                        <div class="text-[11px] text-gray-500" data-drill-corpo>Carregando…</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- 2. Receitas não tributadas (M400) × CST das saídas --}}
        <div class="bg-white rounded border border-gray-300 mb-5 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full" style="background-color: #b45309"></span>
                    <h2 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Receitas não tributadas declaradas (M400) × CST das saídas</h2>
                    @if($naoTributadas->isNotEmpty())
                        <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $naoTributadas->count() }}</span>
                    @endif
                </div>
                <span class="text-[10px] text-gray-400 uppercase tracking-wide whitespace-nowrap">PIS · por competência</span>
            </div>
            @if($naoTributadas->isEmpty())
                <p class="px-4 py-6 text-sm text-gray-500">Nenhuma apuração de PIS/COFINS com M400 no filtro atual. Este cruzamento depende da importação do SPED Contribuições.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm tabela-cards">
                        <thead class="bg-gray-50 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="text-left px-4 py-2">Competência</th>
                                <th class="text-right px-4 py-2">Declarado (M400)</th>
                                <th class="text-right px-4 py-2">Itens CST 04–09</th>
                                <th class="text-right px-4 py-2">Diferença</th>
                                <th class="text-right px-4 py-2">Δ%</th>
                                <th class="text-left px-4 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($naoTributadas as $m)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-2.5 font-medium text-gray-900" data-label="Competência">{{ $fmtCompetencia($m['competencia']) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-900" data-label="Declarado">{{ $fmtMoeda($m['declarado']) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-900" data-label="Itens CST">{{ $fmtMoeda($m['computado']) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-700" data-label="Diferença">{{ $fmtMoeda($m['delta']) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-700" data-label="Δ%">{{ number_format($m['delta_pct'], 2, ',', '.') }}%</td>
                                    <td class="px-4 py-2.5" data-label="Status">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold text-white" style="background-color: {{ $flagCores[$m['flag']] ?? '#6b7280' }}">{{ $flagLabels[$m['flag']] ?? $m['flag'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- 3. Retenções na fonte (F600) × fonte pagadora --}}
        <div class="bg-white rounded border border-gray-300 mb-5 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full" style="background-color: #0b1f3a"></span>
                    <h2 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Retenções na fonte (F600) × regularidade da fonte pagadora</h2>
                    @if($retencoesFonte->isNotEmpty())
                        <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $retencoesFonte->count() }}</span>
                    @endif
                </div>
                <span class="text-[10px] text-gray-400 uppercase tracking-wide whitespace-nowrap">crédito compensável</span>
            </div>
            @if($retencoesFonte->isEmpty())
                <p class="px-4 py-6 text-sm text-gray-500">Nenhuma retenção na fonte (F600) no filtro atual. Este cruzamento depende da importação do SPED Contribuições.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm tabela-cards">
                        <thead class="bg-gray-50 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="text-left px-4 py-2">Fonte pagadora</th>
                                <th class="text-right px-4 py-2">Retenções</th>
                                <th class="text-right px-4 py-2">PIS</th>
                                <th class="text-right px-4 py-2">COFINS</th>
                                <th class="text-right px-4 py-2">Total retido</th>
                                <th class="text-left px-4 py-2">Regularidade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($retencoesFonte as $f)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-2.5">
                                        @if($f['participante_id'])
                                            <a href="{{ route('app.participante', $f['participante_id']) }}" data-link class="font-medium text-blue-600 hover:underline">{{ $f['razao_social'] }}</a>
                                        @else
                                            <div class="font-medium text-gray-900">{{ $f['razao_social'] }}</div>
                                        @endif
                                        <div class="text-[11px] text-gray-400">{{ $f['cnpj'] }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-gray-600" data-label="Retenções">{{ $f['qtd'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-900" data-label="PIS">{{ $fmtMoeda($f['valor_pis']) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-900" data-label="COFINS">{{ $fmtMoeda($f['valor_cofins']) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono font-semibold text-gray-900" data-label="Total">{{ $fmtMoeda($f['valor_total']) }}</td>
                                    <td class="px-4 py-2.5" data-label="Regularidade">
                                        @if(! $f['consultada'])
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold text-white" style="background-color: #9ca3af">Não consultada</span>
                                        @elseif($f['motivos'] === [])
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold text-white" style="background-color: #16a34a">Regular</span>
                                        @else
                                            @foreach($f['motivos'] as $m)
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold text-white mb-0.5" style="background-color: #dc2626">{{ $m }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- 4. ICMS-ST nas compras × regime do fornecedor --}}
        <div class="bg-white rounded border border-gray-300 mb-5 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full" style="background-color: #7c3aed"></span>
                    <h2 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">ICMS-ST nas compras × regime do fornecedor</h2>
                    @if($icmsSt['fornecedores']->isNotEmpty())
                        <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $icmsSt['fornecedores']->count() }}</span>
                    @endif
                </div>
                <span class="text-[10px] text-gray-400 uppercase tracking-wide whitespace-nowrap">
                    ST a recolher (E210): <strong class="text-gray-600">{{ $fmtMoeda($icmsSt['e210_st_recolher']) }}</strong>
                </span>
            </div>
            @if($icmsSt['fornecedores']->isEmpty())
                <p class="px-4 py-6 text-sm text-gray-500">
                    Nenhuma compra com ICMS-ST destacado no filtro atual. Comum em comércio que compra de atacado com ST já retido na origem —
                    o cruzamento acende quando o SPED Fiscal escriturar base ou valor de ST nas entradas (registro C190).
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm tabela-cards">
                        <thead class="bg-gray-50 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="text-left px-4 py-2">Fornecedor</th>
                                <th class="text-left px-4 py-2">Regime tributário</th>
                                <th class="text-right px-4 py-2">Notas</th>
                                <th class="text-right px-4 py-2">Base ST</th>
                                <th class="text-right px-4 py-2">ICMS-ST</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($icmsSt['fornecedores'] as $f)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-2.5">
                                        @if($f['participante_id'])
                                            <a href="{{ route('app.participante', $f['participante_id']) }}" data-link class="font-medium text-blue-600 hover:underline">{{ $f['razao_social'] }}</a>
                                        @else
                                            <div class="font-medium text-gray-900">{{ $f['razao_social'] }}</div>
                                        @endif
                                        <div class="text-[11px] text-gray-400">{{ $f['documento'] }}</div>
                                    </td>
                                    <td class="px-4 py-2.5" data-label="Regime">
                                        @if($f['regime'] === null)
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold text-white" style="background-color: #9ca3af">Não consultado</span>
                                        @elseif($f['regime'] === 'Simples Nacional' || $f['regime'] === 'MEI')
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold text-white" style="background-color: #b45309">{{ $f['regime'] }}</span>
                                        @else
                                            <span class="text-gray-700">{{ $f['regime'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-gray-600" data-label="Notas">{{ $f['qtd_notas'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-900" data-label="Base ST">{{ $fmtMoeda($f['bc_st']) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono font-semibold text-gray-900" data-label="ICMS-ST">{{ $fmtMoeda($f['valor_st']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- 5. Estoque declarado (H010) × movimentação --}}
        <div class="bg-white rounded border border-gray-300 mb-5 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full" style="background-color: #0891b2"></span>
                    <h2 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Estoque declarado (H010) × movimentação do item</h2>
                    @if($estoque['itens_total'] > 0)
                        <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ number_format($estoque['itens_total'], 0, ',', '.') }}</span>
                    @endif
                </div>
                @if($estoque['itens_total'] > 0)
                    <span class="text-[10px] text-gray-400 uppercase tracking-wide whitespace-nowrap">
                        {{ number_format($estoque['parados_qtd'], 0, ',', '.') }} sem giro · <strong class="text-gray-600">{{ $fmtMoeda($estoque['parados_valor']) }}</strong> parados
                    </span>
                @endif
            </div>
            @if($estoque['itens']->isEmpty())
                <p class="px-4 py-6 text-sm text-gray-500">
                    Nenhum inventário (bloco H) importado no filtro atual. O bloco H aparece no SPED Fiscal de fevereiro
                    (inventário de 31/12) ou quando a empresa é obrigada a inventário periódico — importe o arquivo que o contém e o cruzamento acende.
                </p>
            @else
                @foreach($estoque['inventarios'] as $inv)
                    <p class="px-4 pt-3 text-[11px] text-gray-500">
                        <strong class="text-gray-700">{{ $inv->cliente_nome }}</strong> — inventário de {{ \Illuminate\Support\Carbon::parse((string) $inv->dt_inventario)->format('d/m/Y') }}
                    </p>
                @endforeach
                @if($estoque['itens_total'] > $estoque['itens']->count())
                    <p class="px-4 pt-1 text-[11px] text-gray-400">Mostrando os {{ $estoque['itens']->count() }} itens de maior valor (de {{ number_format($estoque['itens_total'], 0, ',', '.') }}).</p>
                @endif
                <div class="overflow-x-auto mt-2">
                    <table class="w-full text-sm tabela-cards">
                        <thead class="bg-gray-50 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="text-left px-4 py-2">Item</th>
                                <th class="text-right px-4 py-2">Qtd em estoque</th>
                                <th class="text-right px-4 py-2">Valor em estoque</th>
                                <th class="text-right px-4 py-2">Entradas 12m</th>
                                <th class="text-right px-4 py-2">Saídas 12m</th>
                                <th class="text-left px-4 py-2">Giro</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($estoque['itens'] as $item)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-2.5">
                                        <div class="font-medium text-gray-900">{{ $item['descricao'] ?? $item['cod_item'] }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $item['cod_item'] }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-gray-600" data-label="Qtd">{{ number_format($item['qtd'], 3, ',', '.') }} {{ $item['unid'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono font-semibold text-gray-900" data-label="Valor">{{ $fmtMoeda($item['vl_item']) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-900" data-label="Entradas">{{ $fmtMoeda($item['mov_entradas']) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-900" data-label="Saídas">{{ $fmtMoeda($item['mov_saidas']) }}</td>
                                    <td class="px-4 py-2.5" data-label="Giro">
                                        @if($item['sem_movimentacao'])
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold text-white" style="background-color: #b45309">Sem giro 12m</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold text-white" style="background-color: #16a34a">Com giro</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- 6. Nota cancelada SEFAZ × emitente --}}
        <div class="bg-white rounded border border-gray-300 mb-5 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full" style="background-color: #6b7280"></span>
                <h2 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Nota cancelada na SEFAZ × situação do emitente</h2>
                @if($canceladas->isNotEmpty())
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $canceladas->count() }}</span>
                @endif
            </div>
            @if($canceladas->isEmpty())
                <p class="px-4 py-6 text-sm text-gray-500">Nenhuma nota cancelada na SEFAZ no acervo verificado. Este cruzamento depende do clearance de notas (verificação SEFAZ).</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm tabela-cards">
                        <thead class="bg-gray-50 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="text-left px-4 py-2">Documento</th>
                                <th class="text-left px-4 py-2">Emitente</th>
                                <th class="text-left px-4 py-2">Situação do emitente</th>
                                <th class="text-right px-4 py-2">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($canceladas as $n)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-2.5 text-[11px] text-gray-600" data-label="Documento">{{ $n['numero'] }}<br><span class="text-gray-400">{{ $n['chave_acesso'] }}</span></td>
                                    <td class="px-4 py-2.5">
                                        <div class="font-medium text-gray-900">{{ $n['emit_nome'] }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $n['emit_cnpj'] }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-700" data-label="Situação">{{ $n['situacao_emitente'] ?? 'Não consultado' }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-900" data-label="Valor">{{ $n['valor'] !== null ? $fmtMoeda($n['valor']) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</div>
<script>
window.cruzamentosDrill = (function () {
    const base = @json(route('app.bi.cruzamentos', [], false));
    const filtros = @json($filtros ?? []);
    const carregados = new Set();

    const fmtMoeda = (v) => v == null ? '—'
        : 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const fmtData = (iso) => {
        if (!iso) return '—';
        const [a, m, d] = iso.split('-');
        return `${d}/${m}/${a}`;
    };

    function render(corpo, notas) {
        corpo.textContent = '';
        if (!notas.length) {
            corpo.textContent = 'Nenhum documento de compra no filtro atual.';
            return;
        }
        const tabela = document.createElement('table');
        tabela.className = 'w-full text-[12px]';
        tabela.innerHTML = '<thead><tr class="text-[10px] uppercase tracking-wide text-gray-400">'
            + '<th class="text-left py-1 pr-3">Origem</th><th class="text-left py-1 pr-3">Número</th>'
            + '<th class="text-left py-1 pr-3">Emissão</th><th class="text-right py-1 pr-3">Valor</th>'
            + '<th class="text-right py-1"></th></tr></thead>';
        const tbody = document.createElement('tbody');
        tbody.className = 'divide-y divide-gray-200';
        notas.forEach((n) => {
            const tr = document.createElement('tr');

            const tdOrigem = document.createElement('td');
            tdOrigem.className = 'py-1.5 pr-3';
            const badge = document.createElement('span');
            badge.className = 'inline-block px-1.5 py-0.5 rounded text-[9px] font-bold text-white';
            badge.style.backgroundColor = n.origem === 'XML' ? '#2563eb' : '#0b1f3a';
            badge.textContent = n.origem;
            tdOrigem.appendChild(badge);

            const tdNumero = document.createElement('td');
            tdNumero.className = 'py-1.5 pr-3 text-gray-700';
            tdNumero.textContent = n.numero ?? '—';

            const tdData = document.createElement('td');
            tdData.className = 'py-1.5 pr-3 text-gray-600';
            tdData.textContent = fmtData(n.data_emissao);

            const tdValor = document.createElement('td');
            tdValor.className = 'py-1.5 pr-3 text-right text-gray-900';
            tdValor.textContent = fmtMoeda(n.valor);

            const tdLink = document.createElement('td');
            tdLink.className = 'py-1.5 text-right';
            if (n.chave_acesso) {
                const a = document.createElement('a');
                a.href = '/app/notas?busca=' + encodeURIComponent(n.chave_acesso);
                a.setAttribute('data-link', '');
                a.className = 'text-[11px] text-blue-600 hover:underline';
                a.textContent = 'Abrir';
                tdLink.appendChild(a);
            }

            tr.append(tdOrigem, tdNumero, tdData, tdValor, tdLink);
            tbody.appendChild(tr);
        });
        tabela.appendChild(tbody);
        corpo.appendChild(tabela);
    }

    async function toggle(participanteId, botao) {
        const linha = document.getElementById('drill-' + participanteId);
        if (!linha) return;
        const aberto = !linha.classList.contains('hidden');
        linha.classList.toggle('hidden');
        botao.textContent = aberto ? 'Ver documentos' : 'Ocultar';
        if (aberto || carregados.has(participanteId)) return;

        const corpo = linha.querySelector('[data-drill-corpo]');
        const params = new URLSearchParams(filtros).toString();
        try {
            const r = await fetch(`${base}/fornecedor/${participanteId}/notas${params ? '?' + params : ''}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            if (!r.ok) throw new Error(String(r.status));
            const dados = await r.json();
            render(corpo, dados.notas || []);
            carregados.add(participanteId);
        } catch (e) {
            corpo.textContent = 'Não foi possível carregar os documentos. Tente novamente.';
        }
    }

    return { toggle };
})();
</script>
