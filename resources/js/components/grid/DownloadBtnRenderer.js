export default {
  template: `
  <v-btn @click="downloadAction" color="success" size="small"
    v-if="params.can">
    Скачать
  </v-btn>
  `,
  methods: {
    downloadAction(e) {
      e.stopPropagation();
      this.params.clicked(this.params.data.id);
    }
  },
};