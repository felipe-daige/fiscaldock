{{-- Consulta Avulsa por Fontes (à la carte, vertical advocacia) — docs/advocacia/consultas-certidoes.md --}}
<div class="bg-gray-100 min-h-screen" id="consulta-fontes-container"
     @if(!empty($prefill)) data-prefill='@json($prefill)' @endif>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-4 sm:mb-6">
            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Consulta por Fontes</h1>
                <p class="text-xs text-gray-500 mt-1">Monte a consulta escolhendo exatamente as fontes que precisa. Preço por fonte, cobrado por CNPJ consultado.</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="/app/consulta/historico" class="inline-flex items-center justify-center gap-1.5 rounded border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-50 sm:px-4 sm:text-sm" data-link>
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Histórico</span>
                </a>
                <span class="inline-flex items-center rounded border border-gray-200 bg-white px-3 py-2 text-xs sm:text-sm text-gray-600">Saldo: <strong class="ml-1 text-gray-900">{{ \App\Support\Dinheiro::brl($saldoReais) }}</strong></span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- Coluna 1-2: fontes + alvos --}}
            <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                @if(!empty($kits))
                    {{-- Card kits: preset preenche a seleção; desconto só vale com a seleção exata --}}
                    <div class="bg-white rounded border border-gray-200">
                        <div class="border-b border-gray-200 px-4 py-3">
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Kits prontos</h2>
                            <p class="text-[11px] text-gray-500 mt-0.5">Um clique preenche a seleção. Ajustou alguma fonte? O preço volta ao valor unitário — o desconto vale para o kit completo.</p>
                        </div>
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            @foreach($kits as $kit)
                                <button type="button" class="kit-preset text-left rounded border border-gray-300 px-3 py-2.5 transition-colors hover:bg-gray-50"
                                        data-fontes='@json($kit['fontes'])'>
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="text-[13px] font-semibold text-gray-900">{{ $kit['nome'] }}</span>
                                        @if($kit['desconto_percentual'] > 0)
                                            <span class="flex-shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold text-white" style="background-color:#047857">−{{ number_format($kit['desconto_percentual'], 0, ',', '.') }}%</span>
                                        @endif
                                    </span>
                                    <span class="block text-[11px] text-gray-500 mt-0.5">{{ count($kit['fontes']) }} fontes ·
                                        @if($kit['preco_total'] < $kit['preco_bruto'])<s class="text-gray-400">{{ \App\Support\Dinheiro::brl($kit['preco_bruto']) }}</s>@endif
                                        <strong class="text-gray-800">{{ \App\Support\Dinheiro::brl($kit['preco_total']) }}</strong> por CNPJ</span>
                                    @if($kit['descricao'])<span class="block text-[11px] text-gray-400 mt-0.5">{{ $kit['descricao'] }}</span>@endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Card fontes --}}
                <div class="bg-white rounded border border-gray-200">
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">1 · Fontes da consulta</h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Os dados cadastrais (Receita Federal) entram grátis em toda consulta.</p>
                    </div>
                    <div class="p-4 space-y-4">
                        @forelse($gruposFontes as $chaveGrupo => $grupo)
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ $grupo['label'] }}</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($grupo['fontes'] as $fonte)
                                        <label class="fonte-opt flex items-start gap-2.5 rounded border border-gray-300 px-3 py-2.5 cursor-pointer transition-colors hover:bg-gray-50">
                                            <input type="checkbox" name="fontes[]" value="{{ $fonte['chave'] }}" data-preco="{{ $fonte['preco'] }}" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-gray-800 focus:ring-gray-500">
                                            <span class="min-w-0 flex-1">
                                                <span class="block text-[13px] font-semibold text-gray-900">{{ $fonte['nome'] }}</span>
                                                <span class="block text-[11px] text-gray-500">{{ \App\Support\Dinheiro::brl($fonte['preco']) }} por CNPJ</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Nenhuma fonte disponível no momento. Tente novamente mais tarde.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Card alvos --}}
                <div class="bg-white rounded border border-gray-200">
                    <div class="border-b border-gray-200 px-4 py-3 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">2 · Quem consultar</h2>
                            <p class="text-[11px] text-gray-500 mt-0.5">Busque nos seus participantes e clientes cadastrados (somente CNPJ).</p>
                        </div>
                        <span class="text-[11px] text-gray-500">{{ $totalParticipantes }} participantes na base</span>
                    </div>
                    <div class="p-4">
                        <input type="search" id="fontes-busca-alvo" placeholder="Buscar por razão social ou CNPJ…" autocomplete="off"
                               class="w-full border border-gray-300 rounded text-sm px-3 py-2.5 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                        <div id="fontes-resultados-alvo" class="mt-2 max-h-64 overflow-y-auto divide-y divide-gray-100 border border-gray-200 rounded hidden"></div>
                        <div id="fontes-selecionados" class="mt-3 flex flex-wrap gap-1.5"></div>
                    </div>
                </div>
            </div>

            {{-- Coluna 3: resumo --}}
            <div>
                <div class="bg-white rounded border border-gray-200 lg:sticky lg:top-4">
                    <div class="border-b border-gray-200 px-4 py-3">
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Resumo</h2>
                    </div>
                    <div class="p-4 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Fontes selecionadas</span><strong id="fontes-resumo-fontes" class="text-gray-900">0</strong></div>
                        <div class="flex justify-between"><span class="text-gray-500">CNPJs selecionados</span><strong id="fontes-resumo-alvos" class="text-gray-900">0</strong></div>
                        <div class="flex justify-between"><span class="text-gray-500">Preço por CNPJ</span><strong id="fontes-resumo-unitario" class="text-gray-900">R$ 0,00</strong></div>
                        <div id="fontes-resumo-desconto-linha" class="hidden flex justify-between"><span style="color:#047857">Desconto <span id="fontes-resumo-kit-nome"></span></span><strong id="fontes-resumo-desconto" style="color:#047857">−R$ 0,00</strong></div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 mt-2"><span class="text-gray-700 font-medium">Total</span><strong id="fontes-resumo-total" class="text-gray-900">R$ 0,00</strong></div>
                        <p id="fontes-saldo-aviso" class="hidden text-[11px] rounded px-2 py-1.5" style="background-color:#fffbeb;color:#b45309;">Saldo insuficiente para esta seleção. <a href="/app/saldo" class="underline font-medium" data-link>Adicionar saldo</a></p>
                        <button type="button" id="fontes-executar" disabled
                                class="w-full mt-2 inline-flex items-center justify-center gap-2 rounded bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed">
                            Executar consulta
                        </button>
                        <p class="text-[11px] text-gray-400">Fontes que falharem por indisponibilidade da fonte oficial são estornadas automaticamente.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php $consultaFontesJsVersion = @filemtime(public_path('js/consulta-fontes.js')) ?: time(); @endphp
<script src="/js/consulta-fontes.js?v={{ $consultaFontesJsVersion }}"></script>
<script>
    if (window.initConsultaFontes) { window.initConsultaFontes(); }
</script>
