<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-6">
        <h1 class="text-lg font-bold text-gray-900 uppercase tracking-wide mb-4">Catálogo × Itens de Nota</h1>
        <p class="text-xs text-gray-500 mb-4">{{ $kpis['sem_catalogo'] }} item(ns) sem catálogo · {{ $kpis['total_itens'] }} itens movimentados</p>
        <table class="w-full text-sm">
            <thead><tr><th class="text-left">Código</th><th class="text-left">Origem</th><th class="text-right">Valor</th></tr></thead>
            <tbody>
            @foreach($itens as $item)
                <tr>
                    <td>{{ $item['codigo_item'] }}</td>
                    <td>{{ $item['fontes'] }}</td>
                    <td class="text-right">{{ number_format($item['valor_total'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
