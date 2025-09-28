# Sistema Multitenant - CRUD con Autenticación

## 📋 Descripción del Proyecto

Sistema web multitenant desarrollado con Laravel 10 y Vue.js 3 que permite a múltiples empresas gestionar usuarios y tareas de forma completamente aislada. Cada empresa (tenant) tiene su propia base de datos independiente, garantizando la total separación de datos y seguridad empresarial.

## ✨ Características Principales

- **🔐 Autenticación Multitenant**: Sistema de login independiente por empresa con detección automática
- **🏢 Aislamiento Total de Datos**: Cada empresa tiene su base de datos MySQL completamente separada
- **👥 CRUD Completo de Usuarios**: Gestión integral de usuarios con roles admin/usuario
- **📋 CRUD de Tareas**: Sistema de gestión de tareas asignadas a usuarios por empresa
- **🌐 Frontend SPA Responsivo**: Interfaz moderna con Vue.js 3, TypeScript y Vuetify
- **🔧 API REST Segura**: Endpoints protegidos con sistema de tokens personalizado
- **🏗️ Arquitectura Escalable**: Fácil incorporación de nuevos tenants
- **🔄 Routing Multitenant**: Subdominios automáticos (empresa1.localhost, empresa2.localhost)

## 🛠️ Stack Tecnológico

### Backend
- **Framework**: Laravel 10
- **Lenguaje**: PHP 8.2+
- **Base de Datos**: MySQL 8.0
- **Autenticación**: Sistema personalizado con tokens base64
- **Multitenant**: Stancl/Tenancy Package v3
- **ORM**: Eloquent con conexiones dinámicas

### Frontend
- **Framework**: Vue.js 3 con Composition API
- **Lenguaje**: TypeScript
- **UI Framework**: Vuetify 3
- **Build Tool**: Vite
- **HTTP Client**: Axios con interceptores
- **Estado**: Reactive refs y localStorage

### DevOps & Herramientas
- **Control de Versiones**: Git
- **Gestión de Dependencias**: Composer (PHP) + npm (JS)
- **Servidor de Desarrollo**: Laravel Artisan + Vite Dev Server


```

## 🚀 Enlaces Rápidos de Documentación

| Sección | Descripción | Enlace |
|---------|-------------|---------|
| 📦 **Instalación Local** | Configuración completa del entorno de desarrollo | [local-setup.md](installation/local-setup.md) |
| ☁️ **Despliegue AWS** | Guía detallada para producción en EC2 | [aws-deployment.md](installation/aws-deployment.md) |
| 🔌 **API Endpoints** | Documentación completa de todos los endpoints | [endpoints.md](api/endpoints.md) |
| 🔐 **Autenticación** | Sistema de login multitenant y seguridad | [authentication.md](api/authentication.md) |
| 🏢 **Multitenant** | Funcionamiento del sistema multitenant | [multitenant.md](api/multitenant.md) |
| 🗄️ **Base de Datos** | Estructura, esquemas y migraciones | [schema.md](database/schema.md) |
| 🎨 **Frontend** | Componentes Vue.js y funcionalidades | [components.md](frontend/components.md) |
| 🏛️ **Arquitectura** | Diseño del sistema y patrones utilizados | [overview.md](architecture/overview.md) |
| 🧪 **Pruebas** | Casos de prueba y validación manual | [manual-testing.md](testing/manual-testing.md) |

## 🏢 Empresas de Prueba Configuradas

El sistema viene preconfigurado con dos empresas de ejemplo para testing:

### 🏢 Empresa 1
- **Dominio**: `http://empresa1.localhost:5173`
- **Base de Datos**: `empresa1`
- **Admin**: `admin@empresa1.com` / `password123`
- **Usuario Regular**: `user@empresa1.com` / `password123`

### 🏢 Empresa 2  
- **Dominio**: `http://empresa2.localhost:5173`
- **Base de Datos**: `empresa2`
- **Admin**: `admin@empresa2.com` / `password123`
- **Usuario Regular**: `user@empresa2.com` / `password123`

## ⚡ Inicio Rápido

```bash
# 1. Clonar el repositorio
git clone <repository-url>
cd parcialWeb

# 2. Configurar Backend Laravel
cd laravel-api
composer install
cp .env.example .env
php artisan key:generate
# Configurar base de datos en .env
php artisan migrate

# 3. Configurar Frontend Vue.js
cd ../vue-frontend
npm install

# 4. Iniciar servidores (en terminales separadas)
# Terminal 1: Backend
cd laravel-api && php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Frontend  
cd vue-frontend && npm run dev

# 5. Configurar archivo hosts (como administrador)
# Windows: C:\Windows\System32\drivers\etc\hosts
# Linux/Mac: /etc/hosts
# Agregar:
# 127.0.0.1 empresa1.localhost
# 127.0.0.1 empresa2.localhost

# 6. Acceder al sistema
# http://empresa1.localhost:5173
# http://empresa2.localhost:5173
```

## 🔒 Funcionalidades Implementadas y Validadas

### ✅ Sistema de Autenticación
- [x] **Login multitenant** - Detección automática de empresa por dominio
- [x] **Registro de usuarios** - Creación de usuarios aislados por tenant
- [x] **Logout seguro** - Limpieza completa de sesión y tokens
- [x] **Verificación de tokens** - Endpoint `/me` para validar sesión activa
- [x] **Aislamiento de credenciales** - Usuarios de empresa1 no pueden acceder a empresa2

### ✅ CRUD de Usuarios (Por Empresa)
- [x] **Listar usuarios** - Vista completa con roles y filtros
- [x] **Crear usuario** - Formulario con validación completa
- [x] **Editar usuario** - Modificación de datos y roles
- [x] **Eliminar usuario** - Eliminación segura con confirmación
- [x] **Gestión de roles** - Diferenciación entre admin y usuario regular
- [x] **Búsqueda y filtrado** - Sistema de búsqueda en tiempo real

### ✅ CRUD de Tareas (Por Empresa)
- [x] **Listar tareas** - Vista organizada por estado
- [x] **Crear nueva tarea** - Asignación a usuarios de la misma empresa
- [x] **Actualizar tarea** - Cambio de estado y detalles
- [x] **Eliminar tarea** - Eliminación controlada
- [x] **Estados de tarea** - Pendiente, En Progreso, Completada
- [x] **Asignación de usuarios** - Solo usuarios del mismo tenant

### ✅ Sistema Multitenant
- [x] **Detección automática de tenant** - Por dominio, header y email
- [x] **Aislamiento total de datos** - Bases de datos completamente separadas
- [x] **Routing independiente** - Subdominios únicos por empresa
- [x] **Configuración de dominios** - Gestión automática en base central
- [x] **Migraciones por tenant** - Esquemas independientes por empresa
- [x] **Middleware de tenancy** - Protección y contexto automático

### ✅ Frontend Responsivo
- [x] **Interfaz moderna** - Vuetify 3 con Material Design
- [x] **Navegación intuitiva** - Sidebar con acciones contextuales
- [x] **Formularios reactivos** - Validación en tiempo real
- [x] **Feedback visual** - Loading states y notificaciones
- [x] **Responsive design** - Adaptable a dispositivos móviles
- [x] **Gestión de estado** - Persistencia en localStorage

## 📊 Arquitectura de Base de Datos

### Base de Datos Central: `segundo_parcial`
```sql
-- Gestión de tenants y configuración global
├── tenants (id, created_at, updated_at)
├── domains (domain, tenant_id, created_at, updated_at)  
└── migrations (id, migration, batch)
```

### Bases de Datos por Tenant: `empresa1`, `empresa2`
```sql
-- Datos completamente aislados por empresa
├── usuarios (id, nombre, email, password, rol, timestamps)
├── tareas (id, titulo, descripcion, estado, usuario_id, timestamps)
├── personal_access_tokens (id, tokenable_type, tokenable_id, name, token, timestamps)
└── migrations (id, migration, batch)
```

## 🔧 APIs y Endpoints Principales

### Autenticación (Cualquier Dominio)
```http
POST /api/login      # Login con detección automática de tenant
POST /api/register   # Registro de usuario en tenant correspondiente  
POST /api/logout     # Logout seguro
GET  /api/me         # Datos del usuario actual
```

### Gestión por Tenant (Rutas Protegidas)
```http
# Solo accesibles desde dominios de tenant (empresa1.localhost, etc.)
GET    /api/usuarios         # Listar usuarios del tenant
POST   /api/usuarios         # Crear usuario en tenant
GET    /api/usuarios/{id}    # Obtener usuario específico
PUT    /api/usuarios/{id}    # Actualizar usuario
DELETE /api/usuarios/{id}    # Eliminar usuario

GET    /api/tareas           # Listar tareas del tenant
POST   /api/tareas           # Crear tarea en tenant
PUT    /api/tareas/{id}      # Actualizar tarea
DELETE /api/tareas/{id}      # Eliminar tarea
```


## 🔐 Consideraciones de Seguridad

- **Aislamiento de datos**: Cada tenant tiene base de datos completamente separada
- **Validación de tokens**: Sistema personalizado con detección de tenant
- **Sanitización de inputs**: Validación completa en backend y frontend
- **Headers de seguridad**: CORS configurado para dominios específicos
- **Prevención de cross-tenant access**: Middleware de protección implementado

## 👨‍💻 Información del Desarrollo

**Proyecto Académico** - Segundo Parcial Web  
**Tecnologías**: Laravel 10 + Vue.js 3 + MySQL  
**Patrón**: Multitenant con aislamiento por base de datos  
**Arquitectura**: SPA + API REST  





