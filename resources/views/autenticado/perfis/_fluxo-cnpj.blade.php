@php
    $perfilCnpj = $perfilCnpj ?? [];
    $isCpf = (bool) ($perfilCnpj['is_cpf'] ?? false);
    $fiscalPerfil = $perfilCnpj['fiscal'] ?? null;
@endphp

<div class="space-y-4 sm:space-y-6 min-w-0" data-cockpit-profile-flow data-perfil-cnpj-flow>
    @include('autenticado.perfis._alertas-operacionais', [
        'alertasPerfil' => $perfilCnpj['alertas'] ?? [],
    ])

    @include('autenticado.perfis._dados-cadastrais', [
        'cadastroPerfil' => $perfilCnpj['cadastro'] ?? [],
    ])

    <x-cockpit.secao
        :titulo="$isCpf ? 'Risco de Crédito' : 'Score de Risco'"
        subtitulo="Composição do risco a partir das fontes efetivamente avaliadas."
        data-perfil-card="score"
    >
        <x-slot:acao>
            @if(!empty($perfilCnpj['score_atualizado_em']))
                <span class="text-[11px] text-gray-500">Atualizado em {{ $perfilCnpj['score_atualizado_em']->format('d/m/Y H:i') }}</span>
            @endif
        </x-slot:acao>
        @include('autenticado.partials._score-detalhamento', [
            'detalhamento' => $perfilCnpj['score_detalhamento'] ?? [],
            'scoreTotal' => $perfilCnpj['score_total'] ?? null,
            'classificacao' => $perfilCnpj['score_classificacao'] ?? 'nao_avaliado',
            'comHeadline' => true,
            'isCpf' => $isCpf,
            'mensagemCpf' => $perfilCnpj['mensagem_cpf'] ?? null,
        ])
    </x-cockpit.secao>

    @include('autenticado.perfis._certidoes', [
        'fontesPerfil' => $perfilCnpj['fontes_consulta'] ?? [],
        'certidoesPerfil' => $perfilCnpj['certidoes_consulta'] ?? [],
        'ultimaConsultaPerfil' => $perfilCnpj['ultima_consulta'] ?? null,
        'isCpf' => $isCpf,
    ])

    @include('autenticado.perfis._relacionamento-fiscal', ['fiscalPerfil' => $fiscalPerfil])

    {{-- Consolidado fiscal acumulado (C190/D190 de todas as importações EFD). Vem antes de
         Principais Produtos: no varejo (NFC-e sem detalhe por item) é a visão tributária
         que existe. Só renderiza quando a view fornece $consolidadoFiscal. --}}
    @include('autenticado.partials._consolidado-fiscal', [
        'tituloConsolidado' => 'Consolidado Fiscal Acumulado (C190/D190)',
        'subtituloConsolidado' => 'todas as importações EFD · por CFOP · CST · alíquota',
    ])

    @include('autenticado.perfis._principais-produtos', ['produtosPerfil' => $fiscalPerfil['top_produtos'] ?? $perfilCnpj['produtos'] ?? []])
    @include('autenticado.perfis._principais-cfops', ['cfopsPerfil' => $fiscalPerfil['top_cfops'] ?? $perfilCnpj['cfops'] ?? []])

    @include('autenticado.partials.notas-fiscais-card', [
        'notas' => $perfilCnpj['notas'],
        'totalNotas' => $perfilCnpj['total_notas'],
        'ajaxUrl' => $perfilCnpj['notas_ajax_url'],
        'contexto' => $perfilCnpj['notas_contexto'],
        'entityId' => $perfilCnpj['entity_id'],
    ])

    @include('autenticado.partials._historico-consultas-perfil', [
        'historicoConsultasPerfil' => $perfilCnpj['historico'] ?? collect(),
        'documentoPerfil' => $perfilCnpj['documento'] ?? '',
    ])
</div>
