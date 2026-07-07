{{-- Minha Empresa - Dashboard (redesign DANFE++ — piloto) --}}
@php
    $empresaNome = $empresa->razao_social ?? $empresa->nome;
    $documento = $empresa->documento_formatado;
    $userCredits = Auth::user()->credits ?? 0;

    $situacao = mb_strtoupper($certidoes['situacao_cadastral'] ?? 'NÃO CONSULTADO');
    $situacaoBadge = match($situacao) {
        'ATIVA' => ['label' => 'ATIVA', 'hex' => '#047857'],
        'SUSPENSA' => ['label' => 'SUSPENSA', 'hex' => '#d97706'],
        'INAPTA' => ['label' => 'INAPTA', 'hex' => '#dc2626'],
        'BAIXADA' => ['label' => 'BAIXADA', 'hex' => '#9ca3af'],
        default => ['label' => 'NÃO CONSULTADO', 'hex' => '#9ca3af'],
    };

    $scoreBadge = ['label' => 'NÃO AVALIADO', 'hex' => '#9ca3af', 'valor' => '--/100'];
    if ($score) {
        $scoreBadge = match($score->classificacao) {
            'baixo' => ['label' => 'BAIXO', 'hex' => '#047857', 'valor' => $score->score_total . '/100'],
            'medio' => ['label' => 'MÉDIO', 'hex' => '#d97706', 'valor' => $score->score_total . '/100'],
            'alto' => ['label' => 'ALTO', 'hex' => '#ea580c', 'valor' => $score->score_total . '/100'],
            'critico' => ['label' => 'CRÍTICO', 'hex' => '#dc2626', 'valor' => $score->score_total . '/100'],
            'inconclusivo' => ['label' => 'NÃO CONCLUSIVO', 'hex' => '#9ca3af', 'valor' => '—'],
            default => ['label' => strtoupper((string) $score->classificacao), 'hex' => '#9ca3af', 'valor' => $score->score_total . '/100'],
        };
    }

    $regimes = [];
    if ($certidoes['simples_nacional'] === true) {
        $regimes[] = ['label' => 'SIMPLES NACIONAL', 'hex' => '#0f766e'];
    }
    if ($certidoes['mei'] === true) {
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

    // $certidaoLinhas vem pronto do controller (classificado por CertidaoBadge canônico).

    $consultasRealizadas = [];
    if ($ultimaConsulta) {
        foreach ($ultimaConsulta->getConsultasRealizadas() as $tipo) {
            $consultasRealizadas[] = strtoupper(str_replace('_', ' ', ucfirst($tipo)));
        }
    }

    $ultimaConsultaResumo = [
        'data' => $ultimaConsulta?->consultado_em ? $ultimaConsulta->consultado_em->format('d/m/Y H:i') : 'Nenhuma consulta registrada',
        'tipos' => ! empty($consultasRealizadas) ? implode(' | ', $consultasRealizadas) : 'Sem consultas realizadas',
    ];
    $ultimaConsultaMensagem = $ultimaConsulta?->getMensagemExibivel();

    $alertaStyles = [
        'critico' => 'border-l-red-500',
        'atencao' => 'border-l-amber-500',
        'info' => 'border-l-blue-500',
    ];

    $temConsulta = (bool) $ultimaConsulta;
    $acoes = [
        [
            // Sem consulta ainda: CTA de ativação. Com consulta: atualizar.
            'label' => $temConsulta ? 'Atualizar Consultas' : 'Fazer 1ª consulta',
            'href' => '/app/consulta/painel' . ($participante ? '?participante=' . $participante->id : ''),
            'primary' => true,
        ],
        [
            'label' => 'Histórico',
            'href' => '/app/minha-empresa/historico',
            'primary' => false,
            'icon' => 'history',
        ],
    ];

    if ($participante) {
        $acoes[] = [
            'label' => 'Score Fiscal',
            'href' => '/app/score-fiscal/participante/' . $participante->id,
            'primary' => false,
            'icon' => 'chart',
        ];
    }

    if ($ultimaConsulta && $ultimaConsulta->lote) {
        $acoes[] = [
            'label' => 'Baixar Relatório',
            'href' => '/app/consulta/lote/' . $ultimaConsulta->lote->id . '/baixar',
            'primary' => false,
            'download' => true,
            'icon' => 'download',
        ];
    }

    // Ação principal sobe pro header (acima da dobra); o restante vira links secundários,
    // com "Alterar empresa" fechando a régua.
    $acaoPrimaria = $acoes[0] ?? null;
    $acoesSecundarias = array_slice($acoes, 1);
    $linksSecundarios = array_merge($acoesSecundarias, [
        ['label' => 'Alterar empresa', 'href' => '/app/minha-empresa/configurar', 'icon' => 'swap'],
    ]);

    // Inner markup dos ícones (stroke currentColor) usados nos botões de ação secundária.
    $icone = fn (?string $k): string => match ($k) {
        'history' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3.5 2"/>',
        'chart' => '<path d="M4 4v16h16"/><path d="M8 15l3-3 2.5 2.5L19 8"/>',
        'download' => '<path d="M12 4v10m0 0l-3.5-3.5M12 14l3.5-3.5"/><path d="M5 19h14"/>',
        'swap' => '<path d="M17 4l3 3-3 3"/><path d="M20 7H8"/><path d="M7 20l-3-3 3-3"/><path d="M4 17h12"/>',
        default => '',
    };

    // Gauge do score: só preenche quando há valor numérico conclusivo. Arco semicircular
    // de comprimento π·50 ≈ 157.08 (stroke-dasharray); o offset esvazia da direita p/ esquerda.
    $temScoreValor = $score && $score->classificacao !== 'inconclusivo' && is_numeric($score->score_total ?? null);
    $scorePct = $temScoreValor ? max(0, min(100, (int) $score->score_total)) : 0;
    $scoreArco = 157.08;
    $scoreOffset = $scoreArco * (1 - $scorePct / 100);
@endphp

<div class="min-h-screen bg-gray-100" id="minha-empresa-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        {{-- HERO: identidade da empresa + status + ação principal --}}
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5 sm:p-6 mb-4 sm:mb-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-1">Minha Empresa</p>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 leading-tight break-words">{{ $empresaNome }}</h1>
                    <p class="text-xs text-gray-500 mt-1.5">
                        <span class="font-mono">{{ $documento }}</span>
                        @if($localizacao)<span class="text-gray-300 mx-1">·</span>{{ $localizacao }}@endif
                    </p>
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <span class="inline-block whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $situacaoBadge['hex'] }}">{{ $situacaoBadge['label'] }}</span>
                        @foreach($regimes as $regime)
                            <span class="inline-block whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $regime['hex'] }}">{{ $regime['label'] }}</span>
                        @endforeach
                        @if($monitoramento)
                            <span class="inline-block whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #0f766e">MONITORADA</span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col items-start gap-3 lg:items-end shrink-0">
                    @if($acaoPrimaria)
                        <a href="{{ $acaoPrimaria['href'] }}" data-link class="bg-gray-800 text-white hover:bg-gray-700 inline-flex items-center justify-center rounded-md text-sm font-semibold px-5 py-2.5 transition-colors whitespace-nowrap w-full sm:w-auto">
                            {{ $acaoPrimaria['label'] }}
                        </a>
                    @endif
                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                        @foreach($linksSecundarios as $lnk)
                            {{-- Downloads não levam data-link (o SPA faria fetch do arquivo pra dentro do #app). --}}
                            <a href="{{ $lnk['href'] }}" @unless(!empty($lnk['download'])) data-link @endunless
                               class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 hover:border-gray-400 transition-colors whitespace-nowrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 shrink-0 text-gray-400" aria-hidden="true">{!! $icone($lnk['icon'] ?? null) !!}</svg>
                                {{ $lnk['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- KPIs — Resumo Fiscal em cards individuais --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
            {{-- Score com gauge semicircular --}}
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 sm:p-5 col-span-2 lg:col-span-1">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Score de Risco</p>
                <div class="mt-2 flex items-center gap-4">
                    <span class="sr-only">{{ $scoreBadge['valor'] }}</span>
                    <svg viewBox="0 0 120 70" class="w-24 h-14 shrink-0" role="img" aria-label="Score {{ $temScoreValor ? $score->score_total.' de 100' : 'não avaliado' }}">
                        <path d="M10 60 A50 50 0 0 1 110 60" fill="none" stroke="#e5e7eb" stroke-width="10" stroke-linecap="round"/>
                        <path d="M10 60 A50 50 0 0 1 110 60" fill="none" stroke="{{ $scoreBadge['hex'] }}" stroke-width="10" stroke-linecap="round"
                              stroke-dasharray="{{ $scoreArco }}" stroke-dashoffset="{{ $scoreOffset }}" style="transition: stroke-dashoffset .6s ease"/>
                        <text x="60" y="54" text-anchor="middle" fill="#111827" style="font-size:24px;font-weight:700">{{ $temScoreValor ? $score->score_total : '—' }}</text>
                    </svg>
                    <div class="min-w-0">
                        <span class="inline-block whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $scoreBadge['hex'] }}">{{ $scoreBadge['label'] }}</span>
                        <p class="text-[10px] text-gray-400 mt-1">de 100 pontos</p>
                        @if($score?->ultima_consulta_em)
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $score->ultima_consulta_em->format('d/m/Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 sm:p-5">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Situação Cadastral</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $situacaoBadge['hex'] }}"></span>
                    <p class="text-lg sm:text-xl font-bold text-gray-900 truncate">{{ $situacaoBadge['label'] }}</p>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">Receita Federal e cadastros correlatos</p>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 sm:p-5">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Créditos</p>
                <p class="text-lg sm:text-xl font-bold text-gray-900 mt-2">{{ number_format($userCredits, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-500 mt-1">Disponíveis para consultas</p>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 sm:p-5">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Base Monitorada</p>
                <p class="text-lg sm:text-xl font-bold text-gray-900 mt-2">{{ number_format($totalParticipantes, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-500 mt-1">{{ number_format($totalNotas, 0, ',', '.') }} notas registradas</p>
            </div>
        </div>

        {{-- Dados usados pelo painel cadastral. --}}
        @php
            $end = is_array($dadosConsulta['endereco'] ?? null) ? $dadosConsulta['endereco'] : [];
            $fmtTel = function ($tel) {
                $t = preg_replace('/\D/', '', (string) $tel);
                if (strlen($t) === 11) return '('.substr($t, 0, 2).') '.substr($t, 2, 5).'-'.substr($t, 7);
                if (strlen($t) === 10) return '('.substr($t, 0, 2).') '.substr($t, 2, 4).'-'.substr($t, 6);
                return $tel;
            };
            $telefones = array_values(array_filter([$dadosConsulta['telefone_1'] ?? null, $dadosConsulta['telefone_2'] ?? null]));

            $capital = $dadosConsulta['capital_social'] ?? null;
            $registro = array_values(array_filter([
                ['label' => 'NOME FANTASIA', 'valor' => $dadosConsulta['nome_fantasia'] ?? null],
                ['label' => 'NATUREZA JURÍDICA', 'valor' => $dadosConsulta['natureza_juridica'] ?? null],
                ['label' => 'PORTE', 'valor' => $dadosConsulta['porte'] ?? null],
                ['label' => 'REGIME TRIBUTÁRIO', 'valor' => $dadosConsulta['regime_tributario'] ?? null],
                ['label' => 'CAPITAL SOCIAL', 'valor' => is_numeric($capital) ? 'R$ '.number_format((float) $capital, 2, ',', '.') : null],
                ['label' => 'MATRIZ / FILIAL', 'valor' => isset($dadosConsulta['matriz_filial']) ? mb_strtoupper((string) $dadosConsulta['matriz_filial']) : null],
                ['label' => 'INÍCIO DE ATIVIDADE', 'valor' => ! empty($dadosConsulta['data_inicio_atividade']) ? \Carbon\Carbon::parse($dadosConsulta['data_inicio_atividade'])->format('d/m/Y') : null],
            ], fn ($r) => ! empty($r['valor'])));

            // CNAEs: [{codigo, descricao, principal:bool}, ...]
            $cnaes = is_array($dadosConsulta['cnaes'] ?? null) ? $dadosConsulta['cnaes'] : [];
            $cnaePrincipal = collect($cnaes)->first(fn ($c) => ($c['principal'] ?? false) === true);
            $cnaesSecundarios = collect($cnaes)->filter(fn ($c) => ($c['principal'] ?? false) !== true)->values();

            // QSA (sócios): [{nome, qualificacao, cpf_cnpj, data_entrada}, ...]
            $qsa = is_array($dadosConsulta['qsa'] ?? null) ? $dadosConsulta['qsa'] : [];

            $temMapa = $participante && $participante->latitude && $participante->longitude;
            $temRegistro = count($registro) || $cnaePrincipal || $cnaesSecundarios->count() || count($qsa);
            $temLocalizacao = ! empty($end) || count($telefones) || $temMapa;
        @endphp

        {{--
            Cockpit superior: duas pilhas independentes no desktop. Diferente de linhas
            de cards pareados, uma coluna não reserva o vazio deixado pela altura da outra.
        --}}
        <div class="grid grid-cols-1 items-start gap-4 sm:gap-6 xl:grid-cols-12 mb-4 sm:mb-6" data-empresa-overview-grid>
            <div class="xl:col-span-7">
                {{-- Dados básicos e registro jurídico no mesmo documento visual. --}}
                <section class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Dados Cadastrais</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Identificação, enquadramento e atividades da empresa</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-px bg-gray-100">
                        @foreach($dadosEmpresa as $dado)
                            <div class="bg-white px-5 py-4">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ $dado['label'] }}</p>
                                <p class="text-sm text-gray-700 break-words {{ $dado['mono'] ? 'font-mono' : '' }}">{{ $dado['valor'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if($temRegistro)
                        <div class="px-5 py-2.5 border-t border-gray-100 bg-gray-50">
                            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Registro jurídico e econômico</p>
                        </div>

                        @if(count($registro))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-px bg-gray-100">
                                @foreach($registro as $r)
                                    <div @class([
                                        'bg-white px-5 py-4',
                                        'sm:col-span-2' => $loop->last && $loop->count % 2 !== 0,
                                    ])>
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ $r['label'] }}</p>
                                        <p class="text-sm text-gray-700 break-words">{{ $r['valor'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($cnaePrincipal || $cnaesSecundarios->count())
                            <div class="px-5 py-4 border-t border-gray-100">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Atividades Econômicas (CNAE)</p>
                                @if($cnaePrincipal)
                                    <div class="flex items-start gap-2 mb-2">
                                        <span class="inline-block whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white shrink-0" style="background-color: #4338ca">Principal</span>
                                        <p class="text-sm text-gray-700"><span class="font-mono text-gray-500">{{ $cnaePrincipal['codigo'] ?? '' }}</span> — {{ $cnaePrincipal['descricao'] ?? '' }}</p>
                                    </div>
                                @endif
                                @if($cnaesSecundarios->count())
                                    <div class="space-y-1 max-h-40 overflow-y-auto pr-1">
                                        @foreach($cnaesSecundarios->take(12) as $c)
                                            <p class="text-xs text-gray-600"><span class="font-mono text-gray-400">{{ $c['codigo'] ?? '' }}</span> — {{ \Illuminate\Support\Str::limit($c['descricao'] ?? '', 70) }}</p>
                                        @endforeach
                                        @if($cnaesSecundarios->count() > 12)
                                            <p class="text-[11px] text-gray-400 mt-1">… e mais {{ $cnaesSecundarios->count() - 12 }} CNAEs</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(count($qsa))
                            <div class="px-5 py-4 border-t border-gray-100">
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Quadro Societário (QSA)</p>
                                <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                                    @foreach($qsa as $socio)
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-sm text-gray-700 truncate">{{ $socio['nome'] ?? '—' }}</p>
                                                @if(!empty($socio['qualificacao']))<p class="text-[11px] text-gray-400">{{ $socio['qualificacao'] }}</p>@endif
                                            </div>
                                            @if(!empty($socio['data_entrada']))
                                                <span class="text-[11px] text-gray-400 whitespace-nowrap shrink-0">desde {{ \Carbon\Carbon::parse($socio['data_entrada'])->format('d/m/Y') }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </section>
            </div>

            {{-- Coluna de contexto: cards compactos empilham sem interferir na coluna cadastral. --}}
            <aside class="space-y-4 sm:space-y-6 xl:col-span-5">
                @if($score)
                    <section class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between gap-3">
                            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Perfil do Score de Risco</span>
                            @if($participante)
                                <a href="/app/score-fiscal/participante/{{ $participante->id }}" data-link class="text-[11px] text-gray-600 hover:text-gray-900 hover:underline whitespace-nowrap">Ver detalhes</a>
                            @endif
                        </div>
                        <div class="p-4 sm:p-5">
                            @include('autenticado.partials._score-detalhamento', [
                                'detalhamento' => $scoreDetalhamento,
                                'scoreTotal' => $score->score_total,
                                'classificacao' => $score->classificacao,
                                'comHeadline' => false,
                            ])
                        </div>
                    </section>
                @endif

                @if($temLocalizacao)
                    <section class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-gray-100">
                            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Localização</span>
                        </div>
                        <div class="p-5">
                            @if(!empty($end))
                                <p class="text-sm text-gray-700">
                                    {{ trim(($end['tipo_logradouro'] ?? '').' '.($end['logradouro'] ?? '')) }}{{ !empty($end['numero']) ? ', '.$end['numero'] : '' }}
                                    @if(!empty($end['complemento'])) — {{ $end['complemento'] }} @endif
                                </p>
                                <p class="text-sm text-gray-500 mt-0.5">{{ trim(($end['bairro'] ?? '').' · '.($end['municipio'] ?? '').'/'.($end['uf'] ?? ''), ' ·/') }}</p>
                                @if(!empty($end['cep']))
                                    <p class="text-[12px] text-gray-400 mt-0.5 font-mono">CEP {{ preg_replace('/(\d{5})(\d{3})/', '$1-$2', preg_replace('/\D/', '', $end['cep'])) }}</p>
                                @endif
                            @endif

                            @if(count($telefones))
                                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1">
                                    @foreach($telefones as $tel)
                                        <span class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="w-3.5 h-3.5 text-gray-400 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span class="font-mono">{{ $fmtTel($tel) }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if($temMapa)
                                <div id="empresa-mapa"
                                     class="mt-4 h-48 rounded-md border border-gray-200 bg-gray-100"
                                     data-lat="{{ $participante->latitude }}"
                                     data-lng="{{ $participante->longitude }}"></div>
                                <p class="text-[11px] text-gray-400 mt-2">Localização aproximada a partir do endereço cadastral.</p>
                            @endif
                        </div>
                    </section>
                @endif

                <section class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Alertas Operacionais</span>
                        @if(count($alertas) > 0)
                            <span class="text-[10px] font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ count($alertas) }}</span>
                        @endif
                    </div>
                    <div class="p-4 sm:p-5">
                        @if(count($alertas) > 0)
                            <div class="space-y-3">
                                @foreach($alertas as $alerta)
                                    <div class="bg-white rounded-md border border-gray-200 border-l-4 {{ $alertaStyles[$alerta['tipo']] ?? 'border-l-gray-400' }} p-4">
                                        <p class="text-sm text-gray-700">{{ $alerta['mensagem'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex items-center gap-2 rounded-md border border-gray-200 border-l-4 border-l-green-500 p-4">
                                <span class="inline-block w-2 h-2 rounded-full shrink-0" style="background-color:#22c55e"></span>
                                <p class="text-sm text-gray-700">Nenhum alerta no momento.</p>
                            </div>
                        @endif
                    </div>
                </section>

            </aside>
        </div>

        @if($temMapa)
        {{-- Init do mapa (Leaflet já carregado no layout). IIFE roda no load inicial e a cada swap SPA. --}}
        <script>
        (function initEmpresaMapa(t) {
            var el = document.getElementById('empresa-mapa');
            if (!el || el.dataset.init === '1') return;
            if (typeof L === 'undefined') { if ((t || 0) < 50) setTimeout(function () { initEmpresaMapa((t || 0) + 1); }, 100); return; }
            var lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lng);
            if (!lat || !lng) return;
            el.dataset.init = '1';
            var map = L.map(el).setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(map);
            L.marker([lat, lng]).addTo(map);
        })();
        </script>
        @endif

        {{-- Certidões --}}
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mb-4 sm:mb-6">
            <div class="px-5 py-3 border-b border-gray-100 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Certidões</span>
                <span class="text-[11px] text-gray-500">
                    Última consulta: {{ $ultimaConsultaResumo['data'] }}
                    @if($ultimaConsultaResumo['tipos'] !== 'Sem consultas realizadas') · {{ $ultimaConsultaResumo['tipos'] }} @endif
                </span>
            </div>
            @if($ultimaConsultaMensagem)
                <div class="px-5 py-2 border-b border-gray-100 text-[11px] text-gray-500">{{ $ultimaConsultaMensagem }}</div>
            @endif
            <div class="overflow-x-auto">
                <table class="min-w-full tabela-cards">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Indicador</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Status</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Última Emissão</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Validade</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Comprovante</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($certidaoLinhas as $linha)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $linha['nome'] }}</td>
                                <td class="px-4 py-3" data-label="Status">
                                    <span class="inline-block whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $linha['badge']['hex'] }}" @if(!empty($linha['motivo'])) title="{{ $linha['motivo'] }}" @endif>{{ $linha['badge']['label'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700" data-label="Última Emissão">
                                    @if(!empty($linha['emissao']))
                                        {{ $linha['emissao'] }}
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700" data-label="Validade">
                                    {{ $linha['validade'] }}
                                    @if(!empty($linha['motivo']))
                                        <span class="block text-[11px] text-gray-400 mt-0.5" title="{{ $linha['motivo'] }}">{{ \Illuminate\Support\Str::limit($linha['motivo'], 120) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm" data-label="Comprovante">
                                    @if($linha['comprovante'])
                                        <a href="{{ $linha['comprovante'] }}" target="_blank" rel="noopener" class="text-[12px] text-blue-700 hover:underline">Abrir</a>
                                    @else
                                        <span class="text-[12px] text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Panorama fiscal (movimentação + contrapartes/negociantes) da empresa própria. --}}
        <div class="mb-4 sm:mb-6">
            @include('autenticado.consulta.partials.relacionamento-fiscal', [
                'fiscal' => $fiscalResumo,
                'cabecalho' => ['razao' => $empresaNome, 'documento' => $documento, 'uf' => $empresa->uf],
            ])
        </div>

        {{-- Notas fiscais recentes (base unificada XML+EFD), paginação AJAX. --}}
        <div class="mb-4 sm:mb-6">
            @include('autenticado.partials.notas-fiscais-card', [
                'notas' => $notasFiscais,
                'totalNotas' => $notasFiscais->total(),
                'ajaxUrl' => $notasAjaxUrl,
                'contexto' => 'cliente',
                'entityId' => $empresa->id,
            ])
        </div>

        {{-- Gestão reunida em um único painel; não cria duas caixas com alturas desconectadas. --}}
        <section class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Gestão e Integrações</span>
            </div>
            <div class="grid grid-cols-1 xl:grid-cols-12">
                <div class="p-4 sm:p-5 xl:col-span-4">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Monitoramento Contínuo</p>
                    @if($monitoramento)
                        <p class="text-sm text-gray-700">Empresa em monitoramento contínuo ({{ $monitoramento->frequencia }}). A regularidade é reconsultada automaticamente a cada ciclo.</p>
                        <a href="/app/monitoramento/painel" data-link class="text-[12px] text-gray-600 hover:text-gray-900 hover:underline mt-2 inline-block">Gerenciar monitoramento</a>
                    @else
                        <p class="text-[12px] text-gray-500 mb-3">Acompanhe a regularidade da sua empresa automaticamente — CNDs, FGTS e cadastro reconsultados a cada ciclo, com alerta quando algo muda.</p>
                        <a href="/app/monitoramento/painel" data-link class="bg-gray-800 text-white hover:bg-gray-700 inline-flex items-center justify-center rounded-md text-sm font-medium px-4 py-2 transition-colors">Monitorar minha empresa</a>
                    @endif
                </div>

                <div class="p-4 sm:p-5 border-t border-gray-100 xl:col-span-8 xl:border-t-0 xl:border-l">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Certificado Digital (A1)</p>
                    @if(session('status'))
                        <p class="text-[12px] text-green-700 mb-3">{{ session('status') }}</p>
                    @endif
                    @error('certificado')
                        <p class="text-[12px] text-red-700 mb-3">{{ $message }}</p>
                    @enderror

                    @if($certificado ?? null)
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-block whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $certificado['badge_hex'] }}">
                                {{ $certificado['expirado'] ? 'Expirado' : 'Válido' }}
                            </span>
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
                        <form method="POST" action="{{ route('app.minha-empresa.certificado.salvar') }}" enctype="multipart/form-data" class="flex flex-col gap-3 xl:flex-row xl:items-end">
                            @csrf
                            <div class="min-w-0 flex-1">
                                <label class="block text-[11px] text-gray-500 mb-1">Arquivo (.pfx/.p12)</label>
                                <input type="file" name="certificado" accept=".pfx,.p12" required
                                    class="block w-full max-w-full text-[13px] text-gray-600 file:mr-3 file:cursor-pointer file:rounded file:border-0 file:bg-gray-800 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-gray-700">
                            </div>
                            <div class="xl:w-48">
                                <label class="block text-[11px] text-gray-500 mb-1">Senha do certificado</label>
                                <input type="password" name="senha" required class="w-full border border-gray-300 rounded px-3 py-2.5 text-[13px] focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                            </div>
                            <button type="submit" class="bg-gray-800 text-white hover:bg-gray-700 rounded-md text-sm font-medium px-4 py-2.5 transition-colors whitespace-nowrap">Cadastrar certificado</button>
                        </form>
                    @endif
                </div>
            </div>
        </section>

    </div>
</div>
