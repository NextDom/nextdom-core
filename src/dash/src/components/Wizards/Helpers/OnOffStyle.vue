<!--
Choix d'un groupe d'îcone d'état
-->
<template>
  <v-radio-group v-model="icon" row>
    <v-radio v-for="(iconGroup,name, index) in iconGroups" v-bind:key="`iconGroup-${index}`" v-bind:value="name">
      <template v-slot:label>
        <v-icon>{{ iconGroup.on }}</v-icon>/
        <v-icon>{{ iconGroup.off }}</v-icon>
      </template>
    </v-radio>
  </v-radio-group>
</template>

<script>
import Data from "@/libs/Data";

export default {
  name: "OnOffStyle",
  props: {
    value: String
  },
  data: () => ({
    iconGroups: Data.iconGroups
  }),
  computed: {
    icon: {
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