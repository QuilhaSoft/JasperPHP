<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Folder
    |--------------------------------------------------------------------------
    |
    | The default directory where your .jrxml report files are stored.
    |
    */
    'default_folder' => env('JASPER_REPORT_FOLDER', base_path('resources/reports')),

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | Default locale and formatting settings for generated reports.
    |
    */
    'locale' => env('JASPER_LOCALE', 'en_us'),
    'dec_point' => env('JASPER_DEC_POINT', '.'),
    'thousands_sep' => env('JASPER_THOUSANDS_SEP', ','),
];
