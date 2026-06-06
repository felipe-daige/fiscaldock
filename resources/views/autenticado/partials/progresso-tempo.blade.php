{{--
    Linha de tempo decorrido + microcopy de expectativa para telas de automação assíncrona.
    Reutilizável por Consulta CNPJ, Clearance DF-e, Importação EFD, etc. Gerida pelo módulo
    public/js/progresso-automacao.js (cronômetro + shimmer + dica). Ver memory
    `project_padrao_progresso_automacao`.

    Parâmetros (@include):
      - $prefixo : prefixo dos ids (o JS lê "{prefixo}-tempo-valor" e "{prefixo}-dica").
      - $dica    : texto da microcopy (em minúscula no começo, estilo nota discreta).
--}}
<p id="{{ $prefixo }}-tempo" class="text-[11px] text-gray-500 mt-2 flex items-center gap-1">
    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span>decorrido <span id="{{ $prefixo }}-tempo-valor" class="font-mono">00:00</span></span>
</p>
@if(!empty($dica))
    <p id="{{ $prefixo }}-dica" class="text-[11px] text-gray-400 mt-1 hidden">{{ $dica }}</p>
@endif
