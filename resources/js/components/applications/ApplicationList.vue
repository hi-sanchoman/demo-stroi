<template>
    <div class="" style="padding: 20px;">

        <v-row no-gutters v-if="!chosenObject">
            <v-col cols="12" md="4">

                <v-card v-for="construction in objects" :key="construction.id" class="border rounded px-4 py-4 w-fit"
                    @click="selectConstruction(construction)">
                    <v-card-title>{{  construction.name  }}</v-card-title>
                </v-card>
            </v-col>
        </v-row>

        <v-row no-gutters class="" v-if="chosenObject">
            <v-col cols="12" class="mb-5">
                <v-btn @click="chosenObject = null" size="small">Выбрать другой объект</v-btn>
                <h2>Заявки</h2>
            </v-col>

            <v-col cols="12" md="3" class="border px-5 py-5">
                <ApplicationSidebar v-if="currentUser != null" :currentUser="currentUser" />
            </v-col>

            <v-col cols="12" md="9" class="pl-0 pl-md-5 mt-4 mt-md-0">
                <ag-grid-vue class="ag-theme-alpine" style="height: 800px" :rowClassRules="rowClassRules"
                    :columnDefs="columnDefs.value" :rowData="rowData.value" :defaultColDef="defaultColDef"
                    rowSelection="multiple" animateRows="true" @grid-ready="onGridReady" :localeText="localeText"
                    @row-clicked="rowWasClicked">
                </ag-grid-vue>
            </v-col>
        </v-row>

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
import AgSelectFilter from '../AgSelectFilter.js';

let self = this;

export default {
    components: {
        ApplicationSidebar,
        AgGridVue,
        deleteBtnRenderer: DeleteBtnRenderer,
        AgSelectFilter
    },

    data() {
        return {
            router: this.$router,
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
                suppressMovable: true,
                wrapText: true,
                autoHeight: true,
            },
            rowClassRules: {
                'tr-unread': (params) => {
                    return params.data.opened_statuses.length > 0;
                }
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

            constructionOptions: [],
            kindOptions: [
                { value: '', name: 'Все' },
                { value: 'product', name: 'заявка на товар' },
                { value: 'equipment', name: 'заявка на спец. технику' },
                { value: 'service', name: 'заявка на услуги' }
            ],

            chosenObject: null,
            objects: [],
        }
    },

    methods: {
        getObjects() {
            axios.get('/api/v1/constructions').then((response) => {
                // console.log(response.data);
                this.objects = response.data.data;
            });
        },

        selectConstruction(c) {
            const status = 'all';

            this.chosenObject = c;
            this.rowData.value = [];
            this.getApplications(this.chosenObject, status);
        },

        getConstructions() {
            this.constructionOptions.push({ value: '', name: 'Все' });

            axios.get('/api/v1/constructions').then((response) => {
                response.data.data.forEach(c => {
                    this.constructionOptions.push({ value: c.id, name: c.name });
                })
            })
        },

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
                    {
                        field: "id", minWidth: 100, headerName: '№ заявки', filter: 'agNumberColumnFilter', cellRenderer: (params) => {
                            // console.log(params);

                            var link = document.createElement('a');
                            link.href = '#';
                            link.innerText = params.value;

                            link.style = 'text-decoration: underline; color: black;';
                            link.className = params.data.opened_statuses.length > 0 ? 'unread' : 'read';

                            link.addEventListener('click', (e) => {
                                e.preventDefault();
                                const id = params.data.id;
                                this.$router.push(`/applications/${id}/edit`);
                            });
                            return link;
                        }
                    },
                    {
                        field: "construction.name", minWidth: 200, headerName: 'Объект', filter: AgSelectFilter, filterParams: {
                            column: 'construction_id',
                            options: this.constructionOptions,
                        },
                    },
                    {
                        field: "kind", minWidth: 200, headerName: 'Тип заявки', filter: AgSelectFilter, filterParams: {
                            column: 'kind',
                            options: this.kindOptions,
                        }, cellRenderer: (params) => {
                            var kinds = {
                                'product': 'заявка на товар',
                                'equipment': 'заявка на спец. технику',
                                'service': 'заявка на услугу'
                            };

                            const kind = typeof params === 'string' ? kinds[params] : kinds[params?.data?.kind];
                            const inners = this.getInners(params);
                            return `<span>${kind}</span><br>${inners}`;
                        }
                    },
                    {
                        field: "issued_at", minWidth: 200, headerName: 'Дата', type: ['dateColumn'], filter: 'agDateColumnFilter', filterParams: {
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
                    {
                        field: "status", minWidth: 200, headerName: 'Статус', valueGetter: this.getStatus, cellStyle: params => {
                            // console.log(params);

                            if (params.data.status === 'declined') {
                                //mark police cells as red
                                return { color: 'red' };
                            }
                            return null;
                        }, filter: AgSelectFilter, filterParams: {
                            column: 'status',
                            options: [{ value: '', name: 'Все' }, { value: 'draft', name: 'черновик' }, { value: 'in_progress', name: 'в процессе' }, { value: 'completed', name: 'закрыта' }],
                        },
                    },
                    {
                        field: "action",
                        minWidth: 200,
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
                    },
                ];

                if (this.$route.query.status == 'redirect') {
                    this.$router.push('/applications?status=all')
                }
            })
        },

        showApplication(id) {
            this.$router.push(`/applications/${id}/edit`)
        },

        getApplications(construction, status) {
            console.log(`get applications with ${status} status`);

            // get applications 
            axios.get(`/api/v1/applications?status=${status}&construction_id=${construction.id}`).then((response) => {
                this.applications = response.data.data;
                this.rowData.value = response.data.data;
            })
        },

        deleteApplication(id) {
            if (!window.confirm('Вы действительно хотите удалить?')) {
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

        // getKind(params) {
        //     var kinds = {
        //         'product': 'заявка на товар',
        //         'equipment': 'заявка на спец. технику',
        //         'service': 'заявка на услугу'
        //     };

        //     const kind = typeof params === 'string' ? kinds[params] : kinds[params?.data?.kind];
        //     const inners = this.getInners(params);
        //     return `${kind}<br>${inners}`;
        // },

        getInners(params) {
            console.log(params?.data);
            let inners = '';

            if (params?.data?.application_application_products?.length > 0) {
                inners += params?.data?.application_application_products[0]?.product?.name + '<br/>';
            }
            if (params?.data?.application_equipments?.length > 0) {
                inners += params?.data?.application_equipments[0]?.equipment?.name + '<br/>';
            }
            if (params?.data?.application_services?.length > 0) {
                inners += params?.data?.application_services[0]?.service + '<br/>';
            }

            return `<div style="color: grey;">${inners}</div>`;
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
            // this.gridApi.value = params.api;
        },

        cellWasClicked: (event) => { // Example of consuming Grid Event
            console.log("cell was clicked", event);
        },

        deselectRows: () => {
            this.gridApi.value.deselectAll()
        },

        rowWasClicked: (r) => {
            const id = r.data.id;
            console.log(this);
            // console.log(this.router);

            // self.$router.push(`/applications/${id}/edit`);

            location.href = `/applications/${id}/edit`;
        },
    },

    mounted() {
        // this.getApplications('draft')
        this.getCurrentUser();

        this.getConstructions();

        this.getObjects();

        // fetch("https://www.ag-grid.com/example-assets/row-data.json")
        //     .then((result) => result.json())
        //     .then((remoteRowData) => (this.rowData.value = remoteRowData));
    },

    watch: {
        '$route.query': {
            handler(newValue) {
                const { status } = newValue

                if (this.chosenObject) this.getApplications(this.chosenObject, status);
            },
            immediate: true,
        }
    }
}
</script>


<style>
.tr-unread {
    font-weight: bold;
}

.unread {
    font-weight: bold;
}
</style>