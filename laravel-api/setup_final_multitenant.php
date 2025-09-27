<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

echo "=== LIMPIEZA Y CONFIGURACIÓN MULTITENANT ===" . PHP_EOL . PHP_EOL;

try {
    echo "1. Limpiando y recreando bases de datos..." . PHP_EOL;
    
    // Eliminar y recrear empresa1
    DB::statement('DROP DATABASE IF EXISTS empresa1');
    DB::statement('CREATE DATABASE empresa1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✓ Base de datos 'empresa1' recreada" . PHP_EOL;
    
    // Eliminar y recrear empresa2
    DB::statement('DROP DATABASE IF EXISTS empresa2');
    DB::statement('CREATE DATABASE empresa2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✓ Base de datos 'empresa2' recreada" . PHP_EOL;
    
    echo PHP_EOL . "2. Limpiando datos centrales..." . PHP_EOL;
    
    // Limpiar tablas centrales
    DB::table('domains')->delete();
    DB::table('tenants')->delete();
    echo "✓ Datos centrales limpiados" . PHP_EOL;
    
    echo PHP_EOL . "3. Creando tenants y dominios..." . PHP_EOL;
    
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
    echo "✓ Tenant 'empresa1' creado" . PHP_EOL;
    
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
    echo "✓ Tenant 'empresa2' creado" . PHP_EOL;
    
    echo PHP_EOL . "4. Configurando conexiones..." . PHP_EOL;
    
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
    
    echo PHP_EOL . "5. Ejecutando migraciones de tenants..." . PHP_EOL;
    
    // Ejecutar migraciones en empresa1
    Artisan::call('migrate', [
        '--database' => 'empresa1_db',
        '--path' => 'database/migrations/tenant',
        '--force' => true
    ]);
    echo "✓ Migraciones ejecutadas en empresa1" . PHP_EOL;
    
    // Ejecutar migraciones en empresa2
    Artisan::call('migrate', [
        '--database' => 'empresa2_db',
        '--path' => 'database/migrations/tenant',
        '--force' => true
    ]);
    echo "✓ Migraciones ejecutadas en empresa2" . PHP_EOL;
    
    echo PHP_EOL . "6. Verificando estructura..." . PHP_EOL;
    
    $tables1 = DB::connection('empresa1_db')->select('SHOW TABLES');
    echo "✓ Empresa1 - Tablas creadas: " . count($tables1) . PHP_EOL;
    foreach($tables1 as $table) {
        $tableName = array_values((array)$table)[0];
        echo "  - $tableName" . PHP_EOL;
    }
    
    $tables2 = DB::connection('empresa2_db')->select('SHOW TABLES');
    echo "✓ Empresa2 - Tablas creadas: " . count($tables2) . PHP_EOL;
    foreach($tables2 as $table) {
        $tableName = array_values((array)$table)[0];
        echo "  - $tableName" . PHP_EOL;
    }
    
    echo PHP_EOL . "7. Creando usuarios de prueba..." . PHP_EOL;
    
    // Usuarios para empresa1
    DB::connection('empresa1_db')->table('usuarios')->insert([
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
    
    // Usuarios para empresa2
    DB::connection('empresa2_db')->table('usuarios')->insert([
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
    
    echo PHP_EOL . "8. Resumen final..." . PHP_EOL;
    
    $tenants = DB::table('tenants')->count();
    $domains = DB::table('domains')->count();
    $users1 = DB::connection('empresa1_db')->table('usuarios')->count();
    $users2 = DB::connection('empresa2_db')->table('usuarios')->count();
    
    echo "✓ Tenants registrados: $tenants" . PHP_EOL;
    echo "✓ Dominios registrados: $domains" . PHP_EOL;
    echo "✓ Usuarios en empresa1: $users1" . PHP_EOL;
    echo "✓ Usuarios en empresa2: $users2" . PHP_EOL;
    
    echo PHP_EOL . "🎉 ¡CONFIGURACIÓN MULTITENANT COMPLETADA!" . PHP_EOL;
    echo PHP_EOL . "📋 ESTRUCTURA FINAL:" . PHP_EOL;
    echo "=========================================" . PHP_EOL;
    echo "BASE CENTRAL (segundo_parcial):" . PHP_EOL;
    echo "- tenants: 2 registros" . PHP_EOL;
    echo "- domains: 2 registros" . PHP_EOL;
    echo PHP_EOL;
    echo "BASE EMPRESA1 (empresa1):" . PHP_EOL;
    echo "- usuarios: $users1 registros" . PHP_EOL;
    echo "- tareas: tabla creada" . PHP_EOL;
    echo "- personal_access_tokens: tabla creada" . PHP_EOL;
    echo PHP_EOL;
    echo "BASE EMPRESA2 (empresa2):" . PHP_EOL;
    echo "- usuarios: $users2 registros" . PHP_EOL;
    echo "- tareas: tabla creada" . PHP_EOL;
    echo "- personal_access_tokens: tabla creada" . PHP_EOL;
    echo PHP_EOL;
    echo "👤 CREDENCIALES:" . PHP_EOL;
    echo "empresa1.localhost: admin@empresa1.com / password123" . PHP_EOL;
    echo "empresa2.localhost: admin@empresa2.com / password123" . PHP_EOL;
    echo "=========================================" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Ubicación: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}