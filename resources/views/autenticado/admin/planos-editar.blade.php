@php
    $caps = $plano->capabilities ?? [];
    $capBool = fn ($k) => (bool) ($caps[$k] ?? false);
    $exportAtual = is_array($caps['export'] ?? null) ? $caps['export'] : [];
    $lblInput = 'block text-[11px] font-medium text-gray-500 mb-1';
    $inputCls = 'w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded focus:border-gray-800 focus:ring-0 focus:outline-none';
    $precos = app(\App\Services\PricingCatalogService::class);
    $reais = fn ($centavos) => number_format(((int) $centavos) / 100, 2, '.', '');
    $saldoInclusoBrl = \App\Support\Dinheiro::brl($precos->creditsToCurrency((int) $plano->creditos_inclusos));
    // Marca d'água = plano sem NENHUM formato de export pago (regra do RequiresEntitlement/composer).
    $temExportPago = ! empty($exportAtual);
    $armazenamentoAtual = array_key_exists('armazenamento_mb', $caps)
        ? $caps['armazenamento_mb']
        : config("arquivos.quota_por_plano_mb.{$plano->codigo}", config('arquivos.quota_padrao_mb', 250));
@endphp
<div class="min-h-screen bg-gray-100 pb-24">
    <div class="admin-page max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        {{-- Breadcrumb --}}
        <nav class="text-[11px] text-gray-400 mb-3">
            <a href="{{ route('app.admin.planos.index') }}" data-link class="hover:text-gray-700">Planos</a>
            <span class="mx-1">/</span>
            <span class="text-gray-600">{{ $plano->nome }}</span>
        </nav>

        {{-- Cabeçalho com resumo do estado atual --}}
        <div class="bg-white rounded border border-gray-300 border-t-2 mb-4 overflow-hidden" style="border-top-color: #0b1f3a">
            <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-lg font-bold text-gray-900">{{ $plano->nome }}</h1>
                        @if($plano->is_active)
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #047857">Ativo</span>
                        @else
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #6b7280">Inativo</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-0.5">código <code class="text-gray-600">{{ $plano->codigo }}</code></p>
                </div>
                {{-- Chips de resumo (estado salvo) --}}
                <div class="flex items-center gap-2 flex-wrap text-[11px]">
                    <span class="whitespace-nowrap px-2 py-1 rounded bg-gray-50 border border-gray-200 text-gray-700">{{ $plano->preco_mensal_centavos > 0 ? 'R$ '.$reais($plano->preco_mensal_centavos).'/mês' : 'sob consulta' }}</span>
                    <span class="whitespace-nowrap px-2 py-1 rounded bg-gray-50 border border-gray-200 text-gray-700">{{ $saldoInclusoBrl }}/mês incluso</span>
                    @if($temExportPago)
                        <span class="whitespace-nowrap px-2 py-1 rounded text-white" style="background-color: #1e4679">export: {{ strtoupper(implode(' · ', $exportAtual)) }}</span>
                    @else
                        <span class="whitespace-nowrap px-2 py-1 rounded text-white" style="background-color: #b45309">PDF com marca d'água</span>
                    @endif
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color: #dc2626">
                <ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('app.admin.planos.update', $plano->id) }}" class="space-y-4" id="form-plano">
            @csrf

            {{-- 1. Comercial --}}
            <div class="bg-white rounded border border-gray-300 border-l-2 overflow-hidden" style="border-left-color: #0b1f3a">
                <div class="px-4 py-2.5 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-[12px] font-bold text-gray-900 uppercase tracking-wide"><span class="text-gray-400">1.</span> Comercial</h2>
                    <p class="text-[10px] text-gray-400">Preço, saldo incluso e visibilidade do plano.</p>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $lblInput }}">Nome</label>
                        <input type="text" name="nome" value="{{ old('nome', $plano->nome) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Ordem de exibição</label>
                        <input type="number" name="ordem" value="{{ old('ordem', $plano->ordem) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Preço mensal</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[13px] text-gray-400">R$</span>
                            <input type="number" step="0.01" min="0" name="preco_mensal_reais" value="{{ old('preco_mensal_reais', $reais($plano->preco_mensal_centavos)) }}" class="{{ $inputCls }} pl-9">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Preço anual</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[13px] text-gray-400">R$</span>
                            <input type="number" step="0.01" min="0" name="preco_anual_reais" value="{{ old('preco_anual_reais', $reais($plano->preco_anual_centavos)) }}" class="{{ $inputCls }} pl-9">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Saldo incluso / mês</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[13px] text-gray-400">R$</span>
                            <input type="number" step="0.01" min="0" name="saldo_incluso_reais" value="{{ old('saldo_incluso_reais', number_format($precos->creditsToCurrency((int) $plano->creditos_inclusos), 2, '.', '')) }}" class="{{ $inputCls }} pl-9">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Concedido a cada renovação; acumula até o rollover cap.</p>
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Faixa comercial (slug)</label>
                        <input type="text" name="faixa_slug" value="{{ old('faixa_slug', $plano->faixa_slug) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Rollover cap (×mensal)</label>
                        <input type="number" step="0.01" min="0" name="rollover_cap_multiplicador" value="{{ old('rollover_cap_multiplicador', $plano->rollover_cap_multiplicador) }}" class="{{ $inputCls }}">
                        <p class="text-[10px] text-gray-400 mt-1">Quanto do incluso pode acumular. 1 = banca 1 mês.</p>
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="inline-flex items-center gap-2 text-[13px] text-gray-700 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plano->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300">
                            Plano ativo (visível/assinável)
                        </label>
                    </div>
                </div>
            </div>

            {{-- 2. Limites --}}
            <div class="bg-white rounded border border-gray-300 border-l-2 overflow-hidden" style="border-left-color: #1e4679">
                <div class="px-4 py-2.5 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-[12px] font-bold text-gray-900 uppercase tracking-wide"><span class="text-gray-400">2.</span> Limites</h2>
                    <p class="text-[10px] text-gray-400">Campos numéricos em branco = <strong>ilimitado</strong>.</p>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $lblInput }}">Limite de clientes</label>
                        <input type="number" min="0" name="limite_clientes" value="{{ old('limite_clientes', $plano->limite_clientes) }}" placeholder="Ilimitado" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Limite de CNPJs monitorados</label>
                        <input type="number" min="0" name="limite_cnpjs_monitorados" value="{{ old('limite_cnpjs_monitorados', $plano->limite_cnpjs_monitorados) }}" placeholder="Ilimitado" class="{{ $inputCls }}">
                        <p class="text-[10px] text-gray-400 mt-1">Baixar isto força reconciliação (usuário escolhe quais manter).</p>
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Armazenamento incluído (MB)</label>
                        <input type="number" min="1" name="cap_armazenamento_mb" value="{{ old('cap_armazenamento_mb', $armazenamentoAtual) }}" placeholder="Ilimitado" class="{{ $inputCls }}">
                        <p class="text-[10px] text-gray-400 mt-1">Quota da central Meus Arquivos. 1024 MB = 1 GB.</p>
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Frequência padrão (dias)</label>
                        <input type="number" min="1" name="frequencia_padrao_dias" value="{{ old('frequencia_padrao_dias', $plano->frequencia_padrao_dias) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Assentos inclusos</label>
                        <input type="number" min="1" name="assentos_inclusos" value="{{ old('assentos_inclusos', $plano->assentos_inclusos) }}" class="{{ $inputCls }}">
                    </div>
                    <div>
                        <label class="{{ $lblInput }}">Preço de referência do assento extra / mês</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[13px] text-gray-400">R$</span>
                            <input type="number" step="0.01" min="0" name="preco_assento_extra_reais" value="{{ old('preco_assento_extra_reais', $reais($plano->preco_assento_extra_centavos)) }}" class="{{ $inputCls }} pl-9">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Referência para contratação assistida; não altera a recorrência sozinho.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $lblInput }}">Profundidade do auto-monitor</label>
                        <select name="profundidade_auto_monitor" class="{{ $inputCls }}">
                            @foreach($profundidades as $prof)
                                <option value="{{ $prof }}" {{ old('profundidade_auto_monitor', $plano->profundidade_auto_monitor) === $prof ? 'selected' : '' }}>{{ $prof }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1">Ranking: cadastral &lt; validação &lt; licitação &lt; compliance &lt; due diligence.</p>
                    </div>
                </div>
            </div>

            {{-- 3. Capabilities --}}
            <div class="bg-white rounded border border-gray-300 border-l-2 overflow-hidden" style="border-left-color: #047857">
                <div class="px-4 py-2.5 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-[12px] font-bold text-gray-900 uppercase tracking-wide"><span class="text-gray-400">3.</span> Capabilities (permissões)</h2>
                    <p class="text-[10px] text-gray-400">O que este plano libera. Lido pelo EntitlementService em toda a aplicação.</p>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="{{ $lblInput }}">BI</label>
                            <select name="cap_bi" class="{{ $inputCls }}">
                                @foreach(['basico' => 'Básico', 'completo' => 'Completo'] as $v => $lbl)
                                    <option value="{{ $v }}" {{ old('cap_bi', $caps['bi'] ?? 'basico') === $v ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $lblInput }}">Freq. mínima do monitor (dias)</label>
                            <input type="number" min="1" name="cap_frequencia_minima_dias" value="{{ old('cap_frequencia_minima_dias', $caps['frequencia_minima_dias'] ?? 30) }}" class="{{ $inputCls }}">
                        </div>
                        <div>
                            <label class="{{ $lblInput }}">Retenção de dados (meses)</label>
                            <input type="number" min="1" name="cap_retencao_meses" value="{{ old('cap_retencao_meses', $caps['retencao_meses'] ?? null) }}" placeholder="Ilimitado" class="{{ $inputCls }}">
                        </div>
                    </div>

                    {{-- Formatos de export + aviso de marca d'água --}}
                    <div class="border border-gray-200 rounded p-3 bg-gray-50">
                        <label class="{{ $lblInput }}">Formatos de exportação</label>
                        <div class="flex flex-wrap gap-4 mt-1">
                            @foreach($exportFormats as $fmt)
                                <label class="inline-flex items-center gap-2 text-[13px] text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="cap_export[]" value="{{ $fmt }}" data-export-fmt {{ in_array($fmt, old('cap_export', $exportAtual), true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300">
                                    {{ strtoupper($fmt) }}
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[10px] mt-2" id="hint-marca" style="color: #b45309">
                            PDF é sempre liberado. <strong>Sem nenhum formato marcado</strong>, o PDF sai com <strong>marca d'água</strong> (plano gratuito). Marque ≥1 formato para PDF limpo.
                        </p>
                    </div>

                    {{-- Flags booleanas --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach([
                            'cap_pdf_executivo' => ['pdf_executivo', 'PDF executivo', 'Header rico no relatório'],
                            'cap_clearance_lote' => ['clearance_lote', 'Clearance em lote', 'Validar notas em massa'],
                            'cap_clearance_full' => ['clearance_full', 'Clearance completo', 'Tributos item-a-item (cert. A1)'],
                            'cap_score_historico' => ['score_historico', 'Histórico de score', 'Série temporal do risco'],
                        ] as $field => [$capKey, $label, $hint])
                            <label class="flex items-start gap-2 text-[13px] text-gray-700 border border-gray-200 rounded px-3 py-2 cursor-pointer hover:bg-gray-50">
                                <input type="hidden" name="{{ $field }}" value="0">
                                <input type="checkbox" name="{{ $field }}" value="1" {{ old($field, $capBool($capKey)) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 mt-0.5">
                                <span>
                                    <span class="block font-medium">{{ $label }}</span>
                                    <span class="block text-[10px] text-gray-400">{{ $hint }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 4. Mercado Pago --}}
            <div class="bg-white rounded border border-gray-300 border-l-2 overflow-hidden" style="border-left-color: #6b7280">
                <div class="px-4 py-2.5 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-[12px] font-bold text-gray-900 uppercase tracking-wide"><span class="text-gray-400">4.</span> Mercado Pago</h2>
                    <p class="text-[10px] text-gray-400">IDs do <code>preapproval_plan</code> por ciclo.</p>
                </div>
                <div class="p-4">
                    <div class="bg-white border border-gray-200 border-l-4 rounded p-2.5 mb-3 text-[11px] text-gray-600" style="border-left-color: #b45309">
                        Trocar o preço acima <strong>não</strong> altera o valor cobrado — a cobrança segue o <code>preapproval_plan</code> do MP. Atualize o plano no painel do Mercado Pago e cole o novo ID aqui.
                    </div>
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
            </div>
        </form>
    </div>

    {{-- Barra de ação fixa: salvar sempre alcançável num form longo --}}
    <div class="admin-fixed-actions fixed bottom-0 left-0 right-0 bg-white border-t border-gray-300 z-30">
        <div class="admin-page max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-2">
            <a href="{{ route('app.admin.planos.index') }}" data-link class="hidden sm:inline-flex text-[12px] text-gray-500 hover:text-gray-800">← Voltar sem salvar</a>
            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('app.admin.planos.index') }}" data-link class="admin-action inline-flex items-center justify-center px-3 sm:px-4 py-2.5 rounded text-[12px] font-bold uppercase tracking-wide text-white" style="background-color: #6b7280">Cancelar</a>
                <button type="submit" form="form-plano" class="w-full px-3 sm:px-5 py-2.5 rounded text-[12px] font-bold uppercase tracking-wide text-white hover:opacity-90" style="background-color: #0b1f3a">Salvar plano</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    // Aviso de marca d'água reage aos formatos marcados (espelha a regra do backend).
    var boxes = Array.prototype.slice.call(document.querySelectorAll('[data-export-fmt]'));
    var hint = document.getElementById('hint-marca');
    function atualizar() {
        var algum = boxes.some(function (b) { return b.checked; });
        if (!hint) return;
        if (algum) {
            hint.style.color = '#047857';
            hint.innerHTML = 'PDF <strong>limpo</strong> (sem marca d\'água) — este plano tem export pago.';
        } else {
            hint.style.color = '#b45309';
            hint.innerHTML = 'PDF é sempre liberado. <strong>Sem nenhum formato marcado</strong>, o PDF sai com <strong>marca d\'água</strong> (plano gratuito). Marque ≥1 formato para PDF limpo.';
        }
    }
    boxes.forEach(function (b) { b.addEventListener('change', atualizar); });
    atualizar();
})();
</script>
