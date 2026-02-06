{{-- Minha Empresa - Configurar --}}
<div class="min-h-screen bg-gray-50" id="minha-empresa-configurar">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Configurar Minha Empresa</h1>
            <p class="mt-2 text-gray-600">Selecione qual empresa voce deseja monitorar como sua empresa principal.</p>
        </div>

        {{-- Card de selecao --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Suas Empresas (Pessoa Juridica)</h3>
            </div>

            @if(($clientes ?? collect())->count() > 0)
                <div class="p-6 space-y-3" id="lista-empresas">
                    @foreach($clientes as $cliente)
                        <div class="empresa-item flex items-center justify-between p-4 rounded-lg border-2 transition-all cursor-pointer hover:border-blue-300 {{ ($empresaAtual && $empresaAtual->id === $cliente->id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}"
                             data-cliente-id="{{ $cliente->id }}"
                             onclick="selecionarEmpresa({{ $cliente->id }})">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center {{ ($empresaAtual && $empresaAtual->id === $cliente->id) ? 'bg-blue-100' : '' }}">
                                    <svg class="w-6 h-6 {{ ($empresaAtual && $empresaAtual->id === $cliente->id) ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $cliente->razao_social ?? $cliente->nome }}</p>
                                    <p class="text-sm text-gray-500">CNPJ: {{ $cliente->documento_formatado }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                @if($empresaAtual && $empresaAtual->id === $cliente->id)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Empresa Principal
                                    </span>
                                @else
                                    <button type="button" class="btn-selecionar inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700" onclick="event.stopPropagation(); definirPrincipal({{ $cliente->id }})">
                                        Selecionar
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Estado vazio --}}
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhuma empresa cadastrada</h3>
                    <p class="mt-2 text-sm text-gray-500">Voce ainda nao possui empresas (PJ) cadastradas no sistema.</p>
                    <a href="/app/novo_cliente" data-link class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow-sm transition hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Cadastrar Empresa
                    </a>
                </div>
            @endif
        </div>

        {{-- Informacoes adicionais --}}
        <div class="mt-6 bg-blue-50 rounded-xl border border-blue-200 p-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="font-semibold text-blue-800">O que acontece ao selecionar?</h4>
                    <ul class="mt-2 space-y-1 text-sm text-blue-700">
                        <li>- A empresa selecionada aparecera no dashboard "Minha Empresa"</li>
                        <li>- Voce podera monitorar CNDs, situacao cadastral e score de risco</li>
                        <li>- Alertas e lembretes serao personalizados para esta empresa</li>
                        <li>- Voce pode alterar a empresa principal a qualquer momento</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Voltar --}}
        @if($empresaAtual ?? false)
            <div class="mt-6 text-center">
                <a href="/app/minha-empresa" data-link class="text-gray-600 hover:text-blue-600 transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar para Minha Empresa
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function selecionarEmpresa(clienteId) {
    // Visual feedback
    document.querySelectorAll('.empresa-item').forEach(item => {
        item.classList.remove('border-blue-500', 'bg-blue-50');
        item.classList.add('border-gray-200');
    });

    const selectedItem = document.querySelector(`[data-cliente-id="${clienteId}"]`);
    if (selectedItem) {
        selectedItem.classList.remove('border-gray-200');
        selectedItem.classList.add('border-blue-500', 'bg-blue-50');
    }
}

function definirPrincipal(clienteId) {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Salvando...';

    fetch('/app/minha-empresa/definir-principal', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ cliente_id: clienteId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirecionar usando SPA se disponivel
            if (window.spaNavigate) {
                window.spaNavigate(data.redirect || '/app/minha-empresa');
            } else {
                window.location.href = data.redirect || '/app/minha-empresa';
            }
        } else {
            alert(data.message || 'Erro ao definir empresa principal');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao salvar. Tente novamente.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
