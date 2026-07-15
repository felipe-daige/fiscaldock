@php
    $detalheConsulta = $detalheConsulta ?? null;
    $fontesConsultadas = collect($detalheConsulta['blocos'] ?? [])
        ->reject(fn (array $bloco) => ($bloco['chave'] ?? null) === 'cadastro')
        ->values()
        ->all();
    $certidoesConsultadas = $detalheConsulta['certidoes'] ?? [];
    $consultadoEm = $detalheConsulta['consultado_em'] ?? null;
    $totalFontes = count($fontesConsultadas);
@endphp

<section class="p-4" data-certidoes-contexto="{{ $contexto ?? 'cadastro' }}">
    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500">{{ $entidadeLabel ?? 'Cadastro' }}</p>
                <span class="whitespace-nowrap rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white"
                      style="background-color: {{ $totalFontes > 0 ? '#374151' : '#9ca3af' }}">
                    {{ $totalFontes > 0 ? $totalFontes.' '.($totalFontes === 1 ? 'fonte' : 'fontes') : 'Não consultado' }}
                </span>
            </div>
            @if(!empty($nomeCadastro))
                <p class="mt-1 break-words text-sm font-semibold leading-snug text-gray-800">{{ $nomeCadastro }}</p>
            @endif
            <p class="mt-1 text-[11px] text-gray-500">
                {{ $totalFontes > 0 ? 'Resultado fiscal mais recente disponível para este cadastro.' : 'Nenhum resultado fiscal disponível para este cadastro.' }}
            </p>
        </div>
        @if($consultadoEm)
            <span class="whitespace-nowrap text-[10px] font-semibold text-gray-500">
                Consultado em {{ $consultadoEm->format('d/m/Y H:i') }}
            </span>
        @endif
    </div>

    @if($fontesConsultadas !== [])
        <div class="mt-3">
            @include('autenticado.consulta.partials.detalhe-blocos', [
                'blocos' => $fontesConsultadas,
                'certidoes' => $certidoesConsultadas,
                'resumo' => null,
                'cabecalho' => [],
            ])
        </div>
    @else
        <div class="mt-3 rounded border border-dashed border-gray-300 px-3 py-3 text-xs text-gray-500">
            Ainda não há fontes fiscais consultadas para este cadastro.
            <a href="{{ $consultaUrl ?? '/app/consulta/painel' }}" data-link class="font-semibold text-gray-700 hover:text-gray-900 hover:underline">Consultar agora</a>.
        </div>
    @endif
</section>
