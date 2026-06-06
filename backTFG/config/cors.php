<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | Configuración CORS para la API de Laravel. Permite al frontend (Vite en
    | localhost:5173) comunicarse con el backend sin bloqueos del navegador.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    // Tiempo en segundos que el navegador cachea los preflights OPTIONS
    // 7200 = 2 horas, evita repetir el preflight en cada petición
    'max_age' => 7200,

    'supports_credentials' => false,

];
