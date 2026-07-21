// Consulta Avulsa por Fontes (à la carte, vertical advocacia).
// Fontes: checkboxes server-rendered (preço em data-preco). Alvos: busca AJAX em
// participantes + clientes (endpoints já existentes da Nova Consulta). Preço live é
// client-side (soma dos data-preco × alvos) com CONFERÊNCIA server-side no submit —
// o backend segue autoritativo (executar revalida seleção, saldo e preço).
(function () {
    'use strict';

    function csrf() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function brl(v) {
        return 'R$ ' + Number(v || 0).toFixed(2).replace('.', ',');
    }

    function debounce(fn, ms) {
        var t;
        return function () {
            var args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(null, args); }, ms);
        };
    }

    window.initConsultaFontes = function () {
        var container = document.getElementById('consulta-fontes-container');
        if (!container || container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        // Alvos selecionados: chave "tipo:id" → {tipo, id, label}
        var alvos = {};

        var busca = document.getElementById('fontes-busca-alvo');
        var resultados = document.getElementById('fontes-resultados-alvo');
        var selecionados = document.getElementById('fontes-selecionados');
        var btnExecutar = document.getElementById('fontes-executar');
        var avisoSaldo = document.getElementById('fontes-saldo-aviso');

        function fontesMarcadas() {
            return Array.prototype.slice.call(container.querySelectorAll('input[name="fontes[]"]:checked'));
        }

        function atualizarResumo() {
            var fontes = fontesMarcadas();
            var precoUnitario = fontes.reduce(function (s, cb) { return s + parseFloat(cb.dataset.preco || '0'); }, 0);
            var nAlvos = Object.keys(alvos).length;
            var total = precoUnitario * nAlvos;

            document.getElementById('fontes-resumo-fontes').textContent = String(fontes.length);
            document.getElementById('fontes-resumo-alvos').textContent = String(nAlvos);
            document.getElementById('fontes-resumo-unitario').textContent = brl(precoUnitario);
            document.getElementById('fontes-resumo-total').textContent = brl(total);

            btnExecutar.disabled = !(fontes.length > 0 && nAlvos > 0);
            avisoSaldo.classList.add('hidden');
            document.getElementById('fontes-resumo-desconto-linha').classList.add('hidden');

            // Conferência server-side do preço/saldo (autoritativa) — só quando há seleção.
            if (fontes.length > 0 && nAlvos > 0) {
                fetch('/app/consulta/nova/fontes/calcular-custo', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ fontes: fontes.map(function (cb) { return cb.value; }), quantidade: nAlvos })
                }).then(function (r) { return r.json(); }).then(function (data) {
                    if (!data.success) return;
                    document.getElementById('fontes-resumo-unitario').textContent = brl(data.preco_bruto_por_alvo_reais || data.preco_por_alvo_reais);
                    document.getElementById('fontes-resumo-total').textContent = brl(data.custo_total_reais);

                    // Desconto de kit (seleção exata): linha verde com o nome do kit.
                    var linhaDesc = document.getElementById('fontes-resumo-desconto-linha');
                    if (data.kit && data.desconto_por_alvo_reais > 0) {
                        document.getElementById('fontes-resumo-kit-nome').textContent = '(' + data.kit.nome + ')';
                        document.getElementById('fontes-resumo-desconto').textContent = '−' + brl(data.desconto_por_alvo_reais * nAlvos);
                        linhaDesc.classList.remove('hidden');
                    } else {
                        linhaDesc.classList.add('hidden');
                    }

                    if (!data.saldo_suficiente) {
                        avisoSaldo.classList.remove('hidden');
                        btnExecutar.disabled = true;
                    }
                }).catch(function () { /* preview client-side já exibido */ });
            }
        }

        function renderSelecionados() {
            selecionados.innerHTML = '';
            Object.keys(alvos).forEach(function (chave) {
                var a = alvos[chave];
                var chip = document.createElement('span');
                chip.className = 'inline-flex items-center gap-1 rounded border border-gray-300 bg-gray-50 px-2 py-1 text-[11px] text-gray-700';
                chip.appendChild(document.createTextNode(a.label));
                var x = document.createElement('button');
                x.type = 'button';
                x.className = 'text-gray-400 hover:text-gray-700 font-bold';
                x.textContent = '×';
                x.addEventListener('click', function () {
                    delete alvos[chave];
                    renderSelecionados();
                    atualizarResumo();
                });
                chip.appendChild(x);
                selecionados.appendChild(chip);
            });
        }

        function linhaResultado(tipo, item) {
            var doc = item.documento_formatado || item.documento || '';
            var label = (item.razao_social || item.nome || doc) + ' · ' + doc;
            var chave = tipo + ':' + item.id;

            var linha = document.createElement('button');
            linha.type = 'button';
            linha.className = 'w-full text-left px-3 py-2 text-[13px] hover:bg-gray-50 flex items-center justify-between gap-2';
            var texto = document.createElement('span');
            texto.className = 'min-w-0 truncate text-gray-800';
            texto.textContent = label;
            var badge = document.createElement('span');
            badge.className = 'flex-shrink-0 text-[10px] uppercase tracking-wide text-gray-400';
            badge.textContent = tipo === 'participante' ? 'Participante' : 'Cliente';
            linha.appendChild(texto);
            linha.appendChild(badge);
            linha.addEventListener('click', function () {
                alvos[chave] = { tipo: tipo, id: item.id, label: label };
                resultados.classList.add('hidden');
                busca.value = '';
                renderSelecionados();
                atualizarResumo();
            });
            return linha;
        }

        var buscar = debounce(function (termo) {
            if (!termo || termo.length < 2) {
                resultados.classList.add('hidden');
                return;
            }

            var headers = { 'X-Requested-With': 'XMLHttpRequest' };
            Promise.all([
                fetch('/app/consulta/nova/participantes?tipo_documento=PJ&per_page=10&busca=' + encodeURIComponent(termo), { headers: headers })
                    .then(function (r) { return r.json(); }).catch(function () { return { data: [] }; }),
                fetch('/app/consulta/nova/clientes?busca=' + encodeURIComponent(termo), { headers: headers })
                    .then(function (r) { return r.json(); }).catch(function () { return { data: [] }; })
            ]).then(function (res) {
                var participantes = (res[0].data || []).filter(function (p) { return p.pode_consultar !== false; });
                var clientes = (res[1].data || res[1].clientes || []).filter(function (c) {
                    var digitos = String(c.documento || '').replace(/\D/g, '');
                    return digitos.length === 14;
                });

                resultados.innerHTML = '';
                participantes.slice(0, 10).forEach(function (p) { resultados.appendChild(linhaResultado('participante', p)); });
                clientes.slice(0, 5).forEach(function (c) { resultados.appendChild(linhaResultado('cliente', c)); });

                if (!resultados.children.length) {
                    var vazio = document.createElement('p');
                    vazio.className = 'px-3 py-2 text-[13px] text-gray-500';
                    vazio.textContent = 'Nenhum CNPJ encontrado para "' + termo + '".';
                    resultados.appendChild(vazio);
                }
                resultados.classList.remove('hidden');
            });
        }, 300);

        busca.addEventListener('input', function () { buscar(busca.value.trim()); });

        container.querySelectorAll('input[name="fontes[]"]').forEach(function (cb) {
            cb.addEventListener('change', atualizarResumo);
        });

        // Kits: preset preenche a seleção inteira (substitui a atual). Desconto é confirmado
        // pelo calcular-custo — se o usuário ajustar depois, a linha de desconto some sozinha.
        container.querySelectorAll('.kit-preset').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var fontesKit = [];
                try { fontesKit = JSON.parse(btn.dataset.fontes || '[]'); } catch (e) { return; }
                container.querySelectorAll('input[name="fontes[]"]').forEach(function (cb) {
                    cb.checked = fontesKit.indexOf(cb.value) !== -1;
                });
                atualizarResumo();
            });
        });

        // Prefill de re-emissão (?fonte=&documento= → data-prefill server-resolved): marca as
        // fontes e adiciona o alvo, deixando o resumo pronto pro 1 clique em Executar.
        if (container.dataset.prefill) {
            try {
                var prefill = JSON.parse(container.dataset.prefill);
                (prefill.fontes || []).forEach(function (chave) {
                    var cb = container.querySelector('input[name="fontes[]"][value="' + chave + '"]');
                    if (cb) cb.checked = true;
                });
                if (prefill.alvo && prefill.alvo.id) {
                    alvos[prefill.alvo.tipo + ':' + prefill.alvo.id] = prefill.alvo;
                    renderSelecionados();
                }
            } catch (e) { /* prefill inválido: tela abre limpa */ }
        }

        btnExecutar.addEventListener('click', function () {
            var fontes = fontesMarcadas().map(function (cb) { return cb.value; });
            var participanteIds = [];
            var clienteIds = [];
            Object.keys(alvos).forEach(function (chave) {
                var a = alvos[chave];
                (a.tipo === 'participante' ? participanteIds : clienteIds).push(a.id);
            });
            if (!fontes.length || (!participanteIds.length && !clienteIds.length)) return;

            btnExecutar.disabled = true;
            btnExecutar.textContent = 'Iniciando…';

            var tabId = 'fontes-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);

            fetch('/app/consulta/nova/fontes/executar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    participante_ids: participanteIds,
                    cliente_ids: clienteIds,
                    fontes: fontes,
                    tab_id: tabId
                })
            }).then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                .then(function (res) {
                    if (res.ok && res.data.success && res.data.redirect_url) {
                        window.location.href = res.data.redirect_url;
                        return;
                    }
                    alert(res.data.error || 'Não foi possível iniciar a consulta.');
                    btnExecutar.textContent = 'Executar consulta';
                    atualizarResumo();
                })
                .catch(function () {
                    alert('Falha de rede ao iniciar a consulta. Tente novamente.');
                    btnExecutar.textContent = 'Executar consulta';
                    atualizarResumo();
                });
        });

        atualizarResumo();
    };

    // Carga direta (full page load) — no SPA o inline script da view chama initConsultaFontes.
    if (document.readyState !== 'loading') {
        window.initConsultaFontes();
    } else {
        document.addEventListener('DOMContentLoaded', window.initConsultaFontes);
    }
})();
