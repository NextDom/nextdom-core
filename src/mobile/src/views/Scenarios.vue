<template>
  <mu-container class="global scenarios">
    <h1>Scénarios</h1>
    <mu-list toggle-nested v-if="scenarios !== null">
      <mu-list-item
        button
        nested
        v-for="groupName in sortedGroupsList"
        v-bind:key="`groupName-${groupName}`"
        v-bind:open="groupsListState[groupName]"
        v-on:click="toggleItem(groupName)"
      >
        <mu-list-item-title>
          <template v-if="groupName === 'no-group'">Aucun</template>
          <template v-else>{{ groupName }}</template>
        </mu-list-item-title>
        <mu-list-item-action>
          <mu-icon class="toggle-icon" size="24" value="keyboard_arrow_down"></mu-icon>
        </mu-list-item-action>
        <mu-list-item
          slot="nested"
          v-for="scenario in scenarios[groupName]"
          v-bind:key="scenario.id"
        >
          <mu-list-item-action>
            <i v-bind:class="scenarioIcon(scenario)"></i>
          </mu-list-item-action>
          <mu-list-item-title>{{ scenario.name }}</mu-list-item-title>
          <mu-list-item-action>
            <mu-icon size="24" value="play_circle_filled" v-on:click="launch(scenario.id)"></mu-icon>
          </mu-list-item-action>
        </mu-list-item>
      </mu-list-item>
    </mu-list>
  </mu-container>
</template>

<script>
import communication from "../libs/communication.js";
import utils from "../libs/utils.js";

export default {
  name: "scenarios",
  data: function() {
    return {
      scenarios: null,
      groupsListState: []
    };
  },
  computed: {
    /**
     * Get groups list with no-group first
     */
    sortedGroupsList: function() {
      if (this.scenarios !== null) {
        let groupsList = Object.keys(this.scenarios).sort((a, b) => {
          if (a === "no-group") {
            return -1;
          } else if (b === "no-group") {
            return 1;
          } else if (a < b) {
            return -1;
          } else if (a > b) {
            return 1;
          }
          return 0;
        });
        return groupsList;
      }
    }
  },
  mounted() {
    this.$emit("setCurrentView", "/scenarios");
    // Get dashboard data
    communication.get("/api/scenario/all/by_group", result => {
      // Restore last list state
      for (let groupName in result) {
        const showGroup = localStorage.getItem(
          "scenario-group-show-" + groupName
        );
        if (showGroup !== null) {
          this.groupsListState[groupName] = showGroup === "true" ? true : false;
        } else {
          this.groupsListState[groupName] = true;
        }
      }
      this.scenarios = result;
    });
  },
  methods: {
    /**
     * Called when group visibility changes
     */
    toggleItem: function(groupName) {
      this.groupsListState[groupName] = !this.groupsListState[groupName];
      localStorage.setItem(
        "scenario-group-show-" + groupName,
        this.groupsListState[groupName]
      );
    },
    /**
     * Launch scenario
     * @param {scenarioId} Id of the scenario to launch
     */
    launch: function(scenarioId) {
      communication.post("/api/scenario/launch/" + scenarioId);
    },
    /**
     * Get scenario icon
     * @param {scenario} Scenario object
     */
    scenarioIcon: function(scenario) {
      return utils.extractIcon(scenario.displayIcon, "fas fa-film");
    }
  }
};
</script>

<style scoped lang="scss">
.mu-item-action i {
  font-size: 1.4rem;
}
</style>

