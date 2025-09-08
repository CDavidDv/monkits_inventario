# Sistema de Administración de Inventario - Documentación Completa

## 🎯 Descripción General

Se ha implementado un sistema completo de administración de inventario con tres paneles diferenciados según el rol del usuario:

- **Panel de Administración**: Control total del sistema
- **Panel de Trabajadores**: Gestión de stock con conceptos específicos
- **Panel de Supervisores**: Análisis y configuración de límites

## 🏗️ Arquitectura del Sistema

### Tecnologías Utilizadas
- **Backend**: Laravel 10 + Spatie Permission
- **Frontend**: Inertia.js + Vue.js 3
- **Base de Datos**: MySQL
- **Autenticación**: Laravel Jetstream + Sanctum

### Estructura de Roles y Permisos

#### 1. Rol: Admin
**Permisos completos:**
- `admin_access` - Acceso al panel de administración
- `manage_users` - Gestión de usuarios
- `manage_roles` - Gestión de roles
- `manage_components` - Gestión de componentes
- `manage_kits` - Gestión de kits
- `manage_categories` - Gestión de categorías
- `view_all_movements` - Ver todos los movimientos
- `export_data` - Exportar datos

#### 2. Rol: Supervisor
**Permisos específicos:**
- `supervisor_access` - Acceso al panel de supervisores
- `manage_stock_limits` - Ajustar límites de stock
- `view_stock_analysis` - Ver análisis de stock
- `view_category_analysis` - Ver análisis por categorías
- `export_stock_reports` - Exportar reportes

#### 3. Rol: Worker
**Permisos de operación:**
- `worker_access` - Acceso al panel de trabajadores
- `manage_stock_quantities` - Gestionar cantidades de stock
- `add_stock` - Agregar stock
- `remove_stock` - Remover stock
- `adjust_stock` - Ajustar stock
- `view_worker_movements` - Ver movimientos propios

#### 4. Rol: User
**Permisos básicos:**
- `view_dashboard` - Ver dashboard
- `view_reports` - Ver reportes
- `view_inventory` - Ver inventario

## 🚀 Instalación y Configuración

### 1. Requisitos Previos
- PHP 8.1+
- MySQL 5.7+
- Composer
- Node.js 16+

### 2. Instalación
```bash
# Clonar el proyecto
git clone <repository-url>
cd inventario

# Instalar dependencias PHP
composer install

# Instalar dependencias Node.js
npm install

# Configurar variables de entorno
cp .env.example .env
# Editar .env con credenciales de base de datos

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones y seeders
php artisan migrate:fresh --seed

# Compilar assets
npm run build
```

### 3. Usuarios por Defecto
```
Admin: admin@monkits.com / password
Supervisor: supervisor@monkits.com / password
Trabajador: worker@monkits.com / password
Usuario: user@monkits.com / password
```

## 📊 Paneles de Usuario

### 1. Panel de Administración (`/admin/dashboard`)

#### Funcionalidades Principales
- **Dashboard**: Estadísticas generales del sistema
- **Gestión de Componentes**: CRUD completo de componentes
- **Gestión de Kits**: Crear y configurar kits
- **Gestión de Usuarios**: Administrar usuarios y roles
- **Log de Movimientos**: Historial completo de cambios

#### Rutas Disponibles
```
GET  /admin/dashboard              - Dashboard principal
GET  /admin/components            - Lista de componentes
GET  /admin/components/create     - Crear componente
POST /admin/components            - Guardar componente
GET  /admin/components/{id}/edit  - Editar componente
PUT  /admin/components/{id}       - Actualizar componente
DELETE /admin/components/{id}     - Eliminar componente
GET  /admin/kits                 - Lista de kits
GET  /admin/kits/create          - Crear kit
POST /admin/kits                 - Guardar kit
GET  /admin/users                - Lista de usuarios
GET  /admin/users/create         - Crear usuario
POST /admin/users                - Guardar usuario
GET  /admin/movements            - Log de movimientos
GET  /admin/movements/export     - Exportar movimientos
```

### 2. Panel de Trabajadores (`/worker/dashboard`)

#### Funcionalidades Principales
- **Dashboard**: Estadísticas del trabajador
- **Gestión de Stock**: Agregar/remover stock con conceptos
- **Movimientos**: Ver historial personal de movimientos
- **Búsqueda**: Buscar componentes rápidamente

#### Conceptos de Stock Disponibles
- `venta` - Venta
- `armado` - Armado de Kit
- `dañado` - Dañado/Defectuoso
- `ajuste` - Ajuste de Inventario
- `transferencia` - Transferencia
- `devolucion` - Devolución
- `perdida` - Pérdida
- `donacion` - Donación
- `mantenimiento` - Mantenimiento
- `otro` - Otro

#### Rutas Disponibles
```
GET  /worker/dashboard                    - Dashboard del trabajador
GET  /worker/inventory                   - Gestionar inventario
POST /worker/add-stock                   - Agregar stock
POST /worker/remove-stock                - Remover stock
POST /worker/adjust-stock                - Ajustar stock
GET  /worker/movements                   - Ver movimientos propios
GET  /worker/concepts                    - Obtener conceptos
GET  /worker/search-components           - Buscar componentes
GET  /worker/components/{id}/details     - Detalles del componente
```

### 3. Panel de Supervisores (`/supervisor/dashboard`)

#### Funcionalidades Principales
- **Dashboard**: Estadísticas de supervisión
- **Gestión de Límites**: Ajustar mínimos y máximos de stock
- **Análisis de Stock**: Reportes detallados del inventario
- **Análisis por Categorías**: Distribución y valor por categoría
- **Exportación**: Generar reportes en CSV

#### Rutas Disponibles
```
GET  /supervisor/dashboard                    - Dashboard del supervisor
GET  /supervisor/inventory                   - Gestionar inventario
PUT  /supervisor/components/{id}/limits      - Actualizar límites
POST /supervisor/components/bulk-limits      - Actualización masiva
GET  /supervisor/stock-analysis              - Análisis de stock
GET  /supervisor/category-analysis           - Análisis por categorías
GET  /supervisor/stock-report/export         - Exportar reporte
```

## 🔧 Funcionalidades Técnicas

### 1. Sistema de Auditoría
- **Log Automático**: Todos los movimientos se registran automáticamente
- **Trazabilidad**: IP, usuario y timestamp de cada acción
- **Historial Completo**: Movimientos, ajustes y cambios de configuración

### 2. Validaciones de Seguridad
- **Permisos por Ruta**: Middleware de permisos en cada endpoint
- **Validación de Datos**: Reglas de validación robustas
- **Transacciones de BD**: Operaciones atómicas para consistencia

### 3. Exportación de Datos
- **Formato CSV**: Compatible con Excel y otras herramientas
- **Filtros**: Exportación con criterios específicos
- **Headers Personalizados**: Columnas en español con formato adecuado

## 📱 Interfaces de Usuario

### 1. Diseño Responsivo
- **Tailwind CSS**: Framework de utilidades para diseño moderno
- **Componentes Vue**: Reutilizables y mantenibles
- **Mobile First**: Optimizado para dispositivos móviles

### 2. Componentes Principales
- **Modal**: Para formularios y confirmaciones
- **Tablas**: Con paginación y ordenamiento
- **Formularios**: Validación en tiempo real
- **Gráficos**: Visualización de datos estadísticos

### 3. Navegación
- **Breadcrumbs**: Ruta de navegación clara
- **Menú Lateral**: Acceso rápido a funcionalidades
- **Búsqueda Global**: Encontrar componentes rápidamente

## 🚨 Sistema de Alertas

### 1. Tipos de Alertas
- **Stock Bajo**: Cuando se alcanza la cantidad mínima
- **Sobre Stock**: Cuando se excede la cantidad máxima
- **Componentes Críticos**: Stock muy bajo o muy alto

### 2. Notificaciones
- **Dashboard**: Indicadores visuales en tiempo real
- **Tablas**: Resaltado de elementos que requieren atención
- **Contadores**: Número de elementos que necesitan revisión

## 📈 Reportes y Análisis

### 1. Análisis de Stock
- **Distribución por Categorías**: Valor y cantidad por grupo
- **Top Componentes**: Por valor y por cantidad
- **Tendencias**: Movimientos por mes (últimos 6 meses)

### 2. Análisis por Categorías
- **Utilización**: Porcentaje de componentes en estado normal
- **Valor Total**: Suma del valor del inventario por categoría
- **Estadísticas**: Promedios y totales por grupo

### 3. Exportación
- **Reporte de Stock**: Estado completo del inventario
- **Movimientos**: Historial detallado de cambios
- **Formato CSV**: Compatible con herramientas de análisis

## 🔒 Seguridad y Permisos

### 1. Control de Acceso
- **Middleware de Permisos**: Verificación automática en cada ruta
- **Roles Jerárquicos**: Admin > Supervisor > Worker > User
- **Permisos Granulares**: Control específico por funcionalidad

### 2. Validación de Datos
- **Reglas de Negocio**: Validaciones específicas del dominio
- **Sanitización**: Limpieza de datos de entrada
- **Transacciones**: Consistencia en operaciones complejas

### 3. Auditoría
- **Log de Acciones**: Registro de todas las operaciones
- **Trazabilidad**: Usuario, IP y timestamp de cada acción
- **Historial**: Mantenimiento de versiones anteriores

## 🚀 Funcionalidades Futuras

### 1. Mejoras Planificadas
- **Notificaciones por Email**: Alertas automáticas
- **API REST**: Integración con sistemas externos
- **Dashboard Móvil**: Aplicación nativa para dispositivos móviles
- **Códigos QR**: Identificación rápida de componentes

### 2. Integraciones
- **Sistemas de Compras**: Automatización de pedidos
- **Contabilidad**: Sincronización con sistemas financieros
- **CRM**: Gestión de clientes y ventas
- **ERP**: Integración con sistemas empresariales

## 🛠️ Mantenimiento y Soporte

### 1. Comandos Artisan
```bash
# Verificar alertas de inventario
php artisan inventory:check-alerts

# Limpiar cache de permisos
php artisan permission:cache-reset

# Limpiar cache general
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Logs y Debugging
- **Logs de Laravel**: `storage/logs/laravel.log`
- **Logs de Base de Datos**: Configuración en `.env`
- **Debug Mode**: Activación en entorno de desarrollo

### 3. Monitoreo
- **Estado de la Base de Datos**: Verificar conexiones
- **Permisos de Usuario**: Validar roles asignados
- **Rutas Disponibles**: `php artisan route:list`

## 📞 Soporte y Contacto

### 1. Problemas Comunes
- **Error de Permisos**: Verificar roles y permisos asignados
- **Error de Base de Datos**: Verificar conexión y migraciones
- **Error de Rutas**: Verificar archivo `routes/web.php`

### 2. Solución de Problemas
1. Revisar logs en `storage/logs/`
2. Verificar permisos de usuario
3. Ejecutar `php artisan route:list`
4. Verificar configuración de Spatie Permission

### 3. Recursos Adicionales
- **Documentación Laravel**: https://laravel.com/docs
- **Spatie Permission**: https://spatie.be/docs/laravel-permission
- **Inertia.js**: https://inertiajs.com/
- **Vue.js**: https://vuejs.org/

---

**Sistema desarrollado con Laravel 10 + Spatie Permission + Inertia.js + Vue.js**

**Versión**: 1.0.0  
**Fecha**: Agosto 2025  
**Desarrollador**: Sistema de Inventario Completo
