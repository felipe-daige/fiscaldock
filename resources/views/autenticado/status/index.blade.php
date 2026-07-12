@php
    $todosItens = collect($grupos)->flatMap(fn ($g) => $g['itens']->values());
    $problemas = $todosItens->filter(fn ($i) => $i->status !== \App\Models\IntegracaoStatus::STATUS_OPERACIONAL);
    $ultimaAtualizacao = $todosItens->max('updated_at');

    // Pior status decide a cor do banner: fora > degradado > manutenção.
    $gravidade = [
        \App\Models\IntegracaoStatus::STATUS_FORA => 3,
        \App\Models\IntegracaoStatus::STATUS_DEGRADADO => 2,
        \App\Models\IntegracaoStatus::STATUS_MANUTENCAO => 1,
    ];
    $pior = $problemas->sortByDesc(fn ($i) => $gravidade[$i->status] ?? 0)->first();
    $bannerCor = $pior ? $pior->corHex : '#047857';

    $legenda = [
        ['label' => 'Operacional', 'cor' => '#047857'],
        ['label' => 'Degradado', 'cor' => '#b45309'],
        ['label' => 'Fora do ar', 'cor' => '#dc2626'],
        ['label' => 'Em manutenção', 'cor' => '#2563eb'],
    ];
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Status dos serviços</h1>
            <p class="mt-1 text-xs text-gray-500">Disponibilidade atual das integrações usadas pela plataforma.</p>
        </div>

        {{-- Banner geral --}}
        <div class="rounded px-4 py-3 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2" style="background-color: {{ $bannerCor }}">
            <div class="flex items-center gap-2.5">
                @if($problemas->isEmpty())
                    <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-semibold text-white">Todos os sistemas operacionais</span>
                @else
                    <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    <span class="text-sm font-semibold text-white">{{ $problemas->count() }} {{ $problemas->count() === 1 ? 'serviço' : 'serviços' }} com problema</span>
                @endif
            </div>
            @if($ultimaAtualizacao)
                <span class="text-[11px] text-white/80 whitespace-nowrap">Atualizado {{ $ultimaAtualizacao->diffForHumans() }}</span>
            @endif
        </div>

        <div class="space-y-6">
            @foreach($grupos as $grupo)
                @if($grupo['itens']->isNotEmpty())
                    @php($operacionais = $grupo['itens']->where('status', \App\Models\IntegracaoStatus::STATUS_OPERACIONAL)->count())
                    <div class="bg-white rounded border border-gray-300 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                            <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">{{ $grupo['titulo'] }}</span>
                            <span class="text-[11px] {{ $operacionais === $grupo['itens']->count() ? 'text-gray-400' : 'font-semibold text-gray-600' }}">
                                {{ $operacionais }}/{{ $grupo['itens']->count() }} operacionais
                            </span>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($grupo['itens'] as $i)
                                <div class="px-4 py-3 flex items-start gap-3">
                                    <span class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0" style="background-color: {{ $i->corHex }}"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $i->nome }}</p>
                                        @if($i->mensagem)
                                            <p class="text-xs text-gray-600 mt-0.5 whitespace-pre-line">{{ $i->mensagem }}</p>
                                        @endif
                                        <p class="text-[11px] text-gray-400 mt-1">
                                            atualizado {{ $i->updated_at->diffForHumans() }}
                                            @if($i->atualizadoPor) · por {{ $i->atualizadoPor->name }} @endif
                                        </p>
                                    </div>
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white whitespace-nowrap mt-0.5" style="background-color: {{ $i->corHex }}">
                                        {{ $i->label }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Legenda --}}
        <div class="flex flex-wrap gap-x-4 gap-y-1.5 mt-5 text-[11px] text-gray-500">
            @foreach($legenda as $l)
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $l['cor'] }}"></span>
                    {{ $l['label'] }}
                </span>
            @endforeach
        </div>
    </div>
</div>
