<template>
  <v-app>
    <Dash />
  </v-app>
</template>

<script>
import Dash from "./components/Dash";

export default {
  name: "App",

  components: {
    Dash
  },

  data: () => ({
    //
  })
};
</script>

<style>
html {
  overflow-y: auto !important;
}
</style>