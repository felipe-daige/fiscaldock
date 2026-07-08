@props(['label'])

{{-- Legenda de grupo dentro do modal Exportar (ex.: "Documento", "Planilhas").
     Separa visualmente o PDF das duas planilhas, para o usuário entender que
     XLSX e CSV são o mesmo tipo de coisa em formatos diferentes. --}}
<p class="pt-2 pb-1 text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ $label }}</p>
