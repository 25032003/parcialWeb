import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

export const vuetifyInstance = createVuetify({
  components,
  directives,
})

export default vuetifyInstance