{{-- Bloco reusável do dossiê: resumo + infográficos + listas de movimentação + detalhamento.
     Fonte única usada pelos PDFs de dossiê standalone (participante/cliente) e pela seção
     "Dossiês" anexada ao PDF do BI. Consome do escopo: $participante (Participante OU Cliente
     no slot participante), $score, $movimentacao, $consulta, $top_produtos, $top_cfops. --}}
@include('reports.dossie._resumo', ['participante' => $participante])
@include('reports.dossie._infograficos', ['participante' => $participante])
@include('reports.dossie._listas-movimentacao', ['top_produtos' => $top_produtos ?? [], 'top_cfops' => $top_cfops ?? []])
@include('reports.dossie._detalhamento', ['participante' => $participante])
