<template>
  <div class="" style="padding: 20px;">
    <!-- <v-container> -->
    <v-row no-gutters>
      <v-col cols="12">
        <h1 v-if="task" class="w-full text-left">Задача: {{ task.name }}</h1>
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

        <v-form v-if="task" class="space-y-6 d-flex flex-column">
          <span><strong>Дедлайн</strong>: {{ task.due_date ?? 'без срока' }}</span>
          <span class="mt-1">Срочно? {{ task.is_hurry === true ? 'ДА' : 'нет' }}</span>

          <div class="mt-8">
            <strong>Описание:</strong><br />
            {{ task.description }}
          </div>


          <div class="mt-8">
            <strong>Ответственные</strong>

            <v-list>
              <v-list-item v-for="r in task.responsibles" :key="r.id" :value="r.id">
                <v-list-item-title class="d-flex space-x-4" style="align-items: center">
                  <v-img :src="r.photo_url" width="40" height="40"></v-img>
                  <span class="ml-2">{{ r.name }}</span>
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </div>

          <v-btn v-if="task.status === 'new' && isResponsible()" class="mt-5" @click="start" color="green">
            Начать
          </v-btn>

          <v-btn v-if="task.status === 'in_progress'" class="mt-5" @click="complete" color="red">
            Завершить
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
      task: null,
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
    isResponsible() {
      // $ids = this.task.responsibles.find(r => r.user_id)
      return this.currentUser != null && this.task != null && this.task.responsibles.find(r => r.id == this.currentUser.id);
    },

    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data;

        this.loadData();
      });
    },

    loadData() {
      this.getTask();
    },

    getUsers() {
      axios.get('/api/v1/users').then((response) => {
        this.users = response.data;
      })
    },

    getTask() {
      axios.get(`/api/v1/tasks/${this.$route.params.id}`).then((response) => {
        console.log(response.data);
        this.task = response.data;

        this.task.is_hurry = this.task.is_hurry === 1 ? true : false;
      })
    },

    start() {
      try {
        axios.post(`/api/v1/tasks/${this.task.id}/start`, this.task).then((response) => {
          // console.log(response.data);
          this.task = response.data;
        })
      } catch (e) {
        console.log(e)

        if (e.response.status === 422) {
          // errors.value = e.response.data.errors
        }
      }
    },

    complete() {
      try {
        axios.post(`/api/v1/tasks/${this.task.id}/complete`, this.task).then((response) => {
          // console.log(response.data);
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