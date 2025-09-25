<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🧹 Limpiando base de datos central...\n";

// Conectar a base de datos central
config(['database.connections.mysql.database' => 'segundo_parcial']);
DB::purge('mysql');

try {
    // Eliminar tablas que NO deberían estar en la base central
    DB::statement('DROP TABLE IF EXISTS tareas');
    echo "✅ Tabla 'tareas' eliminada de la base central\n";
    
    DB::statement('DROP TABLE IF EXISTS usuarios');  
    echo "✅ Tabla 'usuarios' eliminada de la base central\n";
    
    echo "\n🎯 La base de datos central ahora contiene SOLO:\n";
    echo "   - tenants (información de tenants)\n";
    echo "   - domains (dominios)\n"; 
    echo "   - personal_access_tokens (para autenticación central)\n";
    echo "   - migrations (control de migraciones)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Limpieza completada - Multitenancy configurado correctamente!\n";