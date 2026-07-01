<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
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
                              class="bg-white border border-gray-300 rounded p-3 flex flex-wrap items-center gap-3">
                            @csrf @method('PUT')
                            <span class="text-sm font-semibold text-gray-900 w-56 shrink-0">{{ $i->nome }}</span>
                            <select name="status" class="border-gray-300 rounded text-sm">
                                @foreach($statuses as $s)
                                    <option value="{{ $s }}" @selected($i->status === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            <input name="mensagem" maxlength="500" value="{{ $i->mensagem }}"
                                   placeholder="Mensagem (opcional)" class="flex-1 min-w-[12rem] border-gray-300 rounded text-sm" />
                            <button class="bg-gray-900 text-white text-sm px-4 py-1.5 rounded hover:bg-gray-700">Salvar</button>
                        </form>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
</div>
