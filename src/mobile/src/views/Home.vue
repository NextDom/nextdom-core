<template>
  <div class="home">
    <h1>Résumé</h1>
    <Summary v-bind:roomsSummary="roomsSummary"></Summary>
  </div>
</template>

<script>
import communication from "../libs/communication.js";
import Summary from "@/components/Summary.vue";

export default {
  name: "home",
  data: function() {
    return {
      roomsSummary: null
    };
  },
  components: {
    Summary
  },
  mounted() {
    // Get dashboard data
    communication.get("/api/summary/get_default_room_tree", this.initData);
  },
  methods: {
    initData(data) {
      this.roomsSummary = data;
    }
  }
};
</script>

<style scoped lang="scss">
</style>

