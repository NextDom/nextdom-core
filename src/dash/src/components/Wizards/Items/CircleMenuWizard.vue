<template>
  <v-stepper v-model="step">
    <v-stepper-header>
      <v-stepper-step v-bind:complete="step > 1" step="1">Menus</v-stepper-step>
      <v-divider />
      <v-stepper-step step="2">Personnalisation</v-stepper-step>
    </v-stepper-header>
    <v-stepper-items>
      <v-stepper-content step="1">
        <MenusList v-model="previewData.menus" withoutTitle />
        <StepperButtons v-model="step" v-on:previous="$emit('hide')" v-bind:nextDisabled="previewData.menus.length === 0" />
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
import WidgetStyle from "@/components/Wizards/Helpers/WidgetStyle";
import WidgetPreview from "@/components/Wizards/WidgetPreview";
import ImgStyle from "@/components/Wizards/Helpers/ImgStyle";
import MenusList from "@/components/Wizards/Helpers/MenusList";

export default {
  extends: BaseWizard,
  name: "CircleMenuWizard",
  components: {
    WidgetStyle,
    ImgStyle,
    StepperButtons,
    WidgetPreview,
    MenusList
  },
  props: {
    baseData: {
      type: Object,
      default: () => ({
        id: -1,
        type: "CircleMenu",
        cmdId: -1,
        pos: { top: 0, left: 0 },
        picture: "on-off/v1-on.png",
        title: "",
        menus: [],
        style: {
          border: false,
          width: 280,
          height: 150,
          transparent: true,
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
    "previewData.menus": function(newValue) {
      console.log(newValue);
    },
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
