@php
    $fmtVal = function ($tipo, $v) {
        if ($v === null) return '—';
        return $tipo === 'float' ? 'R$ '.number_format((float) $v, 2, ',', '.') : number_format((int) $v, 0, ',', '.');
    };
    $inputVal = function ($tipo, $v) {
        if ($v === null) return '';
        return $tipo === 'float' ? number_format((float) $v, 2, '.', '') : (string) (int) $v;
    };
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Parâmetros comerciais</h1>
            <p class="text-xs text-gray-500 mt-0.5">Valores em reais. Cada parâmetro usa o valor abaixo; sem customização, vale o padrão do sistema. Ao salvar, a cobrança e as telas de preço passam a usar o novo valor.</p>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'comercial'])

        @if(session('status'))
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color: #047857">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color: #dc2626">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="space-y-3">
            @foreach($parametros as $chave => $p)
                @php $customizado = $p['override'] !== null; @endphp
                <div class="bg-white rounded border border-gray-300 overflow-hidden"
                     @if($customizado) style="border-left: 3px solid #b45309" @endif>
                    <div class="p-4 flex flex-col sm:flex-row sm:items-end gap-3 sm:gap-4">

                        {{-- Identidade do parâmetro --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h2 class="text-sm font-bold text-gray-900">{{ $p['rotulo'] }}</h2>
                                @if($customizado)
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #b45309">Customizado</span>
                                @else
                                    <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-gray-600" style="background-color: #e5e7eb">Padrão</span>
                                @endif
                            </div>
                            @if(!empty($p['dica']))
                                <p class="text-[11px] text-gray-500 mt-1">{{ $p['dica'] }}</p>
                            @endif
                            <p class="text-[11px] text-gray-400 mt-1">
                                Padrão do sistema: <span class="font-semibold text-gray-500">{{ $fmtVal($p['tipo'], $p['default']) }}</span>
                            </p>
                        </div>

                        {{-- Controles: input em uso + salvar + resetar --}}
                        <form method="POST" action="{{ route('app.admin.comercial.update', $chave) }}"
                              class="flex items-end gap-2 sm:shrink-0">
                            @csrf
                            <div class="w-32">
                                <label class="block text-[10px] text-gray-500 uppercase tracking-wide mb-1">Valor em uso{{ $p['tipo'] === 'float' ? ' (R$)' : '' }}</label>
                                <input type="number" step="{{ $p['tipo'] === 'float' ? '0.01' : '1' }}" min="0" name="valor"
                                       value="{{ $inputVal($p['tipo'], $p['efetivo']) }}"
                                       class="w-full text-[13px] py-2 px-3 border border-gray-300 rounded text-right font-mono">
                            </div>
                            <button type="submit" class="px-4 py-2 rounded text-[12px] font-bold uppercase tracking-wide text-white hover:opacity-90" style="background-color: #0b1f3a">Salvar</button>
                        </form>

                        @if($customizado)
                            <form method="POST" action="{{ route('app.admin.comercial.reset', $chave) }}" class="sm:shrink-0"
                                  onsubmit="return confirm('Voltar este parâmetro ao padrão do sistema?');">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto px-3 py-2 rounded text-[12px] font-bold uppercase tracking-wide text-gray-600 border border-gray-300 hover:bg-gray-50" title="Voltar ao padrão do sistema">Resetar</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
