<template>
  <v-container class="items-center justify-center ">
    <v-row class="text-center justify-center items-center mt-6">
      <v-col cols="12" md="6" class="text-left overflow-y-scroll">
        <h2 class="mb-8">Мои задачи</h2>

        <v-card class="mb-4" v-for="task in tasks" :key="task.id">
          <v-card-text>
            <div>Дедлайн: {{ task.due_date }}</div>
            <p class="text-h4 text--primary">
              {{ task.name }}
            </p>

            <div class="text--primary">
              {{ task.description }}
            </div>
          </v-card-text>
          <v-card-actions>
            <v-btn variant="text" color="teal-accent-4" @click="navToTask(task)">
              Перейти
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
      <v-col cols="12" md="6" class="text-left overflow-y-scroll">
        <h2 class="mb-8">Последние события</h2>

        <a class="text-decoration-none" v-for="log in logs" :key="log.id"
          :href="`/applications/${log.application_id}/edit`">
          <v-card class="mb-4" :text="`${log.created_at}: ${log.log}`"></v-card>
        </a>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Dashboard',

  data() {
    return {
      logs: [],
      tasks: [],
    }
  },

  mounted() {
    this.getCurrentUser();
    this.getLogs();
    this.getTasks();
  },

  methods: {
    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data
        // console.log(this.currentUser)
      })
    },

    navToTask(task) {
      this.$router.push(`/tasks/${task.id}/edit`);
    },

    getTasks() {
      axios.get('/api/v1/tasks').then((response) => {
        // console.log(response.data.data);
        this.tasks = response.data.data;
      })
    },

    getLogs() {
      axios.get('/api/v1/application-logs').then((response) => {
        // console.log(response.data);
        this.logs = response.data.data;
      });
    },
  }
}
</script>
