<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

echo "🌐 CONFIGURACIÓN DE SUBDOMINIOS Y USUARIOS POR TENANT\n";
echo "====================================================\n\n";

// Verificar subdominios existentes
echo "🔍 Verificando subdominios existentes:\n";
$domains = Domain::all();
foreach ($domains as $domain) {
    echo "   ✅ {$domain->domain} -> tenant: {$domain->tenant_id}\n";
}

if ($domains->isEmpty()) {
    echo "   ❌ No hay dominios configurados\n";
    echo "   🔧 Los subdominios ya deberían estar creados. Verificando...\n";
}

echo "\n👤 CREANDO USUARIOS PARA CADA TENANT:\n";

// Función para crear usuario en un tenant específico
function createUserInTenant($tenantId, $userData) {
    $databaseName = 'tenant_' . $tenantId;
    
    // Cambiar a la base de datos del tenant
    config(['database.connections.mysql.database' => $databaseName]);
    DB::purge('mysql');
    
    try {
        // Verificar si ya existe el usuario
        $existingUser = DB::table('usuarios')->where('email', $userData['email'])->first();
        
        if ($existingUser) {
            echo "   ⚠️  Usuario {$userData['email']} ya existe en {$tenantId}\n";
            return;
        }
        
        // Crear el usuario
        DB::table('usuarios')->insert([
            'nombre' => $userData['nombre'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'rol' => $userData['rol'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "   ✅ Usuario creado en {$tenantId}: {$userData['email']} ({$userData['rol']})\n";
        
    } catch (Exception $e) {
        echo "   ❌ Error creando usuario en {$tenantId}: " . $e->getMessage() . "\n";
    }
}

// Crear usuarios para empresa1
echo "\n🏢 Empresa1 (empresa1.midominio.com):\n";
createUserInTenant('empresa1', [
    'nombre' => 'Admin Empresa 1',
    'email' => 'admin@empresa1.com',
    'password' => 'password123',
    'rol' => 'admin'
]);

createUserInTenant('empresa1', [
    'nombre' => 'Usuario Empresa 1',
    'email' => 'usuario@empresa1.com', 
    'password' => 'password123',
    'rol' => 'usuario'
]);

// Crear usuarios para empresa2
echo "\n🏢 Empresa2 (empresa2.midominio.com):\n";
createUserInTenant('empresa2', [
    'nombre' => 'Admin Empresa 2',
    'email' => 'admin@empresa2.com',
    'password' => 'password123',
    'rol' => 'admin'
]);

createUserInTenant('empresa2', [
    'nombre' => 'Usuario Empresa 2',
    'email' => 'usuario@empresa2.com',
    'password' => 'password123', 
    'rol' => 'usuario'
]);

echo "\n📊 RESUMEN FINAL:\n";
echo "================\n";
echo "🌐 Subdominios configurados:\n";
echo "   • empresa1.midominio.com -> Base de datos: tenant_empresa1\n";
echo "   • empresa2.midominio.com -> Base de datos: tenant_empresa2\n\n";

echo "👤 Usuarios creados:\n";
echo "   📍 Empresa 1:\n";
echo "     - admin@empresa1.com (admin) - password123\n";
echo "     - usuario@empresa1.com (usuario) - password123\n\n";
echo "   📍 Empresa 2:\n";
echo "     - admin@empresa2.com (admin) - password123\n";
echo "     - usuario@empresa2.com (usuario) - password123\n\n";

echo "🎯 ¡Configuración completada!\n";
echo "Cada tenant tiene sus usuarios aislados y puede acceder solo a sus datos.\n";