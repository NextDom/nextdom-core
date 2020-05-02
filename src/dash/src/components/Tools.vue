<!--
Menu fab de gestion
-->
<template>
  <div>
    <v-speed-dial fixed bottom right direction="top" open-on-hover transition="scale-transition">
      <template v-slot:activator>
        <v-btn color="blue darken-2" dark fab>
          <v-icon>mdi-account-circle</v-icon>
        </v-btn>
      </template>
      <v-tooltip left>
        <template v-slot:activator="{ on }">
          <v-btn fab dark small color="green" v-on="on" v-on:click="toggleEditMode">
            <v-icon>mdi-pencil</v-icon>
          </v-btn>
        </template>
        <span>Modifier les éléments</span>
      </v-tooltip>
      <template v-if="$store.getters.dashType === 'manual'">
        <v-tooltip left>
          <template v-slot:activator="{ on }">
            <v-btn fab dark small color="indigo" v-on="on" v-on:click="addItem">
              <v-icon>fa-plus</v-icon>
            </v-btn>
          </template>
          <span>Ajouter un élément</span>
        </v-tooltip>
      </template>
      <v-tooltip left>
        <template v-slot:activator="{ on }">
          <v-btn fab dark small color="pink" v-on="on" v-on:click="save">
            <v-icon>mdi-content-save</v-icon>
          </v-btn>
        </template>
        <span>Sauvegarder</span>
      </v-tooltip>
      <v-tooltip left>
        <template v-slot:activator="{ on }">
          <v-btn fab dark small color="grey" v-on="on" v-on:click="showPreferences">
            <v-icon>mdi-cogs</v-icon>
          </v-btn>
        </template>
        <span>Préférences</span>
      </v-tooltip>
    </v-speed-dial>
    <v-snackbar v-model="snackbar" bottom v-bind:color="snackbarColor" v-bind:timeout="timeout">{{ message }}</v-snackbar>
  </div>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "Tools",
  data: () => ({
    snackbar: false,
    snackbarColor: "primary",
    message: "",
    timeout: 3000
  }),
  methods: {
    addItem() {
      this.$eventBus.$emit("showAddItemWizard");
    },
    toggleEditMode() {
      this.$store.commit("setEditMode", !this.$store.getters.editMode);
    },
    showPreferences() {
      this.$eventBus.$emit("showDashPreferences");
    },
    save() {
      console.log(JSON.stringify(this.$store.getters.dashData));
      this.$store.commit("saveToLocalStorage");
      Communication.postWithOptions(
        "/api/dash/save",
        {
          id: 1,
          name: "Dash",
          data: JSON.stringify({
            dashData: this.$store.getters.dashData,
            widgetsData: this.$store.getters.widgets
          })
        },
        () => {
          this.message = "Sauvegarde réussie";
          this.snackbarColor = "success";
          this.snackbar = true;
        },
        () => {
          this.message = "Problème de sauvegarde";
          this.snackbarColor = "error";
          this.snackbar = true;
        }
      );
    }
  }
};
</script>
