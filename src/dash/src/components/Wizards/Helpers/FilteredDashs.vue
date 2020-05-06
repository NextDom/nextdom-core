<template>
  <v-card color="blue-grey lighten-5">
    <v-card-title>
      Dashs
      <v-spacer />
      <v-text-field v-model="search" append-icon="mdi-magnify" label="Filtrer" single-line hide-details></v-text-field>
    </v-card-title>
    <v-data-table
      v-bind:headers="headers"
      v-bind:items="dashsList"
      single-select
      show-select
      item-key="id"
      v-bind:items-per-page="5"
      v-bind:loading="Object.keys(rawDashs).length === 0"
      loading-text="Chargement..."
      class="elevation-10"
      v-model="scenario"
      v-bind:search="search"
    ></v-data-table>
  </v-card>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "FilteredDashs",
  props: {
    value: {
      type: Array,
      default: () => []
    },
    type: {}
  },
  data: () => ({
    rawDashs: {},
    dashsList: [],
    search: "",
    headers: [
      { text: "ID", value: "id" },
      { text: "Nom", value: "name" }
    ]
  }),
  created() {
    Communication.get("/api/dash/all", result => {
      this.rawDashs = result;
      this.dashsList = result;
      //      this.updateDashsList();
    });
  },
  computed: {
    dataLoaded() {
      return !(Object.keys(this.rawDashs).length === 0);
    },
    scenario: {
      get() {
        return this.value;
      },
      set(newValue) {
        this.$emit("input", newValue);
      }
    }
  },
  methods: {
    /*
    updateScenariosList() {
      this.dashsList = [];
      for (let scenarioIndex in this.rawScenarios) {
        const scenario = this.rawScenarios[scenarioIndex];
        if (!(!scenario.active && this.hideInactives)) {
          this.scenariosList.push({
            scenario: scenario,
            id: scenario.id,
            name: scenario.name,
            group: scenario.group
          });
        }
      }
    }
    */
  }
};
</script>