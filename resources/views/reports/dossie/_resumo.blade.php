@php
    $k = $movimentacao['kpis'];
    $fmt = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $docNum = preg_replace('/\D/', '', (string) $participante->documento);
    $isCpf = strlen($docNum) === 11;
    $classificacaoScore = $score['classificacao'] ?? 'nao_avaliado';
    $scoreHex = \App\Support\Reports\ReportTheme::riscoHex($classificacaoScore);
    $scoreInconclusivo = $classificacaoScore === 'inconclusivo';
    $scoreNaoAvaliado = ($score['score_total'] ?? null) === null && in_array($classificacaoScore, ['nao_avaliado', 'inconclusivo'], true);
    $classificacaoLabel = app(\App\Services\RiskScoreService::class)->getLabelClassificacao($classificacaoScore);
    $scoreTotalBarra = max(0, min(100, (int) ($score['score_total'] ?? 0)));
    $scoreBarraPct = $scoreTotalBarra === 0 ? 100 : $scoreTotalBarra;
@endphp
@php
    $docLabel = $isCpf ? 'CPF' : 'CNPJ';
    $cadastro = collect($consulta['blocos'] ?? [])->firstWhere('chave', 'cadastro');
    $cadastroItens = collect($cadastro['itens'] ?? []);
    $valorCadastro = function (string $label) use ($cadastroItens): ?string {
        $valor = trim((string) ($cadastroItens->firstWhere('label', $label)['valor'] ?? ''));

        return $valor !== '' && $valor !== '—' ? $valor : null;
    };
    $situacaoCadastro = trim((string) ($participante->situacao_cadastral ?? '')) ?: $valorCadastro('Situação cadastral') ?: 'Não consultada';
    $regimeTributario = trim((string) ($participante->regime_tributario ?? '')) ?: $valorCadastro('Regime tributário') ?: 'Não consultado';
@endphp
<div class="secao">
    <div class="secao-header">Identificação</div>
    <div class="secao-body">
        <table class="ident">
            <tr>
                <td><div class="ident-k">{{ $isCpf ? 'Nome' : 'Razão Social' }}</div><div class="ident-v">{{ $participante->razao_social ?: '—' }}</div></td>
                <td>
                    @if($isCpf)
                        <div class="ident-k">Tipo de pessoa</div>
                        <div class="ident-v"><span class="badge" style="background-color: #374151">Pessoa física</span></div>
                    @else
                        <div class="ident-k">Situação Cadastral</div>
                        <div class="ident-v"><span class="badge" style="background-color: {{ \App\Support\Reports\ReportTheme::statusHex($situacaoCadastro) }}">{{ $situacaoCadastro }}</span></div>
                    @endif
                </td>
            </tr>
            <tr>
                <td><div class="ident-k">{{ $docLabel }}</div><div class="ident-v mono">{{ $participante->documento }}</div></td>
                <td>
                    @if($isCpf)
                        <div class="ident-k">Avaliação de crédito</div>
                        <div class="ident-v"><span class="badge" style="background-color: #6b7280">Não avaliada</span></div>
                    @else
                        <div class="ident-k">Regime Tributário</div>
                        <div class="ident-v"><span class="badge" style="background-color: {{ \App\Support\Reports\ReportTheme::regimeHex($regimeTributario) }}">{{ $regimeTributario }}</span></div>
                    @endif
                </td>
            </tr>
            <tr>
                <td><div class="ident-k">UF</div><div class="ident-v">{{ $participante->uf ?: '—' }}</div></td>
                <td></td>
            </tr>
        </table>
    </div>
</div>

<div class="secao">
    <div class="secao-header">{{ $isCpf ? 'Risco de Crédito (CPF)' : 'Regularidade & Score' }}</div>
    <div class="secao-body">
        <table class="grid2"><tr>
            <td>
                @if($isCpf)
                    <span style="font-size:8px;color:#4b5563;line-height:1.45;">{{ $score['mensagem'] ?? \App\Services\Risk\RiscoCreditoCpfService::MENSAGEM_NAO_AVALIADO }}</span>
                @elseif($consulta['tem'])
                    @foreach(array_slice($consulta['blocos'], 0, 6) as $b)
                        @if(!empty($b['badge']))
                            <span class="badge" style="background-color: {{ $b['badge']['hex'] }}; display:inline-block; margin:0 0 3px 0;">{{ $b['titulo'] }}: {{ $b['badge']['label'] }}</span><br>

                        @endif
                    @endforeach
                @else
                    <span style="font-size:8px;color:#9ca3af;">Sem consulta de certidões para este participante.</span>
                @endif
            </td>
            <td>
                <div class="kpi"><table><tr>
                    <td><div class="lbl">{{ $isCpf ? 'Score de Crédito' : 'Score Fiscal' }}</div><div class="val" style="color: {{ $scoreHex }}">{{ $scoreNaoAvaliado ? '—' : $score['score_total'] }}</div></td>
                    <td><div class="lbl">Classificação</div><div class="val">{{ $isCpf ? 'Não avaliado' : $classificacaoLabel }}</div></td>
                </tr></table></div>
                @if($isCpf)
                    <div style="margin-top:4px;font-size:7.5px;color:#6b7280;">A movimentação abaixo é evidência comercial e não foi convertida em nota de crédito.</div>
                @elseif($scoreInconclusivo)
                    <div style="margin-top:4px;font-size:7.5px;color:#6b7280;">Cobertura insuficiente — score conclusivo exige CND Federal + 2 certidões avaliadas.</div>
                @elseif(! $scoreNaoAvaliado)
                    {{-- Barra = intensidade do risco: regular (0) cheia verde; crítico (100) cheia vermelha. --}}
                    <div class="score-bar" style="margin-top:4px;"><div style="background-color:{{ $scoreHex }};width:{{ $scoreBarraPct }}%;height:14px;"></div></div>
                @endif
                @unless($isCpf)
                    <div style="margin-top:8px;">
                        @include('reports.partials._score-detalhamento', ['detalhamento' => $score['detalhamento'] ?? []])
                    </div>
                @endunless
            </td>
        </tr></table>
    </div>
</div>

<div class="secao">
    <div class="secao-header">Movimentações (resumo)</div>
    @include('reports.partials._kpi-strip', ['compacto' => true, 'itens' => [
        ['label' => 'Total Notas', 'valor' => $k['total_notas']],
        ['label' => 'Valor Movimentado', 'valor' => $fmt($k['valor_movimentado'])],
        ['label' => 'Entradas', 'valor' => $k['entradas_qtd'].' · '.$fmt($k['entradas_valor'])],
        ['label' => 'Saídas', 'valor' => $k['saidas_qtd'].' · '.$fmt($k['saidas_valor'])],
        ['label' => 'Período', 'valor' => ($k['periodo_inicio'] ?? '—').' a '.($k['periodo_fim'] ?? '—')],
    ]])
</div>
