<template>
  <div class="consumption-info-cmd cmd">
    <span class="pull-left">Consommation :</span>
    <span class="pull-right">{{ cmd.state }} {{ cmd.unite }}</span>
  </div>
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
