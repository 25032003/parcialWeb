<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

// Eliminar tenants existentes
DB::table('domains')->delete();
DB::table('tenants')->delete();

// Crear base de datos para empresa1
DB::statement("CREATE DATABASE IF NOT EXISTS `tenant_empresa1`");
echo "✅ Base de datos creada: tenant_empresa1\n";

// Crear base de datos para empresa2  
DB::statement("CREATE DATABASE IF NOT EXISTS `tenant_empresa2`");
echo "✅ Base de datos creada: tenant_empresa2\n";

// Crear tenants con IDs simples
$tenant1 = Tenant::create(['id' => 'empresa1']);
Domain::create([
    'domain' => 'empresa1.midominio.com',
    'tenant_id' => 'empresa1'
]);
echo "✅ Tenant empresa1 creado con dominio empresa1.midominio.com\n";

$tenant2 = Tenant::create(['id' => 'empresa2']);
Domain::create([
    'domain' => 'empresa2.midominio.com',
    'tenant_id' => 'empresa2'
]);
echo "✅ Tenant empresa2 creado con dominio empresa2.midominio.com\n";

echo "\n🎯 Tenants creados exitosamente!\n";
echo "- tenant_empresa1 -> empresa1.midominio.com\n";
echo "- tenant_empresa2 -> empresa2.midominio.com\n";