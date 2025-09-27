<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Facades\Hash;

echo "=== CONFIGURACIÓN MULTITENANT: EMPRESA1 Y EMPRESA2 ===" . PHP_EOL . PHP_EOL;

try {
    echo "1. Creando bases de datos..." . PHP_EOL;
    
    // Crear bases de datos
    DB::statement('CREATE DATABASE IF NOT EXISTS empresa1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✓ Base de datos 'empresa1' creada" . PHP_EOL;
    
    DB::statement('CREATE DATABASE IF NOT EXISTS empresa2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✓ Base de datos 'empresa2' creada" . PHP_EOL;
    
    echo PHP_EOL . "2. Ejecutando migraciones en base central..." . PHP_EOL;
    
    // Ejecutar migraciones en la base central (para tenants y domains)
    Artisan::call('migrate', ['--force' => true]);
    echo "✓ Migraciones centrales completadas" . PHP_EOL;
    
    echo PHP_EOL . "3. Creando tenants..." . PHP_EOL;
    
    // Limpiar tenants existentes
    Tenant::query()->delete();
    Domain::query()->delete();
    
    // Crear tenant empresa1
    $tenant1 = Tenant::create(['id' => 'empresa1']);
    $tenant1->domains()->create(['domain' => 'empresa1.localhost']);
    echo "✓ Tenant 'empresa1' creado con dominio 'empresa1.localhost'" . PHP_EOL;
    
    // Crear tenant empresa2
    $tenant2 = Tenant::create(['id' => 'empresa2']);
    $tenant2->domains()->create(['domain' => 'empresa2.localhost']);
    echo "✓ Tenant 'empresa2' creado con dominio 'empresa2.localhost'" . PHP_EOL;
    
    echo PHP_EOL . "4. Ejecutando migraciones en bases de datos de tenants..." . PHP_EOL;
    
    // Configurar conexión para empresa1
    config(['database.connections.tenant_empresa1' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => 'empresa1',
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]]);
    
    // Configurar conexión para empresa2
    config(['database.connections.tenant_empresa2' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => 'empresa2',
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]]);
    
    // Ejecutar migraciones en empresa1
    Artisan::call('migrate', [
        '--database' => 'tenant_empresa1',
        '--force' => true,
        '--path' => 'database/migrations'
    ]);
    echo "✓ Migraciones ejecutadas en base de datos 'empresa1'" . PHP_EOL;
    
    // Ejecutar migraciones en empresa2
    Artisan::call('migrate', [
        '--database' => 'tenant_empresa2',
        '--force' => true,
        '--path' => 'database/migrations'
    ]);
    echo "✓ Migraciones ejecutadas en base de datos 'empresa2'" . PHP_EOL;
    
    echo PHP_EOL . "5. Creando usuarios de prueba..." . PHP_EOL;
    
    // Crear usuarios en empresa1
    DB::connection('tenant_empresa1')->table('usuarios')->insert([
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
    DB::connection('tenant_empresa2')->table('usuarios')->insert([
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
    
    echo PHP_EOL . "6. Verificando configuración..." . PHP_EOL;
    
    // Verificar tenants
    $totalTenants = Tenant::count();
    echo "✓ Total de tenants: $totalTenants" . PHP_EOL;
    
    // Verificar dominios
    $totalDomains = Domain::count();
    echo "✓ Total de dominios: $totalDomains" . PHP_EOL;
    
    // Verificar usuarios
    $users1 = DB::connection('tenant_empresa1')->table('usuarios')->count();
    $users2 = DB::connection('tenant_empresa2')->table('usuarios')->count();
    echo "✓ Usuarios en empresa1: $users1" . PHP_EOL;
    echo "✓ Usuarios en empresa2: $users2" . PHP_EOL;
    
    // Verificar tablas
    echo PHP_EOL . "7. Verificando estructura de tablas..." . PHP_EOL;
    
    $tables1 = DB::connection('tenant_empresa1')->select('SHOW TABLES');
    echo "✓ Tablas en empresa1: " . count($tables1) . PHP_EOL;
    foreach($tables1 as $table) {
        $tableName = array_values((array)$table)[0];
        echo "  - $tableName" . PHP_EOL;
    }
    
    $tables2 = DB::connection('tenant_empresa2')->select('SHOW TABLES');
    echo "✓ Tablas en empresa2: " . count($tables2) . PHP_EOL;
    foreach($tables2 as $table) {
        $tableName = array_values((array)$table)[0];
        echo "  - $tableName" . PHP_EOL;
    }
    
    echo PHP_EOL . "🎉 ¡CONFIGURACIÓN MULTITENANT COMPLETADA!" . PHP_EOL;
    echo PHP_EOL . "Usuarios de prueba:" . PHP_EOL;
    echo "Empresa1 (empresa1.localhost): admin@empresa1.com / password123" . PHP_EOL;
    echo "Empresa2 (empresa2.localhost): admin@empresa2.com / password123" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}