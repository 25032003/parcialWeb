<?php

try {
    $pdo = new PDO('mysql:host=localhost;dbname=empresa2;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Crear usuario admin para empresa2
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([
        'Admin Empresa 2',
        'admin@empresa2.com',
        password_hash('password123', PASSWORD_DEFAULT),
        'admin'
    ]);
    
    echo "✅ Usuario admin@empresa2.com creado exitosamente\n";
    echo "Password: password123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>