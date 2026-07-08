@props([
    'format' => 'pdf',      // pdf | xlsx | csv
    'path',                 // rota de download GET
    'descricao' => null,
    'modalId' => 'modal-exportar-catalogo',
    'overlay' => 'download-overlay-catalogo',
])

@php
    // Variante do export-option para o Catálogo: em vez de montar a querystring a partir de
    // selects por id, reaproveita window.location.search — o form de filtros é GET, então a
    // URL da página já carrega TODOS os filtros ativos (inclusive cfops[]/csts[] multi-select).
    // Mesmo protocolo de overlay/cookie do design system (iframe + poll do cookie bi_download).
    $labels = ['pdf' => 'PDF', 'xlsx' => 'Planilha Excel (.xlsx)', 'csv' => 'Planilha CSV (.csv)'];
    $label = $labels[$format] ?? strtoupper((string) $format);

    $diferencas = [
        'pdf' => 'Documento pronto para ler, imprimir e enviar ao cliente. Não é editável.',
        'xlsx' => 'Abre no Excel/Google Sheets já formatado, com abas e valores numéricos — dá para somar, filtrar e pivotar.',
        'csv' => 'ZIP com uma tabela por arquivo, texto puro. Universal: abre em qualquer sistema.',
    ];
    $diferenca = $diferencas[$format] ?? null;

    $tintas = ['pdf' => '#b91c1c', 'xlsx' => '#047857', 'csv' => '#1d4ed8'];
    $tinta = $tintas[$format] ?? '#374151';

    $icones = [
        'pdf' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 3v4a1 1 0 001 1h4M9 13h6M9 17h4"/>'
            .'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 8.5V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h6.5L19 8.5z"/>',
        'xlsx' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/>'
            .'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 9h16M4 15h16M10 3v18"/>',
        'csv' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/>'
            .'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 8h8M8 12h8M8 16h5"/>',
    ];
    $iconePath = $icones[$format] ?? $icones['pdf'];

    // JS: fecha o modal, monta a URL a partir de location.search (limpa page/download_token
    // antigos), dispara download em iframe oculto e faz poll do cookie bi_download.
    $js = "(function(){"
        . "document.getElementById('".addslashes($modalId)."').classList.add('hidden');"
        . "var ov=document.getElementById('".addslashes($overlay)."');"
        . "var tok='d'+Date.now()+Math.floor(Math.random()*1e6);"
        . "var sp=new URLSearchParams(window.location.search);"
        . "sp.delete('page');sp.delete('download_token');"
        . "sp.set('download_token',tok);"
        . "var u='".addslashes($path)."?'+sp.toString();"
        . "document.cookie='bi_download=; path=/; max-age=0';"
        . "if(ov)ov.classList.remove('hidden');"
        . "var f=document.createElement('iframe');f.style.display='none';f.src=u;document.body.appendChild(f);"
        . "var n=0;var t=setInterval(function(){n++;"
        .   "if(document.cookie.indexOf('bi_download=')>-1){"
        .     "clearInterval(t);document.cookie='bi_download=; path=/; max-age=0';"
        .     "if(ov)ov.classList.add('hidden');setTimeout(function(){f.remove();},60000);"
        .   "}else if(n>1040){"
        .     "clearInterval(t);if(ov)ov.classList.add('hidden');setTimeout(function(){f.remove();},60000);"
        .   "}},250);"
        . "})()";

    $icon = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="'.$tinta.'" viewBox="0 0 24 24" aria-hidden="true">'.$iconePath.'</svg>';
@endphp

<button type="button" data-export-option="{{ $format }}" onclick="{{ $js }}"
    {{ $attributes->merge(['class' => 'flex w-full items-start gap-3 rounded border border-gray-300 px-4 py-3 text-left transition-colors hover:bg-gray-50']) }}>
    <span class="mt-0.5 shrink-0">{!! $icon !!}</span>
    <span class="min-w-0">
        <span class="block text-sm font-semibold text-gray-900">{{ $label }}</span>
        @if ($descricao)
            <span class="block text-[12px] text-gray-600">{{ $descricao }}</span>
        @endif
        @if ($diferenca)
            <span class="mt-0.5 block text-[11px] leading-snug text-gray-400">{{ $diferenca }}</span>
        @endif
    </span>
</button>
