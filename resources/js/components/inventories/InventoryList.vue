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
                    <h2>Склады</h2>
                </v-col>

                <div v-for="inventory in inventories" :key="inventory.id" class="border rounded px-4 py-4 w-fit">
                    <h2 class="block mb-4">{{ inventory.construction.name }}</h2>

                    <router-link :to="'/inventories/' + inventory.id + '/products?status=accepted'"
                        class="text-decoration-none text-black">
                        <a
                            class="mt-4 py-2 px-2 block text-decoration-none text-black border hover:bg-slate-200">Перейти</a>
                    </router-link>
                </div>
            </v-row>
        </v-container>


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
            inventories: [],
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

        getInventories() {
            axios.get('/api/v1/inventories').then((response) => {
                this.inventories = response.data.data;
            })
        }
    },

    mounted() {
        // this.getApplications('draft')
        this.getInventories()
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
