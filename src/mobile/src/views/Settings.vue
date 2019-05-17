<template>
  <mu-container class="global settings">
    <h1>Paramètres</h1>
    <mu-button flat id="disconnect-button" color="primary">
      <mu-icon left value="lock_open"></mu-icon>Déconnexion
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
    /**
     * Disconnect user
     */
    disconnect: function() {
      communication.disconnect();
    }
  }
};
</script>

<style scoped lang="scss">
@import "@/assets/theme.scss";

#disconnect-button {
  width: 100%;
}
</style>

