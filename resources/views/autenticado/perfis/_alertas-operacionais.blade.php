@php
    $ordemSeveridade = [
        'critico' => 4,
        'crítico' => 4,
        'critica' => 4,
        'crítica' => 4,
        'alta' => 4,
        'atencao' => 3,
        'atenção' => 3,
        'media' => 2,
        'média' => 2,
        'info' => 1,
        'baixa' => 0,
    ];
    $normalizarSeveridade = fn (array $alerta): string => mb_strtolower((string) ($alerta['severidade'] ?? $alerta['tipo'] ?? 'info'));
    $alertasPerfil = collect($alertasPerfil ?? [])
        ->sortByDesc(fn (array $alerta) => $ordemSeveridade[$normalizarSeveridade($alerta)] ?? 0)
        ->values();
    $tipoHex = [
        'critico' => '#b91c1c',
        'crítico' => '#b91c1c',
        'critica' => '#b91c1c',
        'crítica' => '#b91c1c',
        'alta' => '#b91c1c',
        'atencao' => '#b45309',
        'media' => '#b45309',
        'info' => '#2563eb',
        'baixa' => '#6b7280',
    ];
    $tiposVermelhos = ['critico', 'crítico', 'critica', 'crítica', 'alta'];
    $temAlertaVermelho = $alertasPerfil->contains(fn (array $alerta) => in_array($normalizarSeveridade($alerta), $tiposVermelhos, true));
    $alertaPreview = $alertasPerfil->first();
    $tipoPreview = $alertaPreview ? $normalizarSeveridade($alertaPreview) : 'info';
    $hexPreview = $alertaPreview
        ? ($alertaPreview['hex'] ?? $tipoHex[$tipoPreview] ?? '#6b7280')
        : '#047857';
    $tituloPreview = trim((string) ($alertaPreview['titulo'] ?? ''));
    $descricaoPreview = trim((string) ($alertaPreview['descricao'] ?? $alertaPreview['mensagem'] ?? ''));
    $textoPreview = $alertaPreview
        ? trim($tituloPreview.($tituloPreview && $descricaoPreview ? ' — ' : '').$descricaoPreview)
        : 'Nenhum alerta operacional no momento.';
    $labelPreview = $tipoPreview === 'alta' ? 'ALTA' : 'CRÍTICO';
@endphp

<details
    class="group overflow-hidden rounded border border-gray-300 bg-white"
    @if($temAlertaVermelho) style="border-left-width: 4px; border-left-color: #b91c1c" @endif
    data-cockpit-secao
    data-perfil-card="alertas"
    data-perfil-alertas-retratil
    @if($temAlertaVermelho) data-perfil-alerta-destaque="vermelho" @endif
>
    <summary
        class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-2 [&::-webkit-details-marker]:hidden"
        style="background-color: {{ $temAlertaVermelho ? '#fef2f2' : '#f9fafb' }}"
    >
        <span class="min-w-0">
            <span class="block text-[10px] font-semibold uppercase tracking-widest" style="color: {{ $temAlertaVermelho ? '#991b1b' : '#6b7280' }}">Alertas Operacionais</span>
            <span class="mt-1 flex min-w-0 items-center gap-2" data-perfil-alertas-preview>
                <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $hexPreview }}"></span>
                @if($temAlertaVermelho)
                    <span class="shrink-0 rounded px-1.5 py-0.5 text-[9px] font-bold tracking-wide text-white" style="background-color: #b91c1c">{{ $labelPreview }}</span>
                @endif
                <span class="truncate text-[11px] {{ $temAlertaVermelho ? 'font-semibold' : ($alertaPreview ? 'text-gray-600' : 'text-gray-400') }}" @if($temAlertaVermelho) style="color: #991b1b" @endif title="{{ $textoPreview }}">
                    {{ $textoPreview ?: 'Ocorrência fiscal identificada.' }}
                </span>
            </span>
        </span>
        <span class="flex shrink-0 items-center gap-3">
            <span class="rounded px-2 py-0.5 text-[10px] font-semibold {{ $temAlertaVermelho ? 'text-white' : 'text-gray-600' }}" style="background-color: {{ $temAlertaVermelho ? '#b91c1c' : '#e5e7eb' }}">{{ $alertasPerfil->count() }}</span>
            <span class="text-lg leading-none transition-transform group-open:rotate-180" style="color: {{ $temAlertaVermelho ? '#b91c1c' : '#9ca3af' }}" aria-hidden="true">⌄</span>
        </span>
    </summary>

    <div class="border-t border-gray-200 p-4 sm:p-5">
        @if($alertasPerfil->isEmpty())
            <div class="flex items-center gap-3 rounded border border-gray-300 border-l-4 p-4" style="border-left-color: #047857">
                <span class="h-2 w-2 shrink-0 rounded-full" style="background-color: #047857"></span>
                <p class="text-sm text-gray-700">Nenhum alerta operacional no momento.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($alertasPerfil as $alerta)
                    @php
                        $tipo = mb_strtolower((string) ($alerta['severidade'] ?? $alerta['tipo'] ?? 'info'));
                        $hex = $alerta['hex'] ?? $tipoHex[$tipo] ?? '#6b7280';
                        $titulo = $alerta['titulo'] ?? null;
                        $descricao = $alerta['descricao'] ?? $alerta['mensagem'] ?? 'Ocorrência fiscal identificada.';
                    @endphp
                    <article class="rounded border border-gray-300 border-l-4 p-4" style="border-left-color: {{ $hex }}">
                        @if($titulo)
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold text-gray-900">{{ $titulo }}</p>
                                <span class="rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $hex }}">
                                    {{ $tipo }}
                                </span>
                            </div>
                        @endif
                        <p class="{{ $titulo ? 'mt-1' : '' }} text-sm leading-relaxed text-gray-700">{{ $descricao }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</details>
