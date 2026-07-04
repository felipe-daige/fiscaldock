{{-- Painel de Monitoramento: gestão dos monitorados (consulta contínua) + grupos.
     O monitoramento NÃO tem motor próprio — cada ciclo reusa a pipeline da consulta de CNPJ
     (ConsultaLote + ProcessarConsultaJob); o link "ver lote" abre o resultado padrão. --}}
<div class="p-4 lg:p-6 space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <div>
            <h1 class="text-lg font-bold text-gray-900">Monitoramento</h1>
            <p class="text-xs text-gray-500">Consulta contínua de participantes, clientes e grupos — na frequência e plano que você escolher.</p>
        </div>
        <button type="button" onclick="document.getElementById('modal-monitorar').classList.remove('hidden')"
            class="text-[11px] font-semibold px-3 py-1.5 rounded text-white" style="background-color: #047857">
            + Monitorar novo
        </button>
    </div>

    <div id="painel-monitorados" class="bg-white rounded border border-gray-300 overflow-hidden">
        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Monitorados ({{ $assinaturas->count() }})</span>
        </div>
        @if($assinaturas->isEmpty())
            <div class="p-6 text-center text-xs text-gray-400">Nenhum monitoramento ativo. Clique em “Monitorar novo”.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-200 text-[10px] text-gray-400 uppercase tracking-wide">
                            <th class="text-left py-2 px-3">Alvo</th>
                            <th class="text-left px-2">Plano</th>
                            <th class="text-left px-2">Frequência</th>
                            <th class="text-left px-2">Última execução</th>
                            <th class="text-left px-2">Próxima</th>
                            <th class="text-right px-2">Custo/ciclo</th>
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
                                <td class="px-2">
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-white text-[10px]" style="background-color: {{ $a['status'] === 'ativo' ? '#047857' : '#9ca3af' }}">{{ $a['status'] }}</span>
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

    <div id="painel-grupos" class="bg-white rounded border border-gray-300 overflow-hidden">
        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Grupos ({{ $grupos->count() }})</span>
            <button type="button" onclick="painelCriarGrupo()" class="text-[11px] font-semibold px-2.5 py-1 rounded text-white" style="background-color: #334155">+ Novo grupo</button>
        </div>
        @if($grupos->isEmpty())
            <div class="p-6 text-center text-xs text-gray-400">Nenhum grupo. Grupos permitem monitorar vários participantes de uma vez.</div>
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
                            <button type="button" onclick="painelRenomearGrupo({{ $g->id }}, {{ json_encode($g->nome) }})" class="text-[11px] underline text-gray-600">renomear</button>
                            <button type="button" onclick="if(confirm('Excluir o grupo? Um monitoramento ativo dele será cancelado.')) painelAcao('{{ route('app.monitoramento.grupos.excluir', ['id' => $g->id]) }}', 'DELETE')" class="text-[11px] underline ml-2" style="color: #dc2626">excluir</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal Monitorar novo --}}
    <div id="modal-monitorar" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(17,24,39,.5)">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-800">Monitorar novo</span>
                <button type="button" onclick="document.getElementById('modal-monitorar').classList.add('hidden')" class="text-gray-400 text-xl leading-none">&times;</button>
            </div>
            <form id="form-monitorar" class="p-4 space-y-3">
                <div>
                    <label class="text-[11px] text-gray-500 block mb-1">Tipo de alvo</label>
                    <select id="mon-tipo" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" onchange="painelTipoChange()">
                        <option value="grupo">Grupo</option>
                        <option value="participante">Participante</option>
                        <option value="cliente">Cliente</option>
                    </select>
                </div>
                <div id="mon-alvo-grupo">
                    <label class="text-[11px] text-gray-500 block mb-1">Grupo</label>
                    <select id="mon-grupo" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                        @foreach($grupos as $g)
                            <option value="{{ $g->id }}">{{ $g->nome }} ({{ $g->participantes_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div id="mon-alvo-busca" class="hidden">
                    <label class="text-[11px] text-gray-500 block mb-1">Buscar por nome ou CNPJ</label>
                    <input id="mon-busca" type="text" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" placeholder="Digite pelo menos 3 caracteres" oninput="painelBuscar()">
                    <div id="mon-busca-resultados" class="mt-1 border border-gray-200 rounded divide-y divide-gray-100 max-h-40 overflow-y-auto hidden"></div>
                    <input type="hidden" id="mon-alvo-id">
                    <p id="mon-alvo-escolhido" class="text-[11px] text-gray-600 mt-1 hidden"></p>
                </div>
                <div class="grid grid-cols-2 gap-3">
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
                <p class="text-[11px] text-gray-400">Grupos são dinâmicos: quem entrar no grupo passa a ser monitorado no próximo ciclo; o custo do ciclo acompanha o nº de membros.</p>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-monitorar').classList.add('hidden')" class="text-xs px-3 py-1.5 text-gray-600">Cancelar</button>
                    <button type="submit" class="text-xs px-3 py-1.5 rounded text-white" style="background-color: #047857">Monitorar</button>
                </div>
            </form>
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
    function painelCriarGrupo() {
        var nome = prompt('Nome do novo grupo:');
        if (!nome) return;
        fetch('{{ route('app.monitoramento.grupos.criar') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': painelCsrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify({ nome: nome })
        }).then(function () { window.location.reload(); });
    }
    function painelRenomearGrupo(id, atual) {
        var nome = prompt('Novo nome do grupo:', atual);
        if (!nome || nome === atual) return;
        fetch('/app/monitoramento/grupos/' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': painelCsrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify({ nome: nome })
        }).then(function () { window.location.reload(); });
    }
    function painelTipoChange() {
        var tipo = document.getElementById('mon-tipo').value;
        document.getElementById('mon-alvo-grupo').classList.toggle('hidden', tipo !== 'grupo');
        document.getElementById('mon-alvo-busca').classList.toggle('hidden', tipo === 'grupo');
        document.getElementById('mon-alvo-id').value = '';
        document.getElementById('mon-alvo-escolhido').classList.add('hidden');
    }
    var painelBuscaTimer = null;
    function painelBuscar() {
        clearTimeout(painelBuscaTimer);
        var q = document.getElementById('mon-busca').value.trim();
        if (q.length < 3) { document.getElementById('mon-busca-resultados').classList.add('hidden'); return; }
        painelBuscaTimer = setTimeout(function () {
            var tipo = document.getElementById('mon-tipo').value;
            var url = tipo === 'cliente'
                ? '{{ route('app.consulta.nova.clientes') }}?busca=' + encodeURIComponent(q)
                : '{{ route('app.consulta.nova.participantes') }}?busca=' + encodeURIComponent(q);
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    var itens = (j.data || []);
                    var box = document.getElementById('mon-busca-resultados');
                    box.innerHTML = '';
                    itens.slice(0, 10).forEach(function (it) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'block w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50';
                        b.textContent = (it.razao_social || it.nome || '—') + ' · ' + (it.documento || '');
                        b.onclick = function () {
                            document.getElementById('mon-alvo-id').value = it.id;
                            var e = document.getElementById('mon-alvo-escolhido');
                            e.textContent = 'Selecionado: ' + (it.razao_social || it.nome);
                            e.classList.remove('hidden');
                            box.classList.add('hidden');
                        };
                        box.appendChild(b);
                    });
                    box.classList.toggle('hidden', itens.length === 0);
                });
        }, 300);
    }
    (function () {
        var form = document.getElementById('form-monitorar');
        if (!form || form.dataset.bound) return;
        form.dataset.bound = '1';
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var tipo = document.getElementById('mon-tipo').value;
            var payload = {
                plano_id: parseInt(document.getElementById('mon-plano').value, 10),
                frequencia: document.getElementById('mon-frequencia').value
            };
            if (tipo === 'grupo') {
                var g = document.getElementById('mon-grupo').value;
                if (!g) { alert('Crie um grupo primeiro.'); return; }
                payload.grupo_id = parseInt(g, 10);
            } else {
                var id = document.getElementById('mon-alvo-id').value;
                if (!id) { alert('Busque e selecione um alvo.'); return; }
                payload[tipo === 'cliente' ? 'cliente_id' : 'participante_id'] = parseInt(id, 10);
            }
            fetch('{{ route('app.monitoramento.assinatura.criar') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': painelCsrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
              .then(function (res) {
                  if (res.ok && res.j.success) { window.location.reload(); }
                  else { alert((res.j && res.j.error) || 'Não foi possível criar o monitoramento.'); }
              }).catch(function () { alert('Falha de rede.'); });
        });
    })();
    </script>
</div>
