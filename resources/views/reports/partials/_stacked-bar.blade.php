{{--
    Barra empilhada 100% + legenda, para dompdf.

    Props:
      $itens: [['label' => 'NF-e', 'pct' => 62.4, 'valor' => 'R$ 1.234,56', 'hex' => '#2563eb'], ...]

    Uma <table> de uma linha onde cada <td> tem width = pct% — dompdf respeita larguras
    percentuais de célula de forma estável (divs float/inline-block, não). Fatias < 2%
    são agrupadas visualmente em "Outros" para não virarem faixas de 0px.
--}}
@php
    $itens = collect($itens)->filter(fn ($i) => (float) $i['pct'] > 0)->values();
    $grandes = $itens->filter(fn ($i) => (float) $i['pct'] >= 2)->values();
    $pequenos = $itens->filter(fn ($i) => (float) $i['pct'] < 2)->values();

    $fatias = $grandes->all();
    if ($pequenos->isNotEmpty()) {
        $fatias[] = [
            'label' => 'Outros ('.$pequenos->count().')',
            'pct' => round($pequenos->sum(fn ($i) => (float) $i['pct']), 1),
            'valor' => '',
            'hex' => '#d1d5db',
        ];
    }
@endphp

@if (! empty($fatias))
    <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
        <tr>
            @foreach ($fatias as $f)
                <td style="width:{{ max(1, (float) $f['pct']) }}%;height:14px;background-color:{{ $f['hex'] }};font-size:0;line-height:0;"></td>
            @endforeach
        </tr>
    </table>

    <table style="width:100%;border-collapse:collapse;margin-top:6px;">
        @foreach (array_chunk($fatias, 3) as $linha)
            <tr>
                @foreach ($linha as $f)
                    <td style="width:2%;padding:2px 3px 2px 0;vertical-align:middle;">
                        <div style="width:8px;height:8px;background-color:{{ $f['hex'] }};font-size:0;line-height:0;"></div>
                    </td>
                    <td style="width:31.3%;padding:2px 10px 2px 0;font-size:7.5px;color:#374151;vertical-align:middle;">
                        {{ $f['label'] }}
                        <span style="color:#9ca3af;">· {{ $f['pct'] }}%</span>
                        @if (! empty($f['valor']))
                            <span style="color:#111827;font-weight:bold;"> {{ $f['valor'] }}</span>
                        @endif
                    </td>
                @endforeach
                @for ($i = count($linha); $i < 3; $i++)
                    <td colspan="2">&nbsp;</td>
                @endfor
            </tr>
        @endforeach
    </table>
@else
    <span style="font-size:8px;color:#9ca3af;">Sem distribuição no período.</span>
@endif
