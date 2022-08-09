<template>
  <div style="padding: 20px;">
    <!-- <v-container> -->

    <v-row no-gutters>
      <v-col cols="12">
        <h1 v-if="inventory != null" class="w-full text-left">
          Склад: {{ inventory.construction.name }} (услуги)
        </h1>
      </v-col>
    </v-row>

    <v-row no-gutters class="mt-10">
      <v-col cols="12" md="4" lg="3" class="border px-5 py-5">
        <InventorySidebar v-if="currentUser != null && inventory != null" :currentUser="currentUser"
          :inventory="inventory" />
      </v-col>


      <v-col cols="12" md="8" lg="9" class="pl-0 pl-md-5 mt-4 mt-md-0">
        <!-- STOCKS -->
        <v-table transition="slide-x-transition" style="overflow-x:auto;">
          <thead>
            <tr>
              <th class="text-left">
                №
              </th>
              <th class="text-left">
                Наименование позиции
              </th>
              <th class="text-left">
                Ед. изм.
              </th>
              <th class="text-left">
                Кол-во
              </th>
              <th>
                Действие
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="stocks.length <= 0">
              <td colspan="5">Нет данных.</td>
            </tr>

            <template v-for="(stock, index) in stocks" :key="stock.id">
              <tr class="hover:bg-slate-100">
                <td>{{ (index + 1) }}</td>
                <td>{{ stock.application_service.service }}</td>
                <td>{{ stock.application_service.unit }}</td>
                <td>{{ stock.quantity }}</td>
                <td>
                </td>
              </tr>
            </template>
          </tbody>
        </v-table>
      </v-col>
    </v-row>

    <!-- </v-container> -->



    <!-- Snackbar -->
    <v-snackbar v-model="snackbar.status" :timeout="snackbar.timeout">
      {{ snackbar.text }}

      <template v-slot:actions>
        <v-btn color="blue" variant="text" @click="snackbar.status = false">
          Закрыть
        </v-btn>
      </template>
    </v-snackbar>
  </div>
</template>

<script>
import axios from 'axios'
import InventorySidebar from './InventorySidebar.vue'

export default {
  components: {
    InventorySidebar
  },

  data() {
    return {
      snackbar: {
        status: false,
        text: '',
        timeout: 2000,
      },
      inventory: null,
      stocks: [],
      currentUser: null,
    }
  },

  methods: {
    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data
        // console.log(this.currentUser)
      })
    },

    // showPosition(item) {
    //   this.historyDialog = true
    //   this.history = item
    //   this.historyInventories = []

    //   this.getHistoryInventories()
    // },

    getInventory() {
      // get applications 
      axios.get('/api/v1/inventories/' + this.$route.params.id).then((response) => {
        this.inventory = response.data.data;
      })
    },

    getServices() {
      axios.get('/api/v1/inventories/' + this.$route.params.id + '/services').then((response) => {
        this.stocks = response.data.data;
      })
    },

  },

  mounted() {
    console.log('mounted');
    // this.isLoading = true;

    // get current user
    this.getCurrentUser();

    this.getInventory();

    this.getServices();
  },

  watch: {
    '$route.query': {
      handler(newValue) {
        const { status } = newValue

        if (status == 'waiting') {
          // this.getIncoming();
        } else if (status == 'accepted') {
          this.getServices();
        } else if (status == 'declined') {
          console.log('get declined');
        }
      },
      immediate: true,
    }
  }
}
</script>
