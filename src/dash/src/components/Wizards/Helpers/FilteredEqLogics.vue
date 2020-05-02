<template>
  <v-card color="blue-grey lighten-5">
    <v-card-title>
      EqLogic
      <v-spacer />
      <v-text-field v-model="search" append-icon="mdi-magnify" label="Filtrer" single-line hide-details></v-text-field>
    </v-card-title>
    <v-data-table
      v-bind:headers="headers"
      v-bind:items="eqLogicsList"
      single-select
      show-select
      item-key="id"
      v-bind:items-per-page="5"
      v-bind:loading="Object.keys(eqLogicsTree).length === 0"
      loading-text="Chargement..."
      class="elevation-10"
      v-model="eqLogic"
      v-bind:search="search"
    ></v-data-table>
  </v-card>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "FilteredEqLogics",
  props: {
    value: {
      type: Array,
      default: () => []
    },
    type: String
  },
  data: () => ({
    eqLogicsTree: {},
    eqLogicsList: [],
    search: "",
    headers: [
      { text: "Pièce", value: "room" },
      { text: "Objet", value: "eqLogic" }
    ]
  }),
  created() {
    Communication.get("/api/summary/all", result => {
      this.eqLogicsTree = result;
      this.updateEqLogicssList();
    });
  },
  watch: {
    type: function() {
      if (this.dataLoaded) {
        this.updateEqLogicssList();
      }
    }
  },
  computed: {
    dataLoaded() {
      return !(Object.keys(this.eqLogicsTree).length === 0);
    },
    eqLogic: {
      get() {
        return this.value;
      },
      set(newValue) {
        this.$emit("input", newValue);
      }
    }
  },
  methods: {
    updateEqLogicssList() {
      this.eqLogicsList = [];
      for (let roomId in this.eqLogicsTree) {
        for (let eqLogicId in this.eqLogicsTree[roomId]["eqLogics"]) {
          if (
            !(
              this.type !== undefined &&
              this.eqLogicsTree[roomId]["eqLogics"][eqLogicId]["type"] !==
                this.type
            )
          ) {
            this.eqLogicsList.push({
              room: this.eqLogicsTree[roomId]["name"],
              eqLogic: this.eqLogicsTree[roomId]["eqLogics"][eqLogicId]["name"],
              id: parseInt(
                this.eqLogicsTree[roomId]["eqLogics"][eqLogicId]["id"]
              ),
              eqLogicData: this.eqLogicsTree[roomId]["eqLogics"][eqLogicId]
            });
          }
        }
      }
    }
  }
};
</script>