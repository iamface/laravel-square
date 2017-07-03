<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Square Personal Access Token
    |--------------------------------------------------------------------------
     */
    'token' => env('SQUARE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Square Location
    |--------------------------------------------------------------------------
     */
    'location' => env('SQUARE_STORE_NAME'),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
     */
    'currency' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | Include Non-Capable Locations
    |--------------------------------------------------------------------------
    |
    | This value determines if all locations will be returned (including non-capable
    | credit card processing locations). Only credit card processing capable locations
    | will be able to create authorizations and transactions. If you still want to
    | return and list locations that cannot process cards for display only, set
    | this to true.
     */
    'non_capable_locations' => false

];
