<template>
  <v-stepper v-model="step">
    <v-stepper-header>
      <v-stepper-step v-bind:complete="step > 1" step="1">Objet</v-stepper-step>
      <v-divider />
      <v-stepper-step v-bind:complete="step > 2" step="2">Commande d'état</v-stepper-step>
      <v-divider />
      <v-stepper-step v-bind:complete="step > 3" step="3">Commande On</v-stepper-step>
      <v-divider />
      <v-stepper-step v-bind:complete="step > 4" step="4">Commande Off</v-stepper-step>
      <v-divider />
      <v-stepper-step step="5">Personnalisation</v-stepper-step>
    </v-stepper-header>
    <v-stepper-items>
      <v-stepper-content step="1">
        <FilteredEqLogics v-model="eqLogic" />
        <StepperButtons v-model="step" v-on:previous="$emit('hide')" v-bind:nextDisabled="eqLogic.length === 0" />
      </v-stepper-content>
      <v-stepper-content step="2">
        <FilteredCommands v-model="stateCommand" v-bind:eqLogicId="eqLogicId" title="Commande d'état" type="info" />
        <StepperButtons v-model="step" v-bind:nextDisabled="stateCommand.length === 0" />
      </v-stepper-content>
      <v-stepper-content step="3">
        <FilteredCommands v-model="onCommand" v-bind:eqLogicId="eqLogicId" title="Commande pour allumer" type="action" />
        <StepperButtons v-model="step" v-bind:nextDisabled="onCommand.length === 0" />
      </v-stepper-content>
      <v-stepper-content step="4">
        <FilteredCommands v-model="offCommand" v-bind:eqLogicId="eqLogicId" title="Commande pour éteindre" type="action" />
        <StepperButtons v-model="step" v-bind:nextDisabled="offCommand.length === 0" />
      </v-stepper-content>
      <v-stepper-content step="5">
        <v-card color="blue-grey lighten-5">
          <v-card-title>Personnalisation</v-card-title>
          <v-content>
            <WidgetStyle v-model="previewData" />
            <OnOffStyle v-model="previewData.icon" />
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
import FilteredEqLogics from "@/components/Wizards/Helpers/FilteredEqLogics";
import WidgetStyle from "@/components/Wizards/Helpers/WidgetStyle";
import WidgetPreview from "@/components/Wizards/WidgetPreview";
import OnOffStyle from "@/components/Wizards/Helpers/OnOffStyle";

export default {
  extends: BaseWizard,
  name: "EqLogicActionWizard",
  components: {
    WidgetStyle,
    FilteredCommands,
    FilteredEqLogics,
    OnOffStyle,
    StepperButtons,
    WidgetPreview
  },
  data: () => ({
    eqLogic: [],
    eqLogicId: undefined,
    stateCommand: [],
    onCommand: [],
    offCommand: []
  }),
  watch: {
    step: function(newStep) {
      switch (newStep) {
        case 1:
          this.stopStateUpdater();
          this.eqLogic = [];
          break;
        case 2:
          this.eqLogicId = this.eqLogic[0].id;
          this.stateCommand = [];
          break;
        case 3:
          this.onCommand = [];
          break;
        case 4:
          this.offCommand = [];
          break;
        case 5:
          this.startStateUpdater();
          this.previewData.title = this.eqLogic[0].eqLogic;
          this.previewData.state = this.stateCommand[0].data.state;
          this.previewData.cmdId = parseInt(this.stateCommand[0].id);
          this.previewData.onCommandId = parseInt(this.onCommand[0].id);
          this.previewData.offCommandId = parseInt(this.offCommand[0].id);
          break;
      }
    }
  },
  created() {
    this.resetData();
  },
  methods: {
    resetData() {
      this.previewData = JSON.parse(
        JSON.stringify({
          id: -1,
          type: "EqLogicAction",
          cmdId: -1,
          pos: { top: 0, left: 0 },
          icon: "lamp",
          title: "",
          state: true,
          onCommandId: -1,
          offCommandId: -1,
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
      );
    },
    finish() {
      this.endOfWizard();
      this.resetData();
    }
  }
};
</script>

