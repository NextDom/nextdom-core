<template>
  <v-stepper v-model="step">
    <v-stepper-header>
      <v-stepper-step v-bind:complete="step > 1" step="1">Objet</v-stepper-step>
      <v-divider />
      <v-stepper-step step="2">Personnalisation</v-stepper-step>
    </v-stepper-header>
    <v-stepper-items>
      <v-stepper-content step="1">
        <FilteredEqLogics v-model="eqLogic" v-bind:default="previewData.eqLogicId" type="camera" />
        <StepperButtons v-model="step" v-on:previous="$emit('hide')" v-bind:nextDisabled="eqLogic.length === 0" />
      </v-stepper-content>
      <v-stepper-content step="2">
        <v-card color="blue-grey lighten-5">
          <v-card-title>Personnalisation</v-card-title>
          <v-content>
            <v-slider
              v-if="$store.getters.dashType === 'manual'"
              v-model="previewData.style.height"
              min="100"
              max="500"
              label="Hauteur"
              thumb-label
            />
            <v-checkbox v-model="previewData.quality" label="Qualité supérieure" />
            <WidgetPreview v-bind:previewData="previewData" />
          </v-content>
        </v-card>
        <StepperButtons v-model="step" last v-on:next="finish" />
      </v-stepper-content>
    </v-stepper-items>
  </v-stepper>
</template>

<script>
import StepperButtons from "@/components/Wizards/Helpers/StepperButtons";
import BaseWizard from "@/components/Wizards/BaseWizard";
import FilteredEqLogics from "@/components/Wizards/Helpers/FilteredEqLogics";
import WidgetPreview from "@/components/Wizards/WidgetPreview";

export default {
  extends: BaseWizard,
  name: "CameraWizard",
  components: {
    FilteredEqLogics,
    StepperButtons,
    WidgetPreview
  },
  props: {
    baseData: {
      type: Object,
      default: () => ({
        id: -1,
        cmdId: -1,
        type: "Camera",
        pos: { top: 0, left: 0 },
        eqLogicId: -1,
        localApiKey: "",
        refreshInterval: 0,
        title: "Camera",
        quality: true,
        style: {
          border: false,
          width: "auto",
          height: "auto",
          transparent: true,
          titleSize: 20
        }
      })
    }
  },
  data: () => ({
    eqLogic: [],
    previewData: {}
  }),
  watch: {
    step: function(newStep) {
      switch (newStep) {
        case 1:
          this.eqLogic = [];
          if (this.$store.getters.dashType === "manual") {
            this.previewData.height = 200;
          } else {
            this.previewData.height = "auto";
          }
          break;
        case 2:
          if (this.previewData.id === -1) {
            this.previewData.title = this.eqLogic[0].eqLogic;
          }
          this.previewData.eqLogicId = this.eqLogic[0].id;
          this.previewData.refreshInterval = this.eqLogic[0].eqLogicData.configuration[
            "thumbnail::refresh"
          ];
          this.previewData.localApiKey = this.eqLogic[0].eqLogicData.configuration[
            "localApiKey"
          ];
          break;
      }
    }
  },
  created() {
    this.resetData();
    if (this.previewData.id !== -1) {
      this.step = 2;
    }
  },
  methods: {
    resetData() {
      this.previewData = JSON.parse(JSON.stringify(this.baseData));
    },
    finish() {
      this.endOfWizard();
      this.resetData();
    }
  }
};
</script>

