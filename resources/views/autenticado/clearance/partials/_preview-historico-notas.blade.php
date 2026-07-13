@php
    $total = (int) ($preview['total'] ?? 0);
    $esperados = (int) ($preview['esperados'] ?? 0);
    $autorizadas = (int) ($preview['autorizadas'] ?? 0);
    $alertas = (int) ($preview['alertas'] ?? 0);
    $indeterminadas = (int) ($preview['indeterminadas'] ?? 0);
    $erros = (int) ($preview['erros'] ?? 0);
    $documentos = $preview['documentos'] ?? collect();
    $veredito = $preview['veredito'] ?? [];
    $kpis = $preview['kpis'] ?? [];
    $totalCriticas = (int) ($veredito['total_criticas'] ?? 0);
    $totalRevisar = (int) ($veredito['total_revisar'] ?? 0);
    $totalConformes = (int) data_get($kpis, 'roi.conformes', 0);
    $formatarDocumento = static function ($documento) {
        $digitos = preg_replace('/\D/', '', (string) $documento);

        return match (strlen($digitos)) {
            14 => preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $digitos),
            11 => preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $digitos),
            default => $documento ?: 'Documento não informado',
        };
    };
    $statusLote = \App\Models\ConsultaLote::normalizeStatus($lote->status);
    $severidadeMeta = [
        'critica' => ['label' => 'Divergências críticas encontradas', 'hex' => '#dc2626'],
        'revisar' => ['label' => 'Revisão necessária', 'hex' => '#b45309'],
        'ruido' => ['label' => 'Dentro da tolerância', 'hex' => '#6b7280'],
        'ok' => ['label' => 'Tudo validado e correto', 'hex' => '#047857'],
    ];
    $vereditoMeta = $severidadeMeta[$veredito['severidade'] ?? 'ok'];
    $previewMeta = $total > 0 ? $vereditoMeta : match (true) {
        $statusLote === 'erro' => ['label' => 'Falha no processamento', 'hex' => '#dc2626'],
        $statusLote === 'processando' => ['label' => 'Consulta em andamento', 'hex' => '#b45309'],
        default => ['label' => 'Sem snapshot associado', 'hex' => '#6b7280'],
    };
@endphp

<div class="rounded border border-gray-200 bg-white overflow-hidden">
    <div class="px-4 py-2 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2" style="background-color: #f9fafb">
        <div>
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Preview do resultado</p>
            <p class="text-xs text-gray-500 mt-0.5">Lote #{{ $lote->id }} · {{ $total }} retorno{{ $total === 1 ? '' : 's' }} para {{ $esperados }} documento{{ $esperados === 1 ? '' : 's' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $previewMeta['hex'] }}">{{ $previewMeta['label'] }}</span>
            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusMeta['hex'] }}">{{ $statusMeta['label'] }}</span>
        </div>
    </div>

    @if($total > 0)
        <div class="px-4 py-3 border-b border-gray-200 border-l-4" style="border-left-color: {{ $vereditoMeta['hex'] }}">
            <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Veredito da validação</p>
                    <p class="text-base font-bold mt-1" style="color: {{ $vereditoMeta['hex'] }}">{{ $vereditoMeta['label'] }}</p>
                    <p class="text-xs text-gray-600 mt-1">{{ $veredito['mensagem'] ?? 'Resultado analisado contra a escrituração disponível.' }}</p>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <span class="px-2 py-1 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $totalCriticas > 0 ? '#dc2626' : '#9ca3af' }}">{{ $totalCriticas }} crítica{{ $totalCriticas === 1 ? '' : 's' }}</span>
                    <span class="px-2 py-1 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $totalRevisar > 0 ? '#b45309' : '#9ca3af' }}">{{ $totalRevisar }} a revisar</span>
                    <span class="px-2 py-1 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $totalConformes > 0 ? '#047857' : '#9ca3af' }}">{{ $totalConformes }} conforme{{ $totalConformes === 1 ? '' : 's' }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200">
        <div class="p-3">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Retornos</p>
            <p class="text-lg font-bold text-gray-900 mt-0.5">{{ $total }} de {{ $esperados }}</p>
            <p class="text-[11px] text-gray-500 mt-0.5">snapshots associados</p>
        </div>
        <div class="p-3">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Autorizadas</p>
            <p class="text-lg font-bold text-gray-900 mt-0.5">{{ $autorizadas }}</p>
            <p class="text-[11px] text-gray-500 mt-0.5">retornos sem bloqueio SEFAZ</p>
        </div>
        <div class="p-3">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Alertas SEFAZ</p>
            <p class="text-lg font-bold mt-0.5" style="color: {{ $alertas > 0 ? '#dc2626' : '#111827' }}">{{ $alertas }}</p>
            <p class="text-[11px] text-gray-500 mt-0.5">canceladas, denegadas ou inutilizadas</p>
        </div>
        <div class="p-3">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">A revisar</p>
            <p class="text-lg font-bold mt-0.5" style="color: {{ ($indeterminadas + $erros) > 0 ? '#b45309' : '#111827' }}">{{ $indeterminadas + $erros }}</p>
            <p class="text-[11px] text-gray-500 mt-0.5">indeterminadas ou com erro</p>
        </div>
    </div>

    @if($total > 0)
        <div class="border-t border-gray-200">
            <div class="px-3 py-2" style="background-color: #f9fafb">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Amostra dos retornos mais recentes</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($documentos as $documento)
                    @php
                        $tipoDocumento = strtoupper((string) ($documento->tipo_documento ?: 'NFE'));
                        $parteDestino = $documento->dest_nome ?: $documento->tomador_nome;
                        $parteDestinoDocumento = $documento->dest_cnpj ?: $documento->tomador_cnpj;
                        $documentoSeveridade = $severidadeMeta[$documento->severidade ?? 'ok'];
                    @endphp
                    <div class="px-3 py-3 space-y-3">
                        <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">{{ $tipoDocumento }}</span>
                                    <span class="text-sm font-semibold text-gray-900">Nº {{ $documento->numero ?: '—' }} / Série {{ $documento->serie ?: '—' }}</span>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $documento->status_hex }}">{{ $documento->status_label }}</span>
                                </div>
                                <p class="text-[10px] font-mono text-gray-400 mt-1 break-all">{{ $documento->chave_acesso }}</p>
                            </div>
                            <div class="lg:text-right whitespace-nowrap">
                                <p class="text-sm font-mono font-semibold text-gray-900">{{ $documento->valor_total_label }}</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Consultada em {{ $documento->momento_consulta }}</p>
                            </div>
                        </div>

                        <div class="rounded border border-gray-200 overflow-hidden">
                            <div class="px-3 py-2 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2" style="background-color: #f9fafb">
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Conferência Declarado × SEFAZ</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Validação dos valores e campos identificadores da nota</p>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $documentoSeveridade['hex'] }}">{{ $documentoSeveridade['label'] }}</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-200">
                                <div class="px-3 py-2.5">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Declarado</p>
                                    <p class="text-sm font-mono font-semibold text-gray-900 mt-1">{{ $documento->declarado_valor_label ?? '—' }}</p>
                                </div>
                                <div class="px-3 py-2.5">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">SEFAZ</p>
                                    <p class="text-sm font-mono font-semibold text-gray-900 mt-1">{{ $documento->valor_total_label ?? '—' }}</p>
                                </div>
                                <div class="px-3 py-2.5">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Diferença</p>
                                    <p class="text-sm font-mono font-semibold mt-1" style="color: {{ $documentoSeveridade['hex'] }}">{{ $documento->delta_valor_label ?? '—' }}</p>
                                    @if(isset($documento->delta_percentual_label))
                                        <p class="text-[10px] text-gray-500 mt-0.5">{{ $documento->delta_percentual_label }}</p>
                                    @endif
                                </div>
                            </div>
                            @if(!empty($documento->motivos))
                                <div class="px-3 py-2.5 border-t border-gray-200">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Leitura do resultado</p>
                                    <ul class="mt-1 space-y-0.5">
                                        @foreach($documento->motivos as $motivo)
                                            <li class="text-[11px] text-gray-600 leading-snug">• {{ $motivo }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if(!empty($documento->conferencias))
                                <div class="px-3 py-2.5 border-t border-gray-200">
                                    @include('autenticado.clearance.partials._conferencias', [
                                        'conferencias' => $documento->conferencias,
                                        'permitirConsultaSintegra' => false,
                                    ])
                                </div>
                            @endif
                        </div>

                        <div class="rounded border border-gray-200 overflow-hidden">
                            <div class="px-3 py-1.5 border-b border-gray-200" style="background-color: #f9fafb">
                                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Partes retornadas pela SEFAZ</p>
                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-200">
                                <div class="px-3 py-2.5">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Emitente</p>
                                    <p class="text-xs font-semibold text-gray-900 mt-1">{{ $documento->emit_nome ?: 'Não informado' }}</p>
                                    <p class="text-[11px] font-mono text-gray-600 mt-0.5">{{ $formatarDocumento($documento->emit_cnpj) }}</p>
                                </div>
                                <div class="px-3 py-2.5">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Destinatário / Tomador</p>
                                    <p class="text-xs font-semibold text-gray-900 mt-1">{{ $parteDestino ?: 'Não informado' }}</p>
                                    <p class="text-[11px] font-mono text-gray-600 mt-0.5">{{ $formatarDocumento($parteDestinoDocumento) }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Perfis vinculados à escrituração</p>
                                @if($documento->origem_declarada)
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: #4b5563">Origem {{ $documento->origem_declarada }}</span>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                @include('autenticado.clearance.partials._perfil-nota-listagem', [
                                    'perfil' => $documento->cliente_perfil,
                                    'papel' => 'Cliente',
                                    'papelDocumento' => 'Empresa da carteira vinculada ao documento',
                                    'nomeFallback' => $documento->cliente_nome_declarado,
                                    'documentoFallback' => $documento->cliente_documento_declarado,
                                ])
                                @include('autenticado.clearance.partials._perfil-nota-listagem', [
                                    'perfil' => $documento->participante_perfil,
                                    'papel' => 'Participante',
                                    'papelDocumento' => 'Contraparte declarada no XML/EFD',
                                    'nomeFallback' => $documento->participante_nome_declarado,
                                    'documentoFallback' => $documento->participante_documento_declarado,
                                ])
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if(($preview['restantes'] ?? 0) > 0)
                <p class="px-3 py-2 border-t border-gray-100 text-[11px] text-gray-500">+ {{ $preview['restantes'] }} retorno{{ $preview['restantes'] === 1 ? '' : 's' }} no resultado completo.</p>
            @endif
        </div>
    @else
        <div class="px-4 py-4 border-t border-gray-200">
            @if($statusLote === 'erro')
                <p class="text-sm font-semibold text-gray-900">A consulta não foi concluída.</p>
                <p class="text-[11px] text-gray-500 mt-1">{{ $lote->error_message ?: 'Nenhum snapshot foi persistido antes da falha.' }}</p>
            @elseif(in_array($statusLote, ['pendente', 'processando'], true))
                <p class="text-sm font-semibold text-gray-900">Aguardando os primeiros retornos.</p>
                <p class="text-[11px] text-gray-500 mt-1">Os detalhes aparecerão aqui conforme os snapshots forem persistidos.</p>
            @else
                <p class="text-sm font-semibold text-gray-900">Nenhum snapshot permanece associado a este lote.</p>
                <p class="text-[11px] text-gray-500 mt-1">Abra o resultado para consultar o estado completo disponível para a verificação.</p>
            @endif
        </div>
    @endif

    <div class="px-3 py-2.5 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2" style="background-color: #f9fafb">
        <p class="text-[10px] text-gray-500">Este preview mostra situação e amostra dos retornos. Divergências Declarado × SEFAZ ficam no resultado completo.</p>
        <a href="{{ $resultadoUrl }}" data-link class="text-xs font-semibold text-gray-700 hover:text-gray-900 hover:underline whitespace-nowrap">Abrir resultado completo →</a>
    </div>
</div>
