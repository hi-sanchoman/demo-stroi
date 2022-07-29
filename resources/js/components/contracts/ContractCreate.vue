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
          <ContractSidebar v-if="currentUser != null" :currentUser="currentUser" />
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

          <v-form class="space-y-6">
            <v-row>
              <template v-if="form.kind == 'default'">
                <v-col cols="12" md="4">
                  <v-text-field v-model="form.name" label="Название договора" variant="underlined" required
                    density="comfortable" type="text"></v-text-field>
                </v-col>

                <v-col cols="12" md="3">
                  <label for="file">Загрузите приложение</label>
                  <input type="file" id="file" v-on:change="handleFile($event)" aria-label="file" />
                </v-col>
              </template>

              <!-- common -->
              <v-col cols="12" md="2">
                <v-textarea v-if="isAddNotes" outlined v-model="form.notes" label="Примечание" variant="underlined"
                  density="comfortable"></v-textarea>
                <v-btn v-if="!isAddNotes" color="" size="small" @click="showNotes()">
                  + примечание
                </v-btn>
              </v-col>
            </v-row>


            <v-btn v-if="form.name != null && form.file_application != null" class="mt-5" @click="save" color="primary">
              Создать договор
            </v-btn>
          </v-form>
        </v-col>
      </v-row>

    </v-container>
  </div>
</template>


<script>
import axios from 'axios'
import ContractSidebar from "./ContractSidebar.vue";

export default {
  components: {
    ContractSidebar,
  },

  inject: ['isLoading'],

  data() {
    return {

      isAddNotes: false,

      form: {
        name: null,
        file_application: null,
        notes: null,
      },

      currentUser: null,
      oldKind: null,
    }
  },

  mounted() {
    this.isLoading = true

    // get current user
    this.getCurrentUser();
  },

  methods: {
    getCurrentUser() {
      axios.get('/api/v1/me').then((response) => {
        this.currentUser = response.data;
      });
    },

    showNotes() {
      this.isAddNotes = true
    },

    add() {
      // console.log('add product', this.current.product.name, this.current.quantity, this.current.notes)
      if (this.current.quantity == null) {
        return;
      }

      if (this.form.kind == 'product') {
        this.products.push({
          id: this.products.length + 1,
          product: this.current.product,
          unit: this.current.unit,
          category: this.current.category,
          quantity: this.current.quantity,
          notes: this.current.notes,
        });
      }

      else if (this.form.kind == 'equipment') {
        this.equipments.push({
          id: this.equipments.length + 1,
          equipment: this.current.equipment,
          quantity: this.current.quantity,
          notes: this.current.notes,
          days: this.current.days,
        });

        // console.log(this.equipments);
      }

      else if (this.form.kind == 'service') {
        this.services.push({
          id: this.services.length + 1,
          service: this.current.service,
          unit: this.current.unit,
          category: this.current.category,
          quantity: this.current.quantity,
          notes: this.current.notes,
        });

        // console.log(this.equipments);
      }

      this.current = {
        product: null,
        service: null,
        equipment: null,
        unit: null,
        category: null,
        quantity: null,
        notes: null,
        isAddNotes: false,
        days: null,
      }
    },

    deleteProduct(item) {
      if (this.form.kind == 'product') {
        this.products = this.products.filter(function (el) { return el.id != item.id })
      }

      else if (this.form.kind == 'equipment') {
        this.equipments = this.equipments.filter(el => el.id != item.id);
      }

      else if (this.form.kind == 'service') {
        this.services = this.services.filter(el => el.id != item.id);
      }
    },

    savecontract() {
      this.form.construction_id = this.form.construction.id

      if (this.form.kind == 'product') {
        this.form.products = this.products
      } else if (this.form.kind == 'equipment') {
        this.form.equipments = this.equipments;
      } else if (this.form.kind == 'service') {
        this.form.services = this.services;
      }

      // console.log(this.form)
      // return;

      try {
        axios.post('/api/v1/contracts', this.form).then((response) => {
          var contract = response.data.data
          this.$router.push({ name: 'contracts.edit', params: { id: contract.id } })
        })
      } catch (e) {
        console.log(e)

        if (e.response.status === 422) {
          // errors.value = e.response.data.errors
        }
      }
    }
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