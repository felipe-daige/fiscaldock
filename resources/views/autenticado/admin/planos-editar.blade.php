@php
    $caps = $plano->capabilities ?? [];
    $capBool = fn ($k) => (bool) ($caps[$k] ?? false);
    $exportAtual = is_array($caps['export'] ?? null) ? $caps['export'] : [];
    $lblInput = 'block text-[11px] text-gray-500 mb-1';
    $inputCls = 'w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded';
    $precos = app(\App\Services\PricingCatalogService::class);
    $reais = fn ($centavos) => number_format(((int) $centavos) / 100, 2, '.', '');
    $creditosEmReais = \App\Support\Dinheiro::brl($precos->creditsToCurrency((int) $plano->creditos_inclusos));
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Editar plano — {{ $plano->nome }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Código: <code>{{ $plano->codigo }}</code></p>
            </div>
            <a href="{{ route('app.admin.planos.index') }}" data-link class="text-[12px] text-gray-500 hover:text-gray-800">← Voltar</a>
        </div>

        @if($errors->any())
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color: #dc2626">
                <ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('app.admin.planos.update', $plano->id) }}" class="space-y-4">
            @csrf

            {{-- Identidade e comercial --}}
            <div class="bg-white rounded border border-gray-300 p-4 space-y-3">
                <h2 class="text-sm font-bold text-gray-900">Comercial</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $lblInput }}">Nome</label>
                        <input type="text" name="nome" value="{{ old('nome', $plano->nome) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Ordem de exibição</label>
                        <input type="number" name="ordem" value="{{ old('ordem', $plano->ordem) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Preço mensal (R$)</label>
                        <input type="number" step="0.01" min="0" name="preco_mensal_reais" value="{{ old('preco_mensal_reais', $reais($plano->preco_mensal_centavos)) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Preço anual (R$)</label>
                        <input type="number" step="0.01" min="0" name="preco_anual_reais" value="{{ old('preco_anual_reais', $reais($plano->preco_anual_centavos)) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Créditos inclusos / mês <span class="text-gray-400">≈ {{ $creditosEmReais }}</span></label>
                        <input type="number" min="0" name="creditos_inclusos" value="{{ old('creditos_inclusos', $plano->creditos_inclusos) }}" class="{{ $inputCls }}">
                        <p class="text-[10px] text-gray-400 mt-1">Quantidade de créditos (unidade interna). Valor em R$ = créditos × preço unitário do catálogo.</p>
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Faixa comercial (slug)</label>
                        <input type="text" name="faixa_slug" value="{{ old('faixa_slug', $plano->faixa_slug) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Rollover cap (×mensal)</label>
                        <input type="number" step="0.01" min="0" name="rollover_cap_multiplicador" value="{{ old('rollover_cap_multiplicador', $plano->rollover_cap_multiplicador) }}" class="{{ $inputCls }}">
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="inline-flex items-center gap-2 text-[13px] text-gray-700">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plano->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300">
                            Plano ativo (visível/assinável)
                        </label>
                    </div>
                </div>
            </div>

            {{-- Limites --}}
            <div class="bg-white rounded border border-gray-300 p-4 space-y-3">
                <h2 class="text-sm font-bold text-gray-900">Limites</h2>
                <p class="text-[11px] text-gray-400 -mt-1">Deixe em branco para <strong>ilimitado</strong>.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $lblInput }}">Limite de clientes</label>
                        <input type="number" min="0" name="limite_clientes" value="{{ old('limite_clientes', $plano->limite_clientes) }}" placeholder="Ilimitado" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Limite de CNPJs monitorados</label>
                        <input type="number" min="0" name="limite_cnpjs_monitorados" value="{{ old('limite_cnpjs_monitorados', $plano->limite_cnpjs_monitorados) }}" placeholder="Ilimitado" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Frequência padrão (dias)</label>
                        <input type="number" min="1" name="frequencia_padrao_dias" value="{{ old('frequencia_padrao_dias', $plano->frequencia_padrao_dias) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Assentos inclusos</label>
                        <input type="number" min="1" name="assentos_inclusos" value="{{ old('assentos_inclusos', $plano->assentos_inclusos) }}" class="{{ $inputCls }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $lblInput }}">Profundidade do auto-monitor</label>
                        <select name="profundidade_auto_monitor" class="{{ $inputCls }}">
                            @foreach($profundidades as $prof)
                                <option value="{{ $prof }}" {{ old('profundidade_auto_monitor', $plano->profundidade_auto_monitor) === $prof ? 'selected' : '' }}>{{ $prof }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Capabilities --}}
            <div class="bg-white rounded border border-gray-300 p-4 space-y-3">
                <h2 class="text-sm font-bold text-gray-900">Capabilities (permissões)</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $lblInput }}">BI</label>
                        <select name="cap_bi" class="{{ $inputCls }}">
                            @foreach(['basico' => 'Básico', 'completo' => 'Completo'] as $v => $lbl)
                                <option value="{{ $v }}" {{ old('cap_bi', $caps['bi'] ?? 'basico') === $v ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Frequência mínima do monitor (dias)</label>
                        <input type="number" min="1" name="cap_frequencia_minima_dias" value="{{ old('cap_frequencia_minima_dias', $caps['frequencia_minima_dias'] ?? 30) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Retenção de dados (meses)</label>
                        <input type="number" min="1" name="cap_retencao_meses" value="{{ old('cap_retencao_meses', $caps['retencao_meses'] ?? null) }}" placeholder="Ilimitado" class="{{ $inputCls }}">
                    </div>
                </div>

                <div>
                    <label class="{{ $lblInput }}">Formatos de export</label>
                    <div class="flex flex-wrap gap-4 mt-1">
                        @foreach($exportFormats as $fmt)
                            <label class="inline-flex items-center gap-2 text-[13px] text-gray-700">
                                <input type="checkbox" name="cap_export[]" value="{{ $fmt }}" {{ in_array($fmt, old('cap_export', $exportAtual), true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300">
                                {{ strtoupper($fmt) }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                    @foreach([
                        'cap_pdf_executivo' => ['pdf_executivo', 'PDF executivo'],
                        'cap_clearance_lote' => ['clearance_lote', 'Clearance em lote'],
                        'cap_clearance_full' => ['clearance_full', 'Clearance completo'],
                        'cap_score_historico' => ['score_historico', 'Histórico de score'],
                    ] as $field => [$capKey, $label])
                        <label class="inline-flex items-center gap-2 text-[13px] text-gray-700">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1" {{ old($field, $capBool($capKey)) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Mercado Pago --}}
            <div class="bg-white rounded border border-gray-300 p-4 space-y-3">
                <h2 class="text-sm font-bold text-gray-900">Mercado Pago</h2>
                <p class="text-[11px] text-gray-400 -mt-1">IDs do <code>preapproval_plan</code> por ciclo. Trocar o preço acima <strong>não</strong> altera o valor cobrado — atualize o plano no MP e cole o novo ID aqui.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $lblInput }}">preapproval_plan_id (mensal)</label>
                        <input type="text" name="mp_preapproval_plan_id_mensal" value="{{ old('mp_preapproval_plan_id_mensal', $plano->mp_preapproval_plan_id_mensal) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">preapproval_plan_id (anual)</label>
                        <input type="text" name="mp_preapproval_plan_id_anual" value="{{ old('mp_preapproval_plan_id_anual', $plano->mp_preapproval_plan_id_anual) }}" class="{{ $inputCls }}">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('app.admin.planos.index') }}" data-link class="px-4 py-2.5 rounded text-[12px] font-bold uppercase tracking-wide text-white" style="background-color: #6b7280">Cancelar</a>
                <button type="submit" class="px-5 py-2.5 rounded text-[12px] font-bold uppercase tracking-wide text-white hover:opacity-90" style="background-color: #0b1f3a">Salvar plano</button>
            </div>
        </form>

    </div>
</div>
