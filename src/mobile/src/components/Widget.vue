<template>
  <div class="widget">
    <h3>{{ eqlogic.name }}</h3>
    <div v-for="cmd in eqlogic.cmds" v-bind:key="cmd.id" class="cmds">{{ cmd.name }}</div>
  </div>
</template>

<script>
export default {
  name: "Widget",
  props: {
    eqlogic: null
  }
};
</script>

<style scoped lang="scss">
.widget {
  width: 25%;
  box-sizing: border-box;
  border: 1px solid black;
}
</style>
