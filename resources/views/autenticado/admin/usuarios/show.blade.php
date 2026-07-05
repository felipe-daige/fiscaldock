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
                ['Saldo', $fmtR(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) $usuario->credits)), '#1d4ed8'],
                ['Consultas', $fmtN($kpis['qtd_consultas']), '#1d4ed8'],
                ['Importações', $fmtN($kpis['qtd_importacoes']), '#1d4ed8'],
                ['Custo total', $fmtR(app(\App\Services\PricingCatalogService::class)->creditsToCurrency((int) $kpis['creditos_consumidos'])), '#b45309'],
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

        @php($ehEu = auth()->id() === $usuario->id)
        {{-- Ações administrativas --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="px-4 py-2.5 border-b border-gray-200"><p class="text-sm font-semibold text-gray-800">Ações administrativas</p></div>
            <div class="p-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Crédito --}}
                <form method="POST" action="/app/admin/usuarios/{{ $usuario->id }}/creditar" class="space-y-2">
                    @csrf
                    <label class="block text-[11px] text-gray-500">Ajuste de crédito (negativo debita)</label>
                    <input type="number" step="1" name="valor" placeholder="ex.: 50 ou -20" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                    <input type="text" name="motivo" placeholder="Motivo (obrigatório)" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                    <button class="text-white text-[12px] font-semibold px-3 py-2 rounded" style="background-color:#1d4ed8">Aplicar ajuste</button>
                </form>
                {{-- Bloqueio + admin + impersonar --}}
                <div class="space-y-3">
                    <form method="POST" action="/app/admin/usuarios/{{ $usuario->id }}/bloquear" class="flex gap-2 items-center">
                        @csrf
                        <input type="text" name="motivo" placeholder="Motivo" class="flex-1 text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                        <button @disabled($ehEu) class="text-white text-[12px] font-semibold px-3 py-2 rounded disabled:opacity-40" style="background-color:{{ $usuario->bloqueado_em ? '#047857' : '#b91c1c' }}">{{ $usuario->bloqueado_em ? 'Desbloquear' : 'Bloquear' }}</button>
                    </form>
                    <form method="POST" action="/app/admin/usuarios/{{ $usuario->id }}/admin" class="flex gap-2 items-center">
                        @csrf
                        <input type="text" name="motivo" placeholder="Motivo" class="flex-1 text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                        <button @disabled($ehEu) class="text-white text-[12px] font-semibold px-3 py-2 rounded disabled:opacity-40" style="background-color:#334155">{{ $usuario->is_admin ? 'Remover admin' : 'Tornar admin' }}</button>
                    </form>
                    @unless($usuario->is_admin || $ehEu)
                    <form method="POST" action="/app/admin/usuarios/{{ $usuario->id }}/impersonar" class="flex gap-2 items-center">
                        @csrf
                        <input type="text" name="motivo" placeholder="Motivo" class="flex-1 text-[13px] py-2.5 px-3 border border-gray-300 rounded" required>
                        <button class="text-white text-[12px] font-semibold px-3 py-2 rounded" style="background-color:#7c3aed">Impersonar (leitura)</button>
                    </form>
                    @endunless
                </div>
            </div>
            @error('valor')<p class="px-4 pb-3 text-[12px] text-red-600">{{ $message }}</p>@enderror
            @error('motivo')<p class="px-4 pb-3 text-[12px] text-red-600">{{ $message }}</p>@enderror
            @if(session('status'))<p class="px-4 pb-3 text-[12px] text-emerald-700">{{ session('status') }}</p>@endif
        </div>

        {{-- Trilha administrativa --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
            <div class="px-4 py-2.5 border-b border-gray-200"><p class="text-sm font-semibold text-gray-800">Trilha administrativa</p></div>
            <table class="w-full text-sm tabela-cards"><tbody class="divide-y divide-gray-100">
            @forelse($trilhaAdmin as $log)
                <tr>
                    <td class="px-3 py-2 w-40 text-[12px] text-gray-500">{{ optional($log->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="px-3 py-2 w-32 text-[11px] font-semibold text-gray-600" data-label="Ação">{{ $log->acao }}</td>
                    <td class="px-3 py-2 text-[12px] text-gray-700" data-label="Motivo">{{ $log->motivo }} <span class="text-gray-400">· {{ $log->admin->name ?? '—' }}</span></td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-3 py-6 text-center text-gray-400">Sem ações administrativas.</td></tr>
            @endforelse
            </tbody></table>
        </div>

        {{-- Timeline --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="px-4 py-2.5 border-b border-gray-200"><p class="text-sm font-semibold text-gray-800">Atividade recente</p></div>
            <table class="w-full text-sm tabela-cards">
                <tbody class="divide-y divide-gray-100">
                @forelse($timeline as $ev)
                    <tr>
                        <td class="px-3 py-2 w-40 text-[12px] text-gray-500">{{ $ev['data'] ? \Carbon\Carbon::parse($ev['data'])->format('d/m/Y H:i') : '—' }}</td>
                        <td class="px-3 py-2 w-32" data-label="Tipo"><span class="text-[11px] font-semibold text-gray-600">{{ $tipoLabel[$ev['tipo']] ?? $ev['tipo'] }}</span></td>
                        <td class="px-3 py-2 text-gray-800" data-label="Título">{{ $ev['titulo'] }}</td>
                        <td class="px-3 py-2 text-[12px] text-gray-500" data-label="Detalhe">{{ $ev['detalhe'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-6 text-center text-gray-400 text-sm">Sem atividade registrada.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
