{{-- Detalhe expansível por CNPJ — layout "DANFE profissional".
     Estrutura:
       1. Parecer da análise (faixa com acento slate)
       2. Bloco identidade (Dados cadastrais) — largura total, como a caixa do emitente
       3. Fontes/certidões — mosaico em colunas independentes: expandir um cartão move só
          os cartões abaixo dele na mesma coluna, sem esticar o vizinho lateral.
          Cada cartão leva o acento da cor do status na borda esquerda (sinal fiscal de carimbo).
     Espera: $blocos (ResultadoDetalhePresenter::blocos), $resumo (texto). --}}
@php
    $blocos = $blocos ?? [];
    $resumo = $resumo ?? null;
    $certidoes = $certidoes ?? [];
    $cabecalho = $cabecalho ?? [];
    $monoLabels = ['Certidão nº', 'Emissão', 'Validade', 'Início de atividade', 'Capital social', 'UF', 'Telefone'];
    $cadastro = collect($blocos)->firstWhere('chave', 'cadastro');
    $fontes = collect($blocos)->reject(fn ($b) => ($b['chave'] ?? null) === 'cadastro')->values();

    $badgeCurto = function (?string $label): string {
        $label = trim((string) $label);
        $normalizado = mb_strtolower($label);

        return match (true) {
            $label === '' => '—',
            $normalizado === 'regular' => 'OK',
            str_contains($normalizado, 'positiva com efeitos') => 'P.E.N.',
            str_contains($normalizado, 'positiva') || str_contains($normalizado, 'irregular') => 'IRREG.',
            str_contains($normalizado, 'indetermin') => 'INDET.',
            str_contains($normalizado, 'falha') || str_contains($normalizado, 'erro') => 'FALHA',
            str_contains($normalizado, 'indispon') => 'INDISP.',
            str_contains($normalizado, 'não consultada') || str_contains($normalizado, 'nao consultada') => 'N/C',
            str_contains($normalizado, 'não encontrado') || str_contains($normalizado, 'nao encontrado') => 'S/DADO',
            mb_strlen($label) > 12 => \Illuminate\Support\Str::limit($label, 12),
            default => $label,
        };
    };
@endphp

@if(!empty($resumo))
    <div class="mb-3 rounded border border-gray-200 bg-white px-3 py-2.5" style="border-left: 3px solid #1f2937">
        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Parecer da análise</p>
        <p class="text-xs text-gray-700 leading-relaxed mt-1">{{ $resumo }}</p>
    </div>
@endif

@if(!empty($certidoes))
    <div class="mb-3 flex flex-wrap gap-1.5">
        @foreach($certidoes as $cert)
            @php
                $certTooltip = trim(($cert['titulo'] ?? '').' · '.($cert['label'] ?? '').(!empty($cert['descricao']) ? "\n".$cert['descricao'] : ''));
            @endphp
            <span class="cert-chip inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white cursor-help"
                  style="background-color: {{ $cert['hex'] }}"
                  title="{{ $certTooltip }}"
                  aria-label="{{ $certTooltip }}">
                {{ $cert['sigla'] }} {{ $cert['glyph'] }}
                <span class="cert-tip hidden">
                    <strong>{{ $cert['titulo'] }} · {{ $cert['label'] }}</strong>
                    @if(!empty($cert['descricao'])){{ $cert['descricao'] }}@endif
                </span>
            </span>
        @endforeach
    </div>
@endif

@if(empty($blocos))
    <p class="text-xs text-gray-500">Sem detalhes adicionais para esta consulta.</p>
@else
    {{-- ── Identidade (cadastro): largura total, retrátil ───────────────────── --}}
    @if($cadastro)
        @php
            $cadId = 'cad-'.bin2hex(random_bytes(6));
            $cadastroItens = collect($cadastro['itens'] ?? []);
            $previewItens = $cadastroItens->whereIn('label', ['Porte', 'Natureza jurídica', 'Início de atividade'])->values();
            $situacaoCadastro = trim((string) ($cabecalho['situacao'] ?? ''));
            $situacaoUpper = mb_strtoupper($situacaoCadastro);
            $situacaoHex = match ($situacaoUpper) {
                'ATIVA', '02' => '#047857',
                'SUSPENSA' => '#ea580c',
                'INAPTA', 'BAIXADA', 'NULA' => '#b91c1c',
                default => '#6b7280',
            };
            $regimeItem = $cadastroItens->firstWhere('label', 'Regime tributário');
            $regimeValor = trim((string) ($regimeItem['valor'] ?? ''));
            $regimeConsultado = $regimeItem !== null && $regimeValor !== '' && $regimeValor !== '—';
            $regimeLabel = $regimeConsultado ? $regimeValor : 'Não consultado';
            $regimeHex = \App\Support\Reports\ReportTheme::regimeHex($regimeLabel);
            $regimeTooltip = $regimeConsultado
                ? trim('Regime tributário: '.$regimeLabel.(!empty($regimeItem['tooltip']) ? "\n".$regimeItem['tooltip'] : ''))
                : 'Regime tributário não consultado neste plano ou ausente no resultado.';
        @endphp
        {{-- Componente DS do card retrátil (toggle onclick inline, cache-robusto): o parcial
             roda em páginas sem o handler delegado do lote (participante, clearance). --}}
        <x-card-retratil :titulo="$cadastro['titulo']" :id="$cadId" class="mb-3" style="border-top: 2px solid #1e4679">
            <x-slot:subheader>
                {{-- Regime tributário sempre presente (cai em "Não consultado") — wrapper incondicional. --}}
                <span class="flex items-center flex-wrap gap-2 mt-1">
                        @if($situacaoCadastro !== '')
                            <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white"
                                  style="background-color: {{ $situacaoHex }}"
                                  title="Situação cadastral: {{ $situacaoCadastro }}">
                                Situação cadastral: {{ $situacaoCadastro }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white"
                              style="background-color: {{ $regimeHex }}"
                              title="{{ $regimeTooltip }}">
                            Regime tributário: {{ $regimeLabel }}
                        </span>
                    </span>
                @if(!empty($cabecalho['razao']) || !empty($cabecalho['documento']))
                    <span class="block text-[12px] text-gray-800 font-medium truncate mt-0.5">
                        @if(!empty($cabecalho['razao'])){{ $cabecalho['razao'] }}@endif
                        @if(!empty($cabecalho['documento'])) <span class="text-gray-300 font-normal">·</span> <span class="font-mono text-[11px] text-gray-500">{{ $cabecalho['documento'] }}</span>@endif
                    </span>
                @endif
                @if($previewItens->isNotEmpty())
                    <span class="flex flex-wrap gap-x-3 gap-y-0.5 mt-1.5">
                        @foreach($previewItens as $p)
                            <span class="text-[10px] text-gray-500"><span class="text-gray-400">{{ $p['label'] }}:</span> {{ $p['valor'] }}</span>
                        @endforeach
                    </span>
                @endif
            </x-slot:subheader>

            <div class="space-y-3">
                @if(!empty($cadastro['itens']))
                    <dl class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-2">
                        @foreach($cadastro['itens'] as $item)
                            <div class="min-w-0">
                                <dt class="text-[9px] text-gray-400 uppercase tracking-wider">{{ $item['label'] }}</dt>
                                <dd @class(['text-[12px] text-gray-800 font-medium break-words mt-0.5', 'font-mono tabular-nums' => in_array($item['label'], $monoLabels, true)])>
                                    @if(!empty($item['tooltip']))
                                        <span class="underline decoration-dotted cursor-help" title="{{ $item['tooltip'] }}">{{ $item['valor'] }}</span>
                                    @else
                                        {{ $item['valor'] }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @foreach(($cadastro['listas'] ?? []) as $lista)
                    <div class="rounded border border-gray-100 bg-gray-50 px-3 py-2.5">
                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1.5">{{ $lista['titulo'] }}</p>
                        <ul class="space-y-1">
                            @foreach($lista['linhas'] as $linha)
                                <li class="text-[11px] text-gray-700 leading-snug flex gap-1.5">
                                    <span class="text-gray-300 select-none">▪</span>
                                    <span>{{ $linha }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </x-card-retratil>
    @endif

    {{-- ── Fontes / certidões: mosaico com colunas independentes ───────────── --}}
    @if($fontes->isNotEmpty())
        @once
            <style>
                .consulta-fontes-mosaic {
                    display: flex;
                    flex-direction: column;
                    gap: .75rem;
                }
                .consulta-fontes-mosaic-column {
                    display: contents;
                }
                .consulta-fontes-mosaic-card {
                    min-width: 0;
                    order: var(--consulta-mosaic-order, 0);
                }
                @media (min-width: 1024px) {
                    .consulta-fontes-mosaic {
                        display: grid;
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                        align-items: start;
                        gap: .75rem;
                    }
                    .consulta-fontes-mosaic-column {
                        display: flex;
                        min-width: 0;
                        flex-direction: column;
                        gap: .75rem;
                    }
                    .consulta-fontes-mosaic-card {
                        order: 0;
                    }
                }
            </style>
        @endonce
        @php
            $fontesColunas = [
                $fontes->filter(fn ($_, $index) => $index % 2 === 0),
                $fontes->filter(fn ($_, $index) => $index % 2 === 1),
            ];
        @endphp
        <div class="consulta-fontes-mosaic">
            @foreach($fontesColunas as $coluna)
                <div class="consulta-fontes-mosaic-column">
                    @foreach($coluna as $ordem => $bloco)
                        @php
                            $acento = $bloco['badge']['hex'] ?? '#9ca3af';
                        @endphp
                        <div class="consulta-fontes-mosaic-card" style="--consulta-mosaic-order: {{ $ordem }}">
                            {{-- Card retrátil (componente DS): o badge no header preserva o status à vista; o
                                 corpo (mensagem oficial longa, itens, comprovante) só abre sob demanda. --}}
                            <x-card-retratil :titulo="$bloco['titulo']" :acento="$acento" :truncar-titulo="true">
                                <x-slot:badges>
                                    @if(!empty($bloco['badge']))
                                        @php
                                            $badgeTooltip = trim(($bloco['titulo'] ?? '').' · '.($bloco['badge']['label'] ?? '').(!empty($bloco['mensagem']) ? "\n".$bloco['mensagem'] : ''));
                                        @endphp
                                        <span class="whitespace-nowrap px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide text-white shrink-0 cursor-help"
                                              style="background-color: {{ $bloco['badge']['hex'] }}"
                                              title="{{ $badgeTooltip }}"
                                              aria-label="{{ $badgeTooltip }}">
                                            {{ $badgeCurto($bloco['badge']['label'] ?? '') }}
                                        </span>
                                    @endif
                                </x-slot:badges>
                                    @if(!empty($bloco['itens']))
                                        <dl class="grid grid-cols-2 gap-x-4 gap-y-2">
                                            @foreach($bloco['itens'] as $item)
                                                <div class="min-w-0">
                                                    <dt class="text-[9px] text-gray-400 uppercase tracking-wider">{{ $item['label'] }}</dt>
                                                    <dd @class(['text-[12px] text-gray-800 font-medium break-words mt-0.5', 'font-mono tabular-nums' => in_array($item['label'], $monoLabels, true)])>
                                                        @if(!empty($item['tooltip']))
                                                            <span class="underline decoration-dotted cursor-help" title="{{ $item['tooltip'] }}">{{ $item['valor'] }}</span>
                                                        @else
                                                            {{ $item['valor'] }}
                                                        @endif
                                                    </dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    @endif

                                    @foreach(($bloco['listas'] ?? []) as $lista)
                                        <div>
                                            <p class="text-[9px] text-gray-400 uppercase tracking-wider mb-1">{{ $lista['titulo'] }}</p>
                                            <ul class="space-y-0.5">
                                                @foreach($lista['linhas'] as $linha)
                                                    <li class="text-[11px] text-gray-700 leading-snug flex gap-1.5">
                                                        <span class="text-gray-300 select-none">▪</span>
                                                        <span>{{ $linha }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach

                                    @if(!empty($bloco['mensagem']))
                                        {{-- overflow-wrap:anywhere — mensagens oficiais trazem URLs longas sem espaço,
                                             que sem quebra alargam a coluna do grid e cortam o card à direita. --}}
                                        <p class="text-[11px] text-gray-500 italic leading-snug border-l-2 border-gray-200 pl-2" style="overflow-wrap: anywhere">{{ $bloco['mensagem'] }}</p>
                                    @endif

                                    @if(!empty($bloco['nota']))
                                        {{-- Nota didática (presenter): traduz a mensagem oficial — o que significa,
                                             onde o contribuinte verifica e o que a recusa NÃO prova. --}}
                                        <div class="rounded border border-blue-100 px-2.5 py-2" style="background-color: #eff6ff">
                                            <p class="text-[11px] leading-snug" style="color: #1e40af; overflow-wrap: anywhere">{{ $bloco['nota'] }}</p>
                                        </div>
                                    @endif

                                    @if(!empty($bloco['comprovante_url']))
                                        {{-- Comprovante arquivado localmente (rota app.consulta.comprovante) abre no
                                             modal de preview (padrão /app/arquivos); URL externa do órgão não é
                                             embutível em iframe — segue em nova aba. --}}
                                        @php
                                            $comprovanteLocal = str_starts_with($bloco['comprovante_url'], url('/app/consulta/resultado/'));
                                        @endphp
                                        @if($comprovanteLocal)
                                            <button type="button"
                                                    data-preview-url="{{ $bloco['comprovante_url'] }}?preview=1"
                                                    data-download-url="{{ $bloco['comprovante_url'] }}"
                                                    data-preview-nome="{{ $bloco['titulo'] }} — Comprovante"
                                                    onclick="(function(b){var m=document.getElementById('comprovante-preview-modal');if(!m){window.open(b.dataset.downloadUrl,'_blank');return}if(m.parentElement!==document.body)document.body.appendChild(m);document.getElementById('comprovante-preview-titulo').textContent=b.dataset.previewNome;document.getElementById('comprovante-preview-baixar').setAttribute('href',b.dataset.downloadUrl);document.getElementById('comprovante-preview-abrir').setAttribute('href',b.dataset.previewUrl);document.getElementById('comprovante-preview-frame').src=b.dataset.previewUrl;m.classList.remove('hidden');document.body.classList.add('overflow-hidden')})(this)"
                                                    class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-700 hover:text-gray-900 hover:underline">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Ver comprovante
                                            </button>
                                        @else
                                            <a href="{{ $bloco['comprovante_url'] }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-700 hover:text-gray-900 hover:underline">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                Ver comprovante
                                            </a>
                                        @endif
                                    @endif
                            </x-card-retratil>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif
@endif

{{-- Modal de preview do comprovante (mesmo padrão de /app/arquivos — abre/fecha por onclick
     inline, DS cache-robusto). @once: o parcial é incluído 1x por resultado no lote, o modal
     só pode existir 1x por página. --}}
@once
    <div id="comprovante-preview-modal" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Visualização do comprovante">
        <div class="absolute inset-0" style="background-color: rgba(15, 23, 42, 0.55)"
            onclick="(function(){var m=document.getElementById('comprovante-preview-modal');document.getElementById('comprovante-preview-frame').src='about:blank';m.classList.add('hidden');document.body.classList.remove('overflow-hidden')})()"></div>
        {{-- Full-bleed no mobile; no desktop ocupa a janela com folga lateral, teto de 1440px. --}}
        <div class="relative mx-auto w-full h-[100dvh] sm:my-6 sm:w-[calc(100%-3rem)] sm:max-w-[1440px] sm:h-[calc(100dvh-3rem)] bg-white border-0 sm:border border-gray-300 rounded-none sm:rounded shadow-xl flex flex-col overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-4 py-2.5 border-b border-gray-200 bg-gray-50 shrink-0">
                <span id="comprovante-preview-titulo" class="text-sm font-semibold text-gray-800 truncate"></span>
                <div class="flex items-center gap-2 shrink-0">
                    <a id="comprovante-preview-abrir" href="#" target="_blank" rel="noopener" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-[11px] font-semibold border border-gray-300 bg-white text-gray-700 hover:bg-gray-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5h5m0 0v5m0-5l-7 7M18 14v4a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h4"></path></svg>
                        Abrir em nova aba
                    </a>
                    <a id="comprovante-preview-baixar" href="#" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-[11px] font-semibold text-white hover:opacity-90" style="background-color: #1e4679">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M5 20h14"></path></svg>
                        Baixar
                    </a>
                    <button type="button" class="p-1.5 rounded text-gray-500 hover:bg-gray-200" aria-label="Fechar visualização"
                        onclick="(function(){var m=document.getElementById('comprovante-preview-modal');document.getElementById('comprovante-preview-frame').src='about:blank';m.classList.add('hidden');document.body.classList.remove('overflow-hidden')})()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M6 18L18 6"></path></svg>
                    </button>
                </div>
            </div>
            <iframe id="comprovante-preview-frame" src="about:blank" class="flex-1 w-full min-h-0 bg-gray-100" title="Visualização do comprovante"></iframe>
        </div>
    </div>

    {{-- ESC fecha o preview. Progressivo: se este script não rodar, overlay e botão × seguem
         fechando. Listener no document, guardado por flag pra não duplicar a cada navegação SPA. --}}
    <script>
        if (! window.__comprovantePreviewEsc) {
            window.__comprovantePreviewEsc = true;
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') return;
                var m = document.getElementById('comprovante-preview-modal');
                if (! m || m.classList.contains('hidden')) return;
                document.getElementById('comprovante-preview-frame').src = 'about:blank';
                m.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });
        }
    </script>
@endonce
