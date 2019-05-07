<template>
  <mu-container class="home">
    <h1>Résumé</h1>
    <Summary v-bind:roomsSummary="roomsSummary"></Summary>
  </mu-container>
</template>

<script>
import communication from "@/libs/communication.js";
import Summary from "@/components/Summary.vue";
import eventsManager from "@/libs/eventsManager.js";

export default {
  name: "home",
  data: function() {
    return {
      roomsSummary: null
    };
  },
  props: {
    roomId: null
  },
  components: {
    Summary
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
        this.initData
      );
    }
  },
  methods: {
    initData(data) {
      this.roomsSummary = data;
    }
  }
};
</script>

<style scoped lang="scss">
.mu-sub-header {
  font-size: 1.5rem;
}
</style>

