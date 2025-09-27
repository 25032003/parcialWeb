<?php

// Script para iniciar el servidor Laravel con configuración multitenant
echo "=== INICIANDO SERVIDOR LARAVEL MULTITENANT ===\n\n";

echo "Verificando configuración...\n";

// Cambiar al directorio correcto
$laravelDir = __DIR__;
echo "Directorio: $laravelDir\n";

// Verificar que artisan existe
if (!file_exists($laravelDir . '/artisan')) {
    echo "❌ Error: No se encontró el archivo artisan\n";
    exit(1);
}

echo "✓ Archivo artisan encontrado\n";

// Limpiar configuraciones
echo "\n1. Limpiando configuraciones...\n";
system('php artisan config:clear');
system('php artisan cache:clear');
system('php artisan route:clear');

echo "\n2. Configuración del servidor:\n";
echo "   - Host: 0.0.0.0 (todas las interfaces)\n";
echo "   - Puerto: 8000\n";
echo "   - Dominios soportados:\n";
echo "     * http://empresa1.localhost:8000\n";
echo "     * http://empresa2.localhost:8000\n";
echo "     * http://127.0.0.1:8000 (fallback)\n";

echo "\n3. Iniciando servidor...\n";
echo "Presiona Ctrl+C para detener el servidor\n";
echo "=====================================\n\n";

// Iniciar el servidor
passthru('php artisan serve --host=0.0.0.0 --port=8000');

?>