{{-- Clearance Full — Camada A: regularidade da contraparte das notas do lote.
     Lê de participante_scores (fonte canônica) via RegularidadeContraparteService::resumoPorLoteClearance.
     O detalhe expansível de cada CNPJ é o MESMO da Consulta CNPJ e do Score Fiscal
     (ResultadoDetalhePresenter::detalheDoParticipante + partial `consulta.partials.detalhe-blocos`).
     Badges com hex INLINE (regra dura do design system — Tailwind v4 compila cor pra oklch()). --}}
@if(($regularidade['ativo'] ?? false) && ($regularidade['total'] ?? 0) > 0)
    @php($temAlerta = ($regularidade['irregulares'] ?? 0) > 0)
    @php($idsContrapartes = collect($regularidade['contrapartes'])->pluck('participante_id')->all())
    <div class="bg-white rounded border mb-4" style="border-color: {{ $temAlerta ? '#fca5a5' : '#e5e7eb' }}">
        <div class="px-4 py-3 border-b flex flex-wrap items-center justify-between gap-2" style="border-color: #e5e7eb">
            <div>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Clearance completo</p>
                <p class="text-sm font-bold text-gray-900">Regularidade da contraparte</p>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                @if($temAlerta)
                    <span class="text-[10px] font-bold uppercase tracking-wide text-white px-2 py-0.5 rounded" style="background-color: #dc2626">
                        {{ $regularidade['irregulares'] }} irregular{{ $regularidade['irregulares'] > 1 ? 'es' : '' }}
                    </span>
                @else
                    <span class="text-[10px] font-bold uppercase tracking-wide text-white px-2 py-0.5 rounded" style="background-color: #047857">Nenhuma irregularidade</span>
                @endif
                @if(($regularidade['pendentes'] ?? 0) > 0)
                    <span class="text-[10px] font-bold uppercase tracking-wide text-white px-2 py-0.5 rounded" style="background-color: #9ca3af">
                        {{ $regularidade['pendentes'] }} em apuração
                    </span>
                @endif

                {{-- Dossiê ÚNICO de todos os CNPJs — rota canônica em lote (a mesma da listagem de
                     participantes: DossieLoteBuilder). É POST + download de PDF, então vai como form
                     com target=_blank e SEM data-link (o SPA não pode interceptar binário —
                     memory feedback_spa_datalink_download). --}}
                <form method="POST" action="{{ route('app.participantes.dossie-lote') }}" target="_blank" class="inline">
                    @csrf
                    @foreach($idsContrapartes as $pid)
                        <input type="hidden" name="ids[]" value="{{ $pid }}">
                    @endforeach
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-white px-2.5 py-1 rounded"
                            style="background-color: #1f2937">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        </svg>
                        Dossiê dos CNPJs ({{ count($idsContrapartes) }})
                    </button>
                </form>
            </div>
        </div>

        <div class="divide-y" style="border-color: #f3f4f6">
            @foreach($regularidade['contrapartes'] as $c)
                @php($notasAlerta = collect($c['notas'])->where('alerta', true))
                <div class="px-4 py-3">
                    <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $c['razao_social'] ?: 'Contraparte' }}</p>
                            <p class="text-[11px] text-gray-500 font-mono">{{ $c['documento'] }}</p>
                        </div>
                        <a href="{{ route('app.risk.show', ['id' => $c['participante_id']]) }}" data-link
                           class="text-[11px] font-semibold text-gray-600 hover:text-gray-900 underline whitespace-nowrap">Score Fiscal</a>
                    </div>

                    @if($c['pendente'])
                        <p class="text-[12px] text-gray-500">Consulta em andamento — a regularidade aparece aqui assim que as fontes responderem.</p>
                    @else
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            @foreach($c['certidoes'] as $cert)
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded border" style="border-color: #e5e7eb">
                                    <span class="text-gray-500">{{ $cert['label'] }}</span>
                                    <span class="font-bold text-white px-1.5 py-px rounded" style="background-color: {{ $cert['badge']['hex'] }}">{{ $cert['badge']['label'] }}</span>
                                </span>
                            @endforeach
                        </div>

                        @if($c['irregular'])
                            {{-- O cruzamento que só o clearance enxerga: nota VÁLIDA na SEFAZ + contraparte IRREGULAR. --}}
                            <div class="rounded p-2.5 mb-2" style="background-color: #fef2f2; border: 1px solid #fecaca">
                                <p class="text-[12px] font-semibold" style="color: #991b1b">
                                    {{ implode(' · ', $c['motivos']) }}
                                </p>
                                @if($notasAlerta->isNotEmpty())
                                    <p class="text-[11px] mt-1" style="color: #991b1b">
                                        {{ $notasAlerta->count() }} nota(s) AUTORIZADA(S) na SEFAZ desta contraparte —
                                        crédito fiscal exposto a glosa. Notas:
                                        <span class="font-mono">{{ $notasAlerta->pluck('numero')->filter()->take(6)->implode(', ') ?: '—' }}</span>
                                    </p>
                                @endif
                            </div>
                        @endif

                        {{-- Resultado COMPLETO do CNPJ — exatamente o que a Consulta CNPJ entrega
                             (dados cadastrais, CNAEs, QSA, certidões com comprovante, parecer).
                             Mesmo partial, mesma leitura: as telas não divergem. --}}
                        {{-- `open`: o resultado do CNPJ é o produto que o usuário pagou — abre expandido,
                             sem exigir clique. O <summary> continua permitindo recolher. --}}
                        @if(!empty($c['detalhe']))
                            <details class="group" open>
                                <summary class="cursor-pointer text-[11px] font-semibold text-gray-600 hover:text-gray-900 select-none">
                                    <span class="group-open:hidden">Ver resultado completo do CNPJ</span>
                                    <span class="hidden group-open:inline">Ocultar resultado completo</span>
                                    @if(!empty($c['detalhe']['consultado_em']))
                                        <span class="font-normal text-gray-400">· consultado em {{ \Illuminate\Support\Carbon::parse($c['detalhe']['consultado_em'])->format('d/m/Y') }}</span>
                                    @endif
                                </summary>
                                <div class="mt-3">
                                    @include('autenticado.consulta.partials.detalhe-blocos', [
                                        'blocos' => $c['detalhe']['blocos'],
                                        'resumo' => $c['detalhe']['resumo'],
                                        'certidoes' => $c['detalhe']['certidoes'],
                                        'cabecalho' => $c['detalhe']['cabecalho'],
                                    ])
                                </div>
                            </details>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>

        <div class="px-4 py-2 border-t" style="border-color: #f3f4f6; background-color: #f9fafb">
            <p class="text-[11px] text-gray-500">
                Fontes: situação cadastral (Receita Federal), SINTEGRA (inscrição estadual) e CND Federal (PGFN).
                Regularidade é a <strong>de hoje</strong> — não a da data de emissão (certidão retroativa não existe).
            </p>
        </div>
    </div>
@endif
