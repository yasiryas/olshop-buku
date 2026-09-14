<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AgenWebsite API
    |--------------------------------------------------------------------------
    |
    | Kredensial API sebaiknya didefinisikan di .env (WIGATI_AGENWEB_*).
    | Bila kosong, aplikasi membaca nilai legacy di tabel settings (DB).
    |
    */

    'api_key' => env('WIGATI_AGENWEB_API_KEY', ''),

    'origin_city_id' => env('WIGATI_AGENWEB_ORIGIN_CITY_ID', ''),

    'origin_postal_code' => env('WIGATI_AGENWEB_ORIGIN_POSTAL_CODE', ''),
];