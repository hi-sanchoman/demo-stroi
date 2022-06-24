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
import InventoryList from './components/inventories/InventoryList.vue'
import PaymentList from './components/payments/PaymentList.vue'

import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import { loadFonts } from './plugins/webfontloader'

// import HttpClient from './httpclient.js'
import axios from 'axios'

loadFonts()

// Routes
const routes = [
  { path: '/', component: HelloWorld },
  
  { path: '/login', component: Login },
  
  { path: '/applications/create', name: 'applications.create', component: ApplicationCreate },
  { path: '/applications/:id/edit', name: 'applications.edit', component: ApplicationEdit },
  { path: '/applications/', name: 'applications.index', component: ApplicationList },


  { path: '/storages', component: HelloWorld },
  { path: '/inventories', component: InventoryList },
  { path: '/payments', component: PaymentList },
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
  } else if(to.matched.some(record => record.meta.guest)) {
    if (isLoggedIn()) {
      next({path: '/',
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

// not auth interceptor
axios.interceptors.request.use(function(config) {
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

app.mount('#app')