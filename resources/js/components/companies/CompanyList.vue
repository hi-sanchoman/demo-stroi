<template>
  <div style="padding: 20px;">
    <!-- <v-container> -->
    <!-- <v-row no-gutters>
                <v-col cols="12">
                    <h1 class="w-full text-left">Заявки</h1>
                </v-col>
            </v-row> -->

    <v-row no-gutters class="">
      <v-col cols="12" class="mb-5 flex flex-row justify-between">
        <div class="d-flex flex-row w-100 justify-space-between">
          <h2>Клиенты</h2>

          <div>
            <router-link to="/companies/create" color="blue" size="small" class="text-decoration-none">
              <v-btn color="primary">Добавить</v-btn>
            </router-link>
          </div>
        </div>
      </v-col>

      <v-col cols="12" class="">
        <ag-grid-vue v-if="currentUser != null" class="ag-theme-alpine" style="height: 800px"
          :columnDefs="columnDefs.value" :rowData="rowData.value" :defaultColDef="defaultColDef" animateRows="true"
          @row-clicked="showPosition" :localeText="localeText" @grid-ready="onGridReady"></ag-grid-vue>
      </v-col>
    </v-row>

    <!-- </v-container> -->

  </div>
</template>

<script>
import axios from 'axios'
import { AgGridVue } from "ag-grid-vue3";
import "ag-grid-community/styles/ag-grid.css"; // Core grid CSS, always needed
import "ag-grid-community/styles/ag-theme-alpine.css"; // Optional theme CSS
import { reactive, ref } from "vue";
import DeleteBtnRenderer from '../grid/DeleteBtnRenderer.js';
import AgSelectFilter from '../AgSelectFilter.js';

export default {
  components: {
    AgGridVue,
    deleteBtnRenderer: DeleteBtnRenderer,
    AgSelectFilter
  },

  data() {
    return {
      companies: [],
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
      statusOptions: [
        { value: '', name: 'Все' },
        // { value: 'lead', name: 'лид' },
        // { value: 'prospect', name: 'потенциальный' },
        // { value: 'customer', name: 'клиент' },
        // { value: 'recurrent', name: 'повторный' },
        { value: 'supplier', name: 'поставщик' },
        { value: 'service', name: 'услуги' },
      ]
    }
  },

  methods: {
    dateFormatter(params) {
      var dateAsString = params.data.item.created_at;
      var dateParts = dateAsString.split('/');
      return `${dateParts[0]} - ${dateParts[1]} - ${dateParts[2]}`;
    },

    getCompanies() {
      axios.get('/api/v1/companies').then((response) => {
        console.log(response.data.data);

        this.rowData.value = response.data.data;
      })
    },

    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data
        // console.log(this.currentUser)

        // columns
        this.columnDefs.value = [
          { field: 'item.id', minWidth: 100, headerName: '№', filter: 'agNumberColumnFilter', valueGetter: this.getIndex },
          { field: 'item.name', minWidth: 300, headerName: 'Название', valueGetter: this.getName },
          {
            field: 'item.status', minWidth: 300, headerName: 'Статус', valueGetter: this.getStatus, filter: AgSelectFilter, filterParams: {
              column: 'status',
              options: this.statusOptions,
            },
          },
          { field: 'item.service', minWidth: 150, headerName: 'Вид услуги', valueGetter: this.getService },
          { field: 'item.phone', minWidth: 150, headerName: 'Телефон', valueGetter: this.getPhone },
          { field: 'item.email', minWidth: 150, headerName: 'Эл. почта', valueGetter: this.getEmail },
          {
            field: "action",
            headerName: 'Действие',
            cellRendererSelector: (params) => {
              return {
                component: 'deleteBtnRenderer',
                params: {
                  can: params.data.responsible_id === this.currentUser.id,  // TODO: only creator can delete
                  clicked: this.remove,
                }
              }
            },
          },
        ];

        this.getCompanies();
      })
    },

    remove: (id) => {
      if (!confirm('Вы действительно хотите удалить?')) return;

      axios.delete(`/api/v1/companies/${id}`).then((response) => {
        if (response.data != 1) { return; }
        location.reload();
      })
    },

    addCompany: () => {
      this.$router.push(`/companies/create`);
    },

    onGridReady: (params) => {
      console.log('grid ready', params);
    },

    showPosition(params) {
      console.log('show company', params);
      this.$router.push(`/companies/${params.data.id}/edit`);
    },

    cellWasClicked: (event) => { // Example of consuming Grid Event
      console.log("cell was clicked", event);
      // this.showPosition(event.data);
    },

    getIndex: (params) => {
      // console.log(params);
      return params.node.rowIndex + 1;
    },

    getName: (params) => {
      // console.log(params);
      return params.data.name;
    },

    getKind(params) {

    },

    getService(params) {
      return params.data.service;
    },

    getStatus: (params) => {
      var statuses = {
        'supplier': 'поставщик',
        'service': 'услуги',
      };

      return typeof params === 'string' ? statuses[params] : statuses[params?.data?.status];

      // if ($params?.data?.status === 'service') {
      //   $statusStr += '<br>' + $params?.data?.service;
      // }

      // return $statusStr;
    },

    getPhone: (params) => {
      return params.data.phone;
    },

    getEmail: (params) => {
      return params.data.email;
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
    }

  },

  mounted() {
    // this.getApplications('draft')
    this.getCurrentUser();
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