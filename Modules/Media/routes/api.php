<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\MediaController;
use Modules\Core\Http\Middleware\ManualAuth;

Route::middleware(ManualAuth::class)->prefix('v1/media')->group(function () {

    
    Route::post('/sync', [MediaController::class, 'sync'])->name('media.sync');

});