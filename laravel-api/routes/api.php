<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Central)
|--------------------------------------------------------------------------
|
| These are the central API routes. The tenant-specific routes are now
| located in routes/tenant.php and will be accessible through subdomains.
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'API Central - Sistema Multitenancy',
        'info' => 'Las rutas de la aplicación están disponibles en subdominios',
        'example' => 'https://empresa1.localhost/api/usuarios/listUsers'
    ]);
});
