@php
    // Snapshot completo da última consulta SEFAZ/Clearance da nota (nfe_consultas/cte_consultas).
    // $consulta: model NfeConsulta|CteConsulta com ->tipo_snapshot ('nfe'|'cte') OU null.
    // Espelha o "resultado completo" que mostramos para consultas de CNPJ: identificação das
    // partes, totais/impostos, itens e eventos oficiais. Em qualquer nota (EFD/XML) o card
    // aparece; sem consulta, vira atalho para verificar no Clearance.
    $consulta = $consulta ?? null;
    $nota = $nota ?? null;
    $chaveConsulta = $chaveConsulta ?? ($consulta->chave_acesso ?? null);

    $situacaoBadge = [
        'AUTORIZADA' => '#047857',
        'CANCELADA' => '#dc2626',
        'DENEGADA' => '#991b1b',
        'INUTILIZADA' => '#374151',
        'NAO_ENCONTRADA' => '#b45309',
        'INDETERMINADO' => '#1d4ed8',
    ];
    $situacaoLabel = [
        'AUTORIZADA' => 'Autorizada',
        'CANCELADA' => 'Cancelada',
        'DENEGADA' => 'Denegada',
        'INUTILIZADA' => 'Inutilizada',
        'NAO_ENCONTRADA' => 'Não encontrada',
        'INDETERMINADO' => 'Indeterminada',
    ];

    // Helpers de exibição — o snapshot InfoSimples traz cada valor como string crua ("") +
    // uma versão `normalizado_*` numérica. Preferimos a normalizada; caímos na crua.
    $brl = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $temTexto = fn ($v) => $v !== null && trim((string) $v) !== '';
    $ov = function ($v, $default = '—') use ($temTexto) { return $temTexto($v) ? $v : $default; };
    $docFmt = function ($doc) {
        $d = preg_replace('/\D/', '', (string) $doc);
        if (strlen($d) === 14) { return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $d); }
        if (strlen($d) === 11) { return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $d); }
        return $doc ?: null;
    };

    if ($consulta) {
        $status = strtoupper((string) ($consulta->status ?? ''));
        $isCte = ($consulta->tipo_snapshot ?? null) === 'cte';
        $valorConsulta = $isCte ? ($consulta->valor_prestacao ?? null) : ($consulta->valor_total ?? null);
        $eventos = is_array($consulta->eventos ?? null) ? $consulta->eventos : [];
        $totais = is_array($consulta->totais ?? null) ? $consulta->totais : [];
        $produtos = is_array($consulta->produtos ?? null) ? $consulta->produtos : [];
        $componentes = is_array($consulta->componentes ?? null) ? $consulta->componentes : [];
        $nfesRef = is_array($consulta->nfes_referenciadas ?? null) ? $consulta->nfes_referenciadas : [];
        $loteId = $consulta->consulta_lote_id ?? null;

        // Payload BRUTO da InfoSimples (o documento retornado, sem o wrapper interno). Exibido
        // num disclosure pra o usuário conferir que a tela é fiel ao recebido, byte a byte.
        $payloadTop = is_array($consulta->payload ?? null) ? $consulta->payload : [];
        $rawPayload = $payloadTop[$isCte ? 'cte_clearance' : 'nfe_clearance'] ?? null;
        $rawJson = $rawPayload !== null
            ? json_encode($rawPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : null;
        // Flag oficial da InfoSimples: consulta sem certificado A1 mascara contraparte, omite
        // totais e abrevia descrições — explica os limites do que a Receita devolve.
        $semCertificado = (bool) ($consulta->consulta_sem_certificado ?? false);

        // Valor de $totais preferindo normalizado_<chave>, senão a versão crua.
        $tot = function (string $chave, ?array $fonte = null) use ($totais) {
            $fonte = $fonte ?? $totais;
            $norm = $fonte['normalizado_'.$chave] ?? null;
            if ($norm !== null && (float) $norm != 0.0) { return (float) $norm; }
            $raw = $fonte[$chave] ?? null;
            return ($raw !== null && trim((string) $raw) !== '') ? (float) str_replace(['.', ','], ['', '.'], (string) $raw) : 0.0;
        };
        $impostosCte = is_array($totais['impostos'] ?? null) ? $totais['impostos'] : [];

        // Protocolo de autorização (primeiro evento que tiver).
        $protocolo = null;
        foreach ($eventos as $ev) { $ev = (array) $ev; if (! empty($ev['protocolo'])) { $protocolo = $ev['protocolo']; break; } }

        // ── Auditoria: Declarado (escriturado) × Receita (SEFAZ) ──
        // Fonte ÚNICA: DivergenciaService::auditarUmDocumento (mesma engine do clearance em lote),
        // computada no controller e injetada como $auditoria. NÃO reimplementar o confronto aqui —
        // a engine confere a CONTRAPARTE contra emit E dest do SEFAZ (não emit=emit), tolera máscara
        // e aplica o gate de identidade (CNPJ confere → nome/UF viram drift, não divergência).
        $auditoria = $auditoria ?? null;
        $bloqueante = in_array($status, ['CANCELADA', 'DENEGADA', 'INUTILIZADA'], true);
        $naoLocalizada = in_array($status, ['NAO_ENCONTRADA', 'INDETERMINADO', 'NAO_ENCONTRADO'], true);

        $sevMeta = [
            'ok' => ['#047857', 'Conforme', 'Declarado e Receita batem nos pontos verificados.'],
            'ruido' => ['#0891b2', 'Ruído de cadastro', 'Pequenas diferenças de cadastro, sem impacto fiscal.'],
            'revisar' => ['#b45309', 'Revisar', 'Há divergência a revisar entre o escriturado e a Receita.'],
            'critica' => ['#dc2626', 'Divergência crítica', 'Divergência grave — revise antes de qualquer apuração.'],
        ];
        $sev = $auditoria['severidade'] ?? 'ok';
        [$vHex, $vLabel, $vTextoDefault] = $sevMeta[$sev] ?? $sevMeta['ok'];
        $veredito = [
            'hex' => $vHex,
            'label' => $vLabel,
            'texto' => trim(implode(' ', $auditoria['motivos'] ?? [])) ?: $vTextoDefault,
        ];

        // Status por linha (vocabulário da engine) → ícone/cor.
        $auditStatusMeta = [
            'confere' => ['#047857', '✓'],
            'difere' => ['#dc2626', '✕'],
            'indeterminado' => ['#9ca3af', '—'],
            'sem_dado' => ['#9ca3af', '—'],
        ];

        // Situação e Valor não vêm em `conferencias` (são status/delta) → montados no topo.
        $auditRows = [];
        $auditRows[] = [
            'campo' => 'Situação na Receita',
            'declarado' => 'Escriturada na contabilidade',
            'sefaz' => $situacaoLabel[$status] ?? ($status ?: 'Consultada'),
            'status' => $bloqueante ? 'difere' : ($naoLocalizada ? 'indeterminado' : 'confere'),
            'nota' => $bloqueante ? 'Documento escriturado que a Receita não reconhece.'
                : ($naoLocalizada ? 'A Receita não retornou situação válida para esta chave.' : null),
        ];

        $vDecl = $auditoria['declarado_valor'] ?? null;
        $vSefaz = $auditoria['sefaz_valor'] ?? null;
        if ($vDecl !== null && $vSefaz !== null && (float) $vSefaz != 0.0) {
            $diff = round((float) $vDecl - (float) $vSefaz, 2);
            $bate = abs($diff) < 0.01;
            $auditRows[] = [
                'campo' => $isCte ? 'Valor da prestação' : 'Valor total',
                'declarado' => $brl($vDecl),
                'sefaz' => $brl($vSefaz),
                'status' => $bate ? 'confere' : 'difere',
                'nota' => $bate ? null : 'Diferença de '.$brl(abs($diff)).' entre o escriturado e a Receita.',
            ];
        }

        foreach (($auditoria['conferencias'] ?? []) as $conf) {
            $auditRows[] = [
                'campo' => $conf['campo'],
                'declarado' => $conf['declarado'] ?? '—',
                'sefaz' => $conf['sefaz'] ?? '—',
                'status' => $conf['status'] ?? 'indeterminado',
                'nota' => $conf['nota'] ?? null,
            ];
        }
    }
@endphp

<div class="bg-white rounded border border-gray-300 overflow-hidden mb-4">
    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center justify-between gap-2">
        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-widest">Resultado da Consulta · Clearance {{ ($consulta && $isCte) ? 'CT-e' : 'NF-e' }}</span>
        @if($consulta)
            <span class="whitespace-nowrap px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide text-white" style="background-color: {{ $situacaoBadge[$status] ?? '#374151' }}">
                {{ $situacaoLabel[$status] ?? ($status ?: 'Consultada') }}
            </span>
        @else
            <span class="text-[10px] font-semibold text-gray-400 bg-gray-200 px-2 py-0.5 rounded uppercase tracking-wide">Sem consulta</span>
        @endif
    </div>

    @if($consulta)
        @if($semCertificado)
            <div class="px-4 py-2 border-b border-gray-200 flex items-start gap-2" style="background-color: #f0f9ff">
                <svg class="w-3.5 h-3.5 mt-0.5 shrink-0" fill="none" stroke="#0369a1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-[11px]" style="color: #0369a1">Consulta pública (sem certificado A1). A Receita devolve os dados oficiais, mas <strong>mascara parte do CNPJ/nome da contraparte</strong>, não detalha os impostos e abrevia a descrição dos itens. Todos os campos abaixo são exatamente o que a InfoSimples retornou.</p>
            </div>
        @endif

        {{-- Faixa de indicadores --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-200">
            <div class="p-4">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Consultado em</p>
                <p class="text-sm font-semibold text-gray-900">{{ $consulta->consultado_em ? \Illuminate\Support\Carbon::parse($consulta->consultado_em)->format('d/m/Y') : '—' }}</p>
                <p class="text-[11px] text-gray-500">{{ $consulta->consultado_em ? \Illuminate\Support\Carbon::parse($consulta->consultado_em)->format('H:i') : '' }}</p>
            </div>
            <div class="p-4">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Fonte</p>
                <p class="text-sm font-semibold text-gray-900">SEFAZ · {{ $isCte ? 'CT-e' : 'NF-e' }}</p>
                <p class="text-[11px] text-gray-500">Receita Federal</p>
            </div>
            <div class="p-4">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $isCte ? 'Valor da prestação' : 'Valor da nota' }}</p>
                <p class="text-sm font-semibold text-gray-900">{{ $valorConsulta !== null ? $brl($valorConsulta) : '—' }}</p>
                @if($isCte && $temTexto($consulta->valor_carga))<p class="text-[11px] text-gray-500">Carga {{ $brl($consulta->valor_carga) }}</p>@endif
            </div>
            <div class="p-4">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Protocolo</p>
                <p class="text-sm font-semibold text-gray-900 font-mono break-all">{{ $ov($protocolo) }}</p>
                <p class="text-[11px] text-gray-500">{{ count($eventos) }} evento(s)</p>
            </div>
        </div>

        @if(! empty($consulta->infosimples_code_message) || ! empty($consulta->error_message))
            <div class="px-4 py-3 border-t border-gray-200" style="background-color: #fffbeb">
                <p class="text-[11px] text-gray-700">{{ $consulta->error_message ?: $consulta->infosimples_code_message }}</p>
            </div>
        @endif

        {{-- Auditoria: Declarado × Receita --}}
        <div class="px-4 py-4 border-t border-gray-200">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Auditoria — Declarado × Receita</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">Confronto entre o que foi escriturado ({{ ($auditoria['declarado_origem'] ?? null) === 'xml' ? 'XML do contador' : (($auditoria['declarado_origem'] ?? null) === 'efd' ? 'EFD/SPED' : 'acervo') }}) e a resposta oficial da Receita. A Receita mascara parte do CNPJ/nome da contraparte — validado pela porção visível.</p>
                </div>
                <span class="px-2.5 py-1 rounded text-[11px] font-bold uppercase tracking-wide text-white shrink-0" style="background-color: {{ $veredito['hex'] }}">{{ $veredito['label'] }}</span>
            </div>

            <div class="rounded border mb-3 px-3 py-2" style="border-color: {{ $veredito['hex'] }}33; background-color: {{ $veredito['hex'] }}0d">
                <p class="text-xs" style="color: {{ $veredito['hex'] }}">{{ $veredito['texto'] }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-left text-[10px] uppercase tracking-wide text-gray-400 border-b border-gray-200">
                            <th class="py-1.5 pr-3 w-6"></th>
                            <th class="py-1.5 pr-3">Campo</th>
                            <th class="py-1.5 pr-3">Declarado</th>
                            <th class="py-1.5 pr-3">Receita (SEFAZ)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($auditRows as $row)
                            @php [$rHex, $rIco] = $auditStatusMeta[$row['status']] ?? $auditStatusMeta['indeterminado']; @endphp
                            <tr>
                                <td class="py-2 pr-3 align-top"><span class="font-bold" style="color: {{ $rHex }}">{{ $rIco }}</span></td>
                                <td class="py-2 pr-3 align-top text-gray-500">
                                    {{ $row['campo'] }}
                                    @if(! empty($row['nota']))<span class="block text-[10px] text-gray-400 mt-0.5">{{ $row['nota'] }}</span>@endif
                                </td>
                                <td class="py-2 pr-3 align-top text-gray-900 font-medium">{{ $row['declarado'] }}</td>
                                <td class="py-2 pr-3 align-top font-medium" style="color: {{ $row['status'] === 'confere' ? '#111827' : $rHex }}">{{ $row['sefaz'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Identificação das partes --}}
        <div class="px-4 py-3 border-t border-gray-200">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-3">Dados do documento na Receita</p>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-4">
                {{-- Cabeçalho --}}
                <div class="space-y-1.5">
                    <div class="flex justify-between gap-3 text-xs"><span class="text-gray-500">Número / Série</span><span class="text-gray-900 font-medium">{{ $ov($consulta->numero) }} / {{ $ov($consulta->serie) }}</span></div>
                    <div class="flex justify-between gap-3 text-xs"><span class="text-gray-500">Modelo</span><span class="text-gray-900 font-medium">{{ $ov($consulta->modelo) }}</span></div>
                    <div class="flex justify-between gap-3 text-xs"><span class="text-gray-500">Emissão</span><span class="text-gray-900 font-medium">{{ $consulta->data_emissao ? \Illuminate\Support\Carbon::parse($consulta->data_emissao)->format('d/m/Y') : '—' }}</span></div>
                    <div class="flex justify-between gap-3 text-xs"><span class="text-gray-500">Natureza</span><span class="text-gray-900 font-medium text-right">{{ $ov($consulta->natureza_operacao) }}</span></div>
                    @if($isCte)
                        <div class="flex justify-between gap-3 text-xs"><span class="text-gray-500">Modal / Serviço</span><span class="text-gray-900 font-medium text-right">{{ $ov($consulta->modal) }} · {{ $ov($consulta->tipo_servico) }}</span></div>
                        <div class="flex justify-between gap-3 text-xs"><span class="text-gray-500">CFOP</span><span class="text-gray-900 font-medium">{{ $ov($consulta->cfop) }}</span></div>
                        <div class="flex justify-between gap-3 text-xs"><span class="text-gray-500">Trajeto</span><span class="text-gray-900 font-medium">{{ $ov($consulta->uf_inicio) }} → {{ $ov($consulta->uf_fim) }}</span></div>
                    @else
                        <div class="flex justify-between gap-3 text-xs"><span class="text-gray-500">Tipo operação</span><span class="text-gray-900 font-medium">{{ $ov($consulta->tipo_operacao) }}</span></div>
                    @endif
                    <div class="flex justify-between gap-3 text-xs"><span class="text-gray-500">Chave</span><span class="text-gray-900 font-mono text-[10px] break-all text-right">{{ $ov($chaveConsulta) }}</span></div>
                </div>

                {{-- Partes --}}
                <div class="space-y-3">
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $isCte ? 'Transportadora (emitente)' : 'Emitente' }}</p>
                        <p class="text-sm text-gray-900 font-medium">{{ $ov($consulta->emit_nome) }}</p>
                        <p class="text-[11px] text-gray-500">{{ $ov($docFmt($consulta->emit_cnpj)) }}@if($temTexto($consulta->emit_ie)) · IE {{ $consulta->emit_ie }}@endif @if($temTexto($consulta->emit_municipio))· {{ $consulta->emit_municipio }}/{{ $consulta->emit_uf }}@elseif($temTexto($consulta->emit_uf))· {{ $consulta->emit_uf }}@endif</p>
                    </div>
                    @if($isCte)
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Tomador</p>
                            <p class="text-sm text-gray-900 font-medium">{{ $ov($consulta->tomador_nome) }}</p>
                            <p class="text-[11px] text-gray-500">{{ $ov($docFmt($consulta->tomador_cnpj ?: $consulta->tomador_cpf)) }}@if($temTexto($consulta->tomador_uf)) · {{ $consulta->tomador_municipio ? $consulta->tomador_municipio.'/' : '' }}{{ $consulta->tomador_uf }}@endif</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Remetente</p>
                                <p class="text-xs text-gray-900">{{ $ov($consulta->remet_nome) }}</p>
                                <p class="text-[11px] text-gray-500">{{ $ov($docFmt($consulta->remet_cnpj ?: $consulta->remet_cpf)) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Destinatário</p>
                                <p class="text-xs text-gray-900">{{ $ov($consulta->dest_nome) }}</p>
                                <p class="text-[11px] text-gray-500">{{ $ov($docFmt($consulta->dest_cnpj ?: $consulta->dest_cpf)) }}</p>
                            </div>
                        </div>
                    @else
                        <div>
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Destinatário</p>
                            <p class="text-sm text-gray-900 font-medium">{{ $ov($consulta->dest_nome) }}</p>
                            <p class="text-[11px] text-gray-500">{{ $ov($docFmt($consulta->dest_cnpj ?: $consulta->dest_cpf)) }}@if($temTexto($consulta->dest_municipio)) · {{ $consulta->dest_municipio }}/{{ $consulta->dest_uf }}@elseif($temTexto($consulta->dest_uf))· {{ $consulta->dest_uf }}@endif</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Totais / impostos --}}
        @php
            if ($isCte) {
                $linhasTotais = array_filter([
                    ['Prestação', $valorConsulta],
                    ['Carga', $temTexto($consulta->valor_carga) ? (float) $consulta->valor_carga : null],
                    ['ICMS', $tot('valor_icms', $impostosCte)],
                    ['Base ICMS', $tot('base_calculo_icms', $impostosCte)],
                    ['A receber', $tot('valor_a_receber')],
                ], fn ($l) => $l[1] !== null && (float) $l[1] != 0.0);
            } else {
                $linhasTotais = array_filter([
                    ['Produtos', $tot('valor_produtos')],
                    ['Total da nota', $tot('valor_nfe')],
                    ['Base ICMS', $tot('base_calculo_icms')],
                    ['ICMS', $tot('valor_icms')],
                    ['ICMS ST', $tot('valor_icms_substituicao')],
                    ['IPI', $tot('valor_ipi')],
                    ['PIS', $tot('valor_pis')],
                    ['COFINS', $tot('valor_cofins')],
                    ['Frete', $tot('valor_frete')],
                    ['Seguro', $tot('valor_seguro')],
                    ['Descontos', $tot('valor_descontos')],
                    ['Outras despesas', $tot('outras_despesas')],
                    ['Tributos (aprox.)', $tot('valor_tributos')],
                ], fn ($l) => (float) $l[1] != 0.0);
            }
        @endphp
        @if(! empty($linhasTotais))
            <div class="px-4 py-3 border-t border-gray-200">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-3">Totais e impostos (SEFAZ)</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($linhasTotais as $lt)
                        <div class="bg-gray-50 rounded border border-gray-200 px-3 py-2">
                            <p class="text-[10px] text-gray-500 uppercase tracking-wide">{{ $lt[0] }}</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $brl($lt[1]) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Itens (NF-e) --}}
        @if(! $isCte && ! empty($produtos))
            <div class="px-4 py-3 border-t border-gray-200">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Itens do documento ({{ count($produtos) }})</p>
                @if($semCertificado)<p class="text-[11px] text-gray-400 mb-2">Descrições abreviadas pela Receita na consulta sem certificado — quantidades e valores são os reais.</p>@endif
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-left text-[10px] uppercase tracking-wide text-gray-400 border-b border-gray-200">
                                <th class="py-1.5 pr-2">#</th>
                                <th class="py-1.5 pr-2">Descrição</th>
                                <th class="py-1.5 pr-2 text-right">Qtd</th>
                                <th class="py-1.5 pr-2">Un</th>
                                <th class="py-1.5 pr-2 text-right">Vlr unit.</th>
                                <th class="py-1.5 text-right">Vlr produto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach(array_slice($produtos, 0, 100) as $prod)
                                @php $prod = (array) $prod; @endphp
                                <tr>
                                    <td class="py-1.5 pr-2 text-gray-500">{{ $prod['num'] ?? '' }}</td>
                                    <td class="py-1.5 pr-2 text-gray-900">{{ $prod['descricao'] ?? '—' }}</td>
                                    <td class="py-1.5 pr-2 text-right text-gray-700 font-mono">{{ $prod['quantidade'] ?? '—' }}</td>
                                    <td class="py-1.5 pr-2 text-gray-500">{{ $prod['unidade_comercial'] ?? '' }}</td>
                                    <td class="py-1.5 pr-2 text-right text-gray-700 font-mono">{{ $prod['valor_unidade'] ?? '—' }}</td>
                                    <td class="py-1.5 text-right text-gray-900 font-mono">{{ $prod['valor_produto'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($produtos) > 100)<p class="text-[11px] text-gray-400 mt-2">Mostrando 100 de {{ count($produtos) }} itens.</p>@endif
            </div>
        @endif

        {{-- Componentes do frete (CT-e) --}}
        @if($isCte && ! empty($componentes))
            <div class="px-4 py-3 border-t border-gray-200">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Componentes do frete ({{ count($componentes) }})</p>
                <div class="space-y-1.5">
                    @foreach($componentes as $comp)
                        @php $comp = (array) $comp; $cv = $comp['valor'] ?? $comp['normalizado_valor'] ?? null; @endphp
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="text-gray-700">{{ $comp['nome'] ?? $comp['descricao'] ?? 'Componente' }}</span>
                            <span class="text-gray-900 font-mono">{{ $cv !== null ? $brl($cv) : '—' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- NF-es referenciadas (CT-e) --}}
        @if($isCte && ! empty($nfesRef))
            <div class="px-4 py-3 border-t border-gray-200">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">NF-es transportadas ({{ count($nfesRef) }})</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(array_slice($nfesRef, 0, 60) as $ref)
                        @php $ref = is_array($ref) ? ($ref['chave'] ?? $ref['chave_acesso'] ?? reset($ref)) : $ref; @endphp
                        <span class="text-[10px] font-mono text-gray-600 bg-gray-100 rounded px-1.5 py-0.5 break-all">{{ $ref }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Eventos SEFAZ --}}
        @if(! empty($eventos))
            <div class="px-4 py-3 border-t border-gray-200">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Eventos SEFAZ</p>
                <div class="space-y-2">
                    @foreach($eventos as $ev)
                        @php
                            $ev = (array) $ev;
                            $evDesc = $ev['evento'] ?? $ev['descricao'] ?? $ev['tipo_evento'] ?? $ev['tipo'] ?? 'Evento';
                            $evData = $ev['data_autorizacao'] ?? $ev['data'] ?? $ev['data_inclusao'] ?? $ev['data_evento'] ?? $ev['datahora'] ?? null;
                            $evProt = $ev['protocolo'] ?? null;
                        @endphp
                        <div class="flex items-start justify-between gap-3 text-xs">
                            <div class="min-w-0">
                                <span class="text-gray-700">{{ $evDesc }}</span>
                                @if($evProt)<span class="block text-[10px] font-mono text-gray-400">Protocolo {{ $evProt }}</span>@endif
                            </div>
                            @if($evData)<span class="text-[11px] font-mono text-gray-500 shrink-0">{{ $evData }}</span>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Rodapé: metadados + links oficiais --}}
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50/50 flex flex-wrap items-center gap-x-4 gap-y-2">
            @if($temTexto($consulta->versao_xml))<span class="text-[11px] text-gray-500">Versão XML {{ $consulta->versao_xml }}</span>@endif
            @if(($isCte ? ($consulta->cte_completa ?? null) : ($consulta->nfe_completa ?? null)))
                <span class="text-[11px] text-gray-500">Documento completo</span>
            @endif
            @if($consulta->consulta_sem_certificado ?? false)<span class="text-[11px] text-gray-500">Consulta sem certificado</span>@endif
            @if(! empty($consulta->url_html))
                <a href="{{ $consulta->url_html }}" target="_blank" rel="noopener" class="text-xs text-gray-700 hover:text-gray-900 hover:underline">Ver documento oficial</a>
            @endif
            @if(! empty($consulta->url_xml))
                <a href="{{ $consulta->url_xml }}" target="_blank" rel="noopener" class="text-xs text-gray-700 hover:text-gray-900 hover:underline">Baixar XML</a>
            @endif
            @if(! empty($consulta->url_site_receipt))
                <a href="{{ $consulta->url_site_receipt }}" target="_blank" rel="noopener" class="text-xs text-gray-700 hover:text-gray-900 hover:underline">Comprovante</a>
            @endif
            @if($loteId)
                <a href="/app/clearance/buscar/resultado/{{ $loteId }}" data-link class="text-xs text-gray-700 hover:text-gray-900 hover:underline">Abrir lote de clearance</a>
            @endif
        </div>

        {{-- Payload bruto da InfoSimples: prova de fidelidade. A tela deriva 100% deste JSON. --}}
        @if($rawJson)
            <details class="border-t border-gray-200">
                <summary class="px-4 py-2.5 cursor-pointer text-[11px] font-semibold text-gray-500 uppercase tracking-wide hover:bg-gray-50 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    Resposta bruta da InfoSimples (fonte da tela)
                </summary>
                <div class="px-4 pb-4">
                    <p class="text-[11px] text-gray-400 mb-2">Documento exato retornado pela consulta ({{ $isCte ? 'cte' : 'nfe' }}_clearance). Todos os campos exibidos acima vêm daqui, sem alteração.</p>
                    <pre class="text-[10px] font-mono text-gray-700 bg-gray-50 border border-gray-200 rounded p-3 overflow-x-auto max-h-96">{{ $rawJson }}</pre>
                </div>
            </details>
        @endif
    @else
        <div class="px-4 py-8 text-center">
            <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-gray-700">Esta nota ainda não foi verificada na Receita.</p>
            <p class="text-[11px] text-gray-500 mt-1">Confirme a situação oficial (autorizada, cancelada, denegada…) no Clearance.</p>
            @if($chaveConsulta)
                <a href="/app/clearance/notas?busca={{ $chaveConsulta }}" data-link class="mt-3 inline-flex items-center gap-1.5 px-3 py-2 bg-gray-800 text-white hover:bg-gray-700 rounded text-xs font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16 10a6 6 0 11-12 0 6 6 0 0112 0z"></path></svg>
                    Verificar esta nota no Clearance
                </a>
            @endif
        </div>
    @endif
</div>
