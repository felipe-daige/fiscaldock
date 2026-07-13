<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CteConsulta;
use App\Models\NfeConsulta;
use App\Services\Consultas\ComprovanteArquivador;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClearanceComprovanteController extends Controller
{
    public function __invoke(string $tipo, int $id, string $arquivo): StreamedResponse|RedirectResponse
    {
        $model = $tipo === 'cte' ? CteConsulta::class : NfeConsulta::class;
        $snapshot = $model::query()
            ->whereKey($id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $originais = [
            'html' => $snapshot->url_html,
            'xml' => $snapshot->url_xml,
            'site_receipt' => $snapshot->url_site_receipt,
        ];
        abort_unless(array_key_exists($arquivo, $originais), 404);

        $path = data_get($snapshot->payload, "comprovantes_arquivos.{$arquivo}");
        if ($this->pathDoUsuario($path) && Storage::disk('local')->exists($path)) {
            $extensao = pathinfo($path, PATHINFO_EXTENSION) ?: 'bin';
            $nome = ComprovanteArquivador::rotuloDePath($path)
                ?? "comprovante-{$tipo}-{$snapshot->id}-{$arquivo}";

            return Storage::disk('local')->download(
                $path,
                "{$nome}.{$extensao}",
            );
        }

        $original = $originais[$arquivo];
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
