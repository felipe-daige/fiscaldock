@php
    $perfil = $perfil ?? null;
    $papel = $papel ?? 'Parte';
    $papelDocumento = $papelDocumento ?? null;
    $nome = $perfil?->razao_social ?: ($perfil?->nome ?: ($nomeFallback ?? 'Não identificado'));
    $nomeFantasia = $perfil?->nome_fantasia;
    $documentoRaw = $perfil?->documento ?: ($documentoFallback ?? null);
    $digitosDocumento = preg_replace('/\D/', '', (string) $documentoRaw);
    $documentoFormatado = match (strlen($digitosDocumento)) {
        14 => preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $digitosDocumento),
        11 => preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $digitosDocumento),
        default => $documentoRaw,
    };
    $situacao = trim((string) ($perfil?->situacao_cadastral ?? ''));
    $situacaoHex = match (mb_strtoupper($situacao)) {
        'ATIVA', '02' => '#047857',
        '' => null,
        default => '#dc2626',
    };
    $local = collect([$perfil?->municipio, $perfil?->uf])->filter()->implode(' / ');
    $isCliente = $perfil instanceof \App\Models\Cliente;
    $href = $perfil
        ? ($isCliente ? "/app/cliente/{$perfil->id}" : "/app/participante/{$perfil->id}")
        : null;
@endphp

<div class="rounded border border-gray-200 bg-white overflow-hidden">
    <div class="bg-gray-50 px-3 py-2 border-b border-gray-200 flex items-center justify-between gap-2">
        <div>
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Perfil do {{ mb_strtolower($papel) }}</p>
            @if($papelDocumento)
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $papelDocumento }}</p>
            @endif
        </div>
        @if($isCliente && $perfil?->is_empresa_propria)
            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: #1d4ed8">Empresa própria</span>
        @else
            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $isCliente ? '#1d4ed8' : '#6b7280' }}">{{ $papel }}</span>
        @endif
    </div>
    <div class="p-3">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0">
                @if($href)
                    <a href="{{ $href }}" data-link class="text-sm font-semibold text-gray-900 hover:text-gray-600 hover:underline">{{ $nome }}</a>
                @else
                    <p class="text-sm font-semibold text-gray-900">{{ $nome }}</p>
                @endif
                @if($nomeFantasia)
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $nomeFantasia }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-1.5">
                @if($situacaoHex)
                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $situacaoHex }}">{{ $situacao }}</span>
                @endif
                @if($perfil?->regime_tributario)
                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: #0f766e">{{ $perfil->regime_tributario }}</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2.5 mt-3">
            <div>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">CNPJ / CPF</p>
                <p class="text-[11px] font-mono text-gray-700 mt-0.5">{{ $documentoFormatado ?: '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Inscrição estadual</p>
                <p class="text-[11px] font-mono text-gray-700 mt-0.5">{{ $perfil?->inscricao_estadual ?: '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Município / UF</p>
                <p class="text-[11px] text-gray-700 mt-0.5">{{ $local ?: '—' }}</p>
            </div>
        </div>

        @unless($perfil)
            <p class="text-[10px] text-gray-400 mt-2">Perfil cadastral completo ainda não disponível; exibindo os dados identificados na nota.</p>
        @endunless
    </div>
</div>
