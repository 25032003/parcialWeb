<?php

echo "=== VERIFICACIÓN DE USUARIOS EN AMBAS EMPRESAS ===\n\n";

try {
    // Conectar a MySQL
    $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "1. Usuarios en EMPRESA1:\n";
    $stmt = $pdo->query("SELECT id, nombre, email, rol FROM empresa1.usuarios");
    $users1 = $stmt->fetchAll();
    foreach ($users1 as $user) {
        echo "  - ID:{$user['id']} | {$user['nombre']} <{$user['email']}> [{$user['rol']}]\n";
    }
    
    echo "\n2. Usuarios en EMPRESA2:\n";
    $stmt = $pdo->query("SELECT id, nombre, email, rol FROM empresa2.usuarios");
    $users2 = $stmt->fetchAll();
    if (empty($users2)) {
        echo "  ❌ No hay usuarios en empresa2\n";
    } else {
        foreach ($users2 as $user) {
            echo "  - ID:{$user['id']} | {$user['nombre']} <{$user['email']}> [{$user['rol']}]\n";
        }
    }
    
    echo "\n3. Credenciales disponibles para testing:\n";
    echo "EMPRESA1:\n";
    foreach ($users1 as $user) {
        echo "  - Email: {$user['email']} | Password: password123\n";
    }
    
    if (!empty($users2)) {
        echo "\nEMPRESA2:\n";
        foreach ($users2 as $user) {
            echo "  - Email: {$user['email']} | Password: password123\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";

?>