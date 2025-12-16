{{-- Inteligência Tributária - Autenticado --}}
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Inteligência Tributária
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Análises avançadas e insights sobre situação tributária e oportunidades de otimização
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Área de Upload --}}
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Enviar Documentos para Análise</h2>
                    <p class="text-sm text-gray-600">Arraste e solte seus documentos ou clique para selecionar</p>
                </div>

                {{-- Área de Drag & Drop --}}
                <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-indigo-400 transition-colors cursor-pointer bg-gray-50 hover:bg-indigo-50">
                    <div class="mb-4">
                        <svg class="mx-auto h-16 w-16 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <p class="text-lg text-gray-700 mb-2 font-semibold">
                        Arraste e solte documentos aqui
                    </p>
                    <p class="text-sm text-gray-500 mb-4">ou</p>
                    <label for="doc-files" class="inline-block cursor-pointer px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                        Selecionar Arquivos
                        <input type="file" id="doc-files" name="docs[]" multiple accept=".pdf,.xlsx,.xls,.xml" class="hidden">
                    </label>
                    <p class="text-xs text-gray-500 mt-4">Formatos aceitos: PDF, XLSX, XLS, XML</p>
                </div>

                {{-- Lista de Arquivos Selecionados --}}
                <div id="files-list" class="mt-6 hidden">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Arquivos selecionados:</h3>
                    <div id="files-container" class="space-y-2"></div>
                    <div class="mt-4 flex gap-3">
                        <button id="upload-btn" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                            Enviar para Análise
                        </button>
                        <button id="clear-btn" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                            Limpar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informações --}}
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
            <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-indigo-900 mb-1">Como funciona</h3>
                    <p class="text-sm text-indigo-800">
                        Os documentos serão analisados automaticamente via API. O sistema identificará oportunidades de otimização tributária, riscos e recomendações personalizadas.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('doc-files');
    const filesList = document.getElementById('files-list');
    const filesContainer = document.getElementById('files-container');
    const uploadBtn = document.getElementById('upload-btn');
    const clearBtn = document.getElementById('clear-btn');
    let selectedFiles = [];

    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-indigo-500', 'bg-indigo-50');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('border-indigo-500', 'bg-indigo-50');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-indigo-500', 'bg-indigo-50');
        const files = Array.from(e.dataTransfer.files).filter(f => 
            f.name.endsWith('.pdf') || f.name.endsWith('.xlsx') || f.name.endsWith('.xls') || f.name.endsWith('.xml')
        );
        handleFiles(files);
    });

    fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        handleFiles(files);
    });

    function handleFiles(files) {
        selectedFiles = [...selectedFiles, ...files];
        updateFilesList();
    }

    function updateFilesList() {
        filesContainer.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
            fileItem.innerHTML = `
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-sm text-gray-700">${file.name}</span>
                    <span class="text-xs text-gray-500">(${(file.size / 1024).toFixed(2)} KB)</span>
                </div>
                <button onclick="removeFile(${index})" class="text-red-500 hover:text-red-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            filesContainer.appendChild(fileItem);
        });
        filesList.classList.remove('hidden');
    }

    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        updateFilesList();
        if (selectedFiles.length === 0) {
            filesList.classList.add('hidden');
        }
    };

    clearBtn.addEventListener('click', () => {
        selectedFiles = [];
        fileInput.value = '';
        filesList.classList.add('hidden');
    });

    uploadBtn.addEventListener('click', () => {
        if (selectedFiles.length === 0) return;
        alert('Funcionalidade de upload será implementada via API/n8n');
    });
});
</script>
