<template>
    <div style="padding: 20px;">

        <!-- <v-container> -->

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
                <ag-grid-vue class="ag-theme-alpine" style="height: 500px" :columnDefs="columnDefs.value"
                    :rowData="rowData.value" :defaultColDef="defaultColDef" animateRows="true" @grid-ready="onGridReady"
                    :localeText="localeText">
                </ag-grid-vue>


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

        <!-- </v-container> -->

    </div>
</template>

<script>
import axios from 'axios'
import InventorySidebar from './InventorySidebar.vue'
import { AgGridVue } from "ag-grid-vue3";
import "ag-grid-community/styles/ag-grid.css"; // Core grid CSS, always needed
import "ag-grid-community/styles/ag-theme-alpine.css"; // Optional theme CSS
import { reactive, ref } from "vue";

export default {
    components: {
        InventorySidebar,
        AgGridVue
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

            gridApi: ref(null),
            columnDefs: reactive({}),
            rowData: reactive({}),
            defaultColDef: {
                sortable: true,
                filter: true,
                flex: 1,
                floatingFilter: true,
                resizable: true,
                wrapText: true,
                autoHeight: true,
            },
            localeText: {
                selectAll: '(Выбрать все)',
                selectAllSearchResults: '(Выбрать все результаты)',
                searchOoo: 'Поиск...',
                blanks: '(Пусто)',
                noMatches: 'Нет совпадений',

                // Number Filter & Text Filter
                filterOoo: 'Фильтр...',
                equals: 'Равно',
                notEqual: 'Не равно',
                blank: 'Пусто',
                notBlank: 'Не пусто',
                empty: 'Выберите одно',

                // Number Filter
                lessThan: 'Меньше чем',
                greaterThan: 'Больше чем',
                lessThanOrEqual: 'Меньше чем или равно',
                greaterThanOrEqual: 'Больше чем или равно',
                inRange: 'В диапазоне',
                inRangeStart: 'от',
                inRangeEnd: 'до',

                // Text Filter
                contains: 'Содержит',
                notContains: 'Не содержит',
                startsWith: 'Начинается с ',
                endsWith: 'Заканчивается на',

                // Date Filter
                dateFormatOoo: 'dd-mm-YYYY',
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
                    {
                        field: "id", minWidth: 100, headerName: '№ заявки', filter: 'agNumberColumnFilter'
                    },
                    {
                        field: "log", minWidth: 200, headerName: 'Операция'
                    },
                    {
                        field: "user.email", minWidth: 200, headerName: 'Пользователь'
                    },
                    {
                        field: "created_at", minWidth: 200, headerName: 'Дата', type: ['dateColumn'], filter: 'agDateColumnFilter', filterParams: {
                            debounceMs: 500,
                            suppressAndOrCondition: true,
                            comparator: function (filterLocalDateAtMidnight, cellValue) {
                                if (cellValue == null) {
                                    return 0;
                                }
                                var dateParts = cellValue.split('/');
                                var year = Number(dateParts[2]);
                                var month = Number(dateParts[1]) - 1;
                                var day = Number(dateParts[0]);
                                var cellDate = new Date(year, month, day);

                                // console.log([cellValue, dateParts, year, month, day]);

                                if (cellDate < filterLocalDateAtMidnight) {
                                    return -1;
                                } else if (cellDate > filterLocalDateAtMidnight) {
                                    return 1;
                                } else {
                                    return 0;
                                }
                            },
                        },
                    },
                ];
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
                this.rowData.value = response.data;
            })
        },

        onGridReady: (params) => {
            // console.log(params);
            // this.gridApi.value = params.api;
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
