@php
    $totalImportacoes = $importacoes->count();
    $totalEfd = $importacoes->where('_tipo', 'efd')->count();
    $totalXml = $importacoes->where('_tipo', 'xml')->count();
@endphp

{{-- Histórico Unificado de Importações --}}
<div class="min-h-screen bg-gray-100" id="historico-importacoes-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-8">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Histórico de Importações</h1>
            <p class="text-xs text-gray-500 mt-1">Consolidado operacional das importações EFD e XML processadas pela conta.</p>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo Operacional</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 divide-x divide-y lg:divide-y-0 divide-gray-200">
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1 sm:mb-2">Total</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($totalImportacoes, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">operações registradas</p>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1 sm:mb-2">EFD</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($totalEfd, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">arquivos SPED</p>
                </div>
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1 sm:mb-2">XML</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($totalXml, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">lotes XML</p>
                </div>
            </div>
        </div>

        @if($importacoes->isNotEmpty())
            <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6" data-mobile-filters>
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 hidden sm:block">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtro</span>
                </div>
                <div class="px-4 py-4">
                    <div class="mobile-filter-scroll flex items-center gap-2" id="filtro-tipo-wrapper">
                        <button
                            type="button"
                            data-tipo="todos"
                            class="filtro-tipo px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wide border rounded bg-gray-800 text-white border-gray-800 hover:bg-gray-800 hover:text-white"
                        >Todos</button>
                        <button
                            type="button"
                            data-tipo="efd"
                            class="filtro-tipo px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wide border rounded bg-white text-gray-700 border-gray-300 hover:bg-gray-50"
                        >EFD</button>
                        <button
                            type="button"
                            data-tipo="xml"
                            class="filtro-tipo px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wide border rounded bg-white text-gray-700 border-gray-300 hover:bg-gray-50"
                        >XML</button>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded border border-gray-300 overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Importações</span>
                    <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded">{{ $totalImportacoes }} no histórico</span>
                </div>
                <div class="w-full min-w-0">
                    <table class="tabela-cards historico-tabela">
                        <colgroup>
                            <col class="w-[31%]">
                            <col class="w-[14%]">
                            <col class="w-[23%]">
                            <col class="w-[13%]">
                            <col class="w-[13%]">
                            <col class="w-[6%]">
                        </colgroup>
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Importação realizada</th>
                                <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Conteúdo</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Resultado</th>
                                <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Competência</th>
                                <th class="px-3 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Status</th>
                                <th class="w-12 px-3 py-2.5 bg-gray-50"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                    @foreach($importacoes as $imp)
                        @php
                            $preview = $imp['_preview'];
                            $data = $preview['data'];
                        @endphp
                        <tr class="hist-row cursor-pointer hover:bg-gray-50/50 transition-colors"
                            data-tipo="{{ $preview['tipo'] }}"
                            data-importacao-card="{{ $preview['id'] }}"
                            data-history-result-url="{{ $preview['href'] }}">
                            <td class="px-3 py-3.5">
                                <div class="flex w-full min-w-0 items-start gap-3">
                                    <div class="w-12 shrink-0 border-r border-gray-200 pr-3 text-center" title="{{ $data?->format('d/m/Y H:i') }}">
                                        <p class="text-[10px] font-bold uppercase text-gray-500">{{ $preview['data_label'] ?? '—' }}</p>
                                        <p class="mt-0.5 text-xs font-semibold text-gray-900">{{ $data?->format('H:i') ?? '—' }}</p>
                                    </div>
                                    <div class="min-w-0 max-w-[390px]">
                                        <a href="{{ $preview['href'] }}" data-link class="block truncate text-sm font-semibold text-gray-900 hover:text-gray-600 hover:underline" title="{{ $preview['titulo'] }}">{{ $preview['titulo'] }}</a>
                                        <p class="mt-0.5 truncate text-[11px] text-gray-500" title="{{ $preview['arquivo'] }}">{{ $preview['arquivo'] }}</p>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-1.5 text-[10px] uppercase text-gray-400">
                                            <span>Importação #{{ $preview['id'] }}</span>
                                            @if($preview['cnpj'] && $preview['titulo'] !== $preview['cnpj'])
                                                <span aria-hidden="true">•</span>
                                                <span class="font-mono">{{ $preview['cnpj'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Conteúdo" class="px-3 py-3 text-center">
                                <div class="text-center">
                                    <span class="inline-block whitespace-nowrap rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $preview['conteudo']['badge']['hex'] }}">{{ $preview['conteudo']['badge']['label'] }}</span>
                                    <p class="mt-1.5 text-[11px] text-gray-500">{{ $preview['conteudo']['detalhe'] }}</p>
                                </div>
                            </td>
                            <td data-label="Resultado" class="px-3 py-3">
                                <div class="text-left">
                                    <p class="text-xs font-semibold text-gray-900">{{ $preview['resultado']['titulo'] }}</p>
                                    @if($preview['resultado']['detalhes'] !== [])
                                        <p class="mt-1 text-[11px] text-gray-500">{{ implode(' · ', $preview['resultado']['detalhes']) }}</p>
                                    @endif
                                    @if($preview['resultado']['valor'])
                                        <p class="mt-1 text-[11px] font-mono text-gray-700">{{ $preview['resultado']['valor'] }}</p>
                                    @endif
                                </div>
                            </td>
                            <td data-label="Competência" class="px-3 py-3 text-center">
                                @if($preview['competencia'])
                                    <span class="inline-block whitespace-nowrap rounded px-2 py-0.5 text-[11px] font-bold text-gray-800" style="background-color: #f3f4f6">{{ $preview['competencia'] }}</span>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td data-label="Status" class="px-3 py-3 text-center">
                                <span class="inline-block whitespace-nowrap rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $preview['status']['hex'] }}">{{ $preview['status']['label'] }}</span>
                            </td>
                            <td class="px-3 py-3 text-right">
                                <x-acoes-menu trigger="kebab">
                                    <x-acoes-item href="{{ $preview['href'] }}" data-link>Abrir</x-acoes-item>
                                    @if(! $preview['processando'] && $preview['tipo'] === 'efd')
                                        <x-acoes-item variant="danger" data-excluir-importacao="{{ $preview['id'] }}" data-filename="{{ $preview['arquivo'] }}">Excluir</x-acoes-item>
                                    @elseif(! $preview['processando'] && $preview['tipo'] === 'xml')
                                        <x-acoes-item variant="danger" data-excluir-xml="{{ $preview['id'] }}" data-filename="{{ $preview['arquivo'] }}">Excluir</x-acoes-item>
                                    @endif
                                </x-acoes-menu>
                            </td>
                        </tr>
                    @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="zero-state-filtro" class="hidden py-16 text-center">
                <p class="text-sm text-gray-700">Nenhuma importação deste tipo encontrada.</p>
            </div>
        @else
            <div class="bg-white rounded border border-gray-300 p-8 sm:p-12 text-center">
                <p class="text-base font-semibold text-gray-700 mb-1">Nenhuma importação realizada ainda</p>
                <p class="text-sm text-gray-500 mb-6">Importe seus primeiros arquivos para começar a preencher este histórico.</p>
                <div class="flex items-center justify-center gap-3 flex-wrap">
                    <a href="/app/importacao/efd" data-link class="px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium">Importar EFD</a>
                    <a href="/app/importacao/xml" data-link class="px-4 py-2 bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded text-sm font-medium">Importar XML</a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
(function () {
    var btns = document.querySelectorAll('.filtro-tipo');
    var rows = document.querySelectorAll('.hist-row');
    var zeroFiltro = document.getElementById('zero-state-filtro');

    if (!btns.length) return;

    btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tipo = this.getAttribute('data-tipo');
            var visiveis = 0;

            btns.forEach(function (b) {
                b.classList.remove('bg-gray-800', 'text-white', 'border-gray-800', 'hover:bg-gray-800', 'hover:text-white');
                b.classList.add('bg-white', 'text-gray-700', 'border-gray-300');
            });

            this.classList.add('bg-gray-800', 'text-white', 'border-gray-800', 'hover:bg-gray-800', 'hover:text-white');
            this.classList.remove('bg-white', 'text-gray-700', 'border-gray-300');

            rows.forEach(function (row) {
                var rowTipo = row.getAttribute('data-tipo');
                var show = tipo === 'todos' || rowTipo === tipo;
                row.style.display = show ? '' : 'none';
                if (show) visiveis++;
            });

            if (zeroFiltro) zeroFiltro.classList.toggle('hidden', visiveis > 0);
        });
    });
})();
</script>

<script>
(function () {
    if (window._histCardClickInit) return;
    window._histCardClickInit = true;

    document.addEventListener('click', function (e) {
        var host = e.target.closest('.hist-row, .hist-card');
        if (!host) return;
        // Deixa os elementos interativos (links, botões, menu de ações) agirem sozinhos.
        if (e.target.closest('a, button, input, label, select, [data-acoes-menu], [data-excluir-importacao], [data-excluir-xml]')) return;
        var link = host.querySelector('a[data-link]');
        if (link) link.click();
    });
})();
</script>

@include('autenticado.importacao._modal-excluir-xml')

<div id="modal-excluir-importacao" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-lg bg-white shadow-xl">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-900">Excluir importação</h3>
            <p class="mt-1 text-xs text-gray-500" id="excluir-arquivo"></p>
        </div>
        <div class="px-5 py-4 text-sm text-gray-700">
            <p class="mb-3">Esta ação é <strong>irreversível</strong>. Serão apagados:</p>
            <ul id="excluir-impacto" class="mb-4 space-y-1 text-xs text-gray-600"></ul>
            <label class="flex items-start gap-2 rounded border border-gray-200 p-3">
                <input type="checkbox" id="excluir-participantes" class="mt-0.5">
                <span class="text-xs text-gray-700">
                    Também excluir os participantes desta importação
                    <span id="excluir-part-detalhe" class="block text-gray-500"></span>
                </span>
            </label>
        </div>
        <div class="flex justify-end gap-2 border-t border-gray-200 px-5 py-3">
            <button type="button" id="excluir-cancelar" class="rounded border border-gray-300 px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50">Cancelar</button>
            <button type="button" id="excluir-confirmar" class="rounded px-3 py-1.5 text-xs font-medium text-white" style="background-color:#dc2626">Excluir definitivamente</button>
        </div>
    </div>
</div>

<script>
(function () {
    if (window._excluirImportacaoInit) return;
    window._excluirImportacaoInit = true;

    var modal = document.getElementById('modal-excluir-importacao');
    if (!modal) return;
    var elArquivo = document.getElementById('excluir-arquivo');
    var elImpacto = document.getElementById('excluir-impacto');
    var elPartDet = document.getElementById('excluir-part-detalhe');
    var chkPart = document.getElementById('excluir-participantes');
    var btnConfirmar = document.getElementById('excluir-confirmar');
    var btnCancelar = document.getElementById('excluir-cancelar');
    var atual = { id: null, redirect: null, trigger: null };

    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }
    function abrir() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function fechar() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

    function onClickExcluir(btn) {
        atual.id = btn.getAttribute('data-excluir-importacao');
        atual.redirect = btn.getAttribute('data-redirect');
        atual.trigger = btn;
        chkPart.checked = false;
        elArquivo.textContent = btn.getAttribute('data-filename') || '';
        elImpacto.innerHTML = '<li>Carregando prévia…</li>';
        elPartDet.textContent = '';
        abrir();

        fetch('/app/importacao/efd/' + atual.id + '/preview-exclusao', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            elImpacto.innerHTML =
                '<li>' + d.notas + ' notas e ' + d.itens + ' itens</li>' +
                '<li>' + d.catalogo + ' itens de catálogo</li>' +
                '<li>' + d.apuracoes + ' apurações · ' + d.retencoes + ' retenções · ' + d.divergencias + ' divergências</li>';
            var p = d.participantes || {};
            elPartDet.textContent = (p.orfaos || 0) + ' órfãos serão excluídos · ' + (p.compartilhados || 0) + ' compartilhados serão preservados';
        })
        .catch(function () { elImpacto.innerHTML = '<li style="color:#dc2626">Falha ao carregar prévia.</li>'; });
    }

    function confirmar() {
        if (!atual.id) return;
        btnConfirmar.disabled = true;
        btnConfirmar.textContent = 'Excluindo…';
        fetch('/app/importacao/efd/' + atual.id, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf(),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ excluir_participantes: chkPart.checked })
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
        .then(function (res) {
            btnConfirmar.disabled = false;
            btnConfirmar.textContent = 'Excluir definitivamente';
            if (!res.ok || !res.j.success) {
                elImpacto.innerHTML = '<li style="color:#dc2626">' + (res.j.error || 'Falha ao excluir.') + '</li>';
                return;
            }
            fechar();
            if (atual.redirect) {
                window.location.href = atual.redirect;
            } else if (atual.trigger) {
                var row = atual.trigger.closest('tr, [data-importacao-card]');
                if (row) row.remove();
            }
        })
        .catch(function () {
            btnConfirmar.disabled = false;
            btnConfirmar.textContent = 'Excluir definitivamente';
            elImpacto.innerHTML = '<li style="color:#dc2626">Erro de rede ao excluir.</li>';
        });
    }

    function handler(e) {
        var btn = e.target.closest('[data-excluir-importacao]');
        if (btn && !btn.disabled) { e.preventDefault(); onClickExcluir(btn); }
    }
    document.addEventListener('click', handler);
    btnCancelar.addEventListener('click', fechar);
    btnConfirmar.addEventListener('click', confirmar);
    modal.addEventListener('click', function (e) { if (e.target === modal) fechar(); });

    window._cleanupFunctions = window._cleanupFunctions || {};
    window._cleanupFunctions.excluirImportacao = function () {
        document.removeEventListener('click', handler);
        window._excluirImportacaoInit = false;
    };
})();
</script>
