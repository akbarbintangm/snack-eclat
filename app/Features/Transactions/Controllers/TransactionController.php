<?php

namespace App\Features\Transactions\Controllers;

use App\Features\Transactions\Requests\StoreTransactionRequest;
use App\Features\Transactions\Requests\UpdateTransactionRequest;
use App\Features\Transactions\Services\TransactionService;
use App\Http\Controllers\Controller;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransactionController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TransactionService $service) {}

    /**
     * @OA\Get(
     *     path="/api/v1/transactions",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     summary="List transaksi penjualan dengan pagination",
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Daftar transaksi")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            return $this->paginatedResponse($this->service->paginate(
                $request->only(['search', 'status', 'date_from', 'date_to']),
                min((int) $request->integer('per_page', 10), 100)
            ), 'Transactions loaded');
        } catch (Throwable $e) {
            Log::error('Failed to load transactions', ['exception' => $e]);

            return $this->errorResponse('Unable to load transactions', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/transactions",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     summary="Tambah transaksi dan detail snack",
     *     @OA\Response(response=201, description="Transaksi dibuat"),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            return $this->successResponse($this->service->create($request->validated()), 'Transaction created', [], 201);
        } catch (Throwable $e) {
            Log::error('Failed to create transaction', ['payload' => $request->validated(), 'exception' => $e]);

            return $this->errorResponse('Unable to create transaction', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/transactions/{transaction}",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     summary="Detail transaksi",
     *     @OA\Parameter(name="transaction", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail transaksi"),
     *     @OA\Response(response=404, description="Transaksi tidak ditemukan")
     * )
     */
    public function show(int $transaction): JsonResponse
    {
        try {
            return $this->successResponse($this->service->find($transaction), 'Transaction loaded');
        } catch (Throwable $e) {
            Log::warning('Transaction not found', ['id' => $transaction, 'exception' => $e]);

            return $this->errorResponse('Transaction not found', 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/transactions/{transaction}",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update transaksi",
     *     @OA\Parameter(name="transaction", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Transaksi diupdate")
     * )
     */
    public function update(UpdateTransactionRequest $request, int $transaction): JsonResponse
    {
        try {
            return $this->successResponse($this->service->update($transaction, $request->validated()), 'Transaction updated');
        } catch (Throwable $e) {
            Log::error('Failed to update transaction', ['id' => $transaction, 'exception' => $e]);

            return $this->errorResponse('Unable to update transaction', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/transactions/{transaction}",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     summary="Hapus transaksi",
     *     @OA\Parameter(name="transaction", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Transaksi dihapus")
     * )
     */
    public function destroy(int $transaction): JsonResponse
    {
        try {
            $this->service->delete($transaction);

            return $this->successResponse(null, 'Transaction deleted');
        } catch (Throwable $e) {
            Log::error('Failed to delete transaction', ['id' => $transaction, 'exception' => $e]);

            return $this->errorResponse('Unable to delete transaction', 500);
        }
    }
}
