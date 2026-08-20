<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Access Code
    |--------------------------------------------------------------------------
    |
    | Shared code protecting the public pages before real authentication
    | (Fortify) is required. This is not meant to be a strong security
    | boundary, only a simple public "curtain" for the site.
    |
    */

    'code' => env('ACCESS_CODE'),

];
