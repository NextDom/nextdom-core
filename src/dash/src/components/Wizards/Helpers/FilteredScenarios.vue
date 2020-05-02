<template>
  <v-card color="blue-grey lighten-5">
    <v-card-title>
      Scénarios
      <v-spacer />
      <v-text-field
        v-model="search"
        append-icon="mdi-magnify"
        label="Filtrer"
        single-line
        hide-details
      ></v-text-field>
    </v-card-title>
    <v-data-table
      v-bind:headers="headers"
      v-bind:items="scenariosList"
      single-select
      show-select
      item-key="id"
      v-bind:items-per-page="5"
      v-bind:loading="Object.keys(rawScenarios).length === 0"
      loading-text="Chargement..."
      class="elevation-10"
      v-model="scenario"
      v-bind:search="search"
    ></v-data-table>
    <v-checkbox
      label="Cacher les scénarios désactivés"
      v-model="hideInactives"
      v-on:change="updateScenariosList"
    />
  </v-card>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "FilteredScenarios",
  props: {
    value: {
      type: Array,
      default: () => []
    },
    type: {}
  },
  data: () => ({
    rawScenarios: {},
    scenariosList: [],
    search: "",
    hideInactives: true,
    headers: [
      { text: "Group", value: "group" },
      { text: "Scenario", value: "name" }
    ]
  }),
  created() {
    Communication.get("/api/scenario/all", result => {
      this.rawScenarios = result;
      this.updateScenariosList();
    });
  },
  computed: {
    dataLoaded() {
      return !(Object.keys(this.rawScenarios).length === 0);
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
    updateScenariosList() {
      this.scenariosList = [];
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
  }
};
</script>