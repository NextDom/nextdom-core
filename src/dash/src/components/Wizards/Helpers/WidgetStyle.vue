<!--
Réglages communs aux widgets
-->
<template>
  <div>
    <v-text-field v-model="formData.title" label="Titre" />
    <template v-if="$store.getters.dashType === 'manual'">
      <v-checkbox v-model="autoSizing" label="Dimensions automatiques" />
      <v-slider v-if="!autoSizing" v-model="formData.style.width" min="50" max="600" label="Largeur" thumb-label>
        <template v-slot:append>
          <v-text-field v-model="formData.style.width" class="mt-0 pt-0" hide-details single-line type="number" />
        </template>
      </v-slider>
      <v-slider v-if="!autoSizing" v-model="formData.style.height" min="50" max="600" label="Hauteur" thumb-label>
        <template v-slot:append>
          <v-text-field v-model="formData.style.height" class="mt-0 pt-0" hide-details single-line type="number" />
        </template>
      </v-slider>
    </template>
    <v-row>
      <v-col cols="6">
        <v-checkbox v-model="formData.style.transparent" label="Transparent" />
      </v-col>
      <v-col cols="6">
        <v-checkbox v-model="formData.style.border" v-if="!formData.style.transparent" label="Afficher les bordures" />
      </v-col>
    </v-row>
    <v-row v-if="!formData.style.transparent">
      <v-col cols="3">
        <v-label>Couleur de fond</v-label>
      </v-col>
      <v-col cols="9">
        <v-hover v-slot:default="{ hover }">
          <v-color-picker v-bind:hide-canvas="!hover" v-model="backgroundColor" hide-inputs></v-color-picker>
        </v-hover>
      </v-col>
    </v-row>
    <v-slider v-model="formData.style.titleSize" min="5" max="60" label="Police du titre" step="1" thumb-label>
      <template v-slot:append>
        <v-text-field v-model="formData.style.titleSize" class="mt-0 pt-0" hide-details single-line type="number" />
      </template>
    </v-slider>
    <v-slider v-model="formData.style.contentSize" min="10" max="120" label="Taille du contenu" step="1" thumb-label>
      <template v-slot:append>
        <v-text-field v-model="formData.style.contentSize" class="mt-0 pt-0" hide-details single-line type="number" />
      </template>
    </v-slider>
  </div>
</template>

<script>
import Data from "@/libs/Data";

export default {
  name: "WidgetStyle",
  props: {
    value: {}
  },
  data: () => ({
    iconGroups: Data.iconGroups,
    backgroundColor: "#FFFFFFFF",
    autoSizing: false
  }),
  created() {
    this.$eventBus.$on("previewWidthChange", previewSize => {
      if (this.autoSizing) {
        this.formData.style.width = previewSize.width;
        this.formData.style.height = previewSize.height;
      }
    });
  },
  watch: {
    backgroundColor: function(newBackroundColor) {
      this.formData.style.backgroundColor = newBackroundColor;
    }
  },
  computed: {
    formData: {
      get() {
        return this.value;
      },
      set(newValue) {
        this.$emit("input", newValue);
      }
    }
  }
};
</script>
