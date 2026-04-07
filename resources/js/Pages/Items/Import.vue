<template>
  <AppLayout title="Importación / Exportación de Items">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Importación / Exportación de Items
        </h2>
        <div class="flex items-center space-x-3">
          <a
            :href="route('items.export')"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Exportar todo (.xlsx)
          </a>
          <Link href="/dashboard" class="text-blue-600 hover:text-blue-800 text-sm">
            ← Volver al dashboard
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
          <h3 class="text-lg font-semibold text-blue-900 mb-3">Instrucciones</h3>
          <ol class="list-decimal list-inside space-y-2 text-sm text-blue-800">
            <li>Descarga la plantilla Excel con el botón de abajo.</li>
            <li>
              Columnas requeridas: <strong>Tipo</strong> y <strong>Nombre</strong>.
              Valores válidos para Tipo:
              <code class="bg-blue-100 px-1 rounded">individual</code>,
              <code class="bg-blue-100 px-1 rounded">componente</code>,
              <code class="bg-blue-100 px-1 rounded">kit</code>.
            </li>
            <li>
              <strong>ID (modelo):</strong> Si ya existe un item con ese modelo,
              se actualizan solo los stocks. Si es nuevo, se crea el item completo.
            </li>
            <li>
              <strong>Categoría:</strong> Escribe el nombre de la categoría.
              Se crea automáticamente si no existe.
            </li>
            <li>
              <strong>Imagen:</strong> Deja en blanco para autodetectar
              <code class="bg-blue-100 px-1 rounded">public/images/items/{modelo}.{ext}</code>,
              o escribe el nombre de archivo exacto.
            </li>
            <li>
              <strong>Ítems Asignados</strong> (solo para kits/componentes):
              Formato <code class="bg-blue-100 px-1 rounded">cantidad:modelo,cantidad:modelo</code>
              (ej: <code class="bg-blue-100 px-1 rounded">5:MOD001,3:MOD002</code>).
            </li>
            <li>Sube el archivo Excel o CSV y haz clic en Importar.</li>
          </ol>
        </div>

        <!-- Step 1: Download template -->
        <div class="bg-white shadow-sm rounded-lg p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Paso 1: Descargar plantilla</h3>
          <a
            :href="route('items.import.template')"
            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md transition"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Descargar plantilla Excel (.xlsx)
          </a>
        </div>

        <!-- Step 2: Upload -->
        <div class="bg-white shadow-sm rounded-lg p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Paso 2: Subir archivo</h3>

          <form @submit.prevent="submit" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_token" :value="csrfToken" />
            <div
              class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition"
              :class="{ 'border-blue-400 bg-blue-50': isDragging }"
              @dragover.prevent="isDragging = true"
              @dragleave="isDragging = false"
              @drop.prevent="handleDrop"
            >
              <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>

              <div v-if="!form.archivo">
                <p class="text-gray-600 mb-2">Arrastra tu archivo aquí, o</p>
                <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                  </svg>
                  Seleccionar archivo
                  <input
                    type="file"
                    accept=".xlsx,.xls,.csv"
                    class="hidden"
                    @change="handleFileChange"
                  />
                </label>
                <p class="mt-2 text-xs text-gray-500">Excel (.xlsx, .xls) o CSV — máximo 10MB</p>
              </div>

              <div v-else class="flex items-center justify-center space-x-4">
                <div class="flex items-center space-x-2">
                  <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div class="text-left">
                    <p class="text-sm font-medium text-gray-900">{{ form.archivo.name }}</p>
                    <p class="text-xs text-gray-500">{{ formatFileSize(form.archivo.size) }}</p>
                  </div>
                </div>
                <button type="button" @click="removeFile" class="text-red-500 hover:text-red-700 text-sm">
                  Quitar
                </button>
              </div>
            </div>

            <InputError class="mt-2" :message="form.errors.archivo" />

            <div class="mt-6 flex justify-end">
              <PrimaryButton
                :disabled="!form.archivo || form.processing"
                :class="{ 'opacity-50 cursor-not-allowed': !form.archivo || form.processing }"
              >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                {{ form.processing ? 'Importando...' : 'Importar Items' }}
              </PrimaryButton>
            </div>
          </form>
        </div>

        <!-- Import result -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 rounded-lg p-6">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
              <h4 class="font-semibold text-green-800">{{ $page.props.flash.success }}</h4>
              <div class="flex space-x-6 text-sm text-green-700 mt-1">
                <p>Items creados: <strong>{{ $page.props.flash.imported_count ?? 0 }}</strong></p>
                <p>Items actualizados: <strong>{{ $page.props.flash.updated_count ?? 0 }}</strong></p>
              </div>

              <div v-if="$page.props.flash.import_errors?.length > 0" class="mt-4">
                <h5 class="font-medium text-orange-800 mb-2">
                  Advertencias ({{ $page.props.flash.import_errors.length }} filas):
                </h5>
                <ul class="text-sm text-orange-700 space-y-1 max-h-40 overflow-y-auto">
                  <li v-for="err in $page.props.flash.import_errors" :key="err" class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>{{ err }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Clear all success -->
        <div v-if="$page.props.flash?.clear_success" class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3">
          <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-green-800 font-medium">{{ $page.props.flash.clear_success }}</p>
        </div>

        <!-- Zona de peligro: Vaciar base de datos -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
          <h3 class="text-lg font-semibold text-red-800 mb-2">Zona de peligro</h3>
          <p class="text-sm text-red-700 mb-4">
            Esta acción eliminará <strong>todos los items</strong>, sus relaciones y todos los movimientos de inventario. No se puede deshacer.
          </p>
          <button
            v-if="!confirmingClear"
            type="button"
            @click="confirmingClear = true"
            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition text-sm"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Vaciar base de datos de items
          </button>
          <div v-else class="flex items-center gap-3">
            <p class="text-sm font-semibold text-red-800">¿Confirmas que quieres eliminar TODOS los items?</p>
            <button
              type="button"
              @click="clearAll"
              :disabled="clearForm.processing"
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md text-sm transition"
            >
              Sí, vaciar todo
            </button>
            <button
              type="button"
              @click="confirmingClear = false"
              class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-md text-sm transition"
            >
              Cancelar
            </button>
          </div>
        </div>

        <!-- Preview (Excel files won't show preview — only CSV) -->
        <div v-if="preview.length > 0" class="bg-white shadow-sm rounded-lg p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">
            Vista previa CSV (primeras {{ preview.length }} filas)
          </h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-xs border-collapse">
              <thead>
                <tr class="bg-gray-100">
                  <th
                    v-for="col in previewHeaders"
                    :key="col"
                    class="border border-gray-300 px-3 py-2 text-left font-semibold text-gray-700"
                  >{{ col }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, i) in preview" :key="i" class="hover:bg-gray-50">
                  <td
                    v-for="col in previewHeaders"
                    :key="col"
                    class="border border-gray-300 px-3 py-1 text-gray-600"
                  >{{ row[col] || '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
  categories: Array
})

const page = usePage()

const isDragging      = ref(false)
const preview         = ref([])
const previewHeaders  = ref([])

// Obtener token CSRF del meta tag o de las propiedades de Inertia
const csrfToken = computed(() => {
  const metaTag = document.querySelector('meta[name="csrf-token"]')
  if (metaTag) {
    return metaTag.getAttribute('content')
  }
  // Alternativa: desde Inertia props si está disponible
  return page.props.csrf_token || ''
})

const form = useForm({ archivo: null })
const clearForm = useForm({})
const confirmingClear = ref(false)

const clearAll = () => {
  clearForm.post(route('items.clear-all'), {
    onSuccess: () => { confirmingClear.value = false }
  })
}

const ACCEPTED_TYPES = ['.xlsx', '.xls', '.csv']

const isAcceptedFile = (file) =>
  ACCEPTED_TYPES.some(ext => file.name.toLowerCase().endsWith(ext))

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.archivo = file
    if (file.name.toLowerCase().endsWith('.csv')) {
      readPreview(file)
    } else {
      // Clear any previous CSV preview for Excel files
      preview.value = []
      previewHeaders.value = []
    }
  }
}

const handleDrop = (e) => {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  if (file && isAcceptedFile(file)) {
    form.archivo = file
    if (file.name.toLowerCase().endsWith('.csv')) {
      readPreview(file)
    } else {
      preview.value = []
      previewHeaders.value = []
    }
  }
}

const removeFile = () => {
  form.archivo = null
  preview.value = []
  previewHeaders.value = []
}

const readPreview = (file) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    const text = e.target.result
    const lines = text.split('\n').filter(l => l.trim())
    if (lines.length < 2) return

    const headers = lines[0].split(',').map(h => h.trim().replace(/"/g, ''))
    previewHeaders.value = headers

    const rows = []
    for (let i = 1; i < Math.min(6, lines.length); i++) {
      const values = lines[i].split(',').map(v => v.trim().replace(/"/g, ''))
      const row = {}
      headers.forEach((h, idx) => { row[h] = values[idx] || '' })
      rows.push(row)
    }
    preview.value = rows
  }
  reader.readAsText(file)
}

const formatFileSize = (bytes) => {
  if (bytes < 1024)          return bytes + ' B'
  if (bytes < 1024 * 1024)   return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

const submit = () => {
  form.post(route('items.import.store'), {
    forceFormData: true,
    onSuccess: () => { removeFile() }
  })
}
</script>
