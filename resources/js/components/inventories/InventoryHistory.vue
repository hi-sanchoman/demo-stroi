<template>
    <div>
        <v-container>
            <v-row no-gutters>
                <v-col cols="12">
                    <h1 v-if="inventory != null" class="w-full text-left">Склад: {{ inventory.construction.name }}</h1>
                </v-col>
            </v-row>



            <v-row no-gutters class="mt-10">
                <v-col cols="12" md="4" lg="3" class="border px-5 py-5">
                    <InventorySidebar v-if="currentUser != null && inventory != null" :currentUser="currentUser"
                        :inventory="inventory" />
                </v-col>


                <v-col cols="12" md="8" lg="9" class="pl-0 pl-md-5 mt-4 mt-md-0">
                    <v-table transition="slide-x-transition" style="overflow-x:auto;">
                        <thead>
                            <tr>
                                <th class="text-left">
                                    №
                                </th>
                                <th class="text-left">
                                    Операция
                                </th>
                                <th class="text-left">
                                    Пользователь
                                </th>
                                <th class="text-left">
                                    Дата
                                </th>
                                <th>

                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="logs.length <= 0">
                                <td colspan="5">Нет данных.</td>
                            </tr>

                            <tr v-for="(item, index) in logs" :key="item.id" class="hover:bg-slate-100">
                                <td>{{ index + 1 }}</td>
                                <td>{{ item.log }}</td>
                                <td>{{ item.user.email }}</td>
                                <td>{{ item.created_at }}</td>
                                <td>
                                    <!-- Management -->
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-col>
            </v-row>
        </v-container>
    </div>
</template>

<script>
import axios from 'axios'
import InventorySidebar from './InventorySidebar.vue'

export default {
    components: {
        InventorySidebar
    },

    data() {
        return {
            snackbar: {
                status: false,
                text: '',
                timeout: 2000,
            },
            inventory: null,
            logs: [],
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

        getInventory() {
            // get applications 
            axios.get('/api/v1/inventories/' + this.$route.params.id).then((response) => {
                this.inventory = response.data.data;
            })
        },

        getLogs() {
            axios.get('/api/v1/inventories/' + this.$route.params.id + '/history').then((response) => {
                this.logs = response.data;
            })
        },


    },

    mounted() {
        console.log('mounted');
        // this.isLoading = true;

        // get current user
        this.getCurrentUser();

        this.getInventory();

        this.getLogs();
    },

    // watch: {
    //     '$route.query': {
    //         handler(newValue) {
    //             const { status } = newValue

    //             if (status == 'waiting') {
    //                 // this.getIncoming();
    //             } else if (status == 'accepted') {
    //                 this.$router.push('/inventories/' + this.$route.params.id + '?status=accepted');
    //             } else if (status == 'declined') {
    //                 console.log('get declined');
    //             }
    //         },
    //         immediate: true,
    //     }
    // }
}
</script>
