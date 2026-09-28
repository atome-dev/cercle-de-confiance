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

    /*
    |--------------------------------------------------------------------------
    | Anonymous Contact
    |--------------------------------------------------------------------------
    |
    | When disabled, the contact form requires the sender's name and email
    | and no longer offers to stay anonymous. The rest of the dossier flow
    | (tracking code, encryption) is unchanged.
    |
    */

    'anonymous_contact_enabled' => (bool) env('ANONYMOUS_CONTACT_ENABLED', true),

];
