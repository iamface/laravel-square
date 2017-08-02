<?php

Route::prefix('laravelsquare')->group(function () {
    Route::get('index', 'iamface\laravelsquare\LaravelSquareController@index');
    Route::get('customers/{info?}', 'iamface\laravelsquare\LaravelSquareController@getCustomers');
    Route::get('customer/{identifier}', 'iamface\laravelsquare\LaravelSquareController@getCustomer');
    Route::post('authorize', 'iamface\laravelsquare\LaravelSquareController@authorizeCard');
});