<?php

// Configuración directa de PDO para evitar problemas de autenticación
try {
    echo "=== CREACIÓN DE USUARIO EN BASE DE DATOS CENTRAL ===\n\n";
    
    // Conexión PDO directa
    $pdo = new PDO('mysql:host=localhost;dbname=segundo_parcial;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✓ Conexión establecida con segundo_parcial\n\n";
    
    // 1. Verificar tablas existentes
    echo "1. Verificando tablas disponibles...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tablas encontradas:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    echo "\n";
    
    // 2. Verificar si existe tabla users o usuarios
    $hasUsers = in_array('users', $tables);
    $hasUsuarios = in_array('usuarios', $tables);
    
    if ($hasUsers) {
        echo "2. Trabajando con tabla 'users'...\n";
        
        // Verificar estructura
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        echo "Estructura de la tabla:\n";
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
        echo "\n";
        
        // Verificar usuarios existentes
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "Usuarios existentes: $count\n\n";
        
        // Crear nuevo usuario
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $result = $stmt->execute([
            'Admin Central',
            'admin@central.com',
            password_hash('password123', PASSWORD_DEFAULT)
        ]);
        
        if ($result) {
            $userId = $pdo->lastInsertId();
            echo "✓ Usuario creado exitosamente\n";
            echo "  - ID: $userId\n";
            echo "  - Nombre: Admin Central\n";
            echo "  - Email: admin@central.com\n";
            echo "  - Password: password123\n";
        }
        
    } elseif ($hasUsuarios) {
        echo "2. Trabajando con tabla 'usuarios'...\n";
        
        // Verificar estructura
        $stmt = $pdo->query("DESCRIBE usuarios");
        $columns = $stmt->fetchAll();
        echo "Estructura de la tabla:\n";
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
        echo "\n";
        
        // Verificar usuarios existentes
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
        $count = $stmt->fetch()['count'];
        echo "Usuarios existentes: $count\n\n";
        
        // Crear nuevo usuario
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $result = $stmt->execute([
            'Admin Central',
            'admin@central.com',
            password_hash('password123', PASSWORD_DEFAULT),
            'admin'
        ]);
        
        if ($result) {
            $userId = $pdo->lastInsertId();
            echo "✓ Usuario creado exitosamente\n";
            echo "  - ID: $userId\n";
            echo "  - Nombre: Admin Central\n";
            echo "  - Email: admin@central.com\n";
            echo "  - Password: password123\n";
            echo "  - Rol: admin\n";
        }
        
    } else {
        echo "2. No hay tabla de usuarios en la base de datos central\n";
        echo "💡 En un sistema multitenant, los usuarios normalmente se almacenan\n";
        echo "   en las bases de datos de cada tenant, no en la base central.\n\n";
        
        echo "La base de datos central contiene:\n";
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $stmt->fetch()['count'];
            echo "  - $table: $count registros\n";
        }
        
        echo "\n¿Te gustaría crear un usuario en alguno de los tenants?\n";
        echo "Tenants disponibles:\n";
        
        // Mostrar tenants disponibles
        if (in_array('tenants', $tables)) {
            $stmt = $pdo->query("SELECT * FROM tenants");
            $tenants = $stmt->fetchAll();
            foreach ($tenants as $tenant) {
                echo "  - {$tenant['id']}\n";
            }
        }
    }
    
    echo "\n3. Resumen del contenido de la base de datos central:\n";
    
    // Mostrar tenants si existen
    if (in_array('tenants', $tables)) {
        $stmt = $pdo->query("SELECT * FROM tenants");
        $tenants = $stmt->fetchAll();
        echo "Tenants registrados (" . count($tenants) . "):\n";
        foreach ($tenants as $tenant) {
            echo "  - {$tenant['id']}\n";
        }
    }
    
    // Mostrar dominios si existen
    if (in_array('domains', $tables)) {
        $stmt = $pdo->query("SELECT * FROM domains");
        $domains = $stmt->fetchAll();
        echo "\nDominios registrados (" . count($domains) . "):\n";
        foreach ($domains as $domain) {
            echo "  - {$domain['domain']} → {$domain['tenant_id']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";

?>