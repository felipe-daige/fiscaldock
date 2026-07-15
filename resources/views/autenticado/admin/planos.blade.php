@php
    use App\Services\PricingCatalogService;
    $precos = app(PricingCatalogService::class);
    $fmtStorage = function ($p) {
        $caps = $p->capabilities ?? [];
        $mb = array_key_exists('armazenamento_mb', $caps)
            ? $caps['armazenamento_mb']
            : config("arquivos.quota_por_plano_mb.{$p->codigo}", config('arquivos.quota_padrao_mb', 250));

        return $mb === null ? 'Ilimitado' : ($mb >= 1024 ? number_format($mb / 1024, 0, ',', '.').' GB' : $mb.' MB');
    };
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Planos</h1>
            <p class="text-xs text-gray-500 mt-0.5">Edite preços, saldo, assentos e capabilities direto no catálogo. As vitrines pública e autenticada leem estes mesmos dados.</p>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'planos'])

        @if(session('status'))
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color: #047857">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-xs text-gray-600" style="border-left-color: #b45309">
            <strong>Atenção:</strong> mudar o preço aqui afeta telas e gating, mas <strong>não</strong> sincroniza a cobrança recorrente no Mercado Pago — o valor cobrado segue o <code>preapproval_plan</code> do MP. Ajuste o plano no painel do MP e atualize o <code>mp_preapproval_plan_id</code> na edição.
        </div>

        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div>
                <table class="w-full text-[13px] tabela-cards">
                    <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="text-left px-3 py-2">Plano</th>
                            <th class="text-right px-3 py-2">Mensal</th>
                            <th class="text-right px-3 py-2">Anual</th>
                            <th class="text-right px-3 py-2">Saldo incluso/mês</th>
                            <th class="text-right px-3 py-2">Acessos</th>
                            <th class="text-right px-3 py-2">Assento extra</th>
                            <th class="text-right px-3 py-2">Arquivos</th>
                            <th class="text-center px-3 py-2">Ativo</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($planos as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2.5" data-label="Plano">
                                    <span class="font-semibold text-gray-900">{{ $p->nome }}</span>
                                    <span class="block text-[11px] text-gray-400">{{ $p->codigo }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-right text-gray-700" data-label="Mensal">{{ $p->preco_mensal_centavos > 0 ? 'R$ '.number_format($p->preco_mensal_centavos / 100, 2, ',', '.') : '—' }}</td>
                                <td class="px-3 py-2.5 text-right text-gray-700" data-label="Anual">{{ $p->preco_anual_centavos > 0 ? 'R$ '.number_format($p->preco_anual_centavos / 100, 2, ',', '.') : '—' }}</td>
                                <td class="px-3 py-2.5 text-right text-gray-700" data-label="Saldo incluso/mês">{{ \App\Support\Dinheiro::brl(($p->creditos_inclusos)) }}</td>
                                <td class="px-3 py-2.5 text-right text-gray-700" data-label="Acessos">{{ $p->assentos_inclusos >= 9999 ? 'Sob medida' : number_format((int) $p->assentos_inclusos, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5 text-right text-gray-700" data-label="Assento extra">{{ $p->preco_assento_extra_centavos > 0 ? 'R$ '.number_format($p->preco_assento_extra_centavos / 100, 2, ',', '.') : '—' }}</td>
                                <td class="px-3 py-2.5 text-right text-gray-700" data-label="Arquivos">{{ $fmtStorage($p) }}</td>
                                <td class="px-3 py-2.5 text-center" data-label="Ativo">
                                    @if($p->is_active)
                                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color: #047857">Ativo</span>
                                    @else
                                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color: #6b7280">Inativo</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-right" data-label="Ações">
                                    <a href="{{ route('app.admin.planos.edit', $p->id) }}" data-link
                                        class="admin-action w-full sm:w-auto inline-flex items-center justify-center px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide text-white hover:opacity-90" style="background-color: #0b1f3a">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
