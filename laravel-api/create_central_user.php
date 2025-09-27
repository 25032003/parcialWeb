<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

// Configurar Eloquent
$capsule = new DB;

// Conexión a la base de datos central
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'segundo_parcial',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
], 'default');

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== CREACIÓN DE USUARIO EN BASE DE DATOS CENTRAL ===\n\n";

try {
    // Verificar tablas disponibles
    echo "1. Verificando tablas en segundo_parcial...\n";
    $tables = DB::select("SHOW TABLES");
    
    echo "Tablas encontradas:\n";
    foreach ($tables as $table) {
        $table_name = array_values((array)$table)[0];
        echo "  - $table_name\n";
    }
    echo "\n";
    
    // Verificar si existe la tabla users
    $hasUsersTable = false;
    $hasUsuariosTable = false;
    
    foreach ($tables as $table) {
        $table_name = array_values((array)$table)[0];
        if ($table_name === 'users') {
            $hasUsersTable = true;
        }
        if ($table_name === 'usuarios') {
            $hasUsuariosTable = true;
        }
    }
    
    if ($hasUsersTable) {
        echo "2. Creando usuario en tabla 'users'...\n";
        
        // Verificar estructura de la tabla users
        $columns = DB::select("DESCRIBE users");
        echo "Estructura de la tabla users:\n";
        foreach ($columns as $column) {
            echo "  - {$column->Field} ({$column->Type})\n";
        }
        echo "\n";
        
        // Crear usuario
        $userId = DB::table('users')->insertGetId([
            'name' => 'Usuario Central',
            'email' => 'admin@central.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✓ Usuario creado con ID: $userId\n";
        echo "  - Nombre: Usuario Central\n";
        echo "  - Email: admin@central.com\n";
        echo "  - Password: password123\n\n";
        
    } elseif ($hasUsuariosTable) {
        echo "2. Creando usuario en tabla 'usuarios'...\n";
        
        // Verificar estructura de la tabla usuarios
        $columns = DB::select("DESCRIBE usuarios");
        echo "Estructura de la tabla usuarios:\n";
        foreach ($columns as $column) {
            echo "  - {$column->Field} ({$column->Type})\n";
        }
        echo "\n";
        
        // Crear usuario
        $userId = DB::table('usuarios')->insertGetId([
            'nombre' => 'Usuario Central',
            'email' => 'admin@central.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'rol' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "✓ Usuario creado con ID: $userId\n";
        echo "  - Nombre: Usuario Central\n";
        echo "  - Email: admin@central.com\n";
        echo "  - Password: password123\n";
        echo "  - Rol: admin\n\n";
        
    } else {
        echo "❌ No se encontró tabla 'users' ni 'usuarios' en la base de datos central.\n";
        echo "Las tablas de usuarios están solo en las bases de datos de los tenants.\n\n";
        
        echo "3. Verificando usuarios existentes en tenants...\n";
        
        // Verificar usuarios en empresa1
        echo "Usuarios en empresa1:\n";
        $empresa1Users = DB::connection('mysql')->select("SELECT * FROM empresa1.usuarios");
        foreach ($empresa1Users as $user) {
            echo "  - {$user->nombre} <{$user->email}> [{$user->rol}]\n";
        }
        
        echo "\nUsuarios en empresa2:\n";
        $empresa2Users = DB::connection('mysql')->select("SELECT * FROM empresa2.usuarios");
        foreach ($empresa2Users as $user) {
            echo "  - {$user->nombre} <{$user->email}> [{$user->rol}]\n";
        }
        
        echo "\n💡 Los usuarios se gestionan por tenant, no en la base de datos central.\n";
        echo "La base de datos central solo maneja tenants y dominios.\n";
    }
    
    // Mostrar contenido actual de la base de datos central
    echo "3. Contenido actual de la base de datos central:\n";
    
    // Tenants
    $tenants = DB::table('tenants')->get();
    echo "Tenants registrados (" . count($tenants) . "):\n";
    foreach ($tenants as $tenant) {
        echo "  - {$tenant->id}\n";
    }
    
    // Domains
    $domains = DB::table('domains')->get();
    echo "\nDominios registrados (" . count($domains) . "):\n";
    foreach ($domains as $domain) {
        echo "  - {$domain->domain} → {$domain->tenant_id}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";

function now() {
    return date('Y-m-d H:i:s');
}

?>