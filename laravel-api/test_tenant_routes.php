<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';

// Inicializar la aplicación
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== PRUEBA DE RUTAS MULTITENANT ===\n\n";

// Simular una petición a la ruta de login
$request = Request::create('http://empresa1.localhost:8000/api/login', 'POST', [
    'email' => 'admin@empresa1.com',
    'password' => 'password123'
]);

// Agregar headers necesarios
$request->headers->set('Host', 'empresa1.localhost:8000');
$request->headers->set('Content-Type', 'application/json');
$request->headers->set('Accept', 'application/json');

try {
    echo "1. Simulando petición POST a /api/login en empresa1.localhost...\n";
    
    $response = $kernel->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
    
    if ($response->getStatusCode() === 404) {
        echo "\n❌ Ruta no encontrada. Verificando configuración...\n";
        
        // Listar todas las rutas disponibles
        $routes = Route::getRoutes();
        echo "Rutas registradas:\n";
        foreach ($routes->getRoutes() as $route) {
            $uri = $route->uri();
            $methods = implode('|', $route->methods());
            echo "  $methods $uri\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DE LA PRUEBA ===\n";

?>