<template>
  <div>
    <v-container>
      <!-- <v-row no-gutters>
                <v-col cols="12">
                    <h1 class="w-full text-left">Заявки</h1>
                </v-col>
            </v-row> -->

      <v-row no-gutters class="mt-10 d-flex flex-column-reverse flex-sm-row">
        <v-col cols="12" md="3" class="border px-5 py-5">
          <ContractSidebar v-if="currentUser != null" :currentUser="currentUser" />

          <div v-if="currentUser != null && contract != null">
            <h4 class="mt-4">Комментарии к договору:</h4>

            <div class="d-flex flex-column">
              <div class="d-flex flex">
                <v-text-field
                  v-model="comment"
                  variant="underlined"
                />
                <v-btn @click="sendComment" size="small">Отправить</v-btn>
              </div>

              <div
                class="d-flex flex-column"
                style="height: 500px; overflow-y: auto"
              >
                <div
                  v-for="comment in contract.comments"
                  :key="comment.id"
                  class="d-flex flex-column mb-4"
                >
                  <div>
                    <strong>{{ comment.user.name }}</strong>
                  </div>
                  <div class="text-body-2" style="color: #ccc">
                    {{ comment.created_at }}
                  </div>
                  <div>
                    {{ comment.comment }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </v-col>

        <v-col cols="12" md="9" class="pl-0 pl-md-5 mt-4 mt-md-0">
          <div v-if="errors">
            <div v-for="(v, k) in errors" :key="k"
              class="bg-red-500 text-white rounded font-bold mb-4 shadow-lg py-2 px-4 pr-0">
              <p v-for="error in v" :key="error" class="text-sm">
                {{ error }}
              </p>
            </div>
          </div>

          <h2>
            {{ getKind(contract?.kind) }} {{ contract?.company_ceo }} №{{ contract?.id }}
          </h2>

          <h5>от {{ contract?.created_at }}</h5>
          <h5>Инициатор: {{ contract?.owner?.name }}</h5>

          <v-table class="mt-4">
            <tr>
              <th style="text-align: left">Бланк</th>
              <th style="text-align: left">Договор</th>
              <th style="text-align: left">Подписанный документ</th>
            </tr>

            <tr style="vertical-align: top;">
              <td>
                <a v-if="contract?.id" size="small" color="primary" target="_blank"
                  class="mt-3 v-btn v-btn--elevated v-theme--light v-btn--density-default v-btn--size-small v-btn--variant-contained"
                  :href="`/export/contracts/${contract?.id}`">Скачать бланк</a>


                <div class="mt-4">
                  <a target="_blank" v-if="contract?.file_price" :href="`/uploads/${contract?.file_price}`"
                    class='d-block mb-2 text-blue-500'>Скачать прайс (Приложение №1)</a>
                  <a target="_blank" v-if="contract?.file_smeta" :href="`/uploads/${contract?.file_smeta}`"
                    class='d-block mb-2 text-blue-500'>Скачать смету</a>
                  <a target="_blank" v-if="contract?.file_passport" :href="`/uploads/${contract?.file_passport}`"
                    class='d-block mb-2 text-blue-500'>Скачать тех. пасспорт</a>
                </div>
              </td>
              <td>
                <template v-if="isLawyer() && !contract?.file_contract && contract.status !== 'draft'">
                  Загрузить договор (текст)
                  <input type="file" id="file" v-on:change="handleFileUpload($event)" aria-label="upload contract" />
                </template>

                <a v-if="contract?.file_contract" target="_blank" :href="`/uploads/${contract?.file_contract}`"
                  class="mt-3 v-btn v-btn--elevated v-theme--light v-btn--density-default v-btn--size-small v-btn--variant-contained">Скачать
                  договор (текст)</a>
              </td>
              <td>
                <template v-if="isLawyer() && !contract?.file_singed && contract.status === 'signed_by_ceo'">
                  Загрузить подписанный договор
                  <input type="file" id="file" v-on:change="handleFileSignedUpload($event)" aria-label="upload contract" />
                </template>

                <a v-if="contract?.file_singed" target="_blank" :href="`/uploads/${contract?.file_singed}`"
                  class="mt-3 v-btn v-btn--elevated v-theme--light v-btn--density-default v-btn--size-small v-btn--variant-contained text-white" style="background: green">
                  Скачать договор (подписанный)
                </a>
              </td>
            </tr>
          </v-table>

          <template v-if="contract?.status === 'draft' && currentUser?.id === contract?.owner_id">
            <v-btn class="mt-5" size="small" color="success" @click="sign(contract)">Отправить на утверждение</v-btn>
          </template>

          <!-- sing in flow -->
          <v-row no-gutters class="mt-5" v-if="contract?.status !== 'draft'">
            <v-col no-gutters cols="12">
              <v-expansion-panels no-gutters v-model="showPanels">
                <v-expansion-panel value="sign">
                  <v-expansion-panel-title>
                    Утверждение заявки
                  </v-expansion-panel-title>

                  <v-expansion-panel-text no-gutters style="" class="px-0">
                    <v-list-item no-gutters v-for="(
                          item, index
                      ) in contract?.contract_contract_statuses" :key="item.id">
                      <v-list-item-header>
                        <v-list-item-title>
                          <v-col cols="12" class="px-0">
                            <strong>{{
                            index + 1
                            }}.
                              {{
                              item
                              .contract_path
                              .position
                              }}</strong>
                            -
                            {{
                            item
                            .contract_path
                            .responsible
                            .name
                            }}
                          </v-col>

                          <!-- declined -->
                          <span v-if="
                              item.status ==
                              'declined'
                          ">
                            <v-chip class="mr-2" color="red" text-color="white">
                              Отклонено
                            </v-chip>

                            <span>{{
                            item.declined_reason
                            }}</span>
                            -
                            <span>{{
                            item.updated_at
                            }}</span>
                          </span>
                          <!-- accepted -->
                          <span v-else-if="
                              item.status ==
                              'accepted'
                          ">
                            <v-chip class="mr-2" color="green" text-color="white">
                              Одобрено
                            </v-chip>

                            <span>{{
                            item.updated_at
                            }}</span>
                          </span>

                          <!-- incoming -->
                          <span v-else-if="
                              currentUser !=
                              null &&
                              item.status ==
                              'incoming' &&
                              item
                                .contract_path
                                .responsible
                                .id ==
                              currentUser.id
                          ">
                            <v-btn v-if="!isLawyer() || (isLawyer() && contract?.file_contract && !isPrefinal())" size="small"
                              color="success" @click="signContract(item)" class="mr-5">
                              Подписать
                            </v-btn>

                            <!-- подписать без эцп -->
                            <v-btn v-if="isLawyer() && isPrefinal()" size="small"
                              color="success" @click="signContractAs(item, 'default')" class="mr-5">
                              На подпись (стандарт)
                            </v-btn>

                            <!-- подписать с эцп -->
                            <v-btn v-if="isLawyer() && isPrefinal()" size="small"
                              color="success" @click="signContractAs(item, 'ecp')" class="mr-5">
                              На подпись с ЭЦП
                            </v-btn>

                            <v-btn v-if="item.contract_path.responsible.id !=contract.owner_id" size="small" 
                              color="error" @click="showDecline(item)" class="">
                              Отклонить
                            </v-btn>
                          </span>
                          <span v-else>...</span>
                        </v-list-item-title>
                      </v-list-item-header>
                    </v-list-item>
                  </v-expansion-panel-text>
                </v-expansion-panel>
              </v-expansion-panels>
            </v-col>
          </v-row>


        </v-col>
      </v-row>

    </v-container>
  </div>

  <!-- Snackbar -->
  <v-snackbar v-model="snackbar.status" :timeout="snackbar.timeout">
    {{ snackbar.text }}

    <template v-slot:actions>
      <v-btn color="blue" variant="text" @click="snackbar.status = false">
        Закрыть
      </v-btn>
    </template>
  </v-snackbar>

  <!-- Decline dialog -->
  <v-dialog v-model="declineDialog" persistent>
    <v-card>
      <v-card-title>
        <span class="text-h5">Отклонить заявку?</span>
      </v-card-title>

      <v-card-text>
        <v-container>
          <v-row>
            <v-col cols="12">
              <v-textarea v-model="declinedReason" label="Причина отклонения*" required></v-textarea>
            </v-col>
          </v-row>
        </v-container>
        <small>* обязательные поля</small>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>

        <v-btn color="blue-darken-1" text @click="declineDialog = false">
          Отмена
        </v-btn>

        <v-btn color="red-darken-1" text @click="declineContract()">
          Отклонить
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>


<script>
import axios from 'axios'
import ContractSidebar from "./ContractSidebar.vue";
import { store } from '../../store.js'

export default {
  components: {
    ContractSidebar,
  },

  inject: ['isLoading'],

  data() {
    return {
      snackbar: {
        status: false,
        text: "",
        timeout: 2000,
      },
      
      store,

      declinedContractStatus: null,
      declineDialog: false,
      declinedReason: "",

      currentUser: null,
      contract: null,
      showPanels: ["sign"],

      SOCKET_URL: 'wss://127.0.0.1:13579/',
      callback: null,
      response: null,
      webScoket: null,
      
      comment: null,
    }
  },

  mounted() {
    this.isLoading = true

    // get current user
    this.getCurrentUser();
  },

  methods: {
    isPrefinal() {
      let status = this.contract.contract_contract_statuses[this.contract.contract_contract_statuses.length - 2];

      return status.status === 'incoming';
    },

    sendComment() {
      if (!this.comment) return;

      const data = {
        comment: this.comment,
        contract_id: this.contract.id,
        user_id: this.currentUser.id,
      };

      axios.post("/api/v1/contract-comments", data).then((response) => {
        this.contract.comments = response.data;
      });
    },
    
    isLawyer() {
      return this.currentUser?.roles[0].title === 'Lawyer';
    },

    showDecline(status) {
      this.declineDialog = true;
      this.declinedContractStatus = status;
    },

    sign(contract) {
      var data = { method: "sign" };

      axios
        .put(
          "/api/v1/contracts-first-sign/" + contract.id,
          data
        )
        .then((response) => {
          if (response.data === -1) {
            this.snackbar.text = "Ошибка";
            this.snackbar.status = true;
            return;
          }

          this.getContract();

          this.snackbar.text = "Заявка успешно подписана.";
          this.snackbar.status = true;
        });
    },

    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data;

        this.getContract()
      });
    },

    getCountNewBadges() {
      axios
        .get("/api/v1/badges-unread?type=contracts")
        .then((response) => {
          console.log("count of badges: ", response.data);
          this.store.badgeContracts = response.data.total;
        });
    },

    getContract() {
      axios
        .get("/api/v1/contracts/" + this.$route.params.id)
        .then((response) => {
          this.contract = response.data;

          // count badges
          this.getCountNewBadges();
        });
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

    signContract(status) {
      var data = { method: "sign" };

      // ceo -> show ncalayer
      if (status.contract_path.order === 999 && status.signature === 'ecp') {
        this.signNCA(status);        
        return;
      }

      axios
        .put(
          "/api/v1/contract-statuses/" + status.id,
          data
        )
        .then((response) => {
          if (response.data === -1) {
            this.snackbar.text = "Ошибка: заполните все поля в бланке.";
            this.snackbar.status = true;

            return;
          }

          this.getContract();

          this.snackbar.text = "Заявка успешно подписана.";
          this.snackbar.status = true;
        });
    },

    signContractAs(status, type) {
      var data = { method: "sign", type };

      axios
        .put(
          "/api/v1/contract-statuses/" + status.id,
          data
        )
        .then((response) => {
          if (response.data === -1) {
            this.snackbar.text = "Ошибка: заполните все поля в бланке.";
            this.snackbar.status = true;

            return;
          }

          this.getContract();

          this.snackbar.text = "Заявка успешно подписана.";
          this.snackbar.status = true;
        });
    },

    connect() {
      if (this.webSocket && this.webSocket.readyState < 2) {
        console.log("reusing the socket connection [state = " + this.webSocket.readyState + "]: " + this.webSocket.url);
        return Promise.resolve(this.webSocket);
      }

      return new Promise((resolve, reject) => {
        this.webSocket = new WebSocket('wss://127.0.0.1:13579/');

        this.webSocket.onopen = () => {
          console.log("socket connection is opened [state = " + this.webSocket.readyState + "]: " + this.webSocket.url);
          resolve(this.webSocket);
        };

        this.webSocket.onerror = (err) => {
          this.unblockScreen();
          console.error("socket connection error : ", err);
          reject(err);
        };

        this.webSocket.onclose = (event) => {
          if (event.wasClean) {
            console.error("socket connection is closed ");
          } else {
            console.log('Connection error');
            openDialog();
          }
          console.log('Code: ' + event.code + ' Reason: ' + event.reason);
        };
      });
    },

    requestNCA(status) {
      this.blockScreen();

      var selectedStorages = []
      var storageCheckboxes = document.querySelectorAll('input[name=storage-check]:checked')
      for (var i = 0; i < storageCheckboxes.length; i++) {
        selectedStorages.push(storageCheckboxes[i].value)
      }

      var signatureType = null;
      var dataToSign = '123123';
      var decode = true;
      var encapsulate = false;
      var digested = false;
      var extKeyUsageOidString = null;
      var extKeyUsageOids = extKeyUsageOidString ? extKeyUsageOidString.split(",") : [];
      var caCertsString = null;
      var caCerts = caCertsString ? caCertsString.split(",") : null;
      caCerts = [];
      var localeRadio = 'ru';
      var tsaProfile = {};

      var signInfo = {
        "module": "kz.gov.pki.knca.basics",
        "method": "sign",
        "args": {
          "allowedStorages": [],
          "format": 'cms',
          "data": dataToSign,
          "signingParams": { decode, encapsulate, digested, tsaProfile },
          "signerParams": {
            "extKeyUsageOids": extKeyUsageOids,
            "chain": caCerts
          },
          "locale": localeRadio
        }
      }

      if (selectedStorages.length == 0) {
        delete signInfo.args.allowedStorages;
      }

      return this.connect().then((webSocket) => {
        webSocket.send(JSON.stringify(signInfo))

        return new Promise((resolve, reject) => {
          webSocket.onmessage = ({ data }) => {
            this.response = JSON.parse(data);
            if (this.response != null) {
              var responseStatus = this.response['status'];
              if (responseStatus === true) {
                var responseBody = this.response['body'];
                if (responseBody != null) {
                  this.unblockScreen();

                  if (responseBody.hasOwnProperty('result')) {
                    var result = responseBody.result;
                    console.log('result', result);

                    let data = {
                      'method': 'sign',
                      'cms': result
                    };

                    axios
                      .put(
                        "/api/v1/contract-statuses/" + status.id,
                        data
                      )
                      .then((response) => {
                        if (response.data === -1) {
                          this.snackbar.text = "Ошибка: заполните все поля в бланке.";
                          this.snackbar.status = true;

                          return;
                        }

                        this.getContract();

                        this.snackbar.text = "Заявка успешно подписана.";
                        this.snackbar.status = true;
                      });

                    return;
                  }
                }
              } else if (responseStatus === false) {
                this.unblockScreen();
                var responseCode = this.response['code'];
                alert(responseCode);
              }
            }
            resolve(this.response);
          }
        })
      })
        .catch(function (err) {
          // this.unblockScreen();
          console.log(err)
        });
    },

    signNCA(status) {
      console.log('load NCA');
      this.requestNCA(status);
    },

    blockScreen() {
      console.log('show UI');
    },

    unblockScreen() {
      console.log('hide UI');
    },

    openDialog() {
      if (confirm("NCALayer-ге қосылғанда қате шықты. NCALayer-ды қайта қосып, ОК-ді басыңыз\nОшибка при подключении к NCALayer. Запустите NCALayer и нажмите ОК") === true) {
        location.reload();
      }
    },



    declineContract() {
      var data = {
        method: "decline",
        declined_reason: this.declinedReason,
      };

      axios
        .put(
          "/api/v1/contract-statuses/" +
          this.declinedContractStatus.id,
          data
        )
        .then((response) => {
          this.getContract();

          this.snackbar.text = "Заявка отклонена.";
          this.snackbar.status = true;

          this.declineDialog = false;
          this.declinedContractStatus = null;
          this.declinedReason = "";
        });
    },

    handleFileUpload(event) {
      this.file = event.target.files[0];

      let formData = new FormData();
      formData.append("file", this.file);
      formData.append("contract_id", this.contract.id);

      axios
        .post("/upload-contract", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        })
        .then((response) => {
          this.getContract();

          this.snackbar.text = "Договор загружен";
          this.snackbar.status = true;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    
    handleFileSignedUpload(event) {
      this.file = event.target.files[0];

      let formData = new FormData();
      formData.append("file", this.file);
      formData.append("contract_id", this.contract.id);
      formData.append("type", "signed");

      axios
        .post("/upload-contract", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        })
        .then((response) => {
          this.getContract();

          this.snackbar.text = "Договор загружен";
          this.snackbar.status = true;
        })
        .catch((error) => {
          console.error(error);
        });
    },
  },

  watch: {
    '$route.query': {
      handler(newValue) {
        if (!this.oldKind) {
          this.oldKind = newValue;
          return;
        }

        location.reload();
      },
      immediate: true,
    }
  }
}
</script>


<style scoped>
input,
select,
textarea {
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 10px 7px;
  width: 50%;
  display: block;
}
</style>