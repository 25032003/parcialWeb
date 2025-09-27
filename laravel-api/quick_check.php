<?php

echo "Verificando usuarios...\n";

try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "EMPRESA2 usuarios:\n";
    $result = $pdo->query("SELECT nombre, email FROM empresa2.usuarios");
    while ($row = $result->fetch()) {
        echo "- {$row['nombre']} <{$row['email']}>\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>