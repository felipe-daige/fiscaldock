@php
    $notaResultado = $notaResultado ?? null;
    $statusMeta = $statusMeta ?? ['label' => 'Pendente', 'hex' => '#9ca3af'];
    $tipoDocumento = strtoupper((string) ($tipoDocumento ?? 'NFE'));
    $chaveConsultada = $chaveConsultada ?? null;
    $aguardaPersistencia = (bool) ($aguardaPersistencia ?? false);
    $statusLote = \App\Models\ConsultaLote::normalizeStatus($lote->status ?? 'pendente');
    $erroCriticoLote = $lote->publicErrorUi([
        'context' => 'clearance-busca-avulsa',
        'url' => request()->getPathInfo(),
    ]);
@endphp

<div
    class="min-h-screen bg-gray-100"
    id="clearance-resultado-root"
    data-status="{{ $statusLote }}"
    data-tab-id="{{ $lote->tab_id ?? '' }}"
    data-stream-url="{{ url('/app/consulta/progresso/stream') }}"
    data-json-url="{{ route('app.clearance.buscar.resultado', ['consultaLoteId' => $lote->id, 'tipo_documento' => strtolower($tipoDocumento), 'chave_acesso' => $chaveConsultada]) }}"
    data-await-result="{{ $aguardaPersistencia ? '1' : '0' }}"
    data-poll-result="1"
    data-progress-snapshot='@json($progressSnapshot ?? null)'
    data-iniciado-em="{{ optional($lote->created_at)->timestamp }}"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="/app/clearance/buscar" data-link class="inline-flex items-center gap-2 text-xs text-gray-600 hover:text-gray-900 hover:underline mb-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar para busca avulsa
                </a>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Resultado da Busca de Nota</h1>
                <p class="text-xs text-gray-500 mt-1">A consulta abre nesta página e acompanha o processamento até a finalização do DF-e.</p>
            </div>
            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white self-start" style="background-color: {{ $statusMeta['hex'] }}">
                {{ $statusMeta['label'] }}
            </span>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo Operacional</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200">
                <div class="px-4 py-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consulta</p>
                    <p class="text-lg font-bold text-gray-900">#{{ $lote->id }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">busca avulsa</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Documento</p>
                    <p class="text-lg font-bold text-gray-900">{{ $tipoDocumento === 'CTE' ? 'CT-e' : 'NF-e' }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">consulta unitária</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Cliente</p>
                    <p class="text-sm font-bold text-gray-900">{{ $lote->cliente?->razao_social ?? 'Não informado' }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">vínculo obrigatório</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Custo</p>
                    <p class="text-lg font-bold text-gray-900">@brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) ($lote->creditos_cobrados ?? 0)))</p>
                    <p class="text-[11px] text-gray-500 mt-1">debitados nesta consulta</p>
                </div>
            </div>
        </div>

        @if(in_array($statusLote, ['pendente', 'processando'], true))
            <div id="clearance-resultado-progresso" class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Andamento</span>
                    <span id="clearance-resultado-percent" class="text-[10px] text-gray-500 font-mono">0%</span>
                </div>
                <div class="p-4">
                    <p id="clearance-resultado-mensagem" class="text-sm text-gray-600 mb-1">Iniciando consulta...</p>
                    <div class="w-full h-1.5 rounded-full overflow-hidden" style="background-color: #e5e7eb">
                        <div id="clearance-resultado-bar" class="h-full" style="background-color: #1f2937; width: 8%; transition: width 350ms ease-out"></div>
                    </div>
                    @include('autenticado.partials.progresso-tempo', [
                        'prefixo' => 'clearance-resultado',
                        'dica' => 'consultamos a SEFAZ em tempo real — pode levar alguns segundos.',
                    ])
                    <p id="clearance-resultado-etapa-label" class="text-[11px] text-gray-500 mt-2 hidden"></p>
                    <div id="clearance-resultado-steps" class="hidden mt-3 flex flex-wrap gap-2"></div>
                </div>
            </div>
        @endif

        @if($statusLote === 'erro')
            @include('autenticado.partials.system-critical-error', ['errorUi' => $erroCriticoLote])
        @elseif($notaResultado)
            <div class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resultado Final</span>
                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white self-start sm:self-auto" style="background-color: {{ $notaResultado['situacao_hex'] ?? '#374151' }}">
                        {{ $notaResultado['situacao'] ?? 'INDETERMINADO' }}
                    </span>
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                        <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Tipo</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ $notaResultado['tipo_documento'] ?? $tipoDocumento }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Situação</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ $notaResultado['situacao'] ?? '—' }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Valor total</p>
                            <p class="text-sm font-bold text-gray-900 font-mono mt-1 whitespace-nowrap">{{ $notaResultado['valor_total_label'] ?? '—' }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Emissão</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ $notaResultado['data_emissao'] ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Emitente</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $notaResultado['emit'] ?? '—' }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Destinatário / Tomador</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $notaResultado['dest'] ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-[1fr,240px] gap-2">
                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Chave consultada</p>
                            <p class="text-xs text-gray-900 font-mono break-all mt-1">{{ $notaResultado['nfe_id'] ?? $chaveConsultada ?? '—' }}</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Cliente associado</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $notaResultado['cliente_nome'] ?? ($lote->cliente?->razao_social ?? '—') }}</p>
                        </div>
                    </div>

                    @php $detalhes = $notaResultado['detalhes'] ?? null; @endphp
                    @if($detalhes)
                        @php $isCteResultado = ($notaResultado['tipo_documento'] ?? '') === 'CTE'; @endphp

                        {{-- Dados da operação --}}
                        <div class="border border-gray-200 rounded overflow-hidden">
                            <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Dados da operação</span>
                            </div>
                            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200">
                                <div class="px-3 py-2.5 col-span-2 lg:col-span-1">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Natureza da operação</p>
                                    <p class="text-xs text-gray-900 mt-1">{{ $detalhes['natureza_operacao'] ?? '—' }}</p>
                                </div>
                                @if($isCteResultado)
                                    <div class="px-3 py-2.5">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Tipo de serviço / CFOP</p>
                                        <p class="text-xs text-gray-900 mt-1">{{ $detalhes['tipo_servico'] ?? '—' }}{{ !empty($detalhes['cfop']) ? ' · CFOP '.$detalhes['cfop'] : '' }}</p>
                                    </div>
                                    <div class="px-3 py-2.5">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Modal / Trajeto</p>
                                        <p class="text-xs text-gray-900 mt-1">{{ $detalhes['modal'] ?? '—' }}{{ !empty($detalhes['trajeto']) ? ' · '.$detalhes['trajeto'] : '' }}</p>
                                    </div>
                                    <div class="px-3 py-2.5">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Valor da carga</p>
                                        <p class="text-xs text-gray-900 font-mono mt-1">{{ $detalhes['valor_carga_label'] ?? '—' }}</p>
                                    </div>
                                @else
                                    <div class="px-3 py-2.5">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Tipo de operação</p>
                                        <p class="text-xs text-gray-900 mt-1">{{ $detalhes['tipo_operacao'] ?? '—' }}</p>
                                    </div>
                                    <div class="px-3 py-2.5">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Versão XML</p>
                                        <p class="text-xs text-gray-900 mt-1">{{ $detalhes['versao_xml'] ?? '—' }}</p>
                                    </div>
                                    <div class="px-3 py-2.5">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Abrangência</p>
                                        <p class="text-xs text-gray-900 mt-1">{{ ($detalhes['consulta_sem_certificado'] ?? false) ? 'Consulta pública (sem certificado)' : 'Consulta completa' }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Partes --}}
                        <div class="border border-gray-200 rounded overflow-hidden">
                            <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Partes do documento</span>
                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-200">
                                <div class="px-3 py-2.5">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Emitente</p>
                                    <p class="text-sm text-gray-900 mt-1">{{ $detalhes['emit']['nome'] ?? '—' }}</p>
                                    <p class="text-[11px] text-gray-500 font-mono">{{ $detalhes['emit']['documento'] ?? '' }}</p>
                                    <p class="text-[11px] text-gray-500">
                                        {{ !empty($detalhes['emit']['ie']) ? 'IE '.$detalhes['emit']['ie'] : '' }}
                                        {{ !empty($detalhes['emit']['local']) ? ' · '.$detalhes['emit']['local'] : '' }}
                                    </p>
                                </div>
                                @if($isCteResultado)
                                    <div class="px-3 py-2.5 space-y-2">
                                        @forelse($detalhes['partes'] ?? [] as $parte)
                                            <div>
                                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $parte['papel'] }}</p>
                                                <p class="text-sm text-gray-900">{{ $parte['nome'] ?? '—' }}</p>
                                                <p class="text-[11px] text-gray-500 font-mono">{{ $parte['documento'] ?? '' }}{{ !empty($parte['local']) ? ' · '.$parte['local'] : '' }}</p>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-500">Sem partes adicionais informadas.</p>
                                        @endforelse
                                    </div>
                                @else
                                    <div class="px-3 py-2.5">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Destinatário</p>
                                        <p class="text-sm text-gray-900 mt-1">{{ $detalhes['dest']['nome'] ?? '—' }}</p>
                                        <p class="text-[11px] text-gray-500 font-mono">{{ $detalhes['dest']['documento'] ?? '' }}</p>
                                        <p class="text-[11px] text-gray-500">{{ $detalhes['dest']['local'] ?? '' }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Eventos SEFAZ --}}
                        @if(!empty($detalhes['eventos_chips']))
                            <div class="border border-gray-200 rounded overflow-hidden">
                                <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Eventos na SEFAZ</span>
                                </div>
                                <div class="px-3 py-2.5 flex flex-wrap gap-2">
                                    @foreach($detalhes['eventos_chips'] as $chip)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-[10px] font-bold text-white" style="background-color: {{ $chip['hex'] ?? '#374151' }}">
                                            {{ $chip['label'] }}
                                            @if(!empty($chip['protocolo']))
                                                <span class="font-mono font-normal opacity-80">prot. {{ $chip['protocolo'] }}</span>
                                            @endif
                                            @if(!empty($chip['data']))
                                                <span class="font-normal opacity-80">{{ $chip['data'] }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Totais (NF-e) / Componentes da prestação (CT-e) --}}
                        @if($isCteResultado && !empty($detalhes['componentes']))
                            <div class="border border-gray-200 rounded overflow-hidden">
                                <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Componentes da prestação</span>
                                </div>
                                <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200">
                                    @foreach($detalhes['componentes'] as $componente)
                                        <div class="px-3 py-2.5">
                                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $componente['nome'] }}</p>
                                            <p class="text-xs text-gray-900 font-mono mt-1">{{ $componente['valor'] !== null ? 'R$ '.$componente['valor'] : '—' }}</p>
                                        </div>
                                    @endforeach
                                    @if(($detalhes['nfes_referenciadas_count'] ?? 0) > 0)
                                        <div class="px-3 py-2.5">
                                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">NF-e referenciadas</p>
                                            <p class="text-xs text-gray-900 mt-1">{{ $detalhes['nfes_referenciadas_count'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif(!$isCteResultado && !empty($detalhes['totais']))
                            <div class="border border-gray-200 rounded overflow-hidden">
                                <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Totais informados pela SEFAZ</span>
                                </div>
                                <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200">
                                    @foreach($detalhes['totais'] as $total)
                                        <div class="px-3 py-2.5">
                                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $total['label'] }}</p>
                                            <p class="text-xs text-gray-900 font-mono mt-1">{{ $total['valor'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Produtos (NF-e, quando a consulta traz itens) --}}
                        @if(!$isCteResultado && !empty($detalhes['produtos']))
                            <div class="border border-gray-200 rounded overflow-hidden">
                                <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Produtos ({{ count($detalhes['produtos']) }})</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr class="bg-gray-50 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">
                                                <th class="px-3 py-2">Descrição</th>
                                                <th class="px-3 py-2">NCM</th>
                                                <th class="px-3 py-2">CFOP</th>
                                                <th class="px-3 py-2 text-right">Qtd</th>
                                                <th class="px-3 py-2 text-right">Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($detalhes['produtos'] as $produto)
                                                <tr>
                                                    <td class="px-3 py-2 text-gray-900">{{ $produto['descricao'] }}</td>
                                                    <td class="px-3 py-2 text-gray-600 font-mono">{{ $produto['ncm'] ?? '—' }}</td>
                                                    <td class="px-3 py-2 text-gray-600 font-mono">{{ $produto['cfop'] ?? '—' }}</td>
                                                    <td class="px-3 py-2 text-gray-600 text-right">{{ $produto['quantidade'] ?? '—' }}</td>
                                                    <td class="px-3 py-2 text-gray-900 font-mono text-right">{{ $produto['valor'] ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- Metadados da consulta --}}
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                            <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consultado em</p>
                                <p class="text-xs text-gray-900 mt-1">{{ $notaResultado['consultado_em'] ?? '—' }}</p>
                            </div>
                            @if($isCteResultado)
                                <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Valor da prestação</p>
                                    <p class="text-xs text-gray-900 font-mono mt-1">{{ $detalhes['valor_prestacao_label'] ?? '—' }}</p>
                                </div>
                            @endif
                            @if(!empty($detalhes['comprovante_url']))
                                <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Comprovante</p>
                                    <a href="{{ $detalhes['comprovante_url'] }}" target="_blank" rel="noopener" class="text-xs text-blue-700 hover:underline mt-1 inline-block">Abrir comprovante oficial</a>
                                </div>
                            @endif
                            @if(!empty($detalhes['url_xml']))
                                <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">XML</p>
                                    <a href="{{ $detalhes['url_xml'] }}" target="_blank" rel="noopener" class="text-xs text-blue-700 hover:underline mt-1 inline-block">Baixar XML</a>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Nenhum dos CNPJs é cliente: usuário classifica quem é cliente e quem é participante --}}
                    @if(!empty($classificacaoPartes))
                        <div id="classificar-partes-bloco"
                             class="border rounded overflow-hidden"
                             style="border-color: #fcd34d;"
                             data-endpoint="{{ route('app.clearance.buscar.classificar-partes') }}"
                             data-chave="{{ $classificacaoPartes['chave_acesso'] }}">
                            <div class="px-3 py-2 border-b" style="background-color: #fffbeb; border-color: #fde68a;">
                                <p class="text-[10px] font-bold uppercase tracking-widest" style="color: #92400e;">Organize sua carteira</p>
                                <p class="text-xs mt-0.5" style="color: #78350f;">Nenhum destes CNPJs está cadastrado como seu cliente. Qual deles é o seu cliente? O outro será registrado como participante (contraparte).</p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-gray-200 bg-white">
                                @foreach($classificacaoPartes['lados'] as $parte)
                                    <div class="px-3 py-3 flex flex-col gap-2">
                                        <div>
                                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $parte['papel'] }}</p>
                                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $parte['nome'] }}</p>
                                            <p class="text-[11px] text-gray-500 font-mono">{{ $parte['cnpj_fmt'] }}</p>
                                        </div>
                                        <button type="button"
                                                data-classificar-lado="{{ $parte['lado'] }}"
                                                class="self-start inline-flex items-center justify-center px-3 py-1.5 text-white rounded text-xs font-semibold"
                                                style="background-color: #1f2937;">
                                            Este é meu cliente
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <div class="px-3 py-2 border-t border-gray-200 bg-gray-50 flex items-center justify-between gap-2">
                                <p id="classificar-partes-feedback" class="text-[11px] text-gray-500"></p>
                                <button type="button" id="classificar-partes-dispensar" class="text-[11px] text-gray-500 hover:text-gray-800 hover:underline whitespace-nowrap">Nenhum é meu cliente</button>
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row gap-2 pt-1">
                        @if(!empty($notaResultado['detalhe_url']))
                            <a href="{{ $notaResultado['detalhe_url'] }}" data-link class="px-4 py-2 rounded text-sm font-medium text-white text-center" style="background-color: #374151">Ver detalhe do documento</a>
                        @endif
                        <a href="/app/clearance/buscar" data-link class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-sm font-medium text-center">Nova busca</a>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded border border-gray-300 p-4 border-l-4 border-l-blue-500">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Aguardando persistência</p>
                <p class="mt-2 text-sm text-gray-700">O provedor finalizou a consulta, mas o resultado final ainda não apareceu no acervo/tabelas canônicas. Esta página continuará tentando carregar o retorno.</p>
            </div>
        @endif
    </div>
</div>

<script src="{{ asset('js/progresso-automacao.js') }}?v={{ @filemtime(public_path('js/progresso-automacao.js')) ?: time() }}"></script>
<script src="{{ asset('js/clearance-resultado.js') }}?v={{ @filemtime(public_path('js/clearance-resultado.js')) ?: time() }}" defer></script>
