@php
    use App\Services\PricingCatalogService;
    $precos = app(PricingCatalogService::class);
    $fmtLim = fn ($v) => $v === null ? 'Ilimitado' : number_format((int) $v, 0, ',', '.');
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Planos</h1>
            <p class="text-xs text-gray-500 mt-0.5">Edite limites, capabilities e preço direto no catálogo (subscription_plans). Os entitlements de todos os usuários leem daqui.</p>
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
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="text-left px-3 py-2">Plano</th>
                            <th class="text-right px-3 py-2">Mensal</th>
                            <th class="text-right px-3 py-2">Créditos</th>
                            <th class="text-right px-3 py-2">Clientes</th>
                            <th class="text-right px-3 py-2">Monitorados</th>
                            <th class="text-left px-3 py-2">Profundidade</th>
                            <th class="text-center px-3 py-2">Ativo</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($planos as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2.5">
                                    <span class="font-semibold text-gray-900">{{ $p->nome }}</span>
                                    <span class="block text-[11px] text-gray-400">{{ $p->codigo }}</span>
                                </td>
                                <td class="px-3 py-2.5 text-right text-gray-700">{{ $p->preco_mensal_centavos > 0 ? 'R$ '.number_format($p->preco_mensal_centavos / 100, 2, ',', '.') : '—' }}</td>
                                <td class="px-3 py-2.5 text-right text-gray-700">{{ number_format((int) $p->creditos_inclusos, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5 text-right text-gray-700">{{ $fmtLim($p->limite_clientes) }}</td>
                                <td class="px-3 py-2.5 text-right text-gray-700">{{ $fmtLim($p->limite_cnpjs_monitorados) }}</td>
                                <td class="px-3 py-2.5 text-gray-700">{{ $p->profundidade_auto_monitor ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-center">
                                    @if($p->is_active)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color: #047857">Ativo</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color: #6b7280">Inativo</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-right">
                                    <a href="{{ route('app.admin.planos.edit', $p->id) }}" data-link
                                        class="inline-flex items-center px-3 py-1.5 rounded text-[11px] font-bold uppercase tracking-wide text-white hover:opacity-90" style="background-color: #0b1f3a">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
