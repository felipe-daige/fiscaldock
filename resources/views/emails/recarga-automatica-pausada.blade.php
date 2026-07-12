{{-- Markdown mailable (não é Notification) — a view raiz é esta, e é aqui que $message
     existe pro CID da logo. Mesmo tema/marca das notifications. --}}
<x-mail::message :logo="\App\Support\Mail\Blocos::logoSrc($message ?? null)">
# Sua recarga automática foi pausada

Olá, {{ $usuario->name }}.

Interrompemos a recarga automática do seu saldo. Motivo informado pelo provedor de pagamento:

{!! \App\Support\Mail\Blocos::destaque(e($motivo), \App\Support\Mail\Blocos::AMBAR) !!}

**O que isso muda agora:** nada é cobrado automaticamente até você regularizar. Se o saldo acabar,
as consultas e o **monitoramento contínuo** dos seus CNPJs param de rodar — e uma mudança de
situação cadastral pode passar despercebida.

<x-mail::button :url="url('/app/creditos')">
Atualizar forma de pagamento
</x-mail::button>

Você também pode adicionar saldo avulso, sem religar a recarga automática.

Se não reconhece essa recarga, fale com o suporte respondendo este e-mail.
</x-mail::message>
