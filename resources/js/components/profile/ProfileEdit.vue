<template>
  <div>
    <v-container class="fill-height">
      <v-row no-gutters class="d-flex justify-center align-center fill-height">
        <v-col cols="12" md="4" class="mx-auto my-auto border px-5 py-5 elevation-3">
          <h3 class="w-full text-center mb-3">
            Редактировать профиль
          </h3>

          <v-form ref="form">
            <div class="flex flex-column justify-center items-center text-center">
              <v-img class="bg-white mx-auto mt-4 mb-4 cursor-pointer" width="150" height="150" :aspect-ratio="1"
                :src="formData.photoUrl ? formData.photoUrl : '/images/default_profile.png'" cover />


              <input class="mx-auto mt-2 mb-5" type="file" id="file" v-on:change="handleFileUpload($event)"
                aria-label="photo" />
            </div>

            <v-text-field v-model="formData.name" label="Имя" :error-messages="errors.name" required>
            </v-text-field>

            <v-text-field v-model="formData.email" label="Электронная почта" :error-messages="errors.email" required>
            </v-text-field>

            <!-- <v-text-field v-model="formData.password" label="Пароль" :append-icon="show ? 'mdi-eye' : 'mdi-eye-off'"
              :type="show ? 'text' : 'password'" @click:append="show = !show">
            </v-text-field>

            <v-text-field v-model="formData.passwordConfirm" label="Повторите пароль"
              :append-icon="show ? 'mdi-eye' : 'mdi-eye-off'" :type="show ? 'text' : 'password'"
              @click:append="show = !show">
            </v-text-field> -->

            <v-row class="d-flex align-center justify-center">
              <v-btn color="primary" class="mr-4" @click="update">
                Обновить
              </v-btn>
            </v-row>
          </v-form>
        </v-col>
      </v-row>
    </v-container>

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
import axios from "axios";
import { store } from "../../store";

export default {
  name: "ProfileEdit",

  data: () => ({
    store,

    snackbar: {
      status: false,
      text: '',
      timeout: 2000,
    },

    valid: false,
    show: false,

    formData: {
      name: null,
      photoUrl: null,
      email: null,
      password: null,
      device_name: "browser",
    },

    errors: {},
  }),

  mounted() {
    this.getProfile();
  },

  methods: {
    getProfile() {
      axios.get('/api/v1/me').then((response) => {
        console.log(response.data);

        this.formData.name = response.data.name;
        this.formData.email = response.data.email;
        this.formData.photoUrl = response.data.photo_url;
      });
    },

    handleFileUpload(event) {
      this.file = event.target.files[0];

      let formData = new FormData();
      formData.append('file', this.file);
      
      axios.post('/api/v1/upload-photo', formData, {
        'headers': { 'Content-Type': 'multipart/form-data' }
      }).then((response) => {
        this.formData.photoUrl = response.data.data.photo;

        this.snackbar.text = 'Фото профиля изменено';
        this.snackbar.status = true;
      }).catch((error) => {
        console.error(error);
      })
    },

    update() {
      axios
        .put("/api/v1/profile", this.formData)
        .then((response) => {
          if (response.data == 'ok') {
            this.snackbar.text = 'Профиль успешно обновлен.'
            this.snackbar.status = true
          }
        })
        .catch((exception) => {
          console.log(exception);
          // this.errors = exception.response.data.errors;
          this.snackbar.text = 'ОШИБКА: ' + exception.message;
          this.snackbar.status = true
        });
    },
  },
};
</script>
