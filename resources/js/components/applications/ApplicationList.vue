<template>
    <div class="" style="padding: 20px;">

        <v-row no-gutters v-if="!chosenObject">
            <v-col cols="12" md="4">

                <v-card v-for="construction in objects" :key="construction.id" class="border rounded px-4 py-4 w-fit"
                    @click="selectConstruction(construction)">
                    <v-card-title class="d-flex justify-space-between">
                        {{ construction.name }}

                        <v-badge v-if="store.badges[construction.id]" color="error"
                            :content="store.badges[construction.id]" inline></v-badge>
                    </v-card-title>
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
                <div v-if="!isWarehouseManager()" class="d-flex flex-column flex-sm-row  mb-4">
                    <div class="d-flex mb-2 flex-column">
                        <!-- <label class="font-bold"><b>Строительный объект:</b></label>
                        <select @change="filterTotal($event, 'constructions')" v-model="filter_construction_ids" multiple>
                            <option value="*">Все</option>
                            <option v-for="construction in objects" :key="construction.id" :value="construction.id">{{construction.name}}</option>
                        </select> -->

                        <multiselect :selectLabel="''" :deselectLabel="''" @select="addFilterConstruction" @remove="removeFilterConstruction" v-model="filter_construction_ids" :options="objects" placeholder="Строительный объект" label="name"
                            track-by="name" multiple>
                        </multiselect>
                    </div>
                    
                    <div class="d-flex mb-2 flex-column ml-sm-4">
                        <!-- <label class="font-bold"><b>Тип заявки:</b></label>
                        <select @change="filterTotal($event, 'kinds')" v-model="filter_kind_ids" multiple>
                            <option v-for="kind in kindOptions" :key="kind.value" :value="kind.value">{{kind.name}}</option>
                        </select> -->
                        <multiselect :selectLabel="''" :deselectLabel="''" @select="addFilterKind" @remove="removeFilterKind" v-model="filter_kind_ids" :options="kindOptions" placeholder="Тип заявки" label="name"
                            track-by="name" multiple>
                        </multiselect>
                    </div>

                    <div class="d-flex mb-2 flex-column ml-sm-4">
                        <!-- <label class="font-bold"><b>Статус:</b></label>
                        <select @change="filterTotal($event, 'statuses')" v-model="filter_status_ids" multiple>
                            <option v-for="status in statusOptions" :key="status.value" :value="status.value">{{status.name}}</option>
                        </select> -->

                        <multiselect :selectLabel="''" :deselectLabel="''" @select="addFilterStatus" @remove="removeFilterStatus" v-model="filter_status_ids" :options="statusOptions" placeholder="Статус" label="name"
                            track-by="value" multiple>
                        </multiselect>
                    </div>

                    <div class="d-flex mb-2 flex-column ml-sm-4">
                        <div class="d-flex">От: <input class="ml-1 text-grey" type="date" @change="filterTotal" v-model="filter_period_from" aria-label="from"/></div>
                        <div class="d-flex">До: <input class="ml-1 text-grey" type="date" @change="filterTotal" v-model="filter_period_to" aria-label="from"/></div>
                    </div>
                    
                    <div class="d-flex mt-4 mt-sm-0 mb-2 flex-column ml-sm-4">
                        <button @click="clearFilterTotal()">Очистить фильтр</button>
                    </div>

                    <div class="d-flex flex-grow-1 justify-center justify-sm-end">
                        <v-btn @click="downloadTable()" size="small" color="primary">Скачать таблицу</v-btn>
                    </div>
                </div>

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
import DownloadBtnRenderer from '../grid/DownloadBtnRenderer.js';
import AgSelectFilter from '../AgSelectFilter.js';
import Multiselect from 'vue-multiselect'

let self = this;

export default {
    components: {
        ApplicationSidebar,
        AgGridVue,
        deleteBtnRenderer: DeleteBtnRenderer,
        downloadBtnRenderer: DownloadBtnRenderer,
        AgSelectFilter,
        Multiselect,
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
                filter: false,
                flex: 1,
                floatingFilter: false,
                resizable: true,
                suppressMovable: true,
                wrapText: true,
                autoHeight: true,
            },
            rowClassRules: {
                'tr-unread': (params) => {
                    // console.log('tr-undread', params);

                    for (let i = 0; i < params.data.opened_statuses.length; i++) {
                        if (params.data.opened_statuses[i].user_id == this.currentUser.id) {
                            return params.data.opened_statuses[i].status === 'unread';
                        }
                    }
                    return false;
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
                // { value: '*', name: 'Все' },
                { value: 'product', name: 'заявка на товар' },
                { value: 'equipment', name: 'заявка на спец. технику' },
                { value: 'service', name: 'заявка на услуги' }
            ],
            statusOptions: [
                // { value: '*', name: 'Все' },
                { value: 'incoming', name: 'На подпись'},
                { value: 'in_progress', name: 'В процессе исполнения'},
                { value: 'signed', name: 'Материально исполнена'},
                { value: 'completed', name: 'Закрыта'},
            ],

            chosenObject: null,
            objects: [],

            filter_construction_ids: [],
            filter_category_ids: [],
            filter_kind_ids: [],
            filter_status_ids: [],
            filter_period_to: null,
            filter_period_from: null,

            construction_ids: [],
            kind_ids: [],
            status_ids: [],

            higher: [],
        }
    },

    methods: {
        downloadTable() {
            location.href = '/export/table?data=' + JSON.stringify({data: this.rowData.value, user: this.currentUser});
        },

        clearFilterTotal() {
            this.filter_construction_ids = [];
            this.filter_category_ids = [];
            this.filter_kind_ids = [];
            this.filter_status_ids = [];
            this.filter_period_to = null;
            this.filter_period_from = null;

            this.construction_ids = [],
            this.kind_ids = [],
            this.status_ids = [],

            this.getApplications(this.chosenObject, 'all');
        },

        addFilterStatus(option, id) {
            this.status_ids.push(option.value);
            this.filterTotal();
        },

        removeFilterStatus(option, id) {
            this.status_ids = this.status_ids.filter(f => f != option.value);
            this.filterTotal();
        },

        addFilterKind(option, id) {
            this.kind_ids.push(option.value);
            this.filterTotal();
        },

        removeFilterKind(option, id) {
            this.kind_ids = this.kind_ids.filter(f => f != option.value);
            this.filterTotal();
        },

        addFilterConstruction(option, id) {
            this.construction_ids.push(option.id);
            this.filterTotal();
        },

        removeFilterConstruction(option, id) {
            this.construction_ids = this.construction_ids.filter(f => f != option.id);
            this.filterTotal();
        },

        filterTotal() {
            console.log(this.status_ids);

            const query = `q=${JSON.stringify({
                constructions: this.construction_ids, 
                kinds: this.kind_ids,
                statuses: this.status_ids,
                period_from: this.filter_period_from,
                period_to: this.filter_period_to,
            })}`;
            console.log(query, 'query');

            axios.get(`/api/v1/filter-applications?${query}`,).then((response) => {
                this.applications = response.data.data;
                this.rowData.value = response.data.data;

                console.log(this.rowData.value);
            })
        },

        getObjects() {
            // console.log(this.$route.params, 'params');
            axios.get('/api/v1/constructions').then((response) => {
                // console.log(response.data);
                this.objects = response.data.data;

                if (this.$route.query.construction_id) {
                    this.objects.forEach(o => {
                        // console.log(o.id, this.$route.query.construction_id);

                        if (o.id == this.$route.query.construction_id) {
                            this.selectConstruction(o)
                        }
                    })
                }
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
                        field: "num", minWidth: 100, headerName: '№ заявки', cellRenderer: (params) => {
                            return params.value ? params.value : `(${params.data.id})`;
                        }
                    },
                    {
                        field: "construction.name", minWidth: 200, headerName: 'Объект', 
                    },
                    {
                        field: "category",
                        minWidth: 200,
                        headerName: 'Статья расходов',
                        cellRenderer: (params) => {
                            // console.log('cell', params.data);
                            let categories = [];

                            if (params.data.kind === 'product' && params.data.application_application_products && params.data.application_application_products.length > 0) {
                                return params.data.application_application_products[0].category?.name;
                            } else if (params.data.kind === 'equipment' && params.data.application_equipments && params.data.application_equipments.length > 0) {
                                return 'Аренда спец. техники';
                            } else if (params.data.kind === 'service' && params.data.application_services && params.data.application_services.length > 0) {
                                return params.data.application_services[0].category;
                            }
                        },
                    },
                    {
                        field: "kind", minWidth: 200, headerName: 'Тип заявки', cellRenderer: (params) => {
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
                        field: "created_at", minWidth: 125, headerName: 'Дата', type: ['dateColumn'], filterParams: {
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
                        field: "status", minWidth: 300, headerName: 'Статус', valueGetter: this.getStatus, cellStyle: params => {
                            // console.log(params);

                            if (params.data.status === 'declined') {
                                //mark police cells as red
                                return { color: 'red' };
                            }
                            return null;
                        },
                    },
                    {
                        field: "action",
                        minWidth: 200,
                        headerName: '',
                        cellRendererSelector: (params) => {
                            // console.log('cell', params);

                            return {
                                component: 'downloadBtnRenderer',
                                params: {
                                    can: this.isMaterialAccountant(),
                                    clicked: this.downloadApplication,
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

        isMaterialAccountant() {
            return this.currentUser != null && this.currentUser.roles[0].title === 'Material Accountant';
        },

        downloadApplication(id) {
            window.location = `/export/application/${id}`;
        },  

        getHigher() {

        },

        showApplication(id) {
            this.$router.push(`/applications/${id}/edit`)
        },

        getApplications(construction, status) {
            console.log(`get applications with ${status} status for ${construction.id}`);

            // get applications 
            axios.get(`/api/v1/applications?status=${status}&construction_id=${construction.id}`).then((response) => {
                this.applications = response.data.data;
                this.rowData.value = response.data.data;

                // this.filterTotal();
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
            // console.log(params?.data);
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
            // var statuses = {
            //     'draft': 'черновик',
            //     'in_progress': 'в процессе',
            //     'in_review': 'на подпись',
            //     'declined': 'отклонена',
            //     'completed': 'закрыта',
            // };

            // return statuses[params.data.status];
            if (params.data.status == 'completed') {
                return 'закрыта';
            }

            if (params.data.is_signed == 1) {
                return 'материально исполнена';
            }

            if (params.data.status == 'draft') {
                return 'черновик';
            }

            const toBeSignedByMe = params.data.application_application_statuses.find(s => {
                if (this.currentUser != null && s.status == 'incoming' && s.application_path != null && s.application_path.responsible.id == this.currentUser.id) {
                    return true;
                }

                return false;
            })

            if (toBeSignedByMe) {
                return 'на подпись';
            }

            // TODO: who is next from highers
            const signedByNext = params.data.application_application_statuses.find(s => {
                if (s.status == 'incoming' && s.application_path != null) {
                    return true;
                }

                return false;
            })

            if (signedByNext) {
                return `В процессе исполнения: ${signedByNext?.application_path?.responsible?.name}`;
            }

            return '-';
        },

        isWarehouseManager() {
            return this.currentUser != null && this.currentUser.roles[0].title === 'Warehouse Manager';
        },

        getActions(params) {

        },

        onGridReady: (params) => {
            // console.log(params);
            // this.gridApi.value = params.api;
        },

        cellWasClicked: (event) => { // Example of consuming Grid Event
            // console.log("cell was clicked", event);
        },

        deselectRows: () => {
            this.gridApi.value.deselectAll()
        },

        rowWasClicked: (r) => {
            const id = r.data.id;
            // console.log(this);
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

.ag-cell,
.ag-full-width-row .ag-cell-wrapper.ag-row-group {
    line-height: 20px;
}
</style>