import '../css/app.css'
import 'vue-select/dist/vue-select.css'
import './bootstrap'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

createApp(App).use(router).mount('#app')