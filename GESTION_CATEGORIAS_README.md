# Gestión de Categorías - Sistema de Inventario

## Descripción

Se ha implementado un sistema completo de gestión de categorías para el inventario, permitiendo a los usuarios crear, editar y eliminar categorías de componentes.

## Funcionalidades Implementadas

### 1. **Crear Categorías**
- Nombre de la categoría (requerido)
- Descripción opcional
- Color personalizable (selector de color)
- Estado activo/inactivo

### 2. **Editar Categorías**
- Modificar nombre, descripción y color
- Validación para evitar nombres duplicados
- Actualización en tiempo real

### 3. **Eliminar Categorías**
- Verificación de componentes asociados
- Prevención de eliminación si hay dependencias
- Confirmación antes de eliminar

### 4. **Visualización**
- Tabla con todas las categorías
- Contador de componentes por categoría
- Indicador visual de color
- Estado de la categoría

## Cómo Usar

### Acceso a la Gestión de Categorías

1. Navega a la página de **Inventario**
2. Haz clic en el botón **"Gestionar Categorías"** (botón verde)
3. Se abrirá un modal con todas las funcionalidades

### Crear una Nueva Categoría

1. En el modal de gestión, completa el formulario:
   - **Nombre**: Nombre único de la categoría
   - **Descripción**: Descripción opcional
   - **Color**: Selecciona un color personalizado
2. Haz clic en **"Crear"**

### Editar una Categoría Existente

1. En la tabla de categorías, haz clic en el botón de editar (ícono azul)
2. Modifica los campos deseados
3. Haz clic en **"Actualizar"**

### Eliminar una Categoría

1. En la tabla de categorías, haz clic en el botón de eliminar (ícono rojo)
2. Confirma la eliminación en el modal de confirmación
3. **Nota**: Solo se pueden eliminar categorías sin componentes asociados

## Estructura Técnica

### Backend (Laravel)

- **Controlador**: `app/Http/Controllers/CategoryController.php`
- **Modelo**: `app/Models/Category.php`
- **Rutas**: `routes/web.php` (resource routes)
- **Migración**: `database/migrations/2024_12_19_000001_create_categories_table.php`
- **Seeder**: `database/seeders/CategoriesSeeder.php`

### Frontend (Vue.js)

- **Componente**: `resources/js/Pages/Inventario/index.vue`
- **Funcionalidades**:
  - Modal de gestión de categorías
  - Formulario de creación/edición
  - Tabla de categorías
  - Modales de confirmación
  - Integración con API REST

### API Endpoints

- `GET /categories` - Listar todas las categorías
- `POST /categories` - Crear nueva categoría
- `GET /categories/{id}` - Obtener categoría específica
- `PUT /categories/{id}` - Actualizar categoría
- `DELETE /categories/{id}` - Eliminar categoría
- `POST /categories/{id}/deactivate` - Desactivar categoría

## Validaciones

### Crear/Editar Categoría
- **Nombre**: Requerido, único, máximo 255 caracteres
- **Descripción**: Opcional, máximo 500 caracteres
- **Color**: Opcional, formato hexadecimal válido
- **Estado**: Booleano (activo/inactivo)

### Eliminar Categoría
- No se puede eliminar si tiene componentes asociados
- Se requiere confirmación del usuario
- Validación en backend antes de eliminar

## Características de Seguridad

- **CSRF Protection**: Todas las operaciones incluyen token CSRF
- **Validación**: Validación tanto en frontend como backend
- **Permisos**: Integrado con el sistema de permisos existente
- **Auditoría**: Todas las operaciones se registran (trait Auditable)

## Datos de Prueba

El sistema incluye categorías predefinidas:
- Electrónicos (Azul)
- Mecánicos (Verde)
- Kits Completos (Amarillo)
- Herramientas (Rojo)
- Consumibles (Púrpura)
- Documentación (Gris)

## Notas de Implementación

- **Responsive Design**: El modal se adapta a diferentes tamaños de pantalla
- **Estado Reactivo**: Todas las operaciones actualizan la interfaz en tiempo real
- **Manejo de Errores**: Mensajes de error claros para el usuario
- **UX Optimizada**: Confirmaciones antes de acciones destructivas
- **Integración**: Se integra perfectamente con el sistema de inventario existente

## Próximas Mejoras Sugeridas

1. **Filtros y Búsqueda**: Agregar búsqueda y filtros en la tabla de categorías
2. **Ordenamiento**: Permitir ordenar por diferentes columnas
3. **Paginación**: Implementar paginación para grandes cantidades de categorías
4. **Importar/Exportar**: Funcionalidad para importar/exportar categorías
5. **Historial de Cambios**: Ver historial de modificaciones por categoría
6. **Categorías Anidadas**: Soporte para subcategorías
7. **Plantillas**: Plantillas predefinidas para tipos comunes de categorías
