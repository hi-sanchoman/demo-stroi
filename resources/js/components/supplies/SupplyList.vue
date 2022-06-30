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
                    <h2>Накопители</h2>
                </v-col>

                <v-col cols="12" class="pl-5">
                    <v-table transition="slide-x-transition">
                        <thead>
                            <tr>
                                <th class="text-left">
                                    №
                                </th>
                                <th class="text-left">
                                    Наименование ресурса
                                </th>
                                <th class="text-left">
                                    Статья расходов
                                </th>
                                <th class="text-left">
                                    Ед. изм.
                                </th>
                                <th>
                                    Кол-во
                                </th>
                                <th>
                                    По плану
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="inventories.length <= 0">
                                <td colspan="5">Нет данных.</td>
                            </tr>

                            <tr
                                v-for="(inventory, index) in inventories"
                                :key="inventory.item.id"
                                style="cursor:pointer"
                                @click="showPosition(inventory)"
                            >
                                <td>{{ index + 1 }}</td>
                                <td>{{ inventory.item.application_product.product.name }}</td>
                                <td>{{ inventory.item.application_product.product.categories[0].name }}</td>
                                <td>{{ inventory.item.application_product.product.unit }}</td>
                                <td>{{ inventory.total }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-col>
            </v-row>    
        </v-container>


        <!-- inventory history dialog -->
        <v-dialog
            v-model="historyDialog"
        >
            <v-card>
                <v-card-title>
                    <span class="text-h5">История "{{ history.item.application_product.product.name }}"</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row no-gutters class="">

                            <v-col cols="12" class="">
                                <v-table transition="slide-x-transition">
                                    <thead>
                                        <tr>
                                            <th class="text-left">
                                                №
                                            </th>
                                            <th class="text-left">
                                                Наименование ресурса
                                            </th>
                                            <th class="text-left">
                                                Статья расходов
                                            </th>
                                            <th class="text-left">
                                                Ед. изм.
                                            </th>
                                            <th>
                                                Кол-во
                                            </th>
                                            <th>
                                                Дата поступления на склад
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="historyInventories.length <= 0">
                                            <td colspan="5">Нет данных.</td>
                                        </tr>

                                        <tr
                                            v-for="(inventory, index) in historyInventories"
                                            :key="inventory.id"
                                        >
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ inventory.application_product.product.name }}</td>
                                            <td>{{ inventory.application_product.product.categories[0].name }}</td>
                                            <td>{{ inventory.application_product.product.unit }}</td>
                                            <td>{{ inventory.quantity }}</td>
                                            <td>{{ inventory.application_product.updated_at }}</td>
                                        </tr>
                                    </tbody>
                                </v-table>
                            </v-col>
                        </v-row>    
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>


    </div>
</template>

<script>
import axios from 'axios'

export default {
    components: {
    },

    data() {
        return {
            inventories: [],
            currentUser: null,
            historyDialog: false,
            history: null,
            historyInventories: [],
        } 
    },
    
    methods: {
        getCurrentUser() {
            axios.get('/api/v1/me').then((response) => {
                this.currentUser = response.data
                // console.log(this.currentUser)
            })
        },

        showPosition(item) {
            this.historyDialog = true
            this.history = item
            this.historyInventories = []

            this.getHistoryInventories()
        },

        getSupplies() {
            // get applications 
            axios.get('/api/v1/supplies').then((response) => {
                this.inventories = response.data
            })
        },

        getHistoryInventories() {
            console.log(this.history)

            axios.get('/api/v1/history-supplies/' + this.history.item.application_product.product.id).then((response) => {
                this.historyInventories = response.data
            })
        }

    },

    mounted() {
        // this.getApplications('draft')
        this.getSupplies()
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