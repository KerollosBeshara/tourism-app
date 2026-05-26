<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\CoreController;
use Modules\Core\Http\Controllers\Auth\AuthController;
use Modules\Core\Http\Controllers\AgencyStatusController;
use Modules\Core\Http\Middleware\ManualAuth;

           




Route::prefix('v1/core')->group(function () {

    // Public Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/health', function () {
                return response()->json(['status' => 'ok']);
        });

        
    });

    // Agency Status Routes (Protected)
    Route::middleware(ManualAuth::class)->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/user-load', [AuthController::class, 'userLoad'])->name('auth.user_load');
        
        Route::apiResource('agency-statuses', AgencyStatusController::class);
        Route::post('/agency-statuses/bulk-update', [AgencyStatusController::class, 'bulkUpdate'])
            ->name('agency-statuses.bulk-update');
    });

});


