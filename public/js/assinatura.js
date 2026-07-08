window.initPlanos = function () {
    var root = document.getElementById('assinatura-modal');
    if (!root) return;
    if (root.__init) return;
    root.__init = true;

    var mp = (window.MercadoPago && window.__MP_PUBLIC_KEY)
        ? new MercadoPago(window.__MP_PUBLIC_KEY, { locale: 'pt-BR' })
        : null;
    var bricks = mp ? mp.bricks() : null;
    var controller = null;
    var cancelModal = document.getElementById('assinatura-cancel-modal');
    var cancelConfirmBtn = document.getElementById('assinatura-cancel-confirmar');
    var cancelDefaultText = cancelConfirmBtn ? cancelConfirmBtn.textContent : '';

    var teto = parseInt(window.__MP_TETO_CENTAVOS, 10) || 400000;

    function cicloAtual() {
        var r = document.querySelector('input[name="ciclo"]:checked');
        return r ? r.value : 'mensal';
    }

    function centavosDoBotao(btn, ciclo) {
        var c = ciclo === 'anual'
            ? btn.getAttribute('data-ciclo-anual-centavos')
            : btn.getAttribute('data-ciclo-mensal-centavos');
        return parseInt(c, 10) || 0;
    }

    // Cobrança acima do teto do MP não é self-service: o botão vira "Falar com atendente".
    function atualizarBotoes() {
        var ciclo = cicloAtual();
        document.querySelectorAll('[data-assinar]').forEach(function (btn) {
            var acimaDoTeto = centavosDoBotao(btn, ciclo) > teto;
            btn.textContent = acimaDoTeto ? 'Falar com atendente' : 'Assinar';
            btn.style.backgroundColor = acimaDoTeto ? '#0f766e' : '#1f2937';
            btn.setAttribute('data-assistido', acimaDoTeto ? '1' : '0');
        });
    }

    function abrirWhatsapp(btn, ciclo) {
        var base = window.__WHATSAPP_URL || '';
        if (!base) { mostrarErro('Atendimento indisponível no momento.'); return; }
        var nome = btn.getAttribute('data-nome') || btn.getAttribute('data-plano');
        var msg = 'Olá! Quero assinar o plano ' + nome + ' (' + ciclo + '). Pode me ajudar?';
        var sep = base.indexOf('?') === -1 ? '?' : '&';
        window.open(base + sep + 'text=' + encodeURIComponent(msg), '_blank');
    }

    function mostrarErro(msg) {
        var el = document.getElementById('assinatura-erro');
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function mostrarErroCancelamento(msg) {
        var el = document.getElementById('assinatura-cancel-erro');
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function limparErroCancelamento() {
        var el = document.getElementById('assinatura-cancel-erro');
        if (el) {
            el.textContent = '';
            el.classList.add('hidden');
        }
    }

    function fechar() {
        root.classList.add('hidden');
        root.classList.remove('flex');
        if (controller && controller.unmount) { try { controller.unmount(); } catch (e) {} controller = null; }
    }

    function abrirCancelamento() {
        if (!cancelModal) return;
        limparErroCancelamento();
        setCancelando(false);
        cancelModal.classList.remove('hidden');
        cancelModal.classList.add('flex');
    }

    function fecharCancelamento() {
        if (!cancelModal) return;
        cancelModal.classList.add('hidden');
        cancelModal.classList.remove('flex');
        limparErroCancelamento();
        setCancelando(false);
    }

    function setCancelando(cancelando) {
        if (!cancelConfirmBtn) return;
        cancelConfirmBtn.disabled = cancelando;
        cancelConfirmBtn.textContent = cancelando ? 'Cancelando...' : cancelDefaultText;
        cancelConfirmBtn.classList.toggle('opacity-70', cancelando);
        cancelConfirmBtn.classList.toggle('cursor-wait', cancelando);
    }

    function abrir(plano, valorReais) {
        if (!bricks) { mostrarErro('Pagamento indisponível no momento.'); return; }
        root.classList.remove('hidden');
        root.classList.add('flex');
        document.getElementById('assinatura-erro').classList.add('hidden');
        document.getElementById('assinatura-brick').innerHTML = '';
        if (controller && controller.unmount) { try { controller.unmount(); } catch (e) {} }

        bricks.create('cardPayment', 'assinatura-brick', {
            initialization: { amount: valorReais },
            callbacks: {
                onReady: function () {},
                onError: function () { mostrarErro('Erro ao carregar o cartão.'); },
                onSubmit: function (formData) {
                    return enviar(plano, cicloAtual(), formData.token);
                },
            },
        }).then(function (c) { controller = c; });
    }

    function enviar(plano, ciclo, token) {
        return fetch(window.__ASSINAR_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ plano: plano, ciclo: ciclo, token: token }),
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (res) {
                if (!res.ok) { mostrarErro(res.j.error || 'Falha ao assinar.'); return; }
                window.location.reload();
            }).catch(function () { mostrarErro('Falha de rede.'); });
    }

    document.querySelectorAll('[data-assinar]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var ciclo = cicloAtual();
            var centavos = centavosDoBotao(btn, ciclo);
            if (centavos > teto) { abrirWhatsapp(btn, ciclo); return; } // checkout assistido
            abrir(btn.getAttribute('data-plano'), centavos / 100);
        });
    });

    document.querySelectorAll('input[name="ciclo"]').forEach(function (radio) {
        radio.addEventListener('change', atualizarBotoes);
    });
    atualizarBotoes();

    var fecharBtn = document.getElementById('assinatura-fechar');
    if (fecharBtn) fecharBtn.addEventListener('click', fechar);

    var cancelarBtn = document.getElementById('assinatura-cancelar');
    if (cancelarBtn) {
        cancelarBtn.addEventListener('click', function () {
            abrirCancelamento();
        });
    }

    var cancelFecharBtn = document.getElementById('assinatura-cancel-fechar');
    if (cancelFecharBtn) cancelFecharBtn.addEventListener('click', fecharCancelamento);

    var cancelVoltarBtn = document.getElementById('assinatura-cancel-voltar');
    if (cancelVoltarBtn) cancelVoltarBtn.addEventListener('click', fecharCancelamento);

    if (cancelModal) {
        cancelModal.addEventListener('click', function (event) {
            if (event.target === cancelModal) fecharCancelamento();
        });
    }

    if (cancelConfirmBtn) {
        cancelConfirmBtn.addEventListener('click', function () {
            limparErroCancelamento();
            setCancelando(true);

            fetch(window.__CANCELAR_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.__CSRF, 'Accept': 'application/json' },
            })
                .then(function (r) {
                    return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, j: j }; });
                })
                .then(function (res) {
                    if (!res.ok) {
                        mostrarErroCancelamento(res.j.error || 'Não foi possível cancelar a assinatura agora.');
                        setCancelando(false);
                        return;
                    }
                    window.location.reload();
                })
                .catch(function () {
                    mostrarErroCancelamento('Falha de rede. Tente novamente em alguns instantes.');
                    setCancelando(false);
                });
        });
    }

    window._cleanupFunctions = window._cleanupFunctions || {};
    window._cleanupFunctions.planos = function () {
        if (controller && controller.unmount) { try { controller.unmount(); } catch (e) {} }
        controller = null;
        if (root) { root.__init = false; }
    };
};

if (document.getElementById('assinatura-modal')) { window.initPlanos(); }
