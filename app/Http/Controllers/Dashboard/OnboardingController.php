<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ConsentLog;
use App\Services\Lgpd\ConsentLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Confirmação de onboarding pós-cadastro.
 *
 * O aceite legal dos termos acontece no checkbox obrigatório do formulário de
 * signup (gate). Aqui o titular reconfirma no modal de boas-vindas — gera um
 * consent_log adicional na trilha auditável (reaproveita LGPD fase 2.1).
 */
class OnboardingController extends Controller
{
    public function confirmarTermos(Request $request, ConsentLogService $consent): JsonResponse
    {
        $user = $request->user();

        $consent->registrar($user->id, ConsentLog::TIPO_TERMOS, ConsentLog::ACAO_ACEITE,
            versao: config('legal.terms_version'), ip: $request->ip(), userAgent: $request->userAgent());
        $consent->registrar($user->id, ConsentLog::TIPO_PRIVACIDADE, ConsentLog::ACAO_ACEITE,
            versao: config('legal.privacy_version'), ip: $request->ip(), userAgent: $request->userAgent());

        return response()->json(['success' => true]);
    }
}
