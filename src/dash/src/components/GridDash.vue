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
  props: {
    value: Object
  },
  data: () => ({
    /*
    gridData: {
      id: "0",
      children: [],
      orientation: "horizontal",
      type: "grid"
    },
  */
    parentId: "0"
  }),
  mounted() {
    this.$store.commit("setDashType", "grid");
  },
  computed: {
    gridData: {
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
