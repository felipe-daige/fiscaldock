@php
    $percentual = (float) ($resumoArquivos['percentual'] ?? 0);
    $quotaIlimitada = ($resumoArquivos['quota_bytes'] ?? null) === null;
    $corUso = $percentual >= 95 ? '#dc2626' : ($percentual >= 80 ? '#b45309' : '#1e4679');
    $accept = collect($extensoesArquivos)->map(fn ($ext) => '.'.$ext)->implode(',');
    $badgeOrigem = [
        'upload' => ['Upload', '#1e4679'],
        'comprovante' => ['Sistema', '#047857'],
        'importacao' => ['Importação', '#7c3aed'],
    ];
@endphp

<div class="min-h-screen bg-gray-100" id="arquivos-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-5">

        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Meus Arquivos</h1>
                <p class="text-xs text-gray-500 mt-1">Envie documentos e acesse os comprovantes preservados pela sua conta.</p>
            </div>
            <span class="inline-flex self-start items-center px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #0b1f3a">
                Plano {{ $planoArquivos->nome ?? 'Free' }}
            </span>
        </div>

        @if(session('success'))
            <div class="rounded border px-3 py-2.5 text-[12px]" style="background-color: #ecfdf5; border-color: #a7f3d0; color: #047857">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded border px-3 py-2.5 text-[12px]" style="background-color: #fef2f2; border-color: #fecaca; color: #b91c1c">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="rounded border px-3 py-2.5 text-[12px]" style="background-color: #fef2f2; border-color: #fecaca; color: #b91c1c">
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Uso e quota --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="px-4 py-4 sm:px-5 sm:py-5">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Armazenamento da conta</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{ $resumoArquivos['usado_formatado'] }}
                            <span class="text-sm font-normal text-gray-400">de {{ $resumoArquivos['quota_formatada'] }}</span>
                        </p>
                        <p class="text-[11px] text-gray-500 mt-1">
                            @if($quotaIlimitada)
                                Seu plano não possui limite de armazenamento.
                            @else
                                {{ $resumoArquivos['disponivel_formatado'] }} disponíveis para novos uploads e importações.
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('app.planos') }}" data-link class="inline-flex self-start items-center justify-center px-3 py-2 rounded border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        Ver opções de espaço
                    </a>
                </div>

                @unless($quotaIlimitada)
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-[10px] text-gray-500 mb-1.5">
                            <span>Uso atual</span>
                            <span class="font-semibold" style="color: {{ $corUso }}">{{ number_format($percentual, 1, ',', '.') }}%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden" role="progressbar" aria-valuenow="{{ $percentual }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-full rounded-full" style="width: {{ $percentual }}%; background-color: {{ $corUso }}"></div>
                        </div>
                    </div>
                @endunless
            </div>

            <div class="grid grid-cols-4 border-t border-gray-200 divide-x divide-gray-200">
                <div class="px-3 py-3 sm:px-5">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide">Total</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">{{ number_format($resumoArquivos['total_arquivos'], 0, ',', '.') }}</p>
                </div>
                <div class="px-3 py-3 sm:px-5">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide">Uploads</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">{{ number_format($resumoArquivos['total_uploads'], 0, ',', '.') }}</p>
                </div>
                <div class="px-3 py-3 sm:px-5">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide">Comprovantes</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">{{ number_format($resumoArquivos['total_comprovantes'], 0, ',', '.') }}</p>
                </div>
                <div class="px-3 py-3 sm:px-5">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide">Importados</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">{{ number_format($resumoArquivos['total_importados'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Upload compacto --}}
        <div class="bg-white rounded border border-gray-300 overflow-hidden">
            <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Enviar arquivos</span>
            </div>
            <form action="{{ route('app.arquivos.store') }}" method="POST" enctype="multipart/form-data" class="p-3 sm:px-4 flex flex-col sm:flex-row sm:items-center gap-3">
                @csrf
                <label class="flex-1 flex items-center gap-3 rounded border border-dashed border-gray-300 px-3 py-2 cursor-pointer hover:border-gray-400 hover:bg-gray-50 transition-colors min-w-0">
                    <svg class="w-5 h-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 16V4m0 0L8 8m4-4l4 4M4 15v4a1 1 0 001 1h14a1 1 0 001-1v-4"></path>
                    </svg>
                    <div class="min-w-0 flex-1">
                        <input type="file" name="arquivos[]" multiple required accept="{{ $accept }}" class="block w-full text-[11px] text-gray-500 file:mr-3 file:rounded file:border-0 file:px-3 file:py-1 file:text-[11px] file:font-semibold file:text-gray-700 file:bg-gray-100">
                        <span class="block text-[10px] text-gray-400 mt-0.5 truncate">até {{ $uploadMaximoMb }} MB cada · máx. {{ $uploadMaximoPorLote }} por envio · PDF, XML, TXT, CSV, XLS, XLSX, ZIP, JPG, PNG</span>
                    </div>
                </label>
                <button type="submit" class="shrink-0 px-4 py-2 rounded text-xs font-semibold text-white hover:opacity-90" style="background-color: #1e4679">
                    Enviar para o FiscalDock
                </button>
            </form>
        </div>

        {{-- Listagem --}}
        <div class="space-y-3">
                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <form method="GET" action="{{ route('app.arquivos.index') }}" class="p-3 sm:p-4 flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"></path>
                            </svg>
                            <input type="search" name="q" value="{{ $buscaArquivos }}" placeholder="Buscar por nome ou formato" class="w-full rounded border border-gray-300 py-2 pl-9 pr-3 text-xs focus:border-gray-700 focus:ring-0">
                        </div>
                        <select name="origem" class="rounded border border-gray-300 px-3 py-2 text-xs text-gray-700 focus:border-gray-700 focus:ring-0">
                            <option value="todos" @selected($origemArquivos === 'todos')>Todos os arquivos</option>
                            <option value="upload" @selected($origemArquivos === 'upload')>Enviados por mim</option>
                            <option value="comprovante" @selected($origemArquivos === 'comprovante')>Comprovantes</option>
                            <option value="importacao" @selected($origemArquivos === 'importacao')>Importados (EFD/XML)</option>
                        </select>
                        <button type="submit" class="rounded px-4 py-2 text-xs font-semibold text-white" style="background-color: #374151">Filtrar</button>
                    </form>
                </div>

                <div class="bg-white rounded border border-gray-300 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200 flex items-center justify-between gap-3">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Arquivos armazenados</span>
                        <span class="text-[10px] text-gray-400">{{ number_format($arquivos->total(), 0, ',', '.') }} resultado(s)</span>
                    </div>

                    @if($arquivos->isEmpty())
                        <div class="px-5 py-12 text-center">
                            <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path>
                            </svg>
                            <p class="text-sm font-semibold text-gray-700 mt-3">Nenhum arquivo encontrado</p>
                            <p class="text-[11px] text-gray-500 mt-1">Envie um documento ou ajuste os filtros da busca.</p>
                        </div>
                    @else
                        {{-- Desktop --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full table-fixed">
                                <colgroup>
                                    <col style="width: 45%">
                                    <col style="width: 20%">
                                    <col style="width: 12%">
                                    <col style="width: 15%">
                                    <col style="width: 8%">
                                </colgroup>
                                <thead>
                                    <tr class="border-b border-gray-200 bg-white">
                                        <th class="px-4 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Arquivo</th>
                                        <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Origem</th>
                                        <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Tamanho</th>
                                        <th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Data</th>
                                        <th class="px-3 py-2 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($arquivos as $arquivo)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="w-9 h-9 shrink-0 rounded border border-gray-200 bg-gray-50 flex items-center justify-center text-[9px] font-bold text-gray-600">{{ $arquivo['extensao'] }}</span>
                                                    <div class="min-w-0 flex-1">
                                                        @if($arquivo['previewavel'])
                                                            <button type="button"
                                                                data-preview-url="{{ route('app.arquivos.preview', $arquivo['id']) }}"
                                                                data-preview-nome="{{ $arquivo['nome'] }}"
                                                                data-preview-download="{{ route('app.arquivos.download', $arquivo['id']) }}"
                                                                onclick="(function(b){var m=document.getElementById('arquivo-preview-modal');if(!m)return;document.getElementById('arquivo-preview-titulo').textContent=b.dataset.previewNome;document.getElementById('arquivo-preview-baixar').setAttribute('href',b.dataset.previewDownload);document.getElementById('arquivo-preview-abrir').setAttribute('href',b.dataset.previewUrl);document.getElementById('arquivo-preview-frame').src=b.dataset.previewUrl;m.classList.remove('hidden');document.body.classList.add('overflow-hidden')})(this)"
                                                                class="block w-full max-w-full text-left group" title="Visualizar {{ $arquivo['nome'] }}">
                                                                <span class="block text-xs font-medium text-gray-900 truncate group-hover:underline" style="color: #1e4679">{{ $arquivo['nome'] }}</span>
                                                            </button>
                                                        @else
                                                            <p class="text-xs font-medium text-gray-900 truncate" title="{{ $arquivo['nome'] }}">{{ $arquivo['nome'] }}</p>
                                                        @endif
                                                        @if($arquivo['dono_documento'])
                                                            <p class="text-[10px] text-gray-500 truncate" title="CNPJ do documento">
                                                                <span class="font-semibold">{{ $arquivo['dono_documento'] }}</span>@if($arquivo['dono_nome']) · {{ $arquivo['dono_nome'] }}@endif
                                                            </p>
                                                        @else
                                                            <p class="text-[10px] text-gray-400 truncate">{{ $arquivo['mime_type'] }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-3">
                                                <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" title="{{ $arquivo['origem_label'] }}" style="background-color: {{ $badgeOrigem[$arquivo['origem']][1] ?? '#047857' }}">
                                                    {{ $badgeOrigem[$arquivo['origem']][0] ?? 'Sistema' }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-3 text-[11px] text-gray-600">{{ $arquivo['tamanho_formatado'] }}</td>
                                            <td class="px-3 py-3 text-[11px] text-gray-600">{{ $arquivo['modificado_em']->format('d/m/Y H:i') }}</td>
                                            <td class="px-3 py-3">
                                                <div class="flex items-center justify-end gap-1">
                                                    @if($arquivo['historico_url'])
                                                        <a href="{{ $arquivo['historico_url'] }}" data-link class="p-1.5 rounded text-gray-500 hover:text-blue-700 hover:bg-gray-100" title="Abrir consulta de origem" aria-label="Abrir consulta de origem de {{ $arquivo['nome'] }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        </a>
                                                    @endif
                                                    @if($arquivo['baixavel'] ?? true)
                                                        <a href="{{ route('app.arquivos.download', $arquivo['id']) }}" class="p-1.5 rounded text-gray-500 hover:text-blue-700 hover:bg-gray-100" title="Baixar arquivo" aria-label="Baixar {{ $arquivo['nome'] }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M5 20h14"></path></svg>
                                                        </a>
                                                    @else
                                                        <span class="p-1.5 text-gray-300" title="Original não retido — as notas extraídas estão no seu acervo">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M5 20h14M4 4l16 16"></path></svg>
                                                        </span>
                                                    @endif
                                                    @if($arquivo['pode_excluir'])
                                                        <form action="{{ route('app.arquivos.destroy', $arquivo['id']) }}" method="POST" onsubmit="return confirm('Excluir este arquivo permanentemente?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 rounded text-gray-500 hover:text-red-700 hover:bg-gray-100" title="Excluir arquivo" aria-label="Excluir {{ $arquivo['nome'] }}">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-10 0l1 12h6l1-12m-6 0V4h4v3"></path></svg>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="p-1.5 text-gray-300" title="{{ $arquivo['origem'] === 'importacao' ? 'Exclusão pela tela de importações' : 'Comprovante protegido' }}">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V8a4 4 0 018 0v3m-9 0h10v9H7v-9z"></path></svg>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile --}}
                        <div class="md:hidden divide-y divide-gray-100">
                            @foreach($arquivos as $arquivo)
                                <div class="p-4">
                                    <div class="flex items-start gap-3">
                                        <span class="w-9 h-9 shrink-0 rounded border border-gray-200 bg-gray-50 flex items-center justify-center text-[9px] font-bold text-gray-600">{{ $arquivo['extensao'] }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold text-gray-900 break-words">{{ $arquivo['nome'] }}</p>
                                            @if($arquivo['dono_documento'])
                                                <p class="text-[10px] text-gray-500 mt-0.5 break-words">
                                                    <span class="font-semibold">{{ $arquivo['dono_documento'] }}</span>@if($arquivo['dono_nome']) · {{ $arquivo['dono_nome'] }}@endif
                                                </p>
                                            @endif
                                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                                <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $badgeOrigem[$arquivo['origem']][1] ?? '#047857' }}">{{ $badgeOrigem[$arquivo['origem']][0] ?? 'Sistema' }}</span>
                                                <span class="text-[10px] text-gray-500">{{ $arquivo['tamanho_formatado'] }}</span>
                                                <span class="text-[10px] text-gray-400">{{ $arquivo['modificado_em']->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 mt-3">
                                        @if($arquivo['previewavel'])
                                            <button type="button"
                                                data-preview-url="{{ route('app.arquivos.preview', $arquivo['id']) }}"
                                                data-preview-nome="{{ $arquivo['nome'] }}"
                                                data-preview-download="{{ route('app.arquivos.download', $arquivo['id']) }}"
                                                onclick="(function(b){var m=document.getElementById('arquivo-preview-modal');if(!m)return;document.getElementById('arquivo-preview-titulo').textContent=b.dataset.previewNome;document.getElementById('arquivo-preview-baixar').setAttribute('href',b.dataset.previewDownload);document.getElementById('arquivo-preview-abrir').setAttribute('href',b.dataset.previewUrl);document.getElementById('arquivo-preview-frame').src=b.dataset.previewUrl;m.classList.remove('hidden');document.body.classList.add('overflow-hidden')})(this)"
                                                class="flex-1 text-center rounded border px-3 py-2 text-[11px] font-semibold text-white" style="background-color: #1e4679; border-color: #1e4679">Visualizar</button>
                                        @endif
                                        @if($arquivo['baixavel'] ?? true)
                                            <a href="{{ route('app.arquivos.download', $arquivo['id']) }}" class="flex-1 text-center rounded border border-gray-300 px-3 py-2 text-[11px] font-semibold text-gray-700">Baixar</a>
                                        @else
                                            <span class="flex-1 text-center rounded border border-gray-200 px-3 py-2 text-[11px] font-semibold text-gray-400" title="Original não retido — as notas extraídas estão no seu acervo">Sem original</span>
                                        @endif
                                        @if($arquivo['historico_url'])
                                            <a href="{{ $arquivo['historico_url'] }}" data-link class="flex-1 text-center rounded border border-gray-300 px-3 py-2 text-[11px] font-semibold text-gray-700">Ver origem</a>
                                        @endif
                                        @if($arquivo['pode_excluir'])
                                            <form action="{{ route('app.arquivos.destroy', $arquivo['id']) }}" method="POST" onsubmit="return confirm('Excluir este arquivo permanentemente?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded border border-gray-300 px-3 py-2 text-[11px] font-semibold" style="color: #b91c1c">Excluir</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($arquivos->hasPages())
                    <div>{{ $arquivos->withQueryString()->onEachSide(1)->links() }}</div>
                @endif
        </div>

        <div class="rounded border border-gray-300 bg-white px-4 py-3 text-[11px] text-gray-500 leading-relaxed">
            <strong class="text-gray-700">O que aparece aqui?</strong> Uploads manuais, comprovantes preservados pelo sistema e os arquivos das suas importações EFD/XML — o peso de todos conta no armazenamento do plano. O SPED original das importações EFD pode ser baixado; lotes XML guardam só as notas extraídas (o arquivo bruto não é retido). Certificados digitais permanecem protegidos na área da Empresa.
        </div>
    </div>

    {{-- Modal de preview (abre/fecha por onclick inline — padrão DS cache-robusto) --}}
    <div id="arquivo-preview-modal" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Visualização do arquivo">
        <div class="absolute inset-0" style="background-color: rgba(15, 23, 42, 0.55)"
            onclick="(function(){var m=document.getElementById('arquivo-preview-modal');document.getElementById('arquivo-preview-frame').src='about:blank';m.classList.add('hidden');document.body.classList.remove('overflow-hidden')})()"></div>
        {{-- Full-bleed no mobile; no desktop ocupa a janela com folga lateral, teto de 1440px. --}}
        <div class="relative mx-auto w-full h-[100dvh] sm:my-6 sm:w-[calc(100%-3rem)] sm:max-w-[1440px] sm:h-[calc(100dvh-3rem)] bg-white border-0 sm:border border-gray-300 rounded-none sm:rounded shadow-xl flex flex-col overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-4 py-2.5 border-b border-gray-200 bg-gray-50 shrink-0">
                <span id="arquivo-preview-titulo" class="text-sm font-semibold text-gray-800 truncate"></span>
                <div class="flex items-center gap-2 shrink-0">
                    <a id="arquivo-preview-abrir" href="#" target="_blank" rel="noopener" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-[11px] font-semibold border border-gray-300 bg-white text-gray-700 hover:bg-gray-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5h5m0 0v5m0-5l-7 7M18 14v4a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h4"></path></svg>
                        Abrir em nova aba
                    </a>
                    <a id="arquivo-preview-baixar" href="#" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-[11px] font-semibold text-white hover:opacity-90" style="background-color: #1e4679">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M5 20h14"></path></svg>
                        Baixar
                    </a>
                    <button type="button" class="p-1.5 rounded text-gray-500 hover:bg-gray-200" aria-label="Fechar visualização"
                        onclick="(function(){var m=document.getElementById('arquivo-preview-modal');document.getElementById('arquivo-preview-frame').src='about:blank';m.classList.add('hidden');document.body.classList.remove('overflow-hidden')})()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6"></path></svg>
                    </button>
                </div>
            </div>
            <iframe id="arquivo-preview-frame" src="about:blank" class="flex-1 w-full min-h-0 bg-gray-100" title="Visualização do arquivo"></iframe>
        </div>
    </div>

    {{-- ESC fecha o preview. Progressivo: se este script não rodar, overlay e botão × seguem
         fechando. Listener no document, guardado por flag pra não duplicar a cada navegação SPA. --}}
    <script>
        if (! window.__arquivoPreviewEsc) {
            window.__arquivoPreviewEsc = true;
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                var m = document.getElementById('arquivo-preview-modal');
                if (! m || m.classList.contains('hidden')) return;
                document.getElementById('arquivo-preview-frame').src = 'about:blank';
                m.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });
        }
    </script>
</div>
