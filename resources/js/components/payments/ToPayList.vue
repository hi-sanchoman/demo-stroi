<template>
    <div style="padding: 20px;">
        <!-- <v-container> -->
        <v-row no-gutters v-if="!chosenObject">
            <v-col cols="12" md="4">
                <!-- <multiselect v-model="chosenObject" :options="objects" placeholder="Выберите объект" label="name"
                    track-by="name">
                </multiselect> -->

                <v-card v-for="construction in objects" :key="construction.id" class="border rounded px-4 py-4 w-fit"
                    @click="selectConstruction(construction)">
                    <v-card-title>{{ construction.name }}</v-card-title>
                </v-card>
            </v-col>
        </v-row>

        <v-row no-gutters class="mt-5" v-if="chosenObject">
            <v-col cols="12" class="mb-5">
                <v-btn @click="chosenObject = null" size="small">Выбрать другой объект</v-btn>
                <h2>Входящие на оплату</h2>
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
                            <!-- <th class="text-left">
                                    Наименование ресурса
                                </th> -->
                            <th class="text-left">
                                Компания
                            </th>
                            <th>
                                Счета на оплату
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

                        <tr v-for="payment in payments" :key="payment.id">
                            <td>{{ payment.application.construction.name }}</td>
                            <td style="">
                                <span class="cursor-pointer hover:underline"
                                    @click="goToApplication(payment.application.parent_id ? payment.application.parent_id : payment.application.id)">
                                    {{ payment.application.num ? payment.application.num : payment.application.id }}
                                </span>
                                <br />
                                {{ getKind(payment.application.kind) }}
                                <br />
                                <a class="mb-1" :href="`/export/application/${payment.application.parent_id ? payment.application.parent_id : payment.application.id}`">скачать
                                    заявку</a>
                            </td>
                            <td width="30%">{{ payment.company.name }}</td>
                            <td>
                                <a v-for="(file, index) in payment.files" :key="index"
                                    class="block px-2 py-1 mb-1 text-sm border text-black text-decoration-none hover:bg-slate-100 cursor-pointer"
                                    target="_blank" :href="'/uploads/' + file" style="display: block; min-width: 120px">
                                    Счет на оплату - {{ index + 1 }}
                                </a>
                            </td>
                            <td>{{ payment.to_be_paid }} тг</td>
                            <!-- <td>

                                </td> -->
                            <td>
                                <v-btn color="success" @click="setPaid(payment)">
                                    Оплачено
                                </v-btn>
                            </td>
                        </tr>
                    </tbody>
                </v-table>
            </v-col>
        </v-row>
        <!-- </v-container> -->


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

            chosenObject: null,
            objects: [],
        }
    },

    methods: {
        getCurrentUser() {
            axios.get('/api/v1/me').then((response) => {
                this.currentUser = response.data
                // console.log(this.currentUser)
            })
        },

        getObjects() {
            axios.get('/api/v1/constructions').then((response) => {
                // console.log(response.data);
                this.objects = response.data.data;
            });
        },

        getPayments(construction) {
            // console.log('get payments')

            // get applications 
            axios.get(`/api/v1/payments-to-pay?construction_id=${construction.id}`).then((response) => {
                this.payments = response.data.data
                // console.log(this.payments)
            });
        },

        setPaid(offer) {
            if (!confirm('Вы действительно оплатили?')) return;

            axios.put(`/api/v1/to-pay/${offer.id}`).then((response) => {
                this.getPayments(this.chosenObject);

                this.snackbar.text = 'Оплачено';
                this.snackbar.status = true;
            });
        },

        goToApplication(id) {
            this.$router.push(`/applications/${id}/edit`);
        },

        getKind(kind) {
            let kinds = {
                'product': 'заявка на товар',
                'equipment': 'заявка на спец. технику',
                'service': 'заявка на услугу',
            }

            return kinds[kind] ?? '';
        },

        selectConstruction(c) {
            this.chosenObject = c;
            this.payments = [];
            this.getPayments(c);
        }

    },

    mounted() {
        // this.getApplications('draft')
        this.getCurrentUser();
        this.getObjects();
        // this.getPayments();
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
