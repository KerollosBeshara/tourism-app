<?php

use Illuminate\Support\Facades\Route;
use Modules\Geo\Http\Controllers\GeoController;
use Modules\Geo\Http\Controllers\AgencyLanguageController;
use Modules\Geo\Http\Controllers\DestinationController;
use Modules\Geo\Http\Controllers\DestinationTourismItemController;
use Modules\Geo\Http\Controllers\CityController;
use Modules\Core\Http\Middleware\ManualAuth;
use Modules\Geo\Http\Controllers\CountryController;

Route::middleware(ManualAuth::class)->prefix('v1/geo')->group(function () {
    Route::apiResource('geos', GeoController::class)->names('geo');


    Route::prefix('countries')->group(function () {
        Route::get('/', [CountryController::class, 'index']);          // Fetch / Search Countries
        Route::post('/', [CountryController::class, 'store']);         // Create Country
        Route::put('/{country}', [CountryController::class, 'update']);  // Update Country
        Route::delete('/{country}', [CountryController::class, 'destroy']); // Delete Country
    });

    // Agency Languages with manual auth for high performance
    Route::prefix('agency-languages')->group(function () {
        Route::get('/', [AgencyLanguageController::class, 'index']);
        Route::post('/', [AgencyLanguageController::class, 'store']);
        Route::put('/{agency_language}', [AgencyLanguageController::class, 'update']);
        Route::delete('/bulk-delete', [AgencyLanguageController::class, 'bulkDelete']);
    });



    Route::get('destinations/countries-lookup', [DestinationController::class, 'countriesLookup']);

    Route::apiResource('destinations', DestinationController::class);
    
    Route::apiResource('cities', CityController::class);

    Route::get('destinations/{destination}/items', [DestinationTourismItemController::class, 'index']);

    Route::prefix('destination-tourism-items')->group(function () {
        Route::get('destination/{destinationId}', [DestinationTourismItemController::class, 'index']);
        Route::post('save', [DestinationTourismItemController::class, 'save']);
        Route::delete('{id}', [DestinationTourismItemController::class, 'destroy']);
    });



    // Media Gallery Endpoints
    Route::get('destinations/{destination}/media', [DestinationMediaController::class, 'index']);
    Route::post('destinations/media/sync', [DestinationMediaController::class, 'sync']);

    Route::get('languages', [AgencyLanguageController::class, 'languages']);
});
