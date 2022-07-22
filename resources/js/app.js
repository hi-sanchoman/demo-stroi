import 'vuetify/styles'
import { createApp } from 'vue'
import { createVuetify } from 'vuetify'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'

import HelloWorld from './components/HelloWorld.vue'
import Login from './components/auth/Login.vue'
import ApplicationList from './components/applications/ApplicationList.vue'
import ApplicationCreate from './components/applications/ApplicationCreate.vue'
import ApplicationSidebar from './components/applications/ApplicationSidebar.vue'
import ApplicationEdit from './components/applications/ApplicationEdit.vue'
import InventorySidebar from './components/inventories/InventorySidebar.vue'
import InventoryHistory from './components/inventories/InventoryHistory.vue'
import InventoryList from './components/inventories/InventoryList.vue'
import InventoryShow from './components/inventories/InventoryShow.vue'
import PaymentList from './components/payments/PaymentList.vue'
import ToPayList from './components/payments/ToPayList.vue'
import SupplyList from './components/supplies/SupplyList.vue'
import ProfileEdit from './components/profile/ProfileEdit.vue'

import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import { loadFonts } from './plugins/webfontloader'

// import HttpClient from './httpclient.js'
import axios from 'axios'

// import OneSignalVuePlugin from '@onesignal/onesignal-vue3'
import messaging from './firebase'

loadFonts()

// Routes
const routes = [
  { path: '/', component: HelloWorld },

  { path: '/login', component: Login },
  { path: '/profile', component: ProfileEdit },

  { path: '/applications/create', name: 'applications.create', component: ApplicationCreate },
  { path: '/applications/:id/edit', name: 'applications.edit', component: ApplicationEdit },
  { path: '/applications/', name: 'applications.index', component: ApplicationList },

  { path: '/inventories', name: 'inventories.index', component: InventoryList },
  { path: '/inventories/:id', name: 'inventories.show', component: InventoryShow },
  { path: '/inventories/:id/history', name: 'inventories.history', component: InventoryHistory },

  { path: '/supplies', name: 'supplies.index', component: SupplyList },

  { path: '/storages', component: HelloWorld },
  { path: '/payments', component: PaymentList },
  { path: '/to-pay', component: ToPayList },
]

// create routes
const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

function isLoggedIn() {
  return localStorage.getItem('token')
}

router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isLoggedIn()) {
      next({ path: '/login', query: { redirect: to.fullPath } })
    } else {
      next()
    }
  } else if (to.matched.some(record => record.meta.guest)) {
    if (isLoggedIn()) {
      next({
        path: '/',
        query: { redirect: to.fullPath }
      })
    } else {
      next()
    }
  } else {
    next() // make sure to always call next()!
  }
})

// create app
const app = createApp(App)
const vuetify = createVuetify({
  components,
  directives,
})

app.use(vuetify)
app.use(router)
// app.use(OneSignalVuePlugin, {
//   appId: '6e633a0b-7f5c-4b5b-b2cf-b6c05a382e4a',
// })

// not auth interceptor
axios.interceptors.request.use(function (config) {
  const token = localStorage.getItem('token')
  config.headers.Authorization = 'Bearer ' + token

  // console.log("token", token)

  return config
})

axios.interceptors.response.use(function (response) {
  return response
}, function (error) {
  console.log("interceptor error", error.response.data)

  if (error.response.status === 401) {
    // store.dispatch('logout')
    localStorage.removeItem('token')
    router.push('/login')
  }

  return Promise.reject(error)
})


app.provide('isLoading', false)

// firebase messaging
app.config.globalProperties.$messaging = messaging

app.mount('#app')