<?php

use DigitalNode\Larafields\Actions\GetFormAction;
use DigitalNode\Larafields\Actions\UpdateFormAction;
use DigitalNode\Larafields\DTOs\GetFormDTO;
use DigitalNode\Larafields\DTOs\UpdateFormDTO;
use DigitalNode\Larafields\Http\Controllers\AssetsController;
use DigitalNode\Larafields\Http\Middleware\ApplicationPasswordAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('larafields')->group(function () {
    Route::get('/assets/css/larafields.css', [AssetsController::class, 'css']);
    Route::get('/assets/js/larafields.js', [AssetsController::class, 'js']);
});

Route::middleware(ApplicationPasswordAuth::class)
    ->group(function () {
        Route::get('/forms', function (Request $request, GetFormAction $action) {
            return response()->json(
                $action->execute(GetFormDTO::fromRequest($request))
            );
        })->prefix('larafields');

        Route::post('/forms', function (Request $request, UpdateFormAction $action) {
            $result = $action->execute(UpdateFormDTO::fromRequest($request));

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
