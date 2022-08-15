<template>
  <div class="" style="padding: 20px;">
    <!-- <v-container> -->
    <v-row no-gutters>
      <v-col cols="12">
        <h1 class="w-full text-left">Создать контакт клиента</h1>
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
          <v-text-field v-model="form.lastname" label="Фамилия" variant="underlined" required density="comfortable"
            type="text"></v-text-field>

          <v-text-field v-model="form.firstname" label="Имя" variant="underlined" required density="comfortable"
            type="text"></v-text-field>

          <v-text-field v-model="form.position" label="Должность" variant="underlined" required density="comfortable"
            type="text"></v-text-field>

          <v-text-field v-model="form.phone1" label="Телефон 1" variant="underlined" density="comfortable" type="text">
          </v-text-field>

          <v-text-field v-model="form.phone2" label="Телефон 2" variant="underlined" density="comfortable" type="text">
          </v-text-field>

          <v-text-field v-model="form.email" label="Электронный ящик" variant="underlined" density="comfortable"
            type="email">
          </v-text-field>

          <v-btn v-if="form.lastname && form.firstname && form.position" class="mt-5" @click="save" color="primary">
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
        company_id: null,
        firstname: null,
        lastname: null,
        position: null,
        email: null,
        phone1: null,
        phone2: null,
      },

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
        this.form.company_id = this.$route.params.id;

        axios.post(`/api/v1/companies/${this.form.company_id}/contacts`, this.form).then((response) => {
          this.$router.push({ name: 'companies.edit', params: { id: this.$route.params.id } });
        })
      } catch (e) {
        console.log(e)

        if (e.response.status === 422) {
          // errors.value = e.response.data.errors
        }
      }
    }
  },

}
</script>