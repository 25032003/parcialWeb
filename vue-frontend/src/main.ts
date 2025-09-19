import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import vuetify from './plugins/vuetify'
import { createVuetify } from 'vuetify'


createApp(App)
  .use(createPinia())
  .use(router)
  .use(vuetify)
  .mount('#app')
