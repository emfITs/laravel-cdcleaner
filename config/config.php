<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'current' => env('CLEANER_CURRENT', 'current'),
    'releases' => env('CLEANER_RELEASES', 'releases'),
    'web' => env('CLEANER_WEB', null),
    'keep' => env('CLEANER_KEEP', 2),
    'keep_failed' => env('CLEANER_KEEP_FAILED', true),
];
