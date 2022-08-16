<template>
  <div class="" style="padding: 20px;">
    <!-- <v-container> -->
    <v-row no-gutters>
      <v-col cols="12">
        <h1 class="w-full text-left">Создать задачу</h1>
      </v-col>
    </v-row>

    <v-row no-gutters class="mt-10">
      <v-col cols="12" md="4" class="pl-0 pl-md-5 mt-4 mt-md-0">
        <div v-if="errors">
          <div v-for="(v, k) in errors" :key="k"
            class="bg-red-500 text-white rounded font-bold mb-4 shadow-lg py-2 px-4 pr-0">
            <p v-for="error in v" :key="error" class="text-sm">
              {{ error }}
            </p>
          </div>
        </div>

        <v-form class="space-y-6 d-flex flex-column">
          <v-text-field v-model="form.name" label="Название" variant="underlined" required density="comfortable"
            type="text"></v-text-field>

          <v-switch v-model="form.is_hurry" :label="`Срочно?`" color="primary"></v-switch>

          <v-textarea v-model="form.description" label="Описание" variant="outlined" density="comfortable" type="text">
          </v-textarea>

          <div class="flex flex-row">
            <span class="oks-datetime-label">Дедлайн</span>
            <input class="oks-datetime" type="datetime-local" v-model="form.due_date" placeholder="Укажите дедлайн"
              aria-label="deadline datepicker" />
          </div>

          <div class="mt-8">
            <multiselect v-model="form.responsibles" :options="users" placeholder="Укажите ответственных" label="name"
              track-by="name" multiple>
            </multiselect>
          </div>

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
        description: null,
        name: null,
        due_date: null,
        is_hurry: null,
        responsibles: [],
      },

      currentUser: null,
      users: [],
    }
  },

  mounted() {
    // this.isLoading = true

    // get current user
    this.getCurrentUser();
    this.getUsers();
  },

  methods: {
    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data;
      });
    },

    getUsers() {
      axios.get('/api/v1/users').then((response) => {
        this.users = response.data;
      })
    },

    save() {
      try {
        axios.post(`/api/v1/tasks`, this.form).then((response) => {
          console.log(response.data);
          this.$router.push({ name: 'tasks.index' });
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


<style>
.oks-datetime-label {
  color: rgba(0, 0, 0, 0.87);
  opacity: 0.6;
  margin-right: 10px;
}

.oks-datetime {
  border: 1px solid gray;
  padding: 4px 8px;
  border-radius: 4px;

}
</style>