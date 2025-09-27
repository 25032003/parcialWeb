@echo off
cd /d "C:\parcialWeb\laravel-api"
echo Iniciando servidor Laravel para multitenant...
echo Servidor corriendo en: http://0.0.0.0:8000
echo Dominios configurados:
echo - http://empresa1.localhost:8000
echo - http://empresa2.localhost:8000
echo.
php artisan serve --host=0.0.0.0 --port=8000
pause