<!--
Choix d'une commande
-->
<template>
  <v-card color="blue-grey lighten-5">
    <v-card-title>
      {{ title }}
      <v-spacer />
      <v-text-field v-model="search" append-icon="mdi-magnify" label="Filtrer" single-line hide-details></v-text-field>
    </v-card-title>
    <v-data-table
      v-bind:headers="headers"
      v-bind:items="commandsList"
      single-select
      show-select
      item-key="id"
      v-bind:items-per-page="5"
      v-bind:loading="Object.keys(commandsTree).length === 0"
      loading-text="Chargement..."
      class="elevation-10"
      v-model="command"
      v-bind:search="search"
    ></v-data-table>
    <v-checkbox label="Cacher les commandes non-essentielles" v-model="hideUseless" v-on:change="updateCommandsList" />
  </v-card>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "FilteredCommands",
  props: {
    value: {
      type: Array,
      default: () => []
    },
    title: {
      type: String,
      default: "Commande"
    },
    default: {
      type: Number,
      default: -1
    },
    type: String,
    subType: String,
    eqLogicId: Number,
    custom: Function
  },
  data: () => ({
    commandsTree: {},
    commandsList: [],
    search: "",
    hideUseless: true,
    headers: [
      { text: "Pièce", value: "room" },
      { text: "Objet", value: "eqLogic" },
      { text: "Commande", value: "cmd" }
    ]
  }),
  mounted() {
    Communication.get("/api/summary/all", result => {
      this.commandsTree = result;
      this.updateCommandsList();
    });
  },
  watch: {
    type: function() {
      if (this.dataLoaded) {
        this.updateCommandsList();
      }
    },
    subType: function() {
      if (this.dataLoaded) {
        this.updateCommandsList();
      }
    },
    eqLogicId: function() {
      if (this.dataLoaded) {
        this.updateCommandsList();
      }
    }
  },
  computed: {
    dataLoaded() {
      return !(Object.keys(this.commandsTree).length === 0);
    },
    command: {
      get() {
        return this.value;
      },
      set(newValue) {
        this.$emit("input", newValue);
      }
    }
  },
  methods: {
    updateCommandsList() {
      this.commandsList = [];
      for (let roomId in this.commandsTree) {
        for (let eqLogicIndex in this.commandsTree[roomId]["eqLogics"]) {
          for (let cmdId in this.commandsTree[roomId]["eqLogics"][eqLogicIndex][
            "cmds"
          ]) {
            // Filtre des commandes
            const cmdData = this.commandsTree[roomId]["eqLogics"][eqLogicIndex][
              "cmds"
            ][cmdId];
            if (
              this.mustBeShowed(cmdData) &&
              !(this.hideUseless && this.isUselessCommand(cmdData["name"]))
            ) {
              const commandData = {
                room: this.commandsTree[roomId]["name"],
                eqLogic: this.commandsTree[roomId]["eqLogics"][eqLogicIndex][
                  "name"
                ],
                cmd: cmdData["name"],
                id: cmdData["id"],
                data: cmdData
              };
              if (this.default == commandData.id) {
                this.command = [commandData];
              }
              this.commandsList.push(commandData);
            }
          }
        }
      }
    },
    mustBeShowed(cmdData) {
      if (this.type !== undefined && cmdData.type !== this.type) {
        return false;
      }
      if (
        this.eqLogicId !== undefined &&
        parseInt(cmdData.eqLogicId) !== this.eqLogicId
      ) {
        return false;
      }
      if (this.subType !== undefined && cmdData.subType !== this.subType) {
        return false;
      }
      if (this.custom !== undefined && !this.custom(cmdData)) {
        return false;
      }
      return !(this.hideUseless && this.isUselessCommand(cmdData["name"]));
    },
    isUselessCommand(name) {
      const keywords = [
        "sabotage",
        "batterie",
        "battery",
        "rafraich",
        "refresh"
      ];
      const lowerName = name.toLowerCase();
      for (let keywordIndex in keywords) {
        if (lowerName.indexOf(keywords[keywordIndex]) !== -1) {
          return true;
        }
      }
      return false;
    }
  }
};
</script>