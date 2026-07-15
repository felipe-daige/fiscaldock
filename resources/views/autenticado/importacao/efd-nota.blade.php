@php
    $tipoBadge = $nota->tipo_operacao === 'entrada'
        ? ['label' => 'ENTRADA', 'hex' => '#047857']
        : ['label' => 'SAÍDA', 'hex' => '#b45309'];

    $modeloLabel = match($nota->modelo) {
        '00' => 'NFS-e',
        '55' => 'NF-e',
        '65' => 'NFC-e',
        '57' => 'CT-e',
        '67' => 'CT-e OS',
        '01' => 'Nota Fiscal',
        '1B' => 'Nota Fiscal Avulsa',
        '04' => 'Nota Fiscal de Produtor',
        default => $nota->modelo ? 'MODELO ' . $nota->modelo : 'DOCUMENTO FISCAL',
    };

    $origemBadge = match($nota->origem_arquivo ?? '') {
        'fiscal' => ['label' => 'EFD ICMS/IPI', 'hex' => '#4338ca'],
        'contribuicoes' => ['label' => 'EFD PIS/COFINS', 'hex' => '#0f766e'],
        default => ['label' => 'EFD', 'hex' => '#374151'],
    };

    $subtitulo = match($nota->modelo) {
        '55' => 'Nota Fiscal Eletrônica',
        '65' => 'Nota Fiscal de Consumidor Eletrônica',
        '57' => 'Conhecimento de Transporte Eletrônico',
        '67' => 'Conhecimento de Transporte para Outros Serviços',
        '00' => 'Nota Fiscal de Serviço Eletrônica',
        default => ($nota->origem_arquivo ?? '') === 'contribuicoes' ? 'Documento de serviço escriturado via EFD' : 'Documento fiscal escriturado via EFD',
    };

    // ICMS do C190 (consolidados) — no perfil comercial o C170 não carrega ICMS (P2).
    // Fallback aos itens só quando não há C190 (NF-e antiga sem consolidado).
    $totalIcms = $nota->consolidados->sum('valor_icms') ?: $nota->itens->sum('valor_icms');
    $totalPis = $nota->itens->sum('valor_pis');
    $totalCofins = $nota->itens->sum('valor_cofins');
    $totalTributos = $totalIcms + $totalPis + $totalCofins;
    $temTributos = $totalTributos > 0;
    $itensExibir = $nota->itensDetalhe();
    $viaTwin = $nota->itensViaTwin();
    $catalogoMap = $nota->catalogoPorItem();
    $tabValor = $itensExibir->sum('valor_total');
    $tabIcms = $itensExibir->sum('valor_icms');
    $tabPis = $itensExibir->sum('valor_pis');
    $tabCofins = $itensExibir->sum('valor_cofins');
    $aliqDiv = fn ($cat, $item) => $cat
        && $cat->aliq_icms !== null
        && $item->aliquota_icms !== null
        && (float) $item->aliquota_icms > 0
        && abs((float) $cat->aliq_icms - (float) $item->aliquota_icms) > 0.01;
@endphp

<div class="bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-8">
            <a href="/app/notas" data-link class="inline-flex items-center gap-1 text-xs text-gray-600 hover:text-gray-900 hover:underline">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar para Notas Fiscais
            </a>

            <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">
                        Nota Fiscal {{ $nota->numero ?? 'Sem número' }}{{ $nota->serie ? ' / Série ' . $nota->serie : '' }}
                    </h1>
                    <p class="text-xs text-gray-500">{{ $subtitulo }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $tipoBadge['hex'] }}">{{ $tipoBadge['label'] }}</span>
                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #374151">{{ strtoupper($modeloLabel) }}</span>
                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $origemBadge['hex'] }}">{{ $origemBadge['label'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo da Nota</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-5 divide-x divide-y lg:divide-y-0 divide-gray-200">
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Emissão</p>
                    <p class="text-lg font-bold text-gray-900">{{ $nota->data_emissao ? \Carbon\Carbon::parse($nota->data_emissao)->format('d/m/Y') : '—' }}</p>
                    <p class="text-[11px] text-gray-500">{{ $nota->data_emissao ? 'Documento escriturado no período' : 'Data não informada' }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Valor Total</p>
                    <p class="text-lg font-bold text-gray-900">R$&nbsp;{{ $nota->valor_total !== null ? number_format($nota->valor_total, 2, ',', '.') : '—' }}</p>
                    <p class="text-[11px] text-gray-500">Total contabilizado da nota</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Itens</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($itensExibir->count(), 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500">{{ $viaTwin ? 'Detalhados pela EFD gêmea' : ($itensExibir->count() === 1 ? 'Item escriturado' : 'Itens escriturados') }}</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Desconto</p>
                    <p class="text-lg font-bold text-gray-900">R$&nbsp;{{ number_format((float) ($nota->valor_desconto ?? 0), 2, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500">Valor total de descontos</p>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Tributos</p>
                    <p class="text-lg font-bold text-gray-900">R$&nbsp;{{ number_format($totalTributos, 2, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500">ICMS do consolidado (C190) + PIS/COFINS dos itens</p>
                </div>
            </div>
            @if($nota->chave_acesso)
                <div class="px-4 py-3 border-t border-gray-200">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Chave de Acesso</p>
                    <p class="text-xs font-mono text-gray-700 break-all">{{ implode(' ', str_split($nota->chave_acesso, 4)) }}</p>
                </div>
            @endif
        </div>

        @include('autenticado.notas.partials._consulta-clearance', ['consulta' => $consulta ?? null, 'chaveConsulta' => $nota->chave_acesso, 'nota' => $nota, 'auditoria' => $auditoria ?? null])

        <div class="mb-4 space-y-4" data-layout-principal>
            @include('autenticado.importacao.efd-nota._itens')

            @if($temTributos)
                @include('autenticado.importacao.efd-nota._resumo-tributario')
            @endif
        </div>

        @if($nota->participante || $nota->cliente)
            <div class="mb-3 flex items-end justify-between gap-3">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500">Partes da operação</p>
                    <p class="mt-1 text-[11px] text-gray-500">Identificação cadastral das pessoas vinculadas a este documento.</p>
                </div>
            </div>
        @endif

        @php
            $partesOperacao = [];

            if ($nota->participante) {
                $partesOperacao[] = \App\Support\DesignSystem\ParteOperacaoPresenter::card(
                    $nota->participante,
                    'Participante',
                );
            }

            if ($nota->cliente) {
                $partesOperacao[] = \App\Support\DesignSystem\ParteOperacaoPresenter::card(
                    $nota->cliente,
                    'Cliente',
                    papel: $nota->cliente->is_empresa_propria ? 'Empresa própria' : null,
                );
            }
        @endphp

        <div class="grid grid-cols-1 {{ count($partesOperacao) === 2 ? 'xl:grid-cols-2' : '' }} items-stretch gap-4 mb-4" data-partes-operacao>
            @foreach($partesOperacao as $parte)
                <x-parte-operacao-card
                    :titulo="$parte['titulo']"
                    :nome="$parte['nome']"
                    :href="$parte['href']"
                    :descricao="$parte['descricao']"
                    :situacao="$parte['situacao']"
                    :situacao-hex="$parte['situacao_hex']"
                    :papel="$parte['papel']"
                    :papel-hex="$parte['papel_hex']"
                    :campos="$parte['campos']"
                />
            @endforeach
        </div>

        @if($nota->participante || $nota->cliente)
            <section class="bg-white rounded border border-gray-300 overflow-hidden mb-4" data-regularidade-partes>
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Regularidade fiscal das partes</span>
                    <p class="mt-1 text-[11px] text-gray-500">Últimas fontes consultadas para cada cadastro, separadas dos dados da operação.</p>
                </div>
                <div class="divide-y divide-gray-200">
                    @if($nota->participante)
                        @include('autenticado.importacao.efd-nota._certidoes-consultadas', [
                            'detalheConsulta' => $participanteConsultaDetalhe ?? null,
                            'contexto' => 'participante',
                            'entidadeLabel' => 'Participante',
                            'nomeCadastro' => $nota->participante->razao_social,
                            'consultaUrl' => '/app/consulta/nova?participantes='.$nota->participante->id,
                        ])
                    @endif

                    @if($nota->cliente)
                        @include('autenticado.importacao.efd-nota._certidoes-consultadas', [
                            'detalheConsulta' => $clienteConsultaDetalhe ?? null,
                            'contexto' => 'cliente',
                            'entidadeLabel' => $nota->cliente->is_empresa_propria ? 'Empresa própria' : 'Cliente',
                            'nomeCadastro' => $nota->cliente->razao_social ?? $nota->cliente->nome,
                            'consultaUrl' => '/app/consulta/painel',
                        ])
                    @endif
                </div>
            </section>
        @endif
    </div>
</div>
