<template>
  <v-container fluid>
    <!-- Loading state -->
    <div v-if="loading" class="text-center pa-4">
      <v-progress-circular indeterminate color="primary"></v-progress-circular>
      <div class="mt-2">Cargando...</div>
    </div>

    <!-- Contenido principal -->
    <v-row v-else>
      <!-- Sidebar -->
      <v-col cols="12" md="3">
        <v-card class="pa-4">
          <div class="text-subtitle-1 mb-2">Acciones</div>

          <!-- Agregar usuario: habilitado solo si es admin -->
          <v-btn
            block color="primary" class="mb-3"
            :disabled="!isAdmin"
            @click="goAddUser"
          >
            Agregar usuario
          </v-btn>

          <!-- Nuevo: Ir a tareas -->
          <v-btn block color="secondary" class="mb-3" @click="goTasks">
            Ver tareas
          </v-btn>

          <!-- Buscar (filtra en UsersList) -->
          <v-text-field
            v-model="search"
            label="Buscar usuarios"
            prepend-inner-icon="mdi-magnify"
            density="comfortable"
            clearable
            class="mb-4"
          />

          <v-btn 
            block 
            color="error" 
            variant="tonal" 
            :loading="loading"
            @click="logout"
          >
            Cerrar sesión
          </v-btn>

          <v-divider class="my-4" />

          <div class="text-caption">
            Sesión: <strong>{{ user?.nombre }}</strong> ({{ user?.rol }})
          </div>
        </v-card>
      </v-col>

      <!-- Contenido principal -->
      <v-col cols="12" md="9">
        <v-card class="pa-4">
          <div class="text-h6 mb-4">Usuarios</div>
          <!-- Componente que pinta la tabla -->
          <UsersList :search-term="search" />
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import UsersList from '@/views/UsersList.vue'
import api from '@/services/api'

type User = { id:number; nombre:string; email:string; rol:'admin'|'usuario' }

const router = useRouter()
const search = ref('')
const user = ref<User | null>(null)
const loading = ref(false)

// Verificar autenticación y obtener datos del usuario
onMounted(async () => {
  const token = localStorage.getItem('token')
  
  if (!token) {
    router.push('/login')
    return
  }

  try {
    loading.value = true
    // Verificar que el token sea válido obteniendo los datos del usuario
    const response = await api.get('/user')
    user.value = response.data
    
    // Actualizar localStorage con datos frescos del usuario
    localStorage.setItem('user', JSON.stringify(response.data))
  } catch (error) {
    console.error('Error verificando autenticación:', error)
    // El interceptor ya manejará el 401 y redirigirá al login
  } finally {
    loading.value = false
  }
})

const isAdmin = computed(() => user.value?.rol === 'admin')

const goAddUser = () => router.push('/usuarios/nuevo')
const goTasks = () => router.push('/tareas')

const logout = async () => {
  try {
    loading.value = true
    // Llamar al endpoint de logout para invalidar el token en el backend
    await api.post('/logout')
  } catch (error) {
    console.error('Error al hacer logout:', error)
  } finally {
    // Limpiar datos locales
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
    loading.value = false
  }
}
</script>
