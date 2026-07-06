<?php

use App\Features\Auth\Controllers\AuthController;
use App\Features\Documentation\Controllers\OpenApiController;
use App\Features\Eclat\Controllers\EclatController;
use App\Features\Reports\Controllers\ReportController;
use App\Features\Snacks\Controllers\SnackController;
use App\Features\Transactions\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json([
        'success' => true,
        'message' => 'Snack Eclat API is healthy',
    ]));

    Route::post('/auth/signup', [AuthController::class, 'signup']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/documentation/openapi.json', OpenApiController::class);

    Route::middleware('auth.token')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::middleware('role:admin')->group(function (): void {
            Route::apiResource('snacks', SnackController::class);
            Route::delete('/transactions', [TransactionController::class, 'destroyAll']);
            Route::post('/transactions/import', [TransactionController::class, 'import']);
            Route::apiResource('transactions', TransactionController::class);

            Route::get('/eclat/transaction-period', [EclatController::class, 'transactionPeriod']);
            Route::get('/eclat/runs', [EclatController::class, 'runs']);
            Route::post('/eclat/analyze', [EclatController::class, 'analyze']);
            Route::get('/eclat/runs/{run}', [EclatController::class, 'showRun']);
            Route::get('/eclat/results', [EclatController::class, 'results']);
        });

        Route::middleware('role:admin,owner')->group(function (): void {
            Route::get('/reports/summary', [ReportController::class, 'summary']);
        });

        Route::middleware('role:owner')->group(function (): void {
            Route::get('/reports/recommendations', [ReportController::class, 'recommendations']);
        });
    });
});
