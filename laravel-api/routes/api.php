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
Route::post('/logout', [TempAuthController::class, 'logout']);

// Ruta para obtener datos del usuario actual
Route::get('/me', [TempAuthController::class, 'me']);

// Rutas de usuarios temporales
Route::middleware('temp.auth')->group(function () {
    Route::get('/usuarios/listUsers', [TempAuthController::class, 'listUsers']);
    Route::post('/usuarios/addUser', [TempAuthController::class, 'addUser']);
});

// Rutas de tareas temporales
Route::middleware('temp.auth')->group(function () {
    Route::get('/tareas', [TempAuthController::class, 'listTareas']);
    Route::post('/tareas', [TempAuthController::class, 'addTarea']);
    Route::put('/tareas/{id}', [TempAuthController::class, 'updateTarea']);
    Route::delete('/tareas/{id}', [TempAuthController::class, 'deleteTarea']);
});

// Ruta protegida temporal
Route::middleware('temp.auth')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->attributes->get('auth_user'),
            'empresa' => $request->attributes->get('auth_empresa')
        ]);
    });
});
