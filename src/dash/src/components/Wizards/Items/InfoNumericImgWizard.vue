<template>
  <v-stepper v-model="step">
    <v-stepper-header>
      <v-stepper-step v-bind:complete="step > 1" step="1">Commande</v-stepper-step>
      <v-divider />
      <v-stepper-step step="2">Personnalisation</v-stepper-step>
    </v-stepper-header>
    <v-stepper-items>
      <v-stepper-content step="1">
        <FilteredCommands v-model="command" type="info" subType="numeric" v-bind:custom="filterCmd" />
        <StepperButtons v-model="step" v-on:previous="$emit('hide')" v-bind:nextDisabled="command.length === 0" />
      </v-stepper-content>
      <v-stepper-content step="2">
        <v-card color="blue-grey lighten-5">
          <v-card-title>Personnalisation</v-card-title>
          <v-content>
            <WidgetStyle v-model="previewData" />
            <LevelImgStyle v-model="previewData.picture" />
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
import FilteredCommands from "@/components/Wizards/Helpers/FilteredCommands";
import WidgetStyle from "@/components/Wizards/Helpers/WidgetStyle";
import WidgetPreview from "@/components/Wizards/WidgetPreview";
import LevelImgStyle from "@/components/Wizards/Helpers/LevelImgStyle";

export default {
  extends: BaseWizard,
  name: "InfoNumericImgWizard",
  components: {
    WidgetStyle,
    FilteredCommands,
    LevelImgStyle,
    StepperButtons,
    WidgetPreview
  },
  data: () => ({
    command: [],
    previewData: {}
  }),
  watch: {
    previewData: function(newData) {
      console.log(newData);
    },
    step: function(newStep) {
      switch (newStep) {
        case 1:
          this.command = [];
          break;
        case 2:
          if (this.command.length > 0) {
            this.previewData.title = this.command[0]["eqLogic"];
            if (this.command[0]["data"]["unite"] === "%") {
              this.previewData.percent = true;
              this.previewData.unit = "%";
            } else {
              this.previewData.percent = false;
              this.previewData.unit = this.command[0].data.unite;
              this.previewData.min = this.command[0].data.configuration.minValue;
              this.previewData.max = this.command[0].data.configuration.maxValue;
            }
            this.previewData.cmdId = parseInt(this.command[0].id);
          }
          break;
      }
    }
  },
  created() {
    this.resetData();
  },
  methods: {
    filterCmd(cmd) {
      if (cmd.unite === "%") {
        return true;
      }
      if (
        cmd.configuration.maxValue !== undefined &&
        cmd.configuration.maxValue !== ""
      ) {
        return true;
      }
      return false;
    },
    resetData() {
      this.previewData = JSON.parse(
        JSON.stringify({
          id: -1,
          type: "InfoNumericImg",
          cmdId: -1,
          pos: { top: 0, left: 0 },
          picture: { name: "shutter", values: [0, 100] },
          title: "",
          unit: "%",
          percent: false,
          min: 0,
          max: 100,
          state: 100,
          style: {
            border: true,
            width: 280,
            height: 150,
            transparent: false,
            backgroundColor: "#FFFFFFFF",
            titleSize: 20,
            contentSize: 60
          }
        })
      );
    },
    finish() {
      this.endOfWizard();
      this.resetData();
    }
  }
};
</script>
