{{-- Detalhes do Alerta --}}
<div class="min-h-screen bg-gray-100 pb-12" id="alerta-detalhes-container">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        
        {{-- Breadcrumb / Voltar --}}
        <div class="mb-4 sm:mb-6">
            <a href="/app/alertas" data-link class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 hover:underline transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Voltar para Central de Alertas
            </a>
        </div>

        {{-- Cabeçalho do Alerta --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Alerta Fiscal</span>
            </div>
            <div class="p-5 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        @php
                            $sevColors = [
                                'alta' => '#dc2626',
                                'media' => '#b45309',
                                'baixa' => '#9ca3af'
                            ];
                            $sevLabels = ['alta' => 'Alta', 'media' => 'Média', 'baixa' => 'Baixa'];
                            $sevClass = $sevColors[$alerta->severidade ?? 'baixa'] ?? $sevColors['baixa'];
                            $sevLabel = $sevLabels[$alerta->severidade ?? 'baixa'] ?? 'Baixa';
                            
                            $catLabels = [
                                'notas_fiscais' => 'Notas Fiscais',
                                'compliance' => 'Compliance',
                                'importacao' => 'Importação'
                            ];
                            $catLabel = $catLabels[$alerta->categoria ?? ''] ?? \Str::title(str_replace('_', ' ', $alerta->categoria));
                        @endphp
                        
                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $sevClass }}">
                            Severidade {{ $sevLabel }}
                        </span>
                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">
                            {{ $catLabel }}
                        </span>
                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #9ca3af">
                            {{ $alerta->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">{{ $alerta->titulo }}</h1>
                    <p class="text-sm sm:text-base text-gray-600 max-w-2xl">{{ $alerta->descricao }}</p>
                    @if(!empty($alerta->notas))
                    <div class="mt-3 inline-flex items-start gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded text-sm text-gray-600 max-w-2xl">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        <span><span class="font-semibold text-gray-700">Motivo:</span> {{ $alerta->notas }}</span>
                    </div>
                    @endif
                </div>
                
                @if($alerta->status === 'ativo')
                <div class="flex-shrink-0">
                    <button onclick="document.getElementById('modal-resolver-alerta').classList.remove('hidden')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gray-800 text-white text-sm font-semibold rounded hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Resolver Alerta
                    </button>
                </div>
                @else
                <div class="flex-shrink-0 flex flex-col items-stretch sm:items-end gap-2">
                    @if($alerta->status === 'resolvido')
                        <span class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded" style="background-color: #047857">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Resolvido em {{ $alerta->resolvido_em ? $alerta->resolvido_em->format('d/m/Y H:i') : '-' }}
                        </span>
                    @else
                        <span class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded" style="background-color: #9ca3af">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Ignorado
                        </span>
                    @endif
                    {{-- Reabrir: volta para ativo (registra "reaberto" na auditoria) --}}
                    <button type="button" id="btn-reabrir-alerta" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reabrir
                    </button>
                </div>
                @endif
            </div>
            
        {{-- Entidades Relacionadas --}}
        @php
            $detalhesAlerta = is_string($alerta->detalhes) ? json_decode($alerta->detalhes, true) : $alerta->detalhes;
            $detalhesAlerta = is_array($detalhesAlerta) ? $detalhesAlerta : [];
            $notaPrincipalId = null;

            if (!empty($detalhesAlerta['nota_id'])) {
                $notaPrincipalId = $detalhesAlerta['nota_id'];
            } elseif (array_is_list($detalhesAlerta) && count($detalhesAlerta) === 1 && !empty($detalhesAlerta[0]['nota_id'])) {
                $notaPrincipalId = $detalhesAlerta[0]['nota_id'];
            } elseif (!empty($detalhesAlerta['itens']) && is_array($detalhesAlerta['itens']) && count($detalhesAlerta['itens']) === 1 && !empty($detalhesAlerta['itens'][0]['nota_id'])) {
                $notaPrincipalId = $detalhesAlerta['itens'][0]['nota_id'];
            } elseif (!empty($detalhesAlerta['notas']) && is_array($detalhesAlerta['notas']) && count($detalhesAlerta['notas']) === 1 && !empty($detalhesAlerta['notas'][0]['nota_id'])) {
                $notaPrincipalId = $detalhesAlerta['notas'][0]['nota_id'];
            }
        @endphp
        @if($alerta->cliente_id || $alerta->participante_id || $notaPrincipalId)
            <div class="mt-6 pt-5 border-t border-gray-100 flex flex-wrap gap-6">
                @if($alerta->cliente)
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Cliente Vinculado</p>
                    <a href="/app/cliente/{{ $alerta->cliente_id }}" data-link class="inline-flex items-center gap-2 text-sm text-gray-900 hover:text-gray-600 font-medium group transition-colors">
                        <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        {{ $alerta->cliente->razao_social }}
                    </a>
                </div>
                @endif
                
                @if($alerta->participante)
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Participante / Fornecedor</p>
                    <a href="/app/participante/{{ $alerta->participante_id }}" data-link class="inline-flex items-center gap-2 text-sm text-gray-900 hover:text-gray-600 font-medium group transition-colors">
                        <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        {{ $alerta->participante->razao_social }}
                    </a>
                </div>
                @endif

                @if($notaPrincipalId)
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Nota Fiscal Referida</p>
                    <a href="/app/notas/efd/{{ $notaPrincipalId }}" data-link class="inline-flex items-center gap-2 text-sm text-gray-900 hover:text-gray-600 font-medium group transition-colors">
                        <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        Ir para Nota Fiscal
                    </a>
                </div>
                @endif
            </div>
            @endif
            </div>
        </div>

        {{-- Guia de Resolução Didático (fonte: App\Services\GuiaAlertaService) --}}
        @php
            $iconeAcao = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>';
            if (in_array($alerta->tipo, ['nunca_consultado', 'consulta_vencida'])) {
                $iconeAcao = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>';
            } elseif (in_array($alerta->tipo, ['situacao_irregular', 'cnpj_situacao_irregular', 'participante_inativo', 'participante_sem_ie', 'fornecedor_irregular'])) {
                $iconeAcao = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
            }
        @endphp

        <div class="mt-6 bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Orientação de Tratativa</span>
            </div>
            <div class="px-5 sm:px-8 py-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $guia['titulo_o_que_e'] }}
                    </h3>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $guia['texto_o_que_e'] }}</p>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $iconeAcao !!}</svg>
                        {{ $guia['titulo_acao'] }}
                    </h3>
                    <p class="text-sm text-gray-700 leading-relaxed mb-3">{{ $guia['texto_acao'] }}</p>
                    
                    @if($guia['cta_url'])
                    <a href="{{ $guia['cta_url'] }}" data-link class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-50 hover:text-gray-900 transition-colors">
                        {{ $guia['cta_text'] }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Como calculamos: transparência auditável (fonte, gatilho, janela, limiar) --}}
            @if(!empty($guia['metodologia']))
            <div class="mt-6 pt-5 border-t border-gray-100">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Como calculamos
                </h3>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $guia['metodologia'] }}</p>
            </div>
            @endif
            </div>
        </div>

        {{-- Histórico deste alerta (auditoria: quem, quando, de→para) --}}
        @php
            $auditorias = $alerta->auditorias()->with('user:id,name')->limit(50)->get();
            $acaoHexDet = [
                'criado' => '#6b7280', 'resolvido' => '#047857', 'auto_resolvido' => '#059669',
                'ignorado' => '#9ca3af', 'visto' => '#374151', 'reaberto' => '#b45309', 'reativado' => '#b45309',
            ];
        @endphp
        @if($auditorias->isNotEmpty())
        <div class="mt-6 sm:mt-8 bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Histórico deste alerta</span>
                <a href="/app/alertas/historico" data-link class="text-[11px] text-gray-500 hover:text-gray-800 hover:underline">Ver histórico completo</a>
            </div>
            <div class="p-4">
                @foreach($auditorias as $ev)
                    @php $hex = $acaoHexDet[$ev->acao] ?? '#6b7280'; @endphp
                    <div class="flex items-start gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                        <span class="mt-1.5 w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $hex }}"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $hex }}">{{ $ev->acaoLabel() }}</span>
                                <span class="text-[11px] text-gray-500">por <span class="font-medium text-gray-700">{{ $ev->atorLabel() }}</span> · {{ $ev->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($ev->notas)
                                <p class="mt-1 text-[12px] text-gray-600">{{ $ev->notas }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Certidão positiva: apresentação dedicada (certidões + exposição de compras) --}}
        @if($alerta->tipo === 'certidao_positiva')
        @php
            $detCert = is_string($alerta->detalhes) ? json_decode($alerta->detalhes, true) : ($alerta->detalhes ?? []);
            $detCert = is_array($detCert) ? $detCert : [];
            $sevHex = ['alta' => '#dc2626', 'media' => '#b45309', 'baixa' => '#9ca3af'];
            $vTotal = (float) ($detCert['valor_total'] ?? 0);
        @endphp
        <div class="mt-6 sm:mt-8 bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <h3 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Certidões positivas encontradas</h3>
            </div>
            <div class="p-4 space-y-2">
                {{-- Card retrátil DS (mesmo padrão do participante/lote): status à vista no
                     header, detalhe abre sob demanda. --}}
                @foreach(($detCert['certidoes'] ?? []) as $cert)
                    @php $hex = $sevHex[$cert['severidade'] ?? ''] ?? '#9ca3af'; @endphp
                    <x-card-retratil :titulo="$cert['label'] ?? '—'" :acento="$hex">
                        <x-slot:badges>
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white shrink-0" style="background-color: {{ $hex }}">{{ $cert['status'] ?? 'Positiva' }}</span>
                        </x-slot:badges>
                        <p class="text-[12px] text-gray-700">Status informado pela fonte: <span class="font-medium">{{ $cert['status'] ?? 'Positiva' }}</span></p>
                        @if(!empty($cert['severidade']))
                            <p class="text-[11px] text-gray-500">Severidade do apontamento: {{ ucfirst($cert['severidade']) }}.</p>
                        @endif
                        @if($alerta->participante_id)
                            <a href="/app/participante/{{ $alerta->participante_id }}" data-link class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-700 hover:text-gray-900 hover:underline">Ver certidão completa no perfil</a>
                        @endif
                    </x-card-retratil>
                @endforeach
            </div>

            @if($vTotal > 0)
            <div class="border-t border-gray-200 px-4 py-3">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest mb-3">Exposição de compras deste fornecedor</p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">Últimos 12 meses</p>
                        <p class="text-sm font-bold text-gray-900">R$&nbsp;{{ number_format((float) ($detCert['valor_12m'] ?? 0), 2, ',', '.') }}</p>
                        <p class="text-[10px] text-gray-400">risco corrente</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">Últimos 5 anos</p>
                        <p class="text-sm font-bold" style="color:#b45309;">R$&nbsp;{{ number_format((float) ($detCert['valor_5anos'] ?? 0), 2, ',', '.') }}</p>
                        <p class="text-[10px] text-gray-400">sujeito a glosa (decadência)</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wide">Total histórico</p>
                        <p class="text-sm font-bold text-gray-900">R$&nbsp;{{ number_format($vTotal, 2, ',', '.') }}</p>
                        <p class="text-[10px] text-gray-400">{{ (int) ($detCert['qtd_notas'] ?? 0) }} nota(s)</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Certidão vencendo: prazos no card retrátil DS (mesmo padrão do modal da central). --}}
        @if($alerta->tipo === 'certidao_vencendo')
        @php
            $detVenc = is_string($alerta->detalhes) ? json_decode($alerta->detalhes, true) : ($alerta->detalhes ?? []);
            $detVenc = is_array($detVenc) ? $detVenc : [];
        @endphp
        @if(!empty($detVenc['certidoes']))
        <div class="mt-6 sm:mt-8 bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <h3 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Prazos das certidões</h3>
            </div>
            <div class="p-4 space-y-2">
                @foreach($detVenc['certidoes'] as $cert)
                    @php
                        $dias = isset($cert['dias']) && is_numeric($cert['dias']) ? (int) $cert['dias'] : null;
                        $vencida = ! empty($cert['vencida']);
                        if ($vencida) {
                            $diasAbs = $dias !== null ? abs($dias) : null;
                            $prazo = $diasAbs === 1 ? 'Vencida há 1 dia' : ($diasAbs !== null ? 'Vencida há '.$diasAbs.' dias' : 'Vencida');
                        } elseif ($dias === 0) {
                            $prazo = 'Vence hoje';
                        } elseif ($dias === 1) {
                            $prazo = 'Vence em 1 dia';
                        } elseif ($dias !== null) {
                            $prazo = 'Vence em '.$dias.' dias';
                        } else {
                            $prazo = 'Próxima do vencimento';
                        }
                        $hexVenc = $vencida || ($dias !== null && $dias <= 7) ? '#dc2626' : '#b45309';
                    @endphp
                    <x-card-retratil :titulo="$cert['label'] ?? 'Certidão'" :acento="$hexVenc">
                        <x-slot:badges>
                            <span class="whitespace-nowrap px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white shrink-0" style="background-color: {{ $hexVenc }}">{{ $prazo }}</span>
                        </x-slot:badges>
                        <p class="text-[12px] text-gray-700">Validade: <span class="font-medium">{{ $cert['validade'] ?? 'não informada' }}</span></p>
                        <p class="text-[11px] text-gray-500">{{ $prazo }}.</p>
                        @if($alerta->participante_id)
                            <a href="/app/participante/{{ $alerta->participante_id }}" data-link class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-700 hover:text-gray-900 hover:underline">Renovar/ver certidão no perfil</a>
                        @endif
                    </x-card-retratil>
                @endforeach
            </div>
        </div>
        @endif
        @endif

        {{-- Registros Afetados (Interface Melhorada) — genérico, exceto tipos com apresentação dedicada --}}
        @php
            $dados = is_string($alerta->detalhes) ? json_decode($alerta->detalhes, true) : $alerta->detalhes;
            
            if (!is_array($dados)) {
                $dados = [];
            }
            
            // Check if it's a list of objects (sequential array of arrays)
            $isList = array_is_list($dados) && count($dados) > 0 && is_array($dados[0]);
            
            // Formatador de valores (Moedas, Datas, CNPJ/CPF, CFOP, NCM)
            $formatarValor = function($chave, $valor) {
                if ($valor === null || $valor === '') return '-';
                if (is_array($valor) || is_object($valor)) {
                    $arr = (array)$valor;
                    if (array_is_list($arr)) {
                        $html = '<div class="flex flex-wrap gap-1.5 mt-1">';
                        foreach($arr as $v) {
                            $strVal = is_string($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE);
                            $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-white text-gray-700 border border-gray-200 shadow-sm">' . htmlspecialchars((string)$strVal) . '</span>';
                        }
                        $html .= '</div>';
                        return $html;
                    }
                    return '<pre class="text-[10px] bg-gray-50 p-2 rounded mt-1 border border-gray-100 overflow-x-auto text-gray-700">' . json_encode($valor, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                }
                
                $chaveLower = strtolower($chave);
                
                // Formatar Documentos
                if (in_array($chaveLower, ['cnpj', 'cnpj_cpf', 'cpf', 'documento'])) {
                    $limpo = preg_replace('/[^0-9]/', '', (string)$valor);
                    $formatado = $valor;
                    if (strlen($limpo) === 14) {
                        $formatado = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $limpo);
                    } elseif (strlen($limpo) === 11) {
                        $formatado = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $limpo);
                    }
                    return '<div class="flex items-center gap-1.5 group">' . 
                           '<span>' . $formatado . '</span>' .
                           '<a href="https://www.google.com/search?q=consulta+cnpj+' . $limpo . '" target="_blank" rel="noopener noreferrer" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-900 transition-all flex-shrink-0" title="Consultar Documento">' .
                           '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>' .
                           '</a>' .
                           '</div>';
                }
                
                // Dicionário CFOP
                if ($chaveLower === 'cfop') {
                    $cfopStr = (string)$valor;
                    $cfopDict = [
                        '1102' => 'Compra para comercialização',
                        '1403' => 'Compra de marc. sujeita a ST',
                        '5102' => 'Venda de mercadoria adquirida de terceiros',
                        '5405' => 'Venda de merc. com ICMS retido por ST',
                        '6102' => 'Venda (operação interestadual)',
                        '6403' => 'Venda c/ retenção (interestadual)',
                        '6108' => 'Venda para não contribuinte (interestadual)',
                        '5929' => 'Lançamento efetuado em decorrência de EC',
                        '1949' => 'Outra entrada de merc. ou serviço',
                        '5949' => 'Outra saída de mercadoria',
                    ];
                    $desc = $cfopDict[$cfopStr] ?? 'Código Fiscal de Operações';
                    return '<span class="cursor-help border-b border-dashed border-gray-400 text-gray-700 font-medium" title="' . $desc . '">' . $valor . '</span>';
                }
                
                // Formatar NCM
                if (str_contains($chaveLower, 'ncm')) {
                    $valStr = (string)$valor;
                    if(strlen($valStr) == 8) {
                        $formatado = preg_replace('/(\d{4})(\d{2})(\d{2})/', '$1.$2.$3', $valStr);
                        return '<span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #4338ca" title="Nomenclatura Comum do Mercosul">' . $formatado . '</span>';
                    }
                    return '<span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #4338ca">' . $valor . '</span>';
                }

                // Destaque para descrição de Produto
                if ($chaveLower === 'xprod' || $chaveLower === 'descricao' || $chaveLower === 'produto') {
                    return '<span class="font-bold text-gray-900">' . $valor . '</span>';
                }
                
                // Formatar CST/CSOSN Origem
                if (in_array($chaveLower, ['cst', 'csosn', 'origem'])) {
                    $cstDict = [
                        '00' => 'Tributada integralmente',
                        '10' => 'Tributada e com cobrança do ICMS por ST',
                        '20' => 'Com redução de base de cálculo',
                        '30' => 'Isenta ou não tributada e com cobrança do ICMS por ST',
                        '40' => 'Isenta',
                        '41' => 'Não tributada',
                        '50' => 'Suspensão',
                        '51' => 'Diferimento',
                        '60' => 'ICMS cobrado anteriormente por ST',
                        '70' => 'Com redução de base de cálculo e cobrança do ICMS por ST',
                        '90' => 'Outras',
                        '101' => 'SN - Tributada com permissão de crédito',
                        '102' => 'SN - Tributada sem permissão de crédito',
                        '103' => 'SN - Isenção do ICMS para faixa de receita bruta',
                        '201' => 'SN - Tributada com permissão de crédito e cobrança por ST',
                        '202' => 'SN - Tributada sem permissão de crédito e cobrança por ST',
                        '500' => 'SN - ICMS cobrado anteriormente por ST (substituído)',
                        '900' => 'SN - Outros',
                    ];
                    $desc = $cstDict[(string)$valor] ?? 'Código de Situação Tributária';
                    return '<span class="cursor-help font-mono text-xs text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200" title="' . $desc . '">' . $valor . '</span>';
                }
                
                // Formatar Moeda
                if (str_contains($chaveLower, 'valor') || str_contains($chaveLower, 'vnf') || str_contains($chaveLower, 'vlr') || str_contains($chaveLower, 'vprod') || $chaveLower === 'vtributo') {
                    if (is_numeric($valor)) {
                        return 'R$ ' . number_format((float)$valor, 2, ',', '.');
                    }
                }
                
                // Formatar Datas
                if (str_contains($chaveLower, 'data') || str_contains($chaveLower, 'dhemi') || str_contains($chaveLower, 'created_at') || str_contains($chaveLower, 'vencimento')) {
                    $time = strtotime($valor);
                    if ($time !== false) {
                        if (str_contains($valor, 'T') || str_contains($valor, ' ')) {
                            return date('d/m/Y H:i', $time);
                        }
                        return date('d/m/Y', $time);
                    }
                }
                
                return $valor;
            };
            
            // Função para traduzir/padronizar cabeçalhos de tabela e cards
            $formatarChave = function($chave) {
                $mapa = [
                    'chnfe' => 'Chave de Acesso',
                    'chave' => 'Chave de Acesso',
                    'nnf' => 'Nº da Nota',
                    'serie' => 'Série',
                    'cnf' => 'Código',
                    'dhemi' => 'Emissão',
                    'cnpj' => 'CNPJ',
                    'cpf' => 'CPF',
                    'vnf' => 'Valor Total',
                    'vlr' => 'Valor',
                    'cfop' => 'CFOP',
                    'cst' => 'CST',
                    'natop' => 'Natureza da Operação',
                    'xnome' => 'Razão Social',
                    'modelo' => 'Modelo',
                    'status' => 'Status'
                ];
                $chaveLower = strtolower($chave);
                return $mapa[$chaveLower] ?? \Str::title(str_replace('_', ' ', $chave));
            };
            
            // Chaves técnicas irrelevantes para análise contábil
            $ignorar = ['id', 'participante_id', 'cliente_id', 'empresa_id', 'created_at', 'updated_at', 'deleted_at', 'tenant_id', 'alerta_id'];

            // Extrair colunas (se for matriz) e ignorar as técnicas
            $headers = [];
            $impactoFinanceiro = 0;
            $hasImpact = false;
            
            if ($isList) {
                foreach ($dados as $item) {
                    if(is_array($item)) {
                        foreach (array_keys($item) as $key) {
                            if (!in_array($key, $headers) && !in_array(strtolower($key), $ignorar)) {
                                $headers[] = $key;
                            }
                        }
                        // Calcular impacto
                        foreach ($item as $k => $v) {
                            $kLower = strtolower($k);
                            if (in_array($kLower, ['vnf', 'vlr', 'valor', 'vprod', 'vtributo']) && is_numeric($v)) {
                                $impactoFinanceiro += (float) $v;
                                $hasImpact = true;
                            }
                        }
                    }
                }
            } else {
                // Se for objeto único (card), também retiramos as chaves indesejadas
                $dadosFiltrados = [];
                foreach ((array)$dados as $key => $val) {
                    if (!in_array(strtolower($key), $ignorar)) {
                        $dadosFiltrados[$key] = $val;
                    }
                    $kLower = strtolower($key);
                    if (in_array($kLower, ['vnf', 'vlr', 'valor', 'vprod', 'vtributo']) && is_numeric($val)) {
                        $impactoFinanceiro += (float) $val;
                        $hasImpact = true;
                    }
                }
                $dados = $dadosFiltrados;
            }
        @endphp

        @if(!empty($dados) && ! in_array($alerta->tipo, ['certidao_positiva', 'certidao_vencendo'], true))
        <div class="mt-6 sm:mt-8 bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h3 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Registros Afetados
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">
                            {{ $alerta->total_afetados ?? count($isList ? $dados : [$dados]) }} registro(s)
                        </span>
                        @if($hasImpact)
                        <span class="whitespace-nowrap inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #047857" title="Impacto Financeiro Estimado (somatório)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            R$&nbsp;{{ number_format($impactoFinanceiro, 2, ',', '.') }}
                        </span>
                        @endif
                    </div>
                </div>
                
                @if($isList && !empty($dados))
                <button onclick="exportarTabelaParaCSV()" class="auth-control w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Exportar CSV
                </button>
                @endif
            </div>
            
            <div class="overflow-x-auto border-b border-gray-100 pb-1">
                @if($isList)
                <table class="min-w-full tabela-cards">
                    <thead>
                        <tr class="border-b border-gray-300">
                            @foreach($headers as $header)
                            <th scope="col" class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide whitespace-nowrap bg-gray-50">
                                {{ $formatarChave($header) }}
                            </th>
                            @endforeach
                            <th scope="col" class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide whitespace-nowrap bg-gray-50">
                                Ação
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($dados as $item)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            @foreach($headers as $header)
                            @php
                                $val = $item[$header] ?? null;
                                $isChave = strtolower($header) === 'chnfe' || strtolower($header) === 'chave';
                            @endphp
                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700" data-label="{{ $formatarChave($header) }}">
                                @if($isChave && $val)
                                <div class="flex items-center gap-2 group">
                                    <span class="font-mono text-xs text-gray-800">{{ $val }}</span>
                                    <button onclick="copiarTexto(this, '{{ $val }}')" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-900 transition-all flex-shrink-0 focus:opacity-100" title="Copiar Chave">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                    <a href="https://www.nfe.fazenda.gov.br/portal/consultaRecaptcha.aspx?tipoConsulta=resumo&tipoConteudo=7PhJ+gAVw2g=&chave={{ $val }}" target="_blank" rel="noopener noreferrer" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-900 transition-all flex-shrink-0 focus:opacity-100" title="Consultar na SEFAZ">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                                @else
                                    {!! $formatarValor($header, $val) !!}
                                @endif
                            </td>
                            @endforeach
                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700" data-label="Ação">
                                @if(!empty($item['nota_id']))
                                    <a href="/app/notas/efd/{{ $item['nota_id'] }}" data-link class="inline-flex items-center gap-1 text-xs text-gray-700 hover:text-gray-900 hover:underline font-medium">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Abrir Nota
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 bg-white">
                    @foreach($dados as $key => $val)
                    @php
                        $isChave = strtolower($key) === 'chnfe' || strtolower($key) === 'chave';
                    @endphp
                    <div class="bg-gray-50 rounded border border-gray-200 p-3.5 transition-colors">
                        <dt class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">{{ $formatarChave($key) }}</dt>
                        <dd class="text-sm font-medium text-gray-900 break-words">
                            @if($isChave && $val)
                            <div class="flex items-center gap-2 flex-wrap group">
                                <span class="font-mono text-xs">{{ $val }}</span>
                                <button onclick="copiarTexto(this, '{{ $val }}')" class="text-gray-400 hover:text-gray-900 transition-colors inline-block" title="Copiar Chave">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                                <a href="https://www.nfe.fazenda.gov.br/portal/consultaRecaptcha.aspx?tipoConsulta=resumo&tipoConteudo=7PhJ+gAVw2g=&chave={{ $val }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-gray-900 transition-colors inline-block" title="Consultar na SEFAZ">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                            @else
                                {!! $formatarValor($key, $val) !!}
                            @endif
                        </dd>
                    </div>
                    @endforeach
                </div>
                @if(!empty($dados['nota_id']))
                <div class="px-5 pb-5">
                    <a href="/app/notas/efd/{{ $dados['nota_id'] }}" data-link class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 text-gray-700 text-xs font-semibold rounded hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Ir para Nota Fiscal
                    </a>
                </div>
                @endif
                @endif
            </div>
        </div>
        
        <script>
            if (typeof window.copiarTexto !== 'function') {
                window.copiarTexto = function(btn, texto) {
                    if(navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(texto).then(sucessoCopia);
                    } else {
                        // Fallback para navs antigos
                        let textArea = document.createElement("textarea");
                        textArea.value = texto;
                        textArea.style.position = "fixed";
                        textArea.style.left = "-999999px";
                        textArea.style.top = "-999999px";
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        try {
                            document.execCommand('copy');
                            sucessoCopia();
                        } catch (err) { }
                        textArea.remove();
                    }
                    
                    function sucessoCopia() {
                        const originalHtml = btn.innerHTML;
                        const originalClasses = btn.className;
                        
                        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                        btn.className = "text-green-600 flex-shrink-0 transition-all opacity-100";
                        
                        setTimeout(() => {
                            btn.innerHTML = originalHtml;
                            btn.className = originalClasses;
                        }, 2000);
                    }
                };
            }

            if (typeof window.exportarTabelaParaCSV !== 'function') {
                window.exportarTabelaParaCSV = function() {
                    const dados = @json($dados);
                    if (!dados || (Array.isArray(dados) && dados.length === 0)) return;
                    
                    const list = Array.isArray(dados) ? dados : [dados];
                    if (list.length === 0) return;

                    const headers = Object.keys(list[0]);
                    const csvRows = [];
                    
                    // Header row
                    csvRows.push(headers.join(';'));
                    
                    // Data rows
                    for (const row of list) {
                        const values = headers.map(header => {
                            let val = row[header] !== null && row[header] !== undefined ? String(row[header]) : '';
                            // Tratar aspas e ponto e vírgula para não quebrar o CSV brasileiro
                            val = val.replace(/"/g, '""');
                            if (val.search(/([";\n])/g) >= 0) {
                                val = `"${val}"`;
                            }
                            return val;
                        });
                        csvRows.push(values.join(';'));
                    }
                    
                    const csvContent = "\uFEFF" + csvRows.join("\n");
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement("a");
                    link.setAttribute("href", url);
                    link.setAttribute("download", "alerta_{{ $alerta->id }}_dados.csv");
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                };
            }
        </script>
        @endif

    </div>
</div>

{{-- MODAL DE RESOLUÇÃO — só faz sentido para alerta ainda ativo --}}
@if($alerta->status === 'ativo')
<div id="modal-resolver-alerta" class="hidden relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div id="modal-resolver-backdrop" class="fixed inset-0 bg-gray-900/75 transition-opacity backdrop-blur-sm"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-300">
                {{-- Header --}}
                <div class="bg-white px-5 pt-5 pb-4 border-b border-gray-100">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full" style="background-color:#ecfdf5">
                            <svg class="h-5 w-5" style="color:#047857" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Marcar alerta como resolvido</h3>
                            {{-- Contexto do alerta sendo resolvido --}}
                            <p class="mt-1 text-sm text-gray-500 truncate">{{ $alerta->titulo }}</p>
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="bg-white px-5 py-4 space-y-4">
                    {{-- Faixa "rede de segurança": alinhada ao comportamento (resolvido reativa) --}}
                    <div class="flex gap-2.5 rounded border px-3 py-2.5" style="border-color:#bfdbfe;background-color:#eff6ff">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:#2563eb" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-[13px] leading-5 text-gray-700">
                            Pode marcar sem medo: os alertas são recalculados a partir dos seus dados.
                            Se o problema <strong>ainda aparecer</strong> na próxima verificação, o alerta
                            <strong>reaparece sozinho</strong> — resolver não esconde um problema que não foi corrigido.
                        </p>
                    </div>

                    @if(!empty($guia['texto_acao']))
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Como resolver de verdade</p>
                        <p class="text-[13px] text-gray-600 leading-5">{{ $guia['texto_acao'] }}</p>
                    </div>
                    @endif

                    <div>
                        <label for="alerta-nota-resolucao" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Nota de resolução (opcional)</label>
                        <textarea id="alerta-nota-resolucao" rows="2" class="block w-full rounded border border-gray-300 shadow-sm focus:border-gray-400 focus:ring-1 focus:ring-gray-400 text-sm" placeholder="Ex: corrigido no ERP e reimportado o SPED em 04/07"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-5 py-3 flex flex-col sm:flex-row-reverse gap-2">
                    <button type="button" id="btn-confirmar-resolucao" class="inline-flex w-full sm:w-auto justify-center items-center gap-1.5 rounded px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90 focus:outline-none transition-opacity" style="background-color:#047857">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Confirmar e resolver
                    </button>
                    <button type="button" class="js-fechar-modal-resolver mt-0 inline-flex w-full sm:w-auto justify-center rounded bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-none transition-colors">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function(){
        const modal = document.getElementById('modal-resolver-alerta');
        const btnConfirm = document.getElementById('btn-confirmar-resolucao');
        if(!modal || !btnConfirm) return;

        function fecharModal() { modal.classList.add('hidden'); }
        // Fechar: botão Cancelar, clique no backdrop, tecla Esc.
        modal.querySelectorAll('.js-fechar-modal-resolver').forEach(function(b){ b.addEventListener('click', fecharModal); });
        var backdrop = document.getElementById('modal-resolver-backdrop');
        if (backdrop) backdrop.addEventListener('click', fecharModal);
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && !modal.classList.contains('hidden')) fecharModal(); });

        const btnConfirmHtml = btnConfirm.innerHTML;

        btnConfirm.addEventListener('click', async function() {
            btnConfirm.disabled = true;
            btnConfirm.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Resolvendo...';
            
            const notas = document.getElementById('alerta-nota-resolucao').value;

            try {
                const docToken = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = docToken ? docToken.content : '';
                
                const response = await fetch('/app/alertas/{{ $alerta->id }}/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: 'resolvido', notas: notas })
                });

                if(response.ok) {
                    if (window.SPA && window.SPA.navigate) {
                        document.getElementById('modal-resolver-alerta').classList.add('hidden');
                        window.SPA.navigate('/app/alertas');
                    } else {
                        window.location.href = '/app/alertas';
                    }
                } else {
                    const error = await response.json();
                    alert('Ocorreu um erro ao resolver o alerta: ' + (error.message || 'Tente novamente.'));
                    btnConfirm.disabled = false;
                    btnConfirm.innerHTML = btnConfirmHtml;
                }
            } catch(e) {
                alert('Erro na requisição. Verifique sua conexão.');
                btnConfirm.disabled = false;
                btnConfirm.innerHTML = btnConfirmHtml;
            }
        });
    })();
</script>
@endif

{{-- Reabrir alerta resolvido/ignorado (volta para ativo) --}}
@if(in_array($alerta->status, ['resolvido', 'ignorado'], true))
<script>
    (function(){
        const btn = document.getElementById('btn-reabrir-alerta');
        if(!btn) return;
        const originalHtml = btn.innerHTML;
        btn.addEventListener('click', async function() {
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-1 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Reabrindo...';
            try {
                const meta = document.querySelector('meta[name="csrf-token"]');
                const response = await fetch('/app/alertas/{{ $alerta->id }}/status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': meta ? meta.content : '', 'Accept': 'application/json' },
                    body: JSON.stringify({ status: 'ativo' })
                });
                if(response.ok) {
                    if (window.SPA && window.SPA.navigate) window.SPA.navigate('/app/alertas');
                    else window.location.href = '/app/alertas';
                } else {
                    alert('Não foi possível reabrir o alerta. Tente novamente.');
                    btn.disabled = false; btn.innerHTML = originalHtml;
                }
            } catch(e) {
                alert('Erro na requisição. Verifique sua conexão.');
                btn.disabled = false; btn.innerHTML = originalHtml;
            }
        });
    })();
</script>
@endif
