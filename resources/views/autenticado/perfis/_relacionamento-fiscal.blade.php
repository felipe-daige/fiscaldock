@php
    $fiscalPerfil = $fiscalPerfil ?? null;
    $perspectivaParticipante = ($fiscalPerfil['perspectiva'] ?? null) === 'participante';
    $relacionamentosPerfil = collect($fiscalPerfil['relacionamentos'] ?? [])->take(10);
    $indicadoresRelacionamento = [
        [
            'label' => $perspectivaParticipante ? 'Compras com este CNPJ' : 'Compras',
            'valor' => \App\Support\Dinheiro::brl($fiscalPerfil['total_comprado'] ?? 0),
            'sub' => number_format($fiscalPerfil['qtd_entrada'] ?? 0, 0, ',', '.').' notas de entrada',
            'mono' => true,
        ],
        [
            'label' => $perspectivaParticipante ? 'Vendas para este CNPJ' : 'Vendas',
            'valor' => \App\Support\Dinheiro::brl($fiscalPerfil['total_vendido'] ?? 0),
            'sub' => number_format($fiscalPerfil['qtd_saida'] ?? 0, 0, ',', '.').' notas de saída',
            'mono' => true,
        ],
        [
            'label' => 'Notas no Acervo',
            'valor' => number_format($fiscalPerfil['qtd_notas'] ?? 0, 0, ',', '.'),
            'sub' => 'Documentos fiscais considerados',
        ],
        [
            'label' => $perspectivaParticipante ? 'Empresas Relacionadas' : 'Contrapartes',
            'valor' => number_format($fiscalPerfil['empresas_count'] ?? $relacionamentosPerfil->count(), 0, ',', '.'),
            'sub' => 'Relacionamentos encontrados',
        ],
    ];
    $papelHex = ['fornecedor' => '#2563eb', 'cliente' => '#0f766e', 'ambos' => '#7c3aed'];
    $papelLabel = ['fornecedor' => 'Fornecedor', 'cliente' => 'Cliente', 'ambos' => 'Fornecedor e cliente'];
@endphp

<x-cockpit.secao
    titulo="Relacionamento & Movimentação Fiscal"
    subtitulo="Compras, vendas e principais relações encontradas no acervo EFD."
    body-class="p-0"
    data-perfil-card="relacionamento"
>
    <x-slot:acao>
        @if(!empty($fiscalPerfil['primeira_nota']) && !empty($fiscalPerfil['ultima_nota']))
            <span class="font-mono text-[11px] text-gray-500">
                {{ \Carbon\Carbon::parse($fiscalPerfil['primeira_nota'])->format('m/Y') }}–{{ \Carbon\Carbon::parse($fiscalPerfil['ultima_nota'])->format('m/Y') }}
            </span>
        @endif
    </x-slot:acao>

    @if(empty($fiscalPerfil))
        <p class="px-4 py-5 text-sm text-gray-500 sm:px-5">Sem movimentação no acervo fiscal deste CNPJ.</p>
    @else
        <x-cockpit.indicadores :itens="$indicadoresRelacionamento" class="border-b border-gray-200" />

        <div class="p-4 sm:p-5">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500">{{ $fiscalPerfil['relacionamentos_titulo'] ?? 'Principais contrapartes' }}</p>
                <span class="text-[11px] text-gray-400">Top {{ $relacionamentosPerfil->count() }}</span>
            </div>
            @if($relacionamentosPerfil->isEmpty())
                <p class="text-sm text-gray-500">Nenhum relacionamento identificado.</p>
            @else
                <div class="divide-y divide-gray-100 rounded border border-gray-200">
                    @foreach($relacionamentosPerfil as $relacao)
                        @php
                            $nome = $relacao['nome'] ?? $relacao['empresa_nome'] ?? '—';
                            $entrada = (float) ($relacao['valor_entrada'] ?? 0);
                            $saida = (float) ($relacao['valor_saida'] ?? 0);
                            $papel = $relacao['papel'] ?? null;
                        @endphp
                        <div class="flex flex-col gap-2 px-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-800" title="{{ $nome }}">{{ $nome }}</p>
                                <p class="mt-0.5 text-[11px] font-semibold" style="color: {{ $papelHex[$papel] ?? '#6b7280' }}">
                                    {{ $papelLabel[$papel] ?? 'Relacionamento fiscal' }}
                                    @if(!empty($relacao['is_propria']) || !empty($relacao['is_empresa_propria'])) · Empresa própria @endif
                                </p>
                            </div>
                            <div class="shrink-0 text-left sm:text-right">
                                <p class="font-mono text-sm font-semibold text-gray-900">{{ \App\Support\Dinheiro::brl($entrada + $saida) }}</p>
                                <p class="text-[10px] text-gray-400">
                                    @if($entrada > 0) Compras {{ \App\Support\Dinheiro::brl($entrada) }} @endif
                                    @if($entrada > 0 && $saida > 0) · @endif
                                    @if($saida > 0) Vendas {{ \App\Support\Dinheiro::brl($saida) }} @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</x-cockpit.secao>
