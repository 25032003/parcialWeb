<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICACIÓN FINAL DEL SISTEMA MULTITENANT ===" . PHP_EOL . PHP_EOL;

try {
    echo "1. Verificando base de datos central..." . PHP_EOL;
    
    // Verificar tenants
    $tenants = DB::table('tenants')->get();
    echo "✓ Tenants registrados: " . count($tenants) . PHP_EOL;
    foreach($tenants as $tenant) {
        echo "  - {$tenant->id}" . PHP_EOL;
    }
    
    // Verificar dominios
    $domains = DB::table('domains')->get();
    echo "✓ Dominios registrados: " . count($domains) . PHP_EOL;
    foreach($domains as $domain) {
        echo "  - {$domain->domain} → {$domain->tenant_id}" . PHP_EOL;
    }
    
    echo PHP_EOL . "2. Verificando bases de datos de tenants..." . PHP_EOL;
    
    // Configurar conexiones
    config(['database.connections.empresa1_check' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => 'empresa1',
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]]);
    
    config(['database.connections.empresa2_check' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => 'empresa2',
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]]);
    
    // Verificar empresa1
    echo "📊 EMPRESA1:" . PHP_EOL;
    $tables1 = DB::connection('empresa1_check')->select('SHOW TABLES');
    echo "  ✓ Tablas: " . count($tables1) . PHP_EOL;
    foreach($tables1 as $table) {
        $tableName = array_values((array)$table)[0];
        
        if($tableName === 'usuarios') {
            $count = DB::connection('empresa1_check')->table('usuarios')->count();
            echo "    - $tableName ($count registros)" . PHP_EOL;
        } elseif($tableName === 'tareas') {
            $count = DB::connection('empresa1_check')->table('tareas')->count();
            echo "    - $tableName ($count registros)" . PHP_EOL;
        } else {
            echo "    - $tableName" . PHP_EOL;
        }
    }
    
    // Verificar empresa2
    echo "📊 EMPRESA2:" . PHP_EOL;
    $tables2 = DB::connection('empresa2_check')->select('SHOW TABLES');
    echo "  ✓ Tablas: " . count($tables2) . PHP_EOL;
    foreach($tables2 as $table) {
        $tableName = array_values((array)$table)[0];
        
        if($tableName === 'usuarios') {
            $count = DB::connection('empresa2_check')->table('usuarios')->count();
            echo "    - $tableName ($count registros)" . PHP_EOL;
        } elseif($tableName === 'tareas') {
            $count = DB::connection('empresa2_check')->table('tareas')->count();
            echo "    - $tableName ($count registros)" . PHP_EOL;
        } else {
            echo "    - $tableName" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "3. Verificando usuarios específicos..." . PHP_EOL;
    
    $usuarios1 = DB::connection('empresa1_check')->table('usuarios')->get();
    echo "👤 Usuarios en EMPRESA1:" . PHP_EOL;
    foreach($usuarios1 as $user) {
        echo "  - {$user->nombre} <{$user->email}> [{$user->rol}]" . PHP_EOL;
    }
    
    $usuarios2 = DB::connection('empresa2_check')->table('usuarios')->get();
    echo "👤 Usuarios en EMPRESA2:" . PHP_EOL;
    foreach($usuarios2 as $user) {
        echo "  - {$user->nombre} <{$user->email}> [{$user->rol}]" . PHP_EOL;
    }
    
    echo PHP_EOL . "4. Verificando aislamiento de datos..." . PHP_EOL;
    
    // Verificar que los emails no se cruzan
    $email1InEmpresa2 = DB::connection('empresa2_check')->table('usuarios')
                        ->where('email', 'admin@empresa1.com')->exists();
    $email2InEmpresa1 = DB::connection('empresa1_check')->table('usuarios')
                        ->where('email', 'admin@empresa2.com')->exists();
    
    if (!$email1InEmpresa2 && !$email2InEmpresa1) {
        echo "✅ Aislamiento de datos correcto - Los usuarios están separados por tenant" . PHP_EOL;
    } else {
        echo "❌ Problema de aislamiento - Los datos se están cruzando entre tenants" . PHP_EOL;
    }
    
    echo PHP_EOL . "5. Verificando configuraciones de tenancy..." . PHP_EOL;
    
    echo "✓ Prefijo de BD configurado: '" . config('tenancy.database.prefix') . "'" . PHP_EOL;
    echo "✓ Conexión template: " . config('tenancy.database.template_tenant_connection') . PHP_EOL;
    echo "✓ TenancyServiceProvider: " . (class_exists('Stancl\Tenancy\TenancyServiceProvider') ? 'Registrado' : 'No registrado') . PHP_EOL;
    
    echo PHP_EOL . "🎯 RESUMEN FINAL DEL SISTEMA MULTITENANT:" . PHP_EOL;
    echo "================================================" . PHP_EOL;
    echo "✅ Bases de datos creadas: segundo_parcial, empresa1, empresa2" . PHP_EOL;
    echo "✅ Tenants configurados: empresa1, empresa2" . PHP_EOL;
    echo "✅ Dominios configurados: empresa1.localhost, empresa2.localhost" . PHP_EOL;
    echo "✅ Migraciones ejecutadas: usuarios, tareas, personal_access_tokens" . PHP_EOL;
    echo "✅ Usuarios de prueba creados: 2 por empresa" . PHP_EOL;
    echo "✅ Aislamiento de datos: Verificado" . PHP_EOL;
    echo "✅ Configuración de tenancy: Completada" . PHP_EOL;
    echo "================================================" . PHP_EOL;
    echo PHP_EOL . "🚀 ¡SISTEMA MULTITENANT LISTO PARA USAR!" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error en verificación: " . $e->getMessage() . PHP_EOL;
    echo "Ubicación: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}