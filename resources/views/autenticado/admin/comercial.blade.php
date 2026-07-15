@php
    $fmtVal = function ($tipo, $valor) {
        if ($valor === null) {
            return '—';
        }

        return $tipo === 'float'
            ? 'R$ '.number_format((float) $valor, 2, ',', '.')
            : number_format((int) $valor, 0, ',', '.');
    };

    $inputVal = function ($tipo, $valor) {
        if ($valor === null) {
            return '';
        }

        return $tipo === 'float'
            ? number_format((float) $valor, 2, '.', '')
            : (string) (int) $valor;
    };

    $totalParametros = count($parametros);
    $totalCustomizados = collect($parametros)->filter(fn ($parametro) => $parametro['override'] !== null)->count();
@endphp

<div class="min-h-screen bg-gray-100">
    <div class="admin-page w-full max-w-none px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Parâmetros comerciais</h1>
            <p class="text-xs text-gray-500 mt-0.5">Valores em reais. Sem customização, vale o padrão do sistema. Alterações passam a valer imediatamente nas cobranças e telas de preço.</p>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'comercial'])

        @if(session('status'))
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-4 text-sm text-gray-700" style="border-left-color: #047857">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-4 text-sm text-gray-700" style="border-left-color: #dc2626">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="bg-white rounded border border-gray-300 overflow-hidden" aria-labelledby="parametros-comerciais-heading">
            <div class="bg-gray-50 px-4 sm:px-6 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 id="parametros-comerciais-heading" class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Parâmetros globais</h2>
                    <p class="text-[11px] text-gray-500 mt-1">Compare o padrão do sistema com o valor efetivamente aplicado.</p>
                </div>
                <div class="flex items-center gap-2 text-[10px] font-semibold uppercase tracking-wide text-gray-500">
                    <span class="px-2 py-1 rounded" style="background-color: #e5e7eb">{{ $totalParametros }} parâmetros</span>
                    @if($totalCustomizados > 0)
                        <span class="px-2 py-1 rounded text-white" style="background-color: #b45309">{{ $totalCustomizados }} customizado{{ $totalCustomizados === 1 ? '' : 's' }}</span>
                    @endif
                </div>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach($parametros as $chave => $p)
                    @php($customizado = $p['override'] !== null)
                    <article class="px-4 sm:px-6 py-4 sm:py-5" @if($customizado) style="border-left: 3px solid #b45309" @endif>
                        <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(10rem,13rem)_minmax(20rem,28rem)] xl:items-end">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-gray-900">{{ $p['rotulo'] }}</h3>
                                    @if($customizado)
                                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #b45309">Customizado</span>
                                    @else
                                        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-gray-600" style="background-color: #e5e7eb">Padrão</span>
                                    @endif
                                </div>
                                @if(!empty($p['dica']))
                                    <p class="text-xs text-gray-500 mt-1 max-w-3xl">{{ $p['dica'] }}</p>
                                @endif
                                <p class="text-[10px] text-gray-400 font-mono mt-2">{{ $chave }}</p>
                            </div>

                            <div>
                                <span class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Padrão do sistema</span>
                                <div class="auth-control w-full px-3 border border-gray-200 rounded bg-gray-50 flex items-center justify-end text-sm font-semibold text-gray-600 font-mono">
                                    {{ $fmtVal($p['tipo'], $p['default']) }}
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-end gap-2">
                                <form method="POST" action="{{ route('app.admin.comercial.update', $chave) }}" class="flex-1 flex flex-col sm:flex-row sm:items-end gap-2">
                                    @csrf
                                    <div class="flex-1 min-w-0">
                                        <label for="comercial-{{ $chave }}" class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Valor em uso{{ $p['tipo'] === 'float' ? ' (R$)' : '' }}</label>
                                        <input id="comercial-{{ $chave }}" type="number" step="{{ $p['tipo'] === 'float' ? '0.01' : '1' }}" min="0" name="valor"
                                               value="{{ $inputVal($p['tipo'], $p['efetivo']) }}"
                                               class="auth-control w-full text-sm px-3 border border-gray-300 rounded text-right font-mono focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                                    </div>
                                    <button type="submit" class="auth-control w-full sm:w-auto px-5 rounded text-[12px] font-bold uppercase tracking-wide text-white hover:opacity-90" style="background-color: #1f2937">Salvar</button>
                                </form>

                                @if($customizado)
                                    <form method="POST" action="{{ route('app.admin.comercial.reset', $chave) }}" class="sm:shrink-0"
                                          onsubmit="return confirm('Voltar este parâmetro ao padrão do sistema?');">
                                        @csrf
                                        <button type="submit" class="auth-control w-full sm:w-auto px-4 rounded text-[12px] font-bold uppercase tracking-wide text-gray-600 border border-gray-300 bg-white hover:bg-gray-50" title="Voltar ao padrão do sistema">Resetar</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</div>
