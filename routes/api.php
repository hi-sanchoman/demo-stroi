<?php

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
    // User
    Route::get('/me', [App\Http\Controllers\Api\V1\UserController::class, 'me']);
    Route::post('/device-token', [App\Http\Controllers\Api\V1\UserController::class, 'saveDeviceToken']);
    Route::put('/profile', [App\Http\Controllers\Api\V1\UserController::class, 'updateProfile']);
    Route::post('/upload-photo', [App\Http\Controllers\Api\V1\UserController::class, 'uploadPhoto']);

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


    Route::put('/application-equipments/{id}/prepare', 'ApplicationEquipmentsApiController@prepare');
    Route::post('/application-equipments/add-note', 'ApplicationEquipmentsApiController@addNote');
    Route::apiResource('application-equipments', 'ApplicationEquipmentsApiController');
    Route::apiResource('equipment-offers', 'EquipmentOfferApiController');


    Route::put('/application-services/{id}/service-price', 'ApplicationServicesApiController@updateServicePrice');
    Route::put('/application-services/{id}/prepare', 'ApplicationServicesApiController@prepare');
    Route::apiResource('service-offers', 'ServiceOfferApiController');

    // Inventories
    Route::get('/history-supplies/{productId}/{kind}', 'SupplyApiController@history');
    Route::get('/supplies', 'SupplyApiController@index');

    Route::get('inventories/dropdown', 'InventoryApiController@dropdown');
    Route::get('inventories/{id}/foremans', 'InventoryApiController@foremans');
    Route::get('/inventories/{id}/stocks', 'InventoryStockApiController@index');

    Route::get('/inventories/{id}/products', 'InventoryStockApiController@products');
    Route::get('/inventories/{id}/equipments', 'InventoryStockApiController@equipments');
    Route::get('/inventories/{id}/services', 'InventoryStockApiController@services');


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

    Route::get('users', 'UserController@index');

    Route::apiResource('products', 'ProductApiController');
    Route::apiResource('categories', 'CategoryApiController');
    Route::apiResource('companies', 'CompanyApiController');
    Route::apiResource('units', 'UnitApiController');
    Route::apiResource('equipments', 'EquipmentApiController');

    Route::delete('companies/{id}/contacts/{contactId}', 'CompanyContactApiController@destroy');
    Route::get('companies/{id}/contacts', 'CompanyContactApiController@index');
    Route::post('companies/{id}/contacts', 'CompanyContactApiController@store');

    Route::get('companies/{id}/offers', 'CompanyApiController@offers');

    Route::apiResource('companies', 'CompanyApiController');

    
    Route::post('tasks/{id}/start', 'TaskApiController@start');
    Route::post('tasks/{id}/complete', 'TaskApiController@complete');
    Route::apiResource('tasks', 'TaskApiController');

    Route::get('/payments', [App\Http\Controllers\Api\V1\PaymentApiController::class, 'payments']);
    Route::put('/payments', [App\Http\Controllers\Api\V1\PaymentApiController::class, 'updateBatch']);
    Route::get('/payments-to-pay', [App\Http\Controllers\Api\V1\PaymentApiController::class, 'paymentsToPay']);
    Route::put('/to-pay/{id}', [App\Http\Controllers\Api\V1\PaymentApiController::class, 'setPaid']);

    Route::get('/foremans', [App\Http\Controllers\Api\V1\Admin\InventoryApiController::class, 'getForemans']);
    Route::post('/move-stocks', [App\Http\Controllers\Api\V1\Admin\InventoryApiController::class, 'moveStocks']);
    Route::post('/move-stocks-outside', [App\Http\Controllers\Api\V1\Admin\InventoryApiController::class, 'moveStocksOutside']);
    Route::post('/move-stocks-foreman', [App\Http\Controllers\Api\V1\Admin\InventoryApiController::class, 'moveStocksForeman']);

    Route::get('/temp-incoming/{id}', [App\Http\Controllers\Api\V1\Admin\InventoryApiController::class, 'getIncoming']);
    Route::put('/temp-inventory-accept/{id}', [App\Http\Controllers\Api\V1\Admin\InventoryApiController::class, 'acceptIncoming']);
    Route::put('/temp-inventory-decline/{id}', [App\Http\Controllers\Api\V1\Admin\InventoryApiController::class, 'declineIncoming']);





    Route::get('/badges-unread', [App\Http\Controllers\Api\V1\UserController::class, 'getUnreadBadge']);
    Route::put('/read-badge', [App\Http\Controllers\Api\V1\UserController::class, 'readBadge']);
});
