<template>
  <v-radio-group v-model="icon" row>
    <v-radio value label="Pas d'icône" />
    <v-radio v-for="iconCode in iconsList" v-bind:key="iconCode" v-bind:value="iconCode">
      <template v-slot:label>
        <v-icon>{{ iconCode }}</v-icon>
      </template>
    </v-radio>
  </v-radio-group>
</template>

<script>
export default {
  name: "IconStyle",
  props: {
    value: String
  },
  data: () => ({
    iconsList: [
      "mdi-play",
      "mdi-play-circle",
      "fa-bolt",
      "fa-thermometer-three-quarters"
    ]
  }),
  computed: {
    icon: {
      get() {
        return this.value;
      },
      set(newValue) {
        if (newValue === 0) {
          newValue = "";
        }
        this.$emit("input", newValue);
      }
    }
  }
};
</script>