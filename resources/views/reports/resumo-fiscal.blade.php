@extends('reports.layout')

@php
    use App\Support\Reports\ReportTheme;

    $fmtRs = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $fmtPct = fn ($v) => number_format((float) $v, 1, ',', '.').'%';
    $fmtData = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d/m/Y') : '—';

    // Cor da seta de delta: subir imposto a recolher é ruim (vermelho), descer é bom (verde).
    $deltaCor = fn ($pct) => $pct > 0 ? ReportTheme::IRREGULAR : ($pct < 0 ? ReportTheme::OK : ReportTheme::NEUTRO);
    $deltaSeta = fn ($pct) => $pct > 0 ? '▲' : ($pct < 0 ? '▼' : '—');

    $sevMeta = [
        'alta' => ['label' => 'Alta', 'hex' => ReportTheme::IRREGULAR],
        'media' => ['label' => 'Média', 'hex' => ReportTheme::ALERTA],
        'info' => ['label' => 'Info', 'hex' => ReportTheme::NEUTRO],
    ];
    // Flag canônico (verde/amarelo/vermelho) → cor do design system dos relatórios.
    $flagHex = fn ($f) => ['verde' => ReportTheme::OK, 'amarelo' => ReportTheme::ALERTA, 'vermelho' => ReportTheme::IRREGULAR][$f] ?? ReportTheme::NEUTRO;

    $nomeCliente = $cliente->razao_social ?: $cliente->nome;
@endphp

@section('titulo', 'Fechamento Fiscal — '.$nomeCliente)

@section('meta')
    <div>{{ $competenciaLabel }}</div>
    <div class="mono">{{ $cliente->documento_formatado }}</div>
@endsection

@section('rodape_hash', $hashDoc)

@section('conteudo')

    {{-- Identificação --}}
    <div class="card-slate" style="margin-bottom:14px;">
        <table class="ident">
            <tr>
                <td>
                    <div class="ident-k">Cliente</div>
                    <div class="ident-v">{{ $nomeCliente }}</div>
                </td>
                <td>
                    <div class="ident-k">Documento</div>
                    <div class="ident-v mono">{{ $cliente->documento_formatado }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="ident-k">Competência</div>
                    <div class="ident-v">{{ $competenciaLabel }}</div>
                </td>
                <td>
                    <div class="ident-k">Emitido em</div>
                    <div class="ident-v">{{ $geradoEm->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Visão do Mês ── --}}
    <div class="secao">
        <div class="secao-header">Visão do Mês</div>
        <div class="secao-body">
            @if(empty($resumo['tem_dados']))
                <p class="muted">Sem dados de apuração para esta competência.</p>
            @else
                @php
                    $kpis = $resumo['kpis'];
                    $cards = [
                        ['ICMS a recolher', $kpis['icms_a_recolher']],
                        ['PIS a recolher', $kpis['pis_a_recolher']],
                        ['COFINS a recolher', $kpis['cofins_a_recolher']],
                        ['Retenções compensáveis', $kpis['retencoes_compensaveis']],
                        ['Saldo líquido', $kpis['saldo_liquido']],
                    ];
                @endphp
                <table class="table">
                    <thead>
                        <tr>
                            <th>Indicador</th>
                            <th class="right" style="width:120px;">Valor</th>
                            <th class="right" style="width:110px;">Δ vs anterior</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cards as [$label, $kpi])
                            @php $pct = $kpi['delta']['percentual'] ?? 0; @endphp
                            <tr>
                                <td>
                                    {{ $label }}
                                    @if($label === 'Saldo líquido' && ($kpi['parcial'] ?? false))
                                        <span class="muted small">(competência incompleta — só uma EFD presente)</span>
                                    @endif
                                </td>
                                <td class="right mono">{{ $fmtRs($kpi['valor']) }}</td>
                                <td class="right" style="color:{{ $deltaCor($pct) }};">{{ $deltaSeta($pct) }} {{ $fmtPct(abs($pct)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── A Recolher & Vencimentos ── --}}
    <div class="secao">
        <div class="secao-header">
            A Recolher &amp; Vencimentos
            <span class="meta">Total {{ $fmtRs($aRecolher['total']) }}</span>
        </div>
        <div class="secao-body">
            @if(empty($aRecolher['linhas']))
                <p class="muted">Nada a recolher nesta competência.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tributo</th>
                            <th class="right" style="width:110px;">Valor</th>
                            <th style="width:130px;">Vencimento</th>
                            <th style="width:60px;">Fonte</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($aRecolher['linhas'] as $l)
                            <tr>
                                <td>{{ $l['tributo'] }}</td>
                                <td class="right mono">{{ $fmtRs($l['valor']) }}</td>
                                <td>
                                    {{ $fmtData($l['vencimento']) }}
                                    @if($l['vencimento_estimado'])
                                        <span class="badge" style="background-color:{{ ReportTheme::ALERTA }};">estimado</span>
                                    @endif
                                </td>
                                <td class="mono muted">{{ $l['fonte'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="right" style="border-top:1.5px solid #1f2937; font-weight:bold;">Total do mês</td>
                            <td class="right mono" style="border-top:1.5px solid #1f2937; font-weight:bold;">{{ $fmtRs($aRecolher['total']) }}</td>
                            <td colspan="2" style="border-top:1.5px solid #1f2937;"></td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

    {{-- ── Está Batendo? (cruzamentos) ── --}}
    <div class="secao">
        <div class="secao-header">Está Batendo? — Declarado × Notas</div>
        <div class="secao-body">
            @php
                $cz = $cruzamentos;
                $czIcms = $cz['icms'] ?? [];
                $czPc = $cz['pis_cofins'] ?? [];
                $czRet = $cz['retencoes'] ?? [];
                $linhasCz = [];
                if (! empty($czIcms['tem_dados'])) {
                    $linhasCz[] = ['ICMS débitos (E110 × C190)', $czIcms['declarado_debito'] ?? 0, $czIcms['notas_debito'] ?? 0, $czIcms['divergencia_debito_pct'] ?? 0, $czIcms['status_debito'] ?? 'verde'];
                    $linhasCz[] = ['ICMS créditos (E110 × C190)', $czIcms['declarado_credito'] ?? 0, $czIcms['notas_credito'] ?? 0, $czIcms['divergencia_credito_pct'] ?? 0, $czIcms['status_credito'] ?? 'verde'];
                }
                if (! empty($czPc['pis_declarado']) || ! empty($czPc['pis_notas'])) {
                    $linhasCz[] = ['PIS a recolher (M200 × notas)', $czPc['pis_declarado'] ?? 0, $czPc['pis_notas'] ?? 0, $czPc['pis_divergencia_pct'] ?? 0, $czPc['pis_status'] ?? 'verde'];
                    $linhasCz[] = ['COFINS a recolher (M600 × notas)', $czPc['cofins_declarado'] ?? 0, $czPc['cofins_notas'] ?? 0, $czPc['cofins_divergencia_pct'] ?? 0, $czPc['cofins_status'] ?? 'verde'];
                }
            @endphp
            @if(empty($linhasCz) && empty($czRet['tem_dados']))
                <p class="muted">Sem dados de cruzamento para esta competência.</p>
            @else
                @if(! empty($linhasCz))
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cruzamento</th>
                                <th class="right" style="width:110px;">Declarado</th>
                                <th class="right" style="width:110px;">Notas</th>
                                <th class="right" style="width:70px;">Div. %</th>
                                <th class="center" style="width:70px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($linhasCz as [$label, $decl, $notas, $pct, $flag])
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="right mono">{{ $fmtRs($decl) }}</td>
                                    <td class="right mono">{{ $fmtRs($notas) }}</td>
                                    <td class="right">{{ $fmtPct($pct) }}</td>
                                    <td class="center">
                                        <span class="badge" style="background-color:{{ $flagHex($flag) }};">{{ $flag === 'verde' ? 'OK' : ($flag === 'amarelo' ? 'Atenção' : 'Diverge') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
                @if(! empty($czRet['tem_dados']))
                    <table class="table" style="margin-top:8px;">
                        <thead>
                            <tr>
                                <th>Retenções na fonte</th>
                                <th class="right" style="width:110px;">Retido (F600)</th>
                                <th class="right" style="width:110px;">Deduzido (M)</th>
                                <th class="right" style="width:110px;">Não compensado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>PIS/COFINS retido × deduzido na apuração</td>
                                <td class="right mono">{{ $fmtRs($czRet['total_retido']) }}</td>
                                <td class="right mono">{{ $fmtRs($czRet['deduzido_apuracao']) }}</td>
                                <td class="right mono" style="color:{{ ($czRet['nao_compensado'] ?? 0) > 0.01 ? ReportTheme::ALERTA : ReportTheme::OK }};">{{ $fmtRs($czRet['nao_compensado']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            @endif
        </div>
    </div>

    {{-- ── Alertas ── --}}
    @php $listaAlertas = $alertas['alertas'] ?? []; @endphp
    @if(! empty($listaAlertas))
        <div class="secao">
            <div class="secao-header">
                Alertas
                <span class="meta">{{ $alertas['resumo']['total'] }} {{ $alertas['resumo']['total'] === 1 ? 'alerta' : 'alertas' }}</span>
            </div>
            <div class="secao-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:60px;">Sev.</th>
                            <th style="width:80px;">Categoria</th>
                            <th>Alerta</th>
                            <th class="right" style="width:100px;">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listaAlertas as $a)
                            @php $sev = $sevMeta[$a['severidade']] ?? ['label' => ucfirst((string) $a['severidade']), 'hex' => ReportTheme::NEUTRO]; @endphp
                            <tr>
                                <td><span class="badge" style="background-color:{{ $sev['hex'] }};">{{ $sev['label'] }}</span></td>
                                <td class="muted">{{ $a['categoria'] }}</td>
                                <td>
                                    <div style="font-weight:600; color:#111827;">{{ $a['titulo'] }}</div>
                                    @if(! empty($a['descricao']))
                                        <div class="muted small" style="margin-top:2px;">{{ $a['descricao'] }}</div>
                                    @endif
                                </td>
                                <td class="right mono">{{ isset($a['valor']) ? $fmtRs($a['valor']) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ── Espelho ICMS/IPI ── --}}
    <div class="secao">
        <div class="secao-header">Espelho ICMS/IPI</div>
        <div class="secao-body">
            @if(empty($icms['tem_dados']))
                <p class="muted">Sem dados de apuração ICMS/IPI para esta competência.</p>
            @else
                @php
                    $ip = $icms['icms_proprio'];
                    $linhasIcms = [
                        ['Débitos', $ip['tot_debitos']],
                        ['Ajustes de débito', $ip['tot_aj_debitos']],
                        ['Estornos de crédito', $ip['estornos_credito']],
                        ['Créditos', $ip['tot_creditos']],
                        ['Ajustes de crédito', $ip['tot_aj_creditos']],
                        ['Saldo credor anterior', $ip['sld_credor_ant']],
                        ['Deduções', $ip['tot_deducoes']],
                        ['ICMS a recolher', $ip['a_recolher']],
                        ['Saldo credor a transportar', $ip['sld_credor_transportar']],
                        ['Débitos especiais', $ip['deb_especiais']],
                    ];
                @endphp
                <table class="table">
                    <thead>
                        <tr><th>Linha da apuração (E110)</th><th class="right" style="width:130px;">Valor</th></tr>
                    </thead>
                    <tbody>
                        @foreach($linhasIcms as [$label, $val])
                            <tr>
                                <td style="{{ $label === 'ICMS a recolher' ? 'font-weight:bold;' : '' }}">{{ $label }}</td>
                                <td class="right mono" style="{{ $label === 'ICMS a recolher' ? 'font-weight:bold;' : '' }}">{{ $fmtRs($val) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if(! empty($icms['tem_st']) && ! empty($icms['icms_st']))
                    <p class="muted small" style="margin-top:6px;">ICMS-ST a recolher ({{ $icms['icms_st']['uf'] ?? '' }}): <strong>{{ $fmtRs($icms['icms_st']['icms_recolher']) }}</strong></p>
                @endif
                <p class="muted small" style="margin-top:4px;">Período: {{ $icms['periodo_inicio'] }} a {{ $icms['periodo_fim'] }}</p>
            @endif
        </div>
    </div>

    {{-- ── Espelho PIS/COFINS ── --}}
    <div class="secao">
        <div class="secao-header">Espelho PIS/COFINS</div>
        <div class="secao-body">
            @if(empty($pisCofins['tem_dados']))
                <p class="muted">Sem dados de apuração PIS/COFINS para esta competência.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Linha da apuração (bloco M)</th>
                            <th class="right" style="width:120px;">PIS</th>
                            <th class="right" style="width:120px;">COFINS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $p = $pisCofins['pis']; $c = $pisCofins['cofins'];
                            $linhasPc = [
                                ['Não cumulativo', 'nao_cumulativo'],
                                ['Crédito descontado', 'credito_descontado'],
                                ['Devida (não cumulativo)', 'nc_devida'],
                                ['Retenção', 'retencao_nc'],
                                ['A recolher (não cumulativo)', 'nc_recolher'],
                                ['Cumulativo', 'cumulativo'],
                                ['A recolher (cumulativo)', 'cum_recolher'],
                            ];
                        @endphp
                        @foreach($linhasPc as [$label, $key])
                            <tr>
                                <td>{{ $label }}</td>
                                <td class="right mono">{{ $fmtRs($p[$key] ?? 0) }}</td>
                                <td class="right mono">{{ $fmtRs($c[$key] ?? 0) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="font-weight:bold; border-top:1.5px solid #1f2937;">Total a recolher</td>
                            <td class="right mono" style="font-weight:bold; border-top:1.5px solid #1f2937;">{{ $fmtRs($p['total_recolher']) }}</td>
                            <td class="right mono" style="font-weight:bold; border-top:1.5px solid #1f2937;">{{ $fmtRs($c['total_recolher']) }}</td>
                        </tr>
                    </tbody>
                </table>
                @if(! empty($pisCofins['regime']))
                    <p class="muted small" style="margin-top:4px;">Regime: {{ $pisCofins['regime'] }}</p>
                @endif
            @endif
        </div>
    </div>

    {{-- ── Retenções ── --}}
    <div class="secao">
        <div class="secao-header">
            Retenções na Fonte
            @if(! empty($retencoes['tem_dados']))
                <span class="meta">{{ $fmtRs($retencoes['kpis']['total_retido']) }} · {{ $retencoes['kpis']['qtd_retencoes'] }} registros</span>
            @endif
        </div>
        <div class="secao-body">
            @if(empty($retencoes['tem_dados']))
                <p class="muted">Sem retenções na fonte nesta competência.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:70px;">Data</th>
                            <th>Documento</th>
                            <th>Natureza</th>
                            <th class="right" style="width:80px;">PIS</th>
                            <th class="right" style="width:80px;">COFINS</th>
                            <th class="right" style="width:80px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($retencoes['retencoes'] as $r)
                            <tr>
                                <td>{{ $r['data'] }}</td>
                                <td class="mono">{{ $r['documento'] }}</td>
                                <td>{{ $r['natureza'] }}</td>
                                <td class="right mono">{{ $fmtRs($r['valor_pis']) }}</td>
                                <td class="right mono">{{ $fmtRs($r['valor_cofins']) }}</td>
                                <td class="right mono">{{ $fmtRs($r['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="right" style="border-top:1.5px solid #1f2937; font-weight:bold;">Total retido</td>
                            <td class="right mono" style="border-top:1.5px solid #1f2937; font-weight:bold;">{{ $fmtRs($retencoes['kpis']['total_retido']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>
    </div>

@endsection
