<template>
  <div class="default-info-cmd cmd" v-if="parseInt(cmd.visible) === 1">
    <span class="pull-left">{{ cmd.name }}</span>
    <span class="pull-right">{{ cmd.state }} {{ cmd.unite }}</span>
  </div>
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
