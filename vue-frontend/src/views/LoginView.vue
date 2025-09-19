<template>
    <v-container class="d-flex align-center justify-center fill-height">
        <v-card class="w-full max-w-md p-6 rounded-2xl">
            <div class="text-center mb-4">
                <h2 class="text-xl font-semibold">Iniciar sesión</h2>
                <p class="text-sm text-gray-500">UMG 2025</p>
            </div>

            <v-form v-model="valid" @submit.prevent="onSubmit">
                <v-text-field v-model="email" label="Email" type="email" :rules="[rules.required, rules.email]"
                    prepend-inner-icon="mdi-email" density="comfortable" clearable />
                <v-text-field v-model="password" label="Password" type="password" :rules="[rules.required, rules.min6]"
                    prepend-inner-icon="mdi-lock" density="comfortable" clearable />

                <v-alert v-if="errorMsg" type="error" variant="tonal" class="mb-3" :text="errorMsg"
                    density="comfortable" />

                <v-btn :loading="loading" :disabled="!valid || loading" type="submit" color="primary" class="w-full">
                    Entrar
                </v-btn>
            </v-form>
        </v-card>
    </v-container>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const loading = ref(false)
const valid = ref(false)
const errorMsg = ref('')

const rules = {
    required: (v: string) => !!v || 'Requerido',
    email: (v: string) => /.+@.+\..+/.test(v) || 'Email inválido',
    min6: (v: string) => (v?.length ?? 0) >= 6 || 'Mínimo 6 caracteres',
}

const onSubmit = async () => {
    errorMsg.value = ''
    loading.value = true
    try {
        // Llamada a tu endpoint
        const { data } = await api.post('/login', {
            email: email.value,
            password: password.value,
        })

        // Guardar token y usuario
        localStorage.setItem('token', data.token)
        localStorage.setItem('user', JSON.stringify(data.usuario))

        // Redirigir (a lo que tengas, por ejemplo /usuarios)
        const redirect = (route.query.redirect as string) || '/usuarios'
        router.push(redirect)
    } catch (e: any) {
        // Mensaje claro desde backend o genérico
        errorMsg.value = e?.response?.data?.message
            || e?.response?.data?.errors?.email?.[0]
            || 'No se pudo iniciar sesión. Verifica tus credenciales.'
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.min-h-screen {
    min-height: 100vh;
}

.max-w-md {
    max-width: 28rem;
}

.p-6 {
    padding: 1.5rem;
}

.mb-3 {
    margin-bottom: .75rem;
}

.mb-4 {
    margin-bottom: 1rem;
}

.mt-4 {
    margin-top: 1rem;
}

.w-full {
    width: 100%;
}

.text-center {
    text-align: center;
}

.text-xl {
    font-size: 1.25rem;
}

.font-semibold {
    font-weight: 600;
}

.text-sm {
    font-size: .875rem;
}

.text-xs {
    font-size: .75rem;
}

.text-gray-500 {
    color: #6b7280;
}

.rounded-2xl {
    border-radius: 1rem;
}
</style>
