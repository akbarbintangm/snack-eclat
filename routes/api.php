<?php

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

    Route::apiResource('snacks', SnackController::class);
    Route::apiResource('transactions', TransactionController::class);

    Route::get('/eclat/runs', [EclatController::class, 'runs']);
    Route::post('/eclat/analyze', [EclatController::class, 'analyze']);
    Route::get('/eclat/runs/{run}', [EclatController::class, 'showRun']);
    Route::get('/eclat/results', [EclatController::class, 'results']);

    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/recommendations', [ReportController::class, 'recommendations']);

    Route::get('/documentation/openapi.json', OpenApiController::class);
});
