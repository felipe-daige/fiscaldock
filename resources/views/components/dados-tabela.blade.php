{{-- Tabela descritiva universal do DANFE Modernizado.
     Recebe campos no formato:
     ['label' => string, 'valor' => mixed, 'mono' => bool, 'full' => bool, 'title' => ?string].
     A quantidade e a ordem dos campos pertencem ao chamador; valores ausentes viram "—" para
     preservar a estrutura visual entre entidades comparáveis. --}}
@props(['campos' => []])

<dl {{ $attributes->merge(['class' => 'grid grid-cols-2 border-l border-t border-gray-200']) }} data-dados-tabela>
    @foreach($campos as $campo)
        @php
            $valorBruto = $campo['valor'] ?? null;
            $valor = trim((string) $valorBruto);
            $valor = $valor !== '' ? $valor : '—';
            $title = trim((string) ($campo['title'] ?? ''));
        @endphp
        <div @class([
            'min-w-0 overflow-hidden border-r border-b border-gray-200 px-3 py-2',
            'h-16' => (bool) ($campo['full'] ?? false),
            'h-14' => ! (bool) ($campo['full'] ?? false),
            'col-span-2' => (bool) ($campo['full'] ?? false),
        ]) data-dado-celula>
            <dt class="text-[9px] font-semibold uppercase tracking-wide text-gray-400">
                {{ $campo['label'] ?? 'Dado' }}
            </dt>
            <dd @class([
                    'mt-1 text-[12px] font-medium leading-snug text-gray-700',
                    'line-clamp-2' => (bool) ($campo['full'] ?? false),
                    'line-clamp-1' => ! (bool) ($campo['full'] ?? false),
                    'font-mono tabular-nums' => (bool) ($campo['mono'] ?? false),
                ])
                @if($title !== '') title="{{ $title }}" @endif>
                {{ $valor }}
            </dd>
        </div>
    @endforeach
</dl>
