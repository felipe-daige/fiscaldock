@php
    $nomeCompleto = trim(($usuario->name ?? '').' '.($usuario->sobrenome ?? '')) ?: 'Usuário #'.$usuario->id;
    $podeExcluir = auth()->id() !== $usuario->id && ! $usuario->is_admin && ! $usuario->anonimizado_em;
    $trialAtivo = $usuario->trial_used && $usuario->trial_expires_at && \Carbon\Carbon::parse($usuario->trial_expires_at)->isFuture();
@endphp
<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">

        <nav class="text-[11px] text-gray-400 mb-3">
            <a href="{{ route('app.admin.usuarios.index') }}" data-link class="hover:text-gray-700">Usuários</a>
            <span class="mx-1">/</span>
            <span class="text-gray-600">{{ $nomeCompleto }}</span>
        </nav>

        @include('autenticado.admin.partials.nav', ['tab' => 'usuarios'])

        {{-- Cabeçalho com estado --}}
        <div class="bg-white rounded border border-gray-300 border-t-2 mb-4 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" style="border-top-color:#0b1f3a">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg font-bold text-gray-900 truncate">{{ $nomeCompleto }}</h1>
                    @if($usuario->is_admin)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#475569">admin</span>
                    @endif
                    @if($usuario->bloqueado_em)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#b91c1c">bloqueado</span>
                    @endif
                    @if($trialAtivo)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#1e4679">trial</span>
                    @endif
                    @if($usuario->deletion_requested_at)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#dc2626">exclusão pedida</span>
                    @endif
                    @if($usuario->anonimizado_em)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color:#6b7280">anonimizado</span>
                    @endif
                </div>
                <p class="text-[11px] text-gray-400 mt-0.5">{{ $usuario->email }} · ID <code class="text-gray-600">#{{ $usuario->id }}</code></p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('app.admin.usuarios.show', $usuario->id) }}" data-link class="admin-action w-full sm:w-auto inline-flex items-center justify-center px-3 py-2 rounded border border-gray-300 text-[12px] font-semibold text-gray-700 bg-white hover:bg-gray-50 whitespace-nowrap">Detalhe completo</a>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color:#dc2626">
                <ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @include('autenticado.admin.usuarios._form')

        @include('autenticado.admin.usuarios._delete_zone', [
            'deleteUsuario' => $usuario,
            'deletePodeExcluir' => $podeExcluir,
            'deleteClasses' => 'mt-5',
        ])
    </div>
</div>
