<?php

Route::get('index', 'iamface\laravelsquare\LaravelSquareController@index');
Route::get('customer/{identifier}', 'iamface\laravelsquare\LaravelSquareController@getCustomer');