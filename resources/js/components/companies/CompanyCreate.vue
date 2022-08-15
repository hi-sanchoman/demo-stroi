<template>
  <div class="" style="padding: 20px;">
    <!-- <v-container> -->
    <v-row no-gutters>
      <v-col cols="12">
        <h1 class="w-full text-left">Создать клиента</h1>
      </v-col>
    </v-row>

    <v-row no-gutters class="mt-10">
      <v-col cols="4" class="pl-0 pl-md-5 mt-4 mt-md-0">
        <div v-if="errors">
          <div v-for="(v, k) in errors" :key="k"
            class="bg-red-500 text-white rounded font-bold mb-4 shadow-lg py-2 px-4 pr-0">
            <p v-for="error in v" :key="error" class="text-sm">
              {{ error }}
            </p>
          </div>
        </div>

        <v-form class="space-y-6">
          <v-text-field v-model="form.name" label="Название" variant="underlined" required density="comfortable"
            type="text"></v-text-field>

          <v-text-field v-model="form.phone" label="Телефон" variant="underlined" density="comfortable" type="text">
          </v-text-field>

          <v-text-field v-model="form.email" label="Эл. почта" variant="underlined" density="comfortable" type="email">
          </v-text-field>

          <v-text-field v-model="form.address" label="Адрес" variant="underlined" density="comfortable" type="text">
          </v-text-field>

          <v-text-field v-model="form.website" label="Вебсайт" variant="underlined" density="comfortable" type="url">
          </v-text-field>

          <v-textarea outlined v-model="form.notes" label="Примечание" variant="underlined" density="comfortable">
          </v-textarea>

          <multiselect v-model="form.status" :options="statuses" placeholder="Укажите статус" label="name"
            track-by="name">
          </multiselect>

          <v-btn v-if="form.name" class="mt-5" @click="save" color="primary">
            Создать
          </v-btn>
        </v-form>
      </v-col>
    </v-row>

    <!-- </v-container> -->
  </div>
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
      },

      statuses: [
        { name: 'Лид', value: 'lead' },
        { name: 'Потенциальный', value: 'prospect' },
        { name: 'Клиент', value: 'customer' },
        { name: 'Повторный', value: 'recurrent' },
      ],

      currentUser: null,
    }
  },

  mounted() {
    // this.isLoading = true

    // get current user
    this.getCurrentUser();
  },

  methods: {
    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data;
      });
    },


    save() {

      try {
        axios.post('/api/v1/companies', this.form).then((response) => {
          // this.$router.push({ name: 'companies.index' })
          this.$router.push({ name: 'companies.edit', params: { id: response.data.id } })
        })
      } catch (e) {
        console.log(e)

        if (e.response.status === 422) {
          // errors.value = e.response.data.errors
        }
      }
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