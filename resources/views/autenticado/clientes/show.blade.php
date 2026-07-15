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
    $situacaoCadastral = trim((string) ($cliente->situacao_cadastral ?? ''));
    $situacaoCadastral = $situacaoCadastral !== '' && $situacaoCadastral !== '—' ? $situacaoCadastral : 'Não consultada';
    $situacaoCadastralBadge = ['label' => $situacaoCadastral, 'hex' => \App\Support\Reports\ReportTheme::statusHex($situacaoCadastral)];
    $regimeTributario = trim((string) ($cliente->regime_tributario ?? ''));
    $regimeTributario = $regimeTributario !== '' && $regimeTributario !== '—' ? $regimeTributario : 'Não consultado';
    $regimeTributarioBadge = ['label' => $regimeTributario, 'hex' => \App\Support\Reports\ReportTheme::regimeHex($regimeTributario)];
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
    $dadosCadastrais = [
        ['label' => $cliente->tipo_pessoa === 'PJ' ? 'Razão Social' : 'Nome', 'valor' => $cliente->razao_social ?? $cliente->nome ?? '-', 'mono' => false, 'destaque' => true],
        ['label' => $cliente->tipo_pessoa === 'PJ' ? 'Nome Fantasia' : 'Nome de Exibição', 'valor' => $cliente->nome ?? '-', 'mono' => false, 'destaque' => true],
        ['label' => $cliente->tipo_pessoa === 'PJ' ? 'CNPJ' : 'CPF', 'valor' => $cliente->documento_formatado, 'mono' => true],
        ['label' => 'E-mail', 'valor' => $cliente->email ?: 'Não informado', 'mono' => false, 'href' => $cliente->email ? 'mailto:'.$cliente->email : null],
        ['label' => 'Telefone', 'valor' => $cliente->telefone ?: 'Não informado', 'mono' => false, 'href' => $cliente->telefone ? 'tel:'.preg_replace('/\D/', '', $cliente->telefone) : null],
        ['label' => 'Endereço completo', 'valor' => $cliente->endereco ?: 'Não informado', 'mono' => false],
        ['label' => 'Município / UF', 'valor' => implode(' - ', array_filter([$cliente->municipio, $cliente->uf])) ?: 'Não informado', 'mono' => false],
        ['label' => 'CEP', 'valor' => $cliente->cep ?: 'Não informado', 'mono' => true],
        ['label' => 'Situação Cadastral', 'badge' => $situacaoCadastralBadge],
        ['label' => 'Regime Tributário', 'badge' => $regimeTributarioBadge],
        ['label' => 'Status do Cadastro', 'valor' => $cliente->ativo ? 'Ativo' : 'Inativo', 'mono' => false],
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
        <a href="{{ route('app.cliente.edit', $cliente->id) }}" data-link class="auth-control inline-flex items-center justify-center rounded bg-gray-800 px-4 text-sm font-semibold text-white hover:bg-gray-700">
            Editar cadastro
        </a>
    </x-slot:principal>

    <x-slot:acoes>
        <a href="/app/clientes" data-link class="auth-control inline-flex items-center rounded border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Voltar para clientes
        </a>
        <button type="button" id="btn-dossie-cliente" class="auth-control px-3 text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">
            Dossiê PDF
        </button>
        @if(!$cliente->is_empresa_propria)
            <button type="button" id="btn-excluir-cliente" data-id="{{ $cliente->id }}" data-nome="{{ $clienteNome }}" class="auth-control px-3 text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">
                Excluir
            </button>
        @endif
    </x-slot:acoes>

    <x-slot:resumo>
        <x-cockpit.indicadores :itens="$resumoCliente" />
    </x-slot:resumo>

    <div class="space-y-4 sm:space-y-6 min-w-0" data-cockpit-profile-flow>
        <x-cockpit.secao
            titulo="Dados Cadastrais"
            subtitulo="Identificação, contato, localização e enquadramento fiscal."
            body-class="p-0"
        >
            <x-slot:acao>
                <span class="whitespace-nowrap rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusBadge['hex'] }}">
                    Cadastro {{ $statusBadge['label'] }}
                </span>
            </x-slot:acao>
            <x-cockpit.dados :itens="$dadosCadastrais" />
        </x-cockpit.secao>

                <div class="mt-0">
                    @include('autenticado.partials.notas-fiscais-card', [
                        'notas' => $notasFiscais,
                        'totalNotas' => $totalNotasFiscais,
                        'ajaxUrl' => $notasAjaxUrl,
                        'contexto' => $notasContexto,
                        'entityId' => $notasEntityId,
                    ])
                </div>

                {{-- Detalhamento do Score --}}
                <x-cockpit.secao titulo="Detalhamento do Score">
                    @include('autenticado.partials._score-detalhamento', [
                        'detalhamento' => $score_detalhamento ?? [],
                        'scoreTotal' => $score['score_total'] ?? null,
                        'classificacao' => $score['classificacao'] ?? 'nao_avaliado',
                        'comHeadline' => true,
                    ])
                </x-cockpit.secao>

                @include('autenticado.monitoramento._movimentacao-listas', ['top_produtos' => $top_produtos ?? [], 'top_cfops' => $top_cfops ?? []])
        </div>

        <div>
            @include('autenticado.partials._historico-consultas-perfil', [
                'historicoConsultasPerfil' => $historicoConsultasPerfil ?? collect(),
                'documentoPerfil' => $cliente->documento,
            ])
        </div>

        {{-- Modal do dossiê: o usuário escolhe quantos participantes vinculados
             entram junto no PDF (nenhum ou top N por volume EFD). --}}
        <div id="modal-dossie-show" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/40" id="modal-dossie-show-overlay"></div>
            <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded border border-gray-300 w-full max-w-md overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Dossiê do Cliente</span>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-gray-700 mb-4">
                            Dossiê de <strong>{{ $clienteNome }}</strong> em PDF. Escolha quantos participantes vinculados quer ver junto no documento.
                        </p>
                        <label class="block text-[11px] text-gray-500 mb-1" for="dossie-show-top">Participantes no dossiê</label>
                        <select id="dossie-show-top" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
                            <option value="0">Nenhum — somente o cliente</option>
                            <option value="10" selected>Top 10 por volume EFD</option>
                            <option value="20">Top 20 por volume EFD</option>
                            <option value="50">Top 50 por volume EFD</option>
                        </select>
                        <p class="text-[11px] text-gray-400 mt-2">A planilha (XLSX) traz os dados do cliente; a seleção de participantes vale só para o PDF.</p>
                    </div>
                    <div class="px-4 py-3 border-t border-gray-200 bg-white flex justify-end gap-2">
                        <button type="button" id="btn-cancelar-dossie-show" class="px-3 py-2 text-sm font-medium bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded">
                            Cancelar
                        </button>
                        <button type="button" id="btn-dossie-xlsx-show" class="px-3 py-2 text-sm font-medium text-white rounded hover:opacity-90" style="background-color: #047857" data-id="{{ $cliente->id }}">
                            Planilha (XLSX)
                        </button>
                        <button type="button" id="btn-confirmar-dossie-show" class="px-3 py-2 text-sm font-medium bg-gray-800 text-white hover:bg-gray-700 rounded" data-id="{{ $cliente->id }}">
                            Gerar PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function() {
            var btnDossie = document.getElementById('btn-dossie-cliente');
            var modal = document.getElementById('modal-dossie-show');
            var overlay = document.getElementById('modal-dossie-show-overlay');
            var btnCancelar = document.getElementById('btn-cancelar-dossie-show');
            var btnConfirmar = document.getElementById('btn-confirmar-dossie-show');

            function fecharModal() {
                if (modal) modal.classList.add('hidden');
            }

            if (btnDossie) {
                btnDossie.addEventListener('click', function() {
                    if (modal) modal.classList.remove('hidden');
                });
            }
            if (overlay) overlay.addEventListener('click', fecharModal);
            if (btnCancelar) btnCancelar.addEventListener('click', fecharModal);

            if (btnConfirmar) {
                btnConfirmar.addEventListener('click', function() {
                    var top = document.getElementById('dossie-show-top');
                    var valor = top ? top.value : '0';
                    // GET direto: o navegador trata a resposta como download e a página fica.
                    window.location.href = '/app/cliente/' + this.dataset.id + '/dossie' + (valor !== '0' ? '?top=' + valor : '');
                    fecharModal();
                    if (window.showToast) window.showToast('Gerando dossiê... o download começa em instantes.', 'info');
                });
            }

            var btnXlsx = document.getElementById('btn-dossie-xlsx-show');
            if (btnXlsx) {
                btnXlsx.addEventListener('click', function() {
                    window.location.href = '/app/cliente/' + this.dataset.id + '/dossie?formato=xlsx';
                    fecharModal();
                    if (window.showToast) window.showToast('Gerando planilha... o download começa em instantes.', 'info');
                });
            }
        })();
        </script>

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
