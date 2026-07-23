{{-- Consolidado fiscal (C190/D190) agregado por CFOP/CST/alíquota.
     Params opcionais: $tituloConsolidado, $subtituloConsolidado. --}}
@if(!empty($consolidadoFiscal ?? null) && ($consolidadoFiscal['tem_dados'] ?? false))
@php
    $cf = $consolidadoFiscal;
    $tituloConsolidado = $tituloConsolidado ?? 'Consolidado Fiscal do Mês (C190/D190)';
    $subtituloConsolidado = $subtituloConsolidado ?? 'agregado por CFOP · CST · alíquota';
    $grupos = [
        'saida' => ['label' => 'Saídas', 'hex' => '#b45309', 'linhas' => $cf['linhas']->where('tipo_operacao', 'saida'), 'tot' => $cf['saidas']],
        'entrada' => ['label' => 'Entradas', 'hex' => '#047857', 'linhas' => $cf['linhas']->where('tipo_operacao', 'entrada'), 'tot' => $cf['entradas']],
    ];
@endphp
<div class="bg-white rounded border border-gray-300 mt-6" id="consolidado-fiscal-section">
    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">{{ $tituloConsolidado }}</span>
        <span class="text-[10px] text-gray-400">{{ $subtituloConsolidado }}</span>
    </div>

    <div class="px-4 py-4 space-y-6">
        @foreach($grupos as $op => $g)
            @continue($g['linhas']->isEmpty())
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $g['hex'] }}">{{ $g['label'] }}</span>
                    <span class="text-[11px] text-gray-500">{{ number_format($g['tot']['notas'], 0, ',', '.') }} {{ $g['tot']['notas'] === 1 ? 'nota' : 'notas' }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">
                                <th class="px-3 py-2 text-left">CFOP</th>
                                <th class="px-3 py-2 text-left">CST</th>
                                <th class="px-3 py-2 text-right">Alíq.</th>
                                <th class="px-3 py-2 text-right">Notas</th>
                                <th class="px-3 py-2 text-right">Operação</th>
                                <th class="px-3 py-2 text-right">Base ICMS</th>
                                <th class="px-3 py-2 text-right">ICMS</th>
                                <th class="px-3 py-2 text-right">ICMS ST</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($g['linhas'] as $l)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-3 py-2">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold text-white font-mono" style="background-color: #4338ca">{{ $l->cfop ?? '—' }}</span>
                                    </td>
                                    <td class="px-3 py-2 font-mono text-xs text-gray-600">{{ $l->cst_icms ?? '—' }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-xs text-gray-600">{{ $l->aliquota_icms !== null ? number_format((float) $l->aliquota_icms, 2, ',', '.') . '%' : '—' }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-xs text-gray-700">{{ number_format((int) $l->notas, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-xs font-semibold text-gray-900">R$&nbsp;{{ number_format((float) $l->operacao, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-xs text-gray-600">R$&nbsp;{{ number_format((float) $l->bc, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-xs text-gray-700">R$&nbsp;{{ number_format((float) $l->icms, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right font-mono text-xs text-gray-600">R$&nbsp;{{ number_format((float) $l->icms_st, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300 bg-gray-50 font-semibold">
                                <td class="px-3 py-2 text-[11px] text-gray-800" colspan="4">Total {{ mb_strtolower($g['label']) }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs text-gray-900">R$&nbsp;{{ number_format($g['tot']['operacao'], 2, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs text-gray-700">R$&nbsp;{{ number_format($g['tot']['bc'], 2, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs text-gray-900">R$&nbsp;{{ number_format($g['tot']['icms'], 2, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right font-mono text-xs text-gray-700">R$&nbsp;{{ number_format($g['tot']['icms_st'], 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
