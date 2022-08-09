<template>
    <div class="" style="padding: 20px;">
        <!-- <v-container> -->
        <!-- <v-row no-gutters>
                <v-col cols="12">
                    <h1 class="w-full text-left">Заявки</h1>
                </v-col>
            </v-row> -->

        <v-row no-gutters class="mt-10">
            <v-col cols="12" md="3" class="border px-5 py-5">
                <ApplicationSidebar v-if="currentUser != null" :currentUser="currentUser" />
            </v-col>

            <v-col cols="12" md="9" class="pl-0 pl-md-5 mt-4 mt-md-0">
                <div v-if="errors">
                    <div v-for="(v, k) in errors" :key="k"
                        class="bg-red-500 text-white rounded font-bold mb-4 shadow-lg py-2 px-4 pr-0">
                        <p v-for="error in v" :key="error" class="text-sm">
                            {{ error }}
                        </p>
                    </div>
                </div>

                <v-form class="space-y-6">
                    <multiselect v-model="form.construction" :options="constructions"
                        placeholder="Укажите строительный объект" label="name" track-by="name">
                    </multiselect>

                    <v-row class="mt-5">
                        <v-col v-if="form.kind == 'product'" cols="12">Добавить товар к заявке:</v-col>
                        <v-col v-else-if="form.kind == 'equipment'" cols="12">Добавить спец. технику к заявке:
                        </v-col>
                        <v-col v-else-if="form.kind == 'service'" cols="12">Добавить услугу к заявке:</v-col>
                    </v-row>

                    <v-row>
                        <template v-if="form.kind == 'product'">
                            <v-col cols="12" md="3">
                                <multiselect v-model="current.category" :options="categories"
                                    placeholder="Укажите категорию" label="name" track-by="name"></multiselect>
                            </v-col>

                            <v-col cols="12" md="3">
                                <multiselect v-model="current.product" placeholder="Укажите товар" label="name"
                                    track-by="name" :options="options" :multiple="false" :searchable="true"
                                    :loading="isLoading" @search-change="asyncFind" :selectLabel="''"
                                    :deselectLabel="''">
                                </multiselect>
                            </v-col>

                            <v-col cols="12" md="2">
                                <multiselect v-model="current.unit" :options="units" placeholder="Ед. изм." label="name"
                                    track-by="name" :selectLabel="''" :deselectLabel="''">
                                </multiselect>
                            </v-col>
                        </template>

                        <!-- Equipment -->
                        <template v-else-if="form.kind == 'equipment'">
                            <v-col cols="12" md="4">
                                <multiselect v-model="current.equipment" :options="equipmentOptions"
                                    placeholder="Укажите спец. технику" label="name" track-by="name"></multiselect>
                            </v-col>

                            <!-- <v-col cols="12" md="2">
                                    <v-text-field v-model="current.days" label="Срок (дней)" variant="underlined"
                                        required density="comfortable" type="number"></v-text-field>
                                </v-col> -->

                            <v-col cols="12" md="2">
                                <multiselect v-model="current.unit" :options="units" placeholder="Ед. изм." label="name"
                                    track-by="name" :selectLabel="''" :deselectLabel="''">
                                </multiselect>
                            </v-col>
                        </template>

                        <!-- Service -->
                        <template v-else-if="form.kind == 'service'">
                            <v-col cols="12" md="2">
                                <multiselect v-model="current.category" :options="categories"
                                    placeholder="Укажите категорию" label="name" track-by="name"></multiselect>
                            </v-col>

                            <!-- <v-col cols="12" md="3">
                                    <v-text-field v-model="current.category" label="Напишите категорию"
                                        variant="underlined" required density="comfortable" type="text"></v-text-field>
                                </v-col> -->

                            <v-col cols="12" md="3">
                                <v-text-field v-model="current.service" label="Напишите услугу" variant="underlined"
                                    required density="comfortable" type="text"></v-text-field>
                            </v-col>

                            <!-- <v-col cols="12" md="2">
                                    <v-text-field v-model="current.unit" label="Ед. изм." variant="underlined" required
                                        density="comfortable" type="text"></v-text-field>
                                </v-col> -->
                            <v-col cols="12" md="2">
                                <multiselect v-model="current.unit" :options="units" placeholder="Ед. изм." label="name"
                                    track-by="name" :selectLabel="''" :deselectLabel="''">
                                </multiselect>
                            </v-col>

                            <v-col cols="12" md="1">
                                <v-text-field v-model="current.price" label="Цена" variant="underlined" required
                                    density="comfortable" type="number"></v-text-field>
                            </v-col>
                        </template>

                        <!-- common -->
                        <v-col cols="12" md="1">
                            <v-text-field v-model="current.quantity" label="Кол-во" @keyup.enter="addProduct()"
                                variant="underlined" required density="comfortable" type="number"></v-text-field>
                        </v-col>

                        <v-col cols="12" md="2">
                            <v-textarea v-if="current.isAddNotes" outlined v-model="current.notes" label="Примечание"
                                variant="underlined" density="comfortable" @keyup.enter="addProduct()"></v-textarea>
                            <v-btn v-if="!current.isAddNotes" color="" size="small" @click="showNotes()">
                                + примечание
                            </v-btn>
                        </v-col>

                        <v-col cols="12" md="1">
                            <v-btn size="small" color="primary" @click="addProduct()">
                                <v-icon>mdi-plus</v-icon>
                            </v-btn>
                        </v-col>

                    </v-row>

                    <v-table v-if="form.kind == 'product'">
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Статья расходов</th>
                                <th>Наименование ресурсов</th>
                                <th>Ед. изм.</th>
                                <th>Кол-во</th>
                                <th>Примечание</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(item, index) in products" :key="item.id">
                                <td>{{ item.id }}</td>
                                <td>{{ item.category?.name }}</td>
                                <td>{{ item.product?.name }}</td>
                                <td>{{ item.unit?.name }}</td>
                                <td class="d-flex mt-3">
                                    <v-text-field v-model="products[index].quantity" type="number" variant="plain"
                                        density="compact">
                                    </v-text-field>
                                </td>
                                <td>{{ item.notes }}</td>
                                <td>
                                    <v-btn size="small" color="error" @click="deleteProduct(item)">
                                        <v-icon>mdi-close</v-icon>
                                    </v-btn>
                                </td>
                            </tr>
                        </tbody>
                    </v-table>

                    <v-table v-else-if="form.kind == 'equipment'">
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Наименование спец. техники</th>
                                <th>Ед. изм.</th>
                                <th>Кол-во</th>
                                <th>Примечание</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(item, index) in equipments" :key="item.id">
                                <td>{{ item.id }}</td>
                                <td>{{ item.equipment?.name }}</td>
                                <td>{{ item.unit?.name }}</td>
                                <td class="d-flex mt-3">
                                    <v-text-field v-model="equipments[index].quantity" type="number" variant="plain"
                                        density="compact">
                                    </v-text-field>
                                </td>
                                <td>{{ item.notes }}</td>
                                <td>
                                    <v-btn size="small" color="error" @click="deleteProduct(item)">
                                        <v-icon>mdi-close</v-icon>
                                    </v-btn>
                                </td>
                            </tr>
                        </tbody>
                    </v-table>

                    <v-table v-else-if="form.kind == 'service'">
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Статья расходов</th>
                                <th>Наименование ресурсов</th>
                                <th>Ед. изм.</th>
                                <th>Кол-во</th>
                                <th>Цена за ед.</th>
                                <th>Сумма</th>
                                <th>Примечание</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(item, index) in services" :key="item.id">
                                <td>{{ item.id }}</td>
                                <td>{{ item.category }}</td>
                                <td>{{ item.service }}</td>
                                <td>{{ item.unit }}</td>
                                <td>
                                    {{ item.quantity }}
                                </td>
                                <td>
                                    {{ item.price }}
                                </td>
                                <td>
                                    <template v-if="item.price">{{ item.price * item.quantity }}₸</template>
                                </td>
                                <td>{{ item.notes }}</td>
                                <td>
                                    <v-btn size="small" color="error" @click="deleteProduct(item)">
                                        <v-icon>mdi-close</v-icon>
                                    </v-btn>
                                </td>
                            </tr>
                        </tbody>
                    </v-table>

                    <v-btn
                        v-if="form.construction != null && (products.length > 0 || equipments.length > 0 || services.length > 0)"
                        class="mt-5" @click="saveApplication" color="primary">
                        Создать
                    </v-btn>
                </v-form>
            </v-col>
        </v-row>

        <!-- </v-container> -->
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
            current: {
                product: null,
                equipment: null,
                service: null,
                category: null,
                unit: null,
                quantity: null,
                notes: null,
                isAddNotes: false,
                days: null,
                price: null,
            },
            options: [],
            isLoading: false,
            categories: [],
            units: [],
            equipmentOptions: [],

            services: [],
            equipments: [],
            products: [],
            form: {
                // 'issued_at': new Date(),
                'construction': null,
                'is_urgent': false,
                kind: this.$route.query.kind ?? 'product',
                products: [],
                equipments: [],
                services: [],
            },
            constructions: [],
            currentUser: null,
            oldKind: null,
        }
    },

    mounted() {
        // this.isLoading = true

        // get current user
        this.getCurrentUser();

        // get products -> async autocomplete TODO!
        // axios.get('/api/v1/products').then((response) => {
        //     this.options = response.data.data;
        // });

        // get categories
        axios.get('/api/v1/categories').then((response) => {
            response.data.data.forEach((item) => {
                this.categories.push(item)
            });
        });

        // get units
        axios.get('/api/v1/units').then((response) => {
            response.data.data.forEach((item) => {
                this.units.push(item)
            });
        });

        // get constructions
        axios.get('/api/v1/constructions').then((response) => {
            this.constructions = response.data.data
        });

        // get equipments
        axios.get('/api/v1/equipments').then((response) => {
            this.equipmentOptions = response.data.data;
        });
    },

    methods: {
        asyncFind(query) {
            console.log(query);

            if (query.length < 2) return;

            this.isLoading = true

            axios.get('/api/v1/products?q=' + query).then(response => {
                this.options = response.data;
                this.isLoading = false
            })
        },
        getCurrentUser() {
            axios.get('/api/v1/me').then((response) => {
                this.currentUser = response.data;
            });
        },

        showNotes() {
            this.current.isAddNotes = true
        },

        addProduct() {
            // console.log('add product', this.current.product.name, this.current.quantity, this.current.notes)
            if (this.current.quantity == null) {
                return;
            }

            if (this.form.kind == 'product') {
                this.products.push({
                    id: this.products.length + 1,
                    product: this.current.product,
                    unit: this.current.unit,
                    category: this.current.category,
                    quantity: this.current.quantity,
                    notes: this.current.notes,
                });
            }

            else if (this.form.kind == 'equipment') {
                this.equipments.push({
                    id: this.equipments.length + 1,
                    equipment: this.current.equipment,
                    quantity: this.current.quantity,
                    notes: this.current.notes,
                    unit: this.current.unit,
                });

                // console.log(this.equipments);
            }

            else if (this.form.kind == 'service') {
                this.services.push({
                    id: this.services.length + 1,
                    service: this.current.service,
                    // unit: this.current.unit,
                    unit: this.current.unit.name,
                    price: this.current.price,
                    category: this.current.category.name,
                    quantity: this.current.quantity,
                    notes: this.current.notes,
                });

                // console.log(this.equipments);
            }

            this.current = {
                product: null,
                service: null,
                equipment: null,
                unit: null,
                category: null,
                quantity: null,
                notes: null,
                isAddNotes: false,
                days: null,
                price: null,
            }
        },

        deleteProduct(item) {
            if (this.form.kind == 'product') {
                this.products = this.products.filter(function (el) { return el.id != item.id })
            }

            else if (this.form.kind == 'equipment') {
                this.equipments = this.equipments.filter(el => el.id != item.id);
            }

            else if (this.form.kind == 'service') {
                this.services = this.services.filter(el => el.id != item.id);
            }
        },

        saveApplication() {
            this.form.construction_id = this.form.construction.id

            if (this.form.kind == 'product') {
                this.form.products = this.products
            } else if (this.form.kind == 'equipment') {
                this.form.equipments = this.equipments;
            } else if (this.form.kind == 'service') {
                this.form.services = this.services;
            }

            // console.log(this.form)
            // return;

            try {
                axios.post('/api/v1/applications', this.form).then((response) => {
                    var application = response.data.data
                    this.$router.push({ name: 'applications.edit', params: { id: application.id } })
                })
            } catch (e) {
                console.log(e)

                if (e.response.status === 422) {
                    // errors.value = e.response.data.errors
                }
            }
        }
    },

    watch: {
        '$route.query': {
            handler(newValue) {
                if (!this.oldKind) {
                    this.oldKind = newValue;
                    return;
                }

                location.reload();
            },
            immediate: true,
        }
    }
}
</script>