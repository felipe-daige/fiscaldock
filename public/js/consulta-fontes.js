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

        // Modal de seleção de consultas + resumo na página principal.
        var modal = document.getElementById('modal-consultas');
        var chipsVazio = document.getElementById('consultas-selecao-vazia');
        var chipsBox = document.getElementById('consultas-selecao-chips');
        var salvarBloco = document.getElementById('salvar-plano-bloco');

        function fontesMarcadas() {
            return Array.prototype.slice.call(container.querySelectorAll('input[name="fontes[]"]:checked'));
        }

        function todasCheckboxes() {
            return Array.prototype.slice.call(container.querySelectorAll('input[name="fontes[]"]'));
        }

        function checkboxesDoGrupo(chave) {
            var det = container.querySelector('details.grupo-consultas[data-grupo="' + chave + '"]');
            return det ? Array.prototype.slice.call(det.querySelectorAll('input[name="fontes[]"]')) : [];
        }

        function abrirModal() { if (modal) { modal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; } }
        function fecharModal() { if (modal) { modal.classList.add('hidden'); document.body.style.overflow = ''; } }

        // Chips das consultas escolhidas na página principal (fora do modal). Remover desmarca a
        // checkbox correspondente no modal e recalcula tudo.
        function renderConsultasSelecionadas() {
            var fontes = fontesMarcadas();
            chipsBox.innerHTML = '';
            if (fontes.length === 0) {
                chipsVazio.classList.remove('hidden');
                chipsBox.classList.add('hidden');
                salvarBloco.classList.add('hidden');
                return;
            }
            chipsVazio.classList.add('hidden');
            chipsBox.classList.remove('hidden');
            salvarBloco.classList.remove('hidden');

            fontes.forEach(function (cb) {
                var chip = document.createElement('span');
                chip.className = 'inline-flex items-center gap-1.5 rounded border border-gray-300 bg-gray-50 px-2 py-1 text-[11px] text-gray-700';
                var nome = document.createElement('span');
                nome.textContent = cb.dataset.nome || cb.value;
                chip.appendChild(nome);
                var preco = document.createElement('span');
                preco.className = 'text-gray-400';
                preco.textContent = brl(cb.dataset.preco);
                chip.appendChild(preco);
                var x = document.createElement('button');
                x.type = 'button';
                x.className = 'text-gray-400 hover:text-gray-700 font-bold';
                x.textContent = '×';
                x.addEventListener('click', function () { cb.checked = false; sincronizar(); });
                chip.appendChild(x);
                chipsBox.appendChild(chip);
            });
        }

        // Rodapé do modal: contagem + preço bruto local (soma dos data-preco marcados).
        function atualizarModalFooter() {
            var fontes = fontesMarcadas();
            var preco = fontes.reduce(function (s, cb) { return s + parseFloat(cb.dataset.preco || '0'); }, 0);
            var cont = document.getElementById('modal-consultas-contagem');
            var pv = document.getElementById('modal-consultas-preco');
            if (cont) cont.textContent = String(fontes.length);
            if (pv) pv.textContent = brl(preco);
        }

        // Realça (inline style — garante render sem depender de variante Tailwind nova) o card da
        // consulta marcada dentro do modal, e mostra a contagem de marcadas por grupo recolhido.
        function pintarCards() {
            container.querySelectorAll('label.fonte-opt').forEach(function (label) {
                var cb = label.querySelector('input[name="fontes[]"]');
                if (cb && cb.checked) {
                    label.style.borderColor = '#1f2937';
                    label.style.backgroundColor = '#f9fafb';
                    label.style.boxShadow = 'inset 0 0 0 1px #1f2937';
                } else {
                    label.style.borderColor = '';
                    label.style.backgroundColor = '';
                    label.style.boxShadow = '';
                }
            });

            // Badge de contagem no header de cada grupo recolhível (feedback do que ficou marcado
            // dentro de um grupo fechado).
            container.querySelectorAll('details.grupo-consultas').forEach(function (det) {
                var marcadas = det.querySelectorAll('input[name="fontes[]"]:checked').length;
                var badge = det.querySelector('.grupo-count');
                if (!badge) return;
                if (marcadas > 0) {
                    badge.textContent = String(marcadas);
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });

            atualizarTogglesLabels();
        }

        // "Selecionar todos"/"Limpar" por grupo + global, refletindo o estado atual da seleção.
        function atualizarTogglesLabels() {
            container.querySelectorAll('.grupo-toggle').forEach(function (btn) {
                var cbs = checkboxesDoGrupo(btn.dataset.grupo);
                var todos = cbs.length > 0 && cbs.every(function (cb) { return cb.checked; });
                btn.textContent = todos ? 'Limpar' : 'Selecionar todos';
            });
            var tt = document.getElementById('toggle-todas');
            if (tt) {
                var all = todasCheckboxes();
                var todos = all.length > 0 && all.every(function (cb) { return cb.checked; });
                tt.textContent = todos ? 'Limpar todas' : 'Selecionar todas';
            }
        }

        // Sincroniza TODAS as superfícies após qualquer mudança de seleção de consultas.
        function sincronizar() {
            renderConsultasSelecionadas();
            atualizarModalFooter();
            pintarCards();
            atualizarResumo();
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

        // Modo "Ver todos" (lista expandida com filtros) — quando true, escolher um alvo NÃO fecha
        // a lista nem limpa a busca; permite marcar vários em sequência.
        var modoTodos = false;
        var verTodosBtn = document.getElementById('fontes-ver-todos');
        var filtros = document.getElementById('fontes-filtros');
        var filtroUf = document.getElementById('filtro-uf');
        var filtroSituacao = document.getElementById('filtro-situacao');
        var filtroRelacao = document.getElementById('filtro-relacao');

        // ---- Badges/indicadores da linha rica (mesma semântica/hex do layout antigo de consultas) ----
        function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }
        function badgeHtml(label, hex, extra) {
            return '<span class="inline-flex shrink-0 items-center whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:' + (hex || '#9ca3af') + '"' + (extra || '') + '>' + esc(label) + '</span>';
        }
        function relacaoHex(papel) { return papel === 'fornecedor' ? '#2563eb' : papel === 'cliente' ? '#0f766e' : '#7c3aed'; }

        function linhaResultado(tipo, item) {
            var doc = item.documento_formatado || item.documento || '';
            var nome = item.razao_social || item.nome || doc;
            var label = nome + ' · ' + doc;
            var chave = tipo + ':' + item.id;
            var jaSel = !!alvos[chave];
            // CPF ainda não é consultável no back (à la carte CNPJ-only até a task CPF landar).
            var ehCpf = item.is_cpf === true;
            var selecionavel = item.pode_consultar !== false && !ehCpf;

            // Badges inline: risco (alerta), relação fiscal, CPF/CNPJ.
            var badges = '';
            var nivel = item.alerta_nivel || '';
            if ((nivel === 'critico' || nivel === 'alto' || nivel === 'medio') && item.alerta_label) {
                badges += badgeHtml(item.alerta_label, item.alerta_hex || '#dc2626');
            }
            badges += badgeHtml(ehCpf ? 'CPF' : 'CNPJ', ehCpf ? '#9ca3af' : '#374151');

            var relacao;
            if (item.fiscal_resumo) {
                var fr = item.fiscal_resumo;
                relacao = '<div class="mt-1 flex flex-wrap items-center gap-1.5">'
                    + badgeHtml(fr.papel_label, relacaoHex(fr.papel))
                    + '<span class="text-[11px] text-gray-500 truncate max-w-[180px]">' + esc(fr.empresa_label) + '</span>'
                    + '<span class="text-[11px] font-semibold text-gray-700">' + esc(fr.total_formatado) + '</span></div>';
            } else {
                // Sem relação fiscal (nenhuma nota) — estado neutro, texto discreto (não badge colorido).
                relacao = '<div class="mt-1 text-[11px] text-gray-400">Sem movimentação fiscal</div>';
            }

            var row = document.createElement('div');
            row.className = 'px-3 py-2.5' + (jaSel ? ' bg-gray-50' : '');
            row.innerHTML =
                '<div class="flex items-start gap-3">'
                    + '<input type="checkbox" class="alvo-check mt-0.5 h-5 w-5 flex-shrink-0 rounded border-gray-300 text-gray-800 focus:ring-gray-500 ' + (selecionavel ? 'cursor-pointer' : 'cursor-not-allowed opacity-50') + '"' + (jaSel ? ' checked' : '') + (selecionavel ? '' : ' disabled') + '>'
                    + '<button type="button" class="alvo-sel flex-1 min-w-0 text-left ' + (selecionavel ? '' : 'cursor-not-allowed opacity-70') + '">'
                        + '<div class="flex items-center gap-1.5 min-w-0">'
                            + '<span class="text-[13px] font-semibold text-gray-900 truncate">' + esc(nome) + '</span>'
                            + badges
                        + '</div>'
                        + '<div class="text-[11px] text-gray-500 mt-0.5 font-mono">' + esc(doc) + '</div>'
                        + relacao
                    + '</button>'
                    + '<button type="button" class="alvo-exp flex-shrink-0 inline-flex items-center gap-1 px-1.5 py-1 text-[11px] font-medium text-gray-500 hover:text-gray-900" title="Detalhes"><span class="alvo-exp-label hidden sm:inline">Ver detalhes</span><svg class="alvo-chev h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>'
                + '</div>'
                + '<div class="alvo-detalhe hidden mt-2 pl-8"></div>';

            var selBtn = row.querySelector('.alvo-sel');
            var check = row.querySelector('.alvo-check');
            function toggleAlvo() {
                if (!selecionavel) return;
                if (alvos[chave]) { delete alvos[chave]; } else { alvos[chave] = { tipo: tipo, id: item.id, label: label }; }
                var on = !!alvos[chave];
                check.checked = on;
                row.className = 'px-3 py-2.5' + (on ? ' bg-gray-50' : '');
                renderSelecionados();
                atualizarResumo();
                if (!modoTodos) { resultados.classList.add('hidden'); busca.value = ''; }
            }
            // Clique no nome/corpo alterna a seleção; a própria checkbox também.
            selBtn.addEventListener('click', toggleAlvo);
            check.addEventListener('click', function (e) { e.stopPropagation(); toggleAlvo(); });

            var expBtn = row.querySelector('.alvo-exp');
            var expLabel = row.querySelector('.alvo-exp-label');
            var detalhe = row.querySelector('.alvo-detalhe');
            var chev = row.querySelector('.alvo-chev');
            var detalheCarregado = false;
            expBtn.addEventListener('click', function () {
                var aberto = !detalhe.classList.contains('hidden');
                if (aberto) {
                    detalhe.classList.add('hidden'); chev.style.transform = '';
                    if (expLabel) expLabel.textContent = 'Ver detalhes';
                    return;
                }
                detalhe.classList.remove('hidden'); chev.style.transform = 'rotate(90deg)';
                if (expLabel) expLabel.textContent = 'Ocultar';
                if (detalheCarregado) return;

                // Carrega o partial completo de certidões (mesmo mosaico da ficha) via AJAX.
                detalhe.innerHTML = '<p class="mt-2 text-[12px] text-gray-400">Carregando certidões…</p>';
                fetch('/app/consulta/alvo/' + tipo + '/' + item.id + '/certidoes', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function (r) { return r.json(); }).then(function (data) {
                    detalheCarregado = true;
                    if (data && data.success && data.tem_consulta && data.html) {
                        detalhe.innerHTML = '<div class="mt-2 rounded border border-gray-200 overflow-hidden">' + data.html + '</div>';
                    } else {
                        detalhe.innerHTML = '<p class="mt-2 text-[12px] text-gray-400 rounded border border-dashed border-gray-200 px-3 py-3 text-center">Sem consulta anterior — nenhuma certidão emitida ainda para este CNPJ.</p>';
                    }
                }).catch(function () {
                    detalhe.innerHTML = '<p class="mt-2 text-[12px] text-red-500">Falha ao carregar certidões.</p>';
                });
            });

            return row;
        }

        // Carrega alvos aplicando busca + filtros (UF/situação/relação). termo curto+sem filtros
        // e fora do modo "todos" → esconde. per_page maior no modo todos.
        function carregarAlvos(termo) {
            var uf = filtroUf ? filtroUf.value : '';
            var situacao = filtroSituacao ? filtroSituacao.value : '';
            var relacao = filtroRelacao ? filtroRelacao.value : '';
            var temFiltro = uf || situacao || relacao;

            if (!modoTodos && !temFiltro && (!termo || termo.length < 2)) {
                resultados.classList.add('hidden');
                return;
            }

            var headers = { 'X-Requested-With': 'XMLHttpRequest' };
            var perPage = modoTodos ? 100 : 10;
            var qs = 'tipo_documento=PJ&per_page=' + perPage;
            if (termo) qs += '&busca=' + encodeURIComponent(termo);
            if (uf) qs += '&uf=' + encodeURIComponent(uf);
            if (situacao) qs += '&situacao_cadastral=' + encodeURIComponent(situacao);
            if (relacao) qs += '&relacao=' + encodeURIComponent(relacao);

            // Clientes (CNPJ próprio) só entram quando não há filtro de relação (relação é conceito
            // de participante). Busca textual repassa.
            var pedidos = [
                fetch('/app/consulta/nova/participantes?' + qs, { headers: headers })
                    .then(function (r) { return r.json(); }).catch(function () { return { data: [] }; })
            ];
            if (!relacao) {
                var cqs = termo ? 'busca=' + encodeURIComponent(termo) : '';
                pedidos.push(fetch('/app/consulta/nova/clientes?' + cqs, { headers: headers })
                    .then(function (r) { return r.json(); }).catch(function () { return { data: [] }; }));
            } else {
                pedidos.push(Promise.resolve({ data: [] }));
            }

            resultados.innerHTML = '<p class="px-3 py-2 text-[13px] text-gray-400">Carregando…</p>';
            resultados.classList.remove('hidden');

            Promise.all(pedidos).then(function (res) {
                var participantes = (res[0].data || []).filter(function (p) { return p.pode_consultar !== false; });
                var clientes = (res[1].data || res[1].clientes || []).filter(function (c) {
                    return String(c.documento || '').replace(/\D/g, '').length === 14;
                });

                resultados.innerHTML = '';
                participantes.forEach(function (p) { resultados.appendChild(linhaResultado('participante', p)); });
                clientes.forEach(function (c) { resultados.appendChild(linhaResultado('cliente', c)); });

                if (!resultados.children.length) {
                    var vazio = document.createElement('p');
                    vazio.className = 'px-3 py-4 text-[13px] text-gray-500 text-center';
                    vazio.textContent = termo ? ('Nenhum CNPJ encontrado para "' + termo + '".') : 'Nenhum CNPJ encontrado com esses filtros.';
                    resultados.appendChild(vazio);
                }
                resultados.classList.remove('hidden');
            });
        }

        var buscar = debounce(carregarAlvos, 300);
        busca.addEventListener('input', function () { buscar(busca.value.trim()); });

        // "Ver todos": alterna o painel de filtros + lista completa (sem termo).
        if (verTodosBtn) verTodosBtn.addEventListener('click', function () {
            modoTodos = !modoTodos;
            var chev = verTodosBtn.querySelector('.chev-todos');
            if (modoTodos) {
                if (filtros) filtros.classList.remove('hidden');
                if (chev) chev.style.transform = 'rotate(180deg)';
                carregarAlvos(busca.value.trim());
            } else {
                if (filtros) filtros.classList.add('hidden');
                if (chev) chev.style.transform = '';
                resultados.classList.add('hidden');
            }
        });

        [filtroUf, filtroSituacao, filtroRelacao].forEach(function (sel) {
            if (sel) sel.addEventListener('change', function () { carregarAlvos(busca.value.trim()); });
        });

        container.querySelectorAll('input[name="fontes[]"]').forEach(function (cb) {
            cb.addEventListener('change', sincronizar);
        });

        // "Selecionar todas" global: marca todas; se já estiverem todas, limpa.
        var toggleTodas = document.getElementById('toggle-todas');
        if (toggleTodas) toggleTodas.addEventListener('click', function () {
            var todas = todasCheckboxes();
            var faltam = todas.some(function (cb) { return !cb.checked; });
            todas.forEach(function (cb) { cb.checked = faltam; });
            sincronizar();
        });

        // "Selecionar todos" por grupo: preventDefault/stopPropagation p/ NÃO recolher o accordion.
        container.querySelectorAll('.grupo-toggle').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var cbs = checkboxesDoGrupo(btn.dataset.grupo);
                var faltam = cbs.some(function (cb) { return !cb.checked; });
                cbs.forEach(function (cb) { cb.checked = faltam; });
                sincronizar();
            });
        });

        // Aplica uma lista de chaves à seleção (substitui a atual). Usado por kits e presets.
        function aplicarSelecao(chaves) {
            container.querySelectorAll('input[name="fontes[]"]').forEach(function (cb) {
                cb.checked = chaves.indexOf(cb.value) !== -1;
            });
            sincronizar();
        }

        // Kits globais e presets pessoais: preenchem a seleção inteira. Desconto (só kit exato) é
        // confirmado pelo calcular-custo — se o usuário ajustar depois, a linha de desconto some.
        container.querySelectorAll('.kit-preset, .preset-aplicar').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var host = btn.classList.contains('preset-aplicar') ? btn.closest('.preset-pessoal') : btn;
                var chaves = [];
                try { chaves = JSON.parse((host && host.dataset.fontes) || '[]'); } catch (e) { return; }
                aplicarSelecao(chaves);
            });
        });

        // Modal open/close.
        var btnAbrir = document.getElementById('btn-abrir-consultas');
        if (btnAbrir) btnAbrir.addEventListener('click', abrirModal);
        var btnFechar = document.getElementById('modal-consultas-fechar');
        if (btnFechar) btnFechar.addEventListener('click', fecharModal);
        // Backdrop agora é o overlay rolável que CONTÉM o painel — só fecha em clique FORA do painel.
        var backdrop = document.getElementById('modal-consultas-backdrop');
        if (backdrop) backdrop.addEventListener('click', function (e) {
            if (!e.target.closest('.modal-panel')) fecharModal();
        });
        var btnAplicarModal = document.getElementById('modal-consultas-aplicar');
        if (btnAplicarModal) btnAplicarModal.addEventListener('click', fecharModal);

        // Excluir preset pessoal — modal de confirmação estilizado (substitui window.confirm).
        var listaPresets = document.getElementById('meus-planos-lista');
        var modalExcluir = document.getElementById('modal-excluir-plano');
        var excluirPendente = null; // { id, card }

        function abrirConfirmExcluir(card, id, nome) {
            excluirPendente = { id: id, card: card };
            var nomeEl = document.getElementById('excluir-plano-nome');
            if (nomeEl) nomeEl.textContent = nome || 'este plano';
            if (modalExcluir) modalExcluir.classList.remove('hidden');
        }

        function fecharConfirmExcluir() {
            excluirPendente = null;
            if (modalExcluir) modalExcluir.classList.add('hidden');
        }

        if (listaPresets) {
            listaPresets.addEventListener('click', function (e) {
                var alvo = e.target.closest('.preset-excluir');
                if (!alvo) return;
                e.stopPropagation();
                var card = alvo.closest('.preset-pessoal');
                var nomeEl = card && card.querySelector('.preset-aplicar span');
                abrirConfirmExcluir(card, alvo.dataset.id, nomeEl ? nomeEl.textContent : '');
            });
        }

        var btnExcluirCancelar = document.getElementById('excluir-plano-cancelar');
        if (btnExcluirCancelar) btnExcluirCancelar.addEventListener('click', fecharConfirmExcluir);
        var backdropExcluir = document.getElementById('modal-excluir-backdrop');
        if (backdropExcluir) backdropExcluir.addEventListener('click', fecharConfirmExcluir);

        var btnExcluirConfirmar = document.getElementById('excluir-plano-confirmar');
        if (btnExcluirConfirmar) btnExcluirConfirmar.addEventListener('click', function () {
            if (!excluirPendente) return;
            var pend = excluirPendente;
            btnExcluirConfirmar.disabled = true;
            fetch('/app/consulta/meus-planos/' + pend.id + '/excluir', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (r) { return r.json(); }).then(function (data) {
                btnExcluirConfirmar.disabled = false;
                if (data.success) {
                    if (pend.card) pend.card.remove();
                    // Sem presets → volta o empty-state (o bloco "Meus planos" fica sempre visível).
                    if (listaPresets && !listaPresets.children.length) {
                        var vazio = document.getElementById('meus-planos-vazio');
                        if (vazio) vazio.classList.remove('hidden');
                    }
                    fecharConfirmExcluir();
                }
            }).catch(function () {
                btnExcluirConfirmar.disabled = false;
                alert('Falha ao excluir o plano. Tente novamente.');
            });
        });

        // Salvar seleção atual como preset pessoal.
        var btnSalvar = document.getElementById('btn-salvar-plano');
        var salvarForm = document.getElementById('salvar-plano-form');
        var salvarNome = document.getElementById('salvar-plano-nome');
        if (btnSalvar) {
            btnSalvar.addEventListener('click', function () { salvarForm.classList.remove('hidden'); btnSalvar.classList.add('hidden'); salvarNome.focus(); });
            document.getElementById('salvar-plano-cancelar').addEventListener('click', function () { salvarForm.classList.add('hidden'); btnSalvar.classList.remove('hidden'); salvarNome.value = ''; });
            document.getElementById('salvar-plano-confirmar').addEventListener('click', function () {
                var nome = salvarNome.value.trim();
                var fontes = fontesMarcadas().map(function (cb) { return cb.value; });
                if (!nome || !fontes.length) { salvarNome.focus(); return; }
                fetch('/app/consulta/meus-planos', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ nome: nome, fontes: fontes })
                }).then(function (r) { return r.json(); }).then(function (data) {
                    if (!data.success) { alert(data.error || 'Não foi possível salvar o plano.'); return; }
                    adicionarPresetCard(data.preset);
                    salvarForm.classList.add('hidden');
                    btnSalvar.classList.remove('hidden');
                    salvarNome.value = '';
                }).catch(function () { alert('Falha de rede ao salvar o plano.'); });
            });
        }

        // Injeta um novo card de preset na lista do modal (após salvar), sem reload.
        function adicionarPresetCard(preset) {
            if (!listaPresets) return;
            var vazio = document.getElementById('meus-planos-vazio');
            if (vazio) vazio.classList.add('hidden'); // some o empty-state ao ter o 1º preset
            var card = document.createElement('div');
            card.className = 'preset-pessoal rounded-lg border border-gray-300 transition-colors hover:border-gray-800 hover:bg-gray-50';
            card.dataset.id = preset.id;
            card.dataset.fontes = JSON.stringify(preset.fontes);

            var row = document.createElement('div');
            row.className = 'flex items-start justify-between gap-1 p-3';

            var aplicar = document.createElement('button');
            aplicar.type = 'button';
            aplicar.className = 'preset-aplicar text-left min-w-0 flex-1';
            aplicar.innerHTML = '<span class="block text-[13px] font-bold text-gray-900"></span>'
                + '<span class="mt-1 flex items-baseline gap-1.5"><strong class="text-sm text-gray-900 font-mono">' + brl(preset.preco_total) + '</strong>'
                + '<span class="text-[11px] text-gray-400">/CNPJ</span>'
                + '<span class="ml-auto text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">' + preset.fontes.length + ' consultas</span></span>';
            aplicar.querySelector('span').textContent = preset.nome;
            aplicar.addEventListener('click', function () { aplicarSelecao(preset.fontes); });

            var excluir = document.createElement('button');
            excluir.type = 'button';
            excluir.className = 'preset-excluir flex-shrink-0 -mr-1 -mt-1 p-1 text-gray-300 hover:text-red-600 font-bold text-base leading-none';
            excluir.title = 'Excluir plano';
            excluir.dataset.id = preset.id;
            excluir.textContent = '×';

            row.appendChild(aplicar);
            row.appendChild(excluir);
            card.appendChild(row);
            listaPresets.appendChild(card);
        }

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

        sincronizar();
    };

    // Carga direta (full page load) — no SPA o inline script da view chama initConsultaFontes.
    if (document.readyState !== 'loading') {
        window.initConsultaFontes();
    } else {
        document.addEventListener('DOMContentLoaded', window.initConsultaFontes);
    }
})();
