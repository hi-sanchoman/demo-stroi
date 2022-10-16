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

        <v-row v-if="chosenObject" no-gutters class="mt-5">
            <v-col cols="12" class="mb-5">
                <v-btn @click="chosenObject = null" size="small">Выбрать другой объект</v-btn>
                <h2>Реестр платежей</h2>

                <div class="d-flex space-x-4">
                    <a v-if="isEconomist()" size="small" color="primary"
                        class="mt-3 v-btn v-btn--elevated v-theme--light v-btn--density-default v-btn--size-small v-btn--variant-contained"
                        :href="`/export/payments/${chosenObject.id}`">Скачать реестр</a>

                    <a v-if="isEconomist()" size="small" color="primary"
                        class="mt-3 ml-3 v-btn v-btn--elevated v-theme--light v-btn--density-default v-btn--size-small v-btn--variant-contained"
                        :href="`/export/payments/${chosenObject.id}?to_be_paid=1`">Скачать на оплате</a>

                    <a v-if="isEconomist()" size="small" color="primary"
                        class="mt-3 ml-3 v-btn v-btn--elevated v-theme--light v-btn--density-default v-btn--size-small v-btn--variant-contained"
                        :href="`/export/payments/${chosenObject.id}?positions=1`">Скачать по позициям</a>
                </div>
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
                                </th>
                                <th class="text-left">
                                    Количество
                                </th> -->
                            <th class="text-left">
                                Компания
                            </th>
                            <th class="text-left">
                                Счета на оплату
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
                            <td colspan="8">Нет данных.</td>
                        </tr>

                        <template v-for="payment in payments" :key="payment.id">
                            <tr>
                                <td>{{ payment.application.construction.name }}</td>
                                <td style="cursor:pointer">
                                    <span @click="goToApplication(payment.application.parent_id ? payment.application.parent_id : payment.application.id)" class="">
                                        {{ payment.application.num ? payment.application.num : payment.application.id }}
                                    </span>
                                    <br />
                                    {{ getKind(payment.application.kind) }}
                                    <br />
                                    <a class="mb-1" :href="`/export/application/${payment.application.parent_id ? payment.application.parent_id : payment.application.id}`">скачать
                                        заявку</a>
                                </td>
                                <!-- <td width="30%">{{ payment.application_product.product.name }}</td> -->
                                <!-- <td>{{ payment.quantity }} {{ payment.application_product.product.unit }}</td> -->
                                <td width="30%">
                                    {{ payment.company.name }}<br>

                                    <v-btn
                                        v-if="payment.product_offers?.length > 0 || payment.equipment_offers?.length > 0 || payment.service_offers?.length > 0"
                                        class="mt-2" size="small" @click="showOffers(payment)">
                                        Показать позиции
                                    </v-btn>
                                </td>
                                <td>
                                    <a v-for="(file, index) in payment.files" :key="index"
                                        class="block px-2 py-1 mb-1 text-sm border text-black text-decoration-none hover:bg-slate-100 cursor-pointer"
                                        target="_blank" :href="'/uploads/' + file"
                                        style="display: block; min-width: 120px">
                                        Счет на оплату - {{ index + 1 }}
                                    </a>
                                </td>
                                <td>{{ payment.amount.toLocaleString('ru') }} ₸</td>
                                <td>{{ payment.paid.toLocaleString('ru') }} ₸</td>
                                <td>{{ (payment.amount - payment.paid).toLocaleString('ru') }} ₸</td>
                                <td class="pt-6">
                                    <v-text-field v-model="payment.order_paid" type="number" density="compact"
                                        class="w-24" variant="underlined" style="width: 150px">
                                    </v-text-field>
                                </td>
                                <td>
                                    {{ payment.to_be_paid ? `${payment.to_be_paid.toLocaleString('ru')} ₸` : '' }}
                                </td>
                            </tr>

                            <template
                                v-if="payment.product_offers?.length > 0 || payment.equipment_offers?.length > 0 || payment.service_offers?.length > 0">
                                <tr :id="'offers_' + payment.id" style="display: none;">
                                    <td colspan="8" class="border-none">
                                        <v-table class="mt-4 mb-8 mx-8 border" style="overflow: visible;">
                                            <thead>
                                                <tr>
                                                    <th style="width: 25%">Статья расходов</th>
                                                    <th style="width: 25%">Название позиции</th>
                                                    <th style="width: 10%">Кол-во</th>
                                                    <th style="width: 10%">Цена за ед.</th>
                                                    <th>Общая сумма</th>
                                                </tr>
                                            </thead>
                    <tbody>
                        <tr v-for="offer in payment.product_offers" :key="offer.id">
                            <td class="">
                                {{ offer.application_product.category.name }}
                            </td>
                            <td class="">
                                {{ offer.application_product.product.name }}
                            </td>
                            <td>
                                {{ offer.quantity }} {{ offer.application_product.unit.name
                                }}
                            </td>
                            <td>
                                {{ offer.price.toLocaleString('ru') }} ₸
                            </td>
                            <td>
                                {{ (offer.price * offer.quantity).toLocaleString('ru') }} ₸
                            </td>
                        </tr>
                        <tr v-for="offer in payment.equipment_offers" :key="offer.id">
                            <td class="">
                                -
                            </td>
                            <td class="">
                                {{ offer.application_equipment.equipment.name }}
                            </td>
                            <td>
                                {{ offer.quantity }} {{ offer.application_equipment.unit.name }}
                            </td>
                            <td>
                                {{ offer.price.toLocaleString('ru') }} ₸
                            </td>
                            <td>
                                {{ (offer.price * offer.quantity).toLocaleString('ru') }} ₸
                            </td>
                        </tr>
                        <tr v-for="offer in payment.service_offers" :key="offer.id">
                            <td class="">
                                {{ offer.application_service.category }}
                            </td>
                            <td class="">
                                {{ offer.application_service.service }}
                            </td>
                            <td>
                                {{ offer.quantity }} {{ offer.application_service.unit }}
                            </td>
                            <td>
                                {{ offer.price.toLocaleString('ru') }} ₸
                            </td>
                            <td>
                                {{ (offer.price * offer.quantity).toLocaleString('ru') }} ₸
                            </td>
                        </tr>

                    </tbody>
                </v-table>
                </td>
                </tr>
</template>
</template>


<tr v-if="payments.length > 0">
    <td colspan="4" class="text-right">ИТОГ</td>
    <td>{{ payments.reduce((acc, i) => acc + i.amount, 0).toLocaleString('ru') }} ₸</td>
    <td>{{ payments.reduce((acc, i) => acc + i.paid, 0).toLocaleString('ru') }} ₸</td>
    <td>{{ payments.reduce((acc, i) => acc + i.amount - i.paid, 0).toLocaleString('ru') }} ₸</td>
    <td colspan="2"></td>
</tr>
</tbody>
</v-table>

<v-btn v-if="this.currentUser != null && this.currentUser.roles[0].title == 'Vice President'" class="mt-5"
    @click="updatePayments" color="primary">
    Отправить на оплату
</v-btn>
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
import Multiselect from 'vue-multiselect'

export default {
    components: {
        Multiselect,
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

    mounted() {
        // this.getApplications('draft')
        this.getCurrentUser();

        this.getObjects();
        // this.getPayments()
    },

    methods: {
        isEconomist() {
            return this.currentUser.roles[0].title === 'Economist';
        },

        getCurrentUser() {
            axios.get('/api/v1/me').then((response) => {
                this.currentUser = response.data
                // console.log(this.currentUser)
            })
        },

        getObjects() {
            axios.get('/api/v1/constructions').then((response) => {
                console.log(response.data);
                this.objects = response.data.data;
            });
        },

        getPayments(construction) {
            console.log('get payments for ', construction);

            // get applications 
            axios.get(`/api/v1/payments?construction_id=${construction.id}`).then((response) => {
                this.payments = response.data.data
                console.log('payments', this.payments)
            });
        },

        updatePayments() {
            var data = [];

            for (var i = 0; i < this.payments.length; i++) {
                var payment = this.payments[i];

                data.push({
                    'id': payment.id,
                    'to_be_paid': payment.order_paid,
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

        showOffers(payment) {
            var offersTable = document.getElementById('offers_' + payment.id);
            offersTable.style.display = offersTable.style.display == 'none' ? 'table-row' : 'none';
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
            // this.rowData.value = [];
            this.getPayments(c);
        }

    },

    watch: {

        // chosenObject(newVal) {
        //     if (!newVal) return;

        //     this.payments = [];
        //     this.getPayments(newVal);
        // }

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
