<template>
  <div
    class="default-info-cmd"
    v-if="parseInt(cmd.visible) === 1"
  >{{ cmd.name }} {{ cmd.state }} {{ cmd.unite }}</div>
</template>

<script>
export default {
  name: "DefaultInfoCmd",
  props: {
    cmd: null
  },
  mounted() {
    this.$store.commit("addShowedCmd", { cmd: this.cmd });
  }
};
</script>
