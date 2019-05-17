<template>
  <mu-container class="power-info-cmd cmd">
    <span class="pull-left">Puissance :</span>
    <span class="pull-right">{{ cmd.state }} {{ cmd.unite }}</span>
  </mu-container>
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
