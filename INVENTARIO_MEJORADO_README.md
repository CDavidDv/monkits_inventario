# Sistema de Inventario Mejorado

## Descripción General

El sistema de inventario ha sido completamente reestructurado para manejar tres tipos de items diferentes, cada uno con sus propias categorías y funcionalidades:

- **Elementos**: Items individuales del inventario
- **Kits**: Conjuntos de elementos que se venden como una unidad
- **Componentes**: Items que tienen múltiples elementos asociados

## Estructura del Backend

### Controladores

#### InventoryDashboardController
- **Función**: Controlador principal que envía todos los datos necesarios para el dashboard
- **Datos enviados**:
  - Estadísticas generales del inventario
  - Items por tipo (elementos, kits, componentes)
  - Categorías por tipo
  - Items con stock bajo y exceso de stock

#### ItemController
- **Funcionalidades CRUD completas** para items
- **Métodos principales**:
  - `store()`: Crear nuevo item
  - `update()`: Actualizar item existente
  - `destroy()`: Eliminar item (marca como inactivo)
  - `getByType()`: Obtener items por tipo
  - `search()`: Búsqueda avanzada de items
  - `adjustStock()`: Ajustar stock de items

#### CategoryController
- **Funcionalidades CRUD completas** para categorías
- **Métodos principales**:
  - `store()`: Crear nueva categoría
  - `update()`: Actualizar categoría existente
  - `destroy()`: Eliminar categoría (marca como inactivo)
  - `getByType()`: Obtener categorías por tipo

### Modelos

#### Item
- **Campos principales**:
  - `type`: element, kit, component
  - `category_id`: ID de la categoría correspondiente
  - `current_stock`, `min_stock`, `max_stock`: Control de inventario
  - `purchase_cost`, `sale_price`: Información de costos
  - `assignedTo`: Relación con otro item (para asignaciones)

#### Category
- **Campos principales**:
  - `type`: element, kit, component
  - `name`, `description`, `color`: Información de la categoría
  - `active`: Estado de la categoría

## Estructura del Frontend

### Componentes Principales

#### InventoryTable
- **Función**: Tabla reutilizable para mostrar items
- **Características**:
  - Ordenamiento por columnas
  - Estados de carga (skeleton)
  - Estados vacíos
  - Acciones CRUD (editar, eliminar, asignar)

#### ItemModal
- **Función**: Modal para crear, editar y asignar items
- **Tipos de modal**:
  - `add`: Crear nuevo item
  - `edit`: Editar item existente
  - `assign`: Asignar item a otro

#### CategoryModal
- **Función**: Modal para gestionar categorías
- **Características**:
  - Formulario para crear/editar categorías
  - Tabla de categorías existentes
  - Gestión de colores y descripciones

#### DeleteConfirmationModal
- **Función**: Confirmación de eliminación
- **Características**:
  - Mensaje de confirmación personalizable
  - Botones de acción claros

### Página Principal (Inventario/index.vue)

#### Secciones
1. **Elementos Individuales**
   - Filtros específicos para elementos
   - Tabla con items de tipo 'element'
   - Gestión de categorías de elementos

2. **Kits**
   - Filtros específicos para kits
   - Tabla con items de tipo 'kit'
   - Gestión de categorías de kits

3. **Componentes**
   - Filtros específicos para componentes
   - Tabla con items de tipo 'component'
   - Gestión de categorías de componentes

#### Funcionalidades
- **Filtrado**: Por búsqueda, categoría y estado de stock
- **Ordenamiento**: Por cualquier columna de la tabla
- **CRUD completo**: Crear, leer, actualizar y eliminar items
- **Gestión de categorías**: Crear, editar y eliminar categorías por tipo

## Flujo de Datos

### 1. Carga Inicial
```
InventoryDashboardController → index() → Inertia::render()
↓
Vue Component recibe props
↓
Se renderizan las tres tablas con sus respectivos datos
```

### 2. Operaciones CRUD
```
Usuario hace clic en botón → Componente emite evento
↓
Vue maneja la acción → Llamada a API
↓
Backend procesa → Respuesta JSON
↓
Frontend actualiza estado → Recarga de datos
```

### 3. Filtrado y Ordenamiento
```
Usuario cambia filtros → Computed properties se recalculan
↓
Tablas se actualizan automáticamente
↓
Contadores se actualizan
```

## Rutas API

### Items
- `GET /items` - Listar todos los items
- `POST /items` - Crear nuevo item
- `GET /items/{id}` - Obtener item específico
- `PUT /items/{id}` - Actualizar item
- `DELETE /items/{id}` - Eliminar item
- `GET /items/type/{type}` - Items por tipo
- `GET /items/category/{categoryId}` - Items por categoría
- `GET /items/search` - Búsqueda de items
- `POST /items/{id}/adjust-stock` - Ajustar stock

### Categorías
- `GET /categories` - Listar todas las categorías
- `POST /categories` - Crear nueva categoría
- `GET /categories/{id}` - Obtener categoría específica
- `PUT /categories/{id}` - Actualizar categoría
- `DELETE /categories/{id}` - Eliminar categoría
- `GET /categories/type/{type}` - Categorías por tipo
- `POST /categories/{id}/deactivate` - Desactivar categoría
- `POST /categories/{id}/reactivate` - Reactivar categoría

## Características Técnicas

### Validaciones
- **Frontend**: Validación en tiempo real con Vue
- **Backend**: Validación robusta con Laravel Validator
- **Base de datos**: Constraints a nivel de base de datos

### Seguridad
- **Middleware**: `permission:manage_inventory` para todas las operaciones
- **Sanitización**: Input sanitizado en frontend y backend
- **Transacciones**: Operaciones críticas protegidas con DB transactions

### Performance
- **Eager Loading**: Relaciones cargadas eficientemente
- **Paginación**: Datos paginados cuando sea necesario
- **Caching**: Posibilidad de implementar cache para consultas frecuentes

## Uso del Sistema

### 1. Crear un Nuevo Item
1. Hacer clic en "Nuevo Elemento/Kit/Componente"
2. Llenar el formulario con la información requerida
3. Seleccionar la categoría correspondiente
4. Guardar

### 2. Editar un Item Existente
1. Hacer clic en el botón de editar (lápiz)
2. Modificar los campos necesarios
3. Guardar cambios

### 3. Gestionar Categorías
1. Hacer clic en "Gestionar Categorías"
2. Crear nueva categoría o editar existente
3. Asignar color y descripción
4. Guardar

### 4. Filtrado y Búsqueda
1. Usar la barra de búsqueda para encontrar items
2. Filtrar por categoría específica
3. Filtrar por estado de stock (bajo, normal, exceso)
4. Ordenar por cualquier columna

## Mantenimiento y Extensión

### Agregar Nuevos Tipos de Items
1. Agregar el nuevo tipo en el modelo Item
2. Actualizar el controlador InventoryDashboardController
3. Agregar la nueva sección en el frontend
4. Crear las categorías correspondientes

### Agregar Nuevos Campos
1. Crear migración para la base de datos
2. Actualizar el modelo Item
3. Modificar el controlador
4. Actualizar el frontend

### Personalizar Validaciones
1. Modificar las reglas en ItemController y CategoryController
2. Actualizar las validaciones del frontend
3. Probar con diferentes escenarios

## Consideraciones de Implementación

### Base de Datos
- Asegurarse de que existan las migraciones necesarias
- Verificar que los índices estén creados para campos de búsqueda
- Considerar particionamiento para tablas grandes

### Frontend
- Verificar que todas las dependencias estén instaladas
- Comprobar que los componentes estén registrados correctamente
- Probar en diferentes navegadores y dispositivos

### Backend
- Verificar que los permisos estén configurados correctamente
- Comprobar que las rutas estén protegidas adecuadamente
- Probar las validaciones con datos edge cases
