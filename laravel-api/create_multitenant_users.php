<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== CREACIÓN DE USUARIOS PARA MULTITENANT ===" . PHP_EOL . PHP_EOL;

try {
    echo "1. Configurando conexiones..." . PHP_EOL;
    
    config(['database.connections.empresa1_db' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => 'empresa1',
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]]);
    
    config(['database.connections.empresa2_db' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => 'empresa2',
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]]);
    
    echo "✓ Conexiones configuradas" . PHP_EOL;
    
    echo PHP_EOL . "2. Verificando estructura de tabla usuarios..." . PHP_EOL;
    
    $columns1 = DB::connection('empresa1_db')->select('DESCRIBE usuarios');
    echo "✓ Estructura de tabla usuarios en empresa1:" . PHP_EOL;
    foreach($columns1 as $column) {
        echo "  - {$column->Field} ({$column->Type})" . PHP_EOL;
    }
    
    echo PHP_EOL . "3. Limpiando usuarios existentes..." . PHP_EOL;
    DB::connection('empresa1_db')->table('usuarios')->delete();
    DB::connection('empresa2_db')->table('usuarios')->delete();
    echo "✓ Usuarios anteriores eliminados" . PHP_EOL;
    
    echo PHP_EOL . "4. Creando usuarios para empresa1..." . PHP_EOL;
    
    // Usuarios para empresa1 - usando 'nombre' en lugar de 'name'
    DB::connection('empresa1_db')->table('usuarios')->insert([
        [
            'nombre' => 'Admin Empresa 1',
            'email' => 'admin@empresa1.com',
            'password' => Hash::make('password123'),
            'rol' => 'admin',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'nombre' => 'Usuario Empresa 1',
            'email' => 'user@empresa1.com',
            'password' => Hash::make('password123'),
            'rol' => 'usuario',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    echo "✓ 2 usuarios creados en empresa1" . PHP_EOL;
    
    echo PHP_EOL . "5. Creando usuarios para empresa2..." . PHP_EOL;
    
    // Usuarios para empresa2 - usando 'nombre' en lugar de 'name'
    DB::connection('empresa2_db')->table('usuarios')->insert([
        [
            'nombre' => 'Admin Empresa 2',
            'email' => 'admin@empresa2.com',
            'password' => Hash::make('password123'),
            'rol' => 'admin',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'nombre' => 'Usuario Empresa 2',
            'email' => 'user@empresa2.com',
            'password' => Hash::make('password123'),
            'rol' => 'usuario',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    echo "✓ 2 usuarios creados en empresa2" . PHP_EOL;
    
    echo PHP_EOL . "6. Verificación final..." . PHP_EOL;
    
    $users1 = DB::connection('empresa1_db')->table('usuarios')->count();
    $users2 = DB::connection('empresa2_db')->table('usuarios')->count();
    
    echo "✓ Total usuarios empresa1: $users1" . PHP_EOL;
    echo "✓ Total usuarios empresa2: $users2" . PHP_EOL;
    
    // Mostrar usuarios creados
    echo PHP_EOL . "7. Usuarios creados en empresa1:" . PHP_EOL;
    $usuariosE1 = DB::connection('empresa1_db')->table('usuarios')->get();
    foreach($usuariosE1 as $user) {
        echo "  - {$user->nombre} ({$user->email}) - {$user->rol}" . PHP_EOL;
    }
    
    echo PHP_EOL . "8. Usuarios creados en empresa2:" . PHP_EOL;
    $usuariosE2 = DB::connection('empresa2_db')->table('usuarios')->get();
    foreach($usuariosE2 as $user) {
        echo "  - {$user->nombre} ({$user->email}) - {$user->rol}" . PHP_EOL;
    }
    
    echo PHP_EOL . "🎉 ¡USUARIOS MULTITENANT CREADOS EXITOSAMENTE!" . PHP_EOL;
    echo PHP_EOL . "📝 CREDENCIALES DE ACCESO:" . PHP_EOL;
    echo "===========================================" . PHP_EOL;
    echo "EMPRESA 1 (empresa1.localhost):" . PHP_EOL;
    echo "- admin@empresa1.com / password123" . PHP_EOL;
    echo "- user@empresa1.com / password123" . PHP_EOL;
    echo PHP_EOL;
    echo "EMPRESA 2 (empresa2.localhost):" . PHP_EOL;
    echo "- admin@empresa2.com / password123" . PHP_EOL;
    echo "- user@empresa2.com / password123" . PHP_EOL;
    echo "===========================================" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Ubicación: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}