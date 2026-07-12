{{--
    View RAIZ de toda notification por e-mail (sobrescreve `notifications::email`).

    Dois motivos pra existir customizada:

    1. É o único ponto do render que enxerga `$message` (o Mailer injeta em
       `$data['message']`; os componentes anônimos do tema — header/footer — NÃO o
       recebem). A logo é embutida por CID aqui e desce como prop até o header. Sem isso
       ela teria que morar numa URL pública, e `public/binary_files/` não é bind-mount
       (vem da imagem — sumiria no primeiro `--force-recreate`).

    2. A view nativa imprime `{{ $line }}` (escapado). Aqui, linha que for `HtmlString`
       sai crua — é o que habilita os blocos ricos de `App\Support\Mail\Blocos` (chip de
       severidade, tabela de dados, KPIs). Texto normal continua escapado.
--}}
@php
    $logo = \App\Support\Mail\Blocos::logoSrc($message ?? null);
@endphp
<x-mail::message :logo="$logo">
{{-- Etiqueta (kicker) — vem ANTES do título, dá o contexto do e-mail antes da 1ª frase.
     Chega por `$mail->viewData['etiqueta']`, que o `MailMessage::data()` mescla aqui. --}}
@isset($etiqueta)
{!! \App\Support\Mail\Blocos::etiqueta($etiqueta, $etiquetaCor ?? \App\Support\Mail\Blocos::NAVY) !!}
@endisset

{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# Atenção
@else
# Olá!
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{!! $line instanceof \Illuminate\Support\HtmlString ? $line : e($line) !!}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{!! $line instanceof \Illuminate\Support\HtmlString ? $line : e($line) !!}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{!! $salutation instanceof \Illuminate\Support\HtmlString ? $salutation : e($salutation) !!}
@else
Equipe {{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
Se o botão "{{ $actionText }}" não funcionar, copie e cole este endereço no seu navegador:
<span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
