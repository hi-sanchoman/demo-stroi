<template>
    <div>
        <v-list 
            density="compact" 
            transition="slide-x-transition"
        >
            <v-list-subheader class="d-block">
                Склад

                <div class="float-right">
                    <v-btn
                        color="primary"
                        plain
                        @click="showMoveDialog()"
                    >
                        <v-icon>mdi-arrow-expand-horizontal</v-icon>
                    </v-btn>
                </div>
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
            
            <router-link :to="'/inventories/' + inventory.id + '?status=accepted'" class="text-decoration-none text-black">
                <v-list-item
                    key="accepted"
                    value="accepted"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'Товары'"></v-list-item-title>
                </v-list-item>
            </router-link>

            <!-- <router-link :to="'/inventories/' + inventory.id + '?status=declined'" class="text-decoration-none text-black">
                <v-list-item
                    key="declined"
                    value="declined"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'Отклоненные'"></v-list-item-title>
                </v-list-item>
            </router-link>           -->
            
            <router-link :to="'/inventories/' + inventory.id + '/history'" class="text-decoration-none text-black">
                <v-list-item
                    key="history"
                    value="history"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'История'"></v-list-item-title>
                </v-list-item>
            </router-link>  
        </v-list>
    </div>

    <!-- Add Company Dialog -->
    <v-dialog
        v-model="moveDialog"
        persistent
    >
        <v-card
            class="min-w-5xl w-7xl" style="width: 500px"
        >
            <v-card-title>
                <span class="text-h5">Перемещение товара</span>
            </v-card-title>

            <v-card-text>
                <v-container>
                    <v-row>
                        <v-col
                            cols="12"
                        >
                            <multiselect  
                                v-model="move.product" :options="stocks" placeholder="Укажите товар" label="name" track-by="id">
                            </multiselect>

                            <multiselect  
                                class="mt-2"
                                v-model="move.where" :options="foremans" placeholder="Укажите бригадира" label="name" track-by="id">
                            </multiselect>

                            <v-text-field
                                class="mt-4"
                                v-model="move.quantity"
                                label="Количество"
                                variant="underlined"
                                required
                                density="comfortable"
                                type="number"
                                @keyup.enter="move()"
                            ></v-text-field>

                        </v-col>
                    </v-row>
                </v-container>
                <!-- <small>* обязательные поля</small> -->
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                
                <v-btn
                    color="default"
                    text
                    @click="moveDialog = false"
                >
                    Отмена
                </v-btn>

                <v-btn
                    color="success"
                    text
                    @click="moveIt()"
                >
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

    mounted() {
        console.log('user', this.currentUser);

        if (this.inventory != null) {
            axios.get('/api/v1/inventory-stocks/' + this.inventory.id).then((response) => {
                var goods = response.data;
                // console.log("goods", goods);
                
                for (var i = 0; i < goods.length; i++) {
                    console.log(goods[i]);

                    this.stocks.push({
                        'stock_id': goods[i].id,
                        'name': goods[i].application_product.product.name,
                    })
                }

                console.log(this.stocks);
            })

            axios.get('/api/v1/foremans').then((response) => {
                this.foremans = response.data;
            })
        }
    },

    data() {
        return {
            moveDialog: false,
            move: {
                product: null,
                where: null,
                quantity: 0,
            },
            stocks: [],
            foremans: [],
        } 
    },

    methods: {
        showMoveDialog() {
            this.moveDialog = true;

            console.log('[show move]')
        },

        moveIt() {
            if (!window.confirm('Вы уверены, что хотите переместить?')) {
                return;
            }
            var data = {
                stock: this.move.product,
                where: this.move.where,
                quantity: this.move.quantity,
            }

            axios.post('/api/v1/move-stocks', data).then((response) => {
                this.moveDialog = false;
                this.move = {
                    product: null,
                    where: null,
                    quantity: 0,
                };
                
                location.reload();
            })
        },
    }
}
</script>
