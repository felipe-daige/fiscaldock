{{-- Principais produtos + CFOPs (Top N) do acervo EFD. Espera $top_produtos, $top_cfops. Web, mobile-safe. --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
    {{-- Principais produtos --}}
    <div class="rounded-lg border border-slate-200 bg-white p-3">
        <p class="text-[10px] text-slate-400 uppercase tracking-wide mb-2">Principais produtos</p>
        @if(empty($top_produtos))
            <p class="text-[11px] text-slate-400">Sem produtos no acervo.</p>
        @else
            <table class="w-full text-[11px] border-collapse">
                <thead>
                    <tr class="text-slate-400 uppercase tracking-wide border-b border-slate-200">
                        <th class="text-left font-medium py-0.5 pr-2">Descrição</th>
                        <th class="text-right font-medium py-0.5 whitespace-nowrap">Valor</th>
                        <th class="text-right font-medium py-0.5 pl-2 whitespace-nowrap">Qtd</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($top_produtos as $p)
                        <tr class="odd:bg-slate-50/60">
                            <td class="py-0.5 pr-2 text-slate-700"><div class="max-w-[180px] truncate" title="{{ $p['descricao'] ?? $p['cod_item'] ?? '—' }}">{{ $p['descricao'] ?? $p['cod_item'] ?? '—' }}</div></td>
                            <td class="py-0.5 text-right font-mono text-slate-900 whitespace-nowrap">R$&nbsp;{{ number_format((float) ($p['valor'] ?? 0), 2, ',', '.') }}</td>
                            <td class="py-0.5 pl-2 text-right text-slate-500 whitespace-nowrap">{{ (int) ($p['qtd'] ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- CFOPs --}}
    <div class="rounded-lg border border-slate-200 bg-white p-3">
        <p class="text-[10px] text-slate-400 uppercase tracking-wide mb-2">Principais CFOPs</p>
        @if(empty($top_cfops))
            <p class="text-[11px] text-slate-400">Sem CFOPs no acervo.</p>
        @else
            <table class="w-full text-[11px] border-collapse">
                <thead>
                    <tr class="text-slate-400 uppercase tracking-wide border-b border-slate-200">
                        <th class="text-left font-medium py-0.5 pr-2 whitespace-nowrap">CFOP</th>
                        <th class="text-left font-medium py-0.5 pr-2">Descrição</th>
                        <th class="text-right font-medium py-0.5 whitespace-nowrap">Valor</th>
                        <th class="text-right font-medium py-0.5 pl-2 whitespace-nowrap">Qtd</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($top_cfops as $c)
                        <tr class="odd:bg-slate-50/60">
                            <td class="py-0.5 pr-2 font-mono text-slate-700 whitespace-nowrap">{{ $c['cfop'] ?? '—' }}</td>
                            <td class="py-0.5 pr-2 text-slate-600"><div class="max-w-[160px] truncate" title="{{ $c['descricao'] ?? '' }}">{{ $c['descricao'] ?? '' }}</div></td>
                            <td class="py-0.5 text-right font-mono text-slate-900 whitespace-nowrap">R$&nbsp;{{ number_format((float) ($c['valor'] ?? 0), 2, ',', '.') }}</td>
                            <td class="py-0.5 pl-2 text-right text-slate-500 whitespace-nowrap">{{ (int) ($c['qtd'] ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
