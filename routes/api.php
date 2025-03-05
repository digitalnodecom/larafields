<?php

use DigitalNode\Larafields\Actions\GetFormAction;
use DigitalNode\Larafields\Actions\UpdateFormAction;
use DigitalNode\Larafields\Http\Controllers\AssetsController;
use DigitalNode\Larafields\Http\Middleware\ApplicationPasswordAuth;
use DigitalNode\Larafields\Larafields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('larafields')->group(function () {
    Route::get('/assets/lf.css', [AssetsController::class, 'css']);
});

Route::middleware(ApplicationPasswordAuth::class)
    ->group(function () {
        Route::get('/forms', function (Request $request, GetFormAction $action) {
            return response()->json(
                $action->execute($request)
            );
        })->prefix('larafields');

        Route::post('/forms', function (Request $request, Larafields $larafields, UpdateFormAction $action) {
            $result = $action->execute($request);
            
            if (isset($result['status']) && $result['status'] === 'error') {
                return response()->json([
                    'status' => $result['status'],
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? null,
                ], $result['code'] ?? 422);
            }
            
            return response()->json($result);
        })->prefix('larafields');
    });
