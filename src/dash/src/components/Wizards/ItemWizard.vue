<!--
Dialog de base d'ajout d'un élément
-->
<template>
  <v-dialog v-model="showed">
    <component v-if="currentWizard !== null" v-bind:is="currentWizard" v-on:hide="showed = false" v-bind="editAttr" />
  </v-dialog>
</template>

<script>
import CameraWizard from "@/components/Wizards/Items/CameraWizard";
import InfoBinaryWizard from "@/components/Wizards/Items/InfoBinaryWizard";
import InfoBinaryImgWizard from "@/components/Wizards/Items/InfoBinaryImgWizard";
import InfoNumericWizard from "@/components/Wizards/Items/InfoNumericWizard";
import InfoNumericImgWizard from "@/components/Wizards/Items/InfoNumericImgWizard";
import CmdActionWizard from "@/components/Wizards/Items/CmdActionWizard";
import ScenarioActionWizard from "@/components/Wizards/Items/ScenarioActionWizard";
import ScenarioActionImgWizard from "@/components/Wizards/Items/ScenarioActionImgWizard";
import EqLogicActionWizard from "@/components/Wizards/Items/EqLogicActionWizard";
import LinkToDashWizard from "@/components/Wizards/Items/LinkToDashWizard";
import CircleMenuWizard from "@/components/Wizards/Items/CircleMenuWizard";

export default {
  name: "ItemWizard",
  components: {
    CameraWizard,
    CircleMenuWizard,
    InfoBinaryWizard,
    InfoNumericWizard,
    InfoNumericImgWizard,
    InfoBinaryImgWizard,
    CmdActionWizard,
    ScenarioActionWizard,
    ScenarioActionImgWizard,
    EqLogicActionWizard,
    LinkToDashWizard
  },
  data: () => ({
    showed: false,
    currentWizard: null,
    editAttr: {}
  }),
  mounted() {
    this.$eventBus.$on("WizardAddItem", component => {
      this.editAttr = {};
      this.currentWizard = component + "Wizard";
      this.showed = true;
    });
    this.$eventBus.$on("WizardEditItem", widgetId => {
      this.editAttr = {
        baseData: this.$store.getters.widgets[widgetId]
      };
      this.currentWizard =
        this.$store.getters.widgets[widgetId].type + "Wizard";
      this.showed = true;
    });
  }
};
</script>
