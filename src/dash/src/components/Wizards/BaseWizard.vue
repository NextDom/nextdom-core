<script>
export default {
  name: "BaseWizard",
  data: () => ({
    step: 1,
    previewData: null,
    search: "",
    stateUpdater: null
  }),
  methods: {
    resetData() {
      this.previewData = JSON.parse(JSON.stringify(this.baseData));
    },
    startStateUpdater() {
      this.stateUpdater = setInterval(() => {
        this.previewData.state = !this.previewData.state;
      }, 2000);
    },
    stopStateUpdater() {
      if (this.stateUpdater !== null) {
        clearInterval(this.stateUpdater);
      }
    },
    endOfWizard() {
      this.stopStateUpdater();
      this.$store.commit("setEditMode", true);
      if (this.previewData.id === -1) {
        this.previewData.id = this.genFakeGuid();
      }
      // Clone de l'objet
      this.$store.commit(
        "addWidget",
        JSON.parse(JSON.stringify(this.previewData))
      );
      if (this.$store.getters.dashType === "grid") {
        this.$eventBus.$emit("addedWidget", this.previewData.id);
      }
      this.$emit("hide");
      this.search = "";
      this.step = 1;
    },
    genFakeGuid() {
      return (
        Math.random()
          .toString(36)
          .substr(2) +
        Math.random()
          .toString(36)
          .substr(2)
      );
    }
  }
};
</script>

<style>
.v-stepper .v-card {
  margin-bottom: 1rem;
  padding: 0 1rem 1rem 1rem;
}
</style>