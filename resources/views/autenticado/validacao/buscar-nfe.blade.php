@php
    $saldoAtual = (int) ($saldoAtual ?? 0);
    $custoEstimadoCreditos = (int) ($custoEstimadoCreditos ?? 14);
    $clientes = $clientes ?? collect();
    $ultimasConsultasDfe = $ultimasConsultasDfe ?? collect();
    $saldoSuficiente = $saldoAtual >= $custoEstimadoCreditos;

    $badgeCoresSituacao = [
        'AUTORIZADA' => '#047857',
        'NEGATIVA' => '#047857',
        'CANCELADA' => '#dc2626',
        'DENEGADA' => '#dc2626',
        'INUTILIZADA' => '#dc2626',
        'INDETERMINADO' => '#d97706',
        'NAO_ENCONTRADA' => '#d97706',
        'ERRO_PARAMETRO' => '#6b7280',
        'ERRO_PROVEDOR' => '#6b7280',
    ];
@endphp

<style>
    .buscar-dfe-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 1.5rem;
        align-items: start;
    }

    @media (max-width: 1023px) {
        .buscar-dfe-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    .documento-tipo-card {
        border-color: #d1d5db;
        background-color: #f9fafb;
        position: relative;
    }

    .documento-tipo-card.is-selected {
        border-color: #1f2937;
        background-color: #f3f4f6;
        box-shadow: inset 0 0 0 1px #1f2937;
    }

    .documento-tipo-card.is-disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    .documento-tipo-card.is-disabled:hover {
        background-color: #f9fafb;
    }

    .documento-tipo-card .documento-selecionado {
        display: none;
    }

    .documento-tipo-card.is-selected .documento-selecionado {
        display: inline-flex;
    }

    .progress-track {
        width: 100%;
        height: 6px;
        background-color: #e5e7eb;
        border-radius: 9999px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background-color: #1f2937;
        width: 8%;
        transition: width 350ms ease-out;
    }
</style>

<div class="min-h-screen bg-gray-100" id="buscar-nfe-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Buscar DF-e</h1>
                <p class="text-xs text-gray-500 mt-1">Consulta avulsa por chave de acesso. Resultado inline e histórico das últimas consultas.</p>
            </div>
            <a href="/app/validacao/notas" data-link class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-sm font-medium self-start">
                Verificar notas da base
            </a>
        </div>

        <div class="buscar-dfe-grid">
            <section class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Buscar DF-e</span>
                    <span class="text-[10px] text-gray-400 uppercase tracking-wide">Consulta avulsa por chave</span>
                </div>

                <div class="p-4 sm:p-6 space-y-5">
                    <div>
                        <p class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Tipo de documento</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2" role="radiogroup" aria-label="Tipo de documento fiscal">
                            <label class="documento-tipo-card border rounded p-3 cursor-pointer hover:bg-gray-100 transition-colors is-selected" data-document-type-card="nfe">
                                <input type="radio" name="documento_tipo" value="nfe" class="sr-only documento-tipo" checked>
                                <span class="flex items-start justify-between gap-2">
                                    <span class="block">
                                        <span class="block text-sm font-bold text-gray-900">NF-e</span>
                                        <span class="block text-[11px] text-gray-500 mt-0.5">Modelo 55</span>
                                    </span>
                                    <span class="documento-selecionado px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">Selecionado</span>
                                </span>
                            </label>

                            <label class="documento-tipo-card is-disabled border rounded p-3" data-document-type-card="cte" aria-disabled="true" title="Em breve — CT-e ainda não disponível">
                                <input type="radio" name="documento_tipo" value="cte" class="sr-only documento-tipo" disabled>
                                <span class="flex items-start justify-between gap-2">
                                    <span class="block">
                                        <span class="block text-sm font-bold text-gray-900">CT-e</span>
                                        <span class="block text-[11px] text-gray-500 mt-0.5">Transporte</span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #9ca3af">Em breve</span>
                                </span>
                            </label>

                            <label class="documento-tipo-card is-disabled border rounded p-3" data-document-type-card="nfse" aria-disabled="true" title="Em breve — NFS-e ainda não disponível">
                                <input type="radio" name="documento_tipo" value="nfse" class="sr-only documento-tipo" disabled>
                                <span class="flex items-start justify-between gap-2">
                                    <span class="block">
                                        <span class="block text-sm font-bold text-gray-900">NFS-e</span>
                                        <span class="block text-[11px] text-gray-500 mt-0.5">Serviços</span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #9ca3af">Em breve</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="nfe-cliente-id" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Associar a cliente <span class="text-gray-300 normal-case">(opcional)</span></label>
                        <select
                            id="nfe-cliente-id"
                            name="cliente_id"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                        >
                            <option value="">Não associar a cliente agora</option>
                            @foreach($clientes as $cliente)
                                @php
                                    $documento = preg_replace('/\D/', '', (string) ($cliente->documento ?? ''));
                                    $documentoLabel = strlen($documento) === 14
                                        ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $documento)
                                        : ($cliente->documento ?? null);
                                @endphp
                                <option value="{{ $cliente->id }}">
                                    {{ $cliente->razao_social }}@if($documentoLabel) ({{ $documentoLabel }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="nfe-chave" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Chave de acesso</label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input
                                type="text"
                                id="nfe-chave"
                                inputmode="numeric"
                                autocomplete="off"
                                maxlength="60"
                                placeholder="Cole a chave de 44 dígitos"
                                class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm font-mono focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                            >
                            <button
                                type="button"
                                id="btn-consultar-nfe"
                                class="px-4 py-2 rounded text-sm font-medium text-white disabled:opacity-40 disabled:cursor-not-allowed"
                                style="background-color: #374151"
                                disabled
                            >
                                Consultar documento
                            </button>
                        </div>
                        <div class="mt-2 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <p id="nfe-chave-feedback" class="text-[11px] text-gray-500">Cole a chave com 44 dígitos (pontos/espaços são removidos automaticamente).</p>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide"><span id="nfe-chave-count">0</span>/44 dígitos</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 py-2 px-3 bg-gray-50 border border-gray-200 rounded">
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <span class="font-semibold text-gray-900">Custo:</span>
                            <span>{{ $custoEstimadoCreditos }} créditos</span>
                            <span class="text-gray-300">·</span>
                            <span class="font-semibold text-gray-900">Saldo:</span>
                            <span id="saldo-atual-label">{{ number_format($saldoAtual, 0, ',', '.') }}</span>
                        </div>
                        <span id="saldo-badge" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $saldoSuficiente ? '#047857' : '#dc2626' }}">
                            {{ $saldoSuficiente ? 'Saldo suficiente' : 'Saldo insuficiente' }}
                        </span>
                    </div>

                    <div id="bloco-progresso" class="hidden border border-gray-200 rounded p-4 bg-gray-50/50">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Consultando</p>
                            <p id="progresso-percent" class="text-[10px] text-gray-500 font-mono">0%</p>
                        </div>
                        <div class="progress-track">
                            <div id="progresso-bar" class="progress-fill"></div>
                        </div>
                        <p id="progresso-etapa" class="text-xs text-gray-600 mt-2">Iniciando consulta...</p>
                    </div>

                    <div id="bloco-erro" class="hidden border border-red-200 rounded p-4" style="background-color: #fef2f2">
                        <div class="flex items-start gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white self-start" style="background-color: #dc2626">Erro</span>
                            <div class="flex-1 min-w-0">
                                <p id="erro-titulo" class="text-sm font-semibold text-gray-900">Não foi possível consultar</p>
                                <p id="erro-mensagem" class="text-xs text-gray-700 mt-1">-</p>
                                <p id="erro-refund" class="hidden text-[11px] text-gray-500 mt-2">Créditos estornados.</p>
                            </div>
                        </div>
                    </div>

                    <div id="bloco-resultado" class="hidden border border-gray-200 rounded overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resultado da consulta</span>
                            <span id="resultado-status-badge" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white self-start sm:self-auto" style="background-color: #374151">-</span>
                        </div>

                        <div class="p-4 space-y-3">
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                                <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Tipo</p>
                                    <p id="resultado-tipo" class="text-sm font-bold text-gray-900 mt-1">-</p>
                                </div>
                                <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Situação</p>
                                    <p id="resultado-situacao" class="text-sm font-bold text-gray-900 mt-1">-</p>
                                </div>
                                <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Valor total</p>
                                    <p id="resultado-valor" class="text-sm font-bold text-gray-900 font-mono mt-1">-</p>
                                </div>
                                <div class="border border-gray-200 rounded p-3 bg-gray-50/60">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Emissão</p>
                                    <p id="resultado-emissao" class="text-sm font-bold text-gray-900 mt-1">-</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                <div class="border border-gray-200 rounded p-3">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Emitente</p>
                                    <p id="resultado-emitente" class="text-sm text-gray-900 mt-1">-</p>
                                </div>
                                <div class="border border-gray-200 rounded p-3">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Destinatário / Tomador</p>
                                    <p id="resultado-destinatario" class="text-sm text-gray-900 mt-1">-</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-[1fr,240px] gap-2">
                                <div class="border border-gray-200 rounded p-3">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Chave consultada</p>
                                    <p id="resultado-chave" class="text-xs text-gray-900 font-mono break-all mt-1">-</p>
                                </div>
                                <div class="border border-gray-200 rounded p-3">
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Cliente associado</p>
                                    <p id="resultado-cliente" class="text-sm text-gray-900 mt-1">-</p>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2 pt-1">
                                <a id="btn-resultado-detalhe" href="#" class="px-4 py-2 rounded text-sm font-medium text-white text-center" style="background-color: #374151">Ver detalhe da nota</a>
                                <button type="button" id="btn-resultado-reconsultar" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-sm font-medium">Consultar novamente</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="bg-white rounded border border-gray-300 overflow-hidden lg:sticky lg:top-4">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Últimas consultas</span>
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $ultimasConsultasDfe->count() }}</span>
                </div>

                @if($ultimasConsultasDfe->isEmpty())
                    <div class="px-4 py-6 text-center">
                        <p class="text-sm font-semibold text-gray-900">Nenhuma consulta ainda</p>
                        <p class="text-xs text-gray-500 mt-1">As consultas avulsas salvas pelo n8n aparecerão aqui.</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($ultimasConsultasDfe as $notaHistorico)
                            @php
                                $payload = is_array($notaHistorico->payload ?? null) ? $notaHistorico->payload : [];
                                $situacao = strtoupper((string) (data_get($payload, 'nfe.situacao')
                                    ?? data_get($notaHistorico->validacao ?? [], 'status')
                                    ?? 'SALVA'));
                                $badgeCor = $badgeCoresSituacao[$situacao] ?? '#374151';
                                $tipoDocumento = strtoupper((string) ($notaHistorico->tipo_documento ?: 'NFE'));
                                $clienteHistorico = $notaHistorico->cliente?->razao_social ?: 'Sem cliente';
                                $atualizadoEm = $notaHistorico->updated_at ?: $notaHistorico->created_at;
                                $chaveAbrev = $notaHistorico->nfe_id
                                    ? substr($notaHistorico->nfe_id, 0, 6) . '…' . substr($notaHistorico->nfe_id, -4)
                                    : '';
                            @endphp
                            <li>
                                <a href="/app/validacao/nota/{{ $notaHistorico->id }}" data-link class="block px-4 py-3 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">{{ $tipoDocumento }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $badgeCor }}">{{ $situacao }}</span>
                                        @if($notaHistorico->numero_nota)
                                            <span class="text-xs font-semibold text-gray-900">Nº {{ $notaHistorico->numero_nota }}</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-gray-700 truncate">{{ $clienteHistorico }}</p>
                                    @if($chaveAbrev)
                                        <p class="text-[10px] text-gray-400 font-mono mt-0.5">{{ $chaveAbrev }}</p>
                                    @endif
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $atualizadoEm ? $atualizadoEm->format('d/m/Y H:i') : '' }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="px-4 py-2 border-t border-gray-200 bg-gray-50">
                        <a href="/app/validacao/notas?ordem=consulta" data-link class="text-[11px] text-gray-600 hover:text-gray-900 hover:underline">Ver todas →</a>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</div>

<script>
    window.BUSCAR_NFE_CONFIG = {
        custo: {{ $custoEstimadoCreditos }},
        endpoints: {
            consultar: '{{ route('app.clearance.buscar-nfe.consultar') }}',
            resultado: '{{ url('/app/validacao/buscar-nfe/resultado') }}',
            sse: '{{ url('/app/consulta/progresso/stream') }}',
        },
        cores: @json($badgeCoresSituacao),
    };
</script>
<script src="{{ asset('js/clearance-buscar-nfe.js') }}?v={{ @filemtime(public_path('js/clearance-buscar-nfe.js')) ?: time() }}" defer></script>
