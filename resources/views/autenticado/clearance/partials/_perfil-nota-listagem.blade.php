@php
    $perfil = $perfil ?? null;
    $papel = $papel ?? 'Parte';
    $isCliente = $perfil instanceof \App\Models\Cliente;
    $papelEhCliente = $isCliente || mb_strtolower($papel) === 'cliente';
    $papelBadge = $isCliente && $perfil?->is_empresa_propria ? 'Empresa própria' : $papel;
    $parte = \App\Support\DesignSystem\ParteOperacaoPresenter::card(
        $perfil,
        'Perfil do '.mb_strtolower($papel),
        fallback: [
            'nome' => $nomeFallback ?? null,
            'documento' => $documentoFallback ?? null,
        ],
        modo: \App\Support\DesignSystem\ParteOperacaoPresenter::MODO_COMPACTO,
        papel: $papelBadge,
        papelHex: $papelEhCliente ? '#1d4ed8' : '#6b7280',
        descricao: $papelDocumento ?? null,
    );
@endphp

<x-parte-operacao-card
    :titulo="$parte['titulo']"
    :nome="$parte['nome']"
    :href="$parte['href']"
    :descricao="$parte['descricao']"
    :situacao="$parte['situacao']"
    :situacao-hex="$parte['situacao_hex']"
    :papel="$parte['papel']"
    :papel-hex="$parte['papel_hex']"
    :campos="$parte['campos']"
/>
