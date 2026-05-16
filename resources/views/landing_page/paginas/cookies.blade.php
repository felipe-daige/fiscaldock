@push('structured-data')
    @include('landing_page.partials.breadcrumb-schema', [
        'trail' => [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Política de Cookies', 'url' => url('/cookies')],
        ],
    ])
@endpush

<section class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">FiscalDock</p>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide mt-1">Política de Cookies</h1>
                <p class="text-xs text-gray-500 mt-1">Quais cookies usamos, suas finalidades e como gerenciar suas preferências.</p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] uppercase tracking-wide text-gray-500">
                    <a href="{{ route('inicio') }}" class="hover:underline" style="color: #1e4fa0">Início</a>
                    <span>/</span>
                    <span>Política de Cookies</span>
                </div>
            </div>

            <div class="p-4 sm:p-6 space-y-6 text-sm text-gray-700 leading-relaxed">
                {{-- Conteúdo completo na Task 2 --}}
                <p>Em breve: conteúdo da Política de Cookies da FiscalDock.</p>
            </div>
        </div>
    </div>
</section>
