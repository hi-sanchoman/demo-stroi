<template>
    <div>
        <v-container class="fill-height">
            <v-row no-gutters class="d-flex justify-center align-center fill-height">
                <v-col  
                    cols="6"
                    class="mx-auto my-auto border px-15 py-15 elevation-3"
                >
                    <h3 class="w-full text-center mb-3">Вход в систему</h3>

                    <v-form
                        ref="form"                   
                    >
                        <v-text-field
                            v-model="formData.email"
                            label="Электронная почта"
                            :error-messages="errors.email"
                            required
                        ></v-text-field>

                        <v-text-field
                            v-model="formData.password"
                            label="Пароль"
                            :append-icon="show ? 'mdi-eye' : 'mdi-eye-off'"
                            :type="show ? 'text' : 'password'"
                            @click:append="show = !show"
                            @keyup.enter="login"
                            required
                        ></v-text-field>

                        <v-row class="d-flex align-center justify-center">
                            <v-btn
                                color="primary"
                                class="mr-4"
                                @click="login"
                            >
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

export default {
    name: 'Login',

    data: () => ({
        valid: false,
        show: false,

        formData: {
            email: null,
            password: null,
            device_name: 'browser',
        },

        errors: {},
    }),

    methods: {
        login() {
            axios.post('/api/v1/auth/login', this.formData).then((response) => {
                localStorage.setItem('token', response.data)
                this.$router.push('/')
            }).catch((exception) => {
                this.errors = exception.response.data.errors
            })
        }
    },

    mounted() {

    }
}
</script>
