@php
    $fontesPerfil = $fontesPerfil ?? [];
    $certidoesPerfil = $certidoesPerfil ?? [];
    $ultimaConsultaPerfil = $ultimaConsultaPerfil ?? null;
    $isCpf = $isCpf ?? false;
@endphp

<x-cockpit.secao
    titulo="Certidões e Cadastros Fiscais"
    subtitulo="Regularidade fiscal e situação estadual consolidadas por fonte."
    :contagem="$isCpf ? null : count($fontesPerfil)"
    body-class="p-0"
    data-perfil-card="certidoes"
>
    <x-slot:acao>
        @if(!$isCpf && $ultimaConsultaPerfil?->consultado_em)
            <span class="text-[11px] text-gray-500">Atualizado em {{ $ultimaConsultaPerfil->consultado_em->format('d/m/Y H:i') }}</span>
        @endif
    </x-slot:acao>

    @if($isCpf)
        <p class="px-4 py-5 text-sm text-gray-500 sm:px-5">Certidões de CNPJ e cadastros fiscais não se aplicam a pessoa física.</p>
    @elseif(!empty($fontesPerfil))
        @if($ultimaConsultaPerfil?->getMensagemExibivel())
            <div class="border-b border-gray-200 px-4 py-2 text-[11px] text-gray-500 sm:px-5">{{ $ultimaConsultaPerfil->getMensagemExibivel() }}</div>
        @endif
        <div class="p-3 sm:p-4" style="background-color: #f9fafb">
            @include('autenticado.consulta.partials.detalhe-blocos', [
                'blocos' => $fontesPerfil,
                'certidoes' => $certidoesPerfil,
                'resumo' => null,
                'cabecalho' => [],
            ])
        </div>
    @else
        <div class="px-4 py-5 text-sm text-gray-500 sm:px-5">
            Nenhuma consulta de certidões realizada ainda.
            <a href="/app/consulta/painel" data-link class="font-medium text-gray-700 underline hover:text-gray-900">Consultar agora</a>.
        </div>
    @endif
</x-cockpit.secao>
