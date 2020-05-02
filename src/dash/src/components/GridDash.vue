<template>
  <GridContainer v-model="gridData" v-bind:parentId="parentId" />
</template>

<script>
import GridContainer from "@/components/GridContainer.vue";
export default {
  name: "GridDash",
  components: {
    GridContainer
  },
  data: () => ({
    gridData: {
      id: "0",
      children: [],
      orientation: "horizontal",
      type: "grid"
    },
    parentId: "0"
  }),
  mounted() {
    this.$store.commit("setDashType", "grid");
  }
};
</script>
