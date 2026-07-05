// Command palette (Ctrl+K / Cmd+K) — navega pelos destinos de window.paletteRegistry.
// Carregado no <head> do layout autenticado: roda 1x por full-load (o layout persiste
// entre navegações SPA), com guard de idempotência.
(function () {
    if (window.__paletteInit) return;
    window.__paletteInit = true;

    let overlay = null;
    let input = null;
    let lista = null;
    let selecionado = 0;
    let resultados = [];

    function normalizar(s) {
        return (s || '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    }

    // Score por termo: match direto pesa mais (bônus se no início de palavra);
    // fallback subsequência (todas as letras na ordem) vale pouco; sem match zera o item.
    function pontuar(consulta, item) {
        const alvo = normalizar(item.label + ' ' + (item.keywords || []).join(' ') + ' ' + (item.grupo || ''));
        let total = 0;
        const termos = normalizar(consulta).split(/\s+/).filter(Boolean);
        for (const termo of termos) {
            const idx = alvo.indexOf(termo);
            if (idx !== -1) {
                total += 10 + ((idx === 0 || alvo[idx - 1] === ' ') ? 5 : 0);
                continue;
            }
            let i = 0;
            for (const ch of alvo) {
                if (ch === termo[i]) i++;
                if (i === termo.length) break;
            }
            if (i < termo.length) return 0;
            total += 1;
        }
        return total;
    }

    function escapar(s) {
        const div = document.createElement('div');
        div.textContent = s == null ? '' : String(s);
        return div.innerHTML;
    }

    function montar() {
        overlay = document.createElement('div');
        overlay.className = 'palette__overlay';
        overlay.innerHTML =
            '<div class="palette__panel" role="dialog" aria-modal="true" aria-label="Busca rápida">' +
            '<input type="text" class="palette__input" placeholder="Ir para… (digite pra filtrar)" aria-label="Buscar destino">' +
            '<div class="palette__list" role="listbox"></div>' +
            '</div>';
        document.body.appendChild(overlay);
        input = overlay.querySelector('.palette__input');
        lista = overlay.querySelector('.palette__list');

        overlay.addEventListener('mousedown', function (e) {
            if (e.target === overlay) fechar();
        });
        input.addEventListener('input', function () { filtrar(input.value); });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); mover(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); mover(-1); }
            else if (e.key === 'Enter') { e.preventDefault(); irPara(selecionado); }
            else if (e.key === 'Escape') { e.preventDefault(); fechar(); }
        });
        lista.addEventListener('click', function (e) {
            const el = e.target.closest('[data-idx]');
            if (el) irPara(parseInt(el.dataset.idx, 10));
        });
    }

    function filtrar(consulta) {
        const registro = Array.isArray(window.paletteRegistry) ? window.paletteRegistry : [];
        if (!consulta.trim()) {
            resultados = registro.slice(0, 12);
        } else {
            resultados = registro
                .map(function (item) { return { item: item, score: pontuar(consulta, item) }; })
                .filter(function (r) { return r.score > 0; })
                .sort(function (a, b) { return b.score - a.score; })
                .slice(0, 12)
                .map(function (r) { return r.item; });
        }
        selecionado = 0;
        render();
    }

    function render() {
        if (!resultados.length) {
            lista.innerHTML = '<div class="palette__vazio">Nada encontrado</div>';
            return;
        }
        lista.innerHTML = resultados.map(function (item, i) {
            const sel = i === selecionado;
            return '<div class="palette__item' + (sel ? ' palette__item--sel' : '') + '" data-idx="' + i + '" role="option"' + (sel ? ' aria-selected="true"' : '') + '>' +
                '<span>' + escapar(item.label) + '</span>' +
                '<span class="palette__grupo">' + escapar(item.grupo || '') + '</span>' +
                '</div>';
        }).join('');
        const selEl = lista.querySelector('.palette__item--sel');
        if (selEl) selEl.scrollIntoView({ block: 'nearest' });
    }

    function mover(delta) {
        if (!resultados.length) return;
        selecionado = (selecionado + delta + resultados.length) % resultados.length;
        render();
    }

    function irPara(idx) {
        const item = resultados[idx];
        if (!item) return;
        fechar();
        if (typeof window.navigateTo === 'function') {
            window.navigateTo(item.href);
        } else {
            window.location.href = item.href;
        }
    }

    function abrir() {
        if (!overlay) montar();
        overlay.classList.add('palette__overlay--open');
        input.value = '';
        filtrar('');
        input.focus();
    }

    function fechar() {
        if (overlay) overlay.classList.remove('palette__overlay--open');
    }

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            if (overlay && overlay.classList.contains('palette__overlay--open')) fechar();
            else abrir();
        }
    });
})();
