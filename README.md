# Sistema de Inventario - MonKits

## 📋 Descripción General

Sistema de gestión de inventario desarrollado en Laravel con Vue.js e Inertia.js, diseñado para administrar componentes electrónicos, kits y controlar niveles de stock con notificaciones automáticas.

## ✨ Características Principales

### 🔧 Gestión de Inventario
- **Componentes Individuales**: Resistencias, capacitores, microcontroladores, etc.
- **Kits Anidados**: Kits que contienen otros kits (ej: Robokart Kit → Tarjeta Programada → Componentes)
- **Control de Stock**: Cantidades mínimas y máximas configurables
- **Alertas Automáticas**: Notificaciones de stock bajo y sobrestock

### 👥 Sistema de Roles y Permisos
- **Admin**: Acceso completo al sistema
- **Manager**: Gestión de inventario y reportes
- **User**: Consulta y operaciones básicas
- **Inventory**: Gestión especializada de inventario

### 📊 Dashboard y Reportes
- Estadísticas en tiempo real
- Visualización de stock bajo/alto
- Historial de movimientos
- Valor total del inventario
- Gráficos y métricas

### 🔍 Auditoría y Logs
- Registro completo de todas las acciones
- Trazabilidad de cambios
- Historial de movimientos de stock
- Logs de usuarios y IPs

## 🏗️ Arquitectura del Sistema

### Backend (Laravel 10)
- **Framework**: Laravel 10.x
- **Base de Datos**: MySQL
- **Autenticación**: Laravel Fortify + Jetstream
- **Permisos**: Spatie Laravel Permission
- **API**: RESTful con Inertia.js

### Frontend (Vue.js 3)
- **Framework**: Vue.js 3 con Composition API
- **UI**: Tailwind CSS
- **Navegación**: Inertia.js
- **Componentes**: Reutilizables y modulares

## 🗄️ Estructura de la Base de Datos

### Tablas Principales

#### `users`
- Información de usuarios del sistema
- Integración con Spatie Laravel Permission
- Campos: id, name, email, password, active, timestamps

#### `categories`
- Categorías de componentes (Electrónicos, Mecánicos, etc.)
- Campos: id, name, description, color, active, timestamps

#### `components`
- Tabla central para componentes y kits
- Campos: id, name, code, description, unit, cost, location, supplier, image, min_quantity, max_quantity, current_quantity, is_kit, category_id, created_by, updated_by, active, timestamps

#### `kit_components`
- Relación muchos a muchos entre kits y componentes
- Campos: id, kit_id, component_id, quantity_required, timestamps

#### `inventory_movements`
- Registro de todos los movimientos de stock
- Campos: id, component_id, type, quantity, quantity_before, quantity_after, notes, reference, created_by, timestamps

#### `inventory_alerts`
- Alertas de stock bajo/alto
- Campos: id, component_id, type, message, is_read, read_at, read_by, timestamps

#### `audit_logs`
- Logs de auditoría del sistema
- Campos: id, action, model_type, model_id, old_values, new_values, description, ip_address, user_agent, user_id, timestamps

### Tablas de Permisos (Spatie)
- `roles`: Roles del sistema
- `permissions`: Permisos disponibles
- `model_has_roles`: Relación usuarios-roles
- `model_has_permissions`: Relación usuarios-permisos
- `role_has_permissions`: Relación roles-permisos

## 🚀 Instalación y Configuración

### Requisitos Previos
- PHP 8.1+
- Composer
- Node.js 16+
- MySQL 8.0+
- XAMPP/WAMP/LAMP

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone [URL_DEL_REPOSITORIO]
cd inventario
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Instalar dependencias JavaScript**
```bash
npm install
```

4. **Configurar variables de entorno**
```bash
cp .env.example .env
# Editar .env con la configuración de tu base de datos
```

5. **Generar clave de aplicación**
```bash
php artisan key:generate
```

6. **Ejecutar migraciones**
```bash
php artisan migrate
```

7. **Ejecutar seeders**
```bash
php artisan db:seed
```

8. **Compilar assets**
```bash
npm run dev
```

9. **Iniciar servidor**
```bash
php artisan serve
```

### Usuarios por Defecto

| Rol | Email | Password | Descripción |
|-----|-------|----------|-------------|
| admin | admin@monkits.com | password | Administrador del sistema |
| manager | manager@monkits.com | password | Gerente de inventario |
| user | user@monkits.com | password | Usuario estándar |
| inventory | inventory@monkits.com | password | Especialista en inventario |

## 🔐 Sistema de Permisos

### Roles Disponibles

#### Admin
- Acceso completo a todas las funcionalidades
- Gestión de usuarios y roles
- Configuración del sistema
- Reportes administrativos

#### Manager
- Gestión completa de inventario
- Crear/editar/eliminar componentes y kits
- Ajustes de stock
- Reportes de inventario
- Gestión de alertas

#### User
- Consulta de inventario
- Ver reportes básicos
- Operaciones de stock (con aprobación)

#### Inventory
- Gestión especializada de inventario
- Ajustes de stock
- Creación de componentes
- Gestión de kits

### Permisos Clave
- `components.view`: Ver componentes
- `components.create`: Crear componentes
- `components.edit`: Editar componentes
- `components.delete`: Eliminar componentes
- `kits.manage`: Gestión de kits
- `inventory.adjust`: Ajustes de stock
- `reports.view`: Ver reportes
- `users.manage`: Gestión de usuarios

## 📱 Funcionalidades del Sistema

### Gestión de Componentes
- **Crear Componente**: Nombre, código, descripción, unidad, costo, ubicación, proveedor
- **Editar Componente**: Modificar cualquier campo
- **Eliminar Componente**: Eliminación lógica con auditoría
- **Imagen**: Subir imagen del componente
- **Categorías**: Organización por categorías

### Gestión de Kits
- **Crear Kit**: Definir componentes y cantidades requeridas
- **Kits Anidados**: Kits que contienen otros kits
- **Cálculo Automático**: Stock disponible basado en componentes
- **Validación**: Verificar disponibilidad de componentes

### Control de Stock
- **Entrada de Stock**: Registrar nuevas existencias
- **Salida de Stock**: Registrar salidas
- **Ajustes**: Correcciones de inventario
- **Transferencias**: Movimientos entre ubicaciones
- **Historial**: Trazabilidad completa

### Alertas y Notificaciones
- **Stock Bajo**: Alertas automáticas cuando se alcanza el mínimo
- **Sobrestock**: Notificaciones cuando se excede el máximo
- **Kit No Disponible**: Alertas cuando faltan componentes
- **Gestión de Alertas**: Marcar como leídas, asignar responsabilidades

### Reportes y Dashboard
- **Dashboard Principal**: Vista general del inventario
- **Estadísticas**: Totales, valores, tendencias
- **Reportes de Stock**: Niveles actuales vs. mínimos/máximos
- **Movimientos**: Historial de transacciones
- **Valoración**: Valor total del inventario

## 🛠️ Comandos Artisan

### Verificar Alertas de Inventario
```bash
php artisan inventory:check-alerts
```

### Crear Usuario
```bash
php artisan make:user --name="Nombre" --email="email@ejemplo.com" --role="admin"
```

### Limpiar Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Verificar Estado del Sistema
```bash
php artisan about
```

## 📊 API Endpoints

### Inventario
- `GET /inventory/dashboard` - Dashboard principal
- `GET /inventory/stats` - Estadísticas del inventario
- `GET /inventory/alerts` - Alertas activas
- `POST /inventory/alerts/{id}/read` - Marcar alerta como leída

### Componentes
- `GET /components` - Lista de componentes
- `POST /components` - Crear componente
- `GET /components/{id}` - Ver componente
- `PUT /components/{id}` - Actualizar componente
- `DELETE /components/{id}` - Eliminar componente
- `POST /components/{id}/adjust-stock` - Ajustar stock

### Usuarios
- `GET /users` - Lista de usuarios
- `POST /users` - Crear usuario
- `PUT /users/{id}` - Actualizar usuario
- `DELETE /users/{id}` - Eliminar usuario

## 🔧 Personalización

### Configuración de Alertas
```php
// config/inventory.php
return [
    'low_stock_threshold' => 10,
    'overstock_threshold' => 1000,
    'alert_check_frequency' => 'daily',
];
```

### Personalización de Roles
```php
// database/seeders/RolesSeeder.php
$adminRole = Role::create(['name' => 'admin']);
$adminRole->givePermissionTo([
    'components.*',
    'kits.*',
    'inventory.*',
    'users.*',
    'reports.*'
]);
```

### Traducciones
```php
// resources/lang/es/inventory.php
return [
    'low_stock' => 'Stock Bajo',
    'overstock' => 'Sobrestock',
    'component_created' => 'Componente creado exitosamente',
];
```

## 🧪 Testing

### Ejecutar Tests
```bash
php artisan test
```

### Tests Disponibles
- Tests de autenticación
- Tests de permisos
- Tests de componentes
- Tests de inventario
- Tests de API

## 📈 Monitoreo y Mantenimiento

### Logs del Sistema
- **Laravel Logs**: `storage/logs/laravel.log`
- **Audit Logs**: Tabla `audit_logs`
- **Error Logs**: Logs de errores y excepciones

### Tareas Programadas
```bash
# Verificar alertas diariamente
php artisan schedule:run
```

### Backup de Base de Datos
```bash
php artisan backup:run
```

## 🚨 Solución de Problemas

### Errores Comunes

#### Error de Migración
```bash
# Si hay problemas con migraciones
php artisan migrate:fresh --seed
```

#### Problemas de Permisos
```bash
# Limpiar cache de permisos
php artisan permission:cache-reset
```

#### Error de Componentes
```bash
# Verificar estado de la base de datos
php artisan tinker
>>> DB::connection()->getPdo();
```

### Verificación del Sistema
1. **Base de Datos**: Verificar conexión
2. **Permisos**: Confirmar roles asignados
3. **Rutas**: Verificar que las rutas estén registradas
4. **Componentes**: Confirmar que los componentes se cargan

## 🔄 Actualizaciones

### Actualizar Dependencias
```bash
composer update
npm update
```

### Ejecutar Nuevas Migraciones
```bash
php artisan migrate
```

### Limpiar Cache Después de Actualizaciones
```bash
php artisan optimize:clear
```

## 📚 Recursos Adicionales

### Documentación
- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/guide/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)

### Herramientas de Desarrollo
- **Laravel Telescope**: Debugging y monitoreo
- **Laravel Debugbar**: Barra de debug
- **Vue DevTools**: Herramientas de desarrollo Vue.js

## 🤝 Contribución

### Estándares de Código
- PSR-12 para PHP
- ESLint + Prettier para JavaScript
- Convenciones de Laravel
- Documentación de funciones

### Flujo de Trabajo
1. Fork del repositorio
2. Crear rama feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Soporte

### Contacto
- **Email**: soporte@monkits.com
- **Documentación**: [URL_DOCUMENTACION]
- **Issues**: [URL_REPOSITORIO]/issues

### Horarios de Soporte
- **Lunes a Viernes**: 9:00 AM - 6:00 PM
- **Sábados**: 9:00 AM - 2:00 PM
- **Emergencias**: 24/7

---

**Versión**: 1.0.0  
**Última Actualización**: {{ new Date().toLocaleDateString('es-ES') }}  
**Desarrollado por**: Equipo de Desarrollo MonKits
