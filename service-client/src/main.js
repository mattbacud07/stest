import './assets/main.css'
import './assets/css/app.css'
import './assets/css/general.css'

// Import Plugins
// import '@/assets/fonts/font-awesome-4.7.0/css/font-awesome.css'

// Import JS Files
import '@/assets/js/app.js'

// Material Design Icons
import '@mdi/font/css/materialdesignicons.css'


import { createApp } from 'vue'
import { createPinia } from 'pinia'

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
app.use(createPinia())
app.use(vuetify)
app.use(ToastPlugin)
app.use(router)


app.mount('#app')


// createApp(App).use(vuetify).mount('#app')

