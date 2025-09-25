<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Crear base de datos para tenant
$tenantId = '166c75ff-d951-41e5-aab6-f92bc837c5d2';
$databaseName = 'tenant' . str_replace('-', '', $tenantId);

try {
    DB::statement("CREATE DATABASE IF NOT EXISTS `$databaseName`");
    echo "✅ Base de datos creada: $databaseName\n";
} catch (Exception $e) {
    echo "❌ Error creando base de datos: " . $e->getMessage() . "\n";
}

// Crear para el segundo tenant también
$tenantId2 = '6679dcd3-bc00-44ff-9556-46b628f2b1cc';
$databaseName2 = 'tenant' . str_replace('-', '', $tenantId2);

try {
    DB::statement("CREATE DATABASE IF NOT EXISTS `$databaseName2`");
    echo "✅ Base de datos creada: $databaseName2\n";
} catch (Exception $e) {
    echo "❌ Error creando base de datos: " . $e->getMessage() . "\n";
}

echo "✅ Bases de datos de tenant creadas!\n";