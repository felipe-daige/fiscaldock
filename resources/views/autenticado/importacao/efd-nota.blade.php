{{-- Detalhe de Nota EFD --}}
@php
    $tipoClass = $nota->tipo_operacao === 'entrada'
        ? 'bg-green-100 text-green-700'
        : 'bg-amber-100 text-amber-700';
    $tipoLabel = $nota->tipo_operacao === 'entrada' ? 'Entrada' : 'Saída';

    $modeloLabel = match($nota->modelo) {
        '00'  => 'NFS-e',
        '55'  => 'NF-e',
        '65'  => 'NFC-e',
        '57'  => 'CT-e',
        '67'  => 'CT-e OS',
        '01'  => 'Nota Fiscal',
        '1B'  => 'Nota Fiscal Avulsa',
        '04'  => 'Nota Fiscal de Produtor',
        default => null,
    };
    if (!$modeloLabel && ($nota->origem_arquivo ?? null) === 'contribuicoes') {
        $modeloLabel = 'NFS-e';
    }
    $modeloLabel = $modeloLabel ?? ($nota->modelo ? 'Modelo ' . $nota->modelo : null);

    $modeloBadgeClass = match(true) {
        in_array($nota->modelo, ['55', '65']) => 'bg-blue-100 text-blue-700',
        in_array($nota->modelo, ['57', '67']) => 'bg-purple-100 text-purple-700',
        $nota->modelo === '00' => 'bg-teal-100 text-teal-700',
        ($nota->origem_arquivo ?? null) === 'contribuicoes' => 'bg-teal-100 text-teal-700',
        default => 'bg-gray-100 text-gray-600',
    };

    $origemLabel = match($nota->origem_arquivo ?? '') {
        'fiscal' => 'EFD ICMS/IPI',
        'contribuicoes' => 'EFD PIS/COFINS',
        default => null,
    };
    $origemBadgeClass = match($nota->origem_arquivo ?? '') {
        'fiscal' => 'bg-indigo-100 text-indigo-700',
        'contribuicoes' => 'bg-teal-100 text-teal-700',
        default => 'bg-gray-100 text-gray-600',
    };

    $subtitulo = match($nota->modelo) {
        '55' => 'Nota Fiscal Eletrônica',
        '65' => 'Nota Fiscal de Consumidor Eletrônica',
        '57' => 'Conhecimento de Transporte Eletrônico',
        '67' => 'CT-e para Outros Serviços',
        '00' => 'Nota Fiscal de Serviço Eletrônica',
        default => ($nota->origem_arquivo ?? '') === 'contribuicoes' ? 'Nota Fiscal de Serviço' : 'Documento Fiscal',
    };

    $totalIcms = $nota->itens->sum('valor_icms');
    $totalPis = $nota->itens->sum('valor_pis');
    $totalCofins = $nota->itens->sum('valor_cofins');
    $temTributos = $totalIcms > 0 || $totalPis > 0 || $totalCofins > 0;
    $totalTributos = $totalIcms + $totalPis + $totalCofins;
    $totalItensValor = $nota->itens->sum('valor_total');
@endphp

<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <style>
            @keyframes card-slide-in {
                from { opacity: 0; transform: translateY(60px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .dash-animate {
                opacity: 0;
                animation: card-slide-in 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            @media (prefers-reduced-motion: reduce) {
                .dash-animate { opacity: 1; animation: none; }
            }
        </style>

        {{-- Cabeçalho --}}
        <div class="mb-4 sm:mb-6">
            <a href="{{ url()->previous() }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar
            </a>

            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                Nº {{ $nota->numero ?? '—' }}{{ $nota->serie ? '/' . $nota->serie : '' }}
            </h1>
            <div class="flex items-center gap-2 mt-1 flex-wrap">
                <p class="text-sm text-gray-500">{{ $subtitulo }}</p>
                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $tipoClass }}">{{ $tipoLabel }}</span>
                @if($modeloLabel)
                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $modeloBadgeClass }}">{{ $modeloLabel }}</span>
                @endif
                @if($origemLabel)
                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $origemBadgeClass }}">{{ $origemLabel }}</span>
                @endif
            </div>
        </div>

        {{-- Dados da Nota — card único compacto --}}
        <div class="bg-white rounded-lg border border-gray-200 mb-4 sm:mb-6 dash-animate">
            <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Dados da Nota</h2>
            </div>
            <div class="px-4 sm:px-5 py-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Data de Emissão</p>
                        <p class="font-medium text-gray-800">
                            {{ $nota->data_emissao ? \Carbon\Carbon::parse($nota->data_emissao)->format('d/m/Y') : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Valor Total</p>
                        <p class="font-semibold text-gray-900 text-base">
                            R$ {{ $nota->valor_total !== null ? number_format($nota->valor_total, 2, ',', '.') : '—' }}
                        </p>
                    </div>
                    @if($nota->valor_desconto)
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Desconto</p>
                        <p class="font-medium text-gray-800">
                            R$ {{ number_format($nota->valor_desconto, 2, ',', '.') }}
                        </p>
                    </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Itens</p>
                        <p class="font-medium text-gray-800">{{ $nota->itens->count() }}</p>
                    </div>
                </div>

                {{-- Chave de Acesso --}}
                @if($nota->chave_acesso)
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-0.5">Chave de Acesso</p>
                    <p class="font-mono text-xs text-gray-700 break-all tracking-wide">
                        {{ implode(' ', str_split($nota->chave_acesso, 4)) }}
                    </p>
                </div>
                @endif

                {{-- Resumo Tributário inline --}}
                @if($temTributos)
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">ICMS</p>
                            <p class="font-medium text-gray-800">R$ {{ number_format($totalIcms, 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">PIS</p>
                            <p class="font-medium text-gray-800">R$ {{ number_format($totalPis, 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">COFINS</p>
                            <p class="font-medium text-gray-800">R$ {{ number_format($totalCofins, 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Total Tributos</p>
                            <p class="font-bold text-gray-900">R$ {{ number_format($totalTributos, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Participante + Cliente --}}
        <div class="grid grid-cols-1 {{ $nota->participante && $nota->cliente ? 'lg:grid-cols-3' : '' }} gap-4 sm:gap-6 mb-4 sm:mb-6">

            {{-- Participante --}}
            @if($nota->participante)
            @php
                $p = $nota->participante;
                $situacaoClass = match(strtolower($p->situacao_cadastral ?? '')) {
                    'ativa' => 'bg-green-100 text-green-700',
                    default => 'bg-red-100 text-red-700',
                };
                $crtLabel = match((string)($p->crt ?? '')) {
                    '1' => 'Simples Nacional',
                    '2' => 'Excesso Simples',
                    '3' => 'Lucro Presumido/Real',
                    default => null,
                };
                $temDadosReceita = $p->cnae_principal || $p->porte || $p->capital_social;
                $municipioUf = collect([$p->municipio, $p->uf])->filter()->implode(' / ');
                $endereco = collect([$p->endereco, $p->numero, $p->bairro])->filter()->implode(', ');
                $cepFormatado = $p->cep ? preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', preg_replace('/\D/', '', $p->cep)) : null;
                $capitalFormatado = $p->capital_social ? 'R$ ' . number_format($p->capital_social, 2, ',', '.') : null;
            @endphp
            <div class="{{ $nota->cliente ? 'lg:col-span-2' : '' }} bg-white rounded-lg border border-gray-200 dash-animate" style="animation-delay: 0.1s">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Participante</h2>
                </div>
                <div class="px-4 sm:px-5 py-4 space-y-3 text-sm">

                    <div class="flex flex-wrap items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-base leading-tight">
                                <a href="/app/participante/{{ $p->id }}" data-link class="hover:text-blue-600 hover:underline transition-colors">{{ $p->razao_social ?? '—' }}</a>
                            </p>
                            @if($p->nome_fantasia)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $p->nome_fantasia }}</p>
                            @endif
                        </div>
                        <div class="flex flex-shrink-0 gap-2 flex-wrap">
                            @if($p->situacao_cadastral)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $situacaoClass }}">{{ $p->situacao_cadastral }}</span>
                            @endif
                            @if($p->regime_tributario)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $p->regime_tributario }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-2">
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">CNPJ / CPF</p>
                            <p class="font-mono text-gray-800">{{ $p->cnpj_formatado ?? '—' }}</p>
                        </div>
                        @if($p->inscricao_estadual)
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Inscrição Estadual</p>
                            <p class="font-mono text-gray-800">{{ $p->inscricao_estadual }}</p>
                        </div>
                        @endif
                        @if($crtLabel)
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">CRT</p>
                            <p class="text-gray-800">{{ $crtLabel }}</p>
                        </div>
                        @endif
                        @if($municipioUf)
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Município / UF</p>
                            <p class="text-gray-800">{{ $municipioUf }}</p>
                        </div>
                        @endif
                        @if($cepFormatado)
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">CEP</p>
                            <p class="font-mono text-gray-800">{{ $cepFormatado }}</p>
                        </div>
                        @endif
                        @if($endereco)
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Endereço</p>
                            <p class="text-gray-800">{{ $endereco }}</p>
                        </div>
                        @endif
                    </div>

                    @if($temDadosReceita)
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-2 pt-3 border-t border-gray-100">
                        @if($p->cnae_principal)
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">CNAE Principal</p>
                            <p class="text-gray-800">{{ $p->cnae_principal }}</p>
                        </div>
                        @endif
                        @if($p->porte)
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Porte</p>
                            <p class="text-gray-800">{{ $p->porte }}</p>
                        </div>
                        @endif
                        @if($capitalFormatado)
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Capital Social</p>
                            <p class="text-gray-800">{{ $capitalFormatado }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($p->data_inicio_atividade)
                    <div class="text-xs text-gray-500 pt-1">
                        Início de atividade: <span class="text-gray-700">{{ \Carbon\Carbon::parse($p->data_inicio_atividade)->format('d/m/Y') }}</span>
                    </div>
                    @endif

                </div>
            </div>
            @endif

            {{-- Cliente --}}
            @if($nota->cliente)
            <div class="bg-white rounded-lg border border-gray-200 dash-animate" style="animation-delay: 0.15s">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Cliente</h2>
                </div>
                <div class="px-4 sm:px-5 py-4 text-sm space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <a href="/app/cliente/{{ $nota->cliente->id }}" data-link class="font-semibold text-gray-900 hover:text-blue-600 hover:underline transition-colors">
                                {{ $nota->cliente->razao_social ?? '—' }}
                            </a>
                            @if($nota->cliente->documento_formatado)
                            <p class="text-xs font-mono text-gray-500">{{ $nota->cliente->documento_formatado }}</p>
                            @endif
                        </div>
                    </div>
                    @if($nota->cliente->municipio || $nota->cliente->uf)
                    <p class="text-xs text-gray-500">
                        {{ collect([$nota->cliente->municipio, $nota->cliente->uf])->filter()->implode(' / ') }}
                    </p>
                    @endif
                    @if($nota->cliente->situacao_cadastral || $nota->cliente->regime_tributario)
                    <div class="flex gap-2 flex-wrap">
                        @if($nota->cliente->situacao_cadastral)
                        @php $cliSitClass = strtolower($nota->cliente->situacao_cadastral) === 'ativa' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $cliSitClass }}">{{ $nota->cliente->situacao_cadastral }}</span>
                        @endif
                        @if($nota->cliente->regime_tributario)
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $nota->cliente->regime_tributario }}</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>

        {{-- Itens da Nota --}}
        <div class="bg-white rounded-lg border border-gray-200 dash-animate" style="animation-delay: 0.2s">
            <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Itens da Nota</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $nota->itens->count() }} {{ $nota->itens->count() === 1 ? 'item' : 'itens' }}</p>
                </div>
            </div>
            @if($nota->itens->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-3 py-2 text-left text-gray-500 font-medium">Nº</th>
                            <th class="px-3 py-2 text-left text-gray-500 font-medium">Cód</th>
                            <th class="px-3 py-2 text-left text-gray-500 font-medium">Descrição</th>
                            <th class="px-3 py-2 text-right text-gray-500 font-medium">Qtd</th>
                            <th class="px-3 py-2 text-left text-gray-500 font-medium">UN</th>
                            <th class="px-3 py-2 text-right text-gray-500 font-medium">Vlr Unit</th>
                            <th class="px-3 py-2 text-right text-gray-500 font-medium">Vlr Total</th>
                            <th class="px-3 py-2 text-center text-gray-500 font-medium">CFOP</th>
                            <th class="px-3 py-2 text-center text-gray-500 font-medium">CST</th>
                            <th class="px-3 py-2 text-right text-gray-500 font-medium">Alíq</th>
                            <th class="px-3 py-2 text-right text-gray-500 font-medium">ICMS</th>
                            <th class="px-3 py-2 text-right text-gray-500 font-medium">PIS</th>
                            <th class="px-3 py-2 text-right text-gray-500 font-medium">COFINS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($nota->itens as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-2 text-gray-700">{{ $item->numero_item ?? '—' }}</td>
                            <td class="px-3 py-2 font-mono text-gray-700">{{ $item->codigo_item ?? '—' }}</td>
                            <td class="px-3 py-2 text-gray-800 max-w-xs truncate">{{ $item->descricao ?? '—' }}</td>
                            <td class="px-3 py-2 text-right text-gray-700">
                                {{ $item->quantidade !== null ? number_format($item->quantidade, 2, ',', '.') : '—' }}
                            </td>
                            <td class="px-3 py-2 text-gray-700">{{ $item->unidade_medida ?? '—' }}</td>
                            <td class="px-3 py-2 text-right text-gray-700">
                                {{ $item->valor_unitario !== null ? number_format($item->valor_unitario, 2, ',', '.') : '—' }}
                            </td>
                            <td class="px-3 py-2 text-right font-medium text-gray-800">
                                {{ $item->valor_total !== null ? number_format($item->valor_total, 2, ',', '.') : '—' }}
                            </td>
                            <td class="px-3 py-2 text-center font-mono text-gray-700">{{ $item->cfop ?? '—' }}</td>
                            <td class="px-3 py-2 text-center text-gray-700">{{ $item->cst_icms ?? '—' }}</td>
                            <td class="px-3 py-2 text-right text-gray-700">
                                {{ $item->aliquota_icms !== null ? number_format($item->aliquota_icms, 2, ',', '.') . '%' : '—' }}
                            </td>
                            <td class="px-3 py-2 text-right text-gray-700">
                                {{ $item->valor_icms !== null ? number_format($item->valor_icms, 2, ',', '.') : '—' }}
                            </td>
                            <td class="px-3 py-2 text-right text-gray-700">
                                {{ $item->valor_pis !== null ? number_format($item->valor_pis, 2, ',', '.') : '—' }}
                            </td>
                            <td class="px-3 py-2 text-right text-gray-700">
                                {{ $item->valor_cofins !== null ? number_format($item->valor_cofins, 2, ',', '.') : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr class="font-medium text-xs text-gray-800">
                            <td class="px-3 py-2.5 text-right" colspan="6">Total</td>
                            <td class="px-3 py-2.5 text-right font-bold">{{ number_format($totalItensValor, 2, ',', '.') }}</td>
                            <td class="px-3 py-2.5" colspan="3"></td>
                            <td class="px-3 py-2.5 text-right font-semibold">{{ number_format($totalIcms, 2, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right font-semibold">{{ number_format($totalPis, 2, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right font-semibold">{{ number_format($totalCofins, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="px-5 py-6 sm:py-8 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Nenhum item registrado para esta nota.</p>
            </div>
            @endif
        </div>

    </div>
</div>
