@php
    $fmtR = fn ($valor) => 'R$ '.number_format((float) $valor, 2, ',', '.');
    $fmtN = fn ($valor) => number_format((float) $valor, 0, ',', '.');
    $periodos = ['30' => '30 dias', '90' => '90 dias', '365' => '12 meses', 'tudo' => 'Todo o histórico'];
    $periodoLabel = $periodos[$m['periodo']] ?? '30 dias';
    $precos = app(\App\Services\PricingCatalogService::class);
    $saldoUsuarios = ($m['creditos']['saldo_base']);
    $saldoVendido = ($m['creditos']['vendidos']);
    $saldoConsumido = ($m['creditos']['consumidos']);
    $disco = $operacao['disco'];
    $totalAlertas = ($disco['status'] === 'saudavel' ? 0 : 1)
        + ($operacao['pendencias_vencidas'] > 0 ? 1 : 0)
        + ($operacao['integracoes_problemas'] > 0 ? 1 : 0);
    $ultimaCompra = $m['receita']['ultima_compra_em']
        ? \Carbon\Carbon::parse($m['receita']['ultima_compra_em'])->format('d/m/Y H:i')
        : 'nenhuma compra aprovada';
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Visão Geral</h1>
                <p class="text-xs text-gray-500 mt-0.5">Indicadores essenciais do negócio e pontos que precisam de atenção.</p>
            </div>
            <span class="text-[10px] text-gray-400 uppercase tracking-wide whitespace-nowrap">Atualizado em {{ now()->format('d/m/Y H:i') }}</span>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'visao'])

        <form method="GET" class="bg-white rounded border border-gray-300 overflow-hidden mb-4" data-mobile-filters>
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Período de análise</p>
            </div>
            <div class="p-3 flex flex-col min-[420px]:flex-row min-[420px]:items-center gap-3">
                <label for="admin-periodo" class="text-[11px] text-gray-600 min-[420px]:mr-auto">Novos usuários, receita aprovada e uso do produto</label>
                <select id="admin-periodo" name="periodo" onchange="this.form.submit()" class="auth-control w-full min-[420px]:w-52 text-[13px] px-3 border border-gray-300 rounded bg-white focus:border-gray-400 focus:ring-1 focus:ring-gray-400">
                    @foreach($periodos as $codigo => $label)
                        <option value="{{ $codigo }}" @selected($m['periodo'] === $codigo)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <section class="bg-white rounded border border-gray-300 overflow-hidden mb-4" aria-labelledby="resumo-negocio-titulo">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
                <h2 id="resumo-negocio-titulo" class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo do negócio</h2>
                <span class="text-[10px] text-gray-400">{{ $periodoLabel }}</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['Usuários', $fmtN($m['crescimento']['total_usuarios']), $fmtN($m['crescimento']['novos']).' novos no período · '.$fmtN($m['crescimento']['ativos']).' ativos em 30d'],
                    ['Receita aprovada', $fmtR($m['receita']['aprovada_periodo']), $fmtR($m['receita']['aprovada_total']).' acumulados'],
                    ['MRR estimado', $fmtR($m['receita']['mrr']), $fmtN($m['receita']['assinaturas_ativas']).' assinatura(s) ativa(s)'],
                    ['Saldo dos usuários', $fmtR($saldoUsuarios), $fmtR($saldoVendido).' vendidos · '.$fmtR($saldoConsumido).' consumidos'],
                ] as [$rotulo, $valor, $detalhe])
                    <div @class([
                        'p-3 sm:p-4 min-w-0',
                        'border-l border-gray-200' => $loop->index % 2 === 1,
                        'border-t border-gray-200' => $loop->index >= 2,
                        'lg:border-l lg:border-gray-200' => ! $loop->first,
                        'lg:border-t-0' => $loop->index >= 2,
                    ])>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $rotulo }}</p>
                        <p class="text-lg sm:text-xl font-bold text-gray-900 mt-0.5 truncate">{{ $valor }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5 leading-snug">{{ $detalhe }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-white rounded border border-gray-300 overflow-hidden mb-4" aria-labelledby="operacao-agora-titulo">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
                <h2 id="operacao-agora-titulo" class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Operação agora</h2>
                <span class="inline-flex whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $totalAlertas > 0 ? '#b45309' : '#047857' }}">
                    {{ $totalAlertas > 0 ? $totalAlertas.' ponto(s) de atenção' : 'Tudo em ordem' }}
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3">
                <article class="p-4 border-b lg:border-b-0 border-gray-200">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Disco da VPS</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ $disco['livre_formatado'] }} livres</p>
                        </div>
                        <span class="inline-flex whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $disco['status_cor'] }}">{{ $disco['status_label'] }}</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-2">
                        @if($disco['disponivel'])
                            {{ number_format((float) $disco['percentual'], 1, ',', '.') }}% ocupado · {{ $disco['usado_formatado'] }} de {{ $disco['total_formatado'] }}
                        @else
                            A capacidade física não pôde ser lida.
                        @endif
                    </p>
                    <a href="{{ route('app.admin.armazenamento.index') }}" data-link class="inline-flex mt-3 text-[11px] font-semibold text-gray-700 hover:text-gray-900 hover:underline">Ver armazenamento →</a>
                </article>

                @php
                    $pendenciasCor = $operacao['pendencias_vencidas'] > 0 ? '#b91c1c' : '#047857';
                    $pendenciasLabel = $operacao['pendencias_vencidas'] > 0 ? 'Priorizar' : 'Em dia';
                @endphp
                <article class="p-4 border-b lg:border-b-0 lg:border-l border-gray-200">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Pendências admin</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ $fmtN($operacao['pendencias_abertas']) }} aberta(s)</p>
                        </div>
                        <span class="inline-flex whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $pendenciasCor }}">{{ $pendenciasLabel }}</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-2">{{ $fmtN($operacao['pendencias_vencidas']) }} vencida(s) exigindo acompanhamento do operador.</p>
                    <a href="{{ route('app.admin.pendencias.index') }}" data-link class="inline-flex mt-3 text-[11px] font-semibold text-gray-700 hover:text-gray-900 hover:underline">Abrir pendências →</a>
                </article>

                @php
                    $integracoesCor = $operacao['integracoes_problemas'] > 0 ? '#b91c1c' : '#047857';
                    $integracoesLabel = $operacao['integracoes_problemas'] > 0 ? 'Verificar' : 'Operacional';
                @endphp
                <article class="p-4 lg:border-l border-gray-200">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Integrações</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ $fmtN($operacao['integracoes_problemas']) }} com problema</p>
                        </div>
                        <span class="inline-flex whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $integracoesCor }}">{{ $integracoesLabel }}</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-2">Disponibilidade informada ao usuário e mantida pelo time FiscalDock.</p>
                    <a href="{{ route('app.admin.integracoes.index') }}" data-link class="inline-flex mt-3 text-[11px] font-semibold text-gray-700 hover:text-gray-900 hover:underline">Ver integrações →</a>
                </article>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <section class="bg-white rounded border border-gray-300 overflow-hidden" aria-labelledby="comercial-titulo">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
                    <h2 id="comercial-titulo" class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Comercial e conversão</h2>
                    <a href="{{ route('app.admin.usuarios.index') }}" data-link class="text-[10px] text-gray-500 hover:text-gray-900 hover:underline">Ver usuários</a>
                </div>
                <div class="grid grid-cols-2">
                    @foreach([
                        ['Trials em curso', $fmtN($m['trial']['em_curso']), 'fotografia atual'],
                        ['Convertidos', $fmtN($m['trial']['convertidos']), 'compra confirmada'],
                        ['Conversão do trial', number_format((float) $m['trial']['taxa_conversao'], 1, ',', '.').'%', $fmtN($m['trial']['total']).' trials usados'],
                        ['Assinaturas ativas', $fmtN($m['receita']['assinaturas_ativas']), $fmtN($m['receita']['recargas_ativas']).' recarga(s) ativa(s)'],
                    ] as [$rotulo, $valor, $detalhe])
                        <div @class([
                            'p-3 sm:p-4',
                            'border-l border-gray-200' => $loop->index % 2 === 1,
                            'border-t border-gray-200' => $loop->index >= 2,
                        ])>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $rotulo }}</p>
                            <p class="text-lg font-bold text-gray-900 mt-0.5">{{ $valor }}</p>
                            <p class="text-[11px] text-gray-500">{{ $detalhe }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-200 px-4 py-2.5 text-[11px] text-gray-500">
                    {{ $fmtN($m['trial']['expirados']) }} trial(s) expirado(s) · última compra: {{ $ultimaCompra }}
                </div>
            </section>

            <section class="bg-white rounded border border-gray-300 overflow-hidden" aria-labelledby="uso-produto-titulo">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
                    <h2 id="uso-produto-titulo" class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Uso do produto</h2>
                    <span class="text-[10px] text-gray-400">{{ $periodoLabel }}</span>
                </div>
                <div class="grid grid-cols-2">
                    @foreach([
                        ['Consultas CNPJ', $fmtN($m['uso']['consultas']), 'lotes iniciados'],
                        ['Importações', $fmtN($m['uso']['importacoes']), 'EFD + XML'],
                        ['Clearance', $fmtN($m['uso']['clearance']), 'NF-e + CT-e'],
                        ['Monitoramentos', $fmtN($m['uso']['monitoramentos_ativos']), 'ativos agora'],
                    ] as [$rotulo, $valor, $detalhe])
                        <div @class([
                            'p-3 sm:p-4',
                            'border-l border-gray-200' => $loop->index % 2 === 1,
                            'border-t border-gray-200' => $loop->index >= 2,
                        ])>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $rotulo }}</p>
                            <p class="text-lg font-bold text-gray-900 mt-0.5">{{ $valor }}</p>
                            <p class="text-[11px] text-gray-500">{{ $detalhe }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-200 px-4 py-2.5 text-[11px] text-gray-500">
                    Saldo acumulado: {{ $fmtR($saldoVendido) }} vendido · {{ $fmtR($saldoConsumido) }} consumido
                </div>
            </section>
        </div>
    </div>
</div>
