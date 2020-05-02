<!--
Assistant de création d'un nouveau dash
-->
<template>
  <v-dialog v-model="showWizard" persistent>
    <v-stepper v-model="step">
      <v-stepper-header>
        <v-stepper-step v-bind:complete="step > 1" step="1">Positionnement</v-stepper-step>
        <v-divider />
        <v-stepper-step v-bind:complete="step > 2" step="2">Dimensions</v-stepper-step>
        <v-divider />
        <v-stepper-step step="3">Thème</v-stepper-step>
      </v-stepper-header>
      <v-stepper-items>
        <v-stepper-content step="1">
          <v-card color="blue-grey lighten-5">
            <v-card-title>Placement des éléments</v-card-title>
            <v-card-text>
              <v-radio-group v-model="positioning" row>
                <v-radio label="Manuel" value="manual" />
                <v-radio label="Grille" value="grid" />
              </v-radio-group>
              <v-alert type="info" v-if="positioning === 'manual'">Les éléments sont positionnés à un endroit précis de l'écran.</v-alert>
              <v-alert type="info" v-else>La position des éléments est définie par une grille.</v-alert>
            </v-card-text>
          </v-card>
          <v-alert type="warning">Ce paramètre ne pourra pas être changé par la suite.</v-alert>
          <StepperButtons v-model="step" v-bind:cancelable="false" />
        </v-stepper-content>
        <v-stepper-content step="2">
          <v-card color="blue-grey lighten-5">
            <v-card-title>Dimensions</v-card-title>
            <v-card-text>
              <v-radio-group v-model="size" row v-bind:disabled="positioning === 'manual'">
                <v-radio label="Fixe" value="fix" />
                <v-radio label="Adaptée" value="responsive" />
              </v-radio-group>
              <v-alert type="info" v-if="size === 'responsive'">Le contenu s'adaptera à la taille de l'écran.</v-alert>
              <v-alert type="info" v-else>Le contenu est de taille fixe et s'affichera tout le temps avec les mêmes dimensions.</v-alert>
              <v-content v-if="size === 'fix'">
                <v-row>
                  <v-col cols="6">
                    <v-text-field v-model="width" label="Largeur" />
                  </v-col>
                  <v-col cols="6">
                    <v-text-field v-model="height" label="Hauteur" />
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="2">
                    <v-btn v-on:click="width = 1920; height = 1080">1080p</v-btn>
                  </v-col>
                  <v-col cols="2">
                    <v-btn v-on:click="width = 1280; height = 720">720p</v-btn>
                  </v-col>
                  <v-col cols="2">
                    <v-btn v-on:click="width = 1440; height = 900">WXGA+</v-btn>
                  </v-col>
                  <v-col cols="2">
                    <v-btn v-on:click="width = 1280; height = 800">WXGA</v-btn>
                  </v-col>
                </v-row>
              </v-content>
            </v-card-text>
          </v-card>
          <StepperButtons v-model="step" />
        </v-stepper-content>
        <v-stepper-content step="3">
          <StepperButtons v-model="step" last v-on:next="endOfWizard" />
        </v-stepper-content>
      </v-stepper-items>
    </v-stepper>
  </v-dialog>
</template>

<script>
import StepperButtons from "@/components/Wizards/Helpers/StepperButtons";

export default {
  name: "AddDashWizard",
  components: {
    StepperButtons
  },
  props: {
    showWizard: null
  },
  data: () => ({
    step: 1,
    positioning: "manual",
    size: "fix",
    width: 1280,
    height: 720
  }),
  watch: {
    step: function(newStep) {
      if (newStep === 2) {
        if (this.positioning === "manual") {
          this.size = "fix";
        }
      }
    }
  },
  methods: {
    endOfWizard() {
      this.$emit("endOfWizard", {
        positioning: this.positioning,
        size: this.size,
        width: this.width,
        height: this.height
      });
    }
  }
};
</script>
