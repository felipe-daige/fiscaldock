<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait SetsDownloadToken
{
    /**
     * Anexa o cookie `bi_download=<token>` à resposta de download quando o request traz
     * `download_token`. O frontend (iframe nativo + poll do cookie em <x-download-button>)
     * usa a PRESENÇA desse cookie para saber que o arquivo chegou e esconder o overlay
     * "baixando documento". httpOnly=false: o JS precisa enxergar o nome do cookie.
     */
    protected function comTokenDownload($response, Request $request)
    {
        $token = $request->get('download_token');
        if ($token) {
            $response->headers->setCookie(cookie('bi_download', (string) $token, 1, '/', null, null, false));
        }

        return $response;
    }
}
