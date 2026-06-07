<?php

namespace App\Features\Eclat\Controllers;

use App\Features\Eclat\Requests\AnalyzeEclatRequest;
use App\Features\Eclat\Services\EclatService;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class EclatController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly EclatService $service) {}

    /**
     * @OA\Get(
     *     path="/api/v1/eclat/runs",
     *     tags={"ECLAT"},
     *     security={{"bearerAuth":{}}},
     *     summary="List riwayat proses ECLAT",
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Riwayat proses ECLAT")
     * )
     */
    public function runs(Request $request): JsonResponse
    {
        try {
            return $this->paginatedResponse($this->service->paginateRuns(
                $request->only(['status']),
                min((int) $request->integer('per_page', 10), 100)
            ), 'ECLAT runs loaded');
        } catch (Throwable $e) {
            Log::error('Failed to load ECLAT runs', ['exception' => $e]);

            return $this->errorResponse('Unable to load ECLAT runs', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/eclat/analyze",
     *     tags={"ECLAT"},
     *     security={{"bearerAuth":{}}},
     *     summary="Jalankan analisis ECLAT",
     *     @OA\Response(response=201, description="Analisis selesai"),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function analyze(AnalyzeEclatRequest $request): JsonResponse
    {
        try {
            $payload = $request->validated();

            return $this->successResponse(
                $this->service->analyze((float) $payload['min_support'], (float) $payload['min_confidence']),
                'ECLAT analysis completed',
                [],
                201
            );
        } catch (Throwable $e) {
            Log::error('Failed to run ECLAT analysis', ['payload' => $request->validated(), 'exception' => $e]);

            return $this->errorResponse('Unable to run ECLAT analysis', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/eclat/runs/{run}",
     *     tags={"ECLAT"},
     *     security={{"bearerAuth":{}}},
     *     summary="Detail proses ECLAT",
     *     @OA\Parameter(name="run", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail proses")
     * )
     */
    public function showRun(int $run): JsonResponse
    {
        try {
            return $this->successResponse($this->service->findRun($run), 'ECLAT run loaded');
        } catch (Throwable $e) {
            Log::warning('ECLAT run not found', ['id' => $run, 'exception' => $e]);

            return $this->errorResponse('ECLAT run not found', 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/eclat/results",
     *     tags={"ECLAT"},
     *     security={{"bearerAuth":{}}},
     *     summary="List hasil association rule ECLAT",
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="run_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Daftar hasil ECLAT")
     * )
     */
    public function results(Request $request): JsonResponse
    {
        try {
            return $this->paginatedResponse($this->service->paginateResults(
                $request->only(['run_id', 'min_confidence', 'min_support']),
                min((int) $request->integer('per_page', 10), 100)
            ), 'ECLAT results loaded');
        } catch (Throwable $e) {
            Log::error('Failed to load ECLAT results', ['exception' => $e]);

            return $this->errorResponse('Unable to load ECLAT results', 500);
        }
    }
}
