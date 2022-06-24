<template>
    <div>
        <v-container>
            <!-- <v-row no-gutters>
                <v-col cols="12">
                    <h1 class="w-full text-left">Заявки</h1>
                </v-col>
            </v-row> -->

            <v-row no-gutters class="mt-10">
                <v-col cols="2" class="border px-5 py-5">
                    <ApplicationSidebar v-if="currentUser != null" :currentUser="currentUser" />
                </v-col>

                <v-col cols="10" class="pl-5">
                    <div v-if="errors">
                        <div v-for="(v, k) in errors" :key="k" class="bg-red-500 text-white rounded font-bold mb-4 shadow-lg py-2 px-4 pr-0">
                            <p v-for="error in v" :key="error" class="text-sm">
                                    {{ error }}
                            </p>
                        </div>
                    </div>

                    <v-form class="space-y-6">
                        <v-row
                            v-if="application != null && application.status != 'draft'"
                            class="mb-5"
                        >
                            <v-col cols="12">
                                <strong>Заявка №{{ application.id }}</strong><br>
                                Дата заявки: {{ application.issued_at }}
                            </v-col>
                        </v-row>

                        <multiselect 
                            v-model="form.construction" 
                            :options="constructions" 
                            placeholder="Укажите строительный объект" 
                            :disabled="application != null && application.status != 'draft'"
                            label="name" 
                            track-by="name">
                        </multiselect>

                        <v-row 
                            v-if="application != null && application.status == 'draft'"
                            class="mt-5"
                        >
                            <v-col cols="12">Добавить товар к заявке:</v-col>
                        </v-row>

                        <v-row v-if="application != null && application.status == 'draft'">
                            <v-col cols="3">
                                <multiselect v-model="current.category" :options="categories" placeholder="Укажите категорию" label="name" track-by="name"></multiselect>
                            </v-col>

                            <v-col cols="3">
                                <multiselect v-model="current.product" :options="options" placeholder="Укажите товар" label="name" track-by="name"></multiselect>
                            </v-col>

                            <v-col cols="2">
                                <v-text-field
                                    v-model="current.quantity"
                                    label="Количество"
                                    @keyup.enter="addProduct()"
                                    variant="underlined"
                                    required
                                    density="comfortable"
                                    type="number"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="2">
                                <v-textarea
                                    v-if="current.isAddNotes"
                                    outlined
                                    v-model="current.notes"
                                    label="Примечание"
                                    variant="underlined"
                                    density="comfortable"
                                    @keyup.enter="addProduct()"
                                ></v-textarea>
                                <v-btn
                                    v-if="!current.isAddNotes"
                                    color=""
                                    size="small"
                                    @click="showNotes()"
                                >
                                    + примечание
                                </v-btn>
                            </v-col>
                            
                            <v-col cols="2">
                                <v-btn 
                                    size="small"
                                    color="primary"
                                    @click="addProduct()"
                                >
                                    <v-icon>mdi-plus</v-icon>
                                </v-btn>
                            </v-col>

                            
                        </v-row>

                        <v-table>
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Статья расходов</th>
                                    <th>Наименование ресурсов</th>
                                    <th>Ед. изм.</th>
                                    <th>Кол-во</th>
                                    <th>Цена</th>
                                    <th>Сумма</th>
                                    <th>Компания</th>
                                    <th v-if="isCanPrepareQuantity()">Подготовлено</th>
                                    <th v-if="isCanReceiveQuantity()">Получено</th>
                                    <th>Примечание</th>
                                    <th>Файлы</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="(item, index) in products" :key="item.id">
                                    <td>{{ item.id }}</td>
                                    <td>{{ item.category.name }}</td>
                                    <td>{{ item.product.name }}</td>
                                    <td>{{ item.product.unit }}</td>
                                    <td :class="application.status == 'draft' ? 'd-flex mt-3' : ''">
                                        <v-text-field
                                            v-model="products[index].quantity"
                                            type="number"
                                            variant="plain"
                                            density="compact"
                                            v-if="application.status == 'draft'"
                                        >
                                        </v-text-field>

                                        <span v-if="application.status != 'draft'">{{ products[index].quantity }}</span>
                                    </td>
                                    <td>
                                        <v-text-field
                                            v-model="products[index].price"
                                            type="number"
                                            density="compact"
                                            class="w-10"
                                            variant="underlined"
                                            style="width: 50px"
                                            v-if="application.status == 'in_progress'"
                                        >
                                        </v-text-field>

                                        <!-- <span v-if="application.status != 'draft'">{{ products[index].price }}</span> -->
                                    </td>

                                    <td>
                                        {{ countSum(products[index]) }}
                                    </td>

                                    <td class="w-36">
                                        <v-text-field
                                            v-model="products[index].company"
                                            type="text"
                                            density="compact"
                                            class="w-10"
                                            variant="underlined"
                                            style="width: 50px"
                                            v-if="application.status == 'in_progress'"
                                        >
                                        </v-text-field>
                                    </td>

                                    <td v-if="isCanPrepareQuantity()" class="d-flex mt-3 justify-center" s>
                                        <v-text-field
                                            v-model="products[index].prepared"
                                            type="number"
                                            density="compact"
                                            class="w-10"
                                            variant="underlined"
                                            style="width: 50px"
                                            v-if="application.status == 'in_progress'"
                                        >
                                        </v-text-field>

                                        <v-btn
                                            size="x-small"
                                            color="green"
                                            class="ml-1"
                                            @click="prepareQuantity(item)"
                                        >
                                            <v-icon>mdi-check</v-icon>
                                        </v-btn>

                                        <!-- <span v-if="item.quantity == item.prepared">{{ item.prepared }}</span> -->
                                    </td>  
                                    <td v-if="isCanReceiveQuantity()" class="">
                                        <span>{{ item.delivered }}</span>    

                                        <v-btn
                                            v-if="item.delivered != item.quantity"
                                            size="x-small"
                                            color="primary"
                                            class="ml-3"
                                            @click="showReceiveDialog(item)"
                                        >
                                            <v-icon>mdi-plus</v-icon>
                                        </v-btn>
                                    </td> 
                                    <td>{{ item.notes }}</td>
                                    <td class="d-flex">
                                        <!-- FILES -->

                                        <!-- UPLOAD -->
                                        <input
                                            v-if="application.status == 'in_progress'"
                                            type="file"
                                            :ref="'doc_' + item.id"
                                            class="
                                                w-full
                                                px-4
                                                py-2
                                                mt-2
                                                border
                                                rounded-md
                                                focus:outline-none
                                                focus:ring-1
                                                focus:ring-blue-600
                                            "
                                        />

                                        <v-btn
                                            v-if="application.status == 'in_progress'"
                                            size="x-small"
                                            color="primary"
                                            class="ml-3"
                                            @click="uploadFile(item)"
                                        >
                                            Загрузить
                                        </v-btn>
                                    </td>
                                    <td>
                                        <v-btn 
                                            v-if="application.status == 'draft'"
                                            size="small"
                                            color="error"
                                            @click="deleteProduct(item)"
                                        >
                                            <v-icon>mdi-close</v-icon>
                                        </v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>

                        <v-btn
                            v-if="form.construction != null && products.length > 0 && (application.status == 'draft' || application.status == 'in_progress')"
                            class="mt-5"
                            @click="updateApplication"
                            color="primary"
                        >
                            Сохранить
                        </v-btn>
                    </v-form>

                    <v-container class="mt-10" v-if="application != null">
                        <v-row 
                            v-if="currentUser != null && application.status == 'declined' && application.owner_id == currentUser.id"
                            class="my-5"
                            no-gutters
                        >
                            <v-col cols="12">
                                <v-btn
                                    size="large"
                                    color="primary"
                                    @click="makeApplicationEditable()"
                                    class="ml-5"
                                >
                                    Внести корректировки в заявку
                                </v-btn>
                            </v-col>
                        </v-row>
                        
                        <v-row no-gutters>
                            <v-col cols="12">
                                <v-expansion-panels v-model="showPanels">
                                    <v-expansion-panel value="sign">
                                        <v-expansion-panel-title>
                                            Подписание заявки
                                        </v-expansion-panel-title>

                                        <v-expansion-panel-text>
                                            <v-list-item
                                                v-for="item in application.application_application_statuses" :key="item.id"
                                            >
                                                <v-list-item-header>
                                                    <v-list-item-title>
                                                        <strong>{{ item.application_path.id }}. {{ item.application_path.position }}</strong> - {{ item.application_path.responsible.name }}  

                                                        <!-- declined -->
                                                        <span v-if="item.status == 'declined'">
                                                            <v-chip
                                                                class="ma-2"
                                                                color="red"
                                                                text-color="white"
                                                            >
                                                                Отклонено
                                                            </v-chip>

                                                            <span>{{ item.declined_reason }}</span> -    
                                                            <span>{{ item.updated_at }}</span>
                                                        </span>
                                                        <!-- accepted -->
                                                        <span v-else-if="item.status == 'accepted'">
                                                            <v-chip
                                                                class="ma-2"
                                                                color="green"
                                                                text-color="white"
                                                            >
                                                                Одобрено
                                                            </v-chip>

                                                            <span>{{ item.updated_at }}</span>
                                                        </span>
                                                        <!-- incoming -->
                                                        <span v-else-if="currentUser != null && item.status == 'incoming' && item.application_path.responsible.id == currentUser.id">
                                                            <v-btn
                                                                size="small"
                                                                color="success"
                                                                @click="signApplication(item)"
                                                                class="ml-5"
                                                            >
                                                                Подписать
                                                            </v-btn>

                                                            <v-btn
                                                                v-if="item.application_path.responsible.id != application.owner_id"
                                                                size="small"
                                                                color="error"
                                                                @click="showDecline(item)"
                                                                class="ml-5"
                                                            >
                                                                Отклонить
                                                            </v-btn>
                                                        </span>

                                                    </v-list-item-title>
                                                </v-list-item-header>
                                            </v-list-item>
                                        </v-expansion-panel-text>
                                    </v-expansion-panel>
                                </v-expansion-panels>
                            </v-col>
                        </v-row>
                    </v-container>

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
        

        <!-- Decline dialog -->
        <v-dialog
            v-model="declineDialog"
            persistent
        >
            <v-card>
                <v-card-title>
                    <span class="text-h5">Отклонить заявку?</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col
                                cols="12"
                            >
                                <v-textarea
                                    v-model="declinedReason"
                                    label="Причина отклонения*"
                                    required
                                ></v-textarea>
                            </v-col>
                        </v-row>
                    </v-container>
                    <small>* обязательные поля</small>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    
                    <v-btn
                        color="blue-darken-1"
                        text
                        @click="declineDialog = false"
                    >
                        Отмена
                    </v-btn>

                    <v-btn
                        color="red-darken-1"
                        text
                        @click="declineApplication()"
                    >
                        Отклонить
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog
            v-model="receiveDialog"
            persistent
        >
            <v-card>
                <v-card-title>
                    <span class="text-h5">Получение материально-товарных ценностей</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col
                                cols="12"
                            >
                                <v-text-field
                                    v-model="current.delivered"
                                    label="Количество"
                                    variant="outlined"
                                    type="number"
                                    required
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-container>
                    <small>* обязательные поля</small>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    
                    <v-btn
                        color=""
                        text
                        @click="receiveDialog = false"
                    >
                        Отмена
                    </v-btn>

                    <v-btn
                        color="blue-darken-1"
                        text
                        @click="acceptDelivery()"
                    >
                        Принять
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>
</template>


<script>
import { onMounted, reactive, ref } from "vue";
import useConstructions from "../../composables/constructions"
import useApplications from "../../composables/applications"
import Multiselect from 'vue-multiselect'
import axios from 'axios'
import ApplicationSidebar from "./ApplicationSidebar.vue";

export default {
    components: {
        Multiselect,
        ApplicationSidebar,
    },

    inject: ['isLoading'],

    data() {
        return {
            snackbar: {
                status: false,
                text: '',
                timeout: 2000,
            },
            current: {
                product: null,
                category: null,
                quantity: null,
                notes: null,
                isAddNotes: false,
                delivered: null,
            },
            options: [],
            categories: [],
            products: [],
            form: {
                // 'issued_at': new Date(),
                'construction': null,
                'is_urgent': false,
                'kind': 'acquisition_of_inventory',
            },
            constructions: [],
            application: null,
            currentUser: null,

            declinedApplicationStatus: null,
            declineDialog: false,
            declinedReason: '',

            showPanels: ['sign'],

            receiveDialog: false,
            receiveProduct: null,
        }
    },

    mounted() {
        this.isLoading = true

        // get current user
        this.getCurrentUser()

        // get products -> async autocomplete TODO!
        axios.get('/api/v1/products').then((response) => {
            response.data.data.forEach((item) => {
                this.options.push(item)
            })
            
            // this.isLoading = false
        })

        // get categories
        axios.get('/api/v1/categories').then((response) => {
            response.data.data.forEach((item) => {
                this.categories.push(item)
            })
            
            // this.isLoading = false
        })

        // get constructions
        axios.get('/api/v1/constructions').then((response) => {
            this.constructions = response.data.data
        })

        // get application
        this.getApplication()
    },

    methods: {
        showNotes() {
            this.current.isAddNotes = true
        },

        getCurrentUser() {
            axios.get('/api/v1/me').then((response) => {
                this.currentUser = response.data

                if (this.currentUser.roles[0].title == 'Supplier' || this.currentUser.roles[0].title == 'Warehouse Manager') {
                    this.showPanels = []
                }
            })
        },

        getApplication() {
            axios.get('/api/v1/applications/' + this.$route.params.id).then((response) => {
                this.application = response.data.data

                this.products = this.application.application_application_products

                this.form.construction = this.application.construction
                this.form.kind = this.application.kind
            })
        },  

        addProduct() {
            // console.log('add product', this.current.product.name, this.current.quantity, this.current.notes)
            if (this.current.quantity == null) {
                return;
            }

            this.products.push({
                id: this.products.length + 1,
                product: this.current.product,
                category: this.current.category,
                quantity: this.current.quantity,
                notes: this.current.notes,
            })

            this.current = {
                product: null,
                category: null,
                quantity: null,
                notes: null,
                isAddNotes: false
            }
        },

        deleteProduct(item) {
            this.products = this.products.filter(function(el) { return el.id != item.id })
        },

        updateApplication() {
            this.form.products = this.products
            this.form.construction_id = this.form.construction.id

            console.log(this.form)

            try {
                axios.put(`/api/v1/applications/${this.application.id}`, this.form).then((response) => {
                    this.snackbar.text = 'Заявка успешно обновлена.'
                    this.snackbar.status = true
                })
            } catch (e) {
                console.log(e)

                if (e.response.status === 422) {
                    // errors.value = e.response.data.errors
                }

                this.snackbar.text = 'Ошибка.'
                this.snackbar.status = true
            }
        },

        signApplication(applicationStatus) {
            var data = { 'method': 'sign' }

            axios.put('/api/v1/application-statuses/' + applicationStatus.id, data).then((response) => {
                this.getApplication()

                this.snackbar.text = 'Заявка успешно подписана.'
                this.snackbar.status = true
            })
        },

        showDecline(applicationStatus) {
            this.declineDialog = true
            this.declinedApplicationStatus = applicationStatus
        },

        declineApplication() {
            var data = { 'method': 'decline', 'declined_reason': this.declinedReason }

            axios.put('/api/v1/application-statuses/' + this.declinedApplicationStatus.id, data).then((response) => {
                this.getApplication()

                this.snackbar.text = 'Заявка отклонена.'
                this.snackbar.status = true

                this.declineDialog = false
                this.declinedApplicationStatus = null
                this.declinedReason = ''
            })
        },

        makeApplicationEditable() {
            var data = { 
                'status': 'draft', 
                'products': this.products, 
                'construction_id': this.application.construction_id,
                'kind': 'acquisition_of_inventory'
            }

            axios.put('/api/v1/applications/' + this.application.id, data).then((response) => {
                this.getApplication()

                this.snackbar.text = 'Теперь заявку можно редактировать'
                this.snackbar.status = true
            })
        },

        isCanPrepareQuantity() {
            return this.currentUser != null && this.currentUser.roles[0].title == 'Supplier';
        },

        isCanReceiveQuantity() {
            return this.currentUser != null && this.currentUser.roles[0].title == 'Warehouse Manager';
        },

        prepareQuantity(product) {
            product.mode = 'prepare'

            axios.put('/api/v1/application-products/' + product.id, product).then((response) => {
                this.getApplication()

                this.snackbar.text = 'Ценностно-материальная позиция обновлена.'
                this.snackbar.status = true
            })
        },

        showReceiveDialog(item) {
            this.receiveDialog = true
            this.receiveProduct = item
        },  

        acceptDelivery() {
            this.receiveProduct.delivered = this.current.delivered
            this.receiveProduct.mode = 'receive'

            axios.put('/api/v1/application-products/' + this.receiveProduct.id, this.receiveProduct).then((response) => {
                this.getApplication()

                this.snackbar.text = 'Ценностно-материальная позиция обновлена.'
                this.snackbar.status = true

                this.receiveDialog = false
                this.receiveProduct = null
                this.current.delivered = null
            })
        },

        countSum(product) {
            return product.quantity * product.price
        },

        uploadFile(item) {
            var key = 'doc_' + item.id
            console.log(key, this.$refs, this.$refs[0], this.$refs[key])
        },
    },  
}
</script>