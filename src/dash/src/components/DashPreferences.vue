<!--
Fenêtre de configuration du dash
-->
<template>
  <v-dialog v-model="showed">
    <v-card>
      <v-card-title>Préférences</v-card-title>
      <v-card-text>
        <v-container>
          <v-text-field v-model="formData.name" label="Nom" />
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
          <v-row>
            <v-col cols="4" left>
              <v-btn href="/dash/index.html" color="success" dark>
                <v-icon left>mdi-plus</v-icon>Nouveau
              </v-btn>
            </v-col>
            <v-col cols="4">
              <v-btn v-bind:to="{name: 'dash-editor', params: {dashId: formData.id}}" color="success" dark>
                <v-icon left>mdi-plus</v-icon>Editeur avancé
              </v-btn>
            </v-col>
            <v-col cols="4" right>
              <v-btn v-on:click="deleteDash" color="red" dark>
                <v-icon left>mdi-delete</v-icon>Supprimer
              </v-btn>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import Communication from "@/libs/Communication";

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
  },
  methods: {
    deleteDash() {
      Communication.get("/api/dash/1/delete", () => {
        this.$store.commit("deleteDash");
        this.$emit("startWizard");
      });
    }
  }
};
</script>