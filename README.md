# Segundo parcial de Desarrollo Web

Sistema web para gestión de tareas con arquitectura multitenant que permite a diferentes empresas administrar sus propias tareas y usuarios de forma aislada.

## Tecnologías

- **Backend**: Laravel 10 con sistema multitenant
- **Frontend**: Vue.js 3 + TypeScript + Vuetify
- **Base de datos**: mariaDB (una DB por empresa)

## Estructura del Proyecto

```
parcialWeb/
├── laravel-api/        # API REST Laravel
├── vue-frontend/       # Aplicación Vue.js
└── docs/              # Documentación completa
```

## Documentación Completa

📚 Para instalación, configuración y uso detallado, consulta la documentación completa en la carpeta [`/docs`](./docs/README.md)

## Inicio Rápido

1. **Iniciar el servidor Laravel:**
   ```bash
   start_laravel_server.bat
   ```

2. **Iniciar el frontend Vue:**
   ```bash
   start_vue_frontend.bat
   ```

3. **Acceder a la aplicación:**
   - Empresa 1: http://empresa1.localhost:3000
   - Empresa 2: http://empresa2.localhost:3000

---

🔗 **Ver documentación detallada**: [docs/README.md](./docs/README.md)