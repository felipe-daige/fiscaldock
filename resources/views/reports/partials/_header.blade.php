<div style="position:fixed; top:-74px; left:0; width:100%;">
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:210px; vertical-align:middle; border:none; white-space:nowrap;">
                <img src="{{ \App\Support\PdfReport::logoDataUri() }}" alt="FiscalDock" style="height:26px; vertical-align:middle;">
                <span style="vertical-align:middle; margin-left:7px; font-size:16px; font-weight:bold; color:#1e4679; letter-spacing:.01em;">FiscalDock</span>
            </td>
            <td style="vertical-align:middle; border:none;">
                <span style="font-size:13px; font-weight:bold; color:#1f2937; text-transform:uppercase; letter-spacing:.04em;">@yield('titulo', 'Relatório')</span>
            </td>
            <td style="vertical-align:middle; text-align:right; border:none; color:#6b7280; font-size:8px;">
                @if(!empty($pdfExecutivo))
                    <div style="display:inline-block; background:#1e4679; color:#fff; padding:1px 7px; border-radius:3px; font-size:7px; font-weight:bold; text-transform:uppercase; letter-spacing:.1em; margin-bottom:2px;">Relatório Executivo</div>
                @endif
                @yield('meta')
                <div>gerado em {{ now()->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>
    {{-- Régua: dupla e mais grossa no executivo (Profissional+), simples nos demais. --}}
    <div style="height:{{ !empty($pdfExecutivo) ? '2.5px' : '1.5px' }}; background:#1f2937; margin-top:5px;"></div>
    @if(!empty($pdfExecutivo))
        <div style="height:1px; background:#1e4679; margin-top:1.5px;"></div>
    @endif
</div>
