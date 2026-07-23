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
            'alto' => ['label' => 'ALTO', 'hex' => '#dc2626'],
            'critico' => ['label' => 'CRÍTICO', 'hex' => '#b91c1c'],
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
    $acoes = [
        [
            'label' => $ultimaConsulta ? 'Atualizar Consultas' : 'Fazer 1ª consulta',
            'href' => '/app/consulta/painel?clientes='.$empresa->id,
        ],
        ['label' => 'Histórico', 'href' => '/app/minha-empresa/historico', 'icon' => 'history'],
    ];
    $acoes[] = ['label' => 'Score Fiscal', 'href' => '/app/score-fiscal?cliente_id='.$empresa->id, 'icon' => 'chart'];
    if ($ultimaConsulta) {
        $acoes[] = [
            'label' => 'Baixar Dossiê',
            'href' => '/app/cliente/'.$empresa->id.'/dossie',
            'download' => true,
            'icon' => 'download',
        ];
    }
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
        <div class="flex max-w-3xl flex-wrap justify-start gap-2 lg:justify-end" data-perfil-acoes-superiores>
            @foreach($acoes as $indice => $link)
                <a href="{{ $link['href'] }}" @if(empty($link['download'])) data-link @endif
                   class="auth-control inline-flex items-center gap-1.5 rounded px-3 text-sm font-semibold transition-colors {{ $indice === 0 ? 'bg-gray-800 text-white hover:bg-gray-700' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                    @if(!empty($link['icon']))
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5 shrink-0" aria-hidden="true">{!! $icone($link['icon']) !!}</svg>
                    @endif
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </x-slot:principal>

    <x-slot:resumo>
        <x-cockpit.indicadores :itens="$indicadoresEmpresa" data-empresa-indicadores />
    </x-slot:resumo>

    @include('autenticado.perfis._fluxo-cnpj', ['perfilCnpj' => $perfilCnpj])

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
</x-cockpit.layout>
