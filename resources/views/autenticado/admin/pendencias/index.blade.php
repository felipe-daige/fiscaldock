@php
    $vencidasTotal = $abertas->filter(fn ($p) => $p->esta_vencida)->count();
    $comLembreteTotal = $abertas->filter(fn ($p) => $p->lembrar_em && ! $p->esta_vencida)->count();
    $semDataTotal = $abertas->filter(fn ($p) => ! $p->lembrar_em)->count();
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Pendências</h1>
            <p class="text-xs text-gray-500 mt-0.5">Lembretes e notas operacionais do time FiscalDock. Compartilhado entre admins.</p>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'pendencias'])

        @if(session('ok'))
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color:#047857">
                {{ session('ok') }}
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <div class="xl:col-span-2 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Pendências abertas</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $vencidasTotal }} vencida(s), {{ $comLembreteTotal }} com lembrete e {{ $semDataTotal }} sem data.
                        </p>
                    </div>
                    @if($vencidasTotal > 0)
                        <span class="inline-flex w-fit px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#b91c1c">
                            Priorizar vencidas
                        </span>
                    @endif
                </div>

                @forelse($abertas as $p)
                    @php
                        $cor = $p->esta_vencida ? '#b91c1c' : ($p->lembrar_em ? '#b45309' : '#334155');
                        $status = $p->esta_vencida ? 'Vencida' : ($p->lembrar_em ? 'Lembrete' : 'Sem data');
                    @endphp
                    <div class="bg-white border border-gray-300 border-l-4 rounded overflow-hidden" style="border-left-color: {{ $cor }}">
                        <div class="p-4">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:{{ $cor }}">{{ $status }}</span>
                                        @if($p->lembrar_em)
                                            <span class="text-[11px] @class(['text-red-700 font-semibold' => $p->esta_vencida, 'text-gray-500' => ! $p->esta_vencida])">
                                                {{ $p->esta_vencida ? 'vencida em' : 'lembrar em' }} {{ $p->lembrar_em->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900 mt-2">{{ $p->titulo }}</p>
                                    @if($p->nota)
                                        <p class="text-xs text-gray-600 mt-1 whitespace-pre-line">{{ $p->nota }}</p>
                                    @endif
                                    <p class="text-[11px] text-gray-400 mt-2">
                                        Criada por {{ $p->criadoPor?->name ?? '—' }} em {{ $p->created_at->format('d/m/Y') }}
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('app.admin.pendencias.resolver', $p) }}" class="shrink-0">
                                    @csrf
                                    <button class="w-full md:w-auto px-3 py-2 rounded text-[12px] font-bold uppercase tracking-wide text-white hover:opacity-90" style="background-color:#047857">
                                        Resolver
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded border border-gray-300 p-8 text-center">
                        <p class="text-sm font-semibold text-gray-900">Nenhuma pendência aberta.</p>
                        <p class="text-xs text-gray-500 mt-1">Use o painel ao lado para registrar o próximo lembrete operacional.</p>
                    </div>
                @endforelse
            </div>

            <div class="space-y-4">
                <form method="POST" action="{{ route('app.admin.pendencias.store') }}" class="bg-white border border-gray-300 rounded overflow-hidden">
                    @csrf
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Nova pendência</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Registre um lembrete compartilhado entre admins.</p>
                    </div>
                    <div class="p-4 space-y-3">
                        <div>
                            <label for="titulo" class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Título</label>
                            <input id="titulo" name="titulo" required maxlength="255" placeholder="Ex.: revisar parâmetro comercial"
                                   value="{{ old('titulo') }}"
                                   class="w-full border border-gray-300 bg-gray-50 rounded text-sm px-3 py-2.5 shadow-sm focus:bg-white focus:border-gray-500 focus:ring-1 focus:ring-gray-500" />
                            @error('titulo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="nota" class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Nota / contexto</label>
                            <textarea id="nota" name="nota" rows="4" placeholder="Contexto opcional para o próximo operador"
                                      class="w-full border border-gray-300 bg-gray-50 rounded text-sm px-3 py-2.5 shadow-sm focus:bg-white focus:border-gray-500 focus:ring-1 focus:ring-gray-500">{{ old('nota') }}</textarea>
                        </div>
                        <div>
                            <label for="lembrar_em" class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Lembrar em</label>
                            <input id="lembrar_em" type="date" name="lembrar_em" value="{{ old('lembrar_em') }}"
                                   class="w-full border border-gray-300 bg-gray-50 rounded text-sm px-3 py-2.5 shadow-sm focus:bg-white focus:border-gray-500 focus:ring-1 focus:ring-gray-500" />
                        </div>
                        <button class="w-full px-4 py-2.5 rounded text-[12px] font-bold uppercase tracking-wide text-white hover:opacity-90" style="background-color:#0b1f3a">
                            Adicionar pendência
                        </button>
                    </div>
                </form>

                <div class="bg-white border border-gray-300 rounded overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Resolvidas</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Últimas {{ $resolvidas->count() }} pendências fechadas.</p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#334155">{{ $resolvidas->count() }}</span>
                    </div>

                    @if($resolvidas->isNotEmpty())
                        <div class="divide-y divide-gray-100">
                            @foreach($resolvidas as $p)
                                <div class="p-3">
                                    <p class="text-sm font-semibold text-gray-700 line-through">{{ $p->titulo }}</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">
                                        {{ $p->resolvidoPor?->name ?? '—' }} · {{ $p->resolvido_em?->format('d/m/Y') }}
                                    </p>
                                    <div class="flex flex-wrap gap-3 mt-2 text-xs">
                                        <form method="POST" action="{{ route('app.admin.pendencias.reabrir', $p) }}">
                                            @csrf
                                            <button class="text-gray-600 hover:underline">reabrir</button>
                                        </form>
                                        <form method="POST" action="{{ route('app.admin.pendencias.destroy', $p) }}" onsubmit="return confirm('Excluir esta pendência?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:underline">excluir</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <p class="text-sm text-gray-400">Nenhuma pendência resolvida recentemente.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
