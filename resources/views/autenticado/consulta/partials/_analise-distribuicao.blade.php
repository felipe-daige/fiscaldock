{{-- Barra de distribuição (regular/atenção/indeterminado/neutro) de uma fonte da
     Análise da Consulta. Espera $f (com total/regular/atencao/indeterminado/neutro). --}}
@php($tot = max(1, (int) ($f['total'] ?? 0)))
<div class="flex h-2.5 w-full rounded-full overflow-hidden" style="background-color: #f3f4f6">
    @if((int) ($f['regular'] ?? 0) > 0)<div style="width: {{ round(($f['regular'] / $tot) * 100, 2) }}%; background-color: #047857" title="Regular: {{ (int) $f['regular'] }}"></div>@endif
    @if((int) ($f['atencao'] ?? 0) > 0)<div style="width: {{ round(($f['atencao'] / $tot) * 100, 2) }}%; background-color: #dc2626" title="Atenção: {{ (int) $f['atencao'] }}"></div>@endif
    @if((int) ($f['indeterminado'] ?? 0) > 0)<div style="width: {{ round(($f['indeterminado'] / $tot) * 100, 2) }}%; background-color: #d97706" title="Indeterminado: {{ (int) $f['indeterminado'] }}"></div>@endif
    @if((int) ($f['neutro'] ?? 0) > 0)<div style="width: {{ round(($f['neutro'] / $tot) * 100, 2) }}%; background-color: #9ca3af" title="Não consultado: {{ (int) $f['neutro'] }}"></div>@endif
</div>
