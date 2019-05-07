<template>
  <mu-container class="settings">
    <h1>Paramètres</h1>
    <mu-button flat color="primary">
      <mu-icon left value="lock_open"></mu-icon>
      {{ Déconnexion }}
    </mu-button>
  </mu-container>
</template>

<script>
import communication from "@/libs/communication.js";

export default {
  name: "settings",
  components: {},
  mounted() {
    // Get dashboard data
    this.$emit("setCurrentView", "/settings");
  },
  methods: {
    disconnect: function() {
      communication.disconnect();
    }
  }
};
</script>

<style scoped lang="scss">
</style>

