<template>
  <v-data-table
    :items="filtered"
    :headers="headers"
    :loading="loading"
    class="elevation-1"
  >
    <template #no-data>
      <div class="pa-6 text-center">No hay usuarios para mostrar.</div>
    </template>

    <!-- Formatear la columna de fecha de creación -->
    <template #item.created_at="{ item }">
      {{ formatDate(item.created_at) }}
    </template>
  </v-data-table>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'

type Usuario = { id:number; nombre:string; email:string; rol:'admin'|'usuario'; created_at?: string }

const props = defineProps<{ searchTerm?: string }>()

const items = ref<Usuario[]>([])
const loading = ref(false)

const headers = [
  { title: 'Nombre', value: 'nombre' },
  { title: 'Email',  value: 'email' },
  { title: 'Rol',    value: 'rol' },
  { title: 'Creado', value: 'created_at' },
]

// carga desde la API
const fetchUsers = async () => {
  loading.value = true
  try {
    const { data } = await api.get<Usuario[]>('/usuarios/listUsers')
    items.value = data
  } finally {
    loading.value = false
  }
}

onMounted(fetchUsers)

const filtered = computed(() => {
  const q = (props.searchTerm || '').toLowerCase().trim()
  if (!q) return items.value
  return items.value.filter(u =>
    u.nombre.toLowerCase().includes(q) ||
    u.email.toLowerCase().includes(q)  ||
    u.rol.toLowerCase().includes(q)
  )
})

const formatDate = (s?: string) => {
  if (!s) return ''
  const d = new Date(s)
  if (isNaN(d.getTime())) return s
  return new Intl.DateTimeFormat('es-ES', { dateStyle: 'medium', timeStyle: 'short' }).format(d)
}

// recargar si quieres al cambiar término (opcional)
/* watch(() => props.searchTerm, () => { ... }) */
</script>
