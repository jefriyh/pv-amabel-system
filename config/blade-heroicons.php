<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Prefix Default
    |--------------------------------------------------------------------------
    */
    'prefix' => 'heroicon',

    'fallback' => 'fallback',

    'paths' => array_values(array_filter([
        __DIR__ . '/../vendor/blade-ui-kit/blade-heroicons/resources/svg',
        __DIR__ . '/../resources/svg/heroicons',
        __DIR__ . '/../resources/svg',
        base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg'),
        resource_path('svg/heroicons'),
        resource_path('svg'),
        public_path('vendor/blade-heroicons'),
    ], 'is_dir')),

    'class' => '',

    'attributes' => [],

];
