<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TempAuthController;

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
        'example' => 'https://empresa1.localhost/api/usuarios/listUsers',
        'temp_login' => 'http://127.0.0.1:8000/api/login (temporal)'
    ]);
});

// Rutas temporales para testing (funcionan sin tenant)
Route::post('/login', [TempAuthController::class, 'login']);
Route::post('/register', [TempAuthController::class, 'register']);

// Ruta protegida temporal
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
