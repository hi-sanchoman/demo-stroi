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

                <v-col cols="12" class="">
                    <v-table transition="slide-x-transition" style="overflow-x:auto;">
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
                                <th class="text-left">
                                    Количество
                                </th>
                                <th>
                                    Сумма
                                </th>
                                <th>
                                    Оплачено
                                </th>
                                <th>
                                    Остаток
                                </th>
                                <th>
                                    Оплатить
                                </th>
                                <th>
                                    На оплате
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
                                <td>{{ payment.quantity }} {{ payment.application_product.product.unit }}</td>
                                <td>{{ payment.price * payment.quantity }} ₸</td>
                                <td>{{ payment.paidTotal }} ₸</td>
                                <td>{{ (payment.price * payment.quantity - payment.paidTotal) }} ₸</td>
                                <td class="pt-6">
                                    <v-text-field
                                        v-model="payment.order_paid"
                                        type="number"
                                        density="compact"
                                        class="w-24"
                                        variant="underlined"
                                        style="width: 150px"
                                        v-if="payment.application_product.application.status == 'in_progress'"
                                    >
                                    </v-text-field>
                                </td>
                                <td>
                                    {{ payment.to_be_paid ? `${payment.to_be_paid} ₸` : '' }} 
                                </td>
                            </tr>
                        </tbody>
                    </v-table>

                    <v-btn
                        v-if="this.currentUser != null && this.currentUser.roles[0].title == 'Vice President'"
                        class="mt-5"
                        @click="updatePayments"
                        color="primary"
                    >
                        Отправить на оплату
                    </v-btn>
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
                console.log(this.currentUser)
            })
        },

        getPayments() {
            console.log('get payments')

            // get applications 
            axios.get('/api/v1/payments').then((response) => {
                this.payments = response.data.data
                console.log(this.payments)
            });
        },

        updatePayments() {
            var data = [];

            for (var i = 0; i < this.payments.length; i++) {
                var offer = this.payments[i];

                data.push({
                    'id': offer.id,
                    'to_be_paid': offer.order_paid,
                });
            }

            // update once in a batch
            axios.put('/api/v1/payments', { data: data }).then((response) => {
                this.payments = response.data.data;

                for (var i = 0; i < this.payments.length; i++) {
                    this.payments[i].order_paid = 0;
                }

                this.snackbar.text = 'Отправлено на оплату';
                this.snackbar.status = true;
            });
        },

    },

    mounted() {
        // this.getApplications('draft')
        this.getCurrentUser();

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
