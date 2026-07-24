{{-- Consulta por Fontes (à la carte) — seletor canônico de consulta por CPF/CNPJ.
     Seleção de consultas/kit/preset via MODAL; usuário salva a própria combinação ("meu plano"). --}}
@php
    // Helper local: badge "Grátis" (hex inline, regra do design system) quando preço = 0.
    $precoLabel = fn ($p) => $p <= 0
        ? '<span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#047857">Grátis</span>'
        : '<span class="text-gray-500">'.\App\Support\Dinheiro::brl($p).' <span class="text-gray-400">por alvo</span></span>';
@endphp
<div class="bg-gray-100 min-h-screen" id="consulta-fontes-container"
     @if(!empty($prefill)) data-prefill='@json($prefill)' @endif>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6">
            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Nova Consulta</h1>
                <p class="text-xs text-gray-500 mt-0.5">Monte a consulta por CPF ou CNPJ escolhendo exatamente as fontes necessárias. Cadastro básico grátis vale apenas para CNPJ.</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="/app/consulta/historico" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 sm:px-4 sm:text-sm" data-link>
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="hidden sm:inline">Histórico</span>
                </a>
                <div class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs sm:px-4 sm:text-sm">
                    <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h.01M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path></svg>
                    <span class="text-gray-500">Saldo</span>
                    <strong class="text-gray-900 font-mono">{{ \App\Support\Dinheiro::brl($saldoReais) }}</strong>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- Coluna 1-2: consultas + alvos --}}
            <div class="lg:col-span-3 space-y-4 sm:space-y-6">
                {{-- Card consultas (seleção via modal) --}}
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full text-white text-[11px] font-bold" style="background-color:#1f2937">1</span>
                            <div class="min-w-0">
                                <h2 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Consultas</h2>
                                <p class="text-[11px] text-gray-500 leading-tight">Kits prontos, um plano seu ou consultas avulsas.</p>
                            </div>
                        </div>
                        <button type="button" id="btn-abrir-consultas" class="inline-flex items-center gap-1.5 rounded bg-gray-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-gray-700 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Escolher
                        </button>
                    </div>
                    <div class="p-4">
                        <div id="consultas-selecao-vazia" class="flex flex-col items-center justify-center gap-2 py-8 text-center">
                            <svg class="h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-3-3v6m9-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-sm text-gray-400">Nenhuma consulta escolhida. Clique em <strong class="text-gray-600">Escolher</strong>.</p>
                        </div>
                        <div id="consultas-selecao-chips" class="hidden flex flex-wrap gap-1.5"></div>
                    </div>
                </div>

                {{-- Card alvos --}}
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full text-white text-[11px] font-bold" style="background-color:#1f2937">2</span>
                            <div class="min-w-0">
                                <h2 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Quem consultar</h2>
                                <p class="text-[11px] text-gray-500 leading-tight">Busque CPF ou CNPJ nos seus participantes e clientes.</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded flex-shrink-0">{{ $totalParticipantes }} na base</span>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <input type="search" id="fontes-busca-alvo" placeholder="Buscar por nome, CPF, razão social ou CNPJ…" autocomplete="off"
                                       class="w-full border border-gray-300 rounded text-sm pl-9 pr-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400 focus:outline-none">
                            </div>
                            <button type="button" id="fontes-ver-todos" class="inline-flex items-center gap-1.5 rounded border border-gray-300 bg-white px-3 py-2.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 flex-shrink-0">
                                <svg class="chev-todos h-4 w-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                <span class="hidden sm:inline">Ver todos</span>
                            </button>
                        </div>
                        {{-- Filtros (aparecem ao expandir "Ver todos") --}}
                        <div id="fontes-filtros" class="hidden mt-2 grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <select id="filtro-tipo-pessoa" class="border border-gray-300 rounded text-[13px] px-2 py-2 focus:ring-1 focus:ring-gray-400 focus:border-gray-400 focus:outline-none">
                                <option value="">Pessoa (todas)</option>
                                <option value="PF">Pessoa física</option>
                                <option value="PJ">Pessoa jurídica</option>
                            </select>
                            <select id="filtro-uf" class="border border-gray-300 rounded text-[13px] px-2 py-2 focus:ring-1 focus:ring-gray-400 focus:border-gray-400 focus:outline-none">
                                <option value="">UF (todas)</option>
                                @foreach($participantesUfs as $uf)<option value="{{ $uf }}">{{ $uf }}</option>@endforeach
                            </select>
                            <select id="filtro-situacao" class="border border-gray-300 rounded text-[13px] px-2 py-2 focus:ring-1 focus:ring-gray-400 focus:border-gray-400 focus:outline-none">
                                <option value="">Situação (todas)</option>
                                @foreach($participantesSituacoes as $sit)<option value="{{ $sit }}">{{ ucfirst(mb_strtolower($sit)) }}</option>@endforeach
                            </select>
                            <select id="filtro-relacao" class="border border-gray-300 rounded text-[13px] px-2 py-2 focus:ring-1 focus:ring-gray-400 focus:border-gray-400 focus:outline-none">
                                <option value="">Relação (todas)</option>
                                <option value="fornecedor">Fornecedor</option>
                                <option value="cliente">Cliente</option>
                                <option value="sem_movimentacao">Sem movimentação</option>
                            </select>
                        </div>
                        <div id="fontes-resultados-alvo" class="mt-2 max-h-72 overflow-y-auto divide-y divide-gray-100 border border-gray-200 rounded hidden"></div>
                        <p id="fontes-compat-aviso" class="hidden mt-2 rounded px-3 py-2 text-[11px]" style="background-color:#fffbeb;color:#92400e;"></p>
                        <div id="fontes-selecionados" class="mt-3 space-y-2"></div>
                    </div>
                </div>
            </div>

            {{-- Coluna 3: resumo (sticky) --}}
            <div>
                <div class="bg-white rounded border border-gray-300 overflow-hidden lg:sticky lg:top-4">
                    <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200">
                        <h2 class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo</h2>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-2 divide-x divide-gray-200 border border-gray-200 rounded mb-3">
                            <div class="px-3 py-2.5 text-center">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consultas</p>
                                <p id="fontes-resumo-fontes" class="text-lg font-bold text-gray-900">0</p>
                            </div>
                            <div class="px-3 py-2.5 text-center">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Alvos</p>
                                <p id="fontes-resumo-alvos" class="text-lg font-bold text-gray-900">0</p>
                            </div>
                        </div>
                        <div class="space-y-1.5 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500">Preço por alvo</span><strong id="fontes-resumo-unitario" class="text-gray-900 font-mono">R$ 0,00</strong></div>
                            <div id="fontes-resumo-desconto-linha" class="hidden flex justify-between"><span style="color:#047857">Desconto <span id="fontes-resumo-kit-nome"></span></span><strong id="fontes-resumo-desconto" class="font-mono" style="color:#047857">−R$ 0,00</strong></div>
                        </div>
                        <div class="flex items-baseline justify-between border-t border-gray-200 pt-3 mt-3">
                            <span class="text-gray-700 font-medium text-sm">Total</span>
                            <strong id="fontes-resumo-total" class="text-xl font-bold text-gray-900 font-mono">R$ 0,00</strong>
                        </div>
                        <p id="fontes-saldo-aviso" class="hidden text-[11px] rounded px-2 py-1.5 mt-2" style="background-color:#fffbeb;color:#b45309;">Saldo insuficiente para esta seleção. <a href="/app/saldo" class="underline font-medium" data-link>Adicionar saldo</a></p>
                        <div id="fontes-sensivel-bloco" class="hidden mt-3 rounded border p-3" style="border-color:#fecaca;background-color:#fef2f2;">
                            <p class="text-[11px] font-semibold" style="color:#991b1b;">Dado pessoal sensível (LGPD art. 11)</p>
                            <p class="text-[11px] mt-1 leading-snug" style="color:#7f1d1d;">Esta seleção inclui consulta a dado sensível de terceiro. Base legal: {{ config('advocacia.sensivel.base_legal') }} Declare abaixo a finalidade — o registro fica na trilha de auditoria.</p>
                            <textarea id="fontes-sensivel-finalidade" rows="2" maxlength="2000"
                                      placeholder="Finalidade (ex.: nº do processo, contexto da investigação de parte)…"
                                      class="mt-2 w-full rounded border border-red-200 px-2 py-1.5 text-[12px] focus:border-red-400 focus:ring-red-300"></textarea>
                        </div>
                        <button type="button" id="fontes-executar" disabled
                                class="w-full mt-3 inline-flex items-center justify-center gap-2 rounded bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed">
                            Executar consulta
                        </button>
                        <p class="text-[11px] text-gray-400 mt-2 leading-snug">Consultas que falharem por indisponibilidade da fonte oficial são estornadas automaticamente.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ MODAL de seleção de consultas ============ --}}
    {{-- Overlay rolável + painel centralizado com altura AUTO (cap por max-height inline, não por
         classe arbitrária). Painel só cresce até o conteúdo; se passar de 88vh, o corpo rola. --}}
    <div id="modal-consultas" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 overflow-y-auto" id="modal-consultas-backdrop">
            <div class="flex min-h-full items-start sm:items-center justify-center p-0 sm:p-4">
                <div class="modal-panel relative w-full max-w-3xl bg-white sm:rounded-lg shadow-2xl flex flex-col" style="max-height: 88vh">
            <div class="flex items-start justify-between px-5 py-3.5 border-b border-gray-200 flex-shrink-0">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Escolher consultas</h3>
                    <p class="text-[11px] text-gray-500 mt-0.5">Um kit preenche tudo de uma vez; ajuste marcando/desmarcando consultas.</p>
                </div>
                <button type="button" id="modal-consultas-fechar" class="text-gray-400 hover:text-gray-700 text-2xl leading-none -mt-1">&times;</button>
            </div>

            <div class="overflow-y-auto px-5 py-4 space-y-6 flex-1">
                {{-- Planos (kits da vitrine visíveis pro usuário: publico=todos + os atribuídos a ele) --}}
                @if(!empty($kits))
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-2">Planos</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($kits as $kit)
                                <button type="button" class="kit-preset group text-left rounded-lg border border-gray-300 p-3 transition-colors hover:border-gray-800 hover:bg-gray-50" data-fontes='@json($kit['fontes'])'>
                                    <span class="flex items-start justify-between gap-2">
                                        <span class="text-[13px] font-bold text-gray-900">{{ $kit['nome'] }}</span>
                                        @if($kit['desconto_percentual'] > 0)
                                            <span class="flex-shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold text-white" style="background-color:#047857">−{{ number_format($kit['desconto_percentual'], 0, ',', '.') }}%</span>
                                        @endif
                                    </span>
                                    <span class="mt-1 flex items-baseline gap-1.5">
                                        @if($kit['preco_total'] <= 0)
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#047857">Grátis</span>
                                        @else
                                            @if($kit['preco_total'] < $kit['preco_bruto'])<s class="text-[11px] text-gray-400">{{ \App\Support\Dinheiro::brl($kit['preco_bruto']) }}</s>@endif
                                            <strong class="text-sm text-gray-900 font-mono">{{ \App\Support\Dinheiro::brl($kit['preco_total']) }}</strong><span class="text-[11px] text-gray-400">/alvo</span>
                                        @endif
                                        <span class="ml-auto text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ count($kit['fontes']) }} {{ count($kit['fontes']) === 1 ? 'consulta' : 'consultas' }}</span>
                                    </span>
                                    @if($kit['descricao'])<span class="block text-[11px] text-gray-500 mt-1.5 leading-snug">{{ $kit['descricao'] }}</span>@endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Presets pessoais ("meus planos") — sempre visível; salvar via botão do rodapé --}}
                <div id="meus-planos-bloco">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-2">Meus planos</p>
                    <p id="meus-planos-vazio" class="{{ empty($meusPlanos) ? '' : 'hidden' }} text-[12px] text-gray-400 rounded-lg border border-dashed border-gray-200 px-3 py-4 text-center">
                        Nenhum plano salvo. Monte uma seleção e use <strong class="text-gray-500">Salvar como plano</strong> no rodapé para reutilizar depois.
                    </p>
                    <div id="meus-planos-lista" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($meusPlanos as $preset)
                            <div class="preset-pessoal rounded-lg border border-gray-300 transition-colors hover:border-gray-800 hover:bg-gray-50" data-id="{{ $preset['id'] }}" data-fontes='@json($preset['fontes'])'>
                                <div class="flex items-start justify-between gap-1 p-3">
                                    <button type="button" class="preset-aplicar text-left min-w-0 flex-1">
                                        <span class="block text-[13px] font-bold text-gray-900">{{ $preset['nome'] }}</span>
                                        <span class="mt-1 flex items-baseline gap-1.5">
                                            <strong class="text-sm text-gray-900 font-mono">{{ \App\Support\Dinheiro::brl($preset['preco_total']) }}</strong><span class="text-[11px] text-gray-400">/alvo</span>
                                            <span class="ml-auto text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ count($preset['fontes']) }} consultas</span>
                                        </span>
                                    </button>
                                    <button type="button" class="preset-excluir flex-shrink-0 -mr-1 -mt-1 p-1 text-gray-300 hover:text-red-600 font-bold text-base leading-none" title="Excluir plano" data-id="{{ $preset['id'] }}">×</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Todas as consultas — grupos recolhíveis (abertos por padrão) --}}
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Todas as consultas (avulsas)</p>
                        <button type="button" id="toggle-todas" class="text-[11px] font-semibold text-gray-600 hover:text-gray-900">Selecionar todas</button>
                    </div>
                    <div class="space-y-2">
                        @forelse($gruposFontes as $chaveGrupo => $grupo)
                            @php
                                $grupoTemSelecionavel = collect($grupo['fontes'])->contains(
                                    fn ($fonte) => (bool) ($fonte['selecionavel'] ?? false)
                                );
                            @endphp
                            <details class="grupo-consultas rounded-lg border border-gray-200 overflow-hidden" data-grupo="{{ $chaveGrupo }}" open>
                                <summary class="flex items-center justify-between gap-2 px-3 py-2.5 cursor-pointer select-none bg-gray-50 hover:bg-gray-100 transition-colors">
                                    <span class="flex items-center gap-2 min-w-0">
                                        <span class="text-[12px] font-semibold text-gray-700">{{ $grupo['label'] }}</span>
                                        <span class="grupo-count hidden flex-shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-bold text-white" style="background-color:#1f2937">0</span>
                                        <span class="text-[10px] text-gray-400">{{ count($grupo['fontes']) }} {{ count($grupo['fontes']) === 1 ? 'consulta' : 'consultas' }}</span>
                                    </span>
                                    <span class="flex items-center gap-3 flex-shrink-0">
                                        @if($grupoTemSelecionavel)
                                            <button type="button" class="grupo-toggle text-[11px] font-semibold text-gray-500 hover:text-gray-900" data-grupo="{{ $chaveGrupo }}">Selecionar todos</button>
                                        @else
                                            <span class="text-[9px] font-bold uppercase tracking-wide text-amber-700">Em manutenção</span>
                                        @endif
                                        <svg class="chev h-4 w-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </span>
                                </summary>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2.5 border-t border-gray-100">
                                    @foreach($grupo['fontes'] as $fonte)
                                        @php
                                            $documentosLabel = $fonte['documentos_aceitos_label'];
                                            $selecionavel = (bool) ($fonte['selecionavel'] ?? false);
                                            $tiposEmManutencao = (array) ($fonte['tipos_pessoa_em_manutencao'] ?? []);
                                            $documentosHex = $documentosLabel === 'CPF e CNPJ'
                                                ? '#0f766e'
                                                : ($documentosLabel === 'CPF' ? '#6b7280' : '#374151');
                                        @endphp
                                        <label class="fonte-opt flex items-center gap-2.5 rounded-lg border px-3 py-2 transition-colors {{ $selecionavel ? 'border-gray-300 cursor-pointer hover:bg-gray-50' : 'border-gray-200 cursor-not-allowed bg-gray-50/70' }}"
                                               data-tipos-pessoa='@json($fonte['tipos_pessoa'])'
                                               data-tipos-planejados='@json($fonte['tipos_pessoa_planejados'])'
                                               data-selecionavel-base="{{ $selecionavel ? '1' : '0' }}"
                                               data-documentos-label="{{ $documentosLabel }}">
                                            <input type="checkbox" name="fontes[]" value="{{ $fonte['chave'] }}" data-preco="{{ $fonte['preco'] }}" data-nome="{{ $fonte['nome'] }}"
                                                   data-tipos-pessoa='@json($fonte['tipos_pessoa'])' data-documentos-label="{{ $documentosLabel }}"
                                                   data-requisitos-pf='@json($fonte['requisitos_pf'])'
                                                   data-requisitos-alvo='@json($fonte['requisitos_alvo'])'
                                                   data-sensivel="{{ !empty($fonte['sensivel']) ? '1' : '0' }}"
                                                   {{ $selecionavel ? '' : 'disabled' }}
                                                   class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-gray-800 focus:ring-gray-500 disabled:cursor-not-allowed disabled:opacity-40">
                                            <span class="min-w-0 flex-1">
                                                <span class="flex items-center gap-1.5">
                                                    <span class="min-w-0 flex-1 text-[13px] font-semibold {{ $selecionavel ? 'text-gray-900' : 'text-gray-600' }} leading-tight">{{ $fonte['nome'] }}</span>
                                                    <span class="inline-flex flex-shrink-0 items-center rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white"
                                                          style="background-color:{{ $documentosHex }}">{{ $documentosLabel }}</span>
                                                </span>
                                                @if($tiposEmManutencao !== [])
                                                    <span class="mt-1 inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white" style="background-color:#b45309">
                                                        {{ implode(' e ', array_map(fn ($tipo) => $tipo === 'PF' ? 'CPF' : 'CNPJ', $tiposEmManutencao)) }} em manutenção
                                                    </span>
                                                @endif
                                                @if($selecionavel)
                                                    <span class="block text-[11px] mt-0.5">{!! $precoLabel($fonte['preco']) !!}</span>
                                                @else
                                                    <span class="mt-1 inline-flex items-center rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white" style="background-color:#b45309">Em manutenção</span>
                                                @endif
                                                @if(!empty($fonte['descricao']))
                                                    <span class="mt-1 block text-[10px] leading-snug text-gray-500">{{ $fonte['descricao'] }}</span>
                                                @endif
                                                @if(!$selecionavel && !empty($fonte['motivo_manutencao']))
                                                    <span class="mt-1 block text-[10px] leading-snug text-gray-400">{{ $fonte['motivo_manutencao'] }}</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        @empty
                            <p class="text-sm text-gray-500">Nenhuma consulta disponível no momento. Tente novamente mais tarde.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 border-t border-gray-200 bg-gray-50 sm:rounded-b-lg">
                {{-- Linha do nome do plano (aparece ao clicar "Salvar como plano") --}}
                <div id="salvar-plano-form" class="hidden flex items-center gap-2 px-5 py-2.5 border-b border-gray-200">
                    <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h8l4 4v10a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v4h6"></path></svg>
                    <input type="text" id="salvar-plano-nome" maxlength="120" placeholder="Nome do plano (ex.: Diligência padrão)"
                           class="flex-1 rounded border border-gray-300 px-2.5 py-1.5 text-[13px] focus:ring-1 focus:ring-gray-400 focus:border-gray-400 focus:outline-none">
                    <button type="button" id="salvar-plano-confirmar" class="rounded bg-gray-900 px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-gray-700">Salvar</button>
                    <button type="button" id="salvar-plano-cancelar" class="text-[12px] text-gray-500 hover:text-gray-800">Cancelar</button>
                </div>
                {{-- Linha principal: contagem/preço + ações --}}
                <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                    <span class="text-[12px] text-gray-500"><strong id="modal-consultas-contagem" class="text-gray-900">0</strong> consultas · <strong id="modal-consultas-preco" class="text-gray-900 font-mono">R$ 0,00</strong>/alvo</span>
                    <div class="flex items-center gap-2">
                        {{-- Só aparece com ≥1 consulta na seleção (togglado pelo JS) --}}
                        <span id="salvar-plano-bloco" class="hidden">
                            <button type="button" id="btn-salvar-plano" class="inline-flex items-center gap-1.5 rounded border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h8l4 4v10a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v4h6"></path></svg>
                                Salvar como plano
                            </button>
                        </span>
                        <button type="button" id="modal-consultas-aplicar" class="rounded bg-gray-900 px-5 py-2 text-sm font-semibold text-white hover:bg-gray-700">Aplicar seleção</button>
                    </div>
                </div>
            </div>
                </div>{{-- /modal-panel --}}
            </div>{{-- /flex centralizador --}}
        </div>{{-- /overlay rolável (backdrop) --}}
    </div>{{-- /modal-consultas --}}

    {{-- ===== Confirmação de exclusão de plano (sobre o modal de consultas, z maior) ===== --}}
    <div id="modal-excluir-plano" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-black/50" id="modal-excluir-backdrop"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-sm bg-white rounded-lg shadow-2xl">
                <div class="p-5">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full" style="background-color:#fef2f2">
                            <svg class="h-5 w-5" style="color:#dc2626" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-gray-900">Excluir plano?</h3>
                            <p class="text-[13px] text-gray-500 mt-1">O plano <strong id="excluir-plano-nome" class="text-gray-800">—</strong> será removido dos seus planos salvos. As consultas continuam disponíveis avulsas.</p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 px-5 py-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                    <button type="button" id="excluir-plano-cancelar" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">Cancelar</button>
                    <button type="button" id="excluir-plano-confirmar" class="rounded px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90" style="background-color:#dc2626">Excluir plano</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #modal-consultas details.grupo-consultas > summary { list-style: none; }
    #modal-consultas details.grupo-consultas > summary::-webkit-details-marker { display: none; }
    #modal-consultas details.grupo-consultas[open] > summary .chev { transform: rotate(180deg); }
</style>
@php $consultaFontesJsVersion = @filemtime(public_path('js/consulta-fontes.js')) ?: time(); @endphp
<script src="/js/consulta-fontes.js?v={{ $consultaFontesJsVersion }}"></script>
<script>
    if (window.initConsultaFontes) { window.initConsultaFontes(); }
</script>
