<template>
  <v-stepper v-model="step">
    <v-stepper-header>
      <v-stepper-step v-bind:complete="step > 1" step="1">Commande</v-stepper-step>
      <v-divider />
      <v-stepper-step step="2">Personnalisation</v-stepper-step>
    </v-stepper-header>
    <v-stepper-items>
      <v-stepper-content step="1">
        <FilteredCommands v-model="command" v-bind:default="previewData.cmdId" type="action" subType="other" />
        <StepperButtons v-model="step" v-on:previous="$emit('hide')" v-bind:nextDisabled="command.length === 0" />
      </v-stepper-content>
      <v-stepper-content step="2">
        <v-card color="blue-grey lighten-5">
          <v-card-title>Personnalisation</v-card-title>
          <v-content>
            <WidgetStyle v-model="previewData" />
            <IconStyle v-model="previewData.icon" />
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
import IconStyle from "@/components/Wizards/Helpers/IconStyle";

export default {
  extends: BaseWizard,
  name: "CmdActionWizard",
  components: {
    WidgetStyle,
    FilteredCommands,
    IconStyle,
    StepperButtons,
    WidgetPreview
  },
  props: {
    baseData: {
      type: Object,
      default: () => ({
        id: -1,
        type: "CmdAction",
        cmdId: -1,
        pos: { top: 0, left: 0 },
        icon: "play-circle",
        title: "",
        state: true,
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
    command: [],
    stateUpdater: null
  }),
  watch: {
    step: function(newStep) {
      switch (newStep) {
        case 1:
          this.command = [];
          break;
        case 2:
          this.stateUpdater = setInterval(() => {
            this.previewData.state = !this.previewData.state;
          }, 2000);
          if (this.command.length > 0) {
            if (this.previewData.id === -1) {
              this.previewData.title = this.command[0]["eqLogic"];
            }
            this.previewData.cmdId = parseInt(this.command[0]["id"]);
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
