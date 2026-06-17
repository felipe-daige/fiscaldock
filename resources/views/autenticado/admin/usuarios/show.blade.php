@php
    $fmtR = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $fmtN = fn ($v) => number_format((float) $v, 0, ',', '.');
    $quando = fn ($ts) => $ts ? \Carbon\Carbon::createFromTimestamp((int) $ts)->format('d/m/Y H:i') : '—';
    $tipoLabel = ['consulta' => 'Consulta', 'importacao_efd' => 'Importação EFD', 'importacao_xml' => 'Importação XML', 'credito' => 'Crédito', 'pagamento' => 'Pagamento'];
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4">
            <a href="/app/admin/usuarios" data-link class="text-[12px] text-blue-600">← Voltar</a>
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 mt-1">{{ $usuario->name }} {{ $usuario->sobrenome }}</h1>
            <p class="text-xs text-gray-500">{{ $usuario->email }} · {{ $usuario->empresa ?: 'sem empresa' }} · CNPJ {{ $usuario->cnpj ?: '—' }}</p>
        </div>

        {{-- Perfil --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
            @foreach([
                ['Créditos', $fmtN($usuario->credits), '#1d4ed8'],
                ['Consultas', $fmtN($kpis['qtd_consultas']), '#1d4ed8'],
                ['Importações', $fmtN($kpis['qtd_importacoes']), '#1d4ed8'],
                ['Créditos consumidos', $fmtN($kpis['creditos_consumidos']), '#b45309'],
                ['Total pago', $fmtR($kpis['total_pago']), '#047857'],
                ['Plano', $assinatura->plano_nome ?? (($usuario->trial_used && $usuario->trial_expires_at && \Carbon\Carbon::parse($usuario->trial_expires_at)->isFuture()) ? 'Trial' : 'Gratuito'), '#334155'],
                ['Criado em', \Carbon\Carbon::parse($usuario->created_at)->format('d/m/Y'), '#334155'],
                ['Última sessão', $sessao ? $quando($sessao->last_activity) : '—', '#334155'],
            ] as [$label, $valor, $cor])
                <div class="bg-white rounded border border-gray-300 border-l-4 p-3" style="border-left-color: {{ $cor }}">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $label }}</p>
                    <p class="text-base font-bold text-gray-900">{{ $valor }}</p>
                </div>
            @endforeach
        </div>

        {{-- Perfil de cadastro (coletado no signup) --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="px-4 py-2.5 border-b border-gray-200"><p class="text-sm font-semibold text-gray-800">Perfil de cadastro</p></div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 p-4 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Cargo</dt><dd class="text-gray-900 text-right">{{ $usuario->cargo ?: '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Faturamento anual</dt><dd class="text-gray-900 text-right">{{ config('cadastro.faturamento.'.$usuario->faturamento_anual, $usuario->faturamento_anual ?: '—') }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Desafio principal</dt><dd class="text-gray-900 text-right">{{ config('cadastro.desafios.'.$usuario->desafio_principal, $usuario->desafio_principal ?: '—') }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Desafio secundário</dt><dd class="text-gray-900 text-right">{{ config('cadastro.desafios.'.$usuario->desafio_secundario, $usuario->desafio_secundario ?: '—') }}</dd></div>
            </dl>
        </div>

        @if($usuario->deletion_requested_at)
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color:#dc2626">
                Exclusão de conta solicitada em {{ \Carbon\Carbon::parse($usuario->deletion_requested_at)->format('d/m/Y') }} (LGPD).
            </div>
        @endif

        {{-- Timeline --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="px-4 py-2.5 border-b border-gray-200"><p class="text-sm font-semibold text-gray-800">Atividade recente</p></div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                @forelse($timeline as $ev)
                    <tr>
                        <td class="px-3 py-2 w-40 text-[12px] text-gray-500">{{ $ev['data'] ? \Carbon\Carbon::parse($ev['data'])->format('d/m/Y H:i') : '—' }}</td>
                        <td class="px-3 py-2 w-32"><span class="text-[11px] font-semibold text-gray-600">{{ $tipoLabel[$ev['tipo']] ?? $ev['tipo'] }}</span></td>
                        <td class="px-3 py-2 text-gray-800">{{ $ev['titulo'] }}</td>
                        <td class="px-3 py-2 text-[12px] text-gray-500">{{ $ev['detalhe'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-6 text-center text-gray-400 text-sm">Sem atividade registrada.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
