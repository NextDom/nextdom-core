<template>
  <div class="eqlogics">
    <Widget
      v-for="eqLogic in eqLogicsList"
      v-bind:key="eqLogic.id"
      v-bind:cmds="eqLogic.cmds"
      v-bind:eqlogic="eqLogic"
    ></Widget>
  </div>
</template>

<script>
import Widget from "./Widget";

export default {
  name: "EqLogics",
  props: {
    eqLogicsList: null
  },
  components: {
    Widget
  }
};
</script>

<style>
.eqlogics {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: center;
  align-content: stretch;
}
</style>
