# Dashboard del Sistema de Inventario

## 🎯 Descripción General

El Dashboard Principal es el centro de control del sistema de inventario, diseñado para proporcionar una visión completa y en tiempo real del estado del inventario, alertas críticas y estadísticas importantes.

## ✨ Características Principales

### 📊 Estadísticas en Tiempo Real
- **Total de Componentes**: Contador de todos los componentes activos
- **Total de Kits**: Contador de kits ensamblados disponibles
- **Stock Bajo**: Componentes que requieren atención inmediata
- **Sobre Stock**: Componentes que exceden el stock máximo
- **Total de Categorías**: Organización del inventario
- **Valor Total**: Valor monetario total del inventario

### 🚨 Sistema de Alertas
- **Stock Bajo**: Componentes críticos que requieren reabastecimiento
- **Alertas No Leídas**: Notificaciones pendientes de revisión
- **Indicadores Visuales**: Colores y badges para identificar prioridades

### 📈 Información Detallada
- **Top Componentes por Valor**: Los componentes más valiosos del inventario
- **Distribución por Categorías**: Visualización de la organización del inventario
- **Movimientos Recientes**: Historial de entradas, salidas y ajustes
- **Estado del Stock**: Indicadores de stock bajo, normal y alto

### 🎨 Interfaz Moderna
- **Diseño Responsivo**: Adaptable a diferentes tamaños de pantalla
- **Animaciones Suaves**: Transiciones y efectos visuales atractivos
- **Tema Personalizable**: Colores y estilos configurables
- **Iconografía Clara**: Iconos intuitivos para cada funcionalidad

## 🏗️ Arquitectura del Sistema

### Controlador Principal
- **DashboardController**: Maneja la lógica del dashboard principal
- **Cache Inteligente**: Sistema de cache para mejorar el rendimiento
- **Configuración Dinámica**: Parámetros configurables desde archivos de config

### Modelos Utilizados
- **Component**: Información de componentes y kits
- **Category**: Categorías de inventario
- **InventoryAlert**: Sistema de alertas
- **InventoryMovement**: Historial de movimientos
- **User**: Información de usuarios

### Frontend
- **Vue.js 3**: Framework de interfaz de usuario
- **Inertia.js**: Integración con Laravel
- **Tailwind CSS**: Framework de estilos
- **Componentes Reutilizables**: Arquitectura modular

## ⚙️ Configuración

### Archivo de Configuración
El dashboard se configura a través del archivo `config/dashboard.php`:

```php
return [
    'limits' => [
        'low_stock_components' => 5,
        'unread_alerts' => 5,
        'recent_movements' => 10,
        'category_distribution' => 6,
        'top_value_components' => 5,
    ],
    
    'alerts' => [
        'auto_refresh_interval' => 30000,
        'show_critical_first' => true,
    ],
    
    'theme' => [
        'primary_color' => '#3B82F6',
        'secondary_color' => '#10B981',
        // ... más configuraciones
    ],
];
```

### Personalización de Límites
- **Componentes con Stock Bajo**: Número máximo mostrado
- **Alertas No Leídas**: Cantidad de alertas visibles
- **Movimientos Recientes**: Historial de movimientos
- **Distribución de Categorías**: Top categorías mostradas
- **Top Componentes por Valor**: Ranking de componentes

## 🔧 Funcionalidades Técnicas

### Sistema de Cache
- **Cache por Usuario**: Cada usuario tiene su propio cache
- **TTL Configurable**: Tiempo de vida del cache ajustable
- **Invalidación Manual**: Función para limpiar cache
- **Mejora de Rendimiento**: Reduce consultas a la base de datos

### Permisos y Seguridad
- **Middleware de Autenticación**: Acceso solo para usuarios autenticados
- **Verificación de Permisos**: Control de acceso basado en roles
- **Auditoría**: Registro de todas las acciones realizadas

### Optimización de Consultas
- **Eager Loading**: Carga eficiente de relaciones
- **Límites Configurables**: Control de cantidad de datos
- **Índices de Base de Datos**: Optimización de consultas

## 📱 Responsive Design

### Breakpoints
- **Mobile First**: Diseño optimizado para dispositivos móviles
- **Tablet**: Adaptación para pantallas medianas
- **Desktop**: Experiencia completa en pantallas grandes

### Componentes Adaptativos
- **Grid Responsivo**: Columnas que se ajustan automáticamente
- **Cards Flexibles**: Elementos que se reorganizan según el espacio
- **Navegación Móvil**: Menú optimizado para touch

## 🎨 Sistema de Temas

### Colores Principales
- **Azul**: Componentes y estadísticas generales
- **Verde**: Kits y elementos positivos
- **Rojo**: Alertas y stock bajo
- **Amarillo**: Advertencias y sobre stock
- **Púrpura**: Valor monetario y finanzas

### Personalización
- **Variables CSS**: Colores configurables
- **Gradientes**: Efectos visuales modernos
- **Sombras**: Profundidad y dimensión
- **Transiciones**: Animaciones suaves

## 🚀 Acciones Rápidas

### Botones de Acceso Directo
- **Nuevo Componente**: Crear componente rápidamente
- **Gestionar Kits**: Administrar kits del inventario
- **Reportes**: Acceso a dashboard detallado
- **Configuración**: Ajustes del sistema

### Navegación Intuitiva
- **Enlaces Contextuales**: Navegación desde elementos específicos
- **Breadcrumbs**: Ruta de navegación clara
- **Menús Desplegables**: Acceso a funcionalidades relacionadas

## 📊 Datos y Métricas

### Cálculos Automáticos
- **Valor Total**: Suma de (cantidad × costo) de todos los componentes
- **Porcentajes de Stock**: Relación entre stock actual y máximo
- **Estados de Stock**: Clasificación automática (bajo, normal, alto)
- **Tendencias**: Cambios porcentuales (en desarrollo)

### Filtros y Ordenamiento
- **Por Categoría**: Agrupación lógica de componentes
- **Por Valor**: Ordenamiento por valor monetario
- **Por Stock**: Priorización por nivel de stock
- **Por Fecha**: Ordenamiento cronológico

## 🔔 Sistema de Notificaciones

### Tipos de Alertas
- **Stock Bajo**: Cuando `current_quantity <= min_quantity`
- **Sobre Stock**: Cuando `current_quantity >= max_quantity`
- **Stock Crítico**: Cuando el stock está muy por debajo del mínimo

### Gestión de Alertas
- **Marcado como Leído**: Sistema de seguimiento de alertas
- **Priorización**: Alertas críticas se muestran primero
- **Historial**: Registro de todas las alertas generadas

## 📈 Reportes y Exportación

### Información Disponible
- **Resumen Ejecutivo**: Estadísticas principales
- **Detalle por Categoría**: Análisis por grupos
- **Historial de Movimientos**: Trazabilidad completa
- **Análisis de Valor**: Componentes más valiosos

### Formatos de Exportación
- **PDF**: Reportes formateados
- **Excel**: Datos para análisis
- **CSV**: Intercambio de datos

## 🛠️ Mantenimiento

### Limpieza de Cache
```bash
# Limpiar cache del dashboard
php artisan dashboard:clear-cache

# Limpiar cache general
php artisan cache:clear
```

### Monitoreo de Rendimiento
- **Tiempo de Carga**: Métricas de rendimiento
- **Uso de Memoria**: Consumo de recursos
- **Consultas de BD**: Optimización de queries
- **Cache Hit Rate**: Efectividad del sistema de cache

## 🔮 Futuras Mejoras

### Funcionalidades Planificadas
- **Gráficos Interactivos**: Visualizaciones avanzadas
- **Notificaciones Push**: Alertas en tiempo real
- **Dashboard Móvil**: Aplicación nativa
- **Integración con APIs**: Conexión con sistemas externos
- **Machine Learning**: Predicción de demanda

### Optimizaciones Técnicas
- **WebSockets**: Actualizaciones en tiempo real
- **Service Workers**: Funcionalidad offline
- **PWA**: Aplicación web progresiva
- **Microservicios**: Arquitectura escalable

## 📚 Recursos Adicionales

### Documentación Relacionada
- [README Principal](INVENTARIO_README.md)
- [Guía de Instalación](README.md)
- [API Documentation](docs/api.md)

### Soporte Técnico
- **Issues**: Reportar problemas en GitHub
- **Wiki**: Documentación colaborativa
- **Discussions**: Foro de la comunidad

---

**Dashboard desarrollado con Laravel 10 + Vue.js 3 + Tailwind CSS**
