<template>
  <div>
    <v-list density="compact" transition="slide-x-transition">
      <v-list-subheader class="d-block">
        Договоры

        <div class="float-right" v-if="currentUser != null && canCreateContract()">
          <v-btn color="primary" size="x-small" plain @click="openTypeChooser()">
            <v-icon>mdi-plus</v-icon>
          </v-btn>
        </div>
      </v-list-subheader>

      <router-link to="/contracts?status=all" class="text-decoration-none text-black">
        <v-list-item key="all" value="all" active-color="primary">
          <v-list-item-title v-text="'Все договоры'"></v-list-item-title>
        </v-list-item>
      </router-link>

      <v-list-item>
        <v-list-item-title v-text="' '"></v-list-item-title>
      </v-list-item>


      <!-- <template v-if="currentUser != null && currentUser.roles[0].title != 'Warehouse Manager'">
        <router-link to="/contracts?status=incoming" class="text-decoration-none text-black">
          <v-list-item key="incoming" value="incoming" active-color="primary">
            <v-list-item-title v-text="`Входящие`"></v-list-item-title>
          </v-list-item>
        </router-link>

        <router-link to="/contracts?status=declined_by_me" class="text-decoration-none text-black">
          <v-list-item key="declined_by_me" value="declined_by_me" active-color="primary">
            <v-list-item-title v-text="`Отклоненные мною`"></v-list-item-title>
          </v-list-item>
        </router-link>
      </template> -->

    </v-list>
  </div>

  <!-- Choose Application Type Dialog -->
  <v-dialog v-model="typeChooserDialog" persistent>
    <v-card class="oks-dialog min-w-5xl w-7xl" style="">
      <v-card-title>
        <span class="text-h5">Выберите тип договора</span>
      </v-card-title>

      <v-card-text>
        <v-container>
          <v-item-group v-model="chosenType" mandatory>
            <v-container>
              <v-row>
                <v-col v-for="kind in kinds" :key="kind.id" cols="12" md="4">
                  <v-item v-slot="{ isSelected, toggle }">
                    <v-card :color="isSelected ? 'primary' : ''" class="d-flex align-center oks-type-chooser" dark
                      @click="toggle">
                      <v-scroll-y-transition>
                        <div class="text-sm flex-grow-1 text-center">
                          {{ isSelected ? 'Выбрано' : kind.name }}
                        </div>
                      </v-scroll-y-transition>
                    </v-card>
                  </v-item>
                </v-col>
              </v-row>
            </v-container>
          </v-item-group>
        </v-container>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>

        <v-btn color="default" text @click="typeChooserDialog = false">
          Отмена
        </v-btn>

        <v-btn color="success" text @click="create()">
          Создать заявку на договор
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

</template>

<script>

export default {
  props: ['currentUser'],

  data() {

    return {
      typeChooserDialog: false,
      chosenType: null,
      isSelected: false,
      kinds: [
        { id: 'sub', name: 'Субподряд' },
        { id: 'service', name: 'Услуга' },
        { id: 'delivery', name: 'Поставка' },
        { id: 'rent', name: 'Аренда помещения' },
        { id: 'equipment', name: 'Аренда спец. техники' },
      ]
    };
  },

  mounted() {
    // console.log()
  },

  methods: {
    canCreateContract() {
      const allowedRoles = [
        'PTD Engineer', 'Supplier', 'Supervisor', 'Accountant', 'Vice President', 'CEO', 'Chief Financial Officer', 'Material Accountant',
        'PTD Manager', 'Section Manager', 'Chief Engineer', 'Economist', 'Lawyer'
      ];

      return this.currentUser != null && allowedRoles.includes(this.currentUser.roles[0].title);
    },

    openTypeChooser() {
      this.typeChooserDialog = true;
    },

    create() {
      let kind = this.kinds[this.chosenType];
      // this.$router.push();
      window.location = `/contracts/create?kind=${kind.id}`;
    }
  }
}
</script>


<style>
.oks-type-chooser {
  height: 4rem;
}

@media only screen and (min-width: 768px) {
  .oks-type-chooser {
    height: 10rem;
  }
}
</style>