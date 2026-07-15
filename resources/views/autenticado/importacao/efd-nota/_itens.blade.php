<section class="overflow-hidden rounded border border-gray-300 bg-white" data-itens-nota>
    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
        <div>
            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Itens da nota</span>
            <p class="text-[11px] text-gray-500 mt-1">Produtos e serviços escriturados, com o cadastro 0200 lado a lado.</p>
        </div>
        <span class="shrink-0 rounded px-2 py-0.5 text-[10px] font-semibold text-gray-600" style="background-color: #e5e7eb">
            {{ $itensExibir->count() }} {{ $itensExibir->count() === 1 ? 'item' : 'itens' }}
        </span>
    </div>

    @if($itensExibir->isNotEmpty())
        @if($viaTwin)
            <div class="px-4 py-2 border-b text-[11px]" style="background-color: #fffbeb; border-color: #fde68a; color: #b45309">
                Itens detalhados via EFD PIS/COFINS; a saída fiscal foi escriturada por C190.
            </div>
        @endif
        <div class="divide-y divide-gray-200" data-itens-lista>
            @foreach($itensExibir as $item)
                @php
                    $cat = $catalogoMap[$item->codigo_item] ?? null;
                    $div = $aliqDiv($cat, $item);
                @endphp
                <article class="px-4 py-4 transition-colors hover:bg-gray-50">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">Item {{ $item->numero_item ?? '—' }}</p>
                            <p class="mt-1 text-sm font-semibold leading-snug text-gray-900">{{ $item->descricao ?: ($cat?->descr_item ?? '—') }}</p>
                            <p class="mt-1 text-[10px] font-mono text-gray-500">Código {{ $item->codigo_item ?? '—' }}</p>
                        </div>
                        <div class="flex shrink-0 items-center justify-between gap-3 sm:flex-col sm:items-end sm:justify-start">
                            <span class="font-mono text-sm font-bold text-gray-900">R$&nbsp;{{ $item->valor_total !== null ? number_format($item->valor_total, 2, ',', '.') : '—' }}</span>
                            <div class="flex flex-wrap justify-end gap-1">
                                @if($cat && $cat->cod_ncm)
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold text-white" style="background-color: #4338ca" title="NCM do catálogo">{{ $cat->cod_ncm }}</span>
                                @elseif($cat && $cat->exigeNcm())
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold text-white" style="background-color: #b45309" title="Tipo {{ $cat->tipo_item }} ({{ $cat->tipo_label }}) é mercadoria/produto — NCM deveria estar preenchido no 0200">NCM faltando</span>
                                @elseif($cat)
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-medium text-gray-600" style="background-color: #f3f4f6" title="Tipo {{ $cat->tipo_item }} ({{ $cat->tipo_label }}) — NCM não é exigido p/ este tipo de item">não exige NCM</span>
                                @else
                                    <span class="text-[10px] font-semibold text-amber-600" title="Sem catálogo 0200">sem cat.</span>
                                @endif
                                @if($div)
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold text-white" style="background-color: #b45309" title="Alíquota do item difere do catálogo">alíq ≠</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <dl class="mt-4 flex flex-wrap gap-x-8 gap-y-3 border-t border-gray-200 pt-3 text-[11px]">
                        <div class="min-w-24">
                            <dt class="text-[9px] font-semibold uppercase tracking-wide text-gray-400">Quantidade</dt>
                            <dd class="mt-0.5 text-gray-700">{{ $item->quantidade !== null ? number_format($item->quantidade, 2, ',', '.') : '—' }} {{ $item->unidade_medida ?? '' }}</dd>
                        </div>
                        <div class="min-w-16">
                            <dt class="text-[9px] font-semibold uppercase tracking-wide text-gray-400">CFOP</dt>
                            <dd class="mt-0.5 font-mono text-gray-700">{{ $item->cfop ?? '—' }}</dd>
                        </div>
                        <div class="min-w-24">
                            <dt class="text-[9px] font-semibold uppercase tracking-wide text-gray-400">Valor unitário</dt>
                            <dd class="mt-0.5 font-mono text-gray-700">{{ $item->valor_unitario !== null ? number_format($item->valor_unitario, 2, ',', '.') : '—' }}</dd>
                        </div>
                        <div class="min-w-36 flex-1">
                            <dt class="text-[9px] font-semibold uppercase tracking-wide text-gray-400">Alíquota item / catálogo</dt>
                            <dd class="mt-0.5 font-mono {{ $div ? 'text-amber-700 font-semibold' : 'text-gray-700' }}">
                                {{ $item->aliquota_icms !== null ? number_format((float) $item->aliquota_icms, 1, ',', '.') . '%' : '—' }}
                                @if($cat && $cat->aliq_icms !== null)
                                    <span class="text-gray-400">/ {{ number_format((float) $cat->aliq_icms, 1, ',', '.') }}%</span>
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <dl class="mt-3 flex flex-wrap gap-x-8 gap-y-2 border-t border-dashed border-gray-200 pt-3">
                        @foreach([
                            ['label' => 'ICMS', 'valor' => $item->valor_icms],
                            ['label' => 'PIS', 'valor' => $item->valor_pis],
                            ['label' => 'COFINS', 'valor' => $item->valor_cofins],
                        ] as $tributoItem)
                            <div class="min-w-20">
                                <dt class="text-[9px] uppercase tracking-wide text-gray-400">{{ $tributoItem['label'] }}</dt>
                                <dd class="mt-0.5 font-mono text-[11px] font-semibold text-gray-700">R$&nbsp;{{ $tributoItem['valor'] !== null ? number_format($tributoItem['valor'], 2, ',', '.') : '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    @if($cat)
                        <div class="pt-3">
                            <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="text-[11px] font-medium text-indigo-600 hover:underline">Ver catálogo ▾</button>
                            <div class="hidden mt-2 pt-2 border-t border-gray-200 text-[11px] space-y-2">
                                <p class="text-gray-700">{{ $cat->descr_item }}</p>
                                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-gray-500">
                                    <span>NCM <span class="font-mono text-gray-700">{{ $cat->cod_ncm ?: '—' }}</span></span>
                                    <span>Tipo <span class="text-gray-700">{{ $cat->tipo_item }} · {{ $cat->tipo_label }}</span></span>
                                    <span>Unidade <span class="text-gray-700">{{ $cat->unid_inv ?: '—' }}</span></span>
                                    <span>Alíq. cat. <span class="text-gray-700">{{ $cat->aliq_icms !== null ? number_format((float) $cat->aliq_icms, 2, ',', '.') . '%' : '—' }}</span></span>
                                    <span>Cód. barras <span class="font-mono text-gray-700">{{ $cat->cod_barra ?: '—' }}</span></span>
                                    <span>Cód. genérico <span class="font-mono text-gray-700">{{ $cat->cod_gen ?: '—' }}</span></span>
                                </div>
                                <button type="button" data-cat-hist="{{ $cat->cod_item }}" data-cat-cliente="{{ $cat->cliente_id }}" class="font-medium text-indigo-600 hover:underline">Histórico, movimentação e drift ▾</button>
                                <div class="cat-hist-panel hidden border border-gray-200 rounded bg-gray-50/50"></div>
                            </div>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex flex-wrap items-center justify-end gap-x-4 gap-y-1 text-[11px]">
            <span class="text-gray-500">Total itens <span class="font-mono font-semibold text-gray-900">R$&nbsp;{{ number_format($tabValor, 2, ',', '.') }}</span></span>
            <span class="text-gray-500">ICMS <span class="font-mono text-gray-700">{{ number_format($tabIcms, 2, ',', '.') }}</span></span>
            <span class="text-gray-500">PIS <span class="font-mono text-gray-700">{{ number_format($tabPis, 2, ',', '.') }}</span></span>
            <span class="text-gray-500">COFINS <span class="font-mono text-gray-700">{{ number_format($tabCofins, 2, ',', '.') }}</span></span>
        </div>
    @elseif($nota->consolidados->isNotEmpty())
        <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 text-[11px] text-gray-500">Escriturada por C190 (consolidado) — sem detalhe por item / catálogo.</div>
        <div class="divide-y divide-gray-200" data-itens-lista>
            @foreach($nota->consolidados as $consolidado)
                <article class="px-4 py-4 transition-colors hover:bg-gray-50">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold text-white" style="background-color: #4338ca">CFOP {{ $consolidado->cfop ?? '—' }}</span>
                        <span class="text-[11px] text-gray-500">CST {{ $consolidado->cst_icms ?? '—' }} · {{ $consolidado->aliquota_icms !== null ? number_format((float) $consolidado->aliquota_icms, 2, ',', '.') . '%' : '—' }}</span>
                    </div>
                    <dl class="flex flex-wrap gap-x-10 gap-y-3">
                        <div class="min-w-28"><dt class="text-[9px] uppercase text-gray-400">Operação</dt><dd class="font-mono text-xs font-semibold text-gray-900">R$&nbsp;{{ number_format((float) ($consolidado->valor_operacao ?? 0), 2, ',', '.') }}</dd></div>
                        <div class="min-w-24"><dt class="text-[9px] uppercase text-gray-400">ICMS</dt><dd class="font-mono text-xs text-gray-700">R$&nbsp;{{ number_format((float) ($consolidado->valor_icms ?? 0), 2, ',', '.') }}</dd></div>
                        <div class="min-w-24"><dt class="text-[9px] uppercase text-gray-400">ICMS ST</dt><dd class="font-mono text-xs text-gray-700">R$&nbsp;{{ number_format((float) ($consolidado->valor_icms_st ?? 0), 2, ',', '.') }}</dd></div>
                    </dl>
                </article>
            @endforeach
        </div>
    @else
        <div class="px-4 py-8 text-center">
            <p class="text-sm font-medium text-gray-600">Nenhum item registrado para esta nota.</p>
            <p class="mt-1 text-xs text-gray-400">O arquivo não trouxe detalhamento por item nem consolidação C190.</p>
        </div>
    @endif
</section>
