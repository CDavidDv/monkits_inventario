<template>
  <AppLayout :title="`${item.name} - Detalles`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link :href="route('inventario.index')" 
                class="text-gray-500 hover:text-gray-700 transition-colors">
            <ArrowLeft class="w-6 h-6" />
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              {{ item.name }}
            </h2>
            <p class="text-sm text-gray-600">{{ getItemTypeLabel(item.type) }} - {{ item.category?.name || 'Sin categoría' }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span :class="getItemStatusClass(item)" 
                class="px-3 py-1 rounded-full text-sm font-medium">
            {{ item.active ? 'Activo' : 'Inactivo' }}
          </span>
          <span :class="getStockStatusClass(item)" 
                class="px-3 py-1 rounded-full text-sm font-medium">
            {{ getStockStatusText(item) }}
          </span>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Estadísticas Generales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white overflow-hidden shadow-xl rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Package class="w-8 h-8 text-blue-600" />
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-500">Stock Actual</div>
                  <div class="text-2xl font-bold text-gray-900">{{ parseFloat(item.current_stock).toFixed(2) }} {{ item.unit }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <TrendingUp class="w-8 h-8 text-green-600" />
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-500">Total Entradas</div>
                  <div class="text-2xl font-bold text-green-700">+{{ stats.total_entries || 0 }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <TrendingDown class="w-8 h-8 text-red-600" />
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-500">Total Salidas</div>
                  <div class="text-2xl font-bold text-red-700">-{{ getQuantityDisplay(stats.total_exits, 2) || 0 }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Activity class="w-8 h-8 text-purple-600" />
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-500">Total Movimientos</div>
                  <div class="text-2xl font-bold text-purple-700">{{ stats.total_movements || 0 }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Información Detallada -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Información del Item -->
          <div class="bg-white shadow-xl rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Información General</h3>
            </div>
            <div class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                <p class="mt-1 text-sm text-gray-900">{{ item.name }}</p>
              </div>
              
              <div v-if="item.description">
                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <p class="mt-1 text-sm text-gray-900">{{ item.description }}</p>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Unidad</label>
                  <p class="mt-1 text-sm text-gray-900">{{ item.unit }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Tipo</label>
                  <p class="mt-1 text-sm text-gray-900">{{ getItemTypeLabel(item.type) }}</p>
                </div>
              </div>
              
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Stock Actual</label>
                  <p class="mt-1 text-sm font-bold" :class="getStockColor(item)">
                    {{ item.current_stock }}
                  </p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Mínimo</label>
                  <p class="mt-1 text-sm text-gray-900">{{ item.min_stock }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Máximo</label>
                  <p class="mt-1 text-sm text-gray-900">{{ item.max_stock }}</p>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Costo de Compra</label>
                  <p class="mt-1 text-sm text-gray-900">${{ item.purchase_cost || '0.00' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Precio de Venta</label>
                  <p class="mt-1 text-sm text-gray-900">${{ item.sale_price || '0.00' }}</p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Ubicación</label>
                  <p class="mt-1 text-sm text-gray-900">{{ item.location || 'No especificada' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Código de Barras</label>
                  <p class="mt-1 text-sm text-gray-900 font-mono">{{ item.codigo_barras || '-' }}</p>
                </div>
              </div>

              <!-- Botón de editar -->
              <div class="mt-6">
                <button @click="openEditModal"
                  class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                  Editar Información
                </button>
              </div>
            </div>
          </div>

          <!-- Imágenes del Item -->
          <div v-if="getItemImages.length > 0" class="bg-white shadow-xl rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Imágenes</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 gap-3">
                <div v-for="(image, index) in getItemImages" :key="index"
                     class="rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow cursor-pointer"
                     @click="openImageViewer(index)">
                  <img :src="getImageUrl(image)"
                       :alt="`Imagen ${index + 1}`"
                       class="w-full h-48 object-cover hover:opacity-75 transition-opacity" />
                  <div class="bg-gray-50 px-3 py-2 text-xs text-gray-600 truncate">{{ image }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Elementos Asignados (para kits y componentes) -->
          <div v-if="assignedElements && assignedElements.length > 0" class="bg-white shadow-xl rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Elementos Asignados</h3>
            </div>
            <div class="p-6">
              <div class="space-y-3">
                <div v-for="element in assignedElements" :key="element.id" 
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <h4 class="text-sm font-medium text-gray-900">{{ element.name }}</h4>
                      <span class="text-xs text-gray-500">({{ element.type }})</span>
                    </div>
                    <p class="text-xs text-gray-500">{{ element.categoryName }}</p>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      {{ element.quantity }} {{ element.unit }}
                    </div>
                    <div class="text-xs text-gray-500">
                      Stock: {{ element.current_stock }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Usado en (para elementos) -->
          <div v-if="usedInItems && usedInItems.length > 0" class="bg-white shadow-xl rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Usado en</h3>
            </div>
            <div class="p-6">
              <div class="space-y-3">
                <div v-for="parentItem in usedInItems" :key="parentItem.id" 
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <h4 class="text-sm font-medium text-gray-900">{{ parentItem.name }}</h4>
                      <span class="text-xs text-gray-500">({{ parentItem.type }})</span>
                    </div>
                    <p class="text-xs text-gray-500">{{ parentItem.categoryName }}</p>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      {{ parentItem.quantity }} {{ item.unit }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Historial de Movimientos -->
        <div class="bg-white shadow-xl rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Historial de Movimientos</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Fecha
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipo
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cantidad
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Usuario
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Observaciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="movement in movements.data" :key="movement.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(movement.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getMovementTypeClass(movement.type)" 
                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getMovementTypeText(movement.type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm" :class="getQuantityColor(movement.type, movement.quantity)">
                    {{ getQuantityDisplay(movement.type, movement.quantity) }} {{ item.unit }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ movement.user?.name || 'Sistema' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ movement.notes || '-' }}
                  </td>
                </tr>
                <tr v-if="movements.data.length === 0">
                  <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    No hay movimientos registrados para este item
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div v-if="movements.links && movements.links.length > 3" class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Mostrando {{ movements.from }} a {{ movements.to }} de {{ movements.total }} resultados
              </div>
              <div class="flex space-x-1">
                <Link v-for="link in movements.links" :key="link.label" 
                      :href="link.url" :class="[
                        'px-3 py-2 text-sm rounded-md',
                        link.active 
                          ? 'bg-blue-500 text-white' 
                          : link.url 
                            ? 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' 
                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      ]" v-html="link.label" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 sticky top-0 bg-white">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Editar Información</h3>
            <button @click="closeEditModal"
              class="text-gray-400 hover:text-gray-600 transition-colors">
              <X class="w-6 h-6" />
            </button>
          </div>
        </div>

        <form @submit.prevent="submitEdit" class="p-6 space-y-6">
          <!-- Nombre -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
            <input v-model="editFormData.name" type="text" required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
          </div>

          <!-- Descripción -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea v-model="editFormData.description" rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
          </div>

          <!-- Ubicación -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
            <input v-model="editFormData.location" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Ej: Estante A1, Almacén B" />
          </div>

          <!-- Código de Barras -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Código de Barras</label>
            <input v-model="editFormData.codigo_barras" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Ej: 7501234567890" />
          </div>

          <!-- Imágenes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Imágenes (nombres de archivos)</label>
            <input v-model="editFormData.imagenes" type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Ej: foto1.jpg, foto2.png" />
            <p class="mt-1 text-xs text-gray-500">
              Ingresa los nombres de las imágenes separados por comas
            </p>
            <div v-if="editImageList.length > 0" class="mt-2 flex flex-wrap gap-2">
              <span v-for="img in editImageList" :key="img"
                class="inline-flex items-center px-2 py-1 bg-blue-50 border border-blue-200 rounded text-xs text-blue-700">
                {{ img }}
              </span>
            </div>
          </div>

          <!-- Botones -->
          <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
            <button @click="closeEditModal" type="button"
              class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
              Cancelar
            </button>
            <button :disabled="editFormLoading" type="submit"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg transition-colors flex items-center gap-2">
              <svg v-if="editFormLoading" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Image Viewer Modal -->
    <div v-if="showImageViewer" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4">
      <div class="max-w-4xl max-h-[90vh] relative flex flex-col">
        <!-- Close button -->
        <button @click="closeImageViewer"
            class="absolute -top-12 right-0 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100 transition-colors z-10">
          <X class="w-6 h-6 text-gray-600" />
        </button>

        <!-- Main Image -->
        <div class="flex-1 flex items-center justify-center overflow-hidden rounded-lg">
          <img :src="getImageUrl(currentViewerImage)"
               :alt="currentViewerImage"
               class="max-w-full max-h-[80vh] object-contain" />
        </div>

        <!-- Image Info and Navigation -->
        <div class="mt-4 bg-gray-900 bg-opacity-75 text-white rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <p class="text-sm font-medium">{{ currentViewerImageIndex + 1 }} / {{ getItemImages.length }}</p>
              <p class="text-xs text-gray-300 truncate">{{ currentViewerImage }}</p>
            </div>
            <div v-if="getItemImages.length > 1" class="flex gap-2">
              <button @click="previousImage"
                  :disabled="currentViewerImageIndex === 0"
                  class="px-3 py-2 bg-gray-700 hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed rounded transition-colors text-sm">
                Anterior
              </button>
              <button @click="nextImage"
                  :disabled="currentViewerImageIndex === getItemImages.length - 1"
                  class="px-3 py-2 bg-gray-700 hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed rounded transition-colors text-sm">
                Siguiente
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import axios from 'axios'
import {
  ArrowLeft,
  Package,
  TrendingUp,
  TrendingDown,
  Activity,
  X
} from 'lucide-vue-next'

const props = defineProps({
  item: Object,
  movements: Object,
  assignedElements: Array,
  usedInItems: Array,
  stats: Object
})

// Image viewer state
const showImageViewer = ref(false)
const currentViewerImageIndex = ref(0)

// Edit modal state
const showEditModal = ref(false)
const editFormData = ref({
  name: props.item.name,
  description: props.item.description || '',
  location: props.item.location || '',
  codigo_barras: props.item.codigo_barras || '',
  imagenes: props.item.imagenes || ''
})
const editFormLoading = ref(false)

// Computed properties for images
const getItemImages = computed(() => {
  if (!props.item.imagenes) return []
  return props.item.imagenes.split(',').map(img => img.trim()).filter(img => img)
})

const currentViewerImage = computed(() => {
  const images = getItemImages.value
  if (images.length === 0) return ''
  return images[currentViewerImageIndex.value] || images[0]
})

const editImageList = computed(() => {
  if (!editFormData.value.imagenes) return []
  return editFormData.value.imagenes.split(',').map(s => s.trim()).filter(s => s.length > 0)
})

// Helper functions
const getItemTypeLabel = (type) => {
  const labels = {
    element: 'Elemento',
    component: 'Componente',
    kit: 'Kit'
  }
  return labels[type] || type
}

const getItemStatusClass = (item) => {
  return item.active
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800'
}

const getStockStatusClass = (item) => {
  const current = parseFloat(item.current_stock)
  const min = parseFloat(item.min_stock)
  const max = parseFloat(item.max_stock)

  if (current < min) {
    return 'bg-red-100 text-red-800'
  } else if (current === min) {
    return 'bg-yellow-100 text-yellow-800'
  } else if (current > max) {
    return 'bg-blue-100 text-blue-800'
  } else {
    return 'bg-green-100 text-green-800'
  }
}

const getStockStatusText = (item) => {
  const current = parseFloat(item.current_stock)
  const min = parseFloat(item.min_stock)
  const max = parseFloat(item.max_stock)

  if (current < min) {
    return 'Bajo Stock'
  } else if (current === min) {
    return 'En el Mínimo'
  } else if (current > max) {
    return 'Sobre Stock'
  } else {
    return 'Stock Normal'
  }
}

const getStockColor = (item) => {
  const current = parseFloat(item.current_stock)
  const min = parseFloat(item.min_stock)
  const max = parseFloat(item.max_stock)

  if (current < min) {
    return 'text-red-600'
  } else if (current === min) {
    return 'text-yellow-600'
  } else if (current > max) {
    return 'text-blue-600'
  } else {
    return 'text-green-600'
  }
}

const getMovementTypeClass = (type) => {
  const classes = {
    entry: 'bg-green-100 text-green-800',
    exit: 'bg-red-100 text-red-800',
    out: 'bg-red-100 text-red-800',
    adjustment: 'bg-yellow-100 text-yellow-800',
    production: 'bg-blue-100 text-blue-800',
    damage: 'bg-red-100 text-red-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getMovementTypeText = (type) => {
  const texts = {
    entry: 'Entrada',
    exit: 'Salida',
    out: 'Salida',
    adjustment: 'Ajuste',
    production: 'Producción',
    damage: 'Daño/Pérdida'
  }
  return texts[type] || type
}

const getQuantityColor = (type, quantity) => {
  if (type === 'entry' || (type === 'adjustment' && quantity > 0)) {
    return 'text-green-600 font-medium'
  } else if (type === 'exit' || type === 'damage' || (type === 'adjustment' && quantity < 0)) {
    return 'text-red-600 font-medium'
  }
  return 'text-gray-900'
}

const getQuantityDisplay = (type, quantity) => {
  const formatNumber = (num) => parseFloat(num).toFixed(2)
  
  if (type === 'entry' || (type === 'adjustment' && quantity > 0)) {
    return `+${formatNumber(quantity)}`
  } else if (type === 'exit' || type === 'damage') {
    return `-${formatNumber(Math.abs(quantity))}`
  }
  return formatNumber(quantity)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Image viewer functions
const getImageUrl = (filename) => {
  if (!filename) return ''
  // Construir URL absoluta considerando que el app puede estar en un subdirectorio
  const baseUrl = import.meta.env.BASE_URL || '/'
  return `${baseUrl}images/items/${filename}`.replace(/\/+/g, '/')
}

const openImageViewer = (index) => {
  currentViewerImageIndex.value = index
  showImageViewer.value = true
}

const closeImageViewer = () => {
  showImageViewer.value = false
  currentViewerImageIndex.value = 0
}

const previousImage = () => {
  if (currentViewerImageIndex.value > 0) {
    currentViewerImageIndex.value--
  }
}

const nextImage = () => {
  const images = getItemImages.value
  if (currentViewerImageIndex.value < images.length - 1) {
    currentViewerImageIndex.value++
  }
}

// Edit modal functions
const openEditModal = () => {
  // Reinicializar los datos actuales
  editFormData.value = {
    name: props.item.name,
    description: props.item.description || '',
    location: props.item.location || '',
    codigo_barras: props.item.codigo_barras || '',
    imagenes: props.item.imagenes || ''
  }
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
}

const submitEdit = async () => {
  editFormLoading.value = true
  try {
    const response = await axios.put(route('items.update', props.item.id), editFormData.value)
    if (response.status === 200) {
      closeEditModal()
      // Recargar la página para mostrar los cambios
      window.location.reload()
    }
  } catch (error) {
    console.error('Error updating item:', error.response?.data)
    alert('Error al guardar los cambios: ' + (error.response?.data?.message || 'Error desconocido'))
  } finally {
    editFormLoading.value = false
  }
}
</script>

