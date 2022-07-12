<template>
    <div>
        <v-container>
            <!-- <v-row no-gutters>
                <v-col cols="12">
                    <h1 class="w-full text-left">Заявки</h1>
                </v-col>
            </v-row> -->

            <v-row no-gutters class="mt-10">
                <v-col cols="12" md="2" class="border px-5 py-5">
                    <ApplicationSidebar v-if="currentUser != null" :currentUser="currentUser" />
                </v-col>

                <v-col cols="12" md="10" class="pl-0 pl-md-5 mt-4 mt-md-0">
                    <v-table transition="slide-x-transition">
                        <thead>
                            <tr>
                                <th class="text-left">
                                    №
                                </th>
                                <th class="text-left">
                                    Объект
                                </th>
                                <th class="text-left">
                                    Дата заявки
                                </th>
                                <th class="text-left">
                                    Статус
                                </th>
                                <th>
                                    Управление
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="applications.length <= 0">
                                <td colspan="5">Нет заявок.</td>
                            </tr>

                            <tr
                                v-for="application in applications"
                                :key="application.id"
                                
                            >
                                <td @click="showApplication(application.id)" style="cursor: pointer">{{ application.id }}</td>
                                <td @click="showApplication(application.id)" style="cursor: pointer">{{ application.construction.name }}</td>
                                <td @click="showApplication(application.id)" style="cursor: pointer">{{ application.issued_at }}</td>
                                
                                <td >
                                    <v-chip
                                        v-if="application.status == 'draft'"
                                        class="ma-2"
                                        color="grey"
                                        text-color="white"
                                    >
                                        Черновик
                                    </v-chip>
                                    
                                    <v-row
                                        v-if="application.status == 'in_review'"
                                        class="py-3"
                                    >
                                        <v-chip
                                            v-for="item in application.application_application_statuses" 
                                            :key="item.id"
                                            class="ma-2"
                                            :color="getStatusColor(item)"
                                            text-color="white"
                                        >
                                            {{ getStatusText(item) }}
                                        </v-chip>  
                                    </v-row>

                                    <v-col
                                        v-if="application.status == 'declined'"
                                    >
                                        <v-chip
                                            
                                            class="ma-2"
                                            color="red"
                                            text-color="white"
                                        >   
                                            Отклонено
                                        </v-chip>        
                                    </v-col>
                                </td>
                                
                                <td>
                                    <v-btn @click="deleteApplication(application.id)"
                                        color="error"
                                        size="small"
                                        v-if="application.status == 'draft'"
                                    >
                                        Удалить
                                    </v-btn>
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
import useApplications from "../../composables/applications"
import ApplicationSidebar from "./ApplicationSidebar.vue"
import axios from 'axios'
import { store } from '../../store.js'

export default {
    components: {
        ApplicationSidebar,
    },

    data() {
        return {
            applications: [],
            currentUser: null,
            store,
        } 
    },
    
    methods: {
        readApplicationsBadge() {
            axios.put('/api/v1/read-badge', { type: 'applications' }).then((response) => {
                this.store.badgeNew = 0;
            });
        },

        getCurrentUser() {
            axios.get('/api/v1/me').then((response) => {
                this.currentUser = response.data
                // console.log(this.currentUser)

                if (this.$route.query.status == 'redirect') {
                    this.readApplicationsBadge();

                    if (this.currentUser.roles[0].title == 'PTD Engineer') {
                        this.$router.push('/applications?status=draft')
                        return
                    } else if (this.currentUser.roles[0].title == 'Supplier') {
                        this.$router.push('/applications?status=in_progress_supplier')
                        return
                    } else if (this.currentUser.roles[0].title == 'Economist' || 
                        this.currentUser.roles[0].title == 'Chief Financial Officer'
                    ) {
                        this.$router.push('/applications?status=in_progress_economist')
                        return
                    } else if (this.currentUser.roles[0].title == 'Warehouse Manager') {
                        this.$router.push('/applications?status=in_progress_warehouse')
                        return
                    }

                    this.$router.push('/applications?status=incoming')
                }
            })
        },

        showApplication(id) {
            this.$router.push(`/applications/${id}/edit`)
        },

        getApplications(status) {
            // get applications 
            axios.get('/api/v1/applications?status=' + status).then((response) => {
                this.applications = response.data.data
                console.log(this.applications);
            })
        },

        deleteApplication(id) {
            if (!window.confirm('Вы действительно хотите?')) {
                return
            }
            
            axios.delete('/api/v1/applications/' + id).then((response) => {
                this.getApplications()
            })
        },

        getStatusColor(item) {
            if (item.status == 'accepted') return 'green'
            if (item.status == 'declined') return 'declined'
            if (item.status == 'waiting' || item.status == 'incoming') return 'grey'
        },

        getStatusText(item) {
            return item.application_path.responsible.name
        }

    },

    mounted() {
        // this.getApplications('draft')
        this.getCurrentUser()
    },

    watch: {
        '$route.query': {
            handler(newValue) {
                const { status } = newValue

                this.getApplications(status)
            },
            immediate: true,
        }
    }
}
</script>
