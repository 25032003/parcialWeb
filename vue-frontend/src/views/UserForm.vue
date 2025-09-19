<template>
  <v-container class="py-6" style="max-width: 720px;">
    <v-card>
      <v-card-title>Nuevo usuario</v-card-title>
      <v-card-text>
        <v-form @submit.prevent="onSubmit" ref="formRef">
          <v-text-field v-model="form.nombre" label="Nombre" :rules="[r.required]" />
          <v-text-field v-model="form.email" label="Email" type="email" :rules="[r.required, r.email]" />
          <v-text-field v-model="form.password" label="Contraseña" type="password" :rules="[r.required, r.min6]" />
          <v-select v-model="form.rol" :items="roles" label="Rol" :rules="[r.required]" />

          <div class="d-flex gap-2 mt-4">
            <v-btn type="submit" color="primary" :loading="loading">Guardar</v-btn>
            <v-btn variant="text" @click="goBack">Cancelar</v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

type Rol = 'admin' | 'usuario'

const router = useRouter()
const loading = ref(false)
const formRef = ref()
const roles: Rol[] = ['admin', 'usuario']

const form = reactive({
  nombre: '',
  email: '',
  password: '',
  rol: 'usuario' as Rol,
})

const r = {
  required: (v: any) => !!v || 'Requerido',
  email: (v: string) => /.+@.+\..+/.test(v) || 'Email inválido',
  min6: (v: string) => (v?.length ?? 0) >= 6 || 'Mínimo 6 caracteres',
}

const onSubmit = async () => {
  loading.value = true
  try {
    await api.post('/usuarios/addUser', form)
    router.push('/usuarios')
  } catch (e: any) {
    alert(e?.response?.data?.message || 'Error al crear usuario')
  } finally {
    loading.value = false
  }
}

const goBack = () => router.back()
</script>