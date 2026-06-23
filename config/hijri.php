<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default locale
    |--------------------------------------------------------------------------
    |
    | Language tag used for month/day names and notations when none is passed
    | explicitly. Bundled: "ar", "en", "bn", "tr". Unknown tags fall back to the default.
    |
    */
    'default_locale' => env('HIJRI_LOCALE', 'ar'),

    /*
    |--------------------------------------------------------------------------
    | Default separator
    |--------------------------------------------------------------------------
    |
    | Separator used by the format() helper, e.g. "/" => 17/02/1403.
    |
    */
    'default_separator' => '/',

];
