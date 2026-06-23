<table style="width:100%;border-collapse:collapse;font-size:8px;">
    <thead>
        <tr>
            @foreach ($sec['colunas'] as $col)
                <th style="border:1px solid #e5e7eb;background:#f3f4f6;padding:3px 5px;text-align:left;color:#374151;">{{ $col }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($sec['linhas'] as $linha)
            <tr>
                @foreach (array_values($linha) as $cel)
                    <td style="border:1px solid #e5e7eb;padding:3px 5px;color:#111827;">{{ $cel }}</td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="{{ max(1, count($sec['colunas'])) }}" style="border:1px solid #e5e7eb;padding:6px;color:#9ca3af;text-align:center;">Sem dados no período</td></tr>
        @endforelse
    </tbody>
</table>
