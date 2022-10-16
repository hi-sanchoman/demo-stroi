<template>
  <div style="padding: 20px;">
    <v-row no-gutters class="mt-10">
      <v-col cols="12" md="2" class="border px-5 py-5">
        <ContractSidebar v-if="currentUser != null" :currentUser="currentUser" />
      </v-col>

      <v-col cols="12" md="10" class="pl-0 pl-md-5 mt-4 mt-md-0">
        <v-table transition="slide-x-transition">
          <thead>
            <tr>
              <th class="text-left">
                Наименование ТОО, ИП
              </th>
              <th class="text-left">
                Ответственный
              </th>
              <th class="text-left">
                БИН, ИИН
              </th>
              <th class="text-left">
                № договора
              </th>
              <th class="text-left">
                Дата
              </th>
              <th class="text-left">
                Вид договора
              </th>
              <th class="text-left">
                Наименование объекта
              </th>
              <th class="text-left">
                Предмет договора
              </th>
              <th class="text-left">
                Сумма договора
              </th>
              <th class="text-left">
                Статус
              </th>
              <th>
                
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><input @change="setFilter" id="name" type="text" v-model="filter_name" aria-label="filter_input" /></td>
              <td>
                <multiselect :selectLabel="''" :deselectLabel="''" @select="addFilterOwner" @remove="removeFilterOwner" v-model="filter_owner_ids" :options="ownerOptions" placeholder="Инициатор" label="name" track-by="name" multiple>
                </multiselect>
              </td>
              <td><input @change="setFilter" id="bin" type="text" v-model="filter_bin" aria-label="filter_input" /></td>
              <td><input @change="setFilter" id="no" type="text" v-model="filter_no" aria-label="filter_input" /></td>
              <td>
                <div class="d-flex mb-2 flex-column mr-1">
                  <div class="d-flex">От: <input class="ml-1 text-grey" type="date" @change="filterTotal" v-model="filter_period_from" aria-label="from"/></div>
                  <div class="d-flex">До: <input class="ml-1 text-grey" type="date" @change="filterTotal" v-model="filter_period_to" aria-label="from"/></div>
                </div>
              </td>
              <td>
                <multiselect :selectLabel="''" :deselectLabel="''" @select="addFilterKind" @remove="removeFilterKind" v-model="filter_kind_ids" :options="kindOptions" placeholder="Вид договора" label="name" track-by="name" multiple>
                </multiselect>
              </td>
              <td><input @change="setFilter" id="object" type="text" v-model="filter_object" aria-label="filter_input" /></td>
              <td><input @change="setFilter" id="subject" type="text" v-model="filter_subject" aria-label="filter_input" /></td>
              <td><input @change="setFilter" id="price" type="text" v-model="filter_price" aria-label="filter_input" /></td>
              <td>
                <multiselect :selectLabel="''" :deselectLabel="''" @select="addFilterStatus" @remove="removeFilterStatus" v-model="filter_status_ids" :options="statusOptions" placeholder="Статус" label="name" track-by="value" multiple>
                </multiselect>
              </td>
              <td><button @click="clearFilterTotal()">Очистить фильтр</button></td>
            </tr>

            <tr v-if="contracts.length <= 0">
              <td colspan="11">Нет договоров.</td>
            </tr>

            <tr @click="showContract(contract.id)" v-for="contract in contracts" :key="contract.id" :class="isUnread(contract)">
              <td style="cursor: pointer">{{ contract.company_name}}</td>
              <td style="cursor: pointer">{{ contract.owner.name }}</td>
              <td style="cursor: pointer">{{ contract.company_bin }}</td>
              <td style="cursor: pointer">{{ contract.num }}</td>
              <td style="cursor: pointer">{{ contract.created_at }}</td>
              <td style="cursor: pointer">{{ getKind(contract.kind) }}</td>
              <td style="cursor: pointer">{{ contract.address }}</td>
              <td style="cursor: pointer">{{ contract.subject }}</td>
              <td style="cursor: pointer">{{ contract.price ? `${contract.price?.toLocaleString('ru')} тг` : ''}}</td>
              <!-- status -->
              <td>
                {{ contract.status }}
              </td>

              <td>
                <v-btn @click="deleteContract(contract.id)" color="error" size="small"
                  v-if="contract.status == 'draft' && isPTDEngineer()">
                  Удалить
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import ContractSidebar from "./ContractSidebar.vue"
import axios from 'axios'
import { store } from '../../store.js'
import { AgGridVue } from "ag-grid-vue3";
import "ag-grid-community/styles/ag-grid.css"; // Core grid CSS, always needed
import "ag-grid-community/styles/ag-theme-alpine.css"; // Optional theme CSS
import { reactive, ref } from "vue";
import DeleteBtnRenderer from '../grid/DeleteBtnRenderer.js';
import Multiselect from 'vue-multiselect'

export default {
  components: {
    ContractSidebar,
    AgGridVue,
    deleteBtnRenderer: DeleteBtnRenderer,
    Multiselect
  },

  data() {
    return {
      contracts: [],
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

      ownerOptions: [],
      kindOptions: [
        { value: 'sub', name: 'Субподряд' },
        { value: 'service', name: 'Услуга' },
        { value: 'delivery', name: 'Поставка' },
        { value: 'rent', name: 'Аренда помещения' },
        { value: 'equipment', name: 'Аренда спец. техники' },
      ],
      statusOptions: [
        { value: 'mine', name: 'Мои договора' },
        { value: 'incoming', name: 'На подпись'},
        { value: 'in_progress', name: 'В процессе исполнения'},
        { value: 'signed_by_ceo', name: 'Подписан'},
      ],


      filter_owner_ids: [],
      filter_kind_ids: [],
      filter_status_ids: [],
      filter_period_to: null,
      filter_period_from: null,
      filter_name: null,
      filter_bin: null,
      filter_no: null,
      filter_object: null,
      filter_subject: null,
      filter_price: null,

      owner_ids: [],
      kind_ids: [],
      status_ids: [],
    }
  },

  methods: {
    isUnread(contract) {
      console.log(contract);

      for (let i = 0; i < contract.opened_statuses.length; i++) {
        if (this.currentUser.id != null && contract.opened_statuses[i].user_id == this.currentUser.id) {
          if (contract.opened_statuses[i].status === 'unread') {
            return 'tr-unread';
          }
        }
      }

      return 'tr-read';
    },  

    getOwners() {
      axios.get('api/v1/contract-owners').then((response) => {
        this.ownerOptions = response.data;
      })
    },

    setFilter($event) {
      const val = $event.target.value;
      const key = $event.target.id;

      if (key == 'name') this.filter_name = val;
      if (key == 'bin') this.filter_bin = val;
      if (key == 'no') this.filter_no = val;
      if (key == 'object') this.filter_object = val;
      if (key == 'price') this.filter_price = val;
      if (key == 'subject') this.filter_subject = val;
      
      this.filterTotal();
    },

    clearFilterTotal() {
      this.filter_owner_ids = [];
      this.filter_kind_ids = [];
      this.filter_status_ids = [];
      this.filter_period_to = null;
      this.filter_period_from = null;
      this.filter_name = null;
      this.filter_bin = null;
      this.filter_no = null;
      this.filter_object = null;
      this.filter_subject = null;
      this.filter_price = null;

      this.owner_ids = [];
      this.kind_ids = [];
      this.status_ids = [];

      this.getContracts('all');
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

    addFilterOwner(option, id) {
        this.owner_ids.push(option.id);
        this.filterTotal();
    },

    removeFilterOwner(option, id) {
        this.owner_ids = this.owner_ids.filter(f => f != option.id);
        this.filterTotal();
    },

    filterTotal() {
        const query = `q=${JSON.stringify({
            owners: this.owner_ids, 
            kinds: this.kind_ids,
            statuses: this.status_ids,
            period_from: this.filter_period_from,
            period_to: this.filter_period_to,
            name: this.filter_name,
            bin: this.filter_bin,
            no: this.filter_no,
            object: this.filter_object,
            subject: this.filter_subject,
            price: this.filter_price,
        })}`;
        console.log(query, 'query');

        axios.get(`/api/v1/filter-contracts?${query}`,).then((response) => {
            this.contracts = response.data.data;
        })
    },
    
    isPTDEngineer() {
      return this.currentUser != null && this.currentUser.roles[0].title == 'PTD Engineer';
    },

    readContractsBadge() {
      axios.put('/api/v1/read-badge', { type: 'contracts' }).then((response) => {
        this.store.badgeContracts = 0;
      });
    },

    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data

        // columns
        // this.columnDefs.value = [
        //   { field: "id", headerName: '№ заявки', filter: 'agNumberColumnFilter' },
        //   { field: "construction.name", headerName: 'Объект' },
        //   { field: "kind", headerName: 'Тип заявки', valueGetter: this.getKind },
        //   { field: "issued_at", headerName: 'Дата', type: ['dateColumn'], filter: 'agDateColumnFilter' },
        //   { field: "status", headerName: 'Статус', valueGetter: this.getStatus },
        //   // { field: "signs", headerName: 'Подписи' },
        //   {
        //     field: "action",
        //     headerName: 'Действие',
        //     cellRendererSelector: (params) => {
        //       // console.log('cell', params);

        //       return {
        //         component: 'deleteBtnRenderer',
        //         params: {
        //           can: params.data.status == 'draft' && this.isPTDEngineer(),
        //           clicked: this.deletecontract,
        //         }
        //       }
        //     },
        //     // cellRenderer: this.deleteBtnRenderer,
        //     // cellRendererParams: {
        //     //     clicked: function (field) {
        //     //         alert(`${field} was clicked`);
        //     //     },
        //     // },
        //   },
        // ];

        if (this.$route.query.status == 'redirect') {
          console.log('redirect')

          // this.readcontractsBadge();

          // if (this.currentUser.roles[0].title == 'PTD Engineer') {
          //     this.$router.push('/contracts?status=draft')
          //     return
          // } else if (this.currentUser.roles[0].title == 'Supplier') {
          //     this.$router.push('/contracts?status=in_progress_supplier')
          //     return
          // } else if (this.currentUser.roles[0].title == 'Economist' ||
          //     this.currentUser.roles[0].title == 'Chief Financial Officer'
          // ) {
          //     this.$router.push('/contracts?status=in_progress_economist')
          //     return
          // }
          // } else if (this.currentUser.roles[0].title == 'Warehouse Manager') {
          //     this.$router.push('/contracts?status=in_progress_warehouse')
          //     return
          // }

          this.$router.push('/contracts?status=all')
        }
      })
    },

    showContract(id) {
      this.$router.push(`/contracts/${id}/edit`)
    },

    getContracts(status) {
      console.log(`get contracts with ${status} status`);

      // get contracts 
      axios.get('/api/v1/contracts?status=' + status).then((response) => {
        // this.contracts = response.data.data;
        // this.rowData.value = response.data.data;

        this.filterTotal();
      })
    },

    deleteContract(id) {
      if (!window.confirm('Вы действительно хотите?')) {
        return
      }

      axios.delete('/api/v1/contracts/' + id).then((response) => {
        this.getContracts('all')
      })
    },

    getStatusColor(item) {
      if (item.status == 'accepted') return 'green';
      if (item.status == 'declined') return 'declined';
      if (item.status == 'waiting' || item.status == 'incoming') return 'grey';
    },

    getStatusText(item) {
      return item.contract_path.responsible.name;
    },

    getKind(kind) {
      var kinds = {
        'sub': 'Субподряд',
        'rent': 'Аренда помещения',
        'equipment': 'Аренда спец. техники',
        'service': 'Услуга',
        'delivery': 'Поставка'
      };

      return kinds[kind];
    },

    // getStatus(params) {
    //   var statuses = {
    //     'draft': 'черновик',
    //     'in_progress': 'в процессе',
    //     'in_review': 'на рассмотрении',
    //     'declined': 'отклонена',
    //     'completed': 'закрыта',
    //   };

    //   return statuses[params.data.status];
    // },

    // getActions(params) {

    // },

    // onGridReady: (params) => {
    //   // console.log(params);
    //   this.gridApi.value = params.api;
    // },

    // cellWasClicked: (event) => { // Example of consuming Grid Event
    //   console.log("cell was clicked", event);
    // },

    // deselectRows: () => {
    //   this.gridApi.value.deselectAll()
    // }


  },

  mounted() {
    // this.getcontracts('draft')
    this.getCurrentUser();

    this.getOwners();

    // fetch("https://www.ag-grid.com/example-assets/row-data.json")
    //     .then((result) => result.json())
    //     .then((remoteRowData) => (this.rowData.value = remoteRowData));
  },

  watch: {
    '$route.query': {
      handler(newValue) {
        const { status } = newValue

        this.getContracts(status)
      },
      immediate: true,
    }
  }
}
</script>


<style scoped>
.tr-unread td {
  font-weight: bold;
}

input,
select,
textarea {
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 5px 5px;
  width: 100%;
  display: block;
}

@media screen and (max-width: 600px) {
  input,
  select,
  textarea {
    width: 100%;
  }
}

@media screen and (min-width: 768px) {
  .v-table {
    overflow-x: auto !important;
  }
}

</style>