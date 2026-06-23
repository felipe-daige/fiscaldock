<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;

final class PdfReport
{
    public static function render(string $view, array $dados = [], string $orientacao = 'portrait'): DomPDF
    {
        return Pdf::loadView($view, $dados)
            ->setPaper('a4', $orientacao)
            ->setOptions([
                'isPhpEnabled' => true,          // habilita o script de numeração de página
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,      // logo é base64, sem remoto
                'defaultFont' => 'DejaVu Sans',
            ]);
    }

    public static function logoDataUri(): string
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $path = public_path('binary_files/logo/logo-fiscaldock_whitebg-removebg.png');
        $bin = is_file($path) ? (string) file_get_contents($path) : '';

        return $cache = 'data:image/png;base64,'.base64_encode($bin);
    }
}
