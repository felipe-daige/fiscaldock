@php
    $saldoAtual = (int) ($saldoAtual ?? 0);
    $custoEstimadoCreditos = (int) ($custoEstimadoCreditos ?? 14);
    $fornecedorMvp = $fornecedorMvp ?? 'InfoSimples';
    $clientes = $clientes ?? collect();
    $ultimasConsultasDfe = $ultimasConsultasDfe ?? collect();
    $saldoSuficiente = $saldoAtual >= $custoEstimadoCreditos;
@endphp

<style>
    .buscar-dfe-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 1.5rem;
        align-items: stretch;
    }

    .buscar-dfe-main-card {
        height: 100%;
    }

    .buscar-dfe-side {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        height: 100%;
    }

    @media (max-width: 767px) {
        .buscar-dfe-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    .documento-tipo-card {
        border-color: #d1d5db;
        background-color: #f9fafb;
    }

    .documento-tipo-card.is-selected {
        border-color: #1f2937;
        background-color: #f3f4f6;
        box-shadow: inset 0 0 0 1px #1f2937;
    }

    .documento-tipo-card .documento-selecionado {
        display: none;
    }

    .documento-tipo-card.is-selected .documento-selecionado {
        display: inline-flex;
    }
</style>

<div class="min-h-screen bg-gray-100" id="buscar-nfe-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Buscar DF-e</h1>
                <p class="text-xs text-gray-500 mt-1">Consulta avulsa por chave de acesso para validar NF-e, CT-e ou NFS-e antes de importar ou revisar.</p>
            </div>
            <a href="/app/validacao/notas" data-link class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-sm font-medium self-start">
                Verificar notas da base
            </a>
        </div>

        <div class="space-y-4 sm:space-y-6">
            <section class="bg-white rounded border border-gray-300 p-4 border-l-4 border-l-blue-500">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Como usar</p>
                <div class="mt-3 space-y-3 text-sm text-gray-700">
                    <p>Use esta busca para validar um documento fiscal que ainda não foi importado por XML ou EFD.</p>
                    <p>Selecione o tipo de documento, associe um cliente se necessário e informe a chave de acesso para consultar e salvar os dados retornados.</p>
                </div>
            </section>

            <div class="buscar-dfe-grid">
                <section class="buscar-dfe-main-card bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Consulta avulsa</span>
                    </div>

                    <div class="p-4 sm:p-6">
                        <div class="mb-5">
                            <p class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Tipo de documento</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2" role="radiogroup" aria-label="Tipo de documento fiscal">
                                <label class="documento-tipo-card border rounded p-3 cursor-pointer hover:bg-gray-100 transition-colors" data-document-type-card="nfe">
                                    <input type="radio" name="documento_tipo" value="nfe" class="sr-only documento-tipo" checked>
                                    <span class="flex items-start justify-between gap-2">
                                        <span class="block">
                                            <span class="block text-sm font-bold text-gray-900">NF-e</span>
                                            <span class="block text-[11px] text-gray-500 mt-0.5">Modelo 55</span>
                                        </span>
                                        <span class="documento-selecionado px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">Selecionado</span>
                                    </span>
                                </label>

                                <label class="documento-tipo-card border rounded p-3 cursor-pointer hover:bg-gray-100 transition-colors" data-document-type-card="cte">
                                    <input type="radio" name="documento_tipo" value="cte" class="sr-only documento-tipo">
                                    <span class="flex items-start justify-between gap-2">
                                        <span class="block">
                                            <span class="block text-sm font-bold text-gray-900">CT-e</span>
                                            <span class="block text-[11px] text-gray-500 mt-0.5">Transporte</span>
                                        </span>
                                        <span class="documento-selecionado px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">Selecionado</span>
                                    </span>
                                </label>

                                <label class="documento-tipo-card border rounded p-3 cursor-pointer hover:bg-gray-100 transition-colors" data-document-type-card="nfse">
                                    <input type="radio" name="documento_tipo" value="nfse" class="sr-only documento-tipo">
                                    <span class="flex items-start justify-between gap-2">
                                        <span class="block">
                                            <span class="block text-sm font-bold text-gray-900">NFS-e</span>
                                            <span class="block text-[11px] text-gray-500 mt-0.5">Serviços</span>
                                        </span>
                                        <span class="documento-selecionado px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">Selecionado</span>
                                    </span>
                                </label>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-2">Selecione o tipo de documento fiscal que deseja consultar por chave de acesso.</p>
                        </div>

                        <div class="mb-5">
                            <label for="nfe-cliente-id" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Associar a cliente</label>
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
                            <p class="text-[11px] text-gray-500 mt-2">Opcional. Se selecionado, a nota será salva vinculada a este cliente quando a integração real estiver ativa.</p>
                        </div>

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
                            <p id="nfe-chave-feedback" class="text-[11px] text-gray-500">Selecione o tipo e informe uma chave com 44 dígitos numéricos.</p>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide"><span id="nfe-chave-count">0</span>/44 dígitos</p>
                        </div>
                    </div>
                </section>

                <div class="buscar-dfe-side">
                    <section class="bg-white rounded border border-gray-300 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Custo estimado</span>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div class="p-4">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Custo base</p>
                                <p class="text-lg font-bold text-gray-900 mt-1">{{ $custoEstimadoCreditos }} créditos</p>
                                <p class="text-[11px] text-gray-500 mt-1">Valor informativo do produto Clearance. A cobrança real será aplicada quando a integração InfoSimples estiver ativa.</p>
                            </div>
                            <div class="p-4">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Seu saldo</p>
                                <p class="text-lg font-bold text-gray-900 mt-1">{{ number_format($saldoAtual, 0, ',', '.') }} créditos</p>
                                <p class="mt-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $saldoSuficiente ? '#047857' : '#d97706' }}">
                                        {{ $saldoSuficiente ? 'Saldo suficiente' : 'Saldo baixo' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="bg-white rounded border border-gray-300 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Estados previstos</span>
                        </div>
                        <div class="p-4 flex flex-wrap gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #9ca3af">Pendente</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">Consultando</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #047857">Autorizada</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #d97706">Divergente</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #dc2626">Cancelada</span>
                        </div>
                    </section>
                </div>
            </div>

            <section class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Últimas consultas de documentos fiscais</span>
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $ultimasConsultasDfe->count() }} registro(s)</span>
                </div>

                <div id="historico-dfe-vazio" class="{{ $ultimasConsultasDfe->isEmpty() ? '' : 'hidden' }} px-4 py-6 text-center">
                    <p class="text-sm font-semibold text-gray-900">Nenhuma consulta salva ainda</p>
                    <p class="text-xs text-gray-500 mt-1">As consultas salvas pelo n8n aparecerão aqui. Consultas simuladas nesta tela aparecem como prévia.</p>
                </div>

                <div id="historico-dfe-lista" class="{{ $ultimasConsultasDfe->isEmpty() ? 'hidden' : '' }} divide-y divide-gray-100">
                    @foreach($ultimasConsultasDfe as $notaHistorico)
                        @php
                            $payload = is_array($notaHistorico->payload ?? null) ? $notaHistorico->payload : [];
                            $situacao = data_get($payload, 'nfe.situacao')
                                ?? data_get($notaHistorico->validacao ?? [], 'status')
                                ?? 'Salva';
                            $tipoDocumento = strtoupper((string) ($notaHistorico->tipo_documento ?: 'NFE'));
                            $clienteHistorico = $notaHistorico->cliente?->razao_social ?: 'Sem cliente';
                            $atualizadoEm = $notaHistorico->updated_at ?: $notaHistorico->created_at;
                        @endphp
                        <div class="px-4 py-3 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">{{ $tipoDocumento }}</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        @if($notaHistorico->numero_nota)
                                            Nº {{ $notaHistorico->numero_nota }}@if($notaHistorico->serie) / Série {{ $notaHistorico->serie }}@endif
                                        @else
                                            Documento fiscal
                                        @endif
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #047857">{{ $situacao }}</span>
                                </div>
                                <p class="text-xs text-gray-500 font-mono break-all mt-1">{{ $notaHistorico->nfe_id ?: 'Chave não informada' }}</p>
                                <p class="text-[11px] text-gray-500 mt-1">{{ $clienteHistorico }} · {{ $atualizadoEm ? $atualizadoEm->format('d/m/Y H:i') : 'Sem data' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="/app/validacao/nota/{{ $notaHistorico->id }}" data-link class="text-xs text-gray-600 hover:text-gray-900 hover:underline">Detalhes</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="resultado-consulta-dfe" class="hidden bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resultado da consulta</span>
                    <span id="resultado-status-badge" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white self-start sm:self-auto" style="background-color: #374151">Preparado</span>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
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

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mt-3">
                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Emitente</p>
                            <p id="resultado-emitente" class="text-sm text-gray-900 mt-1">-</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Destinatário / Tomador</p>
                            <p id="resultado-destinatario" class="text-sm text-gray-900 mt-1">-</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-[1fr,320px] gap-3 mt-3">
                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Chave consultada</p>
                            <p id="resultado-chave" class="text-xs text-gray-900 font-mono break-all mt-1">-</p>
                        </div>
                        <div class="border border-gray-200 rounded p-3">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Cliente associado</p>
                            <p id="resultado-cliente" class="text-sm text-gray-900 mt-1">-</p>
                        </div>
                    </div>

                    <div class="mt-3 border border-gray-200 rounded p-3 border-l-4" style="border-left-color: #047857">
                        <p class="text-sm font-semibold text-gray-900">Persistência prevista</p>
                        <p id="resultado-persistencia" class="text-sm text-gray-600 mt-1">A consulta será salva na base FiscalDock quando o backend e o n8n estiverem conectados.</p>
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row gap-2">
                        <button type="button" id="btn-resultado-detalhe" class="px-4 py-2 bg-white border border-gray-300 text-gray-400 rounded text-sm font-medium cursor-not-allowed" disabled>Ver detalhe da nota</button>
                        <button type="button" id="btn-resultado-reconsultar" class="px-4 py-2 bg-white border border-gray-300 text-gray-400 rounded text-sm font-medium cursor-not-allowed" disabled>Consultar novamente</button>
                        <button type="button" id="btn-resultado-validar" class="px-4 py-2 bg-white border border-gray-300 text-gray-400 rounded text-sm font-medium cursor-not-allowed" disabled>Validar no clearance</button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<script src="{{ asset('js/clearance-buscar-nfe.js') }}?v={{ @filemtime(public_path('js/clearance-buscar-nfe.js')) ?: time() }}" defer></script>
