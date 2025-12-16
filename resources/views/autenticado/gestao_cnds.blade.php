{{-- Gestão de CNDs - Autenticado --}}
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Gestão de CNDs
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Gerencie Certidões Negativas de Débito e monitore vencimentos
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
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Enviar Certidões (CNDs)</h2>
                    <p class="text-sm text-gray-600">Arraste e solte suas certidões ou clique para selecionar</p>
                </div>

                {{-- Área de Drag & Drop --}}
                <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-orange-400 transition-colors cursor-pointer bg-gray-50 hover:bg-orange-50">
                    <div class="mb-4">
                        <svg class="mx-auto h-16 w-16 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <p class="text-lg text-gray-700 mb-2 font-semibold">
                        Arraste e solte certidões aqui
                    </p>
                    <p class="text-sm text-gray-500 mb-4">ou</p>
                    <label for="cnd-files" class="inline-block cursor-pointer px-6 py-3 bg-orange-600 text-white rounded-lg font-semibold hover:bg-orange-700 transition-colors">
                        Selecionar Arquivos
                        <input type="file" id="cnd-files" name="cnds[]" multiple accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                    </label>
                    <p class="text-xs text-gray-500 mt-4">Formatos aceitos: PDF, JPG, PNG</p>
                </div>

                {{-- Lista de Arquivos Selecionados --}}
                <div id="files-list" class="mt-6 hidden">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Arquivos selecionados:</h3>
                    <div id="files-container" class="space-y-2"></div>
                    <div class="mt-4 flex gap-3">
                        <button id="upload-btn" class="px-6 py-2 bg-orange-600 text-white rounded-lg font-semibold hover:bg-orange-700 transition-colors">
                            Enviar Arquivos
                        </button>
                        <button id="clear-btn" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                            Limpar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informações --}}
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
            <div class="flex items-start gap-4">
                <svg class="w-6 h-6 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-orange-900 mb-1">Como funciona</h3>
                    <p class="text-sm text-orange-800">
                        As certidões serão processadas automaticamente via API. O sistema extrairá informações, monitorará vencimentos e enviará alertas quando necessário.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('cnd-files');
    const filesList = document.getElementById('files-list');
    const filesContainer = document.getElementById('files-container');
    const uploadBtn = document.getElementById('upload-btn');
    const clearBtn = document.getElementById('clear-btn');
    let selectedFiles = [];

    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-orange-500', 'bg-orange-50');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('border-orange-500', 'bg-orange-50');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-orange-500', 'bg-orange-50');
        const files = Array.from(e.dataTransfer.files).filter(f => 
            f.name.endsWith('.pdf') || f.name.endsWith('.jpg') || f.name.endsWith('.jpeg') || f.name.endsWith('.png')
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
