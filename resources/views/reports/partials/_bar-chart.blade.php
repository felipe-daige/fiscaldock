<table style="width:100%;border-collapse:collapse;">
    @foreach($itens as $item)
        <tr>
            <td style="width:28%;padding:3px 8px 3px 0;font-size:8px;color:#374151;vertical-align:middle;">{{ $item['label'] }}</td>
            <td style="padding:3px 0;vertical-align:middle;">
                <div style="background:#f3f4f6;width:100%;height:10px;">
                    <div style="background-color:{{ $item['hex'] }};width:{{ max(0, min(100, (int) $item['pct'])) }}%;height:10px;"></div>
                </div>
            </td>
            <td style="width:22%;padding:3px 0 3px 8px;font-size:8px;color:#111827;text-align:right;vertical-align:middle;font-weight:bold;">{{ $item['valor'] }}</td>
        </tr>
    @endforeach
</table>
