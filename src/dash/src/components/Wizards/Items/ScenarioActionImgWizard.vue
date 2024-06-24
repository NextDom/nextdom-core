<template>
  <v-stepper v-model="step">
    <v-stepper-header>
      <v-stepper-step v-bind:complete="step > 1" step="1">Scénario</v-stepper-step>
      <v-divider />
      <v-stepper-step step="2">Personnalisation</v-stepper-step>
    </v-stepper-header>
    <v-stepper-items>
      <v-stepper-content step="1">
        <FilteredScenarios v-model="scenario" v-bind:default="previewData.scenarioId  " />
        <StepperButtons v-model="step" v-on:previous="$emit('hide')" v-bind:nextDisabled="scenario.length === 0" />
      </v-stepper-content>
      <v-stepper-content step="2">
        <v-card color="blue-grey lighten-5">
          <v-card-title>Personnalisation</v-card-title>
          <v-content>
            <WidgetStyle v-model="previewData" />
            <ImgStyle v-model="previewData.picture" />
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
import FilteredScenarios from "@/components/Wizards/Helpers/FilteredScenarios";
import ImgStyle from "@/components/Wizards/Helpers/ImgStyle";
import WidgetStyle from "@/components/Wizards/Helpers/WidgetStyle";
import WidgetPreview from "@/components/Wizards/WidgetPreview";

export default {
  extends: BaseWizard,
  name: "ScenarioActionImgWizard",
  components: {
    ImgStyle,
    WidgetStyle,
    FilteredScenarios,
    WidgetPreview,
    StepperButtons
  },
  props: {
    baseData: {
      type: Object,
      default: () => ({
        id: -1,
        type: "ScenarioActionImg",
        scenarioId: -1,
        pos: { top: 0, left: 0 },
        title: "",
        state: "stop",
        picture: "on-off/play-on.png",
        style: {
          border: true,
          width: 280,
          height: 150,
          transparent: false,
          backgroundColor: "#FFFFFFFF",
          titleSize: 20,
          contentSize: 40
        }
      })
    }
  },
  data: () => ({
    scenario: []
  }),
  watch: {
    step: function(newStep) {
      switch (newStep) {
        case 1:
          this.scenario = [];
          break;
        case 2:
          if (this.scenario.length > 0) {
            if (this.previewData.id === -1) {
              this.previewData.title = this.scenario[0].name;
            }
            this.previewData.scenarioId = parseInt(this.scenario[0].id);
          }
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
    finish() {
      this.endOfWizard();
      this.resetData();
    }
  }
};
</script>

