{{-- Linha de relacionamento/contraparte (flex, mobile-safe — não é <td>).
     Nome trunca à esquerda (min-w-0); valores ficam visíveis à direita (shrink-0).
     Espera: $rel, $papelHex, $papelLabel. --}}
@php($relNome = $rel['nome'] ?? $rel['empresa_nome'] ?? '—')
@php($relPropria = $rel['is_propria'] ?? $rel['is_empresa_propria'] ?? false)
@php($relEntrada = (float) ($rel['valor_entrada'] ?? 0))
@php($relSaida = (float) ($rel['valor_saida'] ?? 0))
<div class="flex items-start justify-between gap-2 py-1 text-[11px]">
    <div class="min-w-0">
        <p class="text-slate-700 truncate" title="{{ $relNome }}">{{ $relNome }}@if($relPropria) <span class="text-slate-400">(própria)</span>@endif</p>
        <p class="font-semibold leading-tight" style="color: {{ $papelHex[$rel['papel']] ?? '#374151' }}">{{ $papelLabel[$rel['papel']] ?? '—' }}</p>
    </div>
    <div class="shrink-0 text-right font-mono text-slate-700">
        <p>R$ {{ number_format($relEntrada + $relSaida, 2, ',', '.') }}</p>
        @if($relEntrada > 0)<p class="text-[10px] leading-tight font-normal" style="color: #2563eb">↓ entrada R$ {{ number_format($relEntrada, 2, ',', '.') }}</p>@endif
        @if($relSaida > 0)<p class="text-[10px] leading-tight font-normal" style="color: #0f766e">↑ saída R$ {{ number_format($relSaida, 2, ',', '.') }}</p>@endif
    </div>
</div>
