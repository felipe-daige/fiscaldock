<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Blog FiscalDock</h1>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Artigos sobre compliance fiscal, SPED, riscos tributarios e boas praticas para escritorios contabeis.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
            <a href="/blog/{{ $post['slug'] }}" class="group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg hover:border-blue-300 transition-all duration-300">
                <div class="h-48 bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center p-6">
                    <div class="text-center">
                        <span class="inline-block px-3 py-1 bg-white/20 text-white text-xs font-semibold rounded-full mb-3">{{ $post['categoria'] }}</span>
                        <div class="text-white/80 text-sm">{{ $post['tempo_leitura'] }} de leitura</div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="text-sm text-gray-500 mb-2">{{ \Carbon\Carbon::parse($post['data'])->format('d/m/Y') }}</div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors">{{ $post['title'] }}</h2>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ $post['excerpt'] }}</p>
                    <div class="mt-4 inline-flex items-center gap-1 text-blue-600 font-semibold text-sm group-hover:gap-2 transition-all">
                        Ler artigo
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
