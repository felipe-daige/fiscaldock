@php
    $fmtN = fn ($v) => number_format((float) $v, 0, ',', '.');
    $quando = fn ($ts) => $ts ? \Carbon\Carbon::createFromTimestamp((int) $ts)->format('d/m/Y H:i') : '—';
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Usuários</h1>
            <p class="text-xs text-gray-500 mt-0.5">Quem são os usuários e sua atividade (derivada). Somente leitura.</p>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'usuarios'])

        <form method="GET" class="bg-white rounded border border-gray-300 p-3 mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-3">
                <label class="block text-[11px] text-gray-500 mb-1">Buscar</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="nome, e-mail, empresa ou CNPJ" class="w-full text-[13px] py-2.5 px-3 border border-gray-300 rounded">
            </div>
            <div class="flex items-end">
                <button type="submit" class="text-[13px] py-2.5 px-4 rounded text-white font-semibold" style="background-color:#1d4ed8">Filtrar</button>
            </div>
        </form>

        <div class="bg-white rounded border border-gray-300 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="text-left px-3 py-2.5">Usuário</th>
                        <th class="text-left px-3 py-2.5">Empresa</th>
                        <th class="text-left px-3 py-2.5">Plano</th>
                        <th class="text-right px-3 py-2.5">Créditos</th>
                        <th class="text-left px-3 py-2.5">Criado</th>
                        <th class="text-left px-3 py-2.5">Última atividade</th>
                        <th class="text-right px-3 py-2.5">Consultas</th>
                        <th class="text-right px-3 py-2.5">Importações</th>
                        <th class="text-left px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($usuarios as $u)
                    @php
                        $plano = $u->plano_nome ?: (($u->trial_used && $u->trial_expires_at && \Carbon\Carbon::parse($u->trial_expires_at)->isFuture()) ? 'Trial' : 'Gratuito');
                    @endphp
                    <tr>
                        <td class="px-3 py-2">
                            <a href="/app/admin/usuarios/{{ $u->id }}" data-link class="text-blue-600 underline cursor-pointer">{{ $u->name }} {{ $u->sobrenome }}</a>
                            <div class="text-[11px] text-gray-400">{{ $u->email }}</div>
                        </td>
                        <td class="px-3 py-2 text-gray-700">{{ $u->empresa ?: '—' }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $plano }}</td>
                        <td class="px-3 py-2 text-right text-gray-900 font-semibold">{{ $fmtN($u->credits) }}</td>
                        <td class="px-3 py-2 text-[12px] text-gray-500">{{ \Carbon\Carbon::parse($u->created_at)->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-[12px] text-gray-500">{{ $quando($u->ultima_atividade_ts) }}</td>
                        <td class="px-3 py-2 text-right text-gray-700">{{ $fmtN($u->qtd_consultas) }}</td>
                        <td class="px-3 py-2 text-right text-gray-700">{{ $fmtN($u->qtd_importacoes) }}</td>
                        <td class="px-3 py-2">
                            @if($u->is_admin)<span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color:#334155">admin</span>@endif
                            @if($u->deletion_requested_at)<span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase text-white" style="background-color:#dc2626">exclusão</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-3 py-6 text-center text-gray-400 text-sm">Nenhum usuário.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $usuarios->withQueryString()->links() }}</div>
    </div>
</div>
