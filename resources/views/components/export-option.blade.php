@props([
    'format' => 'pdf',          // pdf | xlsx | csv
    'descricao' => null,        // linha 1: o que vem DENTRO do arquivo (específico da tela)
    'diferenca' => null,        // linha 2: o que o FORMATO é (default por formato, abaixo)
    'modalId' => null,          // id do modal de formato (para fechá-lo ao acionar)
    'overlay' => 'download-overlay',

    // Modo A — download GET (iframe + cookie), igual ao componente download-button
    'path' => null,
    'query' => '',
    'clienteSelect' => 'filtro-cliente',
    'extras' => [],

    // Modo B — download POST com ids[] da seleção (Clientes/Participantes)
    'postPath' => null,
    'idsFn' => null,            // nome de função global que devolve array de ids
    'vazioMsg' => 'Selecione ao menos um item para exportar.',

    // Modo C — encadeia num segundo modal de opções (ex.: escopo do PDF no BI)
    'opensModal' => null,
])

@php
    // Linha de opção do modal "Exportar". Cache-robusto: onclick inline, sem JS-file
    // (mesmo padrão dos componentes download-button/modal). Três modos mutuamente exclusivos:
    // path (GET) | postPath+idsFn (POST) | opensModal (encadeia).
    //
    // Protocolo do overlay (idêntico nos dois downloads): limpa o cookie `bi_download`,
    // mostra o overlay, dispara o download num iframe oculto e faz poll da PRESENÇA do
    // cookie (o valor é criptografado pelo Laravel) — o controller o anexa via
    // SetsDownloadToken::comTokenDownload quando recebe `download_token`.
    //
    // Rótulo/diferença: XLSX e CSV são AMBOS planilhas — o rótulo diz "Planilha" nos dois
    // e a linha `diferenca` explica quando usar cada um (formatação/abas × texto puro).
    $labels = [
        'pdf' => 'PDF',
        'xlsx' => 'Planilha Excel (.xlsx)',
        'csv' => 'Planilha CSV (.csv)',
    ];
    $label = $labels[$format] ?? strtoupper((string) $format);

    // Diferença entre os formatos — default do componente (a view pode sobrescrever).
    $diferencas = [
        'pdf' => 'Documento pronto para ler, imprimir e enviar ao cliente. Não é editável.',
        'xlsx' => 'Abre no Excel/Google Sheets já formatado, com abas e valores numéricos — dá para somar, filtrar e pivotar.',
        'csv' => 'Texto puro, uma tabela só, sem formatação nem abas. Universal: abre em qualquer sistema.',
    ];
    $diferenca = $diferenca ?? ($diferencas[$format] ?? null);

    // Ícone e tinta por formato (hex inline — Tailwind v4 compila cor para oklch e falha
    // em alguns browsers; regra dura do design system DANFE).
    $tintas = ['pdf' => '#b91c1c', 'xlsx' => '#047857', 'csv' => '#1d4ed8'];
    $tinta = $tintas[$format] ?? '#374151';

    $icones = [
        // Folha com canto dobrado + linhas de texto (documento).
        'pdf' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 3v4a1 1 0 001 1h4M9 13h6M9 17h4"/>'
            .'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 8.5V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h6.5L19 8.5z"/>',
        // Grade preenchida (planilha com células/abas).
        'xlsx' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/>'
            .'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 9h16M4 15h16M10 3v18"/>',
        // Linhas de texto separadas (tabela plana, texto puro).
        'csv' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/>'
            .'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 8h8M8 12h8M8 16h5"/>',
    ];
    $iconePath = $icones[$format] ?? $icones['pdf'];

    $fecharModal = $modalId ? "document.getElementById('".addslashes($modalId)."').classList.add('hidden');" : '';

    // Trecho comum: cria iframe oculto + poll do cookie até o arquivo chegar.
    $pollJs = "var n=0;var t=setInterval(function(){n++;"
        . "if(document.cookie.indexOf('bi_download=')>-1){"
        .   "clearInterval(t);document.cookie='bi_download=; path=/; max-age=0';"
        .   "if(ov)ov.classList.add('hidden');setTimeout(function(){f.remove();},60000);"
        . "}else if(n>1040){"
        .   "clearInterval(t);if(ov)ov.classList.add('hidden');setTimeout(function(){f.remove();},60000);"
        . "}},250);";

    if ($opensModal) {
        // Modo C: fecha o modal de formato e abre o de opções. Sem overlay aqui —
        // quem baixa é o botão de dentro do segundo modal.
        $js = $fecharModal."document.getElementById('".addslashes($opensModal)."').classList.remove('hidden');";
    } elseif ($postPath) {
        // Modo B: POST com ids[]. form.target = iframe oculto → a página não navega e
        // o browser trata a resposta como download (mesmo cookie/overlay do GET).
        $js = "(function(){"
            . "var ids=(typeof window['".addslashes((string) $idsFn)."']==='function')?window['".addslashes((string) $idsFn)."']():[];"
            . "if(!ids||!ids.length){if(window.showToast)window.showToast('".addslashes($vazioMsg)."','info');return;}"
            . $fecharModal
            . "var ov=document.getElementById('".addslashes($overlay)."');"
            . "var tok='d'+Date.now()+Math.floor(Math.random()*1e6);"
            . "document.cookie='bi_download=; path=/; max-age=0';"
            . "if(ov)ov.classList.remove('hidden');"
            . "var nm='exp'+Date.now();"
            . "var f=document.createElement('iframe');f.name=nm;f.style.display='none';document.body.appendChild(f);"
            . "var fm=document.createElement('form');fm.method='POST';fm.action='".addslashes($postPath)."';fm.target=nm;fm.style.display='none';"
            . "var mt=document.querySelector('meta[name=csrf-token]');"
            . "var add=function(k,v){var i=document.createElement('input');i.type='hidden';i.name=k;i.value=v;fm.appendChild(i);};"
            . "add('_token',mt?mt.content:'');add('download_token',tok);"
            . "ids.forEach(function(id){add('ids[]',id);});"
            . "document.body.appendChild(fm);fm.submit();fm.remove();"
            . $pollJs
            . "})()";
    } else {
        // Modo A: download GET. Reaproveita a montagem de querystring do componente download-button
        // (cliente_id + meses quando os selects existem; extras por id de elemento).
        $extrasPairs = [];
        foreach ($extras as $elId => $paramName) {
            $extrasPairs[] = "['".addslashes((string) $elId)."','".addslashes((string) $paramName)."']";
        }
        $extrasJs = $extrasPairs === []
            ? ''
            : "var ex=[".implode(',', $extrasPairs)."];ex.forEach(function(a){var el=document.getElementById(a[0]);if(el)qs.push(a[1]+'='+encodeURIComponent(el.value||''));});";

        $js = "(function(){"
            . $fecharModal
            . "var ov=document.getElementById('".addslashes($overlay)."');"
            . "var c=document.getElementById('".addslashes($clienteSelect)."');"
            . "var p=document.getElementById('filtro-periodo');"
            . "var tok='d'+Date.now()+Math.floor(Math.random()*1e6);"
            . "var qs=[];"
            . ($query !== '' ? "qs.push('".addslashes($query)."');" : '')
            . "if(c)qs.push('cliente_id='+(c.value||''));"
            . "if(p)qs.push('meses='+(p.value||0));"
            . $extrasJs
            . "qs.push('download_token='+tok);"
            . "var u='".addslashes($path)."?'+qs.join('&');"
            . "document.cookie='bi_download=; path=/; max-age=0';"
            . "if(ov)ov.classList.remove('hidden');"
            . "var f=document.createElement('iframe');f.style.display='none';f.src=u;document.body.appendChild(f);"
            . $pollJs
            . "})()";
    }

    $icon = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="'.$tinta.'" viewBox="0 0 24 24" aria-hidden="true">'.$iconePath.'</svg>';
@endphp

<button type="button" data-export-option="{{ $format }}" onclick="{{ $js }}"
    {{ $attributes->merge(['class' => 'flex w-full items-start gap-3 rounded border border-gray-300 px-4 py-3 text-left transition-colors hover:bg-gray-50']) }}>
    <span class="mt-0.5 shrink-0">{!! $icon !!}</span>
    <span class="min-w-0">
        <span class="block text-sm font-semibold text-gray-900">{{ $label }}</span>
        @if ($descricao)
            <span class="block text-[12px] text-gray-600">{{ $descricao }}</span>
        @endif
        @if ($diferenca)
            <span class="mt-0.5 block text-[11px] leading-snug text-gray-400">{{ $diferenca }}</span>
        @endif
    </span>
</button>
