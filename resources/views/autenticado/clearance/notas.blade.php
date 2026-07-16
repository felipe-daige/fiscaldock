@php
    $notas = $notas ?? collect();
    $clientes = $clientes ?? collect();
    $filtros = $filtros ?? [];
    $escopoNotas = $escopoNotas ?? [];
    $saldoAtual = (float) ($saldoAtual ?? 0);
    $custosTiers = $custosTiers ?? ['basico' => 5, 'full' => 5];
    $sort = $sort ?? 'valor_total';
    $dir = $dir ?? 'desc';

    $buildSortUrl = function (string $col) use ($filtros, $sort, $dir) {
        $nextDir = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
        $params = array_filter(array_merge($filtros, ['sort' => $col, 'dir' => $nextDir]), fn ($v) => $v !== null && $v !== '');
        return '/app/clearance/notas?'.http_build_query($params);
    };

    $sortArrow = function (string $col) use ($sort, $dir) {
        if ($sort !== $col) {
            return '<svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>';
        }
        return $dir === 'asc'
            ? '<svg class="w-3 h-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>'
            : '<svg class="w-3 h-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>';
    };

    $sortCustom = $sort !== 'valor_total' || $dir !== 'desc';
    $resetSortUrl = '/app/clearance/notas?'.http_build_query(array_filter($filtros, fn ($v) => $v !== null && $v !== ''));
    $sortLabels = [
        'origem' => 'Origem',
        'numero' => 'Nota',
        'data_emissao' => 'Emissão',
        'emit_razao_social' => 'Emitente',
        'dest_razao_social' => 'Destinatário',
        'valor_total' => 'Valor',
        'tipo_nota' => 'Tipo',
        'modelo' => 'Modelo',
        'status' => 'Status',
    ];

    $statusConsultaOptions = [
        'todos' => 'Todas',
        'consultadas' => 'Consultadas',
        'nao_consultadas' => 'Não consultadas',
    ];

    $statusBadge = function ($nota) {
        $status = strtoupper(trim((string) ($nota->status_consulta ?? '')));

        return match ($status) {
            '' => ['label' => 'Não consultada', 'hex' => '#6b7280'],
            'AUTORIZADA' => ['label' => 'Autorizada', 'hex' => '#047857'],
            'CANCELADA' => ['label' => 'Cancelada', 'hex' => '#b91c1c'],
            'DENEGADA' => ['label' => 'Denegada', 'hex' => '#b91c1c'],
            'INUTILIZADA' => ['label' => 'Inutilizada', 'hex' => '#b45309'],
            'NAO_ENCONTRADA' => ['label' => 'Não encontrada', 'hex' => '#b91c1c'],
            'INDETERMINADO' => ['label' => 'Indeterminada', 'hex' => '#b45309'],
            default => ['label' => ucfirst(mb_strtolower(str_replace('_', ' ', $status))), 'hex' => '#2563eb'],
        };
    };

    $modeloLabels = ['55' => 'NF-e (55)', '65' => 'NFC-e (65)', '57' => 'CT-e (57/67)'];

    // Situação oficial na Receita (snapshot SEFAZ) — dimensão-núcleo do clearance.
    $situacaoReceitaLabels = [
        'AUTORIZADA' => 'Autorizada',
        'CANCELADA' => 'Cancelada',
        'DENEGADA' => 'Denegada',
        'INUTILIZADA' => 'Inutilizada',
        'NAO_ENCONTRADA' => 'Não encontrada',
        'INDETERMINADO' => 'Indeterminada',
    ];

    // Filtros que vivem na gaveta "avançados" — abre sozinha se algum estiver ativo.
    $temAvancado = ! empty($filtros['periodo_de']) || ! empty($filtros['periodo_ate'])
        || ! empty($filtros['participante_cnpj']) || ! empty($filtros['tipo_nota'])
        || ! empty($filtros['modelo']);

    $clienteNome = null;
    if (! empty($filtros['cliente_id'])) {
        $clienteNome = optional($clientes->firstWhere('id', (int) $filtros['cliente_id']))->razao_social;
    }

    // URL preservando sort/dir e demais filtros, removendo/alterando os informados.
    $mkFiltroUrl = function (array $overrides) use ($filtros, $sort, $dir) {
        $base = array_merge($filtros, $overrides);
        if (($base['status_consulta'] ?? 'todos') === 'todos') {
            unset($base['status_consulta']);
        }
        $base['sort'] = $sort;
        $base['dir'] = $dir;
        $params = array_filter($base, fn ($v) => $v !== null && $v !== '');
        return '/app/clearance/notas?'.http_build_query($params);
    };

    $chips = [];
    if (! empty($filtros['busca'])) {
        $chips[] = ['label' => 'Busca: "'.$filtros['busca'].'"', 'url' => $mkFiltroUrl(['busca' => null])];
    }
    if (! empty($filtros['periodo_de']) || ! empty($filtros['periodo_ate'])) {
        $de = $filtros['periodo_de'] ?? '…';
        $ate = $filtros['periodo_ate'] ?? '…';
        $chips[] = ['label' => 'Período: '.$de.' → '.$ate, 'url' => $mkFiltroUrl(['periodo_de' => null, 'periodo_ate' => null])];
    }
    if (! empty($filtros['cliente_id'])) {
        $chips[] = ['label' => 'Cliente: '.($clienteNome ?? $filtros['cliente_id']), 'url' => $mkFiltroUrl(['cliente_id' => null])];
    }
    if (! empty($filtros['participante_cnpj'])) {
        $chips[] = ['label' => 'CNPJ: '.$filtros['participante_cnpj'], 'url' => $mkFiltroUrl(['participante_cnpj' => null])];
    }
    if (! empty($filtros['tipo_nota'])) {
        $chips[] = ['label' => 'Tipo: '.ucfirst($filtros['tipo_nota']), 'url' => $mkFiltroUrl(['tipo_nota' => null])];
    }
    if (! empty($filtros['modelo'])) {
        $chips[] = ['label' => $modeloLabels[$filtros['modelo']] ?? $filtros['modelo'], 'url' => $mkFiltroUrl(['modelo' => null])];
    }
    if (($filtros['status_consulta'] ?? 'todos') !== 'todos') {
        $chips[] = ['label' => 'Consulta: '.($statusConsultaOptions[$filtros['status_consulta']] ?? $filtros['status_consulta']), 'url' => $mkFiltroUrl(['status_consulta' => 'todos'])];
    }
    if (! empty($filtros['situacao_receita'])) {
        $chips[] = ['label' => 'Receita: '.($situacaoReceitaLabels[$filtros['situacao_receita']] ?? $filtros['situacao_receita']), 'url' => $mkFiltroUrl(['situacao_receita' => null])];
    }
@endphp

<div class="min-h-screen bg-gray-100" id="validacao-notas-container"
    data-ids-url="{{ route('app.clearance.todos-ids') }}"
    data-validar-url="{{ route('app.clearance.validar') }}"
    data-tem-mais-pagina="{{ $notas->lastPage() > 1 ? '1' : '0' }}"
    data-saldo-atual="{{ $saldoAtual }}"
    data-custo-basico="{{ $custosTiers['basico'] }}"
    data-custo-full="{{ $custosTiers['full'] }}"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <x-clearance.certificado-banner />

        <div class="mb-4 sm:mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Verificar Notas</h1>
                <p class="text-xs text-gray-500 mt-1">Selecione notas (XML ou EFD) e dispare a validação contábil em lote.</p>
            </div>
            <div class="grid w-full grid-cols-2 gap-2 sm:w-auto sm:flex sm:items-center sm:justify-end">
                <a href="/app/clearance/dashboard" data-link class="inline-flex min-w-0 items-center justify-center gap-1.5 px-3 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-xs font-medium sm:gap-2 sm:px-4 sm:text-sm">
                    Voltar ao Painel
                </a>
                <a href="{{ route('app.clearance.notas.historico') }}" data-link class="inline-flex min-w-0 items-center justify-center gap-1.5 px-3 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded text-xs font-medium sm:gap-2 sm:px-4 sm:text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="truncate">Histórico</span>
                </a>
            </div>
        </div>

        <details class="bg-white rounded border border-gray-300 border-l-4 mb-4 group" style="border-left-color: #2563eb;">
            <summary class="cursor-pointer px-4 py-3 flex items-center justify-between list-none hover:bg-gray-50">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-semibold text-gray-900">Como funciona a verificação de notas</span>
                </div>
                <span class="text-[11px] font-semibold text-gray-500 group-open:hidden">Abrir</span>
                <span class="text-[11px] font-semibold text-gray-500 hidden group-open:inline">Fechar</span>
            </summary>

            <div class="border-t border-gray-200">
                <div class="px-4 py-4">
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-3">Fluxo em 3 etapas</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="relative pl-10">
                            <span class="absolute left-0 top-0 w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center" style="background-color: #2563eb;">1</span>
                            <p class="text-sm font-semibold text-gray-900">Seleção</p>
                            <p class="text-xs text-gray-600 mt-0.5">Você escolhe notas por filtro, importação ou seleção manual — e confirma o tier de validação.</p>
                        </div>
                        <div class="relative pl-10">
                            <span class="absolute left-0 top-0 w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center" style="background-color: #2563eb;">2</span>
                            <p class="text-sm font-semibold text-gray-900">Consulta oficial</p>
                            <p class="text-xs text-gray-600 mt-0.5">O FiscalDock consulta a Receita Federal e normaliza o retorno automaticamente.</p>
                        </div>
                        <div class="relative pl-10">
                            <span class="absolute left-0 top-0 w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center" style="background-color: #2563eb;">3</span>
                            <p class="text-sm font-semibold text-gray-900">Resultado</p>
                            <p class="text-xs text-gray-600 mt-0.5">Você recebe a situação real, eventos de correção/cancelamento e o valor confrontado com a fonte.</p>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Matriz de suporte</p>
                        <span class="text-[10px] font-semibold text-gray-400">Quem pode ser verificado hoje</span>
                    </div>
                    <div class="overflow-x-auto border border-gray-200 rounded">
                        <table class="w-full text-xs tabela-cards">
                            <thead style="background-color: #f9fafb;">
                                <tr class="text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">
                                    <th class="py-2 px-3">Documento</th>
                                    <th class="py-2 px-3">Modelo</th>
                                    <th class="py-2 px-3">Chave</th>
                                    <th class="py-2 px-3">Status</th>
                                    <th class="py-2 px-3">Observação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                <tr>
                                    <td class="py-2 px-3 font-medium text-gray-900">NF-e</td>
                                    <td class="py-2 px-3" data-label="Modelo">55</td>
                                    <td class="py-2 px-3" data-label="Chave">44 dígitos</td>
                                    <td class="py-2 px-3" data-label="Status"><span class="inline-block px-2 py-0.5 rounded text-white text-[10px] font-semibold" style="background-color: #047857;">Suportado</span></td>
                                    <td class="py-2 px-3 text-gray-500" data-label="Observação">Fonte nacional unificada.</td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-3 font-medium text-gray-900">NFC-e</td>
                                    <td class="py-2 px-3" data-label="Modelo">65</td>
                                    <td class="py-2 px-3" data-label="Chave">44 dígitos</td>
                                    <td class="py-2 px-3" data-label="Status"><span class="inline-block px-2 py-0.5 rounded text-white text-[10px] font-semibold" style="background-color: #047857;">Suportado</span></td>
                                    <td class="py-2 px-3 text-gray-500" data-label="Observação">Mesma base da NF-e.</td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-3 font-medium text-gray-900">CT-e / CT-e OS</td>
                                    <td class="py-2 px-3" data-label="Modelo">57 / 67</td>
                                    <td class="py-2 px-3" data-label="Chave">44 dígitos</td>
                                    <td class="py-2 px-3" data-label="Status"><span class="inline-block px-2 py-0.5 rounded text-white text-[10px] font-semibold" style="background-color: #047857;">Suportado</span></td>
                                    <td class="py-2 px-3 text-gray-500" data-label="Observação">Transportes e serviços de transporte.</td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-3 font-medium text-gray-900">NFS-e</td>
                                    <td class="py-2 px-3" data-label="Modelo">—</td>
                                    <td class="py-2 px-3" data-label="Chave">Municipal / 50 dig. nacional</td>
                                    <td class="py-2 px-3" data-label="Status"><span class="inline-block px-2 py-0.5 rounded text-white text-[10px] font-semibold" style="background-color: #6b7280;">Fora de escopo</span></td>
                                    <td class="py-2 px-3 text-gray-500" data-label="Observação">Sem fonte nacional unificada — ver nota abaixo.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 rounded border border-gray-200 p-3" style="background-color: #fffbeb;">
                        <p class="text-[11px] font-semibold text-gray-700 mb-1">Por que NFS-e fica de fora?</p>
                        <p class="text-xs text-gray-600 leading-relaxed">Cada município emite NFS-e com código de verificação próprio (normalmente 8–9 caracteres alfanuméricos) e expõe APIs diferentes. A única fonte nacional disponível hoje exige <strong>NFS-e Nacional</strong> (chave de 50 dígitos), padrão que a maioria das prefeituras ainda não adotou. Por isso o Clearance não lista NFS-e nesta tela — elas continuam visíveis em Notas Fiscais e no Dashboard, sem cruzamento externo.</p>
                    </div>
                </div>

                <div class="px-4 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Como é cobrado</p>
                        <span class="text-[10px] font-semibold text-gray-400">Por nota verificada</span>
                    </div>
                    <div class="border border-gray-300 rounded p-3">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm font-semibold text-gray-900">Clearance</p>
                            <span class="inline-block px-2 py-0.5 rounded text-white text-[10px] font-semibold" style="background-color: #6b7280;">Preço único</span>
                        </div>
                        <p class="text-lg font-bold text-gray-900">@brl(($custosTiers['basico'])) <span class="text-xs font-medium text-gray-500">/nota</span></p>
                        <p class="text-[11px] text-gray-500 mt-1">Situação oficial + eventos de cancelamento + confronto de valores e alertas contábeis. Mesmo preço da busca por chave avulsa.</p>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-3">A cobrança acontece na hora da confirmação. <strong>Falhas do provedor estornam o valor</strong> automaticamente.</p>
                </div>
            </div>
        </details>

        <div id="clearance-notas-error" class="mb-4"></div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo Operacional</span>
                <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">XML + EFD unificadas por chave</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-gray-200">
                <div class="px-4 py-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Notas XML</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($escopoNotas['total_xml'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Importadas pelo usuário</p>
                </div>
                <div class="px-4 py-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Notas EFD</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($escopoNotas['total_efd'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Extraídas do SPED</p>
                </div>
                <div class="px-4 py-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Base Unificada</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($escopoNotas['total_unificado'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Deduplicadas por chave</p>
                </div>
                <div class="px-4 py-4" style="background-color: #ecfdf5">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[10px] font-semibold uppercase tracking-wide" style="color: #047857">Saldo</p>
                        <span class="text-[9px] font-bold uppercase tracking-wide text-white px-1.5 py-0.5 rounded" style="background-color: #047857">Saldo</span>
                    </div>
                    <p id="clearance-saldo-atual" class="text-xl font-bold mt-0.5" style="color: #047857">@brl(($saldoAtual))</p>
                    <p class="text-[11px] mt-1" style="color: #065f46">Disponível para validações</p>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="/app/clearance/notas" class="bg-white rounded border border-gray-300 overflow-hidden mb-4" id="validacao-filtros-form" data-mobile-filters>
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros</span>
            </div>
            <div class="p-3 sm:p-4 space-y-3">
                {{-- Filtros primários (sempre visíveis) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 items-end gap-3" data-primary-clearance-filters>
                    <div class="sm:col-span-2 md:col-span-3 lg:col-span-2">
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide leading-4">Buscar (nº ou chave)</label>
                        <div class="relative mt-1">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" name="busca" value="{{ $filtros['busca'] ?? '' }}" placeholder="Número do documento ou chave de acesso" class="w-full border border-gray-300 rounded pl-9 pr-2 py-1.5 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide leading-4">Cliente</label>
                        <select name="cliente_id" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                            <option value="">Todos</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}" {{ ($filtros['cliente_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->razao_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide leading-4">Consulta</label>
                        <select name="status_consulta" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                            @foreach($statusConsultaOptions as $value => $label)
                                <option value="{{ $value }}" {{ ($filtros['status_consulta'] ?? 'todos') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 md:col-span-1">
                        <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide leading-4">Situação na Receita</label>
                        <select name="situacao_receita" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                            <option value="">Todas</option>
                            @foreach($situacaoReceitaLabels as $sv => $sl)
                                <option value="{{ $sv }}" {{ ($filtros['situacao_receita'] ?? '') === $sv ? 'selected' : '' }}>{{ $sl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filtros avançados (gaveta) — abre sozinha quando algum está ativo. `details`
                     nativo mantém os inputs no DOM mesmo fechado, então o GET envia tudo. --}}
                <details class="border-t border-gray-200 pt-2 sm:pt-3 group" {{ $temAvancado ? 'open' : '' }}>
                    <summary class="cursor-pointer select-none inline-flex min-h-11 w-full sm:w-auto items-center gap-1.5 text-[10px] font-semibold text-gray-500 uppercase tracking-wide hover:text-gray-700">
                        <svg class="w-3.5 h-3.5 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Filtros avançados
                        @if($temAvancado)<span class="normal-case text-[9px] font-bold text-gray-600 bg-gray-200 rounded px-1.5 py-0.5 tracking-normal">ativos</span>@endif
                    </summary>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide leading-4">Emissão — De</label>
                            <input type="date" name="periodo_de" value="{{ $filtros['periodo_de'] ?? '' }}" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide leading-4">Emissão — Até</label>
                            <input type="date" name="periodo_ate" value="{{ $filtros['periodo_ate'] ?? '' }}" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide leading-4">CNPJ Participante</label>
                            <input type="text" name="participante_cnpj" value="{{ $filtros['participante_cnpj'] ?? '' }}" placeholder="00.000.000/0000-00" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide leading-4">Tipo</label>
                            <select name="tipo_nota" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                <option value="">Todos</option>
                                <option value="entrada" {{ ($filtros['tipo_nota'] ?? '') === 'entrada' ? 'selected' : '' }}>Entrada</option>
                                <option value="saida" {{ ($filtros['tipo_nota'] ?? '') === 'saida' ? 'selected' : '' }}>Saída</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wide leading-4">Modelo</label>
                            <select name="modelo" class="mt-1 w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                <option value="">Todos</option>
                                @foreach($modeloLabels as $mv => $ml)
                                    <option value="{{ $mv }}" {{ ($filtros['modelo'] ?? '') === $mv ? 'selected' : '' }}>{{ $ml }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </details>
            </div>
            <div class="mobile-filter-actions bg-gray-50 px-3 sm:px-4 py-2 border-t border-gray-200 grid grid-cols-2 md:flex gap-2">
                <button type="submit" class="px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">Aplicar</button>
                <a href="/app/clearance/notas" data-link class="inline-flex items-center justify-center px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide border border-gray-300 text-gray-700">Limpar</a>
            </div>
        </form>

        @if(! empty($chips))
            <div class="mobile-filter-scroll mb-4 flex flex-nowrap md:flex-wrap items-center gap-2 overflow-x-auto md:overflow-visible pb-1 md:pb-0">
                <span class="shrink-0 whitespace-nowrap text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Filtros ativos</span>
                @foreach($chips as $chip)
                    <a href="{{ $chip['url'] }}" data-link class="inline-flex items-center gap-1.5 bg-white border border-gray-300 rounded-full pl-3 pr-2 py-1 text-[11px] font-medium text-gray-700 hover:bg-gray-50">
                        {{ $chip['label'] }}
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endforeach
                <a href="/app/clearance/notas" data-link class="shrink-0 whitespace-nowrap text-[11px] font-semibold text-gray-500 hover:text-gray-900 underline underline-offset-2">Limpar tudo</a>
            </div>
        @endif

        {{-- Status da seleção --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3 text-sm text-gray-700">
                    <button type="button" id="btn-selecionar-todas" class="px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide border border-gray-300 text-gray-700{{ $notas->lastPage() > 1 ? '' : ' hidden' }}">Selecionar Todos ({{ number_format($notas->total(), 0, ',', '.') }})</button>
                    <span id="selecao-label">Nenhuma nota selecionada</span>
                </div>
                <div class="flex items-center gap-3">
                    @if ($sortCustom)
                        <a href="{{ $resetSortUrl }}" data-link data-clearance-preserve-scroll class="inline-flex items-center gap-1 text-[10px] font-semibold text-gray-500 hover:text-gray-900 uppercase tracking-wide" title="Limpar ordenação">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Limpar ordem · {{ $sortLabels[$sort] ?? $sort }}
                            @if ($dir === 'asc')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            @endif
                        </a>
                    @endif
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded uppercase tracking-wide">{{ number_format($notas->total(), 0, ',', '.') }} resultado(s)</span>
                </div>
            </div>
        </div>

        {{-- CTA de validação (escondido sem seleção). O TIER é escolhido no modal de confirmação. --}}
        <div id="clearance-planos" class="mb-4 hidden">
            {{-- Certificado A1: com o cert cadastrado a MESMA consulta (mesmo preço) volta completa. --}}
            <div class="rounded border bg-white px-4 py-2.5 flex flex-wrap items-center justify-between gap-2" style="border-color: #e5e7eb">
                <p class="text-[11px] text-gray-600">
                    <strong class="text-gray-900">Certificado digital A1:</strong>
                    com o certificado da empresa cadastrado, a consulta vem completa (tributos, itens com NCM/CFOP/CST, XML e contraparte sem máscara) — <strong>sem custo adicional por nota</strong>.
                </p>
                <a href="/app/minha-empresa#certificado-digital" target="_blank" rel="noopener"
                   class="text-[11px] font-semibold whitespace-nowrap px-2.5 py-1 rounded text-white" style="background-color: #1f2937">Cadastrar certificado</a>
            </div>

            <div class="mt-3 flex items-center justify-end bg-white rounded border border-gray-300 px-4 py-3">
                <button type="button" id="btn-validar" class="px-4 py-2 rounded text-[11px] font-bold uppercase tracking-wide text-white disabled:opacity-40" style="background-color: #047857" disabled>Validar</button>
            </div>
        </div>

        {{-- Progresso SSE do clearance externo --}}
        <div id="clearance-progresso" class="mb-4 hidden bg-white rounded border border-gray-300 px-4 py-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Clearance externo em andamento</p>
                <p id="clearance-progresso-percent" class="text-[10px] text-gray-500 font-mono">0%</p>
            </div>
            <div style="width: 100%; height: 6px; background-color: #e5e7eb; border-radius: 9999px; overflow: hidden">
                <div id="clearance-progresso-bar" style="height: 100%; background-color: #1f2937; width: 8%; transition: width 350ms ease-out"></div>
            </div>
            <p id="clearance-progresso-etapa" class="text-xs text-gray-600 mt-2">Iniciando clearance...</p>
        </div>

        {{-- Tabela --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden relative" id="clearance-listagem-card" data-spa-list>
            <div id="clearance-sort-loading" class="hidden absolute inset-0 z-10 bg-white/75 backdrop-blur-[1px] flex items-center justify-center pointer-events-none">
                <div class="bg-white rounded border border-gray-300 shadow-sm px-4 py-3 flex items-center gap-3">
                    <svg class="w-4 h-4 animate-spin text-gray-700" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Ordenação</p>
                        <p class="text-sm text-gray-700">Ordenando notas...</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm tabela-cards">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left"><input type="checkbox" id="chk-master" class="w-4 h-4"></th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">
                                <a href="{{ $buildSortUrl('origem') }}" data-link data-clearance-preserve-scroll class="inline-flex items-center gap-1 hover:text-gray-700">Origem {!! $sortArrow('origem') !!}</a>
                            </th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">
                                <a href="{{ $buildSortUrl('numero') }}" data-link data-clearance-preserve-scroll class="inline-flex items-center gap-1 hover:text-gray-700">Nota {!! $sortArrow('numero') !!}</a>
                            </th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">
                                <a href="{{ $buildSortUrl('data_emissao') }}" data-link data-clearance-preserve-scroll class="inline-flex items-center gap-1 hover:text-gray-700">Emissão {!! $sortArrow('data_emissao') !!}</a>
                            </th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">
                                <a href="{{ $buildSortUrl('emit_razao_social') }}" data-link data-clearance-preserve-scroll class="inline-flex items-center gap-1 hover:text-gray-700">Emitente {!! $sortArrow('emit_razao_social') !!}</a>
                            </th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">
                                <a href="{{ $buildSortUrl('dest_razao_social') }}" data-link data-clearance-preserve-scroll class="inline-flex items-center gap-1 hover:text-gray-700">Destinatário {!! $sortArrow('dest_razao_social') !!}</a>
                            </th>
                            <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-500 uppercase tracking-wide">
                                <a href="{{ $buildSortUrl('valor_total') }}" data-link data-clearance-preserve-scroll class="inline-flex items-center gap-1 hover:text-gray-700 justify-end w-full">Valor {!! $sortArrow('valor_total') !!}</a>
                            </th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">
                                <a href="{{ $buildSortUrl('modelo') }}" data-link data-clearance-preserve-scroll class="inline-flex items-center gap-1 hover:text-gray-700">Modelo {!! $sortArrow('modelo') !!}</a>
                            </th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">
                                <a href="{{ $buildSortUrl('tipo_nota') }}" data-link data-clearance-preserve-scroll class="inline-flex items-center gap-1 hover:text-gray-700">Tipo {!! $sortArrow('tipo_nota') !!}</a>
                            </th>
                            <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wide">
                                <a href="{{ $buildSortUrl('status') }}" data-link data-clearance-preserve-scroll class="inline-flex items-center gap-1 hover:text-gray-700">Status {!! $sortArrow('status') !!}</a>
                            </th>
                            <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-500 uppercase tracking-wide"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="tbody-notas">
                        @forelse($notas as $n)
                            @php
                                $s = $statusBadge($n);
                                $isXml = $n->origem === 'xml';
                                $origemHex = $isXml ? '#374151' : '#9ca3af';
                                $dataEmissao = $n->data_emissao ? \Illuminate\Support\Carbon::parse($n->data_emissao) : null;
                                $detalheUrl = route('app.notas.detalhes', [
                                    'origem' => $n->origem,
                                    'id' => $n->id,
                                ], false);
                                $modeloLabel = $n->modelo_label ?? 'N/D';
                                $modeloHex = $n->modelo_hex ?? '#9ca3af';
                                $notaKey = $n->origem.'-'.$n->id;
                                $motivo = null;
                                if (is_array($n->validacao ?? null)) {
                                    $alertas = $n->validacao['alertas'] ?? [];
                                    foreach ($alertas as $a) {
                                        if (in_array($a['nivel'] ?? null, ['bloqueante', 'atencao'], true)) {
                                            $motivo = $a['mensagem'] ?? $a['codigo'] ?? null;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            <tr data-nota-id="{{ $n->id }}" data-origem="{{ $n->origem }}" class="hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    <input type="checkbox" class="w-4 h-4 chk-nota" value="{{ $n->id }}">
                                </td>
                                <td class="px-3 py-2" data-label="Origem">
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $origemHex }}">
                                        {{ strtoupper($n->origem) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 font-mono text-xs" data-label="Nota">{{ $n->numero }}/{{ $n->serie }}</td>
                                <td class="px-3 py-2 text-xs" data-label="Emissão">{{ $dataEmissao?->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 text-xs text-gray-700 truncate max-w-[180px]" data-label="Emitente">{{ $n->emit_razao_social }}</td>
                                <td class="px-3 py-2 text-xs text-gray-700 truncate max-w-[180px]" data-label="Destinatário">{{ $n->dest_razao_social }}</td>
                                <td class="px-3 py-2 text-xs text-right font-mono" data-label="Valor">R$&nbsp;{{ number_format((float) $n->valor_total, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 text-xs" data-label="Modelo">
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $modeloHex }}">
                                        {{ $modeloLabel }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-xs" data-label="Tipo">
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $n->tipo_nota === 'entrada' ? '#047857' : '#b45309' }}">
                                        {{ ucfirst($n->tipo_nota) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-xs" data-label="Status">
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white td-status" style="background-color: {{ $s['hex'] }}">{{ $s['label'] }}</span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" class="nota-details-toggle inline-flex items-center gap-1 text-xs text-gray-600 hover:text-gray-900" data-nota-key="{{ $notaKey }}" aria-expanded="false">
                                        <span>Detalhes</span>
                                        <svg class="w-3.5 h-3.5 nota-details-chevron transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr class="nota-expand-row hidden" data-expand-for="{{ $notaKey }}">
                                <td colspan="11" class="px-4 py-3 bg-gray-50">
                                    @include('autenticado.clearance.partials._detalhes-nota-listagem', [
                                        'nota' => $n,
                                        'status' => $s,
                                        'dataEmissao' => $dataEmissao,
                                        'detalheUrl' => $detalheUrl,
                                        'motivo' => $motivo,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-3 py-12 text-center">
                                    @if(($escopoNotas['total_unificado'] ?? 0) === 0)
                                        <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <p class="text-sm font-semibold text-gray-700 mt-3">Nenhuma nota no acervo ainda</p>
                                        <p class="text-xs text-gray-500 mt-1">Importe documentos fiscais para começar a verificar.</p>
                                        <div class="mt-4 flex items-center justify-center gap-2">
                                            <a href="/app/importacao/efd" data-link class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">Importar EFD</a>
                                            <a href="/app/importacao/xml" data-link class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide border border-gray-300 text-gray-700">Importar XML</a>
                                        </div>
                                    @else
                                        <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <p class="text-sm font-semibold text-gray-700 mt-3">Nenhuma nota bate com os filtros</p>
                                        <p class="text-xs text-gray-500 mt-1">Ajuste ou limpe os filtros para ver mais resultados.</p>
                                        <a href="/app/clearance/notas" data-link class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide border border-gray-300 text-gray-700">Limpar filtros</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                {{ $notas->withQueryString()->links() }}
            </div>
        </div>

        @include('autenticado.clearance.partials._historico-verificacoes')
    </div>

</div>

{{-- Modal: Confirmar Clearance (a escolha de TIER vive aqui — foi tirada do topo da tela) --}}
<div id="modal-confirmar-validacao" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded border border-gray-300 shadow-lg max-w-md w-full">
        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Confirmar Clearance</span>
        </div>
        <div class="p-5 space-y-4">
            <p class="text-sm text-gray-700">Como validar <strong id="modal-confirm-qtd">0</strong> nota(s)?</p>

            {{-- Escolha EXCLUSIVA de tier (radio). name=clearance_tier: o JS (tierAtual) lê daqui. --}}
            <div class="space-y-2">
                <label for="tier-basico" data-tier-opt="basico" class="flex items-start gap-2.5 rounded border p-3 cursor-pointer transition" style="border-color: #1f2937; background-color: #f9fafb">
                    <input type="radio" name="clearance_tier" id="tier-basico" value="basico" class="mt-0.5 h-4 w-4" style="accent-color: #1f2937" checked>
                    <span class="flex-1 min-w-0">
                        <span class="flex items-center justify-between gap-2">
                            <span class="text-sm font-bold text-gray-900">Clearance</span>
                            <span class="text-sm font-bold text-gray-900" data-tier-total="basico">R$ 0,00</span>
                        </span>
                        <span class="block text-[11px] text-gray-500 mt-0.5">Status SEFAZ, validação contábil e cruzamento EFD. <span class="text-gray-400">@brl(($custosTiers['basico']))/nota</span></span>
                    </span>
                </label>
                @if(config('clearance.full.habilitado'))
                <label for="tier-full" data-tier-opt="full" class="flex items-start gap-2.5 rounded border p-3 cursor-pointer transition" style="border-color: #d1d5db">
                    <input type="radio" name="clearance_tier" id="tier-full" value="full" class="mt-0.5 h-4 w-4" style="accent-color: #1f2937">
                    <span class="flex-1 min-w-0">
                        <span class="flex items-center justify-between gap-2">
                            <span class="text-sm font-bold text-gray-900">Clearance completo</span>
                            <span class="text-sm font-bold text-gray-900" data-tier-total="full">R$ 0,00</span>
                        </span>
                        <span class="block text-[11px] text-gray-500 mt-0.5">Tudo do Clearance <strong>+</strong> regularidade da contraparte (cadastral, SINTEGRA, CND Federal). <span class="text-gray-400">@brl(($custosTiers['full']))/nota</span></span>
                    </span>
                </label>
                @else
                {{-- Full desligado: teaser (sem radio) pra manter o upsell visível. --}}
                <div class="flex items-start gap-2.5 rounded border border-dashed p-3" style="border-color: #d1d5db; background-color: #f9fafb">
                    <span class="flex-1 min-w-0">
                        <span class="flex items-center justify-between gap-2">
                            <span class="text-sm font-bold text-gray-500">Clearance completo</span>
                            <span class="text-[9px] font-bold uppercase tracking-wide text-white px-2 py-0.5 rounded" style="background-color: #6b7280">Em breve</span>
                        </span>
                        <span class="block text-[11px] text-gray-500 mt-0.5">Tudo do Clearance + a regularidade da contraparte de cada nota (situação cadastral, SINTEGRA e CND Federal).</span>
                    </span>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-2 divide-x divide-gray-200 border border-gray-200 rounded overflow-hidden">
                <div class="px-3 py-3">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Custo total</p>
                    <p class="text-lg font-bold text-gray-900 mt-0.5"><span id="modal-confirm-custo">R$ 0,00</span></p>
                </div>
                <div class="px-3 py-3">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Saldo após</p>
                    <p class="text-lg font-bold mt-0.5" id="modal-confirm-saldo-apos">R$ 0,00</p>
                </div>
            </div>

            {{-- Preço FECHADO por nota (R$ 2,00): inclui a regularidade da contraparte. O total acima
                 já é o total — não há cobrança posterior. --}}
            <div id="modal-confirm-regularidade" class="hidden rounded p-3" style="background-color: #f0fdf4; border: 1px solid #bbf7d0">
                <p class="text-[12px]" style="color: #166534">
                    <strong>Inclui a regularidade da contraparte</strong> de cada nota — situação cadastral, SINTEGRA (IE) e CND Federal. Sem cobrança adicional: o valor acima é o total.
                </p>
            </div>
        </div>
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-2">
            <button type="button" id="modal-confirm-cancelar" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">Cancelar</button>
            <button type="button" id="modal-confirm-ok" class="px-4 py-2 text-xs font-semibold text-white rounded disabled:opacity-40 disabled:cursor-not-allowed" style="background-color: #1f2937">Confirmar validação</button>
        </div>
    </div>
</div>

{{-- Modal: Sucesso --}}
<div id="modal-sucesso-validacao" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded border border-gray-300 shadow-lg max-w-md w-full">
        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Validação concluída</span>
            <span class="text-[9px] font-bold uppercase tracking-wide text-white px-2 py-0.5 rounded" style="background-color: #047857">OK</span>
        </div>
        <div class="p-5 space-y-3">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8" fill="none" stroke="#047857" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-gray-700">Todas as notas selecionadas foram processadas.</p>
            </div>
            <div class="border border-gray-200 rounded px-3 py-3" style="background-color: #ecfdf5">
                <p class="text-[10px] font-semibold uppercase tracking-wide" style="color: #047857">Valor debitado</p>
                <p class="text-lg font-bold mt-0.5" style="color: #047857"><span id="modal-sucesso-valor">R$ 0,00</span></p>
            </div>
        </div>
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-end">
            <button type="button" id="modal-sucesso-ok" class="px-4 py-2 text-xs font-semibold text-white rounded" style="background-color: #1f2937">OK</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/clearance-notas.js') }}?v={{ @filemtime(public_path('js/clearance-notas.js')) ?: time() }}" defer></script>
