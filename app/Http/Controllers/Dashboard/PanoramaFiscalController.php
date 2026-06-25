<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Participante;
use App\Services\Consultas\PanoramaFiscalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PanoramaFiscalController extends Controller
{
    public function __construct(private PanoramaFiscalService $service) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'scope' => 'required|in:participante,cliente',
            'id' => 'required|integer',
        ]);

        $userId = (int) $request->user()->id;
        $id = (int) $data['id'];

        // Ownership: o alvo precisa ser do usuário. Nunca confiar no id do frontend.
        $existe = $data['scope'] === 'participante'
            ? Participante::where('user_id', $userId)->whereKey($id)->exists()
            : Cliente::where('user_id', $userId)->whereKey($id)->exists();

        abort_unless($existe, 404);

        return response()->json([
            'panorama' => $this->service->para($userId, $data['scope'], $id),
        ]);
    }
}
