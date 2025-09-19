<template>
  <v-container fluid>
    <v-card class="pa-4 mb-4">
      <div class="text-h6 mb-2">Nueva tarea</div>
      <v-form @submit.prevent="createTask">
        <v-row>
          <v-col cols="12" md="4">
            <v-text-field v-model="form.titulo" label="Título" :rules="[r.required]" />
          </v-col>
          <v-col cols="12" md="4">
            <v-select v-model="form.usuario_id" :items="usuarios" item-title="nombre" item-value="id" label="Asignar a" :rules="[r.required]" />
          </v-col>
          <v-col cols="12" md="4">
            <v-select v-model="form.estado" :items="estados" label="Estado" />
          </v-col>
          <v-col cols="12">
            <v-textarea v-model="form.descripcion" label="Descripción" rows="2" auto-grow />
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field v-model="form.fecha_vencimiento" label="Fecha vencimiento" type="date" />
          </v-col>
          <v-col cols="12" class="d-flex gap-2">
            <v-btn type="submit" color="primary" :loading="loading">Crear tarea</v-btn>
            <v-btn color="secondary" @click="downloadCsv" :loading="downloading">DESCARGAR FORMULARIO</v-btn>
          </v-col>
        </v-row>
      </v-form>
    </v-card>

    <v-card class="pa-4">
      <div class="text-h6 mb-4">Tareas</div>
      <v-data-table :headers="headers" :items="tareas" :loading="loadingList">
        <template #item.usuario="{ item }">
          {{ item.usuario?.nombre || '—' }}
        </template>
        <template #item.fecha_vencimiento="{ item }">
          {{ formatDate(item.fecha_vencimiento) }}
        </template>
        <template #item.created_at="{ item }">
          {{ formatDate(item.created_at) }}
        </template>
      </v-data-table>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import api from '@/services/api'

const estados = ['pendiente', 'en_progreso', 'completada']

const headers = [
  { title: 'Título', value: 'titulo' },
  { title: 'Usuario', value: 'usuario' },
  { title: 'Estado', value: 'estado' },
  { title: 'Vence', value: 'fecha_vencimiento' },
  { title: 'Creado', value: 'created_at' },
]

const tareas = ref<any[]>([])
const usuarios = ref<any[]>([])
const loading = ref(false)
const downloading = ref(false)
const loadingList = ref(false)

const form = reactive({
  usuario_id: undefined as number | undefined,
  titulo: '',
  descripcion: '',
  estado: 'pendiente',
  fecha_vencimiento: '' as string | ''
})

const r = {
  required: (v: any) => !!v || 'Requerido',
}

const fetchUsuarios = async () => {
  const { data } = await api.get('/usuarios/listUsers')
  usuarios.value = data
}
const fetchTareas = async () => {
  loadingList.value = true
  try {
    const { data } = await api.get('/tareas')
    tareas.value = data
  } finally {
    loadingList.value = false
  }
}

const createTask = async () => {
  loading.value = true
  try {
    await api.post('/tareas', form)
    Object.assign(form, { titulo: '', descripcion: '', estado: 'pendiente', fecha_vencimiento: '' })
    await fetchTareas()
  } catch (e: any) {
    alert(e?.response?.data?.message || 'Error al crear tarea')
  } finally {
    loading.value = false
  }
}

const downloadCsv = async () => {
  downloading.value = true
  try {
    const res = await api.get('/tareas/export/pendientes', { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const a = document.createElement('a')
    a.href = url
    a.download = 'tareas_pendientes.csv'
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(url)
  } finally {
    downloading.value = false
  }
}

const formatDate = (s?: string) => {
  if (!s) return ''
  const d = new Date(s)
  if (isNaN(d.getTime())) return s
  return new Intl.DateTimeFormat('es-ES', { dateStyle: 'medium', timeStyle: 'short' }).format(d)
}

onMounted(async () => {
  await Promise.all([fetchUsuarios(), fetchTareas()])
})
</script>
