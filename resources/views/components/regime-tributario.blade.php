@props(['valor' => null, 'nota' => null])

@php
    // Regime em 2 linhas: valor principal + nota curta embaixo (cinza). Nada de nota inline
    // com "—" — em coluna estreita com truncate a nota era cortada. Tooltip carrega o texto
    // completo. Espelha o formatRegimeTributario do consulta-lote.js.
    $valorTxt = trim((string) $valor);
    $notaTxt = trim((string) $nota);
    // Nota histórica "foi optante do <REGIME> até <DATA>" → regime histórico + data.
    // O valor canônico ($valor) segue "Não informado" — a nota só enriquece o display.
    $hist = ($notaTxt !== '' && preg_match('/^foi optante do (.+?) at[ée] (.+)$/iu', $notaTxt, $m))
        ? ['regime' => trim($m[1]), 'ate' => trim($m[2])]
        : null;
    // "regime da matriz (RFB)" → rótulo curto padronizado.
    $notaCurta = preg_match('/matriz/iu', $notaTxt) ? 'Regime da matriz (RFB)' : $notaTxt;
    // Regime estimado pelo sistema (RFB não publica): marca no valor + rótulo curto.
    $estimado = (bool) preg_match('/^estimado/iu', $notaTxt);
    if ($estimado) {
        $notaCurta = 'Estimado — RFB não publica';
    }
    $display = $valorTxt !== '' ? $valorTxt : 'Não informado';
@endphp

@if($hist)
    <span {{ $attributes->merge(['class' => 'inline-flex flex-col leading-tight']) }} title="Regime atual não publicado pela Receita — último regime conhecido: {{ $notaTxt }}">
        <span>{{ $hist['regime'] }} <span style="color:#9ca3af;">(histórico)</span></span>
        <span class="text-[11px]" style="color:#9ca3af;">até {{ $hist['ate'] }}</span>
    </span>
@elseif($notaTxt !== '')
    <span {{ $attributes->merge(['class' => 'inline-flex flex-col leading-tight']) }} title="{{ $display }} — {{ $notaTxt }}">
        <span>{{ $display }}@if($estimado) <span style="color:#9ca3af;">(estimado)</span>@endif</span>
        <span class="text-[11px]" style="color:#9ca3af;">{{ $notaCurta }}</span>
    </span>
@else
    <span {{ $attributes }}>{{ $display }}</span>
@endif
