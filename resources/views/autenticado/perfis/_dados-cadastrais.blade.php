@php
    $cadastroPerfil = $cadastroPerfil ?? [];
    $isCpf = (bool) ($cadastroPerfil['is_cpf'] ?? false);
    $cnaesPerfil = collect($cadastroPerfil['cnaes'] ?? []);
    $qsaPerfil = collect($cadastroPerfil['qsa'] ?? []);
    $consultadoEm = $cadastroPerfil['consultado_em'] ?? null;
    $principalValores = [true, 1, '1', 'true', 'SIM', 'S'];
@endphp

<x-cockpit.secao
    titulo="Dados Cadastrais"
    subtitulo="Identificação, registro e composição do cadastro."
    body-class="p-0"
    data-perfil-card="cadastro"
>
    <x-slot:acao>
        @if($consultadoEm)
            <span class="text-[11px] text-gray-500">Atualizado em {{ $consultadoEm->format('d/m/Y H:i') }}</span>
        @endif
    </x-slot:acao>

    <x-cockpit.dados :itens="$cadastroPerfil['basicos'] ?? []" />

    <div class="divide-y divide-gray-200 border-t border-gray-200">
        <details data-perfil-cadastro-detalhe="registro">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50 sm:px-5">
                <span>
                    <span class="block text-[10px] font-semibold uppercase tracking-widest text-gray-500">Registro Jurídico e Econômico</span>
                    <span class="mt-0.5 block text-[11px] text-gray-400">Natureza jurídica, porte, regime, capital e início de atividade.</span>
                </span>
                <span class="text-lg leading-none text-gray-400" aria-hidden="true">⌄</span>
            </summary>
            <div class="border-t border-gray-200">
                @if($isCpf)
                    <p class="px-4 py-4 text-sm text-gray-500 sm:px-5">Registro jurídico e econômico não se aplica a pessoa física.</p>
                @else
                    <x-cockpit.dados :itens="$cadastroPerfil['registro'] ?? []" />
                @endif
            </div>
        </details>

        <details data-perfil-cadastro-detalhe="cnaes">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50 sm:px-5">
                <span>
                    <span class="block text-[10px] font-semibold uppercase tracking-widest text-gray-500">Atividades Econômicas (CNAE)</span>
                    <span class="mt-0.5 block text-[11px] text-gray-400">
                        {{ $isCpf ? 'Não se aplica a pessoa física' : ($cnaesPerfil->count() ? $cnaesPerfil->count().' atividade(s) cadastrada(s)' : 'Nenhuma atividade informada') }}
                    </span>
                </span>
                <span class="text-lg leading-none text-gray-400" aria-hidden="true">⌄</span>
            </summary>
            <div class="border-t border-gray-200 px-4 py-4 sm:px-5">
                @forelse($cnaesPerfil as $cnae)
                    @php
                        $principal = in_array($cnae['principal'] ?? false, $principalValores, true);
                    @endphp
                    <div class="flex items-start gap-2 py-1.5">
                        @if($principal)
                            <span class="shrink-0 rounded px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: #4338ca">Principal</span>
                        @endif
                        <p class="text-sm text-gray-700">
                            <span class="font-mono text-gray-500">{{ $cnae['codigo'] ?? '—' }}</span>
                            — {{ $cnae['descricao'] ?? 'Descrição não informada' }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ $isCpf ? 'Atividades econômicas não se aplicam a pessoa física.' : 'Nenhuma atividade econômica disponível.' }}</p>
                @endforelse
            </div>
        </details>

        <details data-perfil-cadastro-detalhe="qsa">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50 sm:px-5">
                <span>
                    <span class="block text-[10px] font-semibold uppercase tracking-widest text-gray-500">Quadro Societário (QSA)</span>
                    <span class="mt-0.5 block text-[11px] text-gray-400">
                        {{ $isCpf ? 'Não se aplica a pessoa física' : ($qsaPerfil->count() ? $qsaPerfil->count().' integrante(s) encontrado(s)' : 'Nenhum integrante informado') }}
                    </span>
                </span>
                <span class="text-lg leading-none text-gray-400" aria-hidden="true">⌄</span>
            </summary>
            <div class="divide-y divide-gray-100 border-t border-gray-200">
                @forelse($qsaPerfil as $socio)
                    <div class="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-start sm:justify-between sm:px-5">
                        <div class="min-w-0">
                            <p class="break-words text-sm text-gray-700">{{ $socio['nome'] ?? '—' }}</p>
                            <p class="text-[11px] text-gray-400">{{ $socio['qualificacao'] ?? 'Qualificação não informada' }}</p>
                        </div>
                        @if(!empty($socio['cpf_cnpj']))
                            <span class="shrink-0 font-mono text-[11px] text-gray-400">{{ $socio['cpf_cnpj'] }}</span>
                        @endif
                    </div>
                @empty
                    <p class="px-4 py-4 text-sm text-gray-500 sm:px-5">{{ $isCpf ? 'Quadro societário não se aplica a pessoa física.' : 'Nenhum dado societário disponível.' }}</p>
                @endforelse
            </div>
        </details>

        <details data-perfil-cadastro-detalhe="contato">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50 sm:px-5">
                <span>
                    <span class="block text-[10px] font-semibold uppercase tracking-widest text-gray-500">Endereço e Contato Consultados</span>
                    <span class="mt-0.5 block text-[11px] text-gray-400">Localização e contatos retornados pela última consulta cadastral.</span>
                </span>
                <span class="text-lg leading-none text-gray-400" aria-hidden="true">⌄</span>
            </summary>
            <div class="border-t border-gray-200">
                <x-cockpit.dados :itens="$cadastroPerfil['contato'] ?? []" />
            </div>
        </details>
    </div>
</x-cockpit.secao>
