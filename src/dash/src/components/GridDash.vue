<template>
  <GridContainer v-model="gridData" root="0" />
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
