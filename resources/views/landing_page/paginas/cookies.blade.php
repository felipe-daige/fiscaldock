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
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">1. O que são cookies</p>
                    <p>Cookies são pequenos arquivos de texto gravados no seu dispositivo quando você visita um site. Eles permitem que páginas funcionem corretamente, lembrem suas preferências e, em alguns casos, ajudem a medir o uso ou exibir conteúdos relevantes.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">2. Categorias que utilizamos</p>
                    <ul class="space-y-2 list-disc list-inside">
                        <li><strong>Necessários</strong> — sempre ativos. Garantem operação básica do site: sessão autenticada, proteção contra CSRF e o registro da sua própria escolha sobre cookies.</li>
                        <li><strong>Funcionais</strong> — armazenam preferências de uso (por exemplo, opções de layout). Atualmente <strong>não utilizamos</strong> cookies funcionais.</li>
                        <li><strong>Análise</strong> — medem como o site é utilizado para que a FiscalDock melhore a experiência. Atualmente <strong>nenhum</strong> cookie está ativo nessa categoria.</li>
                        <li><strong>Marketing</strong> — personalizam comunicações e medem campanhas. Atualmente <strong>nenhum</strong> cookie está ativo nessa categoria.</li>
                    </ul>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">3. Cookies em uso hoje</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px] border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="bg-gray-50 px-3 py-2 text-left font-semibold text-gray-700 border-b border-gray-200">Nome</th>
                                    <th class="bg-gray-50 px-3 py-2 text-left font-semibold text-gray-700 border-b border-gray-200">Propósito</th>
                                    <th class="bg-gray-50 px-3 py-2 text-left font-semibold text-gray-700 border-b border-gray-200">Duração</th>
                                    <th class="bg-gray-50 px-3 py-2 text-left font-semibold text-gray-700 border-b border-gray-200">Categoria</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="px-3 py-2 border-b border-gray-100 align-top"><code>fiscaldock_session</code></td>
                                    <td class="px-3 py-2 border-b border-gray-100 align-top">Mantém a sessão autenticada do Laravel.</td>
                                    <td class="px-3 py-2 border-b border-gray-100 align-top">Sessão</td>
                                    <td class="px-3 py-2 border-b border-gray-100 align-top">Necessário</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 border-b border-gray-100 align-top"><code>XSRF-TOKEN</code></td>
                                    <td class="px-3 py-2 border-b border-gray-100 align-top">Proteção contra CSRF em formulários.</td>
                                    <td class="px-3 py-2 border-b border-gray-100 align-top">Sessão</td>
                                    <td class="px-3 py-2 border-b border-gray-100 align-top">Necessário</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 align-top"><code>fd-cookies-consent</code></td>
                                    <td class="px-3 py-2 align-top">Armazena a sua escolha de consentimento.</td>
                                    <td class="px-3 py-2 align-top">12 meses</td>
                                    <td class="px-3 py-2 align-top">Necessário</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">4. Como gerenciar suas preferências</p>
                    <p>Use o botão abaixo para reabrir o painel de preferências e ajustar quais categorias opcionais aceita. Também é possível bloquear ou apagar cookies pelas configurações do seu navegador — algumas funcionalidades podem ser impactadas.</p>
                    <button type="button" data-action="open-cookie-settings"
                            class="mt-3 inline-flex items-center justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Configurar cookies
                    </button>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">5. Cookies de terceiros</p>
                    <p>Atualmente a FiscalDock não utiliza cookies de terceiros. Caso isso mude (por exemplo, com a adoção de ferramentas de análise ou marketing), esta página será atualizada e o seu consentimento será solicitado antes de qualquer cookie opcional ser ativado.</p>
                </div>

                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">6. Contato e atualizações</p>
                    <p>Dúvidas sobre cookies podem ser enviadas para <a href="mailto:contato@fiscaldock.com.br" class="hover:underline" style="color: #1e4fa0">contato@fiscaldock.com.br</a>. Última atualização: {{ now()->format('d/m/Y') }}.</p>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-3">Continuar navegação</p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('privacidade') }}" class="bg-gray-800 text-white hover:bg-gray-700 rounded text-sm font-medium px-4 py-3 text-center">
                            Ler Política de Privacidade
                        </a>
                        <a href="{{ route('inicio') }}" class="bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 rounded text-sm font-medium px-4 py-3 text-center">
                            Voltar ao início
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
