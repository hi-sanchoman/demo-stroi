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
                    <h2>Входящие на оплату</h2>
                </v-col>

                <v-col cols="12" class="">
                    <v-table transition="slide-x-transition">
                        <thead>
                            <tr>
                                <th class="text-left">
                                    Объект
                                </th> 
                                <th class="text-left">
                                    № заявки
                                </th> 
                                <th class="text-left">
                                    Наименование ресурса
                                </th>
                                <th class="text-left">
                                    Компания
                                </th>
                                <th>
                                    Сумма к оплате
                                </th>
                                <!-- <th>
                                    Платеж. поручение
                                </th> -->
                                <th>
                                    Управление
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
                                <td>{{ payment.application_product.application.construction.name }}</td>
                                <td>{{ payment.application_product.application.id }}</td>
                                <td width="30%">{{ payment.application_product.product.name }}</td>
                                <td>{{ payment.company.name }}</td>
                                <td>{{ payment.to_be_paid }} тг</td>
                                <!-- <td>

                                </td> -->
                                <td>
                                    <v-btn
                                        color="success"
                                        @click="setPaid(payment)"
                                    >
                                        Оплачено
                                    </v-btn>
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-col>
            </v-row>    
        </v-container>


        <!-- Snackbar -->
        <v-snackbar
            v-model="snackbar.status"
            :timeout="snackbar.timeout"
        >
            {{ snackbar.text }}

            <template v-slot:actions>
                <v-btn
                    color="blue"
                    variant="text"
                    @click="snackbar.status = false"
                >
                    Закрыть
                </v-btn>
            </template>
        </v-snackbar>


    </div>
</template>

<script>
import axios from 'axios'

export default {
    components: {
    },

    data() {
        return {
            snackbar: {
                status: false,
                text: '',
                timeout: 2000,
            },
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
            axios.get('/api/v1/payments-to-pay').then((response) => {
                this.payments = response.data.data
                console.log(this.payments)
            });
        },

        setPaid(offer) {
            axios.put(`/api/v1/to-pay/${offer.id}`).then((response) => {
                this.getPayments();

                this.snackbar.text = 'Оплачено';
                this.snackbar.status = true;
            });
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
