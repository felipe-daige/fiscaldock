<table style="width:100%;border-collapse:collapse;">
    <tr>
        @foreach($itens as $kpi)
            <td style="padding:6px 10px;vertical-align:top;{{ $loop->last ? '' : 'border-right:1px solid #e5e7eb;' }}">
                <div style="font-size:7px;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">{{ $kpi['label'] }}</div>
                <div style="font-size:13px;font-weight:bold;color:#111827;">{{ $kpi['valor'] }}</div>
            </td>
        @endforeach
    </tr>
</table>
