<?php

namespace App\View\Components\Clearance;

use App\Models\CertificadoDigital;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class CertificadoBanner extends Component
{
    public bool $exibir;

    public function __construct()
    {
        $userId = Auth::id();

        $this->exibir = (bool) config('clearance.certificado.habilitado')
            && $userId !== null
            && ! CertificadoDigital::query()->where('user_id', $userId)->exists();
    }

    public function render(): View
    {
        return view('components.clearance.certificado-banner');
    }
}
