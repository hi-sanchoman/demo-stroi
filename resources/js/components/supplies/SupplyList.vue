<template>
    <div>
        <v-container>
            <!-- <v-row no-gutters>
                <v-col cols="12">
                    <h1 class="w-full text-left">Заявки</h1>
                </v-col>
            </v-row> -->

            <v-row no-gutters class="mt-5">
                <v-col cols="12" class="mb-5">
                    <h2>Накопители</h2>
                </v-col>

                <v-col cols="12" class="">
                    <ag-grid-vue v-if="currentUser != null" class="ag-theme-alpine" style="height: 500px"
                        :columnDefs="columnDefs.value" :rowData="rowData.value" :defaultColDef="defaultColDef"
                        rowSelection="multiple" animateRows="true" @row-clicked="showPosition"
                        @grid-ready="onGridReady">
                    </ag-grid-vue>

                    <!-- <v-table transition="slide-x-transition" style="overflow-x:auto;">
                        <thead>
                            <tr>
                                <th class="text-left">
                                    №
                                </th>
                                <th class="text-left">
                                    Объект
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

                            <tr v-for="(inventory, index) in inventories" :key="inventory.item.id"
                                style="cursor:pointer" @click="showPosition(inventory)">
                                <td>{{ index + 1 }}</td>
                                <td>{{ inventory.item.construction.name }}</td>
                                <td>
                                    <span v-if="inventory.item.application_product">
                                        {{ inventory.item.application_product.product.name }}
                                    </span>
                                    <span v-else-if="inventory.item.application_equipment">
                                        {{ inventory.item.application_equipment.equipment.name }}
                                    </span>
                                    <span v-else-if="inventory.item.application_service">
                                        {{ inventory.item.application_service.service }}
                                    </span>
                                </td>
                                <td>
                                    <span v-if="inventory.item.application_product">
                                        {{ inventory.item.application_product.category.name }}
                                    </span>
                                    <span v-else-if="inventory.item.application_equipment">
                                        -
                                    </span>
                                    <span v-else-if="inventory.item.application_service">
                                        {{ inventory.item.application_service.category }}
                                    </span>
                                </td>
                                <td>
                                    <span v-if="inventory.item.application_product">
                                        {{ inventory.item.application_product.unit.name }}
                                    </span>
                                    <span v-else-if="inventory.item.application_service">
                                        {{ inventory.item.application_service.unit }}
                                    </span>
                                    <span v-else-if="inventory.item.application_equipment">
                                        шт
                                    </span>
                                </td>
                                <td>{{ inventory.total }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </v-table> -->
                </v-col>
            </v-row>
        </v-container>


        <!-- inventory history dialog -->
        <v-dialog v-model="historyDialog">
            <v-card>
                <v-card-title>
                    <span class="text-h5">История "{{ historyName }}"</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row no-gutters class="">

                            <v-col cols="12" class="">
                                <v-table transition="slide-x-transition" style="overflow-x: auto">
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

                                        <tr v-for="(inventory, index) in historyInventories" :key="inventory.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>
                                                <template v-if="inventory.application_product">
                                                    {{ inventory.application_product.product.name }}
                                                </template>
                                                <template v-else-if="inventory.application_equipment">
                                                    {{ inventory.application_equipment.equipment.name }}
                                                </template>
                                                <template v-else-if="inventory.application_service">
                                                    {{ inventory.application_service.service }}
                                                </template>
                                            </td>
                                            <td>
                                                <template v-if="inventory.application_product">
                                                    {{ inventory.application_product.category.name }}
                                                </template>
                                                <template v-else-if="inventory.application_equipment">
                                                    -
                                                </template>
                                                <template v-else-if="inventory.application_service">
                                                    {{ inventory.application_service.category }}
                                                </template>
                                            </td>
                                            <td>
                                                <template v-if="inventory.application_product">
                                                    {{ inventory.application_product.unit.name }}
                                                </template>
                                                <template v-else-if="inventory.application_equipment">
                                                    шт
                                                </template>
                                                <template v-else-if="inventory.application_service">
                                                    {{ inventory.application_service.unit }}
                                                </template>
                                            </td>
                                            <td>{{ inventory.quantity }}</td>
                                            <td>{{ inventory.created_at }}</td>
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
import { AgGridVue } from "ag-grid-vue3";
import "ag-grid-community/styles/ag-grid.css"; // Core grid CSS, always needed
import "ag-grid-community/styles/ag-theme-alpine.css"; // Optional theme CSS
import { reactive, ref } from "vue";

export default {
    components: {
        AgGridVue
    },

    data() {
        return {
            inventories: [],
            currentUser: null,
            historyDialog: false,
            history: null,
            historyName: null,
            historyInventories: [],

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
        getCurrentUser() {
            axios.get('/api/v1/me').then((response) => {
                this.currentUser = response.data
                // console.log(this.currentUser)

                // columns
                this.columnDefs.value = [
                    { field: 'item.id', headerName: '№', filter: 'agNumberColumnFilter', valueGetter: this.getIndex },
                    { field: 'item.id', headerName: 'Наименование ресурса', valueGetter: this.getName },
                    { field: 'item.id', headerName: 'Статья расходов', valueGetter: this.getCategory },
                    { field: 'item.id', headerName: 'Ед. изм.', valueGetter: this.getUnit },
                    { field: "item.quantity", headerName: 'Количество' },
                    { field: "item.created_at", headerName: 'Дата', type: ['dateColumn'], filter: 'agDateColumnFilter' },
                    // { field: "status", headerName: 'Статус', valueGetter: this.getStatus },
                    // { field: "signs", headerName: 'Подписи' },
                    // {
                    //     field: "action",
                    //     headerName: 'Действие',
                    //     cellRendererSelector: (params) => {
                    //         // console.log('cell', params);

                    //         return {
                    //             component: 'deleteBtnRenderer',
                    //             params: {
                    //                 can: params.data.status == 'draft' && this.isPTDEngineer(),
                    //                 clicked: this.deleteApplication,
                    //             }
                    //         }
                    //     },
                    // },
                ];
            })
        },

        showPosition(params) {
            console.log('show history', params.data.item);

            this.history = params.data;
            this.historyName = this.getHistoryName();
            this.historyDialog = true;
            this.historyInventories = [];

            this.getHistoryInventories()
        },

        getSupplies() {
            // get applications 
            axios.get('/api/v1/supplies').then((response) => {
                console.log('resp', response);
                // this.inventories = response.data.data;
                this.rowData.value = response.data.data;
            })
        },

        getHistoryInventories() {
            // console.log(this.history)
            let id = null;
            let kind = null;

            if (this.history.item.application_product) {
                id = this.history.item.application_product.product.id;
                kind = 'product';
            } else if (this.history.item.application_service) {
                id = this.history.item.application_service.id;
                kind = 'service';
            } else if (this.history.item.application_equipment) {
                id = this.history.item.application_equipment.equipment.id;
                kind = 'equipment';
            }

            axios.get('/api/v1/history-supplies/' + id + '/' + kind).then((response) => {
                this.historyInventories = response.data
            })
        },

        getHistoryName() {
            if (this.history.item.application_product)
                return this.history.item.application_product.product.name;

            if (this.history.item.application_service)
                return this.history.item.application_service.service;

            if (this.history.item.application_equipment)
                return this.history.item.application_equipment.equipment.name;
        },

        onGridReady: (params) => {
            console.log('grid ready', params);
            // this.gridApi.value = params.api;
        },

        cellWasClicked: (event) => { // Example of consuming Grid Event
            console.log("cell was clicked", event);
            // this.showPosition(event.data);
        },

        deselectRows: () => {
            this.gridApi.value.deselectAll()
        },

        getIndex: (params) => {
            // console.log(params);
            return params.node.rowIndex + 1;
        },

        getName: (params) => {
            if (params.data.item.application_product) {
                return params.data.item.application_product.product.name;
            }

            else if (params.data.item.application_equipment) {
                return params.data.item.application_equipment.equipment.name;
            }

            else if (params.data.item.application_service) {
                return params.data.item.application_service.service;
            }
        },

        getCategory: (params) => {
            if (params.data.item.application_product) {
                return params.data.item.application_product.category.name;
            }

            else if (params.data.item.application_equipment) {
                return '-';
            }

            else if (params.data.item.application_service) {
                return params.data.item.application_service.category;
            }
        },

        getUnit: (params) => {
            if (params.data.item.application_product) {
                return params.data.item.application_product.unit.name;
            }

            else if (params.data.item.application_equipment) {
                return 'шт';
            }

            else if (params.data.item.application_service) {
                return params.data.item.application_service.unit;
            }
        },

    },

    mounted() {
        // this.getApplications('draft')
        this.getCurrentUser();
        this.getSupplies();
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