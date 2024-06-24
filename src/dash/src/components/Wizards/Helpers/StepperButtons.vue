<!--
Bouton précédent/suivant des assistants
-->
<template>
  <v-row>
    <v-col cols="6">
      <v-btn
        color="secondary"
        v-if="step > 1 || (step === 1 && cancelable)"
        v-on:click="previousEvent"
      >{{ step > 1 ? previousLabel : cancelLabel }}</v-btn>
    </v-col>
    <v-col cols="6" class="text-right">
      <v-btn color="primary" v-bind:disabled="nextDisabled" v-on:click="nextEvent">{{ last ? endLabel : nextLabel }}</v-btn>
    </v-col>
  </v-row>
</template>

<script>
export default {
  name: "StepperButtons",
  props: {
    previousLabel: {
      type: String,
      default: "Précédent"
    },
    nextLabel: {
      type: String,
      default: "Suivant"
    },
    cancelLabel: {
      type: String,
      default: "Annuler"
    },
    endLabel: {
      type: String,
      default: "Fin"
    },
    last: {
      type: Boolean,
      default: false
    },
    cancelable: {
      type: Boolean,
      default: true
    },
    nextDisabled: {
      type: Boolean,
      default: false
    },
    value: Number
  },
  computed: {
    step: {
      get() {
        return this.value;
      },
      set(newStep) {
        this.$emit("input", newStep);
      }
    }
  },
  methods: {
    previousEvent() {
      this.step--;
      this.$emit("previous");
    },
    nextEvent() {
      this.step++;
      this.$emit("next");
    }
  }
};
</script>