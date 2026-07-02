@props(['valor' => null, 'nota' => null])

@php
    // Componente restaurado 2026-07-01 (era untracked e foi wipeado; app não bootava sem ele
    // pois muitas views usam <x-regime-tributario>). Reconciliar se o agente do regime tiver
    // uma versão mais rica.
    $valorTxt = trim((string) $valor);
    $notaTxt = trim((string) $nota);
    // Nota histórica "foi optante do <REGIME> até <DATA>" → exibe o regime histórico + a data.
    // O valor canônico ($valor) segue "Não informado" — a nota só enriquece o display.
    $hist = ($notaTxt !== '' && preg_match('/^foi optante do (.+?) at[ée] (.+)$/iu', $notaTxt, $m))
        ? ['regime' => trim($m[1]), 'ate' => trim($m[2])]
        : null;
    $display = $valorTxt !== '' ? $valorTxt : 'Não informado';
@endphp

@if($hist)
    <span {{ $attributes }} title="{{ $notaTxt }}">{{ $hist['regime'] }} <span style="color:#9ca3af;">(histórico)</span> · até {{ $hist['ate'] }}</span>
@elseif($notaTxt !== '')
    <span {{ $attributes }} title="{{ $notaTxt }}">{{ $display }} <span style="color:#9ca3af;font-size:.85em;">— {{ $notaTxt }}</span></span>
@else
    <span {{ $attributes }}>{{ $display }}</span>
@endif
