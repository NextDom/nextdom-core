<template>
  <div class="power-info-cmd cmd">
    <span class="pull-left">Puissance :</span>
    <span class="pull-right">{{ cmd.state }} {{ cmd.unite }}</span>
  </div>
</template>

<script>
export default {
  name: "PowerInfoCmd",
  props: {
    cmd: null
  },
  mounted() {
    this.$store.commit("addShowedCmd", { cmd: this.cmd });
  }
};
</script>
