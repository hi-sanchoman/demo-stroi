<template>
  <div class="" style="padding: 20px;">
    <!-- <v-container> -->
    <v-row class="">
      <v-col cols="12" md="2" class="pl-0 pl-md-5 mt-4 mt-md-0">
        <v-card>
          <template v-slot:title>
            Карточка клиента
          </template>

          <template v-slot:subtitle>
            Основная информация
          </template>

          <template v-slot:text>
            <v-form class="space-y-2">
              <v-text-field v-model="form.name" label="Название" variant="underlined" required density="comfortable"
                type="text"></v-text-field>

              <v-text-field v-model="form.phone" label="Телефон" variant="underlined" density="comfortable" type="text">
              </v-text-field>

              <v-text-field v-model="form.email" label="Эл. почта" variant="underlined" density="comfortable"
                type="email">
              </v-text-field>

              <v-text-field v-model="form.address" label="Адрес" variant="underlined" density="comfortable" type="text">
              </v-text-field>

              <v-text-field v-model="form.website" label="Вебсайт" variant="underlined" density="comfortable"
                type="url">
              </v-text-field>

              <v-textarea outlined v-model="form.notes" label="Примечание" variant="underlined" density="comfortable">
              </v-textarea>

              <multiselect v-model="form.status" :options="statuses" placeholder="Укажите статус" label="name"
                track-by="name">
              </multiselect>

              <v-text-field v-if="form.status?.value === 'service'" v-model="form.service" label="Вид услуги"
                variant="underlined" density="comfortable" type="text">
              </v-text-field>

              <div v-if="company && company.responsible" class="px-1 py-1">
                <h3 class="mt-6">Ответственный:</h3>
                <v-row class="mt-2 d-flex align-center">
                  <div style="width: 50px">
                    <v-img :src="company.responsible.photo_url" />
                  </div>
                  <span class="ml-2">{{ company.responsible.name }}</span>
                  <!-- <span>{{ company.responsible.roles[0].title }}</span> -->
                </v-row>
              </div>

              <v-btn v-if="form.name" class="mt-6" @click="save" color="primary">
                Сохранить
              </v-btn>
            </v-form>
          </template>
        </v-card>

      </v-col>



      <v-col cols="12" md="7" lg="7">
        <v-card>
          <v-tabs v-model="tab" background-color="" fixed-tabs>
            <!-- <v-tab value="logs">История</v-tab> -->
            <!-- <v-tab value="events">События</v-tab> -->
            <v-tab value="offers">Предложения</v-tab>
          </v-tabs>

          <v-card-text>
            <v-window v-model="tab">
              <!-- <v-window-item value="logs">
                История
              </v-window-item> -->

              <!-- <v-window-item value="events">
                События
              </v-window-item> -->

              <v-window-item value="offers">

                <v-table class="w-100" v-if="canSeeOffers">
                  <thead>
                    <tr>
                      <th>№ заявки</th>
                      <th>Объект</th>
                      <th>Наименование ресурса</th>
                      <th>Кол-во</th>
                      <th>Цена</th>
                      <th>Сумма</th>
                      <th>Файл</th>
                      <th>Дата</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(offer, index) in offers" :key="index">
                      <td>{{ offer.application.num ? offer.application.num : offer.application.id}}</td>
                      <td>{{ offer.construction.name }}</td>
                      <td>
                        <span v-if="offer.type === 'application'">{{ offer.data.product.name }}</span>
                        <span v-else-if="offer.type === 'equipment'">{{ offer.data.equipment.name }}</span>
                        <span v-else-if="offer.type === 'service'">{{ offer.data.service }}</span>
                      </td>
                      <td>
                        {{ offer.offer.quantity }}
                        <span v-if="offer.type === 'application'">{{ offer.data.unit.name }}</span>
                        <span v-else-if="offer.type === 'equipment'">{{ offer.data.unit.name }}</span>
                        <span v-else-if="offer.type === 'service'">{{ offer.data.unit }}</span>
                      </td>
                      <td>
                        <span>{{ offer.offer.price }}</span>
                      </td>
                      <td>
                        {{ (offer.offer.price * offer.offer.quantity) }} ₸
                      </td>
                      <td>
                        <span v-if="
                          offer.offer.file !=
                          null
                        ">
                          <a class="px-2 py-2 mr-2 border text-black text-decoration-none hover:bg-slate-100 cursor-pointer"
                            target="_blank" :href="
                              '/uploads/' +
                              offer.offer.file
                            ">Просмотр</a>
                        </span>
                      </td>
                      <td>
                        {{ offer.offer.created_at }}
                      </td>
                    </tr>
                  </tbody>
                </v-table>

              </v-window-item>
            </v-window>
          </v-card-text>
        </v-card>
      </v-col>



      <v-col cols="12" md="3" lg="3">
        <v-card>
          <template v-slot:title>
            Контакты

            <div class="float-right ml-4" v-if="currentUser != null && company != null">
              <router-link :to="`/companies/${company.id}/contacts/create`">
                <v-btn color="primary" size="x-small" plain>
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
              </router-link>
            </div>
          </template>

          <template v-slot:subtitle>
            <span v-if="contacts.length <= 0">Еще нет контактов.</span>

            <div v-else class="d-flex flex-column w-100">
              <v-card class="w-100 mt-4" v-for="contact in contacts" :key="contact.id">
                <template v-slot:title>
                  <div class="d-flex flex-row w-100 justify-space-between">
                    <div>{{ contact.lastname }} {{ contact.firstname }}</div>
                    <div>
                      <v-btn size="small" :data-id="contact.id" @click="deleteContact">x удалить</v-btn>
                    </div>
                  </div>
                </template>
                <template v-slot:subtitle>
                  {{ contact.position }}
                </template>
                <template v-slot:text>
                  <span v-if="contact.phone1">Тел 1: {{ contact.phone1 }}</span><br />
                  <span v-if="contact.phone2">Тел 2: {{ contact.phone2 }}</span><br />
                  <span v-if="contact.email">Эл. почта: {{ contact.email }}</span>
                </template>
              </v-card>
            </div>
          </template>
        </v-card>
      </v-col>
    </v-row>

    <!-- </v-container> -->
  </div>



  <!-- Snackbar -->
  <v-snackbar v-model="snackbar.status" :timeout="snackbar.timeout">
    {{ snackbar.text }}

    <template v-slot:actions>
      <v-btn color="blue" variant="text" @click="snackbar.status = false">
        Закрыть
      </v-btn>
    </template>
  </v-snackbar>



</template>


<script>
import Multiselect from 'vue-multiselect'
import axios from 'axios'

export default {
  components: {
    Multiselect,
  },

  data() {
    return {
      form: {
        name: null,
        email: null,
        phone: null,
        website: null,
        address: null,
        status: null,
        notes: null,
        service: null,
      },

      statuses: [
        { name: 'Поставщик', value: 'supplier' },
        { name: 'Услуги', value: 'service' },
      ],

      currentUser: null,

      tab: 'logs',
      company: null,
      logs: [],
      events: [],
      contacts: [],
      offers: [],

      snackbar: {
        text: null,
        status: false,
      },

      canSeeOffers: false,
    }
  },

  mounted() {
    // this.isLoading = true

    // get current user
    this.getCurrentUser();
  },

  methods: {
    loadPage() {
      this.getCompany();
    },

    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data;

        this.loadPage();

        this.canSeeOffers = ['Vice President', 'Ecomonist', 'Supplier', 'Supervisor', 'Accountant', 'Chief Financial Officer'].includes(this.currentUser.roles[0].title);
      });
    },

    getCompany() {
      axios
        .get("/api/v1/companies/" + this.$route.params.id)
        .then((response) => {
          this.company = response.data;

          this.form = {
            id: this.company.id,
            name: this.company.name,
            email: this.company.email,
            phone: this.company.phone,
            address: this.company.address,
            website: this.company.website,
            status: this.findStatus(this.company.status),
            notes: this.company.notes,
            service: this.company.service,
          }
        });

      this.getContacts();
      this.getApplicationOffers();
    },

    getApplicationOffers() {
      axios.get(`/api/v1/companies/${this.$route.params.id}/offers`).then((response) => {
        this.offers = this.offers.concat(response.data);

        // console.log(this.offers);
      })
    },

    getContacts() {
      axios.get(`/api/v1/companies/${this.$route.params.id}/contacts`).then((response) => {
        this.contacts = response.data.data;
      })
    },

    findStatus(status) {
      // console.log({ status });
      return this.statuses.find(s => s.value === status);
    },

    save() {
      try {
        axios.put(`/api/v1/companies/${this.form.id}`, this.form).then((response) => {
          this.snackbar.text = "Клиент успешно обновлен.";
          this.snackbar.status = true;
        })
      } catch (e) {
        // console.log(e)

        if (e.response.status === 422) {
          // errors.value = e.response.data.errors
        }
      }
    },

    deleteContact(e) {
      if (!confirm('Вы действительно хотите удалить?')) return;
      const id = e.target.dataset.id;

      axios.delete(`/api/v1/companies/${this.company.id}/contacts/${id}`).then((response) => {
        if (response.data !== 1) { return; }

        this.getContacts();
      })
    }
  },

  watch: {
    '$route.query': {
      handler(newValue) {
        if (!this.oldKind) {
          this.oldKind = newValue;
          return;
        }

        location.reload();
      },
      immediate: true,
    }
  }
}
</script>