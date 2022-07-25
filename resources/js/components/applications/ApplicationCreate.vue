<template>
    <div>
        <v-container>
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
                            <v-col cols="12">Добавить товар к заявке:</v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="12" md="3">
                                <multiselect v-model="current.category" :options="categories"
                                    placeholder="Укажите категорию" label="name" track-by="name"></multiselect>
                            </v-col>

                            <v-col cols="12" md="3">
                                <multiselect v-model="current.product" :options="options" placeholder="Укажите товар"
                                    label="name" track-by="name"></multiselect>
                            </v-col>

                            <v-col cols="12" md="2">
                                <multiselect v-model="current.unit" :options="units" placeholder="Ед. изм." label="name"
                                    track-by="name"></multiselect>
                            </v-col>

                            <v-col cols="12" md="1">
                                <v-text-field v-model="current.quantity" label="Количество" @keyup.enter="addProduct()"
                                    variant="underlined" required density="comfortable" type="number"></v-text-field>
                            </v-col>

                            <v-col cols="12" md="2">
                                <v-textarea v-if="current.isAddNotes" outlined v-model="current.notes"
                                    label="Примечание" variant="underlined" density="comfortable"
                                    @keyup.enter="addProduct()"></v-textarea>
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

                        <v-table>
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
                                    <td>{{ item.category.name }}</td>
                                    <td>{{ item.product.name }}</td>
                                    <td>{{ item.unit.name }}</td>
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

                        <v-btn v-if="form.construction != null && products.length > 0" class="mt-5"
                            @click="saveApplication" color="primary">
                            Создать
                        </v-btn>
                    </v-form>
                </v-col>
            </v-row>

        </v-container>
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
                category: null,
                unit: null,
                quantity: null,
                notes: null,
                isAddNotes: false,
            },
            options: [],
            categories: [],
            products: [],
            units: [],
            form: {
                // 'issued_at': new Date(),
                'construction': null,
                'is_urgent': false,
                kind: this.$route.query.kind ?? 'product'
            },
            constructions: [],
            currentUser: null,
            oldKind: null,
        }
    },

    mounted() {
        this.isLoading = true

        // get current user
        this.getCurrentUser();

        // get products -> async autocomplete TODO!
        axios.get('/api/v1/products').then((response) => {
            response.data.data.forEach((item) => {
                // console.log(item)
                this.options.push(item)
            });

            // this.isLoading = false
        });

        // get categories
        axios.get('/api/v1/categories').then((response) => {
            response.data.data.forEach((item) => {
                // console.log(item)
                this.categories.push(item)
            });

            // this.isLoading = false
        });

        // get units
        axios.get('/api/v1/units').then((response) => {
            response.data.data.forEach((item) => {
                // console.log(item)
                this.units.push(item)
            });

            // this.isLoading = false
        });

        // get constructions
        axios.get('/api/v1/constructions').then((response) => {
            this.constructions = response.data.data
        });
    },

    methods: {
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

            this.products.push({
                id: this.products.length + 1,
                product: this.current.product,
                unit: this.current.unit,
                category: this.current.category,
                quantity: this.current.quantity,
                notes: this.current.notes,
            })

            this.current = {
                product: null,
                unit: null,
                category: null,
                quantity: null,
                notes: null,
                isAddNotes: false
            }
        },

        deleteProduct(item) {
            this.products = this.products.filter(function (el) { return el.id != item.id })
        },

        saveApplication() {
            this.form.products = this.products
            this.form.construction_id = this.form.construction.id

            console.log(this.form)

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