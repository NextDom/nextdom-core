<template>
  <div class="consumption-info-cmd cmd">Consommation : {{ cmd.value }} {{ cmd.unite }}</div>
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
