<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Support\Facades\Hash;

echo "=== CONFIGURACIÓN MULTITENANT SIMPLIFICADA ===" . PHP_EOL . PHP_EOL;

try {
    echo "1. Creando bases de datos..." . PHP_EOL;
    
    // Crear bases de datos
    DB::statement('CREATE DATABASE IF NOT EXISTS empresa1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✓ Base de datos 'empresa1' creada" . PHP_EOL;
    
    DB::statement('CREATE DATABASE IF NOT EXISTS empresa2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✓ Base de datos 'empresa2' creada" . PHP_EOL;
    
    echo PHP_EOL . "2. Ejecutando migraciones en base central..." . PHP_EOL;
    
    // Ejecutar migraciones en la base central
    Artisan::call('migrate', ['--force' => true]);
    echo "✓ Migraciones centrales completadas" . PHP_EOL;
    
    echo PHP_EOL . "3. Creando tenants y dominios..." . PHP_EOL;
    
    // Limpiar datos existentes
    Domain::query()->delete();
    Tenant::query()->delete();
    
    // Crear tenant empresa1 directamente en la tabla
    DB::table('tenants')->insert([
        'id' => 'empresa1',
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✓ Tenant 'empresa1' creado" . PHP_EOL;
    
    // Crear dominio para empresa1
    DB::table('domains')->insert([
        'domain' => 'empresa1.localhost',
        'tenant_id' => 'empresa1',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✓ Dominio 'empresa1.localhost' creado" . PHP_EOL;
    
    // Crear tenant empresa2 directamente en la tabla
    DB::table('tenants')->insert([
        'id' => 'empresa2',
        'data' => '{}',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✓ Tenant 'empresa2' creado" . PHP_EOL;
    
    // Crear dominio para empresa2
    DB::table('domains')->insert([
        'domain' => 'empresa2.localhost',
        'tenant_id' => 'empresa2',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✓ Dominio 'empresa2.localhost' creado" . PHP_EOL;
    
    echo PHP_EOL . "4. Ejecutando migraciones en bases de datos de tenants..." . PHP_EOL;
    
    // Configurar conexión temporal para empresa1
    config(['database.connections.temp_empresa1' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => 'empresa1',
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]]);
    
    // Configurar conexión temporal para empresa2
    config(['database.connections.temp_empresa2' => [
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
        '--database' => 'temp_empresa1',
        '--force' => true
    ]);
    echo "✓ Migraciones ejecutadas en 'empresa1'" . PHP_EOL;
    
    // Ejecutar migraciones en empresa2
    Artisan::call('migrate', [
        '--database' => 'temp_empresa2',
        '--force' => true
    ]);
    echo "✓ Migraciones ejecutadas en 'empresa2'" . PHP_EOL;
    
    echo PHP_EOL . "5. Creando usuarios de prueba..." . PHP_EOL;
    
    // Crear usuarios en empresa1
    DB::connection('temp_empresa1')->table('usuarios')->insert([
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
    echo "✓ 2 usuarios creados en empresa1" . PHP_EOL;
    
    // Crear usuarios en empresa2
    DB::connection('temp_empresa2')->table('usuarios')->insert([
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
    echo "✓ 2 usuarios creados en empresa2" . PHP_EOL;
    
    echo PHP_EOL . "6. Verificación final..." . PHP_EOL;
    
    // Verificar estructura
    $tenants = DB::table('tenants')->count();
    $domains = DB::table('domains')->count();
    echo "✓ Tenants creados: $tenants" . PHP_EOL;
    echo "✓ Dominios creados: $domains" . PHP_EOL;
    
    $users1 = DB::connection('temp_empresa1')->table('usuarios')->count();
    $users2 = DB::connection('temp_empresa2')->table('usuarios')->count();
    echo "✓ Usuarios en empresa1: $users1" . PHP_EOL;
    echo "✓ Usuarios en empresa2: $users2" . PHP_EOL;
    
    echo PHP_EOL . "🎉 ¡SISTEMA MULTITENANT CONFIGURADO EXITOSAMENTE!" . PHP_EOL;
    echo PHP_EOL . "📋 RESUMEN DE CONFIGURACIÓN:" . PHP_EOL;
    echo "- Base central: segundo_parcial (tenants, domains)" . PHP_EOL;
    echo "- Base empresa1: usuarios, tareas, personal_access_tokens" . PHP_EOL;
    echo "- Base empresa2: usuarios, tareas, personal_access_tokens" . PHP_EOL;
    echo PHP_EOL . "👤 USUARIOS DE PRUEBA:" . PHP_EOL;
    echo "Empresa1: admin@empresa1.com / password123" . PHP_EOL;
    echo "Empresa2: admin@empresa2.com / password123" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Línea: " . $e->getLine() . PHP_EOL;
}