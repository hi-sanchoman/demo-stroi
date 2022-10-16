import 'vuetify/styles'
import { createApp } from 'vue'
import { createVuetify } from 'vuetify'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'

import HelloWorld from './components/HelloWorld.vue'
import Login from './components/auth/Login.vue'
import ApplicationList from './components/applications/ApplicationList.vue'
import ApplicationCreate from './components/applications/ApplicationCreate.vue'
import ApplicationEdit from './components/applications/ApplicationEdit.vue'
import InventoryHistory from './components/inventories/InventoryHistory.vue'
import InventoryList from './components/inventories/InventoryList.vue'
import InventoryShow from './components/inventories/InventoryShow.vue'
import InventoryEquipment from './components/inventories/InventoryEquipment.vue'
import InventoryService from './components/inventories/InventoryService.vue'
import PaymentList from './components/payments/PaymentList.vue'
import ToPayList from './components/payments/ToPayList.vue'
import SupplyList from './components/supplies/SupplyList.vue'
import ProfileEdit from './components/profile/ProfileEdit.vue'
import ContractList from './components/contracts/ContractList.vue'
import ContractCreate from './components/contracts/ContractCreate.vue'
import ContractEdit from './components/contracts/ContractEdit.vue'
import CompanyList from './components/companies/CompanyList.vue'
import CompanyCreate from './components/companies/CompanyCreate.vue'
import CompanyEdit from './components/companies/CompanyEdit.vue'
import CompanyContactCreate from './components/companies/contacts/ContactCreate.vue'
import TaskList from './components/tasks/TaskList.vue'
import TaskCreate from './components/tasks/TaskCreate.vue'
import TaskEdit from './components/tasks/TaskEdit.vue'

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

  // contracts  
  { path: '/contracts/create', name: 'contracts.create', component: ContractCreate },
  { path: '/contracts/:id/edit', name: 'contracts.edit', component: ContractEdit },
  { path: '/contracts/', name: 'contracts.index', component: ContractList },

  { path: '/inventories', name: 'inventories.index', component: InventoryList },
  { path: '/inventories/:id/products', name: 'inventories.show', component: InventoryShow },
  { path: '/inventories/:id/equipment', name: 'inventories.equipment', component: InventoryEquipment },
  { path: '/inventories/:id/services', name: 'inventories.services', component: InventoryService },
  { path: '/inventories/:id/history', name: 'inventories.history', component: InventoryHistory },

  { path: '/supplies', name: 'supplies.index', component: SupplyList },
  
  // companies
  { path: '/companies', name: 'companies.index', component: CompanyList },
  { path: '/companies/create', name: 'companies.create', component: CompanyCreate },

  
  // company contacts
  { path: '/companies/:id/contacts/create', name: 'companies.contacts.create', component: CompanyContactCreate },
  
  { path: '/companies/:id/edit', name: 'companies.edit', component: CompanyEdit },

  // tasks
  { path: '/tasks', name: 'tasks.index', component: TaskList },
  { path: '/tasks/create', name: 'tasks.create', component: TaskCreate },
  { path: '/tasks/:id/edit', name: 'tasks.edit', component: TaskEdit },


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