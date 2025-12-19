{{-- RAF - Upload de SPED Autenticado --}}
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page title (o layout já tem header global) --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">RAF - Regime Tributário & SPED</h1>
            <p class="mt-1 text-sm text-gray-600">Envie o SPED para o n8n processar e visualize o CSV retornado.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            {{-- Formulário de Upload --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md">
                <div class="p-6 space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Processar SPED</h2>
                        <p class="mt-1 text-sm text-gray-600">1) Escolha o tipo de SPED. 2) Selecione/arraste o arquivo .txt. 3) Envie.</p>
                    </div>

                    <form id="sped-form" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:items-end">
                            <div class="space-y-2">
                                <label for="tipo" class="block text-sm font-semibold text-gray-800">Tipo de SPED</label>
                                <select 
                                    id="tipo" 
                                    name="tipo" 
                                    class="w-full rounded-lg border border-gray-300 bg-white text-gray-800 shadow-sm transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 focus:ring-offset-gray-50 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                                >
                                    <option value="" selected disabled>Selecione o tipo</option>
                                    <option value="EFD Contribuições">EFD Contribuições</option>
                                    <option value="EFD Fiscal">EFD Fiscal</option>
                                </select>
                                <p id="tipo-hint" class="text-xs text-gray-500">Selecione para liberar o upload.</p>
                            </div>

                            <div class="space-y-2">
                                <label for="sped" class="block text-sm font-semibold text-gray-800">Arquivo SPED (.txt)</label>

                                <input
                                    type="file"
                                    id="sped"
                                    name="sped"
                                    accept=".txt,text/plain"
                                    class="sr-only"
                                    disabled
                                >

                                <div
                                    id="sped-dropzone"
                                    class="w-full rounded-xl border-2 border-dashed px-4 py-6 text-center transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                                    role="button"
                                    tabindex="0"
                                    aria-disabled="true"
                                    aria-describedby="sped-file-help"
                                >
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <div class="space-y-0.5">
                                            <p class="text-sm font-semibold text-gray-900" id="sped-dropzone-title">Nenhum arquivo selecionado</p>
                                            <p class="text-xs text-gray-500" id="sped-dropzone-subtitle">Selecione o tipo de SPED para liberar o upload.</p>
                                        </div>
                                    </div>
                                </div>

                                <div id="sped-file-meta" class="hidden rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate" id="sped-file-name"></p>
                                            <p class="text-xs text-gray-500" id="sped-file-size"></p>
                                        </div>
                                        <button 
                                            type="button" 
                                            id="sped-change-file" 
                                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 transition hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 active:bg-gray-100 px-3 py-1.5 text-sm"
                                        >
                                            Trocar arquivo
                                        </button>
                                    </div>
                                </div>

                                <p id="sped-file-help" class="text-xs text-gray-500">Máximo 10 MB.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    type="submit"
                                    class="btn-primary-solid px-5 py-2.5 gap-2 font-semibold shadow-sm disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:bg-primary-500"
                                    id="sped-submit"
                                    disabled
                                >
                                    <svg id="sped-submit-spinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <svg id="sped-submit-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                    <span id="sped-submit-label">Enviar SPED</span>
                                </button>
                            </div>

                            <div id="sped-alert" class="hidden rounded-xl border border-gray-200 bg-white text-gray-700 px-4 py-3 text-sm" role="status" aria-live="polite">
                                <div class="flex gap-2">
                                    <span id="sped-alert-icon" class="mt-0.5">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                                        </svg>
                                    </span>
                                    <p id="sped-alert-text" class="min-w-0"></p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Resultados --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-md">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Resultado do CSV</h3>
                            <p class="mt-1 text-sm text-gray-600">Será exibido aqui após o processamento.</p>
                        </div>
                        <div id="result-badge" class="hidden px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                            Processado
                        </div>
                    </div>

                    <div id="result-empty" class="text-sm text-gray-600">Nenhum resultado ainda.</div>

                    <div id="result-table-container" class="hidden overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50" id="result-thead"></thead>
                            <tbody class="divide-y divide-gray-200" id="result-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    function initRaf() {
    const form = document.getElementById('sped-form');
    if (!form) return;
    if (form.dataset.rafInitialized === '1') return;
    form.dataset.rafInitialized = '1';

    const submitBtn = document.getElementById('sped-submit');
    const resultBadge = document.getElementById('result-badge');
    const resultEmpty = document.getElementById('result-empty');
    const tableContainer = document.getElementById('result-table-container');
    const theadEl = document.getElementById('result-thead');
    const tbodyEl = document.getElementById('result-tbody');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const tipoSelect = document.getElementById('tipo');
    const fileInput = document.getElementById('sped');
    const submitLabel = document.getElementById('sped-submit-label');
    const submitSpinner = document.getElementById('sped-submit-spinner');
    const submitIcon = document.getElementById('sped-submit-icon');

    const dropzone = document.getElementById('sped-dropzone');
    const dropzoneTitle = document.getElementById('sped-dropzone-title');
    const dropzoneSubtitle = document.getElementById('sped-dropzone-subtitle');
    const fileMeta = document.getElementById('sped-file-meta');
    const fileNameEl = document.getElementById('sped-file-name');
    const fileSizeEl = document.getElementById('sped-file-size');
    const changeFileBtn = document.getElementById('sped-change-file');

    const alertEl = document.getElementById('sped-alert');
    const alertTextEl = document.getElementById('sped-alert-text');
    const alertIconWrap = document.getElementById('sped-alert-icon');

    let isLoading = false;

    const formatFileSize = (bytes) => {
        if (!Number.isFinite(bytes)) return '';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${Math.round((bytes / Math.pow(k, i)) * 10) / 10} ${sizes[i]}`;
    };

    const showAlert = (type, message) => {
        if (!alertEl || !alertTextEl) return;

        // Classes base do alert
        const baseClasses = 'rounded-xl border px-4 py-3 text-sm';
        
        // Classes específicas por tipo
        const typeClasses = {
            info: 'border-gray-200 bg-white text-gray-700',
            success: 'border-green-200 bg-green-50 text-green-800',
            error: 'border-red-200 bg-red-50 text-red-800',
        };

        alertEl.className = `${baseClasses} ${typeClasses[type] || typeClasses.info}`;
        alertTextEl.textContent = message || '';
        alertEl.classList.toggle('hidden', !message);

        if (alertIconWrap) {
            if (type === 'success') {
                alertIconWrap.innerHTML = `
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                `;
            } else if (type === 'error') {
                alertIconWrap.innerHTML = `
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                `;
            } else {
                alertIconWrap.innerHTML = `
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                    </svg>
                `;
            }
        }
    };

    const setDropzoneEnabled = (enabled) => {
        if (!dropzone) return;
        
        // Classes base da dropzone
        const baseClasses = 'w-full rounded-xl border-2 border-dashed px-4 py-6 text-center transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2';
        
        if (enabled) {
            // Dropzone habilitada
            dropzone.className = `${baseClasses} border-gray-300 bg-white hover:border-primary-400 hover:bg-primary-50/30 cursor-pointer`;
            dropzone.setAttribute('aria-disabled', 'false');
        } else {
            // Dropzone desabilitada
            dropzone.className = `${baseClasses} border-gray-300 bg-gray-100 pointer-events-none cursor-not-allowed opacity-60`;
            dropzone.setAttribute('aria-disabled', 'true');
        }
        
        dropzoneSubtitle.textContent = enabled
            ? 'Arraste e solte aqui, ou clique para selecionar.'
            : 'Selecione o tipo de SPED para liberar o upload.';
    };

    const updateFileUi = () => {
        const file = fileInput.files?.[0];
        if (!file) {
            dropzoneTitle.textContent = 'Nenhum arquivo selecionado';
            fileMeta.classList.add('hidden');
            fileNameEl.textContent = '';
            fileSizeEl.textContent = '';
            return;
        }

        dropzoneTitle.textContent = file.name;
        fileMeta.classList.remove('hidden');
        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = `${formatFileSize(file.size)} • ${file.type || 'text/plain'}`;
    };

    const updateEnablement = () => {
        const hasTipo = tipoSelect.value !== '';
        const fileEnabled = hasTipo && !isLoading;
        fileInput.disabled = !fileEnabled;
        setDropzoneEnabled(fileEnabled);
        const hasFile = fileInput.files?.length > 0;
        submitBtn.disabled = !(hasTipo && hasFile) || isLoading;
    };

    tipoSelect.addEventListener('change', updateEnablement);
    fileInput.addEventListener('change', () => {
        updateFileUi();
        updateEnablement();
        if (fileInput.files?.length) {
            showAlert('info', '');
        }
    });

    const openFilePicker = () => {
        if (fileInput.disabled || isLoading) return;
        fileInput.click();
    };

    if (dropzone) {
        dropzone.addEventListener('click', openFilePicker);
        dropzone.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openFilePicker();
            }
        });

        const setDragOver = (on) => {
            if (!dropzone) return;
            const isEnabled = dropzone.getAttribute('aria-disabled') === 'false';
            if (!isEnabled) return;
            
            if (on) {
                dropzone.classList.remove('border-gray-300', 'bg-white', 'hover:border-primary-400', 'hover:bg-primary-50/30');
                dropzone.classList.add('border-primary-500', 'bg-primary-50/50');
            } else {
                dropzone.classList.remove('border-primary-500', 'bg-primary-50/50');
                dropzone.classList.add('border-gray-300', 'bg-white', 'hover:border-primary-400', 'hover:bg-primary-50/30');
            }
        };

        dropzone.addEventListener('dragenter', (e) => {
            if (fileInput.disabled || isLoading) return;
            e.preventDefault();
            setDragOver(true);
        });
        dropzone.addEventListener('dragover', (e) => {
            if (fileInput.disabled || isLoading) return;
            e.preventDefault();
            setDragOver(true);
        });
        dropzone.addEventListener('dragleave', () => setDragOver(false));
        dropzone.addEventListener('drop', (e) => {
            if (fileInput.disabled || isLoading) return;
            e.preventDefault();
            setDragOver(false);

            const file = e.dataTransfer?.files?.[0];
            if (!file) return;
            const isTxt = file.name?.toLowerCase().endsWith('.txt') || file.type === 'text/plain';
            if (!isTxt) {
                showAlert('error', 'Selecione um arquivo .txt');
                return;
            }

            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    if (changeFileBtn) {
        changeFileBtn.addEventListener('click', openFilePicker);
    }

    const setLoading = (loading, message = '') => {
        isLoading = !!loading;
        submitLabel.textContent = isLoading ? 'Enviando...' : 'Enviar SPED';
        submitSpinner.classList.toggle('hidden', !isLoading);
        submitIcon.classList.toggle('hidden', isLoading);
        tipoSelect.disabled = isLoading;
        updateEnablement();

        if (message) {
            showAlert('info', message);
        }
    };

    const renderTable = (headers, rows) => {
        if (!headers.length || !rows.length) {
            tableContainer.classList.add('hidden');
            resultEmpty.classList.remove('hidden');
            return;
        }

        theadEl.innerHTML = `
            <tr>
                ${headers.map(h => `<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 whitespace-nowrap">${h}</th>`).join('')}
            </tr>
        `;

        tbodyEl.innerHTML = rows.map(row => `
            <tr class="odd:bg-white even:bg-gray-50/50 hover:bg-primary-50/40">
                ${row.map(cell => `<td class="px-4 py-3 text-gray-700 whitespace-normal break-words align-top">${cell ?? ''}</td>`).join('')}
            </tr>
        `).join('');

        resultEmpty.classList.add('hidden');
        tableContainer.classList.remove('hidden');
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const tipo = tipoSelect.value;
        const file = fileInput.files[0];

        if (!tipo) {
            showAlert('error', 'Selecione o tipo de SPED.');
            return;
        }

        if (!file) {
            showAlert('error', 'Selecione um arquivo .txt');
            return;
        }

        showAlert('info', 'Enviando...');
        setLoading(true);

        const formData = new FormData();
        formData.append('tipo', tipo);
        formData.append('sped', file);

        try {
            const response = await fetch('/app/solucoes/raf/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf || ''
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Falha ao processar o SPED.');
            }

            showAlert('success', 'Processado com sucesso.');
            resultBadge.classList.remove('hidden');
            renderTable(data.headers || [], data.rows || []);

            // Reset parcial após sucesso
            form.reset();
            updateFileUi();
            updateEnablement();
        } catch (err) {
            showAlert('error', err.message || 'Erro inesperado.');
            resultBadge.classList.add('hidden');
            tableContainer.classList.add('hidden');
            resultEmpty.classList.remove('hidden');
        } finally {
            setLoading(false);
        }
    });

    // Estado inicial
    updateFileUi();
    updateEnablement();
    }

    // Expor para o SPA (resources/js/spa.js chama initRaf ao navegar)
    window.initRaf = initRaf;

    // Também rodar na primeira carga (full reload)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRaf, { once: true });
    } else {
        initRaf();
    }
})();
</script>

