<template>
    <div>
        <v-container class="fill-height">
            <v-row no-gutters class="d-flex justify-center align-center fill-height">
                <v-col cols="12" md="4" class="mx-auto my-auto border px-5 py-5 elevation-3">
                    <h3 class="w-full text-center mb-3">Вход в систему</h3>
  
                    <v-form ref="form">
                        <!-- <v-text-field v-model="formData.email" label="Электронная почта" :error-messages="errors.email"
                            required></v-text-field> -->
  
                        <multiselect class="mb-4" v-model="formData.email" :options="users"
                            placeholder="Выберите пользователя" label="name" track-by="name">
                        </multiselect>
  
                        <v-text-field v-model="formData.password" label="Пароль"
                            :append-icon="show ? 'mdi-eye' : 'mdi-eye-off'" :type="show ? 'text' : 'password'"
                            @click:append="show = !show" @keyup.enter="login" required></v-text-field>
  
                        <v-row class="d-flex align-center justify-center">
                            <v-btn color="primary" class="mr-4" @click="login">
                                Войти в систему
                            </v-btn>
                        </v-row>
                    </v-form>
                </v-col>
            </v-row>
        </v-container>
    </div>
  </template>
  
  <script>
  import axios from 'axios'
  import Multiselect from 'vue-multiselect'
  
  export default {
    name: 'Login',
  
    components: {
        Multiselect,
    },
  
    data: () => ({
        valid: false,
        show: false,
  
        formData: {
            email: null,
            password: null,
            device_name: 'browser',
        },
  
        users: [
            { 'name': 'ПТО', value: 'ptd@mail.com' },
            { 'name': 'Начальник ПТО', value: 'ptdchief@mail.com' },
            { 'name': 'Начальник Участка', value: 'nachuch@mail.com' },
            { 'name': 'Главный инженер', value: 'engineer@mail.com' },
            { 'name': 'Зам.директора по строительству', value: 'stroidir@mail.com' },
            { 'name': 'Снабженец', value: 'supplier@mail.com' },
            { 'name': 'Начальник снабжения', value: 'supervisor@mail.com' },
            { 'name': 'Зав. склада', value: 'warehouse@mail.com' },
            // { 'name': 'Экономист', value: 'economist@mail.com' },
            { 'name': 'Начальник отдела по экономике', value: 'economist@mail.com' },
            { 'name': 'Финансовый директор', value: 'findir@mail.com' },
            { 'name': 'Бухгалтер', value: 'buh@mail.com' },
            { 'name': 'Бригадир', value: 'brigadir@mail.com' },
            { 'name': 'Материальный бухгалтер', value: 'mathub@mail.com' },
            { 'name': 'Генеральный директор', value: 'ceo@mail.com' },
        ],
  
        errors: {},
    }),
  
    methods: {
        login() {
            // console.log(this.formData);
  
            this.formData.email = this.formData.email.value;
  
            axios.post('/api/v1/auth/login', this.formData).then((response) => {
                localStorage.setItem('token', response.data)
                // this.$router.push('/applications?status=redirect')
                this.$router.push('/')
            }).catch((exception) => {
                this.errors = exception.response.data.errors
            })
        }
    },
  
    mounted() {
        // this.formData.email = 'ptd@mail.com';
        this.formData.password = 'password123';
    }
  }
  </script>
  