{{-- Painel de Monitoramento: gestão dos monitorados (consulta contínua) + grupos +
     freio de consumo (trava) + acesso ao histórico. Ponto único do produto — a antiga view
     de "clientes" foi absorvida aqui (2026-07-04).
     O monitoramento NÃO tem motor próprio — cada ciclo reusa a pipeline da consulta de CNPJ
     (ConsultaLote + ProcessarConsultaJob); o link "ver lote" abre o resultado padrão. --}}
@inject('entitlements', 'App\Services\Entitlements\EntitlementService')
@php
    // Freio de consumo do auto-monitor: só faz sentido com assinatura da conta (o cap vive nela).
    $u = auth()->user();
    $assinaturaConta = $u ? $u->subscription()->first() : null;
    $capEfetivo = $u ? $entitlements->consumptionCap($u) : 0;
    $consumoCiclo = $u ? $entitlements->consumoMonitoramentoNoCiclo($u) : 0;
    $capPadrao = $u ? (int) $entitlements->planFor($u)->creditos_inclusos : 0;
    $limiteAtual = $assinaturaConta?->limite_consumo_automatico;
    $precos = app(\App\Services\PricingCatalogService::class);
    $projecaoCiclo = $consumoCiclo + collect($assinaturas)->where('vence_no_ciclo', true)->sum('custo_ciclo');
    $pctConsumo = $capEfetivo > 0 ? min(100, (int) round($consumoCiclo * 100 / $capEfetivo)) : 0;
    $corBarra = $pctConsumo >= 100 ? '#dc2626' : ($pctConsumo >= 80 ? '#d97706' : '#1f2937');
@endphp
<div class="p-4 lg:p-6 space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="text-lg font-bold text-gray-900">Monitoramento</h1>
            <p class="text-xs text-gray-500">Consulta contínua de participantes, clientes e grupos — na frequência e plano que você escolher.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/app/monitoramento/historico" data-link
                class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1.5 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Histórico
            </a>
            <button type="button" onclick="document.getElementById('modal-monitorar').classList.remove('hidden')"
                class="text-[11px] font-semibold px-3 py-1.5 rounded text-white" style="background-color: #047857">
                + Novo monitorado
            </button>
        </div>
    </div>

    <div id="painel-monitorados" class="bg-white rounded border border-gray-300 overflow-hidden">
        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-2 flex-wrap">
            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Monitorados ({{ $assinaturas->count() }})</span>
            @if($assinaturas->where('status', 'ativo')->isNotEmpty())
                <span class="text-[11px] text-gray-500">Custo mensal estimado (ativos): <span class="font-semibold text-gray-800">≈ {{ \App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) $assinaturas->where('status', 'ativo')->sum('custo_mes'))) }}/mês</span></span>
            @endif
        </div>
        @if($assinaturas->isEmpty())
            <div class="p-6 text-center text-xs text-gray-400">Nenhum monitoramento ativo. Clique em “Novo monitorado”.</div>
        @else
            {{-- Mobile: cards empilhados (a tabela de 8 colunas não cabe em tela estreita) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($assinaturas as $a)
                    @php
                        $sit = strtolower((string) ($a['ultima']['situacao'] ?? ''));
                        $hex = $sit === 'regular' ? '#047857'
                             : ($sit === 'irregular' ? '#dc2626'
                             : ($sit === 'atencao' ? '#d97706'
                             : (($a['ultima']['status'] ?? '') === 'erro' ? '#b45309' : '#9ca3af')));
                    @endphp
                    <div class="px-4 py-3 space-y-1.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <span class="text-xs font-medium text-gray-800">{{ $a['alvo_nome'] }}</span>
                                @if($a['alvo_tipo'] === 'grupo')
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px] ml-1" style="background-color: #6366f1">grupo · {{ $a['membros'] }} {{ $a['membros'] === 1 ? 'membro' : 'membros' }}</span>
                                @else
                                    <span class="block text-[11px] text-gray-400">{{ $a['alvo_doc'] }}</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-1">
                                <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px]" style="background-color: {{ $a['status'] === 'ativo' ? '#047857' : '#9ca3af' }}">{{ $a['status'] }}</span>
                                @if(($a['status'] ?? '') === 'pausado' && !empty($a['pausada_motivo']))
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px]" style="background-color: #b45309">{{ ['saldo' => 'sem saldo', 'falhas' => 'falhas seguidas', 'manual' => 'pausa manual'][$a['pausada_motivo']] ?? $a['pausada_motivo'] }}</span>
                                @endif
                                @if(!empty($a['aguardando_ciclo']))
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px]" style="background-color: #6b7280">aguardando próximo ciclo</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-500">
                            <span>Plano: <span class="text-gray-700">{{ $a['plano_nome'] }}</span></span>
                            <span>Frequência: <span class="text-gray-700 capitalize">{{ $a['frequencia'] }}</span></span>
                            <span>Custo/ciclo: <span class="text-gray-700">{{ \App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) $a['custo_ciclo'])) }}</span></span>
                            <span>Custo/mês: <span class="text-gray-700">≈ {{ \App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) $a['custo_mes'])) }}</span></span>
                            <span>Próxima: <span class="text-gray-700">{{ $a['proxima_em'] ?? '—' }}</span></span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-gray-500">
                            <span>Última execução:</span>
                            @if($a['ultima'])
                                <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px] capitalize" style="background-color: {{ $hex }}">{{ $a['ultima']['situacao'] ?? $a['ultima']['status'] }}</span>
                                <span class="text-[10px] text-gray-400">{{ $a['ultima']['executado_em'] }}</span>
                                @if($a['ultima']['lote_id'])
                                    <a href="{{ route('app.consulta.lote.show', ['id' => $a['ultima']['lote_id']]) }}" data-link class="text-[10px] underline" style="color: #2563eb">ver lote</a>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-x-3 gap-y-1 pt-1">
                            @if($a['status'] === 'ativo')
                                <button type="button" onclick="painelAcao('{{ route('app.monitoramento.assinatura.pausar', ['id' => $a['id']]) }}', 'POST')" class="text-[11px] underline text-gray-600">pausar</button>
                            @else
                                <button type="button" onclick="painelAcao('{{ route('app.monitoramento.assinatura.reativar', ['id' => $a['id']]) }}', 'POST')" class="text-[11px] underline" style="color: #047857">reativar</button>
                            @endif
                            <button type="button" onclick="if(confirm('Cancelar este monitoramento?')) painelAcao('{{ route('app.monitoramento.assinatura.cancelar', ['id' => $a['id']]) }}', 'DELETE')" class="text-[11px] underline" style="color: #dc2626">cancelar</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="overflow-x-auto hidden md:block">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-[10px] text-gray-400 uppercase tracking-wide">
                            <th class="text-left py-2 px-3">Alvo</th>
                            <th class="text-left px-2">Plano</th>
                            <th class="text-left px-2">Frequência</th>
                            <th class="text-left px-2">Última execução</th>
                            <th class="text-left px-2">Próxima</th>
                            <th class="text-right px-2">Custo/ciclo</th>
                            <th class="text-right px-2">Custo/mês</th>
                            <th class="text-left px-2">Status</th>
                            <th class="text-right px-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($assinaturas as $a)
                            <tr>
                                <td class="py-2 px-3">
                                    <span class="font-medium text-gray-800">{{ $a['alvo_nome'] }}</span>
                                    @if($a['alvo_tipo'] === 'grupo')
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px] ml-1" style="background-color: #6366f1">grupo · {{ $a['membros'] }} {{ $a['membros'] === 1 ? 'membro' : 'membros' }}</span>
                                    @else
                                        <span class="block text-[11px] text-gray-400">{{ $a['alvo_doc'] }}</span>
                                    @endif
                                </td>
                                <td class="px-2 text-gray-700">{{ $a['plano_nome'] }}</td>
                                <td class="px-2 text-gray-700 capitalize">{{ $a['frequencia'] }}</td>
                                <td class="px-2">
                                    @if($a['ultima'])
                                        @php
                                            $sit = strtolower((string) ($a['ultima']['situacao'] ?? ''));
                                            $hex = $sit === 'regular' ? '#047857'
                                                 : ($sit === 'irregular' ? '#dc2626'
                                                 : ($sit === 'atencao' ? '#d97706'
                                                 : ($a['ultima']['status'] === 'erro' ? '#b45309' : '#9ca3af')));
                                        @endphp
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px] capitalize" style="background-color: {{ $hex }}">{{ $a['ultima']['situacao'] ?? $a['ultima']['status'] }}</span>
                                        <span class="block text-[10px] text-gray-400 mt-0.5">{{ $a['ultima']['executado_em'] }}</span>
                                        @if($a['ultima']['lote_id'])
                                            <a href="{{ route('app.consulta.lote.show', ['id' => $a['ultima']['lote_id']]) }}" data-link class="text-[10px] underline" style="color: #2563eb">ver lote</a>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-2 text-gray-500">{{ $a['proxima_em'] ?? '—' }}</td>
                                <td class="px-2 text-right text-gray-700 whitespace-nowrap">{{ \App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) $a['custo_ciclo'])) }}</td>
                                <td class="px-2 text-right text-gray-700 whitespace-nowrap">≈ {{ \App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) $a['custo_mes'])) }}</td>
                                <td class="px-2">
                                    <div class="flex flex-wrap items-center gap-1">
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px]" style="background-color: {{ $a['status'] === 'ativo' ? '#047857' : '#9ca3af' }}">{{ $a['status'] }}</span>
                                        @if(($a['status'] ?? '') === 'pausado' && !empty($a['pausada_motivo']))
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px]" style="background-color: #b45309">{{ ['saldo' => 'sem saldo', 'falhas' => 'falhas seguidas', 'manual' => 'pausa manual'][$a['pausada_motivo']] ?? $a['pausada_motivo'] }}</span>
                                        @endif
                                        @if(!empty($a['aguardando_ciclo']))
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px]" style="background-color: #6b7280">aguardando próximo ciclo</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 text-right whitespace-nowrap">
                                    @if($a['status'] === 'ativo')
                                        <button type="button" onclick="painelAcao('{{ route('app.monitoramento.assinatura.pausar', ['id' => $a['id']]) }}', 'POST')" class="text-[11px] underline text-gray-600">pausar</button>
                                    @else
                                        <button type="button" onclick="painelAcao('{{ route('app.monitoramento.assinatura.reativar', ['id' => $a['id']]) }}', 'POST')" class="text-[11px] underline" style="color: #047857">reativar</button>
                                    @endif
                                    <button type="button" onclick="if(confirm('Cancelar este monitoramento?')) painelAcao('{{ route('app.monitoramento.assinatura.cancelar', ['id' => $a['id']]) }}', 'DELETE')" class="text-[11px] underline ml-2" style="color: #dc2626">cancelar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Freio de consumo do auto-monitor (trava). Migrado da antiga view "clientes" (2026-07-04). --}}
    <div id="painel-trava" class="bg-white rounded border border-gray-300 overflow-hidden">
        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Freio de consumo do auto-monitor</span>
        </div>
        <div class="p-4">
            <p class="text-xs text-gray-500 mb-4 max-w-2xl">Defina o limite de gasto em R$ que o monitoramento automático pode consumir por ciclo. Ao atingir o limite, as próximas consultas automáticas aguardam o próximo ciclo — nada é pausado nem cancelado, e elas retomam sozinhas. Seu saldo fica protegido de consumo inesperado.</p>
            @if(! $assinaturaConta)
                <div class="bg-blue-50 border border-blue-200 rounded p-3 text-xs text-gray-700 flex items-start gap-2">
                    <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="font-semibold text-gray-800 mb-0.5">O freio se ativa com uma assinatura de monitoramento</p>
                        <p>Planos pagos de monitoramento incluem uma cota mensal em R$, e é sobre ela que o freio atua. No seu plano atual, o monitoramento automático básico (cadastral) não tem custo adicional — então não há limite a definir.</p>
                        <a href="/app/planos" data-link class="inline-block mt-1 text-blue-600 hover:underline font-semibold">Ver planos de monitoramento →</a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                    <div class="bg-gray-50 border border-gray-200 rounded p-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Teto efetivo</p>
                        <p class="text-base font-bold text-gray-900">{{ \App\Support\Dinheiro::brl($precos->creditsToCurrency((int) $capEfetivo)) }} <span class="text-[11px] font-normal text-gray-400">/ciclo</span></p>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded p-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consumido no ciclo</p>
                        <p class="text-base font-bold text-gray-900">{{ \App\Support\Dinheiro::brl($precos->creditsToCurrency((int) $consumoCiclo)) }}</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded p-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Padrão do plano</p>
                        <p class="text-base font-bold text-gray-900">{{ \App\Support\Dinheiro::brl($precos->creditsToCurrency((int) $capPadrao)) }}</p>
                    </div>
                </div>
                @if($capEfetivo > 0)
                <div class="mb-4">
                    <div class="flex items-center justify-between text-[11px] text-gray-500 mb-1">
                        <span>Consumo do ciclo: {{ $pctConsumo }}%</span>
                        <span>Projetado até o fim do ciclo: {{ \App\Support\Dinheiro::brl($precos->creditsToCurrency((int) $projecaoCiclo)) }}</span>
                    </div>
                    <div class="w-full h-2 rounded bg-gray-200 overflow-hidden">
                        <div class="h-2 rounded" style="width: {{ $pctConsumo }}%; background-color: {{ $corBarra }}"></div>
                    </div>
                </div>
                @endif
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-[11px] text-gray-500 mb-1">Teto personalizado (R$)</label>
                        {{-- O usuário digita R$; o JS converte pra créditos (unidade interna do backend) no envio. --}}
                        <input type="number" id="input-limite-consumo" min="0" max="{{ $precos->creditsToCurrency(1000000) }}" step="0.01"
                               value="{{ $limiteAtual !== null && $limiteAtual !== '' ? $precos->creditsToCurrency((int) $limiteAtual) : '' }}"
                               data-credit-unit-price="{{ $precos->creditUnitPrice() }}"
                               placeholder="Padrão do plano" class="text-[13px] py-2.5 px-3 border border-gray-300 rounded w-48">
                    </div>
                    <button id="btn-salvar-limite" type="button" class="px-3 py-2.5 rounded bg-gray-800 hover:bg-gray-700 text-white text-[13px] font-semibold transition">Salvar</button>
                    <span id="limite-feedback" class="text-[12px]"></span>
                </div>
                <p class="text-[11px] text-gray-400 mt-2">Deixe em branco para usar o padrão do plano ({{ \App\Support\Dinheiro::brl($precos->creditsToCurrency((int) $capPadrao)) }} em saldo incluso). Use <span class="font-semibold">0</span> para não impor limite (o saldo passa a ser o único controle).</p>
            @endif
        </div>
    </div>

    <div id="painel-grupos" class="bg-white rounded border border-gray-300 overflow-hidden">
        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Grupos ({{ $grupos->count() }})</span>
            <button type="button" onclick="painelGrupoModal(null, '')" class="text-[11px] font-semibold px-2.5 py-1 rounded text-white" style="background-color: #334155">+ Novo grupo</button>
        </div>
        @if($grupos->isEmpty())
            <div class="p-6 text-center text-xs text-gray-400">Nenhum grupo. Grupos permitem monitorar vários participantes de uma vez — crie um em “Novo monitorado” (opção “Criar novo grupo”) ou em “+ Novo grupo”.</div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($grupos as $g)
                    <div class="px-4 py-2.5 flex items-center justify-between gap-2 flex-wrap">
                        <div>
                            <span class="text-xs font-medium text-gray-800">{{ $g->nome }}</span>
                            <span class="text-[11px] text-gray-400 ml-2">{{ $g->participantes_count }} {{ $g->participantes_count === 1 ? 'membro' : 'membros' }}</span>
                            @if(in_array($g->id, $gruposMonitorados))
                                <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px] ml-1" style="background-color: #047857">monitorado</span>
                            @endif
                        </div>
                        <div class="whitespace-nowrap">
                            <button type="button" onclick="painelGrupoModal({{ $g->id }}, {{ json_encode($g->nome) }})" class="text-[11px] underline" style="color: #2563eb">gerenciar</button>
                            <button type="button" onclick="if(confirm('Excluir o grupo? Um monitoramento ativo dele será cancelado.')) painelAcao('{{ route('app.monitoramento.grupos.excluir', ['id' => $g->id]) }}', 'DELETE')" class="text-[11px] underline ml-2" style="color: #dc2626">excluir</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal Novo monitorado: fluxo único — busca alvos (multi-seleção) e, opcionalmente,
         agrupa (grupo existente ou novo). Assinatura de grupo é dinâmica: membros avaliados
         a cada ciclo. --}}
    <div id="modal-monitorar" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(17,24,39,.5)">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-y-auto" style="max-height: 90vh">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-800">Novo monitorado</span>
                <button type="button" onclick="document.getElementById('modal-monitorar').classList.add('hidden')" class="text-gray-400 text-xl leading-none">&times;</button>
            </div>
            <form id="form-monitorar" class="p-4 space-y-3">
                <div>
                    <label class="text-[11px] text-gray-500 block mb-1">Buscar em</label>
                    <select id="mon-tipo" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" onchange="painelTipoChange()">
                        <option value="participante">Participantes</option>
                        <option value="cliente">Clientes</option>
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Pode misturar: alterne a fonte e selecione participantes e clientes no mesmo pedido.</p>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[11px] text-gray-500">Buscar por nome ou CNPJ</label>
                        <button type="button" onclick="painelVerTodos('mon')" class="text-[11px] underline" style="color: #2563eb">ver todos</button>
                    </div>
                    <input id="mon-busca" type="text" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="Digite pelo menos 3 caracteres" oninput="painelBuscar()">
                    <div id="mon-busca-resultados" class="mt-1 border border-gray-200 rounded divide-y divide-gray-100 overflow-y-auto hidden" style="max-height: 14rem"></div>
                    <div id="mon-selecionados-bar" class="mt-1.5 items-center justify-between gap-2 flex-wrap" style="display: none">
                        <span id="mon-selecionados-count" class="text-[11px] text-gray-500"></span>
                        <span class="flex items-center gap-3">
                            <button type="button" id="mon-sugerir-grupo" onclick="painelSugerirGrupo()" class="text-[11px] underline font-semibold" style="color: #047857; display: none"></button>
                            <button type="button" onclick="monLimparSelecao()" class="text-[11px] underline text-gray-600">limpar seleção</button>
                        </span>
                    </div>
                    {{-- Chips com teto de altura + scroll: adicionar muitos CNPJs não estica o modal. --}}
                    <div id="mon-selecionados" class="mt-1 flex flex-wrap gap-1.5 overflow-y-auto" style="max-height: 6.5rem"></div>
                </div>
                <div id="mon-grupo-bloco">
                    <label class="text-[11px] text-gray-500 block mb-1">Grupo (opcional)</label>
                    <select id="mon-grupo-opcao" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" onchange="painelGrupoChange()">
                        <option value="">Sem grupo — monitorar individualmente</option>
                        @foreach($grupos as $g)
                            <option value="{{ $g->id }}" data-monitorado="{{ in_array($g->id, $gruposMonitorados) ? 1 : 0 }}">{{ $g->nome }} — {{ $g->participantes_count }} {{ $g->participantes_count === 1 ? 'membro' : 'membros' }}{{ in_array($g->id, $gruposMonitorados) ? ' · já monitorado' : '' }}</option>
                        @endforeach
                        <option value="novo">+ Criar novo grupo…</option>
                    </select>
                    <input id="mon-grupo-nome" type="text" class="hidden mt-2 w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="Nome do novo grupo">
                    <p id="mon-grupo-hint" class="hidden text-[11px] text-gray-400 mt-1"></p>
                </div>
                <div id="mon-plano-freq" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] text-gray-500 block mb-1">Plano</label>
                        <select id="mon-plano" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                            @foreach($planos as $pl)
                                <option value="{{ $pl['id'] }}">{{ $pl['nome'] }} — {{ \App\Support\Dinheiro::brl(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) $pl['custo'])) }}/CNPJ</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] text-gray-500 block mb-1">Frequência</label>
                        <select id="mon-frequencia" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                            <option value="semanal">Semanal</option>
                            <option value="quinzenal">Quinzenal</option>
                            <option value="mensal" selected>Mensal</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-monitorar').classList.add('hidden')" class="text-xs px-3 py-1.5 text-gray-600">Cancelar</button>
                    <button type="submit" class="text-xs px-3 py-1.5 rounded text-white" style="background-color: #047857">Monitorar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal do grupo: criar, renomear e gerenciar membros num lugar só. Criou o grupo →
         o modal vira edição na hora, pra já adicionar membros sem sair dele. --}}
    <div id="modal-grupo" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(17,24,39,.5)">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-y-auto" style="max-height: 90vh">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <span id="grupo-titulo" class="text-sm font-semibold text-gray-800">Novo grupo</span>
                <button type="button" onclick="grupoFechar()" class="text-gray-400 text-xl leading-none">&times;</button>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="text-[11px] text-gray-500 block mb-1">Nome do grupo</label>
                    <div class="flex gap-2">
                        <input id="grupo-nome" type="text" class="flex-1 text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="Ex.: Fornecedores críticos"
                            onkeydown="if (event.key === 'Enter') { event.preventDefault(); grupoSalvar(); }">
                        <button type="button" id="grupo-salvar" onclick="grupoSalvar()" class="text-xs px-3 py-1.5 rounded text-white whitespace-nowrap" style="background-color: #047857">Criar grupo</button>
                    </div>
                </div>
                <div id="grupo-membros-sec" class="hidden space-y-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[11px] text-gray-500">Adicionar participante</label>
                            <button type="button" onclick="painelVerTodos('membros')" class="text-[11px] underline" style="color: #2563eb">ver todos</button>
                        </div>
                        <input id="membros-busca" type="text" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="Digite pelo menos 3 caracteres" oninput="membrosBuscar()">
                        <div id="membros-busca-resultados" class="mt-1 border border-gray-200 rounded divide-y divide-gray-100 overflow-y-auto hidden" style="max-height: 14rem"></div>
                    </div>
                    <div id="membros-lista" class="border border-gray-200 rounded divide-y divide-gray-100 overflow-y-auto" style="max-height: 15rem">
                        <div class="px-3 py-2 text-xs text-gray-400">Carregando…</div>
                    </div>
                </div>
                <p class="text-[11px] text-gray-400">O monitoramento de grupo é dinâmico: quem entra no grupo passa a ser consultado no próximo ciclo; o custo acompanha o nº de membros.</p>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="grupoFechar()" class="text-xs px-3 py-1.5 text-gray-600">Fechar</button>
            </div>
        </div>
    </div>

    <script>
    function painelCsrf() { return document.querySelector('meta[name=csrf-token]').content; }
    function painelAcao(url, method) {
        fetch(url, { method: method, headers: { 'X-CSRF-TOKEN': painelCsrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json().catch(function () { return {}; }); })
            .then(function () { window.location.reload(); })
            .catch(function () { alert('Falha de rede.'); });
    }
    // POST/PUT/DELETE JSON com erro legível — rejeita com a mensagem do backend.
    function painelPost(url, method, body) {
        return fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': painelCsrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: body ? JSON.stringify(body) : undefined
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) {
                if (!r.ok || j.success === false) { throw new Error((j && (j.error || j.message)) || 'Falha na operação.'); }
                return j;
            });
        });
    }

    // Seleção mista: chips carregam o tipo (participante escuro, cliente teal) e o
    // backend aceita participantes[] + clientes[] no mesmo request. Só grupo é exclusivo
    // — e recebe apenas os participantes da seleção.
    var monSelecionados = [];
    function monLimparSelecao() {
        monSelecionados = [];
        monRenderSelecionados();
    }
    function monRenderSelecionados() {
        var nParticipantes = monSelecionados.filter(function (s) { return s.tipo === 'participante'; }).length;
        var nClientes = monSelecionados.length - nParticipantes;
        var partes = [];
        if (nParticipantes) { partes.push(nParticipantes + ' participante(s)'); }
        if (nClientes) { partes.push(nClientes + ' cliente(s)'); }
        var bar = document.getElementById('mon-selecionados-bar');
        bar.style.display = monSelecionados.length ? 'flex' : 'none';
        document.getElementById('mon-selecionados-count').textContent = partes.join(' · ');
        var box = document.getElementById('mon-selecionados');
        box.innerHTML = '';
        monSelecionados.forEach(function (s, i) {
            var chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] text-white';
            chip.style.backgroundColor = s.tipo === 'cliente' ? '#0e7490' : '#334155';
            chip.title = s.tipo === 'cliente' ? 'Cliente' : 'Participante';
            chip.appendChild(document.createTextNode(s.nome));
            var x = document.createElement('button');
            x.type = 'button';
            x.textContent = '×';
            x.className = 'leading-none font-bold';
            x.onclick = function () { monSelecionados.splice(i, 1); monRenderSelecionados(); };
            chip.appendChild(x);
            box.appendChild(chip);
        });
        painelAtualizarGrupoBloco();
        painelAtualizarSugestaoGrupo();
    }
    function painelAtualizarSugestaoGrupo() {
        // Selecionou 2+ participantes e ainda não escolheu grupo? Sugere criar um com eles.
        var nP = monSelecionados.filter(function (s) { return s.tipo === 'participante'; }).length;
        var semGrupo = document.getElementById('mon-grupo-opcao').value === '';
        var btn = document.getElementById('mon-sugerir-grupo');
        btn.textContent = 'criar grupo com estes ' + nP + ' participantes?';
        btn.style.display = (nP >= 2 && semGrupo) ? '' : 'none';
    }
    function painelSugerirGrupo() {
        document.getElementById('mon-grupo-opcao').value = 'novo';
        painelGrupoChange();
        document.getElementById('mon-grupo-nome').focus();
    }
    function painelAtualizarGrupoBloco() {
        // Grupo só faz sentido com participantes: some quando a busca está em clientes
        // E não há participante selecionado.
        var tipo = document.getElementById('mon-tipo').value;
        var temParticipante = monSelecionados.some(function (s) { return s.tipo === 'participante'; });
        document.getElementById('mon-grupo-bloco').classList.toggle('hidden', tipo !== 'participante' && !temParticipante);
    }
    function painelTipoChange() {
        // Trocar a fonte da busca NÃO limpa a seleção — é assim que se mistura
        // participantes e clientes no mesmo pedido.
        document.getElementById('mon-busca').value = '';
        document.getElementById('mon-busca-resultados').classList.add('hidden');
        painelAtualizarGrupoBloco();
    }
    function painelGrupoChange() {
        var sel = document.getElementById('mon-grupo-opcao');
        var opt = sel.selectedOptions[0];
        var hint = document.getElementById('mon-grupo-hint');
        var monitorado = opt && opt.dataset.monitorado === '1';
        document.getElementById('mon-grupo-nome').classList.toggle('hidden', sel.value !== 'novo');
        // Grupo já monitorado: só adiciona membros à assinatura existente — plano/frequência não se aplicam.
        document.getElementById('mon-plano-freq').classList.toggle('hidden', monitorado);
        if (sel.value === 'novo') {
            hint.textContent = 'Os participantes selecionados acima formarão o grupo. O monitoramento de grupo é dinâmico: quem entrar depois passa a ser consultado no próximo ciclo, e o custo acompanha o nº de membros.';
        } else if (monitorado) {
            hint.textContent = 'Este grupo já está monitorado — os participantes selecionados acima serão adicionados a ele e entram no próximo ciclo.';
        } else if (sel.value !== '') {
            hint.textContent = 'Os selecionados acima serão adicionados ao grupo; o monitoramento cobre todos os membros a cada ciclo.';
        } else {
            hint.textContent = '';
        }
        hint.classList.toggle('hidden', hint.textContent === '');
        painelAtualizarSugestaoGrupo();
    }
    // ── Lista de alvos (busca e "ver todos") ─────────────────────────────────
    // Compartilhada pelos modais "Novo monitorado" (mon) e do grupo (membros).
    // Participantes vêm do endpoint da consulta, ordenados por volume movimentado
    // (ordenar=valor, default do backend) e com o volume exibido ao lado do CNPJ.
    var painelListaCfg = {
        mon: {
            input: 'mon-busca',
            box: 'mon-busca-resultados',
            tipo: function () { return document.getElementById('mon-tipo').value; },
            escolher: function (item) {
                var tipo = document.getElementById('mon-tipo').value;
                if (!monSelecionados.some(function (s) { return s.id === item.id && s.tipo === tipo; })) {
                    monSelecionados.push({ id: item.id, nome: item.nome, tipo: tipo });
                }
                monRenderSelecionados();
            }
        },
        membros: {
            input: 'membros-busca',
            box: 'membros-busca-resultados',
            tipo: function () { return 'participante'; },
            escolher: function (item) { membrosAssociar([item.id], 'adicionar'); }
        }
    };
    function painelCarregarLista(key, opts) {
        opts = opts || {};
        var cfg = painelListaCfg[key];
        var tipo = cfg.tipo();
        var box = document.getElementById(cfg.box);
        var q = document.getElementById(cfg.input).value.trim();
        if (!opts.todos && q.length < 3) { box.classList.add('hidden'); return; }
        var params = ['ordenar=valor', 'per_page=50', 'page=' + (opts.page || 1)];
        if (!opts.todos) { params.push('busca=' + encodeURIComponent(q)); }
        var url = (tipo === 'cliente'
            ? '{{ route('app.consulta.nova.clientes') }}'
            : '{{ route('app.consulta.nova.participantes') }}') + '?' + params.join('&');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                var itens = (j.data || []);
                var mais = box.querySelector('[data-mais]');
                if (mais) { mais.remove(); }
                if (!opts.append) { box.innerHTML = ''; }
                itens.forEach(function (it) {
                    var nome = it.razao_social || it.nome || '—';
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'w-full flex items-center justify-between gap-2 text-left px-3 py-1.5 text-xs hover:bg-gray-50';
                    var esq = document.createElement('span');
                    esq.className = 'text-gray-700 truncate min-w-0';
                    esq.textContent = nome + ' · ' + (it.documento_formatado || it.documento || '—');
                    var dir = document.createElement('span');
                    dir.className = 'text-gray-400 whitespace-nowrap text-[11px]';
                    dir.textContent = tipo === 'cliente'
                        ? ((it.participantes_count != null ? it.participantes_count : 0) + ' participante(s)')
                        : (it.fiscal_resumo && it.fiscal_resumo.total_formatado ? it.fiscal_resumo.total_formatado : 'sem movimentação');
                    b.appendChild(esq);
                    b.appendChild(dir);
                    b.onclick = function () {
                        cfg.escolher({ id: it.id, nome: nome });
                        if (opts.todos) {
                            // Multi-seleção: a lista fica aberta; o item clicado é marcado.
                            b.disabled = true;
                            b.classList.add('opacity-50');
                            esq.textContent = '✓ ' + esq.textContent;
                        } else {
                            document.getElementById(cfg.input).value = '';
                            box.classList.add('hidden');
                        }
                    };
                    box.appendChild(b);
                });
                if (!box.children.length) {
                    box.innerHTML = '<div class="px-3 py-1.5 text-xs text-gray-400">Nenhum resultado.</div>';
                }
                var pag = j.pagination;
                if (pag && pag.current_page < pag.last_page) {
                    var m = document.createElement('button');
                    m.type = 'button';
                    m.dataset.mais = '1';
                    m.className = 'w-full text-center px-3 py-1.5 text-[11px] underline';
                    m.style.color = '#2563eb';
                    m.textContent = 'Carregar mais (' + (pag.total - pag.current_page * pag.per_page) + ' restantes)';
                    m.onclick = function () { painelCarregarLista(key, { todos: opts.todos, page: pag.current_page + 1, append: true }); };
                    box.appendChild(m);
                }
                box.classList.remove('hidden');
            })
            .catch(function () {
                box.innerHTML = '<div class="px-3 py-1.5 text-xs text-red-600">Falha ao carregar a lista.</div>';
                box.classList.remove('hidden');
            });
    }
    function painelVerTodos(key) {
        document.getElementById(painelListaCfg[key].input).value = '';
        painelCarregarLista(key, { todos: true });
    }
    var painelBuscaTimer = null;
    function painelBuscar() {
        clearTimeout(painelBuscaTimer);
        painelBuscaTimer = setTimeout(function () { painelCarregarLista('mon'); }, 300);
    }
    (function () {
        var form = document.getElementById('form-monitorar');
        if (!form || form.dataset.bound) return;
        form.dataset.bound = '1';
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var pIds = monSelecionados.filter(function (s) { return s.tipo === 'participante'; }).map(function (s) { return s.id; });
            var cIds = monSelecionados.filter(function (s) { return s.tipo !== 'participante'; }).map(function (s) { return s.id; });
            var assinatura = function (extra) {
                extra.plano_id = parseInt(document.getElementById('mon-plano').value, 10);
                extra.frequencia = document.getElementById('mon-frequencia').value;
                return painelPost('{{ route('app.monitoramento.assinatura.criar') }}', 'POST', extra).then(function (j) {
                    if (j && j.aviso) { alert(j.aviso); }
                    return j;
                });
            };
            var associar = function (grupoId) {
                return painelPost('{{ route('app.participantes.associar-grupo') }}', 'POST', { grupo_id: grupoId, participantes: pIds });
            };
            // grupo_id não combina com clientes[] no mesmo request (regra do backend);
            // clientes da seleção mista viram uma 2ª chamada individual.
            var clientesDepois = function () { return cIds.length ? assinatura({ clientes: cIds }) : null; };
            var sel = document.getElementById('mon-grupo-opcao');
            var grupoVisivel = !document.getElementById('mon-grupo-bloco').classList.contains('hidden');
            var gsel = grupoVisivel ? sel.value : '';
            var fluxo;
            if (gsel === '') {
                if (!pIds.length && !cIds.length) { alert('Busque e selecione ao menos um participante ou cliente.'); return; }
                var payload = {};
                if (pIds.length) { payload.participantes = pIds; }
                if (cIds.length) { payload.clientes = cIds; }
                fluxo = assinatura(payload);
            } else if (gsel === 'novo') {
                var nome = document.getElementById('mon-grupo-nome').value.trim();
                if (!nome) { alert('Dê um nome ao novo grupo.'); return; }
                if (!pIds.length) { alert('Selecione os participantes que formarão o grupo (clientes não entram em grupos).'); return; }
                fluxo = painelPost('{{ route('app.monitoramento.grupos.criar') }}', 'POST', { nome: nome })
                    .then(function (j) {
                        var gid = j.grupo.id;
                        return associar(gid).then(function () { return assinatura({ grupo_id: gid }); });
                    })
                    .then(clientesDepois);
            } else {
                var gid = parseInt(gsel, 10);
                var jaMonitorado = sel.selectedOptions[0].dataset.monitorado === '1';
                if (jaMonitorado && !pIds.length && !cIds.length) { alert('Este grupo já está monitorado. Selecione participantes para adicionar a ele.'); return; }
                // Grupo existente sem novos participantes = monitorar o grupo como está.
                fluxo = (pIds.length ? associar(gid) : Promise.resolve())
                    .then(function () { return jaMonitorado ? null : assinatura({ grupo_id: gid }); })
                    .then(clientesDepois);
            }
            fluxo.then(function () { window.location.reload(); })
                 .catch(function (e) { alert(e.message || 'Falha de rede.'); });
        });
    })();

    // ── Modal do grupo (criar / renomear / membros) ──────────────────────────
    var membrosGrupoId = null;   // null = criando grupo novo
    var membrosAlterado = false;
    function painelGrupoModal(id, nome) {
        membrosGrupoId = id || null;
        membrosAlterado = false;
        document.getElementById('grupo-titulo').textContent = id ? 'Grupo — ' + nome : 'Novo grupo';
        document.getElementById('grupo-nome').value = nome || '';
        document.getElementById('grupo-salvar').textContent = id ? 'Salvar nome' : 'Criar grupo';
        document.getElementById('grupo-membros-sec').classList.toggle('hidden', !id);
        document.getElementById('membros-busca').value = '';
        document.getElementById('membros-busca-resultados').classList.add('hidden');
        document.getElementById('modal-grupo').classList.remove('hidden');
        if (id) { membrosCarregar(); } else { document.getElementById('grupo-nome').focus(); }
    }
    function grupoFechar() {
        document.getElementById('modal-grupo').classList.add('hidden');
        // Nome/contagem de membros/custo por ciclo mudaram — recarrega o painel.
        if (membrosAlterado) { window.location.reload(); }
    }
    function grupoSalvar() {
        var nome = document.getElementById('grupo-nome').value.trim();
        if (!nome) { alert('Dê um nome ao grupo.'); return; }
        var btn = document.getElementById('grupo-salvar');
        if (!membrosGrupoId) {
            painelPost('{{ route('app.monitoramento.grupos.criar') }}', 'POST', { nome: nome })
                .then(function (j) {
                    // Criou → vira edição na hora: já dá pra adicionar membros sem fechar o modal.
                    membrosGrupoId = j.grupo.id;
                    membrosAlterado = true;
                    document.getElementById('grupo-titulo').textContent = 'Grupo — ' + nome;
                    btn.textContent = 'Salvar nome';
                    document.getElementById('grupo-membros-sec').classList.remove('hidden');
                    membrosCarregar();
                })
                .catch(function (e) { alert(e.message || 'Falha de rede.'); });
        } else {
            painelPost('/app/monitoramento/grupos/' + membrosGrupoId, 'PUT', { nome: nome })
                .then(function () {
                    membrosAlterado = true;
                    document.getElementById('grupo-titulo').textContent = 'Grupo — ' + nome;
                    btn.textContent = 'Salvo ✓';
                    setTimeout(function () { btn.textContent = 'Salvar nome'; }, 1500);
                })
                .catch(function (e) { alert(e.message || 'Falha de rede.'); });
        }
    }
    function membrosCarregar() {
        var lista = document.getElementById('membros-lista');
        lista.innerHTML = '<div class="px-3 py-2 text-xs text-gray-400">Carregando…</div>';
        fetch('/app/monitoramento/grupos/' + membrosGrupoId + '/participantes', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) { return r.json(); })
          .then(function (j) {
              var itens = j.participantes || [];
              lista.innerHTML = '';
              if (!itens.length) {
                  lista.innerHTML = '<div class="px-3 py-2 text-xs text-gray-400">Nenhum membro. Use a busca acima para adicionar.</div>';
                  return;
              }
              itens.forEach(function (p) {
                  var row = document.createElement('div');
                  row.className = 'px-3 py-1.5 flex items-center justify-between gap-2 text-xs';
                  var info = document.createElement('span');
                  info.className = 'text-gray-700 truncate min-w-0';
                  info.textContent = p.nome + (p.documento ? ' · ' + p.documento : '');
                  var btn = document.createElement('button');
                  btn.type = 'button';
                  btn.className = 'text-[11px] underline';
                  btn.style.color = '#dc2626';
                  btn.textContent = 'remover';
                  btn.onclick = function () { membrosAssociar([p.id], 'remover'); };
                  row.appendChild(info);
                  row.appendChild(btn);
                  lista.appendChild(row);
              });
          })
          .catch(function () { lista.innerHTML = '<div class="px-3 py-2 text-xs text-red-600">Falha ao carregar membros.</div>'; });
    }
    function membrosAssociar(participanteIds, acao) {
        painelPost('{{ route('app.participantes.associar-grupo') }}', 'POST', {
            grupo_id: membrosGrupoId,
            participantes: participanteIds,
            acao: acao
        }).then(function () { membrosAlterado = true; membrosCarregar(); })
          .catch(function (e) { alert(e.message || 'Falha de rede.'); });
    }
    var membrosBuscaTimer = null;
    function membrosBuscar() {
        clearTimeout(membrosBuscaTimer);
        membrosBuscaTimer = setTimeout(function () { painelCarregarLista('membros'); }, 300);
    }
    </script>

    @if($assinaturaConta)
    <script>
    (function () {
        // Freio de consumo do auto-monitor: salvar teto. Listener no botão do render atual
        // (SPA reinsere o partial → elemento antigo some, sem vazamento).
        var btn = document.getElementById('btn-salvar-limite');
        var input = document.getElementById('input-limite-consumo');
        var feedback = document.getElementById('limite-feedback');
        if (!btn || !input) { return; }

        var token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        // Input em R$; backend fala créditos → converter na ida (÷ preço unitário) e na volta (×).
        var unitPrice = parseFloat(input.dataset.creditUnitPrice) || 0.20;
        var brl = function (creditos) {
            return 'R$ ' + (Math.round(creditos * unitPrice * 100) / 100)
                .toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        btn.addEventListener('click', function () {
            var raw = input.value.trim().replace(',', '.');
            var limite = raw === '' ? null : Math.round(parseFloat(raw) / unitPrice);
            if (limite !== null && (isNaN(limite) || limite < 0 || limite > 1000000)) {
                feedback.textContent = 'Valor inválido.';
                feedback.style.color = '#dc2626';
                return;
            }
            btn.disabled = true;
            feedback.textContent = 'Salvando…';
            feedback.style.color = '#6b7280';
            fetch('{{ route('app.monitoramento.limite-consumo') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: JSON.stringify({ limite: limite })
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
              .then(function (res) {
                  if (res.ok && res.j.success) {
                      feedback.textContent = '✓ Teto atualizado (' + brl(Number(res.j.cap_efetivo)) + '/ciclo).';
                      feedback.style.color = '#047857';
                  } else {
                      feedback.textContent = (res.j && (res.j.error || res.j.message)) || 'Não foi possível salvar.';
                      feedback.style.color = '#dc2626';
                  }
              }).catch(function () {
                  feedback.textContent = 'Erro de conexão.';
                  feedback.style.color = '#dc2626';
              }).finally(function () { btn.disabled = false; });
        });
    })();
    </script>
    @endif
</div>
