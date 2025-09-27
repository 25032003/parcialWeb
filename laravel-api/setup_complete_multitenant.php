<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

echo "=== CONFIGURACIÓN MULTITENANT - VERSIÓN FINAL ===" . PHP_EOL . PHP_EOL;

try {
    echo "1. Creando bases de datos..." . PHP_EOL;
    
    // Crear bases de datos
    DB::statement('CREATE DATABASE IF NOT EXISTS empresa1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✓ Base de datos 'empresa1' creada" . PHP_EOL;
    
    DB::statement('CREATE DATABASE IF NOT EXISTS empresa2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✓ Base de datos 'empresa2' creada" . PHP_EOL;
    
    echo PHP_EOL . "2. Ejecutando migraciones centrales..." . PHP_EOL;
    
    // Ejecutar migraciones centrales (tenants y domains)
    Artisan::call('migrate', ['--force' => true]);
    echo "✓ Migraciones centrales completadas" . PHP_EOL;
    
    echo PHP_EOL . "3. Creando tenants y dominios..." . PHP_EOL;
    
    // Limpiar datos existentes
    DB::table('domains')->delete();
    DB::table('tenants')->delete();
    
    // Crear tenant empresa1
    DB::table('tenants')->insert([
        'id' => 'empresa1',
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    DB::table('domains')->insert([
        'domain' => 'empresa1.localhost',
        'tenant_id' => 'empresa1',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✓ Tenant 'empresa1' y dominio creados" . PHP_EOL;
    
    // Crear tenant empresa2
    DB::table('tenants')->insert([
        'id' => 'empresa2',
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    DB::table('domains')->insert([
        'domain' => 'empresa2.localhost',
        'tenant_id' => 'empresa2',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✓ Tenant 'empresa2' y dominio creados" . PHP_EOL;
    
    echo PHP_EOL . "4. Configurando conexiones temporales..." . PHP_EOL;
    
    // Configurar conexiones temporales
    config(['database.connections.empresa1_temp' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => 'empresa1',
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]]);
    
    config(['database.connections.empresa2_temp' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => 'empresa2',
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]]);
    
    echo "✓ Conexiones temporales configuradas" . PHP_EOL;
    
    echo PHP_EOL . "5. Ejecutando migraciones de tenants..." . PHP_EOL;
    
    // Ejecutar migraciones específicas de tenants en empresa1
    Artisan::call('migrate', [
        '--database' => 'empresa1_temp',
        '--path' => 'database/migrations/tenant',
        '--force' => true
    ]);
    echo "✓ Migraciones de tenant ejecutadas en empresa1" . PHP_EOL;
    
    // Ejecutar migraciones específicas de tenants en empresa2
    Artisan::call('migrate', [
        '--database' => 'empresa2_temp',
        '--path' => 'database/migrations/tenant',
        '--force' => true
    ]);
    echo "✓ Migraciones de tenant ejecutadas en empresa2" . PHP_EOL;
    
    echo PHP_EOL . "6. Verificando tablas creadas..." . PHP_EOL;
    
    $tables1 = DB::connection('empresa1_temp')->select('SHOW TABLES');
    echo "✓ Tablas en empresa1: " . count($tables1) . PHP_EOL;
    foreach($tables1 as $table) {
        $tableName = array_values((array)$table)[0];
        echo "  - $tableName" . PHP_EOL;
    }
    
    $tables2 = DB::connection('empresa2_temp')->select('SHOW TABLES');
    echo "✓ Tablas en empresa2: " . count($tables2) . PHP_EOL;
    foreach($tables2 as $table) {
        $tableName = array_values((array)$table)[0];
        echo "  - $tableName" . PHP_EOL;
    }
    
    echo PHP_EOL . "7. Creando usuarios de prueba..." . PHP_EOL;
    
    // Crear usuarios en empresa1
    DB::connection('empresa1_temp')->table('usuarios')->insert([
        [
            'name' => 'Admin Empresa 1',
            'email' => 'admin@empresa1.com',
            'password' => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'name' => 'Usuario Empresa 1',
            'email' => 'user@empresa1.com',
            'password' => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    echo "✓ Usuarios creados en empresa1" . PHP_EOL;
    
    // Crear usuarios en empresa2
    DB::connection('empresa2_temp')->table('usuarios')->insert([
        [
            'name' => 'Admin Empresa 2',
            'email' => 'admin@empresa2.com',
            'password' => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'name' => 'Usuario Empresa 2',
            'email' => 'user@empresa2.com',
            'password' => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    echo "✓ Usuarios creados en empresa2" . PHP_EOL;
    
    echo PHP_EOL . "8. Verificación final..." . PHP_EOL;
    
    $totalTenants = DB::table('tenants')->count();
    $totalDomains = DB::table('domains')->count();
    $users1 = DB::connection('empresa1_temp')->table('usuarios')->count();
    $users2 = DB::connection('empresa2_temp')->table('usuarios')->count();
    
    echo "✓ Tenants: $totalTenants" . PHP_EOL;
    echo "✓ Dominios: $totalDomains" . PHP_EOL;
    echo "✓ Usuarios empresa1: $users1" . PHP_EOL;
    echo "✓ Usuarios empresa2: $users2" . PHP_EOL;
    
    echo PHP_EOL . "🎉 ¡SISTEMA MULTITENANT CONFIGURADO CORRECTAMENTE!" . PHP_EOL;
    echo PHP_EOL . "📊 RESUMEN FINAL:" . PHP_EOL;
    echo "┌─────────────────────────────────────────────┐" . PHP_EOL;
    echo "│ BASE CENTRAL: segundo_parcial               │" . PHP_EOL;
    echo "│ - tenants (2 registros)                     │" . PHP_EOL;
    echo "│ - domains (2 registros)                     │" . PHP_EOL;
    echo "│                                             │" . PHP_EOL;
    echo "│ BASE EMPRESA1: empresa1                     │" . PHP_EOL;
    echo "│ - usuarios (2 registros)                    │" . PHP_EOL;
    echo "│ - tareas                                    │" . PHP_EOL;
    echo "│ - personal_access_tokens                    │" . PHP_EOL;
    echo "│                                             │" . PHP_EOL;
    echo "│ BASE EMPRESA2: empresa2                     │" . PHP_EOL;
    echo "│ - usuarios (2 registros)                    │" . PHP_EOL;
    echo "│ - tareas                                    │" . PHP_EOL;
    echo "│ - personal_access_tokens                    │" . PHP_EOL;
    echo "└─────────────────────────────────────────────┘" . PHP_EOL;
    echo PHP_EOL . "👤 CREDENCIALES DE PRUEBA:" . PHP_EOL;
    echo "Empresa 1: admin@empresa1.com / password123" . PHP_EOL;
    echo "Empresa 2: admin@empresa2.com / password123" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Archivo: " . $e->getFile() . PHP_EOL;
    echo "Línea: " . $e->getLine() . PHP_EOL;
}