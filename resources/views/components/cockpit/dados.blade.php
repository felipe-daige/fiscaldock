@props(['itens' => []])

<dl {{ $attributes->merge(['class' => 'divide-y divide-gray-100']) }} data-cockpit-dados>
    @foreach($itens as $item)
        @php
            $valor = $item['valor'] ?? null;
            $texto = is_scalar($valor) ? trim((string) $valor) : '';
        @endphp
        <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-baseline sm:px-5">
            <dt class="shrink-0 text-[10px] font-semibold text-gray-400 uppercase tracking-wide sm:w-56">{{ $item['label'] ?? 'Dado' }}</dt>
            <dd class="min-w-0 break-words text-sm text-gray-700 {{ !empty($item['mono']) ? 'font-mono tabular-nums' : '' }} {{ !empty($item['destaque']) ? 'font-semibold text-gray-900' : '' }}">
                @if(!empty($item['badge']))
                    <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white"
                          style="background-color: {{ $item['badge']['hex'] ?? '#6b7280' }}">{{ $item['badge']['label'] ?? '—' }}</span>
                @elseif(!empty($item['href']))
                    <a href="{{ $item['href'] }}" class="font-medium text-gray-700 hover:text-gray-900 hover:underline">{{ $texto !== '' ? $texto : '—' }}</a>
                @else
                    {{ $texto !== '' ? $texto : '—' }}
                @endif
            </dd>
        </div>
    @endforeach
</dl>
