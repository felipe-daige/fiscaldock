<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Mesma VerifyEmail nativa (URL assinada + expiração de config('auth.verification.expire')),
 * só que enfileirada. Conteúdo pt-BR vem do VerifyEmail::toMailUsing() no AppServiceProvider.
 */
class VerifyEmailQueued extends VerifyEmail implements ShouldQueue
{
    use Queueable;
}
