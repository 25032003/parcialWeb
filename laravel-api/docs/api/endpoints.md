# 🔌 API Endpoints - Sistema Multitenant

Esta documentación detalla todos los endpoints disponibles en la API del sistema multitenant, incluyendo parámetros, respuestas y ejemplos de uso.

## 📋 Información General

### 🌐 **URL Base**
```
http://127.0.0.1:8000/api
```

### 🔒 **Autenticación**
El sistema utiliza **Bearer Token** authentication. Incluye el token en el header:
```
Authorization: Bearer {tu_token_aqui}
```

### 🏢 **Detección de Tenant**
El sistema detecta automáticamente el tenant basado en:
1. **Host Header**: `empresa1.localhost` o `empresa2.localhost`
2. **Subdomain**: Primeros 8 caracteres del host

### 📤 **Formato de Respuestas**
Todas las respuestas siguen este formato JSON estándar:

**Respuesta Exitosa:**
```json
{
  "success": true,
  "data": {
    // Datos específicos del endpoint
  },
  "message": "Descripción de la operación"
}
```

**Respuesta de Error:**
```json
{
  "success": false,
  "message": "Descripción del error",
  "errors": {
    "campo": ["Lista de errores específicos"]
  }
}
```

### 📊 **Códigos de Estado HTTP**
| Código | Significado | Uso |
|--------|-------------|-----|
| 200 | OK | Operación exitosa |
| 201 | Created | Recurso creado exitosamente |
| 400 | Bad Request | Error en parámetros de entrada |
| 401 | Unauthorized | Token inválido o faltante |
| 403 | Forbidden | Sin permisos para la operación |
| 404 | Not Found | Recurso no encontrado |
| 422 | Unprocessable Entity | Error de validación |
| 500 | Internal Server Error | Error interno del servidor |

---

# 🔐 Endpoints de Autenticación

## 🚪 **POST /api/login**
Autentica un usuario en el sistema.

### 📤 **Request**
```http
POST /api/login
Host: empresa1.localhost:8000
Content-Type: application/json

{
  "email": "admin@empresa1.com",
  "password": "password123"
}
```

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "nombre": "Admin Empresa 1",
      "email": "admin@empresa1.com",
      "rol": "admin",
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    "token": "1|8Zx9Y2VmNpQrStUvWxYz3A4B5C6D7E8F9G0H1I2J3K4L5M6N7O8P9Q0R1S2T3U4V",
    "empresa": "empresa1"
  },
  "message": "Login exitoso"
}
```

### ❌ **Response Error (401)**
```json
{
  "success": false,
  "message": "Credenciales inválidas"
}
```

### 🔧 **Curl Example**
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Host: empresa1.localhost:8000" \
  -d '{
    "email": "admin@empresa1.com",
    "password": "password123"
  }'
```

---

## 👤 **GET /api/me**
Obtiene la información del usuario autenticado.

### 📤 **Request**
```http
GET /api/me
Host: empresa1.localhost:8000
Authorization: Bearer 1|8Zx9Y2VmNpQrStUvWxYz3A4B5C6D7E8F9G0H1I2J3K4L5M6N7O8P9Q0R1S2T3U4V
```

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "nombre": "Admin Empresa 1",
      "email": "admin@empresa1.com",
      "rol": "admin",
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    "empresa": "empresa1"
  },
  "message": "Usuario autenticado obtenido correctamente"
}
```

### ❌ **Response Error (401)**
```json
{
  "success": false,
  "message": "Token no válido o expirado"
}
```

### 🔧 **Curl Example**
```bash
curl -X GET http://127.0.0.1:8000/api/me \
  -H "Authorization: Bearer 1|8Zx9Y2VmNpQrStUvWxYz3A4B5C6D7E8F9G0H1I2J3K4L5M6N7O8P9Q0R1S2T3U4V" \
  -H "Host: empresa1.localhost:8000"
```

---

## 📝 **POST /api/register**
Registra un nuevo usuario en el sistema.

### 📤 **Request**
```http
POST /api/register
Host: empresa1.localhost:8000
Content-Type: application/json

{
  "nombre": "Nuevo Usuario",
  "email": "nuevo@empresa1.com",
  "password": "password123",
  "password_confirmation": "password123",
  "rol": "usuario"
}
```

### 📋 **Parámetros**
| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| nombre | string | ✅ | Mínimo 2 caracteres |
| email | string | ✅ | Email válido, único en tenant |
| password | string | ✅ | Mínimo 8 caracteres |
| password_confirmation | string | ✅ | Debe coincidir con password |
| rol | string | ❌ | 'admin' o 'usuario' (default: 'usuario') |

### 📥 **Response Success (201)**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 5,
      "nombre": "Nuevo Usuario",
      "email": "nuevo@empresa1.com",
      "rol": "usuario",
      "created_at": "2024-01-15T15:45:30.000000Z",
      "updated_at": "2024-01-15T15:45:30.000000Z"
    },
    "token": "5|NewTokenGeneratedForUser123456789ABC",
    "empresa": "empresa1"
  },
  "message": "Usuario registrado exitosamente"
}
```

### ❌ **Response Error (422)**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "email": ["El email ya existe en esta empresa"],
    "password": ["La contraseña debe tener al menos 8 caracteres"]
  }
}
```

### 🔧 **Curl Example**
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Host: empresa1.localhost:8000" \
  -d '{
    "nombre": "Nuevo Usuario",
    "email": "nuevo@empresa1.com",
    "password": "password123",
    "password_confirmation": "password123",
    "rol": "usuario"
  }'
```

---

## 🚪 **POST /api/logout**
Cierra la sesión del usuario autenticado.

### 📤 **Request**
```http
POST /api/logout
Host: empresa1.localhost:8000
Authorization: Bearer 1|8Zx9Y2VmNpQrStUvWxYz3A4B5C6D7E8F9G0H1I2J3K4L5M6N7O8P9Q0R1S2T3U4V
```

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": null,
  "message": "Sesión cerrada exitosamente"
}
```

### 🔧 **Curl Example**
```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H "Authorization: Bearer 1|8Zx9Y2VmNpQrStUvWxYz3A4B5C6D7E8F9G0H1I2J3K4L5M6N7O8P9Q0R1S2T3U4V" \
  -H "Host: empresa1.localhost:8000"
```

---

# 👥 Endpoints de Usuarios

> **Nota**: Todos los endpoints de usuarios requieren autenticación. Los usuarios con rol 'usuario' solo pueden gestionar sus propios datos.

## 📋 **GET /api/usuarios**
Obtiene lista de usuarios del tenant actual.

### 📤 **Request**
```http
GET /api/usuarios
Host: empresa1.localhost:8000
Authorization: Bearer {token_admin}
```

### 🔍 **Parámetros de Query (Opcionales)**
| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| search | string | Buscar por nombre o email | `?search=admin` |
| rol | string | Filtrar por rol | `?rol=admin` |
| limit | integer | Límite de resultados | `?limit=10` |
| page | integer | Página para paginación | `?page=2` |

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": {
    "usuarios": [
      {
        "id": 1,
        "nombre": "Admin Empresa 1",
        "email": "admin@empresa1.com",
        "rol": "admin",
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
      },
      {
        "id": 2,
        "nombre": "Usuario Empresa 1",
        "email": "user@empresa1.com",
        "rol": "usuario",
        "created_at": "2024-01-15T11:15:00.000000Z",
        "updated_at": "2024-01-15T11:15:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 1,
      "total_items": 2,
      "per_page": 10
    }
  },
  "message": "Usuarios obtenidos correctamente"
}
```

### 🔧 **Curl Example**
```bash
curl -X GET "http://127.0.0.1:8000/api/usuarios?search=admin&limit=10" \
  -H "Authorization: Bearer {token}" \
  -H "Host: empresa1.localhost:8000"
```

---

## 👤 **GET /api/usuarios/{id}**
Obtiene un usuario específico por ID.

### 📤 **Request**
```http
GET /api/usuarios/1
Host: empresa1.localhost:8000
Authorization: Bearer {token}
```

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": {
    "usuario": {
      "id": 1,
      "nombre": "Admin Empresa 1",
      "email": "admin@empresa1.com",
      "rol": "admin",
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    }
  },
  "message": "Usuario encontrado"
}
```

### ❌ **Response Error (404)**
```json
{
  "success": false,
  "message": "Usuario no encontrado"
}
```

---

## ✏️ **PUT /api/usuarios/{id}**
Actualiza un usuario existente.

### 📤 **Request**
```http
PUT /api/usuarios/1
Host: empresa1.localhost:8000
Authorization: Bearer {token_admin}
Content-Type: application/json

{
  "nombre": "Admin Empresa 1 Actualizado",
  "email": "admin.nuevo@empresa1.com",
  "rol": "admin"
}
```

### 📋 **Parámetros**
| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| nombre | string | ❌ | Mínimo 2 caracteres |
| email | string | ❌ | Email válido, único en tenant |
| rol | string | ❌ | 'admin' o 'usuario' |
| password | string | ❌ | Mínimo 8 caracteres |

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": {
    "usuario": {
      "id": 1,
      "nombre": "Admin Empresa 1 Actualizado",
      "email": "admin.nuevo@empresa1.com",
      "rol": "admin",
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T16:20:15.000000Z"
    }
  },
  "message": "Usuario actualizado exitosamente"
}
```

### ❌ **Response Error (403)**
```json
{
  "success": false,
  "message": "Sin permisos para modificar este usuario"
}
```

---

## 🗑️ **DELETE /api/usuarios/{id}**
Elimina un usuario del sistema.

### 📤 **Request**
```http
DELETE /api/usuarios/2
Host: empresa1.localhost:8000
Authorization: Bearer {token_admin}
```

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": null,
  "message": "Usuario eliminado exitosamente"
}
```

### ❌ **Response Error (403)**
```json
{
  "success": false,
  "message": "Sin permisos para eliminar este usuario"
}
```

---

# ✅ Endpoints de Tareas

> **Nota**: Todos los endpoints de tareas requieren autenticación. Los usuarios pueden ver todas las tareas pero solo pueden crear/modificar las propias.

## 📋 **GET /api/tareas**
Obtiene lista de tareas del tenant actual.

### 📤 **Request**
```http
GET /api/tareas
Host: empresa1.localhost:8000
Authorization: Bearer {token}
```

### 🔍 **Parámetros de Query (Opcionales)**
| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| search | string | Buscar por título o descripción | `?search=reunión` |
| estado | string | Filtrar por estado | `?estado=completada` |
| usuario_id | integer | Filtrar por creador | `?usuario_id=1` |
| limit | integer | Límite de resultados | `?limit=20` |
| page | integer | Página para paginación | `?page=1` |

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": {
    "tareas": [
      {
        "id": 1,
        "titulo": "Revisar documentación",
        "descripcion": "Revisar y actualizar la documentación del proyecto",
        "estado": "pendiente",
        "usuario_id": 1,
        "created_at": "2024-01-15T12:00:00.000000Z",
        "updated_at": "2024-01-15T12:00:00.000000Z",
        "usuario": {
          "id": 1,
          "nombre": "Admin Empresa 1",
          "email": "admin@empresa1.com"
        }
      },
      {
        "id": 2,
        "titulo": "Preparar presentación",
        "descripcion": "Crear slides para reunión del equipo",
        "estado": "completada",
        "usuario_id": 2,
        "created_at": "2024-01-15T13:30:00.000000Z",
        "updated_at": "2024-01-15T14:45:00.000000Z",
        "usuario": {
          "id": 2,
          "nombre": "Usuario Empresa 1",
          "email": "user@empresa1.com"
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 1,
      "total_items": 2,
      "per_page": 20
    }
  },
  "message": "Tareas obtenidas correctamente"
}
```

### 🔧 **Curl Example**
```bash
curl -X GET "http://127.0.0.1:8000/api/tareas?estado=pendiente&limit=10" \
  -H "Authorization: Bearer {token}" \
  -H "Host: empresa1.localhost:8000"
```

---

## ✅ **GET /api/tareas/{id}**
Obtiene una tarea específica por ID.

### 📤 **Request**
```http
GET /api/tareas/1
Host: empresa1.localhost:8000
Authorization: Bearer {token}
```

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": {
    "tarea": {
      "id": 1,
      "titulo": "Revisar documentación",
      "descripcion": "Revisar y actualizar la documentación del proyecto",
      "estado": "pendiente",
      "usuario_id": 1,
      "created_at": "2024-01-15T12:00:00.000000Z",
      "updated_at": "2024-01-15T12:00:00.000000Z",
      "usuario": {
        "id": 1,
        "nombre": "Admin Empresa 1",
        "email": "admin@empresa1.com"
      }
    }
  },
  "message": "Tarea encontrada"
}
```

---

## ➕ **POST /api/tareas**
Crea una nueva tarea.

### 📤 **Request**
```http
POST /api/tareas
Host: empresa1.localhost:8000
Authorization: Bearer {token}
Content-Type: application/json

{
  "titulo": "Nueva tarea importante",
  "descripcion": "Descripción detallada de la tarea a realizar",
  "estado": "pendiente"
}
```

### 📋 **Parámetros**
| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| titulo | string | ✅ | Mínimo 3 caracteres, máximo 255 |
| descripcion | string | ❌ | Máximo 1000 caracteres |
| estado | string | ❌ | 'pendiente' o 'completada' (default: 'pendiente') |

### 📥 **Response Success (201)**
```json
{
  "success": true,
  "data": {
    "tarea": {
      "id": 3,
      "titulo": "Nueva tarea importante",
      "descripcion": "Descripción detallada de la tarea a realizar",
      "estado": "pendiente",
      "usuario_id": 1,
      "created_at": "2024-01-15T16:30:00.000000Z",
      "updated_at": "2024-01-15T16:30:00.000000Z"
    }
  },
  "message": "Tarea creada exitosamente"
}
```

### ❌ **Response Error (422)**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "titulo": ["El título es obligatorio"],
    "estado": ["El estado debe ser 'pendiente' o 'completada'"]
  }
}
```

---

## ✏️ **PUT /api/tareas/{id}**
Actualiza una tarea existente.

### 📤 **Request**
```http
PUT /api/tareas/1
Host: empresa1.localhost:8000
Authorization: Bearer {token}
Content-Type: application/json

{
  "titulo": "Revisar documentación - ACTUALIZADO",
  "descripcion": "Revisar y actualizar toda la documentación del proyecto antes del viernes",
  "estado": "completada"
}
```

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": {
    "tarea": {
      "id": 1,
      "titulo": "Revisar documentación - ACTUALIZADO",
      "descripcion": "Revisar y actualizar toda la documentación del proyecto antes del viernes",
      "estado": "completada",
      "usuario_id": 1,
      "created_at": "2024-01-15T12:00:00.000000Z",
      "updated_at": "2024-01-15T16:45:00.000000Z"
    }
  },
  "message": "Tarea actualizada exitosamente"
}
```

### ❌ **Response Error (403)**
```json
{
  "success": false,
  "message": "Solo puedes modificar tus propias tareas"
}
```

---

## 🗑️ **DELETE /api/tareas/{id}**
Elimina una tarea del sistema.

### 📤 **Request**
```http
DELETE /api/tareas/3
Host: empresa1.localhost:8000
Authorization: Bearer {token}
```

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": null,
  "message": "Tarea eliminada exitosamente"
}
```

### ❌ **Response Error (403)**
```json
{
  "success": false,
  "message": "Solo puedes eliminar tus propias tareas"
}
```

---

# 🏢 Endpoints del Sistema

## 📊 **GET /api/stats**
Obtiene estadísticas generales del tenant.

### 📤 **Request**
```http
GET /api/stats
Host: empresa1.localhost:8000
Authorization: Bearer {token}
```

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": {
    "stats": {
      "total_usuarios": 5,
      "total_tareas": 12,
      "tareas_pendientes": 8,
      "tareas_completadas": 4,
      "usuarios_admin": 2,
      "usuarios_normales": 3
    },
    "empresa": "empresa1"
  },
  "message": "Estadísticas obtenidas correctamente"
}
```

---

## ❤️ **GET /api/health**
Verifica el estado de la API.

### 📤 **Request**
```http
GET /api/health
```

### 📥 **Response Success (200)**
```json
{
  "success": true,
  "data": {
    "status": "OK",
    "timestamp": "2024-01-15T16:50:00.000000Z",
    "version": "1.0.0",
    "environment": "local",
    "database": "connected"
  },
  "message": "Sistema funcionando correctamente"
}
```

---

# 🧪 Ejemplos de Uso Completos

## 🔄 **Flujo Completo de Autenticación**

### 1. Login y obtener token
```bash
# Login
LOGIN_RESPONSE=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Host: empresa1.localhost:8000" \
  -d '{
    "email": "admin@empresa1.com",
    "password": "password123"
  }')

# Extraer token (usando jq)
TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.data.token')
echo "Token obtenido: $TOKEN"
```

### 2. Verificar usuario autenticado
```bash
curl -X GET http://127.0.0.1:8000/api/me \
  -H "Authorization: Bearer $TOKEN" \
  -H "Host: empresa1.localhost:8000"
```

### 3. Cerrar sesión
```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Host: empresa1.localhost:8000"
```

## 👥 **Gestión Completa de Usuarios**

### 1. Listar todos los usuarios
```bash
curl -X GET http://127.0.0.1:8000/api/usuarios \
  -H "Authorization: Bearer $TOKEN" \
  -H "Host: empresa1.localhost:8000"
```

### 2. Crear nuevo usuario
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Host: empresa1.localhost:8000" \
  -d '{
    "nombre": "Test Usuario",
    "email": "test@empresa1.com",
    "password": "password123",
    "password_confirmation": "password123",
    "rol": "usuario"
  }'
```

### 3. Actualizar usuario
```bash
curl -X PUT http://127.0.0.1:8000/api/usuarios/5 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Host: empresa1.localhost:8000" \
  -d '{
    "nombre": "Test Usuario Actualizado",
    "email": "test.updated@empresa1.com"
  }'
```

## ✅ **Gestión Completa de Tareas**

### 1. Crear nueva tarea
```bash
curl -X POST http://127.0.0.1:8000/api/tareas \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Host: empresa1.localhost:8000" \
  -d '{
    "titulo": "Tarea de ejemplo",
    "descripcion": "Esta es una tarea de prueba",
    "estado": "pendiente"
  }'
```

### 2. Listar tareas con filtros
```bash
curl -X GET "http://127.0.0.1:8000/api/tareas?estado=pendiente&limit=5" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Host: empresa1.localhost:8000"
```

### 3. Marcar tarea como completada
```bash
curl -X PUT http://127.0.0.1:8000/api/tareas/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Host: empresa1.localhost:8000" \
  -d '{
    "estado": "completada"
  }'
```

---

# 🔍 Testing y Herramientas

## 🧪 **Postman Collection**
Importa esta colección en Postman para probar todos los endpoints:

```json
{
  "info": {
    "name": "Sistema Multitenant API",
    "description": "Colección completa de endpoints"
  },
  "variable": [
    {
      "key": "baseUrl",
      "value": "http://127.0.0.1:8000/api"
    },
    {
      "key": "token",
      "value": ""
    },
    {
      "key": "empresa1Host",
      "value": "empresa1.localhost:8000"
    },
    {
      "key": "empresa2Host",
      "value": "empresa2.localhost:8000"
    }
  ],
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Host",
                "value": "{{empresa1Host}}"
              }
            ],
            "url": "{{baseUrl}}/login",
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"admin@empresa1.com\",\n  \"password\": \"password123\"\n}"
            }
          }
        }
      ]
    }
  ]
}
```

## 🔧 **Scripts de Testing Automatizado**

### Bash Script para Testing Completo
```bash
#!/bin/bash

# testing_api.sh - Script de pruebas automatizadas

BASE_URL="http://127.0.0.1:8000/api"
HOST_EMPRESA1="empresa1.localhost:8000"
HOST_EMPRESA2="empresa2.localhost:8000"

echo "🧪 === TESTING API SISTEMA MULTITENANT ==="

# Test 1: Health Check
echo "1. Testing Health Check..."
response=$(curl -s -w "%{http_code}" -o /tmp/health.json $BASE_URL/health)
if [ "$response" = "200" ]; then
    echo "   ✅ Health Check OK"
else
    echo "   ❌ Health Check Failed: $response"
fi

# Test 2: Login Empresa 1
echo "2. Testing Login Empresa 1..."
token1=$(curl -s -X POST $BASE_URL/login \
  -H "Content-Type: application/json" \
  -H "Host: $HOST_EMPRESA1" \
  -d '{"email":"admin@empresa1.com","password":"password123"}' \
  | jq -r '.data.token')

if [ "$token1" != "null" ] && [ "$token1" != "" ]; then
    echo "   ✅ Login Empresa 1 OK - Token: ${token1:0:20}..."
else
    echo "   ❌ Login Empresa 1 Failed"
    exit 1
fi

# Test 3: Get User Info
echo "3. Testing User Info..."
user_info=$(curl -s -X GET $BASE_URL/me \
  -H "Authorization: Bearer $token1" \
  -H "Host: $HOST_EMPRESA1")

user_email=$(echo $user_info | jq -r '.data.user.email')
if [ "$user_email" = "admin@empresa1.com" ]; then
    echo "   ✅ User Info OK"
else
    echo "   ❌ User Info Failed"
fi

# Test 4: Create Task
echo "4. Testing Create Task..."
task_response=$(curl -s -X POST $BASE_URL/tareas \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $token1" \
  -H "Host: $HOST_EMPRESA1" \
  -d '{"titulo":"Test Task","descripcion":"Testing API","estado":"pendiente"}')

task_id=$(echo $task_response | jq -r '.data.tarea.id')
if [ "$task_id" != "null" ] && [ "$task_id" != "" ]; then
    echo "   ✅ Create Task OK - ID: $task_id"
else
    echo "   ❌ Create Task Failed"
fi

# Test 5: Get Tasks
echo "5. Testing Get Tasks..."
tasks_response=$(curl -s -X GET $BASE_URL/tareas \
  -H "Authorization: Bearer $token1" \
  -H "Host: $HOST_EMPRESA1")

tasks_count=$(echo $tasks_response | jq '.data.tareas | length')
if [ "$tasks_count" -gt "0" ]; then
    echo "   ✅ Get Tasks OK - Count: $tasks_count"
else
    echo "   ❌ Get Tasks Failed"
fi

# Test 6: Tenant Isolation
echo "6. Testing Tenant Isolation..."
token2=$(curl -s -X POST $BASE_URL/login \
  -H "Content-Type: application/json" \
  -H "Host: $HOST_EMPRESA2" \
  -d '{"email":"admin@empresa2.com","password":"password123"}' \
  | jq -r '.data.token')

if [ "$token2" != "null" ] && [ "$token2" != "" ]; then
    tasks_empresa2=$(curl -s -X GET $BASE_URL/tareas \
      -H "Authorization: Bearer $token2" \
      -H "Host: $HOST_EMPRESA2")
    
    empresa2_count=$(echo $tasks_empresa2 | jq '.data.tareas | length')
    echo "   ✅ Tenant Isolation OK - Empresa2 tasks: $empresa2_count"
else
    echo "   ❌ Tenant Isolation Failed"
fi

echo "🎉 === TESTING COMPLETADO ==="
```

---

# 📚 Referencias Adicionales

## 🔗 **Enlaces Relacionados**
- [Guía de Instalación Local](../installation/local-setup.md)
- [Estructura de Base de Datos](../database/schema.md)
- [Componentes del Frontend](../frontend/components.md)
- [Pruebas Manuales](../testing/manual-testing.md)

## 📖 **Documentación Externa**
- [Laravel Sanctum](https://laravel.com/docs/10.x/sanctum) - Sistema de autenticación
- [Stancl/Tenancy](https://tenancyforlaravel.com/docs/v3) - Multitenant Package
- [JSON API Specification](https://jsonapi.org/) - Estándares de API

## 🆘 **Soporte y Troubleshooting**

### 🐛 **Errores Comunes**

**Error 401 - Token inválido:**
- Verificar que el token esté en el header `Authorization: Bearer {token}`
- Comprobar que el token no haya expirado
- Asegurar que el host header corresponda al tenant correcto

**Error 404 - Endpoint no encontrado:**
- Verificar que la URL sea correcta
- Comprobar que el método HTTP sea el correcto
- Revisar que las rutas estén definidas con `php artisan route:list`

**Error 422 - Validación:**
- Revisar que todos los campos requeridos estén presentes
- Verificar formato de datos (email válido, longitud de strings, etc.)
- Comprobar que los valores estén en los rangos permitidos

---

✅ **¡Documentación de API Completa!**  
Todos los endpoints están documentados y listos para usar en tu sistema multitenant.