{{--
    Gráfico de COLUNAS verticais agrupadas para dompdf (sem JS, sem SVG).

    Props:
      $colunas: [['label' => '01/26', 'series' => [['valor' => 1.2, 'hex' => '#2563eb'], ...]], ...]
      $legenda: [['label' => 'Entradas', 'hex' => '#b45309'], ...]
      $altura:  altura útil de CADA semi-eixo, em px (default 78)

    Suporta valores NEGATIVOS: eixo zero no meio, positivo acima, negativo abaixo.
    A semi-área negativa só é renderizada se existir algum valor < 0 (senão o gráfico
    ficaria com metade em branco). Alturas em px inteiro — dompdf arredonda % de div
    aninhada de forma inconsistente.
--}}
@php
    $altura = $altura ?? 78;
    $valores = collect($colunas)->flatMap(fn ($c) => array_column($c['series'], 'valor'));
    $maxAbs = (float) max(0.000001, $valores->map(fn ($v) => abs((float) $v))->max() ?? 0);
    $temNegativo = $valores->contains(fn ($v) => (float) $v < 0);
    $alturaNeg = $temNegativo ? (int) round($altura * 0.55) : 0;
    $px = fn (float $v, int $espaco) => (int) round(min(1, abs($v) / $maxAbs) * $espaco);
@endphp

@if (! empty($colunas))
    <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
        <tr>
            @foreach ($colunas as $col)
                <td style="vertical-align:bottom;padding:0 2px;">
                    {{-- semi-eixo positivo --}}
                    <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
                        <tr>
                            @foreach ($col['series'] as $s)
                                @php $h = ((float) $s['valor']) > 0 ? $px((float) $s['valor'], $altura) : 0; @endphp
                                <td style="vertical-align:bottom;padding:0 1px;">
                                    <div style="height:{{ $altura - $h }}px;font-size:0;line-height:0;">&nbsp;</div>
                                    <div style="height:{{ max($h, ((float) $s['valor']) > 0 ? 1 : 0) }}px;background-color:{{ $s['hex'] }};font-size:0;line-height:0;"></div>
                                </td>
                            @endforeach
                        </tr>
                    </table>

                    {{-- eixo zero --}}
                    <div style="height:1px;background:#9ca3af;font-size:0;line-height:0;"></div>

                    {{-- semi-eixo negativo --}}
                    @if ($temNegativo)
                        <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
                            <tr>
                                @foreach ($col['series'] as $s)
                                    @php $h = ((float) $s['valor']) < 0 ? $px((float) $s['valor'], $alturaNeg) : 0; @endphp
                                    <td style="vertical-align:top;padding:0 1px;">
                                        <div style="height:{{ max($h, ((float) $s['valor']) < 0 ? 1 : 0) }}px;background-color:{{ $s['hex'] }};font-size:0;line-height:0;"></div>
                                        <div style="height:{{ $alturaNeg - $h }}px;font-size:0;line-height:0;">&nbsp;</div>
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    @endif

                    <div style="text-align:center;font-size:6.5px;color:#6b7280;padding-top:2px;white-space:nowrap;">{{ $col['label'] }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    @if (! empty($legenda))
        <table style="width:100%;border-collapse:collapse;margin-top:5px;">
            <tr>
                @foreach ($legenda as $l)
                    <td style="width:1%;padding:0 3px 0 0;vertical-align:middle;">
                        <div style="width:8px;height:8px;background-color:{{ $l['hex'] }};font-size:0;line-height:0;"></div>
                    </td>
                    <td style="padding:0 12px 0 0;font-size:7.5px;color:#374151;vertical-align:middle;white-space:nowrap;">{{ $l['label'] }}</td>
                @endforeach
                <td>&nbsp;</td>
            </tr>
        </table>
    @endif
@else
    <span style="font-size:8px;color:#9ca3af;">Sem série no período.</span>
@endif
