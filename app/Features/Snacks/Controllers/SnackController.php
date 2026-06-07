<?php

namespace App\Features\Snacks\Controllers;

use App\Features\Snacks\Requests\StoreSnackRequest;
use App\Features\Snacks\Requests\UpdateSnackRequest;
use App\Features\Snacks\Services\SnackService;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SnackController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SnackService $service) {}

    /**
     * @OA\Get(
     *     path="/api/v1/snacks",
     *     tags={"Snacks"},
     *     security={{"bearerAuth":{}}},
     *     summary="List snack dengan pagination",
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Daftar snack")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            return $this->paginatedResponse($this->service->paginate(
                $request->only(['search', 'status']),
                min((int) $request->integer('per_page', 10), 100)
            ), 'Snack list loaded');
        } catch (Throwable $e) {
            Log::error('Failed to load snacks', ['exception' => $e]);

            return $this->errorResponse('Unable to load snacks', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/snacks",
     *     tags={"Snacks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Tambah snack",
     *     @OA\Response(response=201, description="Snack dibuat"),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function store(StoreSnackRequest $request): JsonResponse
    {
        try {
            return $this->successResponse($this->service->create($request->validated()), 'Snack created', [], 201);
        } catch (Throwable $e) {
            Log::error('Failed to create snack', ['payload' => $request->validated(), 'exception' => $e]);

            return $this->errorResponse('Unable to create snack', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/snacks/{snack}",
     *     tags={"Snacks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Detail snack",
     *     @OA\Parameter(name="snack", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail snack"),
     *     @OA\Response(response=404, description="Snack tidak ditemukan")
     * )
     */
    public function show(int $snack): JsonResponse
    {
        try {
            return $this->successResponse($this->service->find($snack), 'Snack loaded');
        } catch (Throwable $e) {
            Log::warning('Snack not found', ['id' => $snack, 'exception' => $e]);

            return $this->errorResponse('Snack not found', 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/snacks/{snack}",
     *     tags={"Snacks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update snack",
     *     @OA\Parameter(name="snack", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Snack diupdate")
     * )
     */
    public function update(UpdateSnackRequest $request, int $snack): JsonResponse
    {
        try {
            return $this->successResponse($this->service->update($snack, $request->validated()), 'Snack updated');
        } catch (Throwable $e) {
            Log::error('Failed to update snack', ['id' => $snack, 'exception' => $e]);

            return $this->errorResponse('Unable to update snack', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/snacks/{snack}",
     *     tags={"Snacks"},
     *     security={{"bearerAuth":{}}},
     *     summary="Hapus snack",
     *     @OA\Parameter(name="snack", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Snack dihapus")
     * )
     */
    public function destroy(int $snack): JsonResponse
    {
        try {
            $this->service->delete($snack);

            return $this->successResponse(null, 'Snack deleted');
        } catch (Throwable $e) {
            Log::error('Failed to delete snack', ['id' => $snack, 'exception' => $e]);

            return $this->errorResponse('Unable to delete snack', 500);
        }
    }
}
