<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Integrações</h1>
            <p class="text-xs text-gray-500 mt-0.5">Status de disponibilidade exibido aos usuários em /app/status.</p>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'integracoes'])

        @if(session('ok'))
            <div class="mb-4 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-3 py-2">{{ session('ok') }}</div>
        @endif

        @foreach($grupos as $grupo)
            @if($grupo['itens']->isNotEmpty())
                <h2 class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mt-4 mb-2">{{ $grupo['titulo'] }}</h2>
                <div class="space-y-2 mb-4">
                    @foreach($grupo['itens'] as $i)
                        <form method="POST" action="{{ route('app.admin.integracoes.update', $i) }}"
                              class="bg-white border border-gray-300 rounded p-3 grid grid-cols-1 sm:grid-cols-[14rem_10rem_minmax(12rem,1fr)_auto] sm:items-center gap-3">
                            @csrf @method('PUT')
                            <span class="text-sm font-semibold text-gray-900">{{ $i->nome }}</span>
                            <select name="status" aria-label="Status de {{ $i->nome }}" class="w-full border-gray-300 rounded text-sm">
                                @foreach($statuses as $s)
                                    <option value="{{ $s }}" @selected($i->status === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            <input name="mensagem" maxlength="500" value="{{ $i->mensagem }}"
                                   placeholder="Mensagem (opcional)" class="w-full min-w-0 border-gray-300 rounded text-sm" />
                            <button class="w-full sm:w-auto text-white text-sm px-4 py-2 rounded hover:opacity-90" style="background-color:#111827">Salvar</button>
                        </form>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
</div>
