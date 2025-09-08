# Funcionalidad de Asignación por Tipos - Con Selector de Tipo

## Descripción

Se ha implementado una funcionalidad mejorada para asignar elementos y componentes según el tipo de item, con **selector de tipo específico para kits** y **cálculo automático de costos**.

## Reglas de Asignación por Tipo

### 🎯 **Kits**
- **Pueden asignar**: Elementos + Componentes
- **Selector de tipo**: Radio buttons para elegir entre "Elemento" o "Componente"
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

## Nueva Funcionalidad: Selector de Tipo para Kits

### Características del Selector
- **Solo aparece para kits**: No se muestra para componentes o elementos
- **Radio buttons**: Opciones "Elemento" y "Componente"
- **Filtrado dinámico**: Cambia la lista de items disponibles según la selección
- **Limpieza automática**: Al cambiar el tipo, se limpia la selección anterior

### Interfaz de Usuario
```
┌─────────────────────────────────────┐
│ Tipo de item a agregar:             │
│ ○ Elemento  ● Componente            │
└─────────────────────────────────────┘
```

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

### Frontend

#### Componente ItemModal
- **Selector de tipo**: Radio buttons solo para kits
- **Filtrado dinámico**: Muestra elementos/componentes según selección
- **Labels dinámicos**: Cambian según el tipo seleccionado
- **Cálculo automático**: Incluye costos de componentes para kits
- **Búsqueda**: Filtra según el tipo seleccionado
- **Watchers**: Limpian selección al cambiar tipo

#### Variables Reactivas Nuevas
```javascript
const selectedItemType = ref('element') // Para seleccionar tipo en kits
```

#### Computed Properties Actualizadas
```javascript
const availableItems = computed(() => {
    if (props.type === 'kit') {
        // Filtrar según el tipo seleccionado
        if (selectedItemType.value === 'element') {
            return filteredElements.value
        } else if (selectedItemType.value === 'component') {
            return filteredComponents.value
        }
    }
    // ... resto de la lógica
})
```

## Flujo de Trabajo Mejorado

### 1. Crear Kit
1. Usuario selecciona tipo "kit"
2. Modal se abre con selector de tipo (Elemento/Componente)
3. Usuario selecciona "Elemento" o "Componente"
4. Dropdown se filtra según la selección
5. Usuario selecciona el item específico
6. **Costo se calcula automáticamente** incluyendo componentes
7. Al guardar, se validan los tipos permitidos

### 2. Crear Componente
1. Usuario selecciona tipo "componente"
2. Modal se abre sin selector de tipo
3. Dropdown muestra solo elementos
4. Usuario selecciona elemento
5. **Costo se calcula automáticamente** solo de elementos
6. Al guardar, se validan los tipos permitidos

### 3. Crear Elemento
1. Usuario selecciona tipo "elemento"
2. Modal se abre sin sección de asignación
3. Campo de costo es editable manualmente
4. No hay validaciones de asignación

## Estructura de Datos

### Estado del Selector
```javascript
// Para kits
selectedItemType: 'element' | 'component'

// Para componentes y elementos
selectedItemType: 'element' (por defecto)
```

### Filtrado Dinámico
```javascript
// Cuando selectedItemType === 'element'
availableItems = filteredElements

// Cuando selectedItemType === 'component'
availableItems = filteredComponents
```

## Interfaz de Usuario

### Modal de Creación/Edición para Kits
- ✅ **Selector de tipo** con radio buttons
- ✅ **Labels dinámicos** según tipo seleccionado
- ✅ **Filtrado automático** de elementos/componentes
- ✅ **Cálculo en tiempo real** incluyendo componentes
- ✅ **Limpieza automática** al cambiar tipo
- ✅ **Búsqueda específica** según tipo seleccionado

### Modal de Creación/Edición para Componentes
- ✅ **Sin selector de tipo** (solo elementos)
- ✅ **Labels fijos** "Seleccionar elemento"
- ✅ **Filtrado automático** solo elementos
- ✅ **Cálculo en tiempo real** solo de elementos

### Modal de Creación/Edición para Elementos
- ✅ **Sin sección de asignación**
- ✅ **Campo de costo manual**

## Validaciones Implementadas

### Frontend
- ✅ Solo elementos para componentes
- ✅ Elementos + componentes para kits (con selector)
- ✅ Nada para elementos
- ✅ Cantidades mínimas validadas
- ✅ Búsqueda en tiempo real según tipo
- ✅ Cálculo automático de costos
- ✅ Limpieza automática al cambiar tipo

### Backend
- ✅ Validación de tipos permitidos
- ✅ Verificación de existencia
- ✅ Verificación de estado activo
- ✅ Cálculo automático de costos
- ✅ Transacciones de base de datos

## Ejemplos de Uso

### Ejemplo 1: Kit con Selector de Tipo
```
Kit: "Kit de Reparación Completo"
1. Usuario selecciona "Elemento" → Ve solo elementos
2. Agrega: "Tornillo M4" × 10 = $50.00
3. Usuario selecciona "Componente" → Ve solo componentes
4. Agrega: "Kit Básico" × 2 = $50.00
Costo Total: $100.00
```

### Ejemplo 2: Componente (Sin Selector)
```
Componente: "Kit Básico"
- Solo ve elementos disponibles
- Agrega: "Tornillo M4" × 5 = $25.00
- Agrega: "Arandela" × 5 = $15.00
Costo Total: $40.00
```

### Ejemplo 3: Elemento (Sin Asignaciones)
```
Elemento: "Tornillo M4"
- No hay sección de asignación
- Costo manual: $5.00
```

## Consideraciones Técnicas

### Frontend
- **Reactividad**: Selector de tipo reactivo con Vue.js
- **Computed properties**: Filtrado dinámico según selección
- **Watchers**: Limpieza automática al cambiar tipo
- **UX**: Interfaz intuitiva con radio buttons
- **Validaciones**: Específicas por tipo de item

### Backend
- **API**: Mantiene compatibilidad con parámetro `type`
- **Validaciones**: Arrays de tipos permitidos por tipo de item
- **Cálculo**: Incluye costos de componentes para kits
- **Base de datos**: Transacciones para integridad

## Próximas Mejoras

1. **Validación de stock**: Verificar disponibilidad al asignar
2. **Historial de cambios**: Tracking de modificaciones en asignaciones
3. **Plantillas**: Crear plantillas reutilizables para kits comunes
4. **Importación masiva**: Cargar asignaciones desde archivos CSV/Excel
5. **Búsqueda avanzada**: Filtros por categoría, stock, precio, etc.
6. **Margen automático**: Calcular precio de venta basado en margen configurado
7. **Selector múltiple**: Permitir seleccionar varios tipos a la vez

## Testing

### Casos de Prueba
1. **Crear kit con selector**: Verificar cambio entre elementos y componentes
2. **Crear componente sin selector**: Verificar que solo ve elementos
3. **Crear elemento sin asignaciones**: Verificar campo manual
4. **Cambio de tipo en kits**: Verificar limpieza automática
5. **Cálculo de costos**: Verificar inclusión de componentes
6. **Búsqueda específica**: Verificar filtrado según tipo seleccionado

### Verificación de Datos
- Verificar que las relaciones se guarden correctamente
- Verificar que los costos se calculen incluyendo componentes
- Verificar que las validaciones de tipo funcionen
- Verificar que la interfaz muestre opciones correctas según tipo
- Verificar que el selector solo aparezca para kits
- Verificar que la limpieza automática funcione al cambiar tipo
