{{-- Minha Empresa - Dashboard --}}
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
        ],
    ];

    if ($participante) {
        $acoes[] = [
            'label' => 'Score Fiscal',
            'href' => '/app/score-fiscal/participante/' . $participante->id,
            'primary' => false,
        ];
    }

    if ($ultimaConsulta && $ultimaConsulta->lote) {
        $acoes[] = [
            'label' => 'Baixar Relatório',
            'href' => '/app/consulta/lote/' . $ultimaConsulta->lote->id . '/baixar',
            'primary' => false,
            'download' => true,
        ];
    }
@endphp

<div class="min-h-screen bg-gray-100" id="minha-empresa-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Minha Empresa</h1>
                    <p class="text-xs text-gray-500 mt-1">{{ $empresaNome }} | {{ $documento }}</p>
                </div>
                <a href="/app/minha-empresa/configurar" data-link class="text-xs text-gray-600 hover:text-gray-900 hover:underline whitespace-nowrap">
                    Alterar empresa principal
                </a>
            </div>
            <div class="flex flex-wrap items-center gap-2 mt-3">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $situacaoBadge['hex'] }}">{{ $situacaoBadge['label'] }}</span>
                @foreach($regimes as $regime)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $regime['hex'] }}">{{ $regime['label'] }}</span>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6 sm:mb-8">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo Fiscal</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200">
                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1 sm:mb-2">Score de Risco</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $scoreBadge['valor'] }}</p>
                    <div class="mt-1 sm:mt-2 flex flex-wrap items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $scoreBadge['hex'] }}">{{ $scoreBadge['label'] }}</span>
                        @if($score?->ultima_consulta_em)
                            <span class="text-[11px] text-gray-500">{{ $score->ultima_consulta_em->format('d/m/Y') }}</span>
                        @endif
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1 sm:mb-2">Situação Cadastral</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ $situacaoBadge['label'] }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Receita Federal e cadastros correlatos</p>
                </div>

                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1 sm:mb-2">Créditos</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ number_format($userCredits, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">Disponíveis para consultas</p>
                </div>

                <div class="p-4 sm:p-6">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1 sm:mb-2">Base Monitorada</p>
                    <p class="text-lg sm:text-xl font-bold text-gray-900">{{ number_format($totalParticipantes, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1">{{ number_format($totalNotas, 0, ',', '.') }} notas registradas</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6 sm:mb-8">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resumo Cadastral da Empresa</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-200">
                @foreach($dadosEmpresa as $dado)
                    <div class="px-4 py-3 sm:px-5 sm:py-4">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">{{ $dado['label'] }}</p>
                        <p class="text-sm text-gray-700 {{ $dado['mono'] ? 'font-mono' : '' }}">{{ $dado['valor'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6 sm:mb-8">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Certidões</span>
                <span class="text-[11px] text-gray-500">
                    Última consulta: {{ $ultimaConsultaResumo['data'] }}
                    @if($ultimaConsultaResumo['tipos'] !== 'Sem consultas realizadas') · {{ $ultimaConsultaResumo['tipos'] }} @endif
                </span>
            </div>
            @if($ultimaConsultaMensagem)
                <div class="px-4 py-2 border-b border-gray-100 text-[11px] text-gray-500">{{ $ultimaConsultaMensagem }}</div>
            @endif
            <div class="overflow-x-auto">
                <table class="min-w-full tabela-cards">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Indicador</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Status</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Validade / Referência</th>
                            <th class="px-3 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide bg-gray-50">Comprovante</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($certidaoLinhas as $linha)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-3 py-3 text-sm text-gray-700">{{ $linha['nome'] }}</td>
                                <td class="px-3 py-3" data-label="Status">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $linha['badge']['hex'] }}">{{ $linha['badge']['label'] }}</span>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-700" data-label="Validade / Referência">{{ $linha['validade'] }}</td>
                                <td class="px-3 py-3 text-sm" data-label="Comprovante">
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

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6 sm:mb-8">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Alertas Operacionais</span>
            </div>
            <div class="p-4">
                @if(count($alertas) > 0)
                    <div class="space-y-3">
                        @foreach($alertas as $alerta)
                            <div class="bg-white rounded border border-gray-300 border-l-4 {{ $alertaStyles[$alerta['tipo']] ?? 'border-l-gray-400' }} p-4">
                                <p class="text-sm text-gray-700">{{ $alerta['mensagem'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded border border-gray-300 border-l-4 border-l-green-500 p-4">
                        <p class="text-sm text-gray-700">Nenhum alerta no momento.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6 sm:mb-8">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Ações</span>
            </div>
            <div class="p-4">
                <div class="flex flex-wrap gap-3">
                    @foreach($acoes as $acao)
                        {{-- Ação de download NÃO leva data-link: o SPA faria fetch XHR do arquivo pra dentro do #app em vez de baixar. --}}
                        <a href="{{ $acao['href'] }}" @unless(!empty($acao['download'])) data-link @endunless class="{{ $acao['primary'] ? 'bg-gray-800 text-white hover:bg-gray-700' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50' }} inline-flex items-center justify-center rounded text-sm font-medium px-4 py-2 transition-colors">
                            {{ $acao['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6 sm:mb-8">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Certificado Digital (A1)</span>
            </div>
            <div class="p-4">
                @if(session('status'))
                    <p class="text-[12px] text-green-700 mb-3">{{ session('status') }}</p>
                @endif
                @error('certificado')
                    <p class="text-[12px] text-red-700 mb-3">{{ $message }}</p>
                @enderror

                @if($certificado ?? null)
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $certificado['badge_hex'] }}">
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
                    <form method="POST" action="{{ route('app.minha-empresa.certificado.salvar') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-end gap-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] text-gray-500 mb-1">Arquivo (.pfx/.p12)</label>
                            <input type="file" name="certificado" accept=".pfx,.p12" required
                                class="text-[13px] text-gray-600 file:mr-3 file:cursor-pointer file:rounded file:border-0 file:bg-gray-800 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-gray-700">
                        </div>
                        <div>
                            <label class="block text-[11px] text-gray-500 mb-1">Senha do certificado</label>
                            <input type="password" name="senha" required class="border border-gray-300 rounded px-3 py-2.5 text-[13px]">
                        </div>
                        <button type="submit" class="bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium px-4 py-2 transition-colors">Cadastrar certificado</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
