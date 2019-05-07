<template>
  <mu-container class="scenarios">
    <h1>Scénarios</h1>
  </mu-container>
</template>

<script>
export default {
  name: "scenarios",
  components: {},
  mounted() {
    // Get dashboard data
    this.$emit("setCurrentView", "/scenarios");
  }
};
</script>

<style scoped lang="scss">
</style>

