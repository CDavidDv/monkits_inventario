# Sistema de Inventario Completo

## 🚀 Características Principales

### 📦 Gestión de Componentes y Kits
- **Componentes individuales**: Chasis, motores, resistencias, etc.
- **Kits ensamblados**: Conjuntos de componentes (ej: Kit Robokart)
- **Relaciones anidadas**: Un kit puede contener otros kits
- **Códigos únicos**: Sistema de identificación por códigos

### 📊 Control de Stock
- **Cantidades mínimas y máximas** configurables por componente
- **Stock actual** en tiempo real
- **Alertas automáticas** cuando se alcanza el mínimo
- **Notificaciones de sobre stock**

### 🔐 Sistema de Roles y Permisos
- **Admin**: Acceso completo al sistema
- **Manager**: Gestión de inventario, reportes, clientes
- **User**: Visualización básica y reportes
- **Inventory**: Gestión específica de inventario

### 📝 Auditoría Completa
- **Logs automáticos** de todas las acciones
- **Historial de movimientos** (entradas, salidas, ajustes)
- **Trazabilidad** de quién hizo qué y cuándo
- **IP y User Agent** registrados

### 🚨 Sistema de Alertas
- **Stock bajo**: Cuando se alcanza la cantidad mínima
- **Sobre stock**: Cuando se excede la cantidad máxima
- **Disponibilidad de kits**: Verificación automática de componentes
- **Notificaciones en tiempo real**

## 🗄️ Estructura de Base de Datos

### Tablas Principales
1. **`categories`** - Categorías de inventario
2. **`components`** - Componentes y kits
3. **`kit_components`** - Relación entre kits y componentes
4. **`inventory_movements`** - Movimientos de stock
5. **`inventory_alerts`** - Alertas del sistema
6. **`audit_logs`** - Logs de auditoría

### Relaciones Clave
- Un componente puede pertenecer a una categoría
- Un kit puede contener múltiples componentes
- Cada movimiento registra el usuario y timestamp
- Todas las acciones se registran en audit_logs

## 🛠️ Instalación y Configuración

### 1. Ejecutar Migraciones
```bash
php artisan migrate
```

### 2. Ejecutar Seeders
```bash
php artisan db:seed
```

### 3. Verificar Permisos
```bash
php artisan permission:cache-reset
```

### 4. Configurar Tarea Programada (Opcional)
```bash
# Agregar al crontab para verificar alertas automáticamente
* * * * * cd /path/to/project && php artisan schedule:run
```

## 📋 Uso del Sistema

### Dashboard Principal
- **URL**: `/inventory/dashboard`
- **Estadísticas generales** del inventario
- **Componentes con stock bajo**
- **Alertas no leídas**
- **Movimientos recientes**
- **Gráficos de distribución**

### Gestión de Componentes
- **Lista**: `/components` - Ver todos los componentes
- **Crear**: `/components/create` - Agregar nuevo componente
- **Ver**: `/components/{id}` - Detalles del componente
- **Editar**: `/components/{id}/edit` - Modificar componente
- **Eliminar**: Soft delete (marca como inactivo)

### Ajustes de Stock
- **Entrada**: Agregar stock al inventario
- **Salida**: Reducir stock del inventario
- **Ajuste**: Corregir cantidad manualmente
- **Transferencia**: Mover entre ubicaciones

## 🔍 Ejemplos de Uso

### Ejemplo: Kit Robokart
```
Kit Robokart Completo (KIT-001)
├── 1x Chasis Robokart (CH-001)
├── 2x Llantas Robokart (LL-001)
├── 2x Motor DC 12V (MT-001)
├── 1x Manual Robokart (MAN-001)
└── 1x Tarjeta Programada (KIT-002)
    ├── 1x Placa PCB (PCB-001)
    ├── 1x Resistencia 10k (RES-001)
    ├── 1x PIC Controller (PIC-001)
    ├── 1x Módulo Bluetooth (BT-001)
    ├── 1x Housing 4 Pines (HOU-001)
    └── 2x Headers 2x4 (HDR-001)
```

### Flujo de Trabajo
1. **Crear componentes** individuales con cantidades mínimas/máximas
2. **Crear kits** que referencien los componentes
3. **Sistema automático** verifica disponibilidad
4. **Alertas** cuando no se pueden construir kits
5. **Movimientos** registran cada cambio de stock
6. **Auditoría** mantiene historial completo

## 🚨 Comandos Artisan

### Verificar Alertas
```bash
php artisan inventory:check-alerts
```

### Limpiar Cache
```bash
php artisan permission:cache-reset
php artisan config:clear
php artisan route:clear
```

## 📊 Reportes Disponibles

### Dashboard
- Total de componentes y kits
- Valor total del inventario
- Distribución por categorías
- Top componentes por valor

### Alertas
- Stock bajo por componente
- Sobre stock por componente
- Kits que no se pueden construir
- Historial de alertas leídas/no leídas

### Movimientos
- Entradas y salidas por fecha
- Ajustes de stock
- Referencias (facturas, órdenes)
- Usuario responsable de cada movimiento

## 🔧 Personalización

### Categorías
- Colores personalizables
- Descripciones detalladas
- Estado activo/inactivo

### Componentes
- Unidades personalizables (pcs, kg, m, etc.)
- Ubicaciones físicas
- Proveedores
- Imágenes opcionales

### Alertas
- Mensajes personalizables
- Tipos de alerta configurables
- Prioridades por componente

## 🚀 Próximas Funcionalidades

- **Notificaciones por email** para alertas críticas
- **API REST** para integración externa
- **Exportación** a Excel/PDF
- **Dashboard móvil** responsive
- **Integración** con sistemas de compras
- **Códigos QR** para identificación rápida

## 📞 Soporte

Para dudas o problemas:
1. Revisar logs en `storage/logs/`
2. Verificar permisos de usuario
3. Ejecutar `php artisan route:list` para verificar rutas
4. Verificar que Spatie Permission esté configurado correctamente

---

**Sistema desarrollado con Laravel 10 + Spatie Permission + Inertia.js + Vue.js**
