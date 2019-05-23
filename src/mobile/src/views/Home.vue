<template>
  <mu-container class="global home">
    <h1>Résumé</h1>
    <Dashboard v-bind:roomData="roomData"></Dashboard>
  </mu-container>
</template>

<script>
import communication from "@/libs/communication.js";
import Dashboard from "@/components/Dashboard.vue";
import eventsManager from "@/libs/eventsManager.js";
import AppEventsBus from "@/libs/appEventsBus.js";

export default {
  name: "home",
  data: function() {
    return {
      roomData: null
    };
  },
  props: {
    roomId: null
  },
  components: {
    Dashboard
  },
  mounted() {
    // Get dashboard data
    this.$emit("setCurrentView", "/");
    // Start update loop
    eventsManager.loop();
    // Default view
    if (this.roomId === undefined) {
      communication.get("/api/summary/get_default_room_tree", this.initData);
    } else {
      // Room specific view
      communication.get(
        "/api/summary/get_room_tree/" + this.roomId,
        this.initData,
        error => {
          AppEventsBus.$emit("showError", error);
        }
      );
    }
  },
  methods: {
    initData(data) {
      this.roomData = data;
    }
  }
};
</script>

<style scoped lang="scss">
.mu-sub-header {
  font-size: 1.5rem;
}
</style>

