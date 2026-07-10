@php($_tab = $tab ?? '')
@php($_vencidas = \App\Models\AdminPendencia::vencidas()->count())
@php($_integProblemas = \App\Models\IntegracaoStatus::problemasCount())
<nav class="admin-subnav mb-5 -mx-4 px-4 sm:mx-0 sm:px-0 border-b border-gray-300 flex gap-1 text-[13px] overflow-x-auto overscroll-x-contain" aria-label="Navegação administrativa">
    @foreach([
        'visao' => ['Visão Geral', '/app/admin'],
        'usuarios' => ['Usuários', '/app/admin/usuarios'],
        'comercial' => ['Comercial', '/app/admin/comercial'],
        'planos' => ['Planos', '/app/admin/planos'],
        'auditoria' => ['Auditoria', '/app/admin/auditoria'],
        'pendencias' => ['Pendências', '/app/admin/pendencias'],
        'integracoes' => ['Integrações', '/app/admin/integracoes'],
    ] as $key => [$label, $href])
        @php($_badge = match($key) { 'pendencias' => $_vencidas, 'integracoes' => $_integProblemas, default => 0 })
        <a href="{{ $href }}" data-link
           @if($_tab === $key) aria-current="page" @endif
           class="shrink-0 whitespace-nowrap min-h-11 px-3 py-2 -mb-px border-b-2 inline-flex items-center {{ $_tab === $key ? 'border-gray-800 text-gray-900 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-800' }}">{{ $label }}@if($_badge > 0)<span class="ml-1 inline-flex items-center justify-center text-white text-[10px] font-bold rounded-full px-1.5 py-0.5 align-middle" style="background-color:#dc2626">{{ $_badge }}</span>@endif</a>
    @endforeach
</nav>
