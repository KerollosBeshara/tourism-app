<?php

use Illuminate\Support\Facades\Route;
use Modules\DayTour\Http\Controllers\DayTourController;
use Modules\Core\Http\Middleware\ManualAuth;

Route::middleware(ManualAuth::class)->prefix('v1')->group(function () {
    // Day Tour CRUD routes
    Route::apiResource('day-tours', DayTourController::class, ['as' => 'daytour'])->names([
        'index' => 'daytour.index',
        'store' => 'daytour.store',
        'show' => 'daytour.show',
        'update' => 'daytour.update',
        'destroy' => 'daytour.destroy',
    ]);

    // Day Tour Image routes
    Route::post('day-tours/{dayTourId}/images', [DayTourController::class, 'uploadImage'])->name('daytour.image.upload');
    Route::get('day-tours/{dayTourId}/images', [DayTourController::class, 'listImages'])->name('daytour.images.list');
    Route::delete('day-tours/{dayTourId}/images/{imageId}', [DayTourController::class, 'deleteImage'])->name('daytour.image.delete');

    // Search route
    Route::get('day-tours/search', [DayTourController::class, 'search'])->name('daytour.search');
});
