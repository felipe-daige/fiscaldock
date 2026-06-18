@forelse($triagem as $item)
    <a href="{{ $item['url'] }}" data-link class="flex items-center justify-between py-2.5 hover:bg-gray-50/60 -mx-1 px-1 rounded">
        <span class="flex items-center gap-2 text-sm text-gray-700">
            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $item['hex'] }}"></span>
            {{ $item['label'] }}
        </span>
        <span class="text-sm font-bold {{ $item['count'] > 0 ? 'text-gray-900' : 'text-gray-300' }}">{{ number_format($item['count'], 0, ',', '.') }}</span>
    </a>
@empty
    <p class="py-6 text-center text-sm text-gray-500">Nenhuma pendência — sua carteira está em dia.</p>
@endforelse
