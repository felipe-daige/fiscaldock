@php
    $resultado = $nota->clearance_resultado ?? null;
    $clientePerfil = $nota->cliente_perfil ?? null;
    $participantePerfil = $nota->participante_perfil ?? null;
    $clientePapel = $nota->tipo_nota === 'entrada' ? 'Destinatário da entrada' : 'Emitente da saída';
    $participantePapel = $nota->tipo_nota === 'entrada' ? 'Emitente / fornecedor' : 'Destinatário / cliente';
    $severidadeMeta = [
        'critica' => ['label' => 'Crítica', 'hex' => '#dc2626'],
        'revisar' => ['label' => 'Revisar', 'hex' => '#b45309'],
        'ruido' => ['label' => 'Dentro da tolerância', 'hex' => '#6b7280'],
        'ok' => ['label' => 'Sem divergência', 'hex' => '#047857'],
    ];
    $severidade = $severidadeMeta[$resultado->severidade ?? ''] ?? null;
    $itensNota = $nota->itens_nota ?? collect();
    $fmtDecimal = function ($valor, int $casas = 4): string {
        $texto = number_format((float) $valor, $casas, ',', '.');

        return str_contains($texto, ',') ? (rtrim(rtrim($texto, '0'), ',') ?: '0') : $texto;
    };
    // Moeda com no mínimo 2 casas; abre pra 4 só quando o unitário tem precisão real
    $fmtMoedaUnit = fn ($valor): string => number_format(
        (float) $valor,
        fmod(round((float) $valor * 10000), 100) == 0 ? 2 : 4,
        ',',
        '.'
    );
@endphp

<div class="space-y-3">
    <div class="rounded border border-gray-200 bg-white overflow-hidden">
        <div class="bg-gray-50 px-3 py-2 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resultado do clearance</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Conferência do documento declarado com o snapshot oficial</p>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                @if($severidade)
                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $severidade['hex'] }}">{{ $severidade['label'] }}</span>
                @endif
                <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $resultado->status_hex ?? $status['hex'] }}">{{ $resultado->status_label ?? $status['label'] }}</span>
            </div>
        </div>

        @if($resultado)
            <div class="p-3 space-y-3">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                    <div class="rounded border border-gray-200 px-2.5 py-2">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Declarado</p>
                        <p class="text-sm font-mono font-semibold text-gray-900 mt-0.5">{{ $resultado->declarado_valor_label ?? 'R$ '.number_format((float) $nota->valor_total, 2, ',', '.') }}</p>
                    </div>
                    <div class="rounded border border-gray-200 px-2.5 py-2">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">SEFAZ</p>
                        <p class="text-sm font-mono font-semibold text-gray-900 mt-0.5">{{ $resultado->valor_total_label ?? '—' }}</p>
                    </div>
                    <div class="rounded border border-gray-200 px-2.5 py-2">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Diferença</p>
                        <p class="text-sm font-mono font-semibold mt-0.5" style="color: {{ $severidade['hex'] ?? '#374151' }}">{{ $resultado->delta_valor_label ?? '—' }}</p>
                        @if(isset($resultado->delta_percentual_label))
                            <p class="text-[10px] text-gray-500">{{ $resultado->delta_percentual_label }}</p>
                        @endif
                    </div>
                    <div class="rounded border border-gray-200 px-2.5 py-2">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consulta</p>
                        <p class="text-[11px] text-gray-900 mt-0.5">{{ $resultado->consultado_em_label ?? '—' }}</p>
                        @if($resultado->natureza_operacao ?? null)
                            <p class="text-[10px] text-gray-500 mt-0.5">{{ $resultado->natureza_operacao }}</p>
                        @endif
                    </div>
                </div>

                @if(!empty($resultado->motivos) || !empty($resultado->eventos_chips) || ($resultado->situacao_ambiente ?? null) || ($resultado->comprovante_url ?? null))
                    <div class="rounded border border-gray-200 px-3 py-2.5">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Leitura operacional</p>
                                @if(!empty($resultado->motivos))
                                    <ul class="mt-1 space-y-0.5">
                                        @foreach($resultado->motivos as $motivoResultado)
                                            <li class="text-[11px] text-gray-600 leading-snug">• {{ $motivoResultado }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-[11px] text-gray-600 mt-1">Snapshot oficial localizado para este documento.</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-1.5">
                                @if($resultado->situacao_ambiente ?? null)
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ str_contains(mb_strtoupper($resultado->situacao_ambiente), 'HOMOLOGA') ? '#dc2626' : '#374151' }}">{{ $resultado->situacao_ambiente }}</span>
                                @endif
                                @foreach($resultado->eventos_chips ?? [] as $evento)
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $evento['hex'] }}" title="{{ $evento['protocolo'] ? 'Protocolo '.$evento['protocolo'] : '' }}{{ $evento['data'] ? ' · '.$evento['data'] : '' }}">{{ $evento['label'] }}</span>
                                @endforeach
                                @if($resultado->comprovante_url ?? null)
                                    <a href="{{ $resultado->comprovante_url }}" target="_blank" rel="noopener" class="text-[11px] text-blue-700 hover:underline">Ver na Receita ↗</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($resultado->conferencias))
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mb-1.5">Conferência Declarado × SEFAZ</p>
                        @include('autenticado.clearance.partials._conferencias', [
                            'conferencias' => $resultado->conferencias,
                        ])
                    </div>
                @endif
            </div>
        @else
            <div class="p-3">
                <p class="text-sm font-semibold text-gray-700">Documento ainda não consultado na SEFAZ</p>
                <p class="text-[11px] text-gray-500 mt-1">Selecione a nota e execute o clearance para ver situação oficial, diferenças, eventos e conferências.</p>
                @if($motivo)
                    <p class="text-[11px] text-gray-600 mt-2">Alerta contábil local: {{ $motivo }}</p>
                @endif
            </div>
        @endif
    </div>

    <div>
        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-1.5">Partes envolvidas na nota</p>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
            @include('autenticado.clearance.partials._perfil-nota-listagem', [
                'perfil' => $clientePerfil,
                'papel' => 'Cliente',
                'papelDocumento' => $clientePapel,
                'nomeFallback' => $nota->cliente_nome,
                'documentoFallback' => $nota->cliente_documento,
            ])
            @include('autenticado.clearance.partials._perfil-nota-listagem', [
                'perfil' => $participantePerfil,
                'papel' => 'Participante',
                'papelDocumento' => $participantePapel,
                'nomeFallback' => $nota->participante_nome,
                'documentoFallback' => $nota->participante_cnpj,
            ])
        </div>
    </div>

    @if($itensNota->isNotEmpty())
        <div class="rounded border border-gray-200 bg-white overflow-hidden">
            <div class="bg-gray-50 px-3 py-2 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Itens da nota</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">Produtos e serviços escriturados no documento</p>
                </div>
                <div class="flex items-center gap-1.5">
                    @if($nota->itens_via_gemea ?? false)
                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: #6b7280" title="A escrituração fiscal desta nota só traz o consolidado C190; os itens vêm da mesma nota na EFD Contribuições.">Itens via EFD Contribuições</span>
                    @endif
                    <span class="text-[10px] font-semibold text-gray-500 bg-gray-200 px-2 py-0.5 rounded">{{ $nota->itens_total }} item(ns)</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[11px]">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-1.5 text-left text-[9px] font-semibold text-gray-400 uppercase tracking-wide">#</th>
                            <th class="px-3 py-1.5 text-left text-[9px] font-semibold text-gray-400 uppercase tracking-wide">Descrição</th>
                            <th class="px-3 py-1.5 text-left text-[9px] font-semibold text-gray-400 uppercase tracking-wide">NCM</th>
                            <th class="px-3 py-1.5 text-left text-[9px] font-semibold text-gray-400 uppercase tracking-wide">CFOP</th>
                            <th class="px-3 py-1.5 text-right text-[9px] font-semibold text-gray-400 uppercase tracking-wide">Qtd</th>
                            <th class="px-3 py-1.5 text-left text-[9px] font-semibold text-gray-400 uppercase tracking-wide">Un</th>
                            <th class="px-3 py-1.5 text-right text-[9px] font-semibold text-gray-400 uppercase tracking-wide">Vlr unit.</th>
                            <th class="px-3 py-1.5 text-right text-[9px] font-semibold text-gray-400 uppercase tracking-wide">Vlr total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($itensNota as $item)
                            <tr>
                                <td class="px-3 py-1.5 font-mono text-gray-400">{{ $item->numero_item }}</td>
                                <td class="px-3 py-1.5 text-gray-900">{{ $item->descricao ?: ($item->codigo_item ? 'Item '.$item->codigo_item : '—') }}</td>
                                <td class="px-3 py-1.5 font-mono text-gray-600">{{ $item->ncm ?: '—' }}</td>
                                <td class="px-3 py-1.5 font-mono text-gray-600">{{ $item->cfop ?: '—' }}</td>
                                <td class="px-3 py-1.5 text-right font-mono text-gray-900">{{ $fmtDecimal($item->quantidade) }}</td>
                                <td class="px-3 py-1.5 text-gray-600">{{ $item->unidade_medida ?: '—' }}</td>
                                <td class="px-3 py-1.5 text-right font-mono text-gray-900">R$&nbsp;{{ $fmtMoedaUnit($item->valor_unitario) }}</td>
                                <td class="px-3 py-1.5 text-right font-mono font-semibold text-gray-900">R$&nbsp;{{ number_format((float) $item->valor_total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(($nota->itens_total ?? 0) > $itensNota->count())
                <div class="px-3 py-2 border-t border-gray-200 bg-gray-50">
                    <a href="{{ $detalheUrl }}" data-link class="text-[11px] text-gray-600 hover:text-gray-900 hover:underline">+ {{ $nota->itens_total - $itensNota->count() }} item(ns) — ver nota completa →</a>
                </div>
            @endif
        </div>
    @endif

    <div class="rounded border border-gray-200 bg-white px-3 py-2.5">
        <div class="grid grid-cols-1 md:grid-cols-[1fr,auto] gap-3 items-end">
            <div>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Documento fiscal</p>
                <p class="text-[11px] font-mono text-gray-900 mt-0.5 break-all">{{ $nota->chave ?? 'Chave não informada' }}</p>
                <p class="text-[11px] text-gray-500 mt-1">
                    Emissão: {{ $dataEmissao?->format('d/m/Y') ?? '—' }}
                    · Total: R$ {{ number_format((float) $nota->valor_total, 2, ',', '.') }}
                    @if($nota->tributos_total !== null)
                        · Tributos: R$ {{ number_format((float) $nota->tributos_total, 2, ',', '.') }}
                    @endif
                </p>
            </div>
            <a href="{{ $detalheUrl }}" data-link class="text-xs font-medium text-gray-700 hover:text-gray-900 hover:underline whitespace-nowrap">Ver nota completa →</a>
        </div>
    </div>
</div>
