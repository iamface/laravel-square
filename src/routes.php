<?php

Route::prefix('laravelsquare')->group(function () {
    Route::get('index', 'iamface\laravelsquare\LaravelSquareController@index');
    Route::get('customers', 'iamface\laravelsquare\LaravelSquareController@getCustomers');
    Route::get('customer/{identifier}', 'iamface\laravelsquare\LaravelSquareController@getCustomer');
});