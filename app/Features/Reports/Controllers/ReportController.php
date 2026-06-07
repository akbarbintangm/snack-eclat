<?php

namespace App\Features\Reports\Controllers;

use App\Features\Reports\Services\ReportService;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ReportService $service) {}

    /**
     * @OA\Get(
     *     path="/api/v1/reports/summary",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     summary="Ringkasan dashboard Snack Eclat",
     *     @OA\Response(response=200, description="Ringkasan dashboard")
     * )
     */
    public function summary(): JsonResponse
    {
        try {
            return $this->successResponse($this->service->summary(), 'Report summary loaded');
        } catch (Throwable $e) {
            Log::error('Failed to load report summary', ['exception' => $e]);

            return $this->errorResponse('Unable to load report summary', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/recommendations",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     summary="Rekomendasi rule penjualan dari hasil ECLAT",
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Daftar rekomendasi")
     * )
     */
    public function recommendations(Request $request): JsonResponse
    {
        try {
            return $this->successResponse(
                $this->service->recommendations(min((int) $request->integer('limit', 5), 25)),
                'Recommendations loaded'
            );
        } catch (Throwable $e) {
            Log::error('Failed to load recommendations', ['exception' => $e]);

            return $this->errorResponse('Unable to load recommendations', 500);
        }
    }
}
