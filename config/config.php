<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'current' => env('CLEANER_CURRENT', 'current'),
    'releases' => env('CLEANER_RELEASES', 'releases'),
    'web' => env('CLEANER_WEB', null),
    'keep' => env('CLEANER_KEEP', 3),
    'storage_path' => env('CLEANER_STORAGE_PATH', 'cdcleaner'),
    'keep_failed' => env('CLEANER_KEEP_FAILED', true),
    'add_actual_path_after_run' => env('CLEANER_ADD_ACTUAL_PATH_AFTER_RUN', true),
];
