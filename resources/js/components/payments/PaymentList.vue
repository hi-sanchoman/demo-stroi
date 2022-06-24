<template>
    <div>
        <v-container>
            <!-- <v-row no-gutters>
                <v-col cols="12">
                    <h1 class="w-full text-left">Заявки</h1>
                </v-col>
            </v-row> -->

            <v-row no-gutters class="mt-10">
                <v-col cols="12" class="mb-5">
                    <h2>Общий Реестр платежей</h2>
                </v-col>

                <v-col cols="12" class="pl-5">
                    <v-table transition="slide-x-transition">
                        <thead>
                            <tr>
                                <th class="text-left">
                                    № заявки
                                </th>
                                <th class="text-left">
                                    Наименование ресурса
                                </th>
                                <th class="text-left">
                                    Компания
                                </th>
                                <th class="text-left">
                                    Количество
                                </th>
                                <th>
                                    Сумма
                                </th>
                                <th>
                                    Оплатить
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="payments.length <= 0">
                                <td colspan="5">Нет данных.</td>
                            </tr>

                            <tr
                                v-for="payment in payments"
                                :key="payment.id"
                            >
                                <td>{{ payment.id }}</td>
                                <td>{{ payment.product.name }}</td>
                                <td>{{ payment.company }}</td>
                                <td>{{ payment.quantity }}</td>
                                <td>{{ payment.price * payment.quantity }} тг</td>
                                <td>
                                    <v-text-field
                                        type="number"
                                        density="compact"
                                        class="w-10"
                                        variant="underlined"
                                        style="width: 50px"
                                        v-if="payment.application.status == 'in_progress'"
                                    >
                                    </v-text-field>
                                </td>
                            </tr>
                        </tbody>
                    </v-table>

                    <v-btn
                        class="mt-5"
                        @click="updatePayments"
                        color="primary"
                    >
                        Сохранить.
                    </v-btn>
                </v-col>
            </v-row>    
        </v-container>


        


    </div>
</template>

<script>
import axios from 'axios'

export default {
    components: {
    },

    data() {
        return {
            payments: [],
            currentUser: null,
        } 
    },
    
    methods: {
        getCurrentUser() {
            axios.get('/api/v1/me').then((response) => {
                this.currentUser = response.data
                // console.log(this.currentUser)
            })
        },

        getPayments() {
            console.log('get payments')

            // get applications 
            axios.get('/api/v1/payments').then((response) => {
                this.payments = response.data.data
                console.log(this.payments)
            })
        },

    },

    mounted() {
        // this.getApplications('draft')
        this.getPayments()
    },

    watch: {
        // '$route.query': {
        //     handler(newValue) {
        //         const { status } = newValue

        //         this.getInventories()
        //     },
        //     immediate: true,
        // }
    }
}
</script>
