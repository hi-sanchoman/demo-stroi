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
                    <!-- <ag-grid-vue class="ag-theme-alpine" style="height: 500px" :columnDefs="columnDefs.value"
                        :rowData="rowData.value" :defaultColDef="defaultColDef" rowSelection="multiple"
                        animateRows="true" @cell-clicked="cellWasClicked" @grid-ready="onGridReady">
                    </ag-grid-vue> -->

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
                                    Тип
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

                            <tr v-for="application in applications" :key="application.id"
                                :class="application.opened_statuses.length > 0 ? 'tr-unread' : 'tr-read'">
                                <td @click="showApplication(application.id)" style="cursor: pointer">{{ application.id
                                }}</td>
                                <td @click="showApplication(application.id)" style="cursor: pointer">{{
                                        application.construction.name
                                }}</td>
                                <td>
                                    {{ getKind(application.kind) }}
                                </td>
                                <td @click="showApplication(application.id)" style="cursor: pointer">{{
                                        application.issued_at
                                }}</td>

                                <td>
                                    <v-chip v-if="application.status == 'draft'" class="ma-2" color="grey"
                                        text-color="white">
                                        Черновик
                                    </v-chip>

                                    <v-row v-if="application.status == 'in_review'" class="py-3">
                                        <v-chip v-for="item in application.application_application_statuses"
                                            :key="item.id" class="ma-2" :color="getStatusColor(item)"
                                            text-color="white">
                                            {{ getStatusText(item) }}
                                        </v-chip>
                                    </v-row>

                                    <v-col v-if="application.status == 'declined'">
                                        <v-chip class="ma-2" color="red" text-color="white">
                                            Отклонено
                                        </v-chip>
                                    </v-col>
                                </td>

                                <td>
                                    <v-btn @click="deleteApplication(application.id)" color="error" size="small"
                                        v-if="application.status == 'draft' && isPTDEngineer()">
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
import { AgGridVue } from "ag-grid-vue3";
import "ag-grid-community/styles/ag-grid.css"; // Core grid CSS, always needed
import "ag-grid-community/styles/ag-theme-alpine.css"; // Optional theme CSS
import { reactive, ref } from "vue";
import DeleteBtnRenderer from '../grid/DeleteBtnRenderer.js';

export default {
    components: {
        ApplicationSidebar,
        AgGridVue,
        deleteBtnRenderer: DeleteBtnRenderer,
    },

    data() {
        return {
            applications: [],
            currentUser: null,
            store,

            gridApi: ref(null),
            columnDefs: reactive({}),
            rowData: reactive({}),
            defaultColDef: {
                sortable: true,
                filter: true,
                flex: 1,
                floatingFilter: true,
                resizable: true,
            },


        }
    },

    methods: {
        isPTDEngineer() {
            return this.currentUser != null && this.currentUser.roles[0].title == 'PTD Engineer';
        },

        readApplicationsBadge() {
            axios.put('/api/v1/read-badge', { type: 'applications' }).then((response) => {
                this.store.badgeNew = 0;
            });
        },

        getCurrentUser() {
            axios.get('/api/v1/me').then((response) => {
                this.currentUser = response.data

                // columns
                this.columnDefs.value = [
                    { field: "id", headerName: '№ заявки', filter: 'agNumberColumnFilter' },
                    { field: "construction.name", headerName: 'Объект' },
                    { field: "kind", headerName: 'Тип заявки', valueGetter: this.getKind },
                    { field: "issued_at", headerName: 'Дата', type: ['dateColumn'], filter: 'agDateColumnFilter' },
                    { field: "status", headerName: 'Статус', valueGetter: this.getStatus },
                    // { field: "signs", headerName: 'Подписи' },
                    {
                        field: "action",
                        headerName: 'Действие',
                        cellRendererSelector: (params) => {
                            // console.log('cell', params);

                            return {
                                component: 'deleteBtnRenderer',
                                params: {
                                    can: params.data.status == 'draft' && this.isPTDEngineer(),
                                    clicked: this.deleteApplication,
                                }
                            }
                        },
                        // cellRenderer: this.deleteBtnRenderer,
                        // cellRendererParams: {
                        //     clicked: function (field) {
                        //         alert(`${field} was clicked`);
                        //     },
                        // },
                    },
                ];

                if (this.$route.query.status == 'redirect') {
                    console.log('redirect')

                    // this.readApplicationsBadge();

                    // if (this.currentUser.roles[0].title == 'PTD Engineer') {
                    //     this.$router.push('/applications?status=draft')
                    //     return
                    // } else if (this.currentUser.roles[0].title == 'Supplier') {
                    //     this.$router.push('/applications?status=in_progress_supplier')
                    //     return
                    // } else if (this.currentUser.roles[0].title == 'Economist' ||
                    //     this.currentUser.roles[0].title == 'Chief Financial Officer'
                    // ) {
                    //     this.$router.push('/applications?status=in_progress_economist')
                    //     return
                    // }
                    // } else if (this.currentUser.roles[0].title == 'Warehouse Manager') {
                    //     this.$router.push('/applications?status=in_progress_warehouse')
                    //     return
                    // }

                    this.$router.push('/applications?status=all')
                }
            })
        },

        showApplication(id) {
            this.$router.push(`/applications/${id}/edit`)
        },

        getApplications(status) {
            console.log(`get applications with ${status} status`);

            // get applications 
            axios.get('/api/v1/applications?status=' + status).then((response) => {
                this.applications = response.data.data;

                this.rowData.value = response.data.data;
            })
        },

        deleteApplication(id) {
            if (!window.confirm('Вы действительно хотите?')) {
                return
            }

            axios.delete('/api/v1/applications/' + id).then((response) => {
                this.getApplications('all')
            })
        },

        getStatusColor(item) {
            if (item.status == 'accepted') return 'green'
            if (item.status == 'declined') return 'declined'
            if (item.status == 'waiting' || item.status == 'incoming') return 'grey'
        },

        getStatusText(item) {
            return item.application_path.responsible.name
        },

        getKind(params) {
            var kinds = {
                'product': 'заявка на товар',
                'equipment': 'заявка на спец. технику',
                'service': 'заявка на услугу'
            };

            return kinds[params?.data?.kind];
        },

        getStatus(params) {
            var statuses = {
                'draft': 'черновик',
                'in_progress': 'в процессе',
                'in_review': 'на рассмотрении',
                'declined': 'отклонена',
                'completed': 'закрыта',
            };

            return statuses[params.data.status];
        },

        getActions(params) {

        },

        onGridReady: (params) => {
            // console.log(params);
            this.gridApi.value = params.api;
        },

        cellWasClicked: (event) => { // Example of consuming Grid Event
            console.log("cell was clicked", event);
        },

        deselectRows: () => {
            this.gridApi.value.deselectAll()
        }


    },

    mounted() {
        // this.getApplications('draft')
        this.getCurrentUser()

        // fetch("https://www.ag-grid.com/example-assets/row-data.json")
        //     .then((result) => result.json())
        //     .then((remoteRowData) => (this.rowData.value = remoteRowData));
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


<style>
.tr-unread td {
    font-weight: bold;
}
</style>