<template>
  <div>
    <v-list density="compact" transition="slide-x-transition">
      <v-list-subheader class="d-block">
        Договоры

        <div class="float-right" v-if="currentUser != null && currentUser.roles[0].title == 'PTD Engineer'">
          <v-btn color="primary" size="x-small" plain @click="create()">
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


      <template v-if="currentUser != null && currentUser.roles[0].title != 'Warehouse Manager'">
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
      </template>

    </v-list>
  </div>

</template>

<script>

export default {
  props: ['currentUser'],

  data() {
    return {
      // typeChooserDialog: false,
      // chosenType: null,
      // isSelected: false,
      // kinds: [
      //   { id: 'product', name: 'Заявка на товар' },
      //   { id: 'equipment', name: 'Заявка на спец. технику' },
      //   { id: 'service', name: 'Заявка на услугу' },
      // ]
    };
  },

  mounted() {
    // console.log()
  },

  methods: {
    // openTypeChooser() {
    //   console.log('show dialog');
    //   this.typeChooserDialog = true;
    // },

    create() {
      // let kind = this.kinds[this.chosenType];
      const kind = 'default';
      this.$router.push(`/contracts/create?kind=${kind.id}`);
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