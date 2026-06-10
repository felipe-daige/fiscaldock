{{-- Modal de exclusão de importação XML (espelha o do EFD). Acionado por qualquer
     botão com data-excluir-xml="{id}" [data-filename] [data-redirect]. Reutilizável em
     xml-detalhes e no histórico. --}}
<div id="modal-excluir-xml" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-lg bg-white shadow-xl">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-900">Excluir importação XML</h3>
            <p class="mt-1 text-xs text-gray-500" id="excluir-xml-arquivo"></p>
        </div>
        <div class="px-5 py-4 text-sm text-gray-700">
            <p class="mb-3">Esta ação é <strong>irreversível</strong>. Serão apagados:</p>
            <ul id="excluir-xml-impacto" class="mb-4 space-y-1 text-xs text-gray-600"></ul>
            <label class="flex items-start gap-2 rounded border border-gray-200 p-3">
                <input type="checkbox" id="excluir-xml-participantes" class="mt-0.5">
                <span class="text-xs text-gray-700">
                    Também excluir os participantes desta importação
                    <span id="excluir-xml-part-detalhe" class="block text-gray-500"></span>
                </span>
            </label>
        </div>
        <div class="flex justify-end gap-2 border-t border-gray-200 px-5 py-3">
            <button type="button" id="excluir-xml-cancelar" class="rounded border border-gray-300 px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50">Cancelar</button>
            <button type="button" id="excluir-xml-confirmar" class="rounded px-3 py-1.5 text-xs font-medium text-white" style="background-color:#dc2626">Excluir definitivamente</button>
        </div>
    </div>
</div>

<script>
(function () {
    if (window._excluirImportacaoXmlInit) return;
    window._excluirImportacaoXmlInit = true;

    var modal = document.getElementById('modal-excluir-xml');
    if (!modal) return;
    var elArquivo = document.getElementById('excluir-xml-arquivo');
    var elImpacto = document.getElementById('excluir-xml-impacto');
    var elPartDet = document.getElementById('excluir-xml-part-detalhe');
    var chkPart = document.getElementById('excluir-xml-participantes');
    var btnConfirmar = document.getElementById('excluir-xml-confirmar');
    var btnCancelar = document.getElementById('excluir-xml-cancelar');
    var atual = { id: null, redirect: null, trigger: null };

    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }
    function abrir() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    function fechar() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

    function onClickExcluir(btn) {
        atual.id = btn.getAttribute('data-excluir-xml');
        atual.redirect = btn.getAttribute('data-redirect');
        atual.trigger = btn;
        chkPart.checked = false;
        elArquivo.textContent = btn.getAttribute('data-filename') || '';
        elImpacto.innerHTML = '<li>Carregando prévia…</li>';
        elPartDet.textContent = '';
        abrir();

        fetch('/app/importacao/xml/' + atual.id + '/preview-exclusao', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            elImpacto.innerHTML = '<li>' + d.notas + ' notas e ' + d.itens + ' itens</li>';
            var p = d.participantes || {};
            elPartDet.textContent = (p.orfaos || 0) + ' órfãos serão excluídos · ' + (p.compartilhados || 0) + ' compartilhados serão preservados';
        })
        .catch(function () { elImpacto.innerHTML = '<li style="color:#dc2626">Falha ao carregar prévia.</li>'; });
    }

    function confirmar() {
        if (!atual.id) return;
        btnConfirmar.disabled = true;
        btnConfirmar.textContent = 'Excluindo…';
        fetch('/app/importacao/xml/' + atual.id, {
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
        var btn = e.target.closest('[data-excluir-xml]');
        if (btn && !btn.disabled) { e.preventDefault(); onClickExcluir(btn); }
    }
    document.addEventListener('click', handler);
    btnCancelar.addEventListener('click', fechar);
    btnConfirmar.addEventListener('click', confirmar);
    modal.addEventListener('click', function (e) { if (e.target === modal) fechar(); });

    window._cleanupFunctions = window._cleanupFunctions || {};
    window._cleanupFunctions.excluirImportacaoXml = function () {
        document.removeEventListener('click', handler);
        window._excluirImportacaoXmlInit = false;
    };
})();
</script>
