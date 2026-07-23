{{-- Admin — criação/edição de kit da consulta por fontes ($kit null = novo) --}}
@php($_novo = $kit === null)
<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">{{ $_novo ? 'Novo kit' : 'Editar kit — '.$kit->nome }}</h1>
            <p class="text-xs text-gray-500 mt-0.5">O kit preenche a seleção na tela de Consulta por Fontes; o desconto só vale quando a seleção final bate exatamente com as fontes marcadas aqui.</p>
        </div>

        @if($errors->any())
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color: #b91c1c">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ $_novo ? '/app/admin/kits' : '/app/admin/kits/'.$kit->id }}" class="bg-white rounded border border-gray-300 p-4 sm:p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Nome</span>
                    <input type="text" name="nome" value="{{ old('nome', $kit->nome ?? '') }}" required maxlength="120"
                           class="mt-1 w-full border border-gray-300 rounded text-sm px-3 py-2 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                </label>
                <label class="block">
                    <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Desconto (%)</span>
                    <input type="number" name="desconto_percentual" value="{{ old('desconto_percentual', $kit->desconto_percentual ?? 0) }}" min="0" max="100" step="0.01" required
                           class="mt-1 w-full border border-gray-300 rounded text-sm px-3 py-2 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                </label>
            </div>

            <label class="block">
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Preço fixo (R$) — opcional</span>
                <input type="number" name="preco_fixo" value="{{ old('preco_fixo', isset($kit->preco_fixo) ? $kit->preco_fixo : '') }}" min="0" max="999999.99" step="0.01" placeholder="deixe vazio para usar o desconto"
                       class="mt-1 w-full border border-gray-300 rounded text-sm px-3 py-2 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                <span class="text-[11px] text-gray-400 mt-1 block">Se preenchido, o kit cobra este valor por alvo (rateado entre as fontes) e o desconto (%) é ignorado.</span>
            </label>

            <div>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Quem vê e paga por este kit</span>
                @php($_publico = old('publico', $kit->publico ?? 'todos'))
                <div class="mt-2 flex flex-col gap-1.5">
                    <label class="flex items-center gap-2 text-[13px] text-gray-800 cursor-pointer">
                        <input type="radio" name="publico" value="todos" @checked($_publico === 'todos') class="h-4 w-4 border-gray-300 text-gray-800 focus:ring-gray-500" onchange="document.getElementById('kit-usuarios').classList.add('hidden')">
                        <span>Todos os usuários</span>
                    </label>
                    <label class="flex items-center gap-2 text-[13px] text-gray-800 cursor-pointer">
                        <input type="radio" name="publico" value="selecionados" @checked($_publico === 'selecionados') class="h-4 w-4 border-gray-300 text-gray-800 focus:ring-gray-500" onchange="document.getElementById('kit-usuarios').classList.remove('hidden')">
                        <span>Somente usuários selecionados</span>
                    </label>
                </div>
                @php($_usuariosSel = array_map('intval', (array) old('usuarios', $usuariosSelecionados ?? [])))
                <div id="kit-usuarios" class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-1.5 {{ $_publico === 'selecionados' ? '' : 'hidden' }}">
                    @foreach($usuarios as $u)
                        <label class="flex items-center gap-2 rounded border border-gray-200 px-2.5 py-1.5 text-[13px] text-gray-800 cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="usuarios[]" value="{{ $u->id }}" @checked(in_array((int) $u->id, $_usuariosSel, true))
                                   class="h-4 w-4 rounded border-gray-300 text-gray-800 focus:ring-gray-500">
                            <span class="min-w-0 flex-1 truncate">{{ $u->name }}<span class="text-[11px] text-gray-400"> · {{ $u->email }}</span></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <label class="block">
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Descrição (opcional)</span>
                <input type="text" name="descricao" value="{{ old('descricao', $kit->descricao ?? '') }}" maxlength="255"
                       class="mt-1 w-full border border-gray-300 rounded text-sm px-3 py-2 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
            </label>

            <div>
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Fontes do kit</span>
                @php($_fontesKit = old('fontes', (array) ($kit->fontes ?? [])))
                <div class="mt-2 space-y-3">
                    @foreach($gruposFontes as $grupo)
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1.5">{{ $grupo['label'] }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                @foreach($grupo['fontes'] as $fonte)
                                    <label class="flex items-center gap-2 rounded border border-gray-200 px-2.5 py-1.5 text-[13px] text-gray-800 cursor-pointer hover:bg-gray-50">
                                        <input type="checkbox" name="fontes[]" value="{{ $fonte['chave'] }}" @checked(in_array($fonte['chave'], $_fontesKit, true))
                                               class="h-4 w-4 rounded border-gray-300 text-gray-800 focus:ring-gray-500">
                                        <span class="min-w-0 flex-1 truncate">{{ $fonte['nome'] }}</span>
                                        <span class="text-[11px] text-gray-400 flex-shrink-0">{{ \App\Support\Dinheiro::brl($fonte['preco']) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <label class="block">
                    <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Ordem</span>
                    <input type="number" name="ordem" value="{{ old('ordem', $kit->ordem ?? 0) }}" min="0" required
                           class="mt-1 w-full border border-gray-300 rounded text-sm px-3 py-2 focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                </label>
                <label class="flex items-end gap-2 pb-2">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $kit->ativo ?? true))
                           class="h-4 w-4 rounded border-gray-300 text-gray-800 focus:ring-gray-500">
                    <span class="text-sm text-gray-800">Kit ativo (visível na tela de consulta)</span>
                </label>
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                <a href="/app/admin/kits" data-link class="text-sm text-gray-500 underline hover:text-gray-800">Cancelar</a>
                <button type="submit" class="inline-flex items-center justify-center rounded bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700">Salvar kit</button>
            </div>
        </form>

        @unless($_novo)
            <form method="POST" action="/app/admin/kits/{{ $kit->id }}/excluir" class="mt-4 text-right"
                  onsubmit="return confirm('Excluir o kit “{{ $kit->nome }}”? A tela de consulta deixa de exibi-lo na hora.');">
                @csrf
                <button type="submit" class="text-[12px] font-medium underline" style="color:#b91c1c">Excluir kit</button>
            </form>
        @endunless
    </div>
</div>
