<template>
  <mu-container class="consumption-info-cmd cmd">
    <span class="pull-left">Consommation :</span>
    <span class="pull-right">{{ cmd.state }} {{ cmd.unite }}</span>
  </mu-container>
</template>

<script>
export default {
  name: "ConsumptionInfoCmd",
  props: {
    cmd: null
  },
  mounted() {
    this.$store.commit("addShowedCmd", { cmd: this.cmd });
  }
};
</script>
