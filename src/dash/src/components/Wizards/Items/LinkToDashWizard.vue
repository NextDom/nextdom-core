<template>
  <v-stepper v-model="step">
    <v-stepper-header>
      <v-stepper-step v-bind:complete="step > 1" step="1">Dash</v-stepper-step>
      <v-divider />
      <v-stepper-step step="2">Personnalisation</v-stepper-step>
    </v-stepper-header>
    <v-stepper-items>
      <v-stepper-content step="1">
        <FilteredDashs v-model="dash" v-bind:default="previewData.target" />
        <StepperButtons v-model="step" v-on:previous="$emit('hide')" v-bind:nextDisabled="dash.length === 0" />
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
import FilteredDashs from "@/components/Wizards/Helpers/FilteredDashs";
import ImgStyle from "@/components/Wizards/Helpers/ImgStyle";
import WidgetStyle from "@/components/Wizards/Helpers/WidgetStyle";
import WidgetPreview from "@/components/Wizards/WidgetPreview";

export default {
  extends: BaseWizard,
  name: "LinkToDashWizard",
  components: {
    ImgStyle,
    WidgetStyle,
    FilteredDashs,
    WidgetPreview,
    StepperButtons
  },
  props: {
    baseData: {
      type: Object,
      default: () => ({
        id: 0,
        type: "LinkToDash",
        target: 0,
        pos: { top: 0, left: 0 },
        title: "",
        hideBorder: true,
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
    dash: []
  }),
  watch: {
    step: function(newStep) {
      switch (newStep) {
        case 1:
          this.dash = [];
          break;
        case 2:
          if (this.dash.length > 0) {
            if (this.previewData.id === -1) {
              this.previewData.title = this.dash[0].name;
            }
            this.previewData.target = parseInt(this.dash[0].id);
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

