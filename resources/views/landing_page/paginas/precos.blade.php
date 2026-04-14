@php
    $pricingData = $pricingData ?? [];
    $packages = $pricingData['packages'] ?? [];
    $tiers = $pricingData['tiers'] ?? [];
    $products = $pricingData['products'] ?? [];
    $creditUnitPrice = $pricingData['credit_unit_price'] ?? 0.20;
@endphp

@push('structured-data')
@include('landing_page.partials.breadcrumb-schema', [
    'trail' => [
        ['name' => 'Início', 'url' => url('/')],
        ['name' => 'Preços', 'url' => url('/precos')],
    ],
])
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => 'FiscalDock — Créditos para consultas fiscais',
    'description' => 'Compra avulsa de créditos para consultas de compliance e clearance, com economia progressiva por volume acumulado.',
    'brand' => ['@type' => 'Brand', 'name' => 'FiscalDock'],
    'offers' => array_map(fn ($package) => [
        '@type' => 'Offer',
        'name' => $package['nome'],
        'price' => number_format($package['preco'], 2, '.', ''),
        'priceCurrency' => 'BRL',
        'availability' => 'https://schema.org/InStock',
        'url' => route('signup'),
        'description' => $package['descricao'],
    ], $packages),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

<section id="precos-hero" class="bg-white pt-14 pb-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto text-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide text-white" style="background-color: #1e4fa0">
                Sem assinatura
            </span>
            <h1 class="mt-6 text-4xl md:text-5xl font-bold text-gray-900">
                Créditos avulsos com <span class="text-blue-600">faixas de economia</span>
            </h1>
            <p class="mt-5 text-lg text-gray-600">
                Você compra créditos quando precisar. Conforme o histórico acumulado de créditos pagos cresce,
                o custo das consultas cai automaticamente nas faixas X, Y e Z.
            </p>
            <div class="mt-8 space-y-4">
                <div class="flex justify-center">
                    <a href="{{ route('signup') }}" data-link class="btn-cta">Criar conta grátis</a>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-3">
                    <a href="{{ route('agendar') }}" data-link class="text-sm font-medium text-gray-600 hover:text-gray-900 hover:underline">
                        Falar com especialista
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="precos-modelo" class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Crédito</p>
                <p class="mt-3 text-3xl font-bold text-gray-900">R$ {{ number_format($creditUnitPrice, 2, ',', '.') }}</p>
                <p class="mt-2 text-sm text-gray-600">Preço unitário estável. O ganho vem da faixa comercial, não de mensalidade.</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Faixas</p>
                <p class="mt-3 text-3xl font-bold text-gray-900">Base → X → Y → Z</p>
                <p class="mt-2 text-sm text-gray-600">Sua faixa sobe pelo histórico de créditos pagos acumulados e melhora o custo das consultas futuras.</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Validade</p>
                <p class="mt-3 text-3xl font-bold text-gray-900">Pré-pago</p>
                <p class="mt-2 text-sm text-gray-600">Créditos comprados não expiram. Só o bônus promocional do trial expira em 30 dias.</p>
            </div>
        </div>
    </div>
</section>

<section id="precos-pacotes" class="bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Pacotes de créditos</h2>
            <p class="mt-3 text-base text-gray-600">Escolha o saldo inicial. A economia por consulta vem da sua faixa acumulada.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            @foreach($packages as $package)
                <div class="bg-white rounded-xl border border-gray-200 p-6 flex flex-col h-full">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $package['nome'] }}</h3>
                            <p class="mt-2 text-sm text-gray-500">{{ number_format($package['creditos'], 0, ',', '.') }} créditos</p>
                        </div>
                        @if($package['slug'] === 'business')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #0f766e">Referência Base</span>
                        @endif
                    </div>
                    <p class="mt-5 text-4xl font-bold text-gray-900">R$ {{ number_format($package['preco'], 0, ',', '.') }}</p>
                    <p class="mt-3 text-sm text-gray-600 flex-1">{{ $package['descricao'] }}</p>
                    <a href="{{ route('signup') }}" data-link class="mt-6 btn-cta btn-cta--block text-center">
                        Criar conta grátis
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="precos-faixas" class="bg-white py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Faixas de economia</h2>
            <p class="mt-3 text-base text-gray-600">Os descontos entram automaticamente conforme o histórico de créditos pagos acumulados.</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 bg-gray-50">Faixa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 bg-gray-50">Histórico pago acumulado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 bg-gray-50">Efeito</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($tiers as $tier)
                            <tr>
                                <td class="px-4 py-4 text-sm font-semibold text-gray-900">{{ $tier['nome'] }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    @if($tier['max_paid_credits'] === null)
                                        {{ number_format($tier['min_paid_credits'], 0, ',', '.') }}+ créditos pagos
                                    @elseif($tier['min_paid_credits'] === 0)
                                        Até {{ number_format($tier['max_paid_credits'], 0, ',', '.') }} créditos pagos
                                    @else
                                        {{ number_format($tier['min_paid_credits'], 0, ',', '.') }} a {{ number_format($tier['max_paid_credits'], 0, ',', '.') }} créditos pagos
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">Reduz o custo das consultas de Compliance e Clearance nas próximas execuções.</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section id="precos-consumo" class="bg-gray-50 py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Custo por consulta e por 100 consultas</h2>
            <p class="mt-3 text-base text-gray-600">A tabela abaixo já considera o valor fixo de R$ {{ number_format($creditUnitPrice, 2, ',', '.') }} por crédito.</p>
        </div>
        <div class="space-y-6">
            @foreach($products as $product)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-2xl font-bold text-gray-900">{{ $product['nome'] }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ $product['descricao'] }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 bg-gray-50">Faixa</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 bg-gray-50">Créditos por consulta</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 bg-gray-50">Preço por consulta</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 bg-gray-50">Preço por 100 consultas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($product['rows'] as $row)
                                    <tr>
                                        <td class="px-4 py-4 text-sm font-semibold text-gray-900">{{ $row['tier_name'] }}</td>
                                        <td class="px-4 py-4 text-sm text-gray-600">{{ number_format($row['credits'], 0, ',', '.') }} créditos</td>
                                        <td class="px-4 py-4 text-sm text-gray-600">R$ {{ number_format($row['price'], 2, ',', '.') }}</td>
                                        <td class="px-4 py-4 text-sm font-semibold text-gray-900">R$ {{ number_format($row['price_for_100'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="precos-proximos-passos" class="bg-white py-14">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-50 rounded-2xl border border-gray-200 p-8 text-center">
            <h2 class="text-3xl font-bold text-gray-900">Comece no seu ritmo</h2>
            <p class="mt-4 text-base text-gray-600">
                Crie a conta, receba o trial inicial e compre créditos quando quiser. Se precisar de ajuda para estimar volume, fale com nosso time.
            </p>
            <div class="mt-8 space-y-4">
                <div class="flex justify-center">
                    <a href="{{ route('signup') }}" data-link class="btn-cta">Criar conta grátis</a>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('duvidas') }}" data-link class="inline-flex items-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-400 hover:text-gray-900">
                        Ainda com dúvidas?
                    </a>
                    <a href="{{ route('solucoes') }}" data-link class="inline-flex items-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-400 hover:text-gray-900">
                        Entenda cada módulo
                    </a>
                    <a href="{{ route('blog.tema', 'efd') }}" data-link class="inline-flex items-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-400 hover:text-gray-900">
                        Leia o guia de EFD
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
