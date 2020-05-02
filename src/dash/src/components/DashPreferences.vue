<!--
Fenêtre de configuration du dash
-->
<template>
  <v-dialog v-model="showed">
    <v-card>
      <v-card-title>Préférences</v-card-title>
      <v-card-text>
        <v-container>
          <v-radio-group v-model="formData.size" row>
            <v-radio label="Fixe" value="fix" />
            <v-radio label="Adaptée" value="responsive" />
          </v-radio-group>
          <v-row v-if="formData.size ==='fix'">
            <v-col cols="6">
              <v-text-field v-model="formData.width" label="Largeur" />
            </v-col>
            <v-col cols="6">
              <v-text-field v-model="formData.height" label="Hauteur" />
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: "DashPreferences",
  props: {
    value: {
      type: Object,
      default: () => {}
    }
  },
  data: () => ({
    showed: false
  }),
  mounted() {
    this.$eventBus.$on("showDashPreferences", () => {
      this.showed = true;
    });
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