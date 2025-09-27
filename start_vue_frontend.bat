@echo off
cls
echo =====================================
echo INICIANDO FRONTEND VUE - MULTITENANT
echo =====================================
echo.

cd /d "C:\parcialWeb\vue-frontend"

echo ✓ Directorio: %CD%
echo ✓ Iniciando servidor de desarrollo...
echo.
echo Frontend corriendo en:
echo - http://localhost:5173
echo - http://empresa1.localhost:5173 
echo - http://empresa2.localhost:5173
echo.
echo Presiona Ctrl+C para detener
echo =====================================
echo.

npm run dev

pause