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
            'url' => $origemCliente['url'],
        ],
    ];
    $dadosCadastrais = [
        ['label' => $cliente->tipo_pessoa === 'PJ' ? 'Razão Social' : 'Nome', 'valor' => $cliente->razao_social ?? $cliente->nome ?? '-', 'mono' => false, 'destaque' => true, 'span' => 'lg:col-span-2'],
        ['label' => $cliente->tipo_pessoa === 'PJ' ? 'Nome Fantasia' : 'Nome de Exibição', 'valor' => $cliente->nome ?? '-', 'mono' => false, 'destaque' => true],
        ['label' => $cliente->tipo_pessoa === 'PJ' ? 'CNPJ' : 'CPF', 'valor' => $cliente->documento_formatado, 'mono' => true],
        ['label' => 'E-mail', 'valor' => $cliente->email ?: 'Não informado', 'mono' => false, 'href' => $cliente->email ? 'mailto:'.$cliente->email : null],
        ['label' => 'Telefone', 'valor' => $cliente->telefone ?: 'Não informado', 'mono' => false, 'href' => $cliente->telefone ? 'tel:'.preg_replace('/\D/', '', $cliente->telefone) : null],
        ['label' => 'Município / UF', 'valor' => implode(' - ', array_filter([$cliente->municipio, $cliente->uf])) ?: 'Não informado', 'mono' => false],
        ['label' => 'CEP', 'valor' => $cliente->cep ?: 'Não informado', 'mono' => true],
        ['label' => 'Situação Cadastral', 'valor' => $situacaoCadastralBadge, 'mono' => false, 'badge' => true],
        ['label' => 'Regime Tributário', 'valor' => $regimeTributarioBadge, 'mono' => false, 'badge' => true],
        ['label' => 'Status do Cadastro', 'valor' => $cliente->ativo ? 'Ativo' : 'Inativo', 'mono' => false],
    ];
@endphp

<div class="min-h-screen bg-gray-100" id="cliente-show-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <a
                            href="/app/clientes"
                            class="text-xs text-gray-600 hover:text-gray-900 hover:underline"
                            data-link
                        >
                            Voltar para clientes
                        </a>
                        <span class="text-gray-300 hidden sm:inline">|</span>
                        <span class="text-xs text-gray-500">Cadastro operacional</span>
                    </div>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide mt-2">{{ $clienteNome }}</h1>
                    <p class="text-xs text-gray-500 mt-1">{{ $cliente->documento_formatado }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $tipoPessoaBadge['hex'] }}">{{ $tipoPessoaBadge['label'] }}</span>
                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusBadge['hex'] }}">{{ $statusBadge['label'] }}</span>
                    @if($cliente->is_empresa_propria)
                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $empresaBadge['hex'] }}">{{ $empresaBadge['label'] }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6 sm:mb-8">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Ações Operacionais</span>
                        <p class="text-[11px] text-gray-500 mt-1">Gerencie o cadastro e acompanhe os vínculos fiscais do cliente.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            id="btn-dossie-cliente"
                            class="px-3 py-2 text-sm font-medium bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded"
                        >
                            Dossiê PDF
                        </button>
                        <a
                            href="{{ route('app.cliente.edit', $cliente->id) }}"
                            data-link
                            class="px-3 py-2 text-sm font-medium bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded"
                        >
                            Editar cadastro
                        </a>
                        @if(!$cliente->is_empresa_propria)
                            <button
                                type="button"
                                id="btn-excluir-cliente"
                                data-id="{{ $cliente->id }}"
                                data-nome="{{ $clienteNome }}"
                                class="px-3 py-2 text-sm font-medium bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded"
                            >
                                Excluir
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200">
                @foreach($resumoCliente as $item)
                    <div class="p-4 sm:p-6">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1 sm:mb-2">{{ $item['label'] }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $item['valor'] }}</p>
                        <p class="text-[11px] text-gray-500 mt-1">{{ $item['sub'] }}</p>
                        @if(!empty($item['url']))
                            <a href="{{ $item['url'] }}" data-link class="mt-1 inline-flex text-[11px] font-semibold text-blue-700 hover:underline">
                                Ver resultado da importação →
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-6 min-w-0">
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Dados Cadastrais</span>
                                <p class="mt-1 text-[11px] text-gray-500">Identificação, contato, localização e enquadramento fiscal.</p>
                            </div>
                            <span class="w-fit whitespace-nowrap rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $statusBadge['hex'] }}">
                                Cadastro {{ $statusBadge['label'] }}
                            </span>
                        </div>
                    </div>
                    <dl class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-3">
                        @foreach($dadosCadastrais as $dado)
                            <div class="rounded border border-gray-200 bg-gray-50 px-3 py-3 {{ $dado['span'] ?? '' }}">
                                <dt class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-400">{{ $dado['label'] }}</dt>
                                @if(!empty($dado['badge']))
                                    <dd>
                                        <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $dado['valor']['hex'] }}">{{ $dado['valor']['label'] }}</span>
                                    </dd>
                                @elseif(!empty($dado['href']))
                                    <dd>
                                        <a href="{{ $dado['href'] }}" class="break-words text-sm font-medium text-gray-700 hover:text-gray-900 hover:underline">{{ $dado['valor'] }}</a>
                                    </dd>
                                @else
                                    <dd class="break-words text-sm text-gray-700 {{ $dado['mono'] ? 'font-mono' : '' }} {{ !empty($dado['destaque']) ? 'font-semibold text-gray-900' : '' }}">{{ $dado['valor'] }}</dd>
                                @endif
                            </div>
                        @endforeach
                        @if($cliente->endereco ?? null)
                            <div class="rounded border border-gray-200 bg-gray-50 px-3 py-3 sm:col-span-2 lg:col-span-3">
                                <dt class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-gray-400">Endereço completo</dt>
                                <dd class="text-sm text-gray-700">{{ $cliente->endereco }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo Relacional</span>
                            <a href="/app/clientes" data-link class="text-xs text-gray-600 hover:text-gray-900 hover:underline">
                                Voltar à carteira
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-gray-200">
                        <div class="px-4 py-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Participantes vinculados</p>
                            <p class="text-base font-bold text-gray-900">{{ number_format($totalParticipantes, 0, ',', '.') }}</p>
                            <p class="text-[11px] text-gray-500 mt-1">Cadastros que usam este cliente como vínculo operacional.</p>
                        </div>
                        <div class="px-4 py-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Notas registradas</p>
                            <p class="text-base font-bold text-gray-900">{{ number_format($totalNotas, 0, ',', '.') }}</p>
                            <p class="text-[11px] text-gray-500 mt-1">Movimentação fiscal consolidada disponível para análise.</p>
                        </div>
                    </div>
                </div>

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
                <div class="bg-white border border-gray-200 rounded-lg p-4 mt-4">
                    <h3 class="text-[13px] font-bold text-gray-700 uppercase tracking-wide mb-3">Detalhamento do Score</h3>
                    @include('autenticado.partials._score-detalhamento', [
                        'detalhamento' => $score_detalhamento ?? [],
                        'scoreTotal' => $score['score_total'] ?? null,
                        'classificacao' => $score['classificacao'] ?? 'nao_avaliado',
                        'comHeadline' => true,
                    ])
                </div>

                @include('autenticado.monitoramento._movimentacao-listas', ['top_produtos' => $top_produtos ?? [], 'top_cfops' => $top_cfops ?? []])
        </div>

        <div class="mt-6 sm:mt-8">
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
    </div>
</div>
