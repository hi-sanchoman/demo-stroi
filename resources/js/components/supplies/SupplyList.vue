<template>
    <div style="padding: 20px;">

        <v-row no-gutters v-if="!chosenObject">
            <v-col cols="12" md="4">
                <v-card v-for="construction in objects" :key="construction.id" class="border rounded px-4 py-4 w-fit"
                    @click="selectConstruction(construction)">
                    <v-card-title>{{  construction.name  }}</v-card-title>
                </v-card>
            </v-col>
        </v-row>

        <v-row no-gutters class="mt-5" v-if="chosenObject">
            <v-col cols="12" class="mb-5">
                <v-btn @click="chosenObject = null" size="small">Выбрать другой объект</v-btn>
                <h2>Накопитель</h2>
            </v-col>

            <v-col cols="12" class="">
                <ag-grid-vue v-if="currentUser != null" class="ag-theme-alpine" style="height: 800px"
                    :columnDefs="columnDefs.value" :rowData="rowData.value" :defaultColDef="defaultColDef"
                    rowSelection="multiple" animateRows="true" @row-clicked="showPosition" :localeText="localeText"
                    @grid-ready="onGridReady">
                </ag-grid-vue>
            </v-col>
        </v-row>


        <!-- inventory history dialog -->
        <v-dialog v-model="historyDialog">
            <v-card class="oks-dialog min-w-5xl w-7xl">
                <v-card-title>
                    <span class="text-h5">История "{{  historyName  }}"</span>
                </v-card-title>

                <v-card-text>
                    <v-container class="oks-container">
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
                                            <td>{{  index + 1  }}</td>
                                            <td>
                                                <template v-if="inventory.application_product">
                                                    {{  inventory.application_product.product.name  }}
                                                </template>
                                                <template v-else-if="inventory.application_equipment">
                                                    {{  inventory.application_equipment.equipment.name  }}
                                                </template>
                                                <template v-else-if="inventory.application_service">
                                                    {{  inventory.application_service.service  }}
                                                </template>
                                            </td>
                                            <td>
                                                <template v-if="inventory.application_product">
                                                    {{  inventory.application_product.category.name  }}
                                                </template>
                                                <template v-else-if="inventory.application_equipment">
                                                    спец. техника
                                                </template>
                                                <template v-else-if="inventory.application_service">
                                                    {{  inventory.application_service.category  }}
                                                </template>
                                            </td>
                                            <td>
                                                <template v-if="inventory.application_product">
                                                    {{  inventory.application_product.unit.name  }}
                                                </template>
                                                <template v-else-if="inventory.application_equipment">
                                                    {{  inventory.application_equipment.unit.name  }}
                                                </template>
                                                <template v-else-if="inventory.application_service">
                                                    {{  inventory.application_service.unit  }}
                                                </template>
                                            </td>
                                            <td>{{  inventory.quantity  }}</td>
                                            <td>{{  inventory.created_at  }}</td>
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
import AG_GRID_LOCALE_EN from '../../assets/gridlocale.js';
import Multiselect from 'vue-multiselect'

export default {
    components: {
        AgGridVue,
        Multiselect,
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
                suppressMovable: true,
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

            chosenObject: null,
            objects: [],
        }
    },

    mounted() {
        // this.getApplications('draft')
        this.getCurrentUser();
        // this.getSupplies();
        this.getObjects();
    },

    methods: {
        getObjects() {
            axios.get('/api/v1/constructions').then((response) => {
                // console.log(response.data);
                this.objects = response.data.data;
            });
        },

        dateFormatter(params) {
            var dateAsString = params.data.item.created_at;
            var dateParts = dateAsString.split('/');
            return `${dateParts[0]} - ${dateParts[1]} - ${dateParts[2]}`;
        },

        getCurrentUser() {
            axios.get('/api/v1/me').then((response) => {
                this.currentUser = response.data
                // console.log(this.currentUser)

                // columns
                this.columnDefs.value = [
                    { field: 'item.id', minWidth: 100, headerName: '№', filter: 'agNumberColumnFilter', valueGetter: this.getIndex },
                    { field: 'item.id', minWidth: 300, headerName: 'Наименование ресурса', valueGetter: this.getName },
                    { field: 'item.id', minWidth: 300, headerName: 'Статья расходов', valueGetter: this.getCategory },
                    { field: 'item.id', minWidth: 150, headerName: 'Ед. изм.', valueGetter: this.getUnit },
                    { field: "item.quantity", minWidth: 150, headerName: 'Количество', filter: 'agNumberColumnFilter' },
                    {
                        field: "item.created_at", minWidth: 200, headerName: 'Дата', type: ['dateColumn'], filter: 'agDateColumnFilter', filterParams: {
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

        getSupplies(construction) {
            // get applications 
            axios.get(`/api/v1/supplies?construction_id=${construction.id}`).then((response) => {
                // console.log('resp', response);
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
            // this.onFirstDataRendered(params);
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
                return 'спец. техника';
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
                return params.data.item.application_equipment.unit.name;
            }

            else if (params.data.item.application_service) {
                return params.data.item.application_service.unit;
            }
        },

        onFirstDataRendered(params) {
            console.log('fit size');
            params.api.sizeColumnsToFit();
        },

        onGridSizeChanged(params) {
            // get the current grids width
            var gridWidth = document.getElementById('grid-wrapper').offsetWidth;

            // keep track of which columns to hide/show
            var columnsToShow = [];
            var columnsToHide = [];

            // iterate over all columns (visible or not) and work out
            // now many columns can fit (based on their minWidth)
            var totalColsWidth = 0;
            var allColumns = params.columnApi.getColumns();
            if (allColumns && allColumns.length > 0) {
                for (var i = 0; i < allColumns.length; i++) {
                    var column = allColumns[i];
                    totalColsWidth += column.getMinWidth() || 0;
                    if (totalColsWidth > gridWidth) {
                        columnsToHide.push(column.getColId());
                    } else {
                        columnsToShow.push(column.getColId());
                    }
                }
            }

            // show/hide columns based on current grid width
            params.columnApi.setColumnsVisible(columnsToShow, true);
            params.columnApi.setColumnsVisible(columnsToHide, false);

            // fill out any available space to ensure there are no gaps
            params.api.sizeColumnsToFit();
        },

        selectConstruction(c) {
            this.chosenObject = c;
            this.rowData.value = [];
            this.getSupplies(c);
        }

    },


    watch: {
        // chosenObject(newVal) {
        //     if (!newVal) return;

        //     this.rowData.value = [];
        //     this.getSupplies(newVal);
        // }
    }
}
</script>


<style scoped>
.oks-dialog {
    width: 300px;
}

.oks-dialog .v-card-text {
    padding: 8px !important;
}

.oks-dialog .v-container {
    padding: 3px;
}

@media only screen and (min-width: 768px) {
    .oks-dialog .v-card-text {
        padding: 16px 24px 10px;
    }

    .oks-dialog {
        min-width: 500px;
        width: auto;
    }


    .v-table__wrapper {
        overflow: visible !important;
    }

    .v-table__wrapper table {
        overflow: visible !important;
    }
}

html,
body {
    height: 100%;
    width: 100%;
    margin: 0;
    box-sizing: border-box;
    -webkit-overflow-scrolling: touch;
}

html {
    position: absolute;
    top: 0;
    left: 0;
    padding: 0;
    overflow: auto;
}

body {
    /* padding: 1rem; */
    overflow: auto;
}
</style>