<template>
  <AppLayout title="Detalles del Proveedor">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ supplier.name }}
        </h2>
        <div class="flex space-x-2">
          <Link
            
            :href="route('suppliers.edit', supplier.id)"
            class="bg-blue-500 hover:bg-blue-700 text-gray-700 font-bold py-2 px-4 rounded inline-flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar
          </Link>
          <Link :href="route('suppliers.index')" class="text-blue-600 hover:text-blue-800">
            ← Volver a la lista
          </Link>
        </div>
      </div>
    </template>

    <Container class="">
      <div class="">
        <!-- Información principal del proveedor -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
          <!-- Información básica -->
          <div class="lg:col-span-2 bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Información del Proveedor</h3>
                <span 
                  class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                  :class="getStatusBadgeClass()"
                >
                  {{ getStatusText() }}
                </span>
              </div>

              <div class="flex items-center mb-6">
                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center text-gray-700 font-bold text-2xl mr-4">
                  {{ supplier.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <h4 class="text-xl font-bold text-gray-900">{{ supplier.name }}</h4>
                  <p class="text-gray-500">{{ supplier.contact || 'Sin contacto asignado' }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Email</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    <a v-if="supplier.email" :href="`mailto:${supplier.email}`" class="text-blue-600 hover:text-blue-800">
                      {{ supplier.email }}
                    </a>
                    <span v-else class="text-gray-400">No especificado</span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    <a v-if="supplier.phone" :href="`tel:${supplier.phone}`" class="text-blue-600 hover:text-blue-800">
                      {{ supplier.phone }}
                    </a>
                    <span v-else class="text-gray-400">No especificado</span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">País</dt>
                  <dd class="mt-1">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                      {{ supplier.country }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Fechas</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    Creado: {{ formatDate(supplier.created_at) }}
                  </dd>
                </div>
                <div class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ supplier.address }}</dd>
                </div>
              </div>
            </div>
          </div>

          <!-- Estadísticas -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Estadísticas</h3>
              
              <div class="space-y-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                  <div class="text-3xl font-bold text-blue-600">
                    {{ stats?.total_elements }}
                  </div>
                  <div class="text-sm text-blue-700">Elementos Suministrados</div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-lg font-bold text-green-600">
                      ${{ stats?.average_purchase_price?.toFixed(2) || '0.00' }}
                    </div>
                    <div class="text-green-700">Precio Prom. Compra</div>
                  </div>
                  <div class="text-center p-3 bg-purple-50 rounded-lg">
                    <div class="text-lg font-bold text-purple-600">
                      ${{ stats?.average_selling_price?.toFixed(2) || '0.00' }}
                    </div>
                    <div class="text-purple-700">Precio Prom. Venta</div>
                  </div>
                </div>

                <div class="text-center p-3 bg-yellow-50 rounded-lg">
                  <div class="text-lg font-bold text-yellow-600">
                    ${{ stats?.total_inventory_value?.toFixed(2) || '0.00' }}
                  </div>
                  <div class="text-yellow-700">Valor Total Inventario</div>
                </div>

                <!-- Botones de acción -->
                <div class="pt-4 border-t space-y-2">
                  <Link
                    :href="route('element-prices.create', { supplier_id: supplier.id })"
                    class="w-full bg-green-500 hover:bg-green-700 text-gray-700 font-bold py-2 px-4 rounded text-center block"
                  >
                    Agregar Precios
                  </Link>
                  <button
                    v-if="supplier.email"
                    @click="sendEmail"
                    class="w-full bg-blue-500 hover:bg-blue-700 text-gray-700 font-bold py-2 px-4 rounded"
                  >
                    Enviar Email
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Elementos y precios -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Elementos y Precios</h3>
              <div class="flex space-x-2">
                <select
                  v-model="sortBy"
                  @change="sortElements"
                  class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="name">Nombre</option>
                  <option value="category">Categoría</option>
                  <option value="purchase_price">Precio Compra</option>
                  <option value="selling_price">Precio Venta</option>
                  <option value="margin">Margen</option>
                </select>
                <Link
                  :href="route('element-prices.create', { supplier_id: supplier.id })"
                  class="bg-green-500 hover:bg-green-700 text-gray-700 font-bold py-2 px-4 rounded text-sm"
                >
                  Agregar Precio
                </Link>
              </div>
            </div>
            
            <div v-if="sortedElements.length === 0" class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
              <p>Este proveedor aún no tiene elementos con precios asignados</p>
              <p class="text-sm">Agrega precios para elementos específicos para empezar</p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Elemento
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Stock Actual
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Precio Compra
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Precio Venta
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Margen
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Valor Stock
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="elementPrice in sortedElements" :key="elementPrice.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div>
                          <div class="text-sm font-medium text-gray-900">
                            <Link 
                              :href="route('elements.show', elementPrice.element.id)"
                              class="text-blue-600 hover:text-blue-800"
                            >
                              {{ elementPrice.element.name }}
                            </Link>
                          </div>
                          <div class="text-sm text-gray-500">{{ elementPrice.element.category }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ elementPrice.element.current_stock }} {{ elementPrice.element.unit }}
                      </div>
                      <div class="text-sm" :class="getStockStatusClass(elementPrice.element)">
                        {{ getStockStatus(elementPrice.element) }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <div class="font-medium text-green-600">
                        ${{ elementPrice.purchase_price?.toFixed(2) || 'N/A' }}
                      </div>
                      <div class="text-xs text-gray-500">{{ elementPrice.currency }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <div class="font-medium text-blue-600">
                        ${{ elementPrice.selling_price?.toFixed(2) || 'N/A' }}
                      </div>
                      <div class="text-xs text-gray-500">{{ elementPrice.currency }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div v-if="elementPrice.purchase_price && elementPrice.selling_price" class="text-sm">
                        <div 
                          class="font-medium"
                          :class="getMarginClass(calculateMargin(elementPrice))"
                        >
                          {{ calculateMargin(elementPrice).toFixed(1) }}%
                        </div>
                        <div class="text-xs text-gray-500">
                          ${{ (elementPrice.selling_price - elementPrice.purchase_price).toFixed(2) }}
                        </div>
                      </div>
                      <div v-else class="text-sm text-gray-400">N/A</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <div v-if="elementPrice.purchase_price" class="font-medium text-purple-600">
                        ${{ (elementPrice.element.current_stock * elementPrice.purchase_price).toFixed(2) }}
                      </div>
                      <div v-else class="text-gray-400">N/A</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <Link
                          :href="route('element-prices.edit', elementPrice.id)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          Editar
                        </Link>
                        <button
                          @click="confirmDeletePrice(elementPrice)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Eliminar
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Historial de comunicaciones -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Historial de Comunicaciones</h3>
            
            <div class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.13 8.13 0 01-2.939-.547l-3.061 1.020a1 1 0 01-1.267-1.267l1.020-3.061A8.13 8.13 0 013 12a8 8 0 018-8 8 8 0 018 8z"></path>
              </svg>
              <p>No hay comunicaciones registradas</p>
              <p class="text-sm">Las comunicaciones futuras aparecerán aquí</p>
            </div>
          </div>
        </div>
      </div>
    </Container>

    <!-- Modal de confirmación para eliminar precio -->
    <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
      <template #title>
        Eliminar Precio
      </template>
      <template #content>
        ¿Estás seguro de que quieres eliminar el precio de "{{ priceToDelete?.element?.name }}" 
        para este proveedor? Esta acción no se puede deshacer.
      </template>
      <template #footer>
        <SecondaryButton @click="showDeleteModal = false">
          Cancelar
        </SecondaryButton>
        <DangerButton class="ml-3" @click="deletePrice" :disabled="processing">
          Eliminar
        </DangerButton>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import Container from '@/Components/Container.vue'

const props = defineProps({
  supplier: Object,
  stats: Object
})

const sortBy = ref('name')
const showDeleteModal = ref(false)
const priceToDelete = ref(null)
const processing = ref(false)

const sortedElements = computed(() => {
  if (!props.supplier.element_prices) return []
  
  return [...props.supplier.element_prices].sort((a, b) => {
    switch (sortBy.value) {
      case 'name':
        return a.element.name.localeCompare(b.element.name)
      case 'category':
        return a.element.category.localeCompare(b.element.category)
      case 'purchase_price':
        return (b.purchase_price || 0) - (a.purchase_price || 0)
      case 'selling_price':
        return (b.selling_price || 0) - (a.selling_price || 0)
      case 'margin':
        return calculateMargin(b) - calculateMargin(a)
      default:
        return 0
    }
  })
})

const getStatusText = () => {
  return props.supplier.element_prices && props.supplier.element_prices.length > 0 ? 'Activo' : 'Inactivo'
}

const getStatusBadgeClass = () => {
  return props.supplier.element_prices && props.supplier.element_prices.length > 0
    ? 'bg-green-100 text-green-800'
    : 'bg-gray-100 text-gray-800'
}

const getStockStatus = (element) => {
  if (element.current_stock <= 0) return 'Agotado'
  if (element.current_stock <= element.min_stock) return 'Stock Bajo'
  return 'Normal'
}

const getStockStatusClass = (element) => {
  if (element.current_stock <= 0) return 'text-red-600'
  if (element.current_stock <= element.min_stock) return 'text-yellow-600'
  return 'text-green-600'
}

const calculateMargin = (elementPrice) => {
  if (!elementPrice.purchase_price || !elementPrice.selling_price) return 0
  return ((elementPrice.selling_price - elementPrice.purchase_price) / elementPrice.purchase_price) * 100
}

const getMarginClass = (margin) => {
  if (margin >= 30) return 'text-green-600'
  if (margin >= 15) return 'text-blue-600'
  if (margin >= 5) return 'text-yellow-600'
  return 'text-red-600'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const sortElements = () => {
  // El computed se actualiza automáticamente
}

const sendEmail = () => {
  if (props.supplier.email) {
    window.location.href = `mailto:${props.supplier.email}?subject=Consulta de inventario`
  }
}

const confirmDeletePrice = (elementPrice) => {
  priceToDelete.value = elementPrice
  showDeleteModal.value = true
}

const deletePrice = () => {
  if (!priceToDelete.value) return
  
  processing.value = true
  
  router.delete(route('element-prices.destroy', priceToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      priceToDelete.value = null
    },
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>
