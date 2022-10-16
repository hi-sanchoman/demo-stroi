<template>
  <div style="padding: 20px;">
    <!-- <v-container> -->

    <v-row no-gutters>
      <v-col cols="12">
        <h1 v-if="inventory != null" class="w-full text-left">
          Склад: {{ inventory.construction.name }} (спец. техника)
        </h1>
      </v-col>
    </v-row>

    <v-row no-gutters class="mt-10">
      <v-col cols="12" md="4" lg="3" class="border px-5 py-5">
        <InventorySidebar v-if="currentUser != null && inventory != null" :currentUser="currentUser"
          :inventory="inventory" />
      </v-col>


      <v-col cols="12" md="8" lg="9" class="pl-0 pl-md-5 mt-4 mt-md-0">
        <!-- INCOMING -->
        <template v-for="item in incoming" :key="item.id">
          <div class="flex justify-between border rounded mb-4 px-2 py-2">
            <div>
              От склада <strong>{{ item.sender.construction.name }}</strong>
              отправлено <strong>{{ item.stock.application_product.product.name }}</strong>
              в количестве <strong>{{ item.quantity }}</strong> {{ item.stock.application_product.product.unit }}
            </div>

            <div class="mt-2">
              <v-btn class="mr-3" color="success" size="small" @click="acceptProduct(item)">Принять</v-btn>
              <v-btn color="error" size="small" @click="declineProduct(item)">Отклонить</v-btn>
            </div>
          </div>
        </template>

        <!-- STOCKS -->
        <v-table transition="slide-x-transition" style="overflow-x:auto;">
          <thead>
            <tr>
              <th class="text-left">
                №
              </th>
              <th class="text-left">
                Наименование техники
              </th>
              <th class="text-left">
                Дата принятия
              </th>
              <th class="text-left">
                Кол-во
              </th>
              <th class="text-left">
                Часов отработано
              </th>
              <th>
                Действие
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="stocks.length <= 0">
              <td colspan="6">Нет данных.</td>
            </tr>

            <template v-for="(stock, index) in stocks" :key="stock.id">
              <tr class="hover:bg-slate-100">
                <td>{{ (index + 1) }}</td>
                <td>{{ stock.application_equipment.equipment.name }}</td>
                <td>{{ stock.created_at }}</td>
                <td>{{ stock.quantity }}</td>
                <td>
                  {{ stock.application_equipment.notes.reduce((acc, n) => acc + n.hours, 0) }}
                </td>
                <td>
                  <v-btn v-if="stock.application_equipment.status == 'draft'" color="info" size="small"
                    @click="showAddNoteDialog(stock)">+ запись</v-btn>
                  <v-btn v-if="stock.application_equipment.status == 'draft'" color="success ml-2" size="small"
                    @click="complete(stock)">готово</v-btn>
                </td>
              </tr>

              <template
                v-if="stock.application_equipment.notes != null && stock.application_equipment.notes.length > 0">
                <tr>
                  <td colspan="6">
                    <v-table>
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Дата</th>
                          <th>Отработано</th>
                          <th>Примечание</th>
                          <th></th>
                        </tr>
                      </thead>
          <tbody>
            <tr v-for="(note, n) in stock.application_equipment.notes" :key="note.id">
              <td>{{ n + 1 }}</td>
              <td>{{ note.created_at }}</td>
              <td>{{ note.hours }}</td>
              <td>{{ note.notes }}</td>
              <td>
                <!-- <v-btn color="error" size="small">удалить</v-btn> -->
              </td>
            </tr>

          </tbody>
        </v-table>
        </td>
        </tr>
</template>

</template>






</tbody>
</v-table>
</v-col>
</v-row>

<!-- </v-container> -->



<v-dialog v-model="addNoteDialog">
  <v-card class="oks-dialog min-w-5xl w-7xl" style="">
    <v-card-title>
      <span class="text-h5">Добавить запись</span>
    </v-card-title>

    <v-card-text>
      <v-container>
        <v-row>
          <v-col cols="12">

          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12">
            <label for="date">Укажите день</label><br />
            <input type="date" id="date" v-model="note.date" :min="new Date()" />

            <v-text-field class="mt-2" v-model="note.hours" label="Количество часов / Отработано" variant="underlined"
              required density="comfortable" type="number" @keyup.enter="addNote()"></v-text-field>

            <v-textarea class="mt-2" v-model="note.notes" label="Примечание" variant="underlined" required
              density="comfortable"></v-textarea>

          </v-col>
        </v-row>
      </v-container>
      <!-- <small>* обязательные поля</small> -->
    </v-card-text>
    <v-card-actions>
      <v-spacer></v-spacer>

      <v-btn color="default" text @click="addNoteDialog = false">
        Отмена
      </v-btn>

      <v-btn color="success" text @click="addNote()">
        Принять
      </v-btn>
    </v-card-actions>
  </v-card>
</v-dialog>



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
      incoming: [],

      addNoteDialog: false,
      note: {
        item: null,
        hours: null,
        notes: null,
        date: null,
      },
    }
  },

  methods: {
    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data
        // console.log(this.currentUser)
      })
    },

    showPosition(item) {
      this.historyDialog = true
      this.history = item
      this.historyInventories = []

      this.getHistoryInventories()
    },

    getInventory() {
      // get applications 
      axios.get('/api/v1/inventories/' + this.$route.params.id).then((response) => {
        this.inventory = response.data.data;
      })
    },

    getEquipments() {
      axios.get('/api/v1/inventories/' + this.$route.params.id + '/equipments').then((response) => {
        this.stocks = response.data.data;
      })
    },

    showAddNoteDialog(item) {
      this.addNoteDialog = true;
      this.note.item = item;
    },

    complete(item) {
      let data = {
        'status': 'completed',
      };

      axios.put('/api/v1/application-equipments/' + item.application_equipment_id, data).then((response) => {
        this.getEquipments();
      })
    },

    addNote() {
      let data = {
        note: this.note
      };

      const noteDate = new Date(this.note.date).setHours(0, 0, 0, 0);
      const today = new Date().setHours(0, 0, 0, 0);

      if (noteDate < today) {
        this.snackbar.text = "Ошибка: нельзя ввести прошедшую дату";
        this.snackbar.status = true;
        return;
      }

      axios.post('/api/v1/application-equipments/add-note', data).then((response) => {
        this.getEquipments();

        this.addNoteDialog = false;

        this.note = {

          item: null,
          hours: null,
          notes: null,
          date: null,
        }
      });
    }
  },

  mounted() {
    console.log('mounted');
    // this.isLoading = true;

    // get current user
    this.getCurrentUser();

    this.getInventory();

    this.getEquipments();
  },

  watch: {
    '$route.query': {
      handler(newValue) {
        const { status } = newValue

        if (status == 'waiting') {
          // this.getIncoming();
        } else if (status == 'accepted') {
          this.getEquipments();
        } else if (status == 'declined') {
          console.log('get declined');
        }
      },
      immediate: true,
    }
  }
}
</script>
