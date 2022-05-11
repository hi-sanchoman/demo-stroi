<?php

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
    // Responsible
    Route::post('responsibles/media', 'ResponsibleApiController@storeMedia')->name('responsibles.storeMedia');
    Route::apiResource('responsibles', 'ResponsibleApiController');

    // Stage
    Route::post('stages/media', 'StageApiController@storeMedia')->name('stages.storeMedia');
    Route::apiResource('stages', 'StageApiController');

    // Business Process
    Route::apiResource('business-processes', 'BusinessProcessApiController');
});
