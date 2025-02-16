import './assets/main.css'
import './assets/css/app.css'
import './assets/css/general.css'

// Import Plugins
// import '@/assets/fonts/font-awesome-4.7.0/css/font-awesome.css'

// Import JS Files
import '@/assets/js/app.js'

// Material Design Icons
import '@mdi/font/css/materialdesignicons.css'

// Satoshi Font Style
import '@/assets/fonts/satoshi/css/satoshi.css'


import { createApp } from 'vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'

import App from './App.vue'
import router from './router'

/** Vuetify */
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import "vuetify/styles";

/** ToastPlugin Notification */
import ToastPlugin from 'vue-toast-notification'
import 'vue-toast-notification/dist/theme-bootstrap.css'

/** Export Excel */
import JsonExcel from 'vue-json-excel3'

/** CASTL plugin - for permissions */
import { abilitiesPlugin } from '@casl/vue'
import { abilityStore } from './stores/abilityStores'
import { ref } from 'vue'

/** Printing */
import Print from 'vue3-print-nb'

/** Signature Pad */
import { VueSignaturePad } from 'vue-signature-pad'



const icons = {
  defaultSet: 'mdi',
  aliases,
  sets: {
    mdi,
  }
}

const themeColor = {
  dark: false,
  colors: {
    primary: "#191970",
  },
}

const vuetify = createVuetify({
  components,
  directives,
  icons,
  theme: {
    defaultTheme: 'themeColor',
    themes: {
      themeColor,
    },
  },
})

const app = createApp(App)

app.component("downloadExcel", JsonExcel);
const pnia = createPinia()
pnia.use(piniaPluginPersistedstate)
app.use(pnia)

const abilityStored = abilityStore(pnia)
let ability = ref(abilityStored.abilities)
app.use(abilitiesPlugin, ability.value)

app.component('VueSignaturePad', VueSignaturePad);

app.use(vuetify)
app.use(ToastPlugin)
app.use(Print)
app.use(router)


app.mount('#app')


// createApp(App).use(vuetify).mount('#app')

