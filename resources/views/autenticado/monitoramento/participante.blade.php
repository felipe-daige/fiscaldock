{{-- Monitoramento - Detalhe do Participante --}}
@inject('entitlements', 'App\Services\Entitlements\EntitlementService')
@php
    // Fase 5.2: frequência mínima permitida pelo tier (trial = 1 dia). Backend também valida.
    $freqMinDias = auth()->check() ? $entitlements->frequenciaMinimaMonitoramento(auth()->user()) : 30;
    $situacaoUpper = strtoupper((string) ($participante->situacao_cadastral ?? ''));
    $situacaoBadge = match($situacaoUpper) {
        'ATIVA', '02' => ['label' => 'ATIVA', 'hex' => '#047857'],
        'INAPTA', 'SUSPENSA' => ['label' => $situacaoUpper, 'hex' => '#dc2626'],
        // BAIXADA gera subscore 100 + piso crítico — vermelho crítico, não cinza apagado.
        'BAIXADA' => ['label' => 'BAIXADA', 'hex' => '#b91c1c'],
        default => ['label' => $situacaoUpper ?: 'NÃO CONSULTADA', 'hex' => '#6b7280'],
    };
    $regimeLabel = trim((string) ($participante->regime_tributario ?? ''));
    $regimeLabel = $regimeLabel !== '' && $regimeLabel !== '—' ? $regimeLabel : 'Não consultado';
    $regimeBadge = ['label' => mb_strtoupper($regimeLabel), 'hex' => \App\Support\Reports\ReportTheme::regimeHex($regimeLabel)];
    $returnToUrl = $returnToUrl ?? '/app/dashboard';
    $resumoParticipante = [
        [
            'label' => 'Origem',
            'valor' => $origemParticipante['label'],
            'sub' => $origemParticipante['arquivo'] ?: 'Sem arquivo vinculado',
            'sub_clamp' => true,
            'link_url' => $origemParticipante['url'],
            'link_label' => 'Ver resultado da importação',
        ],
        [
            'label' => 'Última Consulta',
            'valor' => $participante->ultima_consulta_em ? $participante->ultima_consulta_em->format('d/m/Y') : 'Nunca',
            'sub' => $participante->ultima_consulta_em ? $participante->ultima_consulta_em->format('H:i') : 'Sem consulta realizada',
        ],
        [
            'label' => 'Consultas',
            'valor' => number_format($estatisticas['total_consultas'] ?? 0, 0, ',', '.'),
            'sub' => number_format($estatisticas['consultas_sucesso'] ?? 0, 0, ',', '.').' com sucesso · '.number_format($estatisticas['consultas_erro'] ?? 0, 0, ',', '.').' com erro',
        ],
        [
            'label' => 'Valor utilizado',
            'valor' => \App\Support\Dinheiro::brl($estatisticas['valor_utilizado_reais'] ?? 0),
            'sub' => 'Neste participante',
        ],
        [
            'label' => 'Notas Fiscais',
            'valor' => number_format($totalNotasFiscais ?? 0, 0, ',', '.'),
            'sub' => 'Documentos vinculados',
        ],
    ];
@endphp
<x-cockpit.layout
    container-id="monitoramento-participante-container"
    :titulo="$participante->razao_social ?? 'Participante'"
    :subtitulo="$participante->cnpj_formatado"
    eyebrow="Participante"
    resumo-titulo="Resumo Operacional"
    data-assinatura-ativa="{{ $assinaturaAtiva ? 'true' : 'false' }}"
>
    <x-slot:badges>
        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $situacaoBadge['hex'] }}">
            {{ $situacaoBadge['label'] }}
        </span>
        <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $regimeBadge['hex'] }}">
            {{ $regimeBadge['label'] }}
        </span>
    </x-slot:badges>

    <x-slot:principal>
        <div class="flex max-w-3xl flex-wrap justify-start gap-2 lg:justify-end" data-perfil-acoes-superiores>
            <a href="{{ $returnToUrl }}" data-link class="auth-control inline-flex items-center rounded border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Voltar
            </a>
            @unless($participante->is_cpf)
                <a href="/app/consulta/nova?participantes={{ $participante->id }}" data-link class="auth-control inline-flex items-center justify-center rounded bg-gray-800 px-4 text-sm font-semibold text-white hover:bg-gray-700">
                    Nova consulta
                </a>
            @endunless
            <a href="/app/participante/{{ $participante->id }}/editar" data-link class="auth-control inline-flex items-center justify-center rounded bg-gray-800 px-4 text-sm font-semibold text-white hover:bg-gray-700">
                Editar cadastro
            </a>
            {{-- Botão único Exportar → modal de formato (design system). --}}
            <x-export-menu id="modal-exportar-participante" label="Exportar"
                           titulo="Exportar dossiê" class="px-3 text-sm font-medium"
                           descricao="Dossiê de {{ $participante->razao_social ?? $participante->cnpj_formatado }}. Escolha o formato.">
                <x-export-grupo label="Documento" />
                <x-export-option format="pdf" modal-id="modal-exportar-participante"
                                 overlay="download-overlay-participante-show"
                                 path="/app/participante/{{ $participante->id }}/dossie"
                                 descricao="Dossiê completo (cadastro, movimentação, certidões e score)." />
                <x-export-grupo label="Planilha" />
                <x-export-option format="xlsx" modal-id="modal-exportar-participante"
                                 overlay="download-overlay-participante-show"
                                 path="/app/participante/{{ $participante->id }}/dossie" query="formato=xlsx"
                                 descricao="Mesmos dados do PDF em planilha." />
            </x-export-menu>
            <x-download-overlay id="download-overlay-participante-show" texto="Gerando arquivo…" />
            @if(!$assinaturaAtiva && ! $participante->is_cpf)
                <button type="button" id="btn-criar-assinatura" class="auth-control rounded border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Criar assinatura
                </button>
            @endif
        </div>
    </x-slot:principal>

    <x-slot:resumo>
        <x-cockpit.indicadores :itens="$resumoParticipante" />
    </x-slot:resumo>

    @include('autenticado.perfis._fluxo-cnpj', ['perfilCnpj' => $perfilCnpj])


    @if($assinaturaAtiva)
        <x-cockpit.secao titulo="Gestão do Monitoramento" subtitulo="Configuração operacional deste participante.">
            <x-slot:acao>
                <span class="whitespace-nowrap rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: #047857">Assinatura Ativa</span>
            </x-slot:acao>
            <x-cockpit.dados :itens="[
                ['label' => 'Plano', 'valor' => $assinaturaAtiva->plano->nome ?? '—'],
                ['label' => 'Frequência', 'valor' => ucfirst((string) $assinaturaAtiva->frequencia)],
                ['label' => 'Próxima Execução', 'valor' => $assinaturaAtiva->proxima_execucao_em?->format('d/m/Y H:i') ?? '—'],
                ['label' => 'Última Execução', 'valor' => $assinaturaAtiva->ultima_execucao_em?->format('d/m/Y H:i') ?? 'Nunca'],
                ['label' => 'Custo por Execução', 'valor' => \App\Support\Dinheiro::brl($assinaturaAtiva->plano->custo_creditos ?? 0), 'mono' => true],
            ]" />
            <div class="mt-4 flex flex-wrap gap-2 border-t border-gray-200 pt-4">
                @if($assinaturaAtiva->status === 'ativo')
                    <button type="button" class="btn-pausar-assinatura auth-control rounded border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-600 hover:bg-gray-50" data-assinatura-id="{{ $assinaturaAtiva->id }}">Pausar</button>
                @else
                    <button type="button" class="btn-reativar-assinatura auth-control rounded border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-600 hover:bg-gray-50" data-assinatura-id="{{ $assinaturaAtiva->id }}">Reativar</button>
                @endif
                <button type="button" class="btn-cancelar-assinatura auth-control rounded border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-600 hover:bg-gray-50" data-assinatura-id="{{ $assinaturaAtiva->id }}">Cancelar</button>
            </div>
        </x-cockpit.secao>
    @endif
</x-cockpit.layout>

{{-- Modal Criar Assinatura --}}
<div id="modal-criar-assinatura" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded border border-gray-300 max-w-md w-full overflow-hidden">
        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Criar Assinatura</span>
                <button type="button" class="modal-close text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <form id="form-criar-assinatura">
            <div class="p-4 sm:p-6 space-y-4">
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Selecione o Plano</label>
                    <select name="plano_id" id="select-plano-assinatura" class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-400 focus:border-gray-400" required>
                        <option value="">Selecione...</option>
                        @foreach($planos as $plano)
                            <option value="{{ $plano->id }}">
                                {{ $plano->nome }} ({{ \App\Support\Dinheiro::brl(($plano->custo_creditos)) }}/consulta)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Frequência</label>
                    <select name="frequencia" id="select-frequencia" class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-400 focus:border-gray-400" required>
                        <option value="diario" @disabled($freqMinDias > 1)>Diária @if($freqMinDias > 1)— requer plano superior @endif</option>
                        <option value="semanal" @disabled($freqMinDias > 7)>Semanal @if($freqMinDias > 7)— requer plano superior @endif</option>
                        <option value="quinzenal" @disabled($freqMinDias > 15) @selected($freqMinDias <= 15)>Quinzenal @if($freqMinDias > 15)— requer plano superior @endif</option>
                        <option value="mensal" @selected($freqMinDias > 15)>Mensal</option>
                    </select>
                    @if($freqMinDias > 1)
                        <p class="text-[11px] text-gray-400 mt-1">Seu plano permite no máximo a cada {{ $freqMinDias }} dias. Faça upgrade para monitorar com mais frequência.</p>
                    @endif
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded p-4">
                    <p class="text-sm text-gray-600 mb-2">Participante:</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $participante->razao_social ?? $participante->cnpj_formatado }}</p>
                    <p class="text-xs text-gray-500 font-mono">{{ $participante->cnpj_formatado }}</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-white flex justify-end gap-3">
                <button type="button" class="modal-close px-4 py-2 rounded border border-gray-300 bg-white text-gray-700 text-sm font-semibold transition hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 rounded bg-gray-800 text-white text-sm font-semibold transition hover:bg-gray-700">
                    Criar Assinatura
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    'use strict';

    function initMonitoramentoParticipante() {
        const container = document.getElementById('monitoramento-participante-container');
        if (!container) return;

        if (container.dataset.initialized === '1') return;
        container.dataset.initialized = '1';

        console.log('[Monitoramento Participante] Inicializando...');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const participanteId = {{ $participante->id }};

        // Elementos
        const btnCriarAssinatura = document.getElementById('btn-criar-assinatura');
        const modalCriarAssinatura = document.getElementById('modal-criar-assinatura');
        const formCriarAssinatura = document.getElementById('form-criar-assinatura');

        // Abrir modal criar assinatura
        if (btnCriarAssinatura) {
            btnCriarAssinatura.addEventListener('click', function() {
                if (modalCriarAssinatura) {
                    modalCriarAssinatura.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            });
        }

        // Submit criar assinatura
        if (formCriarAssinatura) {
            formCriarAssinatura.addEventListener('submit', async function(e) {
                e.preventDefault();

                const planoId = document.getElementById('select-plano-assinatura').value;
                const frequencia = document.getElementById('select-frequencia').value;

                if (!planoId) {
                    window.showToast && window.showToast('Selecione um plano', 'error');
                    return;
                }

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Criando...';

                try {
                    const response = await fetch('/app/monitoramento/assinatura', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            participante_id: participanteId,
                            plano_id: planoId,
                            frequencia: frequencia,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Erro ao criar assinatura');
                    }

                    window.showToast && window.showToast('Assinatura criada com sucesso!', 'success');
                    modalCriarAssinatura.classList.add('hidden');
                    document.body.style.overflow = '';

                    // Recarregar pagina para atualizar
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);

                } catch (err) {
                    console.error('[Monitoramento Participante] Erro:', err);
                    window.showToast && window.showToast(err.message || 'Erro ao criar assinatura', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        }

        // Pausar assinatura
        document.querySelectorAll('.btn-pausar-assinatura').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                if (!confirm('Deseja pausar esta assinatura?')) return;

                const assinaturaId = this.dataset.assinaturaId;
                try {
                    const response = await fetch('/app/monitoramento/assinatura/' + assinaturaId + '/pausar', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Erro ao pausar assinatura');
                    }

                    window.showToast && window.showToast('Assinatura pausada com sucesso!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } catch (err) {
                    window.showToast && window.showToast(err.message, 'error');
                }
            });
        });

        // Reativar assinatura
        document.querySelectorAll('.btn-reativar-assinatura').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const assinaturaId = this.dataset.assinaturaId;
                try {
                    const response = await fetch('/app/monitoramento/assinatura/' + assinaturaId + '/reativar', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Erro ao reativar assinatura');
                    }

                    window.showToast && window.showToast('Assinatura reativada com sucesso!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } catch (err) {
                    window.showToast && window.showToast(err.message, 'error');
                }
            });
        });

        // Cancelar assinatura
        document.querySelectorAll('.btn-cancelar-assinatura').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                if (!confirm('Tem certeza que deseja cancelar esta assinatura? Esta ação não pode ser desfeita.')) return;

                const assinaturaId = this.dataset.assinaturaId;
                try {
                    const response = await fetch('/app/monitoramento/assinatura/' + assinaturaId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Erro ao cancelar assinatura');
                    }

                    window.showToast && window.showToast('Assinatura cancelada com sucesso!', 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } catch (err) {
                    window.showToast && window.showToast(err.message, 'error');
                }
            });
        });

        // Copiar chave de acesso
        document.querySelectorAll('.btn-copiar-chave').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const chave = this.dataset.chave;

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(chave).then(function() {
                        window.showToast && window.showToast('Chave copiada para a área de transferência!', 'success');
                    }).catch(function() {
                        fallbackCopyTextToClipboard(chave);
                    });
                } else {
                    fallbackCopyTextToClipboard(chave);
                }
            });
        });

        // Fallback para copiar texto
        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                window.showToast && window.showToast('Chave copiada para a área de transferência!', 'success');
            } catch (err) {
                window.showToast && window.showToast('Erro ao copiar chave', 'error');
            }

            document.body.removeChild(textArea);
        }

        // Fechar modais
        document.querySelectorAll('.modal-close').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const modal = btn.closest('[id^="modal-"]');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });

        // Fechar modal clicando fora
        [modalCriarAssinatura].forEach(function(modal) {
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                });
            }
        });

        console.log('[Monitoramento Participante] Inicialização concluída');
    }

    // Expor globalmente para SPA
    window.initMonitoramentoParticipante = initMonitoramentoParticipante;

    // Auto-inicializar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMonitoramentoParticipante, { once: true });
    } else {
        initMonitoramentoParticipante();
    }
})();
</script>
