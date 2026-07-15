{{-- Minha Empresa — cockpit fiscal em fluxo vertical estável. --}}
@php
    $empresaNome = $empresa->razao_social ?? $empresa->nome;
    $documento = $empresa->documento_formatado;
    $userCredits = Auth::user()->credits ?? 0;

    $situacao = mb_strtoupper($certidoes['situacao_cadastral'] ?? 'NÃO CONSULTADO');
    $situacaoBadge = match($situacao) {
        'ATIVA' => ['label' => 'ATIVA', 'hex' => '#047857'],
        'SUSPENSA' => ['label' => 'SUSPENSA', 'hex' => '#b45309'],
        'INAPTA' => ['label' => 'INAPTA', 'hex' => '#dc2626'],
        'BAIXADA' => ['label' => 'BAIXADA', 'hex' => '#9ca3af'],
        default => ['label' => 'NÃO CONSULTADO', 'hex' => '#9ca3af'],
    };

    $scoreBadge = ['label' => 'NÃO AVALIADO', 'hex' => '#9ca3af'];
    if ($score) {
        $scoreBadge = match($score->classificacao) {
            'baixo' => ['label' => 'BAIXO', 'hex' => '#047857'],
            'medio' => ['label' => 'MÉDIO', 'hex' => '#b45309'],
            'alto' => ['label' => 'ALTO', 'hex' => '#ea580c'],
            'critico' => ['label' => 'CRÍTICO', 'hex' => '#dc2626'],
            'inconclusivo' => ['label' => 'NÃO CONCLUSIVO', 'hex' => '#9ca3af'],
            default => ['label' => mb_strtoupper((string) $score->classificacao), 'hex' => '#9ca3af'],
        };
    }

    $regimes = [];
    if (($certidoes['simples_nacional'] ?? null) === true) {
        $regimes[] = ['label' => 'SIMPLES NACIONAL', 'hex' => '#0f766e'];
    }
    if (($certidoes['mei'] ?? null) === true) {
        $regimes[] = ['label' => 'MEI', 'hex' => '#4338ca'];
    }

    $localizacao = implode(' · ', array_filter([$empresa->municipio, $empresa->uf]));
    $dadosEmpresa = [
        ['label' => 'RAZÃO SOCIAL', 'valor' => $empresaNome, 'mono' => false],
        ['label' => 'CNPJ', 'valor' => $documento, 'mono' => true],
        ['label' => 'LOCALIZAÇÃO', 'valor' => implode(' - ', array_filter([$empresa->municipio, $empresa->uf])) ?: 'Não informado', 'mono' => false],
        ['label' => 'CEP', 'valor' => $empresa->cep ? preg_replace('/(\d{5})(\d{3})/', '$1-$2', preg_replace('/\D/', '', $empresa->cep)) : 'Não informado', 'mono' => true],
        ['label' => 'TELEFONE', 'valor' => $empresa->telefone ?: 'Não informado', 'mono' => false],
        ['label' => 'EMAIL', 'valor' => $empresa->email ?: 'Não informado', 'mono' => false],
    ];

    $consultasRealizadas = [];
    if ($ultimaConsulta) {
        foreach ($ultimaConsulta->getConsultasRealizadas() as $tipo) {
            $consultasRealizadas[] = ucwords(mb_strtolower(str_replace('_', ' ', $tipo)));
        }
    }
    $ultimaConsultaResumo = [
        'data' => $ultimaConsulta?->consultado_em ? $ultimaConsulta->consultado_em->format('d/m/Y H:i') : 'Nenhuma consulta registrada',
        'qtd' => count($consultasRealizadas),
        'lista' => implode(', ', $consultasRealizadas),
    ];
    $ultimaConsultaMensagem = $ultimaConsulta?->getMensagemExibivel();

    $alertaStyles = [
        'critico' => 'border-l-red-500',
        'atencao' => 'border-l-amber-500',
        'info' => 'border-l-blue-500',
    ];

    $acoes = [
        [
            'label' => $ultimaConsulta ? 'Atualizar Consultas' : 'Fazer 1ª consulta',
            'href' => '/app/consulta/painel'.($participante ? '?participante='.$participante->id : ''),
        ],
        ['label' => 'Histórico', 'href' => '/app/minha-empresa/historico', 'icon' => 'history'],
    ];
    if ($participante) {
        $acoes[] = ['label' => 'Score Fiscal', 'href' => '/app/score-fiscal/participante/'.$participante->id, 'icon' => 'chart'];
    }
    if ($ultimaConsulta) {
        $acoes[] = [
            'label' => 'Baixar Dossiê',
            'href' => '/app/cliente/'.$empresa->id.'/dossie',
            'download' => true,
            'icon' => 'download',
        ];
    }
    $acaoPrimaria = $acoes[0];
    $linksSecundarios = array_slice($acoes, 1);
    $icone = fn (?string $tipo): string => match($tipo) {
        'history' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3.5 2"/>',
        'chart' => '<path d="M4 4v16h16"/><path d="M8 15l3-3 2.5 2.5L19 8"/>',
        'download' => '<path d="M12 4v10m0 0l-3.5-3.5M12 14l3.5-3.5"/><path d="M5 19h14"/>',
        default => '',
    };

    $temScoreValor = $score
        && $score->classificacao !== 'inconclusivo'
        && is_numeric($score->score_total ?? null);
    $subtituloEmpresa = $documento.($localizacao ? ' · '.$localizacao : '');
    $indicadoresEmpresa = [
        [
            'label' => 'Score de Risco',
            'valor' => $temScoreValor ? $score->score_total.' / 100' : '—',
            'badge' => $scoreBadge,
            'sub' => $score?->ultima_consulta_em ? 'Avaliado em '.$score->ultima_consulta_em->format('d/m/Y') : 'Ainda não avaliado',
        ],
        [
            'label' => 'Situação Cadastral',
            'valor' => $situacaoBadge['label'],
            'sub' => 'Receita Federal e cadastros correlatos',
        ],
        [
            'label' => 'Saldo',
            'valor' => \App\Support\Dinheiro::brl($userCredits),
            'sub' => 'Disponível para consultas',
        ],
        [
            'label' => 'Base Fiscal',
            'valor' => number_format($totalParticipantes, 0, ',', '.'),
            'sub' => number_format($totalNotas, 0, ',', '.').' notas registradas',
        ],
    ];

    // Shapes variáveis do provedor são normalizados antes do markup. A ausência de dados
    // gera estados vazios dentro do próprio bloco, sem criar/remover colunas vizinhas.
    $enderecoConsulta = is_array($dadosConsulta['endereco'] ?? null) ? $dadosConsulta['endereco'] : [];
    $formatarTelefone = function ($telefone) {
        $digitos = preg_replace('/\D/', '', (string) $telefone);
        if (strlen($digitos) === 11) return '('.substr($digitos, 0, 2).') '.substr($digitos, 2, 5).'-'.substr($digitos, 7);
        if (strlen($digitos) === 10) return '('.substr($digitos, 0, 2).') '.substr($digitos, 2, 4).'-'.substr($digitos, 6);
        return trim((string) $telefone);
    };
    $formatarData = function ($data) {
        if (empty($data)) return null;
        try {
            return \Carbon\Carbon::parse($data)->format('d/m/Y');
        } catch (\Throwable) {
            return trim((string) $data) ?: null;
        }
    };

    $telefonesConsulta = array_values(array_filter([
        $dadosConsulta['telefone_1'] ?? null,
        $dadosConsulta['telefone_2'] ?? null,
    ]));
    $capitalSocial = $dadosConsulta['capital_social'] ?? null;
    $dadosRegistro = [
        ['label' => 'NOME FANTASIA', 'valor' => $dadosConsulta['nome_fantasia'] ?? null],
        ['label' => 'NATUREZA JURÍDICA', 'valor' => $dadosConsulta['natureza_juridica'] ?? null],
        ['label' => 'PORTE', 'valor' => $dadosConsulta['porte'] ?? null],
        ['label' => 'REGIME TRIBUTÁRIO', 'valor' => $dadosConsulta['regime_tributario'] ?? null],
        ['label' => 'CAPITAL SOCIAL', 'valor' => is_numeric($capitalSocial) ? 'R$ '.number_format((float) $capitalSocial, 2, ',', '.') : null],
        ['label' => 'MATRIZ / FILIAL', 'valor' => ! empty($dadosConsulta['matriz_filial']) ? mb_strtoupper((string) $dadosConsulta['matriz_filial']) : null],
        ['label' => 'INÍCIO DE ATIVIDADE', 'valor' => $formatarData($dadosConsulta['data_inicio_atividade'] ?? null)],
    ];

    $cnaes = collect(is_array($dadosConsulta['cnaes'] ?? null) ? $dadosConsulta['cnaes'] : [])
        ->filter(fn ($item) => is_array($item))
        ->values();
    $valoresPrincipal = [true, 1, '1', 'true', 'SIM', 'S'];
    $cnaePrincipal = $cnaes->first(fn ($cnae) => in_array($cnae['principal'] ?? false, $valoresPrincipal, true));
    $cnaesSecundarios = $cnaes
        ->reject(fn ($cnae) => in_array($cnae['principal'] ?? false, $valoresPrincipal, true))
        ->values();
    $qsa = collect(is_array($dadosConsulta['qsa'] ?? null) ? $dadosConsulta['qsa'] : [])
        ->filter(fn ($item) => is_array($item))
        ->values();
    $temMapa = $participante && $participante->latitude && $participante->longitude;
    $temContatoConsulta = ! empty($enderecoConsulta) || count($telefonesConsulta) || $temMapa;
@endphp

<x-cockpit.layout
    container-id="minha-empresa-container"
    :titulo="$empresaNome"
    :subtitulo="$subtituloEmpresa"
    eyebrow="Minha Empresa"
    resumo-titulo="Visão Geral"
    data-empresa-layout="stack"
>
    <x-slot:badges>
        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $situacaoBadge['hex'] }}">{{ $situacaoBadge['label'] }}</span>
        @foreach($regimes as $regime)
            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $regime['hex'] }}">{{ $regime['label'] }}</span>
        @endforeach
        @if($monitoramento)
            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #0f766e">MONITORADA</span>
        @endif
    </x-slot:badges>

    <x-slot:principal>
        <a href="{{ $acaoPrimaria['href'] }}" data-link class="auth-control inline-flex items-center justify-center rounded bg-gray-800 px-5 text-sm font-semibold text-white transition-colors hover:bg-gray-700">
            {{ $acaoPrimaria['label'] }}
        </a>
    </x-slot:principal>

    <x-slot:acoes>
        @foreach($linksSecundarios as $link)
            <a href="{{ $link['href'] }}" @if(empty($link['download'])) data-link @endif
               class="auth-control inline-flex items-center gap-1.5 rounded border border-gray-300 bg-white px-3 text-xs font-medium text-gray-600 transition-colors hover:border-gray-400 hover:bg-gray-50 hover:text-gray-900">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 shrink-0 text-gray-400" aria-hidden="true">{!! $icone($link['icon'] ?? null) !!}</svg>
                {{ $link['label'] }}
            </a>
        @endforeach
    </x-slot:acoes>

    <x-slot:resumo>
        <x-cockpit.indicadores :itens="$indicadoresEmpresa" data-empresa-indicadores />
    </x-slot:resumo>

        <section class="bg-white rounded border border-gray-300 overflow-hidden" data-empresa-alertas>
            <header class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Alertas Operacionais</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold text-gray-600" style="background-color: #e5e7eb">{{ count($alertas) }}</span>
            </header>
            <div class="p-4">
                @if(count($alertas))
                    <div class="space-y-3">
                        @foreach($alertas as $alerta)
                            <div class="rounded border border-gray-300 border-l-4 {{ $alertaStyles[$alerta['tipo']] ?? 'border-l-gray-400' }} p-4">
                                <p class="text-sm text-gray-700">{{ $alerta['mensagem'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center gap-2 rounded border border-gray-300 border-l-4 border-l-green-500 p-4">
                        <span class="w-2 h-2 rounded-full shrink-0" style="background-color: #22c55e"></span>
                        <p class="text-sm text-gray-700">Nenhum alerta no momento.</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- Cadastro em linhas semânticas; conteúdo variável não controla o layout externo. --}}
        <section class="bg-white rounded border border-gray-300 overflow-hidden" data-empresa-cadastro>
            <header class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Dados Cadastrais</p>
                <p class="mt-0.5 text-[11px] text-gray-400">Identificação, registro e composição da empresa</p>
            </header>
            <dl class="divide-y divide-gray-100">
                @foreach($dadosEmpresa as $dado)
                    <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-baseline sm:px-5">
                        <dt class="shrink-0 text-[10px] font-semibold text-gray-400 uppercase tracking-wide sm:w-56">{{ $dado['label'] }}</dt>
                        <dd class="min-w-0 break-words text-sm text-gray-700 {{ $dado['mono'] ? 'font-mono' : '' }}">{{ $dado['valor'] ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="border-t border-gray-200 divide-y divide-gray-200">
                <details data-empresa-registro>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 sm:px-5 hover:bg-gray-50">
                        <span>
                            <span class="block text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Registro jurídico e econômico</span>
                            <span class="mt-0.5 block text-[11px] text-gray-400">Dados complementares da última consulta cadastral</span>
                        </span>
                        <span class="text-lg leading-none text-gray-400" aria-hidden="true">⌄</span>
                    </summary>
                    <dl class="border-t border-gray-200 divide-y divide-gray-100">
                        @foreach($dadosRegistro as $dado)
                            <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-baseline sm:px-5">
                                <dt class="shrink-0 text-[10px] font-semibold text-gray-400 uppercase tracking-wide sm:w-56">{{ $dado['label'] }}</dt>
                                <dd class="min-w-0 break-words text-sm text-gray-700">{{ $dado['valor'] ?: '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </details>

                <details data-empresa-cnaes>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 sm:px-5 hover:bg-gray-50">
                        <span>
                            <span class="block text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Atividades Econômicas (CNAE)</span>
                            <span class="mt-0.5 block text-[11px] text-gray-400">{{ $cnaes->count() ? $cnaes->count().' atividade(s) cadastrada(s)' : 'Nenhuma atividade informada na última consulta' }}</span>
                        </span>
                        <span class="text-lg leading-none text-gray-400" aria-hidden="true">⌄</span>
                    </summary>
                    <div class="border-t border-gray-200 px-4 py-4 sm:px-5">
                        @if($cnaePrincipal)
                            <div class="flex items-start gap-2">
                                <span class="shrink-0 whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #4338ca">Principal</span>
                                <p class="text-sm text-gray-700"><span class="font-mono text-gray-500">{{ $cnaePrincipal['codigo'] ?? '—' }}</span> — {{ $cnaePrincipal['descricao'] ?? 'Descrição não informada' }}</p>
                            </div>
                        @endif
                        @if($cnaesSecundarios->count())
                            <div class="mt-3 max-h-48 space-y-2 overflow-y-auto pr-1">
                                @foreach($cnaesSecundarios as $cnae)
                                    <p class="text-xs text-gray-600"><span class="font-mono text-gray-400">{{ $cnae['codigo'] ?? '—' }}</span> — {{ $cnae['descricao'] ?? 'Descrição não informada' }}</p>
                                @endforeach
                            </div>
                        @endif
                        @if(!$cnaePrincipal && !$cnaesSecundarios->count())
                            <p class="text-sm text-gray-500">Nenhuma atividade econômica disponível.</p>
                        @endif
                    </div>
                </details>

                <details data-empresa-qsa>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 sm:px-5 hover:bg-gray-50">
                        <span>
                            <span class="block text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Quadro Societário (QSA)</span>
                            <span class="mt-0.5 block text-[11px] text-gray-400">{{ $qsa->count() ? $qsa->count().' integrante(s) encontrado(s)' : 'Nenhum integrante informado na última consulta' }}</span>
                        </span>
                        <span class="text-lg leading-none text-gray-400" aria-hidden="true">⌄</span>
                    </summary>
                    <div class="border-t border-gray-200 divide-y divide-gray-100">
                        @forelse($qsa as $socio)
                            <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-start sm:justify-between sm:px-5">
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-700 break-words">{{ $socio['nome'] ?? '—' }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $socio['qualificacao'] ?? 'Qualificação não informada' }}</p>
                                </div>
                                @if($formatarData($socio['data_entrada'] ?? null))
                                    <span class="text-[11px] text-gray-400 whitespace-nowrap">desde {{ $formatarData($socio['data_entrada']) }}</span>
                                @endif
                            </div>
                        @empty
                            <p class="px-4 py-4 text-sm text-gray-500 sm:px-5">Nenhum dado societário disponível.</p>
                        @endforelse
                    </div>
                </details>

                <details data-empresa-localizacao>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 sm:px-5 hover:bg-gray-50">
                        <span>
                            <span class="block text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Endereço e contato consultados</span>
                            <span class="mt-0.5 block text-[11px] text-gray-400">{{ $temContatoConsulta ? 'Dados retornados pela última consulta cadastral' : 'Nenhum dado adicional retornado' }}</span>
                        </span>
                        <span class="text-lg leading-none text-gray-400" aria-hidden="true">⌄</span>
                    </summary>
                    <div class="border-t border-gray-200 px-4 py-4 sm:px-5">
                        @if(!empty($enderecoConsulta))
                            <p class="text-sm text-gray-700">
                                {{ trim(($enderecoConsulta['tipo_logradouro'] ?? '').' '.($enderecoConsulta['logradouro'] ?? '')) ?: 'Logradouro não informado' }}{{ !empty($enderecoConsulta['numero']) ? ', '.$enderecoConsulta['numero'] : '' }}
                                @if(!empty($enderecoConsulta['complemento'])) — {{ $enderecoConsulta['complemento'] }} @endif
                            </p>
                            <p class="mt-0.5 text-sm text-gray-500">{{ trim(($enderecoConsulta['bairro'] ?? '').' · '.($enderecoConsulta['municipio'] ?? '').'/'.($enderecoConsulta['uf'] ?? ''), ' ·/') ?: 'Município não informado' }}</p>
                            @if(!empty($enderecoConsulta['cep']))
                                <p class="mt-0.5 font-mono text-[12px] text-gray-400">CEP {{ preg_replace('/(\d{5})(\d{3})/', '$1-$2', preg_replace('/\D/', '', $enderecoConsulta['cep'])) }}</p>
                            @endif
                        @endif

                        @if(count($telefonesConsulta))
                            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1">
                                @foreach($telefonesConsulta as $telefone)
                                    <span class="font-mono text-sm text-gray-700">{{ $formatarTelefone($telefone) }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if(!$temContatoConsulta)
                            <p class="text-sm text-gray-500">Nenhum endereço ou contato adicional disponível.</p>
                        @endif

                        @if($temMapa)
                            <div id="empresa-mapa" class="mt-4 h-56 rounded border border-gray-300 bg-gray-100"
                                 data-lat="{{ $participante->latitude }}" data-lng="{{ $participante->longitude }}"></div>
                            <p class="mt-2 text-[11px] text-gray-400">Localização aproximada a partir do endereço cadastral.</p>
                        @endif
                    </div>
                </details>
            </div>
        </section>

        <section class="bg-white rounded border border-gray-300 overflow-hidden" data-empresa-score>
            <header class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-3">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Perfil do Score de Risco</span>
                @if($participante && $score)
                    <a href="/app/score-fiscal/participante/{{ $participante->id }}" data-link class="text-[11px] text-gray-600 hover:text-gray-900 hover:underline whitespace-nowrap">Ver detalhes</a>
                @endif
            </header>
            <div class="p-4 sm:p-5">
                @if($score)
                    @include('autenticado.partials._score-detalhamento', [
                        'detalhamento' => $scoreDetalhamento,
                        'scoreTotal' => $score->score_total,
                        'classificacao' => $score->classificacao,
                        'comHeadline' => false,
                    ])
                @else
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-500">O perfil de risco será exibido após a primeira consulta.</p>
                        <a href="{{ $acaoPrimaria['href'] }}" data-link class="text-xs font-semibold text-gray-700 hover:text-gray-900 hover:underline">Realizar consulta</a>
                    </div>
                @endif
            </div>
        </section>

        @if($temMapa)
            <script>
            (function initEmpresaMapa(t) {
                var el = document.getElementById('empresa-mapa');
                if (!el || el.dataset.init === '1') return;
                var painel = el.closest('details');
                if (painel && !painel.open) {
                    if (el.dataset.toggleBound !== '1') {
                        el.dataset.toggleBound = '1';
                        painel.addEventListener('toggle', function () {
                            if (painel.open) initEmpresaMapa(0);
                        });
                    }
                    return;
                }
                if (typeof L === 'undefined') { if ((t || 0) < 50) setTimeout(function () { initEmpresaMapa((t || 0) + 1); }, 100); return; }
                var lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lng);
                if (!lat || !lng) return;
                el.dataset.init = '1';
                var map = L.map(el).setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(map);
                L.marker([lat, lng]).addTo(map);
                setTimeout(function () { map.invalidateSize(); }, 0);
            })();
            </script>
        @endif

        <section class="bg-white rounded border border-gray-300 overflow-hidden" data-empresa-certidoes>
            <header class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Certidões</span>
                <span class="text-[11px] text-gray-500">
                    Última consulta: {{ $ultimaConsultaResumo['data'] }}
                    @if($ultimaConsultaResumo['qtd'])
                        <span class="text-gray-300 mx-1">·</span>
                        <span title="{{ $ultimaConsultaResumo['lista'] }}">{{ $ultimaConsultaResumo['qtd'] }} {{ $ultimaConsultaResumo['qtd'] === 1 ? 'fonte verificada' : 'fontes verificadas' }}</span>
                    @endif
                </span>
            </header>
            @if($ultimaConsultaMensagem)
                <div class="px-4 py-2 border-b border-gray-200 text-[11px] text-gray-500 sm:px-5">{{ $ultimaConsultaMensagem }}</div>
            @endif
            @if(!empty($fontesConsulta))
                <div class="p-3 sm:p-4" style="background-color: #f9fafb">
                    @include('autenticado.consulta.partials.detalhe-blocos', [
                        'blocos' => $fontesConsulta,
                        'certidoes' => $certidoesConsulta ?? [],
                        'resumo' => null,
                        'cabecalho' => [],
                    ])
                </div>
            @else
                <div class="px-4 py-4 text-sm text-gray-500 sm:px-5">
                    Nenhuma consulta de certidões realizada ainda.
                    <a href="/app/consulta/painel" data-link class="text-gray-700 underline hover:text-gray-900">Consultar agora</a>.
                </div>
            @endif
        </section>

        {{-- Gestão usa linhas independentes: um estado nunca controla a largura do outro. --}}
        <section class="bg-white rounded border border-gray-300 overflow-hidden" data-empresa-gestao>
            <header class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Gestão e Integrações</span>
            </header>
            <div class="divide-y divide-gray-200">
                <div class="p-4 sm:p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Monitoramento Contínuo</p>
                            @if($monitoramento)
                                <p class="text-sm text-gray-700">Empresa em monitoramento contínuo ({{ $monitoramento->frequencia }}). A regularidade é reconsultada automaticamente a cada ciclo.</p>
                            @else
                                <p class="text-sm text-gray-500">Acompanhe CNDs, FGTS e cadastro automaticamente, com alerta quando algo mudar.</p>
                            @endif
                        </div>
                        <a href="/app/monitoramento/painel" data-link class="auth-control inline-flex shrink-0 items-center justify-center rounded border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            {{ $monitoramento ? 'Gerenciar monitoramento' : 'Monitorar minha empresa' }}
                        </a>
                    </div>
                </div>

                <div id="certificado-digital" class="p-4 sm:p-5">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Certificado Digital (A1)</p>
                    @if(session('status'))
                        <p class="text-[12px] text-green-700 mb-3">{{ session('status') }}</p>
                    @endif
                    @error('certificado')
                        <p class="text-[12px] text-red-700 mb-3">{{ $message }}</p>
                    @enderror

                    @if($certificado ?? null)
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $certificado['badge_hex'] }}">{{ $certificado['expirado'] ? 'Expirado' : 'Válido' }}</span>
                            <span class="text-sm text-gray-700">Válido até <strong>{{ $certificado['validade']->format('d/m/Y') }}</strong></span>
                            @if($certificado['cnpj'])<span class="text-[12px] text-gray-500">CNPJ {{ $certificado['cnpj'] }}</span>@endif
                            @if($certificado['titular_nome'])<span class="text-[12px] text-gray-500">· {{ $certificado['titular_nome'] }}</span>@endif
                        </div>
                        <form method="POST" action="{{ route('app.minha-empresa.certificado.remover') }}" class="mt-3" onsubmit="return confirm('Remover o certificado digital?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[12px] text-red-700 hover:underline">Remover certificado</button>
                        </form>
                    @else
                        <p class="text-[12px] text-gray-500 mb-3">Cadastre o certificado A1 (.pfx/.p12) da empresa para habilitar o Clearance Full no futuro. <strong>A3 (token/cartão) não é suportado por upload.</strong></p>
                        <form method="POST" action="{{ route('app.minha-empresa.certificado.salvar') }}" enctype="multipart/form-data" class="flex flex-col gap-3 lg:flex-row lg:items-end">
                            @csrf
                            <div class="min-w-0 flex-1">
                                <label class="block text-[11px] text-gray-500 mb-1">Arquivo (.pfx/.p12)</label>
                                <input type="file" name="certificado" accept=".pfx,.p12" required
                                    class="auth-control block w-full max-w-full text-[13px] text-gray-600 file:mr-3 file:cursor-pointer file:rounded file:border-0 file:bg-gray-800 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-gray-700">
                            </div>
                            <div class="lg:w-56">
                                <label class="block text-[11px] text-gray-500 mb-1">Senha do certificado</label>
                                <input type="password" name="senha" required class="auth-control w-full border border-gray-300 rounded px-3 text-[13px] focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                            </div>
                            <button type="submit" class="auth-control rounded bg-gray-800 px-4 text-sm font-medium text-white transition-colors hover:bg-gray-700 whitespace-nowrap">Cadastrar certificado</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

        <div>
            @include('autenticado.consulta.partials.relacionamento-fiscal', [
                'fiscal' => $fiscalResumo,
                'cabecalho' => ['razao' => $empresaNome, 'documento' => $documento, 'uf' => $empresa->uf],
            ])
        </div>

        <div>
            @include('autenticado.partials.notas-fiscais-card', [
                'notas' => $notasFiscais,
                'totalNotas' => $notasFiscais->total(),
                'ajaxUrl' => $notasAjaxUrl,
                'contexto' => 'cliente',
                'entityId' => $empresa->id,
            ])
        </div>
</x-cockpit.layout>
