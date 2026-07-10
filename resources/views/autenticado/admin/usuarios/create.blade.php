<div class="min-h-screen bg-gray-100">
    <div class="admin-page max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="mb-4 flex flex-col min-[380px]:flex-row min-[380px]:items-start min-[380px]:justify-between gap-2">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Admin — Novo usuário</h1>
                <p class="text-xs text-gray-500 mt-0.5">Criação manual de conta com auditoria administrativa.</p>
            </div>
            <a href="{{ route('app.admin.usuarios.index') }}" data-link class="admin-action inline-flex items-center text-[12px] text-gray-500 hover:text-gray-800">← Voltar</a>
        </div>

        @include('autenticado.admin.partials.nav', ['tab' => 'usuarios'])

        @if($errors->any())
            <div class="bg-white rounded border border-gray-300 border-l-4 mb-4 p-3 text-sm text-gray-700" style="border-left-color:#dc2626">
                <ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @include('autenticado.admin.usuarios._form')
    </div>
</div>
