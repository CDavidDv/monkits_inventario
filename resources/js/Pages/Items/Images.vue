<template>
  <AppLayout title="Imágenes de Items">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Gestión de Imágenes
        </h2>
        <Link href="/dashboard" class="text-blue-600 hover:text-blue-800 text-sm">
          ← Volver
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Instrucción -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
          Sube las imágenes de tus productos aquí. El nombre del archivo (ej: <code class="bg-blue-100 px-1 rounded">tuerca 3mm.webp</code>)
          es el que debes escribir en el campo "Imágenes" al crear o editar un item.
        </div>

        <!-- Upload -->
        <div class="bg-white shadow-sm rounded-lg p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Subir imágenes</h3>

          <form @submit.prevent="submit" enctype="multipart/form-data">
            <!-- Drop zone -->
            <div
              class="border-2 border-dashed rounded-lg p-8 text-center transition-colors"
              :class="isDragging ? 'border-blue-400 bg-blue-50' : 'border-gray-300 hover:border-gray-400'"
              @dragover.prevent="isDragging = true"
              @dragleave="isDragging = false"
              @drop.prevent="handleDrop"
            >
              <svg class="mx-auto h-10 w-10 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>

              <div v-if="selectedFiles.length === 0">
                <p class="text-gray-600 mb-2">Arrastra imágenes aquí, o</p>
                <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                  Seleccionar archivos
                  <input
                    type="file"
                    multiple
                    accept=".jpg,.jpeg,.png,.gif,.webp"
                    class="hidden"
                    @change="handleFileChange"
                  />
                </label>
                <p class="mt-2 text-xs text-gray-400">JPG, PNG, GIF, WEBP — máx. 5MB por imagen, hasta 20 a la vez</p>
              </div>

              <div v-else>
                <p class="text-sm font-medium text-gray-700 mb-3">{{ selectedFiles.length }} archivo(s) seleccionado(s)</p>
                <div class="flex flex-wrap gap-2 justify-center mb-3">
                  <span v-for="file in selectedFiles" :key="file.name"
                    class="inline-flex items-center px-2 py-1 bg-gray-100 rounded text-xs text-gray-700">
                    <svg class="w-3 h-3 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ file.name }}
                    <span class="ml-1 text-gray-400">({{ formatSize(file.size) }})</span>
                  </span>
                </div>
                <button type="button" @click="clearFiles" class="text-sm text-red-500 hover:text-red-700 mr-3">
                  Limpiar selección
                </button>
              </div>
            </div>

            <InputError class="mt-2" :message="form.errors?.imagenes" />

            <div class="mt-4 flex justify-end">
              <PrimaryButton :disabled="selectedFiles.length === 0 || uploading">
                <svg v-if="uploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ uploading ? 'Subiendo...' : 'Subir imágenes' }}
              </PrimaryButton>
            </div>
          </form>
        </div>

        <!-- Resultado del upload -->
        <div v-if="$page.props.flash?.uploaded?.length > 0" class="bg-green-50 border border-green-200 rounded-lg p-4">
          <p class="font-semibold text-green-800 mb-2">{{ $page.props.flash.success }}</p>
          <p class="text-sm text-green-700 mb-1">Nombres para copiar en el campo "Imágenes" del item:</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="name in $page.props.flash.uploaded"
              :key="name"
              @click="copyToClipboard(name)"
              class="inline-flex items-center px-2 py-1 bg-white border border-green-300 rounded text-xs text-green-700 hover:bg-green-100 transition"
              :title="'Clic para copiar: ' + name"
            >
              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              {{ name }}
            </button>
          </div>
          <p class="text-xs text-green-600 mt-2">Haz clic en cada nombre para copiarlo al portapapeles.</p>
        </div>

        <!-- Galería de imágenes existentes -->
        <div class="bg-white shadow-sm rounded-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">
              Imágenes subidas
              <span class="text-sm font-normal text-gray-500 ml-2">({{ images.length }} archivos)</span>
            </h3>
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por nombre..."
              class="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <div v-if="filteredImages.length === 0" class="text-center py-12 text-gray-400">
            <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p>No hay imágenes subidas aún</p>
          </div>

          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div
              v-for="img in filteredImages"
              :key="img.name"
              class="group relative border border-gray-200 rounded-lg overflow-hidden bg-gray-50 hover:border-blue-300 transition"
            >
              <!-- Thumbnail -->
              <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                <img
                  :src="img.url"
                  :alt="img.name"
                  class="w-full h-full object-cover"
                  @error="$event.target.style.display='none'"
                />
              </div>

              <!-- Info + acciones -->
              <div class="p-2">
                <p class="text-xs text-gray-700 truncate font-medium" :title="img.name">{{ img.name }}</p>
                <p class="text-xs text-gray-400">{{ formatSize(img.size) }}</p>

                <div class="flex gap-1 mt-2">
                  <!-- Copiar nombre -->
                  <button
                    @click="copyToClipboard(img.name)"
                    class="flex-1 text-xs px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded transition"
                    title="Copiar nombre"
                  >
                    Copiar
                  </button>
                  <!-- Eliminar -->
                  <button
                    @click="confirmDelete(img)"
                    class="text-xs px-2 py-1 bg-red-50 hover:bg-red-100 text-red-500 rounded transition"
                    title="Eliminar imagen"
                  >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Toast de copiado -->
    <div v-if="copied" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-sm px-4 py-2 rounded-full shadow-lg z-50 transition">
      ✓ "{{ copied }}" copiado al portapapeles
    </div>

    <!-- Modal confirmar borrado -->
    <div v-if="imageToDelete" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
        <h4 class="font-semibold text-gray-900 mb-2">Eliminar imagen</h4>
        <p class="text-sm text-gray-600 mb-4">
          ¿Eliminar <strong>{{ imageToDelete.name }}</strong>? Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="imageToDelete = null" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
            Cancelar
          </button>
          <button @click="deleteImage" class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg">
            Eliminar
          </button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  images: Array
})

const isDragging  = ref(false)
const uploading   = ref(false)
const selectedFiles = ref([])
const search      = ref('')
const copied      = ref(null)
const imageToDelete = ref(null)
const form        = ref({})

const filteredImages = computed(() => {
  if (!search.value) return props.images
  const q = search.value.toLowerCase()
  return props.images.filter(img => img.name.toLowerCase().includes(q))
})

const handleFileChange = (e) => {
  selectedFiles.value = Array.from(e.target.files)
}

const handleDrop = (e) => {
  isDragging.value = false
  const files = Array.from(e.dataTransfer.files).filter(f =>
    /\.(jpg|jpeg|png|gif|webp)$/i.test(f.name)
  )
  selectedFiles.value = files
}

const clearFiles = () => {
  selectedFiles.value = []
}

const submit = () => {
  if (selectedFiles.value.length === 0) return

  const data = new FormData()
  selectedFiles.value.forEach(file => {
    data.append('imagenes[]', file)
  })

  uploading.value = true
  router.post(route('items.images.upload'), data, {
    forceFormData: true,
    onFinish: () => {
      uploading.value = false
      selectedFiles.value = []
    }
  })
}

const copyToClipboard = async (name) => {
  try {
    await navigator.clipboard.writeText(name)
    copied.value = name
    setTimeout(() => { copied.value = null }, 2500)
  } catch {
    // Fallback
    const el = document.createElement('textarea')
    el.value = name
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
    copied.value = name
    setTimeout(() => { copied.value = null }, 2500)
  }
}

const confirmDelete = (img) => {
  imageToDelete.value = img
}

const deleteImage = () => {
  if (!imageToDelete.value) return
  router.delete(route('items.images.destroy'), {
    data: { filename: imageToDelete.value.name },
    onFinish: () => { imageToDelete.value = null }
  })
}

const formatSize = (bytes) => {
  if (!bytes) return ''
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}
</script>
