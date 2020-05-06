<!--
Composant global du Dash
-->
<template>
  <div id="global-container" v-bind:style="dashSize">
    <ConnectDialog v-on:connected="start" />
    <DashPreferences v-model="dashData" v-on:startWizard="startWizard" />
    <ManualDash v-if="dashData !== undefined && dashData.positioning === 'manual'" />
    <GridDash v-else-if="dashData !== undefined && dashData.positioning === 'grid'" v-model="dashData.grid" />
    <Tools v-if="initialized" />
    <AddDashWizard v-on:endOfWizard="endOfWizard" v-bind:showWizard="showWizard" />
    <SelectItemToAddWizard />
    <AddItemWizard />
  </div>
</template>

<script>
import ManualDash from "@/components/ManualDash";
import GridDash from "@/components/GridDash";
import ConnectDialog from "@/components/ConnectDialog";
import Communication from "@/libs/Communication";
import DashPreferences from "@/components/DashPreferences";
import Tools from "@/components/Tools";
import SelectItemToAddWizard from "@/components/Wizards/SelectItemToAddWizard";
import AddItemWizard from "@/components/Wizards/AddItemWizard";
import AddDashWizard from "@/components/Wizards/AddDashWizard";
import EventsManager from "@/libs/EventsManager.js";

export default {
  name: "Dash",
  components: {
    ManualDash,
    ConnectDialog,
    GridDash,
    DashPreferences,
    Tools,
    SelectItemToAddWizard,
    AddItemWizard,
    AddDashWizard
  },
  props: {
    dashId: undefined
  },
  data: () => ({
    initialized: false,
    showWizard: false,
    responsive: false,
    dashData: {
      id: -1,
      title: "Dash",
      width: 640,
      height: 480,
      grid: {
        id: "0",
        children: [],
        orientation: "horizontal",
        type: "grid"
      },
      positioning: "manual",
      size: "fix"
    }
  }),
  mounted() {
    if (Communication.isConnected()) {
      this.start();
    }
  },
  watch: {
    /**
     * Détection changement de dash
     */
    dashId: function() {
      this.start();
    }
  },
  computed: {
    /**
     * Taille de l'écran
     */
    dashSize() {
      if (!this.initialized) {
        return {
          width: 0,
          height: 0
        };
      }
      if (this.dashData.size === "fix") {
        return {
          width: this.dashData.width + "px",
          height: this.dashData.height + "px"
        };
      } else {
        return {
          width: "100%",
          height: "100%"
        };
      }
    }
  },
  methods: {
    /**
     * Lance le wizard si aucun dash n'est configuré
     */
    start() {
      if (this.dashId !== undefined) {
        if (localStorage["dashData" + this.dashId] !== undefined) {
          this.$store.commit("loadFromLocalStorage", this.dashId);
          this.dashData = this.$store.getters.dashData;
          this.initialized = true;
        }
        // Lecture des données depuis le serveur
        Communication.get(
          "/api/dash/" + this.dashId,
          result => {
            if (result.id == this.dashId) {
              // La première fois, l'objet n'a pas d'identifiant au moment de l'enregistrement
              result.data.dashData.id = this.dashId;
              if (Array.isArray(result.data.widgetsData)) {
                result.data.widgetsData = {};
              }
              this.dashData = result.data.dashData;
              this.$store.commit("initDash", result.data.dashData);
              this.$store.commit("saveToLocalStorage", this.dashId);
              this.$store.commit("initWidgets", result.data.widgetsData);
              this.initialized = true;
            } else {
              this.startWizard();
            }
          },
          () => {
            this.startWizard();
          }
        );
      } else {
        this.startWizard();
      }
      EventsManager.loop();
    },
    /**
     * Affiche la fenêtre de l'assistant
     */
    startWizard() {
      if (!this.initialized) {
        this.dashData = {
          id: -1,
          name: "Dash",
          width: 640,
          height: 480,
          grid: {
            id: "0",
            children: [],
            orientation: "horizontal",
            type: "grid"
          },
          positioning: "manual",
          size: "fix"
        };
        this.showWizard = true;
      }
    },
    /**
     * A la fin de l'assistant, récupère les valeurs pui sle ferme
     */
    endOfWizard(wizardData) {
      this.dashData = JSON.parse(JSON.stringify(wizardData));
      this.$store.commit("initDash", this.dashData);
      this.$store.commit("setEditMode", true);
      this.initialized = true;
      this.showWizard = false;
    }
  }
};
</script>

<style scoped>
#global-container {
  padding: 0 !important;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  max-width: 100% !important;
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
</style>
