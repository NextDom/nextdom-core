<template>
  <div>
    <h1>Editeur avancé</h1>
    <ConnectDialog v-on:connected="start" />
    <vue-json-editor
      v-bind:lang="language"
      ref="dashEditor"
      v-model="dashData"
      v-bind:show-btns="true"
      v-bind:expandedOnStart="false"
      v-on:json-save="dashSave"
    ></vue-json-editor>
    <vue-json-editor
      v-bind:lang="language"
      ref="widgetsEditor"
      v-model="widgetsData"
      v-bind:show-btns="true"
      v-bind:expandedOnStart="false"
      v-on:json-save="widgetsSave"
    ></vue-json-editor>
  </div>
</template>

<script>
import ConnectDialog from "@/components/ConnectDialog";
import Communication from "@/libs/Communication";
import vueJsonEditor from "vue-json-editor";

export default {
  props: {
    dashId: undefined
  },
  data: () => ({
    language: "en",
    dashData: {},
    widgetsData: {},
    savedDashData: {},
    savedWidgetsData: {},
    dashError: false,
    widgetError: false
  }),
  mounted() {
    this.hackTranslation();
    if (Communication.isConnected()) {
      this.start();
    }
  },
  components: {
    vueJsonEditor,
    ConnectDialog
  },
  methods: {
    // Bouton en français
    hackTranslation() {
      this.$set(this.$refs.dashEditor.$data.locale, "fr", {});
      this.$set(this.$refs.dashEditor.$data.locale.fr, "save", "Sauvegarder");
      this.$set(this.$refs.widgetsEditor.$data.locale, "fr", {});
      this.$set(
        this.$refs.widgetsEditor.$data.locale.fr,
        "save",
        "Sauvegarder"
      );
      this.language = "fr";
    },
    start() {
      if (this.dashId !== undefined) {
        Communication.get(
          "/api/dash/" + this.dashId,
          result => {
            this.dashData = result.data.dashData;
            this.savedDashData = JSON.parse(JSON.stringify(this.dashData));
            this.widgetsData = result.data.widgetsData;
            this.savedWidgetsData = JSON.parse(
              JSON.stringify(this.widgetsData)
            );
          },
          error => {
            console.log(error);
          }
        );
      }
    },
    dashSave() {
      this.savedDashData = JSON.parse(JSON.stringify(this.dashData));
      this.save();
    },
    widgetsSave() {
      this.savedWidgetsData = JSON.parse(JSON.stringify(this.widgetsData));
    },
    save() {
      Communication.postWithOptions("/api/dash/save", {
        id: this.dashId,
        name: this.dashData.name,
        data: JSON.stringify({
          dashData: this.savedDashData,
          widgetsData: this.savedWidgetsData
        })
      });
    }
  }
};
</script>