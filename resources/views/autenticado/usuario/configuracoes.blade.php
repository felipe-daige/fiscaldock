@php
    $user ??= Auth::user();
    $n = $configuracoes['notificacoes'] ?? [];
    $emailAtivo = $n['email_ativo'] ?? false;
    $sevMin = $n['alertas_severidade_minima'] ?? 'media';
    $resumoFreq = $n['resumo_frequencia'] ?? 'semanal';
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6">

        <div>
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Notificações</h1>
            <p class="text-xs text-gray-500 mt-1">Escolha o que chega por e-mail e com que frequência.</p>
        </div>

        <div id="msg-config" class="hidden text-[12px] rounded px-3 py-2"></div>

        {{-- Canal --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Canal de envio</span>
            </div>
            <div class="px-4 py-4 flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <p class="text-[13px] text-gray-900 font-medium">E-mail</p>
                    <p class="text-[11px] text-gray-500">{{ $user->email }}</p>
                </div>
                @if($emailAtivo)
                    <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded text-white" style="background-color: #047857">Ativo</span>
                @else
                    <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded text-white" style="background-color: #b45309">Sem e-mail</span>
                @endif
            </div>
        </div>

        {{-- Alertas --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Alertas por e-mail</span>
            </div>
            <div class="divide-y divide-gray-100">

                {{-- Toggle: operacionais. O <input> é real (sr-only): dá acesso por teclado
                     (Tab + Espaço), estado anunciável por leitor de tela, e faz o <label>
                     inteiro ficar clicável — um <span> com onclick não dá nada disso. --}}
                <label class="px-4 py-4 flex items-center justify-between gap-4 cursor-pointer">
                    <span class="min-w-0">
                        <span class="block text-[13px] text-gray-900 font-medium">Alertas operacionais</span>
                        <span class="block text-[11px] text-gray-500">Certidão positiva, fornecedor irregular, gap de importação, divergências.</span>
                    </span>
                    <input type="checkbox" class="config-toggle-input sr-only" data-campo="alertas_operacionais" @checked($n['alertas_operacionais'] ?? false)>
                    <span class="config-toggle" aria-hidden="true"></span>
                </label>

                {{-- Toggle: monitoramento --}}
                <label class="px-4 py-4 flex items-center justify-between gap-4 cursor-pointer">
                    <span class="min-w-0">
                        <span class="block text-[13px] text-gray-900 font-medium">Alertas de monitoramento</span>
                        <span class="block text-[11px] text-gray-500">Mudança de situação cadastral de um CNPJ que você monitora.</span>
                    </span>
                    <input type="checkbox" class="config-toggle-input sr-only" data-campo="alertas_monitoramento" @checked($n['alertas_monitoramento'] ?? false)>
                    <span class="config-toggle" aria-hidden="true"></span>
                </label>

                {{-- Select: severidade mínima --}}
                <div class="px-4 py-4 flex items-center justify-between gap-4 flex-wrap">
                    <span class="min-w-0">
                        <span class="block text-[13px] text-gray-900 font-medium">E-mail imediato a partir de</span>
                        <span class="block text-[11px] text-gray-500">Alertas abaixo do nível escolhido só aparecem na central e no resumo.</span>
                    </span>
                    <select class="config-select text-[13px] py-2 px-3 border border-gray-300 rounded bg-white" data-campo="alertas_severidade_minima">
                        <option value="media" @selected($sevMin === 'media')>Severidade média e alta</option>
                        <option value="alta" @selected($sevMin === 'alta')>Só severidade alta</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Resumo --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo periódico</span>
            </div>
            <div class="divide-y divide-gray-100">

                <label class="px-4 py-4 flex items-center justify-between gap-4 cursor-pointer">
                    <span class="min-w-0">
                        <span class="block text-[13px] text-gray-900 font-medium">Receber resumo</span>
                        <span class="block text-[11px] text-gray-500">Um panorama com os alertas e a atividade do período. Período vazio não gera e-mail.</span>
                    </span>
                    <input type="checkbox" class="config-toggle-input sr-only" data-campo="resumo_periodico" @checked($n['resumo_periodico'] ?? false)>
                    <span class="config-toggle" aria-hidden="true"></span>
                </label>

                <div class="px-4 py-4 flex items-center justify-between gap-4 flex-wrap">
                    <span class="min-w-0">
                        <span class="block text-[13px] text-gray-900 font-medium">Frequência do resumo</span>
                        <span class="block text-[11px] text-gray-500">Semanal (toda segunda) ou mensal (uma vez por mês). Nada se perde: o resumo cobre todo o período desde o anterior.</span>
                    </span>
                    <select class="config-select text-[13px] py-2 px-3 border border-gray-300 rounded bg-white" data-campo="resumo_frequencia">
                        <option value="semanal" @selected($resumoFreq === 'semanal')>Semanal</option>
                        <option value="mensal" @selected($resumoFreq === 'mensal')>Mensal</option>
                    </select>
                </div>
            </div>
        </div>

        <p class="text-[11px] text-gray-400 text-center">
            Avisos de conta e cobrança (pagamento, verificação) fazem parte do serviço e não são desligáveis.
        </p>

    </div>
</div>

<style>
    /* O trilho é decorativo (aria-hidden); o estado real mora no <input> irmão. */
    .config-toggle { position: relative; display: inline-block; width: 42px; height: 24px; border-radius: 12px; background-color: #d1d5db; flex-shrink: 0; transition: background-color .15s; }
    .config-toggle::after { content: ''; position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; border-radius: 50%; background-color: #fff; transition: left .15s; box-shadow: 0 1px 2px rgba(0,0,0,.2); }
    .config-toggle-input:checked + .config-toggle { background-color: #1f4679; }
    .config-toggle-input:checked + .config-toggle::after { left: 20px; }
    .config-toggle-input:disabled + .config-toggle { opacity: .5; }
    /* Foco por teclado precisa ser visível — o input é sr-only. */
    .config-toggle-input:focus-visible + .config-toggle { outline: 2px solid #1f4679; outline-offset: 2px; }
</style>

<script>
(function () {
    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta ? meta.getAttribute('content') : '';
    var msg = document.getElementById('msg-config');

    function showMsg(ok, text) {
        if (!msg) return;
        msg.classList.remove('hidden');
        msg.style.backgroundColor = ok ? '#ecfdf5' : '#fef2f2';
        msg.style.color = ok ? '#047857' : '#b91c1c';
        msg.textContent = text;
    }

    function salvar(campo, valor, onOk, onFail) {
        fetch('/app/configuracoes/notificacoes', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ campo: campo, valor: valor })
        }).then(function (r) {
            return r.json().then(function (data) { return { ok: r.ok, data: data }; });
        }).then(function (res) {
            if (res.ok && res.data.success) {
                showMsg(true, 'Preferência salva.');
                if (onOk) onOk();
            } else {
                showMsg(false, (res.data.message) || 'Não foi possível salvar.');
                if (onFail) onFail();
            }
        }).catch(function () {
            showMsg(false, 'Falha de conexão.');
            if (onFail) onFail();
        });
    }

    // Toggles (checkbox real — clique no label e teclado disparam 'change' nativo).
    Array.prototype.forEach.call(document.querySelectorAll('.config-toggle-input'), function (el) {
        el.addEventListener('change', function () {
            var campo = el.getAttribute('data-campo');
            var novo = el.checked;
            el.disabled = true;
            salvar(campo, novo,
                function () { el.disabled = false; },
                function () { el.checked = !novo; el.disabled = false; } // rollback visual
            );
        });
    });

    // Selects (enum de frequência)
    Array.prototype.forEach.call(document.querySelectorAll('.config-select'), function (el) {
        var anterior = el.value;
        el.addEventListener('change', function () {
            var campo = el.getAttribute('data-campo');
            el.disabled = true;
            salvar(campo, el.value,
                function () { anterior = el.value; el.disabled = false; },
                function () { el.value = anterior; el.disabled = false; }
            );
        });
    });
})();
</script>
