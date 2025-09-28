# 📦 Instalación Local - Sistema Multitenant

Esta guía completa te permitirá configurar el sistema multitenant Laravel + Vue.js en tu entorno de desarrollo local paso a paso.

## 📋 Requisitos del Sistema

### ✅ Software Obligatorio
- **PHP**: 8.2 o superior
- **Composer**: 2.x (Gestor de dependencias PHP)
- **Node.js**: 18.x LTS o superior
- **npm**: 9.x o superior (incluido con Node.js)
- **MySQL**: 8.0 o superior
- **Git**: Para clonar el repositorio

### 🔧 Extensiones PHP Requeridas
Verifica que tengas estas extensiones instaladas:
```bash
php -m | grep -E "(mysql|pdo|mbstring|xml|curl|zip|gd|intl|bcmath)"
```

**Extensiones necesarias:**
- `php-mysql` - Conexión a MySQL
- `php-pdo` - PHP Data Objects
- `php-mbstring` - Manipulación de strings multibyte
- `php-xml` - Procesamiento XML
- `php-curl` - Cliente HTTP cURL
- `php-zip` - Manipulación de archivos ZIP
- `php-gd` - Procesamiento de imágenes
- `php-intl` - Internacionalización
- `php-bcmath` - Matemáticas de precisión arbitraria

### 🖥️ Verificación de Requisitos
```bash
# Verificar PHP
php --version
# Debe mostrar: PHP 8.2.x o superior

# Verificar Composer
composer --version
# Debe mostrar: Composer version 2.x

# Verificar Node.js
node --version
# Debe mostrar: v18.x.x o superior

# Verificar npm
npm --version
# Debe mostrar: 9.x.x o superior

# Verificar MySQL
mysql --version
# Debe mostrar: mysql Ver 8.0.x
```

## 🚀 Proceso de Instalación Completo

### 📁 **Paso 1: Obtener el Código Fuente**
```bash
# Clonar el repositorio
git clone <repository-url> parcialWeb
cd parcialWeb

# Verificar estructura del proyecto
ls -la
# Debe mostrar: laravel-api/ vue-frontend/
```

### 🔧 **Paso 2: Configurar el Backend Laravel**

#### 2.1 📦 Instalar Dependencias de Laravel
```bash
cd laravel-api

# Instalar paquetes de Composer
composer install

# Verificar instalación
composer show | grep -E "(laravel|stancl)"
# Debe mostrar Laravel 10.x y stancl/tenancy
```

#### 2.2 ⚙️ Configurar Variables de Entorno
```bash
# Copiar archivo de configuración de ejemplo
cp .env.example .env

# Editar configuración (usar tu editor preferido)
nano .env
# o
code .env
```

**Configuración .env completa para desarrollo:**
```env
# === CONFIGURACIÓN BÁSICA ===
APP_NAME="Sistema Multitenant"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# === CONFIGURACIÓN DE BASE DE DATOS ===
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=segundo_parcial
DB_USERNAME=tu_usuario_mysql
DB_PASSWORD=tu_password_mysql

# === CONFIGURACIÓN MULTITENANT ===
TENANCY_DATABASE_AUTO_DELETE_USER=true
TENANCY_DATABASE_TEMPLATE_CONNECTION=tenant_template

# === CONFIGURACIÓN DE CACHE ===
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# === CONFIGURACIÓN DE MAIL (Opcional) ===
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@sistema-multitenant.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### 2.3 🔐 Generar Clave de Aplicación
```bash
# Generar clave única de Laravel
php artisan key:generate

# Verificar que se agregó la clave en .env
grep "APP_KEY" .env
# Debe mostrar: APP_KEY=base64:...
```

#### 2.4 🗄️ Configurar Bases de Datos MySQL

**Opción A: Usando línea de comandos MySQL**
```sql
-- Conectar a MySQL como root
mysql -u root -p

-- Crear las bases de datos necesarias
CREATE DATABASE segundo_parcial CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE empresa1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE empresa2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario específico para Laravel (recomendado)
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'password_seguro123';

-- Otorgar permisos completos
GRANT ALL PRIVILEGES ON segundo_parcial.* TO 'laravel_user'@'localhost';
GRANT ALL PRIVILEGES ON empresa1.* TO 'laravel_user'@'localhost';
GRANT ALL PRIVILEGES ON empresa2.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;

-- Verificar bases de datos creadas
SHOW DATABASES;

-- Salir de MySQL
EXIT;
```

**Opción B: Usando phpMyAdmin o herramienta gráfica**
1. Abrir phpMyAdmin o tu herramienta MySQL preferida
2. Crear las bases de datos: `segundo_parcial`, `empresa1`, `empresa2`
3. Configurar usuario con permisos completos

#### 2.5 📊 Ejecutar Migraciones de Base de Datos
```bash
# Ejecutar migraciones para la base central
php artisan migrate

# Verificar que se ejecutaron correctamente
php artisan migrate:status
# Debe mostrar migraciones con estado "Ran"

# Verificar tablas creadas en base central
php artisan tinker
>>> \DB::table('migrations')->get();
>>> exit
```

#### 2.6 🏢 Configurar Sistema Multitenant

**Crear script de configuración multitenant:**
```bash
# Crear archivo setup_multitenant_local.php
cat > setup_multitenant_local.php << 'EOF'
<?php

require_once 'vendor/autoload.php';

// Cargar aplicación Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🏗️ === CONFIGURANDO SISTEMA MULTITENANT LOCAL ===\n\n";

try {
    // 1. Insertar tenants en la base central
    echo "1. Creando tenants en la base de datos central...\n";
    
    $stmt = DB::getPdo()->prepare("INSERT IGNORE INTO tenants (id, created_at, updated_at) VALUES (?, NOW(), NOW())");
    $stmt->execute(['empresa1']);
    $stmt->execute(['empresa2']);
    echo "   ✅ Tenants empresa1 y empresa2 creados\n";
    
    // 2. Configurar dominios para desarrollo local
    echo "2. Configurando dominios para desarrollo local...\n";
    
    $stmt = DB::getPdo()->prepare("INSERT IGNORE INTO domains (domain, tenant_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->execute(['empresa1.localhost', 'empresa1']);
    $stmt->execute(['empresa2.localhost', 'empresa2']);
    echo "   ✅ Dominios empresa1.localhost y empresa2.localhost configurados\n";
    
    // 3. Ejecutar migraciones de tenants
    echo "3. Ejecutando migraciones para cada tenant...\n";
    passthru('php artisan tenants:migrate --force');
    echo "   ✅ Migraciones de tenants ejecutadas\n";
    
    // 4. Crear usuarios de prueba
    echo "4. Creando usuarios de prueba...\n";
    
    // Usuarios para empresa1
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = DB::connection('mysql')->getPdo()->prepare("
        INSERT IGNORE INTO empresa1.usuarios (nombre, email, password, rol, created_at, updated_at) 
        VALUES (?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute(['Admin Empresa 1', 'admin@empresa1.com', $hashedPassword, 'admin']);
    $stmt->execute(['Usuario Empresa 1', 'user@empresa1.com', $hashedPassword, 'usuario']);
    
    // Usuarios para empresa2
    $stmt = DB::connection('mysql')->getPdo()->prepare("
        INSERT IGNORE INTO empresa2.usuarios (nombre, email, password, rol, created_at, updated_at) 
        VALUES (?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute(['Admin Empresa 2', 'admin@empresa2.com', $hashedPassword, 'admin']);
    $stmt->execute(['Usuario Empresa 2', 'user@empresa2.com', $hashedPassword, 'usuario']);
    
    echo "   ✅ Usuarios de prueba creados para ambas empresas\n";
    
    // 5. Verificar configuración
    echo "\n5. Verificando configuración...\n";
    
    $tenantCount = DB::table('tenants')->count();
    $domainCount = DB::table('domains')->count();
    echo "   📊 Tenants en base central: $tenantCount\n";
    echo "   📊 Dominios configurados: $domainCount\n";
    
    $empresa1Users = DB::connection('mysql')->select("SELECT COUNT(*) as count FROM empresa1.usuarios");
    $empresa2Users = DB::connection('mysql')->select("SELECT COUNT(*) as count FROM empresa2.usuarios");
    echo "   👥 Usuarios en empresa1: " . $empresa1Users[0]->count . "\n";
    echo "   👥 Usuarios en empresa2: " . $empresa2Users[0]->count . "\n";
    
    echo "\n🎉 === CONFIGURACIÓN MULTITENANT COMPLETADA ===\n";
    echo "✅ Sistema listo para desarrollo local\n";
    echo "🌐 URLs de acceso:\n";
    echo "   - Empresa 1: http://empresa1.localhost:5173\n";
    echo "   - Empresa 2: http://empresa2.localhost:5173\n";
    echo "🔑 Credenciales de prueba:\n";
    echo "   - Admin Empresa 1: admin@empresa1.com / password123\n";
    echo "   - Admin Empresa 2: admin@empresa2.com / password123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📋 Stack trace: " . $e->getTraceAsString() . "\n";
}

EOF

# Ejecutar script de configuración
php setup_multitenant_local.php
```

#### 2.7 🧹 Optimizar Laravel para Desarrollo
```bash
# Limpiar cachés existentes
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Verificar configuración
php artisan config:cache
php artisan route:list

# Verificar que el sistema está funcionando
php artisan tinker
>>> app()->version();
>>> exit
```

### 🎨 **Paso 3: Configurar el Frontend Vue.js**

#### 3.1 📦 Instalar Dependencias de Node.js
```bash
# Cambiar al directorio del frontend
cd ../vue-frontend

# Instalar dependencias
npm install

# Verificar instalación
npm list --depth=0
# Debe mostrar Vue.js, Vite, Vuetify, etc.
```

#### 3.2 ⚙️ Configurar Variables de Entorno del Frontend
```bash
# Copiar archivo de configuración
cp .env.example .env

# Editar configuración
nano .env
```

**Configuración .env del frontend:**
```env
# === CONFIGURACIÓN DE API ===
VITE_API_URL=http://127.0.0.1:8000/api

# === CONFIGURACIÓN DE APLICACIÓN ===
VITE_APP_NAME="Sistema Multitenant"
VITE_APP_DESCRIPTION="Sistema de gestión multitenant"

# === CONFIGURACIÓN DE DESARROLLO ===
VITE_DEV_PORT=5173
VITE_DEV_HOST=localhost
```

#### 3.3 🌐 Configurar Archivo Hosts del Sistema

**Windows (ejecutar como Administrador):**
```bash
# Abrir PowerShell como Administrador
# Editar archivo hosts
notepad C:\Windows\System32\drivers\etc\hosts

# Agregar al final del archivo:
127.0.0.1 empresa1.localhost
127.0.0.1 empresa2.localhost

# Guardar y cerrar
```

**Linux/macOS:**
```bash
# Editar archivo hosts con permisos de superusuario
sudo nano /etc/hosts

# Agregar al final del archivo:
127.0.0.1 empresa1.localhost
127.0.0.1 empresa2.localhost

# Guardar (Ctrl+O) y salir (Ctrl+X)
```

#### 3.4 ✅ Verificar Configuración del Frontend
```bash
# Verificar configuración de Vite
npm run build -- --dry-run

# Verificar sintaxis de TypeScript
npx tsc --noEmit

# Verificar configuración de Vue
npx vue-tsc --noEmit
```

## 🖥️ **Paso 4: Iniciar los Servidores de Desarrollo**

### 🔄 Método 1: Terminales Separadas (Recomendado)

**Terminal 1 - Servidor Laravel:**
```bash
cd laravel-api
php artisan serve --host=0.0.0.0 --port=8000

# Debe mostrar:
# Server running on [http://0.0.0.0:8000]
```

**Terminal 2 - Servidor Vue.js:**
```bash
cd vue-frontend
npm run dev

# Debe mostrar:
# Local:   http://localhost:5173/
# Network: use --host to expose
```

### 🚀 Método 2: Scripts Automatizados

**Para Windows - Crear start_servers.bat:**
```batch
@echo off
echo === INICIANDO SERVIDORES DEL SISTEMA MULTITENANT ===
echo.
echo 🔧 Iniciando Laravel API...
start "Laravel API" cmd /k "cd laravel-api && php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 3

echo 🎨 Iniciando Frontend Vue.js...
start "Vue Frontend" cmd /k "cd vue-frontend && npm run dev"

echo.
echo ✅ Servidores iniciados correctamente
echo 🌐 URLs de acceso:
echo    - API: http://127.0.0.1:8000
echo    - Empresa 1: http://empresa1.localhost:5173
echo    - Empresa 2: http://empresa2.localhost:5173
echo.
echo Presiona cualquier tecla para continuar...
pause
```

**Para Linux/Mac - Crear start_servers.sh:**
```bash
#!/bin/bash
echo "=== INICIANDO SERVIDORES DEL SISTEMA MULTITENANT ==="
echo ""
echo "🔧 Iniciando Laravel API en background..."
cd laravel-api && php artisan serve --host=0.0.0.0 --port=8000 &
LARAVEL_PID=$!

echo "🎨 Iniciando Frontend Vue.js..."  
cd vue-frontend && npm run dev &
VUE_PID=$!

echo ""
echo "✅ Servidores iniciados correctamente"
echo "🌐 URLs de acceso:"
echo "   - API: http://127.0.0.1:8000"
echo "   - Empresa 1: http://empresa1.localhost:5173"
echo "   - Empresa 2: http://empresa2.localhost:5173"
echo ""
echo "Presiona Ctrl+C para detener ambos servidores"

# Función para limpiar procesos al salir
cleanup() {
    echo ""
    echo "🛑 Deteniendo servidores..."
    kill $LARAVEL_PID $VUE_PID 2>/dev/null
    exit
}

trap cleanup SIGINT
wait
```

```bash
# Hacer ejecutable el script
chmod +x start_servers.sh

# Ejecutar
./start_servers.sh
```

## 🧪 **Paso 5: Verificar la Instalación**

### 🔍 **5.1 Probar el Backend**
```bash
# Verificar API principal
curl http://127.0.0.1:8000/api/
# Respuesta esperada: JSON con información de la API

# Probar endpoint de login para empresa1
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Host: empresa1.localhost:8000" \
  -d '{
    "email": "admin@empresa1.com",
    "password": "password123"
  }'

# Respuesta esperada: JSON con token y datos del usuario
```

### 🌐 **5.2 Probar el Frontend**

**Acceder a las URLs y verificar:**
- **Empresa 1**: http://empresa1.localhost:5173
- **Empresa 2**: http://empresa2.localhost:5173

**Realizar pruebas de login:**

| Empresa | URL | Email | Password |
|---------|-----|-------|----------|
| Empresa 1 | http://empresa1.localhost:5173/login | admin@empresa1.com | password123 |
| Empresa 2 | http://empresa2.localhost:5173/login | admin@empresa2.com | password123 |

### ✅ **5.3 Lista de Verificación**

- [ ] **PHP 8.2+** instalado y funcionando
- [ ] **Composer** instalado con dependencias Laravel
- [ ] **MySQL** corriendo con bases de datos creadas
- [ ] **Node.js 18+** y npm instalados
- [ ] **Repositorio** clonado correctamente
- [ ] **Laravel .env** configurado con datos de BD
- [ ] **Vue .env** configurado con URL de API
- [ ] **Migraciones** ejecutadas exitosamente
- [ ] **Tenants** creados en base central
- [ ] **Usuarios de prueba** creados
- [ ] **Archivo hosts** configurado
- [ ] **Servidor Laravel** corriendo en puerto 8000
- [ ] **Servidor Vue** corriendo en puerto 5173
- [ ] **URLs accesibles** en navegador
- [ ] **Login funcionando** en ambas empresas

## 🔧 **Resolución de Problemas Comunes**

### ❌ **Error: "Could not open input file: artisan"**
```bash
# Verificar que estás en el directorio correcto
pwd
# Debe mostrar: .../parcialWeb/laravel-api

# Si no estás en el directorio correcto:
cd laravel-api
```

### ❌ **Error: "SQLSTATE[HY000] [1049] Unknown database"**
```bash
# Verificar que las bases de datos existen
mysql -u root -p -e "SHOW DATABASES;"

# Si no existen, crearlas:
mysql -u root -p -e "
CREATE DATABASE segundo_parcial;
CREATE DATABASE empresa1;
CREATE DATABASE empresa2;
"
```

### ❌ **Error: "Connection refused" en API**
```bash
# Verificar que MySQL está corriendo
# Windows:
net start mysql

# Linux:
sudo systemctl status mysql
sudo systemctl start mysql

# macOS:
brew services list | grep mysql
brew services start mysql
```

### ❌ **Error: "Class not found" en Laravel**
```bash
# Limpiar todos los cachés
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Regenerar autoload
composer dump-autoload

# Verificar configuración
php artisan config:cache
```

### ❌ **Error: "Cannot resolve host" en frontend**
```bash
# Verificar archivo hosts
# Windows:
notepad C:\Windows\System32\drivers\etc\hosts

# Linux/Mac:
cat /etc/hosts

# Debe contener:
# 127.0.0.1 empresa1.localhost
# 127.0.0.1 empresa2.localhost
```

### ❌ **Error: "CORS" en peticiones**
```bash
# Verificar configuración CORS en Laravel
cat config/cors.php

# Debe permitir localhost y subdominios
# Si hay problemas, limpiar cache:
php artisan config:clear
```

### ❌ **Error: "404 Not Found" en rutas API**
```bash
# Verificar rutas disponibles
php artisan route:list --path=api

# Debe mostrar rutas de login, register, me, etc.

# Si no aparecen, limpiar cache de rutas:
php artisan route:clear
```

### ❌ **Error: "npm ERR! network timeout"**
```bash
# Configurar timeout más alto
npm config set network-timeout 60000

# Usar registro alternativo
npm install --registry https://registry.npm.taobao.org/

# Limpiar cache de npm
npm cache clean --force
```

## 📋 **Checklist de Instalación Completa**

### ✅ **Requisitos del Sistema**
- [ ] PHP 8.2+ con extensiones requeridas
- [ ] Composer 2.x instalado globalmente
- [ ] MySQL 8.0+ corriendo y accesible  
- [ ] Node.js 18+ LTS instalado
- [ ] npm 9+ funcionando correctamente
- [ ] Git configurado para clonar repositorio

### ✅ **Configuración del Backend**
- [ ] Repositorio clonado en directorio local
- [ ] Dependencias Laravel instaladas con Composer
- [ ] Archivo .env configurado con datos correctos
- [ ] Clave de aplicación Laravel generada
- [ ] Bases de datos MySQL creadas (segundo_parcial, empresa1, empresa2)
- [ ] Usuario MySQL configurado con permisos
- [ ] Migraciones de base central ejecutadas
- [ ] Sistema multitenant configurado con script
- [ ] Usuarios de prueba creados en cada empresa
- [ ] Cachés Laravel limpiados y optimizados

### ✅ **Configuración del Frontend**
- [ ] Dependencias Node.js instaladas con npm
- [ ] Archivo .env del frontend configurado
- [ ] Archivo hosts del sistema configurado
- [ ] TypeScript compilando sin errores
- [ ] Build de Vite funcionando correctamente

### ✅ **Servidores y Conectividad**
- [ ] Servidor Laravel corriendo en puerto 8000
- [ ] Servidor Vue.js corriendo en puerto 5173
- [ ] API accesible desde http://127.0.0.1:8000/api
- [ ] Empresa 1 accesible desde http://empresa1.localhost:5173
- [ ] Empresa 2 accesible desde http://empresa2.localhost:5173
- [ ] Login funcionando con credenciales de prueba
- [ ] Navegación entre secciones funcionando
- [ ] CRUD de usuarios y tareas operativo

## 📞 **Soporte y Resolución de Dudas**

### 🔍 **Para Depurar Problemas:**

1. **Revisar logs de Laravel:**
   ```bash
   tail -f laravel-api/storage/logs/laravel.log
   ```

2. **Revisar consola del navegador:**
   - Presiona F12 en el navegador
   - Ve a la pestaña "Console"
   - Busca errores en rojo

3. **Verificar respuestas de API:**
   - En F12 ve a la pestaña "Network"
   - Realiza una acción (login, crear usuario, etc.)
   - Revisa las peticiones HTTP y sus respuestas

### 📚 **Documentación Relacionada:**
- [Endpoints de API](../api/endpoints.md) - Lista completa de endpoints disponibles
- [Estructura de Base de Datos](../database/schema.md) - Esquemas y relaciones
- [Componentes del Frontend](../frontend/components.md) - Documentación de Vue.js
- [Pruebas Manuales](../testing/manual-testing.md) - Casos de prueba para validar

### 💡 **Consejos Adicionales:**
- Siempre usa terminales separadas para cada servidor durante desarrollo
- Mantén los logs abiertos para monitorear errores en tiempo real
- Usa las herramientas de desarrollo del navegador para depurar frontend
- Guarda copias de seguridad de la base de datos antes de cambios importantes

---

✅ **¡Instalación Completada!**  
Tu sistema multitenant está listo para desarrollo. Puedes comenzar a trabajar con usuarios, tareas y gestión multitenant.