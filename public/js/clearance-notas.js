(function () {
    const root = document.getElementById('validacao-notas-container');
    if (!root) return;

    const idsUrl = root.dataset.idsUrl;
    const validarUrl = root.dataset.validarUrl;
    const custoUrl = root.dataset.custoUrl;

    const form = document.getElementById('validacao-filtros-form');
    const chkMaster = document.getElementById('chk-master');
    const chkNotas = () => Array.from(document.querySelectorAll('.chk-nota'));
    const btnValidar = document.getElementById('btn-validar');
    const btnCusto = document.getElementById('btn-calcular-custo');
    const btnSelTodas = document.getElementById('btn-selecionar-todas');
    const selLabel = document.getElementById('selecao-label');
    const tipoValidacao = document.getElementById('tipo-validacao');

    const selecionados = new Set();

    function getCsrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function queryFiltros() {
        const fd = new FormData(form);
        const params = new URLSearchParams();
        for (const [k, v] of fd.entries()) if (v) params.append(k, v);
        return params.toString();
    }

    function getTipoValidacao() {
        return tipoValidacao ? tipoValidacao.value : 'completa';
    }

    function atualizarSelecao() {
        chkNotas().forEach((chk) => {
            chk.checked = selecionados.has(parseInt(chk.value, 10));
        });
        const n = selecionados.size;
        selLabel.textContent = n === 0 ? 'Nenhuma nota selecionada' : `${n} nota(s) selecionada(s)`;
        btnValidar.disabled = n === 0;
        btnCusto.disabled = n === 0;

        const visiveis = chkNotas();
        const todasVisSelecionadas = visiveis.length > 0 && visiveis.every((c) => c.checked);
        chkMaster.checked = todasVisSelecionadas;
        chkMaster.indeterminate = !todasVisSelecionadas && visiveis.some((c) => c.checked);

        if (todasVisSelecionadas && visiveis.length > 0) {
            btnSelTodas.classList.remove('hidden');
        } else {
            btnSelTodas.classList.add('hidden');
        }
    }

    chkMaster.addEventListener('change', () => {
        chkNotas().forEach((chk) => {
            const id = parseInt(chk.value, 10);
            if (chkMaster.checked) selecionados.add(id);
            else selecionados.delete(id);
        });
        atualizarSelecao();
    });

    document.addEventListener('change', (e) => {
        if (e.target.matches('.chk-nota')) {
            const id = parseInt(e.target.value, 10);
            if (e.target.checked) selecionados.add(id);
            else selecionados.delete(id);
            atualizarSelecao();
        }
    });

    btnSelTodas.addEventListener('click', async () => {
        btnSelTodas.disabled = true;
        try {
            const resp = await fetch(`${idsUrl}?${queryFiltros()}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await resp.json();
            if (data.success) {
                data.ids.forEach((id) => selecionados.add(id));
                atualizarSelecao();
            }
        } finally {
            btnSelTodas.disabled = false;
        }
    });

    btnCusto.addEventListener('click', async () => {
        if (selecionados.size === 0) return;
        btnCusto.disabled = true;
        try {
            const resp = await fetch(custoUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrf(),
                },
                body: JSON.stringify({ nota_ids: Array.from(selecionados), tipo: getTipoValidacao() }),
            });
            const data = await resp.json();
            if (resp.ok) {
                const custo = data.custo || {};
                alert(`Custo estimado: ${custo.custo_total ?? 0} crédito(s) para ${selecionados.size} nota(s) em ${custo.participantes_unicos ?? 0} participante(s).`);
            } else {
                alert(data.message || 'Falha ao calcular custo.');
            }
        } catch (err) {
            alert('Erro de rede ao calcular custo.');
        } finally {
            btnCusto.disabled = selecionados.size === 0;
        }
    });

    btnValidar.addEventListener('click', async () => {
        if (selecionados.size === 0) return;
        const tipo = getTipoValidacao();
        if (!confirm(`Confirmar validação de ${selecionados.size} nota(s) com a opção "${tipo}"?`)) return;
        btnValidar.disabled = true;
        try {
            const resp = await fetch(validarUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrf(),
                },
                body: JSON.stringify({ nota_ids: Array.from(selecionados), tipo }),
            });
            const data = await resp.json();
            if (resp.ok) {
                alert(`Validação concluída. ${data.creditos_utilizados ?? 0} crédito(s) debitado(s).`);
                window.location.reload();
            } else if (resp.status === 402) {
                alert(`Créditos insuficientes. Necessário: ${data.custo_necessario}. Saldo: ${data.saldo_atual}.`);
            } else {
                alert(data.message || 'Falha ao validar notas.');
            }
        } catch (err) {
            alert('Erro de rede ao validar.');
        } finally {
            btnValidar.disabled = selecionados.size === 0;
        }
    });

    atualizarSelecao();
})();
