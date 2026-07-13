@php
    $percentualDisco = $disco['percentual'] ?? 0;
    $barraDisco = max(0, min(100, (float) $percentualDisco));
    $ordemDescricao = match($ordemArmazenamento) {
        'percentual_desc' => 'Maior percentual da quota primeiro',
        'nome_asc' => 'Contas em ordem alfabética',
        default => 'Maior consumo primeiro',
    };
    $descricaoDisco = match($disco['status']) {
        'critico' => 'A capacidade está em nível crítico. Planeje a liberação ou expansão do disco imediatamente.',
        'atencao' => 'O disco já cruzou o limite preventivo de 70%. Acompanhe o crescimento e prepare a migração ou expansão.',
        'saudavel' => 'A ocupação está abaixo do limite preventivo de 70%.',
        default => 'O filesystem não forneceu capacidade e espaço livre nesta leitura.',
    };
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Armazenamento</h1>
                <p class="text-xs text-gray-500 mt-0.5">Saúde física da VPS e quota lógica das contas FiscalDock.</p>
            </div>
            <span class="text-[11px] text-gray-400 whitespace-nowrap">Leitura em {{ now()->format('d/m/Y H:i') }}</span>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'armazenamento'])

        <section class="bg-white rounded border border-gray-300 overflow-hidden mb-4" aria-labelledby="saude-disco-titulo">
            <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200 flex flex-col min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 id="saude-disco-titulo" class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Capacidade física da VPS</h2>
                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $disco['status_cor'] }}">
                        {{ $disco['status_label'] }}
                    </span>
                </div>
                <span class="text-[10px] text-gray-400 uppercase tracking-wide">Filesystem do storage local</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_240px]">
                <div class="p-4 sm:p-5">
                    <div class="flex flex-col min-[420px]:flex-row min-[420px]:items-end min-[420px]:justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Ocupação atual</p>
                            <div class="mt-1 flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                <span class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $disco['usado_formatado'] }}</span>
                                <span class="text-sm text-gray-500">usados de <strong class="font-semibold text-gray-700">{{ $disco['total_formatado'] }}</strong></span>
                            </div>
                        </div>
                        @if($disco['disponivel'])
                            <div class="min-[420px]:text-right">
                                <p class="text-2xl font-bold text-gray-900">{{ number_format((float) $disco['percentual'], 1, ',', '.') }}%</p>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wide">do disco ocupado</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <div class="mb-1.5 flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-[11px]">
                            <span class="font-semibold text-gray-700">{{ $disco['usado_formatado'] }} usados</span>
                            <span class="text-gray-500">{{ $disco['livre_formatado'] }} livres</span>
                        </div>
                        <div class="relative h-4 rounded-sm overflow-hidden border border-gray-300" style="background-color:#f3f4f6" role="progressbar" aria-valuenow="{{ $disco['percentual'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100" aria-valuetext="{{ $disco['usado_formatado'] }} usados de {{ $disco['total_formatado'] }}">
                            <div class="h-full transition-all" style="width:{{ $barraDisco }}%; background-color:{{ $disco['status_cor'] }}"></div>
                            <span class="absolute inset-y-0 border-l border-white/80" style="left:{{ config('arquivos.disco.atencao_percentual', 70) }}%" aria-hidden="true"></span>
                            <span class="absolute inset-y-0 border-l border-white/80" style="left:{{ config('arquivos.disco.critico_percentual', 85) }}%" aria-hidden="true"></span>
                        </div>
                        <div class="mt-1.5 flex flex-wrap justify-end gap-x-3 gap-y-1 text-[9px] text-gray-400 uppercase tracking-wide" aria-hidden="true">
                            <span>Atenção {{ number_format((float) config('arquivos.disco.atencao_percentual', 70), 0, ',', '.') }}%</span>
                            <span>Crítico {{ number_format((float) config('arquivos.disco.critico_percentual', 85), 0, ',', '.') }}%</span>
                        </div>
                    </div>
                </div>

                <aside class="border-t lg:border-t-0 lg:border-l border-gray-200 p-4 sm:p-5 flex flex-col justify-between gap-4" style="background-color:#f8fafc">
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Espaço disponível</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $disco['livre_formatado'] }}</p>
                        <p class="text-[11px] text-gray-500 mt-1">Livre para PostgreSQL, Docker, logs e arquivos privados.</p>
                    </div>
                    <p class="text-[11px] leading-relaxed text-gray-600 border-t border-gray-200 pt-3">{{ $descricaoDisco }}</p>
                </aside>
            </div>
        </section>

        <section class="bg-white rounded border border-gray-300 overflow-hidden mb-4" aria-labelledby="resumo-contas-titulo">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
                <h2 id="resumo-contas-titulo" class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Armazenamento das contas</h2>
                <span class="text-[10px] font-semibold text-gray-400">{{ $resumoArmazenamento['contas_total'] }} conta(s)</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['Uso lógico total', $resumoArmazenamento['uso_logico_formatado'], 'Soma que consome quota dos planos'],
                    ['Arquivos privados', $resumoArmazenamento['uso_filesystem_formatado'], 'Uploads e comprovantes no filesystem'],
                    ['Importações', $resumoArmazenamento['uso_importacoes_formatado'], 'EFD preservada + tamanho lógico XML'],
                    ['Contas em risco', $resumoArmazenamento['contas_criticas'], $resumoArmazenamento['contas_acima_quota'].' acima da quota'],
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

        <div class="bg-white rounded border border-gray-300 border-l-4 p-3 mb-4 text-[11px] text-gray-600 leading-relaxed" style="border-left-color:#1e4679">
            <strong class="text-gray-800">Leituras diferentes:</strong> a VPS inclui PostgreSQL, Docker, logs e sistema operacional. A tabela usa a quota lógica do produto — uploads, comprovantes e importações — portanto os totais não devem coincidir.
        </div>

        @if($resumoArmazenamento['erros_leitura'] > 0 || $resumoArmazenamento['nao_atribuido_bytes'] > 0)
            <div class="bg-white rounded border border-gray-300 border-l-4 p-3 mb-4 text-xs text-gray-700" style="border-left-color:#b45309">
                <p class="font-bold">A medição lógica requer atenção.</p>
                @if($resumoArmazenamento['erros_leitura'] > 0)
                    <p>{{ $resumoArmazenamento['erros_leitura'] }} arquivo(s) ou raiz(es) não puderam ser lidos; o total pode estar parcial.</p>
                @endif
                @if($resumoArmazenamento['nao_atribuido_bytes'] > 0)
                    <p>{{ $resumoArmazenamento['nao_atribuido_formatado'] }} pertencem a diretórios cujo usuário já não existe.</p>
                @endif
            </div>
        @endif

        <form method="GET" class="bg-white rounded border border-gray-300 overflow-hidden mb-4" data-mobile-filters>
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Filtros da listagem</p>
                <span class="text-[10px] text-gray-400">{{ $ordemDescricao }}</span>
            </div>
            <div class="p-3 flex flex-col sm:flex-row sm:items-end gap-3">
                <div class="flex-1">
                    <label for="armazenamento-q" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Buscar conta</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input id="armazenamento-q" type="text" name="q" value="{{ $buscaArmazenamento }}" placeholder="nome, e-mail, empresa, CNPJ ou plano" class="auth-control w-full text-[13px] pl-9 pr-3 border border-gray-300 rounded focus:border-gray-400 focus:ring-1 focus:ring-gray-400 focus:outline-none">
                    </div>
                </div>
                <div class="sm:w-56">
                    <label for="armazenamento-ordem" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Ordenar por</label>
                    <select id="armazenamento-ordem" name="ordenar" onchange="this.form.submit()" class="auth-control w-full text-[13px] px-3 border border-gray-300 rounded bg-white focus:border-gray-400 focus:ring-1 focus:ring-gray-400 focus:outline-none">
                        <option value="uso_desc" @selected($ordemArmazenamento === 'uso_desc')>Maior uso</option>
                        <option value="percentual_desc" @selected($ordemArmazenamento === 'percentual_desc')>Maior % da quota</option>
                        <option value="nome_asc" @selected($ordemArmazenamento === 'nome_asc')>Nome A–Z</option>
                    </select>
                </div>
                <button type="submit" class="auth-control w-full sm:w-auto text-[12px] font-bold uppercase tracking-wide px-5 rounded text-white hover:opacity-90" style="background-color:#1f2937">Filtrar</button>
                @if($buscaArmazenamento !== '' || $ordemArmazenamento !== 'uso_desc')
                    <a href="{{ route('app.admin.armazenamento.index') }}" data-link class="auth-control admin-action w-full sm:w-auto inline-flex items-center justify-center text-[12px] font-semibold text-gray-600 border border-gray-300 rounded bg-white hover:bg-gray-50 px-3">Limpar</a>
                @endif
            </div>
        </form>

        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="px-3 py-2.5 border-b border-gray-200 flex flex-col min-[380px]:flex-row min-[380px]:items-center min-[380px]:justify-between gap-1">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Uso por conta</h2>
                <p class="text-[11px] text-gray-400">{{ $contas->total() }} resultado(s)</p>
            </div>
            <table class="w-full text-sm tabela-cards">
                <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="text-left px-3 py-2.5 font-semibold">Conta</th>
                        <th class="text-left px-3 py-2.5 font-semibold">Plano</th>
                        <th class="text-left px-3 py-2.5 font-semibold min-w-64">Quota utilizada</th>
                        <th class="text-left px-3 py-2.5 font-semibold">Composição</th>
                        <th class="text-right px-3 py-2.5 font-semibold">Situação</th>
                        <th class="text-right px-3 py-2.5 font-semibold">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contas as $conta)
                        @php
                            $usuario = $conta['usuario'];
                            $percentualConta = $conta['percentual'];
                            $barraConta = $percentualConta === null ? 0 : max(0, min(100, (float) $percentualConta));
                            $iniciaisConta = collect([mb_substr((string) $usuario->name, 0, 1), mb_substr((string) $usuario->sobrenome, 0, 1)])->filter()->join('');
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-3 py-3" data-label="Conta">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded text-[10px] font-bold uppercase text-white" style="background-color:#1e4679" aria-hidden="true">{{ $iniciaisConta ?: '#' }}</span>
                                    <div class="min-w-0">
                                        <a href="{{ route('app.admin.usuarios.show', $usuario->id) }}" data-link class="font-semibold text-gray-900 hover:text-gray-600 hover:underline">
                                            {{ trim($usuario->name.' '.$usuario->sobrenome) ?: 'Usuário #'.$usuario->id }}
                                        </a>
                                        <span class="ml-1 text-[10px] font-mono text-gray-400">#{{ $usuario->id }}</span>
                                        <span class="block text-[11px] text-gray-500 truncate">{{ $usuario->email }}</span>
                                        <span class="block text-[10px] text-gray-400 truncate">{{ $usuario->empresa ?: 'Sem empresa informada' }}@if($usuario->cnpj) · {{ $usuario->cnpj }}@endif</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3" data-label="Plano">
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#1e4679">{{ $conta['plano_nome'] }}</span>
                                <span class="block text-[10px] text-gray-400 mt-1">{{ $conta['plano_codigo'] }}</span>
                            </td>
                            <td class="px-3 py-3" data-label="Quota utilizada">
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="font-semibold text-gray-900">{{ $conta['usado_formatado'] }}</span>
                                    <span class="text-[11px] text-gray-500">de {{ $conta['quota_formatada'] }}</span>
                                </div>
                                <div class="mt-1.5 h-2 rounded-full overflow-hidden" style="background-color:#e5e7eb">
                                    <div class="h-full rounded-full" style="width:{{ $barraConta }}%; background-color:{{ $conta['status_cor'] }}"></div>
                                </div>
                                <span class="block text-right text-[10px] text-gray-400 mt-0.5">
                                    {{ $percentualConta === null ? 'sem limite' : number_format((float) $percentualConta, 1, ',', '.').'%' }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-[11px] text-gray-600" data-label="Composição">
                                <span class="block">Uploads: <strong>{{ $conta['uploads_formatado'] }}</strong> · {{ $conta['uploads_total'] }}</span>
                                <span class="block">Comprovantes: <strong>{{ $conta['comprovantes_formatado'] }}</strong> · {{ $conta['comprovantes_total'] }}</span>
                                <span class="block">Importações: <strong>{{ $conta['importacoes_formatado'] }}</strong> · {{ $conta['importacoes_total'] }}</span>
                            </td>
                            <td class="px-3 py-3 text-right" data-label="Situação">
                                <span class="inline-flex whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $conta['status_cor'] }}">{{ $conta['status_label'] }}</span>
                            </td>
                            <td class="px-3 py-3 text-right" data-label="Ação">
                                <a href="{{ route('app.admin.usuarios.show', $usuario->id) }}" data-link class="admin-action inline-flex items-center justify-center px-3 py-2 rounded border border-gray-300 text-[11px] font-bold uppercase tracking-wide text-gray-700 bg-white hover:bg-gray-50 whitespace-nowrap">Ver conta</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-10 text-center text-gray-400 text-sm">Nenhuma conta encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contas->hasPages())
            <div class="mt-4">{{ $contas->links() }}</div>
        @endif
    </div>
</div>
