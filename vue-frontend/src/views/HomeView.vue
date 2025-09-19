<template>
  <v-container fluid>
    <v-row>
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

          <v-btn block color="error" variant="tonal" @click="logout">
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

type User = { id:number; nombre:string; email:string; rol:'admin'|'usuario' }

const router = useRouter()
const search = ref('')

const user = ref<User | null>(null)
onMounted(() => {
  const raw = localStorage.getItem('user')
  user.value = raw ? JSON.parse(raw) as User : null
})

const isAdmin = computed(() => user.value?.rol === 'admin')

const goAddUser = () => router.push('/usuarios/nuevo')
const goTasks = () => router.push('/tareas')

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>
