<!--
Dialog de base d'ajout d'un élément
-->
<template>
  <v-dialog v-model="showed">
    <component v-if="currentWizard !== null" v-bind:is="currentWizard" v-on:hide="showed = false" />
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
import EqLogicActionWizard from "@/components/Wizards/Items/EqLogicActionWizard";

export default {
  name: "AddItemWizard",
  components: {
    CameraWizard,
    InfoBinaryWizard,
    InfoNumericWizard,
    InfoNumericImgWizard,
    InfoBinaryImgWizard,
    CmdActionWizard,
    ScenarioActionWizard,
    EqLogicActionWizard
  },
  data: () => ({
    showed: false,
    currentWizard: null
  }),
  mounted() {
    this.$eventBus.$on("WizardAddItem", component => {
      this.showed = true;
      this.currentWizard = component + "Wizard";
    });
  }
};
</script>
