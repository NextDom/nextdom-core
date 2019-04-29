<template>
  <div class="home">
    <h1>Résumé</h1>
    <Summary v-bind:roomsSummary="roomsSummary"></Summary>
  </div>
</template>

<script>
import axios from "axios";
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
    axios.get("/api/summary/get_default_room_tree").then(response => {
      this.roomsSummary = response.data;
    });
  }
};
</script>

<style scoped lang="scss">
</style>

