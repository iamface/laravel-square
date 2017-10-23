<?php

Route::prefix('laravelsquare')->group(function () {
    Route::get('index', 'iamface\laravelsquare\LaravelSquareController@index');

    // List customers
    Route::get('customers/{info?}', 'iamface\laravelsquare\LaravelSquareController@getCustomers');

    // Get customer
    Route::get('customer/{identifier}', 'iamface\laravelsquare\LaravelSquareController@getCustomer');

    // Authorize card
    Route::post('authorize', 'iamface\laravelsquare\LaravelSquareController@authorizeCard');

    // List transactions
    Route::get('transactions/{identifier}', 'iamface\laravelsquare\LaravelSquareController@listTransactions');

    // List locations
    Route::get('locations', 'iamface\laravelsquare\LaravelSquareController@listLocations');
});