<?php

return [

    'sets' => [
        // 'default' => [
        //     'path' => 'resources/svg',
        //     'prefix' => 'icon',
        //     'fallback' => '',
        // ],
    ],

    'class' => '',

    'attributes' => [],

    /*
    |--------------------------------------------------------------------------
    | Global Fallback Icon
    |--------------------------------------------------------------------------
    | Gunakan icon fallback bila ada icon dari package manapun yang hilang
    | sehingga halaman web tidak crash dengan 500 error.
    */
    'fallback' => '',

    'components' => [
        'disabled' => false,
        'default' => 'icon',
    ],

];
