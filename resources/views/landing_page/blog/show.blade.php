<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            {{-- Artigo --}}
            <article class="lg:col-span-8">
                <div class="mb-8">
                    <a href="/blog" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium text-sm mb-6">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar ao Blog
                    </a>
                    <div class="flex items-center gap-3 mb-4">
                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">{{ $post['categoria'] }}</span>
                        <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($post['data'])->format('d/m/Y') }}</span>
                        <span class="text-sm text-gray-500">{{ $post['tempo_leitura'] }} de leitura</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 leading-tight">{{ $post['title'] }}</h1>
                </div>

                <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                    @include($post['view'])
                </div>

                {{-- CTA ao final do artigo --}}
                <div class="mt-12 bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-8 text-white">
                    <h3 class="text-2xl font-bold mb-3">Proteja seus clientes contra riscos fiscais</h3>
                    <p class="text-white/80 mb-6">O FiscalDock automatiza o monitoramento de participantes, importacao de SPED e deteccao de riscos. Teste gratuitamente.</p>
                    <a href="/agendar" class="btn-cta inline-flex items-center">
                        Testar Gratuitamente
                        <svg class="h-5 w-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>
            </article>

            {{-- Sidebar --}}
            <aside class="lg:col-span-4">
                <div class="sticky top-24">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Outros artigos</h3>
                    <div class="space-y-6">
                        @foreach($otherPosts as $otherPost)
                        <a href="/blog/{{ $otherPost['slug'] }}" class="group block">
                            <div class="bg-white rounded-lg border border-gray-200 p-4 hover:border-blue-300 hover:shadow-sm transition-all">
                                <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-semibold rounded mb-2">{{ $otherPost['categoria'] }}</span>
                                <h4 class="text-sm font-bold text-gray-900 group-hover:text-blue-600 transition-colors leading-snug">{{ $otherPost['title'] }}</h4>
                                <p class="text-xs text-gray-500 mt-1">{{ $otherPost['tempo_leitura'] }} de leitura</p>
                            </div>
                        </a>
                        @endforeach
                    </div>

                    {{-- Mini CTA sidebar --}}
                    <div class="mt-8 bg-gray-50 rounded-xl border border-gray-200 p-6">
                        <h4 class="text-base font-bold text-gray-900 mb-2">Quer ver na pratica?</h4>
                        <p class="text-sm text-gray-600 mb-4">Agende uma demonstracao gratuita do FiscalDock.</p>
                        <a href="/agendar" class="btn-cta btn-cta--block text-sm">Agendar demonstracao</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

{{-- JSON-LD BlogPosting --}}
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BlogPosting",
    "headline": "{{ $post['title'] }}",
    "description": "{{ $post['meta_description'] }}",
    "datePublished": "{{ $post['data'] }}",
    "author": {
        "@@type": "Organization",
        "name": "FiscalDock"
    },
    "publisher": {
        "@@type": "Organization",
        "name": "FiscalDock",
        "logo": {
            "@@type": "ImageObject",
            "url": "{{ asset('binary_files/logo/Logo FiscalDock.png') }}"
        }
    }
}
</script>
