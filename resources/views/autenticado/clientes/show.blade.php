{{-- Cliente - Detalhes --}}
@php
    $clienteNome = $cliente->razao_social ?? $cliente->nome ?? 'Cliente';
    $tipoPessoaBadge = $cliente->tipo_pessoa === 'PJ'
        ? ['label' => 'PJ', 'hex' => '#374151']
        : ['label' => 'PF', 'hex' => '#6b7280'];
    $statusBadge = $cliente->ativo
        ? ['label' => 'ATIVO', 'hex' => '#047857']
        : ['label' => 'INATIVO', 'hex' => '#dc2626'];
    $empresaBadge = ['label' => 'EMPRESA PRÓPRIA', 'hex' => '#0f766e'];
    $resumoCliente = [
        ['label' => 'Participantes Vinculados', 'valor' => number_format($totalParticipantes, 0, ',', '.'), 'sub' => 'Base vinculada ao cadastro'],
        ['label' => 'Notas Fiscais', 'valor' => number_format($totalNotas, 0, ',', '.'), 'sub' => 'Notas unificadas EFD e XML'],
        ['label' => 'Localização', 'valor' => implode(' / ', array_filter([$cliente->municipio, $cliente->uf])) ?: 'Não informado', 'sub' => 'Município e UF'],
        [
            'label' => 'Origem',
            'valor' => $origemCliente['label'],
            'sub' => $origemCliente['arquivo'] ?: 'Sem importação vinculada',
            'sub_clamp' => true,
            'link_url' => $origemCliente['url'],
            'link_label' => 'Ver resultado da importação',
        ],
    ];
@endphp

<x-cockpit.layout
    container-id="cliente-show-container"
    :titulo="$clienteNome"
    :subtitulo="$cliente->documento_formatado"
    eyebrow="Cliente"
    resumo-titulo="Visão Geral"
>
    <x-slot:badges>
        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $tipoPessoaBadge['hex'] }}">{{ $tipoPessoaBadge['label'] }}</span>
        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusBadge['hex'] }}">{{ $statusBadge['label'] }}</span>
        @if($cliente->is_empresa_propria)
            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $empresaBadge['hex'] }}">{{ $empresaBadge['label'] }}</span>
        @endif
    </x-slot:badges>

    <x-slot:principal>
        <div class="flex max-w-2xl flex-wrap justify-start gap-2 lg:justify-end" data-perfil-acoes-superiores>
            <a href="/app/clientes" data-link class="auth-control inline-flex items-center rounded border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Voltar
            </a>
            <a href="{{ route('app.cliente.edit', $cliente->id) }}" data-link class="auth-control inline-flex items-center justify-center rounded bg-gray-800 px-4 text-sm font-semibold text-white hover:bg-gray-700">
                Editar cadastro
            </a>
            {{-- Botão único Exportar → modal de formato (design system). PDF inclui os
                 top N participantes escolhidos; XLSX traz só o cliente. --}}
            <x-export-menu id="modal-exportar-cliente-show" label="Exportar"
                           titulo="Exportar dossiê" class="px-3 text-sm font-medium"
                           descricao="Dossiê de {{ $clienteNome }}. Escolha o formato.">
                <div>
                    <label class="block text-[11px] text-gray-500 mb-1" for="dossie-show-top">Participantes no dossiê (só PDF)</label>
                    <select id="dossie-show-top" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                        <option value="0">Nenhum — somente o cliente</option>
                        <option value="10" selected>Top 10 por volume EFD</option>
                        <option value="20">Top 20 por volume EFD</option>
                        <option value="50">Top 50 por volume EFD</option>
                    </select>
                </div>
                <x-export-grupo label="Documento" />
                <x-export-option format="pdf" modal-id="modal-exportar-cliente-show"
                                 overlay="download-overlay-cliente-show"
                                 path="/app/cliente/{{ $cliente->id }}/dossie"
                                 :extras="['dossie-show-top' => 'top']"
                                 descricao="Dossiê do cliente; inclui os participantes escolhidos acima." />
                <x-export-grupo label="Planilha" />
                <x-export-option format="xlsx" modal-id="modal-exportar-cliente-show"
                                 overlay="download-overlay-cliente-show"
                                 path="/app/cliente/{{ $cliente->id }}/dossie" query="formato=xlsx"
                                 descricao="Dados do cliente em planilha. Não inclui participantes." />
            </x-export-menu>
            @if(!$cliente->is_empresa_propria)
                <button type="button" id="btn-excluir-cliente" data-id="{{ $cliente->id }}" data-nome="{{ $clienteNome }}" class="auth-control rounded border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Excluir
                </button>
            @endif
        </div>
    </x-slot:principal>

    <x-slot:resumo>
        <x-cockpit.indicadores :itens="$resumoCliente" />
    </x-slot:resumo>

    @include('autenticado.perfis._fluxo-cnpj', ['perfilCnpj' => $perfilCnpj])

        {{-- Overlay do download (spinner) — usado pelo modal Exportar dossiê (GET via iframe). --}}
        <x-download-overlay id="download-overlay-cliente-show" texto="Gerando arquivo…" />

        @if(!$cliente->is_empresa_propria)
            <div id="modal-excluir-show" class="fixed inset-0 z-50 hidden">
                <div class="absolute inset-0 bg-black/40" id="modal-excluir-show-overlay"></div>
                <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded border border-gray-300 w-full max-w-md overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Excluir Cliente</span>
                        </div>
                        <div class="p-5">
                            <p class="text-sm text-gray-700">
                                Confirmar exclusão de <strong>{{ $clienteNome }}</strong>? Esta ação remove o cadastro da carteira.
                            </p>
                        </div>
                        <div class="px-4 py-3 border-t border-gray-200 bg-white flex justify-end gap-2">
                            <button type="button" id="btn-cancelar-excluir-show" class="px-3 py-2 text-sm font-medium bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded">
                                Cancelar
                            </button>
                            <button type="button" id="btn-confirmar-excluir-show" class="px-3 py-2 text-sm font-medium bg-gray-800 text-white hover:bg-gray-700 rounded" data-id="{{ $cliente->id }}">
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            (function() {
                var btnExcluir = document.getElementById('btn-excluir-cliente');
                var modal = document.getElementById('modal-excluir-show');
                var overlay = document.getElementById('modal-excluir-show-overlay');
                var btnCancelar = document.getElementById('btn-cancelar-excluir-show');
                var btnConfirmar = document.getElementById('btn-confirmar-excluir-show');

                if (btnExcluir) {
                    btnExcluir.addEventListener('click', function() {
                        if (modal) modal.classList.remove('hidden');
                    });
                }

                function fecharModal() {
                    if (modal) modal.classList.add('hidden');
                }

                if (overlay) overlay.addEventListener('click', fecharModal);
                if (btnCancelar) btnCancelar.addEventListener('click', fecharModal);

                if (btnConfirmar) {
                    btnConfirmar.addEventListener('click', function() {
                        var id = this.dataset.id;
                        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                            || document.querySelector('input[name="_token"]')?.value
                            || '';

                        fetch('/app/cliente/' + id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        })
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                            if (data.success) {
                                if (window.navigateTo) {
                                    window.navigateTo('/app/clientes');
                                } else {
                                    window.location.href = '/app/clientes';
                                }
                            } else {
                                fecharModal();
                                alert(data.message || 'Erro ao excluir cliente.');
                            }
                        })
                        .catch(function() {
                            fecharModal();
                            alert('Erro ao excluir cliente.');
                        });
                    });
                }
            })();
            </script>
        @endif
</x-cockpit.layout>
