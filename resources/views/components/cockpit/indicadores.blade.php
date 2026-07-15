@props(['itens' => []])

<dl {{ $attributes->merge(['class' => 'flex flex-col divide-y divide-gray-200 lg:flex-row lg:divide-x lg:divide-y-0']) }}
    data-cockpit-indicadores>
    @foreach($itens as $item)
        <div class="min-w-0 flex-1 px-4 py-4 sm:px-5">
            <dt class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $item['label'] ?? 'Indicador' }}</dt>
            <dd class="mt-2 flex min-w-0 flex-wrap items-center gap-2">
                @if(!empty($item['url']))
                    <a href="{{ $item['url'] }}" @if(($item['data_link'] ?? true) === true) data-link @endif
                       class="min-w-0 break-words text-xl font-bold text-gray-900 hover:text-gray-600 hover:underline {{ !empty($item['mono']) ? 'font-mono tabular-nums' : '' }}">
                        {{ $item['valor'] ?? '—' }}
                    </a>
                @else
                    <strong class="min-w-0 break-words text-xl font-bold text-gray-900 {{ !empty($item['mono']) ? 'font-mono tabular-nums' : '' }}">{{ $item['valor'] ?? '—' }}</strong>
                @endif

                @if(!empty($item['badge']))
                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white"
                          style="background-color: {{ $item['badge']['hex'] ?? '#6b7280' }}">{{ $item['badge']['label'] ?? '' }}</span>
                @endif
            </dd>
            @if(!empty($item['sub']))
                <p class="mt-1 break-words text-[11px] text-gray-500 {{ !empty($item['sub_clamp']) ? 'line-clamp-2' : '' }}"
                   @if(!empty($item['sub_clamp'])) title="{{ $item['sub'] }}" @endif>{{ $item['sub'] }}</p>
            @endif
            @if(!empty($item['link_url']))
                <a href="{{ $item['link_url'] }}" @if(($item['link_data_link'] ?? true) === true) data-link @endif
                   class="mt-1 inline-flex text-[11px] font-semibold text-gray-600 hover:text-gray-900 hover:underline">
                    {{ $item['link_label'] ?? 'Ver detalhes' }} →
                </a>
            @endif
        </div>
    @endforeach
</dl>
