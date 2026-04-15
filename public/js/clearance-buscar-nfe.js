(function () {
    const root = document.getElementById('buscar-nfe-container');
    if (!root) return;

    const input = document.getElementById('nfe-chave');
    const button = document.getElementById('btn-consultar-nfe');
    const clienteSelect = document.getElementById('nfe-cliente-id');
    const feedback = document.getElementById('nfe-chave-feedback');
    const count = document.getElementById('nfe-chave-count');
    const documentTypeInputs = Array.from(document.querySelectorAll('.documento-tipo'));
    const documentTypeCards = Array.from(document.querySelectorAll('.documento-tipo-card'));
    const resultCard = document.getElementById('resultado-consulta-dfe');
    const resultStatusBadge = document.getElementById('resultado-status-badge');
    const resultTipo = document.getElementById('resultado-tipo');
    const resultSituacao = document.getElementById('resultado-situacao');
    const resultValor = document.getElementById('resultado-valor');
    const resultEmissao = document.getElementById('resultado-emissao');
    const resultEmitente = document.getElementById('resultado-emitente');
    const resultDestinatario = document.getElementById('resultado-destinatario');
    const resultChave = document.getElementById('resultado-chave');
    const resultCliente = document.getElementById('resultado-cliente');
    const resultPersistencia = document.getElementById('resultado-persistencia');
    const historyEmpty = document.getElementById('historico-dfe-vazio');
    const historyList = document.getElementById('historico-dfe-lista');

    const documentTypes = {
        nfe: {
            label: 'NF-e',
            provider: 'InfoSimples',
            emitenteLabel: 'Emitente simulado',
            destinatarioLabel: 'Destinatário simulado',
        },
        cte: {
            label: 'CT-e',
            provider: 'InfoSimples',
            emitenteLabel: 'Transportador simulado',
            destinatarioLabel: 'Tomador simulado',
        },
        nfse: {
            label: 'NFS-e',
            provider: 'InfoSimples',
            emitenteLabel: 'Prestador simulado',
            destinatarioLabel: 'Tomador simulado',
        },
    };
    const defaultButtonLabel = button ? button.textContent.trim() : 'Consultar documento';

    function onlyDigits(value) {
        return (value || '').replace(/\D/g, '').slice(0, 44);
    }

    function selectedDocumentType() {
        const selected = documentTypeInputs.find((item) => item.checked);
        return documentTypes[selected ? selected.value : 'nfe'] || documentTypes.nfe;
    }

    function selectedDocumentTypeKey() {
        const selected = documentTypeInputs.find((item) => item.checked);
        return selected ? selected.value : 'nfe';
    }

    function selectedCliente() {
        if (!clienteSelect || !clienteSelect.value) {
            return { id: null, nome: 'Sem cliente associado' };
        }

        return {
            id: clienteSelect.value,
            nome: clienteSelect.options[clienteSelect.selectedIndex].text.trim(),
        };
    }

    function updateSelectedCard() {
        const key = selectedDocumentTypeKey();
        documentTypeCards.forEach((card) => {
            card.classList.toggle('is-selected', card.dataset.documentTypeCard === key);
        });
    }

    function buildPayloadPreview() {
        const cliente = selectedCliente();

        return {
            tipo_documento: selectedDocumentTypeKey(),
            chave_acesso: onlyDigits(input.value),
            cliente_id: cliente.id,
        };
    }

    function setButtonLoading(isLoading) {
        if (!button) return;

        if (isLoading) {
            button.disabled = true;
            button.textContent = 'Consultando...';
            return;
        }

        button.textContent = defaultButtonLabel;
        updateState();
    }

    function fillResult(payloadPreview) {
        const documentType = selectedDocumentType();
        const cliente = selectedCliente();

        if (resultCard) resultCard.classList.remove('hidden');
        if (resultStatusBadge) {
            resultStatusBadge.textContent = 'Simulado';
            resultStatusBadge.style.backgroundColor = '#374151';
        }
        if (resultTipo) resultTipo.textContent = documentType.label;
        if (resultSituacao) resultSituacao.textContent = 'Autorizada (prévia visual)';
        if (resultValor) resultValor.textContent = 'R$ 0,00';
        if (resultEmissao) resultEmissao.textContent = new Date().toLocaleDateString('pt-BR');
        if (resultEmitente) resultEmitente.textContent = documentType.emitenteLabel;
        if (resultDestinatario) resultDestinatario.textContent = documentType.destinatarioLabel;
        if (resultChave) resultChave.textContent = payloadPreview.chave_acesso;
        if (resultCliente) resultCliente.textContent = cliente.nome;
        if (resultPersistencia) {
            resultPersistencia.textContent = cliente.id
                ? `Payload futuro preparado com cliente_id=${cliente.id}. O n8n fará a consulta, salvará os dados e associará o documento a este cliente.`
                : 'Payload futuro preparado sem cliente_id. O n8n fará a consulta e salvará o documento sem vínculo direto com cliente.';
        }

        resultCard && resultCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function addHistoryPreview(payloadPreview) {
        if (!historyList) return;

        const documentType = selectedDocumentType();
        const cliente = selectedCliente();
        const row = document.createElement('div');
        row.className = 'px-4 py-3 flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between';
        row.innerHTML = `
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">${documentType.label}</span>
                    <span class="text-sm font-semibold text-gray-900">Documento fiscal</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #d97706">Prévia</span>
                </div>
                <p class="text-xs text-gray-500 font-mono break-all mt-1">${payloadPreview.chave_acesso}</p>
                <p class="text-[11px] text-gray-500 mt-1">${cliente.nome} · ${new Date().toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' })}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400">Ainda não persistida</span>
            </div>
        `;

        if (historyEmpty) historyEmpty.classList.add('hidden');
        historyList.classList.remove('hidden');
        historyList.prepend(row);
    }

    function updateState() {
        const digits = onlyDigits(input.value);
        const documentType = selectedDocumentType();
        input.value = digits;
        updateSelectedCard();

        const length = digits.length;
        count.textContent = String(length);

        if (length === 0) {
            button.disabled = true;
            feedback.textContent = `Informe uma chave de ${documentType.label} com 44 dígitos numéricos.`;
            feedback.className = 'text-[11px] text-gray-500';
            return;
        }

        if (length < 44) {
            button.disabled = true;
            feedback.textContent = `Chave incompleta: faltam ${44 - length} dígito(s).`;
            feedback.className = 'text-[11px] text-amber-700';
            return;
        }

        button.disabled = false;
        feedback.textContent = `Chave de ${documentType.label} válida para consulta.`;
        feedback.className = 'text-[11px] text-green-700';
    }

    input.addEventListener('input', updateState);
    input.addEventListener('paste', () => {
        window.setTimeout(updateState, 0);
    });
    documentTypeInputs.forEach((item) => item.addEventListener('change', updateState));

    button.addEventListener('click', () => {
        if (button.disabled) return;

        const payloadPreview = buildPayloadPreview();
        const documentType = selectedDocumentType();
        feedback.textContent = `Consultando ${documentType.label} pela ${documentType.provider}.`;
        feedback.className = 'text-[11px] text-gray-700';
        setButtonLoading(true);

        window.setTimeout(() => {
            fillResult(payloadPreview);
            addHistoryPreview(payloadPreview);
            setButtonLoading(false);
            feedback.textContent = 'Prévia visual concluída. O próximo passo técnico será trocar esta simulação pelo POST Laravel e workflow n8n.';
            feedback.className = 'text-[11px] text-green-700';
        }, 700);
    });

    updateState();
})();
