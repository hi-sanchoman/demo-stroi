<?php

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
    // Construction
    Route::apiResource('constructions', 'ConstructionApiController');

    // Application
    Route::apiResource('applications', 'ApplicationApiController');

    // Application Products
    Route::apiResource('application-products', 'ApplicationProductsApiController');

    // Application Path
    Route::apiResource('application-paths', 'ApplicationPathApiController');

    // Application Status
    Route::apiResource('application-statuses', 'ApplicationStatusApiController');

    // Application Log
    Route::apiResource('application-logs', 'ApplicationLogApiController');
});
