# Funcionalidad de Asignación por Tipos - Actualizada

## Descripción

Se ha implementado una funcionalidad mejorada para asignar elementos y componentes según el tipo de item, con **cálculo automático de costos** y **validaciones específicas por tipo**.

## Reglas de Asignación por Tipo

### 🎯 **Kits**
- **Pueden asignar**: Elementos + Componentes
- **Cálculo de costo**: Suma de (precio del elemento/componente × cantidad)
- **Validación**: Solo elementos y componentes activos

### 🔧 **Componentes**
- **Pueden asignar**: Solo Elementos
- **Cálculo de costo**: Suma de (precio del elemento × cantidad)
- **Validación**: Solo elementos activos

### 📦 **Elementos**
- **Pueden asignar**: Nada
- **Cálculo de costo**: Manual (campo editable)
- **Validación**: No aplica

## Características Implementadas

### Backend

#### Controlador ItemController
- **Método `getAvailableElements`**: Filtra elementos según el tipo de item
  - `type=kit`: Devuelve elementos + componentes
  - `type=component`: Devuelve solo elementos
  - `type=element`: Devuelve solo elementos
- **Método `store`**: Valida tipos permitidos según el tipo de item
- **Método `update`**: Valida tipos permitidos según el tipo de item
- **Cálculo automático**: Incluye costos de componentes para kits

#### Validaciones por Tipo
```php
// Para kits
$validTypes = ['element', 'component'];

// Para componentes
$validTypes = ['element'];

// Para elementos
$validTypes = ['element']; // No aplica
```

### Frontend

#### Componente ItemModal
- **Filtrado dinámico**: Muestra elementos/componentes según el tipo
- **Labels dinámicos**: "Seleccionar elemento" vs "Seleccionar elemento o componente"
- **Cálculo automático**: Incluye costos de componentes para kits
- **Búsqueda**: Filtra tanto elementos como componentes

#### Página Principal de Inventario
- **Carga dinámica**: Carga elementos disponibles según el tipo al abrir modal
- **API calls**: Pasa el tipo como parámetro a `/items/available-elements?type=${type}`

## Flujo de Trabajo Mejorado

### 1. Crear Kit
1. Usuario selecciona tipo "kit"
2. Modal se abre y carga elementos + componentes disponibles
3. Dropdown muestra "Seleccionar elemento o componente"
4. Usuario puede elegir entre elementos y componentes
5. **Costo se calcula automáticamente** incluyendo componentes
6. Al guardar, se validan los tipos permitidos

### 2. Crear Componente
1. Usuario selecciona tipo "componente"
2. Modal se abre y carga solo elementos disponibles
3. Dropdown muestra "Seleccionar elemento"
4. Usuario solo puede elegir elementos
5. **Costo se calcula automáticamente** solo de elementos
6. Al guardar, se validan los tipos permitidos

### 3. Crear Elemento
1. Usuario selecciona tipo "elemento"
2. Modal se abre sin sección de asignación
3. Campo de costo es editable manualmente
4. No hay validaciones de asignación

## Estructura de Datos

### Request API
```javascript
// GET /items/available-elements?type=kit
{
  "type": "kit", // kit, component, element
  "search": "tornillo", // opcional
  "category_id": 1 // opcional
}

// Response
[
  {
    "id": 1,
    "name": "Tornillo M4",
    "type": "element",
    "category": { "name": "Tornillería" },
    "purchase_cost": 5.00
  },
  {
    "id": 2,
    "name": "Kit de Reparación",
    "type": "component",
    "category": { "name": "Kits" },
    "purchase_cost": 25.00
  }
]
```

### Cálculo de Costos
```javascript
// Para kits
Costo Total = Σ (Precio del Elemento × Cantidad) + Σ (Precio del Componente × Cantidad)

// Para componentes
Costo Total = Σ (Precio del Elemento × Cantidad)

// Para elementos
Costo Total = Precio manual
```

## Interfaz de Usuario

### Modal de Creación/Edición
- ✅ **Labels dinámicos** según el tipo de item
- ✅ **Filtrado automático** de elementos/componentes
- ✅ **Cálculo en tiempo real** incluyendo componentes
- ✅ **Validaciones específicas** por tipo
- ✅ **Búsqueda unificada** en elementos y componentes

### Tabla de Inventario
- ✅ **Nueva columna** "Elementos Asignados"
- ✅ **Vista previa** con tipo de item asignado
- ✅ **Contador** de elementos/componentes
- ✅ **Estado vacío** cuando no hay asignaciones

## Validaciones Implementadas

### Frontend
- ✅ Solo elementos para componentes
- ✅ Elementos + componentes para kits
- ✅ Nada para elementos
- ✅ Cantidades mínimas validadas
- ✅ Búsqueda en tiempo real
- ✅ Cálculo automático de costos

### Backend
- ✅ Validación de tipos permitidos
- ✅ Verificación de existencia
- ✅ Verificación de estado activo
- ✅ Cálculo automático de costos
- ✅ Transacciones de base de datos

## Ejemplos de Uso

### Ejemplo 1: Kit con Elementos y Componentes
```
Kit: "Kit de Reparación Completo"
- Elemento: "Tornillo M4" × 10 unidades = $50.00
- Componente: "Kit Básico" × 2 unidades = $50.00
Costo Total: $100.00
```

### Ejemplo 2: Componente con Solo Elementos
```
Componente: "Kit Básico"
- Elemento: "Tornillo M4" × 5 unidades = $25.00
- Elemento: "Arandela" × 5 unidades = $15.00
Costo Total: $40.00
```

### Ejemplo 3: Elemento Sin Asignaciones
```
Elemento: "Tornillo M4"
- Costo manual: $5.00
- Sin elementos asignados
```

## Consideraciones Técnicas

### Backend
- **Filtrado dinámico**: Query builder con condiciones según tipo
- **Validaciones**: Arrays de tipos permitidos por tipo de item
- **Cálculo**: Incluye costos de componentes para kits
- **API**: Parámetro `type` para filtrar elementos disponibles

### Frontend
- **Computed properties**: Filtrado dinámico de elementos/componentes
- **Watchers**: Actualización automática de costos
- **API calls**: Pasa tipo como parámetro
- **UX**: Labels y opciones dinámicas según tipo

## Próximas Mejoras

1. **Validación de stock**: Verificar disponibilidad al asignar
2. **Historial de cambios**: Tracking de modificaciones en asignaciones
3. **Plantillas**: Crear plantillas reutilizables para kits comunes
4. **Importación masiva**: Cargar asignaciones desde archivos CSV/Excel
5. **Búsqueda avanzada**: Filtros por categoría, stock, precio, etc.
6. **Margen automático**: Calcular precio de venta basado en margen configurado

## Testing

### Casos de Prueba
1. **Crear kit con elementos y componentes**: Verificar cálculo automático
2. **Crear componente con solo elementos**: Verificar validaciones
3. **Crear elemento sin asignaciones**: Verificar campo manual
4. **Validaciones de tipo**: Verificar que no se asignen tipos incorrectos
5. **Cálculo de costos**: Verificar inclusión de componentes en kits
6. **Búsqueda**: Verificar filtrado en elementos y componentes

### Verificación de Datos
- Verificar que las relaciones se guarden correctamente
- Verificar que los costos se calculen incluyendo componentes
- Verificar que las validaciones de tipo funcionen
- Verificar que la interfaz muestre opciones correctas según tipo
