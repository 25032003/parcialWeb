@echo off
cls
echo ========================================
echo INICIANDO SERVIDOR LARAVEL MULTITENANT
echo ========================================
echo.

REM Cambiar al directorio de Laravel
cd /d "C:\parcialWeb\laravel-api"

REM Verificar que artisan existe
if not exist artisan (
    echo ERROR: No se encuentra el archivo artisan
    echo Ubicacion actual: %CD%
    pause
    exit /b 1
)

echo ✓ Archivo artisan encontrado
echo ✓ Directorio: %CD%
echo.

REM Limpiar cache
echo Limpiando configuraciones...
php artisan config:clear
php artisan route:clear
php artisan cache:clear
echo.

echo Configuracion del servidor:
echo - Host: 0.0.0.0 (accesible desde cualquier dominio)
echo - Puerto: 8000
echo - Dominios multitenant:
echo   * http://empresa1.localhost:8000
echo   * http://empresa2.localhost:8000
echo.
echo Presiona Ctrl+C para detener el servidor
echo ========================================
echo.

REM Iniciar servidor
php artisan serve --host=0.0.0.0 --port=8000

pause