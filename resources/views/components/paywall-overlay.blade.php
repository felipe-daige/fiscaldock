@props(['titulo' => 'Recurso do BI completo', 'descricao' => null, 'itens' => []])

@php
    $itens = $itens ?: [
        'Análises avançadas calculadas sobre o que você já importou',
        'Alertas e divergências apontados automaticamente',
        'Exportação em planilha nos planos com Excel/CSV',
    ];
@endphp

{{-- Paywall: skeleton borrado + card explicativo. O conteúdo real NÃO é renderizado
     (controller nem computa) — o skeleton só dá contexto visual do que existe ali.
     Segurança de verdade fica nos endpoints (middleware :bi_completo → 403). --}}
<div class="relative overflow-hidden rounded" style="min-height: 60vh; max-height: 70vh">
    <div class="pointer-events-none select-none p-4 sm:p-6" style="filter: blur(7px)" aria-hidden="true">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
            @for($i = 0; $i < 4; $i++)
                <div class="bg-white rounded border border-gray-300 p-4">
                    <div class="h-3 w-20 rounded bg-gray-200 mb-3"></div>
                    <div class="h-6 w-28 rounded bg-gray-300"></div>
                </div>
            @endfor
        </div>
        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-6">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <div class="h-3 w-40 rounded bg-gray-200"></div>
            </div>
            <div class="p-4 space-y-3">
                @for($i = 0; $i < 6; $i++)
                    <div class="flex items-center gap-4">
                        <div class="h-4 rounded bg-gray-200" style="width: {{ 30 + ($i * 17) % 45 }}%"></div>
                        <div class="h-4 w-16 rounded bg-gray-300 ml-auto"></div>
                    </div>
                @endfor
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white rounded border border-gray-300 p-4"><div class="h-40 rounded bg-gray-100"></div></div>
            <div class="bg-white rounded border border-gray-300 p-4"><div class="h-40 rounded bg-gray-100"></div></div>
        </div>
    </div>

    <div class="absolute inset-0 z-10 flex items-center justify-center p-4" style="background: rgba(243, 244, 246, 0.45); backdrop-filter: blur(2px)">
        <div class="bg-white rounded-lg border border-gray-300 shadow-2xl max-w-md w-full overflow-hidden">
            <div class="h-1.5 w-full" style="background: linear-gradient(90deg, #0b1f3a, #1d4ed8)"></div>
            <div class="p-6 sm:p-7">
                <div class="flex items-start gap-4 mb-4">
                    <div class="shrink-0 w-11 h-11 rounded-lg flex items-center justify-center" style="background-color: #eef2ff">
                        <svg class="w-5 h-5" style="color: #0b1f3a" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="inline-block mb-1 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest text-white" style="background-color: #0b1f3a">BI completo</span>
                        <h2 class="text-base font-bold text-gray-900 leading-snug">{{ $titulo }}</h2>
                    </div>
                </div>

                <p class="text-[13px] text-gray-600 leading-relaxed mb-4">{{ $descricao ?? 'Esta análise faz parte do BI completo, disponível nos planos pagos. Seus dados importados continuam intactos — ao assinar, a tela abre na hora com tudo calculado.' }}</p>

                <ul class="space-y-2 mb-5">
                    @foreach($itens as $item)
                        <li class="flex items-start gap-2 text-[12px] text-gray-700">
                            <svg class="w-4 h-4 shrink-0 mt-px" style="color: #047857" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>

                <a href="/app/planos" data-link class="block w-full text-center px-5 py-3 rounded text-[12px] font-bold uppercase tracking-wide text-white hover:opacity-90 transition-opacity mb-2" style="background-color: #0b1f3a">Conhecer os planos</a>
                <a href="/app/bi/dashboard" data-link class="block w-full text-center px-4 py-2 rounded text-[12px] font-semibold text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors">Voltar ao BI Fiscal</a>
            </div>
        </div>
    </div>
</div>
