<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Función para verificar tablas en una base de datos
function checkTables($databaseName) {
    echo "\n🔍 Verificando base de datos: $databaseName\n";
    
    try {
        // Cambiar a la base de datos específica
        config(['database.connections.mysql.database' => $databaseName]);
        DB::purge('mysql');
        
        // Obtener lista de tablas
        $tables = DB::select('SHOW TABLES');
        
        if (empty($tables)) {
            echo "❌ No hay tablas en $databaseName\n";
        } else {
            echo "✅ Tablas encontradas en $databaseName:\n";
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                echo "   - $tableName\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error conectando a $databaseName: " . $e->getMessage() . "\n";
    }
}

// Verificar base de datos central
checkTables('segundo_parcial');

// Verificar bases de datos de tenant
checkTables('tenant_empresa1');
checkTables('tenant_empresa2');

echo "\n🎯 Verificación completada\n";