<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Función para ejecutar migraciones en una base de datos específica
function migrateToTenant($databaseName) {
    echo "\n🔧 Migrando a: $databaseName\n";
    
    // Cambiar temporalmente la conexión
    config(['database.connections.mysql.database' => $databaseName]);
    DB::purge('mysql');
    
    try {
        // Crear tabla migrations si no existe
        DB::statement("CREATE TABLE IF NOT EXISTS migrations (
            id int(10) unsigned NOT NULL AUTO_INCREMENT,
            migration varchar(255) NOT NULL,
            batch int(11) NOT NULL,
            PRIMARY KEY (id)
        )");
        
        // Crear tabla personal_access_tokens
        DB::statement("CREATE TABLE IF NOT EXISTS personal_access_tokens (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            tokenable_type varchar(255) NOT NULL,
            tokenable_id bigint(20) unsigned NOT NULL,
            name varchar(255) NOT NULL,
            token varchar(64) NOT NULL,
            abilities text DEFAULT NULL,
            last_used_at timestamp NULL DEFAULT NULL,
            expires_at timestamp NULL DEFAULT NULL,
            created_at timestamp NULL DEFAULT NULL,
            updated_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY personal_access_tokens_token_unique (token),
            KEY personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type,tokenable_id)
        )");
        
        // Crear tabla usuarios
        DB::statement("CREATE TABLE IF NOT EXISTS usuarios (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            nombre varchar(150) NOT NULL,
            email varchar(150) NOT NULL,
            password varchar(255) NOT NULL,
            rol enum('admin','usuario') NOT NULL DEFAULT 'usuario',
            created_at timestamp NULL DEFAULT NULL,
            updated_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY usuarios_email_unique (email)
        )");
        
        // Crear tabla tareas
        DB::statement("CREATE TABLE IF NOT EXISTS tareas (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            usuario_id bigint(20) unsigned NOT NULL,
            titulo varchar(150) NOT NULL,
            descripcion text DEFAULT NULL,
            estado enum('pendiente','en_progreso','completada') NOT NULL DEFAULT 'pendiente',
            fecha_vencimiento date DEFAULT NULL,
            created_at timestamp NULL DEFAULT NULL,
            updated_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY tareas_usuario_id_index (usuario_id),
            CONSTRAINT tareas_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
        )");
        
        echo "✅ Tablas creadas exitosamente en $databaseName\n";
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

// Migrar a cada tenant
migrateToTenant('tenant_empresa1');
migrateToTenant('tenant_empresa2');

echo "\n🎯 ¡Migraciones completadas!\n";
echo "Cada tenant ahora tiene:\n";
echo "- migrations (tabla de control)\n";
echo "- personal_access_tokens (para autenticación)\n";
echo "- usuarios (gestión de usuarios)\n";
echo "- tareas (gestión de tareas)\n";