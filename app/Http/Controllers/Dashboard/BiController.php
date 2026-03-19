<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BiController extends Controller
{
    private const AUTH_LAYOUT_VIEW = 'autenticado.layouts.app';

    private const BI_INDEX_VIEW = 'autenticado.bi.index';

    public function __construct(
        protected Request $request
    ) {}

    public function index(Request $request)
    {
        $periodo = $request->input('periodo', 'mes_atual');
        [$dataInicio, $dataFim] = $this->resolverPeriodo($periodo, $request);

        $filtros = [
            'data_inicio' => $dataInicio->format('d/m/Y'),
            'data_fim' => $dataFim->format('d/m/Y'),
            'data_inicio_iso' => $dataInicio->format('Y-m-d'),
            'data_fim_iso' => $dataFim->format('Y-m-d'),
        ];

        $data = [
            'periodoAtivo' => $periodo,
            'filtros' => $filtros,
        ];

        if ($this->isAjaxRequest($request)) {
            return response(view(self::BI_INDEX_VIEW, $data)->render())
                ->header('Content-Type', 'text/html');
        }

        return view(self::AUTH_LAYOUT_VIEW, array_merge(
            ['initialView' => self::BI_INDEX_VIEW],
            $data
        ));
    }

    private function resolverPeriodo(string $periodo, Request $request): array
    {
        return match ($periodo) {
            'mes_anterior' => [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ],
            'trimestre_atual' => [
                now()->firstOfQuarter()->startOfDay(),
                now()->lastOfQuarter()->endOfDay(),
            ],
            'semestre_atual' => $this->resolverSemestre(),
            'ano_atual' => [
                now()->startOfYear(),
                now()->endOfYear(),
            ],
            'personalizado' => [
                Carbon::parse($request->input('data_inicio', now()->startOfMonth()->format('Y-m-d')))->startOfDay(),
                Carbon::parse($request->input('data_fim', now()->endOfMonth()->format('Y-m-d')))->endOfDay(),
            ],
            default => [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ],
        };
    }

    private function resolverSemestre(): array
    {
        $now = now();
        $mes = (int) $now->format('n');
        if ($mes <= 6) {
            return [$now->copy()->startOfYear(), $now->copy()->month(6)->endOfMonth()];
        }

        return [$now->copy()->month(7)->startOfMonth(), $now->copy()->endOfYear()];
    }

    private function isAjaxRequest(Request $request): bool
    {
        return $request->ajax()
            || $request->wantsJson()
            || $request->headers->get('X-Requested-With') === 'XMLHttpRequest';
    }
}
