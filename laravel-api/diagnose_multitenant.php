<?php

echo "=== DIAGNÓSTICO DEL SISTEMA MULTITENANT ===\n\n";

// 1. Verificar servidor Laravel
echo "1. Verificando servidor Laravel...\n";
$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true
    ]
]);

$urls_to_test = [
    'http://127.0.0.1:8000',
    'http://empresa1.localhost:8000',
    'http://empresa1.localhost:8000/api/login'
];

foreach ($urls_to_test as $url) {
    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        echo "✓ $url - ACCESIBLE\n";
    } else {
        echo "❌ $url - NO ACCESIBLE\n";
    }
}

echo "\n2. Verificando archivo hosts...\n";
$hosts_file = 'C:\Windows\System32\drivers\etc\hosts';
if (is_readable($hosts_file)) {
    $hosts_content = file_get_contents($hosts_file);
    if (strpos($hosts_content, 'empresa1.localhost') !== false) {
        echo "✓ empresa1.localhost configurado en hosts\n";
    } else {
        echo "❌ empresa1.localhost NO configurado en hosts\n";
        echo "Necesitas agregar esta línea al archivo hosts:\n";
        echo "127.0.0.1 empresa1.localhost\n";
    }
    
    if (strpos($hosts_content, 'empresa2.localhost') !== false) {
        echo "✓ empresa2.localhost configurado en hosts\n";
    } else {
        echo "❌ empresa2.localhost NO configurado en hosts\n";
        echo "Necesitas agregar esta línea al archivo hosts:\n";
        echo "127.0.0.1 empresa2.localhost\n";
    }
} else {
    echo "⚠️ No se puede leer el archivo hosts (necesitas permisos de administrador)\n";
}

echo "\n3. Verificando base de datos...\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=segundo_parcial;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $stmt = $pdo->query("SELECT id FROM tenants");
    $tenants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ Tenants en BD: " . implode(', ', $tenants) . "\n";
    
    $stmt = $pdo->query("SELECT domain, tenant_id FROM domains");
    $domains = $stmt->fetchAll();
    echo "✓ Dominios configurados:\n";
    foreach ($domains as $domain) {
        echo "  - {$domain['domain']} → {$domain['tenant_id']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "\n";
}

echo "\n4. Verificando usuarios de prueba...\n";
try {
    $pdo_empresa1 = new PDO('mysql:host=localhost;dbname=empresa1;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $stmt = $pdo_empresa1->query("SELECT nombre, email, rol FROM usuarios");
    $users = $stmt->fetchAll();
    echo "✓ Usuarios en empresa1:\n";
    foreach ($users as $user) {
        echo "  - {$user['nombre']} <{$user['email']}> [{$user['rol']}]\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error usuarios empresa1: " . $e->getMessage() . "\n";
}

echo "\n=== INSTRUCCIONES PARA SOLUCIONAR ===\n";
echo "1. Asegúrate de que ambos servidores estén corriendo:\n";
echo "   - Laravel: C:\\parcialWeb\\start_laravel_server.bat\n";
echo "   - Vue: C:\\parcialWeb\\start_vue_frontend.bat\n\n";
echo "2. Configura el archivo hosts como administrador:\n";
echo "   - Abre PowerShell como Administrador\n";
echo "   - Ejecuta: notepad C:\\Windows\\System32\\drivers\\etc\\hosts\n";
echo "   - Agrega al final:\n";
echo "     127.0.0.1 empresa1.localhost\n";
echo "     127.0.0.1 empresa2.localhost\n\n";
echo "3. Accede a: http://empresa1.localhost:5173/login\n";
echo "4. Credenciales: admin@empresa1.com / password123\n";

?>