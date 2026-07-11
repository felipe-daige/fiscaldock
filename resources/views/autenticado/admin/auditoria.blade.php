<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Auditoria</h1>
            <p class="text-xs text-gray-500 mt-0.5">Trilha global das ações executadas por operadores FiscalDock.</p>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'auditoria'])
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <table class="w-full text-sm tabela-cards">
                <thead class="bg-gray-50 text-[10px] uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-3 py-2.5 text-left">Data</th>
                        <th class="px-3 py-2.5 text-left">Operador</th>
                        <th class="px-3 py-2.5 text-left">Alvo</th>
                        <th class="px-3 py-2.5 text-left">Ação</th>
                        <th class="px-3 py-2.5 text-left">Motivo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    <tr>
                        <td class="px-3 py-2 text-[12px] text-gray-500" data-label="Data">{{ optional($log->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 text-gray-900" data-label="Operador">{{ $log->admin->name ?? '—' }}</td>
                        <td class="px-3 py-2 text-gray-700" data-label="Alvo">{{ $log->alvo->name ?? '—' }}</td>
                        <td class="px-3 py-2" data-label="Ação">
                            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#334155">{{ $log->acao }}</span>
                        </td>
                        <td class="px-3 py-2 text-[12px] text-gray-600" data-label="Motivo">{{ $log->motivo }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-gray-400 text-sm">Sem ações registradas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</div>
