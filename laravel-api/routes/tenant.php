<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TareaController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
});

// API Routes para tenants
Route::prefix('api')->middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    
    // Rutas públicas (sin autenticación)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Rutas protegidas (requieren autenticación)
    Route::middleware('auth:sanctum')->group(function () {
        // Ruta para obtener usuario actual
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        
        // Logout
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // Rutas para el controlador de usuarios
        Route::prefix('usuarios')->group(function () {
            Route::get('/listUsers', [UsuarioController::class, 'index']);
            Route::post('/addUser', [UsuarioController::class, 'store']);
            Route::get('/getUser/{id}', [UsuarioController::class, 'show']);
            Route::put('/updateUser/{id}', [UsuarioController::class, 'update']);
            Route::delete('/deleteUser/{id}', [UsuarioController::class, 'destroy']);
        });

        // Rutas para tareas
        Route::prefix('tareas')->group(function () {
            Route::get('/', [TareaController::class, 'index']); // listado
            Route::post('/', [TareaController::class, 'store']); // crear
            Route::get('/export/pendientes', [TareaController::class, 'exportPendientes']); // CSV
        });
    });
});
