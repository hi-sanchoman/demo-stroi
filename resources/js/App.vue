<template>
  <v-app>
    <v-app-bar
      color="deep-purple"
      dark
    >
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>

      <v-toolbar-title class="flex-grow-1">
        <router-link to="/" class="text-decoration-none text-white">
          OKS Oasis
        </router-link>
      </v-toolbar-title>

      <v-row class="d-none d-md-flex md-flex-grow-1 md-flex-row">
        <router-link 
          v-if="currentUser != null && (currentUser.roles[0].title != 'Accountant' && currentUser.roles[0].title != 'Foreman')"
          to="/applications?status=redirect" class="text-decoration-none"
        >
          <v-btn flat class="text-white">
            Заявки 
            <span v-if="store.badgeNew > 0">
              <v-badge
                color="error"
                :content="store.badgeNew"
                inline
              ></v-badge>
            </span>
          </v-btn>
        </router-link>

        <router-link 
          v-if="currentUser != null && currentUser.roles[0].title == 'Warehouse Manager'"
          to="/inventories" class="text-decoration-none"
        >
          <v-btn flat class="text-white">Склады</v-btn>
        </router-link>
        
        <router-link 
          v-if="currentUser != null && currentUser.roles[0].title == 'Accountant'"
          to="/to-pay" class="text-decoration-none"
        >
          <v-btn flat class="text-white">На оплату</v-btn>
        </router-link>

        <router-link 
          v-if="currentUser != null && (currentUser.roles[0].title == 'Vice President' || currentUser.roles[0].title == 'Accountant')"
          to="/payments" class="text-decoration-none"
        >
          <v-btn flat class="text-white">Реестр платежей</v-btn>
        </router-link>

        <router-link 
          v-if="currentUser != null && currentUser.roles[0].title == 'Vice President'"
          to="/supplies" class="text-decoration-none"
        >
          <v-btn flat class="text-white">Накопители</v-btn>
        </router-link>


        <!-- <router-link to="/storages" class="text-decoration-none">
          <v-btn flat class="text-white">Склады</v-btn>
        </router-link>

        <router-link to="/inventories" class="text-decoration-none">
          <v-btn flat class="text-white">Накопитель</v-btn>
        </router-link> -->

        <div class="flex-grow-1"></div>

        <router-link 
          v-if="currentUser != null"
          to="/#" class="text-decoration-none"
        >
          <v-btn flat class="text-white">{{ currentUser.email }}</v-btn>
        </router-link>
      </v-row>

      <v-btn icon>
        <v-icon>mdi-account-circle-outline</v-icon>
      
        <v-menu activator="parent" transition="slide-x-transition" anchor="start">
          <v-list>
            <router-link to="/profile" class="text-decoration-none text-black">
              <v-list-item value="profile">
                <v-list-item-title>
                  Профиль
                </v-list-item-title>
              </v-list-item>
            </router-link>
            
            <router-link @click="logout" to="/logout" class="text-decoration-none text-black">
              <v-list-item value="logout">
                <v-list-item-title>
                  Выход
                </v-list-item-title>
              </v-list-item>
            </router-link>
          </v-list>
        </v-menu>
      </v-btn>

    </v-app-bar>

    <v-navigation-drawer
      v-model="drawer"
      absolute
      temporary
    >
      <v-list
        nav
        dense
      >       
        <v-list-item v-if="currentUser != null && (currentUser.roles[0].title != 'Accountant' && currentUser.roles[0].title != 'Foreman')">
          <router-link             
            to="/applications?status=redirect" class="text-decoration-none"
          >
            <v-btn flat class="">
              Заявки
              
              <v-badge
                v-if="store.badgeNew > 0"
                color="error"
                :content="store.badgeNew"
                inline
              ></v-badge>
            </v-btn>
          </router-link>
        </v-list-item>

        <v-list-item v-if="currentUser != null && currentUser.roles[0].title == 'Warehouse Manager'">
          <router-link             
            to="/inventories" class="text-decoration-none"
          >
            <v-btn flat class="">Склады</v-btn>
          </router-link>
        </v-list-item>

        <v-list-item v-if="currentUser != null && currentUser.roles[0].title == 'Accountant'">
          <router-link             
            to="/to-pay" class="text-decoration-none"
          >
            <v-btn flat class="">На оплату</v-btn>
          </router-link>
        </v-list-item>

        <v-list-item v-if="currentUser != null && (currentUser.roles[0].title == 'Vice President' || currentUser.roles[0].title == 'Accountant')">
          <router-link             
            to="/payments" class="text-decoration-none"
          >
            <v-btn flat class="">Реестр платежей</v-btn>
          </router-link>
        </v-list-item>

        <v-list-item v-if="currentUser != null && currentUser.roles[0].title == 'Vice President'">
          <router-link             
            to="/supplies" class="text-decoration-none"
          >
            <v-btn flat class="">Накопители</v-btn>
          </router-link>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <router-view v-slot="{ Component, route }">
        <transition name="slide" mode="out-in">
          <component :is="Component" :key="route.path" />
        </transition>
      </router-view>
    </v-main>

    <v-container v-if="isLoading">
      <v-progress-circular
        :size="70"
        :width="7"
        color="primary"
        indeterminate
      ></v-progress-circular>
    </v-container>

    <v-alert
      v-if="showNotification"
      type="success"
      border
      style="position: absolute; top: 100px; right: 20px; z-index: 999;"
    >
      Новое уведомление!<br><br>
      {{ notification.title }}<br> 
      {{ notification.body }} 
    </v-alert>
  </v-app>
</template>

<script>
import HelloWorld from './components/HelloWorld.vue'
import axios from 'axios'
import { store } from './store.js'
import { getToken, getMessaging, onMessage } from 'firebase/messaging'
import messaging from './firebase'

export default {
  name: 'App',

  components: {
    HelloWorld,
  },

  inject: ['isLoading'],

  data: () => ({
    drawer: false,
    group: null,
    currentUser: null,
    store,
    showNotification: false,
    notification: {
      title: null,
      body: null,
    },
  }),

  beforeCreate() {
    // this.$OneSignal.showSlidedownPrompt()
  },

  watch: {
    '$route' () {
      if (this.currentUser == null) {
        // console.log("get current user data")
        this.getCurrentUser()
      } else {
        // console.log('authenticated', this.currentUser.roles)
        // this.redirectUser()
      }
    }
  },

  methods: {
    listenForNotifications() {
      const messaging = getMessaging();
      
      onMessage(messaging, (payload) => {
        console.log('Message received. ', payload);
        
        this.notification = {
          title: payload.notification.title,
          body: payload.notification.body,
        };

        this.showNotification = true;

        setTimeout(() => {
          this.notification = {
            title: null,
            body: null,
          };

          this.showNotification = false;
        }, 5000);
      });

      // Echo.private('user-' + this.currentUser.id)
      //   .listen('ApplicationSigned', ev => {
      //     if (! ('Notification' in window)) {
      //       alert('Web Notification is not supported');
      //       return;
      //     }

      //     Notification.requestPermission( permission => {
      //       let notification = new Notification(ev.title, {
      //         body: ev.body,
      //         icon: "https://pusher.com/static_logos/320x320.png" // optional image url
      //       });
      //       // link to page on clicking the notification
      //       notification.onclick = () => {
      //         if (ev.action === 'url') {
      //           window.open(ev.data); 
      //         }
      //       };
      //     });
      //   })
    },

    logout() {
      axios.post('/api/v1/auth/logout').then((response) => {
        localStorage.removeItem('token');
        // this.$router.push('/login');
        location.reload()
      }).catch((exception) => {
        // exception
      })
    },

    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        console.log("current user", response.data)
        this.currentUser = response.data

        if (this.currentUser != null) {
          // console.log(this.currentUser.roles[0].title)
          // this.redirectUser()

          // get token
          getToken(messaging, { vapidKey: 'BOOU1xNcZUUoL7b7DZlDJ4uCqGD9syLMBEguNTmTvkTegJ5wiZfGc2O30Jws7XjkzfQZSKNbhuKAQyaNP9txa7M' }).then((currentToken) => {
            if (currentToken) {
              console.log("token: " + currentToken);

              // Send the token to your server and update the UI if necessary
              axios.post('/api/v1/device-token', { device_token: currentToken }).then((response) => {
                console.log('token is saved');
              });
            } else {
              // Show permission request UI
              console.log('No registration token available. Request permission to generate one.');
              // ...
            }
          }).catch((err) => {
            console.log('An error occurred while retrieving token. ', err);
            // ...
          });

          // get new badges
          this.getCountNewApplications();    
        }
      })
    },

    redirectUser() {
      if (this.currentUser.roles[0].title == 'PTD Engineer') {
        // console.log('PTOshnik')
        this.$router.push('/applications')
      } else {
        this.$router.push('/other')
      }
    },

    getCountNewApplications() {
      axios.get('/api/v1/badges-unread?type=applications').then((response) => {
        this.store.badgeNew = response.data;
      });
    }
  },

  mounted() {
    // this.getCurrentUser()

    this.listenForNotifications();
  }
}
</script>

 
<style lang="css">
  .slide-enter-active,
  .slide-leave-active {
    transition: opacity 0.3s, transform 0.3s;
  }

  .slide-enter-from,
  .slide-leave-to {
    opacity: 0;
    transform: translate(-30%);
  }
</style>