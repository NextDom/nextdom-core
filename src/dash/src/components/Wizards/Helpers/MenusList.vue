<template>
  <v-card color="blue-grey lighten-5">
    <v-card-title>
      Menus
      <v-spacer />
    </v-card-title>
    <v-card-text>
      <v-row v-for="(menu, index) in menus" v-bind:key="`menu-${index}`">
        <v-col cols="3">
          <v-text-field v-model="menu.icon" label="Icône" v-bind:append-outer-icon="menu.icon" />
        </v-col>
        <v-col v-if="!withoutTitle" cols="5">
          <v-text-field v-model="menu.title" label="Titre" />
        </v-col>
        <v-col cols="4">
          <v-select v-bind:items="dashsList" label="Dash" item-text="name" item-value="id" v-model="menu.target" />
        </v-col>
      </v-row>
      <v-row>
        <v-btn v-on:click="addMenu">Add</v-btn>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script>
import Communication from "@/libs/Communication";

export default {
  name: "MenusList",
  props: {
    baseData: {
      type: Object,
      default: () => ({
        id: -1,
        cmdId: -1,
        type: "Camera",
        pos: { top: 0, left: 0 },
        eqLogicId: -1,
        localApiKey: "",
        refreshInterval: 0,
        title: "Camera",
        quality: true,
        style: {
          border: false,
          width: "auto",
          height: "auto",
          transparent: true,
          titleSize: 20
        }
      })
    },
    withoutTitle: {
      type: Boolean,
      default: false
    },
    value: {
      type: Array,
      default: () => []
    },
    default: {
      type: Array,
      default: () => []
    },
    type: {}
  },
  data: () => ({
    dashsList: []
  }),
  mounted() {
    Communication.get("/api/dash/all", result => {
      this.dashsList = result;
    });
  },
  computed: {
    dataLoaded() {
      return !(Object.keys(this.rawDashs).length === 0);
    },
    menus: {
      get() {
        return this.value;
      },
      set(newValue) {
        this.$emit("input", newValue);
      }
    }
  },
  methods: {
    addMenu() {
      this.menus.push({ target: -1, icon: "fa-times" });
    }
  }
};
</script>