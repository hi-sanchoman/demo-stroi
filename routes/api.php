<?php

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
    // User
    Route::get('/me', [App\Http\Controllers\Api\V1\UserController::class, 'me']);
    
    // Construction
    Route::apiResource('constructions', 'ConstructionApiController');

    // Application
    Route::apiResource('applications', 'ApplicationApiController');

    // Application Products
    Route::put('/application-products/{id}/prepare', 'ApplicationProductsApiController@prepare');
    Route::apiResource('application-products', 'ApplicationProductsApiController');

    // Application Path
    Route::apiResource('application-paths', 'ApplicationPathApiController');

    // Application Status
    Route::apiResource('application-statuses', 'ApplicationStatusApiController');

    // Application Log
    Route::apiResource('application-logs', 'ApplicationLogApiController');

    // Application Offer
    Route::apiResource('application-offers', 'ApplicationOfferApiController');


    // Inventories
    Route::get('/history-supplies/{productId}', 'SupplyApiController@history');
    Route::get('/supplies', 'SupplyApiController@index');

    Route::get('/inventories/{id}/stocks', 'InventoryStockApiController@index');
    Route::get('/inventories/{id}/incoming', 'InventoryStockApiController@incoming');
    Route::get('/inventories/{id}/history', 'InventoryStockApiController@history');
    Route::apiResource('inventories', 'InventoryApiController');

    // Inventory stocks
    Route::apiResource('inventory-stocks', 'InventoryStockApiController');

    // Inventory applications
    Route::apiResource('inventory-applications', 'InventoryApplicationApiController');
    
});


// login
Route::post('/v1/auth/login', [App\Http\Controllers\Api\V1\AuthController::class, 'login']);

// logout
Route::post('/v1/auth/logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout'])->middleware('auth:sanctum');



// register
// Route::post('/api/v1/auth/register', function(Request $request) {
//     // $request->validate([
//     //     'name' => ''
//     // ])
// });





// non admin
Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1', 'middleware' => ['auth:sanctum']], function () {

    Route::apiResource('products', 'ProductApiController');
    Route::apiResource('categories', 'CategoryApiController');
    Route::apiResource('companies', 'CompanyApiController');
    
    Route::get('/payments', [App\Http\Controllers\Api\V1\PaymentApiController::class, 'payments']);
    Route::put('/payments', [App\Http\Controllers\Api\V1\PaymentApiController::class, 'updateBatch']);
    Route::get('/payments-to-pay', [App\Http\Controllers\Api\V1\PaymentApiController::class, 'paymentsToPay']);
    Route::put('/to-pay/{id}', [App\Http\Controllers\Api\V1\PaymentApiController::class, 'setPaid']);
    
    Route::get('/foremans', [App\Http\Controllers\Api\V1\Admin\InventoryApiController::class, 'getForemans']);
    Route::post('/move-stocks', [App\Http\Controllers\Api\V1\Admin\InventoryApiController::class, 'moveStocks']);
});