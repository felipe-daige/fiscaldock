@php
    $modoPartes = $modoPartes ?? \App\Support\DesignSystem\ParteOperacaoPresenter::MODO_COMPLETO;
    $emitenteEhCliente = $nota->lado_dono === 'emit';
    $destinatarioEhCliente = $nota->lado_dono === 'dest';
    $emitentePerfil = $emitenteEhCliente
        ? ($nota->emitCliente ?: $nota->cliente)
        : $nota->emitente;
    $destinatarioPerfil = $destinatarioEhCliente
        ? ($nota->destCliente ?: $nota->cliente)
        : $nota->destinatario;
    $papelDaParte = static function (bool $ehCliente, $perfil): array {
        if (! $ehCliente) {
            return ['label' => 'Participante', 'hex' => '#6b7280'];
        }

        return [
            'label' => $perfil instanceof \App\Models\Cliente && $perfil->is_empresa_propria
                ? 'Empresa própria'
                : 'Cliente',
            'hex' => '#1d4ed8',
        ];
    };
    $papelEmitente = $papelDaParte($emitenteEhCliente, $emitentePerfil);
    $papelDestinatario = $papelDaParte($destinatarioEhCliente, $destinatarioPerfil);
    $partesDocumento = [
        \App\Support\DesignSystem\ParteOperacaoPresenter::card(
            $emitentePerfil,
            'Emitente',
            fallback: [
                'nome' => $nota->emit_razao_social,
                'documento' => $nota->emit_documento,
                'inscricao_estadual' => $nota->emit_ie,
                'uf' => $nota->emit_uf,
            ],
            modo: $modoPartes,
            papel: $papelEmitente['label'],
            papelHex: $papelEmitente['hex'],
            descricao: $emitenteEhCliente
                ? 'Empresa da carteira vinculada ao documento'
                : 'Contraparte identificada no documento',
        ),
        \App\Support\DesignSystem\ParteOperacaoPresenter::card(
            $destinatarioPerfil,
            'Destinatário',
            fallback: [
                'nome' => $nota->dest_razao_social,
                'documento' => $nota->dest_documento,
                'inscricao_estadual' => $nota->dest_ie,
                'uf' => $nota->dest_uf,
            ],
            modo: $modoPartes,
            papel: $papelDestinatario['label'],
            papelHex: $papelDestinatario['hex'],
            descricao: $destinatarioEhCliente
                ? 'Empresa da carteira vinculada ao documento'
                : 'Contraparte identificada no documento',
        ),
    ];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 items-stretch gap-4 {{ $wrapperClass ?? '' }}" data-partes-documento>
    @foreach($partesDocumento as $parte)
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
    @endforeach
</div>
