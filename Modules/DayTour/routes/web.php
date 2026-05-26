<?php

use Illuminate\Support\Facades\Route;
use Modules\DayTour\Http\Controllers\DayTourController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('daytours', DayTourController::class)->names('daytour');
});
