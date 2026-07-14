<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ConsultaResultado;
use App\Services\Arquivos\ArquivoUsuarioService;
use App\Services\Consultas\ComprovanteArquivador;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsultaComprovanteController extends Controller
{
    public function __construct(private readonly ArquivoUsuarioService $arquivos) {}

    public function __invoke(Request $request, ConsultaResultado $resultado, string $fonte): StreamedResponse|RedirectResponse
    {
        $resultado->loadMissing('lote');
        abort_unless((int) $resultado->lote?->user_id === (int) Auth::id(), 404);

        $bloco = data_get($resultado->resultado_dados, $fonte);
        abort_unless(is_array($bloco), 404, 'Comprovante não encontrado para esta fonte.');

        $path = $bloco['comprovante_arquivo'] ?? null;
        if ($this->pathDoUsuario($path) && Storage::disk('local')->exists($path)) {
            $extensao = pathinfo($path, PATHINFO_EXTENSION) ?: 'bin';
            $nome = ComprovanteArquivador::rotuloDePath($path) ?? "comprovante-{$fonte}";

            // ?preview=1: exibição inline no modal (iframe) — mesmo contrato do preview
            // de /app/arquivos. Formato sem visualização cai no download normal.
            if ($request->boolean('preview')) {
                $contentType = $this->arquivos->previewContentType($extensao);
                if ($contentType !== null) {
                    $headers = [
                        'Content-Type' => $contentType,
                        'X-Content-Type-Options' => 'nosniff',
                    ];
                    if (str_starts_with($contentType, 'text/html')) {
                        // Comprovante HTML é conteúdo de terceiro — origem opaca via
                        // CSP sandbox: sem script, sem cookies, sem same-origin.
                        $headers['Content-Security-Policy'] = 'sandbox';
                    }

                    return Storage::disk('local')->response(
                        $path,
                        "{$nome}.{$extensao}",
                        $headers,
                    );
                }
            }

            return Storage::disk('local')->download(
                $path,
                "{$nome}.{$extensao}",
            );
        }

        $original = $bloco['comprovante'] ?? null;
        if (is_string($original) && filter_var($original, FILTER_VALIDATE_URL)) {
            return redirect()->away($original);
        }

        abort(404, 'O comprovante não está disponível no arquivo local nem na origem.');
    }

    private function pathDoUsuario(mixed $path): bool
    {
        return is_string($path)
            && str_starts_with($path, 'comprovantes/'.Auth::id().'/')
            && ! str_contains($path, '..');
    }
}
