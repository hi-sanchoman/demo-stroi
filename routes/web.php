<?php

Route::get('/', function () {
    return view('welcome');
});

Route::get('/export/application/{id}', 'ApplicationController@exportApplication');


Route::get('/home', function () {
    if (session('status')) {
        return redirect('/')->with('status', session('status'));
    }

    return redirect('/');
});


Route::get('/applications', 'ApplicationController@index');
Route::get('/applications/create', 'ApplicationController@create');
Route::get('/applications/{id}/edit', 'ApplicationController@edit');

Route::get('/supplies', 'ApplicationController@index');

Route::get('/inventories', 'ApplicationController@index');
Route::get('/inventories/{id}/products', 'ApplicationController@index');
Route::get('/inventories/{id}/equipment', 'ApplicationController@index');
Route::get('/inventories/{id}/services', 'ApplicationController@index');
Route::get('/inventories/{id}', 'ApplicationController@index');
Route::get('/inventories/{id}/history', 'ApplicationController@index');
Route::get('/payments', 'ApplicationController@index');
Route::get('/to-pay', 'ApplicationController@index');


Route::get('/companies', 'ApplicationController@index');
Route::get('/companies/create', 'ApplicationController@index');

Route::get('/companies/{id}/contacts/create', 'ApplicationController@index');
Route::delete('/companies/{id}/contacts/{contactId}', 'ApplicationController@index');

Route::get('/companies/{id}/edit', 'ApplicationController@index');
Route::get('/companies/{id}', 'ApplicationController@index');


Route::get('/profile', 'ApplicationController@index');
Route::get('/logout', 'ApplicationController@index');


Auth::routes(['register' => false]);


Route::post('/upload-file', 'ApplicationController@uploadFile');

Route::get('/parse', 'ApplicationController@parse');


Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'HomeController@index')->name('home');

    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    // Crm Status
    Route::delete('crm-statuses/destroy', 'CrmStatusController@massDestroy')->name('crm-statuses.massDestroy');
    Route::resource('crm-statuses', 'CrmStatusController');

    // Crm Customer
    Route::delete('crm-customers/destroy', 'CrmCustomerController@massDestroy')->name('crm-customers.massDestroy');
    Route::resource('crm-customers', 'CrmCustomerController');

    // Crm Note
    Route::delete('crm-notes/destroy', 'CrmNoteController@massDestroy')->name('crm-notes.massDestroy');
    Route::resource('crm-notes', 'CrmNoteController');

    // Crm Document
    Route::delete('crm-documents/destroy', 'CrmDocumentController@massDestroy')->name('crm-documents.massDestroy');
    Route::post('crm-documents/media', 'CrmDocumentController@storeMedia')->name('crm-documents.storeMedia');
    Route::post('crm-documents/ckmedia', 'CrmDocumentController@storeCKEditorImages')->name('crm-documents.storeCKEditorImages');
    Route::resource('crm-documents', 'CrmDocumentController');

    // Product Category
    Route::delete('product-categories/destroy', 'ProductCategoryController@massDestroy')->name('product-categories.massDestroy');
    Route::post('product-categories/media', 'ProductCategoryController@storeMedia')->name('product-categories.storeMedia');
    Route::post('product-categories/ckmedia', 'ProductCategoryController@storeCKEditorImages')->name('product-categories.storeCKEditorImages');
    Route::resource('product-categories', 'ProductCategoryController');

    // Product Tag
    Route::delete('product-tags/destroy', 'ProductTagController@massDestroy')->name('product-tags.massDestroy');
    Route::resource('product-tags', 'ProductTagController');

    // Product
    Route::delete('products/destroy', 'ProductController@massDestroy')->name('products.massDestroy');
    Route::post('products/media', 'ProductController@storeMedia')->name('products.storeMedia');
    Route::post('products/ckmedia', 'ProductController@storeCKEditorImages')->name('products.storeCKEditorImages');
    Route::resource('products', 'ProductController');

    // User Alerts
    Route::delete('user-alerts/destroy', 'UserAlertsController@massDestroy')->name('user-alerts.massDestroy');
    Route::get('user-alerts/read', 'UserAlertsController@read');
    Route::resource('user-alerts', 'UserAlertsController', ['except' => ['edit', 'update']]);

    // Task Status
    Route::delete('task-statuses/destroy', 'TaskStatusController@massDestroy')->name('task-statuses.massDestroy');
    Route::resource('task-statuses', 'TaskStatusController');

    // Task Tag
    Route::delete('task-tags/destroy', 'TaskTagController@massDestroy')->name('task-tags.massDestroy');
    Route::resource('task-tags', 'TaskTagController');

    // Task
    Route::delete('tasks/destroy', 'TaskController@massDestroy')->name('tasks.massDestroy');
    Route::post('tasks/media', 'TaskController@storeMedia')->name('tasks.storeMedia');
    Route::post('tasks/ckmedia', 'TaskController@storeCKEditorImages')->name('tasks.storeCKEditorImages');
    Route::resource('tasks', 'TaskController');

    // Tasks Calendar
    Route::resource('tasks-calendars', 'TasksCalendarController', ['except' => ['create', 'store', 'edit', 'update', 'show', 'destroy']]);

    // Contact Company
    Route::delete('contact-companies/destroy', 'ContactCompanyController@massDestroy')->name('contact-companies.massDestroy');
    Route::resource('contact-companies', 'ContactCompanyController');

    // Contact Contacts
    Route::delete('contact-contacts/destroy', 'ContactContactsController@massDestroy')->name('contact-contacts.massDestroy');
    Route::resource('contact-contacts', 'ContactContactsController');

    // Construction
    Route::delete('constructions/destroy', 'ConstructionController@massDestroy')->name('constructions.massDestroy');
    Route::resource('constructions', 'ConstructionController');

    // Application
    Route::delete('applications/destroy', 'ApplicationController@massDestroy')->name('applications.massDestroy');
    Route::resource('applications', 'ApplicationController');

    // Application Products
    Route::delete('application-products/destroy', 'ApplicationProductsController@massDestroy')->name('application-products.massDestroy');
    Route::resource('application-products', 'ApplicationProductsController');

    // Application Path
    Route::delete('application-paths/destroy', 'ApplicationPathController@massDestroy')->name('application-paths.massDestroy');
    Route::resource('application-paths', 'ApplicationPathController');

    // Application Status
    Route::delete('application-statuses/destroy', 'ApplicationStatusController@massDestroy')->name('application-statuses.massDestroy');
    Route::resource('application-statuses', 'ApplicationStatusController');

    // Application Log
    Route::delete('application-logs/destroy', 'ApplicationLogController@massDestroy')->name('application-logs.massDestroy');
    Route::resource('application-logs', 'ApplicationLogController');
});


Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth']], function () {
    // Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
    }
});
