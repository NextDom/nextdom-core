<template>
  <div class="power-info-cmd cmd">Puissance : {{ cmd.value }} {{ cmd.unite }}</div>
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
