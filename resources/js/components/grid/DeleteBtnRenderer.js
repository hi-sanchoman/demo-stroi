export default {
  template: `
  <v-btn @click="deleteAction" color="error" size="small"
    v-if="params.can">
    Удалить
  </v-btn>
  `,
  methods: {
    deleteAction(e) {
      e.stopPropagation();
      this.params.clicked(this.params.data.id);
    }
  },
};