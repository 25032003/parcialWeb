<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

// Crear tenant
$tenant = Tenant::create();
echo "Tenant creado con ID: {$tenant->id}\n";

// Crear dominio
Domain::create([
    'domain' => 'empresa1.midominio.com',
    'tenant_id' => $tenant->id
]);
echo "Dominio creado: empresa1.midominio.com\n";

echo "✅ Proceso completado!\n";
echo "Base de datos esperada: tenant{$tenant->id}\n";