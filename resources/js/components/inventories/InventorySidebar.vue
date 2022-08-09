<template>
    <div>
        <v-list density="compact" transition="slide-x-transition">
            <v-list-subheader class="d-block">
                Склад

                <!-- <div class="float-right">
                    <v-btn
                        color="primary"
                        plain
                        @click="showMoveDialog()"
                    >
                        <v-icon>mdi-arrow-expand-horizontal</v-icon>
                    </v-btn>
                </div> -->
            </v-list-subheader>

            <!-- <router-link :to="'/inventories/' + inventory.id + '?status=waiting'" class="text-decoration-none text-black">
                <v-list-item
                    key="waiting"
                    value="waiting"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'Приход'"></v-list-item-title>
                </v-list-item>
            </router-link> -->

            <router-link :to="'/inventories/' + inventory.id + '/products/?status=accepted'"
                class="text-decoration-none text-black">
                <v-list-item key="products" value="products" active-color="primary">
                    <v-list-item-title v-text="'Товары'"></v-list-item-title>
                </v-list-item>
            </router-link>

            <router-link v-if="isSectionManager()" :to="'/inventories/' + inventory.id + '/equipment/?status=accepted'"
                class="text-decoration-none text-black">
                <v-list-item key="equipment" value="equipment" active-color="primary">
                    <v-list-item-title v-text="'Спец. техника'"></v-list-item-title>
                </v-list-item>
            </router-link>

            <router-link :to="'/inventories/' + inventory.id + '/services/?status=accepted'"
                class="text-decoration-none text-black">
                <v-list-item key="services" value="services" active-color="primary">
                    <v-list-item-title v-text="'Услуги'"></v-list-item-title>
                </v-list-item>
            </router-link>

            <v-list-item>
                <v-list-item-title>&nbsp;</v-list-item-title>
            </v-list-item>

            <v-list-item
                v-if="currentUser != null && (currentUser.roles[0].title == 'Warehouse Manager' || currentUser.roles[0].title == 'Foreman')"
                key="movement_inside" value="movement_inside" active-color="primary" @click="showMoveDialog()">
                <v-list-item-title class="text-xs md:text-sm" v-text="'Перемещение (бригадирам)'"></v-list-item-title>
            </v-list-item>

            <template v-if="currentUser != null && currentUser.roles[0].title == 'Warehouse Manager'">
                <v-list-item key="movement_foreman" value="movement_foreman" active-color="primary"
                    @click="showMoveToForemanDialog()">
                    <v-list-item-title class="text-xs md:text-sm" v-text="'Перемещение (прорабам)'"></v-list-item-title>
                </v-list-item>

                <v-list-item key="movement_outside" value="movement_outside" active-color="primary"
                    @click="showMoveOutsideDialog()">
                    <v-list-item-title v-text="'Перемещение (объекты)'"></v-list-item-title>
                </v-list-item>

            </template>

            <v-list-item>
                <v-list-item-title>&nbsp;</v-list-item-title>
            </v-list-item>

            <router-link :to="'/inventories/' + inventory.id + '/history'" class="text-decoration-none text-black">
                <v-list-item key="history" value="history" active-color="primary">
                    <v-list-item-title v-text="'История'"></v-list-item-title>
                </v-list-item>
            </router-link>
        </v-list>
    </div>

    <!-- Move Inside Dialog -->
    <v-dialog v-model="moveDialog" persistent>
        <v-card class="oks-dialog min-w-5xl w-7xl" style="">
            <v-card-title>
                <span class="text-h5">Перемещение товара</span>
            </v-card-title>

            <v-card-text>
                <v-container>
                    <v-row>
                        <v-col cols="12">
                            <multiselect v-model="move.product" :options="stocks" placeholder="Укажите товар"
                                label="name" track-by="id" @select="onProductSelect">
                            </multiselect>

                            <v-text-field class="mt-4" v-model="move.where" label="Укажите получателя"
                                variant="underlined" required density="comfortable"></v-text-field>

                            <v-text-field class="mt-2" v-model="move.quantity" label="Количество" variant="underlined"
                                required density="comfortable" type="number" @keyup.enter="move()"></v-text-field>

                            <span class="mt-2">Ед. изм.: {{ move.unit }}</span>

                        </v-col>
                    </v-row>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>

                <v-btn color="default" text @click="moveDialog = false">
                    Отмена
                </v-btn>

                <v-btn color="success" text @click="moveIt()">
                    Переместить
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <!-- Move Foreman Dialog -->
    <v-dialog v-model="moveForemanDialog" persistent>
        <v-card class="oks-dialog min-w-5xl w-7xl" style="">
            <v-card-title>
                <span class="text-h5">Перемещение товара</span>
            </v-card-title>

            <v-card-text>
                <v-container>
                    <v-row>
                        <v-col cols="12">
                            <multiselect v-model="moveForeman.product" :options="stocks" placeholder="Укажите товар"
                                label="name" track-by="id" @select="onProductSelect">
                            </multiselect>

                            <multiselect class="mt-2" v-model="moveForeman.where" :options="foremans"
                                placeholder="Укажите прораба" label="name" track-by="id">
                            </multiselect>

                            <v-text-field class="mt-2" v-model="moveForeman.quantity" label="Количество"
                                variant="underlined" required density="comfortable" type="number"
                                @keyup.enter="moveToForeman()"></v-text-field>

                            <span class="mt-2">Ед. изм.: {{ moveForeman.unit }}</span>

                        </v-col>
                    </v-row>
                </v-container>
                <!-- <small>* обязательные поля</small> -->
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>

                <v-btn color="default" text @click="moveForemanDialog = false">
                    Отмена
                </v-btn>

                <v-btn color="success" text @click="moveToForeman()">
                    Переместить
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <!-- Move Outside Dialog -->
    <v-dialog v-model="moveOutsideDialog" persistent>
        <v-card class="oks-dialog min-w-5xl w-7xl" style="">
            <v-card-title>
                <span class="text-h5">Перемещение товара</span>
            </v-card-title>

            <v-card-text>
                <v-container>
                    <v-row>
                        <v-col cols="12">
                            <multiselect v-model="moveOutside.product" :options="stocks" placeholder="Укажите товар"
                                label="name" track-by="id" @select="onProductSelect">
                            </multiselect>

                            <!-- <multiselect  
                                class="mt-2"
                                v-model="move.where" :options="foremans" placeholder="Укажите бригадира" label="name" track-by="id">
                            </multiselect> -->

                            <multiselect class="mt-4" v-model="moveOutside.where" :options="inventories"
                                placeholder="Укажите склад" label="name" track-by="id">
                            </multiselect>

                            <v-text-field class="mt-4" v-model="moveOutside.quantity" label="Количество"
                                variant="underlined" required density="comfortable" type="number"
                                @keyup.enter="moveOutsideIt()"></v-text-field>

                            <span class="mt-2">Ед. изм.: {{ moveOutside.unit }}</span>

                        </v-col>
                    </v-row>
                </v-container>
                <!-- <small>* обязательные поля</small> -->
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>

                <v-btn color="default" text @click="moveOutsideDialog = false">
                    Отмена
                </v-btn>

                <v-btn color="success" text @click="moveOutsideIt()">
                    Переместить
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>

import Multiselect from 'vue-multiselect'
import axios from 'axios'

export default {
    props: ['currentUser', 'inventory'],

    components: {
        Multiselect,
    },

    data() {
        return {
            moveDialog: false,
            move: {
                product: null,
                where: null,
                quantity: 0,
                unit: null,
                max: 0,
            },

            moveOutsideDialog: false,
            moveOutside: {
                product: null,
                unit: null,
                where: null,
                quantity: 0,
                max: 0,
            },

            inventories: [],
            stocks: [],
            foremans: [],

            moveForemanDialog: false,
            moveForeman: {
                product: null,
                unit: null,
                where: null,
                quantity: 0,
                max: 0,
            }
        }
    },

    mounted() {
        // console.log('user', this.currentUser);

        if (this.inventory != null) {
            axios.get('/api/v1/inventory-stocks/' + this.inventory.id).then((response) => {
                var goods = response.data;
                // console.log("goods", goods);

                for (var i = 0; i < goods.length; i++) {
                    // console.log(goods[i]);

                    this.stocks.push({
                        'stock_id': goods[i].id,
                        'unit': goods[i].application_product.unit.name,
                        'name': goods[i].application_product.product.name,
                    })
                }

                // console.log(this.stocks);
            })

            // axios.get('/api/v1/foremans').then((response) => {
            //     this.foremans = response.data;
            // })
        }

        // get inventories
        axios.get('/api/v1/inventories/dropdown').then((response) => {
            var res = response.data.data;

            for (var i = 0; i < res.length; i++) {
                var inventory = res[i];
                // console.log(inventory.construction);

                if (this.inventory.id == inventory.id) continue;

                inventory.name = inventory.construction.name

                this.inventories.push(inventory);
            }

        });

        // get foremans
        axios.get(`/api/v1/inventories/${this.inventory.id}/foremans`).then((response) => {
            this.foremans = response.data;
        })
    },

    methods: {
        isSectionManager() {
            return this.currentUser != null && this.currentUser.roles[0].title == 'Section Manager';
        },

        showMoveDialog() {
            this.moveDialog = true;
        },

        showMoveToForemanDialog() {
            this.moveForemanDialog = true;
        },

        showMoveOutsideDialog() {
            this.moveOutsideDialog = true;
        },

        moveIt() {
            // TODO: check for quantity

            if (!window.confirm('Вы уверены, что хотите переместить?')) {
                return;
            }

            var data = {
                stock: this.move.product,
                where: this.move.where,
                quantity: this.move.quantity,
            }

            axios.post('/api/v1/move-stocks', data).then((response) => {
                if (response.data == 0) {
                    alert('Недопустимое количество для перемещения');
                    return;
                }

                this.moveDialog = false;
                this.move = {
                    product: null,
                    where: null,
                    quantity: 0,
                    unit: null,
                    max: 0,
                };

                location.reload();
            })
        },

        moveToForeman() {
            // TODO: check for quantity

            if (!window.confirm('Вы уверены, что хотите переместить?')) {
                return;
            }
            var data = {
                sender_id: this.inventory.id,
                stock: this.moveForeman.product,
                where: this.moveForeman.where,
                quantity: this.moveForeman.quantity,
            }

            axios.post('/api/v1/move-stocks-foreman', data).then((response) => {
                if (response.data == 0) {
                    alert('Недопустимое количество для перемещения');
                    return;
                }

                this.moveForeman = {
                    product: null,
                    where: null,
                    unit: null,
                    quantity: 0,
                    max: 0,
                };

                this.moveForemanDialog = false;

                location.reload();
            })
        },

        moveOutsideIt() {
            // console.log(this.moveOutside);

            // TODO: check for quantity

            if (!window.confirm('Вы уверены, что хотите переместить?')) {
                return;
            }
            var data = {
                sender_id: this.inventory.id,
                stock: this.moveOutside.product,
                where: this.moveOutside.where,
                quantity: this.moveOutside.quantity,
            }

            axios.post('/api/v1/move-stocks-outside', data).then((response) => {
                if (response.data == 0) {
                    alert('Недопустимое количество для перемещения');
                    return;
                }

                this.moveOutside = {
                    product: null,
                    where: null,
                    unit: null,
                    quantity: 0,
                    max: 0,
                };

                this.moveOutsideDialog = false;

                location.reload();
            })
        },

        onProductSelect(val) {
            // console.log(val);

            this.move.unit = val.unit;
            this.moveOutside.unit = val.unit;
            this.moveForeman.unit = val.unit;

            // this.move.max = val.
        }
    }
}
</script>


<style>
@media only screen and (min-width: 768px) {
    .oks-dialog {
        width: 500px;
    }
}

@media only screen and (max-width: 1025px) {
    /* .v-container {
        max-width: 1000px !important;
        width: 1000px !important;
    } */
}
</style>