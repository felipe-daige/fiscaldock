{{-- Monitoramento - Lista de Participantes Importados --}}
@php
    $currentListUrl = $currentListUrl ?? request()->getRequestUri();
    $indicadoresParticipantes = [
        ['label' => 'Total', 'valor' => number_format($totalParticipantes ?? 0, 0, ',', '.'), 'sub' => 'Participantes importados'],
        ['label' => 'Situação Ativa', 'valor' => number_format($totalAtiva ?? 0, 0, ',', '.'), 'sub' => 'Cadastro regular'],
        ['label' => 'Irregular', 'valor' => number_format($totalIrregular ?? 0, 0, ',', '.'), 'sub' => 'Requer atenção'],
        ['label' => 'Sem Consulta', 'valor' => number_format($totalSemConsulta ?? 0, 0, ',', '.'), 'sub' => 'Aguardando consulta'],
    ];
@endphp
<x-cadastro-lista-layout
    container-id="monitoramento-participantes-importados-container"
    titulo="Participantes"
    subtitulo="Base operacional de participantes vinculados às importações e consultas."
>
    <x-slot:acoes>
        <button type="button" id="btn-dossie-lote-header" class="inline-flex min-w-0 items-center justify-center gap-1.5 rounded border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 sm:gap-2 sm:px-4 sm:text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="truncate sm:hidden">Dossiê</span>
            <span class="hidden sm:inline">Dossiê PDF</span>
        </button>
    </x-slot:acoes>
    <x-slot:principal>
        <a
            href="/app/participante/novo"
            class="inline-flex min-w-0 items-center justify-center gap-1.5 rounded bg-gray-800 px-3 py-2 text-xs font-medium text-white transition hover:bg-gray-700 sm:gap-2 sm:px-4 sm:text-sm"
            data-link
        >
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span class="truncate sm:hidden">Novo</span>
            <span class="hidden sm:inline">Novo Participante</span>
        </a>
    </x-slot:principal>
    <x-slot:resumo>
        <x-cockpit.indicadores :itens="$indicadoresParticipantes" />
    </x-slot:resumo>

        {{-- Filtros --}}
        <form id="form-filtros" method="GET" action="/app/participantes" class="bg-white rounded border border-gray-300 overflow-hidden" data-mobile-filters>
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros</span>
            </div>
            <div class="p-4">
            @php
                $avancadosPartKeys = ['tipo_documento', 'uf', 'monitorado', 'regime', 'relacao', 'origem', 'cliente', 'importacao'];
                $avancadosPartAtivos = collect($avancadosPartKeys)->filter(fn ($k) => ! empty($filtros[$k] ?? null))->count();
            @endphp

            {{-- Filtros básicos (sempre visíveis) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Buscar</label>
                    <div class="relative">
                        <input
                            type="text"
                            name="busca"
                            id="busca-participantes"
                            placeholder="Documento ou nome..."
                            value="{{ $filtros['busca'] ?? '' }}"
                            class="w-full border border-gray-300 rounded text-[13px] py-2.5 pl-10 pr-4 focus:ring-1 focus:ring-gray-400 focus:border-gray-400"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Status da consulta</label>
                    <select name="status_consulta" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">Qualquer status</option>
                        <option value="nunca" {{ ($filtros['status_consulta'] ?? '') === 'nunca' ? 'selected' : '' }}>Nunca consultado</option>
                        <option value="desatualizada" {{ ($filtros['status_consulta'] ?? '') === 'desatualizada' ? 'selected' : '' }}>Desatualizada (+30 dias)</option>
                        <option value="recente" {{ ($filtros['status_consulta'] ?? '') === 'recente' ? 'selected' : '' }}>Recente (até 30 dias)</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Regularidade (CND)</label>
                    <select name="regularidade" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">Todas</option>
                        <option value="regular" {{ ($filtros['regularidade'] ?? '') === 'regular' ? 'selected' : '' }}>Regular</option>
                        <option value="irregular" {{ ($filtros['regularidade'] ?? '') === 'irregular' ? 'selected' : '' }}>Irregular</option>
                        <option value="indeterminada" {{ ($filtros['regularidade'] ?? '') === 'indeterminada' ? 'selected' : '' }}>Indeterminada</option>
                        <option value="nao_consultado" {{ ($filtros['regularidade'] ?? '') === 'nao_consultado' ? 'selected' : '' }}>Não consultado</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Situação cadastral</label>
                    <select name="situacao" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">Todas</option>
                        <option value="ATIVA" {{ ($filtros['situacao'] ?? '') == 'ATIVA' ? 'selected' : '' }}>Ativa</option>
                        <option value="BAIXADA" {{ ($filtros['situacao'] ?? '') == 'BAIXADA' ? 'selected' : '' }}>Baixada</option>
                        <option value="SUSPENSA" {{ ($filtros['situacao'] ?? '') == 'SUSPENSA' ? 'selected' : '' }}>Suspensa</option>
                        <option value="INAPTA" {{ ($filtros['situacao'] ?? '') == 'INAPTA' ? 'selected' : '' }}>Inapta</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Ordenar por</label>
                    <select name="ordem" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="movimentacao" {{ ($filtros['ordem'] ?? 'movimentacao') === 'movimentacao' ? 'selected' : '' }}>Maior movimentação</option>
                        <option value="recentes" {{ ($filtros['ordem'] ?? '') === 'recentes' ? 'selected' : '' }}>Mais recentes</option>
                        <option value="nome" {{ ($filtros['ordem'] ?? '') === 'nome' ? 'selected' : '' }}>Nome (A–Z)</option>
                    </select>
                </div>
            </div>

            {{-- Toggle "Mais filtros" --}}
            <div class="mt-3">
                <button type="button" onclick="var a=document.getElementById('filtros-avancados-part'); a.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180');"
                    class="inline-flex items-center gap-1.5 text-[13px] text-gray-600 hover:text-gray-900 font-medium">
                    <svg class="w-3.5 h-3.5 transition-transform {{ $avancadosPartAtivos > 0 ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    Mais filtros
                    @if($avancadosPartAtivos > 0)
                        <span class="text-[10px] text-white rounded-full px-1.5 py-0.5" style="background-color:#374151;">{{ $avancadosPartAtivos }}</span>
                    @endif
                </button>
            </div>

            {{-- Filtros avançados (colapsável) --}}
            <div id="filtros-avancados-part" class="{{ $avancadosPartAtivos > 0 ? '' : 'hidden' }} grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mt-3 pt-4 border-t border-gray-200">
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Tipo de pessoa</label>
                    <select name="tipo_documento" id="filtro-tipo-documento" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">Todos</option>
                        <option value="CNPJ" {{ ($filtros['tipo_documento'] ?? '') === 'CNPJ' ? 'selected' : '' }}>Pessoa Jurídica (CNPJ)</option>
                        <option value="CPF" {{ ($filtros['tipo_documento'] ?? '') === 'CPF' ? 'selected' : '' }}>Pessoa Física (CPF)</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">UF</label>
                    <select name="uf" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">Todas</option>
                        @foreach($ufs ?? [] as $ufOpt)
                            <option value="{{ $ufOpt }}" {{ ($filtros['uf'] ?? '') == $ufOpt ? 'selected' : '' }}>{{ $ufOpt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Monitoramento</label>
                    <select name="monitorado" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">Todos</option>
                        <option value="sim" {{ ($filtros['monitorado'] ?? '') === 'sim' ? 'selected' : '' }}>Monitorado</option>
                        <option value="nao" {{ ($filtros['monitorado'] ?? '') === 'nao' ? 'selected' : '' }}>Não monitorado</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Regime</label>
                    <select name="regime" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">Todos</option>
                        <option value="simples nacional" {{ ($filtros['regime'] ?? '') == 'simples nacional' ? 'selected' : '' }}>Simples Nacional</option>
                        <option value="lucro presumido" {{ ($filtros['regime'] ?? '') == 'lucro presumido' ? 'selected' : '' }}>Lucro Presumido</option>
                        <option value="lucro real" {{ ($filtros['regime'] ?? '') == 'lucro real' ? 'selected' : '' }}>Lucro Real</option>
                        <option value="mei" {{ ($filtros['regime'] ?? '') == 'mei' ? 'selected' : '' }}>MEI</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Relação fiscal</label>
                    <select name="relacao" id="filtro-relacao" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">Todas</option>
                        <option value="fornecedor" {{ ($filtros['relacao'] ?? '') == 'fornecedor' ? 'selected' : '' }}>Fornecedor</option>
                        <option value="cliente" {{ ($filtros['relacao'] ?? '') == 'cliente' ? 'selected' : '' }}>Cliente</option>
                        <option value="ambos" {{ ($filtros['relacao'] ?? '') == 'ambos' ? 'selected' : '' }}>Fornecedor e cliente</option>
                        <option value="sem_movimentacao" {{ ($filtros['relacao'] ?? '') == 'sem_movimentacao' ? 'selected' : '' }}>Sem movimentação</option>
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Origem</label>
                    <select name="origem" id="filtro-origem" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">Todas as origens</option>
                        @foreach($origens ?? [] as $oriValor => $oriLabel)
                            <option value="{{ $oriValor }}" {{ ($filtros['origem'] ?? '') == $oriValor ? 'selected' : '' }}>
                                {{ $oriLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Cliente</label>
                    <select name="cliente" id="filtro-cliente" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">Todos os clientes</option>
                        @foreach($clientes ?? [] as $cli)
                            <option value="{{ $cli->id }}" {{ ($filtros['cliente'] ?? '') == $cli->id ? 'selected' : '' }}>
                                {{ $cli->razao_social }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Importação</label>
                    <select name="importacao" id="filtro-importacao" class="w-full border border-gray-300 rounded text-[13px] py-2.5 px-3 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <option value="">Todas as importações</option>
                        @foreach($importacoes ?? [] as $imp)
                            <option value="{{ $imp->id }}" {{ ($filtros['importacao'] ?? '') == $imp->id ? 'selected' : '' }}>
                                {{ $imp->filename ?? 'Importacao #' . $imp->id }} - {{ $imp->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4 pt-4 border-t border-gray-200">
                <div class="mobile-filter-actions flex items-center gap-2">
                    <button type="submit" class="bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium px-4 py-2">
                        Filtrar
                    </button>
                    <a href="/app/participantes" class="bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded text-sm font-medium px-4 py-2" data-link>
                        Limpar
                    </a>
                </div>
            </div>
            </div>
        </form>

        {{-- Barra de selecao global --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                <button type="button" id="btn-selecionar-todos-filtro" class="text-gray-700 hover:text-gray-900 font-medium underline">
                    Selecionar todos (<span id="total-filtrado">{{ $participantes->total() }}</span>)
                </button>
                <button type="button" id="btn-limpar-selecao-geral" class="text-gray-500 hover:text-gray-700 hidden">
                    Limpar selecao
                </button>
            </div>
            <span id="selecao-persistente-info" class="text-xs text-gray-500 hidden sm:text-right">
                <span id="total-selecionados-persistente">0</span> selecionados (todas as paginas)
            </span>
        </div>

        {{-- Acoes em lote (aparece quando ha selecao) --}}
        <div id="acoes-lote" class="hidden bg-white border border-gray-300 rounded p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded text-white text-sm font-bold" id="count-selecionados" style="background-color: #374151">0</span>
                    <span class="text-sm font-medium text-gray-900"><span id="participantes-selecionados-label">participantes selecionados</span></span>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:flex">
                    {{-- Botão único Exportar → modal de formato → overlay. Escopo = participantes
                         SELECIONADOS (POST ids[] via window.exportParticipantesIds). --}}
                    <x-export-menu id="modal-exportar-participantes" titulo="Exportar participantes"
                                   descricao="O arquivo cobre os participantes selecionados na grade."
                                   overlay="download-overlay-participantes">
                        <x-export-grupo label="Documento" />
                        <x-export-option format="pdf" modal-id="modal-exportar-participantes"
                                         overlay="download-overlay-participantes"
                                         post-path="/app/participantes/exportar-pdf" ids-fn="exportParticipantesIds"
                                         vazio-msg="Selecione ao menos um participante para exportar."
                                         descricao="Panorama da listagem em uma folha." />
                        <x-export-grupo label="Planilhas" />
                        <x-export-option format="xlsx" modal-id="modal-exportar-participantes"
                                         overlay="download-overlay-participantes"
                                         post-path="/app/participantes/exportar-xlsx" ids-fn="exportParticipantesIds"
                                         vazio-msg="Selecione ao menos um participante para exportar."
                                         descricao="Uma linha por participante: papel, notas, movimentado e regularidade." />
                        <x-export-option format="csv" modal-id="modal-exportar-participantes"
                                         overlay="download-overlay-participantes"
                                         post-path="/app/participantes/exportar-csv" ids-fn="exportParticipantesIds"
                                         vazio-msg="Selecione ao menos um participante para exportar."
                                         descricao="Mesmas colunas do XLSX, uma linha por participante." />
                    </x-export-menu>
                    <button type="button" id="btn-dossie-lote" class="auth-control inline-flex items-center justify-center gap-2 rounded border border-gray-300 bg-white text-gray-700 transition hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Dossiê PDF
                    </button>
                    <button type="button" id="btn-monitorar-selecionados" class="auth-control inline-flex items-center justify-center gap-2 rounded bg-gray-800 text-white transition hover:bg-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Consultar
                    </button>
                    <button type="button" id="btn-bulk-delete" class="auth-control inline-flex items-center justify-center gap-2 rounded border text-white transition hover:opacity-90" style="background-color: #b91c1c; border-color: #b91c1c">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Deletar
                    </button>
                    <button type="button" id="btn-limpar-selecao" class="px-3 py-2 rounded border border-gray-300 bg-white text-gray-700 text-sm font-medium transition hover:bg-gray-50 sm:px-4">
                        Limpar
                    </button>
                </div>
            </div>
        </div>

        {{-- Lista de Participantes --}}
        <div id="participantes-list-view" class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="overflow-x-auto">
                {{-- min-w: sem ele o table-fixed espreme a coluna de nome a ~0 no mobile em vez
                     de acionar o scroll horizontal do overflow-x-auto --}}
                <table class="w-full min-w-[960px] table-fixed">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th class="w-10 px-3 py-2.5 text-left bg-gray-50">
                                <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-gray-300 text-gray-700 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                            </th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Participante</th>
                            <th class="w-[160px] px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Movimentação</th>
                            <th class="hidden lg:table-cell w-[140px] px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Regime</th>
                            <th class="w-[280px] px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Situação / Certidões</th>
                            <th class="w-[140px] px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Origem</th>
                            <th class="w-20 px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="participantes-tbody">
                        @forelse($participantes ?? [] as $part)
                            @php
                                $isCpf = $part->is_cpf;
                                $participanteUrl = '/app/participante/'.$part->id.'?return_to='.urlencode($currentListUrl);
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors cursor-pointer" data-participante-id="{{ $part->id }}" data-href="{{ $participanteUrl }}">
                                <td class="px-3 py-3">
                                    <input
                                        type="checkbox"
                                        class="checkbox-participante w-4 h-4 rounded border-gray-300 text-gray-700 focus:ring-1 focus:ring-gray-400 focus:border-gray-400 disabled:cursor-not-allowed disabled:border-gray-200 disabled:bg-gray-100"
                                        value="{{ $part->id }}"
                                        data-bloqueado="{{ $isCpf ? '1' : '0' }}"
                                        {{ $isCpf ? 'disabled' : '' }}
                                    >
                                </td>
                                <td class="px-3 py-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div class="truncate text-sm text-gray-900" title="{{ $part->razao_social }}">{{ $part->razao_social ?? '-' }}</div>
                                            @if($isCpf)
                                                <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white flex-shrink-0" style="background-color: #9ca3af">CPF</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] font-mono text-gray-500 mt-1">{{ $part->cnpj_formatado }}</div>
                                        <div class="mt-1 flex items-center gap-2 flex-wrap">
                                            @if($part->papel_badge_label)
                                                <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $part->papel_badge_hex }}" title="Relação fiscal segundo suas notas EFD">
                                                    {{ $part->papel_badge_label }}
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $part->consulta_status_hex }}">
                                                {{ $part->consulta_status_label }}
                                            </span>
                                            @if($part->assinatura_label)
                                                <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $part->assinatura_hex }}">
                                                    {{ $part->assinatura_label }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-1">
                                            {{ $part->consulta_status_meta }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-1 truncate">
                                            @if($part->cliente)
                                                Cliente: {{ $part->cliente->razao_social ?? '-' }}
                                            @else
                                                Sem vínculo com cliente
                                            @endif
                                        </div>
                                    </div>
                                    @if($isCpf)
                                        <div class="text-[11px] text-gray-500 mt-1">Cadastro bloqueado para seleção em lote</div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right align-middle">
                                    @if(($part->mov_qtd ?? 0) > 0)
                                        <div class="text-sm font-semibold text-gray-900 whitespace-nowrap" title="Valor total movimentado nas notas EFD (entradas + saídas, todas as empresas)">
                                            R$&nbsp;{{ number_format($part->mov_valor, 2, ',', '.') }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-0.5 whitespace-nowrap">
                                            {{ number_format($part->mov_qtd, 0, ',', '.') }} {{ $part->mov_qtd === 1 ? 'nota' : 'notas' }}
                                        </div>
                                    @else
                                        <span class="text-[11px] text-gray-400">Sem movimentação</span>
                                    @endif
                                </td>
                                <td class="hidden lg:table-cell px-3 py-3 text-center text-sm text-gray-700">
                                    <x-regime-tributario :valor="$part->regime_tributario" :nota="$part->regime_tributario_nota" />
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex flex-col items-center gap-1" title="{{ $part->situacao_cadastral ?? '' }}">
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            @if($part->situacao_cadastral === 'ATIVA')
                                                <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white whitespace-nowrap" style="background-color: #047857">
                                                    Ativa
                                                </span>
                                            @elseif($part->situacao_cadastral)
                                                <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white whitespace-nowrap" style="background-color: #b45309">
                                                    {{ $part->situacao_cadastral }}
                                                </span>
                                            @endif
                                            @forelse($part->certidoes_badges ?? [] as $b)
                                                <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $b['hex'] }}" title="{{ $b['titulo'] }}: {{ $b['label'] }}">
                                                    {{ $b['curto'] }}
                                                </span>
                                            @empty
                                                <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #9ca3af">
                                                    Sem certidões consultadas
                                                </span>
                                            @endforelse
                                        </div>
                                        @if($part->cnd_federal_status_label !== 'Não consultada')
                                            <div class="text-[11px] text-gray-500 leading-tight text-center">
                                                CND Federal: {{ $part->cnd_federal_status_label }} — {{ $part->cnd_federal_meta }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="min-w-0 flex flex-col items-center px-1">
                                        <span class="inline-flex max-w-full items-center justify-center whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $part->origem_hex }}">
                                            {{ $part->origem_label }}
                                        </span>
                                        <div class="text-[11px] text-gray-500 mt-1 leading-tight text-center" title="Base: {{ $part->created_at?->format('d/m/Y') ?? '-' }}">
                                            Base: {{ $part->created_at?->format('d/m/Y') ?? '-' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-right align-middle">
                                    <x-acoes-menu trigger="kebab">
                                        <x-acoes-item href="/app/participante/{{ $part->id }}?return_to={{ urlencode(request()->fullUrl()) }}" data-link>Ver detalhes</x-acoes-item>
                                        <x-acoes-item href="/app/participante/{{ $part->id }}/editar" data-link>Editar</x-acoes-item>
                                        <x-acoes-item variant="danger" data-excluir-participante="{{ $part->id }}" data-cnpj="{{ $part->cnpj_formatado }}" data-nome="{{ $part->razao_social }}">Excluir</x-acoes-item>
                                    </x-acoes-menu>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2 uppercase tracking-wide">Nenhum participante encontrado</h3>
                                        <p class="text-sm text-gray-600 mb-4">
                                            @if(($filtros['importacao'] ?? null) || ($filtros['cliente'] ?? null) || ($filtros['origem'] ?? null) || ($filtros['busca'] ?? null) || ($filtros['regime'] ?? null) || ($filtros['situacao'] ?? null) || ($filtros['uf'] ?? null) || ($filtros['tipo_documento'] ?? null))
                                                Nenhum participante corresponde aos filtros aplicados.
                                            @else
                                                Importe participantes de um arquivo SPED para comecar.
                                            @endif
                                        </p>
                                        <a
                                            href="/app/importacao/efd"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded bg-gray-800 text-white text-sm font-medium transition hover:bg-gray-700"
                                            data-link
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            Importar SPED
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(isset($participantes) && $participantes->hasPages())
                <div class="border-t border-gray-300 px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[10px] text-gray-500 uppercase tracking-wide">
                            Mostrando {{ $participantes->firstItem() }}-{{ $participantes->lastItem() }} de {{ $participantes->total() }}
                        </p>
                        <div>
                            {{ $participantes->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
</x-cadastro-lista-layout>

{{-- Modais (fora do container para overlay correto) --}}

{{-- Modal de confirmacao de exclusao --}}
<div id="modal-excluir-participante" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" id="modal-excluir-overlay"></div>
        <div class="relative bg-white rounded border border-gray-300 max-w-md w-full p-6 z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded border border-gray-300 flex items-center justify-center">
                    <svg class="w-5 h-5" style="color: #b91c1c" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Excluir participante?</h3>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-700 mb-2">
                    <span class="font-medium" id="modal-excluir-cnpj"></span> — <span id="modal-excluir-nome"></span>
                </p>
                <p class="text-sm text-gray-500">
                    Todo o histórico associado será removido permanentemente, incluindo assinaturas, consultas e scores. As notas fiscais onde este participante aparece serão mantidas.
                </p>
                <p class="text-sm text-red-600 font-medium mt-2">
                    Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" id="btn-cancelar-exclusao" class="px-4 py-2 rounded border border-gray-300 bg-white text-gray-700 text-sm font-medium transition hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="button" id="btn-confirmar-exclusao" class="px-4 py-2 rounded text-white text-sm font-medium transition hover:opacity-90" style="background-color: #b91c1c">
                    Excluir
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de confirmacao de exclusao em lote --}}
<div id="modal-bulk-delete" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" id="modal-bulk-delete-overlay"></div>
        <div class="relative bg-white rounded border border-gray-300 max-w-md w-full p-6 z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded border border-gray-300 flex items-center justify-center">
                    <svg class="w-5 h-5" style="color: #b91c1c" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Excluir <span id="modal-bulk-delete-count">0</span> <span id="modal-bulk-delete-label">participantes</span>?</h3>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-500 mb-2">
                    Todo o histórico associado será removido permanentemente, incluindo assinaturas, consultas e scores. As notas fiscais onde estes participantes aparecem serão mantidas.
                </p>
                <p class="text-sm text-red-600 font-medium">
                    Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" id="btn-cancelar-bulk-delete" class="px-4 py-2 rounded border border-gray-300 bg-white text-gray-700 text-sm font-medium transition hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="button" id="btn-confirmar-bulk-delete" class="px-4 py-2 rounded text-white text-sm font-medium transition hover:opacity-90" style="background-color: #b91c1c">
                    Excluir
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal de dossiê em lote (espelha o de /app/clientes) --}}
{{-- Overlay do download (spinner) — usado pelo modal Exportar (POST ids[] via iframe) --}}
<x-download-overlay id="download-overlay-participantes" texto="Gerando arquivo…" />

<div id="modal-dossie-lote" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" id="modal-dossie-lote-overlay"></div>
        <div class="relative bg-white rounded border border-gray-300 max-w-md w-full p-6 z-10">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Gerar dossiê (PDF)</h3>
            <p class="text-sm text-gray-500 mb-4">Um PDF único com o dossiê completo de cada participante (cadastro, movimentação EFD, certidões e score), ordenado por volume.</p>
            <label class="block text-[11px] text-gray-500 mb-1" for="dossie-lote-escopo">Participantes</label>
            <select id="dossie-lote-escopo" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded mb-4">
                <option value="selecionados" id="dossie-lote-opt-selecionados" class="hidden">Participantes selecionados</option>
                <option value="">Todos os participantes (filtros atuais)</option>
            </select>
            <div class="flex justify-end gap-3">
                <button type="button" id="btn-cancelar-dossie-lote" class="px-4 py-2 rounded border border-gray-300 bg-white text-gray-700 text-sm font-medium transition hover:bg-gray-50">Cancelar</button>
                <button type="button" id="btn-confirmar-dossie-lote" class="px-4 py-2 rounded bg-gray-800 text-white text-sm font-medium transition hover:bg-gray-700">Gerar PDF</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    var STORAGE_KEY = 'participantes_selecionados';

    function initMonitoramentoParticipantesImportados() {
        var container = document.getElementById('monitoramento-participantes-importados-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento Participantes Importados] Inicializando...');

        var selectAll = document.getElementById('select-all');
        var acoesLote = document.getElementById('acoes-lote');
        var countSelecionados = document.getElementById('count-selecionados');
        var btnLimparSelecao = document.getElementById('btn-limpar-selecao');
        var btnMonitorar = document.getElementById('btn-monitorar-selecionados');
        var btnBulkDelete = document.getElementById('btn-bulk-delete');
        var btnSelecionarTodosFiltro = document.getElementById('btn-selecionar-todos-filtro');
        var btnLimparSelecaoGeral = document.getElementById('btn-limpar-selecao-geral');
        var infoSelecaoPersistente = document.getElementById('selecao-persistente-info');
        var totalSelecionadosPersistente = document.getElementById('total-selecionados-persistente');

        // === Persistencia via sessionStorage ===
        function carregarSelecao() {
            try {
                var raw = sessionStorage.getItem(STORAGE_KEY);
                if (raw) return new Set(JSON.parse(raw).map(Number));
            } catch (e) {}
            return new Set();
        }

        function salvarSelecao(setIds) {
            try {
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(setIds)));
            } catch (e) {}
        }

        function limparSelecaoStorage() {
            try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) {}
        }

        var selectedIds = carregarSelecao();

        // Funcao para obter IDs selecionados (do Set persistente)
        function getIdsSelecionados() {
            return Array.from(selectedIds);
        }

        function checkboxBloqueado(cb) {
            return cb.disabled || cb.dataset.bloqueado === '1';
        }

        function getCheckboxes() {
            return Array.from(document.querySelectorAll('.checkbox-participante'));
        }

        function getIdsElegiveisDaPagina() {
            var ids = new Set();
            getCheckboxes().forEach(function(cb) {
                if (!checkboxBloqueado(cb)) {
                    ids.add(Number(cb.value));
                }
            });
            return ids;
        }

        function removerParticipanteDaTela(id) {
            document.querySelectorAll('[data-participante-id="' + id + '"]').forEach(function(el) {
                el.remove();
            });
        }

        // Funcao para atualizar contagem e visibilidade das acoes em lote
        function atualizarAcoesLote() {
            var count = selectedIds.size;

            if (countSelecionados) countSelecionados.textContent = count;
            var labelSelecionados = document.getElementById('participantes-selecionados-label');
            if (labelSelecionados) labelSelecionados.textContent = count === 1 ? 'participante selecionado' : 'participantes selecionados';

            if (acoesLote) {
                if (count > 0) {
                    acoesLote.classList.remove('hidden');
                } else {
                    acoesLote.classList.add('hidden');
                }
            }

            // Atualizar info de selecao persistente
            if (infoSelecaoPersistente && totalSelecionadosPersistente) {
                if (count > 0) {
                    totalSelecionadosPersistente.textContent = count;
                    infoSelecaoPersistente.classList.remove('hidden');
                } else {
                    infoSelecaoPersistente.classList.add('hidden');
                }
            }

            // Botao limpar geral
            if (btnLimparSelecaoGeral) {
                if (count > 0) {
                    btnLimparSelecaoGeral.classList.remove('hidden');
                } else {
                    btnLimparSelecaoGeral.classList.add('hidden');
                }
            }

            // Atualizar estado do checkbox "selecionar todos" da pagina
            var idsElegiveis = getIdsElegiveisDaPagina();
            var checkedCount = 0;
            idsElegiveis.forEach(function(id) {
                if (selectedIds.has(id)) checkedCount++;
            });
            if (selectAll) {
                selectAll.checked = checkedCount === idsElegiveis.size && checkedCount > 0;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < idsElegiveis.size;
                selectAll.disabled = idsElegiveis.size === 0;
            }

            salvarSelecao(selectedIds);
        }

        // Sincronizar checkboxes da pagina atual com o Set persistente
        function sincronizarCheckboxes() {
            getCheckboxes().forEach(function(cb) {
                var id = Number(cb.value);
                if (checkboxBloqueado(cb)) {
                    cb.checked = false;
                    selectedIds.delete(id);
                    return;
                }
                cb.checked = selectedIds.has(id);
            });
        }

        // Ao carregar: restaurar selecao dos checkboxes visiveis
        sincronizarCheckboxes();
        atualizarAcoesLote();

        // Event listener para checkbox "selecionar todos" (pagina atual)
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                getIdsElegiveisDaPagina().forEach(function(id) {
                    if (selectAll.checked) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                });
                sincronizarCheckboxes();
                atualizarAcoesLote();
            });
        }

        // Event listener para checkboxes individuais (tabela e cards compartilham IDs)
        container.addEventListener('change', function(e) {
            var cb = e.target.closest('.checkbox-participante');
            if (!cb) return;

            if (checkboxBloqueado(cb)) {
                cb.checked = false;
                selectedIds.delete(Number(cb.value));
                sincronizarCheckboxes();
                atualizarAcoesLote();
                return;
            }

            var id = Number(cb.value);
            if (cb.checked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }
            sincronizarCheckboxes();
            atualizarAcoesLote();
        });

        // === Botao "Selecionar todos (N)" - busca AJAX ===
        if (btnSelecionarTodosFiltro) {
            btnSelecionarTodosFiltro.addEventListener('click', async function() {
                btnSelecionarTodosFiltro.disabled = true;
                btnSelecionarTodosFiltro.textContent = 'Carregando...';

                try {
                    // Construir query string com filtros atuais
                    var params = new URLSearchParams();
                    var filtroImportacao = document.getElementById('filtro-importacao');
                    var filtroCliente = document.getElementById('filtro-cliente');
                    var filtroOrigem = document.getElementById('filtro-origem');
                    var filtroTipoDocumento = document.getElementById('filtro-tipo-documento');
                    var buscaInput = document.getElementById('busca-participantes');
                    var filtroRegime = formFiltros ? formFiltros.querySelector('select[name="regime"]') : null;
                    var filtroSituacao = formFiltros ? formFiltros.querySelector('select[name="situacao"]') : null;
                    var filtroUf = formFiltros ? formFiltros.querySelector('select[name="uf"]') : null;
                    var filtroRelacao = formFiltros ? formFiltros.querySelector('select[name="relacao"]') : null;
                    var filtroStatusConsulta = formFiltros ? formFiltros.querySelector('select[name="status_consulta"]') : null;
                    var filtroRegularidade = formFiltros ? formFiltros.querySelector('select[name="regularidade"]') : null;
                    var filtroMonitorado = formFiltros ? formFiltros.querySelector('select[name="monitorado"]') : null;

                    if (filtroImportacao && filtroImportacao.value) params.set('importacao', filtroImportacao.value);
                    if (filtroCliente && filtroCliente.value) params.set('cliente', filtroCliente.value);
                    if (filtroOrigem && filtroOrigem.value) params.set('origem', filtroOrigem.value);
                    if (filtroTipoDocumento && filtroTipoDocumento.value) params.set('tipo_documento', filtroTipoDocumento.value);
                    if (buscaInput && buscaInput.value) params.set('busca', buscaInput.value);
                    if (filtroRegime && filtroRegime.value) params.set('regime', filtroRegime.value);
                    if (filtroSituacao && filtroSituacao.value) params.set('situacao', filtroSituacao.value);
                    if (filtroUf && filtroUf.value) params.set('uf', filtroUf.value);
                    if (filtroRelacao && filtroRelacao.value) params.set('relacao', filtroRelacao.value);
                    if (filtroStatusConsulta && filtroStatusConsulta.value) params.set('status_consulta', filtroStatusConsulta.value);
                    if (filtroRegularidade && filtroRegularidade.value) params.set('regularidade', filtroRegularidade.value);
                    if (filtroMonitorado && filtroMonitorado.value) params.set('monitorado', filtroMonitorado.value);

                    var url = '/app/participantes/todos-ids' + (params.toString() ? '?' + params.toString() : '');
                    var res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    var data = await res.json();
                    if (!data.success) throw new Error('Erro ao buscar IDs');

                    // Adicionar todos ao Set
                    data.ids.forEach(function(id) { selectedIds.add(Number(id)); });
                    sincronizarCheckboxes();
                    atualizarAcoesLote();

                    window.showToast && window.showToast(data.total + ' participantes selecionados', 'success');
                } catch (err) {
                    console.error('[Participantes] Erro ao selecionar todos:', err);
                    window.showToast && window.showToast('Erro ao selecionar todos os participantes', 'error');
                } finally {
                    var totalEl = document.getElementById('total-filtrado');
                    var total = totalEl ? totalEl.textContent : '?';
                    btnSelecionarTodosFiltro.disabled = false;
                    btnSelecionarTodosFiltro.innerHTML = 'Selecionar todos (<span id="total-filtrado">' + total + '</span>)';
                }
            });
        }

        // === Botao limpar selecao geral ===
        if (btnLimparSelecaoGeral) {
            btnLimparSelecaoGeral.addEventListener('click', function() {
                selectedIds.clear();
                limparSelecaoStorage();
                sincronizarCheckboxes();
                if (selectAll) selectAll.checked = false;
                atualizarAcoesLote();
            });
        }

        // Botao limpar selecao (na barra de acoes)
        if (btnLimparSelecao) {
            btnLimparSelecao.addEventListener('click', function() {
                selectedIds.clear();
                limparSelecaoStorage();
                sincronizarCheckboxes();
                if (selectAll) selectAll.checked = false;
                atualizarAcoesLote();
            });
        }

        // Escopo dos exports (PDF/XLSX/CSV): os participantes selecionados na grade. O componente
        // export-option monta o POST ids[] num iframe oculto e cuida do overlay/cookie.
        window.exportParticipantesIds = function() {
            return getIdsSelecionados();
        };

        // Botao consultar selecionados
        if (btnMonitorar) {
            btnMonitorar.addEventListener('click', function() {
                var selecionados = getIdsSelecionados();
                if (selecionados.length === 0) {
                    alert('Selecione pelo menos um participante.');
                    return;
                }
                // Limpar selecao ao navegar
                limparSelecaoStorage();
                // Redirecionar para nova consulta com IDs pre-selecionados
                window.location.href = '/app/consulta/nova?participantes=' + selecionados.join(',');
            });
        }

        // === Bulk delete ===
        var modalBulkDelete = document.getElementById('modal-bulk-delete');
        var modalBulkDeleteOverlay = document.getElementById('modal-bulk-delete-overlay');
        var modalBulkDeleteCount = document.getElementById('modal-bulk-delete-count');
        var modalBulkDeleteLabel = document.getElementById('modal-bulk-delete-label');
        var btnCancelarBulkDelete = document.getElementById('btn-cancelar-bulk-delete');
        var btnConfirmarBulkDelete = document.getElementById('btn-confirmar-bulk-delete');

        function abrirModalBulkDelete() {
            var ids = getIdsSelecionados();
            if (ids.length === 0) return;
            if (modalBulkDeleteCount) modalBulkDeleteCount.textContent = ids.length;
            if (modalBulkDeleteLabel) modalBulkDeleteLabel.textContent = ids.length === 1 ? 'participante' : 'participantes';
            if (modalBulkDelete) modalBulkDelete.classList.remove('hidden');
        }

        function fecharModalBulkDelete() {
            if (modalBulkDelete) modalBulkDelete.classList.add('hidden');
        }

        if (btnBulkDelete) btnBulkDelete.addEventListener('click', abrirModalBulkDelete);
        if (btnCancelarBulkDelete) btnCancelarBulkDelete.addEventListener('click', fecharModalBulkDelete);
        if (modalBulkDeleteOverlay) modalBulkDeleteOverlay.addEventListener('click', fecharModalBulkDelete);

        if (btnConfirmarBulkDelete) {
            btnConfirmarBulkDelete.addEventListener('click', async function() {
                var ids = getIdsSelecionados();
                if (ids.length === 0) return;

                btnConfirmarBulkDelete.disabled = true;
                btnConfirmarBulkDelete.innerHTML = '<svg class="animate-spin w-4 h-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Excluindo...';

                try {
                    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    var res = await fetch('/app/participantes/bulk-delete', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': tokenMeta ? tokenMeta.content : '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ ids: ids.map(Number) }),
                    });

                    var data = await res.json();

                    if (!res.ok || !data.success) {
                        throw new Error(data.error || data.message || 'Erro ao excluir participantes');
                    }

                    window.showToast && window.showToast(data.message || 'Participantes excluidos com sucesso!', 'success');

                    // Remover participantes da tabela/cards que estao na pagina
                    ids.forEach(function(id) {
                        removerParticipanteDaTela(id);
                    });

                    // Limpar selecao
                    selectedIds.clear();
                    limparSelecaoStorage();
                    sincronizarCheckboxes();
                    if (selectAll) selectAll.checked = false;
                    atualizarAcoesLote();
                    fecharModalBulkDelete();

                } catch (err) {
                    console.error('[Monitoramento Participantes] Erro ao excluir em lote:', err);
                    window.showToast && window.showToast(err.message || 'Erro ao excluir participantes', 'error');
                    fecharModalBulkDelete();
                } finally {
                    btnConfirmarBulkDelete.disabled = false;
                    btnConfirmarBulkDelete.textContent = 'Excluir';
                }
            });
        }

        // === Dossiê em lote (espelha /app/clientes) ===
        // Submit é form POST (não fetch) para o navegador tratar a resposta como
        // download, mantendo a página atual intacta.
        var modalDossieLote = document.getElementById('modal-dossie-lote');
        var modalDossieLoteOverlay = document.getElementById('modal-dossie-lote-overlay');
        var btnCancelarDossieLote = document.getElementById('btn-cancelar-dossie-lote');
        var btnConfirmarDossieLote = document.getElementById('btn-confirmar-dossie-lote');
        var selectDossieEscopo = document.getElementById('dossie-lote-escopo');

        function abrirModalDossieLote() {
            if (!modalDossieLote) return;
            // Opção "Participantes selecionados" só existe quando há seleção — e vira o default.
            var opt = document.getElementById('dossie-lote-opt-selecionados');
            if (opt) {
                var total = selectedIds.size;
                opt.classList.toggle('hidden', total === 0);
                opt.disabled = total === 0;
                opt.textContent = 'Participantes selecionados (' + total + ')';
                if (selectDossieEscopo) {
                    selectDossieEscopo.value = total > 0 ? 'selecionados' : '';
                }
            }
            modalDossieLote.classList.remove('hidden');
        }

        function fecharModalDossieLote() {
            if (modalDossieLote) modalDossieLote.classList.add('hidden');
        }

        ['btn-dossie-lote', 'btn-dossie-lote-header'].forEach(function(btnId) {
            var btn = document.getElementById(btnId);
            if (btn) btn.addEventListener('click', abrirModalDossieLote);
        });
        if (btnCancelarDossieLote) btnCancelarDossieLote.addEventListener('click', fecharModalDossieLote);
        if (modalDossieLoteOverlay) modalDossieLoteOverlay.addEventListener('click', fecharModalDossieLote);

        function submitDossieLote(ids) {
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/app/participantes/dossie-lote';
            form.style.display = 'none';

            var token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = tokenMeta ? tokenMeta.content : '';
            form.appendChild(token);

            ids.forEach(function(id) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            fecharModalDossieLote();
            if (window.showToast) window.showToast('Gerando dossiê... o download começa em instantes.', 'info');
        }

        if (btnConfirmarDossieLote) {
            btnConfirmarDossieLote.addEventListener('click', async function() {
                var escolha = selectDossieEscopo ? selectDossieEscopo.value : '';

                if (escolha === 'selecionados') {
                    if (selectedIds.size === 0) return;
                    submitDossieLote(Array.from(selectedIds));
                    return;
                }

                // Todos os participantes respeitando os filtros ativos da tela
                // (mesma fonte do "Selecionar todos").
                btnConfirmarDossieLote.disabled = true;
                btnConfirmarDossieLote.textContent = 'Carregando...';
                try {
                    var params = new URLSearchParams();
                    var formF = document.getElementById('form-filtros');
                    if (formF) {
                        ['importacao', 'cliente', 'origem', 'tipo_documento', 'busca', 'regime', 'situacao', 'uf', 'relacao', 'status_consulta', 'regularidade', 'monitorado'].forEach(function(name) {
                            var field = formF.querySelector('[name="' + name + '"]');
                            if (field && field.value) params.set(name, field.value);
                        });
                    }
                    var url = '/app/participantes/todos-ids' + (params.toString() ? '?' + params.toString() : '');
                    var res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });
                    var data = await res.json();
                    if (!data.success) throw new Error('Erro ao buscar participantes');
                    if (!data.ids || data.ids.length === 0) throw new Error('Nenhum participante para gerar o dossiê');
                    submitDossieLote(data.ids.slice(0, 500));
                } catch (err) {
                    console.error('[Participantes] Erro no dossiê em lote:', err);
                    window.showToast && window.showToast(err.message || 'Erro ao gerar o dossiê em lote', 'error');
                } finally {
                    btnConfirmarDossieLote.disabled = false;
                    btnConfirmarDossieLote.textContent = 'Gerar PDF';
                }
            });
        }

        // Submit do formulario via SPA (se usar data-link)
        var formFiltros = document.getElementById('form-filtros');
        if (formFiltros) {
            formFiltros.addEventListener('submit', function(e) {
                // Deixar o form submeter normalmente se SPA router nao estiver ativo
            });
            // Auto-aplica ao mudar qualquer select (sensação "ao vivo" sem AJAX).
            formFiltros.querySelectorAll('select').forEach(function (sel) {
                sel.addEventListener('change', function () {
                    if (typeof formFiltros.requestSubmit === 'function') {
                        formFiltros.requestSubmit();
                    } else {
                        formFiltros.submit();
                    }
                });
            });
        }

        // === Clique na linha abre perfil do participante ===
        var tbody = document.getElementById('participantes-tbody');
        if (tbody) {
            tbody.addEventListener('click', function(e) {
                // Ignorar cliques em checkbox, botoes e links
                if (e.target.closest('input[type="checkbox"], button, a')) return;

                var row = e.target.closest('tr[data-href]');
                if (!row) return;

                // Navegar via SPA
                var href = row.dataset.href;
                if (href && window.navigateTo) {
                    window.navigateTo(href);
                } else if (href) {
                    window.location.href = href;
                }
            });
        }

        // === Exclusao de participante com modal ===
        var modal = document.getElementById('modal-excluir-participante');
        var modalOverlay = document.getElementById('modal-excluir-overlay');
        var modalCnpj = document.getElementById('modal-excluir-cnpj');
        var modalNome = document.getElementById('modal-excluir-nome');
        var btnCancelar = document.getElementById('btn-cancelar-exclusao');
        var btnConfirmar = document.getElementById('btn-confirmar-exclusao');
        var participanteIdParaExcluir = null;

        function abrirModalExclusao(id, cnpj, nome) {
            participanteIdParaExcluir = id;
            if (modalCnpj) modalCnpj.textContent = cnpj;
            if (modalNome) modalNome.textContent = nome || 'Sem razao social';
            if (modal) modal.classList.remove('hidden');
        }

        function fecharModalExclusao() {
            if (modal) modal.classList.add('hidden');
            participanteIdParaExcluir = null;
        }

        // Abrir/fechar/posicionar do menu de ações fica por conta do componente padrão de ações.
        // Aqui só escutamos o clique no item "Excluir" — delegado em document, sobrevive aos swaps
        // do SPA (cleanup abaixo, junto do listener de resize da troca lista/cards).
        function _piOnExcluirClick(e) {
            var btn = e.target.closest('[data-excluir-participante]');
            if (!btn) return;
            abrirModalExclusao(btn.dataset.excluirParticipante, btn.dataset.cnpj, btn.dataset.nome);
        }
        document.addEventListener('click', _piOnExcluirClick);
        if (!window._cleanupFunctions) window._cleanupFunctions = {};
        window._cleanupFunctions.participantesImportados = function () {
            document.removeEventListener('click', _piOnExcluirClick);
        };

        if (btnCancelar) btnCancelar.addEventListener('click', fecharModalExclusao);
        if (modalOverlay) modalOverlay.addEventListener('click', fecharModalExclusao);

        if (btnConfirmar) {
            btnConfirmar.addEventListener('click', async function() {
                if (!participanteIdParaExcluir) return;

                btnConfirmar.disabled = true;
                btnConfirmar.textContent = 'Excluindo...';

                try {
                    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    var res = await fetch('/app/participante/' + participanteIdParaExcluir, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': tokenMeta ? tokenMeta.content : '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    var data = await res.json();

                    if (!res.ok || !data.success) {
                        throw new Error(data.error || 'Erro ao excluir participante');
                    }

                    window.showToast && window.showToast(data.message || 'Participante excluido com sucesso!', 'success');

                    // Remover do Set persistente
                    selectedIds.delete(Number(participanteIdParaExcluir));
                    salvarSelecao(selectedIds);

                    removerParticipanteDaTela(participanteIdParaExcluir);

                    fecharModalExclusao();
                    atualizarAcoesLote();

                } catch (err) {
                    console.error('[Monitoramento Participantes] Erro:', err);
                    window.showToast && window.showToast(err.message || 'Erro ao excluir participante', 'error');
                    fecharModalExclusao();
                } finally {
                    btnConfirmar.disabled = false;
                    btnConfirmar.textContent = 'Excluir';
                }
            });
        }

        console.log('[Monitoramento Participantes Importados] Inicializacao concluida');
    }

    // Expor globalmente para SPA
    window.initMonitoramentoParticipantesImportados = initMonitoramentoParticipantesImportados;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramentoParticipantesImportados, { once: true });
    } else {
        initMonitoramentoParticipantesImportados();
    }
})();
</script>
